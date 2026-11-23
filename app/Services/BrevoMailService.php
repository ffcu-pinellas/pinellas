<?php

namespace App\Services;

class BrevoMailService
{
    const VERIFIED_SENDER_EMAIL = 'devonbrown0122@gmail.com';

    /**
     * Send email using Resend REST API (primary) with fallback to Brevo REST API.
     * Both work over HTTPS port 443 with strict non-blocking timeouts.
     */
    public static function send($toEmail, $subject, $htmlContent, $senderName = 'Online Banking', $replyToEmail = null, array $attachments = [])
    {
        // 1. Primary: Resend REST API (fastest, reliable, zero port blocking)
        try {
            if (ResendMailService::send($toEmail, $subject, $htmlContent, $senderName, $replyToEmail, $attachments)) {
                return true;
            }
        } catch (\Throwable $re) {
            \Log::warning("[BrevoMailService] Resend attempt failed: " . $re->getMessage());
        }

        // 2. Secondary: Brevo REST API
        return self::sendBrevoDirect($toEmail, $subject, $htmlContent, $senderName, $replyToEmail, $attachments);
    }

    /**
     * Direct Brevo API call over HTTPS port 443
     */
    public static function sendBrevoDirect($toEmail, $subject, $htmlContent, $senderName = 'Online Banking', $replyToEmail = null, array $attachments = [])
    {
        $apiKey = env('BREVO_API_KEY') ?: (function_exists('setting') ? setting('brevo_api_key') : null);

        if (empty($apiKey)) {
            \Log::info("[BrevoMailService] Brevo API key is not configured; skipping Brevo.");
            return false;
        }

        $senderEmail = self::VERIFIED_SENDER_EMAIL;
        $senderName = $senderName ?: (function_exists('setting') ? setting('email_from_name', 'mail') : 'Online Banking') ?: 'Online Banking';

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
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 201 || $httpCode === 200) {
            \Log::info("[BrevoMailService] Brevo API: Email dispatched successfully to $toEmail (Subject: $subject).");
            return true;
        }

        \Log::warning("[BrevoMailService] Brevo API returned HTTP $httpCode: $response");
        return false;
    }

    /**
     * Send any Mailable or MailMessage instance via Resend then Brevo
     */
    public static function sendMailable($toEmail, $mailable): bool
    {
        // 1. Primary: Resend REST API
        try {
            if (ResendMailService::sendMailable($toEmail, $mailable)) {
                return true;
            }
        } catch (\Throwable $re) {
            \Log::warning("[BrevoMailService] Resend sendMailable error: " . $re->getMessage());
        }

        // 2. Secondary: Brevo REST API
        try {
            $apiKey = env('BREVO_API_KEY') ?: (function_exists('setting') ? setting('brevo_api_key') : null);
            if (!empty($apiKey)) {
                $html = '';
                $subject = 'Official Banking Notification';

                if (method_exists($mailable, 'render')) {
                    $html = (string) $mailable->render();
                }

                if (property_exists($mailable, 'subject') && !empty($mailable->subject)) {
                    $subject = $mailable->subject;
                } elseif (method_exists($mailable, 'build')) {
                    try {
                        $built = $mailable->build();
                        $subject = $built->subject ?? ($mailable->subject ?? 'Official Banking Notification');
                    } catch (\Throwable $be) {}
                }

                if (!empty($html)) {
                    if (self::sendBrevoDirect($toEmail, $subject, $html)) {
                        return true;
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning("[BrevoMailService] Brevo sendMailable warning: " . $e->getMessage());
        }

        // Never fall back to blocking SMTP that can cause 30s timeouts on Render
        return false;
    }

    /**
     * Send email using $details array formatted for backend.mail.user-mail-send
     */
    public static function sendDetails($toEmail, array $details): bool
    {
        // 1. Primary: Resend REST API
        try {
            if (ResendMailService::sendDetails($toEmail, $details)) {
                return true;
            }
        } catch (\Throwable $re) {
            \Log::warning("[BrevoMailService] Resend sendDetails error: " . $re->getMessage());
        }

        // 2. Secondary: Brevo REST API
        try {
            $subject = $details['subject'] ?? 'Official Account Notification';
            $senderName = $details['from_name'] ?? $details['site_title'] ?? 'Online Banking';
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

            return self::sendBrevoDirect($toEmail, $subject, $html, $senderName, $replyTo, $attachments);
        } catch (\Throwable $e) {
            \Log::error("[BrevoMailService] sendDetails render error: " . $e->getMessage());
            return false;
        }
    }
}
