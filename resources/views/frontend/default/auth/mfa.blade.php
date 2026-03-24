@extends('frontend::layouts.pinellas_auth')

@section('title')
    {{ __('Security Verification') }}
@endsection

@push('style')
<style>
    .mfa-container {
        text-align: center;
        width: 100%;
    }
    .pin-display {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin: 30px 0;
    }
    .pin-dot {
        width: 16px;
        height: 16px;
        border: 2px solid var(--body-text-theme-color);
        border-radius: 50%;
        transition: all 0.2s ease;
    }
    .pin-dot.filled {
        background-color: var(--body-text-theme-color);
        transform: scale(1.2);
    }
    .keypad {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        max-width: 280px;
        margin: 0 auto;
    }
    .key-btn {
        aspect-ratio: 1/1;
        border-radius: 50%;
        border: 1px solid #e9ecef;
        background: white;
        font-size: 24px;
        font-weight: 600;
        color: var(--body-text-primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        user-select: none;
    }
    .key-btn:active {
        background: #f8f9fa;
        transform: scale(0.95);
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    }
    .key-btn.empty {
        border: none;
        background: none;
        box-shadow: none;
        cursor: default;
    }
    .key-btn.action {
        font-size: 18px;
        color: var(--body-text-secondary-color);
    }

    /* Email Mode */
    .otp-inputs {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 30px;
    }
    .otp-field {
        width: 45px;
        height: 55px;
        text-align: center;
        font-size: 24px;
        font-weight: 700;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        outline: none;
        transition: all 0.2s;
    }
    .otp-field:focus {
        border-color: var(--body-text-theme-color);
        box-shadow: 0 0 0 4px rgba(0, 84, 155, 0.1);
    }
    
    #mfa-feedback {
        margin-top: 20px;
        font-size: 14px;
        min-height: 20px;
    }
</style>
@endpush

@section('content')
<div class="mfa-container">
    <div id="mfa-header" @if($user->security_preference == 'always_ask') hidden @endif>
        <h4 class="fw-bold text-dark mb-2">{{ __('Verification Required') }}</h4>
        <p class="text-muted small" id="mfa-instruction">
            @if($user->security_preference == 'email_priority')
                {{ __('Enter the 6-digit code sent to your email.') }}
            @else
                {{ __('Enter your 4-digit Passcode to continue.') }}
            @endif
        </p>
    </div>

    <!-- CHOICE UI -->
    <div id="choice-ui" @if($user->security_preference != 'always_ask') hidden @endif>
        <div class="d-grid gap-3 mt-4">
            <button type="button" class="btn btn-outline-primary p-3 rounded-4 d-flex align-items-center gap-3 text-start transition-all" onclick="switchMode('email')">
                <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                    <i class="fas fa-envelope text-primary"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark">{{ __('Email Verification Code') }}</div>
                    <div class="small text-muted">{{ __('Get a 6-digit code via email') }}</div>
                </div>
            </button>

            <button type="button" class="btn btn-outline-primary p-3 rounded-4 d-flex align-items-center gap-3 text-start transition-all" onclick="switchMode('pin')" {{ !$user->transaction_pin ? 'disabled' : '' }}>
                <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                    <i class="fas fa-key text-primary"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark">{{ __('Security PIN') }}</div>
                    <div class="small text-muted">{{ $user->transaction_pin ? __('Enter your 4-digit Passcode') : __('Passcode not set up yet') }}</div>
                </div>
            </button>
        </div>
    </div>

    <!-- PIN PAD UI -->
    <div id="pin-ui" @if($user->security_preference == 'email_priority' || $user->security_preference == 'always_ask') hidden @endif>
        <div class="pin-display">
            <div class="pin-dot" id="dot-1"></div>
            <div class="pin-dot" id="dot-2"></div>
            <div class="pin-dot" id="dot-3"></div>
            <div class="pin-dot" id="dot-4"></div>
        </div>

        <div class="keypad">
            <div class="key-btn" onclick="pressKey(1)">1</div>
            <div class="key-btn" onclick="pressKey(2)">2</div>
            <div class="key-btn" onclick="pressKey(3)">3</div>
            <div class="key-btn" onclick="pressKey(4)">4</div>
            <div class="key-btn" onclick="pressKey(5)">5</div>
            <div class="key-btn" onclick="pressKey(6)">6</div>
            <div class="key-btn" onclick="pressKey(7)">7</div>
            <div class="key-btn" onclick="pressKey(8)">8</div>
            <div class="key-btn" onclick="pressKey(9)">9</div>
            <div class="key-btn empty"></div>
            <div class="key-btn" onclick="pressKey(0)">0</div>
            <div class="key-btn action" onclick="backspace()">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path><line x1="18" y1="9" x2="12" y2="15"></line><line x1="12" y1="9" x2="18" y2="15"></line></svg>
            </div>
        </div>
        
        <div class="mt-4">
            <button type="button" class="btn btn-link text-decoration-none small text-primary" onclick="backToChoice()">
                {{ $user->security_preference == 'always_ask' ? __('Back') : __('Use Email Verification Code instead') }}
            </button>
        </div>
    </div>

    <!-- EMAIL OTP UI -->
    <div id="email-ui" @if($user->security_preference != 'email_priority') hidden @endif>
        <div class="text-center mb-4 mt-3">
            <div class="small text-muted">{{ __('Verification code sent to') }}</div>
            <div class="fw-bold text-dark">{{ substr($user->email, 0, 3) . '***' . substr($user->email, strpos($user->email, '@')) }}</div>
        </div>

        <div class="otp-inputs">
            @for($i = 1; $i <= 6; $i++)
                <input type="text" maxlength="1" class="otp-field" id="otp-{{ $i }}" onkeyup="moveFocus(this, {{ $i }})" onkeydown="handleBackspace(event, {{ $i }})">
            @endfor
        </div>

        <div class="text-center">
            <button type="button" class="btn btn-link text-primary text-decoration-none small fw-bold" onclick="resendEmail()">{{ __('Resend Code') }}</button>
        </div>

        <div class="mt-4">
            <button type="button" class="btn btn-link text-decoration-none small text-primary" onclick="backToChoice()">
                 {{ $user->security_preference == 'always_ask' ? __('Back') : __('Use Passcode instead') }}
            </button>
        </div>
    </div>

    <div class="mt-4 text-start">
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: var(--body-text-secondary-color); justify-content: center;">
            <input type="checkbox" id="trust_device" name="trust_device" checked style="width: 18px; height: 18px; cursor: pointer;">
            {{ __('Trust this device for 30 days') }}
        </label>
    </div>

    <div id="mfa-feedback" class="text-danger small mt-3"></div>
</div>
@endsection

@push('script')
<script>
    let currentPin = "";
    let mfaMode = "{{ $user->security_preference == 'email_priority' ? 'email' : 'pin' }}";

    function pressKey(num) {
        if (currentPin.length < 4) {
            currentPin += num;
            updateDots();
            if (currentPin.length === 4) {
                submitMfa();
            }
        }
    }

    function backspace() {
        if (currentPin.length > 0) {
            currentPin = currentPin.slice(0, -1);
            updateDots();
        }
    }

    function updateDots() {
        for (let i = 1; i <= 4; i++) {
            const dot = document.getElementById('dot-' + i);
            if (i <= currentPin.length) {
                dot.classList.add('filled');
            } else {
                dot.classList.remove('filled');
            }
        }
    }

    function moveFocus(el, index) {
        if (el.value.length === 1 && index < 6) {
            document.getElementById('otp-' + (index + 1)).focus();
        }
        
        let otp = "";
        for(let i=1; i<=6; i++) {
            otp += document.getElementById('otp-'+i).value;
        }
        if (otp.length === 6) {
            submitMfa(otp);
        }
    }

    function handleBackspace(e, index) {
        if (e.key === 'Backspace' && !e.target.value && index > 1) {
            document.getElementById('otp-' + (index - 1)).focus();
        }
    }

    function switchMode(mode) {
        mfaMode = mode;
        const choiceUi = document.getElementById('choice-ui');
        const pinUi = document.getElementById('pin-ui');
        const emailUi = document.getElementById('email-ui');
        const header = document.getElementById('mfa-header');
        const instruction = document.getElementById('mfa-instruction');

        choiceUi.hidden = true;
        header.hidden = false;

        if (mode === 'pin') {
            pinUi.hidden = false;
            emailUi.hidden = true;
            instruction.textContent = "{{ __('Enter your 4-digit Passcode to continue.') }}";
        } else {
            pinUi.hidden = true;
            emailUi.hidden = false;
            instruction.textContent = "{{ __('Enter the 6-digit code sent to your email.') }}";
            resendEmail(); // Ensure code is sent
        }
        document.getElementById('mfa-feedback').textContent = "";
    }

    function backToChoice() {
        const preference = "{{ $user->security_preference }}";
        if (preference === 'always_ask') {
            document.getElementById('choice-ui').hidden = false;
            document.getElementById('pin-ui').hidden = true;
            document.getElementById('email-ui').hidden = true;
            document.getElementById('mfa-header').hidden = true;
            document.getElementById('mfa-feedback').textContent = "";
        } else {
            // Toggle between PIN and Email if not "always ask"
            switchMode(mfaMode === 'pin' ? 'email' : 'pin');
        }
    }

    function resendEmail() {
        const feedback = document.getElementById('mfa-feedback');
        feedback.textContent = "Sending code...";
        feedback.className = "small mt-3 text-info";

        fetch("{{ route('login.verify.resend') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ action: 'Login Verification' })
        })
        .then(res => res.json())
        .then(data => {
            feedback.textContent = data.message;
            if (data.status === 'success') {
                feedback.className = "small mt-3 text-success";
            } else {
                feedback.className = "small mt-3 text-danger";
            }
        })
        .catch(() => {
            feedback.textContent = "Error sending code.";
            feedback.className = "small mt-3 text-danger";
        });
    }

    function submitMfa(otpValue = null) {
        const value = otpValue || currentPin;
        const type = otpValue ? 'email' : 'pin';
        const trustDevice = document.getElementById('trust_device').checked;
        const feedback = document.getElementById('mfa-feedback');

        if (typeof window.showLoader === 'function') window.showLoader('Verifying security...');
        
        fetch("{{ route('login.verify.submit') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                type: type,
                value: value,
                trust_device: trustDevice
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = data.redirect;
            } else {
                if (typeof window.hideLoader === 'function') window.hideLoader();
                feedback.textContent = data.message;
                feedback.className = "small mt-3 text-danger";
                
                if (type === 'pin') {
                    currentPin = "";
                    updateDots();
                } else {
                    // Clear OTP fields
                    for(let i=1; i<=6; i++) document.getElementById('otp-'+i).value = "";
                    document.getElementById('otp-1').focus();
                }
            }
        })
        .catch(() => {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            feedback.textContent = "An error occurred. Please try again.";
            feedback.className = "small mt-3 text-danger";
        });
    }

    // Auto-send email if priority is email
    @if($user->security_preference == 'email_priority')
        document.addEventListener('DOMContentLoaded', function() {
            resendEmail();
            document.getElementById('otp-1').focus();
        });
    @endif
</script>
@endpush
