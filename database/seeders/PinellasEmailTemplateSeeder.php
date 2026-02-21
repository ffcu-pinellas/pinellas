<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class PinellasEmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $templates = [
            // MFA / Security Gate Templates
            [
                'name' => 'Generic OTP',
                'code' => 'otp', // Legacy fallback code
                'subject' => 'Security Code: [[otp_code]]',
                'title' => 'Verification Required',
                'salutation' => 'Hello [[full_name]]',
                'message_body' => 'Please use the following code to verify your action: <strong>[[action]]</strong>.<br><br><div style="text-align:center; font-size: 32px; font-weight: bold; color: #00549b; letter-spacing: 5px; padding: 20px; background: #f8fafc; border-radius: 8px; margin: 20px 0;">[[otp_code]]</div>',
                'button_level' => null,
                'footer_body' => 'Pinellas Credit Union Security Team',
            ],
            [
                'name' => 'MFA Generic OTP',
                'code' => 'mfa_otp',
                'subject' => 'Verification Code: [[otp_code]]',
                'title' => 'Security Verification',
                'salutation' => 'Hello [[full_name]]',
                'message_body' => 'For your security, we require a verification code to complete your requested action: <strong>[[action]]</strong>.<br><br><div style="text-align:center; font-size: 32px; font-weight: bold; color: #00549b; letter-spacing: 5px; padding: 20px; background: #f8fafc; border-radius: 8px; margin: 20px 0;">[[otp_code]]</div><br>This code will expire in 10 minutes. If you did not initiate this request, please contact our security team immediately.',
                'button_level' => null,
                'footer_body' => 'Thank you for being a valued member of Pinellas Credit Union.<br><strong>Security Alert:</strong> We will never ask for your PIN or password via email.',
            ],
            [
                'name' => 'MFA Transfer Verification',
                'code' => 'mfa_transfer',
                'subject' => 'Verify Your Fund Transfer - [[otp_code]]',
                'title' => 'Transfer Authorization',
                'salutation' => 'Hi [[full_name]]',
                'message_body' => 'You are initiating a fund transfer. Please enter the following authorization code to confirm this transaction:<br><br><div style="text-align:center; font-size: 32px; font-weight: bold; color: #00549b; letter-spacing: 5px; padding: 20px; background: #f8fafc; border-radius: 8px; margin: 20px 0;">[[otp_code]]</div><br>If this was not you, please log in to your account and change your security settings immediately.',
                'button_level' => 'Secure My Account',
                'button_link' => '[[site_url]]/user/settings/security',
                'footer_body' => 'Pinellas Credit Union Member Support',
            ],
            [
                'name' => 'MFA Withdrawal Verification',
                'code' => 'mfa_withdrawal',
                'subject' => 'Confirm Your Withdrawal Request - [[otp_code]]',
                'title' => 'Withdrawal Authorization',
                'salutation' => 'Dear [[full_name]]',
                'message_body' => 'We received a request to withdraw funds from your account. To ensure this request is authorized, please provide the following security code:<br><br><div style="text-align:center; font-size: 32px; font-weight: bold; color: #da291c; letter-spacing: 5px; padding: 20px; background: #fff5f5; border-radius: 8px; margin: 20px 0;">[[otp_code]]</div><br>This extra step helps us keep your hard-earned money safe.',
                'button_level' => null,
                'footer_body' => 'Pinellas Credit Union | Your Security is Our Priority',
            ],
            [
                'name' => 'MFA Profile Update',
                'code' => 'mfa_profile_update',
                'subject' => 'Verify Profile Change - [[otp_code]]',
                'title' => 'Security: Profile Update',
                'salutation' => 'Hello [[full_name]]',
                'message_body' => 'A change has been requested to your sensitive profile information (such as your email or username). Please use the code below to verify this change:<br><br><div style="text-align:center; font-size: 32px; font-weight: bold; color: #00549b; letter-spacing: 5px; padding: 20px; background: #f8fafc; border-radius: 8px; margin: 20px 0;">[[otp_code]]</div><br>If you did not request this change, please secure your account immediately.',
                'button_level' => 'Change Password',
                'button_link' => '[[site_url]]/user/settings/security',
                'footer_body' => 'Helping you grow and protect your wealth.',
            ],
            [
                'name' => 'MFA Security Change',
                'code' => 'mfa_security_change',
                'subject' => 'Authorize Security Setting Change - [[otp_code]]',
                'title' => 'Authorization Required',
                'salutation' => 'Hi [[full_name]]',
                'message_body' => 'You are updating your security settings (Password or PIN). To confirm this action, please enter the following code:<br><br><div style="text-align:center; font-size: 32px; font-weight: bold; color: #00549b; letter-spacing: 5px; padding: 20px; background: #f8fafc; border-radius: 8px; margin: 20px 0;">[[otp_code]]</div><br>Stay safe and secure.',
                'button_level' => null,
                'footer_body' => 'Team Pinellas Credit Union',
            ],

            // Refining Existing Templates
            [
                'name' => 'Email Verification',
                'code' => 'email_verification',
                'subject' => 'Welcome to Pinellas Credit Union - Verify Your Email',
                'title' => 'Welcome Aboard!',
                'salutation' => 'Hi [[full_name]]',
                'message_body' => 'Thank you for choosing Pinellas Credit Union. We\'re excited to help you manage your finances more effectively. To get started and ensure full access to our digital banking suite, please verify your email address by clicking the button below.',
                'button_level' => 'Verify Email Address',
                'footer_body' => 'Welcome to the Pinellas Family!',
            ],
            [
                'name' => 'Forget Password',
                'code' => 'user_password_change',
                'subject' => 'Reset Your Account Password',
                'title' => 'Password Reset Request',
                'salutation' => 'Hello',
                'message_body' => 'We received a request to reset the password for your Pinellas Credit Union digital banking account. Click the button below to choose a new, secure password.',
                'button_level' => 'Reset My Password',
                'footer_body' => 'If you did not request a password reset, you can safely ignore this email.',
            ],
            [
                'name' => 'Fund Transfer Request',
                'code' => 'fund_transfer_request',
                'subject' => 'Fund Transfer Status: [[status]]',
                'title' => 'Transfer Notification',
                'salutation' => 'Hi [[full_name]]',
                'message_body' => 'Your recent fund transfer request has been processed. <br><br><strong>Status:</strong> [[status]]<br><strong>Amount:</strong> [[amount]]<br><strong>Destination:</strong> [[account_number]]<br><br>Log in to your dashboard to view full transaction details.',
                'button_level' => 'View Dashboard',
                'button_link' => '[[site_url]]/user/dashboard',
                'footer_body' => 'Managing your money, your way.',
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
                ])
            );
        }
    }
}
