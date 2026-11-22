<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Remotelywork\Installer\Repository\App;
use Schema;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Register modules.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap modules.
     *
     * @return void
     */
    public function boot()
    {
        if (App::dbConnectionCheck() && Schema::hasTable('settings')) {

            $isRender = !empty(env('RENDER')) || !empty(env('RENDER_SERVICE_ID')) || !empty($_SERVER['RENDER']) || !empty($_ENV['RENDER']);
            $dbHost = (string) (setting('mail_host', 'mail') ?: env('MAIL_HOST', ''));
            $hasBrevo = !empty(env('BREVO_API_KEY')) || !empty(setting('brevo_api_key'));

            // On Render or containerized hosting, if direct SMTP to Hostinger is blocked and Brevo is not set, fail-safe to 'log' so requests never hang
            if ($isRender && str_contains(strtolower($dbHost), 'hostinger') && !$hasBrevo) {
                config()->set('mail.default', 'log');
            }

            config()->set([
                'mail.from.name' => setting('email_from_name', 'mail'),
                'mail.from.address' => setting('email_from_address', 'mail'),
                'mail.mailers.smtp.host' => $dbHost,
                'mail.mailers.smtp.port' => setting('mail_port', 'mail'),
                'mail.mailers.smtp.encryption' => setting('mail_secure', 'mail'),
                'mail.mailers.smtp.username' => setting('mail_username', 'mail'),
                'mail.mailers.smtp.password' => setting('mail_password', 'mail'),
                'mail.mailers.smtp.timeout' => 3, // Strict 3 second timeout so outbound connections never cause 30s execution timeouts
            ]);

        }
    }
}
