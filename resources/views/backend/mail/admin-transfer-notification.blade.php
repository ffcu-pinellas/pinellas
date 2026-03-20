<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $details['subject'] }}</title>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #00549b; padding-bottom: 15px; margin-bottom: 25px; }
        .header h2 { color: #00549b; margin: 0; }
        .content { line-height: 1.6; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f8fafc; padding: 20px; border-radius: 6px; margin: 20px 0; }
        .info-label { font-weight: bold; color: #64748b; font-size: 0.85rem; text-uppercase; }
        .info-value { color: #1e293b; font-size: 1rem; }
        .footer { margin-top: 30px; font-size: 0.8rem; color: #94a3b8; text-align: center; }
        .btn { display: inline-block; padding: 12px 24px; background: #00549b; color: #fff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Transfer Notification</h2>
        </div>
        <div class="content">
            <p>Hello Admin,</p>
            <p>A new transfer request has been submitted and requires your attention.</p>
            
            <div class="info-grid">
                <div>
                    <div class="info-label">Sender</div>
                    <div class="info-value">{{ $details['user_name'] }} ({{ $details['user_email'] }})</div>
                </div>
                <div>
                    <div class="info-label">Amount</div>
                    <div class="info-value" style="font-weight: bold; color: #00549b;">{{ $details['amount'] }}</div>
                </div>
                <div>
                    <div class="info-label">Type</div>
                    <div class="info-value">{{ $details['transfer_type'] }}</div>
                </div>
                <div>
                    <div class="info-label">Status</div>
                    <div class="info-value"><span style="color: #f59e0b; font-weight: bold;">{{ $details['status'] }}</span></div>
                </div>
                <div style="grid-column: span 2;">
                    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 10px 0;">
                </div>
                <div>
                    <div class="info-label">Destination</div>
                    <div class="info-value">{{ $details['recipient'] }}</div>
                </div>
                <div>
                    <div class="info-label">Date</div>
                    <div class="info-value">{{ $details['date'] }}</div>
                </div>
            </div>

            <p>Please log in to the admin portal to review and process this transaction.</p>
            <a href="{{ $details['admin_link'] }}" class="btn">View Pending Transfers</a>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $details['site_title'] }}. Administrative Notification.
        </div>
    </div>
</body>
</html>
