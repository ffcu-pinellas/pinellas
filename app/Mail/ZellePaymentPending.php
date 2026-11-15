<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ZellePaymentPending extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $transaction;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Transaction $transaction)
    {
        $this->user = $user;
        $this->transaction = $transaction;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
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

        $subject = "We're processing your Zelle payment — {$siteTitle}";
        
        return $this->from($fromEmail, $fromName)
                    ->replyTo($replyTo, $fromName)
                    ->subject($subject)
                    ->markdown('emails.zelle_pending');
    }
}
