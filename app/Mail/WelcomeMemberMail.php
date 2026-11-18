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
        $institution = $this->user->institution ?? \App\Models\Institution::getDefault();
        $isCustom = $institution && !$institution->is_default;

        $routingNumber = $isCustom ? ($institution->routing_number ?? '263177742') : (setting('routing_number', 'global') ?? '263177741');
        $siteTitle = $institution->name;
        $primaryColor = $institution->primary_color ?? ($isCustom ? '#c8232c' : '#004d73');

        $customDomain = $institution?->custom_domain;
        if ($customDomain && !str_ends_with(strtolower($customDomain), '.frontfieldcu.pro')) {
            $cleanHost = ltrim($customDomain, 'https://');
            $homeUrl = 'https://' . $cleanHost;
            $homeDomain = $cleanHost;
            $loginUrl = 'https://' . rtrim($cleanHost, '/') . '/login';
        } elseif ($isCustom) {
            $homeDomain = 'clevelandstatebank.com';
            $homeUrl = '#';
            $loginUrl = route('login');
        } else {
            $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'frontfieldcu.pro';
            $homeDomain = !str_ends_with(strtolower($appHost), '.frontfieldcu.pro') || $appHost === 'frontfieldcu.pro' ? $appHost : 'frontfieldcu.pro';
            $homeUrl = rtrim((string) config('app.url'), '/') . '/';
            $loginUrl = route('login');
        }

        $mailingAddress = $institution->mailing_address ?? ($isCustom ? 'P.O. Box 2300 | Cleveland, OH 44166' : '2555 East Bay Drive | Clearwater, FL 33764');
        $regulator = $isCustom
            ? ('Member ' . ($institution->regulator_type ? strtoupper($institution->regulator_type) : 'FDIC') . ' • Equal Housing Lender')
            : 'Federally Insured by NCUA • Equal Housing Lender';
        $fromEmail = $institution->mail_from_address ?: ($institution->support_email ?: setting('support_email', 'global'));
        $fromName = $institution->mail_from_name ?: $siteTitle;
        
        $siteLogo = $institution->logo_path;
        if ($siteLogo && ! \Illuminate\Support\Str::startsWith($siteLogo, ['assets/', 'storage/'])) {
            $siteLogo = 'assets/'.$siteLogo;
        }
        $logoUrl = $siteLogo ? asset($siteLogo) : ($isCustom ? asset('assets/institutions/clevelandstatebank/images/logo.png') : asset('assets/external/images/logo.png'));

        // Check database for document template
        $template = DocumentTemplate::where('category', 'welcome_letter')->active()->first();

        if ($template && $template->email_content) {
            $fromName = $siteTitle;
            $subject = $template->email_subject ?? "Welcome to {$siteTitle} - Your Account Details";

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
                '[[HOME_DOMAIN]]' => $homeDomain,
            ];

            $subject = str_replace(array_keys($replacements), array_values($replacements), $subject);
            $htmlBody = str_replace(array_keys($replacements), array_values($replacements), $template->email_content);

            return $this->from($fromEmail, $fromName)
                ->subject($subject)
                ->html($htmlBody);
        }

        // Fallback to blade view
        return $this->from($fromEmail, $fromName)
            ->subject("Welcome to {$siteTitle} - Your Account Details")
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
                'homeDomain' => $homeDomain,
                'primaryColor' => $primaryColor,
                'mailingAddress' => $mailingAddress,
                'regulator' => $regulator,
            ]);
    }
}
