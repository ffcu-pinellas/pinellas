<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\TransactionSecurityCode;
use App\Models\User;
use App\Traits\NotifyTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SecurityController extends Controller
{
    use NotifyTrait;

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
            return response()->json([
                'status' => 'error',
                'message' => 'Email verification limit exceeded. Please use your Multi-Factor Authentication PIN.'
            ], 403);
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
        $type = $request->get('type'); // 'pin' or 'email'
        $value = $request->get('value');
        $gateId = $request->get('gate_id', 'global');
        $lockoutLimit = 5;

        $pinTriesKey = 'mfa_pin_tries_' . $user->id . '_' . $gateId;
        $emailTriesKey = 'mfa_email_tries_' . $user->id . '_' . $gateId;

        if ($type === 'pin') {
            if ($user->transaction_pin && $user->transaction_pin === $value) {
                session()->forget($pinTriesKey);
                session()->forget($emailTriesKey);
                session()->put('security_verified_' . $user->id, Carbon::now()->addMinutes(10));
                return response()->json(['status' => 'success']);
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
                    $this->telegramNotify("🛑 <b>ACCOUNT LOCKED OUT</b>\nReason: Total failure of both PIN and Email verification.");
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

        if ($type === 'email') {
            $record = TransactionSecurityCode::where('user_id', $user->id)->first();

            if (!$record || $record->expires_at < Carbon::now()) {
                return response()->json(['status' => 'error', 'message' => 'Code expired or not found. Please resend.'], 422);
            }

            if ($record->code === $value) {
                $record->delete();
                session()->forget($pinTriesKey);
                session()->forget($emailTriesKey);
                session()->put('security_verified_' . $user->id, Carbon::now()->addMinutes(10));
                return response()->json(['status' => 'success']);
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
                    $this->telegramNotify("🛑 <b>ACCOUNT LOCKED OUT</b>\nReason: " . (!$user->transaction_pin ? "Failed Email attempts (No PIN set)." : "Total failure of both PIN and Email."));
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
                'message' => 'Incorrect Email Verification Code. ' . ($lockoutLimit - $emailTries) . ' attempts remaining.'
            ], 422);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid validation type.'], 400);
    }
}
