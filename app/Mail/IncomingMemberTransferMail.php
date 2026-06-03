<?php

namespace App\Mail;

use App\Models\Transaction;
use App\Models\User;
use App\Support\MailAsset;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class IncomingMemberTransferMail extends Mailable
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
        $siteTitle = setting('site_title', 'global');
        
        if ($this->status === 'success') {
            $subject = "Deposit Confirmation: Incoming Transfer Credited";
        } elseif ($this->status === 'failed') {
            $subject = "Incoming Transfer Alert: Transfer Declined";
        } else {
            $subject = "Pending Transfer: Incoming Transfer from " . $this->sender->full_name;
        }

        $siteLogo = setting('site_logo', 'global');
        if ($siteLogo && ! Str::startsWith($siteLogo, 'assets/')) {
            $siteLogo = 'assets/'.$siteLogo;
        }

        $receiverWalletName = $this->receiverWalletType == 'primary_savings' ? 'Savings' : 'Checking';
        $maskedAccount = $receiverWalletName . ' (... ' . substr($this->sanitizedNumber, -4) . ')';

        return $this->subject($subject)
            ->view('emails.incoming_member_transfer')
            ->with([
                'receiver' => $this->receiver,
                'sender' => $this->sender,
                'transaction' => $this->transaction,
                'status' => $this->status,
                'memo' => $this->memo,
                'maskedAccount' => $maskedAccount,
                'siteTitle' => $siteTitle,
                'siteLogoUrl' => MailAsset::absolute($siteLogo),
                'homeUrl' => rtrim((string) config('app.url'), '/').'/',
                'transferLogUrl' => route('user.fund_transfer.transfer.log'),
            ]);
    }
}
