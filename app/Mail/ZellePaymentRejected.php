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
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Zelle® Payment Update: Cancelled')
                    ->markdown('emails.zelle_rejected');
    }
}
