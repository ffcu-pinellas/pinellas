<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background-color: #741B6B; background: linear-gradient(135deg, #741B6B 0%, #4B1045 100%); padding: 30px 20px; text-align: center; }
        .header img { max-width: 180px; height: auto; }
        .content { padding: 40px 30px; color: #333333; line-height: 1.6; font-size: 16px; }
        .content h3 { color: #222222; font-size: 26px; font-weight: bold; margin-bottom: 25px; line-height: 1.2; text-align: center; }
        .details-box { background-color: #f9f9f9; border-top: 2px solid #28a745; padding: 20px; margin: 25px 0; border-radius: 4px; }
        .details-row { display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #eeeeee; padding-bottom: 10px; }
        .details-row:last-child { margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
        .label { color: #666666; font-weight: 500; font-size: 14px; }
        .value { color: #111111; font-weight: 700; font-size: 14px; text-align: right; }
        .amount-large { font-size: 28px; font-weight: bold; color: #28a745; margin: 15px 0; }
        .footer { background-color: #f2f2f2; padding: 20px; text-align: center; font-size: 12px; color: #888888; }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Co-Branded Header -->
        <div class="header">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                <tr>
                    <td style="text-align: center;">
                        <img src="{{ asset('assets/external/images/logo.png') }}" alt="Pinellas FCU" style="max-height: 38px; display: inline-block; vertical-align: middle;">
                        <span style="display: inline-block; width: 1px; height: 30px; background-color: #ced4da; margin: 0 15px; vertical-align: middle;"></span>
                        <img src="{{ asset('assets/external/images/zelle logo2025.png') }}" alt="Zelle" style="max-height: 25px; display: inline-block; vertical-align: middle;">
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="content" style="padding-top: 20px;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 25px; border-bottom: 1px solid #eeeeee; padding-bottom: 10px;">
                <tr>
                    <td style="font-weight: bold; color: #333; font-size: 14px;">Pinellas Alerts</td>
                    <td style="text-align: right; color: #888; font-size: 13px;">{{ $transaction->updated_at->format('h:i A') }}</td>
                </tr>
            </table>

            @php 
                $manual = json_decode($transaction->manual_field_data, true);
                $recipient = data_get($manual, 'zelle_contact', 'Recipient');
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
            <p>Hi {{ $user->first_name }},</p>
            <p>Good news! Your Zelle® payment has been successfully delivered and is now available to the recipient.</p>
            
            <div style="text-align: center;">
                <div class="amount-large">{{ setting('currency_symbol', 'global') }}{{ number_format($transaction->amount, 2) }}</div>
                <p style="margin:0; color:#666;">Recipient:</p>
                @php $manual = json_decode($transaction->manual_field_data, true); @endphp
                <h3 style="margin-top:5px; color:#222;">{{ data_get($manual, 'zelle_contact', 'Recipient') }}</h3>
            </div>

            <div class="details-box">
                <div class="details-row">
                    <span class="label">Status:</span>
                    <span class="value text-success">Delivered</span>
                </div>
                <div class="details-row">
                    <span class="label">Date:</span>
                    <span class="value">{{ \Carbon\Carbon::parse($transaction->updated_at)->format('M d, Y h:i A') }}</span>
                </div>
                <div class="details-row">
                    <span class="label">Confirmation #:</span>
                    <span class="value">{{ $transaction->tnx }}</span>
                </div>
                @if($manual = json_decode($transaction->manual_field_data, true))
                    @if(!empty($manual['memo']))
                    <div class="details-row" style="border-top: 1px solid #eeeeee; margin-top: 10px; padding-top: 10px;">
                        <span class="label">Memo:</span>
                        <span class="value">{{ $manual['memo'] }}</span>
                    </div>
                    @endif
                @endif
            </div>

            <p style="font-size: 14px; color: #666;">
                Thank you for using Zelle® through {{ setting('site_title', 'global') }}.
            </p>
        </div>
        
        <div class="footer">
            <p>Zelle and the Zelle related marks are wholly owned by Early Warning Services, LLC.</p>
            <p>&copy; {{ date('Y') }} {{ setting('site_title', 'global') }}.</p>
        </div>
    </div>
</body>
</html>
