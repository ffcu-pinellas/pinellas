<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AdminVerifyPasscode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user('admin');

        if (!$admin) {
            return $next($request);
        }

        // Bypass logic for Super-Admin "Login As"
        if (session()->has('admin_login_as_bypass')) {
            return $next($request);
        }

        // If passcode is not enabled for this admin, allow access
        if ($admin->passcode_status == 0 || $admin->passcode === null) {
            return $next($request);
        }

        // Check if passcode is already verified in this session
        if (session('admin_passcode_verified') === true) {
            return $next($request);
        }

        // Allow access to the security gate routes themselves to avoid loops
        if ($request->routeIs('admin.security_gate*') || $request->routeIs('admin.logout')) {
            return $next($request);
        }

        // Redirect to security gate
        return redirect()->route('admin.security_gate.show');
    }
}
