<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $siteTitle }}</title>
    <style type="text/css">
        body { margin: 0; padding: 0; background-color: #e6e9ef; font-family: Arial, Helvetica, sans-serif; }
        .v1v1email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #cccccc; border-radius: 4px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .v1v1header { background-color: #004d73; padding: 24px 20px; text-align: center; }
        .v1v1header p { color: #d9e1f2; font-size: 12px; margin: 8px 0 0 0; font-family: Arial, Helvetica, sans-serif; }
        .v1v1compliance-bar { background-color: #f4f6f9; padding: 10px 20px; border-bottom: 1px solid #dddddd; font-size: 12px; color: #333333; font-weight: bold; }
        .v1v1content { padding: 24px 20px 32px 20px; font-size: 14px; line-height: 1.5; color: #000000; }
        h2 { font-size: 18px; color: #004d73; margin-top: 24px; margin-bottom: 12px; border-bottom: 1px solid #dddddd; padding-bottom: 4px; }
        .v1v1account-box { background-color: #f8f9fc; border: 1px solid #d0d7de; padding: 12px 16px; margin: 16px 0; border-radius: 4px; }
        .v1v1account-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e6ea; font-size: 14px; }
        .v1v1account-row:last-child { border-bottom: none; }
        .v1v1btn-wrap { text-align: center; margin: 28px 0 12px; }
        a.btn { display: inline-block; background-color: #004d73; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 4px; font-weight: bold; font-size: 14px; }
        .v1v1footer { background-color: #f4f6f9; padding: 20px 20px; font-size: 11px; color: #666666; border-top: 1px solid #dddddd; text-align: center; line-height: 1.5; }
        .v1v1footer .muted { font-size: 11px; color: #888888; margin-top: 10px; }
    </style>
</head>
<body>
<div class="v1v1email-container">
    <!-- Header -->
    <div class="v1v1header">
        @if(!empty($siteLogoUrl))
            <a href="{{ $homeUrl }}"><img style="max-height: 60px; margin-bottom: 8px;" src="{{ $siteLogoUrl }}" alt="{{ $siteTitle }} Logo" /></a>
        @else
            <div style="font-size: 18px; font-weight: 700; color: #ffffff;">{{ $siteTitle }}</div>
        @endif
        <p>2555 East Bay Drive | Clearwater, FL 33764 | {{ $homeDomain }}</p>
    </div>

    <!-- Compliance / Notification bar -->
    <div class="v1v1compliance-bar">Membership & Account Services Division</div>

    <!-- Content -->
    <div class="v1v1content">
        <p>Dear {{ $fullName }},</p>
        <p>We are pleased to welcome you as a member of {{ $siteTitle }}. Your membership application has been approved, and your digital banking access is now fully active. Below are your official account structure and routing transit credentials. Please secure this information for your records.</p>
        
        <h2>Your Account Credentials</h2>
        <div class="v1v1account-box">
            <div class="v1v1account-row">
                <span><strong>Account Holder:</strong></span>
                <span>{{ $fullName }}</span>
            </div>
            <div class="v1v1account-row">
                <span><strong>Primary Checking Account:</strong></span>
                <span style="font-family: monospace; font-weight: bold;">{{ $checkingAccountNumber }}</span>
            </div>
            <div class="v1v1account-row">
                <span><strong>Primary Savings Account:</strong></span>
                <span style="font-family: monospace; font-weight: bold;">{{ $savingsAccountNumber }}</span>
            </div>
            <div class="v1v1account-row">
                <span><strong>ABA Routing Transit Number:</strong></span>
                <span style="font-family: monospace; font-weight: bold; color: #004d73;">{{ $routingNumber }}</span>
            </div>
        </div>

        <p>Through our online portal, you can verify your balances, send transfers instantly via Zelle®, pay bills, and set up your direct deposit routing details.</p>

        <div class="v1v1btn-wrap">
            <a href="{{ $loginUrl }}" class="btn">Access Online Banking</a>
        </div>
    </div>

    <!-- Footer -->
    <div class="v1v1footer">
        <strong>{{ $siteTitle }}</strong>
        <div class="muted">For your security, we will never ask for your full account number, password, or PIN by email.</div>
        <div style="margin-top: 15px; font-size: 10px; color: #888888; border-top: 1px solid #dddddd; padding-top: 15px;">
            Federally Insured by NCUA | Member NDIC | Equal Housing Lender<br>
            &copy; 2026 {{ $siteTitle }}. All rights reserved.
        </div>
    </div>
</div>
</body>
</html>
