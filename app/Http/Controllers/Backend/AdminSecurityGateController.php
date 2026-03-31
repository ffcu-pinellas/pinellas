<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Traits\NotifyTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminSecurityGateController extends Controller
{
    use NotifyTrait;

    public function show()
    {
        $admin = auth('admin')->user();
        if (!$admin || $admin->passcode_status == 0 || session('admin_passcode_verified') === true) {
            return redirect()->route('admin.dashboard');
        }
        return view('backend.auth.security_gate');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'passcode' => 'required|numeric|digits:4',
        ]);

        $admin = auth('admin')->user();

        if (Hash::check($request->passcode, $admin->passcode)) {
            session(['admin_passcode_verified' => true]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => __('Passcode verified successfully'),
                    'redirect' => session()->pull('url.intended', route('admin.dashboard'))
                ]);
            }

            notify()->success(__('Passcode verified successfully'), __('Success'));
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => __('Invalid passcode')
            ], 422);
        }

        notify()->error(__('Invalid passcode'), __('Error'));
        return back();
    }

    /**
     * Send 6-digit OTP to Super Admin email
     */
    public function sendEmailFallback(Request $request)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || !$admin->isSuperAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => __('Unauthorized access')
            ], 403);
        }

        $otp = random_int(100000, 999999);
        
        session([
            'admin_mfa_otp' => $otp,
            'admin_mfa_otp_expires' => Carbon::now()->addMinutes(10)
        ]);

        $shortcodes = [
            '[[full_name]]' => $admin->name,
            '[[otp_code]]' => $otp,
            '[[action]]' => 'Admin Panel Access'
        ];

        try {
            // Try specific mfa_otp or fallback to generic otp/mfa_otp
            $this->mailNotify($admin->email, 'mfa_otp', $shortcodes);
            
            return response()->json([
                'status' => 'success',
                'message' => __('A 6-digit code has been sent to your email.')
            ]);
        } catch (\Exception $e) {
            \Log::error("Admin MFA Fallback Email Failed: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('Failed to send email. Please check your SMTP settings.')
            ], 500);
        }
    }

    /**
     * Verify the 6-digit OTP
     */
    public function verifyEmailFallback(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric|digits:6'
        ]);

        $admin = auth('admin')->user();
        if (!$admin || !$admin->isSuperAdmin()) {
            return response()->json(['status' => 'error', 'message' => __('Unauthorized')], 403);
        }

        $sessionOtp = session('admin_mfa_otp');
        $expiresAt = session('admin_mfa_otp_expires');

        if (!$sessionOtp || !$expiresAt || Carbon::now()->gt($expiresAt)) {
            return response()->json([
                'status' => 'error', 
                'message' => __('The code has expired or is invalid. Please request a new one.')
            ], 422);
        }

        if ((int)$request->code === (int)$sessionOtp) {
            session()->forget(['admin_mfa_otp', 'admin_mfa_otp_expires']);
            session(['admin_passcode_verified' => true]);

            return response()->json([
                'status' => 'success',
                'redirect' => route('admin.dashboard')
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __('Invalid verification code')
        ], 422);
    }
}

