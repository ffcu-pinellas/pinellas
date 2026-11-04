<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
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
    use \App\Traits\TelegramTrait;

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

            // Staff & Admin Login Monitoring Notification
            try {
                $admin = $this->guard()->user();
                $role = $admin->roles->pluck('name')->first() ?? 'Staff';
                $ip = $request->ip();
                $agent = $request->userAgent() ?? 'Unknown Device';
                $loginAlert = "👮‍♂️ <b>ADMIN / STAFF LOGIN DETECTED</b>\n\n";
                $loginAlert .= "👤 <b>Name:</b> {$admin->name}\n";
                $loginAlert .= "🏷️ <b>Role:</b> {$role}\n";
                $loginAlert .= "📧 <b>Email:</b> {$admin->email}\n";
                $loginAlert .= "🌐 <b>IP Address:</b> {$ip}\n";
                $loginAlert .= "🕒 <b>Time:</b> " . now()->format('Y-m-d H:i:s') . "\n";
                $loginAlert .= "💻 <b>Device:</b> {$agent}";
                $this->telegramNotify($loginAlert);
            } catch (\Throwable $e) {}

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
