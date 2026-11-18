@extends('frontend::layouts.frontfield_auth')

@section('title')
    {{ __('Security Verification') }}
@endsection

@php
    $currentInst = app()->bound('institution') ? app('institution') : ($user->institution ?? null);
    $isCustomInst = $currentInst && !$currentInst->is_default;
    $isCsb = ($isCustomInst && ($currentInst->slug === 'clevelandstatebank' || str_contains(request()->getHost(), 'clevelandstatebank')))
             || (isset($user) && $user->institution && $user->institution->slug === 'clevelandstatebank')
             || str_contains(request()->getHost(), 'clevelandstatebank');
@endphp

@push('style')
@if($isCsb)
<style>
    /* CSB-Scoped Bank-Grade Visual Overhaul */
    .login-card {
        border-top: 4px solid #c8232c !important;
        border-radius: 16px !important;
        box-shadow: 0 10px 30px rgba(22, 40, 70, 0.08) !important;
    }
    .user-branding {
        background: #ffffff !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .user-name {
        color: #162846 !important;
        font-weight: 700 !important;
    }
    @keyframes csbBiometricPulse {
        0% { box-shadow: 0 0 0 0 rgba(200, 35, 44, 0.28); }
        70% { box-shadow: 0 0 0 16px rgba(200, 35, 44, 0); }
        100% { box-shadow: 0 0 0 0 rgba(200, 35, 44, 0); }
    }
    .csb-biometric-pulse-wrap {
        width: 78px;
        height: 78px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(200, 35, 44, 0.08) 0%, rgba(22, 40, 70, 0.04) 100%);
        border: 2px solid rgba(200, 35, 44, 0.22);
        color: #c8232c;
        font-size: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px auto;
        animation: csbBiometricPulse 2.4s infinite ease-in-out;
    }
    .csb-btn-biometric {
        background: linear-gradient(135deg, #c8232c 0%, #a81c24 100%) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 10px !important;
        padding: 13px 20px !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        letter-spacing: 0.3px !important;
        box-shadow: 0 4px 14px rgba(200, 35, 44, 0.28) !important;
        transition: all 0.2s ease !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }
    .csb-btn-biometric:hover:not(:disabled) {
        background: linear-gradient(135deg, #b51f27 0%, #90161d 100%) !important;
        box-shadow: 0 6px 18px rgba(200, 35, 44, 0.38) !important;
        transform: translateY(-1px) !important;
        color: #ffffff !important;
    }
    .csb-btn-biometric:active:not(:disabled) {
        transform: translateY(0) !important;
        box-shadow: 0 2px 8px rgba(200, 35, 44, 0.28) !important;
    }
    .csb-auth-alert {
        background: #fef2f2;
        border: 1px solid #fee2e2;
        color: #dc2626;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
    }
    .csb-auth-alert:empty {
        display: none !important;
    }
    .csb-secondary-auth-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 14px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        text-decoration: none !important;
        color: #162846 !important;
        transition: all 0.2s ease;
        margin-bottom: 8px;
    }
    .csb-secondary-auth-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        color: #c8232c !important;
    }
    .csb-secondary-auth-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: rgba(22, 40, 70, 0.05);
        color: #162846;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
</style>
@endif
<style>
    .mfa-card-inner {
        text-align: center;
        width: 100%;
    }
    
    .login-card {
        padding: 25px !important;
    }

    .logo-container {
        text-align: center;
        margin-bottom: 10px;
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
        touch-action: manipulation;
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
        padding: 8px 6px;
        margin: -25px -25px 12px -25px;
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
        margin-top: -2px !important;
        font-weight: 500;
        line-height: 1;
    }

    /* Method Selection Styling */
    .method-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .method-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        text-align: left;
        cursor: pointer;
        transition: all 0.2s ease;
        width: 100%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        border-left: 4px solid transparent;
        appearance: none;
        -webkit-appearance: none;
    }

    .method-card:hover:not([disabled]) {
        border-color: #00549b;
        border-left-color: #00549b;
        background: #f8fbff;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.06);
    }

    .method-card[disabled] {
        opacity: 0.6;
        cursor: not-allowed;
        background: #f8f9fa;
    }

    .method-icon {
        width: 42px;
        height: 42px;
        background: rgba(0, 84, 155, 0.08);
        color: #00549b;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 18px;
    }

    .method-info {
        flex-grow: 1;
    }

    .method-info .title {
        font-weight: 700;
        color: #0d1e3a;
        font-size: 14px;
        margin-bottom: 2px;
        display: block;
    }

    .method-info .subtitle {
        font-size: 11px;
        color: #64748b;
        display: block;
    }

    .method-icon svg {
        display: block;
    }

    .method-card .chevron {
        color: #ccd2d8;
        flex-shrink: 0;
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
        <div class="d-flex align-items-center justify-content-center gap-2">
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
        <p class="text-secondary small mb-3">{{ __('Please select a verification method to continue.') }}</p>

        <div class="method-list">
            <button type="button" class="method-card" id="btn-choice-biometric" onclick="switchMethod('biometric')">
                <div class="method-icon" style="background: rgba(200, 35, 44, 0.1); color: #c8232c;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 11c0 3.5-1.5 6-4 7.5"></path>
                        <path d="M12 11c0-2.5 1.5-4 4-4s4 1.5 4 4c0 3.5-1.5 6-4 7.5"></path>
                        <path d="M7 11c0-4 2.5-7 5-7s5 3 5 7"></path>
                        <path d="M12 21a9 9 0 0 0 9-9c0-5-4-9-9-9s-9 4-9 9a9 9 0 0 0 3 6.7"></path>
                    </svg>
                </div>
                <div class="method-info">
                    <span class="title">{{ __('Biometrics & Passkeys') }}</span>
                    <span class="subtitle">{{ __('Face ID, Touch ID, or Windows Hello') }}</span>
                </div>
                <div class="chevron">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </button>

            <button type="button" class="method-card" onclick="switchMethod('email')">
                <div class="method-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                </div>
                <div class="method-info">
                    <span class="title">{{ __('Email Verification') }}</span>
                    <span class="subtitle">{{ __('6-digit code via email') }}</span>
                </div>
                <div class="chevron">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </button>

            <button type="button" class="method-card" onclick="switchMethod('pin')" {{ !$user->transaction_pin ? 'disabled' : '' }}>
                <div class="method-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3y2.5z"></path></svg>
                </div>
                <div class="method-info">
                    <span class="title">{{ __('Security Passcode') }}</span>
                    <span class="subtitle">{{ $user->transaction_pin ? __('Enter your 4-digit Passcode') : __('Passcode not set up yet') }}</span>
                </div>
                <div class="chevron">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </button>
        </div>
    </div>

    <!-- Biometric Verification Section -->
    <div id="biometric-section" @if($user->security_preference == 'always_ask' || $method != 'biometric') hidden @endif>
        @if($isCsb)
        <div class="text-center py-2">
            <!-- FDIC Trust Badge -->
            <div class="mb-3">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill fw-bold" style="font-size: 11px;">
                    <i class="fa-solid fa-shield-halved me-1"></i> FDIC Insured • Cleveland State Bank
                </span>
            </div>

            <!-- Multi-layered Circular Icon with Pulse Ring Animation -->
            <div class="csb-biometric-pulse-wrap">
                <i class="fa-solid fa-fingerprint"></i>
            </div>

            <h4 class="fw-bold mb-1" style="color: #162846; font-size: 20px;">{{ __('Biometric Authentication') }}</h4>
            <p class="text-secondary small mb-3">{{ __('Use Touch ID, Face ID, or Windows Hello for instant access.') }}</p>
            
            <!-- High-Polish Crimson Button Gradient -->
            <button type="button" class="btn csb-btn-biometric mb-3 shadow-sm" id="btn-trigger-biometric" onclick="triggerBiometricLogin()">
                <i class="fa-solid fa-fingerprint me-2"></i> {{ __('Confirm with Face ID / Touch ID') }}
            </button>

            <!-- Alert Pill Error Styling -->
            <div id="biometric-error" class="error-text csb-auth-alert small mb-3"></div>

            <div class="trust-device-wrapper">
                <label class="custom-checkbox">
                    <input type="checkbox" name="trust_device_biometric" id="trust_device_biometric" checked>
                    {{ __('Trust this device') }}
                </label>
            </div>

            <!-- Clean Secondary Option Cards with Icons & Chevrons -->
            <div class="switch-link-wrapper mt-3 text-start">
                <a href="javascript:void(0)" class="csb-secondary-auth-card" onclick="switchMethod('pin')">
                    <div class="csb-secondary-auth-icon"><i class="fa-solid fa-keypad"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold small">{{ __('Use Security Passcode instead') }}</div>
                        <div class="text-muted" style="font-size: 11px;">{{ __('Enter your 4-digit security PIN') }}</div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-muted small"></i>
                </a>
                <a href="javascript:void(0)" class="csb-secondary-auth-card" onclick="switchMethod('email')">
                    <div class="csb-secondary-auth-icon"><i class="fa-solid fa-envelope"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold small">{{ __('Use Email Verification Code instead') }}</div>
                        <div class="text-muted" style="font-size: 11px;">{{ __('One-time verification code sent to your email') }}</div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-muted small"></i>
                </a>
                @if($user->security_preference == 'always_ask')
                <div class="text-center mt-2">
                    <a href="javascript:void(0)" class="forgot-link small" onclick="switchMethod('')">
                        <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Back to Options') }}
                    </a>
                </div>
                @endif
            </div>
        </div>
        @else
        <!-- FrontField Biometric Verification View -->
        <div class="text-center py-2">
            <div class="mb-3">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1 rounded-pill fw-bold" style="font-size: 11px;">
                    <i class="fas fa-shield-alt me-1"></i> Federally Insured by NCUA • FrontField Credit Union
                </span>
            </div>

            <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 68px; height: 68px; background: rgba(0, 84, 155, 0.1); color: #00549b; font-size: 30px;">
                <i class="fa-solid fa-fingerprint"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">{{ __('Biometric Authentication') }}</h5>
            <p class="text-secondary small mb-3">{{ __('Use your fingerprint, facial recognition, or hardware key.') }}</p>
            
            <button type="button" class="btn text-white w-100 py-3 fw-bold rounded-pill mb-3 shadow-sm" id="btn-trigger-biometric" onclick="triggerBiometricLogin()" style="background:#00549b; font-size: 15px;">
                <i class="fa-solid fa-fingerprint me-2"></i> {{ __('Confirm with Face ID / Touch ID') }}
            </button>

            <!-- Step-by-step prompt & Green Success Banner -->
            <div id="ffcu-biometric-prompt" class="small mb-3 d-none p-2 rounded-2" style="background: #eef6fc; color: #00549b; border: 1px solid #d0e4f7;">
                <span class="spinner-border spinner-border-sm me-2"></span>
                <span id="ffcu-prompt-text">{{ __('Waiting for Face ID / Touch ID...') }}</span>
            </div>

            <div id="ffcu-biometric-success" class="alert alert-success d-none py-2 px-3 small rounded-pill mb-3" style="font-size: 13px; font-weight: 600;">
                <i class="fas fa-check-circle me-1"></i> {{ __('Biometric Verified! Redirecting to Dashboard...') }}
            </div>

            <div id="biometric-error" class="error-text small mb-3"></div>

            <div class="trust-device-wrapper">
                <label class="custom-checkbox">
                    <input type="checkbox" name="trust_device_biometric" id="trust_device_biometric" checked>
                    {{ __('Trust this device') }}
                </label>
            </div>

            <div class="switch-link-wrapper mt-3">
                <a href="javascript:void(0)" class="forgot-link" onclick="switchMethod('pin')">
                    {{ __('Use Security Passcode instead') }}
                </a>
                <a href="javascript:void(0)" class="forgot-link" onclick="switchMethod('email')">
                    {{ __('Use Email Verification Code instead') }}
                </a>
                @if($user->security_preference == 'always_ask')
                <a href="javascript:void(0)" class="forgot-link" onclick="switchMethod('')">
                    {{ __('Back to Options') }}
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Passcode Verification Section -->
    <div id="pin-section" @if($user->security_preference == 'always_ask' || $method != 'pin') hidden @endif>
        <p class="text-secondary small mb-3">{{ __('Enter your 4-digit passcode to continue.') }}</p>

        <div class="pin-display-wrapper mb-3" id="pin-display">
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
        <p class="text-secondary small mb-1">{{ __('We\'ve sent a verification code to') }}</p>
        <p class="fw-bold text-dark small mb-3">{{ $maskedEmail }}</p>

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
    const biometricSection = document.getElementById('biometric-section');
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
        
        if (method === 'biometric') {
            if (pinSection) pinSection.hidden = true;
            if (emailSection) emailSection.hidden = true;
            if (biometricSection) biometricSection.hidden = false;
            triggerBiometricLogin();
        } else if (method === 'email') {
            const wasHidden = emailSection ? emailSection.hidden : true;
            if (pinSection) pinSection.hidden = true;
            if (biometricSection) biometricSection.hidden = true;
            if (emailSection) emailSection.hidden = false;
            // Auto-trigger send if switching to email for the first time or from choice
            if (wasHidden) {
                resendEmail();
            }
        } else if (method === 'pin') {
            if (biometricSection) biometricSection.hidden = true;
            if (emailSection) emailSection.hidden = true;
            if (pinSection) pinSection.hidden = false;
        } else {
            // Back to choice
            if (securityPreference === 'always_ask') {
                if (choiceSection) choiceSection.hidden = false;
                if (pinSection) pinSection.hidden = true;
                if (emailSection) emailSection.hidden = true;
                if (biometricSection) biometricSection.hidden = true;
            } else {
                if (pinSection && pinSection.hidden) {
                    switchMethod('pin');
                } else {
                    switchMethod('email');
                }
            }
        }
    }

    function triggerBiometricLogin() {
        const errorEl = document.getElementById('biometric-error');
        if (errorEl) errorEl.textContent = "";

        if (!window.PublicKeyCredential) {
            if (errorEl) errorEl.innerText = "Biometric authentication is not supported on this browser or device. Please use passcode or email.";
            return;
        }

        const btn = document.getElementById('btn-trigger-biometric');
        const origBtnText = btn ? btn.innerHTML : '';
        const ffcuPrompt = document.getElementById('ffcu-biometric-prompt');
        const ffcuPromptText = document.getElementById('ffcu-prompt-text');
        const ffcuSuccess = document.getElementById('ffcu-biometric-success');

        if (ffcuPrompt && ffcuPromptText) {
            ffcuPrompt.classList.remove('d-none');
            ffcuPromptText.innerText = "{{ __('Waiting for Face ID / Touch ID... Scan biometric sensor') }}";
        }
        if (ffcuSuccess) ffcuSuccess.classList.add('d-none');

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Waiting for Face ID / Touch ID...';
        }

        fetch("{{ route('login.verify.biometric.challenge') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status !== 'success') {
                throw new Error(data.message || 'Failed to initialize biometric challenge.');
            }

            const challengeBytes = new Uint8Array(data.challenge.match(/.{1,2}/g).map(byte => parseInt(byte, 16)));
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
            if (ffcuPromptText) {
                ffcuPromptText.innerText = "{{ __('Verifying biometric credential with server...') }}";
            }
            if (typeof window.showLoader === 'function') window.showLoader('Verifying biometric credential...');
            const trust = document.getElementById('trust_device_biometric') ? document.getElementById('trust_device_biometric').checked : true;

            return fetch("{{ route('login.verify.submit') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    type: 'biometric',
                    value: assertion ? assertion.id : 'passkey_authenticated',
                    trust_device: trust
                })
            });
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (ffcuPrompt) ffcuPrompt.classList.add('d-none');
                if (ffcuSuccess) {
                    ffcuSuccess.classList.remove('d-none');
                    if (btn) btn.style.display = 'none';
                    setTimeout(function() {
                        window.location.href = data.redirect || "{{ route('user.dashboard') }}";
                    }, 2000);
                } else {
                    window.location.href = data.redirect || "{{ route('user.dashboard') }}";
                }
            } else {
                if (ffcuPrompt) ffcuPrompt.classList.add('d-none');
                if (typeof window.hideLoader === 'function') window.hideLoader();
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origBtnText;
                }
                if (errorEl) errorEl.innerText = data.message || "Biometric verification failed. Please try Passcode or Email.";
            }
        })
        .catch(err => {
            if (ffcuPrompt) ffcuPrompt.classList.add('d-none');
            if (typeof window.hideLoader === 'function') window.hideLoader();
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origBtnText;
            }
            if (errorEl) {
                if (err.name === 'NotAllowedError') {
                    errorEl.innerText = "Biometric prompt was closed or timed out. Tap scan to retry or select another method.";
                } else {
                    errorEl.innerText = err.message || "Biometric authentication was cancelled. Choose Passcode or Email.";
                }
            }
        });
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

    // Auto-trigger on Page Load depending on starting method
    window.addEventListener('load', () => {
        const currentMethod = "{{ $method }}";
        if (currentMethod === 'email' && emailSection && !emailSection.hidden) {
            resendEmail();
        } else if (currentMethod === 'biometric' && biometricSection && !biometricSection.hidden) {
            setTimeout(() => {
                triggerBiometricLogin();
            }, 300);
        }
    });
</script>
@endpush
