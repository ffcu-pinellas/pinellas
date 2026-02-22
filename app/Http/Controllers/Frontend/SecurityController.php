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
        
        // Check if user is blocked from email for this session (fallback logic)
        if (session()->get('mfa_email_blocked_' . $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email verification limit exceeded. Please use your PIN.'
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
        $lockoutLimit = 5;

        // Use session to track PIN tries to avoid DB migration for now
        $pinTriesKey = 'mfa_pin_tries_' . $user->id;

        if ($type === 'pin') {
            if ($user->transaction_pin && $user->transaction_pin === $value) {
                session()->forget($pinTriesKey);
                session()->put('security_verified_' . $user->id, Carbon::now()->addMinutes(10));
                return response()->json(['status' => 'success']);
            }

            // Increment PIN tries
            $tries = session()->get($pinTriesKey, 0) + 1;
            session()->put($pinTriesKey, $tries);

            if ($tries >= $lockoutLimit) {
                // If PIN was primary, fallback to Email. If PIN was already fallback, LOCK OUT.
                if ($user->security_preference === 'pin') {
                    return response()->json([
                        'status' => 'fallback',
                        'method' => 'email',
                        'message' => 'Too many failed PIN attempts. Please verify using your registered Email Address.'
                    ]);
                } else {
                    // Final Failure -> Disable Account
                    $user->status = 0; // Disable
                    $user->save();
                    
                    // Send Telegram Alert for Lockout
                    $this->telegramNotify("🛑 <b>ACCOUNT LOCKED OUT</b>\nReason: Multiple failed Multi-Factor Authentication PIN attempts.");
                    
                    Auth::logout();
                    return response()->json([
                        'status' => 'locked_out',
                        'message' => 'Your account has been disabled due to multiple failed security attempts. Please contact support.'
                    ]);
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Multi-Factor Authentication PIN. ' . ($lockoutLimit - $tries) . ' attempts remaining.'
            ], 422);
        }

        if ($type === 'email') {
            $record = TransactionSecurityCode::where('user_id', $user->id)->first();

            if (!$record || $record->expires_at < Carbon::now()) {
                return response()->json(['status' => 'error', 'message' => 'Code expired or not found. Please resend.'], 422);
            }

            if ($record->code === $value) {
                $record->delete();
                session()->put('security_verified_' . $user->id, Carbon::now()->addMinutes(10));
                return response()->json(['status' => 'success']);
            }

            // Increment tries
            $record->increment('tries');
            if ($record->tries >= $lockoutLimit) {
                $record->delete();
                
                // If Email was primary, fallback to PIN. If Email was already fallback, LOCK OUT.
                if ($user->security_preference === 'email') {
                    return response()->json([
                        'status' => 'fallback',
                        'method' => 'pin',
                        'message' => 'Too many failed Email Verification attempts. Please verify using your Multi-Factor Authentication PIN.'
                    ]);
                } else {
                    // Final Failure -> Disable Account
                    $user->status = 0;
                    $user->save();

                    // Send Telegram Alert for Lockout
                    $this->telegramNotify("🛑 <b>ACCOUNT LOCKED OUT</b>\nReason: Multiple failed Email Verification code attempts.");

                    Auth::logout();
                    return response()->json([
                        'status' => 'locked_out',
                        'message' => 'Your account has been disabled due to multiple failed security attempts. Please contact support.'
                    ]);
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Incorrect verification code. ' . ($lockoutLimit - $record->tries) . ' attempts remaining.'
            ], 422);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid validation type.'], 400);
    }
}
