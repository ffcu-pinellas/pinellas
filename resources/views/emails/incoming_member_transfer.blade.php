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
        .bar { height: 4px; }
        .header { padding: 24px 28px 8px; text-align: center; }
        .logo { max-height: 48px; max-width: 280px; }
        .content { padding: 8px 28px 28px; font-size: 15px; line-height: 1.65; color: #334155; }
        h1 { font-size: 20px; color: #0f172a; margin: 0 0 12px; font-weight: 700; }
        .intro { margin-bottom: 20px; color: #475569; }
        
        .status-box { padding: 15px 18px; border-radius: 8px; margin: 20px 0; font-size: 14px; font-weight: 500; display: flex; align-items: center; }
        .status-box.success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .status-box.failed { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .status-box.pending { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
        
        .status-icon { margin-right: 12px; font-size: 18px; font-weight: bold; }
        
        table.meta { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 15px; margin-bottom: 15px; }
        table.meta td { padding: 10px 0; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        table.meta td:first-child { color: #64748b; width: 42%; }
        table.meta td:last-child { font-weight: 600; color: #0f172a; word-break: break-word; text-align: right; }
        
        .btn-wrap { text-align: center; margin: 28px 0 12px; }
        a.btn { display: inline-block; background: #00549b; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 6px rgba(0, 84, 155, 0.15); }
        .footer { padding: 20px 28px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: center; line-height: 1.5; }
        .muted { font-size: 11px; color: #94a3b8; margin-top: 14px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="bar" style="background: {{ $status === 'success' ? '#10b981' : ($status === 'failed' ? '#ef4444' : '#f59e0b') }};"></div>
        <div class="header">
            @if(!empty($siteLogoUrl))
                <a href="{{ $homeUrl }}"><img src="{{ $siteLogoUrl }}" alt="{{ $siteTitle }}" class="logo"></a>
            @else
                <div style="font-size: 18px; font-weight: 700; color: #00549b;">{{ $siteTitle }}</div>
            @endif
        </div>
        <div class="content">
            @if($status === 'success')
                <h1>Funds Credited</h1>
                <p class="intro">Hello {{ $receiver->first_name }},</p>
                <p class="intro">Good news! An incoming member-to-member transfer has been successfully credited to your account and is now available for use.</p>
                
                <div class="status-box success">
                    <span class="status-icon">✓</span>
                    <span>Funds deposited successfully.</span>
                </div>
            @elseif($status === 'failed')
                <h1>Transfer Declined</h1>
                <p class="intro">Hello {{ $receiver->first_name }},</p>
                <p class="intro">An incoming transfer initiated to your account by {{ $sender->full_name }} was declined and canceled by our compliance team.</p>
                
                <div class="status-box failed">
                    <span class="status-icon">✕</span>
                    <span>Transfer declined. No funds were credited.</span>
                </div>
            @else
                <h1>Pending Incoming Transfer</h1>
                <p class="intro">Hello {{ $receiver->first_name }},</p>
                <p class="intro">We wanted to let you know that {{ $sender->full_name }} has initiated an incoming member-to-member transfer to your account. This transfer is currently undergoing standard compliance verification.</p>
                
                <div class="status-box pending">
                    <span class="status-icon">⚠</span>
                    <span>Processing review. Funds will post upon clearance.</span>
                </div>
            @endif

            <table class="meta" cellpadding="0" cellspacing="0">
                <tr>
                    <td>Transaction Type</td>
                    <td>Incoming Member-to-Member Transfer</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td style="color: {{ $status === 'success' ? '#10b981' : ($status === 'failed' ? '#ef4444' : '#f59e0b') }};">
                        {{ $status === 'success' ? 'Completed / Credited' : ($status === 'failed' ? 'Declined / Canceled' : 'Pending Verification') }}
                    </td>
                </tr>
                <tr>
                    <td>Sender Name</td>
                    <td>{{ $sender->full_name }}</td>
                </tr>
                <tr>
                    <td>To Account</td>
                    <td>{{ $maskedAccount }}</td>
                </tr>
                <tr>
                    <td>Amount</td>
                    <td style="color: {{ $status === 'success' ? '#10b981' : '#0f172a' }};">
                        {{ setting('currency_symbol', '$') }}{{ number_format((float) $transaction->amount, 2) }}
                    </td>
                </tr>
                <tr>
                    <td>Date</td>
                    <td>{{ $transaction->created_at->format('M j, Y \a\t g:i A') }}</td>
                </tr>
                <tr>
                    <td>Reference ID</td>
                    <td>{{ $transaction->tnx }}</td>
                </tr>
                @if(!empty(trim((string) $memo)))
                    <tr>
                        <td>Memo</td>
                        <td>{{ $memo }}</td>
                    </tr>
                @endif
            </table>

            <p style="font-size: 14px; color: #64748b; margin-top: 20px;">
                You can view details and track your transactions by logging into your online banking portal at any time.
            </p>

            <div class="btn-wrap">
                <a href="{{ $transferLogUrl }}" class="btn">Access Online Banking</a>
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
