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

        // Tenant isolation enforcement for logged-in members:
        if (auth('web')->check()) {
            $user = auth('web')->user();
            if ($user->institution_id) {
                // If member belongs to a non-default institution (e.g. Cleveland State Bank)
                // but accessed a user route on the default Frontfield domain, auto-redirect to their own portal.
                if ($institution->is_default && $user->institution && ! $user->institution->is_default) {
                    if ($request->is('user*') || $request->is('dashboard*') || $request->is('login')) {
                        $instSlug = $user->institution->slug;
                        $rootHost = parse_url(config('app.url'), PHP_URL_HOST) ?: 'frontfieldcu.pro';
                        $targetHost = $instSlug . '.' . $rootHost;
                        $scheme = $request->getScheme();
                        $pathAndQuery = $request->getRequestUri();
                        return redirect()->away("{$scheme}://{$targetHost}{$pathAndQuery}");
                    }
                }

                // If visitor is on a non-default institution subdomain that does NOT match their institution,
                // terminate the session immediately to guarantee zero cross-tenant leakage.
                if (! $institution->is_default && (int) $user->institution_id !== (int) $institution->id) {
                    auth('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            }
        }

        return $next($request);
    }
}

