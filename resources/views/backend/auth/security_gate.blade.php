@extends('backend.auth.index')

@section('title')
    {{ __('Security Gate') }}
@endsection

@push('style')
<style>
    .login-content {
        max-width: 400px;
        margin: 0 auto;
        padding: 20px 10px; /* Reduced padding */
    }
    
    .mfa-card-inner {
        text-align: center;
        width: 100%;
        background: #fff;
        padding: 25px 20px; /* Reduced padding */
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }
    
    .logo-container {
        text-align: center;
        margin-bottom: 15px; /* Reduced margin */
    }

    .logo-container img {
        max-width: 130px; /* Smaller logo */
        height: auto;
    }

    /* PIN Dots */
    .pin-display-wrapper {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-bottom: 20px;
    }
    .pin-dot {
        width: 12px;
        height: 12px;
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
        gap: 10px; /* Compact gap */
        margin-bottom: 15px;
        max-width: 260px; /* More compact */
        margin-left: auto;
        margin-right: auto;
    }

    .key-btn {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 5px;
        font-size: 20px;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 48px; /* Compact height */
        user-select: none;
        touch-action: manipulation;
    }

    .key-btn:hover {
        background: #e2e8f0;
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
        width: 22px;
        height: 22px;
    }

    .error-text {
        color: #ef4444;
        font-weight: 700;
        margin-top: 5px;
        min-height: 20px;
        font-size: 13px;
    }
    
    .admin-branding {
        background: #f8fafc;
        border-radius: 10px;
        padding: 8px;
        margin-bottom: 15px;
    }

    .admin-name {
        color: #003d73;
        font-weight: 700;
        font-size: 0.95rem;
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

    /* Loading Overlay */
    #verify-loader {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255,255,255,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100;
        border-radius: 12px;
        display: none;
    }
</style>
@endpush

@section('auth-content')
<div class="login-content">
    <div class="mfa-card-inner" id="pin-section" style="position: relative;">
        
        <div id="verify-loader">
            <div class="spinner-border text-primary" role="status"></div>
        </div>

        <div class="logo-container">
             <img src="{{ asset(setting('site_logo','global')) }}" alt="{{ setting('site_title','global') }}">
        </div>

        <div class="admin-branding">
            <h5 class="admin-name mb-0">{{ __('Security Check') }}</h5>
            <p class="small text-muted mb-0">{{ auth('admin')->user()->name }}</p>
        </div>
        
        <p class="text-secondary small mb-3">{{ __('Enter your 4-digit passcode') }}</p>

        <div class="pin-display-wrapper mb-2" id="pin-display">
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

        <div class="mt-2">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-link text-muted small py-0" style="text-decoration: none;">{{ __('Logout & Exit') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    "use strict";

    let currentPin = "";
    const pinSection = document.getElementById('pin-section');
    const errorEl = document.getElementById('pin-error');
    const loaderEl = document.getElementById('verify-loader');

    // Keypad Logic
    document.querySelectorAll('.key-btn[data-key]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentPin.length < 4) {
                currentPin += btn.dataset.key;
                updatePinDots();
                if (currentPin.length === 4) {
                    verifyAdminPin();
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

    function verifyAdminPin() {
        errorEl.textContent = "";
        loaderEl.style.display = 'flex';

        fetch("{{ route('admin.security_gate.verify') }}", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json", 
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({ passcode: currentPin })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = data.redirect || "{{ route('admin.dashboard') }}";
            } else {
                loaderEl.style.display = 'none';
                errorEl.innerText = data.message || 'Incorrect passcode';
                
                // Shake animation
                pinSection.classList.remove('shake');
                void pinSection.offsetWidth;
                pinSection.classList.add('shake');

                // Reset PIN
                currentPin = "";
                updatePinDots();
            }
        })
        .catch(err => {
            loaderEl.style.display = 'none';
            errorEl.innerText = "Verification failed. Please try again.";
            currentPin = "";
            updatePinDots();
        });
    }
</script>
@endpush
