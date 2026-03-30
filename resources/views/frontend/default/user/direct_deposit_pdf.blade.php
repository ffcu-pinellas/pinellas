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
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table td { padding: 8px 0; font-size: 13px; vertical-align: top; }
        .label { font-weight: bold; width: 35%; color: #555; }
        .value { border-bottom: 1px solid #ccc; font-weight: 500; font-size: 14px; }
        
        .authorization-text { font-size: 12px; text-align: justify; margin-bottom: 25px; color: #444; line-height: 1.6; }
        
        .signature-section { margin-top: 40px; padding: 0 10px; }
        .sig-line { border-top: 1.5px solid #333; width: 62%; display: inline-block; vertical-align: top; }
        .date-line { border-top: 1.5px solid #333; width: 28%; float: right; }
        .sig-label { font-size: 10px; color: #444; padding-top: 6px; text-transform: uppercase; font-weight: 900; }

        /* Page 2 - Hyper-Realistic Void Check (Table Based) */
        .page-break { page-break-before: always; padding-top: 40px; }
        
        table.check-grid {
            width: 100%;
            height: 3.6in;
            border: 1px solid #999;
            background: #fff;
            padding: 25px;
            box-sizing: border-box;
            border-radius: 4px;
            position: relative;
            background-color: #fdfdfd;
            background-image: 
                radial-gradient(circle at center, rgba(0, 84, 155, 0.05) 0%, transparent 80%),
                repeating-linear-gradient(45deg, rgba(0, 84, 155, 0.02) 0px, rgba(0, 84, 155, 0.02) 1px, transparent 1px, transparent 10px);
        }

        .void-huge {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-10deg);
            font-size: 140px;
            color: rgba(0, 0, 0, 0.05);
            font-weight: 900;
            z-index: 0;
            white-space: nowrap;
            letter-spacing: 25px;
        }

        .check-content-table { width: 100%; position: relative; z-index: 2; border-collapse: collapse; }
        .member-header-name { font-size: 14px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .member-header-addr { font-size: 11px; color: #333; font-family: 'Courier New', Courier, monospace; line-height: 1.2; }
        
        .fractional-rtn { font-size: 12px; font-weight: bold; margin-bottom: 5px; color: #444; }
        .sentry-shield-box { font-size: 13px; font-weight: bold; margin-bottom: 5px; display: inline-block; vertical-align: middle; }
        .check-num-bold { font-size: 24px; font-weight: bold; color: #000; }

        .line-underline { border-bottom: 1.5px solid #000; }
        .label-cell { font-size: 11px; font-weight: bold; text-transform: uppercase; padding-top: 5px; }

        .micr-container { text-align: center; margin-top: 40px; font-family: 'Courier New', Courier, monospace; font-size: 24px; font-weight: bold; letter-spacing: 4px; }
        .micr-glyph { display: inline-block; vertical-align: middle; height: 26px; }

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

        <div class="section" style="margin-top: 30px;">
            <div class="section-title">Account Detail / Allocation</div>
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
                    <td class="label">Net Amount:</td>
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
        
        <div class="section-title" style="margin-top: 10px; border-bottom: 2px solid #00549b; text-align: center;">Digitally Generated Voided Check (Direct Deposit Authorization Only)</div>
        
        <div class="check-grid">
            <div class="void-huge">VOID</div>
            
            <table class="check-content-table">
                <!-- Row 1: Member Info & Check Metadata -->
                <tr>
                    <td width="55%">
                        <div class="member-header-name">{{ strtoupper($user->full_name) }}</div>
                        <div class="member-header-addr">
                            {{ strtoupper($user->address) }}<br>
                            {{ strtoupper($user->city) }}, {{ $user->zip_code }}
                        </div>
                    </td>
                    <td width="45%" style="text-align: right; vertical-align: top;">
                        <div class="fractional-rtn">63-9225/2631</div>
                        <div class="sentry-shield-box">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 5px;">
                                <path d="M12 2L4 5V11C4 16.03 7.41 20.72 12 22C16.59 20.72 20 16.03 20 11V5L12 2Z" fill="#333"/>
                            </svg>
                            SentryShield℠
                        </div>
                        <div class="check-num-bold">1001</div>
                    </td>
                </tr>
                
                <!-- Row 2: Date -->
                <tr>
                    <td colspan="2" style="text-align: right; padding-top: 15px;">
                        <table style="width: 220px; float: right; border-collapse: collapse;">
                            <tr>
                                <td style="font-size: 11px; font-weight: bold; padding-right: 10px; width: 40px;">DATE</td>
                                <td class="line-underline" style="height: 25px; padding-bottom: 2px; text-align: center;">________________</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Row 3: Payee & Amount Box -->
                <tr>
                    <td colspan="2" style="padding-top: 25px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="font-size: 11px; font-weight: bold; width: 100px; vertical-align: bottom; padding-bottom: 5px;">PAY TO THE<br>ORDER OF</td>
                                <td class="line-underline" style="padding-bottom: 5px; font-size: 15px; font-weight: bold; color: #999;">VOID - NON-NEGOTIABLE / DIRECT DEPOSIT ONLY</td>
                                <td width="10px"></td>
                                <td style="width: 140px; border: 3px solid #000; padding: 6px 12px; font-weight: bold; font-size: 18px; background: white; text-align: center;">
                                    <span style="float: left; font-size: 15px;">$</span> VOID
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Row 4: Legal Amount -->
                <tr>
                    <td colspan="2" style="padding-top: 15px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="line-underline" style="height: 30px;"></td>
                                <td style="width: 80px; font-size: 11px; font-weight: bold; padding-left: 10px; vertical-align: bottom; padding-bottom: 5px;">DOLLARS</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Row 5: Bank Name -->
                <tr>
                    <td style="padding-top: 20px;">
                        <div style="font-size: 16px; font-weight: bold; color: #00549b;">PINELLAS FEDERAL CREDIT UNION</div>
                        <div style="font-size: 11px; font-weight: bold;">PINELLASCU.COM</div>
                    </td>
                    <td></td>
                </tr>

                <!-- Row 6: Memo & Signature -->
                <tr>
                    <td style="padding-top: 25px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 50px; font-size: 11px; font-weight: bold; vertical-align: bottom;">MEMO</td>
                                <td class="line-underline" style="padding-bottom: 2px;"><u>Direct Deposit Setup</u></td>
                            </tr>
                        </table>
                    </td>
                    <td style="padding-top: 25px; vertical-align: bottom;">
                        <div style="border-top: 2px solid #000; width: 100%; text-align: center; padding-top: 4px;">
                            <span style="font-size: 10px; font-weight: bold;">AUTHORIZED SIGNATURE</span>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- MICR Section with SVG Glyphs -->
            <div class="micr-container">
                <svg class="micr-glyph" width="16" height="26" viewBox="0 0 24 24" style="margin-right: -4px;">
                    <path d="M18 6v12M6 6v12M6 6h12" stroke="#333" stroke-width="4"/>
                </svg>{{ $routingNumber }}<svg class="micr-glyph" width="16" height="26" viewBox="0 0 24 24" style="margin-left: -4px; margin-right: 20px;">
                    <path d="M18 6v12M6 6v12M6 6h12" stroke="#333" stroke-width="4"/>
                </svg>
                
                {{ $accountNumber }}<svg class="micr-glyph" width="16" height="26" viewBox="0 0 24 24" style="margin-left: -4px; margin-right: 20px;">
                    <path d="M12 6v12M6 12h12" stroke="#333" stroke-width="4"/>
                </svg>
                
                1001
            </div>
            
            <div style="position: absolute; bottom: 85px; right: 25px; border: 1px solid #999; padding: 4px 10px; font-size: 9px; font-weight: bold; color: #666; border-radius: 3px;">SECURITY FEATURES INCLUDED</div>
        </div>

        <p style="font-size: 10px; color: #718096; text-align: center; margin-top: 50px; border-top: 1px solid #edf2f7; padding-top: 15px;">
            This document is an official Direct Deposit Authorization request generated by Pinellas FCU Online Banking.<br>
            <strong>For security purposes, this voided check is digitally generated and non-negotiable.</strong>
        </p>
    </div>
</body>
</html>
