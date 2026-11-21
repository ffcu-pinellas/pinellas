<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Opening Disclosure &amp; Tier Schedule</title>
    <style>
        @page {
            margin: 36px 40px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid {{ $primaryColor ?? '#00549b' }};
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .inst-title {
            font-size: 20px;
            font-weight: bold;
            color: {{ $primaryColor ?? '#00549b' }};
            letter-spacing: -0.5px;
        }
        .inst-meta {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 2px;
        }
        .doc-badge {
            background-color: {{ $primaryColor ?? '#00549b' }};
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 4px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 12px;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-subtitle {
            font-size: 9.5px;
            color: #64748b;
            margin-bottom: 15px;
        }
        .section-header {
            font-size: 11px;
            font-weight: bold;
            color: {{ $primaryColor ?? '#00549b' }};
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 14px;
            margin-bottom: 8px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-table td {
            padding: 5px 8px;
            vertical-align: top;
            font-size: 10.5px;
        }
        .info-label {
            width: 32%;
            font-weight: bold;
            color: #475569;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .info-value {
            width: 68%;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            font-family: monospace;
            font-size: 11px;
        }
        .limits-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 14px;
        }
        .limits-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        .limits-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }
        .notice-box {
            background-color: #f8fafc;
            border-left: 3px solid {{ $primaryColor ?? '#00549b' }};
            padding: 8px 12px;
            font-size: 9.5px;
            color: #475569;
            margin-top: 10px;
            margin-bottom: 14px;
            border-radius: 2px;
        }
        .footer-table {
            width: 100%;
            margin-top: 22px;
            border-top: 1px solid #cbd5e1;
            padding-top: 12px;
        }
        .sign-box {
            width: 48%;
            border-bottom: 1px solid #0f172a;
            padding-bottom: 30px;
            margin-bottom: 4px;
        }
        .sign-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
        }
        .legal-footer {
            margin-top: 15px;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
            line-height: 1.3;
        }
    </style>
</head>
<body>

    <!-- Header Table -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: middle;">
                <div class="inst-title">{{ $siteTitle }}</div>
                <div class="inst-meta">{{ $mailingAddress }} &bull; Routing (ABA): {{ $routingNumber }}</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <div class="doc-badge">Official Bank Document</div>
                <div style="font-size: 8.5px; color: #64748b; margin-top: 4px; font-family: monospace;">
                    REF: SEC-DISC-{{ strtoupper(substr(md5($checkingAccountNumber . date('Ymd')), 0, 10)) }}
                </div>
            </td>
        </tr>
    </table>

    <div class="doc-title">Account Opening Disclosure &amp; Electronic Banking Schedule</div>
    <div class="doc-subtitle">Consumer Deposit Account Agreement, Electronic Funds Transfer (EFT) Limits &amp; Funds Availability Schedule</div>

    <!-- Section 1: Member & Account Information -->
    <div class="section-header">1. Member &amp; Primary Account Identification</div>
    <table class="info-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="info-label">Account Holder Name:</td>
            <td class="info-value" style="font-family: inherit; font-weight: bold;">{{ $fullName }}</td>
        </tr>
        <tr>
            <td class="info-label">Relationship Banking Tier:</td>
            <td class="info-value" style="font-family: inherit; color: {{ $primaryColor ?? '#00549b' }}; font-weight: bold;">{{ $tierName }}</td>
        </tr>
        <tr>
            <td class="info-label">Primary Checking Account:</td>
            <td class="info-value">{{ $checkingAccountNumber }}</td>
        </tr>
        @if(!empty($savingsAccountNumber))
        <tr>
            <td class="info-label">Primary Savings Account:</td>
            <td class="info-value">{{ $savingsAccountNumber }}</td>
        </tr>
        @endif
        <tr>
            <td class="info-label">ABA Routing Transit Number:</td>
            <td class="info-value">{{ $routingNumber }}</td>
        </tr>
        <tr>
            <td class="info-label">Account Opening Date:</td>
            <td class="info-value" style="font-family: inherit;">{{ now()->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Account Status:</td>
            <td class="info-value" style="font-family: inherit; color: #16a34a; font-weight: bold;">Active &bull; Fully Operational</td>
        </tr>
    </table>

    <!-- Section 2: Electronic Transfer & Daily Tier Limit Schedule -->
    <div class="section-header">2. Electronic Banking &amp; Daily Tier Transaction Limits</div>
    <table class="limits-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 32%;">Service / Channel</th>
                <th style="width: 25%;">Daily Limit</th>
                <th style="width: 23%;">Settlement Speed</th>
                <th style="width: 20%;">Fee</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Internal Account Transfers</strong><br><span style="color:#64748b; font-size:8.5px;">Book transfer between checking, savings &amp; members</span></td>
                <td><strong>$100,000.00</strong> / business day</td>
                <td>Instant (Real-time 24/7)</td>
                <td>$0.00</td>
            </tr>
            <tr>
                <td><strong>Outgoing Domestic Fedwire</strong><br><span style="color:#64748b; font-size:8.5px;">Direct Federal Reserve wire transfer network</span></td>
                <td><strong>$100,000.00</strong> / business day</td>
                <td>Same-Day (Cutoff 3:00 PM EST)</td>
                <td>$0.00</td>
            </tr>
            <tr>
                <td><strong>External Bank ACH Transfer</strong><br><span style="color:#64748b; font-size:8.5px;">NACHA network electronic transfer</span></td>
                <td><strong>$25,000.00</strong> / business day</td>
                <td>1–2 Business Days</td>
                <td>$0.00</td>
            </tr>
            <tr>
                <td><strong>Mobile Remote Check Deposit</strong><br><span style="color:#64748b; font-size:8.5px;">Smartphone camera check capture (Reg CC)</span></td>
                <td><strong>$50,000.00</strong> / day<br><span style="color:#64748b; font-size:8.5px;">($150,000.00 rolling 30-day)</span></td>
                <td>1–2 Business Days</td>
                <td>$0.00</td>
            </tr>
            <tr>
                <td><strong>Visa® Debit Point-of-Sale</strong><br><span style="color:#64748b; font-size:8.5px;">Merchant signature &amp; PIN purchases</span></td>
                <td><strong>$5,000.00</strong> / calendar day</td>
                <td>Instant Authorization</td>
                <td>$0.00</td>
            </tr>
            <tr>
                <td><strong>ATM Cash Withdrawal</strong><br><span style="color:#64748b; font-size:8.5px;">In-network ATM cash disbursement</span></td>
                <td><strong>$2,500.00</strong> / calendar day</td>
                <td>Instant</td>
                <td>$0.00</td>
            </tr>
            <tr>
                <td><strong>Zelle® Instant Payments</strong><br><span style="color:#64748b; font-size:8.5px;">Send and receive funds via mobile number / email</span></td>
                <td><strong>$2,500.00</strong> / rolling 24h</td>
                <td>Minutes</td>
                <td>$0.00</td>
            </tr>
        </tbody>
    </table>

    <!-- Section 3: Funds Availability Notice -->
    <div class="section-header">3. Regulation CC Funds Availability Schedule</div>
    <div class="notice-box">
        <strong>Mobile Check Deposit Funds Availability:</strong> Deposits received and verified prior to 4:00 PM on any business day are considered received on that day. In accordance with Regulation CC, the first $225 of check deposits is available on the next business day. Funds exceeding $225 are generally available on the second business day following the day of deposit. Electronic direct deposits (ACH) and incoming wire transfers are available immediately on the day of receipt.
    </div>

    <!-- Section 4: Regulatory & Insurance Disclosure -->
    <div class="section-header">4. Regulatory Insurance &amp; Security Agreement</div>
    <div style="font-size: 9.5px; color: #475569; margin-bottom: 12px;">
        {{ $regulator }}. Your deposits are backed by the full faith and credit of the United States Government up to legal limits. Member agrees to safeguard all digital banking credentials, biometric passkeys, and two-factor authentication tokens. Report any unauthorized transactions or lost cards immediately by calling {{ $supportPhone ?? '216-230-1837' }}.
    </div>

    <!-- Signatures Table -->
    <table class="footer-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 50%; vertical-align: bottom;">
                <div style="font-family: 'Courier New', monospace; font-size: 13px; font-weight: bold; color: #1e3a8a; margin-bottom: 4px;">
                    &bull;&bull;&bull; ELECTRONICALLY VERIFIED &bull;&bull;&bull;
                </div>
                <div class="sign-label">Digital Banking Operations Officer</div>
                <div style="font-size: 8.5px; color: #64748b;">{{ $siteTitle }} Electronic Transfer Division</div>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: bottom;">
                <div style="display: inline-block; text-align: left;">
                    <div style="font-size: 10px; font-weight: bold; color: #0f172a;">Date of Issuance:</div>
                    <div style="font-size: 9.5px; color: #64748b; font-family: monospace;">{{ now()->format('Y-m-d H:i:s T') }}</div>
                    <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">Verification Stamp: VALIDATED</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="legal-footer">
        &copy; {{ date('Y') }} {{ $siteTitle }}. All rights reserved. This document is an electronic disclosure under the Electronic Signatures in Global and National Commerce Act (E-SIGN Act). Please retain this copy for your permanent tax and banking records.
    </div>

</body>
</html>