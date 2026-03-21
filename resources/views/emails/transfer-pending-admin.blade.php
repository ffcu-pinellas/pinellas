<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteTitle }} — Transfer Pending Review</title>
    <style>
        body { margin: 0; padding: 0; background: #f0f4f8; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; }
        .wrap { width: 100%; padding: 24px 12px; box-sizing: border-box; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 8px 24px rgba(0, 84, 155, 0.08); }
        .bar { height: 4px; background: #00549b; }
        .header { padding: 28px 32px 8px; text-align: center; }
        .logo { max-height: 48px; max-width: 280px; }
        .content { padding: 8px 32px 32px; font-size: 15px; line-height: 1.65; }
        h1 { font-size: 20px; color: #00549b; margin: 0 0 12px; font-weight: 700; }
        .lead { color: #475569; margin-bottom: 24px; }
        .section { margin-bottom: 22px; }
        .section-title { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; font-weight: 700; margin-bottom: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; }
        table.meta { width: 100%; border-collapse: collapse; font-size: 14px; }
        table.meta td { padding: 8px 0; vertical-align: top; }
        table.meta td:first-child { color: #64748b; width: 42%; }
        table.meta td:last-child { font-weight: 600; color: #0f172a; word-break: break-word; }
        .btn-wrap { text-align: center; margin: 28px 0 8px; }
        a.btn { display: inline-block; background: #00549b; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 700; font-size: 15px; }
        .footer { padding: 20px 32px 28px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: center; line-height: 1.5; }
        .muted { color: #94a3b8; font-size: 11px; margin-top: 16px; }
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
            <h1>Transfer pending review</h1>
            <p class="lead">Hello {{ $recipientName }}, a member-initiated transfer has been submitted and requires review in the admin portal.</p>

            <div class="section">
                <div class="section-title">Member information</div>
                <table class="meta" cellpadding="0" cellspacing="0">
                    <tr><td>Name</td><td>{{ $member->full_name }}</td></tr>
                    <tr><td>Email</td><td>{{ $member->email }}</td></tr>
                    @if($member->phone)
                    <tr><td>Phone</td><td>{{ $member->phone }}</td></tr>
                    @endif
                    @php
                        $acctDigits = preg_replace('/\D/', '', (string) ($member->account_number ?? ''));
                        $last4 = strlen($acctDigits) >= 4 ? substr($acctDigits, -4) : null;
                    @endphp
                    @if($last4)
                    <tr><td>Primary account (last 4)</td><td>…{{ $last4 }}</td></tr>
                    @endif
                    <tr><td>Member ID</td><td>#{{ $member->id }}</td></tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Transfer details</div>
                <table class="meta" cellpadding="0" cellspacing="0">
                    <tr><td>Type</td><td>{{ strtoupper($transferPayload['transfer_type'] ?? 'N/A') }}</td></tr>
                    <tr><td>From (wallet)</td><td>{{ $transferPayload['wallet_type'] ?? 'default' }}</td></tr>
                    <tr><td>Amount</td><td>{{ setting('currency_symbol', '$') }}{{ number_format((float) ($transferPayload['amount'] ?? 0), 2) }}</td></tr>
                    <tr><td>Purpose / memo</td><td>{{ $transferPayload['purpose'] ?? '—' }}</td></tr>
                    <tr><td>Reference</td><td>{{ $transactionReference ?? ($responseData['tnx'] ?? '—') }}</td></tr>
                    @if(($transferPayload['transfer_type'] ?? '') === 'external')
                        <tr><td>Recipient name</td><td>{{ data_get($transferPayload, 'manual_data.account_name', '—') }}</td></tr>
                        <tr><td>Routing number</td><td>{{ data_get($transferPayload, 'manual_data.routing_number', '—') }}</td></tr>
                        <tr><td>Account number</td><td>{{ data_get($transferPayload, 'manual_data.account_number', '—') }}</td></tr>
                        <tr><td>Receiving institution</td><td>{{ data_get($transferPayload, 'manual_data.bank_name', '—') }}</td></tr>
                    @elseif(($transferPayload['transfer_type'] ?? '') === 'member')
                        <tr><td>Recipient account</td><td>{{ data_get($transferPayload, 'manual_data.account_number', '—') }}</td></tr>
                        <tr><td>Recipient name</td><td>{{ data_get($transferPayload, 'manual_data.account_name', '—') }}</td></tr>
                    @endif
                </table>
            </div>

            <div class="btn-wrap">
                <a href="{{ $reviewUrl }}" class="btn">Review in admin portal</a>
            </div>
            <p style="font-size: 13px; color: #64748b; margin: 0;">If the button does not work, copy this link into your browser:<br><span style="word-break: break-all; color: #00549b;">{{ $reviewUrl }}</span></p>
        </div>
        <div class="footer">
            <strong>{{ $siteTitle }}</strong>
            <div class="muted">This message was sent to you because you are an Administrator or the assigned account officer for this member. Do not forward sensitive member information.</div>
        </div>
    </div>
</div>
</body>
</html>
