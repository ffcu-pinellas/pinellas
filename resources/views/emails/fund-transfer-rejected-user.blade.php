<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteTitle }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f0f4f8; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; }
        .wrap { width: 100%; padding: 24px 12px; box-sizing: border-box; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06); }
        .bar { height: 4px; background: #b45309; }
        .header { padding: 24px 28px 8px; text-align: center; }
        .logo { max-height: 48px; max-width: 280px; }
        .content { padding: 8px 28px 28px; font-size: 15px; line-height: 1.65; color: #334155; }
        h1 { font-size: 20px; color: #0f172a; margin: 0 0 12px; font-weight: 700; }
        .intro { margin-bottom: 20px; color: #475569; }
        .reason-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 16px 18px; margin: 20px 0; color: #78350f; font-size: 14px; }
        .reason-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: #92400e; margin-bottom: 8px; }
        table.meta { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 8px; }
        table.meta td { padding: 8px 0; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        table.meta td:first-child { color: #64748b; width: 42%; }
        table.meta td:last-child { font-weight: 600; color: #0f172a; word-break: break-word; }
        .btn-wrap { text-align: center; margin: 28px 0 12px; }
        a.btn { display: inline-block; background: #00549b; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 700; font-size: 15px; }
        .footer { padding: 20px 28px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: center; line-height: 1.5; }
        .muted { font-size: 11px; color: #94a3b8; margin-top: 14px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="bar"></div>
        <div class="header">
            @if(!empty($siteLogoUrl))
                <a href="{{ $homeUrl }}"><img src="{{ $siteLogoUrl }}" alt="{{ $siteTitle }}" class="logo"></a>
            @else
                <div style="font-size: 18px; font-weight: 700; color: #00549b;">{{ $siteTitle }}</div>
            @endif
        </div>
        <div class="content">
            <h1>Transfer not approved</h1>
            <p class="intro">Hello {{ $member->full_name }},</p>
            <p class="intro">Your <strong>{{ strtolower($transferKind) }}</strong> request could not be approved at this time. Any funds held for this transfer have been returned to your available balance where applicable.</p>

            <div class="reason-box">
                <div class="reason-label">Reason provided</div>
                <div>{{ $rejectionReason }}</div>
            </div>

            <table class="meta" cellpadding="0" cellspacing="0">
                <tr><td>Transaction reference</td><td>{{ $transaction->tnx }}</td></tr>
                <tr><td>Amount</td><td>{{ setting('currency_symbol', '$') }}{{ number_format((float) $transaction->amount, 2) }}</td></tr>
                <tr><td>Date submitted</td><td>{{ $transaction->created_at->format('M j, Y \a\t g:i A') }}</td></tr>
                <tr><td>Recipient</td><td>{{ data_get($manualData, 'account_name', '—') }}</td></tr>
                @if($isExternal)
                    <tr><td>Receiving institution</td><td>{{ data_get($manualData, 'bank_name', '—') }}</td></tr>
                    <tr><td>Routing number</td><td>{{ data_get($manualData, 'routing_number', '—') }}</td></tr>
                @endif
            </table>

            <p style="font-size: 14px; color: #64748b; margin-top: 20px;">If you believe this is an error, or you need help submitting a new transfer, please sign in to online banking or contact member services.</p>

            <div class="btn-wrap">
                <a href="{{ $transferLogUrl }}" class="btn">View transfer activity</a>
            </div>
        </div>
        <div class="footer">
            <strong>{{ $siteTitle }}</strong>
            <div class="muted">For your security, we will never ask for your full account number, password, or PIN by email.</div>
        </div>
    </div>
</div>
</body>
</html>
