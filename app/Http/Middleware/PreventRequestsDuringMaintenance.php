<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;
use Illuminate\Support\Facades\Schema;
use Remotelywork\Installer\Repository\App;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
        'admin*',
        'api/telegram/*',
        'telegram/*',
        'deploy/*',
    ];

    /**
     * Handle an incoming request with Super Admin IP bypass support.
     */
    public function handle($request, Closure $next)
    {
        if ($this->app->isDownForMaintenance()) {
            if ($this->inExceptArray($request)) {
                return $next($request);
            }

            if ($this->isSuperAdminIpWhitelisted($request->ip())) {
                return $next($request);
            }
        }

        return parent::handle($request, $next);
    }

    /**
     * Check if the incoming IP belongs to a whitelisted Super Admin workstation.
     */
    protected function isSuperAdminIpWhitelisted(?string $ip): bool
    {
        if (!$ip) {
            return false;
        }

        try {
            if (!App::dbConnectionCheck() || !Schema::hasTable('settings')) {
                return false;
            }

            $raw = Setting::get('maintenance_superadmin_ips', null, '[]');
            $ips = is_string($raw) ? json_decode($raw, true) : $raw;

            return is_array($ips) && in_array($ip, $ips, true);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
