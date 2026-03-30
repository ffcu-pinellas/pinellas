<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ZellePaymentApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $transaction;

    public function __construct(User $user, Transaction $transaction)
    {
        $this->user = $user;
        $this->transaction = $transaction;
    }

    public function build()
    {
        return $this->subject('Zelle® Payment Delivered')
                    ->markdown('emails.zelle_approved');
    }
}
