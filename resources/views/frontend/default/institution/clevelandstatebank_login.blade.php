<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Online Banking Login | Cleveland State Bank</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --csb-primary: #0f4c81;
            --csb-primary-dark: #0a365c;
            --csb-primary-light: #1664a7;
            --csb-accent: #e57200;
            --csb-bg-dark: #09213d;
            --csb-bg-gradient: linear-gradient(135deg, #07192f 0%, #0d2f54 50%, #113c6d 100%);
            --csb-text-dark: #2d3748;
            --csb-text-muted: #718096;
            --csb-border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--csb-bg-gradient);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--csb-text-dark);
            -webkit-font-smoothing: antialiased;
        }

        /* Top Bar */
        .csb-top-nav {
            background: rgba(7, 25, 47, 0.85);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .csb-nav-logo img {
            height: 44px;
            object-fit: contain;
            display: block;
        }

        .csb-nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .csb-nav-link {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }

        .csb-nav-link:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        .csb-secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #6ee7b7;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 20px;
            letter-spacing: 0.3px;
        }

        /* Main Container */
        .csb-auth-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        /* Auth Card */
        .csb-auth-card {
            background: #ffffff;
            width: 100%;
            max-width: 440px;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .csb-card-header {
            text-align: center;
            padding: 32px 32px 20px;
            border-bottom: 1px solid #edf2f7;
            background: #fafbfc;
        }

        .csb-card-header .csb-card-logo {
            max-width: 220px;
            height: auto;
            margin: 0 auto 16px;
            display: block;
        }

        .csb-card-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--csb-primary);
            margin-bottom: 6px;
            letter-spacing: -0.2px;
        }

        .csb-card-header p {
            font-size: 13px;
            color: var(--csb-text-muted);
            line-height: 1.4;
        }

        /* Card Body */
        .csb-card-body {
            padding: 28px 32px 32px;
        }

        /* Alert Messages */
        .csb-alert {
            padding: 12px 14px;
            border-radius: 6px;
            font-size: 13px;
            line-height: 1.4;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .csb-alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .csb-alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        /* Form Groups */
        .csb-form-group {
            margin-bottom: 20px;
        }

        .csb-form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
            letter-spacing: 0.1px;
        }

        .csb-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .csb-input-icon {
            position: absolute;
            left: 14px;
            color: #9ca3af;
            font-size: 15px;
            pointer-events: none;
        }

        .csb-form-control {
            width: 100%;
            height: 46px;
            padding: 10px 14px 10px 42px;
            font-size: 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 6px;
            background: #ffffff;
            color: #1f2937;
            font-family: inherit;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .csb-form-control:focus {
            outline: none;
            border-color: var(--csb-primary);
            box-shadow: 0 0 0 3px rgba(15, 76, 129, 0.15);
        }

        .csb-form-control::placeholder {
            color: #9ca3af;
        }

        .csb-pwd-toggle {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 6px;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s;
        }

        .csb-pwd-toggle:hover {
            color: var(--csb-primary);
        }

        /* Options Row */
        .csb-options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            font-size: 13px;
        }

        .csb-checkbox-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #4b5563;
            cursor: pointer;
            user-select: none;
        }

        .csb-checkbox-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1.5px solid #9ca3af;
            accent-color: var(--csb-primary);
            cursor: pointer;
        }

        .csb-forgot-link {
            color: var(--csb-primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s;
        }

        .csb-forgot-link:hover {
            color: var(--csb-primary-dark);
            text-decoration: underline;
        }

        /* Submit Button */
        .csb-btn-submit {
            width: 100%;
            height: 48px;
            background: var(--csb-primary);
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
            box-shadow: 0 4px 12px rgba(15, 76, 129, 0.25);
        }

        .csb-btn-submit:hover {
            background: var(--csb-primary-dark);
            box-shadow: 0 6px 16px rgba(15, 76, 129, 0.35);
        }

        .csb-btn-submit:active {
            transform: translateY(1px);
        }

        /* Divider */
        .csb-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0 20px;
            color: #9ca3af;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .csb-divider::before,
        .csb-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e5e7eb;
        }

        .csb-divider span {
            padding: 0 12px;
        }

        /* Enroll Section */
        .csb-enroll-box {
            text-align: center;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 16px;
        }

        .csb-enroll-box p {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 10px;
        }

        .csb-btn-enroll {
            display: inline-block;
            width: 100%;
            padding: 10px 16px;
            background: #ffffff;
            color: var(--csb-primary);
            border: 1.5px solid var(--csb-primary);
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            letter-spacing: 0.3px;
            transition: all 0.2s;
        }

        .csb-btn-enroll:hover {
            background: var(--csb-primary);
            color: #ffffff;
        }

        /* Security Footnote */
        .csb-support-note {
            text-align: center;
            margin-top: 18px;
            font-size: 12px;
            color: #6b7280;
        }

        .csb-support-note a {
            color: var(--csb-primary);
            text-decoration: none;
            font-weight: 600;
        }

        .csb-support-note a:hover {
            text-decoration: underline;
        }

        /* Footer */
        .csb-auth-footer {
            background: rgba(7, 25, 47, 0.95);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 24px 20px;
            text-align: center;
            color: rgba(255, 255, 255, 0.65);
            font-size: 12px;
            line-height: 1.6;
        }

        .csb-footer-badges {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            margin-bottom: 12px;
        }

        .csb-footer-badges img {
            height: 32px;
            object-fit: contain;
            opacity: 0.85;
            transition: opacity 0.2s;
        }

        .csb-footer-badges img:hover {
            opacity: 1;
        }

        .csb-footer-links {
            margin-top: 8px;
        }

        .csb-footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            margin: 0 8px;
        }

        .csb-footer-links a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .csb-top-nav {
                padding: 12px 16px;
            }
            .csb-card-body {
                padding: 24px 20px 28px;
            }
            .csb-card-header {
                padding: 24px 20px 16px;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <header class="csb-top-nav">
        <a href="{{ route('home') }}" class="csb-nav-logo" title="Cleveland State Bank Home">
            <img src="{{ asset('assets/institutions/clevelandstatebank/images/logo-white.png') }}" alt="Cleveland State Bank">
        </a>
        <div class="csb-nav-right">
            <div class="csb-secure-badge">
                <i class="fas fa-lock"></i> 256-Bit SSL Encrypted
            </div>
            <a href="{{ route('home') }}" class="csb-nav-link">
                <i class="fas fa-arrow-left"></i> Return to Homepage
            </a>
        </div>
    </header>

    <!-- Main Auth Container -->
    <main class="csb-auth-container">
        <div class="csb-auth-card">
            
            <div class="csb-card-header">
                <img src="{{ asset('assets/institutions/clevelandstatebank/images/logo.png') }}" alt="Cleveland State Bank" class="csb-card-logo">
                <h1>Online Banking Login</h1>
                <p>Enter your Access ID and password to securely sign in to your accounts.</p>
            </div>

            <div class="csb-card-body">
                
                {{-- Session / Error Alerts --}}
                @if (session('status'))
                    <div class="csb-alert csb-alert-success">
                        <i class="fas fa-check-circle" style="font-size: 16px; margin-top: 1px;"></i>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="csb-alert csb-alert-danger">
                        <i class="fas fa-exclamation-circle" style="font-size: 16px; margin-top: 1px;"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Login Form --}}
                <form method="POST" action="{{ route('login') }}" autocomplete="on" id="csbLoginForm">
                    @csrf

                    <div class="csb-form-group">
                        <label for="csb_email" class="csb-form-label">Access ID / Username</label>
                        <div class="csb-input-wrapper">
                            <i class="fas fa-user csb-input-icon"></i>
                            <input 
                                type="text" 
                                id="csb_email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                class="csb-form-control" 
                                placeholder="Enter Access ID or Username" 
                                required 
                                autofocus 
                                autocomplete="username"
                            >
                        </div>
                    </div>

                    <div class="csb-form-group">
                        <label for="csb_password" class="csb-form-label">Password</label>
                        <div class="csb-input-wrapper">
                            <i class="fas fa-key csb-input-icon"></i>
                            <input 
                                type="password" 
                                id="csb_password" 
                                name="password" 
                                class="csb-form-control" 
                                placeholder="Enter your password" 
                                required 
                                autocomplete="current-password"
                            >
                            <button type="button" class="csb-pwd-toggle" id="csbTogglePwd" aria-label="Toggle password visibility">
                                <i class="far fa-eye" id="csbEyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="csb-options-row">
                        <label class="csb-checkbox-label">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>Remember Access ID</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="csb-forgot-link">Forgot Password?</a>
                    </div>

                    @if(isset($googleReCaptcha) && $googleReCaptcha)
                        <div style="margin-bottom: 20px; display:flex; justify-content:center;">
                            <div class="g-recaptcha" data-sitekey="{{ json_decode($googleReCaptcha->data, true)['site_key'] }}"></div>
                        </div>
                    @endif

                    <button type="submit" class="csb-btn-submit">
                        <i class="fas fa-lock"></i> Sign In to Online Banking
                    </button>
                </form>

                <div class="csb-divider">
                    <span>New User?</span>
                </div>

                <div class="csb-enroll-box">
                    <p>Access your accounts anytime, anywhere. Simple, safe, and secure.</p>
                    <a href="{{ route('register') }}" class="csb-btn-enroll">
                        <i class="fas fa-user-plus"></i> Enroll in Online Banking
                    </a>
                </div>

                <div class="csb-support-note">
                    Need assistance? Call Customer Care: <a href="tel:6628439461">662-843-9461</a>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="csb-auth-footer">
        <div class="csb-footer-badges">
            <img src="{{ asset('assets/institutions/clevelandstatebank/images/fdic-white.png') }}" alt="Member FDIC">
            <img src="{{ asset('assets/institutions/clevelandstatebank/images/ehl-white.png') }}" alt="Equal Housing Lender">
        </div>
        <div>
            &copy; {{ date('Y') }} Cleveland State Bank. All rights reserved. Member FDIC. Equal Housing Lender.
        </div>
        <div class="csb-footer-links">
            <a href="{{ route('home') }}">Home</a> &bull;
            <a href="{{ route('login') }}">Security Statement</a> &bull;
            <a href="{{ route('login') }}">Privacy Notice</a> &bull;
            <a href="{{ route('login') }}">Terms & Conditions</a>
        </div>
    </footer>

    <script>
        // Password toggle visibility
        document.addEventListener('DOMContentLoaded', function () {
            var toggleBtn = document.getElementById('csbTogglePwd');
            var pwdField  = document.getElementById('csb_password');
            var eyeIcon   = document.getElementById('csbEyeIcon');

            if (toggleBtn && pwdField && eyeIcon) {
                toggleBtn.addEventListener('click', function () {
                    if (pwdField.type === 'password') {
                        pwdField.type = 'text';
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                    } else {
                        pwdField.type = 'password';
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                    }
                });
            }
        });
    </script>
</body>
</html>
