<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background: linear-gradient(135deg, #6d1ed4 0%, #4B1045 100%); padding: 30px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .content { padding: 40px 30px; color: #333333; line-height: 1.6; font-size: 16px; }
        .content h1 { color: #222222; font-size: 26px; font-weight: bold; margin-bottom: 25px; line-height: 1.2; text-align: center; }
        .notice-box { background-color: #f8f9fa; border-left: 4px solid #0d6efd; padding: 20px; margin: 25px 0; border-radius: 4px; }
        .details-box { background-color: #f9f9f9; border-top: 2px solid #741B6B; padding: 20px; margin: 25px 0; border-radius: 4px; }
        .details-row { display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #eeeeee; padding-bottom: 10px; }
        .details-row:last-child { margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
        .label { color: #666666; font-weight: 500; font-size: 14px; }
        .value { color: #111111; font-weight: 700; font-size: 14px; text-align: right; }
        .amount-large { font-size: 28px; font-weight: bold; color: #222222; margin: 15px 0; text-align: center; }
        .footer { background-color: #f2f2f2; padding: 20px; text-align: center; font-size: 12px; color: #888888; }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Co-Branded Header (Dark Purple Gradient) -->
        <div class="header">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                <tr>
                    <td style="text-align: center;">
                        <img src="{{ asset('assets/external/images/logo.png') }}" alt="Pinellas FCU" style="height: 32px; display: inline-block; vertical-align: middle; filter: brightness(0) invert(1);">
                        <span style="display: inline-block; width: 1px; height: 24px; background-color: rgba(255,255,255,0.3); margin: 0 15px; vertical-align: middle;"></span>
                        <img src="{{ asset('assets/external/images/zelle logo2025.png') }}" alt="Zelle" style="height: 22px; display: inline-block; vertical-align: middle; filter: brightness(0) invert(1);">
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="content">
            <!-- Content Header matching Image 1 -->
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 25px; border-bottom: 1px solid #eeeeee; padding-bottom: 10px;">
                <tr>
                    <td style="font-weight: bold; color: #333; font-size: 14px;">Pinellas Alerts</td>
                    <td style="text-align: right; color: #888; font-size: 13px;">{{ $transaction->created_at->format('h:i A') }}</td>
                </tr>
            </table>

            @php 
                $manual = json_decode($transaction->manual_field_data, true);
                $recipient = data_get($manual, 'zelle_contact', 'Recipient');
                $walletType = data_get($manual, 'wallet_type', 'default');
                $maskedFrom = ($walletType === 'primary_savings' || $walletType === 'savings') 
                    ? 'Savings (... ' . substr($user->savings_account_number ?? $user->account_number, -4) . ')'
                    : 'Checking (... ' . substr($user->account_number, -4) . ')';
            @endphp
            
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                <tr>
                    <td width="35" style="vertical-align: top; padding-top: 3px;">
                        <img src="https://img.icons8.com/material-rounded/24/fc8e0b/appointment-reminders.png" alt="Alert" width="24">
                    </td>
                    <td style="font-size: 20px; font-weight: bold; color: #111; line-height: 1.3;">
                        {{ __('You sent a Zelle® payment to') }} {{ strtoupper($recipient) }}
                    </td>
                </tr>
            </table>

            <div class="notice-box">
                <p style="margin: 0; font-size: 15px;">
                    <strong>Security Review:</strong> Your payment has been submitted and, for your protection, is undergoing a security review which could take up to 24 hours. No action is needed on your part.
                </p>
            </div>

            <p>We'll notify you as soon as the review is complete and the funds are delivered.</p>
            
            <div class="details-box">
                <div class="details-row">
                    <span class="label">Status:</span>
                    <span class="value" style="color: #ff9800;">Hold</span>
                </div>
                <div class="details-row">
                    <span class="label">From:</span>
                    <span class="value">{{ $maskedFrom }}</span>
                </div>
                <div class="details-row">
                    <span class="label">Amount:</span>
                    <span class="value">{{ setting('currency_symbol', 'global') }}{{ number_format($transaction->amount, 2) }}</span>
                </div>
                <div class="details-row">
                    <span class="label">Date:</span>
                    <span class="value">{{ $transaction->created_at->format('M d, Y') }}</span>
                </div>
                <div class="details-row">
                    <span class="label">Confirmation #:</span>
                    <span class="value">{{ $transaction->tnx }}</span>
                </div>
                @if(!empty($manual['memo']))
                    <div class="details-row" style="border-top: 1px solid #eeeeee; margin-top: 10px; padding-top: 10px;">
                        <span class="label">Memo:</span>
                        <span class="value">{{ $manual['memo'] }}</span>
                    </div>
                @endif
            </div>

            <p style="font-size: 14px; color: #666; margin-top: 30px;">
                If you have any questions or did not authorize this payment, please contact us immediately.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ setting('site_title', 'global') }}. All rights reserved.<br>
            Zelle and the Zelle related marks are wholly owned by Early Warning Services, LLC.
        </div>
    </div>
</body>
</html>
