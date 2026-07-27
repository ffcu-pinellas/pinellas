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
        $fromEmail = setting('support_email', 'global') ?: setting('email_from_address', 'mail') ?: config('mail.from.address');
        $fromName = setting('email_from_name', 'mail') ?: setting('site_title', 'global') ?: config('mail.from.name');

        $mail = $this->from($fromEmail, $fromName)
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
