<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background-color: #741B6B; background: linear-gradient(135deg, #741B6B 0%, #4B1045 100%); padding: 30px 20px; text-align: center; }
        .header img { max-width: 180px; height: auto; }
        .content { padding: 40px 30px; color: #333333; line-height: 1.6; font-size: 16px; }
        .content h1 { color: #222222; font-size: 26px; font-weight: bold; margin-bottom: 25px; line-height: 1.2; text-align: center; }
        .details-box { background-color: #f9f9f9; border-top: 2px solid #741B6B; padding: 20px; margin: 25px 0; border-radius: 4px; }
        .details-row { display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #eeeeee; padding-bottom: 10px; }
        .details-row:last-child { margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
        .label { color: #666666; font-weight: 500; font-size: 14px; }
        .value { color: #111111; font-weight: 700; font-size: 14px; text-align: right; }
        .amount-large { font-size: 28px; font-weight: bold; color: #741B6B; margin: 15px 0; }
        .footer { background-color: #f2f2f2; padding: 20px; text-align: center; font-size: 12px; color: #888888; }
        .button { display: inline-block; padding: 12px 24px; background-color: #741B6B; color: #ffffff; text-decoration: none; border-radius: 20px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Zelle Branded Header -->
        <div class="header" style="background-color: #741B6B; background: linear-gradient(135deg, #741B6B 0%, #4B1045 100%); padding: 35px 20px; text-align: center;">
            <img src="{{ asset('assets/external/images/zelle logo2025.png') }}" alt="Zelle" style="max-height: 40px; filter: brightness(0) invert(1);">
        </div>
        
        <div class="content">
            <h1>We're processing your payment.</h1>
            <p>Hi {{ $user->first_name }},</p>
            <p>You recently sent a payment using Zelle®. The money is currently being processed and will be delivered to the recipient shortly. Please review the details of this transaction below.</p>
            
            <div style="text-align: center;">
                <div class="amount-large">{{ setting('currency_symbol', 'global') }}{{ number_format($transaction->amount, 2) }}</div>
                <p style="margin:0; color:#666;">Recipient:</p>
                @php $manual = json_decode($transaction->manual_field_data, true); @endphp
                <h3 style="margin-top:5px; color:#222;">{{ data_get($manual, 'zelle_contact', 'Recipient') }}</h3>
            </div>

            <div class="details-box">
                <div class="details-row">
                    <span class="label">Amount:</span>
                    <span class="value">{{ setting('currency_symbol', 'global') }}{{ number_format($transaction->amount, 2) }}</span>
                </div>
                <div class="details-row">
                    <span class="label">Date:</span>
                    <span class="value">{{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y h:i A') }}</span>
                </div>
                <div class="details-row">
                    <span class="label">Reference ID:</span>
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

            <p style="font-size: 14px; color: #666; margin-top: 30px;">
                <strong>Note:</strong> Zelle transactions cannot typically be canceled once they are sent. If you recognize this activity, no further action is required. If you did not authorize this payment, please contact our support team immediately.
            </p>
        </div>
        
        <div class="footer">
            <p>Zelle and the Zelle related marks are wholly owned by Early Warning Services, LLC and are used herein under license.</p>
            <p>&copy; {{ date('Y') }} {{ setting('site_title', 'global') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
