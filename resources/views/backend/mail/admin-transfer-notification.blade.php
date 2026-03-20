<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 20px auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden; }
        .header { background: #00549b; color: #fff; padding: 20px; text-align: center; }
        .content { padding: 30px; }
        .footer { background: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #777; }
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px dashed #eee; padding-bottom: 5px; }
        .label { font-weight: bold; color: #555; }
        .value { color: #000; }
        .badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-info { background: #e1f5fe; color: #0288d1; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0;">New Transfer Alert</h2>
        </div>
        <div class="content">
            <p>Hello Admin/Account Officer,</p>
            <p>A new transfer has been initiated on the platform. Please review the details below:</p>
            
            <div style="background: #fff8e1; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ffe082;">
                <div class="detail-row">
                    <span class="label">Transaction ID:</span>
                    <span class="value">#{{ $details['tnx'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">User:</span>
                    <span class="value">{{ $details['user_name'] }} ({{ $details['user_email'] }})</span>
                </div>
                <div class="detail-row">
                    <span class="label">Type:</span>
                    <span class="value"><span class="badge badge-info">{{ strtoupper($details['transfer_type']) }}</span></span>
                </div>
                <div class="detail-row">
                    <span class="label">Amount:</span>
                    <span class="value" style="font-size: 18px; font-weight: bold; color: #00549b;">{{ $details['amount'] }}</span>
                </div>
            </div>

            <h4 style="border-bottom: 2px solid #00549b; padding-bottom: 5px;">Recipient Details</h4>
            <div class="detail-row">
                <span class="label">Account Holder:</span>
                <span class="value">{{ $details['recipient_name'] }}</span>
            </div>
            @if(isset($details['routing_number']))
            <div class="detail-row">
                <span class="label">Routing Number:</span>
                <span class="value">{{ $details['routing_number'] }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="label">Account Number:</span>
                <span class="value">{{ $details['account_number'] }}</span>
            </div>
            <div class="detail-row" style="margin-top: 20px;">
                <span class="label">Status:</span>
                <span class="value" style="color: #f57c00; font-weight: bold;">PENDING REVIEW</span>
            </div>

            <p style="margin-top: 30px;">
                <a href="{{ $details['admin_url'] }}" style="background: #00549b; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">View in Admin Panel</a>
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }} Security Systems. This is an automated notification.</p>
        </div>
    </div>
</body>
</html>
