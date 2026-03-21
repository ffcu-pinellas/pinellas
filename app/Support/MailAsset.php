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

        // asset() respects config('app.url') and optional ASSET_URL (CDN) — critical for email img src.
        if (function_exists('asset')) {
            return asset($relative);
        }

        return rtrim((string) config('app.url'), '/').'/'.$relative;
    }
}
