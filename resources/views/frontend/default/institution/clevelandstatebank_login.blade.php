<!DOCTYPE html>
<!--- Version: /27.8.0 --->
<!--- ng8Router: True --->
<!--- shortVersion: 27.8.0 --->
<!--[if lt IE 10]><html class="lt-ie9"><![endif]-->
<html lang="en-us">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="format-detection" content="telephone=no">
<meta name="google-play-app" content="app-id=com.csiweb.digitalbanking">
<meta name="msApplication-ID" content="microsoft.build.App" />
<meta name="msApplication-PackageFamilyName" content="microsoft.build_appid" />

<link rel="apple-touch-icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}">
<link rel="android-touch-icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}" />
<link rel="windows-touch-icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}" />
<link rel="shortcut icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}" type="image/x-icon">
<link rel="icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}" type="image/x-icon">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/primeicons@6.0.1/primeicons.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,300,0,0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">

<title>Cleveland State Bank</title>
<meta name="description" content="Cleveland State Bank Digital Banking">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
    color: #2d3748;
    background-color: #07192e;
    min-height: 100vh;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
}

.layout-view-wrapper-loading {
    padding-left: 0 !important;
}

.background-image {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: #09203a;
    background: radial-gradient(circle at 50% 25%, #103861 0%, #07192e 100%);
    z-index: 0;
}

#loginContainer {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 30px 16px;
}

#loginForm {
    width: 100%;
    max-width: 440px;
    margin: 0 auto;
}

.loginCard {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.4);
    padding: 36px 36px 32px;
}

.bank-logo-wrapper {
    text-align: center;
    margin-bottom: 26px;
}

.bank-logo {
    background-image: url("{{ asset('assets/institutions/clevelandstatebank/images/logo.png') }}");
    background-repeat: no-repeat;
    background-position: center center;
    background-size: contain;
    height: 60px;
    width: 100%;
    max-width: 250px;
    margin: 0 auto;
    display: block;
}

.utility-textCenter {
    text-align: center;
}

/* CSI Alerts */
.csi-alert {
    padding: 11px 14px;
    border-radius: 4px;
    font-size: 13px;
    line-height: 1.4;
    margin-bottom: 18px;
}

.csi-alert-danger {
    background-color: #fdf2f2;
    border: 1px solid #f8b4b4;
    color: #9b1c1c;
}

.csi-alert-success {
    background-color: #f3faf7;
    border: 1px solid #84e1bc;
    color: #0e9f6e;
}

/* Form Controls */
.form-group {
    margin-bottom: 18px;
}

.control-label {
    display: block;
    font-family: 'Roboto', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: #4a5568;
    margin-bottom: 6px;
}

.form-control {
    width: 100%;
    height: 44px;
    padding: 10px 14px;
    font-family: 'Roboto', sans-serif;
    font-size: 14px;
    color: #1a202c;
    background: #ffffff;
    border: 1px solid #cbd5e0;
    border-radius: 4px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    outline: none;
    border-color: #0f4c81;
    box-shadow: 0 0 0 3px rgba(15, 76, 129, 0.15);
}

.password-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.password-toggle-btn {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    cursor: pointer;
    color: #718096;
    padding: 4px;
    display: flex;
    align-items: center;
    font-size: 16px;
}

.password-toggle-btn:hover {
    color: #0f4c81;
}

/* Options */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
    font-size: 13px;
}

.checkbox-container {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #4a5568;
    cursor: pointer;
    user-select: none;
}

.checkbox-container input[type="checkbox"] {
    width: 16px;
    height: 16px;
    border-radius: 3px;
    border: 1px solid #cbd5e0;
    accent-color: #0f4c81;
    cursor: pointer;
}

.forgot-link {
    color: #0f4c81;
    text-decoration: none;
    font-weight: 500;
    font-size: 13px;
}

.forgot-link:hover {
    text-decoration: underline;
}

/* Action Buttons */
.btn-primary {
    width: 100%;
    height: 46px;
    background-color: #0f4c81;
    border: 1px solid #0f4c81;
    border-radius: 4px;
    color: #ffffff;
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: background-color 0.2s, border-color 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-primary:hover {
    background-color: #0a365c;
    border-color: #0a365c;
}

/* Enroll Section */
.enroll-section {
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
    text-align: center;
}

.enroll-section p {
    font-size: 13px;
    color: #718096;
    margin-bottom: 10px;
}

.btn-enroll {
    display: block;
    width: 100%;
    height: 42px;
    line-height: 40px;
    background: #ffffff;
    border: 1px solid #0f4c81;
    border-radius: 4px;
    color: #0f4c81;
    font-family: 'Montserrat', sans-serif;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    transition: background-color 0.2s, color 0.2s;
}

.btn-enroll:hover {
    background-color: #0f4c81;
    color: #ffffff;
}

/* Footer */
.login-footer {
    margin-top: 24px;
    text-align: center;
    color: rgba(255, 255, 255, 0.7);
    font-size: 12px;
    line-height: 1.6;
}

.login-footer-badges {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-bottom: 10px;
}

.login-footer-badges img {
    height: 28px;
    object-fit: contain;
}

.login-footer a {
    color: rgba(255, 255, 255, 0.85);
    text-decoration: none;
}

.login-footer a:hover {
    text-decoration: underline;
}

@media (max-width: 480px) {
    .loginCard {
        padding: 28px 20px 24px;
    }
}
</style>
</head>
<body ontouchstart="">
<div id="csi-angularjs-view" class="view">
    <div class="layout-view-wrapper layout-view-wrapper-loading">
        <div class="layout-view">
            <div class="background-image"></div>
            <div id="loginContainer">
                <div id="loginForm">
                    <div class="loginFormInner layout-delay">
                        <div class="loginCard layout-delay ng-isolate-scope">
                            <!-- BEGIN LOGIN BODY -->
                            <section class="loginBody">
                                <div class="bank-logo-wrapper">
                                    <div class="utility-textCenter bank-logo"></div>
                                </div>

                                {{-- Session status & Validation Errors --}}
                                @if (session('status'))
                                    <div class="csi-alert csi-alert-success">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="csi-alert csi-alert-danger">
                                        @foreach ($errors->all() as $error)
                                            <div>{{ $error }}</div>
                                        @endforeach
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('login') }}" autocomplete="on" id="csiLoginForm">
                                    @csrf

                                    <div class="form-group">
                                        <label for="accessId" class="control-label">Access ID</label>
                                        <input 
                                            type="text" 
                                            id="accessId" 
                                            name="email" 
                                            value="{{ old('email') }}" 
                                            class="form-control" 
                                            placeholder="Access ID" 
                                            required 
                                            autofocus 
                                            autocomplete="username"
                                        >
                                    </div>

                                    <div class="form-group">
                                        <label for="password" class="control-label">Password</label>
                                        <div class="password-wrapper">
                                            <input 
                                                type="password" 
                                                id="password" 
                                                name="password" 
                                                class="form-control" 
                                                placeholder="Password" 
                                                required 
                                                autocomplete="current-password"
                                            >
                                            <button type="button" class="password-toggle-btn" id="togglePasswordBtn" aria-label="Toggle password visibility">
                                                <i class="pi pi-eye" id="pwdEyeIcon"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-options">
                                        <label class="checkbox-container">
                                            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                                            <span>Remember Access ID</span>
                                        </label>
                                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot Password?</a>
                                    </div>

                                    @if(isset($googleReCaptcha) && $googleReCaptcha)
                                        <div style="margin-bottom: 18px; display:flex; justify-content:center;">
                                            <div class="g-recaptcha" data-sitekey="{{ json_decode($googleReCaptcha->data, true)['site_key'] }}"></div>
                                        </div>
                                    @endif

                                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                                </form>

                                <div class="enroll-section">
                                    <p>New to Digital Banking?</p>
                                    <a href="{{ route('register') }}" class="btn-enroll">Enroll Now</a>
                                </div>
                            </section>
                            <!-- /END LOGIN BODY -->
                        </div>
                    </div>
                </div>

                <footer class="login-footer">
                    <div class="login-footer-badges">
                        <img src="{{ asset('assets/institutions/clevelandstatebank/images/fdic-white.png') }}" alt="Member FDIC">
                        <img src="{{ asset('assets/institutions/clevelandstatebank/images/ehl-white.png') }}" alt="Equal Housing Lender">
                    </div>
                    <div>&copy; {{ date('Y') }} Cleveland State Bank. All Rights Reserved. Member FDIC. Equal Housing Lender.</div>
                    <div style="margin-top: 6px;">
                        <a href="{{ route('home') }}">Return to Homepage</a>
                    </div>
                </footer>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggleBtn = document.getElementById('togglePasswordBtn');
    var pwdInput  = document.getElementById('password');
    var eyeIcon   = document.getElementById('pwdEyeIcon');

    if (toggleBtn && pwdInput && eyeIcon) {
        toggleBtn.addEventListener('click', function () {
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                eyeIcon.className = 'pi pi-eye-slash';
            } else {
                pwdInput.type = 'password';
                eyeIcon.className = 'pi pi-eye';
            }
        });
    }
});
</script>
</body>
</html>
