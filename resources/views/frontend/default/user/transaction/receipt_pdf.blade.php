<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaction Receipt - Pinellas FCU</title>
    <style>
        @page { margin: 0; padding: 0; size: 8.5in 11in; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; margin: 0; padding: 0; background: #fff; }
        .page-wrapper { width: 8.5in; height: 11in; position: relative; overflow: hidden; padding: 40pt; box-sizing: border-box; }
        
        /* Header */
        .header { border-bottom: 2pt solid #00549b; padding-bottom: 15pt; margin-bottom: 30pt; position: relative; }
        .logo { height: 35pt; }
        .zelle-logo { height: 22pt; position: absolute; top: 8pt; right: 0; }
        
        /* Content */
        .receipt-title { font-size: 22pt; font-weight: bold; color: #00549b; margin-bottom: 10pt; }
        .receipt-status { font-size: 11pt; color: #666; margin-bottom: 30pt; }
        
        .amount-box { background: #f8fbfe; border: 1pt solid #e1e8f0; border-radius: 8pt; padding: 20pt; text-align: center; margin-bottom: 30pt; }
        .amount-label { font-size: 10pt; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 5pt; }
        .amount-value { font-size: 32pt; font-weight: bold; color: #00549b; }

        table.details { width: 100%; border-collapse: collapse; margin-bottom: 40pt; }
        table.details td { padding: 12pt 0; border-bottom: 0.5pt solid #eee; font-size: 11pt; }
        .label { color: #64748b; width: 30%; font-weight: bold; }
        .value { color: #0f172a; font-weight: bold; text-align: right; }
        
        .footer { position: absolute; bottom: 40pt; left: 40pt; right: 40pt; border-top: 0.5pt solid #eee; pt: 15pt; text-align: center; font-size: 9pt; color: #94a3b8; }
        .zelle-disclaimer { margin-top: 20pt; font-style: italic; color: #64748b; font-size: 9pt; text-align: center; }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="header">
            @if(isset($logoBase64))
                <img src="{{ $logoBase64 }}" class="logo">
            @else
                <div style="font-size: 18pt; font-weight: bold; color: #00549b;">PINELLAS FCU</div>
            @endif

            @if(str_contains(strtolower($transaction->description), 'zelle'))
                <img src="data:image/png;base64,{{ base64_encode(curl_get_file_contents('https://www.pinellasfcu.org/templates/pinellas/images/zelle_logo.png')) }}" class="zelle-logo">
            @endif
        </div>

        <div class="receipt-title">Transaction Receipt</div>
        <div class="receipt-status">This is a formal record of your transaction with Pinellas Federal Credit Union.</div>

        <div class="amount-box">
            <div class="amount-label">Transaction Amount</div>
            <div class="amount-value">{{ setting('currency_symbol','$').number_format($transaction->amount, 2) }}</div>
        </div>

        <table class="details">
            <tr>
                <td class="label">Date</td>
                <td class="value">{{ $transaction->created_at->format('M d, Y g:i A') }}</td>
            </tr>
            <tr>
                <td class="label">Description</td>
                <td class="value">{{ $transaction->description }}</td>
            </tr>
            <tr>
                <td class="label">Confirmation #</td>
                <td class="value" style="font-family: monospace;">{{ $transaction->tnx }}</td>
            </tr>
            <tr>
                <td class="label">From Account</td>
                <td class="value">
                    @if($transaction->wallet_type == 'primary_savings' || $transaction->wallet_type == 'savings')
                        Savings (...{{ substr($user->savings_account_number ?? $user->account_number, -4) }}S)
                    @elseif($transaction->wallet_type == 'heloc')
                        HELOC (...{{ substr($user->heloc_account_number ?? $user->account_number, -4) }}H)
                    @elseif($transaction->wallet_type == 'cc')
                        Credit Card (...{{ substr($user->cc_account_number ?? $user->account_number, -4) }}C)
                    @else
                        Checking (...{{ substr($user->account_number, -4) }})
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="value" style="color: {{ $transaction->status->value == 'success' ? '#059669' : ($transaction->status->value == 'pending' ? '#d97706' : '#dc2626') }};">
                    {{ ucfirst($transaction->status->value) }}
                </td>
            </tr>
            @if($transaction->charge > 0)
            <tr>
                <td class="label">Fee</td>
                <td class="value">{{ setting('currency_symbol','$').number_format($transaction->charge, 2) }}</td>
            </tr>
            @endif
        </table>

        @if(str_contains(strtolower($transaction->description), 'zelle'))
            <div class="zelle-disclaimer">
                Zelle and the Zelle related marks are wholly owned by Early Warning Services, LLC and are used herein under license.
            </div>
        @endif

        <div class="footer">
            Pinellas Federal Credit Union • Corporate Offices • P.O. Box 2500 • Largo, FL 33779-2500<br>
            Federally insured by NCUA. Equal Housing Lender.
        </div>
    </div>
</body>
</html>
