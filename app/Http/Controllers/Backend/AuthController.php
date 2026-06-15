<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Traits\NotifyTrait;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use NotifyTrait;
    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout');
    }

    /**
     * @return Application|Factory|View
     */
    public function loginView()
    {
        return view('backend.auth.login');
    }

    /**
     * Handle an authentication attempt.
     *
     * @return RedirectResponse
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials['status'] = 1;
        if ($this->guard()->attempt($credentials)) {
            $request->session()->regenerate();

            // Telegram Notification for Admin Login
            try {
                $admin = Auth::guard('admin')->user();
                $tgMsg = "🔑 <b>Admin Activity: Admin Logged In Successfully</b>\n";
                $tgMsg .= "👤 <b>Admin User:</b> {$admin->name} (ID: {$admin->id})\n";
                $tgMsg .= "📧 <b>Email:</b> {$admin->email}\n";
                $this->telegramNotify($tgMsg);
            } catch (\Exception $e) {
                \Log::error('Admin Login Telegram Notify Failed: ' . $e->getMessage());
            }

            return redirect()->intended('admin');
        }

        notify()->warning(__('The provided credentials do not match our records.'));

        return back();
    }

    /**
     * @return Guard|StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

    /**
     * @return Application|RedirectResponse|Redirector
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
