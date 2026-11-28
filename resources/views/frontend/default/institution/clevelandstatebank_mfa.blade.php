<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Authenticate | Cleveland State Bank</title>

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

/* Center Container */
.csb-center-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
}

.csb-stacked-logo {
    width: 210px;
    height: auto;
    max-height: 250px;
    object-fit: contain;
    margin-bottom: 20px;
    display: block;
}

/* Floating Authenticate Modal */
.csb-auth-modal {
    width: 100%;
    max-width: 540px;
    background: #ffffff;
    box-shadow: 0 12px 42px rgba(0, 0, 0, 0.22);
    border-radius: 3px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* Modal Red Header */
.csb-modal-header {
    background-color: #c8232c;
    height: 46px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    position: relative;
}

.csb-modal-title {
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    margin: 0 auto;
}

.csb-modal-close {
    color: #ffffff;
    font-size: 17px;
    text-decoration: none;
    line-height: 1;
    opacity: 0.9;
    transition: opacity 0.2s;
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
}

.csb-modal-close:hover {
    opacity: 1;
}

/* Modal Body */
.csb-modal-body {
    padding: 28px 32px 34px;
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

/* Green Shield Icon */
.csb-shield-wrap {
    text-align: center;
    margin-bottom: 14px;
}

.csb-shield-icon {
    width: 52px;
    height: 52px;
    display: inline-block;
}

/* Notice text */
.csb-security-notice {
    font-size: 13.5px;
    color: #3e4857;
    text-align: center;
    line-height: 1.45;
    margin-bottom: 22px;
    padding: 0 10px;
}

/* Section Headings */
.csb-section-label {
    font-size: 11px;
    font-weight: 700;
    color: #4a5568;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 8px;
}

/* Device Box */
.csb-device-box {
    background-color: #eef2f6;
    border: 1px solid #d8e0e8;
    border-radius: 2px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.csb-device-info {
    display: flex;
    flex-direction: column;
}

.csb-device-tag {
    font-size: 10px;
    font-weight: 700;
    color: #718096;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 2px;
}

.csb-device-name {
    font-size: 15px;
    font-weight: 600;
    color: #1a202c;
}

.csb-device-arrow {
    color: #718096;
    font-size: 16px;
    font-weight: bold;
}

/* Remember Device Row */
.csb-remember-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 22px;
}

.csb-remember-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 13.5px;
    color: #2d3748;
    user-select: none;
}

.csb-remember-label input[type="checkbox"] {
    width: 17px;
    height: 17px;
    accent-color: #1a2d54;
    cursor: pointer;
}

/* Confirm Via Description */
.csb-confirm-desc {
    font-size: 12.5px;
    color: #4a5568;
    line-height: 1.45;
    margin-bottom: 14px;
}

/* Methods Grid (3 Side-by-Side Cards) */
.csb-methods-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 18px;
}

.csb-method-card {
    border-radius: 2px;
    padding: 14px 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
    min-height: 82px;
}

/* Active State (Matches green card from screenshot) */
.csb-method-card.active {
    background-color: #5cb85c;
    border: 1px solid #4cae4c;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(92, 184, 92, 0.35);
}

.csb-method-card.active .csb-method-icon svg {
    stroke: #ffffff;
    fill: none;
}

.csb-method-card.active .csb-method-text {
    color: #ffffff;
    font-weight: 600;
}

/* Inactive State (Matches white/grey card from screenshot) */
.csb-method-card:not(.active) {
    background-color: #ffffff;
    border: 1px solid #c9d2db;
    color: #1a2d54;
}

.csb-method-card:not(.active):hover {
    border-color: #9cb1c4;
    background-color: #f8fafc;
}

.csb-method-card:not(.active) .csb-method-icon svg {
    stroke: #1a2d54;
    fill: none;
}

.csb-method-card:not(.active) .csb-method-text {
    color: #1a2d54;
    font-weight: 500;
}

.csb-method-icon {
    width: 28px;
    height: 28px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.csb-method-icon svg {
    width: 24px;
    height: 24px;
}

.csb-method-text {
    font-size: 11.5px;
    line-height: 1.25;
}

/* Instruction Text below methods */
.csb-instruction-text {
    font-size: 12.5px;
    color: #4a5568;
    line-height: 1.45;
    margin-bottom: 16px;
}

/* Passcode Helper Lines */
.csb-passcode-helper {
    font-size: 12px;
    color: #556274;
    line-height: 1.4;
    margin-bottom: 10px;
}

/* Passcode Input Field */
.csb-input-container {
    position: relative;
    margin-bottom: 14px;
}

.csb-passcode-input {
    width: 100%;
    height: 48px;
    background-color: #ffffff;
    border: 1px solid #b8bec5;
    border-radius: 2px;
    padding: 0 14px;
    font-size: 15px;
    font-family: inherit;
    color: #1a202c;
    letter-spacing: 1.5px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.csb-passcode-input:focus {
    border-color: #c8232c;
    box-shadow: 0 0 0 2px rgba(200, 35, 44, 0.15);
}

.csb-passcode-input::placeholder {
    color: #8894a4;
    letter-spacing: 0.8px;
    font-size: 13.5px;
    font-weight: 500;
}

/* Resend Row */
.csb-resend-row {
    font-size: 12.5px;
    color: #4a5568;
    margin-top: -6px;
    margin-bottom: 14px;
}

.csb-resend-link {
    color: #1a2d54;
    font-weight: 600;
    text-decoration: underline;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    font-size: inherit;
}

.csb-resend-link:hover {
    color: #c8232c;
}

/* Biometric Trigger Card */
.csb-bio-box {
    background-color: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 4px;
    padding: 20px 16px;
    text-align: center;
    margin-bottom: 16px;
}

.csb-bio-icon-pulse {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background-color: rgba(92, 184, 92, 0.12);
    border: 2px solid rgba(92, 184, 92, 0.35);
    margin: 0 auto 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: csbPulse 2s infinite ease-in-out;
}

@keyframes csbPulse {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(92, 184, 92, 0.3); }
    70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(92, 184, 92, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(92, 184, 92, 0); }
}

.csb-bio-btn-trigger {
    background-color: #5cb85c;
    color: #ffffff;
    border: none;
    border-radius: 2px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
}

.csb-bio-btn-trigger:hover {
    background-color: #4cae4c;
}

.csb-bio-status {
    font-size: 12px;
    color: #64748b;
    margin-top: 8px;
}

/* Error Banner */
.csb-error-box {
    background-color: #fff1f2;
    border: 1px solid #fecdd3;
    color: #b91c1c;
    padding: 10px 14px;
    border-radius: 2px;
    font-size: 12.5px;
    font-weight: 500;
    margin-bottom: 14px;
    display: none;
}

/* Submit Button */
.csb-submit-btn {
    width: 100%;
    height: 46px;
    background-color: #e2e8f0;
    color: #4a5568;
    border: none;
    border-radius: 2px;
    font-size: 13.5px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.csb-submit-btn.ready,
.csb-submit-btn:hover:not(:disabled) {
    background-color: #c8232c;
    color: #ffffff;
}

.csb-submit-btn:disabled {
    cursor: not-allowed;
    opacity: 0.65;
}

.csb-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: csbSpin 0.7s linear infinite;
    display: inline-block;
}

@keyframes csbSpin {
    to { transform: rotate(360deg); }
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

@media (max-width: 576px) {
    .csb-modal-body {
        padding: 20px 18px 26px;
    }
    .csb-methods-grid {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .csb-method-card {
        min-height: 54px;
        flex-direction: row;
        gap: 10px;
        padding: 10px 14px;
    }
    .csb-method-icon {
        margin-bottom: 0;
    }
}
</style>
</head>
<body>

    <!-- Top FDIC Header -->
    <header class="fdic-header">
        <span class="fdic-badge">FDIC</span>
        <span>FDIC-Insured &mdash; Backed by the full faith and credit of the U.S. Government</span>
    </header>

    <!-- Center Content -->
    <main class="csb-center-wrapper">
        <img src="{{ (app()->bound('institution') && app('institution')->logo) ? app('institution')->logo : asset('assets/institutions/clevelandstatebank/images/logo.png') }}" alt="Cleveland State Bank" class="csb-stacked-logo">

        <!-- Authentic Modal -->
        <div class="csb-auth-modal">
            <div class="csb-modal-header">
                <span class="csb-modal-title">AUTHENTICATE</span>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="csb-modal-close" title="Cancel & Sign Out">&times;</button>
                </form>
            </div>

            <div class="csb-modal-body">
                <!-- Green Shield with Key -->
                <div class="csb-shield-wrap">
                    <svg class="csb-shield-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L4 5V11C4 16.5 7.5 21.5 12 22.8C16.5 21.5 20 16.5 20 11V5L12 2Z" stroke="#5cb85c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="10.5" r="2.2" stroke="#5cb85c" stroke-width="1.8"/>
                        <path d="M12 12.7V17M12 15H13.8" stroke="#5cb85c" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>

                <p class="csb-security-notice">
                    For added security we have enabled Multi-Factor Authentication (MFA) for our users.
                </p>

                <!-- Section: SELECT A DEVICE -->
                <div class="csb-section-label">SELECT A DEVICE</div>
                <div class="csb-device-box">
                    <div class="csb-device-info">
                        <span class="csb-device-tag">DEVICE</span>
                        <span class="csb-device-name">{{ $user->first_name ?: ($user->username ?: 'Registered Device') }}</span>
                    </div>
                    <div class="csb-device-arrow">&rarr;</div>
                </div>

                <!-- Remember Device -->
                <div class="csb-remember-row">
                    <label class="csb-remember-label">
                        <input type="checkbox" id="trustDevice" name="trust_device" checked>
                        <span>Remember Device</span>
                    </label>
                </div>

                <!-- Section: CONFIRM VIA -->
                <div class="csb-section-label">CONFIRM VIA</div>
                <p class="csb-confirm-desc">
                    We need to confirm that you have access to the selected device by entering your security PIN, receiving a one-time passcode via email, or using biometrics.
                </p>

                <!-- 3 Action Cards (Exact Screenshot Aesthetic) -->
                <div class="csb-methods-grid">
                    <!-- Card 1: Security PIN -->
                    <div class="csb-method-card @if($method === 'pin') active @endif" id="cardPin" onclick="selectMethod('pin')">
                        <div class="csb-method-icon">
                            <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div class="csb-method-text">Security PIN</div>
                    </div>

                    <!-- Card 2: Passcode via Email (Matches screenshot phrasing) -->
                    <div class="csb-method-card @if($method === 'email') active @endif" id="cardEmail" onclick="selectMethod('email')">
                        <div class="csb-method-icon">
                            <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                                <path d="M12 18h.01"/>
                                <path d="M17 6h-6"/>
                            </svg>
                        </div>
                        <div class="csb-method-text">Passcode via Email</div>
                    </div>

                    <!-- Card 3: Biometrics -->
                    @if(!$isNewDevice)
                    <div class="csb-method-card @if($method === 'biometric') active @endif" id="cardBiometric" onclick="selectMethod('biometric')">
                        <div class="csb-method-icon">
                            <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 11c0 3.866-3.134 7-7 7a9.98 9.98 0 0 1-2-.2"/>
                                <path d="M12 11a7 7 0 0 1 7 7c0 .068 0 .135-.002.202"/>
                                <path d="M12 7c2.21 0 4 1.79 4 4a6.98 6.98 0 0 1-.36 2.2"/>
                                <path d="M8.36 13.2A6.98 6.98 0 0 1 8 11c0-2.21 1.79-4 4-4"/>
                                <path d="M12 3a9 9 0 0 1 9 9c0 1.25-.26 2.45-.73 3.53"/>
                            </svg>
                        </div>
                        <div class="csb-method-text">Biometrics</div>
                    </div>
                    @endif
                </div>

                <!-- Dynamic Instruction Line -->
                <p class="csb-instruction-text" id="methodInstruction">
                    @if($method === 'email')
                        Please enter the 6-digit one-time passcode sent to your registered email ({{ $maskedEmail }}).
                    @elseif($method === 'biometric')
                        Please authenticate using Touch ID, Face ID, or your device biometric sensor.
                    @else
                        Please enter the 4-digit security PIN associated with your account.
                    @endif
                </p>

                <!-- Passcode Input Section (for PIN & Email) -->
                <div id="passcodeSection" style="@if($method === 'biometric') display: none; @endif">
                    <div class="csb-section-label">ENTER PASSCODE</div>
                    <div class="csb-passcode-helper" id="passcodeHelper">
                        @if($method === 'email')
                            <span>Enter the 6-digit passcode sent to your email to authorize this device</span><br>
                            <span>Or select Security PIN or Biometrics above</span>
                        @else
                            <span>Enter your 4-digit security PIN to authorize this device</span><br>
                            <span>Or choose to receive a one-time passcode via email</span>
                        @endif
                    </div>

                    <div class="csb-input-container">
                        <input 
                            type="password" 
                            id="passcodeInput" 
                            placeholder="PASSCODE" 
                            class="csb-passcode-input" 
                            autocomplete="one-time-code"
                            maxlength="8"
                            autofocus
                        >
                    </div>

                    <!-- Resend Email Link -->
                    <div id="emailResendContainer" class="csb-resend-row" style="@if($method !== 'email') display: none; @endif">
                        <span>Didn't receive code? </span>
                        <button type="button" class="csb-resend-link" id="btnResendEmail" onclick="resendEmailCode()">Resend Code</button>
                        <span id="resendTimerSpan" style="color: #718096; margin-left: 4px;"></span>
                    </div>
                </div>

                <!-- Biometric Section -->
                <div id="biometricSection" style="@if($method !== 'biometric') display: none; @endif" class="csb-bio-box">
                    <div class="csb-bio-icon-pulse">
                        <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="#5cb85c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 11c0 3.866-3.134 7-7 7a9.98 9.98 0 0 1-2-.2"/>
                            <path d="M12 11a7 7 0 0 1 7 7c0 .068 0 .135-.002.202"/>
                            <path d="M12 7c2.21 0 4 1.79 4 4a6.98 6.98 0 0 1-.36 2.2"/>
                            <path d="M8.36 13.2A6.98 6.98 0 0 1 8 11c0-2.21 1.79-4 4-4"/>
                            <path d="M12 3a9 9 0 0 1 9 9c0 1.25-.26 2.45-.73 3.53"/>
                        </svg>
                    </div>
                    <button type="button" class="csb-bio-btn-trigger" id="btnTriggerBio" onclick="executeBiometricAuth()">
                        Authenticate with Touch ID / Face ID
                    </button>
                    <div class="csb-bio-status" id="bioStatusText">Click above to verify Face ID / Touch ID</div>
                </div>

                <!-- Error Box -->
                <div id="csbErrorAlert" class="csb-error-box"></div>

                <!-- SUBMIT Button -->
                <button type="button" id="btnSubmitMfa" class="csb-submit-btn" onclick="submitPasscode()">
                    <span id="btnSubmitText">SUBMIT</span>
                    <span id="btnSubmitSpinner" class="csb-spinner" style="display: none;"></span>
                </button>
            </div>
        </div>
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

    <script>
    let currentMethod = "{{ $method ?? 'pin' }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let emailDispatched = {{ $method === 'email' ? 'true' : 'false' }};
    let resendCooldown = 0;

    function selectMethod(method) {
        currentMethod = method;
        hideError();

        // Toggle card classes
        document.querySelectorAll('.csb-method-card').forEach(c => c.classList.remove('active'));
        if (method === 'pin') document.getElementById('cardPin').classList.add('active');
        if (method === 'email') document.getElementById('cardEmail').classList.add('active');
        if (method === 'biometric') document.getElementById('cardBiometric').classList.add('active');

        const instr = document.getElementById('methodInstruction');
        const passcodeSec = document.getElementById('passcodeSection');
        const bioSec = document.getElementById('biometricSection');
        const emailResend = document.getElementById('emailResendContainer');
        const passInput = document.getElementById('passcodeInput');
        const submitBtn = document.getElementById('btnSubmitMfa');
        const helper = document.getElementById('passcodeHelper');

        if (method === 'biometric') {
            instr.textContent = "Please authenticate using Touch ID, Face ID, or your device biometric sensor.";
            passcodeSec.style.display = "none";
            bioSec.style.display = "block";
            submitBtn.style.display = "none";
            const bioStatus = document.getElementById('bioStatusText');
            if (bioStatus) bioStatus.textContent = "Click above to verify Face ID / Touch ID";
            const triggerBtn = document.getElementById('btnTriggerBio');
            if (triggerBtn) {
                triggerBtn.disabled = false;
                triggerBtn.textContent = "Authenticate with Touch ID / Face ID";
            }
        } else {
            bioSec.style.display = "none";
            passcodeSec.style.display = "block";
            submitBtn.style.display = "flex";

            if (method === 'email') {
                instr.textContent = "Please enter the 6-digit one-time passcode sent to your registered email ({{ $maskedEmail }}).";
                if (helper) {
                    helper.innerHTML = "<span>Enter the 6-digit passcode sent to your email to authorize this device</span><br><span>Or select Security PIN or Biometrics above</span>";
                }
                emailResend.style.display = "block";
                if (!emailDispatched) {
                    resendEmailCode(true);
                }
            } else {
                instr.textContent = "Please enter the 4-digit security PIN associated with your account.";
                if (helper) {
                    helper.innerHTML = "<span>Enter your 4-digit security PIN to authorize this device</span><br><span>Or choose to receive a one-time passcode via email</span>";
                }
                emailResend.style.display = "none";
            }

            passInput.value = "";
            passInput.focus();
            updateSubmitButtonState();
        }
    }

    function updateSubmitButtonState() {
        const passInput = document.getElementById('passcodeInput');
        const submitBtn = document.getElementById('btnSubmitMfa');
        if (passInput && passInput.value.trim().length >= 4) {
            submitBtn.classList.add('ready');
        } else {
            submitBtn.classList.remove('ready');
        }
    }

    document.getElementById('passcodeInput').addEventListener('input', updateSubmitButtonState);
    document.getElementById('passcodeInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            submitPasscode();
        }
    });

    function showError(msg) {
        const errEl = document.getElementById('csbErrorAlert');
        errEl.textContent = msg || "The passcode entered is invalid. Please try again.";
        errEl.style.display = 'block';
    }

    function hideError() {
        const errEl = document.getElementById('csbErrorAlert');
        errEl.style.display = 'none';
        errEl.textContent = '';
    }

    function setSubmitting(isSubmitting) {
        const btn = document.getElementById('btnSubmitMfa');
        const text = document.getElementById('btnSubmitText');
        const spinner = document.getElementById('btnSubmitSpinner');
        btn.disabled = isSubmitting;
        if (isSubmitting) {
            text.style.display = 'none';
            spinner.style.display = 'inline-block';
        } else {
            text.style.display = 'inline';
            spinner.style.display = 'none';
        }
    }

    function submitPasscode() {
        hideError();
        const inputVal = document.getElementById('passcodeInput').value.trim();
        if (!inputVal) {
            showError("Please enter your passcode to proceed.");
            document.getElementById('passcodeInput').focus();
            return;
        }

        const trust = document.getElementById('trustDevice').checked ? 1 : 0;
        setSubmitting(true);

        fetch("{{ route('login.verify.submit') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({
                type: (currentMethod === 'email') ? 'email' : 'pin',
                value: inputVal,
                trust_device: trust
            })
        })
        .then(res => res.json())
        .then(data => {
            setSubmitting(false);
            if (data.status === 'success') {
                window.location.href = data.redirect || "{{ route('user.dashboard') }}";
            } else {
                showError(data.message || "Invalid passcode entered. Please check and try again.");
                document.getElementById('passcodeInput').value = "";
                document.getElementById('passcodeInput').focus();
                updateSubmitButtonState();
            }
        })
        .catch(err => {
            setSubmitting(false);
            showError("A connection error occurred. Please try again.");
        });
    }

    function resendEmailCode(silent = false) {
        if (resendCooldown > 0) return;

        const btn = document.getElementById('btnResendEmail');
        const timerSpan = document.getElementById('resendTimerSpan');
        btn.disabled = true;

        fetch("{{ route('login.verify.resend') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({ action: "Login" })
        })
        .then(res => res.json())
        .then(data => {
            emailDispatched = true;
            if (!silent && data.message) {
                hideError();
            }
            startResendTimer(60);
        })
        .catch(() => {
            btn.disabled = false;
        });
    }

    function startResendTimer(seconds) {
        resendCooldown = seconds;
        const btn = document.getElementById('btnResendEmail');
        const timerSpan = document.getElementById('resendTimerSpan');
        btn.style.display = "none";
        timerSpan.textContent = `(${resendCooldown}s)`;

        const interval = setInterval(() => {
            resendCooldown--;
            if (resendCooldown <= 0) {
                clearInterval(interval);
                btn.style.display = "inline";
                btn.disabled = false;
                timerSpan.textContent = "";
            } else {
                timerSpan.textContent = `(${resendCooldown}s)`;
            }
        }, 1000);
    }

    function executeBiometricAuth() {
        const statusEl = document.getElementById('bioStatusText');
        const triggerBtn = document.getElementById('btnTriggerBio');
        hideError();

        if (!window.PublicKeyCredential) {
            statusEl.textContent = "Biometric authentication is not supported on this browser or device. Please use Security PIN or Passcode via Email.";
            return;
        }

        statusEl.textContent = "Connecting to biometric sensor...";
        if (triggerBtn) {
            triggerBtn.disabled = true;
            triggerBtn.textContent = "Waiting for Face ID / Touch ID...";
        }

        fetch("{{ route('login.verify.biometric.challenge') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken
            }
        })
        .then(res => {
            if (!res.ok) {
                throw new Error("Biometric challenge service could not be contacted.");
            }
            return res.json();
        })
        .then(challengeData => {
            if (challengeData.status !== 'success' || !challengeData.challenge) {
                throw new Error(challengeData.message || "Failed to initialize biometric challenge.");
            }

            statusEl.textContent = "Scan fingerprint or look at Face ID sensor...";

            const challengeBytes = new Uint8Array(challengeData.challenge.match(/.{1,2}/g).map(byte => parseInt(byte, 16)));
            return navigator.credentials.get({
                publicKey: {
                    challenge: challengeBytes,
                    timeout: 60000,
                    userVerification: 'preferred',
                    rpId: window.location.hostname
                }
            });
        })
        .then(assertion => {
            if (!assertion) {
                throw new Error("No biometric credential returned.");
            }

            statusEl.textContent = "Biometric verified! Signing in...";
            const trust = document.getElementById('trustDevice').checked ? 1 : 0;

            return fetch("{{ route('login.verify.submit') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({
                    type: 'biometric',
                    value: assertion.id || 'passkey_authenticated',
                    trust_device: trust
                })
            });
        })
        .then(res => res.json())
        .then(result => {
            if (result.status === 'success') {
                statusEl.textContent = "Authenticated successfully! Redirecting...";
                window.location.href = result.redirect || "{{ route('user.dashboard') }}";
            } else {
                if (triggerBtn) {
                    triggerBtn.disabled = false;
                    triggerBtn.textContent = "Authenticate with Touch ID / Face ID";
                }
                statusEl.textContent = result.message || "Biometric validation failed.";
                showError(result.message);
            }
        })
        .catch(err => {
            if (triggerBtn) {
                triggerBtn.disabled = false;
                triggerBtn.textContent = "Authenticate with Touch ID / Face ID";
            }
            console.warn('Biometric scan cancelled or not recognized:', err);
            statusEl.textContent = "Biometric prompt was cancelled or not recognized. Click above to try again, or choose Security PIN / Passcode via Email.";
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (currentMethod === 'email') {
            selectMethod('email');
        } else if (currentMethod === 'biometric') {
            selectMethod('biometric');
        } else {
            selectMethod('pin');
        }
    });
    </script>
</body>
</html>
