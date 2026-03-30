<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Direct Deposit Authorization - Pinellas FCU</title>
    <style>
        @page { margin: 0; padding: 0; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.4; margin: 0; padding: 40px; background: white; position: relative; }
        .container { position: relative; z-index: 1; }
        
        /* Watermark Styling (Matches Statement PDF) */
        .watermark {
            position: fixed;
            top: 25%;
            left: 10%;
            width: 80%;
            opacity: 0.08;
            z-index: -1000;
            text-align: center;
        }
        .watermark img { width: 500px; }

        .header { border-bottom: 2px solid #00549b; padding-bottom: 15px; margin-bottom: 20px; box-sizing: border-box; position: relative; overflow: hidden; }
        .logo { width: 220px; display: block; margin-bottom: 10px; }
        .bank-info { text-align: right; font-size: 11px; color: #666; position: absolute; top: 0; right: 0; line-height: 1.3; }
        .title { text-align: center; font-size: 26px; font-weight: bold; color: #00549b; margin: 30px 0; text-transform: uppercase; letter-spacing: 1.5px; }
        
        .section { margin-bottom: 25px; clear: both; }
        .section-title { font-size: 14px; font-weight: bold; color: #00549b; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 15px; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        td { padding: 8px 0; font-size: 13px; vertical-align: top; }
        .label { font-weight: bold; width: 35%; color: #555; }
        .value { border-bottom: 1px solid #ccc; font-weight: 500; font-size: 14px; }
        
        .authorization-text { font-size: 12px; text-align: justify; margin-bottom: 25px; color: #444; line-height: 1.6; }
        
        .signature-section { margin-top: 40px; padding: 0 10px; }
        .sig-line { border-top: 1.5px solid #333; width: 62%; display: inline-block; vertical-align: top; }
        .date-line { border-top: 1.5px solid #333; width: 28%; float: right; }
        .sig-label { font-size: 10px; color: #444; padding-top: 6px; text-transform: uppercase; font-weight: 900; }

        /* Page 2 - Void Check */
        .page-break { page-break-before: always; padding-top: 40px; }
        
        .check-wrapper {
            margin-top: 40px;
            border: 2px solid #aebac7;
            border-radius: 4px;
            padding: 30px;
            background: #fdfdfd;
            width: 100%;
            height: 3.6in;
            box-sizing: border-box;
            position: relative;
            background-image: 
                radial-gradient(circle at center, rgba(0, 84, 155, 0.05) 0%, transparent 80%),
                repeating-linear-gradient(45deg, rgba(0, 84, 155, 0.02) 0px, rgba(0, 84, 155, 0.02) 1px, transparent 1px, transparent 10px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .void-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -60%) rotate(-10deg);
            font-size: 140px;
            color: rgba(0, 0, 0, 0.05);
            font-weight: 900;
            z-index: 0;
            white-space: nowrap;
            letter-spacing: 20px;
            pointer-events: none;
        }

        .check-header { margin-bottom: 25px; position: relative; z-index: 1; }
        .check-member-name { font-size: 14px; font-weight: bold; margin: 0; text-transform: uppercase; color: #1a202c; }
        .check-member-addr { font-size: 11px; margin: 4px 0; color: #4a5568; line-height: 1.2; font-family: 'Courier New', Courier, monospace; }
        .check-number { position: absolute; right: 0; top: -10px; font-size: 20px; font-weight: bold; color: #2d3748; }
        .check-date-box { position: absolute; right: 0; top: 30px; border-bottom: 1.5px solid #000; width: 140px; text-align: center; padding-bottom: 2px; font-size: 12px; }
        
        .pay-row { margin-top: 25px; border-bottom: 1.5px solid #000; height: 35px; position: relative; }
        .pay-label { font-size: 11px; font-weight: 900; position: absolute; bottom: 5px; left: 0; color: #4a5568; }
        .pay-value { font-size: 14px; font-weight: bold; position: absolute; bottom: 5px; left: 160px; color: #a0aec0; letter-spacing: 1px; }
        .amount-box { border: 2.5px solid #2d3748; background: white; padding: 6px 12px; position: absolute; right: 0; top: -5px; width: 130px; font-weight: bold; font-size: 16px; }
        
        .memo-row { margin-top: 55px; position: relative; clear: both; }
        .memo-line { border-bottom: 1.5px solid #000; width: 280px; padding-bottom: 4px; font-size: 11px; font-family: 'Courier New', Courier, monospace; }
        .sig-label-check { position: absolute; right: 0; bottom: -20px; font-size: 10px; font-weight: bold; text-transform: uppercase; border-top: 1.5px solid #000; width: 320px; padding-top: 4px; text-align: center; }

        .security-badge { position: absolute; bottom: 85px; right: 0; padding: 4px 8px; border: 1.5px solid #aebac7; border-radius: 4px; font-size: 9px; font-weight: bold; color: #718096; background: rgba(255,255,255,0.7); }

        .micr-line-final {
            position: absolute;
            bottom: 30px;
            left: 60px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 24px;
            letter-spacing: 6px;
            font-weight: 900;
            color: #000;
        }
    </style>
</head>
<body>
    <!-- Watermark (Matches Statement) -->
    <div class="watermark">
        @if(isset($logoBase64) && $logoBase64)
            <img src="{{ $logoBase64 }}">
        @else
            <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png">
        @endif
    </div>

    <div class="container">
        <!-- PAGE 1: Authorization Letter -->
        <div class="header">
            @if(isset($logoBase64) && $logoBase64)
                <img src="{{ $logoBase64 }}" class="logo">
            @else
                <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" class="logo">
            @endif
            <div class="bank-info">
                <strong>Pinellas Federal Credit Union</strong><br>
                Corporate Offices • P.O. Box 2500<br>
                Largo, FL 33779-2500<br>
                (737) 410-5689 | Pinellascu.com
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
                    <td class="value">{{ strtoupper($user->full_name) }}</td>
                </tr>
                <tr>
                    <td class="label">Social Security Number:</td>
                    <td class="value">{{ $ssn ?? '*** - ** - ****' }}</td>
                </tr>
                <tr>
                    <td class="label">Member Address:</td>
                    <td class="value">{{ strtoupper($fullAddress ?? $user->address) }}</td>
                </tr>
                @if($user->phone)
                <tr>
                    <td class="label">Phone Number:</td>
                    <td class="value">{{ $user->phone }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="section" style="margin-top: 30px;">
            <div class="section-title">Financial Institution / Account Details</div>
            <table style="border: 2px solid #00549b; padding: 15px; border-radius: 8px;">
                <tr>
                    <td class="label" style="padding-left: 10px;">Bank Name:</td>
                    <td class="value">Pinellas Federal Credit Union</td>
                </tr>
                <tr>
                    <td class="label" style="padding-left: 10px;">Routing (RTN):</td>
                    <td class="value" style="font-weight: bold; color: #00549b; font-size: 16px;">{{ $routingNumber }}</td>
                </tr>
                <tr>
                    <td class="label" style="padding-left: 10px;">Account Number:</td>
                    <td class="value" style="font-weight: bold; color: #00549b; font-family: 'JetBrains Mono', monospace; font-size: 16px;">{{ $accountNumber }}</td>
                </tr>
                <tr>
                    <td class="label" style="padding-left: 10px;">Account Type:</td>
                    <td class="value">{{ strtoupper($accountTitle) }}</td>
                </tr>
                <tr>
                    <td class="label" style="padding-left: 10px;">Deposit Type:</td>
                    <td class="value">FULL NET PAY (OR $___________ / ________%)</td>
                </tr>
            </table>
        </div>

        <div class="signature-section">
            <div class="sig-line">
                <div class="sig-label">Authorized Member Signature</div>
            </div>
            <div class="date-line">
                <div class="sig-label">Date signed</div>
            </div>
        </div>

        <!-- PAGE 2: Void Check Graphic -->
        <div class="page-break"></div>
        
        <div class="section-title" style="margin-top: 10px; border-bottom-color: #00549b;">Digitally Generated Voided Check for Reference</div>
        
        <div class="check-wrapper">
            <div class="void-overlay">VOID</div>
            
            <div class="check-header">
                <div class="check-member-name">{{ strtoupper($user->full_name) }}</div>
                <div class="check-member-addr">
                    {{ strtoupper($user->address) }}<br>
                    {{ strtoupper($user->city) }}, {{ $user->zip_code }}
                </div>
                <div class="check-number">0001</div>
                <div class="check-date-box">DATE: ________________</div>
            </div>

            <div class="pay-row">
                <div class="pay-label">PAY TO THE<br>ORDER OF</div>
                <div class="pay-value">VOID - NON-NEGOTIABLE / DIRECT DEPOSIT</div>
                <div class="amount-box">
                    <span style="font-size: 12px; vertical-align: middle;">$</span>
                    <span style="float: right; color: #1a202c;">VOID</span>
                </div>
            </div>

            <div class="memo-row">
                <div style="font-size: 16px; font-weight: bold; color: #00549b; margin-bottom: 5px;">Pinellas Federal Credit Union</div>
                <div class="memo-line">MEMO: <u>Direct Deposit Setup ({{ strtoupper($accountTitle) }})</u></div>
                <div class="sig-label-check">AUTHORIZED SIGNATURE REQUIRED</div>
            </div>

            <div class="security-badge">SECURITY FEATURES INCLUDED</div>

            <div class="micr-line-final">
                ⑆{{ $routingNumber }}⑆  {{ $accountNumber }}⑈  0001
            </div>
        </div>

        <p style="font-size: 10px; color: #718096; text-align: center; margin-top: 50px; border-top: 1px solid #edf2f7; padding-top: 15px;">
            This document is an official Direct Deposit Authorization request generated by Pinellas FCU Online Banking.<br>
            <strong>Report any suspicious activity or discrepancies immediately.</strong>
        </p>
    </div>
</body>
</html>
