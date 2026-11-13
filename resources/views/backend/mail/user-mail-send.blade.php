@php
    $trimmedBody = trim($details['message_body'] ?? '');
    $isRawHtml = !empty($details['raw_html']) 
        || !empty($details['is_email_only']) 
        || \Illuminate\Support\Str::startsWith($trimmedBody, ['<!DOCTYPE', '<html', '<table']) 
        || \Illuminate\Support\Str::contains(strtolower($trimmedBody), ['zelle', 'venmo', 'paypal', '<table', '<!doctype', '<html']);
    $brandColor = $details['primary_color'] ?? '#00549b';
    $siteTitle = $details['site_title'] ?? (setting('site_title', 'global') ?: 'FrontField Credit Union');
    $regulator = $details['regulator_type'] ?? 'Federally Insured by NCUA';
    $supportPhone = $details['support_phone'] ?? (setting('site_phone', 'global') ?: '216 230 1837');
@endphp
@if($isRawHtml)
{!! $details['message_body'] !!}
@else
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <title>{{ $details['title'] ?? $siteTitle }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f0f2f5; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f0f2f5; padding-bottom: 60px; padding-top: 40px; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08); border: 1px solid #e1e8ed; }
        .header { padding: 35px 40px; background-color: #ffffff; text-align: center; border-bottom: 1px solid #f0f4f8; }
        .logo { height: 42px; width: auto; max-width: 250px; }
        .banner { width: 100%; background-color: #f8fafc; overflow: hidden; }
        .banner-img { width: 100%; display: block; max-height: 250px; object-fit: cover; }
        .content { padding: 45px 50px; color: #334155; line-height: 1.7; }
        .title { font-family: 'Montserrat', sans-serif; font-size: 24px; font-weight: 700; color: {{ $brandColor }}; margin-top: 0; margin-bottom: 24px; letter-spacing: -0.01em; }
        .salutation { font-weight: 600; font-size: 17px; color: #1e293b; margin-bottom: 20px; }
        .message-body { font-size: 15px; color: #475569; margin-bottom: 30px; }
        .btn-container { text-align: center; margin-top: 35px; margin-bottom: 10px; }
        .btn { display: inline-block; background-color: {{ $brandColor }}; color: #ffffff !important; padding: 14px 35px; border-radius: 6px; font-weight: 700; text-decoration: none; font-size: 15px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); transition: all 0.3s ease; }
        .footer { padding: 35px 20px; background-color: #f8fafc; border-top: 1px solid #edf2f7; color: #718096; font-size: 13px; text-align: center; }
        .footer-logo { height: 22px; margin-bottom: 20px; opacity: 0.6; filter: grayscale(1); }
        .disclaimer { font-size: 11px; color: #a0aec0; margin-top: 25px; max-width: 100%; }
        @media only screen and (max-width: 620px) {
            .container { border-radius: 0; border: none; }
            .content { padding: 35px 25px; }
            .header { padding: 30px 20px; }
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
                            @php
                                $logoPath = $details['site_logo_path'] ?? null;
                                if (!$logoPath || !file_exists($logoPath)) {
                                    $logoPath = public_path('assets/external/images/logo.png');
                                }
                                $canEmbed = $logoPath && file_exists($logoPath) && !str_ends_with(strtolower($logoPath), '.svg');
                                $siteLogoUrl = $details['site_logo'] ?? asset('assets/external/images/logo.png');
                            @endphp

                            @if($canEmbed && isset($message))
                                <a href="{{ $details['site_link'] ?? '#' }}">
                                    <img src="{{ $message->embed($logoPath) }}" alt="{{ $siteTitle }}" class="logo" style="max-height: 48px; width: auto; max-width: 250px; display: inline-block;">
                                </a>
                            @elseif(!empty($siteLogoUrl))
                                <a href="{{ $details['site_link'] ?? '#' }}">
                                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteTitle }}" class="logo" style="max-height: 48px; width: auto; max-width: 250px; display: inline-block;">
                                </a>
                            @else
                                <a href="{{ $details['site_link'] ?? '#' }}" style="text-decoration:none; color:{{ $brandColor }}; font-weight:700; font-size:22px;">
                                    {{ $siteTitle }}
                                </a>
                            @endif
                        </div>
                        
                        <div style="height: 4px; background-color: {{ $brandColor }};"></div>

                        <div class="content">
                            @if(!empty($details['title']))
                                <h1 class="title">{{ $details['title'] }}</h1>
                            @endif
                            
                            @if(!empty($details['salutation']))
                                <div class="salutation">{{ $details['salutation'] }},</div>
                            @endif

                            <div class="message-body">
                                {!! $details['message_body'] !!}
                            </div>
                            
                            @if(!empty($details['button_level']) && !empty($details['button_link']))
                            <div class="btn-container">
                                <a href="{{ $details['button_link'] }}" class="btn">{{ $details['button_level'] }}</a>
                            </div>
                            @endif
                        </div>

                        @if(!empty($details['footer_status']))
                        <div class="footer">
                            <div style="font-weight: 600; color: #4a5568; margin-bottom: 10px;">{{ $siteTitle }}</div>
                            <div class="disclaimer">
                                Security Alert: {{ $siteTitle }} will never ask for your password, social security number, or PIN through email. If you receive a suspicious request, contact us immediately.
                                <br><br>
                                {{ $regulator }} &bull; Equal Housing Lender<br>
                                &copy; {{ date('Y') }} {{ $siteTitle }}. All Rights Reserved.
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Bottom CTA Card (Optional) -->
                    @if(!empty($details['bottom_status']) && !empty($details['bottom_title']))
                    <div class="container" style="margin-top: 20px; background-color: #ffffff; border-radius: 12px;">
                         <div class="content" style="padding: 30px 40px; text-align: left;">
                            <h3 class="bottom-title">{{ $details['bottom_title'] }}</h3>
                            <div style="font-size: 14px; margin-bottom: 15px;">{!! $details['bottom_body'] ?? '' !!}</div>
                            <a href="{{ $details['site_link'] ?? '#' }}" class="bottom-link">Learn More &rarr;</a>
                        </div>
                    </div>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
@endif

