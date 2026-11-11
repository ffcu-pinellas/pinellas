<?php

namespace App\Services;

use App\Models\ActiveSession;
use App\Models\Admin;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SessionRadarService
{
    public function __construct()
    {
        ActiveSession::ensureSchema();
    }

    /**
     * Record or heartbeat an active session.
     */
    public function recordActivity(Request $request, string $guard, object $actor, ?string $sessionId = null, ?int $tokenId = null): void
    {
        $sessionKey = $guard === 'sanctum'
            ? 'sanctum:' . ($tokenId ?? $actor->id)
            : $guard . ':' . $sessionId;

        $ip = (string) ($request->ip() ?? '');
        $cacheKey = 'sess_ping_' . md5($sessionKey . '|' . $ip);

        // If already pinged within 25 seconds from same IP, skip extra write
        if (Cache::has($cacheKey)) {
            return;
        }

        try {
            $ua = substr((string) $request->userAgent(), 0, 490);
            $device = $this->parseDevice($ua);
            $city = $this->resolveCity($ip);

            if ($guard === 'admin') {
                $role = method_exists($actor, 'getRoleNames') && $actor->getRoleNames()->isNotEmpty()
                    ? $actor->getRoleNames()->first()
                    : 'Staff';
                $displayName = $actor->name ?? 'Admin #' . $actor->id;
                $username = $actor->email ?? ('admin#' . $actor->id);
            } else {
                $role = $guard === 'sanctum' ? 'Member (Mobile App)' : 'Member';
                $displayName = trim(($actor->first_name ?? '') . ' ' . ($actor->last_name ?? '')) ?: ($actor->username ?? 'Member #' . $actor->id);
                $username = $actor->username ?? ('user#' . $actor->id);
            }

            $existing = ActiveSession::where('session_key', $sessionKey)->first();
            if ($existing && $existing->is_kicked) {
                return;
            }

            ActiveSession::updateOrCreate(
                ['session_key' => $sessionKey],
                [
                    'session_id' => $sessionId,
                    'token_id' => $tokenId,
                    'guard' => $guard,
                    'authenticatable_id' => $actor->id,
                    'display_name' => substr($displayName, 0, 145),
                    'username' => substr($username, 0, 95),
                    'role_label' => substr($role, 0, 58),
                    'ip_address' => substr($ip, 0, 60),
                    'city' => $city,
                    'user_agent' => $ua,
                    'device_summary' => $device,
                    'current_url' => substr('/' . ltrim($request->path(), '/'), 0, 250),
                    'is_kicked' => false,
                    'last_activity_at' => now(),
                ]
            );

            Cache::put($cacheKey, true, now()->addSeconds(25));
        } catch (\Throwable $e) {
            Log::warning('SessionRadar recordActivity warning: ' . $e->getMessage());
        }
    }

    /**
     * Check whether a given web or admin session has been terminated.
     */
    public function isSessionKicked(string $guard, string $sessionId): bool
    {
        try {
            $sessionKey = $guard . ':' . $sessionId;
            return ActiveSession::where('session_key', $sessionKey)
                ->where('is_kicked', true)
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get live active sessions within recent window (default 30 minutes).
     */
    public function getActiveSessions(int $minutes = 30)
    {
        $cutoff = Carbon::now()->subMinutes($minutes);

        return ActiveSession::where('is_kicked', false)
            ->where('last_activity_at', '>=', $cutoff)
            ->orderByDesc('last_activity_at')
            ->get();
    }

    /**
     * Force-disconnect all web & mobile Sanctum sessions for a member.
     */
    public function kickMember(User $user): array
    {
        $sessions = ActiveSession::whereIn('guard', ['web', 'sanctum'])
            ->where('authenticatable_id', $user->id)
            ->where('is_kicked', false)
            ->get();

        $terminatedCount = 0;
        foreach ($sessions as $sess) {
            $this->terminateSessionRecord($sess);
            $terminatedCount++;
        }

        // Revoke all Sanctum API/Capacitor mobile tokens regardless of tracked rows
        $revokedTokens = 0;
        try {
            if (method_exists($user, 'tokens')) {
                $revokedTokens = $user->tokens()->count();
                $user->tokens()->delete();
            }
        } catch (\Throwable $e) {
            Log::warning('Sanctum token revoke warning: ' . $e->getMessage());
        }

        // Also clear Laravel DB sessions table if configured
        $this->purgeLaravelSessionsForUserId($user->id);

        return [
            'terminated_sessions' => $terminatedCount,
            'revoked_tokens' => $revokedTokens,
        ];
    }

    /**
     * Force-disconnect all web sessions for an Admin / Account Officer.
     */
    public function kickAdmin(Admin $admin): array
    {
        $sessions = ActiveSession::where('guard', 'admin')
            ->where('authenticatable_id', $admin->id)
            ->where('is_kicked', false)
            ->get();

        $terminatedCount = 0;
        foreach ($sessions as $sess) {
            $this->terminateSessionRecord($sess);
            $terminatedCount++;
        }

        return [
            'terminated_sessions' => $terminatedCount,
            'revoked_tokens' => 0,
        ];
    }

    /**
     * Terminate a single tracked session row by ID.
     */
    public function kickSessionById(int $activeSessionId): ?ActiveSession
    {
        $sess = ActiveSession::find($activeSessionId);
        if (!$sess) {
            return null;
        }

        $this->terminateSessionRecord($sess);

        if (in_array($sess->guard, ['web', 'sanctum'], true) && $sess->authenticatable_id) {
            $user = User::find($sess->authenticatable_id);
            if ($user) {
                $user->tokens()->delete();
            }
        }

        return $sess;
    }

    protected function terminateSessionRecord(ActiveSession $sess): void
    {
        $sess->is_kicked = true;
        $sess->save();

        Cache::forget('sess_ping_' . md5($sess->session_key . '|' . ($sess->ip_address ?? '')));

        if (!empty($sess->session_id)) {
            $filePath = storage_path('framework/sessions/' . $sess->session_id);
            if (is_file($filePath)) {
                @unlink($filePath);
            }

            if (config('session.driver') === 'database') {
                try {
                    DB::table(config('session.table', 'sessions'))
                        ->where('id', $sess->session_id)
                        ->delete();
                } catch (\Throwable $e) {
                }
            }
        }

        if ($sess->token_id && Schema::hasTable('personal_access_tokens')) {
            try {
                DB::table('personal_access_tokens')->where('id', $sess->token_id)->delete();
            } catch (\Throwable $e) {
            }
        }
    }

    protected function purgeLaravelSessionsForUserId(int $userId): void
    {
        if (config('session.driver') === 'database') {
            try {
                DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $userId)
                    ->delete();
            } catch (\Throwable $e) {
            }
        }
    }

    /**
     * Automatically whitelist Super-Admin IP for maintenance mode bypass.
     */
    public function rememberSuperAdminIp(?string $ip): void
    {
        if (!$ip) {
            return;
        }

        $cacheKey = 'sa_ip_saved_' . md5($ip);
        if (Cache::has($cacheKey)) {
            return;
        }

        try {
            $raw = Setting::get('maintenance_superadmin_ips', null, '[]');
            $ips = is_string($raw) ? json_decode($raw, true) : $raw;
            if (!is_array($ips)) {
                $ips = [];
            }

            if (!in_array($ip, $ips, true)) {
                array_unshift($ips, $ip);
                $ips = array_slice(array_values(array_unique($ips)), 0, 12);
                Setting::add('maintenance_superadmin_ips', json_encode($ips), 'string');
            }

            Cache::put($cacheKey, true, now()->addMinutes(15));
        } catch (\Throwable $e) {
        }
    }

    public function parseDevice(string $ua): string
    {
        if (stripos($ua, 'Capacitor') !== false || stripos($ua, 'FrontField') !== false) {
            return 'FrontField Mobile App';
        }

        $os = 'Unknown OS';
        if (preg_match('/Windows NT/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/iPhone|iPad|iOS/i', $ua)) {
            $os = 'iOS';
        } elseif (preg_match('/Android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/Macintosh|Mac OS X/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $ua)) {
            $os = 'Linux';
        }

        $browser = 'Browser';
        if (preg_match('/Edg\//i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/Chrome\//i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\//i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\//i', $ua) && !preg_match('/Chrome\//i', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/curl|Postman/i', $ua)) {
            $browser = 'API Client';
        }

        return "{$browser} / {$os}";
    }

    public function resolveCity(string $ip): ?string
    {
        if (
            !$ip
            || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
        ) {
            return 'Local / Private Network';
        }

        return Cache::remember('ip_city_' . md5($ip), now()->addHours(12), function () use ($ip) {
            try {
                $resp = Http::timeout(1.5)->acceptJson()->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,city,region,countryCode',
                ]);
                if ($resp->successful() && $resp->json('status') === 'success') {
                    $parts = array_filter([
                        $resp->json('city'),
                        $resp->json('region'),
                        $resp->json('countryCode'),
                    ]);
                    if (!empty($parts)) {
                        return substr(implode(', ', $parts), 0, 115);
                    }
                }
            } catch (\Throwable $e) {
            }

            return null;
        });
    }
}
