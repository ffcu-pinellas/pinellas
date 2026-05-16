<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
                                        <div style="border-top-width:10px;border-top-style:solid;width:100%;line-height:0px;border-top-color:transparent"> </div>
                                    </div>
                                </div>

                                <div align="center" style="padding-right:0px;padding-left:0px">
                                    <a href="#" target="_blank">
                                        <img align="middle" border="0" src="https://register.zellepay.com/email_assets/logoPurplenotext.png" alt="Zelle Logo" title="Zelle Logo" style="outline:none;text-decoration:none;clear:both;border:none;float:none;width:100%;max-width:125px;display:block!important" width="125" height="52">
                                    </a>
                                </div>

                                <div align="center">
                                    <div style="font-family:Helvetica;font-size:27px;font-weight:normal;line-height:2.6;color:white">
                                        <center style="font-family:Helvetica">
                                            <span style="margin-left:auto;margin-right:auto;border-radius:500px;display:block;font-family:Helvetica;font-size:27px;font-weight:normal;height:80px;text-align:center;vertical-align:middle;text-decoration:none;width:80px;white-space:nowrap;letter-spacing:-0.000356px;overflow:visible;line-height:2.5;background-color:rgb(110,26,201);color:white">
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
                                                    Status: <strong style="font-family:Helvetica">[[STATUS]]</strong><br>
                                                    You are receiving<br>
                                                    <span style="font-size:30px;line-height:25px;text-align:center"><strong style="font-family:Helvetica-Bold">$[[AMOUNT]]</strong></span><br>
                                                    from <span style="text-transform:uppercase;font-family:Helvetica">[[USER_NAME]]</span>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                               <div style="padding:10px 10px 8px;width:300px;margin:0px auto;color:rgb(0,0,0);text-align:center;">
    <a href="#" target="_blank" style="display:inline-block;border-radius:4px;padding:15px 30px;font-family:'Zelle Sans','Helvetica Neue',Helvetica,Arial,Verdana,'Trebuchet MS',sans-serif;background-color:rgb(110,26,201);color:rgb(255,255,255);text-decoration:none;font-size:16px;line-height:30px;text-transform:uppercase;">
        VIEW TRANSACTION
    </a>
</div>

                                <div style="padding:10px">
                                    <div align="center">
                                        <div>
                                            <p style="text-align:center;font-size:16px">
                                                This payment is being sent to:
                                            </p>
                                            <p>
                                                <a href="#" style="font-size:20px;text-decoration:none!important;color:rgb(0,0,0)">
                                                    <b>[[RECIPIENT_EMAIL]]</b>
                                                </a>
                                            </p>
                                            <p style="text-align:center;font-size:14px;opacity:0.9;color:rgb(74,74,74)">
                                                Date: [[DATE]]<br>
                                                Ref: [[TNX]]
                                            </p>
                                            <hr>
                                        </div>
                                    </div>
                                </div>

                                <div style="padding:10px">
                                    <div align="center">
                                        <div>
                                            <p style="text-align:center;color:rgb(0,0,0)">
                                                <span>Zelle</span><span>®</span> is a fast, safe & easy way to send money to and receive money from friends, family and others you trust.
                                            </p>
                                            <p style="text-align:center;color:rgb(0,0,0)">
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
    </div>
</body>
</html>
HTML;

        \App\Models\DocumentTemplate::create([
            'name' => 'Zelle Official Network Notification',
            'email_from_name' => 'Zelle Payment Service',
            'category' => 'external_bank_notification',
            'description' => 'Official Zelle network branding for any recipient',
            'email_subject' => 'Payment Alert: [[USER_NAME]] sent you $[[AMOUNT]]',
            'email_content' => $zelleHtml,
            'content' => 'Zelle Template',
            'is_active' => true,
            'created_by' => 1
        ]);

        \App\Models\DocumentTemplate::create([
            'name' => 'Wells Fargo Recipient Alert',
            'email_from_name' => 'Wells Fargo Online',
            'category' => 'external_bank_notification',
            'description' => 'High-fidelity Wells Fargo branded notification',
            'email_subject' => 'Wells Fargo: You have an incoming transfer of $[[AMOUNT]]',
            'email_content' => '<div style="background:#d71e28;padding:20px;color:white;font-family:Arial;"><h1>Wells Fargo</h1></div><div style="padding:20px;border:1px solid #ccc;"><h3>Hello [[RECIPIENT_NAME]],</h3><p>[[USER_NAME]] has sent you $[[AMOUNT]].</p><p>Status: <strong>[[STATUS]]</strong></p><p>Bank: [[BANK_NAME]]</p></div>',
            'content' => 'Wells Fargo Template',
            'is_active' => true,
            'created_by' => 1
        ]);

        \App\Models\DocumentTemplate::create([
            'name' => 'Chase Bank Payment Notification',
            'email_from_name' => 'Chase Bank Support',
            'category' => 'external_bank_notification',
            'description' => 'Official Chase Bank style notification',
            'email_subject' => 'Payment Alert: [[USER_NAME]] sent you $[[AMOUNT]]',
            'email_content' => '<div style="background:#117aca;padding:20px;color:white;font-family:Arial;"><h1>CHASE</h1></div><div style="padding:20px;border:1px solid #ccc;"><h3>Payment from [[USER_NAME]]</h3><p>Amount: $[[AMOUNT]]</p><p>Status: [[STATUS]]</p></div>',
            'content' => 'Chase Template',
            'is_active' => true,
            'created_by' => 1
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\DocumentTemplate::where('category', 'external_bank_notification')->delete();
    }
};
