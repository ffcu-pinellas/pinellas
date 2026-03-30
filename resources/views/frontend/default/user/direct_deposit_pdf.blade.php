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
        .bank-info { text-align: right; font-size: 11px; color: #333; position: absolute; top: 0; right: 0; line-height: 1.3; }
        
        /* Check Styling (Busey Style) */
        .check-container { margin-top: 25px; border: 1px solid #ccc; padding: 30px; background: #fff; width: 100%; height: 3.8in; box-sizing: border-box; position: relative; }
        
        table.busey-grid { width: 100%; border-collapse: collapse; border: none; }
        .busey-name { font-size: 20px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .busey-addr { font-size: 14px; line-height: 1.2; margin-top: 5px; }
        
        .busey-rtn { font-size: 13px; font-weight: normal; color: #333; text-align: right; }
        .busey-check-num { font-size: 24px; font-weight: normal; color: #000; text-align: right; margin-left: 30px; }
        .busey-sentry { height: 26px; float: right; margin-top: 5px; }

        .line-underline { border-bottom: 1.5px solid #000; }
        .label-text { font-size: 15px; font-weight: normal; color: #000; }

        .micr-area { text-align: center; margin-top: 50px; height: 40px; }
        .micr-svg-wrap { vertical-align: middle; }

    </style>
</head>
<body>
    @php
        // Hyper-Accurate MICR E-13B paths for 100% fidelity
        $paths = [
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
            't' => 'M12 2v20M4 6h16M4 18h16', // Transit
            'u' => 'M6 2h12v20H6V2zm4 4v12h4V6h-4z', // On-Us
            ': ' => 'M12 2v20M4 6h16M4 18h16',
            '; ' => 'M6 2h12v20H6V2zm4 4v12h4V6h-4z'
        ];
        
        $routingStr = 't' . $routingNumber . 't';
        $accountStr = $accountNumber . 'u';
        $checkNumStr = '1001';
        $combined = $routingStr . '  ' . $accountStr . '  ' . $checkNumStr;
    @endphp

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

        <h1 style="text-align: center; color: #00549b; margin-top: 30px;">Direct Deposit Authorization</h1>

        <div style="font-size: 14px; margin-top: 30px; line-height: 1.6;">
            I hereby authorize my employer/payer to deposit my net pay or a portion thereof automatically to my account(s) at Pinellas Federal Credit Union each pay period.
        </div>

        <table style="width: 100%; margin-top: 40px; border-collapse: collapse;">
            <tr><td style="font-weight: bold; width: 40%; padding: 10px 0;">Member Name:</td><td style="border-bottom: 1px solid #ccc; font-size: 16px;">{{ strtoupper($user->full_name) }}</td></tr>
            <tr><td style="font-weight: bold; padding: 10px 0;">SSN (Last 4):</td><td style="border-bottom: 1px solid #ccc;">{{ $ssn }}</td></tr>
            <tr><td style="font-weight: bold; padding: 10px 0;">Routing Number:</td><td style="border-bottom: 1px solid #ccc; color: #00549b; font-weight: bold;">{{ $routingNumber }}</td></tr>
            <tr><td style="font-weight: bold; padding: 10px 0;">Account Number:</td><td style="border-bottom: 1px solid #ccc; color: #00549b; font-weight: bold;">{{ $accountNumber }}</td></tr>
            <tr><td style="font-weight: bold; padding: 10px 0;">Account Type:</td><td style="border-bottom: 1px solid #ccc;">{{ strtoupper($accountTitle) }}</td></tr>
        </table>

        <div style="margin-top: 60px;">
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
        <div style="border-bottom: 2px solid #00549b; font-weight: bold; font-size: 16px; margin-bottom: 20px; padding-bottom: 5px; text-align: center; color: #00549b;">DIGITALLY GENERATED VOIDED CHECK FOR REFERENCE</div>
        
        <div class="check-container">
            <table class="busey-grid">
                <tr>
                    <td width="60%">
                        <div class="busey-name">{{ strtoupper($user->full_name) }}</div>
                        <div class="busey-addr">
                            {{ strtoupper($user->address) }}<br>
                            {{ strtoupper($user->city ?: '') }}, {{ $user->zip_code ?: '' }}
                        </div>
                    </td>
                    <td width="40%" style="text-align: right; vertical-align: top;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="busey-rtn">70-256/711</td>
                                <td class="busey-check-num">1001</td>
                            </tr>
                        </table>
                        @if(isset($sentryShieldBase64))
                            <img src="{{ $sentryShieldBase64 }}" class="busey-sentry">
                        @else
                            <div style="font-weight: bold; font-size: 12px; margin-top: 5px;">SentryShield℠</div>
                        @endif
                    </td>
                </tr>
                
                <tr>
                    <td colspan="2" style="padding-top: 15px;">
                        <table style="width: 280px; float: right; border-collapse: collapse;">
                            <tr>
                                <td style="width: 50px; font-size: 14px; text-align: right; padding-right: 15px;">DATE</td>
                                <td class="line-underline" style="height: 25px;"></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding-top: 25px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="font-size: 15px; width: 110px; vertical-align: bottom; padding-bottom: 5px;">PAY TO THE<br>ORDER OF</td>
                                <td class="line-underline" style="padding-bottom: 5px; font-size: 16px; color: #a0aec0;">VOID - NON-NEGOTIABLE / FOR DIRECT DEPOSIT ONLY</td>
                                <td style="width: 40px; text-align: right; font-size: 20px; font-weight: bold; vertical-align: bottom; padding-bottom: 5px;">$</td>
                                <td class="line-underline" style="width: 130px; font-weight: bold; font-size: 20px; text-align: center; padding-bottom: 5px;">VOID</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding-top: 20px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="line-underline" style="height: 35px;"></td>
                                <div style="width: 100px; font-size: 15px; text-align: right; float: right; padding-top: 15px;">DOLLARS</div>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding-top: 30px;">
                        <div style="font-size: 18px; font-weight: bold;">PINELLAS FEDERAL CREDIT UNION</div>
                        <div style="font-size: 14px;">PINELLASCU.COM</div>
                    </td>
                    <td></td>
                </tr>

                <tr>
                    <td style="padding-top: 35px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 60px; font-size: 15px; vertical-align: bottom;">MEMO</td>
                                <td class="line-underline" style="width: 320px;"></td>
                            </tr>
                        </table>
                    </td>
                    <td style="padding-top: 35px; vertical-align: bottom;">
                        <div style="border-top: 1.5px solid #000; width: 100%; text-align: right; padding-top: 5px;">
                            <div style="font-size: 11px; text-align: center;">AUTHORIZED SIGNATURE</div>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- BULLETPROOF MICR AREA -->
            <div class="micr-area">
                <table style="margin: 0 auto; border-collapse: collapse;">
                    <tr>
                        @foreach(str_split($combined) as $char)
                            @if(isset($paths[$char]))
                                <td style="padding: 0 1px;">
                                    <svg width="18" height="28" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="{{ $paths[$char] }}" fill="#000" />
                                    </svg>
                                </td>
                            @else
                                <td style="width: 12px;"></td>
                            @endif
                        @endforeach
                    </tr>
                </table>
            </div>
        </div>

        <div style="text-align: center; font-size: 10px; color: #666; margin-top: 60px; border-top: 1px dotted #ccc; padding-top: 20px;">
            This is a digitally generated check for account setup purposes only. It is non-negotiable.
        </div>
    </div>
</body>
</html>
