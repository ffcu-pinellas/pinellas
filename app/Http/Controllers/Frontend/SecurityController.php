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

        if ($type === 'pin') {
            if ($user->transaction_pin && $user->transaction_pin === $value) {
                // Set a short-lived session flag that security is verified for next action
                session()->put('security_verified_' . $user->id, Carbon::now()->addMinutes(5));
                return response()->json(['status' => 'success']);
            }
            return response()->json(['status' => 'error', 'message' => 'Invalid Transaction PIN.'], 422);
        } 
        
        if ($type === 'email') {
            $record = TransactionSecurityCode::where('user_id', $user->id)->first();
            
            if (!$record || $record->expires_at < Carbon::now()) {
                return response()->json(['status' => 'error', 'message' => 'Code expired or not found. Please resend.'], 422);
            }

            if ($record->code === $value) {
                $record->delete();
                session()->put('security_verified_' . $user->id, Carbon::now()->addMinutes(5));
                return response()->json(['status' => 'success']);
            }

            // Increment tries
            $record->increment('tries');
            if ($record->tries >= 5) {
                session()->put('mfa_email_blocked_' . $user->id, true);
                $record->delete();
                return response()->json([
                    'status' => 'fallback', 
                    'message' => 'Too many failed attempts. Please use your Transaction PIN to complete this request.'
                ]);
            }

            return response()->json([
                'status' => 'error', 
                'message' => 'Incorrect code. ' . (5 - $record->tries) . ' attempts remaining.'
            ], 422);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid validation type.'], 400);
    }
}
