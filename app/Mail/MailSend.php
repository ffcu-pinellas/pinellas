<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailSend extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->from(setting('support_email', 'global'))
            ->subject($this->details['subject'])
            ->view('backend.mail.user-mail-send');

        if (isset($this->details['attachment'])) {
            $mail->attachData(
                $this->details['attachment']['data'],
                $this->details['attachment']['filename'],
                ['mime' => 'application/pdf']
            );
        }

        return $mail;
    }
}
