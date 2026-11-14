@php
    $docUser = $user ?? null;
    $docInst = $docUser ? ($docUser->institution ?? null) : ($institution ?? null);
    $docIsCustom = $docInst && !$docInst->is_default;
    $docTitle = $docInst ? $docInst->name : (setting('site_title') ?? 'FrontField Credit Union');
    $docPrimary = $docInst->primary_color ?? ($docIsCustom ? '#c8232c' : '#00549b');
    $docRegulator = $docIsCustom
        ? ('Member ' . ($docInst->regulator_type ? strtoupper($docInst->regulator_type) : 'FDIC') . '. Equal Housing Lender.')
        : 'Federally Insured by NCUA. Equal Housing Lender.';
    $docAddress = $docIsCustom
        ? ($docInst->mailing_address ?: 'Corporate Offices • P.O. Box 278')
        : 'Corporate Offices • P.O. Box 2300';
    $docCityStateZip = $docIsCustom
        ? ($docInst->mailing_city_state_zip ?: 'Cleveland, MS 38732')
        : 'Cleveland, OH 44166';
    $docPhone = $docIsCustom
        ? ($docInst->support_phone ?: '662-843-9461')
        : (setting('site_phone') ?: '216 230 1837');
    $docDomain = $docIsCustom
        ? "{$docInst->slug}.frontfieldcu.pro"
        : (parse_url(config('app.url'), PHP_URL_HOST) ?? 'frontfieldcu.pro');
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #334155;
            line-height: 1.5;
            margin: 0;
            padding: 40px;
            background-color: #ffffff;
            position: relative;
        }
        /* Watermark Styling */
        .watermark {
            position: fixed;
            top: 25%;
            left: 10%;
            width: 80%;
            opacity: 0.09;
            z-index: -1000;
            text-align: center;
        }
        .watermark img {
            width: 500px;
        }
        .header-container {
            border-bottom: 2px solid {{ $docPrimary }};
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            float: left;
            height: 55px;
        }
        .bank-details {
            float: right;
            text-align: right;
            font-size: 10px;
            color: #4a5568;
            line-height: 1.3;
        }
        .clear {
            clear: both;
        }
        .statement-banner {
            background: #f7fafc;
            padding: 15px 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }
        .statement-banner h1 {
            color: {{ $docPrimary }};
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .statement-banner p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #718096;
            font-weight: bold;
        }
        .meta-grid {
            width: 100%;
            margin-bottom: 30px;
        }
        .meta-box {
            width: 48%;
            vertical-align: top;
        }
        .section-title {
            font-size: 10px;
            font-weight: 800;
            color: #00549b;
            text-transform: uppercase;
            margin-bottom: 8px;
            border-bottom: 1px solid #edf2f7;
            padding-bottom: 4px;
        }
        .meta-content {
            font-size: 11px;
            color: #1a202c;
        }
        .document-content {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            color: #1a202c;
            line-height: 1.6;
            margin-top: 20px;
        }
        .document-content p { margin: 0 0 15px 0 !important; }
        .document-content strong, .document-content b { font-weight: bold !important; }
        .document-content i, .document-content em { font-style: italic !important; }
        .document-content u { text-decoration: underline !important; }
        .document-content ul, .document-content ol { margin-top: 0 !important; padding-left: 20px !important; }
        .document-content h1, .document-content h2, .document-content h3, .document-content h4 { margin: 15px 0 10px 0 !important; font-weight: bold !important; }
        .footer-notice {
            position: fixed;
            bottom: 40px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 9px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="watermark">
        @if(isset($logoBase64) && $logoBase64)
            <img src="{{ $logoBase64 }}">
        @else
            <img src="{{ asset('assets/external/images/logo.png') }}">
        @endif
    </div>

    <div class="header-container">
        @if(isset($logoBase64) && $logoBase64)
            <img src="{{ $logoBase64 }}" class="logo">
        @else
            <img src="{{ asset('assets/external/images/logo.png') }}" class="logo">
        @endif
        <div class="bank-details">
            <strong>{{ $docTitle }}</strong><br>
            {{ $docAddress }}<br>
            {{ $docCityStateZip }}<br>
            {{ $docPhone }} | {{ $docDomain }}
        </div>
        <div class="clear"></div>
    </div>

    <div class="statement-banner">
        <h1>{{ $title }}</h1>
        <p>DATE: {{ strtoupper(now()->format('M d, Y')) }}</p>
    </div>

    @if($user)
    <table class="meta-grid">
        <tr>
            <td class="meta-box">
                <div class="section-title">Member Information</div>
                <div class="meta-content">
                    <strong style="font-size: 13px;">{{ strtoupper($user->full_name) }}</strong><br>
                    {{ $user->address ?: 'NO ADDRESS ON FILE' }}<br>
                    {{ $user->city ?: '' }} {{ $user->zip_code ?: '' }}
                </div>
            </td>
            <td class="meta-box" style="padding-left: 4%;">
                <div class="section-title">Account Details</div>
                <div class="meta-content">
                    <strong>Main Account:</strong> {{ $user->account_number ?: 'N/A' }}<br>
                    <strong>Email:</strong> {{ $user->email ?: 'N/A' }}<br>
                    <strong>Phone:</strong> {{ $user->phone ?: 'N/A' }}
                </div>
            </td>
        </tr>
    </table>
    @endif

    <div class="document-content">
        {!! $content !!}
    </div>

    @if(isset($signature) && $signature && $signature->status === 'signed')
    <div style="margin-top: 28px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 14px 16px; background: #f8fafc; page-break-inside: avoid;">
        <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; color: #00549b; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 10px;">
            Executed Electronic Signature &amp; Cryptographic Verification Certificate
        </div>
        <table style="width: 100%; border-collapse: collapse; font-size: 10px; color: #334155;">
            <tr>
                <td style="width: 48%; vertical-align: middle; padding-right: 12px;">
                    @if(!empty($signature->signature_data))
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 10px; display: inline-block;">
                            <img src="{{ $signature->signature_data }}" alt="Member Signature" style="max-height: 62px; max-width: 240px;">
                        </div>
                    @endif
                    <div style="margin-top: 6px; font-weight: bold; color: #0f172a;">
                        Signed By: {{ strtoupper($user->full_name ?? 'Member') }}
                    </div>
                </td>
                <td style="width: 52%; vertical-align: top; line-height: 1.6;">
                    <strong>Status:</strong> Executed ({{ strtoupper($signature->signature_type) }})<br>
                    <strong>Timestamp (UTC):</strong> {{ data_get($signature->metadata, 'signed_at_utc') ?? optional($signature->signed_at)->format('Y-m-d H:i:s \U\T\C') }}<br>
                    <strong>Signer IP Address:</strong> {{ $signature->ip_address ?? 'Recorded' }}<br>
                    <strong>Certificate ID:</strong> #ESIGN-{{ str_pad((string) $signature->id, 6, '0', STR_PAD_LEFT) }}<br>
                    <strong>SHA-256 Digest:</strong> <span style="font-family: monospace; font-size: 8.5px; word-break: break-all;">{{ data_get($signature->metadata, 'verification_hash') }}</span>
                </td>
            </tr>
        </table>
    </div>
    @endif

    <div class="footer-notice">
        This is an official document issued by {{ $docTitle }}. For any inquiries or verification, please contact our support. <br>
        <strong>{{ $docTitle }} is {{ $docRegulator }}</strong>
    </div>
</body>
</html>
