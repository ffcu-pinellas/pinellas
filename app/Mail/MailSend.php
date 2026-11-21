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
        $senderAddress = trim((string) config('mail.from.address'));
        if (empty($senderAddress)) {
            $senderAddress = trim((string) setting('email_from_address', 'mail'));
        }
        if (empty($senderAddress)) {
            $senderAddress = 'devonbrown0122@gmail.com';
        }

        $replyToEmail = !empty($this->details['from_email'])
            ? trim((string) $this->details['from_email'])
            : (!empty($this->details['support_email']) ? trim((string) $this->details['support_email']) : $senderAddress);

        $fromName = !empty($this->details['from_name']) 
            ? trim((string) $this->details['from_name']) 
            : (!empty($this->details['site_title']) ? trim((string) $this->details['site_title']) : (setting('email_from_name', 'mail') ?: setting('site_title', 'global') ?: config('mail.from.name')));

        $mail = $this->from($senderAddress, $fromName)
            ->replyTo($replyToEmail, $fromName)
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
