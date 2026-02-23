<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class PinellasEmailTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            // MFA / Security Gate Templates
            [
                'name' => 'Email Verification',
                'code' => 'email_verification',
                'subject' => 'Verify Your Email Address - Pinellas Federal Credit Union',
                'title' => 'Email Verification Required',
                'salutation' => 'Hello [[full_name]]',
                'message_body' => 'Welcome to Pinellas Federal Credit Union. To ensure the security of your account and activate your full digital banking features, please verify your email address. This step helps us protect your sensitive financial information.',
                'button_level' => 'Verify My Email',
                'button_link' => '[[token]]',
                'footer_body' => 'Secure Banking, Simplified.',
            ],
            [
                'name' => 'Generic OTP',
                'code' => 'otp',
                'subject' => 'Your Security Code: [[otp_code]]',
                'title' => 'Identity Verification',
                'salutation' => 'Hello [[full_name]]',
                'message_body' => 'For your security, we require a verification code to authorize the following action: <strong>[[action]]</strong>.<br><br><div style="text-align:center; font-size: 32px; font-weight: bold; color: #00549b; letter-spacing: 5px; padding: 20px; background: #f8fafc; border-radius: 8px; margin: 20px 0;">[[otp_code]]</div><br>This code is valid for a single use only. If you did not initiate this request, please contact our security team immediately.',
                'footer_body' => 'Pinellas FCU Security Team',
            ],
            [
                'name' => 'MFA Generic OTP',
                'code' => 'mfa_otp',
                'subject' => 'Authorization Code: [[otp_code]]',
                'title' => 'Security Authorization',
                'salutation' => 'Dear [[full_name]]',
                'message_body' => 'To complete your requested action (<strong>[[action]]</strong>), please enter the authorization code provided below:<br><br><div style="text-align:center; font-size: 32px; font-weight: bold; color: #00549b; letter-spacing: 5px; padding: 20px; background: #f8fafc; border-radius: 8px; margin: 20px 0;">[[otp_code]]</div><br>This code will expire in 10 minutes. <strong>Pinellas FCU will never ask for this code over the phone or via text.</strong>',
                'footer_body' => 'Always Secure. Always Pinellas.',
            ],
            [
                'name' => 'Forget Password',
                'code' => 'user_password_change',
                'subject' => 'Password Reset Request',
                'title' => 'Security: Password Update',
                'salutation' => 'Hello [[full_name]]',
                'message_body' => 'We received a request to reset your digital banking password. To choose a new secure password, please click the button below. If you did not make this request, your account is still secure and you can safely ignore this email.',
                'button_level' => 'Set New Password',
                'button_link' => '[[token]]',
                'footer_body' => 'Your Security is Our Priority.',
            ],

            // Transaction / Account Activity Templates
            [
                'name' => 'Fund Transfer Request',
                'code' => 'fund_transfer_request',
                'subject' => 'Transfer Notification: [[status]]',
                'title' => 'Transaction Status Update',
                'salutation' => 'Hi [[full_name]]',
                'message_body' => 'This is an official notification regarding your recent fund transfer request for <strong>[[amount]]</strong>. Your request status has been updated to: <strong>[[status]]</strong>.<br><br><strong>Transaction Details:</strong><br>Recipient: [[account_name]]<br>Account: [[account_number]]<br>Subtotal: [[amount]]<br>Deducted Amount: [[total_amount]]',
                'button_level' => 'View Activity',
                'button_link' => '[[site_url]]/user/dashboard',
                'footer_body' => 'Thank you for banking with Pinellas Federal Credit Union.',
            ],
            [
                'name' => 'Manual Deposit Action',
                'code' => 'user_manual_deposit_request',
                'subject' => 'Deposit Notification: [[status]]',
                'title' => 'Account Activity: Deposit',
                'salutation' => 'Hello [[full_name]]',
                'message_body' => 'We are writing to update you on your recent deposit request. The transaction status is currently: <strong>[[status]]</strong>.<br><br><strong>Transaction Details:</strong><br>ID: [[txn]]<br>Gateway: [[gateway_name]]<br>Amount: [[deposit_amount]]<br><br>[[message]]',
                'button_level' => 'View My Account',
                'button_link' => '[[site_url]]/user/deposits/history',
                'footer_body' => 'Supporting Your Financial Growth.',
            ],
            [
                'name' => 'Withdraw Request Action',
                'code' => 'withdraw_request_user',
                'subject' => 'Withdrawal Notification: [[status]]',
                'title' => 'Account Activity: Withdrawal',
                'salutation' => 'Dear [[full_name]]',
                'message_body' => 'This email is to inform you of a status update regarding your withdrawal request. Your request is currently: <strong>[[status]]</strong>.<br><br><strong>Transaction Details:</strong><br>Transaction ID: [[txn]]<br>Amount: [[withdraw_amount]]<br>Method: [[method_name]]<br><br>[[message]]',
                'button_level' => 'Review Transaction',
                'button_link' => '[[site_url]]/user/withdraw/history',
                'footer_body' => 'Committed to Your Financial Success.',
            ],

            // Lending / Plans
            [
                'name' => 'Loan Approved',
                'code' => 'loan_approved',
                'subject' => 'Your Loan Application Status: [[status]]',
                'title' => 'Lending Decision Notification',
                'salutation' => 'Dear [[full_name]]',
                'message_body' => 'We are pleased to provide an update on your [[plan_name]] loan application. Your application has been updated to: <strong>[[status]]</strong>.<br><br><strong>Summary:</strong><br>Loan Amount: [[loan_amount]]<br>Installment Rate: [[installment_rate]]<br><br>Please log in to your dashboard to review the terms and complete any remaining steps.',
                'button_level' => 'Review Loan Terms',
                'button_link' => '[[site_url]]/user/loan/list',
                'footer_body' => 'Empowering Your Dreams.',
            ],
            [
                'name' => 'DPS Notification', // Generic Savings Plan
                'code' => 'dps_completed',
                'subject' => 'Savings Plan Notification: [[status]]',
                'title' => 'Savings Account Activity',
                'salutation' => 'Hello [[full_name]]',
                'message_body' => 'We have an update regarding your <strong>[[plan_name]]</strong> savings plan. Activity has been recorded with the status: <strong>[[status]]</strong>.<br><br><strong>Activity Details:</strong><br>Plan Type: [[plan_name]]<br>Matured Amount/Balance: [[matured_amount]]<br><br>Check your dashboard for comprehensive savings history.',
                'button_level' => 'View Savings',
                'button_link' => '[[site_url]]/user/dps/list',
                'footer_body' => 'Building Your Future, One Step at a Time.',
            ],

            // User Management
            [
                'name' => 'User Account Disabled',
                'code' => 'user_account_disabled',
                'subject' => 'Important: Account Security Notification',
                'title' => 'Account Status Update',
                'salutation' => 'Dear [[full_name]]',
                'message_body' => 'For your security and the protection of your assets, your digital banking access has been temporarily deactivated. This often occurs due to extended inactivity or for account verification purposes.<br><br>To restore your access, please contact our member support team or attempt to securely verify your identity through the portal below.',
                'button_level' => 'Contact Support',
                'button_link' => '[[site_url]]/contact',
                'footer_body' => 'Integrity in Every Transaction.',
            ],
            [
                'name' => 'KYC Notification',
                'code' => 'kyc_action',
                'subject' => 'Identity Verification Update',
                'title' => 'KYC / Proof of Identity',
                'salutation' => 'Hi [[full_name]]',
                'message_body' => 'Your identity verification documents (KYC) have been reviewed. The current status of your verification is: <strong>[[status]]</strong>.<br><br>[[message]]',
                'button_level' => 'View Verification Status',
                'button_link' => '[[site_url]]/user/kyc',
                'footer_body' => 'Complying with Federal Regulations for Your Safety.',
            ],
             [
                'name' => 'Support Ticket Update',
                'code' => 'user_support_ticket',
                'subject' => 'Update: Support Ticket [[title]]',
                'title' => 'Support Request Update',
                'salutation' => 'Hello [[full_name]]',
                'message_body' => 'We have an update regarding your support ticket: <strong>[[title]]</strong>.<br><br><strong>Current Status:</strong> [[status]]<br><br><strong>Message:</strong><br>[[message]]',
                'button_level' => 'View Support Ticket',
                'button_link' => '[[site_url]]/user/support-ticket',
                'footer_body' => 'We Are Here to Assist You.',
            ],
        ];

        foreach ($templates as $data) {
            EmailTemplate::updateOrCreate(
                ['code' => $data['code']],
                array_merge($data, [
                    'for' => $data['for'] ?? 'User',
                    'status' => 1,
                    'footer_status' => 1,
                    'bottom_status' => 0,
                    'banner' => null, // Professional standard: No generic banners unless specific
                ])
            );
        }
    }
}
