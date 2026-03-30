<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Direct Deposit Authorization - Pinellas FCU</title>
    <style>
        @page { margin: 0; padding: 0; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.4; margin: 0; padding: 0; background: white; }
        
        .page { padding: 40px; position: relative; height: 10in; box-sizing: border-box; }
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
        
        .signature-section { margin-top: 40px; }
        .sig-line { border-top: 1.5px solid #333; width: 62%; display: inline-block; vertical-align: top; }
        .date-line { border-top: 1.5px solid #333; width: 28%; float: right; }
        .sig-label { font-size: 10px; color: #444; padding-top: 6px; text-transform: uppercase; font-weight: 900; }

        /* Page 2 - Check Styling */
        .check-shell {
            margin-top: 20px;
            border: 1px solid #999;
            padding: 25px;
            background: #fdfdfd;
            width: 100%;
            height: 3.65in;
            box-sizing: border-box;
            position: relative;
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
        .check-name { font-size: 14px; font-weight: bold; text-transform: uppercase; color: #1a202c; }
        .check-addr { font-size: 11px; color: #4a5568; font-family: 'Courier New', Courier, monospace; line-height: 1.2; }
        
        .fractional-rtn { font-size: 11px; color: #444; margin-bottom: 2px; }
        .sentry-logo { height: 26px; display: block; margin: 5px 0; }
        .check-num-big { font-size: 24px; font-weight: bold; color: #000; }

        .line-under { border-bottom: 2.0px solid #000; }
        
        /* MICR Virtual Font (SVG Native) */
        .micr-line-vfont {
            margin-top: 50px;
            text-align: center;
            letter-spacing: 3px;
        }
        .micr-char { display: inline-block; vertical-align: middle; height: 28px; fill: #000; }

    </style>
</head>
<body>
    @php
        // Hyper-Accurate MICR E-13B SVG Paths
        $micrSymbols = [
            '0' => 'M4 2h16v20H4V2zm4 4v12h8V6H8z',
            '1' => 'M12 2h4v20h-4V2z',
            '2' => 'M4 2h16v4h-12v4h12v12H4v-4h12v-4H4V2z',
            '3' => 'M4 2h16v20H4v-4h12v-4h-8v-4h8V6H4V2z',
            '4' => 'M4 2h4v8h12V2h4v20h-4v-8H4V2z',
            '5' => 'M4 2h16v4H8v4h12v12H4v-4h12v-4H4V2z',
            '6' => 'M4 2h4v4h12v16H4V2zm4 8v8h8v-8H8z',
            '7' => 'M4 2h20v20h-4V6H4V2z',
            '8' => 'M4 2h16v20H4V2zm4 4v4h8V6H8zm0 8v4h8v-4H8z',
            '9' => 'M4 2h16v20h-4v-4H4V2zm12 4H8v8h8V6z',
            't' => 'M12 2v20M4 6h16M4 18h16', // Transit symbol (simplified vector)
            'u' => 'M6 2h12v20H6V2zm4 4v12h4V6h-4z', // On-Us symbol (simplified vector)
            ' ' => 'M0 0h12v12H0V0z' // Spacer
        ];
        
        $routing = 't' . $routingNumber . 't';
        $account = $accountNumber . 'u';
        $num = '1001';
        $micrString = $routing . ' ' . $account . ' ' . $num;
    @endphp

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
            </table>
        </div>

        <div class="section">
            <div class="section-title">Bank Account Details (Pinellas FCU)</div>
            <table style="width: 100%; border: 3px solid #00549b; padding: 20px; border-radius: 8px;">
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
                    <td class="label">Balance Allocation:</td>
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
    </div>

    <!-- PAGE 2 -->
    <div class="page">
        <div class="section-title" style="margin-top: 10px; border-bottom: 3px solid #00549b; text-align: center;">Member Bank Information (REFERENCE VOID CHECK)</div>
        
        <div class="check-shell">
            <div class="void-huge">VOID</div>
            
            <table class="check-table">
                <tr>
                    <td width="55%">
                        <div class="check-name">{{ strtoupper($user->full_name) }}</div>
                        <div class="check-addr">
                            {{ strtoupper($user->address) }}<br>
                            {{ strtoupper($user->city ?: '') }}, {{ $user->zip_code ?: '' }}
                        </div>
                    </td>
                    <td width="45%" style="text-align: right; vertical-align: top;">
                        <div class="fractional-rtn">63-9225/2631</div>
                        @if(isset($sentryShieldBase64) && $sentryShieldBase64)
                            <img src="{{ $sentryShieldBase64 }}" class="sentry-logo">
                        @else
                            <div style="font-size: 12px; font-weight: bold; margin-bottom: 5px;">SentryShield℠</div>
                        @endif
                        <div class="check-num-big">1001</div>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="2" style="text-align: right; padding-top: 15px;">
                        <table style="width: 220px; float: right;">
                            <tr>
                                <td style="font-size: 11px; font-weight: bold; width: 40px; text-align: right; padding-right: 10px;">DATE</td>
                                <td class="line-under" style="height: 25px;"></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding-top: 30px;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="font-size: 10px; font-weight: bold; width: 110px; padding-bottom: 5px;">PAY TO THE<br>ORDER OF</td>
                                <td class="line-under" style="padding-bottom: 5px; font-size: 15px; font-weight: bold; color: #a0aec0;">VOID - NON-NEGOTIABLE / FOR DIRECT DEPOSIT ONLY</td>
                                <td style="width: 10px;"></td>
                                <td style="width: 140px; border: 3px solid #000; padding: 6px; font-weight: bold; font-size: 20px; text-align: center; background: white;">
                                    <span style="float: left; font-size: 16px;">$</span> VOID
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding-top: 20px;">
                        <table style="width: 100%;">
                            <tr>
                                <td class="line-under" style="height: 30px;"></td>
                                <td style="width: 80px; font-size: 11px; font-weight: bold; padding-left: 10px; padding-bottom: 5px; vertical-align: bottom;">DOLLARS</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding-top: 25px;">
                        <div style="font-size: 16px; font-weight: bold; color: #00549b;">PINELLAS FEDERAL CREDIT UNION</div>
                        <div style="font-size: 12px; font-weight: bold;">WWW.PINELLASCU.COM</div>
                    </td>
                    <td></td>
                </tr>

                <tr>
                    <td style="padding-top: 30px;">
                        <table style="width: 95%;">
                            <tr>
                                <td style="width: 50px; font-size: 12px; font-weight: bold; vertical-align: bottom;">MEMO</td>
                                <td class="line-under" style="padding-bottom: 2px; font-size: 11px; font-style: italic;">Direct Deposit Setup Authorization</td>
                            </tr>
                        </table>
                    </td>
                    <td style="padding-top: 30px; vertical-align: bottom;">
                        <div style="border-top: 2px solid #000; width: 100%; text-align: center; padding-top: 5px;">
                            <span style="font-size: 10px; font-weight: bold; text-transform: uppercase;">Authorized Signature Required</span>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="micr-line-vfont">
                @foreach (str_split($micrString) as $char)
                    @if (isset($micrSymbols[$char]) && $char != ' ')
                        <svg class="micr-char" width="16" viewBox="0 0 24 24"><path d="{{ $micrSymbols[$char] }}"/></svg>
                    @else
                        <span style="display:inline-block; width: 10px;"></span>
                    @endif
                @endforeach
            </div>
            
            <div style="position: absolute; bottom: 85px; right: 25px; border: 1.5px solid #a0aec0; padding: 4px 10px; font-size: 9px; font-weight: bold; color: #718096; border-radius: 4px; background: white;">SECURITY FEATURES INCLUDED</div>
        </div>

        <p style="font-size: 10px; color: #94a3b8; text-align: center; margin-top: 60px; border-top: 1px dotted #cbd5e0; padding-top: 15px;">
            This document is a formal Direct Deposit Authorization request generated via Pinellas FCU Online Banking.<br>
            <strong>The digital voided check is provided for account reference only and is strictly non-negotiable.</strong>
        </p>
    </div>
</body>
</html>
