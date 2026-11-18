<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckDeactivate
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 0. Executive Officer Lockdown Check
        $isLockdown = (bool) \App\Models\Setting::get('officer_portal_lockdown', 'security');
        if ($isLockdown && auth('admin')->check()) {
            $admin = auth('admin')->user();
            if ($admin && !$admin->isSuperAdmin()) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('admin.login')->withErrors([
                    'msg' => 'Access Denied: The Account Officer portal is temporarily locked down by Executive Administration.'
                ]);
            }
        }

        $institution = app()->bound('institution') ? app('institution') : auth()->user()?->institution;
        $isCsb = ($institution && ($institution->slug === 'clevelandstatebank' || str_contains($institution->slug, 'cleveland')))
            || str_contains(request()->getHost(), 'clevelandstatebank');
        $supportDept = $isCsb ? 'Customer Support' : 'Member Support';
        $supportEmail = $institution?->support_email ?? setting('support_email', 'global') ?? ($isCsb ? 'support@clevelandstatebank.com' : 'support@frontfieldcu.pro');

        if (auth()->check() && auth()->user()->status == 0) {
            $guard = auth()->getDefaultDriver();
            $redirect = $guard == 'admin' ? 'admin.login' : 'login';
            $errorMsg = "Access Denied: Your account has been disabled due to suspicious activities or security reasons. Please contact {$supportDept} at {$supportEmail} for assistance.";

            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route($redirect)->withErrors(['msg' => $errorMsg]);
        } elseif (auth()->check() && auth()->user()->status == 2) {
            $guard = auth()->getDefaultDriver();
            $redirect = $guard == 'admin' ? 'admin.login' : 'login';
            $errorMsg = "Account Status: Closed. This account is no longer active. For more information, please visit your local branch or contact {$supportDept} at {$supportEmail}.";

            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route($redirect)->withErrors(['msg' => $errorMsg]);
        }

        return $next($request);
    }
}
