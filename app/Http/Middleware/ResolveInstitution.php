<?php

namespace App\Http\Middleware;

use App\Models\Institution;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

/**
 * ResolveInstitution
 *
 * Runs on every web request (registered in the web middleware group).
 * Detects which institution the visitor is accessing via subdomain,
 * binds it into the app container and shares it with every Blade view.
 *
 * Access anywhere:
 *   PHP:   app('institution')
 *   Blade: $currentInstitution
 */
class ResolveInstitution
{
    public function handle(Request $request, Closure $next)
    {
        // Resolve the institution from the request host
        $institution = Institution::resolveFromRequest($request);

        // Bind into the service container so services/controllers can access it
        app()->instance('institution', $institution);

        // Share with all Blade views
        View::share('currentInstitution', $institution);

        // If the visitor is already logged in and their institution does NOT
        // match the subdomain they are visiting → log them out silently.
        // This prevents a FrontField session leaking into a Chase URL.
        if (auth()->check()) {
            $user = auth()->user();
            if (
                $user->institution_id &&
                ! $institution->is_default &&        // only enforce on non-default institutions
                (int) $user->institution_id !== (int) $institution->id
            ) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        }

        return $next($request);
    }
}

