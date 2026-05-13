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
            border-bottom: 2px solid #00549b;
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
            color: #00549b;
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
        .document-content {
            font-size: 14px;
            color: #1a202c;
            line-height: 1.6;
        }
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
            <strong>Pinellas Federal Credit Union</strong><br>
            Corporate Offices • P.O. Box 2500<br>
            Largo, FL 33779-2500<br>
            (737) 410-5689 | pinellascu.com
        </div>
        <div class="clear"></div>
    </div>

    <div class="statement-banner">
        <h1>{{ $title }}</h1>
        <p>DATE: {{ strtoupper(now()->format('M d, Y')) }}</p>
    </div>

    <div class="document-content">
        {!! $content !!}
    </div>

    <div class="footer-notice">
        This is an official document issued by Pinellas Federal Credit Union. For any inquiries or verification, please contact our support. <br>
        <strong>Pinellas Federal Credit Union is Federally Insured by NCUA. Equal Housing Lender.</strong>
    </div>
</body>
</html>
