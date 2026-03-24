@extends('frontend::layouts.pinellas_auth')

@section('title')
    {{ __('Security Verification') }}
@endsection

@push('style')
<style>
    .mfa-card-inner {
        text-align: center;
        width: 100%;
    }
    
    .logo-container {
        text-align: center;
        margin-bottom: 16px;
    }

    .logo-container img {
        max-width: 140px;
        height: auto;
    }

    /* PIN Dots */
    .pin-display-wrapper {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-bottom: 25px;
    }
    .pin-dot {
        width: 14px;
        height: 14px;
        border: 2px solid var(--body-text-theme-color);
        border-radius: 50%;
        transition: all 0.2s ease;
    }
    .pin-dot.filled {
        background-color: var(--body-text-theme-color);
        transform: scale(1.1);
    }

    /* Keypad */
    .keypad {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 20px;
        max-width: 280px;
        margin-left: auto;
        margin-right: auto;
    }

    .key-btn {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 8px;
        font-size: 18px;
        font-weight: 600;
        color: var(--body-text-primary-color);
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 46px;
        user-select: none;
    }

    .key-btn:hover {
        background: #e9ecef;
        border-color: #dee2e6;
    }

    .key-btn:active {
        background: #dee2e6;
        transform: scale(0.95);
    }

    .key-btn.invisible {
        visibility: hidden;
    }

    .key-btn.backspace {
        color: var(--primary-button-color);
    }
    
    .key-btn.backspace svg {
        width: 24px;
        height: 24px;
        stroke-width: 2.5px;
    }

    /* Trust Device */
    .trust-device-wrapper {
        margin-top: 15px;
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
    }

    .custom-checkbox {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 14px;
        color: var(--body-text-secondary-color);
        user-select: none;
    }

    .custom-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--body-text-theme-color);
    }

    /* Links */
    .switch-link-wrapper {
        margin-top: 10px;
    }

    .forgot-link {
        color: var(--jha-text-theme);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        display: block;
        margin: 5px 0;
    }

    .forgot-link:hover {
        text-decoration: underline;
    }

    /* Shake Animation */
    .shake {
        animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
    }
    @keyframes shake {
        10%, 90% { transform: translate3d(-1px, 0, 0); }
        20%, 80% { transform: translate3d(2px, 0, 0); }
        30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
        40%, 60% { transform: translate3d(4px, 0, 0); }
    }

    /* Email Code Input */
    .input-box.otp-input {
        letter-spacing: 8px;
        font-size: 24px;
        text-align: center;
        font-weight: 700;
        max-width: 240px;
        margin: 0 auto;
    }

    .error-text {
        color: #ff3b30 !important; /* iOS/Branded Red */
        font-weight: 700 !important;
        margin-top: 8px;
        min-height: 20px;
    }
    
    .user-branding {
        background: #f1f4f8;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 8px;
        margin: -40px -40px 20px -40px;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    .user-name {
        color: #003d73;
        font-size: 0.95rem;
        letter-spacing: 0.5px;
    }

    .user-id {
        color: #64748b;
        font-size: 0.75rem;
        margin-top: 1px;
        font-weight: 500;
    }

    [hidden] {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="mfa-card-inner" id="mfa-container">
    
    <!-- User Branding -->
    <div class="user-branding">
        <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
            <h5 class="user-name mb-0 text-uppercase fw-bold">{{ __('Welcome') }}, {{ $user->first_name }} {{ $user->last_name }}</h5>
            <i class="fas fa-chevron-down small text-muted opacity-50" style="font-size: 0.7rem;"></i>
        </div>
        <div class="user-id">
            @php
                $uName = $user->username ?? $user->email;
                $len = strlen($uName);
                $masked = str_repeat('*', max(0, $len - 6)) . substr($uName, -6);
            @endphp
            {{ $masked }}
        </div>
    </div>
    
    <!-- Choice Section -->
    <div id="choice-section" @if($user->security_preference != 'always_ask') hidden @endif>
        
        <p class="text-secondary small mb-4">{{ __('Please select a verification method to continue.') }}</p>

        <div class="d-grid gap-3">
            <button type="button" class="btn btn-outline-primary p-3 rounded-3 d-flex align-items-center gap-3 text-start border-2 shadow-sm" onclick="switchMethod('email')">
                <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                    <i class="fas fa-envelope text-primary"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark lh-1 mb-1">{{ __('Email Verification') }}</div>
                    <div class="small text-muted">{{ __('6-digit code via email') }}</div>
                </div>
            </button>

            <button type="button" class="btn btn-outline-primary p-3 rounded-3 d-flex align-items-center gap-3 text-start border-2 shadow-sm" onclick="switchMethod('pin')" {{ !$user->transaction_pin ? 'disabled' : '' }}>
                <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                    <i class="fas fa-key text-primary"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark lh-1 mb-1">{{ __('Security Passcode') }}</div>
                    <div class="small text-muted">{{ $user->transaction_pin ? __('Enter your 4-digit Passcode') : __('Passcode not set up yet') }}</div>
                </div>
            </button>
        </div>
    </div>

    <!-- Passcode Verification Section -->
    <div id="pin-section" @if($user->security_preference == 'always_ask' || $method != 'pin') hidden @endif>
        <h4 class="fw-bold text-dark mb-2">{{ __('Verification Required') }}</h4>
        <p class="text-secondary small mb-4">{{ __('Enter your 4-digit passcode to continue.') }}</p>

        <div class="pin-display-wrapper mb-4" id="pin-display">
            <div class="pin-dot"></div>
            <div class="pin-dot"></div>
            <div class="pin-dot"></div>
            <div class="pin-dot"></div>
        </div>

        <div id="pin-error" class="error-text small mb-3"></div>

        <div class="keypad">
            <button type="button" class="key-btn" data-key="1">1</button>
            <button type="button" class="key-btn" data-key="2">2</button>
            <button type="button" class="key-btn" data-key="3">3</button>
            <button type="button" class="key-btn" data-key="4">4</button>
            <button type="button" class="key-btn" data-key="5">5</button>
            <button type="button" class="key-btn" data-key="6">6</button>
            <button type="button" class="key-btn" data-key="7">7</button>
            <button type="button" class="key-btn" data-key="8">8</button>
            <button type="button" class="key-btn" data-key="9">9</button>
            <div class="key-btn invisible"></div>
            <button type="button" class="key-btn" data-key="0">0</button>
            <button type="button" class="key-btn backspace" id="btn-backspace">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path><line x1="18" y1="9" x2="12" y2="15"></line><line x1="12" y1="9" x2="18" y2="15"></line></svg>
            </button>
        </div>

        <div class="trust-device-wrapper">
            <label class="custom-checkbox">
                <input type="checkbox" name="trust_device_pin" id="trust_device_pin" checked>
                {{ __('Trust this device') }}
            </label>
        </div>

        <div class="switch-link-wrapper">
            <a href="javascript:void(0)" class="forgot-link" onclick="switchMethod(securityPreference === 'always_ask' ? '' : 'email')">
                {{ $user->security_preference == 'always_ask' ? __('Back to Options') : __('Use Email Verification Code instead') }}
            </a>
        </div>
    </div>

    <!-- Email Verification Section -->
    <div id="email-section" @if($user->security_preference == 'always_ask' || $method != 'email') hidden @endif>
        <h4 class="fw-bold text-dark mb-2">{{ __('Verification Required') }}</h4>
        <p class="text-secondary small mb-1">{{ __('We\'ve sent a verification code to') }}</p>
        <p class="fw-bold text-dark small mb-4">{{ $maskedEmail }}</p>

        <div class="mb-4">
            <input type="text" id="email_code" class="input-box otp-input" placeholder="000000" maxlength="6" autocomplete="one-time-code">
            <div id="email-error" class="error-text small mt-2"></div>
        </div>

        <button type="button" class="primary-btn w-100 mb-3" id="btn-verify-email" onclick="verifyEmail()">{{ __('Verify Code') }}</button>

        <div class="trust-device-wrapper">
            <label class="custom-checkbox">
                <input type="checkbox" name="trust_device_email" id="trust_device_email" checked>
                {{ __('Trust this device') }}
            </label>
        </div>

        <div class="mt-4">
            <div class="mb-2">
                <a href="javascript:void(0)" class="forgot-link" id="btn-resend-email" onclick="resendEmail()">{{ __('Resend Code') }}</a>
            </div>
            <div class="mt-2 text-muted small" style="opacity: 0.5;">— or —</div>
            <div class="mt-2">
                <a href="javascript:void(0)" class="forgot-link" onclick="switchMethod(securityPreference === 'always_ask' ? '' : 'pin')">
                    {{ $user->security_preference == 'always_ask' ? __('Back to Options') : __('Use Passcode instead') }}
                </a>
            </div>
        </div>
    </div>

</div>
@endsection

@push('script')
<script>
    "use strict";

    let currentPin = "";
    const choiceSection = document.getElementById('choice-section');
    const pinSection = document.getElementById('pin-section');
    const emailSection = document.getElementById('email-section');
    const mfaContainer = document.getElementById('mfa-container');
    const securityPreference = "{{ $user->security_preference }}";

    // Keypad Logic
    document.querySelectorAll('.key-btn[data-key]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentPin.length < 4) {
                currentPin += btn.dataset.key;
                updatePinDots();
                if (currentPin.length === 4) {
                    verifyPin();
                }
            }
        });
    });

    document.getElementById('btn-backspace').addEventListener('click', () => {
        if (currentPin.length > 0) {
            currentPin = currentPin.slice(0, -1);
            updatePinDots();
        }
    });

    function updatePinDots() {
        const dots = document.querySelectorAll('#pin-display .pin-dot');
        dots.forEach((dot, index) => {
            if (index < currentPin.length) {
                dot.classList.add('filled');
            } else {
                dot.classList.remove('filled');
            }
        });
    }

    function switchMethod(method) {
        if (choiceSection) choiceSection.hidden = true;
        
        if (method === 'email') {
            pinSection.hidden = true;
            emailSection.hidden = false;
        } else if (method === 'pin') {
            pinSection.hidden = false;
            emailSection.hidden = true;
        } else {
            // Back to choice
            if (securityPreference === 'always_ask') {
                choiceSection.hidden = false;
                pinSection.hidden = true;
                emailSection.hidden = true;
            } else {
                // Toggle if not always_ask
                if (pinSection.hidden) {
                    switchMethod('pin');
                } else {
                    switchMethod('email');
                }
            }
        }
    }

    function verifyPin() {
        const trust = document.getElementById('trust_device_pin').checked;
        const errorEl = document.getElementById('pin-error');
        
        if (typeof window.showLoader === 'function') window.showLoader('Verifying passcode...');
        errorEl.textContent = "";

        fetch("{{ route('login.verify.submit') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ value: currentPin, trust_device: trust, type: 'pin' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = data.redirect || "{{ route('user.dashboard') }}";
            } else {
                if (typeof window.hideLoader === 'function') window.hideLoader();
                
                errorEl.innerText = data.message || 'Incorrect passcode';
                
                // Shake animation
                pinSection.classList.remove('shake');
                void pinSection.offsetWidth;
                pinSection.classList.add('shake');

                // Reset PIN
                currentPin = "";
                updatePinDots();

                if (data.status === 'fallback') {
                    setTimeout(() => switchMethod('email'), 1500);
                } else if (data.status === 'locked_out') {
                    setTimeout(() => window.location.reload(), 2000);
                }
            }
        })
        .catch(err => {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            errorEl.innerText = "Connection error. Please try again.";
        });
    }

    function verifyEmail() {
        const code = document.getElementById('email_code').value;
        const trust = document.getElementById('trust_device_email').checked;
        const errorEl = document.getElementById('email-error');

        if (code.length < 4) {
            errorEl.innerText = "Please enter the verification code.";
            return;
        }

        if (typeof window.showLoader === 'function') window.showLoader('Verifying code...');
        errorEl.textContent = "";

        fetch("{{ route('login.verify.submit') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ value: code, trust_device: trust, type: 'email' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = data.redirect || "{{ route('user.dashboard') }}";
            } else {
                if (typeof window.hideLoader === 'function') window.hideLoader();
                
                errorEl.innerText = data.message || 'Incorrect code';
                
                // Shake animation
                emailSection.classList.remove('shake');
                void emailSection.offsetWidth;
                emailSection.classList.add('shake');

                if (data.status === 'locked_out') {
                    setTimeout(() => window.location.reload(), 2000);
                }
            }
        })
        .catch(err => {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            errorEl.innerText = "Connection error. Please try again.";
        });
    }

    function resendEmail() {
        const btn = document.getElementById('btn-resend-email');
        const errorEl = document.getElementById('email-error');
        
        btn.style.pointerEvents = 'none';
        btn.innerText = 'Sending...';

        fetch("{{ route('login.verify.resend') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                errorEl.innerText = "New code sent to your email.";
                errorEl.style.color = "#28a745"; // Green
                setTimeout(() => {
                    errorEl.innerText = "";
                    errorEl.style.color = "#ff3b30"; // Back to Red
                }, 5000);
            } else {
                errorEl.innerText = data.message || "Failed to resend code.";
            }
        })
        .finally(() => {
            btn.style.pointerEvents = 'auto';
            btn.innerText = 'Resend Code';
        });
    }

    // Handle Enter key for email input
    document.getElementById('email_code').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') verifyEmail();
    });
</script>
@endpush
