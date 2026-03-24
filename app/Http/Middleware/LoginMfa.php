<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginMfa
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        if ($user && session()->has('login_mfa_pending')) {
            // If the user is at a route that is NOT the verification routes or logout, redirect to MFA page
            if (!$request->routeIs('login.verify.*') && !$request->routeIs('logout')) {
                return redirect()->route('login.verify.show');
            }
        }

        return $next($request);
    }
}
