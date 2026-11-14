<?php

namespace App\Providers;

use App\Traits\NotifyTrait;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

class AuthServiceProvider extends ServiceProvider
{
    use NotifyTrait;

    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization modules.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Implicitly grant "Super Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super-Admin') ? true : null;
        });

        VerifyEmail::toMailUsing(function ($notifiable) {
            $institution = ($notifiable instanceof \App\Models\User && $notifiable->institution_id)
                ? $notifiable->institution
                : ((app()->bound('institution')) ? app('institution') : \App\Models\Institution::getDefault());

            $isCustom = $institution && !$institution->is_default;

            $verifyUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            if ($isCustom) {
                $parsed = parse_url($verifyUrl);
                $targetHost = "{$institution->slug}.frontfieldcu.pro";
                $verifyUrl = "https://{$targetHost}" . ($parsed['path'] ?? '') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
            }

            $siteTitle = $isCustom ? $institution->name : (setting('site_title', 'global') ?: 'FrontField Credit Union');
            $siteUrl = $isCustom ? "https://{$institution->slug}.frontfieldcu.pro" : route('home');

            $shortcodes = [
                '[[token]]' => $verifyUrl,
                '[[full_name]]' => $notifiable->full_name,
                '[[site_title]]' => $siteTitle,
                '[[site_url]]' => $siteUrl,
            ];

            return $this->mailNotify($notifiable->email, 'email_verification', $shortcodes);
        });

    }
}
