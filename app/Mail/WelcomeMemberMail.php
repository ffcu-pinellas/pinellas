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
     * Build view data array for welcome template
     */
    public function buildViewData(): array
    {
        $institution = $this->user->institution ?? \App\Models\Institution::getDefault();
        $isCustom = $institution && !$institution->is_default;

        $routingNumber = $isCustom ? ($institution->routing_number ?? '084301768') : (setting('routing_number', 'global') ?? '263177741');
        $siteTitle = $institution->name;
        $primaryColor = $institution->primary_color ?? ($isCustom ? '#c8232c' : '#00549b');

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
        
        $siteLogo = $institution->logo_path;
        if ($siteLogo && ! \Illuminate\Support\Str::startsWith($siteLogo, ['assets/', 'storage/'])) {
            $siteLogo = 'assets/'.$siteLogo;
        }
        $logoUrl = $siteLogo ? asset($siteLogo) : ($isCustom ? asset('assets/institutions/clevelandstatebank/images/logo.png') : asset('assets/external/images/logo.png'));

        $tierName = $this->user->portfolio?->portfolio_name ?? 'Premier Relationship Banking';

        return [
            'fullName' => $this->user->full_name,
            'checkingAccountNumber' => $this->user->account_number,
            'savingsAccountNumber' => $this->user->savings_account_number,
            'routingNumber' => $routingNumber,
            'tierName' => $tierName,
            'loginUrl' => $loginUrl,
            'siteTitle' => $siteTitle,
            'siteLogoUrl' => $logoUrl,
            'homeUrl' => $homeUrl,
            'homeDomain' => $homeDomain,
            'primaryColor' => $primaryColor,
            'mailingAddress' => $mailingAddress,
            'regulator' => $regulator,
        ];
    }

    /**
     * Dispatch welcome email via Brevo REST API with mail fallback
     */
    public static function dispatchWelcome(User $user): bool
    {
        try {
            $mailable = new self($user);
            $institution = $user->institution ?? \App\Models\Institution::getDefault();
            $siteTitle = $institution->name ?? 'Online Banking';
            $fromName = $institution->mail_from_name ?: $siteTitle;
            $subject = "Welcome to {$siteTitle} - Your Account Credentials & Tier Details";
            $supportEmail = $institution->support_email ?: setting('support_email', 'global');

            $viewData = $mailable->buildViewData();
            $html = view('emails.welcome_member', $viewData)->render();

            $attachments = [];
            try {
                if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                    $pdfContent = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.account_disclosure', $viewData)->output();
                    if (!empty($pdfContent)) {
                        $attachments[] = [
                            'name' => 'Account_Opening_Disclosure.pdf',
                            'content' => base64_encode($pdfContent),
                        ];
                    }
                }
            } catch (\Throwable $pe) {
                \Log::warning("Could not generate account disclosure PDF: " . $pe->getMessage());
            }

            if (\App\Services\BrevoMailService::send($user->email, $subject, $html, $fromName, $supportEmail, $attachments)) {
                return true;
            }
        } catch (\Throwable $e) {
            \Log::warning("Brevo welcome dispatch failed, attempting standard mail: " . $e->getMessage());
        }

        try {
            \Mail::to($user->email)->send(new self($user));
            return true;
        } catch (\Throwable $e) {
            \Log::error("Welcome mail send failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $institution = $this->user->institution ?? \App\Models\Institution::getDefault();
        $siteTitle = $institution->name;
        $fromEmail = $institution->mail_from_address ?: ($institution->support_email ?: setting('support_email', 'global'));
        $fromName = $institution->mail_from_name ?: $siteTitle;

        $viewData = $this->buildViewData();

        $mail = $this->from($fromEmail, $fromName)
            ->subject("Welcome to {$siteTitle} - Your Account Credentials & Tier Details")
            ->view('emails.welcome_member')
            ->with($viewData);

        try {
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdfContent = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.account_disclosure', $viewData)->output();
                if (!empty($pdfContent)) {
                    $mail->attachData($pdfContent, 'Account_Opening_Disclosure.pdf', ['mime' => 'application/pdf']);
                }
            }
        } catch (\Throwable $pe) {
            \Log::warning("Could not attach disclosure PDF to standard welcome mail: " . $pe->getMessage());
        }

        return $mail;
    }
}
