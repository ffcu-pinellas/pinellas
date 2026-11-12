<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Regulation E Dispute Notice - {{ $dispute->claim_number }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #1e293b; font-size: 12px; line-height: 1.5; margin: 28px; }
        .header { border-bottom: 3px solid #00549b; padding-bottom: 12px; margin-bottom: 18px; }
        .brand { font-size: 18px; font-weight: bold; color: #00549b; }
        .subtitle { font-size: 11px; color: #64748b; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 11px; background: #e0f2fe; color: #0369a1; }
        table.meta { width: 100%; border-collapse: collapse; margin: 14px 0 18px 0; }
        table.meta th, table.meta td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; }
        table.meta th { width: 32%; background: #f8fafc; color: #475569; font-weight: bold; }
        .box { background: #f8fafc; border-left: 4px solid #00549b; padding: 10px 14px; margin-bottom: 16px; }
        .timeline-item { border-bottom: 1px solid #e2e8f0; padding: 6px 0; }
        .footer { margin-top: 26px; font-size: 10px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">{{ setting('site_title', 'global') ?: 'FrontField Credit Union' }} — Member Protection & Dispute Operations</div>
        <div class="subtitle">Official Regulation E Electronic Funds Transfer Error &amp; Dispute Notice</div>
    </div>

    <p>
        <strong>Claim Reference:</strong> #{{ $dispute->claim_number }} &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Status:</strong> <span class="badge">{{ $dispute->status_label }}</span>
    </p>

    <table class="meta">
        <tr><th>Member Name</th><td>{{ $dispute->user?->full_name }} ({{ '@' . ($dispute->user?->username ?? 'member') }})</td></tr>
        <tr><th>Account / Wallet</th><td>{{ $walletLabel }}</td></tr>
        <tr><th>Disputed Transaction ID</th><td>{{ $dispute->tnx }}</td></tr>
        <tr><th>Payee / Description</th><td>{{ $dispute->merchant_name ?: ($dispute->transaction?->description ?? 'N/A') }}</td></tr>
        <tr><th>Reg E Dispute Category</th><td>{{ $dispute->category_label }}</td></tr>
        <tr><th>Disputed Amount</th><td><strong>${{ number_format((float) $dispute->disputed_amount, 2) }}</strong></td></tr>
        <tr><th>Filed Date</th><td>{{ $dispute->created_at?->format('M d, Y H:i T') }}</td></tr>
        @if($dispute->provisional_credit_tnx)
            <tr><th>Provisional / Refund Ref</th><td>{{ $dispute->provisional_credit_tnx }}</td></tr>
        @endif
        @if($dispute->clawback_tnx)
            <tr><th>Reversal / Clawback Ref</th><td>{{ $dispute->clawback_tnx }}</td></tr>
        @endif
    </table>

    <div class="box">
        <strong>Member Statement:</strong><br>
        {{ $dispute->description }}
    </div>

    @if($dispute->resolution_notes)
        <div class="box">
            <strong>Investigation Determination / Notes:</strong><br>
            {{ $dispute->resolution_notes }}
        </div>
    @endif

    <h4 style="margin-bottom: 6px; color: #00549b;">Case Audit Timeline</h4>
    @foreach(($dispute->timeline ?? []) as $event)
        <div class="timeline-item">
            <strong>{{ $event['event'] ?? 'Update' }}</strong> — {{ $event['at'] ?? '' }} ({{ $event['actor'] ?? 'System' }})<br>
            <span style="color: #475569;">{{ $event['note'] ?? '' }}</span>
        </div>
    @endforeach

    <div class="footer">
        Issued pursuant to 12 CFR Part 1005 (Regulation E). Provisional credits remain subject to final verification and reversal if the transaction is determined authorized or valid.
    </div>
</body>
</html>

