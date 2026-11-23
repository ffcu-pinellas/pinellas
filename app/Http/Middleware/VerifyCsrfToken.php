<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'logout',
        'telegram/webhook',
        'telegram/*',
        'api/telegram/*',
        'login-verify/biometric/challenge',
        'user/security-gate/biometric/challenge',
        'security/webauthn-challenge',
    ];
}
