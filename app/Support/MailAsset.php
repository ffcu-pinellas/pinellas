<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Absolute URLs for email clients (many ignore relative paths and broken APP_URL breaks asset()).
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

        return rtrim((string) config('app.url'), '/').'/'.$relative;
    }
}
