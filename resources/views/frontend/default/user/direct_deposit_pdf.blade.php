<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Direct Deposit Authorization - Pinellas FCU</title>
    <style>
        @page { margin: 0; padding: 0; size: 8.5in 11in; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #000; margin: 0; padding: 0; background: #fff; -webkit-font-smoothing: antialiased; }
        
        .page { width: 8.5in; height: 11in; position: relative; overflow: hidden; page-break-after: always; }
        .content-safe-zone { width: 7.0in; margin: 0 auto; padding-top: 0.5in; position: relative; }

        /* Watermark (Page 1 Only) */
        .watermark-layer { position: absolute; top: 3.0in; left: 0.5in; width: 6.0in; opacity: 0.08; z-index: -1; text-align: center; }
        .watermark-layer img { width: 5.0in; }

        /* Professional Typography */
        .text-bold { font-weight: bold; }
        .text-upper { text-transform: uppercase; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* Header Section */
        .bank-header { border-bottom: 2pt solid #00549b; padding-bottom: 15pt; margin-bottom: 25pt; position: relative; }
        .bank-logo { width: 2.2in; }
        .bank-meta { position: absolute; top: 0; right: 0; text-align: right; font-size: 9pt; color: #444; line-height: 1.2; }

        /* Letter Styling */
        .doc-title { font-size: 22pt; font-weight: bold; color: #00549b; margin: 25pt 0; letter-spacing: 1pt; }
        .intro-para { font-size: 11pt; line-height: 1.5; text-align: justify; margin-bottom: 25pt; }
        
        table.info-field-table { width: 100%; border-collapse: collapse; margin-bottom: 20pt; }
        table.info-field-table td { padding: 8pt 0; font-size: 11pt; border-bottom: 0.5pt solid #eee; }
        .field-label { width: 30%; color: #555; font-weight: bold; }
        .field-value { width: 70%; font-weight: 500; }

        .account-box-accent { border: 2pt solid #00549b; border-radius: 8pt; padding: 15pt; margin: 25pt 0; background: #f8faff; }

        /* Busey Bank Standard Check Shell */
        .check-shell-professional {
            border: 1pt solid #aaa;
            width: 7.0in;
            height: 3.5in;
            margin-top: 30pt;
            position: relative;
            background: #fff;
            overflow: hidden;
            box-sizing: border-box;
            background-color: #fafafa;
            background-image: 
                radial-gradient(circle at center, rgba(0, 84, 155, 0.02) 0%, transparent 70%),
                repeating-linear-gradient(45deg, rgba(0, 84, 155, 0.01) 0px, rgba(0, 84, 155, 0.01) 1px, transparent 1px, transparent 10px);
        }

        .void-watermark-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-10deg);
            font-size: 120pt;
            color: rgba(0, 0, 0, 0.03);
            font-weight: 900;
            z-index: 0;
            letter-spacing: 15pt;
        }

        table.check-grid-v3 { width: 100%; border-collapse: collapse; position: relative; z-index: 5; padding: 25pt; border: none; box-sizing: border-box; }
        .cell-padding { padding: 20pt; }

        .member-block { font-size: 14pt; font-weight: bold; line-height: 1.2; }
        .member-addr { font-size: 10pt; font-weight: normal; color: #333; margin-top: 4pt; font-family: 'Courier New', Courier, monospace; }

        .metadata-block { text-align: right; vertical-align: top; font-size: 11pt; }
        .check-number-header { font-size: 22pt; font-weight: normal; color: #000; margin-bottom: 4pt; }
        .fractional-header { font-size: 11pt; color: #444; margin-bottom: 8pt; letter-spacing: 0.5pt; }
        .sentry-header-logo { height: 26pt; display: block; float: right; margin-top: 5pt; }

        .divider-full { border-bottom: 1.2pt solid #000; }
        .divider-label { font-size: 12pt; font-weight: normal; padding-top: 5pt; }

        .amount-box-minimal {
            border: 2pt solid #000;
            background: #fff;
            padding: 8pt 15pt;
            font-size: 20pt;
            font-weight: bold;
            text-align: center;
            min-width: 100pt;
            display: inline-block;
        }

        .bank-branding-v3 { font-size: 16pt; font-weight: bold; color: #000; margin-top: 25pt; }
        
        .micr-line-professional {
            position: absolute;
            bottom: 30pt;
            width: 100%;
            text-align: center;
            left: 0;
            height: 35pt;
        }
        .micr-glyph-atomic { height: 24pt; vertical-align: bottom; display: inline-block; }

    </style>
</head>
<body>
    @php
        // Vector-Sprite Definitions (Base64 SVG Strings for E-13B)
        // These are atomic vector objects that DOMPDF treats as indestructible images
        $svgPrefix = 'data:image/svg+xml;base64,';
        $glyphs = [
            '0' => $svgPrefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h12v24H4V2zm4 4v16h4V6H8z"/></svg>'),
            '1' => $svgPrefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M8 2h4v24H8V2z"/></svg>'),
            '2' => $svgPrefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h12v4H8v4h8v14H4v-4h8v-6H4V2z"/></svg>'),
            '3' => $svgPrefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h12v24H4v-4h8v-6H8v-4h4V6H4V2z"/></svg>'),
            '4' => $svgPrefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h4v10h8V2h4v24h-4v-10H4V2z"/></svg>'),
            '5' => $svgPrefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h12v4H8v6h8v12H4v-4h8v-4H4V2z"/></svg>'),
            '6' => $svgPrefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h4v4h8v18H4V2zm4 8v10h4v-10H8z"/></svg>'),
            '7' => $svgPrefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h16v24h-4V6H4V2z"/></svg>'),
            '8' => $svgPrefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h12v24H4V2zm4 4v6h4V6H8zm0 10v6h4v-6H8z"/></svg>'),
            '9' => $svgPrefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h12v24h-4v-4H4V2zm4 4v8h4V6H8z"/></svg>'),
            't' => $svgPrefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M12 2v24M4 8h12M4 18h12"/></svg>'), // Transit
            'u' => $svgPrefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h12v24H4V2zm4 4v16h4V6H8z"/></svg>'), // On-Us (Simplified for base)
            'd' => $svgPrefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 12h12v4H4v-4z"/></svg>') // Dash
        ];

        $transitSym = $glyphs['t'];
        $onUsSym = $svgPrefix . base64_encode('<svg viewBox="0 0 20 28" xmlns="http://www.w3.org/2000/svg"><path d="M4 2h4v24H4V2zm12 0h4v24h-4V2zm-8 10h8v4H8v-4z"/></svg>');
        
        $routingLine = 't' . $routingNumber . 't';
        $accountLine = $accountNumber . 'u';
        $checkNumLine = '1001';
    @endphp

    <!-- PAGE 1: Authorization Header -->
    <div class="page">
        <div class="watermark-layer">
            @if(isset($logoBase64)) <img src="{{ $logoBase64 }}"> @endif
        </div>

        <div class="content-safe-zone">
            <div class="bank-header">
                @if(isset($logoBase64)) <img src="{{ $logoBase64 }}" class="bank-logo"> @endif
                <div class="bank-meta">
                    <strong>Pinellas Federal Credit Union</strong><br>
                    Corporate Offices • P.O. Box 2500<br>
                    Largo, FL 33779-2500<br>
                    Pinellascu.com
                </div>
            </div>

            <div class="doc-title">Direct Deposit Authorization</div>

            <p class="intro-para">
                I hereby authorize my employer/payer, listed below, to deposit my net pay or a portion thereof automatically to my account(s) at Pinellas Federal Credit Union each pay period. This authorization remains in effect until Pinellas Federal Credit Union has received written notification from me.
            </p>

            <table class="info-field-table">
                <tr><td class="field-label">Employer / Payer:</td><td class="field-value">____________________________________________________</td></tr>
                <tr><td class="field-label">Member Name:</td><td class="field-value">{{ strtoupper($user->full_name) }}</td></tr>
                <tr><td class="field-label">SSN (Last 4):</td><td class="field-value">{{ $ssn }}</td></tr>
                <tr><td class="field-label">Member Address:</td><td class="field-value">{{ strtoupper($fullAddress) }}</td></tr>
            </table>

            <div class="account-box-accent">
                <table class="info-field-table" style="margin-bottom: 0;">
                    <tr><td class="field-label">Routing Transit #:</td><td class="field-value" style="font-size: 14pt; color: #00549b;">{{ $routingNumber }}</td></tr>
                    <tr><td class="field-label">Account Number:</td><td class="field-value" style="font-size: 14pt; color: #00549b; font-family: monospace;">{{ $accountNumber }}</td></tr>
                    <tr><td class="field-label">Account Type:</td><td class="field-value">{{ strtoupper($accountTitle) }}</td></tr>
                    <tr><td class="field-label">Deposit Amount:</td><td class="field-value">FULL NET PAY (OR $___________ / ________%)</td></tr>
                </table>
            </div>

            <div style="margin-top: 50pt;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 60%; border-top: 1.5pt solid #000; font-size: 10pt; font-weight: bold; padding-top: 5pt;">AUTHORIZED MEMBER SIGNATURE</td>
                        <td style="width: 10%;"></td>
                        <td style="width: 30%; border-top: 1.5pt solid #000; font-size: 10pt; font-weight: bold; padding-top: 5pt;">DATE SIGNED</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- PAGE 2: Professional Void Check Shell -->
    <div class="page">
        <div class="content-safe-zone">
            <div style="border-bottom: 2pt solid #00549b; padding-bottom: 5pt; margin-bottom: 15pt; text-align: center; color: #00549b; font-weight: bold; font-size: 14pt;">DIGITALLY GENERATED VOID CHECK FOR REFERENCE ONLY</div>
            
            <div class="check-shell-professional">
                <div class="void-watermark-text">VOID</div>
                
                <div class="cell-padding">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 55%; vertical-align: top;">
                                <div class="member-block">{{ strtoupper($user->full_name) }}</div>
                                <div class="member-addr">
                                    {{ strtoupper($user->address) }}<br>
                                    {{ strtoupper($user->city ?: '') }}, {{ $user->zip_code ?: '' }}
                                </div>
                            </td>
                            <td class="metadata-block">
                                <div class="check-number-header">1001</div>
                                <div class="fractional-header">70-256/711</div>
                                @if(isset($sentryShieldBase64))
                                    <img src="{{ $sentryShieldBase64 }}" class="sentry-header-logo">
                                @endif
                            </td>
                        </tr>
                    </table>

                    <div style="text-align: right; margin-top: 15pt;">
                        <table style="width: 2.8in; float: right;">
                            <tr>
                                <td style="font-size: 11pt; font-weight: bold; width: 50pt; text-align: right; padding-right: 15pt; vertical-align: bottom;">DATE</td>
                                <td class="divider-full" style="height: 20pt;"></td>
                            </tr>
                        </table>
                    </div>

                    <div style="clear: both; margin-top: 35pt;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="font-size: 14pt; width: 1.3in; vertical-align: bottom; line-height: 1.1;">PAY TO THE<br>ORDER OF</td>
                                <td class="divider-full" style="padding-bottom: 3pt; font-size: 14pt; font-weight: bold; color: #aaa;">VOID - NON-NEGOTIABLE / DIRECT DEPOSIT SETUP</td>
                                <td style="width: 30pt; text-align: right; vertical-align: bottom; font-size: 20pt; font-weight: bold;">$</td>
                                <td class="divider-full" style="width: 1.2in; text-align: center; vertical-align: bottom; font-size: 20pt; font-weight: bold; padding-bottom: 3pt;">VOID</td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-top: 25pt;">
                        <table style="width: 100%;">
                            <tr>
                                <td class="divider-full" style="height: 35pt;"></td>
                                <td style="width: 1.0in; text-align: right; vertical-align: bottom; font-size: 14pt; font-weight: bold;">DOLLARS</td>
                            </tr>
                        </table>
                    </div>

                    <div class="bank-branding-v3">
                        PINELLAS FEDERAL CREDIT UNION
                        <div style="font-size: 12pt; font-weight: normal; margin-top: 2pt;">PINELLASCU.COM</div>
                    </div>

                    <div style="margin-top: 40pt;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 65pt; font-size: 14pt; vertical-align: bottom;">MEMO</td>
                                <td class="divider-full" style="width: 3.2in;"></td>
                                <td style="width: 20pt;"></td>
                                <td style="vertical-align: bottom;">
                                    <div style="border-top: 1.5pt solid #000; text-align: center; padding-top: 5pt; font-size: 10pt; font-weight: bold;">AUTHORIZED SIGNATURE</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- ZERO-FAILURE MICR LINE (ATOMIC SVG OBJECTS) -->
                <div class="micr-line-professional">
                    @foreach(str_split($routingLine) as $c)
                        @if(isset($glyphs[$c])) <img src="{{ $glyphs[$c] }}" class="micr-glyph-atomic"> @endif
                    @endforeach
                    <img src="{{ $glyphs['d'] }}" class="micr-glyph-atomic" style="opacity:0; width: 0.25in;"> 
                    @foreach(str_split($accountLine) as $c)
                        @if ($c == 'u') <img src="{{ $onUsSym }}" class="micr-glyph-atomic">
                        @elseif(isset($glyphs[$c])) <img src="{{ $glyphs[$c] }}" class="micr-glyph-atomic"> @endif
                    @endforeach
                    <img src="{{ $glyphs['d'] }}" class="micr-glyph-atomic" style="opacity:0; width: 0.25in;">
                    @foreach(str_split($checkNumLine) as $c)
                        @if(isset($glyphs[$c])) <img src="{{ $glyphs[$c] }}" class="micr-glyph-atomic"> @endif
                    @endforeach
                </div>
            </div>

            <p style="text-align: center; font-size: 9pt; color: #888; margin-top: 60pt; border-top: 1px dotted #ccc; padding-top: 15pt;">
                This document is a formal Direct Deposit Authorization request generated via Pinellas FCU Online Banking.<br>
                <strong>The digitally generated voided check is for reference purposes and is strictly non-negotiable.</strong>
            </p>
        </div>
    </div>
</body>
</html>
