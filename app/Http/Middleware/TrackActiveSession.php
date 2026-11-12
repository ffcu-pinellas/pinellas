<?php

namespace App\Http\Middleware;

use App\Services\SessionRadarService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackActiveSession
{
    public function handle(Request $request, Closure $next)
    {
        $path = ltrim($request->path(), '/');
        if (
            str_starts_with($path, 'assets/')
            || str_starts_with($path, 'favicon')
            || str_starts_with($path, 'api/telegram/')
            || str_starts_with($path, 'telegram/')
        ) {
            return $next($request);
        }

        $radar = app(SessionRadarService::class);
        $sessionId = $request->hasSession() ? $request->session()->getId() : null;

        // Enforce immediate killswitch if session was force-disconnected
        if ($sessionId) {
            if (Auth::guard('admin')->check() && $radar->isSessionKicked('admin', $sessionId)) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Session terminated by administrator.'], 401);
                }

                return redirect()->route('admin.login-view')
                    ->withErrors(['email' => __('Your administrative session was terminated remotely.')]);
            }

            if (Auth::guard('web')->check() && $radar->isSessionKicked('web', $sessionId)) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Session terminated by security operations.'], 401);
                }

                return redirect()->route('login')
                    ->withErrors(['email' => __('Your session was terminated remotely by FrontField Security. Please sign in again.')]);
            }
        }

        $response = $next($request);

        // Post-response heartbeat recording so login actions & route middleware auth are captured immediately
        $postSessionId = $request->hasSession() ? $request->session()->getId() : $sessionId;

        if (Auth::guard('admin')->check() && $postSessionId) {
            $admin = Auth::guard('admin')->user();
            if ($admin) {
                $radar->recordActivity($request, 'admin', $admin, $postSessionId);
                if ($admin->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
                    $radar->rememberSuperAdminIp($request->ip());
                }
            }
        }

        if (Auth::guard('web')->check() && $postSessionId) {
            $user = Auth::guard('web')->user();
            if ($user) {
                $radar->recordActivity($request, 'web', $user, $postSessionId);
            }
        }

        if ($request->user('sanctum')) {
            $sanctumUser = $request->user('sanctum');
            $tokenId = optional($sanctumUser->currentAccessToken())->id;
            if ($tokenId) {
                $radar->recordActivity($request, 'sanctum', $sanctumUser, null, $tokenId);
            }
        }

        return $response;
    }
}

