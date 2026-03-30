<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Direct Deposit Authorization - Pinellas FCU</title>
    <style>
        @page { margin: 0; padding: 0; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.4; margin: 0; padding: 40px; background: white; position: relative; }
        .container { position: relative; z-index: 1; }
        
        /* Watermark Styling (Matches Statement) */
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
        .title { text-align: center; font-size: 26px; font-weight: bold; color: #00549b; margin: 25px 0; text-transform: uppercase; letter-spacing: 1.5px; }
        
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

        /* Page 2 - Hyper-Realistic Void Check */
        .page-break { page-break-before: always; padding-top: 40px; }
        
        .check-shell {
            margin-top: 20px;
            border: 1px solid #999;
            padding: 30px;
            background: #fff;
            width: 100%;
            height: 3.65in;
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
            background-image: 
                linear-gradient(rgba(0, 84, 155, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 84, 155, 0.04) 1px, transparent 1px);
            background-size: 12px 12px;
        }

        .void-watermark-huge {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-10deg);
            font-size: 150px;
            color: rgba(0, 0, 0, 0.04);
            font-weight: 900;
            z-index: 0;
            white-space: nowrap;
            letter-spacing: 25px;
            pointer-events: none;
        }

        /* Check Top Section */
        .check-top-left { float: left; width: 330px; position: relative; z-index: 2; }
        .check-top-right { float: right; text-align: right; width: 300px; position: relative; z-index: 2; }
        .fractional-routing { font-size: 12px; color: #333; margin-bottom: 2px; letter-spacing: 0.5px; }
        .sentry-shield { font-size: 14px; font-weight: bold; color: #333; display: inline-flex; align-items: center; align-content: center; position: relative; top: -2px; }
        .check-number-big { font-size: 22px; font-weight: bold; color: #000; margin-top: 10px; clear: both; }

        /* Check Body Lines */
        .pay-to-the-order { margin-top: 20px; position: relative; z-index: 2; border-bottom: 1.5px solid #000; height: 35px; line-height: 50px; }
        .pay-label-bold { font-size: 11px; font-weight: bold; position: absolute; left: 0; top: 15px; width: 100px; line-height: 1.2; text-transform: uppercase; }
        .payee-line-void { position: absolute; left: 105px; bottom: 4px; font-size: 15px; font-weight: bold; color: #999; letter-spacing: 1px; }
        .amount-box-check { position: absolute; right: 0; bottom: 5px; border: 2.5px solid #000; background: white; padding: 4px 10px; width: 130px; font-weight: bold; font-size: 16px; min-height: 25px; }

        .dollars-line { margin-top: 40px; border-bottom: 1.5px solid #000; position: relative; z-index: 2; height: 25px; }
        .dollars-label-right { float: right; font-size: 11px; font-weight: bold; margin-left: 5px; margin-top: 10px; text-transform: uppercase; }

        .bank-info-check { margin-top: 25px; position: relative; z-index: 2; }
        .bank-name-bold { font-size: 16px; font-weight: bold; text-transform: uppercase; color: #000; }
        .bank-url-check { font-size: 12px; font-weight: bold; text-transform: uppercase; color: #000; }

        /* Date & Signature */
        .date-line-check { float: right; border-bottom: 1.5px solid #000; width: 180px; position: absolute; right: 0; top: 85px; padding-bottom: 2px; font-size: 13px; text-align: center; }
        .date-label-check { position: absolute; right: 185px; top: 88px; font-size: 11px; font-weight: bold; text-transform: uppercase; }

        .memo-signature-row { margin-top: 35px; position: relative; z-index: 2; clear: both; }
        .memo-blk { float: left; width: 380px; position: relative; }
        .memo-line-check { border-bottom: 1.5px solid #000; width: 100%; margin-top: 10px; padding-bottom: 2px; font-size: 12px; }
        .memo-label-check { position: absolute; left: 0; top: -15px; font-size: 12px; font-weight: bold; text-transform: uppercase; }

        .sig-blk { float: right; width: 320px; position: relative; text-align: center; }
        .sig-line-check-shell { border-bottom: 1.5px solid #000; width: 100%; margin-top: 10px; height: 1px; }
        .sig-label-check-shell { font-size: 10px; font-weight: bold; display: block; margin-top: 5px; text-transform: uppercase; }

        .micr-line-perfect {
            position: absolute;
            bottom: 35px;
            left: 50%;
            transform: translateX(-50%);
            font-family: 'Courier New', Courier, monospace;
            font-size: 24px;
            letter-spacing: 6px;
            font-weight: 900;
            color: #000;
            white-space: nowrap;
        }

        .shield-icon { display: inline-block; width: 16px; height: 16px; background: #333; margin-right: 5px; -webkit-clip-path: polygon(0% 15%, 50% 0%, 100% 15%, 100% 70%, 50% 100%, 0% 70%); clip-path: polygon(0% 15%, 50% 0%, 100% 15%, 100% 70%, 50% 100%, 0% 70%); }
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
            <div class="section-title">Pinellas FCU Account Details</div>
            <table style="border: 2px solid #00549b; padding: 15px; border-radius: 8px;">
                <tr>
                    <td class="label" style="padding-left: 10px;">Bank Name:</td>
                    <td class="value">Pinellas Federal Credit Union</td>
                </tr>
                <tr>
                    <td class="label" style="padding-left: 10px;">Routing (Transit):</td>
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
                    <td class="label" style="padding-left: 10px;">Allocation:</td>
                    <td class="value">FULL NET PAY (OR $___________ / ________%)</td>
                </tr>
            </table>
        </div>

        <div class="signature-section">
            <div class="sig-line">
                <div class="sig-label">AUTHORIZED MEMBER SIGNATURE</div>
            </div>
            <div class="date-line">
                <div class="sig-label">DATE SIGNED</div>
            </div>
        </div>

        <!-- PAGE 2: Digital Void Check -->
        <div class="page-break"></div>
        
        <div class="section-title" style="margin-top: 10px; border-bottom: 2px solid #00549b;">Digitally Generated Voided Check for Reference</div>
        
        <div class="check-shell">
            <div class="void-watermark-huge">VOID</div>
            
            <div class="check-top-left">
                <div class="check-member-name">{{ strtoupper($user->full_name) }}</div>
                <div class="check-member-addr">
                    {{ strtoupper($user->address) }}<br>
                    {{ strtoupper($user->city) }}, {{ $user->zip_code }}
                </div>
            </div>

            <div class="check-top-right">
                <div class="fractional-routing">63-9225/2631</div>
                <div class="sentry-shield">
                    <span class="shield-icon"></span> SentryShield℠
                </div>
                <div class="check-number-big">1001</div>
                
                <div class="date-label-check">DATE</div>
                <div class="date-line-check">________________</div>
            </div>

            <div class="pay-to-the-order">
                <div class="pay-label-bold">PAY TO THE<br>ORDER OF</div>
                <div class="payee-line-void">VOID - NOT FOR NEGOTIATION / DIRECT DEPOSIT ONLY</div>
                <div class="amount-box-check">
                    <span style="font-size: 14px; position: relative; top: -2px;">$</span>
                    <span style="float: right; color: #000;">VOID</span>
                </div>
            </div>

            <div class="dollars-line">
                <div class="dollars-label-right">DOLLARS</div>
            </div>

            <div class="bank-info-check">
                <div class="bank-name-bold">PINELLAS FEDERAL CREDIT UNION</div>
                <div class="bank-url-check">PINELLASCU.COM</div>
            </div>

            <div class="memo-signature-row">
                <div class="memo-blk">
                    <div class="memo-label-check">MEMO</div>
                    <div class="memo-line-check"><u>Direct Deposit Authorization</u></div>
                </div>
                <div class="sig-blk">
                    <div class="sig-line-check-shell"></div>
                    <div class="sig-label-check-shell">AUTHORIZED SIGNATURE</div>
                </div>
            </div>

            <div class="micr-line-perfect">
                ⑆{{ $routingNumber }}⑆  {{ $accountNumber }}⑈  1001
            </div>
        </div>

        <p style="font-size: 10px; color: #718096; text-align: center; margin-top: 50px; border-top: 1px solid #edf2f7; padding-top: 15px;">
            This document is an official Direct Deposit Authorization request generated by Pinellas FCU Online Banking.<br>
            <strong>Report any suspicious activity or discrepancies immediately.</strong>
        </p>
    </div>
</body>
</html>
