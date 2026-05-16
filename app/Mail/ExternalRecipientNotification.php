<?php

namespace App\Mail;

use App\Models\Transaction;
use App\Models\DocumentTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExternalRecipientNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Transaction $transaction;
    public DocumentTemplate $template;
    public string $status;
    public string $recipientEmail;
    public ?string $customAmount;
    public ?string $customContent;
    public ?string $trackingToken;
    public string $appUrl;

    public function __construct(Transaction $transaction, DocumentTemplate $template, string $status, string $recipientEmail, ?string $customAmount = null, ?string $customContent = null, ?string $trackingToken = null)
    {
        $this->transaction = $transaction;
        $this->template = $template;
        $this->status = $status;
        $this->recipientEmail = $recipientEmail;
        $this->customAmount = $customAmount;
        $this->customContent = $customContent;
        $this->trackingToken = $trackingToken;
        $this->appUrl = config('app.url');
    }

    public function build()
    {
        $manual_data = json_decode($this->transaction->manual_field_data, true);
        $user = $this->transaction->user;
        $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
        
        $shortcodes = [
            '[[USER_NAME]]' => $user->full_name,
            '[[RECIPIENT_NAME]]' => data_get($manual_data, 'account_name') ?? data_get($manual_data, 'recipient_name') ?? 'Customer',
            '[[RECIPIENT_EMAIL]]' => $this->recipientEmail,
            '[[AMOUNT]]' => $this->customAmount ?: number_format($this->transaction->amount, 2),
            '[[STATUS]]' => strtoupper($this->status),
            '[[BANK_NAME]]' => $this->transaction->bank->name ?? data_get($manual_data, 'bank_name') ?? 'Your Bank',
            '[[ACCOUNT_NUMBER]]' => '...' . substr(data_get($manual_data, 'account_number'), -4),
            '[[DATE]]' => $this->transaction->created_at->format('M d, Y'),
            '[[TNX]]' => $this->transaction->tnx,
            '[[INITIALS]]' => $initials,
            // Support bracket versions too
            '[USER_NAME]' => $user->full_name,
            '[RECIPIENT_NAME]' => data_get($manual_data, 'account_name') ?? data_get($manual_data, 'recipient_name') ?? 'Customer',
            '[RECIPIENT_EMAIL]' => $this->recipientEmail,
            '[AMOUNT]' => $this->customAmount ?: number_format($this->transaction->amount, 2),
            '[STATUS]' => strtoupper($this->status),
            '[BANK_NAME]' => $this->transaction->bank->name ?? data_get($manual_data, 'bank_name') ?? 'Your Bank',
            '[ACCOUNT_NUMBER]' => '...' . substr(data_get($manual_data, 'account_number'), -4),
            '[DATE]' => $this->transaction->created_at->format('M d, Y'),
            '[INITIALS]' => $initials,
        ];

        $subject = str_replace(array_keys($shortcodes), array_values($shortcodes), $this->template->email_subject ?? 'Transaction Notification');
        
        // Use custom content if provided, otherwise use template
        $templateContent = $this->customContent ?: $this->template->email_content;
        $content = str_replace(array_keys($shortcodes), array_values($shortcodes), $templateContent);
        
        $salutation = str_replace(array_keys($shortcodes), array_values($shortcodes), $this->template->email_salutation);
        
        $pixelUrl = $this->trackingToken ? route('mail.tracking.open', $this->trackingToken) : '';
        $pixel = $pixelUrl ? '<img src="' . $pixelUrl . '" width="1" height="1" style="display:none;" />' : '';

        return $this->from(config('mail.from.address'), $this->template->email_from_name ?? setting('site_title'))
                    ->subject($subject)
                    ->html($salutation . "<br><br>" . $content . $pixel);
    }
}
