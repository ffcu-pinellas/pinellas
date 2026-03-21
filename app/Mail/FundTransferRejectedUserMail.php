<?php

namespace App\Mail;

use App\Models\Transaction;
use App\Models\User;
use App\Support\MailAsset;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class FundTransferRejectedUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public object $manualData;

    public function __construct(
        public User $member,
        public Transaction $transaction,
        public string $transferKindSlug,
        public string $rejectionReason,
    ) {
        $decoded = json_decode($transaction->manual_field_data ?? '{}', false);
        $this->manualData = is_object($decoded) ? $decoded : (object) [];
    }

    public function build(): self
    {
        $siteTitle = setting('site_title', 'global');
        $label = $this->transferKindSlug === 'external' ? 'External transfer' : 'Member-to-member transfer';
        $subject = "{$siteTitle} — {$label} not approved";

        $siteLogo = setting('site_logo', 'global');
        if ($siteLogo && ! Str::startsWith($siteLogo, 'assets/')) {
            $siteLogo = 'assets/'.$siteLogo;
        }

        return $this->subject($subject)
            ->view('emails.fund-transfer-rejected-user')
            ->with([
                'member' => $this->member,
                'transaction' => $this->transaction,
                'transferKind' => $label,
                'isExternal' => $this->transferKindSlug === 'external',
                'rejectionReason' => trim((string) $this->rejectionReason),
                'manualData' => $this->manualData,
                'siteTitle' => $siteTitle,
                'siteLogoUrl' => MailAsset::absolute($siteLogo),
                'homeUrl' => rtrim((string) config('app.url'), '/').'/',
                'transferLogUrl' => route('user.fund_transfer.transfer.log'),
            ]);
    }
}
