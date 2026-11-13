<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<title>Email Verification | Cleveland State Bank</title>

<link rel="apple-touch-icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}">
<link rel="icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}" type="image/x-icon">

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    min-height: 100vh;
    background-color: #edf0f4;
    background-image: url("{{ asset('assets/institutions/clevelandstatebank/images/csb-bg-ribbons.svg') }}");
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
}

/* ── Top FDIC Notice ── */
.csb-fdic-topbar {
    background-color: #ffffff;
    padding: 6px 20px;
    font-size: 11px;
    color: #1a2d54;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid #edf0f4;
    width: 100%;
}

.csb-fdic-badge {
    font-weight: 900;
    font-size: 12.5px;
    color: #0b2246;
    letter-spacing: 0.5px;
}

.card-center-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px 16px;
}

.csb-modal-card {
    width: 100%;
    max-width: 580px;
    background: #ffffff;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
    border-radius: 2px;
    overflow: hidden;
}

/* Red Header Bar */
.modal-header-red {
    background-color: #c8232c;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 16px;
    position: relative;
}

.modal-header-title {
    color: #ffffff;
    font-size: 13.5px;
    font-weight: bold;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    font-family: Arial, Helvetica, sans-serif;
}

/* Modal Body */
.modal-body {
    padding: 36px 36px 40px;
    text-align: center;
}

.csb-welcome-logo {
    width: 160px;
    height: auto;
    max-height: 190px;
    object-fit: contain;
    margin: 0 auto 16px;
    display: block;
}

.modal-divider {
    height: 1px;
    background-color: #e2e8f0;
    margin: 20px 0;
    border: none;
}

.welcome-title {
    font-size: 17px;
    font-weight: bold;
    color: #222222;
    margin-bottom: 12px;
    font-family: Arial, Helvetica, sans-serif;
}

.welcome-desc {
    font-size: 14px;
    color: #4a5568;
    line-height: 1.6;
    max-width: 480px;
    margin: 0 auto 24px;
    font-family: Arial, Helvetica, sans-serif;
}

.welcome-desc strong {
    color: #1a202c;
}

.btn-started {
    background-color: #c8232c;
    color: #ffffff;
    border: none;
    border-radius: 2px;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 0.8px;
    padding: 13px 32px;
    cursor: pointer;
    text-transform: uppercase;
    transition: background-color 0.2s;
    display: inline-block;
    text-decoration: none;
    width: 100%;
}

.btn-started:hover {
    background-color: #a81c24;
}

.back-to-signin {
    margin-top: 24px;
    display: block;
    color: #1b325f;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
}

.back-to-signin:hover {
    text-decoration: underline;
}

.alert-success {
    background-color: #e8f5e9;
    border: 1px solid #c8e6c9;
    color: #2e7d32;
    font-size: 13px;
    padding: 12px;
    border-radius: 2px;
    margin-bottom: 20px;
    text-align: left;
}

/* Footer */
.csb-footer {
    text-align: center;
    padding: 20px;
    font-size: 11.5px;
    color: #64748b;
    background: transparent;
}

.csb-footer a {
    color: #475569;
    text-decoration: none;
}

.csb-footer a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>

    <!-- Top FDIC Notice -->
    <div class="csb-fdic-topbar">
        <span class="csb-fdic-badge">FDIC</span>
        <span>FDIC-Insured — Backed by the full faith and credit of the U.S. Government</span>
    </div>

    <!-- Center Card -->
    <div class="card-center-wrapper">
        <div class="csb-modal-card">
            <!-- Red Header -->
            <div class="modal-header-red">
                <span class="modal-header-title">EMAIL VERIFICATION</span>
            </div>

            <div class="modal-body">
                <img src="{{ asset('assets/institutions/clevelandstatebank/images/csb-logo-stacked.png') }}" alt="Cleveland State Bank / Grenada State Bank" class="csb-welcome-logo">

                <hr class="modal-divider">

                <div class="welcome-title">Confirm Your Email Address</div>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert-success">
                        A fresh verification link has been dispatched to your email address. Please check your inbox and spam folder.
                    </div>
                @endif

                <p class="welcome-desc">
                    We have sent a secure verification link to <strong>{{ auth()->user()->email ?? 'your registered email' }}</strong>. Please check your inbox and click the link to activate your digital banking profile.
                </p>

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn-started">
                        RESEND VERIFICATION EMAIL
                    </button>
                </form>

                <div style="margin-top: 18px;">
                    <a href="{{ route('login') }}" class="back-to-signin">
                        Already verified? Sign in here &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Clean Institution Footer -->
    <footer class="csb-footer">
        <div>
            &copy; {{ date('Y') }} Cleveland State Bank &bull; 662-843-9461 &bull; <a href="#">Privacy Policy</a> &bull; Member FDIC &bull; Equal Housing Lender
        </div>
    </footer>

</body>
</html>
