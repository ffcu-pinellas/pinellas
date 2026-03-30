<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Direct Deposit Authorization - Pinellas FCU</title>
    <style>
        @font-face {
            font-family: 'MICREncording';
            @if(isset($micrFontBase64) && $micrFontBase64)
                src: url(data:font/ttf;base64,{{ $micrFontBase64 }}) format('truetype');
            @endif
            font-weight: normal;
            font-style: normal;
        }

        @page { margin: 0; padding: 0; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.4; margin: 0; padding: 0; background: white; }
        
        .page { padding: 40px; position: relative; height: 10in; }
        .page-break { page-break-after: always; }

        /* Watermark Styling (Only for Page 1) */
        .watermark {
            position: absolute;
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
        .section-title { font-size: 14px; font-weight: bold; color: #00549b; border-bottom: 1px solid #eee; padding-bottom: 4px; margin-bottom: 12px; text-transform: uppercase; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data-table td { padding: 6px 0; font-size: 13px; vertical-align: top; }
        .label { font-weight: bold; width: 35%; color: #555; }
        .value { border-bottom: 1px solid #ccc; font-weight: 500; font-size: 14px; }
        
        .authorization-text { font-size: 12px; text-align: justify; margin-bottom: 20px; color: #444; line-height: 1.6; }
        
        .signature-section { margin-top: 40px; padding: 0 10px; }
        .sig-line { border-top: 1.5px solid #333; width: 62%; display: inline-block; vertical-align: top; }
        .date-line { border-top: 1.5px solid #333; width: 28%; float: right; }
        .sig-label { font-size: 10px; color: #444; padding-top: 6px; text-transform: uppercase; font-weight: 900; }

        /* Page 2 - Check Styling */
        .check-shell {
            margin-top: 20px;
            border: 1px solid #999;
            padding: 25px;
            background: #fff;
            width: 100%;
            height: 3.65in;
            box-sizing: border-box;
            position: relative;
            background-color: #fdfdfd;
            background-image: 
                radial-gradient(circle at center, rgba(0, 84, 155, 0.03) 0%, transparent 80%),
                repeating-linear-gradient(45deg, rgba(0, 84, 155, 0.01) 0px, rgba(0, 84, 155, 0.01) 1px, transparent 1px, transparent 10px);
        }

        .void-huge {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-8deg);
            font-size: 140px;
            color: rgba(0, 0, 0, 0.03);
            font-weight: 900;
            z-index: 0;
            white-space: nowrap;
            letter-spacing: 20px;
        }

        table.check-table { width: 100%; border-collapse: collapse; position: relative; z-index: 2; }
        .check-name { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .check-addr { font-size: 11px; color: #333; font-family: 'Courier New', Courier, monospace; line-height: 1.2; }
        
        .fractional-rtn { font-size: 11px; color: #444; margin-bottom: 2px; }
        .sentry-logo { height: 28px; margin-bottom: 5px; }
        .check-num { font-size: 22px; font-weight: bold; color: #000; }

        .line-under { border-bottom: 1.5px solid #000; }
        
        .micr-line {
            font-family: 'MICREncording', 'Courier New', Courier, monospace;
            font-size: 26px;
            text-align: center;
            margin-top: 45px;
            letter-spacing: 5px;
            color: #000;
            font-weight: normal;
        }

        .security-features {
            position: absolute;
            bottom: 80px;
            right: 25px;
            border: 1px solid #aaa;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <!-- PAGE 1 -->
    <div class="page page-break">
        <div class="watermark">
            @if(isset($logoBase64) && $logoBase64)
                <img src="{{ $logoBase64 }}">
            @else
                <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png">
            @endif
        </div>

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
            <table class="data-table">
                <tr>
                    <td class="label">Employer / Payer Name:</td>
                    <td class="value">_____________________________________________________</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Employee / Member Information</div>
            <table class="data-table">
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

        <div class="section">
            <div class="section-title">Credit Union Account Details</div>
            <table style="width: 100%; border: 2.5px solid #00549b; padding: 20px; border-radius: 8px;">
                <tr>
                    <td class="label">Bank Name:</td>
                    <td class="value">Pinellas Federal Credit Union</td>
                </tr>
                <tr>
                    <td class="label">Routing (Transit):</td>
                    <td class="value" style="font-weight: bold; color: #00549b; font-size: 16px;">{{ $routingNumber }}</td>
                </tr>
                <tr>
                    <td class="label">Account Number:</td>
                    <td class="value" style="font-weight: bold; color: #00549b; font-family: 'JetBrains Mono', monospace; font-size: 16px;">{{ $accountNumber }}</td>
                </tr>
                <tr>
                    <td class="label">Account Type:</td>
                    <td class="value">{{ strtoupper($accountTitle) }}</td>
                </tr>
                <tr>
                    <td class="label">Deposit Amount:</td>
                    <td class="value">FULL NET PAY (OR $___________ / ________%)</td>
                </tr>
            </table>
        </div>

        <div class="signature-section">
            <div class="sig-line">
                <div class="sig-label">Authorized Member Signature</div>
            </div>
            <div class="date-line">
                <div class="sig-label">Date</div>
            </div>
        </div>
    </div>

    <!-- PAGE 2 -->
    <div class="page">
        <div class="section-title" style="margin-top: 10px; border-bottom: 2.5px solid #00549b; text-align: center;">Member Information Reference (VOIDED CHECK)</div>
        
        <div class="check-shell">
            <div class="void-huge">VOID</div>
            
            <table class="check-table">
                <tr>
                    <td width="60%">
                        <div class="check-name">{{ strtoupper($user->full_name) }}</div>
                        <div class="check-addr">
                            {{ strtoupper($user->address) }}<br>
                            {{ strtoupper($user->city) }}, {{ $user->zip_code }}
                        </div>
                    </td>
                    <td width="40%" style="text-align: right; vertical-align: top;">
                        <div class="fractional-rtn">63-9225/2631</div>
                        @if(isset($sentryShieldBase64) && $sentryShieldBase64)
                            <img src="{{ $sentryShieldBase64 }}" class="sentry-logo">
                        @endif
                        <div class="check-num">1001</div>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="2" style="text-align: right; padding-top: 10px;">
                        <table style="width: 200px; float: right;">
                            <tr>
                                <td style="font-size: 11px; font-weight: bold; width: 40px; padding-right: 5px;">DATE</td>
                                <td class="line-under" style="height: 25px;"></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding-top: 25px;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="font-size: 10px; font-weight: bold; width: 100px; padding-bottom: 5px;">PAY TO THE<br>ORDER OF</td>
                                <td class="line-under" style="padding-bottom: 5px; font-size: 14px; font-weight: bold; color: #888;">VOID - NON-NEGOTIABLE / DIRECT DEPOSIT ONLY</td>
                                <td style="width: 8px;"></td>
                                <td style="width: 130px; border: 3px solid #000; padding: 6px; font-weight: bold; font-size: 18px; text-align: center;">
                                    <span style="float: left; font-size: 14px;">$</span> VOID
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding-top: 15px;">
                        <table style="width: 100%;">
                            <tr>
                                <td class="line-under" style="height: 28px;"></td>
                                <td style="width: 80px; font-size: 11px; font-weight: bold; padding-left: 10px; padding-bottom: 5px; vertical-align: bottom;">DOLLARS</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding-top: 20px;">
                        <div style="font-size: 16px; font-weight: bold; color: #00549b;">PINELLAS FEDERAL CREDIT UNION</div>
                        <div style="font-size: 11px; font-weight: bold;">PINELLASCU.COM</div>
                    </td>
                    <td></td>
                </tr>

                <tr>
                    <td style="padding-top: 25px;">
                        <table style="width: 90%;">
                            <tr>
                                <td style="width: 50px; font-size: 11px; font-weight: bold; vertical-align: bottom;">MEMO</td>
                                <td class="line-under" style="padding-bottom: 2px;"><u>Direct Deposit Setup</u></td>
                            </tr>
                        </table>
                    </td>
                    <td style="padding-top: 25px; vertical-align: bottom;">
                        <div style="border-top: 2px solid #000; width: 100%; text-align: center; padding-top: 4px;">
                            <span style="font-size: 9px; font-weight: bold;">AUTHORIZED SIGNATURE</span>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="micr-line">
                t{{ $routingNumber }}t {{ $accountNumber }}u 1001
            </div>
            
            <div class="security-features">SECURITY FEATURES INCLUDED</div>
        </div>

        <p style="font-size: 10px; color: #718096; text-align: center; margin-top: 60px; border-top: 1px solid #edf2f7; padding-top: 15px;">
            This document is an official Direct Deposit Authorization request generated by Pinellas FCU Online Banking.<br>
            <strong>For security purposes, this voided check is digitally generated and non-negotiable.</strong>
        </p>
    </div>
</body>
</html>
