@extends('frontend::layouts.pinellas_auth')

@section('title')
    {{ __('Security Verification') }}
@endsection

@push('style')
<style>
    .mfa-container {
        text-align: center;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        padding-bottom: 20px;
    }
    .pin-display {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin: 20px 0;
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
    .keypad {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        max-width: 260px;
        margin: 0 auto;
    }
    .key-btn {
        aspect-ratio: 1/1;
        border-radius: 50%;
        border: 1px solid #f1f3f5;
        background: white;
        font-size: 22px;
        font-weight: 600;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        user-select: none;
    }
    .key-btn:active {
        background: #f8f9fa;
        transform: scale(0.92);
    }
    .key-btn.empty { border: none; background: none; box-shadow: none; cursor: default; }
    .key-btn.action { font-size: 16px; color: #666; }

    /* Email Mode */
    .otp-inputs {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-bottom: 20px;
    }
    .otp-field {
        width: 40px;
        height: 50px;
        text-align: center;
        font-size: 22px;
        font-weight: 700;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        outline: none;
        transition: border-color 0.2s;
    }
    .otp-field:focus {
        border-color: var(--body-text-theme-color);
    }
    
    #mfa-feedback {
        min-height: 24px;
        margin-bottom: 10px;
        font-weight: 500;
    }

    .shake {
        animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
    }
    @keyframes shake {
        10%, 90% { transform: translate3d(-1px, 0, 0); }
        20%, 80% { transform: translate3d(2px, 0, 0); }
        30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
        40%, 60% { transform: translate3d(4px, 0, 0); }
    }
    
    .form-check-input:checked {
        background-color: var(--body-text-theme-color);
        border-color: var(--body-text-theme-color);
    }
</style>
@endpush

@section('content')
<div class="mfa-container" id="mfa-main-container">
    <div id="mfa-header" @if($user->security_preference == 'always_ask') hidden @endif>
        <h4 class="fw-bold text-dark mb-1">{{ __('Security Verification') }}</h4>
        <p class="text-muted small mb-3" id="mfa-instruction">
            @if($user->security_preference == 'email_priority')
                {{ __('Enter the 6-digit code sent to your email.') }}
            @else
                {{ __('Enter your 4-digit Passcode to continue.') }}
            @endif
        </p>
    </div>

    <!-- Error/Feedback Area (Moved Up) -->
    <div id="mfa-feedback" class="text-danger small"></div>

    <!-- CHOICE UI -->
    <div id="choice-ui" @if($user->security_preference != 'always_ask') hidden @endif>
        <div class="d-grid gap-2 mt-2">
            <button type="button" class="btn btn-outline-primary p-3 rounded-4 d-flex align-items-center gap-3 text-start border-2 shadow-sm" onclick="switchMode('email')">
                <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                    <i class="fas fa-envelope text-primary"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark lh-1 mb-1">{{ __('Email Verification Code') }}</div>
                    <div class="small text-muted">{{ __('6-digit code via email') }}</div>
                </div>
            </button>

            <button type="button" class="btn btn-outline-primary p-3 rounded-4 d-flex align-items-center gap-3 text-start border-2 shadow-sm" onclick="switchMode('pin')" {{ !$user->transaction_pin ? 'disabled' : '' }}>
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

    <!-- PIN PAD UI -->
    <div id="pin-ui" @if($user->security_preference == 'email_priority' || $user->security_preference == 'always_ask') hidden @endif>
        <div class="pin-display">
            <div class="pin-dot" id="dot-1"></div>
            <div class="pin-dot" id="dot-2"></div>
            <div class="pin-dot" id="dot-3"></div>
            <div class="pin-dot" id="dot-4"></div>
        </div>

        <div class="keypad">
            @foreach([1,2,3,4,5,6,7,8,9] as $num)
                <div class="key-btn" onclick="pressKey({{ $num }})">{{ $num }}</div>
            @endforeach
            <div class="key-btn empty"></div>
            <div class="key-btn" onclick="pressKey(0)">0</div>
            <div class="key-btn action" onclick="backspace()">
                <i class="fas fa-backspace"></i>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="javascript:void(0)" class="text-decoration-none small fw-bold text-primary" onclick="backToChoice()">
                {{ $user->security_preference == 'always_ask' ? __('Back') : __('Use Email Verification Code instead') }}
            </a>
        </div>
    </div>

    <!-- EMAIL OTP UI -->
    <div id="email-ui" @if($user->security_preference != 'email_priority') hidden @endif>
        <div class="text-center mb-3">
            <div class="small text-muted">{{ __('Sent to') }} <span class="fw-bold text-dark">{{ substr($user->email, 0, 3) . '***' . substr($user->email, strpos($user->email, '@')) }}</span></div>
        </div>

        <div class="otp-inputs">
            @for($i = 1; $i <= 6; $i++)
                <input type="text" maxlength="1" class="otp-field" id="otp-{{ $i }}" onkeyup="moveFocus(this, {{ $i }})" onkeydown="handleBackspace(event, {{ $i }})" autofocus>
            @endfor
        </div>

        <div class="text-center">
            <button type="button" class="btn btn-link text-primary text-decoration-none small fw-bold p-0" onclick="resendEmail()">{{ __('Resend Code') }}</button>
        </div>

        <div class="mt-3">
            <a href="javascript:void(0)" class="text-decoration-none small fw-bold text-primary" onclick="backToChoice()">
                 {{ $user->security_preference == 'always_ask' ? __('Back') : __('Use Passcode instead') }}
            </a>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">
        <div class="form-check form-switch d-inline-block text-start">
            <input class="form-check-input" type="checkbox" id="trust_device" name="trust_device" checked style="cursor: pointer; width: 36px; height: 18px;">
            <label class="form-check-label small text-muted ms-2" for="trust_device" style="cursor: pointer; padding-top: 2px;">
                {{ __('Trust this device for 30 days') }}
            </label>
        </div>
    </div>
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
        const container = document.getElementById('mfa-main-container');

        if (typeof window.showLoader === 'function') window.showLoader('Verifying security...');
        feedback.textContent = "";

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
                feedback.textContent = data.message || "Incorrect code. Please try again.";
                feedback.className = "text-danger small mb-2";
                
                // Shake Animation
                container.classList.remove('shake');
                void container.offsetWidth; // Trigger reflow
                container.classList.add('shake');

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
        .catch((error) => {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            console.error("MFA Error:", error);
            feedback.textContent = "An error occurred. Please try again.";
            feedback.className = "text-danger small mb-2";
            
            container.classList.remove('shake');
            void container.offsetWidth;
            container.classList.add('shake');
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
