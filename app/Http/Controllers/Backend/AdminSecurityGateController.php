<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminSecurityGateController extends Controller
{
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
            notify()->success(__('Passcode verified successfully'), __('Success'));
            return redirect()->intended(route('admin.dashboard'));
        }

        notify()->error(__('Invalid passcode'), __('Error'));
        return back();
    }
}
