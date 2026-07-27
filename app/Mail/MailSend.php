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
        $fromEmail = trim((string) setting('email_from_address', 'mail'));
        if (empty($fromEmail)) {
            $fromEmail = trim((string) config('mail.from.address'));
        }
        if (empty($fromEmail)) {
            $fromEmail = trim((string) config('mail.mailers.smtp.username'));
        }
        if (empty($fromEmail)) {
            $fromEmail = trim((string) setting('support_email', 'global'));
        }

        $fromName = setting('email_from_name', 'mail') ?: setting('site_title', 'global') ?: config('mail.from.name');

        $mail = $this->from($fromEmail, $fromName)
            ->subject($this->details['subject'])
            ->view('backend.mail.user-mail-send', ['details' => $this->details]);

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
