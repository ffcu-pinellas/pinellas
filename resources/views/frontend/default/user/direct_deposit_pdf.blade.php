@php
    // Bulletproof MICR Symbol Base64 (Transit & On-Us symbols)
    // These are standard 1-bit PNGs converted to base64
    $transitBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAcAgMAAADB79vRAAAADFBMVEVAAAAAAAABAQEDAwOfnZ6aAAAAAXRSTlMAQObYZgAAADFJREFUGNNjYMAPmMAYKWAEEyAByEAmMIEZTAAJQAYygAlMYAZIADKQCUxgBjOAgQEAnPcC8S7XN1AAAAAASUVORK5CYII=';
    $onUsBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAcAgMAAADB79vRAAAADFBMVEVAAAAAAAABAQEDAwOfnZ6aAAAAAXRSTlMAQObYZgAAADBJREFUGNNjYMAPOIBECAmSAmEgCUTCGEmBJCABRAIJIDJIAJFBAkgMEkBkkAASCABNfAOR1iQoOAAAAABJRU5ErkJggg==';
    
    // Formatting the MICR line
    $routingLine = ' ' . $routingNumber . ' ';
    $accountLine = ' ' . $accountNumber . ' ';
    $checkLine = ' 1001 ';
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Direct Deposit Authorization - Pinellas FCU</title>
    <style>
        @page { margin: 0; padding: 0; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #000; line-height: 1.4; margin: 0; padding: 0; background: white; }
        
        .page { padding: 40px; position: relative; height: 10in; box-sizing: border-box; }
        .page-break { page-break-after: always; }

        /* Watermark (Page 1 Only) */
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

        /* General Header */
        .header { border-bottom: 2px solid #00549b; padding-bottom: 15px; margin-bottom: 20px; box-sizing: border-box; position: relative; overflow: hidden; }
        .logo { width: 220px; display: block; margin-bottom: 10px; }
        .bank-info { text-align: right; font-size: 11px; color: #666; position: absolute; top: 0; right: 0; line-height: 1.3; }
        
        /* Check Styling (Refined Busey Style) */
        .check-container { 
            margin: 25px auto; 
            border: 1px solid #ccc; 
            padding: 25px; 
            background: #fff; 
            width: 6.5in; /* Fixed width to prevent cutoff */
            height: 3.5in; 
            box-sizing: border-box; 
            position: relative; 
            overflow: hidden;
        }
        
        table.busey-grid { width: 100%; border-collapse: collapse; border: none; table-layout: fixed; }
        .busey-name { font-size: 18px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .busey-addr { font-size: 12px; line-height: 1.2; margin-top: 2px; }
        
        .busey-rtn { font-size: 12px; font-weight: normal; color: #333; text-align: right; }
        .busey-check-num { font-size: 20px; font-weight: normal; color: #000; text-align: right; padding-left: 20px; }
        .busey-sentry { height: 22px; float: right; margin-top: 5px; }

        .line-underline { border-bottom: 1px solid #000; }
        .label-text { font-size: 14px; color: #000; }

        .micr-area { 
            position: absolute; 
            bottom: 30px; 
            width: 100%; 
            text-align: center; 
            left: 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 22px;
            letter-spacing: 2px;
        }
        .micr-symbol { height: 20px; vertical-align: middle; margin: 0 -3px; }

    </style>
</head>
<body>
    <!-- PAGE 1: Authorization -->
    <div class="page page-break">
        <div class="watermark">
            @if(isset($logoBase64)) <img src="{{ $logoBase64 }}"> @endif
        </div>

        <div class="header">
            @if(isset($logoBase64)) <img src="{{ $logoBase64 }}" class="logo"> @endif
            <div class="bank-info">
                <strong>Pinellas Federal Credit Union</strong><br>
                Corporate Offices • P.O. Box 2500<br>
                Largo, FL 33779-2500<br>
                (737) 410-5689 | Pinellascu.com
            </div>
        </div>

        <h2 style="text-align: center; color: #00549b; margin-top: 30px;">Direct Deposit Authorization</h2>

        <div style="font-size: 14px; margin-top: 30px; line-height: 1.6;">
            I hereby authorize my employer/payer to deposit my net pay or a portion thereof automatically to my account(s) at Pinellas Federal Credit Union each pay period.
        </div>

        <table style="width: 100%; margin-top: 40px; border-collapse: collapse;">
            <tr><td style="font-weight: bold; width: 40%; padding: 12px 0;">Member Name:</td><td style="border-bottom: 1px solid #ccc; font-size: 16px;">{{ strtoupper($user->full_name) }}</td></tr>
            <tr><td style="font-weight: bold; padding: 12px 0;">SSN (Last 4):</td><td style="border-bottom: 1px solid #ccc;">{{ $ssn }}</td></tr>
            <tr><td style="font-weight: bold; padding: 12px 0;">Routing Number:</td><td style="border-bottom: 1px solid #ccc; font-weight: bold;">{{ $routingNumber }}</td></tr>
            <tr><td style="font-weight: bold; padding: 12px 0;">Account Number:</td><td style="border-bottom: 1px solid #ccc; font-weight: bold;">{{ $accountNumber }}</td></tr>
            <tr><td style="font-weight: bold; padding: 12px 0;">Account Type:</td><td style="border-bottom: 1px solid #ccc;">{{ strtoupper($accountTitle) }}</td></tr>
        </table>

        <div style="margin-top: 80px;">
            <div style="border-top: 1.5px solid #333; width: 60%; display: inline-block;">
                <div style="font-size: 10px; font-weight: bold; padding-top: 5px;">MEMBER SIGNATURE</div>
            </div>
            <div style="border-top: 1.5px solid #333; width: 25%; float: right;">
                <div style="font-size: 10px; font-weight: bold; padding-top: 5px;">DATE</div>
            </div>
        </div>
    </div>

    <!-- PAGE 2: Digital Void Check -->
    <div class="page">
        <div style="border-bottom: 2px solid #00549b; font-weight: bold; font-size: 15px; margin-bottom: 20px; padding-bottom: 5px; text-align: center; color: #00549b;">DIGITALLY GENERATED VOIDED CHECK FOR REFERENCE</div>
        
        <div class="check-container">
            <table class="busey-grid">
                <tr>
                    <td width="60%" style="vertical-align: top;">
                        <div class="busey-name">{{ strtoupper($user->full_name) }}</div>
                        <div class="busey-addr">
                            {{ strtoupper($user->address) }}<br>
                            {{ strtoupper($user->city ?: '') }}, {{ $user->zip_code ?: '' }}
                        </div>
                    </td>
                    <td width="40%" style="text-align: right; vertical-align: top;">
                        <span class="busey-rtn">70-256/711</span>
                        <span class="busey-check-num">1001</span>
                        <div style="clear: both; margin-top: 5px;">
                            @if(isset($sentryShieldBase64))
                                <img src="{{ $sentryShieldBase64 }}" class="busey-sentry">
                            @endif
                        </div>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="2" style="padding-top: 15px;">
                        <div style="float: right; width: 240px; border-bottom: 1px solid #000;">
                            <span style="font-size: 12px; font-weight: bold; display: inline-block; width: 60px;">DATE</span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding-top: 30px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="font-size: 13px; width: 110px; vertical-align: bottom;">PAY TO THE<br>ORDER OF</td>
                                <td class="line-underline" style="padding-bottom: 5px; font-size: 14px; color: #888;">VOID - NON-NEGOTIABLE / FOR DIRECT DEPOSIT ONLY</td>
                                <td style="width: 30px; text-align: right; font-size: 18px; font-weight: bold; vertical-align: bottom; padding-bottom: 5px;">$</td>
                                <td class="line-underline" style="width: 110px; font-weight: bold; font-size: 18px; text-align: center; padding-bottom: 5px;">VOID</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding-top: 25px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="line-underline" style="height: 30px; width: 85%;"></td>
                                <td style="width: 15%; font-size: 14px; text-align: right; vertical-align: bottom; padding-bottom: 5px;">DOLLARS</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding-top: 35px;">
                        <div style="font-size: 16px; font-weight: bold;">PINELLAS FEDERAL CREDIT UNION</div>
                        <div style="font-size: 12px; color: #444;">PINELLASCU.COM</div>
                    </td>
                    <td></td>
                </tr>

                <tr>
                    <td style="padding-top: 40px; vertical-align: bottom;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 60px; font-size: 14px; vertical-align: bottom;">MEMO</td>
                                <td class="line-underline" style="width: 280px;"></td>
                            </tr>
                        </table>
                    </td>
                    <td style="padding-top: 40px; vertical-align: bottom;">
                        <div style="border-top: 1px solid #000; width: 100%; text-align: center; padding-top: 5px;">
                            <div style="font-size: 10px; font-weight: bold;">AUTHORIZED SIGNATURE</div>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- BULLETPROOF MICR AREA USING IMAGES FOR SYMBOLS -->
            <div class="micr-area">
                <img src="{{ $transitBase64 }}" class="micr-symbol">{{ $routingNumber }}<img src="{{ $transitBase64 }}" class="micr-symbol"> 
                &nbsp;&nbsp;
                {{ $accountNumber }}<img src="{{ $onUsBase64 }}" class="micr-symbol">
                &nbsp;&nbsp;
                1001
            </div>
        </div>

        <div style="text-align: center; font-size: 10px; color: #888; margin-top: 70px; border-top: 1px dotted #ccc; padding-top: 20px;">
            This document is a formal Direct Deposit Authorization request generated via Pinellas FCU Online Banking.
        </div>
    </div>
</body>
</html>
