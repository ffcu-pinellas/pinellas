<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ZellePaymentRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $transaction;
    public $reason;

    public function __construct(User $user, Transaction $transaction, $reason = '')
    {
        $this->user = $user;
        $this->transaction = $transaction;
        $this->reason = \App\Traits\NotifyTrait::scrubText($reason);
    }

    public function build()
    {
        $inst = $this->user->institution
            ?? (app()->bound('institution') ? app('institution') : null)
            ?? \App\Models\Institution::getDefault();
        $isCustom = $inst && !$inst->is_default;
        $siteTitle = $inst ? $inst->name : (setting('site_title', 'global') ?? 'FrontField Credit Union');
        $fromName = ($inst->mail_from_name ?? null) ?: $siteTitle;
        $fromEmail = config('mail.mailers.smtp.username') ?: config('mail.from.address') ?: 'notifications@frontfieldcu.pro';
        $replyTo = $inst->support_email ?: ($isCustom ? 'customercare@clevelandstatebank.com' : setting('support_email', 'global'));

        return $this->from($fromEmail, $fromName)
                    ->replyTo($replyTo, $fromName)
                    ->subject("Zelle® Payment Update: Cancelled — {$siteTitle}")
                    ->markdown('emails.zelle_rejected');
    }
}
