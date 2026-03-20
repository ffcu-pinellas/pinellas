<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\User;
use App\Support\MailAsset;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TransferPendingAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Admin $recipient,
        public User $member,
        public array $transferPayload,
        public array $responseData,
        public string $reviewUrl,
        public ?string $transactionReference = null,
    ) {}

    public function build(): self
    {
        $siteTitle = setting('site_title', 'global');
        $kind = strtoupper((string) ($this->transferPayload['transfer_type'] ?? 'transfer'));
        $subject = "{$siteTitle} — {$kind} transfer pending your review";

        $siteLogo = setting('site_logo', 'global');
        if ($siteLogo && ! Str::startsWith($siteLogo, 'assets/')) {
            $siteLogo = 'assets/' . $siteLogo;
        }

        return $this->subject($subject)
            ->view('emails.transfer-pending-admin')
            ->with([
                'recipientName' => $this->recipient->name,
                'siteTitle' => $siteTitle,
                'siteLogoUrl' => MailAsset::absolute($siteLogo),
                'homeUrl' => rtrim((string) config('app.url'), '/').'/',
                'reviewUrl' => $this->reviewUrl,
                'member' => $this->member,
                'transferPayload' => $this->transferPayload,
                'responseData' => $this->responseData,
                'transactionReference' => $this->transactionReference,
            ]);
    }
}
