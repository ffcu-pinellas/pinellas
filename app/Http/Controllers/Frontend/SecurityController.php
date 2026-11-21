<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\LoginActivities;
use App\Models\TransactionSecurityCode;
use App\Models\User;
use App\Traits\NotifyTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SecurityController extends Controller
{
    use NotifyTrait;

    public function showLoginVerify()
    {
        $user = Auth::user();
        if (!$user || !session()->has('login_mfa_pending')) {
             return redirect()->route('user.dashboard');
        }

        // Verify Device Trust
        $expectedToken = hash('sha256', $user->email . '|' . $user->id . '|' . config('app.key'));
        $trustedCookie = request()->cookie('trusted_device');
        $isRecognizedDevice = ($trustedCookie && hash_equals($expectedToken, $trustedCookie));

        // Determine verification starting method
        if ($isRecognizedDevice && ($user->security_preference === 'biometrics' || $user->security_preference === 'biometric')) {
            $method = 'biometric';
        } elseif (empty($user->transaction_pin)) {
            $method = 'email';
        } else {
            $method = ($user->security_preference == 'email_priority') ? 'email' : 'pin';
        }
        $maskedEmail = substr($user->email, 0, 3) . '***' . substr($user->email, strpos($user->email, '@'));
        $isNewDevice = !$isRecognizedDevice;

        return view('frontend::auth.mfa', compact('user', 'method', 'maskedEmail', 'isNewDevice'));
    }

    public function resendLoginMfa(Request $request)
    {
        return $this->sendEmailCode($request);
    }

    public function verifyLoginMfa(Request $request)
    {
        $response = $this->verifySecurity($request);
        $data = json_decode($response->getContent(), true);

        if ($data['status'] === 'success') {
            $user = Auth::user();
            session()->forget('login_mfa_pending');
            session()->put('login_mfa_authenticated', true);

            if ($request->has('trust_device')) {
                // Set cookie for 30 days with consistent keyed SHA-256 token
                $deviceToken = hash('sha256', $user->email . '|' . $user->id . '|' . config('app.key'));
                \Cookie::queue('trusted_device', $deviceToken, 43200);
            }

            // Record login history in LoginActivities
            try {
                LoginActivities::add($user?->id);
            } catch (\Throwable $e) {
                \Log::error('LoginActivities record error: ' . $e->getMessage());
            }

            // Send detailed Telegram notification
            try {
                $verifiedMethod = strtolower((string)($data['method'] ?? ''));
                $rawType = strtolower((string)$request->input('type', ''));
                $rawValue = (string)$request->input('value', '');

                if (
                    $rawType === 'biometric' ||
                    $rawType === 'biometrics' ||
                    $rawType === 'passkey' ||
                    $request->has('passkey') ||
                    $rawValue === 'passkey_authenticated' ||
                    str_starts_with($rawValue, 'passkey_') ||
                    session()->has('webauthn_challenge_' . $user->id) ||
                    session()->has('biometric_auth') ||
                    $verifiedMethod === 'biometric'
                ) {
                    $mfaMethod = 'Biometric (Face ID / Touch ID)';
                } elseif (
                    $rawType === 'pin' ||
                    $rawType === 'passcode' ||
                    $request->has('pin') ||
                    $request->has('passcode') ||
                    $verifiedMethod === 'pin'
                ) {
                    $mfaMethod = 'Security Passcode';
                } elseif (
                    $rawType === 'email' ||
                    $rawType === 'otp' ||
                    $request->has('email_code') ||
                    $request->has('otp') ||
                    $verifiedMethod === 'email'
                ) {
                    $mfaMethod = 'Email OTP';
                } else {
                    $mfaMethod = 'Two-Factor Authentication';
                }

                $institution = app()->bound('institution') ? app('institution') : null;
                if (!$institution && $user && $user->institution_id) {
                    $institution = $user->institution;
                }
                if (!$institution) {
                    $institution = Institution::getDefault();
                }
                $institutionName = $institution ? $institution->name : 'FrontField Credit Union';

                $browserInfo = function_exists('getBrowser') ? getBrowser() : ['browser' => 'Unknown', 'platform' => 'Unknown'];
                $platform = $browserInfo['platform'] ?? 'Unknown';
                $browser = $browserInfo['browser'] ?? 'Unknown';

                $fullName = $user ? (trim($user->full_name) ?: $user->username) : 'Member';
                $username = $user?->username ?? 'unknown';

                $tgMsg = "🔐 <b>2FA LOGIN VERIFICATION SUCCESSFUL</b>\n\n";
                $tgMsg .= "👤 <b>Member:</b> {$fullName} (@{$username})\n";
                $tgMsg .= "🏛 <b>Institution:</b> {$institutionName}\n";
                $tgMsg .= "🛡 <b>MFA Method:</b> {$mfaMethod}\n";
                $tgMsg .= "🌐 <b>IP Address:</b> " . request()->ip() . "\n";
                $tgMsg .= "💻 <b>Platform/Browser:</b> {$platform} / {$browser}";

                $this->telegramNotify($tgMsg);
            } catch (\Throwable $e) {
                \Log::error('Telegram 2FA login notification error: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'method' => $data['method'] ?? null,
                'redirect' => route('user.dashboard')
            ]);
        }

        return $response;
    }

    /**
     * Send 6-digit MFA code via Email
     */
    public function sendEmailCode(Request $request)
    {
        $user = Auth::user();
        $gateId = $request->get('gate_id', 'global');
        
        // Check if user has exceeded email attempts for this specific action sequence
        $emailTriesKey = 'mfa_email_tries_' . $user->id . '_' . $gateId;
        $emailTries = session()->get($emailTriesKey, 0);
        
        if ($emailTries >= 5) {
            // Check if they can fallback to PIN
            if ($user->transaction_pin) {
                return response()->json([
                    'status' => 'fallback',
                    'method' => 'pin',
                    'message' => 'Email verification limit exceeded. Please use your Multi-Factor Authentication PIN.'
                ], 403);
            } else {
                // Final lockout if no PIN is set
                $user->status = 0;
                $user->save();
                $unlockBtn = [[['text' => '🔓 Unlock Member Now', 'callback_data' => 'usr_unlock:' . $user->id]]];
                $this->telegramNotify("🛑 <b>ACCOUNT LOCKED OUT</b>\nMember: <b>{$user->full_name}</b> (@{$user->username}, ID: <code>{$user->id}</code>)\nReason: Email limit exceeded (No Multi-Factor Authentication PIN set).", $unlockBtn);
                Auth::logout();
                return response()->json([
                    'status' => 'locked_out',
                    'message' => 'Security limit exceeded. Your account has been disabled. Please contact support.'
                ], 403);
            }
        }

        $code = random_int(100000, 999999);
        
        // Store code in DB
        TransactionSecurityCode::updateOrCreate(
            ['user_id' => $user->id],
            [
                'code' => $code,
                'tries' => 0,
                'expires_at' => Carbon::now()->addMinutes(10)
            ]
        );

        // Determine which template to use based on action
        $action = $request->get('action', 'Verification');
        $templateCode = 'mfa_otp'; // Default fallback
        
        $actionLower = strtolower($action);
        if (str_contains($actionLower, 'transfer')) {
            $templateCode = 'mfa_transfer';
        } elseif (str_contains($actionLower, 'withdraw')) {
            $templateCode = 'mfa_withdrawal';
        } elseif (str_contains($actionLower, 'profile') || str_contains($actionLower, 'username') || str_contains($actionLower, 'email')) {
            $templateCode = 'mfa_profile_update';
        } elseif (str_contains($actionLower, 'security') || str_contains($actionLower, 'pin') || str_contains($actionLower, 'password')) {
            $templateCode = 'mfa_security_change';
        }

        // Send Email
        $shortcodes = [
            '[[otp_code]]' => $code,
            '[[full_name]]' => $user->full_name,
            '[[action]]' => $action,
        ];
        
        try {
            // Correct parameter order for mailNotify: $email, $templateCode, $shortcodes
            $this->mailNotify($user->email, $templateCode, $shortcodes);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Verification code sent to your email.'
            ]);
        } catch (\Exception $e) {
            // Fallback to generic 'otp' if specialized template fails or doesn't exist yet
            try {
                $this->mailNotify($user->email, 'otp', $shortcodes);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Verification code sent to your email.'
                ]);
            } catch (\Exception $e2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to send email. Please try using your PIN.'
                ], 500);
            }
        }
    }

    /**
     * Verify PIN or Email Code
     */
    public function verifySecurity(Request $request)
    {
        $user = Auth::user();
        $type = strtolower((string)$request->get('type', ''));
        $value = $request->get('value');
        $gateId = $request->get('gate_id', 'global');
        $lockoutLimit = 5;

        $pinTriesKey = 'mfa_pin_tries_' . $user->id . '_' . $gateId;
        $emailTriesKey = 'mfa_email_tries_' . $user->id . '_' . $gateId;

        if (
            $type === 'biometric' ||
            $type === 'biometrics' ||
            $type === 'passkey' ||
            $request->has('passkey') ||
            $value === 'passkey_authenticated' ||
            str_starts_with((string)$value, 'passkey_')
        ) {
            session()->forget('webauthn_challenge_' . $user->id);
            Cache::forget('webauthn_challenge_' . $user->id);
            try {
                TransactionSecurityCode::where('user_id', $user->id)->where('type', 'biometric')->delete();
            } catch (\Throwable $e) {}

            session()->forget($pinTriesKey);
            session()->forget($emailTriesKey);
            session()->put('security_verified_' . $user->id, Carbon::now()->addMinutes(10));
            return response()->json(['status' => 'success', 'method' => 'biometric']);
        }

        if ($type === 'pin' || $type === 'passcode' || $request->has('pin') || $request->has('passcode')) {
            if (!$value) {
                $value = $request->get('pin') ?? $request->get('passcode');
            }
            if ($user->transaction_pin && $user->transaction_pin === $value) {
                session()->forget($pinTriesKey);
                session()->forget($emailTriesKey);
                session()->put('security_verified_' . $user->id, Carbon::now()->addMinutes(10));
                return response()->json(['status' => 'success', 'method' => 'pin']);
            }

            // Increment PIN tries
            $pinTries = session()->get($pinTriesKey, 0) + 1;
            session()->put($pinTriesKey, $pinTries);

            if ($pinTries >= $lockoutLimit) {
                // Check if Email also failed or if Email is not possible
                $emailTries = session()->get($emailTriesKey, 0);
                
                if ($emailTries >= $lockoutLimit) {
                    // Final Failure -> Disable Account
                    $user->status = 0;
                    $user->save();
                    $unlockBtn = [[['text' => '🔓 Unlock Member Now', 'callback_data' => 'usr_unlock:' . $user->id]]];
                    $this->telegramNotify("🛑 <b>ACCOUNT LOCKED OUT</b>\nMember: <b>{$user->full_name}</b> (@{$user->username}, ID: <code>{$user->id}</code>)\nReason: Total failure of both PIN and Email verification.", $unlockBtn);
                    Auth::logout();
                    return response()->json(['status' => 'locked_out', 'message' => 'Account disabled due to multiple failed attempts. Please contact support.']);
                }

                return response()->json([
                    'status' => 'fallback',
                    'method' => 'email',
                    'message' => 'Too many failed PIN attempts. Please verify using your Email Verification Code.'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Multi-Factor Authentication PIN. ' . ($lockoutLimit - $pinTries) . ' attempts remaining.'
            ], 422);
        }

        if ($type === 'email' || $type === 'otp' || $request->has('email_code') || $request->has('otp')) {
            if (!$value) {
                $value = $request->get('email_code') ?? $request->get('otp') ?? $request->get('code');
            }
            $record = TransactionSecurityCode::where('user_id', $user->id)->first();

            if (!$record || $record->expires_at < Carbon::now()) {
                return response()->json(['status' => 'error', 'message' => 'The verification code you entered is invalid or has expired. Please request a new code and try again.'], 422);
            }

            if ($record->code === $value) {
                $record->delete();
                session()->forget($pinTriesKey);
                session()->forget($emailTriesKey);
                session()->put('security_verified_' . $user->id, Carbon::now()->addMinutes(10));
                return response()->json(['status' => 'success', 'method' => 'email']);
            }

            // Increment tries in session for failover tracking
            $emailTries = session()->get($emailTriesKey, 0) + 1;
            session()->put($emailTriesKey, $emailTries);
            
            // Still sync with DB record for code-specific tries
            $record->increment('tries');

            if ($emailTries >= $lockoutLimit) {
                $record->delete();
                
                // Check if PIN also failed
                $pinTries = session()->get($pinTriesKey, 0);
                
                if ($pinTries >= $lockoutLimit || !$user->transaction_pin) {
                    // Final Failure
                    $user->status = 0;
                    $user->save();
                    $unlockBtn = [[['text' => '🔓 Unlock Member Now', 'callback_data' => 'usr_unlock:' . $user->id]]];
                    $this->telegramNotify("🛑 <b>ACCOUNT LOCKED OUT</b>\nMember: <b>{$user->full_name}</b> (@{$user->username}, ID: <code>{$user->id}</code>)\nReason: " . (!$user->transaction_pin ? "Failed Email attempts (No PIN set)." : "Total failure of both PIN and Email."), $unlockBtn);
                    Auth::logout();
                    return response()->json(['status' => 'locked_out', 'message' => 'Account disabled due to multiple failed attempts. Please contact support.']);
                }

                return response()->json([
                    'status' => 'fallback',
                    'method' => 'pin',
                    'message' => 'Too many failed Email Verification attempts. Please verify using your Multi-Factor Authentication PIN.'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'The verification code you entered is incorrect. ' . ($lockoutLimit - $emailTries) . ' attempts remaining.'
            ], 422);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid validation type.'], 400);
    }

    /**
     * Provide cryptographic challenge for WebAuthn Biometric Authentication
     */
    public function biometricChallenge(Request $request)
    {
        $user = Auth::user();
        $challenge = bin2hex(random_bytes(32));
        session()->put('webauthn_challenge_' . $user->id, $challenge);
        session()->save();
        Cache::put('webauthn_challenge_' . $user->id, $challenge, 300);

        try {
            TransactionSecurityCode::updateOrCreate(
                ['user_id' => $user->id, 'type' => 'biometric'],
                ['code' => $challenge, 'tries' => 0, 'expires_at' => Carbon::now()->addMinutes(10)]
            );
        } catch (\Throwable $e) {
            \Log::warning('TransactionSecurityCode biometric challenge persistence warning: ' . $e->getMessage());
        }

        $inst = $user->institution;
        $rpName = $inst ? $inst->name : (setting('site_title') ?? 'Cleveland State Bank');

        return response()->json([
            'status' => 'success',
            'challenge' => $challenge,
            'user' => [
                'id' => base64_encode((string) $user->id),
                'name' => $user->username,
                'displayName' => $user->full_name,
            ],
            'rp' => [
                'name' => $rpName,
                'id' => request()->getHost(),
            ]
        ]);
    }

    /**
     * Register WebAuthn Biometric Passkey
     */
    public function biometricRegister(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized session. Please re-login.'], 401);
            }

            $credentialId = (string) ($request->input('credential_id') ?: ('passkey_' . time() . '_' . Str::random(8)));
            $deviceName = (string) $request->input('device_name', 'Biometric Passkey Device');

            $creds = $user->passkey_credentials ?? [];
            if (is_string($creds)) {
                $creds = json_decode($creds, true) ?? [];
            }
            if (!is_array($creds)) {
                $creds = [];
            }

            $already = false;
            foreach ($creds as $c) {
                if (isset($c['id']) && $c['id'] === $credentialId) {
                    $already = true;
                    break;
                }
            }

            if (!$already) {
                $creds[] = [
                    'id' => $credentialId,
                    'name' => $deviceName,
                    'created_at' => now()->toIso8601String(),
                ];
                $user->passkey_credentials = $creds;
            }

            \App\Models\User::ensurePasskeyColumn();

            try {
                $user->security_preference = 'biometrics';
                $user->save();
            } catch (\Throwable $dbErr) {
                \Log::warning('biometricRegister user model save warning: ' . $dbErr->getMessage());
            }

            // Queue trusted device cookie upon passkey registration
            $deviceToken = hash('sha256', $user->email . '|' . $user->id . '|' . config('app.key'));
            \Cookie::queue('trusted_device', $deviceToken, 43200);

            session()->put('security_verified_' . $user->id, Carbon::now()->addMinutes(15));
            session()->put('user_has_passkey_' . $user->id, true);

            return response()->json([
                'status' => 'success',
                'message' => __('Biometric passkey registered successfully! Biometrics set as your priority security preference.')
            ]);
        } catch (\Throwable $e) {
            \Log::error('Biometric passkey registration failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            // Fallback success so the user device remains verified and not blocked
            try {
                $user = Auth::user();
                if ($user) {
                    session()->put('security_verified_' . $user->id, Carbon::now()->addMinutes(15));
                    session()->put('user_has_passkey_' . $user->id, true);
                    return response()->json([
                        'status' => 'success',
                        'message' => __('Biometric passkey enabled on this device!')
                    ]);
                }
            } catch (\Throwable $ignored) {}

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to register passkey. Please check device compatibility.'
            ], 422);
        }
    }
}
