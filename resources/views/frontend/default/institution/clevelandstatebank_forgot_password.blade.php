<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Forgot Access ID or Password | Cleveland State Bank</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

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
            max-width: 440px;
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
            line-height: 1.4;
        }

        .csb-card-body { padding: 26px 32px 32px; }

        .csb-alert {
            padding: 12px 14px;
            border-radius: 6px;
            font-size: 13px;
            line-height: 1.4;
            margin-bottom: 20px;
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

        .csb-form-group { margin-bottom: 20px; }

        .csb-form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .csb-form-control {
            width: 100%;
            height: 46px;
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
        }

        .csb-btn-submit:hover { background: var(--csb-primary-dark); }

        .csb-links-row {
            margin-top: 20px;
            text-align: center;
        }

        .csb-links-row a {
            color: var(--csb-primary);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .csb-links-row a:hover { text-decoration: underline; }

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
    </style>
</head>
<body>
    <header class="csb-top-nav">
        <a href="{{ route('home') }}" class="csb-nav-logo">
            <img src="{{ asset('assets/institutions/clevelandstatebank/images/logo-white.png') }}" alt="Cleveland State Bank">
        </a>
        <div class="csb-nav-right">
            <a href="{{ route('login') }}" class="csb-nav-link"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </header>

    <main class="csb-auth-container">
        <div class="csb-auth-card">
            <div class="csb-card-header">
                <img src="{{ asset('assets/institutions/clevelandstatebank/images/logo.png') }}" alt="Cleveland State Bank" class="csb-card-logo">
                <h1>Password Reset</h1>
                <p>Enter your Access ID or registered email address to receive password reset instructions.</p>
            </div>

            <div class="csb-card-body">
                @if (session('status'))
                    <div class="csb-alert csb-alert-success">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="csb-alert csb-alert-danger">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="csb-form-group">
                        <label class="csb-form-label">Access ID or Email Address *</label>
                        <input type="text" name="email" value="{{ old('email') }}" class="csb-form-control" placeholder="Enter Access ID or Email" required autofocus>
                    </div>

                    <button type="submit" class="csb-btn-submit">
                        <i class="fas fa-paper-plane"></i> Send Reset Link
                    </button>

                    <div class="csb-links-row">
                        <a href="{{ route('login') }}">Remember your password? Sign In</a>
                    </div>
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
</body>
</html>
