<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>eStatement - {{ $user->full_name }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #334155;
            line-height: 1.5;
            margin: 0;
            padding: 40px;
            background-color: #ffffff;
            position: relative;
        }
        /* Watermark Styling */
        .watermark {
            position: fixed;
            top: 25%;
            left: 10%;
            width: 80%;
            opacity: 0.24;
            z-index: -1000;
            text-align: center;
        }
        .watermark img {
            width: 500px;
        }
        .header-container {
            border-bottom: 2px solid #00549b;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            float: left;
            height: 55px;
        }
        .bank-details {
            float: right;
            text-align: right;
            font-size: 10px;
            color: #4a5568;
            line-height: 1.3;
        }
        .clear {
            clear: both;
        }
        .statement-banner {
            background: #f7fafc;
            padding: 15px 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }
        .statement-banner h1 {
            color: #00549b;
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .statement-banner p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #718096;
            font-weight: bold;
        }
        .meta-grid {
            width: 100%;
            margin-bottom: 30px;
        }
        .meta-box {
            width: 48%;
            vertical-align: top;
        }
        .section-title {
            font-size: 10px;
            font-weight: 800;
            color: #00549b;
            text-transform: uppercase;
            margin-bottom: 8px;
            border-bottom: 1px solid #edf2f7;
            padding-bottom: 4px;
        }
        .meta-content {
            font-size: 11px;
            color: #1a202c;
        }
        .summary-box {
            background: #00549b;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .summary-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 8px;
        }
        .summary-grid {
            width: 100%;
        }
        .summary-item {
            padding: 5px 0;
        }
        .summary-label {
            font-size: 10px;
            opacity: 0.8;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
        }
        .transactions-title {
            font-size: 14px;
            font-weight: bold;
            color: #00549b;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .txn-table {
            width: 100%;
            border-collapse: collapse;
        }
        .txn-table th {
            text-align: left;
            font-size: 10px;
            font-weight: 800;
            color: #4a5568;
            text-transform: uppercase;
            padding: 10px;
            background: #edf2f7;
            border-bottom: 2px solid #cbd5e0;
        }
        .txn-table td {
            padding: 12px 10px;
            font-size: 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .txn-table tr:nth-child(even) {
            background: #f8fafc;
        }
        .amount-col {
            text-align: right;
            font-weight: bold;
            font-size: 11px;
        }
        .text-success { color: #2f855a; }
        .text-danger { color: #c53030; }
        .footer-notice {
            position: fixed;
            bottom: 40px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 9px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="watermark">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}">
        @else
            <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png">
        @endif
    </div>

    <div class="header-container">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="logo">
        @else
            <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" class="logo">
        @endif
        <div class="bank-details">
            <strong>Pinellas Federal Credit Union</strong><br>
            Corporate Offices • P.O. Box 2500<br>
            Largo, FL 33779-2500<br>
            (737) 410-5689 | pinellascu.com
        </div>
        <div class="clear"></div>
    </div>

    <div class="statement-banner">
        <h1>Account Statement</h1>
        <p>FOR THE PERIOD: {{ strtoupper($from_date->format('M d, Y')) }} — {{ strtoupper($to_date->format('M d, Y')) }}</p>
    </div>

    <table class="meta-grid">
        <tr>
            <td class="meta-box">
                <div class="section-title">Member Information</div>
                <div class="meta-content">
                    <strong style="font-size: 13px;">{{ strtoupper($user->full_name) }}</strong><br>
                    {{ $user->address ?: 'NO ADDRESS ON FILE' }}<br>
                    {{ $user->city ?: '' }} {{ $user->zip_code ?: '' }}
                </div>
            </td>
            <td class="meta-box" style="padding-left: 4%;">
                <div class="section-title">Statement Summary</div>
                <div class="meta-content">
                    @foreach($selectedAccounts as $accType)
                        @if(isset($maskedAccounts[$accType]))
                            <strong>{{ ucfirst($accType) }}:</strong> {{ $maskedAccounts[$accType] }}<br>
                        @endif
                    @endforeach
                    <strong>Statement Date:</strong> {{ now()->format('M d, Y') }}
                </div>
            </td>
        </tr>
    </table>

    <div class="summary-box">
        <div class="summary-title">Portfolio Summary</div>
        <table class="summary-grid">
            <tr>
                @if(in_array('checking', $selectedAccounts))
                <td class="summary-item">
                    <div class="summary-label">Checking Balance</div>
                    <div class="summary-value">{{ setting('currency_symbol', 'global') }}{{ number_format($user->balance, 2) }}</div>
                </td>
                @endif
                @if(in_array('savings', $selectedAccounts) && $user->savings_account_number)
                <td class="summary-item">
                    <div class="summary-label">Savings Balance</div>
                    <div class="summary-value">{{ setting('currency_symbol', 'global') }}{{ number_format($user->savings_balance, 2) }}</div>
                </td>
                @endif
                @if(in_array('ira', $selectedAccounts) && $user->ira_account_number)
                <td class="summary-item">
                    <div class="summary-label">IRA Balance</div>
                    <div class="summary-value">{{ setting('currency_symbol', 'global') }}{{ number_format($user->ira_balance, 2) }}</div>
                </td>
                @endif
                @if(in_array('heloc', $selectedAccounts) && $user->heloc_account_number)
                <td class="summary-item">
                    <div class="summary-label">HELOC Balance</div>
                    <div class="summary-value">{{ setting('currency_symbol', 'global') }}{{ number_format($user->heloc_balance, 2) }}</div>
                </td>
                @endif
            </tr>
            @if(in_array('cc', $selectedAccounts) || in_array('loan', $selectedAccounts))
            <tr>
                @if(in_array('cc', $selectedAccounts) && $user->cc_account_number)
                <td class="summary-item">
                    <div class="summary-label">Credit Card Balance</div>
                    <div class="summary-value">{{ setting('currency_symbol', 'global') }}{{ number_format($user->cc_balance, 2) }}</div>
                </td>
                @endif
                @if(in_array('loan', $selectedAccounts) && $user->loan_account_number)
                <td class="summary-item">
                    <div class="summary-label">Loan Balance</div>
                    <div class="summary-value">{{ setting('currency_symbol', 'global') }}{{ number_format($user->loan_balance, 2) }}</div>
                </td>
                @endif
                <td class="summary-item">
                    <div class="summary-label">Total Transactions</div>
                    <div class="summary-value">{{ count($transactions) }}</div>
                </td>
            </tr>
            @endif
        </table>
    </div>

    <div class="transactions-title">Transaction Detail</div>
    <table class="txn-table">
        <thead>
            <tr>
                <th width="12%">Date</th>
                <th>Description / Transaction ID</th>
                <th width="18%">Account Type</th>
                <th width="15%" style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->created_at->format('m/d/Y') }}</td>
                    <td>
                        <strong style="color: #1a202c;">{{ $transaction->description }}</strong><br>
                        <span style="color: #718096; font-size: 9px;">TNX: {{ $transaction->tnx }}</span>
                    </td>
                    <td>
                        <span style="background: #edf2f7; padding: 2px 6px; border-radius: 4px; font-weight: bold; color: #4a5568;">
                            {{ strtoupper(getAccountName($transaction->wallet_type)) }}
                        </span>
                    </td>
                    <td class="amount-col {{ isPlusTransaction($transaction->type) ? 'text-success' : 'text-danger' }}">
                        {{ isPlusTransaction($transaction->type) ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px; color: #a0aec0; font-style: italic;">
                        No transaction activity recorded for the selected criteria in this period.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-notice">
        This statement is an official record of your account activity. Please review it carefully. 
        Report any discrepancies within 60 days. <br>
        <strong>Pinellas Federal Credit Union is Federally Insured by NCUA. Equal Housing Lender.</strong>
    </div>
</body>
</html>
