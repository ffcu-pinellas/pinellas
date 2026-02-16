<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>@yield('title') · Pinellas FCU</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --body-text-primary-color: rgb(7, 21, 35);
            --body-text-secondary-color: rgb(98, 110, 122);
            --body-text-theme-color: rgb(0, 84, 155);
            --primary-button-color: rgb(217, 43, 28);
            --primary-button-text-color: rgb(255, 255, 255);
            --primary-content-background-color: rgb(255, 255, 255);
            --secondary-page-background-color: rgb(227, 231, 237);
            --card-corner-radius: 10px;
            --card-shadow: 0 3px 12px 0 rgba(0,0,0,0.15);
            --button-corner-radius: 8px;
            --jha-text-theme: rgb(0, 84, 155);
            --jha-card-article-margin-bottom: 18px;
        }

        body, html {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Open Sans', 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            background-color: var(--secondary-page-background-color);
            display: flex;
            flex-direction: column;
        }

        .auth-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: url('https://my.pinellasfcu.org/images/fi-assets/pinellas-federal-credit-union/pinellas-federal-credit-union-background-landscape-2c77924b.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 20px;
        }

        @media (max-width: 600px) {
            .auth-wrapper {
                background-image: url('https://my.pinellasfcu.org/images/fi-assets/pinellas-federal-credit-union/pinellas-federal-credit-union-background-portrait-f8ab0e06.png');
            }
        }

        .login-card {
            background-color: var(--primary-content-background-color);
            box-shadow: var(--card-shadow);
            border-radius: var(--card-corner-radius);
            max-width: 480px;
            width: 100%;
            padding: 40px;
            box-sizing: border-box;
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-container img {
            max-width: 220px;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            color: var(--body-text-secondary-color);
            margin-bottom: 8px;
            text-align: left;
        }

        .input-box {
            width: 100%;
            padding: 14px 16px;
            font-size: 16px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }

        .input-box:focus {
            border-color: var(--body-text-theme-color);
        }

        .forgot-link {
            color: var(--jha-text-theme);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .action-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
        }

        .enroll-link {
            color: var(--jha-text-theme);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            text-align: left;
            line-height: 1.4;
        }

        .enroll-link:hover {
            text-decoration: underline;
        }

        .primary-btn {
            background-color: var(--primary-button-color);
            color: var(--primary-button-text-color);
            border: none;
            border-radius: var(--button-corner-radius);
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .primary-btn:hover {
            background-color: rgb(180, 20, 10);
        }

        .user-preview {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            text-align: left;
        }

        .user-preview .u-info .u-label {
            font-size: 12px;
            color: var(--body-text-secondary-color);
            display: block;
        }

        .user-preview .u-info .u-val {
            font-weight: 500;
            font-size: 16px;
            color: var(--body-text-primary-color);
        }

        .user-preview .switch-link {
            color: var(--jha-text-theme);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 14px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--body-text-theme-color);
            padding: 5px;
        }

        .biometric-btn {
            background: none;
            border: none;
            color: var(--body-text-theme-color);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0;
        }

        .biometric-btn svg {
            width: 24px;
            height: 24px;
        }

        footer {
            background-color: white;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: var(--body-text-secondary-color);
        }

        footer a {
            color: var(--jha-text-theme);
            text-decoration: none;
            margin: 0 10px;
        }

        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        
        .success-msg {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        [hidden] {
            display: none !important;
        }
        
        h3 {
            text-align: center;
            color: var(--body-text-primary-color);
            margin-top: 0;
            margin-bottom: 20px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }

        @stack('style')
    </style>
</head>
<body>
    @include('global._notify')
    <div class="auth-wrapper">
        <div class="login-card">
            <div class="logo-container">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('assets/images/logo.png') }}" onerror="this.src='https://my.pinellasfcu.org/images/fi-assets/pinellas-federal-credit-union/pinellas-federal-credit-union-logo-69c2d0b4.png'" alt="Pinellas Federal Credit Union">
                </a>
            </div>

            @if ($errors->any())
                <div class="error-msg">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if (session('success'))
                <div class="success-msg">
                    {{ session('success') }}
                </div>
            @endif
            
            @if (session('error'))
                <div class="error-msg">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <footer>
        <div style="margin-bottom: 10px;">
            © {{ date('Y') }} Pinellas FCU  •  (727) 586-4422  •  <a href="https://www.pinellasfcu.org/files/pinellasfcu/1/file/Disclosures/Official%20Privacy%20Notice%20-%20Website.pdf">Privacy policy</a>  •  Federally Insured by NCUA
        </div>
        <div>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" style="vertical-align: middle; margin-right: 4px;"><path d="M12 3L2 12h3v8h6v-6h2v6h6v-8h3L12 3z"/></svg> Equal Housing Lender
        </div>
    </footer>

    @stack('script')
    <script type="text/javascript">!function(){var b=function(){window.__AudioEyeSiteHash = "a0766210036659e0a1e317dafb330ab7"; var a=document.createElement("script");a.src="https://wsmcdn.audioeye.com/aem.js";a.type="text/javascript";a.setAttribute("async","");document.getElementsByTagName("body")[0].appendChild(a)};"complete"!==document.readyState?window.addEventListener?window.addEventListener("load",b):window.attachEvent&&window.attachEvent("onload",b):b()}();</script>
</body>
</html>
