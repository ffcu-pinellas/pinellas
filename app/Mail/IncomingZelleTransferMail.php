<?php

namespace App\Mail;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class IncomingZelleTransferMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $receiver;
    public User $sender;
    public Transaction $transaction;
    public string $status; // 'success', 'failed', or 'pending'
    public ?string $memo;
    public string $receiverWalletType;
    public string $sanitizedNumber;

    public function __construct(User $receiver, User $sender, Transaction $transaction, string $status, ?string $memo, string $receiverWalletType, string $sanitizedNumber)
    {
        $this->receiver = $receiver;
        $this->sender = $sender;
        $this->transaction = $transaction;
        $this->status = $status;
        $this->memo = $memo;
        $this->receiverWalletType = $receiverWalletType;
        $this->sanitizedNumber = $sanitizedNumber;
    }

    public function build(): self
    {
        if ($this->status === 'success') {
            $subject = "Zelle® Payment Received: {$this->sender->full_name} sent you $" . number_format($this->transaction->amount, 2);
        } elseif ($this->status === 'failed') {
            $subject = "Incoming Zelle® Transfer: Request Cancelled";
        } else {
            $subject = "Zelle® Payment Alert: {$this->sender->full_name} initiated a transfer to you";
        }

        $inst = $this->receiver->institution
            ?? ($this->sender->institution ?? null)
            ?? (app()->bound('institution') ? app('institution') : null)
            ?? \App\Models\Institution::getDefault();
        $isCustom = $inst && !$inst->is_default;
        $siteTitle = $inst ? $inst->name : (setting('site_title', 'global') ?? 'FrontField Credit Union');
        $fromName = ($inst->mail_from_name ?? null) ?: $siteTitle;
        $fromEmail = config('mail.mailers.smtp.username') ?: config('mail.from.address') ?: 'notifications@frontfieldcu.pro';
        $replyTo = $inst->support_email ?: ($isCustom ? 'customercare@clevelandstatebank.com' : setting('support_email', 'global'));

        $receiverWalletName = $this->receiverWalletType == 'primary_savings' ? 'Savings' : 'Checking';
        $maskedAccount = $receiverWalletName . ' (... ' . substr($this->sanitizedNumber, -4) . ')';

        return $this->from($fromEmail, $fromName)
            ->replyTo($replyTo, $fromName)
            ->subject($subject)
            ->view('emails.incoming_zelle_transfer')
            ->with([
                'receiver' => $this->receiver,
                'sender' => $this->sender,
                'transaction' => $this->transaction,
                'status' => $this->status,
                'memo' => $this->memo,
                'maskedAccount' => $maskedAccount,
                'homeUrl' => $isCustom ? "https://{$inst->slug}.frontfieldcu.pro" : rtrim((string) config('app.url'), '/').'/',
                'transferLogUrl' => route('user.fund_transfer.transfer.log'),
            ]);
    }
}
