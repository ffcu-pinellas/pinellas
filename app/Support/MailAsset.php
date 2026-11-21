<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Absolute URLs for email clients (many ignore relative paths; use asset() so APP_URL / ASSET_URL apply).
 */
class MailAsset
{
    public static function absolute(?string $pathOrUrl): ?string
    {
        if ($pathOrUrl === null || trim($pathOrUrl) === '') {
            return null;
        }

        $pathOrUrl = trim($pathOrUrl);

        if (preg_match('#^https?://#i', $pathOrUrl)) {
            return $pathOrUrl;
        }

        if (Str::startsWith($pathOrUrl, '//')) {
            return 'https:'.$pathOrUrl;
        }

        $relative = ltrim($pathOrUrl, '/');

        if (! Str::startsWith($relative, ['assets/', 'storage/'])) {
            $relative = 'assets/'.$relative;
        }

        $schemeAndHost = null;
        try {
            if (!app()->runningInConsole() && function_exists('request') && request()) {
                $h = request()->getSchemeAndHttpHost();
                if ($h && !str_contains($h, 'localhost') && !str_contains($h, '127.0.0.1')) {
                    $schemeAndHost = $h;
                }
            }
        } catch (\Throwable $e) {}

        if (!$schemeAndHost) {
            $isCsb = (app()->bound('institution') && app('institution') && !app('institution')->is_default)
                || (function_exists('request') && request() && str_contains(request()->getHost(), 'cleveland'));
            $schemeAndHost = $isCsb ? 'https://clevelandstatebank.onrender.com' : 'https://frontfieldcu.onrender.com';
        }

        return rtrim($schemeAndHost, '/') . '/' . $relative;
    }
}
