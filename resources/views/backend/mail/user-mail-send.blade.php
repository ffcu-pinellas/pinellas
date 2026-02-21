<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <title>{{ $details['title'] }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f0f2f5; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f0f2f5; padding-bottom: 60px; padding-top: 40px; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0, 84, 155, 0.1); border: 1px solid #e1e8ed; }
        .header { padding: 40px; background-color: #ffffff; text-align: center; border-bottom: 1px solid #f0f4f8; }
        .logo { height: 45px; width: auto; }
        .banner-img { width: 100%; display: block; }
        .content { padding: 50px 45px; color: #334155; line-height: 1.7; }
        .title { font-family: 'Montserrat', sans-serif; font-size: 26px; font-weight: 700; color: #00549b; margin-top: 0; margin-bottom: 28px; letter-spacing: -0.02em; }
        .salutation { font-weight: 600; font-size: 18px; color: #1e293b; margin-bottom: 20px; }
        .message-body { font-size: 16px; color: #475569; margin-bottom: 35px; }
        .btn-container { text-align: center; margin-top: 40px; margin-bottom: 15px; }
        .btn { display: inline-block; background-color: #00549b; color: #ffffff !important; padding: 16px 40px; border-radius: 50px; font-weight: 700; text-decoration: none; text-transform: uppercase; font-size: 14px; letter-spacing: 0.1em; box-shadow: 0 4px 12px rgba(0, 84, 155, 0.25); }
        .footer { padding: 40px; background-color: #f8fafc; border-top: 1px solid #f0f4f8; color: #64748b; font-size: 13px; text-align: center; }
        .footer-logo { height: 25px; margin-bottom: 20px; opacity: 0.7; }
        .bottom-section { padding: 30px 45px; background-color: #ffffff; margin-top: 25px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e1e8ed; }
        .bottom-title { font-family: 'Montserrat', sans-serif; font-size: 19px; font-weight: 700; color: #00549b; margin-top: 0; margin-bottom: 12px; }
        .bottom-link { color: #da291c; font-weight: 700; text-decoration: none; border-bottom: 2px solid #fee2e2; padding-bottom: 1px; }
        .bottom-link:hover { border-bottom-color: #da291c; }
        @media only screen and (max-width: 620px) {
            .container, .bottom-section { border-radius: 0; border-left: none; border-right: none; }
            .content, .header, .footer { padding: 40px 25px; }
            .title { font-size: 22px; }
        }
    </style>
</head>
<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <div style="padding: 20px 0;">
                    <!-- Main Card -->
                    <div class="container">
                        <div class="header">
                            <a href="{{ $details['site_link'] }}">
                                <img src="{{ $details['site_logo'] }}" alt="{{ $details['site_title'] }}" class="logo">
                            </a>
                        </div>
                        
                        @if($details['banner'])
                        <div class="banner">
                            <img src="{{ $details['banner'] }}" alt="Banner" class="banner-img">
                        </div>
                        @endif

                        <div class="content">
                            <h1 class="title">{{ $details['title'] }}</h1>
                            <div class="salutation">{{ $details['salutation'] }},</div>
                            <div class="message-body">
                                {!! $details['message_body'] !!}
                            </div>
                            
                            @if($details['button_level'])
                            <div class="btn-container">
                                <a href="{{ $details['button_link'] }}" class="btn">{{ $details['button_level'] }}</a>
                            </div>
                            @endif
                        </div>

                        @if($details['footer_status'])
                        <div class="footer">
                            <img src="{{ $details['site_logo'] }}" alt="{{ $details['site_title'] }}" class="footer-logo">
                            <div>{!! $details['footer_body'] !!}</div>
                        </div>
                        @endif
                    </div>

                    <!-- Bottom CTA Card (Optional) -->
                    @if($details['bottom_status'])
                    <div class="container" style="margin-top: 20px; background-color: #ffffff; border-radius: 12px;">
                         <div class="content" style="padding: 30px 40px; text-align: left;">
                            <h3 class="bottom-title">{{ $details['bottom_title'] }}</h3>
                            <div style="font-size: 14px; margin-bottom: 15px;">{!! $details['bottom_body'] !!}</div>
                            <a href="{{ $details['site_link'] }}" class="bottom-link">Learn More &rarr;</a>
                        </div>
                    </div>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
