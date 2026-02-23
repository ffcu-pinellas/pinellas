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
                    'bottom_title' => str_replace($find, $replace, $template->bottom_title),
                    'bottom_body' => str_replace($find, $replace, $template->bottom_body),

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
            if ($code === 'user_mail') {
                // Fallback for manual user mail if template is accidentally deleted
                $details = [
                    'subject' => $shortcodes['[[subject]]'] ?? 'Notification',
                    'message_body' => $shortcodes['[[message]]'] ?? '',
                    'site_logo' => asset('assets/' . setting('site_logo', 'global')),
                    'site_link' => route('home'),
                    'site_title' => setting('site_title', 'global'),
                    'title' => 'Security Notification',
                    'salutation' => 'Hello ' . ($shortcodes['[[full_name]]'] ?? 'Member'),
                    'footer_status' => 1,
                    'footer_body' => 'Pinellas Federal Credit Union',
                    'button_level' => null,
                ];
                return Mail::to($email)->send(new MailSend($details));
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

                $pusher_credentials = config('broadcasting.connections.pusher');
                if ($pusher_credentials) {
                    $userId = $template->for == 'Admin' ? '' : $userId;
                    event(new NotificationEvent($template->for, $data, $userId));
                }
            }
        } catch (Exception $e) {
        }
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
