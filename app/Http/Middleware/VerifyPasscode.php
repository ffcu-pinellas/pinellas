<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class VerifyPasscode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type): Response
    {

        $key = $type.'_passcode_status';
        $user = $request->user();

        \Log::info("VerifyPasscode Middleware - Type: $type, Key: $key, Enabled: " . setting($key, 'passcode'));

        if ($user->passcode !== null && setting($key, 'passcode') == 1 && ! Hash::check($request->passcode, $user->passcode)) {
            \Log::warning("VerifyPasscode Failed for User ID: {$user->id}. Input Passcode: " . ($request->passcode ? 'PROVIDED' : 'MISSING'));
            notify()->error(__('Passcode is wrong!'), 'Error');
            return back();
        }

        return $next($request);
    }
}
