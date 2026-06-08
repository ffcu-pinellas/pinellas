<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaction Receipt - {{ $siteTitle }}</title>
    <style>
        /* PDF Page Setup */
        @page {
            margin: 45pt;
            size: letter;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 0;
            line-height: 1.4;
            background: #fff;
        }
        
        /* Header */
        .header {
            margin-bottom: 25pt;
            padding-bottom: 15pt;
            border-bottom: 1pt solid #e2e8f0;
            position: relative;
        }
        
        .logo {
            height: 40pt;
            max-width: 200pt;
        }
        
        .zelle-logo-top {
            position: absolute;
            top: 5pt;
            right: 0;
            height: 22pt;
        }

        /* Title Section */
        .receipt-header {
            margin-bottom: 20pt;
        }
        
        .receipt-title {
            font-size: 22pt;
            font-weight: bold;
            color: #00549b;
            margin: 0;
            letter-spacing: -0.01em;
        }
        
        .receipt-subtitle {
            font-size: 10pt;
            color: #64748b;
            margin-top: 4pt;
        }

        /* Amount Box */
        .amount-card {
            background: #f8fafc;
            border: 1pt solid #e2e8f0;
            border-radius: 10pt;
            padding: 20pt;
            text-align: center;
            margin-bottom: 25pt;
        }
        
        .amount-label {
            font-size: 9pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.08em;
            margin-bottom: 6pt;
        }
        
        .amount-value {
            font-size: 32pt;
            font-weight: bold;
            color: #00549b;
            margin: 0;
        }

        /* Details Table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30pt;
            table-layout: fixed;
        }
        
        .details-table td {
            padding: 12pt 0;
            border-bottom: 0.5pt solid #f1f5f9;
        }
        
        .details-table tr:last-child td {
            border-bottom: none;
        }
        
        .label {
            width: 40%;
            font-size: 10pt;
            color: #64748b;
            font-weight: bold;
            vertical-align: top;
        }
        
        .value {
            width: 60%;
            font-size: 11pt;
            color: #0f172a;
            font-weight: bold;
            text-align: right;
            word-wrap: break-word;
        }
        
        /* Status Badges */
        .status-success { color: #059669; }
        .status-pending { color: #d97706; }
        .status-failed { color: #dc2626; }

        /* Footer */
        .footer {
            margin-top: 40pt;
            padding-top: 15pt;
            border-top: 1.5pt solid #00549b;
            text-align: center;
        }
        
        .footer-text {
            font-size: 8.5pt;
            color: #64748b;
            line-height: 1.4;
        }
        
        .zelle-disclaimer {
            margin-top: 15pt;
            font-size: 8pt;
            color: #94a3b8;
            font-style: italic;
            text-align: center;
            line-height: normal;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(isset($logoBase64))
            <img src="{{ $logoBase64 }}" class="logo">
        @else
            <div style="font-size: 20pt; font-weight: bold; color: #00549b;">{{ strtoupper($siteTitle) }}</div>
        @endif

        @if(str_contains(strtolower($transaction->description), 'zelle'))
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAAsTAAALEwEAmpwYAAAByUlEQVR4nO2Yv0vDQBDHP0mDky0Ojk4ODo4OTg4OTg4OTg4OTg4OTg4OTg4OTg4Ojk4ODo4OTg4OTg4OTg4OTg4OTg4OTg4Ojk4ODo4OTv4D8X6I90f48fCH9/3uvTuue06WZR8U+I8BvANvCNoYQxjXGNoY8v8B9DH0MPQx9DD0MfQx9DD0MfQx9DD0MfQx9DD0MfQx9DD0MfQx9DD0MfQx9DD0MfQx9DD0MfQx9DD0MfQx9DH0MfQx9DH0MfQx9DH0MfQx9DH0MfQx9DH0MfQx9DH0MfQx9DH0MfQx9DH0MfQx9DH0MfQx9DH0MfQx9DH0MfQx9DH0MfQx9DH0MfQx9DH0MfQx9DGW78/AByGe78+In8fT/RmI9wPy7wfsXNifA669fD8g/z4kfB9ivA8Z+X6I789Idn8GuH7E+D4kWf4M98X3Ib7vX9/vW8f3Id7vcMv3Id7vaMv3Id7vaMv3Id7vaMv3Id7vaMv3Id7vaNf8v0J6MqZrkz9C+nKma5M/Qvpypm+88Z/zvcH/ANP+T8K3vLpGgAAAABJRU5ErkJggg==" class="zelle-logo-top">
        @endif
    </div>

    <div class="receipt-header">
        <h1 class="receipt-title">Transaction Receipt</h1>
        <div class="receipt-subtitle">This is an official record of your transaction with {{ $siteTitle }}.</div>
    </div>

    <div class="amount-card">
        <div class="amount-label">Transaction Amount</div>
        <div class="amount-value">{{ setting('currency_symbol','$').number_format($transaction->amount, 2) }}</div>
    </div>

    <table class="details-table">
        <tr>
            <td class="label">Date & Time</td>
            <td class="value">{{ $transaction->created_at->format('M d, Y g:i A') }}</td>
        </tr>
        <tr>
            <td class="label">Description</td>
            <td class="value">{{ $transaction->description }}</td>
        </tr>
        <tr>
            <td class="label">Confirmation Number</td>
            <td class="value" style="font-family: monospace;">{{ $transaction->tnx }}</td>
        </tr>
        <tr>
            <td class="label">From Account</td>
            <td class="value">
                @if($transaction->wallet_type == 'primary_savings' || $transaction->wallet_type == 'savings')
                    Savings Account (...{{ substr($user->savings_account_number ?? $user->account_number, -4) }}S)
                @elseif($transaction->wallet_type == 'heloc')
                    HELOC (...{{ substr($user->heloc_account_number ?? $user->account_number, -4) }}H)
                @elseif($transaction->wallet_type == 'cc')
                    Credit Card (...{{ substr($user->cc_account_number ?? $user->account_number, -4) }}C)
                @else
                    Checking Account (...{{ substr($user->account_number, -4) }})
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Current Status</td>
            <td class="value status-{{ $transaction->status->value }}">
                {{ ucfirst($transaction->status->value) }}
            </td>
        </tr>
        @if($transaction->charge > 0)
        <tr>
            <td class="label">Processing Fee</td>
            <td class="value">{{ setting('currency_symbol','$').number_format($transaction->charge, 2) }}</td>
        </tr>
        @endif
    </table>

    @if(str_contains(strtolower($transaction->description), 'zelle'))
        <div class="zelle-disclaimer">
            Zelle® and the Zelle® related marks are wholly owned by Early Warning Services, LLC and are used herein under license.
        </div>
    @endif

    <div class="footer">
        <div class="footer-text">
            {{ $siteTitle }} • Corporate Offices • P.O. Box 2300 • Cleveland, OH 44166<br>
            Federally insured by NCUA. Equal Housing Lender. © {{ date('Y') }} All Rights Reserved.
        </div>
    </div>
</body>
</html>
