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
        $inst = $this->receiver->institution ?? ($this->sender->institution ?? null);
        $isCustom = $inst && !$inst->is_default;
        $siteTitle = $inst ? $inst->name : (setting('site_title', 'global') ?? 'FrontField Credit Union');
        $primaryColor = $inst->primary_color ?? ($isCustom ? '#c8232c' : '#00549b');
        
        if ($this->status === 'success') {
            $subject = "Deposit Confirmation: Incoming Transfer Credited - {$siteTitle}";
        } elseif ($this->status === 'failed') {
            $subject = "Incoming Transfer Alert: Transfer Cancelled - {$siteTitle}";
        } else {
            $subject = "Pending Transfer: Incoming Transfer from " . $this->sender->full_name;
        }

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
                'primaryColor' => $primaryColor,
                'siteLogoUrl' => $siteLogoUrl,
                'homeUrl' => $homeUrl,
                'transferLogUrl' => route('user.fund_transfer.transfer.log'),
            ]);
    }
}
