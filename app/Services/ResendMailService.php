<?php

namespace App\Services;

class ResendMailService
{
    /**
     * Send email using Resend REST API over HTTPS port 443.
     * Works 100% on Render Free and Paid tiers without any SMTP port blocks.
     */
    public static function send($toEmail, $subject, $htmlContent, $senderName = null, $replyToEmail = null, array $attachments = [])
    {
        $apiKey = env('RESEND_API_KEY') 
            ?: (function_exists('setting') ? setting('resend_api_key') : null) 
            ?: env('MAIL_PASSWORD');

        if (empty($apiKey)) {
            \Log::warning("[ResendMailService] Resend API key is not configured.");
            return false;
        }

        $senderName = $senderName ?: (function_exists('setting') ? setting('email_from_name', 'mail') : 'Online Banking') ?: 'Online Banking';
        $fromEmail = env('RESEND_FROM_EMAIL') ?: (function_exists('setting') ? setting('resend_from_email') : null) ?: 'onboarding@resend.dev';
        $from = "{$senderName} <{$fromEmail}>";

        $payload = [
            'from' => $from,
            'to' => [trim($toEmail)],
            'subject' => $subject,
            'html' => $htmlContent,
        ];

        if (!empty($replyToEmail)) {
            $payload['reply_to'] = trim($replyToEmail);
        }

        if (!empty($attachments)) {
            $resendAttachments = [];
            foreach ($attachments as $att) {
                if (isset($att['content']) && isset($att['name'])) {
                    $resendAttachments[] = [
                        'content' => $att['content'], // base64 string
                        'filename' => $att['name'],
                    ];
                }
            }
            if (!empty($resendAttachments)) {
                $payload['attachments'] = $resendAttachments;
            }
        }

        $url = 'https://api.resend.com/emails';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            \Log::info("[ResendMailService] Email dispatched successfully to $toEmail (Subject: $subject). Response: $response");
            return true;
        }

        \Log::warning("[ResendMailService] Failed to send email to $toEmail (HTTP $httpCode). Error: $err, Response: $response");
        return false;
    }

    /**
     * Send any Mailable or MailMessage instance via Resend REST API
     */
    public static function sendMailable($toEmail, $mailable): bool
    {
        try {
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
                return self::send($toEmail, $subject, $html);
            }
        } catch (\Throwable $e) {
            \Log::warning("[ResendMailService] sendMailable warning: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Send email using $details array formatted for backend.mail.user-mail-send
     */
    public static function sendDetails($toEmail, array $details): bool
    {
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

            return self::send($toEmail, $subject, $html, $senderName, $replyTo, $attachments);
        } catch (\Throwable $e) {
            \Log::error("[ResendMailService] sendDetails render error: " . $e->getMessage());
            return false;
        }
    }
}
