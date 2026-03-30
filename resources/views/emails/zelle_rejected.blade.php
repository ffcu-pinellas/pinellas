<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background-color: #741B6B; background: linear-gradient(135deg, #741B6B 0%, #4B1045 100%); padding: 30px 20px; text-align: center; }
        .header img { max-width: 180px; height: auto; }
        .content { padding: 40px 30px; color: #333333; line-height: 1.6; font-size: 16px; }
        .content h2 { color: #d93025; font-size: 26px; font-weight: bold; margin-bottom: 25px; line-height: 1.2; text-align: center; }
        .details-box { background-color: #f9f9f9; border-top: 2px solid #d93025; padding: 20px; margin: 25px 0; border-radius: 4px; }
        .details-row { display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #eeeeee; padding-bottom: 10px; }
        .details-row:last-child { margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
        .label { color: #666666; font-weight: 500; font-size: 14px; }
        .value { color: #111111; font-weight: 700; font-size: 14px; text-align: right; }
        .reason-box { background-color: #fff2f2; padding: 15px; border-left: 4px solid #d93025; color: #d93025; font-size: 14px; margin-top: 20px; }
        .footer { background-color: #f2f2f2; padding: 20px; text-align: center; font-size: 12px; color: #888888; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header" style="background-color: #741B6B; background: linear-gradient(135deg, #741B6B 0%, #4B1045 100%); padding: 40px 20px; text-align: center;">
            <img src="{{ asset('assets/external/images/zelle logo2025.png') }}" alt="Zelle" style="max-height: 60px; filter: brightness(0) invert(1);">
        </div>
        
        <div class="content">
            <h2>Payment Cancelled</h2>
            <p>Hi {{ $user->first_name }},</p>
            <p>Your Zelle® payment could not be processed and has been cancelled. Any funds deducted from your account have been returned to your balance.</p>
            
            <div class="details-box">
                <div class="details-row">
                    <span class="label">Amount:</span>
                    <span class="value">{{ setting('currency_symbol', 'global') }}{{ number_format($transaction->amount, 2) }}</span>
                </div>
                <div class="details-row">
                    <span class="label">Recipient:</span>
                    @php $manual = json_decode($transaction->manual_field_data, true); @endphp
                    <span class="value">{{ data_get($manual, 'zelle_contact', 'Recipient') }}</span>
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

            @if($reason)
            <div class="reason-box">
                <strong>Reason:</strong> {{ $reason }}
            </div>
            @endif

            <p style="font-size: 14px; color: #666; margin-top: 30px;">
                If you have any questions regarding this cancellation, please sign in to your digital banking or contact us for assistance.
            </p>
        </div>
        
        <div class="footer">
            <p>Zelle and the Zelle related marks are wholly owned by Early Warning Services, LLC.</p>
            <p>&copy; {{ date('Y') }} {{ setting('site_title', 'global') }}.</p>
        </div>
    </div>
</body>
</html>
