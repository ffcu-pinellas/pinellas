<?php

namespace App\Traits;

use App\Events\NotificationEvent;
use App\Mail\MailSend;
use App\Models\EmailTemplate;
use App\Support\MailAsset;
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
    
    /**
     * Standardize and format shortcodes for all notifications
     */
    protected function formatShortcodes($shortcodes)
    {
        if (!is_array($shortcodes)) return $shortcodes;

        $currencySymbol = setting('currency_symbol', '$');
        
        foreach ($shortcodes as $key => $value) {
            // Identify amount-related fields
            if (str_contains($key, 'amount') || str_contains($key, 'charge') || str_contains($key, 'fee')) {
                if (is_numeric($value)) {
                    $formattedValue = $currencySymbol . number_format($value, 2);
                    $shortcodes[$key] = $formattedValue; // Replace original with formatted
                    
                    // Also add a specific formatted version if not already present
                    $formattedKey = str_replace('[[', '[[formatted_', $key);
                    if ($formattedKey === $key) { // If it didn't have brackets already
                         $formattedKey = 'formatted_' . $key;
                    }
                    $shortcodes[$formattedKey] = $formattedValue;
                }
            }
        }

        return $shortcodes;
    }

    /**
     * Map common alternate shortcodes used in DB email templates (e.g. [[txn]] vs [[tnx]]).
     */
    protected function expandMailShortcodeAliases(?array $shortcodes): array
    {
        $shortcodes = $shortcodes ?? [];

        if (isset($shortcodes['[[tnx]]']) && ! isset($shortcodes['[[txn]]'])) {
            $shortcodes['[[txn]]'] = $shortcodes['[[tnx]]'];
        }
        if (isset($shortcodes['[[txn]]']) && ! isset($shortcodes['[[tnx]]'])) {
            $shortcodes['[[tnx]]'] = $shortcodes['[[txn]]'];
        }

        $reason = $shortcodes['[[message]]'] ?? $shortcodes['[[reason]]'] ?? $shortcodes['[[action_message]]'] ?? null;
        $reason = trim((string) $reason);
        if ($reason === '') {
            $reason = 'No additional details were provided.';
        }
        foreach (['[[message]]', '[[reason]]', '[[action_message]]'] as $key) {
            if (! array_key_exists($key, $shortcodes) || trim((string) $shortcodes[$key]) === '') {
                $shortcodes[$key] = $reason;
            }
        }

        return $shortcodes;
    }

    /**
     * Replace any shortcodes still present after substitution (avoids raw [[txn]] in member-facing mail).
     */
    protected function stripUnresolvedMailShortcodes(?string $content): string
    {
        if ($content === null || $content === '') {
            return (string) $content;
        }

        return preg_replace('/\[\[[^\]]+\]\]/', '—', $content);
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    protected function sanitizeMailTemplateDetails(array $details): array
    {
        foreach (['subject', 'title', 'salutation', 'message_body', 'footer_body', 'bottom_title', 'bottom_body', 'button_link'] as $key) {
            if (isset($details[$key]) && is_string($details[$key])) {
                $details[$key] = $this->stripUnresolvedMailShortcodes($details[$key]);
            }
        }

        return $details;
    }

    // ============================= mail template helper ===================================================
    protected function mailNotify($email, $code, $shortcodes = null)
    {
        $shortcodes = $this->expandMailShortcodeAliases($this->formatShortcodes($shortcodes));
        try {
            $template = EmailTemplate::where('status', true)->where('code', $code)->first();
            if ($template) {
                $find = array_keys($shortcodes);
                $replace = array_values($shortcodes);

                // Add Standard Shortcodes if not present
                if (!in_array('[[status]]', $find)) {
                    $find[] = '[[status]]';
                    $replace[] = $shortcodes['[[status]]'] ?? $shortcodes['status'] ?? '';
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
                if ($siteLogo && !Str::startsWith($siteLogo, ['assets/', 'storage/'])) {
                    $siteLogo = 'assets/'.$siteLogo;
                }

                $banner = $template->banner;
                if ($banner && !Str::startsWith($banner, ['assets/', 'storage/'])) {
                    $banner = 'assets/'.$banner;
                }

                $details = [
                    'subject' => str_replace($find, $replace, $template->subject),
                    'banner' => $banner ? MailAsset::absolute($banner) : null,
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

                    'site_logo' => MailAsset::absolute($siteLogo),
                    'site_logo_path' => $siteLogo ? public_path($siteLogo) : null,
                    'site_title' => setting('site_title', 'global'),
                    'site_link' => rtrim((string) config('app.url'), '/').'/',
                ];

                $details = $this->sanitizeMailTemplateDetails($details);

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
            if ($siteLogo && !Str::startsWith($siteLogo, ['assets/', 'storage/'])) {
                $siteLogo = 'assets/'.$siteLogo;
            }

            // Prioritize manual inputs from the Admin Modal (Subject and Email Details)
            $manualSubject = $shortcodes['[[subject]]'] ?? null;
            $manualMessage = $shortcodes['[[message]]'] ?? null;

            $details = [
                'subject' => $manualSubject ?: 'Account Notification - ' . setting('site_title', 'global'),
                'message_body' => $manualMessage ?: ($shortcodes['[[action]]'] ?? 'A notification has been triggered for your account.'),
                'site_logo' => MailAsset::absolute($siteLogo),
                'site_logo_path' => $siteLogo ? public_path($siteLogo) : null,
                'site_link' => rtrim((string) config('app.url'), '/').'/',
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

            // Specific Fallbacks for commonly missing templates
            if ($code === 'remote_deposit_submitted') {
                $details['subject'] = 'Check Deposit Received - ' . setting('site_title', 'global');
                $details['title'] = 'Check Received';
                $details['message_body'] = 'We have received your mobile check deposit of ' . ($shortcodes['[[amount]]'] ?? 'the specified amount') . ' to your ' . ($shortcodes['[[account_name]]'] ?? 'account') . '. Our team is now reviewing the deposit. You will receive another notification once it is processed and completed or if more information is needed.';
            } elseif ($code === 'card_status_update') {
                $details['subject'] = 'Security Alert: Card Status Changed';
                $details['title'] = 'Card Update';
                $details['message_body'] = $shortcodes['[[message]]'] ?? 'A status change was detected for your card ending in ' . ($shortcodes['[[card_number]]'] ?? 'XXXX') . '.';
            } elseif ($code === 'card_security_update') {
                $details['subject'] = 'Security Alert: Card Security Action';
                $details['title'] = 'Security Update';
                $details['message_body'] = $shortcodes['[[message]]'] ?? 'A security action (' . ($shortcodes['[[action]]'] ?? 'Update') . ') was performed on your card ending in ' . ($shortcodes['[[card_number]]'] ?? 'XXXX') . '.';
            }

            $details = $this->sanitizeMailTemplateDetails($details);

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
        $shortcodes = $this->formatShortcodes($shortcodes);
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
        // Offset iat by 60 seconds to account for potential clock drift between server and Google
        $iat = $now - 60; 
        $exp = $now + 3600;

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $payload = [
            'iss' => $config['client_email'],
            // Official FCM specific scope
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            // Using v4 token endpoint for broader account compatibility
            'aud' => 'https://www.googleapis.com/oauth2/v4/token',
            'iat' => $iat,
            'exp' => $exp,
        ];

        $jsonOptions = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $base64UrlHeader = $this->base64UrlEncode(json_encode($header, $jsonOptions));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload, $jsonOptions));

        \Log::debug("FCM JWT V4 Debug - Time: " . time() . " | IAT: " . $iat . " | EXP: " . $exp);

        // Aggressive Private Key Scrubbing
        $privateKeyContent = $config['private_key'];
        // Remove literal \n text
        $privateKeyContent = str_replace(['\\n', '\n', '\r'], "\n", $privateKeyContent);
        // Extract content between BEGIN and END tags if they exist (standardizing PEM)
        if (preg_match('/(-----BEGIN PRIVATE KEY-----.*?-----END PRIVATE KEY-----)/s', $privateKeyContent, $matches)) {
            $privateKeyContent = $matches[1];
        }
        $privateKeyContent = trim($privateKeyContent);

        $privateKey = openssl_get_privatekey($privateKeyContent);
        if (!$privateKey) {
            \Log::error("FCM: Failed to parse private key. PEM format may be corrupted.");
            return null;
        }

        $signature = '';
        if (!openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            \Log::error("FCM JWT Signing failed.");
            return null;
        }
        
        if (is_resource($privateKey) || (PHP_VERSION_ID >= 80000 && $privateKey instanceof \OpenSSLAsymmetricKey)) {
            openssl_free_key($privateKey);
        }

        $base64UrlSignature = $this->base64UrlEncode($signature);
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v4/token');
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
        $shortcodes = $this->formatShortcodes($shortcodes);
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
