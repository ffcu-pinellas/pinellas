<div style="background-color: #f4f6f9; padding: 20px 0; font-family: 'Outfit', 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eef2f5;">
        <!-- Header Gradient -->
        <div style="background: linear-gradient(135deg, #00549b 0%, #002d62 100%); padding: 35px 30px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 700; letter-spacing: -0.5px;">Welcome to Pinellas FCU</h1>
            <p style="color: #a0c2e2; margin: 8px 0 0 0; font-size: 15px;">Your financial journey begins here</p>
        </div>

        <!-- Body Content -->
        <div style="padding: 40px 30px; color: #333333; line-height: 1.6;">
            <p style="font-size: 18px; margin-top: 0; font-weight: 600; color: #002d62;">Hello {{ $fullName }},</p>
            <p style="font-size: 15px; color: #555555;">Thank you for opening an account with Pinellas Federal Credit Union. We are excited to welcome you to our community. Below are your essential account details for your records and reference.</p>
            
            <!-- Details Card -->
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; margin: 30px 0;">
                <h3 style="margin-top: 0; margin-bottom: 18px; font-size: 16px; color: #00549b; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Your Account Credentials</h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 15px;">
                    <tr>
                        <td style="padding: 8px 0; color: #64748b; width: 45%;"><strong>Checking Account #:</strong></td>
                        <td style="padding: 8px 0; color: #0f172a; font-family: monospace; font-weight: bold; font-size: 16px;">{{ $checkingAccountNumber }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #64748b;"><strong>Savings Account #:</strong></td>
                        <td style="padding: 8px 0; color: #0f172a; font-family: monospace; font-weight: bold; font-size: 16px;">{{ $savingsAccountNumber }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #64748b;"><strong>Routing Transit #:</strong></td>
                        <td style="padding: 8px 0; color: #00549b; font-family: monospace; font-weight: bold; font-size: 16px;">{{ $routingNumber }}</td>
                    </tr>
                </table>
            </div>

            <p style="font-size: 15px; color: #555555;">With your new digital banking access, you can manage your balances, send funds instantly via Zelle®, pay bills, and apply for loans right from your dashboard.</p>
            
            <div style="text-align: center; margin: 35px 0 15px 0;">
                <a href="{{ $loginUrl }}" target="_blank" style="background: linear-gradient(135deg, #00549b 0%, #003b70 100%); color: #ffffff; text-decoration: none; padding: 14px 30px; font-weight: 600; font-size: 15px; border-radius: 6px; display: inline-block; box-shadow: 0 4px 10px rgba(0, 84, 155, 0.25);">
                    Access Online Banking
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #eef2f5; font-size: 12px; color: #64748b;">
            <p style="margin: 0 0 10px 0;">Federally Insured by NCUA | Equal Housing Lender</p>
            <p style="margin: 0;">© 2026 Pinellas Federal Credit Union. All rights reserved.</p>
        </div>
    </div>
</div>
