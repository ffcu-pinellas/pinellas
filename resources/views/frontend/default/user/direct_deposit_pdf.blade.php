<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Direct Deposit Authorization - {{ setting('site_title') ?? 'FrontField Credit Union' }}</title>
    <style>
        @page { margin: 0; padding: 0; size: 8.5in 11in; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #000; margin: 0; padding: 0; background: #fff; }
        
        /* Master Container */
        .page-wrapper { width: 8.5in; height: 11in; position: relative; overflow: hidden; }
        .inner-shell { width: 7.0in; margin: 0 auto; padding-top: 30pt; }

        /* Watermark */
        .watermark-overlay { position: absolute; top: 18%; left: 1.25in; width: 6.0in; opacity: 0.08; z-index: -1; text-align: center; }
        .watermark-overlay img { width: 4.5in; }

        /* Typography & Utilities */
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Header Components */
        .bank-header-row { border-bottom: 2pt solid #00549b; padding-bottom: 10pt; margin-bottom: 20pt; position: relative; }
        .bank-logo-img { width: 180pt; }
        .bank-contact { position: absolute; top: 0; right: 0; text-align: right; font-size: 8.5pt; color: #333; line-height: 1.25; }

        /* Document Body */
        .letter-title { font-size: 20pt; font-weight: bold; color: #00549b; text-align: center; margin: 20pt 0; }
        .letter-para { font-size: 10.5pt; line-height: 1.5; text-align: justify; margin-bottom: 15pt; }
        
        table.data-grid { width: 100%; border-collapse: collapse; margin-bottom: 20pt; }
        table.data-grid td { padding: 8pt 0; font-size: 10.5pt; border-bottom: 0.5pt solid #eee; }
        .label-cell { width: 35%; font-weight: bold; color: #444; }

        .highlight-box { border: 2pt solid #00549b; background: #f9fbff; padding: 15pt; border-radius: 6pt; margin-bottom: 30pt; }

    </style>
</head>
<body>
    <!-- PAGE 1: Professional Header -->
    <div class="page-wrapper">
        <div class="watermark-overlay">
            @if(isset($logoBase64)) <img src="{{ $logoBase64 }}"> @endif
        </div>
        
        <div class="inner-shell">
            <div class="bank-header-row">
                @if(isset($logoBase64)) <img src="{{ $logoBase64 }}" class="bank-logo-img"> @endif
                <div class="bank-contact">
                    <strong>{{ setting('site_title') ?? 'FrontField Credit Union' }}</strong><br>
                    Corporate Offices • P.O. Box 2300<br>
                    Cleveland, OH 44166<br>
                    {{ parse_url(config('app.url'), PHP_URL_HOST) ?? 'frontfieldcu.pro' }}
                </div>
            </div>

            <div class="letter-title">Direct Deposit Authorization</div>

            <p class="letter-para">
                I hereby authorize my employer/payer, listed below, to deposit my net pay or a portion thereof automatically to my account(s) at {{ setting('site_title') ?? 'FrontField Credit Union' }} each pay period. This authorization remains in effect until {{ setting('site_title') ?? 'FrontField Credit Union' }} has received written notification from me.
            </p>

            <table class="data-grid">
                <tr><td class="label-cell">Employer / Payer Name:</td><td>____________________________________________</td></tr>
                <tr><td class="label-cell">Member Full Name:</td><td>{{ strtoupper($user->full_name) }}</td></tr>
                <tr><td class="label-cell">SSN (Last 4):</td><td>{{ $ssn }}</td></tr>
                <tr><td class="label-cell">Member Address:</td><td>{{ strtoupper($fullAddress) }}</td></tr>
            </table>

            <div class="highlight-box">
                <table class="data-grid" style="margin-bottom: 0;">
                    <tr><td class="label-cell">Bank Name:</td><td>{{ setting('site_title') ?? 'FrontField Credit Union' }}</td></tr>
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
            
            <p style="text-align: center; font-size: 9pt; color: #888; margin-top: 100pt; border-top: 1px dotted #ccc; padding-top: 15pt;">
                This document is generated via {{ setting('site_title') ?? 'FrontField Credit Union' }} Online Banking for Direct Deposit setup.
            </p>
        </div>
    </div>
</body>
</html>
