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
        if (auth()->check() && auth()->user()->status == 0) {
            $guard = auth()->getDefaultDriver();
            $redirect = $guard == 'admin' ? 'admin.login' : 'login';
            $errorMsg = 'Access Denied: Your account has been disabled for administrative or security reasons. Please contact Member Support at '.setting('support_email', 'global').' for assistance.';

            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route($redirect)->withErrors(['msg' => $errorMsg]);
        } elseif (auth()->check() && auth()->user()->status == 2) {
            $guard = auth()->getDefaultDriver();
            $redirect = $guard == 'admin' ? 'admin.login' : 'login';
            $errorMsg = 'Account Status: Closed. This account is no longer active. For details or to reactivate your accounts, please visit your local branch or contact Support at '.setting('support_email', 'global').'.';

            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route($redirect)->withErrors(['msg' => $errorMsg]);
        }

        return $next($request);
    }
}
