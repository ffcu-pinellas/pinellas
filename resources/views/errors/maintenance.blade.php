@php
    try {
        $siteTitle = setting('site_title', 'global') ?: 'FrontField Credit Union';
        $mTitle = setting('maintenance_title', 'site_maintenance') ?: 'Scheduled System Upgrade';
        $mText = setting('maintenance_text', 'site_maintenance') ?: 'We are performing scheduled core banking infrastructure enhancements. Your accounts and funds remain secure and federally insured.';
        $mEta = setting('maintenance_eta', 'site_maintenance', '');
    } catch (\Throwable $e) {
        $siteTitle = 'FrontField Credit Union';
        $mTitle = 'Scheduled System Upgrade';
        $mText = 'We are performing scheduled core banking infrastructure enhancements. Your accounts and funds remain secure and federally insured.';
        $mEta = '';
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $mTitle }} — {{ $siteTitle }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: radial-gradient(circle at top left, #0a2540 0%, #051324 55%, #020813 100%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .maint-card {
            max-width: 580px;
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(14px);
            border-radius: 20px;
            padding: 40px 34px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.55);
        }
        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(14, 165, 233, 0.16);
            border: 1px solid rgba(56, 189, 248, 0.35);
            color: #7dd3fc;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 22px;
        }
        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #38bdf8;
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.25);
        }
        .icon-wrap {
            width: 72px;
            height: 72px;
            margin: 0 auto 20px;
            border-radius: 18px;
            background: linear-gradient(135deg, #00549b, #0ea5e9);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 24px rgba(0, 84, 155, 0.45);
        }
        .icon-wrap svg { width: 34px; height: 34px; stroke: #ffffff; fill: none; stroke-width: 2; }
        h1 {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 12px;
            color: #ffffff;
        }
        .desc {
            font-size: 15px;
            line-height: 1.65;
            color: #cbd5e1;
            margin-bottom: 24px;
        }
        .eta-box {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 26px;
        }
        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-refresh {
            appearance: none;
            border: none;
            cursor: pointer;
            background: #0ea5e9;
            color: #ffffff;
            font-weight: 600;
            font-size: 14px;
            padding: 11px 22px;
            border-radius: 10px;
            text-decoration: none;
            transition: background 0.15s ease;
        }
        .btn-refresh:hover { background: #0284c7; }
        .footer-note {
            margin-top: 28px;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="maint-card">
        <div class="brand-pill">
            <span class="pulse-dot"></span>
            {{ $siteTitle }} — System Notice
        </div>

        <div class="icon-wrap" aria-hidden="true">
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="m9 12 2 2 4-4"/>
            </svg>
        </div>

        <h1>{{ $mTitle }}</h1>
        <p class="desc">{{ $mText }}</p>

        @if(!empty($mEta))
            <div class="eta-box">
                <span>⏱️ Estimated Completion: <strong>{{ $mEta }}</strong></span>
            </div>
        @endif

        <div class="actions">
            <button type="button" class="btn-refresh" onclick="window.location.reload()">
                Check Availability
            </button>
        </div>

        <div class="footer-note">
            All member balances, scheduled transfers, and security protections remain active and federally insured by NCUA.
        </div>
    </div>
</body>
</html>
