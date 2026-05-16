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
        
        // Dynamic status phrases for realistic bank lingua
        $statusPhrases = [
            'completed' => [
                'desc' => 'has been successfully applied and funds are now available.',
                'action' => 'Funds Available',
                'badge' => 'COMPLETED',
                'zelle_lingua' => 'You have received money.'
            ],
            'success' => [
                'desc' => 'has been successfully applied and funds are now available.',
                'action' => 'Funds Available',
                'badge' => 'COMPLETED',
                'zelle_lingua' => 'You have received money.'
            ],
            'pending' => [
                'desc' => 'is currently processing and will be posted to your account shortly.',
                'action' => 'Processing',
                'badge' => 'PENDING',
                'zelle_lingua' => 'Money is on its way.'
            ],
            'processing' => [
                'desc' => 'is currently processing and will be posted to your account shortly.',
                'action' => 'Processing',
                'badge' => 'PROCESSING',
                'zelle_lingua' => 'Money is on its way.'
            ],
            'hold' => [
                'desc' => 'is on temporary hold pending further verification.',
                'action' => 'On Hold',
                'badge' => 'HOLD',
                'zelle_lingua' => 'Action required.'
            ],
        ];

        $phrase = $statusPhrases[strtolower($this->status)] ?? [
            'desc' => 'has been updated to ' . $this->status . '.',
            'action' => ucfirst($this->status),
            'badge' => strtoupper($this->status),
            'zelle_lingua' => 'Transaction update.'
        ];

        $recipientName = data_get($manual_data, 'account_name') 
            ?? data_get($manual_data, 'recipient_name') 
            ?? data_get($manual_data, 'zelle_contact') 
            ?? data_get($manual_data, 'beneficiary_name')
            ?? 'Customer';
            
        // If it still has Zelle ID in parens, clean it for name
        if (str_contains($recipientName, '(')) {
            $recipientName = trim(explode('(', $recipientName)[0]);
        }

        $rawAccount = data_get($manual_data, 'account_number') 
            ?? data_get($manual_data, 'recipient_account') 
            ?? data_get($manual_data, 'zelle_id')
            ?? '';
            
        if (!$rawAccount && str_contains($this->transaction->description, '(')) {
            // Try to parse from description like "Zelle Payment to Name (ID)"
            preg_match('/\((.*?)\)/', $this->transaction->description, $matches);
            $rawAccount = $matches[1] ?? '';
        }

        $maskedAccount = $rawAccount ? (strlen($rawAccount) > 4 ? '...' . substr($rawAccount, -4) : $rawAccount) : 'N/A';
        
        $memo = $this->transaction->purpose 
            ?? data_get($manual_data, 'memo') 
            ?? data_get($manual_data, 'purpose') 
            ?? $this->transaction->description 
            ?? 'Electronic Transfer';
            
        // If it's a Zelle payment, ensure memo is clean
        if (str_contains($memo, 'Zelle Payment to')) {
             $memo = data_get($manual_data, 'memo') ?? data_get($manual_data, 'purpose') ?? 'Zelle Transfer';
        }

        $shortcodes = [
            '[[USER_NAME]]' => $user->full_name,
            '[[RECIPIENT_NAME]]' => $recipientName,
            '[[RECIPIENT_EMAIL]]' => $this->recipientEmail,
            '[[AMOUNT]]' => $this->customAmount ?: number_format($this->transaction->amount, 2),
            '[[STATUS]]' => $phrase['badge'],
            '[[STATUS_DESC]]' => $phrase['desc'],
            '[[STATUS_ACTION]]' => $phrase['action'],
            '[[ZELLE_LINGUA]]' => $phrase['zelle_lingua'],
            '[[BANK_NAME]]' => $this->transaction->bank->name ?? data_get($manual_data, 'bank_name') ?? 'Your Bank',
            '[[ACCOUNT_NUMBER]]' => $maskedAccount,
            '[[DATE]]' => $this->transaction->created_at->format('M d, Y'),
            '[[CURRENT_DATE]]' => now()->format('M d, Y'),
            '[[TNX]]' => $this->transaction->tnx,
            '[[INITIALS]]' => $initials,
            '[[MEMO]]' => $memo,
            '[[DESCRIPTION]]' => $memo,
            
            // Support bracket versions too
            '[USER_NAME]' => $user->full_name,
            '[RECIPIENT_NAME]' => $recipientName,
            '[RECIPIENT_EMAIL]' => $this->recipientEmail,
            '[AMOUNT]' => $this->customAmount ?: number_format($this->transaction->amount, 2),
            '[STATUS]' => $phrase['badge'],
            '[DATE]' => $this->transaction->created_at->format('M d, Y'),
            '[INITIALS]' => $initials,
            '[MEMO]' => $memo,
            '[DESCRIPTION]' => $memo,
        ];

        $subject = str_replace(array_keys($shortcodes), array_values($shortcodes), $this->template->email_subject ?? 'Transaction Notification');
        
        // Use custom content if provided, otherwise use template
        $templateContent = $this->customContent ?: $this->template->email_content;
        $content = str_replace(array_keys($shortcodes), array_values($shortcodes), $templateContent);
        
        $salutation = str_replace(array_keys($shortcodes), array_values($shortcodes), $this->template->email_salutation);
        $footer = str_replace(array_keys($shortcodes), array_values($shortcodes), $this->template->email_footer);
        
        $pixelUrl = $this->trackingToken ? route('mail.tracking.open', $this->trackingToken) : '';
        $pixel = $pixelUrl ? '<img src="' . $pixelUrl . '" width="1" height="1" style="display:none;" />' : '';

        return $this->from(config('mail.from.address'), $this->template->email_from_name ?? setting('site_title'))
                    ->subject($subject)
                    ->html($salutation . "<br><br>" . $content . "<br><br>" . $footer . $pixel);
    }
}
