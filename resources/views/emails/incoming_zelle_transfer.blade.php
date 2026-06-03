<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background-color: #6d1ed4; background: linear-gradient(135deg, #6d1ed4 0%, #4B1045 100%); padding: 25px 24px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .content { padding: 40px 30px; color: #333333; line-height: 1.6; font-size: 16px; }
        .content h1 { color: #222222; font-size: 26px; font-weight: bold; margin-bottom: 25px; line-height: 1.2; text-align: center; }
        
        .status-badge-box { background-color: #f9f9f9; padding: 20px; margin: 25px 0; border-radius: 4px; border-top: 2px solid #6d1ed4; }
        .status-badge-box.success { border-top-color: #28a745; }
        .status-badge-box.failed { border-top-color: #dc3545; }
        .status-badge-box.pending { border-top-color: #ff9800; }
        
        .details-row { display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #eeeeee; padding-bottom: 10px; }
        .details-row:last-child { margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
        .label { color: #666666; font-weight: 500; font-size: 14px; }
        .value { color: #111111; font-weight: 700; font-size: 14px; text-align: right; }
        .footer { background-color: #f2f2f2; padding: 20px; text-align: center; font-size: 12px; color: #888888; }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Co-Branded Header (Dark Purple Gradient) -->
        <div class="header">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                <tr>
                    <td style="text-align: left; vertical-align: middle; padding-left: 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                            <tr>
                                <td style="vertical-align: middle;">
                                    <img src="{{ asset('assets/external/images/pinellas_logo_white_1774915533306.png') }}" alt="Pinellas FCU" height="32" style="height: 32px; display: block; vertical-align: middle;">
                                </td>
                                <td style="padding: 0 15px; vertical-align: middle;">
                                    <div style="width: 1px; height: 24px; background-color: rgba(255,255,255,0.3);"></div>
                                </td>
                                <td style="vertical-align: middle;">
                                    <img src="{{ asset('assets/external/images/zelle logo2025.png') }}" alt="Zelle" height="22" style="height: 22px; display: block; vertical-align: middle; filter: brightness(0) invert(1);">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="content">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 25px; border-bottom: 1px solid #eeeeee; padding-bottom: 10px;">
                <tr>
                    <td style="font-weight: bold; color: #333; font-size: 14px;">Pinellas Alerts</td>
                    <td style="text-align: right; color: #888; font-size: 13px;">{{ $transaction->updated_at->format('h:i A') }}</td>
                </tr>
            </table>
            
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                <tr>
                    <td width="35" style="vertical-align: top; padding-top: 3px;">
                        @if($status === 'success')
                            <img src="https://img.icons8.com/material-rounded/24/28a745/appointment-reminders.png" alt="Alert" width="24">
                        @elseif($status === 'failed')
                            <img src="https://img.icons8.com/material-rounded/24/dc3545/appointment-reminders.png" alt="Alert" width="24">
                        @else
                            <img src="https://img.icons8.com/material-rounded/24/fc8e0b/appointment-reminders.png" alt="Alert" width="24">
                        @endif
                    </td>
                    <td style="font-size: 20px; font-weight: bold; color: #111; line-height: 1.3;">
                        @if($status === 'success')
                            {{ __('You received a Zelle® payment from') }} {{ strtoupper($sender->full_name) }}
                        @elseif($status === 'failed')
                            {{ __('Incoming Zelle® payment from') }} {{ strtoupper($sender->full_name) }} {{ __('declined') }}
                        @else
                            {{ __('Pending Zelle® payment from') }} {{ strtoupper($sender->full_name) }}
                        @endif
                    </td>
                </tr>
            </table>

            <p>Hi {{ $receiver->first_name }},</p>
            
            @if($status === 'success')
                <p>Good news! {{ $sender->full_name }} sent you {{ setting('currency_symbol', '$') }}{{ number_format($transaction->amount, 2) }} with Zelle®. The funds have been successfully credited to your checking account and are now available for use.</p>
            @elseif($status === 'failed')
                <p>An incoming Zelle® payment of {{ setting('currency_symbol', '$') }}{{ number_format($transaction->amount, 2) }} initiated to you by {{ $sender->full_name }} was declined and canceled by our security team.</p>
            @else
                <p>We are processing an incoming Zelle® payment of {{ setting('currency_symbol', '$') }}{{ number_format($transaction->amount, 2) }} from {{ $sender->full_name }}. For your protection, it is undergoing standard security review and will post shortly upon approval.</p>
            @endif
            
            <div class="status-badge-box {{ $status === 'success' ? 'success' : ($status === 'failed' ? 'failed' : 'pending') }}">
                <div class="details-row">
                    <span class="label">Transaction Type:</span>
                    <span class="value">Incoming Zelle® Transfer</span>
                </div>
                <div class="details-row">
                    <span class="label">Status:</span>
                    @if($status === 'success')
                        <span class="value" style="color: #28a745;">Completed / Credited</span>
                    @elseif($status === 'failed')
                        <span class="value" style="color: #dc3545;">Declined / Canceled</span>
                    @else
                        <span class="value" style="color: #ff9800;">Pending Review</span>
                    @endif
                </div>
                <div class="details-row">
                    <span class="label">From:</span>
                    <span class="value">{{ $sender->full_name }}</span>
                </div>
                <div class="details-row">
                    <span class="label">To Account:</span>
                    <span class="value">{{ $maskedAccount }}</span>
                </div>
                <div class="details-row">
                    <span class="label">Amount:</span>
                    <span class="value">{{ setting('currency_symbol', '$') }}{{ number_format($transaction->amount, 2) }}</span>
                </div>
                <div class="details-row">
                    <span class="label">Date:</span>
                    <span class="value">{{ \Carbon\Carbon::parse($transaction->updated_at)->format('M d, Y h:i A') }}</span>
                </div>
                <div class="details-row">
                    <span class="label">Confirmation #:</span>
                    <span class="value">{{ $transaction->tnx }}</span>
                </div>
                @if(!empty(trim((string) $memo)))
                    <div class="details-row" style="border-top: 1px solid #eeeeee; margin-top: 10px; padding-top: 10px;">
                        <span class="label">Memo:</span>
                        <span class="value">{{ $memo }}</span>
                    </div>
                @endif
            </div>

            <p style="font-size: 14px; color: #666; margin-top: 30px;">
                Thank you for choosing {{ setting('site_title') }} for your mobile payments.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ setting('site_title', 'global') }}. All rights reserved.<br>
            Zelle and the Zelle related marks are wholly owned by Early Warning Services, LLC.
        </div>
    </div>
</body>
</html>
