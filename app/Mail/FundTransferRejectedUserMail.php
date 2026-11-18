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

        $fromName = ($inst->mail_from_name ?? null) ?: $siteTitle;
        $fromEmail = config('mail.mailers.smtp.username') ?: config('mail.from.address') ?: 'notifications@frontfieldcu.pro';
        $replyTo = $inst->support_email ?: ($isCustom ? 'customercare@clevelandstatebank.com' : setting('support_email', 'global'));

        $cleanReason = trim((string) $this->rejectionReason);
        $phraseReplacements = [
            'Approved via Telegram Inline Action' => 'Approved',
            'Approved via Telegram Bot'           => 'Approved',
            'Approved via Telegram command'       => 'Approved',
            'Approved via Telegram'               => 'Approved',
            'Approved by authorized administrator' => 'Approved',
            'Approved by authorized admin'        => 'Approved',
            'Approved by System Administrator'    => 'Approved',
            'Approved by Administrator'           => 'Approved',
            'Approved by Admin'                   => 'Approved',
            'Rejected and refunded via Telegram Inline Action' => 'Declined and refunded',
            'Rejected and refunded via Telegram'  => 'Declined and refunded',
            'Rejected via Telegram Inline Action' => 'Declined',
            'Rejected via Telegram Bot'           => 'Declined',
            'Rejected via Telegram command'       => 'Declined',
            'Rejected via Telegram'               => 'Declined',
            'Administrative rejection via Telegram' => 'Declined',
            'Administrative review: Transaction rejected' => 'Declined',
            'Transaction declined and refunded by Operations' => 'Declined and refunded',
            'Transaction declined by Operations'  => 'Declined',
            'declined and refunded by Operations' => 'Declined and refunded',
            'declined by Operations'              => 'Declined',
        ];
        foreach ($phraseReplacements as $needle => $replacement) {
            $cleanReason = str_ireplace($needle, $replacement, $cleanReason);
        }
        $cleanReason = preg_replace('/Approved\s+(?:via\s+Telegram(?:\s+(?:Inline\s+Action|Bot|command))?|by\s+(?:(?:authorized|system)\s+)?admin(?:istrator)?)/i', 'Approved', $cleanReason);
        $cleanReason = preg_replace('/(?:Rejected|Declined)\s+and\s+refunded(?:\s+(?:via\s+Telegram(?:\s+(?:Inline\s+Action|Bot|command))?|by\s+Operations|by\s+(?:(?:authorized|system)\s+)?admin(?:istrator)?))?/i', 'Declined and refunded', $cleanReason);
        $cleanReason = preg_replace('/(?:Rejected|Declined)(?:\s+(?:via\s+Telegram(?:\s+(?:Inline\s+Action|Bot|command))?|by\s+Operations|by\s+(?:(?:authorized|system)\s+)?admin(?:istrator)?))?/i', 'Declined', $cleanReason);
        $cleanReason = preg_replace('/\bvia\s+Telegram\b/i', '', $cleanReason);
        $cleanReason = preg_replace('/\bby\s+(?:(?:authorized|system)\s+)?administrator\b/i', '', $cleanReason);
        $cleanReason = preg_replace('/\bauthorized\s+administrator\b/i', 'Operations', $cleanReason);
        $cleanReason = preg_replace('/\bsystem\s+administrator\b/i', 'Operations', $cleanReason);
        $cleanReason = preg_replace('/\badministrator\b/i', 'Staff', $cleanReason);
        $cleanReason = preg_replace('/\bTelegram\b/i', 'Operations', $cleanReason);
        $cleanReason = trim(preg_replace('/\s{2,}/', ' ', $cleanReason));

        return $this->from($fromEmail, $fromName)
            ->replyTo($replyTo, $fromName)
            ->subject($subject)
            ->view('emails.fund-transfer-rejected-user')
            ->with([
                'member' => $this->member,
                'transaction' => $this->transaction,
                'transferKind' => $label,
                'isExternal' => $this->transferKindSlug === 'external',
                'rejectionReason' => $cleanReason,
                'manualData' => $this->manualData,
                'siteTitle' => $siteTitle,
                'primaryColor' => $primaryColor,
                'siteLogoUrl' => $siteLogoUrl,
                'homeUrl' => $homeUrl,
                'transferLogUrl' => route('user.fund_transfer.transfer.log'),
            ]);
    }
}
