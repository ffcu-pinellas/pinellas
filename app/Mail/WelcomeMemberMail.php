<?php

namespace App\Mail;

use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMemberMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $routingNumber = setting('routing_number', 'global') ?? '263177741';
        $loginUrl = route('login');
        $siteTitle = setting('site_title', 'global') ?? 'Pinellas FCU';
        
        $siteLogo = setting('site_logo', 'global');
        if ($siteLogo && ! \Illuminate\Support\Str::startsWith($siteLogo, 'assets/')) {
            $siteLogo = 'assets/'.$siteLogo;
        }
        $logoUrl = $siteLogo ? \App\Support\MailAsset::absolute($siteLogo) : '';
        $homeUrl = rtrim((string) config('app.url'), '/').'/';

        // Check database for document template
        $template = DocumentTemplate::where('category', 'welcome_letter')->active()->first();

        if ($template && $template->email_content) {
            $fromName = $template->email_from_name ?? setting('support_email_from_name', 'global') ?? 'Pinellas FCU';
            $subject = $template->email_subject ?? 'Welcome to Pinellas FCU - Your Account Details';

            // Replace shortcodes
            $replacements = [
                '[[FULL_NAME]]' => $this->user->full_name,
                '[[CHECKING_ACCOUNT_NUMBER]]' => $this->user->account_number,
                '[[SAVINGS_ACCOUNT_NUMBER]]' => $this->user->savings_account_number,
                '[[ROUTING_NUMBER]]' => $routingNumber,
                '[[LOGIN_URL]]' => $loginUrl,
                '[[LOGO_URL]]' => $logoUrl,
                '[[SITE_TITLE]]' => $siteTitle,
                '[[HOME_URL]]' => $homeUrl,
            ];

            $subject = str_replace(array_keys($replacements), array_values($replacements), $subject);
            $htmlBody = str_replace(array_keys($replacements), array_values($replacements), $template->email_content);

            return $this->from(setting('support_email', 'global'), $fromName)
                ->subject($subject)
                ->html($htmlBody);
        }

        // Fallback to blade view
        return $this->from(setting('support_email', 'global'), 'Pinellas FCU')
            ->subject('Welcome to Pinellas FCU - Your Account Details')
            ->view('emails.welcome_member')
            ->with([
                'fullName' => $this->user->full_name,
                'checkingAccountNumber' => $this->user->account_number,
                'savingsAccountNumber' => $this->user->savings_account_number,
                'routingNumber' => $routingNumber,
                'loginUrl' => $loginUrl,
                'siteTitle' => $siteTitle,
                'siteLogoUrl' => $logoUrl,
                'homeUrl' => $homeUrl,
            ]);
    }
}
