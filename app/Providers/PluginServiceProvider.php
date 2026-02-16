<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Remotelywork\Installer\Repository\App;
use Schema;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        if (App::dbConnectionCheck() && Schema::hasTable('plugins')) {

            // Nexmo sms plugin
            if (plugin_active('Nexmo')) {
                $nexmoCredential = json_decode(plugin_active('Nexmo')->data);
                config()->set([
                    'sms.connections.nexmo.nexmo_from' => $nexmoCredential->from,
                    'sms.connections.nexmo.api_key' => $nexmoCredential->api_key,
                    'sms.connections.nexmo.api_secret' => $nexmoCredential->api_secret,
                ]);
            }

            // Twilio sms plugin
            if (plugin_active('Twilio')) {
                $twilioCredential = json_decode(plugin_active('Twilio')->data);
                config()->set([
                    'sms.connections.twilio.twilio_sid' => $twilioCredential->twilio_sid,
                    'sms.connections.twilio.twilio_auth_token' => $twilioCredential->twilio_auth_token,
                    'sms.connections.twilio.twilio_phone' => $twilioCredential->twilio_phone,
                ]);
            }

            // Pusher Notification plugin
            if (plugin_active('Pusher')) {
                $push_notification = plugin_active('Pusher');
                if ($push_notification->name == 'Pusher') {
                    $pusherCredential = json_decode($push_notification->data);
                    config()->set([
                        'broadcasting.connections.pusher.app_id' => $pusherCredential->pusher_app_id,
                        'broadcasting.connections.pusher.key' => $pusherCredential->pusher_app_key,
                        'broadcasting.connections.pusher.secret' => $pusherCredential->pusher_app_secret,
                        'broadcasting.connections.pusher.options.cluster' => $pusherCredential->pusher_app_cluster,
                    ]);
                }
            }



            // Default plugin
            config()->set('sms.default', default_plugin('sms') ?? false);

        }

    }
}
