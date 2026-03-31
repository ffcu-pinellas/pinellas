@extends('backend.auth.layouts.app')

@section('title')
    {{ __('Multi-Factor Security Verification') }}
@endsection

@push('style')
<style>
    .mfa-card-inner {
        text-align: center;
        width: 100%;
    }
    
    .login-card {
        padding: 25px !important;
        max-width: 400px;
        margin: 0 auto;
    }

    .logo-container {
        text-align: center;
        margin-bottom: 20px;
    }

    .logo-container img {
        max-width: 150px;
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
        width: 16px;
        height: 16px;
        border: 2px solid #003d73;
        border-radius: 50%;
        transition: all 0.2s ease;
    }
    .pin-dot.filled {
        background-color: #003d73;
        transform: scale(1.1);
    }

    /* Keypad */
    .keypad {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-bottom: 20px;
        max-width: 300px;
        margin-left: auto;
        margin-right: auto;
    }

    .key-btn {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 12px;
        font-size: 22px;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 55px;
        user-select: none;
        touch-action: manipulation;
    }

    .key-btn:hover {
        background: #e2e8f0;
        border-color: #cbd5e1;
    }

    .key-btn:active {
        background: #cbd5e1;
        transform: scale(0.95);
    }

    .key-btn.invisible {
        visibility: hidden;
    }

    .key-btn.backspace {
        color: #ef4444;
    }
    
    .key-btn.backspace svg {
        width: 28px;
        height: 28px;
        stroke-width: 2.5px;
    }

    .error-text {
        color: #ef4444;
        font-weight: 600;
        margin-top: 10px;
        min-height: 22px;
    }
    
    .admin-branding {
        background: #f1f5f9;
        border-radius: 12px;
        padding: 10px;
        margin-bottom: 20px;
    }

    .admin-name {
        color: #003d73;
        font-weight: 700;
        font-size: 1rem;
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
</style>
@endpush

@section('content')
<div class="mfa-card-inner" id="pin-section">
    
    <div class="logo-container">
         <img src="{{ asset(setting('site_logo','global')) }}" alt="{{ setting('site_title','global') }}">
    </div>

    <div class="admin-branding">
        <h5 class="admin-name mb-0 text-uppercase">{{ __('Security Check') }}</h5>
        <p class="small text-muted mb-0">{{ auth('admin')->user()->name }}</p>
    </div>
    
    <p class="text-secondary small mb-4">{{ __('Enter your 4-digit security passcode to access the admin panel.') }}</p>

    <div class="pin-display-wrapper mb-3" id="pin-display">
        <div class="pin-dot"></div>
        <div class="pin-dot"></div>
        <div class="pin-dot"></div>
        <div class="pin-dot"></div>
    </div>

    <div id="pin-error" class="error-text small mb-4"></div>

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

    <div class="mt-4">
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-link text-muted small">{{ __('Logout & Exit') }}</button>
        </form>
    </div>

    <form id="verify-form" action="{{ route('admin.security_gate.verify') }}" method="POST" hidden>
        @csrf
        <input type="hidden" name="passcode" id="passcode-input">
    </form>
</div>
@endsection

@push('script')
<script>
    "use strict";

    let currentPin = "";
    const pinSection = document.getElementById('pin-section');
    const errorEl = document.getElementById('pin-error');
    const inputEl = document.getElementById('passcode-input');
    const formEl = document.getElementById('verify-form');

    // Keypad Logic
    document.querySelectorAll('.key-btn[data-key]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentPin.length < 4) {
                currentPin += btn.dataset.key;
                updatePinDots();
                if (currentPin.length === 4) {
                   submitPin();
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

    function submitPin() {
        errorEl.textContent = "";
        inputEl.value = currentPin;
        formEl.submit();
    }

    // Auto-shake on error from session
    @if($errors->any())
        pinSection.classList.add('shake');
        errorEl.innerText = "{{ $errors->first() }}";
    @endif
</script>
@endpush
