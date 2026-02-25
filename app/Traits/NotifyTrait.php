<?php

namespace App\Traits;

use App\Events\NotificationEvent;
use App\Mail\MailSend;
use App\Models\EmailTemplate;
use App\Models\Notification;
use App\Models\PushNotificationTemplate;
use App\Models\SmsTemplate;
use Exception;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

trait NotifyTrait
{
    use SmsTrait, TelegramTrait;

    // ============================= mail template helper ===================================================
    protected function mailNotify($email, $code, $shortcodes = null)
    {

        try {
            $template = EmailTemplate::where('status', true)->where('code', $code)->first();
            if ($template) {
                $find = array_keys($shortcodes);
                $replace = array_values($shortcodes);

                // Add Standard Shortcodes if not present
                if (!in_array('[[status]]', $find)) {
                    $find[] = '[[status]]';
                    $replace[] = $shortcodes['status'] ?? '';
                }
                if (!in_array('[[site_title]]', $find)) {
                    $find[] = '[[site_title]]';
                    $replace[] = setting('site_title', 'global');
                }
                if (!in_array('[[site_url]]', $find)) {
                    $find[] = '[[site_url]]';
                    $replace[] = route('home');
                }

                $siteLogo = setting('site_logo', 'global');
                if ($siteLogo && !Str::startsWith($siteLogo, 'assets/')) {
                    $siteLogo = 'assets/' . $siteLogo;
                }

                $banner = $template->banner;
                if ($banner && !Str::startsWith($banner, 'assets/')) {
                    $banner = 'assets/' . $banner;
                }

                $details = [
                    'subject' => str_replace($find, $replace, $template->subject),
                    'banner' => $banner ? asset($banner) : null,
                    'title' => str_replace($find, $replace, $template->title),
                    'salutation' => str_replace($find, $replace, $template->salutation),
                    'message_body' => str_replace($find, $replace, $template->message_body),
                    'button_level' => $template->button_level,
                    'button_link' => str_replace($find, $replace, $template->button_link),
                    'footer_status' => $template->footer_status,
                    'footer_body' => str_replace($find, $replace, $template->footer_body),
                    'bottom_status' => $template->bottom_status,
                    'bottom_title' => str_replace($find, $replace, $template->bottom_title) ?? '',
                    'bottom_body' => str_replace($find, $replace, $template->bottom_body) ?? '',

                    'site_logo' => $siteLogo ? asset($siteLogo) : null,
                    'site_title' => setting('site_title', 'global'),
                    'site_link' => route('home'),
                ];

                if ($code == 'email_verification') {
                return (new MailMessage)
                    ->subject($details['subject'])
                    ->markdown('backend.mail.user-mail-send', ['details' => $details]);
            }

            try {
                return Mail::to($email)->send(new MailSend($details));
            } catch (Exception $e) {
                \Log::error("Mail sending failed for $email: " . $e->getMessage());
                notify()->error('SMTP connection failed. Please check your Mail Configuration in .env', 'Error');
                return false;
            }
        } else {
            \Log::warning("Email template with code '$code' not found in database.");
            
            // Standard Fallback for ANY missing template to prevent failure
            $siteLogo = setting('site_logo', 'global');
            if ($siteLogo && !Str::startsWith($siteLogo, 'assets/')) {
                $siteLogo = 'assets/' . $siteLogo;
            }

            // Prioritize manual inputs from the Admin Modal (Subject and Email Details)
            $manualSubject = $shortcodes['[[subject]]'] ?? null;
            $manualMessage = $shortcodes['[[message]]'] ?? null;

            $details = [
                'subject' => $manualSubject ?: 'Account Notification - ' . setting('site_title', 'global'),
                'message_body' => $manualMessage ?: ($shortcodes['[[action]]'] ?? 'A notification has been triggered for your account.'),
                'site_logo' => $siteLogo ? asset($siteLogo) : null,
                'site_link' => route('home'),
                'site_title' => setting('site_title', 'global'),
                'title' => ($manualSubject && $manualMessage) ? 'Official Notification' : 'Account Alert',
                'salutation' => 'Hello ' . ($shortcodes['[[full_name]]'] ?? 'Member'),
                'footer_body' => 'Pinellas Federal Credit Union',
                'footer_status' => 1,
                'banner' => null,
                'button_level' => null,
                'button_link' => null,
                'bottom_status' => 0,
                'bottom_title' => '',
                'bottom_body' => '',
            ];

            try {
                return Mail::to($email)->send(new MailSend($details));
            } catch (Exception $e) {
                \Log::error("Mail fallback sending failed for $email: " . $e->getMessage());
                return false;
            }
        }
    } catch (Exception $e) {
        \Log::error("NotifyTrait mailNotify error: " . $e->getMessage());
        notify()->error('Error preparing email: ' . $e->getMessage(), 'Error');

        return false;
    }
}

    // ============================= push notification template helper ===================================================
    protected function pushNotify($code, $shortcodes, $action, $userId, $for = 'User')
    {
        try {
            $template = PushNotificationTemplate::where('status', true)->where('for', ucfirst($for))->where('code', $code)->first();

            if ($template) {
                $user = \App\Models\User::find($userId);
                $find = array_keys($shortcodes);
                $replace = array_values($shortcodes);
                $data = [
                    'icon' => $template->icon,
                    'user_id' => $userId,
                    'for' => Str::snake($template->for),
                    'title' => str_replace($find, $replace, $template->title),
                    'notice' => strip_tags(str_replace($find, $replace, $template->message_body)),
                    'action_url' => $action,
                ];

                Notification::create($data);

                // Internal Socket Event (Pusher)
                try {
                    $pusher_credentials = config('broadcasting.connections.pusher');
                    if ($pusher_credentials && $pusher_credentials['key']) {
                        $uID = $template->for == 'Admin' ? '' : $userId;
                        event(new NotificationEvent($template->for, $data, $uID));
                    }
                } catch (Exception $e) {
                    \Log::warning("Pusher broadcast failed (Code 404 usually means cluster mismatch): " . $e->getMessage());
                }

                // Native Push via FCM
                if ($for === 'User' && $user && $user->fcm_token) {
                    $this->sendFcmPush($user->fcm_token, $data['title'], $data['notice'], $action);
                } elseif ($for === 'Admin') {
                    // Send to specific admin if userId is provided, else potentially broadcast to all admins with tokens
                    if ($userId) {
                        $admin = \App\Models\Admin::find($userId);
                        if ($admin && $admin->fcm_token) {
                            $this->sendFcmPush($admin->fcm_token, $data['title'], $data['notice'], $action);
                        }
                    } else {
                        // Optional: Broadcast to all admins who have a token
                        $adminsWithTokens = \App\Models\Admin::whereNotNull('fcm_token')->get();
                        foreach ($adminsWithTokens as $admin) {
                            $this->sendFcmPush($admin->fcm_token, $data['title'], $data['notice'], $action);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            \Log::error("Push Notification Error: " . $e->getMessage());
        }
    }

    /**
     * Send Native FCM Push (V1)
     */
    protected function sendFcmPush($token, $title, $body, $action = null)
    {
        \Log::info("FCM: Attempting to send push to token: " . substr($token, 0, 10) . "...");
        $accessToken = $this->getFcmAccessToken();
        if (!$accessToken) {
            \Log::error("FCM: Failed to get access token");
            return;
        }

        $path = base_path('fcm_config/fcm_service_account.json');
        if (!file_exists($path)) {
            \Log::error("FCM: Service Account JSON missing at base path: $path");
            return;
        }
        $config = json_decode(file_get_contents($path), true);
        $projectId = $config['project_id'];

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $fields = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => [
                    'action_url' => (string)$action,
                    'click_action' => 'FCM_PLUGIN_ACTIVITY',
                ],
                'android' => [
                   'notification' => [
                       'click_action' => 'FCM_PLUGIN_ACTIVITY',
                       'sound' => 'default'
                   ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default'
                        ]
                    ]
                ]
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        \Log::info("FCM V1 Send Result (HTTP $httpCode): " . $result);
    }

    /**
     * Generate OAuth 2.0 Access Token for FCM V1
     */
    protected function getFcmAccessToken()
    {
        $path = base_path('fcm_config/fcm_service_account.json');
        if (!file_exists($path)) {
            \Log::error("FCM Service Account JSON not found at BASE: $path");
            return null;
        }

        $config = json_decode(file_get_contents($path), true);
        if (!$config || !isset($config['private_key']) || !isset($config['client_email'])) {
            \Log::error("FCM: Invalid service account JSON structure.");
            return null;
        }

        $now = time();
        
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $payload = [
            'iss' => $config['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        // CRITICAL: Google's OAuth server requires slashes NOT to be escaped within the JWT payload.
        // Standard json_encode turns '/' into '\/', which invalidates the signature.
        $base64UrlHeader = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));

        // Standard Private Key Normalization
        $privateKey = $config['private_key'];
        $privateKey = str_replace('\n', "\n", $privateKey);
        $privateKey = trim($privateKey);

        $signature = '';
        if (!openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            \Log::error("FCM JWT Signing failed. Verify that openssl is enabled and the private_key is an uncorrupted RS256 key.");
            return null;
        }
        $base64UrlSignature = $this->base64UrlEncode($signature);

        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]));
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $resData = json_decode($result, true);
        if (isset($resData['access_token'])) {
            return $resData['access_token'];
        }

        \Log::error("FCM: OAuth Token Exchange Failed (HTTP $httpCode): " . $result . " | ISS: " . $config['client_email']);
        return null;
    }

    protected function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    // ============================= sms notification template helper ===================================================
    protected function smsNotify($code, $shortcodes, $phone)
    {

        if (! config('sms.default') && ! $phone) {
            return false;
        }

        try {
            $template = SmsTemplate::where('status', true)->where('code', $code)->first();
            if ($template) {
                $find = array_keys($shortcodes);
                $replace = array_values($shortcodes);

                $message = [
                    'message_body' => str_replace($find, $replace, $template->message_body),
                ];
                self::sendSms($phone, $message);
            }

        } catch (Exception $e) {
            return false;
        }

    }
}
