<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Traits\NotifyTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSecurityGateController extends Controller
{
    use NotifyTrait;

    public function show()
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->passcode_status == 0 || session('admin_passcode_verified') === true) {
            return redirect()->route('admin.dashboard');
        }

        return view('backend.auth.security_gate');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'passcode' => 'required',
        ]);

        $admin = Auth::guard('admin')->user();

        if ($admin && ($request->passcode === (string) $admin->passcode || Hash::check($request->passcode, $admin->passcode))) {
            session(['admin_passcode_verified' => true]);
            return response()->json([
                'status' => 'success',
                'message' => 'Security verification successful.',
                'redirect' => route('admin.dashboard')
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid Security Passcode.'], 422);
    }

    public function sendEmailFallback(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return response()->json(['status' => false, 'message' => 'Unauthorized access.'], 401);
        }

        $code = random_int(100000, 999999);
        session(['admin_email_gate_otp' => $code]);

        $shortcodes = [
            '[[code]]' => $code,
            '[[otp_code]]' => $code,
            '[[full_name]]' => $admin->name,
            '[[site_title]]' => setting('site_title', 'global'),
        ];

        try {
            $this->mailNotify($admin->email, 'admin_forget_password', $shortcodes);
        } catch (\Throwable $e) {
            \Log::error("Security gate OTP email failed: " . $e->getMessage());
        }

        return response()->json(['status' => true, 'message' => 'Verification code sent to your registered admin email.']);
    }

    public function verifyEmailFallback(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);

        $savedCode = session('admin_email_gate_otp');

        if ($savedCode && (string)$request->code === (string)$savedCode) {
            session()->forget('admin_email_gate_otp');
            session(['admin_passcode_verified' => true]);
            return response()->json([
                'status' => 'success',
                'message' => 'Email verification successful.',
                'redirect' => route('admin.dashboard')
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid or expired verification code.'], 422);
    }
}
