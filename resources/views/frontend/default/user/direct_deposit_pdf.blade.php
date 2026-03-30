<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Direct Deposit Authorization - Pinellas FCU</title>
    <style>
        @page { margin: 0.5in; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.4; margin: 0; padding: 0; background: white; }
        .container { padding: 20px; }
        .header { border-bottom: 2px solid #00549b; padding-bottom: 15px; margin-bottom: 20px; box-sizing: border-box; }
        .logo { width: 220px; display: block; margin-bottom: 10px; }
        .bank-info { text-align: right; font-size: 11px; color: #666; position: absolute; top: 20px; right: 20px; }
        .title { text-align: center; font-size: 24px; font-weight: bold; color: #00549b; margin: 30px 0; text-transform: uppercase; letter-spacing: 1px; }
        
        .section { margin-bottom: 25px; clear: both; }
        .section-title { font-size: 14px; font-weight: bold; color: #00549b; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 15px; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        td { padding: 8px 0; font-size: 13px; vertical-align: top; }
        .label { font-weight: bold; width: 35%; color: #555; }
        .value { border-bottom: 1px solid #ccc; font-weight: 500; }
        
        .authorization-text { font-size: 12px; text-align: justify; margin-bottom: 25px; color: #444; line-height: 1.6; }
        
        .signature-section { margin-top: 40px; }
        .sig-line { border-top: 1px solid #333; width: 60%; display: inline-block; }
        .date-line { border-top: 1px solid #333; width: 30%; float: right; }
        .sig-label { font-size: 10px; color: #666; padding-top: 5px; text-transform: uppercase; font-weight: bold; }

        /* Page 2 - Void Check */
        .page-break { page-break-before: always; }
        
        .check-wrapper {
            margin-top: 50px;
            border: 1px solid #ccc;
            border-radius: 12px;
            padding: 25px;
            background: #fff;
            width: 100%;
            height: 3.5in;
            box-sizing: border-box;
            position: relative;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            background-image: linear-gradient(rgba(0, 84, 155, 0.03) 1px, transparent 1px);
            background-size: 100% 20px;
        }

        .void-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 120px;
            color: rgba(0, 0, 0, 0.04);
            font-weight: 900;
            z-index: 0;
            white-space: nowrap;
            letter-spacing: 15px;
            pointer-events: none;
        }

        .check-header { margin-bottom: 30px; position: relative; z-index: 1; }
        .check-member-name { font-size: 14px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .check-member-addr { font-size: 11px; margin: 2px 0; color: #555; width: 250px; }
        .check-number { position: absolute; right: 0; top: 0; font-size: 18px; font-weight: bold; color: #333; }
        .check-date-box { position: absolute; right: 0; top: 35px; border-bottom: 1px solid #333; width: 140px; text-align: center; padding-bottom: 2px; font-size: 12px; }
        
        .pay-row { margin-top: 20px; border-bottom: 1px solid #333; height: 35px; position: relative; }
        .pay-label { font-size: 12px; font-weight: bold; position: absolute; bottom: 5px; left: 0; }
        .pay-value { font-size: 14px; font-weight: bold; position: absolute; bottom: 5px; left: 160px; color: #888; }
        .amount-box { border: 2px solid #333; background: white; padding: 5px 15px; position: absolute; right: 0; top: -5px; width: 120px; line-height: 1.5; }
        
        .memo-row { margin-top: 50px; position: relative; }
        .memo-line { border-bottom: 1px solid #333; width: 250px; padding-bottom: 2px; font-size: 11px; }
        .sig-line-check { border-bottom: 1px solid #333; width: 300px; float: right; position: relative; top: -5px; }

        .micr-line-final {
            position: absolute;
            bottom: 20px;
            left: 50px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 22px;
            letter-spacing: 5px;
            font-weight: bold;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- PAGE 1: Authorization Letter -->
        <div class="header">
            @if(isset($logoBase64) && $logoBase64)
                <img src="{{ $logoBase64 }}" class="logo">
            @else
                <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" class="logo">
            @endif
            <div class="bank-info">
                Pinellas Federal Credit Union<br>
                P.O. Box 2270<br>
                Largo, FL 33779-2270<br>
                (727) 586-4422 | Pinellasfcu.org
            </div>
        </div>

        <div class="title">Direct Deposit Authorization</div>

        <div class="authorization-text">
            I hereby authorize my employer/payer, listed below, to deposit my net pay or a portion thereof automatically to my account(s) at Pinellas Federal Credit Union each pay period. This authorization is to remain in effect until Pinellas Federal Credit Union has received written notification from me of its termination in such time as to afford Pinellas Federal Credit Union and my employer a reasonable opportunity to act on it.
        </div>

        <div class="section">
            <div class="section-title">Employer / Payer Information</div>
            <table>
                <tr>
                    <td class="label">Employer / Payer Name:</td>
                    <td class="value">_____________________________________________________</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Employee / Member Information</div>
            <table>
                <tr>
                    <td class="label">Member Name:</td>
                    <td class="value">{{ $user->full_name }}</td>
                </tr>
                <tr>
                    <td class="label">Social Security Number:</td>
                    <td class="value">{{ $ssn ?? '*** - ** - ****' }}</td>
                </tr>
                <tr>
                    <td class="label">Member Address:</td>
                    <td class="value">{{ $fullAddress ?? $user->address }}</td>
                </tr>
                @if($user->phone)
                <tr>
                    <td class="label">Phone Number:</td>
                    <td class="value">{{ $user->phone }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="section">
            <div class="section-title">Account Details</div>
            <table>
                <tr>
                    <td class="label">Financial Institution:</td>
                    <td class="value">Pinellas Federal Credit Union</td>
                </tr>
                <tr>
                    <td class="label">Routing Number (RTN):</td>
                    <td class="value" style="font-weight: bold; color: #00549b;">{{ $routingNumber }}</td>
                </tr>
                <tr>
                    <td class="label">Account Number:</td>
                    <td class="value" style="font-weight: bold; color: #00549b; font-family: 'JetBrains Mono', monospace; font-size: 15px;">{{ $accountNumber }}</td>
                </tr>
                <tr>
                    <td class="label">Account Type:</td>
                    <td class="value">{{ $accountTitle }}</td>
                </tr>
                <tr>
                    <td class="label">Deposit Amount:</td>
                    <td class="value">FULL NET PAY (OR $___________ / ________%)</td>
                </tr>
            </table>
        </div>

        <div class="signature-section">
            <div class="sig-line">
                <div class="sig-label">Member Signature</div>
            </div>
            <div class="date-line">
                <div class="sig-label">Date</div>
            </div>
        </div>

        <!-- PAGE 2: Void Check Graphic -->
        <div class="page-break"></div>
        
        <div class="section-title" style="margin-top: 30px;">Member Voided Check (Direct Deposit Reference Only)</div>
        
        <div class="check-wrapper">
            <div class="void-overlay">VOID</div>
            
            <div class="check-header">
                <div class="check-member-name">{{ $user->full_name }}</div>
                <div class="check-member-addr">
                    {{ $user->address }}<br>
                    {{ $user->city }}, {{ $user->zip_code }}
                </div>
                <div class="check-number">0001</div>
                <div class="check-date-box">DATE: ________________</div>
            </div>

            <div class="pay-row">
                <div class="pay-label">PAY TO THE<br>ORDER OF</div>
                <div class="pay-value">VOID - NOT FOR NEGOTIATION</div>
                <div class="amount-box">
                    <span style="font-size: 14px; color: #333;">$</span>
                    <span style="float: right; color: #999; font-weight: bold;">**VOID**</span>
                </div>
            </div>

            <div class="memo-row">
                <div style="font-size: 14px; font-weight: bold; color: #00549b; margin-bottom: 5px;">Pinellas Federal Credit Union</div>
                <div class="memo-line">MEMO: <u>Direct Deposit Authorization</u></div>
                <div class="sig-line-check"></div>
                <div style="float: right; font-size: 10px; text-transform: uppercase; font-weight: bold; margin-top: 5px; margin-right: 20px;">MP SIGNATURE REQUIRED</div>
            </div>

            <div class="micr-line-final">
                ⑆{{ $routingNumber }}⑆  {{ $accountNumber }}⑈  0001
            </div>
        </div>

        <p style="font-size: 10px; color: #999; text-align: center; margin-top: 40px; border-top: 1px dotted #ccc; padding-top: 10px;">
            This document is a formal authorization request generated via Pinellas FCU Digital Banking on {{ date('M d, Y') }}.<br>
            Financial Institution: Pinellas Federal Credit Union | Routing (ABA): {{ $routingNumber }}
        </p>
    </div>
</body>
</html>
