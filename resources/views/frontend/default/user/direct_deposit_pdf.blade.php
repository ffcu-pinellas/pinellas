<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Direct Deposit Authorization - Pinellas FCU</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .container { padding: 30px; }
        .header { border-bottom: 2px solid #00549b; pb: 15px; mb: 20px; overflow: hidden; }
        .logo { float: left; width: 220px; }
        .bank-info { float: right; text-align: right; font-size: 11px; color: #666; }
        .title { text-align: center; font-size: 22px; font-weight: bold; color: #00549b; margin: 25px 0; text-transform: uppercase; }
        
        .section { margin-bottom: 25px; }
        .section-title { font-size: 14px; font-weight: bold; color: #00549b; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 15px; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        td { padding: 8px 0; font-size: 13px; }
        .label { font-weight: bold; width: 35%; color: #555; }
        .value { border-bottom: 1px solid #ccc; }
        
        .authorization-text { font-size: 12px; text-align: justify; margin-bottom: 20px; color: #444; }
        
        .signature-box { border-top: 1px solid #333; width: 60%; margin-top: 40px; }
        .date-box { border-top: 1px solid #333; width: 30%; float: right; margin-top: 40px; }
        
        /* Void Check Graphic */
        .void-check {
            margin-top: 50px;
            border: 2px solid #999;
            border-radius: 8px;
            padding: 20px;
            background-color: #f9f9f9;
            position: relative;
            overflow: hidden;
            width: 100%;
        }
        .void-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 110px;
            color: rgba(0, 0, 0, 0.05);
            font-weight: bold;
            z-index: 0;
            white-space: nowrap;
        }
        .check-content { position: relative; z-index: 1; }
        .check-header { font-size: 14px; font-weight: bold; margin-bottom: 30px; }
        .check-date { float: right; border-bottom: 1px solid #333; width: 120px; text-align: center; }
        .check-pay-to { margin-bottom: 25px; border-bottom: 1px solid #333; padding-bottom: 5px; }
        .check-amount { float: right; border: 1px solid #333; padding: 5px 15px; width: 100px; text-align: right; background: white; }
        
        .micr-line {
            font-family: 'Courier New', Courier, monospace;
            font-size: 18px;
            margin-top: 40px;
            letter-spacing: 2px;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" class="logo">
            <div class="bank-info">
                Pinellas Federal Credit Union<br>
                P.O. Box 2270<br>
                Largo, FL 33779-2270<br>
                www.pinellasfcu.org | (727) 586-4422
            </div>
        </div>

        <div class="title">Direct Deposit Authorization</div>

        <div class="authorization-text">
            I hereby authorize my employer/payer, listed below, to deposit my net pay or a portion thereof automatically to my account(s) indicated below each pay period. This authorization is to remain in full force and effect until Pinellas Federal Credit Union has received written notification from me of its termination in such time and in such manner as to afford Pinellas Federal Credit Union and my employer a reasonable opportunity to act on it.
        </div>

        <div class="section">
            <div class="section-title">Employee / Member Information</div>
            <table>
                <tr>
                    <td class="label">Member Name:</td>
                    <td class="value">{{ $user->full_name }}</td>
                </tr>
                <tr>
                    <td class="label">Social Security Number:</td>
                    <td class="value">*** - ** - ****</td>
                </tr>
                <tr>
                    <td class="label">Phone Number:</td>
                    <td class="value">{{ $user->phone }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Account Information</div>
            <table>
                <tr>
                    <td class="label">Financial Institution:</td>
                    <td class="value">Pinellas Federal Credit Union</td>
                </tr>
                <tr>
                    <td class="label">Routing Number (RTN):</td>
                    <td class="value" style="font-weight: bold; color: #00549b;">{{ $routingNumber }}</td>
                </tr>
                <tr>
                    <td class="label">Account Number:</td>
                    <td class="value" style="font-weight: bold; color: #00549b; font-family: monospace; font-size: 15px;">{{ $accountNumber }}</td>
                </tr>
                <tr>
                    <td class="label">Account Type:</td>
                    <td class="value">{{ $accountTitle }}</td>
                </tr>
                <tr>
                    <td class="label">Deposit Amount:</td>
                    <td class="value">Full Net Pay (or $___________ / ________%)</td>
                </tr>
            </table>
        </div>

        <div style="margin-top: 30px; overflow: hidden;">
            <div class="signature-box">
                <div style="font-size: 10px; color: #666; margin-top: 5px;">Member Signature</div>
            </div>
            <div class="date-box">
                <div style="font-size: 10px; color: #666; margin-top: 5px;">Date</div>
            </div>
        </div>

        <div class="void-check">
            <div class="void-watermark">VOID VOID VOID</div>
            <div class="check-content">
                <div class="check-header">
                    {{ strtoupper($user->full_name) }}<br>
                    <span style="font-size: 10px; font-weight: normal;">123 MEMBER ADDRESS ST.<br>LARGO, FL 33770</span>
                    <div class="check-date">DATE: _________</div>
                </div>
                
                <div class="check-pay-to">
                    PAY TO THE ORDER OF: <span style="font-weight: bold; color: #999;">VOID - DIRECT DEPOSIT AUTHORIZATION</span>
                    <div class="check-amount">$ **VOID**</div>
                </div>

                <div style="margin-top: 10px;">
                    <span style="font-size: 12px; font-weight: bold;">Pinellas Federal Credit Union</span><br>
                    <span style="font-size: 10px;">MEMO: <u>Direct Deposit Setup</u></span>
                </div>

                <div class="micr-line">
                    ⑆{{ $routingNumber }}⑆  {{ $accountNumber }}⑈  0001
                </div>
            </div>
        </div>
        
        <p style="font-size: 9px; color: #888; text-align: center; margin-top: 30px;">
            Generated via Pinellas FCU Digital Banking on {{ date('M j, Y g:i A') }}
        </p>
    </div>
</body>
</html>
