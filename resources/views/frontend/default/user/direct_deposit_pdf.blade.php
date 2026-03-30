<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Direct Deposit Authorization - Pinellas FCU</title>
    <style>
        @page { margin: 0; padding: 0; size: 8.5in 11in; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #000; margin: 0; padding: 0; background: #fff; }
        
        /* Master Container */
        .page-wrapper { width: 8.5in; height: 11in; position: relative; page-break-after: always; overflow: hidden; }
        .inner-shell { width: 7.0in; margin: 0 auto; padding-top: 50pt; }

        /* Watermark (Page 1 Only) */
        .watermark-overlay { position: absolute; top: 15%; left: 1.25in; width: 6.0in; opacity: 0.08; z-index: -1; text-align: center; }
        .watermark-overlay img { width: 5.0in; }

        /* Typography & Utilities */
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Header Components */
        .bank-header-row { border-bottom: 2pt solid #00549b; padding-bottom: 12pt; margin-bottom: 25pt; position: relative; }
        .bank-logo-img { width: 200pt; }
        .bank-contact { position: absolute; top: 0; right: 0; text-align: right; font-size: 9pt; color: #333; line-height: 1.3; }

        /* Document Body */
        .letter-title { font-size: 24pt; font-weight: bold; color: #00549b; text-align: center; margin: 30pt 0; }
        .letter-para { font-size: 11pt; line-height: 1.6; text-align: justify; margin-bottom: 20pt; }
        
        table.data-grid { width: 100%; border-collapse: collapse; margin-bottom: 30pt; }
        table.data-grid td { padding: 10pt 0; font-size: 11pt; border-bottom: 0.5pt solid #eee; }
        .label-cell { width: 35%; font-weight: bold; color: #444; }

        .highlight-box { border: 2pt solid #00549b; background: #f9fbff; padding: 20pt; border-radius: 6pt; margin-bottom: 40pt; }

        /* BUSEY STYLE VOID CHECK (STRICT TABLE-CELL ARCHITECTURE) */
        table.check-master-table { 
            width: 7.0in; 
            height: 3.5in; 
            border: 1pt solid #aaa; 
            background: #fff; 
            margin-top: 50pt;
            border-collapse: collapse;
            table-layout: fixed;
            position: relative;
            background-image: 
                radial-gradient(circle at center, rgba(0, 84, 155, 0.02) 0%, transparent 80%),
                repeating-linear-gradient(45deg, rgba(0, 84, 155, 0.005) 0px, rgba(0, 84, 155, 0.005) 1px, transparent 1px, transparent 8px);
        }

        .void-bg-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-10deg);
            font-size: 130pt;
            color: rgba(0, 0, 0, 0.03);
            font-weight: 900;
            z-index: 1;
            letter-spacing: 20pt;
        }

        .check-inner-cell { padding: 25pt; vertical-align: top; position: relative; z-index: 10; }
        
        .member-name-v4 { font-size: 16pt; font-weight: bold; line-height: 1.1; }
        .member-addr-v4 { font-size: 10pt; font-family: 'Courier New', Courier, monospace; color: #333; margin-top: 4pt; }

        .meta-cell-v4 { text-align: right; vertical-align: top; }
        .check-num-v4 { font-size: 20pt; font-weight: normal; color: #000; margin-bottom: 4pt; }
        .fractional-v4 { font-size: 10pt; color: #444; }
        .sentry-logo-v4 { height: 22pt; display: block; float: right; margin-top: 6pt; }

        .underline-v4 { border-bottom: 1pt solid #000; }
        .label-v4 { font-size: 13pt; color: #000; font-weight: normal; }

        .micr-row-v4 { padding: 40pt 0 20pt 0; text-align: center; }
        .micr-glyph-atomic { height: 26pt; vertical-align: middle; }

    </style>
</head>
<body>
    @php
        // Professional High-Res E-13B Sprites
        $prefix = 'data:image/svg+xml;base64,';
        $v = [
            '0' => $prefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h12v24H4V2zm4 4v16h4V6H8z"/></svg>'),
            '1' => $prefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M10 2h4v24h-4V2z"/></svg>'),
            '2' => $prefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h12v4H8v4h8v14H4v-4h8v-6H4V2z"/></svg>'),
            '3' => $prefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h12v24H4v-4h8v-6H8v-4h4V6H4V2z"/></svg>'),
            '4' => $prefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h4v10h8V2h4v24h-4v-10H4V2z"/></svg>'),
            '5' => $prefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h12v4H8v6h8v12H4v-4h8v-4H4V2z"/></svg>'),
            '6' => $prefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h14v24H4V2zm4 4v16h6V6H8z"/></svg>'),
            '7' => $prefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h16v24h-4V6H4V2z"/></svg>'),
            '8' => $prefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h12v24H4V2zm4 4v6h4V6H8zm0 10v6h4v-6H8z"/></svg>'),
            '9' => $prefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h12v24h-4v-4H4V2zm4 4v8h4V6H8z"/></svg>'),
            't' => $prefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M12 2v24M4 8h12M4 18h12"/></svg>'), // Transit
            'u' => $prefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h4v24H4V2zm12 0h4v24h-4V2zm-8 10h8v4H8v-4z"/></svg>'), // On-Us
            ' ' => $prefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"></svg>') // Spacer
        ];

        $micrString = 't' . $routingNumber . 't ' . $accountNumber . 'u 1001';
    @endphp

    <!-- PAGE 1: Professional Header -->
    <div class="page-wrapper">
        <div class="watermark-overlay">
            @if(isset($logoBase64)) <img src="{{ $logoBase64 }}"> @endif
        </div>
        
        <div class="inner-shell">
            <div class="bank-header-row">
                @if(isset($logoBase64)) <img src="{{ $logoBase64 }}" class="bank-logo-img"> @endif
                <div class="bank-contact">
                    <strong>Pinellas Federal Credit Union</strong><br>
                    Corporate Offices • P.O. Box 2500<br>
                    Largo, FL 33779-2500<br>
                    Pinellascu.com
                </div>
            </div>

            <div class="letter-title">Direct Deposit Authorization</div>

            <p class="letter-para">
                I hereby authorize my employer/payer, listed below, to deposit my net pay or a portion thereof automatically to my account(s) at Pinellas Federal Credit Union each pay period. This authorization remains in effect until Pinellas Federal Credit Union has received written notification from me.
            </p>

            <table class="data-grid">
                <tr><td class="label-cell">Employer / Payer Name:</td><td>____________________________________________</td></tr>
                <tr><td class="label-cell">Member Full Name:</td><td>{{ strtoupper($user->full_name) }}</td></tr>
                <tr><td class="label-cell">SSN (Last 4):</td><td>{{ $ssn }}</td></tr>
                <tr><td class="label-cell">Member Address:</td><td>{{ strtoupper($fullAddress) }}</td></tr>
            </table>

            <div class="highlight-box">
                <table class="data-grid" style="margin-bottom: 0;">
                    <tr><td class="label-cell">Bank Name:</td><td>Pinellas Federal Credit Union</td></tr>
                    <tr><td class="label-cell">Routing Transit #:</td><td style="font-size: 14pt; color: #00549b; font-weight: bold;">{{ $routingNumber }}</td></tr>
                    <tr><td class="label-cell">Account Number:</td><td style="font-size: 14pt; color: #00549b; font-weight: bold; font-family: monospace;">{{ $accountNumber }}</td></tr>
                    <tr><td class="label-cell">Allocation:</td><td>FULL NET PAY (OR $___________ / ________%)</td></tr>
                </table>
            </div>

            <div style="margin-top: 60pt;">
                <table style="width: 100%;">
                    <tr>
                        <td style="border-top: 2pt solid #000; width: 60%; font-size: 10pt; font-weight: bold; padding-top: 6pt;">MEMBER SIGNATURE</td>
                        <td style="width: 10%;"></td>
                        <td style="border-top: 2pt solid #000; width: 30%; font-size: 10pt; font-weight: bold; padding-top: 6pt;">DATE</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- PAGE 2: Strict Table-Cell Architecture Void Check -->
    <div class="page-wrapper">
        <div class="inner-shell">
            <div style="border-bottom: 2pt solid #00549b; padding-bottom: 8pt; margin-bottom: 15pt; text-align: center; color: #00549b; font-weight: bold; font-size: 16pt;">DIGITALLY GENERATED VOID CHECK REFERENCE</div>
            
            <table class="check-master-table">
                <!-- VOID WATERMARK -->
                <div class="void-bg-overlay">VOID</div>

                <tr>
                    <td class="check-inner-cell">
                        <!-- HEADER ROW: Member Info & Meta -->
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15pt;">
                            <tr>
                                <td style="width: 60%; vertical-align: top;">
                                    <div class="member-name-v4">{{ strtoupper($user->full_name) }}</div>
                                    <div class="member-addr-v4">
                                        {{ strtoupper($user->address) }}<br>
                                        {{ strtoupper($user->city ?: '') }}, {{ $user->zip_code ?: '' }}
                                    </div>
                                </td>
                                <td class="meta-cell-v4">
                                    <div class="check-num-v4">1001</div>
                                    <div class="fractional-v4">70-256/711</div>
                                    @if(isset($sentryShieldBase64))
                                        <img src="{{ $sentryShieldBase64 }}" class="sentry-logo-v4">
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <!-- DATE ROW -->
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 25pt;">
                            <tr>
                                <td style="width: 60%;"></td>
                                <td style="width: 40%;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td style="width: 50pt; text-align: right; padding-right: 15pt; font-size: 11pt; font-weight: bold; vertical-align: bottom;">DATE</td>
                                            <td class="underline-v4" style="height: 20pt;"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- PAYEE ROW -->
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15pt;">
                            <tr>
                                <td style="width: 110pt; font-size: 13pt; vertical-align: bottom; padding-bottom: 4pt;">PAY TO THE<br>ORDER OF</td>
                                <td class="underline-v4" style="padding-bottom: 4pt; font-size: 14pt; font-weight: bold; color: #999; vertical-align: bottom;">DIGITALLY VOIDED - NON-NEGOTIABLE / DIRECT DEPOSIT SETUP</td>
                                <td style="width: 25pt; text-align: right; font-size: 20pt; font-weight: bold; vertical-align: bottom; padding-bottom: 4pt;">$</td>
                                <td class="underline-v4" style="width: 100pt; text-align: center; vertical-align: bottom; font-size: 18pt; font-weight: bold; padding-bottom: 4pt;">VOID</td>
                            </tr>
                        </table>

                        <!-- LEGAL AMOUNT ROW -->
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10pt;">
                            <tr>
                                <td class="underline-v4" style="height: 30pt;"></td>
                                <td style="width: 80pt; text-align: right; vertical-align: bottom; font-size: 13pt; padding-bottom: 4pt; font-weight: bold;">DOLLARS</td>
                            </tr>
                        </table>

                        <!-- BANK LOGO ROW -->
                        <div style="font-size: 16pt; font-weight: bold; margin-top: 15pt;">PINELLAS FEDERAL CREDIT UNION</div>
                        <div style="font-size: 11pt; font-weight: normal; margin-top: 2pt; color: #444;">PINELLASCU.COM</div>

                        <!-- FOOTER ROW: MEMO & SIG -->
                        <table style="width: 100%; border-collapse: collapse; margin-top: 25pt;">
                            <tr>
                                <td style="width: 50pt; font-size: 12pt; vertical-align: bottom;">MEMO</td>
                                <td class="underline-v4" style="width: 180pt;"></td>
                                <td style="width: 20pt;"></td>
                                <td style="vertical-align: bottom;">
                                    <div style="border-top: 1.5pt solid #000; text-align: center; padding-top: 5pt; font-size: 10pt; font-weight: bold; color: #000;">AUTHORIZED SIGNATURE REQUIRED</div>
                                </td>
                            </tr>
                        </table>

                        <!-- ZERO-FAILURE MICR ROW -->
                        <div class="micr-row-v4">
                            <table style="margin: 0 auto; border-collapse: collapse;">
                                <tr>
                                    @foreach(str_split($micrString) as $char)
                                        @if(isset($v[$char]))
                                            <td style="width: 8.5in; width: 0.125in;">
                                                <img src="{{ $v[$char] }}" class="micr-glyph-atomic" style="width: 11pt;">
                                            </td>
                                        @else
                                            <td style="width: 15pt;"></td>
                                        @endif
                                    @endforeach
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>

            <p style="text-align: center; font-size: 9pt; color: #888; margin-top: 60pt; border-top: 1px dotted #ccc; padding-top: 15pt;">
                This document is generated via Pinellas FCU Online Banking for Direct Deposit setup.<br>
                <strong>VOID - This digital reference is non-negotiable.</strong>
            </p>
        </div>
    </div>
</body>
</html>
