<?php

namespace App\Services;

use App\Models\DocumentTemplate;

class BankTemplateService
{
    /**
     * Seed all 30+ high-fidelity banking templates
     */
    public static function seedTemplates($adminId = 1)
    {
        $templates = array_merge(
            self::getCoreCustomTemplates(),
            self::getOtherBankTemplates()
        );

        foreach ($templates as $tpl) {
            $tpl['category'] = 'external_bank_notification';
            $tpl['is_active'] = true;
            $tpl['created_by'] = $adminId;
            $tpl['email_salutation'] = '';
            
            DocumentTemplate::updateOrCreate(['name' => $tpl['name']], $tpl);
        }
    }

    /**
     * Return the 8 heavily customized core templates (Zelle, Chase, WF, BofA, PNC, Citi, Huntington, Citizens)
     */
    private static function getCoreCustomTemplates()
    {
        // 1. ZELLE (Standalone, self-contained, no footer needed)
        $zelleHtml = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zelle Payment Notification</title>
</head>
<body style="margin:0px;padding:0px;background-color:rgb(255,255,255);font-family:'Zelle Sans','Helvetica Neue',Helvetica,Arial,Verdana,'Trebuchet MS',sans-serif">
    <div style="min-width:320px;margin:0px auto;background-color:rgb(255,255,255)">
        <div style="background-color:rgb(255,255,255)">
            <div style="margin:0px auto;min-width:320px;max-width:500px;width:calc(19000% - 98300px);word-break:break-word">
                <div style="border-collapse:collapse;width:100%">
                    <div style="min-width:320px;max-width:500px;width:calc(18000% - 89500px);background-color:transparent">
                        <div style="width:100%!important;background-color:transparent">
                            <div style="border:0px solid transparent;padding:0px">
                                <div style="padding:10px">
                                    <div align="center">
                                        <div style="border-top-width:10px;border-top-style:solid;width:100%;line-height:0px;border-top-color:transparent">&nbsp;</div>
                                    </div>
                                </div>

                                <div align="center" style="padding-right:0px;padding-left:0px">
                                    <a href="https://www.zellepay.com/" target="_blank">
                                        <img align="middle" border="0" src="[[APP_URL]]/assets/images/bank_logos/zelle.png" alt="Zelle Logo" title="Zelle Logo" style="outline:none;text-decoration:none;clear:both;border:none;float:none;width:100%;max-width:125px;display:block!important" width="125" height="52">
                                    </a>
                                </div>

                                <div align="center" style="padding-top:20px;">
                                    <div style="font-family:Helvetica;font-size:27px;font-weight:normal;line-height:2.6;color:white">
                                        <center style="font-family:Helvetica">
                                            <span style="margin-left:auto;margin-right:auto;border-radius:500px;display:block;font-family:Helvetica;font-size:27px;font-weight:normal;height:80px;text-align:center;vertical-align:middle;text-decoration:none;width:80px;white-space:nowrap;letter-spacing:-0.000356px;overflow:visible;line-height:2.5;background-color:rgb(179,179,179);color:white">
                                                <div id="circle" style="font-family:Helvetica">[[INITIALS]]</div>
                                            </span>
                                        </center>
                                    </div>
                                </div>

                                <div style="padding-right:0px;padding-left:0px;padding-top:40px">
                                    <div style="display:table;text-align:center;font-size:30px;line-height:30px;margin:auto;color:rgb(0,0,0)">
                                        <div style="display:table-cell;vertical-align:middle;font-size:30px;font-family:Helvetica">
                                            <p style="margin:0px;font-size:20px;line-height:25px;text-align:center;font-family:Helvetica">
                                                <span style="font-size:20px;line-height:25px;font-family:Helvetica">
                                                    [[ZELLE_BODY_TEXT]]
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                               [[ZELLE_BUTTON_BLOCK]]

                                <div style="padding:10px">
                                    <div align="center">
                                        <div>
                                            <p style="text-align:center;font-size:16px">
                                                This payment was directed to your registered email:
                                            </p>
                                            <p>
                                                <a href="#" style="font-size:20px;text-decoration:none!important;color:rgb(110,26,201)">
                                                    <b>[[RECIPIENT_EMAIL]]</b>
                                                </a>
                                            </p>
                                            <p style="text-align:center;font-size:14px;opacity:0.9;color:rgb(74,74,74)">
                                                [[ZELLE_SUBTEXT]]
                                            </p>
                                            <hr style="border: none; border-top: 1px solid #eeeeee; margin: 25px 0;">
                                        </div>
                                    </div>
                                </div>

                                <div style="padding:10px">
                                    <div align="center">
                                        <div>
                                            <p style="text-align:center;color:rgb(112,112,112);font-size:14px;line-height:20px;">
                                                <span>Zelle</span><span>®</span> is a fast, safe & easy way to send money to and receive money from friends, family and others you trust.
                                            </p>
                                            <p style="text-align:center;color:rgb(112,112,112);font-size:14px;">
                                                For more information, please visit
                                                <a style="text-decoration:none;color:rgb(110,26,201)" href="https://www.zellepay.com/support" target="_blank">https://www.zellepay.com</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="background-color:rgb(244,244,244);margin-top:40px;">
            <div style="margin:0px auto;min-width:320px;max-width:500px;width:calc(19000% - 98300px);word-break:break-word;background-color:transparent">
                <div style="border-collapse:collapse;width:100%">
                    <div style="min-width:320px;max-width:500px;width:calc(18000% - 89500px);background-color:transparent">
                        <div style="width:100%!important;background-color:transparent">
                            <div style="border:0px solid transparent;padding:25px 0px">
                                <div align="center" style="padding-right:20px;padding-left:20px">
                                    <a href="https://www.zellepay.com/" target="_blank">
                                        <img align="middle" border="0" src="[[APP_URL]]/assets/images/bank_logos/zelle.png" alt="Zelle Logo" title="Zelle Logo" style="outline:none;text-decoration:none;clear:both;border:0px;height:auto;float:none;width:100%;max-width:69px;display:block!important" width="69">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="background-color:rgb(244,244,244)">
            <div style="margin:0px auto;min-width:320px;max-width:500px;width:calc(19000% - 98300px);word-break:break-word;background-color:transparent">
                <div style="border-collapse:collapse;display:table;width:100%">
                    <div style="min-width:320px;max-width:500px;width:calc(18000% - 89500px);background-color:transparent">
                        <div style="width:100%!important;background-color:transparent">
                            <div style="border:0px solid transparent;padding:15px 0px">
                                <div style="font-size:16px;font-family:'Zelle Sans','Helvetica Neue',Helvetica,Arial,Verdana,'Trebuchet MS',sans-serif;text-align:center;">
                                    <table align="center" style="display:table;min-width:240px;max-width:300px;font-family:'Zelle Sans','Helvetica Neue',Helvetica,Arial,Verdana,'Trebuchet MS',sans-serif">
                                        <tbody>
                                            <tr align="center">
                                                <td><a style="font-size:14px;color:rgb(110,26,201);text-decoration:none;" href="https://www.zellepay.com/support/contact" target="_blank">Contact</a></td>
                                                <td><a style="font-size:14px;color:rgb(110,26,201);text-decoration:none;" href="https://www.zellepay.com/privacy-policy" target="_blank">Privacy</a></td>
                                                <td><a style="font-size:14px;color:rgb(110,26,201);text-decoration:none;" href="https://www.zellepay.com/legal-and-privacy" target="_blank">Legal</a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="background-color:rgb(244,244,244)">
            <div style="margin:0px auto;min-width:320px;max-width:500px;width:calc(19000% - 98300px);word-break:break-word;background-color:transparent">
                <div style="border-collapse:collapse;display:table;width:100%">
                    <div style="min-width:320px;max-width:500px;width:calc(18000% - 89500px);background-color:transparent">
                        <div style="width:100%!important;background-color:transparent">
                            <div style="border:0px solid transparent;padding:10px 0px 30px">
                                <div style="padding:10px">
                                    <div style="font-size:11px;line-height:16px;text-align:center;font-family:'Zelle Sans','Helvetica Neue',Helvetica,Arial,Verdana,'Trebuchet MS',sans-serif;color:rgb(120,120,120)">
                                        Contact <em><i>Zelle</i></em> Support at 1-844-428-8542,<br>7 days a week, 8am-Midnight Eastern.<br>
                                        <a style="text-decoration:none;color:rgb(110,26,201)" href="mailto:customerservice@zellepay.com" target="_blank">customerservice@zellepay.com</a>
                                    </div>
                                </div>
                                <div style="padding:10px">
                                    <div style="font-size:11px;line-height:16px;text-align:center;font-family:'Zelle Sans','Helvetica Neue',Helvetica,Arial,Verdana,'Trebuchet MS',sans-serif;color:rgb(120,120,120)">
                                        Early Warning Services, LLC<br>
                                        16552 N. 90th Street, Scottsdale, AZ 85260 USA
                                    </div>
                                </div>
                                <div style="padding:10px">
                                    <div style="font-size:11px;line-height:16px;text-align:center;font-family:'Zelle Sans','Helvetica Neue',Helvetica,Arial,Verdana,'Trebuchet MS',sans-serif;color:rgb(120,120,120)">
                                        © 2021 Early Warning Services, LLC.<br>
                                        Zelle and the Zelle related marks and logos are<br>property of Early Warning Services, LLC
                                    </div>
                                </div>
                                <div style="padding:10px;text-align:center">
                                    <div style="font-size:11px;line-height:16px;text-align:center;font-family:'Zelle Sans','Helvetica Neue',Helvetica,Arial,Verdana,'Trebuchet MS',sans-serif;color:rgb(120,120,120)">
                                        Unsubscribe <a style="text-decoration:underline;color:rgb(110,26,201)" href="#" target="_blank">here</a> to stop getting emails from <i>Zelle</i>.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

        // 2. WELLS FARGO
        $wfHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: "Open Sans", Arial, Helvetica, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
  .wrapper { background-color: #f4f4f4; padding: 20px; }
  .email-container { max-width: 600px; background-color: #ffffff; margin: 0 auto; border: 1px solid #cccccc; }
  .top-bar { background-color: #f5a623; height: 5px; }
  .header { background-color: #d71920; padding: 20px; text-align: left; }
  .logo-img { height: 28px; }
  .content { padding: 35px 25px; color: #333333; }
  .h1 { font-size: 22px; font-weight: bold; color: #d71920; margin-bottom: 20px; border-bottom: 2px solid #d71920; padding-bottom: 10px; }
  .detail-table { width: 100%; border-collapse: collapse; margin: 25px 0; background-color: #f9f9f9; border: 1px solid #eeeeee; }
  .detail-table td { padding: 15px; border-bottom: 1px solid #eeeeee; font-size: 15px; }
  .label { font-weight: bold; color: #666666; width: 140px; }
  .amount { font-size: 20px; color: #d71920; font-weight: bold; }
  .footer { background-color: #ffffff; padding: 25px; font-size: 11px; color: #777777; border-top: 1px solid #eeeeee; line-height: 1.6; }
  .btn { display: inline-block; background-color: #d71920; color: #ffffff !important; padding: 12px 30px; text-decoration: none; font-weight: bold; margin: 20px 0; border-radius: 3px; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="email-container">
    <div class="top-bar"></div>
    <div class="header">
      <img src="[[APP_URL]]/assets/images/bank_logos/wellsfargo.png" alt="WELLS FARGO" class="logo-img">
    </div>
    <div class="content">
      <div class="h1">Account Notification: Incoming Transfer</div>
      <p style="font-size: 16px;">Hello [[RECIPIENT_NAME]],</p>
      <p style="font-size: 16px;">We are writing to notify you that an incoming electronic transfer (ACH) from [[SENDER_NAME]] [[STATUS_DESC]]</p>
      
      <table class="detail-table">
        <tr><td class="label">Transaction Type:</td><td>Incoming ACH Transfer</td></tr>
        <tr><td class="label">Account Number:</td><td>Ending in ...[[ACCOUNT_NUMBER]]</td></tr>
        <tr><td class="label">Amount:</td><td class="amount">$[[AMOUNT]]</td></tr>
        <tr><td class="label">Status:</td><td><b>[[STATUS]]</b></td></tr>
        <tr><td class="label">Post Date:</td><td>[[DATE]]</td></tr>
        <tr><td class="label" style="border:none;">Description:</td><td style="border:none;">[[DESCRIPTION]]</td></tr>
      </table>
      
      <a href="https://www.wellsfargo.com" class="btn">Sign On to Wells Fargo</a>
      
      <p style="font-size: 14px; margin-top: 30px;">You can view your complete account history, including the transaction description and available balance, by logging into your <a href="https://www.wellsfargo.com" target="_blank" style="color:#d71920;text-decoration:underline;">Wells Fargo Online</a> account or the Wells Fargo Mobile® app.</p>
      <p style="font-size: 14px; margin-top: 20px;">Thank you for banking with Wells Fargo.</p>
    </div>
    <div class="footer">
      <p><strong>Security Reminder:</strong> Wells Fargo will never send you an email asking for your password, PIN, or full Social Security number.</p>
      <p style="margin-top: 20px;">&copy; 1999 - [[CURRENT_YEAR]] Wells Fargo. All rights reserved. <br>
      Wells Fargo Bank, N.A. Member FDIC.</p>
    </div>
  </div>
</div>
</body>
</html>
HTML;

        // 3. CHASE
        $chaseHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif; color: #333333; line-height: 1.5; background-color: #f4f4f4; margin: 0; padding: 0; }
  .wrapper { background-color: #f4f4f4; padding: 20px; }
  .email-container { max-width: 600px; background-color: #ffffff; margin: 0 auto; border: 1px solid #e0e0e0; }
  .header { background-color: #0b3c8c; padding: 25px; text-align: left; }
  .logo-img { height: 24px; }
  .content { padding: 40px 30px; }
  .h1 { font-size: 22px; font-weight: 300; color: #0b3c8c; margin-bottom: 25px; }
  .details-box { background-color: #f6f6f6; border-radius: 4px; padding: 20px; margin: 25px 0; }
  .detail-row { display: flex; margin-bottom: 10px; border-bottom: 1px solid #eeeeee; padding-bottom: 8px; }
  .detail-label { font-weight: bold; width: 130px; font-size: 14px; color: #666666; }
  .detail-value { font-size: 14px; color: #333333; }
  .amount-big { font-size: 24px; color: #0b3c8c; font-weight: bold; margin: 15px 0; }
  .footer { background-color: #f6f6f6; padding: 30px; font-size: 12px; color: #666666; border-top: 1px solid #e0e0e0; text-align: center; }
  .btn { display: inline-block; background-color: #117aca; color: #ffffff !important; padding: 12px 25px; text-decoration: none; border-radius: 3px; font-weight: bold; margin-top: 20px; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="email-container">
    <div class="header">
      <img src="[[APP_URL]]/assets/images/bank_logos/chase.png" alt="CHASE" class="logo-img">
    </div>
    <div class="content">
      <div class="h1">Account Alert: Incoming Transfer Activity</div>
      <p>Hello [[RECIPIENT_NAME]],</p>
      <p>This is an automated notification regarding an incoming Automated Clearing House (ACH) transaction from [[SENDER_NAME]] for your account.</p>
      
      <div class="amount-big">$[[AMOUNT]]</div>
      
      <div class="details-box">
        <div class="detail-row"><span class="detail-label">Status:</span> <span class="detail-value"><b>[[STATUS]]</b></span></div>
        <div class="detail-row"><span class="detail-label">To Account:</span> <span class="detail-value">CHASE CHECKING (...[[ACCOUNT_NUMBER]])</span></div>
        <div class="detail-row"><span class="detail-label">Description:</span> <span class="detail-value">[[DESCRIPTION]]</span></div>
        <div class="detail-row" style="border:none;"><span class="detail-label">Post Date:</span> <span class="detail-value">[[DATE]]</span></div>
      </div>
      
      <p>This funds transfer [[STATUS_DESC]]</p>
      
      <a href="https://www.chase.com" class="btn">Sign on to Chase.com</a>
      
      <p style="margin-top: 30px; font-size: 14px;">To view your complete account activity or manage your notification preferences, please log on to your account directly via the Chase Mobile® app or our website.</p>
      <p style="margin-top: 20px; font-size: 14px;">If you have any questions, please log on to chase.com to send us a secure message or call the number on the back of your card.</p>
    </div>
    <div class="footer">
      <p>This is an automated alert from Chase. Please do not reply to this email.</p>
      <p style="margin-top: 20px;">&copy; [[CURRENT_YEAR]] JPMorgan Chase &amp; Co. All rights reserved. <br>
      JPMorgan Chase Bank, N.A. Member FDIC.</p>
    </div>
  </div>
</div>
</body>
</html>
HTML;

        // 4. PNC
        $pncHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>PNC Account Alert: Incoming Deposit</title>
  <style>
    body { font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
    .email-container { max-width: 600px; background-color: #ffffff; margin: 0 auto; border-top: 4px solid #f47b20; border-bottom: 1px solid #e0e0e0; border-left: 1px solid #e0e0e0; border-right: 1px solid #e0e0e0; }
    .header { background-color: #004c87; padding: 30px 20px; text-align: center; }
    .header img { height: 35px; }
    .content { padding: 30px; color: #333333; line-height: 1.6; }
    .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #f9f9f9; }
    .details-table td { padding: 12px; border-bottom: 1px solid #e0e0e0; font-size: 14px; }
    .details-table td.label { font-weight: bold; color: #555555; width: 35%; }
    .details-table td.value { color: #000000; }
    .btn-container { text-align: center; margin: 30px 0; }
    .btn { background-color: #004c87; color: #ffffff !important; text-decoration: none; padding: 14px 25px; font-weight: bold; border-radius: 4px; display: inline-block; }
    .footer { background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #777777; }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="header">
      <img src="[[APP_URL]]/assets/images/bank_logos/pnc.png" alt="PNC Logo">
    </div>
    
    <div class="content">
      <p>Hello [[RECIPIENT_NAME]],</p>
      <p>This is an automated notification to inform you that an incoming ACH (Direct Deposit/Transfer) from [[SENDER_NAME]] [[STATUS_DESC]]</p>
      
      <table class="details-table">
        <tr>
          <td class="label">Account:</td>
          <td class="value">Primary Checking (...[[ACCOUNT_NUMBER]])</td>
        </tr>
        <tr>
          <td class="label">Amount:</td>
          <td class="value"><strong style="color: #004c87; font-size: 18px;">$[[AMOUNT]]</strong></td>
        </tr>
        <tr>
          <td class="label">Status:</td>
          <td class="value"><b>[[STATUS]]</b></td>
        </tr>
        <tr>
          <td class="label">Effective Date:</td>
          <td class="value">[[DATE]]</td>
        </tr>
        <tr>
          <td class="label" style="border:none;">Memo:</td>
          <td class="value" style="border:none;">[[DESCRIPTION]]</td>
        </tr>
      </table>

      <p>Please log in to your account to view the finalized transaction history and verify your updated balance.</p>
      
      <div class="btn-container">
        <a href="https://pnc.com" class="btn">Sign on to PNC Online Banking</a>
      </div>
      
      <p>If you have any questions, please contact PNC Customer Service at 1-888-PNC-BANK or send us a secure message via the online banking portal.</p>
    </div>
    
    <div class="footer">
      <p>PNC Bank, National Association. Member FDIC.</p>
      <p>&copy; [[CURRENT_YEAR]] The PNC Financial Services Group, Inc. All rights reserved.</p>
      <p>Please do not reply to this automated email.</p>
    </div>
  </div>
</body>
</html>
HTML;

        // 5. BANK OF AMERICA
        $bofaHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Bank of America Alert</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">

  <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #dddddd;">
    <tr>
      <td style="background-color: #012169; padding: 25px; text-align: center;">
        <img src="[[APP_URL]]/assets/images/bank_logos/bofa.png" alt="Bank of America" style="height: 35px;">
      </td>
    </tr>
    
    <tr>
      <td style="padding: 30px; color: #333333; line-height: 1.6;">
        <h2 style="color: #012169; margin-top: 0;">Account Alert</h2>
        
        <p>Dear [[RECIPIENT_NAME]],</p>
        
        <p>This is an automated notification to inform you that a direct deposit or incoming transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>

        <table width="100%" border="0" cellspacing="0" cellpadding="10" style="background-color: #f9f9f9; margin: 20px 0; border-left: 4px solid #012169;">
          <tr>
            <td style="font-weight: bold; width: 140px; border-bottom: 1px solid #eeeeee;">Account:</td>
            <td style="border-bottom: 1px solid #eeeeee;">Checking •••• [[ACCOUNT_NUMBER]]</td>
          </tr>
          <tr>
            <td style="font-weight: bold; border-bottom: 1px solid #eeeeee;">Amount:</td>
            <td style="color: #012169; font-weight: bold; border-bottom: 1px solid #eeeeee; font-size: 18px;">$[[AMOUNT]]</td>
          </tr>
          <tr>
            <td style="font-weight: bold; border-bottom: 1px solid #eeeeee;">Transfer Status:</td>
            <td style="font-weight: bold; border-bottom: 1px solid #eeeeee;">[[STATUS]]</td>
          </tr>
          <tr>
            <td style="font-weight: bold; border-bottom: 1px solid #eeeeee;">Description:</td>
            <td style="border-bottom: 1px solid #eeeeee;">[[DESCRIPTION]]</td>
          </tr>
          <tr>
            <td style="font-weight: bold;">Effective Date:</td>
            <td>[[DATE]]</td>
          </tr>
        </table>

        <p>Please log in to your account to view the full transaction history and verify the details.</p>

        <div style="text-align: center; margin: 30px 0;">
          <a href="https://www.bankofamerica.com" style="background-color: #012169; color: #ffffff !important; text-decoration: none; padding: 12px 20px; font-weight: bold; border-radius: 4px; display: inline-block;">Log In to Online Banking</a>
        </div>
      </td>
    </tr>

    <tr>
      <td style="background-color: #f9f9f9; padding: 20px; font-size: 12px; color: #666666; text-align: center; border-top: 1px solid #dddddd;">
        <p>Please do not reply directly to this automatically-generated email message.</p>
        <p>&copy; [[CURRENT_YEAR]] Bank of America Corporation. All rights reserved. Member FDIC.</p>
      </td>
    </tr>
  </table>

</body>
</html>
HTML;

        // 6. CITI BANK
        $citiHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Citi Incoming ACH Transfer Notification</title>
  <style>
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; color: #333333; }
    .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
    .header { background-color: #003B77; padding: 25px; text-align: center; }
    .header img { height: 35px; }
    .content { padding: 30px; }
    .alert-box { background-color: #e6f2ff; border-left: 5px solid #003B77; padding: 15px; margin-bottom: 25px; font-size: 15px; color: #003B77; font-weight: bold; }
    .details-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
    .details-table td { padding: 12px 0; border-bottom: 1px solid #eeeeee; font-size: 14px; }
    .label { font-weight: bold; color: #666666; width: 40%; }
    .value { color: #333333; text-align: right; }
    .amount { font-size: 20px; font-weight: bold; color: #003B77; }
    .cta-container { text-align: center; margin: 20px 0; }
    .cta-button { background-color: #003B77; color: #ffffff !important; text-decoration: none; padding: 15px 30px; border-radius: 5px; font-weight: bold; display: inline-block; }
    .footer { background-color: #f4f7f6; padding: 20px; text-align: center; font-size: 12px; color: #888888; border-top: 1px solid #dddddd; }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="header">
      <img src="[[APP_URL]]/assets/images/bank_logos/citi.png" alt="citibank">
    </div>
    
    <div class="content">
      <div class="alert-box">
        Deposit Notice: An incoming electronic transfer [[STATUS_DESC]]
      </div>
      
      <p>Hello [[RECIPIENT_NAME]],</p>
      <p>We are writing to notify you that an electronic transfer (ACH) from [[SENDER_NAME]] is currently processing for your Citibank account.</p>
      
      <table class="details-table">
        <tr>
          <td class="label">Account Number:</td>
          <td class="value">•••• •••• •••• [[ACCOUNT_NUMBER]]</td>
        </tr>
        <tr>
          <td class="label">Transaction Type:</td>
          <td class="value">Incoming ACH Transfer</td>
        </tr>
        <tr>
          <td class="label">Originator / Company:</td>
          <td class="value">[[DESCRIPTION]]</td>
        </tr>
        <tr>
          <td class="label">Post Date:</td>
          <td class="value">[[DATE]]</td>
        </tr>
        <tr>
          <td class="label">Current Status:</td>
          <td class="value" style="font-weight: bold; color: #003B77;">[[STATUS]]</td>
        </tr>
        <tr>
          <td class="label">Amount:</td>
          <td class="value amount">$[[AMOUNT]]</td>
        </tr>
      </table>

      <p>If you do not recognize this transaction or believe this is an error, please contact us immediately or log in to view your detailed transaction history.</p>
      
      <div class="cta-container">
        <a href="https://online.citi.com" class="cta-button">Log In to Citi Online</a>
      </div>
    </div>
    
    <div class="footer">
      <p>Please do not reply directly to this email. This mailbox is unattended.</p>
      <p>&copy; [[CURRENT_YEAR]] Citibank, N.A. All rights reserved. Citibank, Citi, and Arc Design are registered service marks of Citigroup Inc.</p>
    </div>
  </div>
</body>
</html>
HTML;

        // 7. HUNTINGTON BANK
        $huntingtonHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Huntington Bank - Incoming ACH Notification</title>
  <style>
    body { font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; color: #333333; }
    .email-wrapper { background-color: #f4f4f4; width: 100%; padding: 20px 0; }
    .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-top: 4px solid #005A36; border-bottom: 1px solid #dddddd; }
    .header { padding: 30px 20px; text-align: left; border-bottom: 1px solid #eeeeee; }
    .header img { height: 35px; }
    .content { padding: 30px 20px; }
    .details-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
    .details-table td { padding: 10px 0; border-bottom: 1px solid #eeeeee; }
    .details-table td.label { font-weight: bold; color: #666666; width: 35%; }
    .details-table td.value { font-weight: bold; color: #333333; text-align: right; }
    .cta-container { text-align: center; margin: 30px 0; }
    .cta-button { background-color: #005A36; color: #ffffff !important; text-decoration: none; padding: 15px 30px; font-weight: bold; border-radius: 4px; display: inline-block; }
    .footer { background-color: #333333; color: #ffffff; padding: 20px; font-size: 12px; line-height: 18px; }
    .footer a { color: #ffffff; text-decoration: underline; }
  </style>
</head>
<body>
<div class="email-wrapper">
  <table cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr>
      <td>
        <table align="center" cellpadding="0" cellspacing="0" border="0" width="600" class="email-container">
          <tr>
            <td class="header">
              <img src="[[APP_URL]]/assets/images/bank_logos/logo_08f700dc70a14d4203b900b6d99b01ec.png" alt="Huntington Alert" style="border-radius: 4px;">
            </td>
          </tr>
          <tr>
            <td class="content">
              <p>Hello [[RECIPIENT_NAME]],</p>
              <p>A new incoming ACH Transfer from [[SENDER_NAME]] [[STATUS_DESC]] Below are the details of the incoming transaction:</p>

              <table class="details-table" cellpadding="0" cellspacing="0">
                <tr>
                  <td class="label">Amount:</td>
                  <td class="value" style="color: #005A36; font-size: 18px;">$[[AMOUNT]]</td>
                </tr>
                <tr>
                  <td class="label">Current Status:</td>
                  <td class="value">[[STATUS]]</td>
                </tr>
                <tr>
                  <td class="label">Company / Memo:</td>
                  <td class="value">[[DESCRIPTION]]</td>
                </tr>
                <tr>
                  <td class="label">Effective Date:</td>
                  <td class="value">[[DATE]]</td>
                </tr>
                <tr>
                  <td class="label">Account Ending In:</td>
                  <td class="value">•••• [[ACCOUNT_NUMBER]]</td>
                </tr>
                <tr>
                  <td class="label">Reference ID:</td>
                  <td class="value">[[TNX]]</td>
                </tr>
              </table>

              <div class="cta-container">
                <a href="https://www.huntington.com" class="cta-button" target="_blank">Log in to Huntington Online</a>
              </div>

              <p>If you have any questions or did not authorize this transaction, please contact us immediately through the Huntington Customer Service Center.</p>
            </td>
          </tr>
          <tr>
            <td class="footer">
              <p>Please do not reply to this email. This is an automated notification based on your account settings.</p>
              <p>The Huntington National Bank is Member FDIC. 
              <br>© [[CURRENT_YEAR]] Huntington Bancshares Incorporated. Huntington, Huntington Bank, and the Huntington Brandmark are service marks of Huntington Bancshares Incorporated.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
</body>
</html>
HTML;

        // 8. CITIZENS BANK
        $citizensHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Incoming ACH Transfer Notification</title>
  <style>
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f7f9; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
    .wrapper { background-color: #f4f7f9; width: 100%; padding: 40px 0; }
    .container { background-color: #ffffff; max-width: 600px; margin: 0 auto; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); }
    .header { background-color: #00763c; padding: 30px; text-align: center; }
    .header img { height: 40px; }
    .content { padding: 40px; color: #333333; }
    .content p { font-size: 16px; line-height: 1.6; margin-top: 0; }
    .details-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; margin: 30px 0; }
    .details-row { display: flex; justify-content: space-between; margin-bottom: 15px; }
    .details-row:last-child { margin-bottom: 0; }
    .label { color: #64748b; font-size: 15px; }
    .value { color: #0f172a; font-weight: 600; font-size: 15px; }
    .button-container { text-align: center; margin-top: 30px; }
    .btn { background-color: #00763c; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 4px; font-weight: bold; font-size: 16px; display: inline-block; }
    .footer { background-color: #e2e8f0; padding: 20px; text-align: center; color: #64748b; font-size: 13px; }
    .footer a { color: #00763c; text-decoration: underline; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="container">
      <div class="header">
        <img src="[[APP_URL]]/assets/images/bank_logos/citizensbank.png" alt="Citizens Bank">
      </div>
      
      <div class="content">
        <p>Dear [[RECIPIENT_NAME]],</p>
        <p>We are writing to notify you that an incoming ACH electronic transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
        
        <div class="details-box">
          <div class="details-row">
            <span class="label">Amount:</span>
            <span class="value" style="color: #00763c; font-size: 18px;">$[[AMOUNT]]</span>
          </div>
          <div class="details-row">
            <span class="label">Status:</span>
            <span class="value">[[STATUS]]</span>
          </div>
          <div class="details-row">
            <span class="label">Date Received:</span>
            <span class="value">[[DATE]]</span>
          </div>
          <div class="details-row">
            <span class="label">Originating Company:</span>
            <span class="value">[[DESCRIPTION]]</span>
          </div>
          <div class="details-row">
            <span class="label">Account Ending In:</span>
            <span class="value">****[[ACCOUNT_NUMBER]]</span>
          </div>
        </div>
        
        <div class="button-container">
          <a href="https://www.citizensbank.com/" class="btn">View Account Details</a>
        </div>
      </div>
      
      <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>If you have any questions or did not authorize this transaction, please contact us immediately via your Secure Message Center or call customer support.</p>
        <p>&copy; [[CURRENT_YEAR]] Citizens Financial Group, Inc. All rights reserved.</p>
      </div>
    </div>
  </div>
</body>
</html>
HTML;

        return [
            ['name' => 'Zelle Official Network Notification', 'email_from_name' => 'Zelle Payment Service', 'description' => 'Official Zelle network branding', 'email_subject' => 'Payment Alert: [[SENDER_NAME]] sent you $[[AMOUNT]]', 'email_content' => $zelleHtml, 'email_footer' => '', 'content' => 'Zelle Template'],
            ['name' => 'Wells Fargo Recipient Alert', 'email_from_name' => 'Wells Fargo Online', 'description' => 'Wells Fargo branding', 'email_subject' => 'Wells Fargo: Incoming transfer of $[[AMOUNT]]', 'email_content' => $wfHtml, 'email_footer' => '', 'content' => 'WF Template'],
            ['name' => 'Chase Bank Notification', 'email_from_name' => 'Chase Bank Support', 'description' => 'Chase branding', 'email_subject' => 'Chase: Payment Alert of $[[AMOUNT]]', 'email_content' => $chaseHtml, 'email_footer' => '', 'content' => 'Chase Template'],
            ['name' => 'PNC Bank Notification', 'email_from_name' => 'PNC Alerts', 'description' => 'PNC branding', 'email_subject' => 'PNC Alert: Incoming Deposit of $[[AMOUNT]]', 'email_content' => $pncHtml, 'email_footer' => '', 'content' => 'PNC Template'],
            ['name' => 'Bank of America Notification', 'email_from_name' => 'Bank of America Alerts', 'description' => 'Bank of America branding', 'email_subject' => 'Bank of America Alert: Incoming Transfer Received', 'email_content' => $bofaHtml, 'email_footer' => '', 'content' => 'BofA Template'],
            ['name' => 'Citibank Notification', 'email_from_name' => 'Citi Alerts', 'description' => 'Citibank branding', 'email_subject' => 'Citibank: Notice of Incoming ACH', 'email_content' => $citiHtml, 'email_footer' => '', 'content' => 'Citi Template'],
            ['name' => 'Huntington Bank Notification', 'email_from_name' => 'Huntington Alerts', 'description' => 'Huntington branding', 'email_subject' => 'Huntington Alert: Incoming Transfer', 'email_content' => $huntingtonHtml, 'email_footer' => '', 'content' => 'Huntington Template'],
            ['name' => 'Citizens Bank Notification', 'email_from_name' => 'Citizens Bank', 'description' => 'Citizens Bank branding', 'email_subject' => 'Citizens Bank: Transfer Notification', 'email_content' => $citizensHtml, 'email_footer' => '', 'content' => 'Citizens Template'],
        ];
    }

    /**
     * Explicit HTML implementations for the remaining 22 banks to ensure 100% realism
     */
    private static function getOtherBankTemplates()
    {
        $generated = [];

        // --- 9. CAPITAL ONE ---
        $capOneHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Capital One Alert</title></head>
<body style="margin:0;padding:20px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;background-color:#f5f5f5;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
  <tr><td style="background-color:#003a6f;padding:25px;text-align:center;"><img src="[[APP_URL]]/assets/images/bank_logos/capitalone.png" height="40" alt="Capital One"></td></tr>
  <tr><td style="padding:40px;color:#333;">
    <h2 style="color:#003a6f;margin-top:0;">Account Alert: Deposit Received</h2>
    <p>Hi [[RECIPIENT_NAME]],</p>
    <p>Good news! An incoming electronic transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <div style="background-color:#f4f7f9;border-left:4px solid #003a6f;padding:20px;margin:30px 0;">
      <p style="margin:0 0 10px 0;"><strong>Amount:</strong> <span style="color:#003a6f;font-size:20px;font-weight:bold;">$[[AMOUNT]]</span></p>
      <p style="margin:0 0 10px 0;"><strong>Status:</strong> [[STATUS]]</p>
      <p style="margin:0 0 10px 0;"><strong>Account:</strong> ...[[ACCOUNT_NUMBER]]</p>
      <p style="margin:0 0 10px 0;"><strong>Date:</strong> [[DATE]]</p>
      <p style="margin:0;"><strong>Memo:</strong> [[DESCRIPTION]]</p>
    </div>
    <a href="https://www.capitalone.com" style="display:block;width:200px;margin:0 auto;background-color:#003a6f;color:#ffffff !important;text-align:center;padding:15px;text-decoration:none;border-radius:25px;font-weight:bold;">Sign In</a>
  </td></tr>
  <tr><td style="background-color:#f5f5f5;padding:20px;font-size:12px;color:#666;text-align:center;border-top:1px solid #ddd;">
    <p>&copy; [[CURRENT_YEAR]] Capital One. All rights reserved. Member FDIC.</p>
  </td></tr>
</table>
</body></html>
HTML;

        // --- 10. U.S. BANK ---
        $usbankHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>U.S. Bank Alert</title></head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background-color:#eaeaea;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #ccc;">
  <tr><td style="background-color:#001a70;padding:20px;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_e6168967519dd8cadd304481410882b2.png" height="30" alt="U.S. Bank"></td></tr>
  <tr><td style="height:4px;background-color:#d42027;"></td></tr>
  <tr><td style="padding:30px;color:#333;">
    <h3 style="color:#001a70;">Transfer Notification</h3>
    <p>Dear [[RECIPIENT_NAME]],</p>
    <p>This is to inform you that an ACH transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <table width="100%" cellpadding="10" cellspacing="0" style="margin:20px 0;border:1px solid #eee;">
      <tr style="background:#f9f9f9;"><td width="40%"><strong>Amount:</strong></td><td style="color:#001a70;font-weight:bold;">$[[AMOUNT]]</td></tr>
      <tr><td><strong>Status:</strong></td><td>[[STATUS]]</td></tr>
      <tr style="background:#f9f9f9;"><td><strong>Date:</strong></td><td>[[DATE]]</td></tr>
      <tr><td><strong>Description:</strong></td><td>[[DESCRIPTION]]</td></tr>
      <tr style="background:#f9f9f9;"><td><strong>Account:</strong></td><td>Ending in [[ACCOUNT_NUMBER]]</td></tr>
    </table>
    <a href="https://www.usbank.com" style="background:#001a70;color:#fff !important;padding:12px 25px;text-decoration:none;display:inline-block;font-weight:bold;">Log In to U.S. Bank</a>
  </td></tr>
  <tr><td style="background-color:#f9f9f9;padding:20px;font-size:11px;color:#777;text-align:center;border-top:1px solid #ddd;">
    <p>U.S. Bank National Association. Member FDIC.</p>
  </td></tr>
</table>
</body></html>
HTML;

        // --- 11. TD BANK ---
        $tdHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>TD Bank Alert</title></head>
<body style="margin:0;padding:20px;font-family:Helvetica,Arial,sans-serif;background-color:#f4f4f4;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #ddd;border-top:5px solid #00b33c;">
  <tr><td style="padding:25px;text-align:left;border-bottom:1px solid #eee;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_d8410783ea930298fbef50804e7c3641.png" height="40" alt="TD Bank"></td></tr>
  <tr><td style="padding:30px;color:#333;">
    <h2 style="color:#333;margin-top:0;">Deposit Alert</h2>
    <p>Hello [[RECIPIENT_NAME]],</p>
    <p>An electronic deposit from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <table width="100%" cellpadding="10" cellspacing="0" style="background:#fcfcfc;border:1px solid #eee;margin:25px 0;">
      <tr><td style="color:#666;font-weight:bold;border-bottom:1px solid #eee;">Amount:</td><td style="color:#00b33c;font-weight:bold;font-size:18px;text-align:right;border-bottom:1px solid #eee;">$[[AMOUNT]]</td></tr>
      <tr><td style="color:#666;font-weight:bold;border-bottom:1px solid #eee;">Status:</td><td style="text-align:right;border-bottom:1px solid #eee;font-weight:bold;">[[STATUS]]</td></tr>
      <tr><td style="color:#666;font-weight:bold;border-bottom:1px solid #eee;">Date:</td><td style="text-align:right;border-bottom:1px solid #eee;">[[DATE]]</td></tr>
      <tr><td style="color:#666;font-weight:bold;">Memo:</td><td style="text-align:right;">[[DESCRIPTION]]</td></tr>
    </table>
    <center><a href="https://onlinebanking.tdbank.com" style="background:#00b33c;color:#fff !important;padding:14px 30px;text-decoration:none;display:inline-block;font-weight:bold;border-radius:3px;">Login to Online Banking</a></center>
  </td></tr>
  <tr><td style="background:#f4f4f4;padding:20px;font-size:12px;color:#888;text-align:center;">&copy; [[CURRENT_YEAR]] TD Bank, N.A. All Rights Reserved. Member FDIC.</td></tr>
</table>
</body></html>
HTML;

        // --- 12. TRUIST ---
        $truistHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Truist Alert</title></head>
<body style="margin:0;padding:20px;font-family:'Open Sans',Helvetica,Arial,sans-serif;background-color:#fafafa;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.05);">
  <tr><td style="background-color:#2e1a47;padding:30px;text-align:center;border-radius:12px 12px 0 0;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_cffbd2bea7509e1bbaf9cddef4455cc2.png" height="35" alt="Truist"></td></tr>
  <tr><td style="padding:40px;color:#222;">
    <h2 style="color:#2e1a47;margin-top:0;">Your Deposit is Processing</h2>
    <p>Hi [[RECIPIENT_NAME]],</p>
    <p>An ACH transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <div style="padding:20px;background:#f5f0fa;border-radius:8px;margin:25px 0;">
      <div style="font-size:24px;font-weight:bold;color:#2e1a47;margin-bottom:15px;">$[[AMOUNT]]</div>
      <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:14px;"><span style="color:#555;">Status:</span><strong>[[STATUS]]</strong></div>
      <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:14px;"><span style="color:#555;">Date:</span><strong>[[DATE]]</strong></div>
      <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:14px;"><span style="color:#555;">Account:</span><strong>...[[ACCOUNT_NUMBER]]</strong></div>
      <div style="display:flex;justify-content:space-between;font-size:14px;"><span style="color:#555;">Memo:</span><strong>[[DESCRIPTION]]</strong></div>
    </div>
    <a href="https://www.truist.com" style="background:#2e1a47;color:#fff !important;padding:15px;text-decoration:none;display:block;text-align:center;font-weight:bold;border-radius:25px;">Sign In to Truist</a>
  </td></tr>
  <tr><td style="padding:20px;font-size:12px;color:#999;text-align:center;border-top:1px solid #eee;">Truist Bank, Member FDIC.</td></tr>
</table>
</body></html>
HTML;

        // --- 13. KEYBANK ---
        $keyHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>KeyBank Alert</title></head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background-color:#f0f0f0;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-top:4px solid #d41313;">
  <tr><td style="padding:20px;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_92a8ab56e51c2cfd7bca9287f8a215d7.png" height="40" alt="KeyBank"></td></tr>
  <tr><td style="padding:30px;color:#333;">
    <h3 style="color:#d41313;border-bottom:1px solid #eee;padding-bottom:10px;">Alert: Incoming Funds</h3>
    <p>Dear [[RECIPIENT_NAME]],</p>
    <p>An incoming transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <table width="100%" cellpadding="8" cellspacing="0" style="margin:20px 0;">
      <tr><td style="width:40%;color:#666;">Amount:</td><td style="font-weight:bold;font-size:18px;color:#d41313;">$[[AMOUNT]]</td></tr>
      <tr><td style="color:#666;">Status:</td><td style="font-weight:bold;">[[STATUS]]</td></tr>
      <tr><td style="color:#666;">Account Ending:</td><td style="font-weight:bold;">[[ACCOUNT_NUMBER]]</td></tr>
      <tr><td style="color:#666;">Date:</td><td style="font-weight:bold;">[[DATE]]</td></tr>
      <tr><td style="color:#666;">Reference:</td><td style="font-weight:bold;">[[DESCRIPTION]]</td></tr>
    </table>
    <a href="https://www.key.com" style="background:#d41313;color:#fff !important;padding:12px 25px;text-decoration:none;display:inline-block;font-weight:bold;border-radius:4px;">Log In</a>
  </td></tr>
  <tr><td style="background:#333;padding:20px;font-size:11px;color:#ccc;text-align:center;">&copy; [[CURRENT_YEAR]] KeyCorp. All Rights Reserved. KeyBank is Member FDIC.</td></tr>
</table>
</body></html>
HTML;

        // --- 14. REGIONS BANK ---
        $regionsHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Regions Bank Alert</title></head>
<body style="margin:0;padding:20px;font-family:Helvetica,sans-serif;background-color:#f4f4f4;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #ccc;">
  <tr><td style="background-color:#6c9a00;padding:20px;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_d77004f66a8fe96f36902fa9b6c7bb7e.png" height="35" alt="Regions Bank"></td></tr>
  <tr><td style="padding:30px;color:#444;">
    <h2 style="color:#6c9a00;">Notification of Deposit</h2>
    <p>Hello [[RECIPIENT_NAME]],</p>
    <p>An electronic ACH transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <div style="border:1px solid #eee;padding:15px;margin:20px 0;">
      <p style="margin:5px 0;"><strong>Amount:</strong> <span style="color:#6c9a00;font-weight:bold;">$[[AMOUNT]]</span></p>
      <p style="margin:5px 0;"><strong>Transfer Status:</strong> <b>[[STATUS]]</b></p>
      <p style="margin:5px 0;"><strong>Date:</strong> [[DATE]]</p>
      <p style="margin:5px 0;"><strong>Account:</strong> *[[ACCOUNT_NUMBER]]</p>
      <p style="margin:5px 0;"><strong>Memo:</strong> [[DESCRIPTION]]</p>
    </div>
    <a href="https://www.regions.com" style="background:#6c9a00;color:#fff !important;padding:12px 20px;text-decoration:none;display:inline-block;font-weight:bold;">Log in to Regions Online</a>
  </td></tr>
  <tr><td style="background-color:#eee;padding:15px;font-size:12px;color:#666;text-align:center;">Regions Bank, Member FDIC.</td></tr>
</table>
</body></html>
HTML;

        // --- 15. DISCOVER ---
        $discoverHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Discover Alert</title></head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background-color:#f9f9f9;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:6px;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
  <tr><td style="padding:25px;border-bottom:2px solid #ff6000;text-align:center;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_3c9e2f9104c56185152cabe9ede24f9b.png" height="30" alt="Discover"></td></tr>
  <tr><td style="padding:35px;color:#333;">
    <h2 style="color:#ff6000;margin-top:0;">Account Alert: Deposit Received</h2>
    <p>Dear [[RECIPIENT_NAME]],</p>
    <p>This message is to inform you that an incoming ACH deposit from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <div style="background:#fcfcfc;border:1px solid #eee;padding:20px;margin:25px 0;">
      <p style="margin:5px 0;"><strong>Amount:</strong> <span style="color:#ff6000;font-weight:bold;font-size:18px;">$[[AMOUNT]]</span></p>
      <p style="margin:5px 0;"><strong>Status:</strong> <b>[[STATUS]]</b></p>
      <p style="margin:5px 0;"><strong>Post Date:</strong> [[DATE]]</p>
      <p style="margin:5px 0;"><strong>To Account Ending:</strong> [[ACCOUNT_NUMBER]]</p>
      <p style="margin:5px 0;"><strong>Memo:</strong> [[DESCRIPTION]]</p>
    </div>
    <center><a href="https://www.discover.com" style="background:#ff6000;color:#ffffff !important;padding:12px 30px;text-decoration:none;font-weight:bold;border-radius:4px;display:inline-block;">Log in to Discover Card</a></center>
  </td></tr>
  <tr><td style="padding:20px;font-size:12px;color:#999;text-align:center;background:#f5f5f5;">&copy; [[CURRENT_YEAR]] Discover Bank, Member FDIC.</td></tr>
</table>
</body></html>
HTML;

        // --- 16. SANTANDER BANK ---
        $santanderHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Santander Alert</title></head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background-color:#f6f6f6;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-top:4px solid #ec0000;">
  <tr><td style="padding:25px;text-align:left;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_8a2b45a4fa0b66a4fdb54985b50e378a.png" height="35" alt="Santander"></td></tr>
  <tr><td style="padding:30px;color:#222;line-height:1.6;">
    <h2 style="color:#ec0000;margin-top:0;">Deposit Notification</h2>
    <p>Dear [[RECIPIENT_NAME]],</p>
    <p>This email serves as notification that an incoming electronic transfer (ACH) from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <div style="background:#fcfcfc;border:1px solid #ddd;padding:20px;margin:20px 0;">
      <p style="margin:5px 0;"><strong>Amount:</strong> <span style="color:#ec0000;font-weight:bold;">$[[AMOUNT]]</span></p>
      <p style="margin:5px 0;"><strong>Status:</strong> <b>[[STATUS]]</b></p>
      <p style="margin:5px 0;"><strong>Date:</strong> [[DATE]]</p>
      <p style="margin:5px 0;"><strong>Account:</strong> Ending in [[ACCOUNT_NUMBER]]</p>
      <p style="margin:5px 0;"><strong>Description:</strong> [[DESCRIPTION]]</p>
    </div>
    <a href="https://www.santanderbank.com" style="background:#ec0000;color:#fff !important;padding:12px 25px;text-decoration:none;display:inline-block;font-weight:bold;">Go to Santander Online</a>
  </td></tr>
  <tr><td style="background:#f6f6f6;padding:20px;font-size:12px;color:#888;text-align:center;">Santander Bank, N.A. is a Member FDIC. &copy; [[CURRENT_YEAR]] Santander Bank.</td></tr>
</table>
</body></html>
HTML;

        // --- 17. BMO HARRIS ---
        $bmoHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>BMO Harris Alert</title></head>
<body style="margin:0;padding:20px;font-family:Helvetica,sans-serif;background-color:#f4f4f4;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #ddd;">
  <tr><td style="background-color:#0079c1;padding:20px;text-align:center;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_92ece96b40353d74a3ddc97e24c7c759.png" height="40" alt="BMO Harris"></td></tr>
  <tr><td style="padding:30px;color:#333;">
    <h3 style="color:#0079c1;margin-top:0;">Account Notification: Incoming Transfer</h3>
    <p>Hello [[RECIPIENT_NAME]],</p>
    <p>An electronic ACH transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <table width="100%" cellpadding="10" cellspacing="0" style="margin:20px 0;background:#f5fafc;border:1px solid #e2f0f8;">
      <tr><td style="font-weight:bold;color:#555;">Amount:</td><td style="font-weight:bold;color:#0079c1;font-size:18px;">$[[AMOUNT]]</td></tr>
      <tr><td style="font-weight:bold;color:#555;">Status:</td><td><b>[[STATUS]]</b></td></tr>
      <tr><td style="font-weight:bold;color:#555;">Date:</td><td>[[DATE]]</td></tr>
      <tr><td style="font-weight:bold;color:#555;">Account Ending:</td><td>[[ACCOUNT_NUMBER]]</td></tr>
      <tr><td style="font-weight:bold;color:#555;">Description:</td><td>[[DESCRIPTION]]</td></tr>
    </table>
    <center><a href="https://www.bmo.com" style="background:#0079c1;color:#fff !important;padding:12px 25px;text-decoration:none;display:inline-block;font-weight:bold;border-radius:4px;">Log In to BMO Digital Banking</a></center>
  </td></tr>
  <tr><td style="background-color:#f4f4f4;padding:15px;font-size:12px;color:#777;text-align:center;">BMO Harris Bank N.A. Member FDIC.</td></tr>
</table>
</body></html>
HTML;

        // --- 18. NAVY FEDERAL ---
        $navyHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Navy Federal Alert</title></head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background-color:#f2f5f8;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-top:6px solid #003366;border-bottom:1px solid #ccc;">
  <tr><td style="padding:25px;text-align:center;border-bottom:1px solid #eee;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_80d4e6e57624781d9c7558d36ad44591.png" height="40" alt="Navy Federal Credit Union"></td></tr>
  <tr><td style="padding:30px;color:#333;">
    <h2 style="color:#003366;margin-top:0;">Deposit Notification</h2>
    <p>Dear [[RECIPIENT_NAME]],</p>
    <p>This message confirms that an incoming electronic deposit from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <div style="background:#f4f7fa;border:1px solid #ddd;padding:20px;margin:25px 0;">
      <p style="margin:5px 0;"><strong>Amount:</strong> <span style="color:#003366;font-weight:bold;font-size:18px;">$[[AMOUNT]]</span></p>
      <p style="margin:5px 0;"><strong>Status:</strong> <b>[[STATUS]]</b></p>
      <p style="margin:5px 0;"><strong>Date:</strong> [[DATE]]</p>
      <p style="margin:5px 0;"><strong>Account:</strong> ...[[ACCOUNT_NUMBER]]</p>
      <p style="margin:5px 0;"><strong>Description:</strong> [[DESCRIPTION]]</p>
    </div>
    <center><a href="https://www.navyfederal.org" style="background:#003366;color:#ffffff !important;padding:12px 30px;text-decoration:none;font-weight:bold;border-radius:4px;display:inline-block;">Sign In to Online Banking</a></center>
  </td></tr>
  <tr><td style="background:#f2f5f8;padding:20px;font-size:11px;color:#777;text-align:center;">Navy Federal Credit Union is federally insured by NCUA. &copy; [[CURRENT_YEAR]] Navy Federal.</td></tr>
</table>
</body></html>
HTML;

        // --- 19. USAA ---
        $usaaHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>USAA Alert</title></head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background-color:#f4f4f4;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #ccc;">
  <tr><td style="background-color:#003a5d;padding:20px;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_4c9c69e8e626859061ecc0db95e9fe8a.png" height="35" alt="USAA"></td></tr>
  <tr><td style="padding:30px;color:#333;">
    <h2 style="color:#003a5d;margin-top:0;">Transfer Activity Alert</h2>
    <p>Hello [[RECIPIENT_NAME]],</p>
    <p>An ACH transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <table width="100%" cellpadding="10" cellspacing="0" style="margin:20px 0;border:1px solid #ccc;">
      <tr><td style="background:#f0f0f0;font-weight:bold;width:30%;">Amount</td><td style="font-weight:bold;color:#003a5d;font-size:18px;">$[[AMOUNT]]</td></tr>
      <tr><td style="background:#f0f0f0;font-weight:bold;width:30%;">Status</td><td style="font-weight:bold;">[[STATUS]]</td></tr>
      <tr><td style="background:#f0f0f0;font-weight:bold;">Date</td><td>[[DATE]]</td></tr>
      <tr><td style="background:#f0f0f0;font-weight:bold;">Account</td><td>...[[ACCOUNT_NUMBER]]</td></tr>
      <tr><td style="background:#f0f0f0;font-weight:bold;">Memo</td><td>[[DESCRIPTION]]</td></tr>
    </table>
    <a href="https://www.usaa.com" style="background:#003a5d;color:#fff !important;padding:12px 20px;text-decoration:none;display:inline-block;font-weight:bold;">Log on to USAA.com</a>
  </td></tr>
  <tr><td style="background-color:#003a5d;padding:15px;font-size:11px;color:#fff;text-align:center;">USAA Federal Savings Bank, Member FDIC.</td></tr>
</table>
</body></html>
HTML;

        // --- 20. CHARLES SCHWAB ---
        $schwabHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Schwab Alert</title></head>
<body style="margin:0;padding:20px;font-family:Helvetica,Arial,sans-serif;background-color:#f4f4f4;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-top:4px solid #00a0df;border-bottom:1px solid #ddd;">
  <tr><td style="padding:25px;text-align:left;border-bottom:1px solid #eee;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_03b044d826f3bdc50ddc87d2de29a17e.png" height="35" alt="Charles Schwab"></td></tr>
  <tr><td style="padding:30px;color:#333;line-height:1.6;">
    <h3 style="color:#00a0df;margin-top:0;">Account Alert: Deposit Received</h3>
    <p>Dear [[RECIPIENT_NAME]],</p>
    <p>This message confirms that an incoming electronic transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <div style="background:#f9f9f9;border:1px solid #eee;padding:20px;margin:25px 0;">
      <p style="margin:5px 0;"><strong>Transfer Amount:</strong> <span style="color:#00a0df;font-weight:bold;font-size:18px;">$[[AMOUNT]]</span></p>
      <p style="margin:5px 0;"><strong>Status:</strong> <b>[[STATUS]]</b></p>
      <p style="margin:5px 0;"><strong>Date:</strong> [[DATE]]</p>
      <p style="margin:5px 0;"><strong>Account Number:</strong> Ending in [[ACCOUNT_NUMBER]]</p>
      <p style="margin:5px 0;"><strong>Description:</strong> [[DESCRIPTION]]</p>
    </div>
    <a href="https://www.schwab.com" style="background:#00a0df;color:#fff !important;padding:14px 25px;text-decoration:none;display:inline-block;font-weight:bold;">Log in to Schwab.com</a>
  </td></tr>
  <tr><td style="background:#f4f4f4;padding:20px;font-size:12px;color:#888;text-align:center;">&copy; [[CURRENT_YEAR]] Charles Schwab & Co., Inc. All rights reserved. Member SIPC.</td></tr>
</table>
</body></html>
HTML;

        // --- 21. SYNCHRONY BANK ---
        $synchronyHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Synchrony Alert</title></head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background-color:#f8f8f8;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #ddd;border-top:5px solid #ffcc00;">
  <tr><td style="padding:25px;text-align:center;background:#000;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_a37e1d210849073d263054424091c79d.png" height="35" alt="Synchrony Bank"></td></tr>
  <tr><td style="padding:30px;color:#333;">
    <h2 style="color:#000;margin-top:0;">Deposit Notification</h2>
    <p>Hello [[RECIPIENT_NAME]],</p>
    <p>An ACH transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <table width="100%" cellpadding="10" cellspacing="0" style="background:#fff8cc;border-left:4px solid #ffcc00;margin:20px 0;">
      <tr><td style="font-weight:bold;">Amount:</td><td style="font-weight:bold;font-size:18px;">$[[AMOUNT]]</td></tr>
      <tr><td style="font-weight:bold;">Status:</td><td><b>[[STATUS]]</b></td></tr>
      <tr><td style="font-weight:bold;">Date:</td><td>[[DATE]]</td></tr>
      <tr><td style="font-weight:bold;">Memo:</td><td>[[DESCRIPTION]]</td></tr>
      <tr><td style="font-weight:bold;">Account:</td><td>...[[ACCOUNT_NUMBER]]</td></tr>
    </table>
    <center><a href="https://www.synchronybank.com" style="background:#ffcc00;color:#000 !important;padding:14px 30px;text-decoration:none;display:inline-block;font-weight:bold;border-radius:25px;">Log In Now</a></center>
  </td></tr>
  <tr><td style="padding:20px;font-size:12px;color:#888;text-align:center;border-top:1px solid #eee;">Synchrony Bank, Member FDIC.</td></tr>
</table>
</body></html>
HTML;

        // --- 22. FIRST CITIZENS BANK ---
        $firstcitHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>First Citizens Alert</title></head>
<body style="margin:0;padding:20px;font-family:Helvetica,sans-serif;background-color:#f5f5f5;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #ddd;">
  <tr><td style="background-color:#005596;padding:25px;text-align:center;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_82bed54a75625dec4e05f4e5ac2bb564.png" height="40" alt="First Citizens Bank"></td></tr>
  <tr><td style="padding:30px;color:#444;">
    <h3 style="color:#005596;">ACH Transfer Alert</h3>
    <p>Dear [[RECIPIENT_NAME]],</p>
    <p>An incoming electronic transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <div style="border:1px solid #eee;padding:15px;margin:20px 0;background:#f9fcfd;">
      <p style="margin:5px 0;"><strong>Amount:</strong> <span style="color:#005596;font-weight:bold;font-size:18px;">$[[AMOUNT]]</span></p>
      <p style="margin:5px 0;"><strong>Status:</strong> <b>[[STATUS]]</b></p>
      <p style="margin:5px 0;"><strong>Date:</strong> [[DATE]]</p>
      <p style="margin:5px 0;"><strong>Account:</strong> Ending in [[ACCOUNT_NUMBER]]</p>
      <p style="margin:5px 0;"><strong>Description:</strong> [[DESCRIPTION]]</p>
    </div>
    <a href="https://www.firstcitizens.com" style="background:#005596;color:#fff !important;padding:12px 25px;text-decoration:none;display:inline-block;font-weight:bold;border-radius:4px;">Log In to Digital Banking</a>
  </td></tr>
  <tr><td style="background-color:#f5f5f5;padding:15px;font-size:12px;color:#777;text-align:center;">First Citizens Bank. Member FDIC.</td></tr>
</table>
</body></html>
HTML;

        // --- 23. SECU (State Employees' Credit Union) ---
        $secuHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>SECU Alert</title></head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background-color:#eef2f1;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #ccc;border-top:5px solid #005b4a;">
  <tr><td style="padding:25px;text-align:left;border-bottom:1px solid #eee;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_e1286297567180605486f2f67082ec60.png" height="40" alt="SECU"></td></tr>
  <tr><td style="padding:30px;color:#333;">
    <h2 style="color:#005b4a;margin-top:0;">Deposit Alert</h2>
    <p>Hello [[RECIPIENT_NAME]],</p>
    <p>We are notifying you that an ACH transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <table width="100%" cellpadding="10" cellspacing="0" style="background:#f4f9f7;border-left:4px solid #005b4a;margin:20px 0;">
      <tr><td style="font-weight:bold;">Transfer Amount:</td><td style="color:#005b4a;font-weight:bold;font-size:18px;">$[[AMOUNT]]</td></tr>
      <tr><td style="font-weight:bold;">Status:</td><td><b>[[STATUS]]</b></td></tr>
      <tr><td style="font-weight:bold;">Date:</td><td>[[DATE]]</td></tr>
      <tr><td style="font-weight:bold;">Reference:</td><td>[[DESCRIPTION]]</td></tr>
      <tr><td style="font-weight:bold;">Account:</td><td>...[[ACCOUNT_NUMBER]]</td></tr>
    </table>
    <center><a href="https://www.ncsecu.org" style="background:#005b4a;color:#fff !important;padding:14px 30px;text-decoration:none;display:inline-block;font-weight:bold;border-radius:4px;">Access Member Access</a></center>
  </td></tr>
  <tr><td style="padding:20px;font-size:12px;color:#888;text-align:center;background:#eef2f1;">State Employees' Credit Union. Federally insured by NCUA.</td></tr>
</table>
</body></html>
HTML;

        // --- 24. M&T BANK ---
        $mtHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>M&T Bank Alert</title></head>
<body style="margin:0;padding:20px;font-family:Helvetica,Arial,sans-serif;background-color:#f4f4f4;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #ddd;border-top:5px solid #006c5b;">
  <tr><td style="padding:25px;text-align:center;border-bottom:1px solid #eee;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_9e4df17c45685f2fea915ace0290daff.png" height="40" alt="M&T Bank"></td></tr>
  <tr><td style="padding:30px;color:#333;">
    <h3 style="color:#006c5b;margin-top:0;">Account Alert: Incoming Transfer</h3>
    <p>Dear [[RECIPIENT_NAME]],</p>
    <p>An electronic transfer (ACH) from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <div style="background:#f9fbfb;border:1px solid #eee;padding:20px;margin:25px 0;">
      <p style="margin:5px 0;"><strong>Amount:</strong> <span style="color:#006c5b;font-weight:bold;font-size:18px;">$[[AMOUNT]]</span></p>
      <p style="margin:5px 0;"><strong>Status:</strong> <b>[[STATUS]]</b></p>
      <p style="margin:5px 0;"><strong>Date:</strong> [[DATE]]</p>
      <p style="margin:5px 0;"><strong>Account:</strong> Ending in [[ACCOUNT_NUMBER]]</p>
      <p style="margin:5px 0;"><strong>Description:</strong> [[DESCRIPTION]]</p>
    </div>
    <a href="https://www.mtb.com" style="background:#006c5b;color:#fff !important;padding:14px 25px;text-decoration:none;display:block;text-align:center;font-weight:bold;border-radius:4px;">Log In to Online Banking</a>
  </td></tr>
  <tr><td style="background:#f4f4f4;padding:20px;font-size:12px;color:#888;text-align:center;">&copy; [[CURRENT_YEAR]] M&T Bank. Member FDIC.</td></tr>
</table>
</body></html>
HTML;

        // --- 25. FIFTH THIRD BANK ---
        $fifthThirdHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Fifth Third Bank Alert</title></head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background-color:#f0f0f0;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #ccc;">
  <tr><td style="background-color:#003a70;padding:20px;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_03e7353ac840a51f2bb6562965e75565.png" height="35" alt="Fifth Third Bank"></td></tr>
  <tr><td style="height:5px;background-color:#008a00;"></td></tr>
  <tr><td style="padding:30px;color:#444;">
    <h2 style="color:#003a70;">Deposit Notification</h2>
    <p>Hello [[RECIPIENT_NAME]],</p>
    <p>An incoming ACH transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <table width="100%" cellpadding="8" cellspacing="0" style="margin:20px 0;border:1px solid #eee;">
      <tr style="background:#f9f9f9;"><td style="width:40%;color:#666;"><strong>Amount:</strong></td><td style="color:#008a00;font-weight:bold;font-size:16px;">$[[AMOUNT]]</td></tr>
      <tr><td style="color:#666;"><strong>Status:</strong></td><td style="font-weight:bold;">[[STATUS]]</td></tr>
      <tr style="background:#f9f9f9;"><td style="color:#666;"><strong>Account:</strong></td><td>...[[ACCOUNT_NUMBER]]</td></tr>
      <tr><td style="color:#666;"><strong>Date:</strong></td><td>[[DATE]]</td></tr>
      <tr style="background:#f9f9f9;"><td style="color:#666;"><strong>Memo:</strong></td><td>[[DESCRIPTION]]</td></tr>
    </table>
    <a href="https://www.53.com" style="background:#003a70;color:#fff !important;padding:12px 25px;text-decoration:none;display:inline-block;font-weight:bold;border-radius:4px;">Log In</a>
  </td></tr>
  <tr><td style="background-color:#eee;padding:15px;font-size:12px;color:#666;text-align:center;">Fifth Third Bank, Member FDIC.</td></tr>
</table>
</body></html>
HTML;

        // --- 26. ALLY BANK ---
        $allyHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Ally Bank Alert</title></head>
<body style="margin:0;padding:20px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;background-color:#f4f4f4;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,0.05);">
  <tr><td style="background-color:#512d6d;padding:30px;text-align:center;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_cab46fc873de0e5848e8fd20b9a7956f.png" height="40" alt="Ally Bank"></td></tr>
  <tr><td style="padding:40px;color:#333;">
    <h2 style="color:#512d6d;margin-top:0;">You've Got Funds</h2>
    <p>Hi [[RECIPIENT_NAME]],</p>
    <p>Good news! An incoming ACH transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <div style="background-color:#f9f4fa;border-left:4px solid #512d6d;padding:20px;margin:30px 0;">
      <p style="margin:0 0 10px 0;"><strong>Amount:</strong> <span style="color:#512d6d;font-size:22px;font-weight:bold;">$[[AMOUNT]]</span></p>
      <p style="margin:0 0 10px 0;"><strong>Status:</strong> <b>[[STATUS]]</b></p>
      <p style="margin:0 0 10px 0;"><strong>Account:</strong> ...[[ACCOUNT_NUMBER]]</p>
      <p style="margin:0 0 10px 0;"><strong>Date:</strong> [[DATE]]</p>
      <p style="margin:0;"><strong>Memo:</strong> [[DESCRIPTION]]</p>
    </div>
    <center><a href="https://www.ally.com" style="background-color:#512d6d;color:#ffffff !important;padding:15px 40px;text-decoration:none;border-radius:25px;font-weight:bold;display:inline-block;">Log in to Ally</a></center>
  </td></tr>
  <tr><td style="background-color:#f4f4f4;padding:20px;font-size:12px;color:#888;text-align:center;border-top:1px solid #eee;">
    <p>&copy; [[CURRENT_YEAR]] Ally Bank, Member FDIC.</p>
  </td></tr>
</table>
</body></html>
HTML;

        // --- 27. SUNCOAST CREDIT UNION ---
        $suncoastHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Suncoast Credit Union Alert</title></head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background-color:#f5f5f5;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-top:5px solid #006b54;border-bottom:1px solid #ddd;">
  <tr><td style="padding:25px;text-align:left;border-bottom:1px solid #eee;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_5d16dadee3776b3ab3e3e0ea568f5409.png" height="40" alt="Suncoast Credit Union"></td></tr>
  <tr><td style="padding:30px;color:#333;">
    <h3 style="color:#006b54;margin-top:0;">Account Alert: Deposit Notification</h3>
    <p>Dear [[RECIPIENT_NAME]],</p>
    <p>An electronic ACH transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <div style="background:#f4f9f7;border:1px solid #eee;padding:20px;margin:25px 0;">
      <p style="margin:5px 0;"><strong>Transfer Amount:</strong> <span style="color:#006b54;font-weight:bold;font-size:18px;">$[[AMOUNT]]</span></p>
      <p style="margin:5px 0;"><strong>Status:</strong> <b>[[STATUS]]</b></p>
      <p style="margin:5px 0;"><strong>Date:</strong> [[DATE]]</p>
      <p style="margin:5px 0;"><strong>Account Number:</strong> Ending in [[ACCOUNT_NUMBER]]</p>
      <p style="margin:5px 0;"><strong>Description:</strong> [[DESCRIPTION]]</p>
    </div>
    <a href="https://www.suncoastcreditunion.com" style="background:#006b54;color:#fff !important;padding:14px 25px;text-decoration:none;display:inline-block;font-weight:bold;border-radius:4px;">Log in to SunNet</a>
  </td></tr>
  <tr><td style="background:#f5f5f5;padding:20px;font-size:12px;color:#888;text-align:center;">Suncoast Credit Union. Federally insured by NCUA.</td></tr>
</table>
</body></html>
HTML;

        // --- 28. AMERICA FIRST CREDIT UNION ---
        $afcuHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>America First Alert</title></head>
<body style="margin:0;padding:20px;font-family:Helvetica,Arial,sans-serif;background-color:#e8e8e8;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #ccc;">
  <tr><td style="background-color:#bd1320;padding:20px;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_d21e512a72b5d549794625b9cf89e696.png" height="35" alt="America First Credit Union"></td></tr>
  <tr><td style="padding:30px;color:#333;">
    <h2 style="color:#bd1320;margin-top:0;">Transfer Activity Alert</h2>
    <p>Hello [[RECIPIENT_NAME]],</p>
    <p>An ACH transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <table width="100%" cellpadding="10" cellspacing="0" style="margin:20px 0;border:1px solid #ccc;">
      <tr><td style="background:#f9f9f9;font-weight:bold;width:30%;">Amount</td><td style="font-weight:bold;color:#bd1320;font-size:18px;">$[[AMOUNT]]</td></tr>
      <tr><td style="background:#f9f9f9;font-weight:bold;width:30%;">Status</td><td style="font-weight:bold;">[[STATUS]]</td></tr>
      <tr><td style="background:#f9f9f9;font-weight:bold;">Date</td><td>[[DATE]]</td></tr>
      <tr><td style="background:#f9f9f9;font-weight:bold;">Account</td><td>...[[ACCOUNT_NUMBER]]</td></tr>
      <tr><td style="background:#f9f9f9;font-weight:bold;">Memo</td><td>[[DESCRIPTION]]</td></tr>
    </table>
    <center><a href="https://www.americafirst.com" style="background:#bd1320;color:#fff !important;padding:12px 30px;text-decoration:none;display:inline-block;font-weight:bold;border-radius:4px;">Log on to Online Banking</a></center>
  </td></tr>
  <tr><td style="background-color:#bd1320;padding:15px;font-size:11px;color:#fff;text-align:center;">America First Credit Union. Federally insured by NCUA.</td></tr>
</table>
</body></html>
HTML;

        // --- 29. PENFED CREDIT UNION ---
        $penfedHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>PenFed Alert</title></head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background-color:#f0f0f0;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-top:4px solid #003a70;">
  <tr><td style="padding:25px;text-align:center;border-bottom:1px solid #eee;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_ac2a9824753a94ecbaad01415c8b78f4.png" height="40" alt="PenFed Credit Union"></td></tr>
  <tr><td style="padding:30px;color:#333;">
    <h3 style="color:#003a70;">Incoming Transfer Alert</h3>
    <p>Dear [[RECIPIENT_NAME]],</p>
    <p>An incoming transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <table width="100%" cellpadding="8" cellspacing="0" style="margin:20px 0;">
      <tr><td style="width:40%;color:#666;">Amount:</td><td style="font-weight:bold;font-size:18px;color:#003a70;">$[[AMOUNT]]</td></tr>
      <tr><td style="color:#666;">Status:</td><td style="font-weight:bold;">[[STATUS]]</td></tr>
      <tr><td style="color:#666;">Account Ending:</td><td style="font-weight:bold;">[[ACCOUNT_NUMBER]]</td></tr>
      <tr><td style="color:#666;">Date:</td><td style="font-weight:bold;">[[DATE]]</td></tr>
      <tr><td style="color:#666;">Reference:</td><td style="font-weight:bold;">[[DESCRIPTION]]</td></tr>
    </table>
    <a href="https://www.penfed.org" style="background:#003a70;color:#fff !important;padding:12px 25px;text-decoration:none;display:block;text-align:center;font-weight:bold;border-radius:4px;">Log In to PenFed Online</a>
  </td></tr>
  <tr><td style="background:#333;padding:20px;font-size:11px;color:#ccc;text-align:center;">PenFed Credit Union. Federally insured by NCUA.</td></tr>
</table>
</body></html>
HTML;

        // --- 30. GOLDEN 1 CREDIT UNION ---
        $golden1Html = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Golden 1 Alert</title></head>
<body style="margin:0;padding:20px;font-family:Helvetica,sans-serif;background-color:#f4f4f4;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #ccc;">
  <tr><td style="background-color:#cf142b;padding:20px;text-align:center;"><img src="[[APP_URL]]/assets/images/bank_logos/logo_c56007b3220051b07078feb9cfcfd5dc.png" height="40" alt="Golden 1 Credit Union"></td></tr>
  <tr><td style="padding:30px;color:#444;">
    <h2 style="color:#cf142b;">Notification of Deposit</h2>
    <p>Hello [[RECIPIENT_NAME]],</p>
    <p>An electronic ACH transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <div style="border:1px solid #eee;padding:15px;margin:20px 0;background:#fff8f9;">
      <p style="margin:5px 0;"><strong>Amount:</strong> <span style="color:#cf142b;font-weight:bold;font-size:18px;">$[[AMOUNT]]</span></p>
      <p style="margin:5px 0;"><strong>Status:</strong> <b>[[STATUS]]</b></p>
      <p style="margin:5px 0;"><strong>Date:</strong> [[DATE]]</p>
      <p style="margin:5px 0;"><strong>Account:</strong> *[[ACCOUNT_NUMBER]]</p>
      <p style="margin:5px 0;"><strong>Memo:</strong> [[DESCRIPTION]]</p>
    </div>
    <a href="https://www.golden1.com" style="background:#cf142b;color:#fff !important;padding:12px 20px;text-decoration:none;display:inline-block;font-weight:bold;border-radius:4px;">Log in to Online Banking</a>
  </td></tr>
  <tr><td style="background-color:#eee;padding:15px;font-size:12px;color:#666;text-align:center;">Golden 1 Credit Union. Federally insured by NCUA.</td></tr>
</table>
</body></html>
HTML;

        // --- 31. FrontField Credit Union ---
        $FrontFieldHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>FrontField FCU Alert</title></head>
<body style="margin:0;padding:20px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;background-color:#f4f7f6;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e0e6e5;border-top:6px solid #00888b;">
  <tr><td style="background-color:#ffffff;padding:25px;text-align:center;border-bottom:1px solid #eef2f1;"><img src="[[APP_URL]]/assets/images/bank_logos/FrontField.png" height="50" alt="FrontField FCU"></td></tr>
  <tr><td style="padding:40px;color:#333333;line-height:1.6;">
    <h2 style="color:#00888b;margin-top:0;font-size:22px;font-weight:600;">Incoming Transaction Notification</h2>
    <p>Dear [[RECIPIENT_NAME]],</p>
    <p>We are pleased to notify you that an incoming ACH electronic transfer from [[SENDER_NAME]] [[STATUS_DESC]]</p>
    <div style="background-color:#f0f8f8;border-left:4px solid #00888b;padding:22px;margin:30px 0;border-radius:4px;">
      <p style="margin:0 0 10px 0;font-size:15px;"><strong>Transfer Amount:</strong> <span style="color:#00888b;font-size:22px;font-weight:bold;">$[[AMOUNT]]</span></p>
      <p style="margin:0 0 10px 0;font-size:15px;"><strong>Current Status:</strong> <span style="font-weight:bold;color:#00888b;">[[STATUS]]</span></p>
      <p style="margin:0 0 10px 0;font-size:15px;"><strong>Date Processed:</strong> [[DATE]]</p>
      <p style="margin:0 0 10px 0;font-size:15px;"><strong>Account Number:</strong> Ending in [[ACCOUNT_NUMBER]]</p>
      <p style="margin:0;font-size:15px;"><strong>Memo/Reference:</strong> [[DESCRIPTION]]</p>
    </div>
    <center><a href="[[APP_URL]]" style="background-color:#00888b;color:#ffffff !important;padding:14px 35px;text-decoration:none;border-radius:4px;font-weight:bold;display:inline-block;font-size:16px;">Access Online Banking</a></center>
  </td></tr>
  <tr><td style="background-color:#f4f7f6;padding:25px;font-size:12px;color:#666666;text-align:center;border-top:1px solid #eef2f1;">
    <p style="margin:0 0 10px 0;font-weight:bold;color:#00888b;">FrontField Credit Union</p>
    <p style="margin:0;line-height:1.5;">Federally insured by NCUA. Equal Housing Opportunity. This is an automated email. Please do not reply directly.</p>
  </td></tr>
</table>
</body></html>
HTML;

        // Compile array
        $banksData = [
            ['Capital One', 'capitalone.com', $capOneHtml],
            ['U.S. Bank', 'usbank.com', $usbankHtml],
            ['TD Bank', 'td.com', $tdHtml],
            ['Truist', 'truist.com', $truistHtml],
            ['KeyBank', 'key.com', $keyHtml],
            ['Regions Bank', 'regions.com', $regionsHtml],
            ['Discover Bank', 'discover.com', $discoverHtml],
            ['Santander Bank', 'santander.com', $santanderHtml],
            ['BMO Harris', 'bmo.com', $bmoHtml],
            ['Navy Federal Credit Union', 'navyfederal.org', $navyHtml],
            ['USAA', 'usaa.com', $usaaHtml],
            ['Charles Schwab', 'schwab.com', $schwabHtml],
            ['Synchrony Bank', 'synchrony.com', $synchronyHtml],
            ['First Citizens Bank', 'firstcitizens.com', $firstcitHtml],
            ['State Employees Credit Union', 'ncsecu.org', $secuHtml],
            ['M&T Bank', 'mtb.com', $mtHtml],
            ['Fifth Third Bank', '53.com', $fifthThirdHtml],
            ['Ally Bank', 'ally.com', $allyHtml],
            ['Suncoast Credit Union', 'suncoastcreditunion.com', $suncoastHtml],
            ['America First Credit Union', 'americafirst.com', $afcuHtml],
            ['PenFed Credit Union', 'penfed.org', $penfedHtml],
            ['Golden 1 Credit Union', 'golden1.com', $golden1Html],
            [setting('site_title', 'global') ?? 'FrontField Credit Union', parse_url(config('app.url'), PHP_URL_HOST) ?? 'FrontFieldcu.com', $FrontFieldHtml],
        ];

        foreach ($banksData as $data) {
            $name = $data[0];
            $domain = $data[1];
            $html = $data[2];

            $generated[] = [
                'name' => "{$name} Notification",
                'email_from_name' => "{$name} Alerts",
                'description' => "{$name} branding",
                'email_subject' => "{$name}: Account Transfer Alert",
                'email_content' => $html,
                'email_footer' => '',
                'content' => "{$name} Template"
            ];
        }

        return $generated;
    }
}
