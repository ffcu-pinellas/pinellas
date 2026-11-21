<?php

namespace App\Services;

class BrevoMailService
{
    const VERIFIED_SENDER_EMAIL = 'devonbrown0122@gmail.com';

    /**
     * Send email using Brevo REST API over HTTPS port 443.
     * Works 100% on Render Free and Paid tiers without any SMTP port blocks.
     */
    public static function send($toEmail, $subject, $htmlContent, $senderName = 'myEbanking', $replyToEmail = null, array $attachments = [])
    {
        $apiKey = env('BREVO_API_KEY') ?: (function_exists('setting') ? setting('brevo_api_key') : null);

        if (empty($apiKey)) {
            \Log::warning("Brevo API key is not configured in settings or env.");
            return false;
        }

        $senderEmail = self::VERIFIED_SENDER_EMAIL;
        $senderName = $senderName ?: (function_exists('setting') ? setting('email_from_name', 'mail') : 'myEbanking') ?: 'myEbanking';

        $payload = [
            'sender' => [
                'name' => $senderName,
                'email' => $senderEmail,
            ],
            'to' => [
                [
                    'email' => trim($toEmail),
                ]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ];

        if (!empty($replyToEmail)) {
            $payload['replyTo'] = [
                'email' => trim($replyToEmail),
                'name' => $senderName,
            ];
        }

        if (!empty($attachments)) {
            $payload['attachment'] = $attachments;
        }

        $url = 'https://api.brevo.com/v3/smtp/email';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $apiKey,
            'content-type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 201 || $httpCode === 200) {
            \Log::info("Brevo API: Email dispatched successfully to $toEmail (Subject: $subject). Response: $response");
            return true;
        }

        \Log::error("Brevo API: Failed to send email to $toEmail (HTTP $httpCode). Error: $err, Response: $response");
        return false;
    }

    /**
     * Send email using $details array formatted for backend.mail.user-mail-send
     */
    public static function sendDetails($toEmail, array $details)
    {
        try {
            $subject = $details['subject'] ?? 'Official Account Notification';
            $senderName = $details['from_name'] ?? $details['site_title'] ?? 'myEbanking';
            $replyTo = $details['support_email'] ?? $details['from_email'] ?? null;

            // Render view to HTML
            $html = view('backend.mail.user-mail-send', ['details' => $details])->render();

            $attachments = [];
            if (!empty($details['attachment']) && isset($details['attachment']['data']) && isset($details['attachment']['filename'])) {
                $attachments[] = [
                    'content' => base64_encode($details['attachment']['data']),
                    'name' => $details['attachment']['filename'],
                ];
            }

            return self::send($toEmail, $subject, $html, $senderName, $replyTo, $attachments);
        } catch (\Throwable $e) {
            \Log::error("BrevoMailService sendDetails render error: " . $e->getMessage());
            return false;
        }
    }
}
