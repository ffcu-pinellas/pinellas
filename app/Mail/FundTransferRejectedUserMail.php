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
        $inst = $this->member->institution ?? null;
        $isCustom = $inst && !$inst->is_default;
        $siteTitle = $inst ? $inst->name : (setting('site_title', 'global') ?? 'FrontField Credit Union');
        $primaryColor = $inst->primary_color ?? ($isCustom ? '#c8232c' : '#00549b');

        $label = $this->transferKindSlug === 'external' ? 'External transfer' : 'Member-to-member transfer';
        $subject = "{$siteTitle} — {$label} not approved";

        $siteLogo = setting('site_logo', 'global');
        if ($siteLogo && ! Str::startsWith($siteLogo, 'assets/')) {
            $siteLogo = 'assets/'.$siteLogo;
        }

        $siteLogoUrl = $isCustom
            ? asset('assets/institutions/clevelandstatebank/images/logo.png')
            : MailAsset::absolute($siteLogo);

        $homeUrl = $isCustom
            ? "https://{$inst->slug}.frontfieldcu.pro"
            : rtrim((string) config('app.url'), '/').'/';

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
                'primaryColor' => $primaryColor,
                'siteLogoUrl' => $siteLogoUrl,
                'homeUrl' => $homeUrl,
                'transferLogUrl' => route('user.fund_transfer.transfer.log'),
            ]);
    }
}
