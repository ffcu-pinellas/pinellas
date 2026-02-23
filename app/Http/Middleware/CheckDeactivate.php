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
            $errorMsg = 'Your account is disabled, please contact our support at '.setting('support_email', 'global');

            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route($redirect)->withErrors(['msg' => $errorMsg]);
        } elseif (auth()->check() && auth()->user()->status == 2) {
            $guard = auth()->getDefaultDriver();
            $redirect = $guard == 'admin' ? 'admin.login' : 'login';
            $errorMsg = 'Your account is closed, please contact our support at '.setting('support_email', 'global');

            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route($redirect)->withErrors(['msg' => $errorMsg]);
        }

        return $next($request);
    }
}
