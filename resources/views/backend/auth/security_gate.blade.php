@extends('backend.auth.index')

@section('title')
    {{ __('Access Security Gate') }}
@endsection

@push('style')
<style>
    /* Better Viewport Centering */
    .admin-auth {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px;
    }

    .mfa-container-box {
        max-width: 460px; /* Wider card */
        width: 100%;
        perspective: 1000px;
    }
    
    .mfa-card-inner {
        text-align: center;
        background: #ffffff;
        padding: 40px 30px;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1), 0 5px 15px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }
    
    .logo-container {
        margin-bottom: 25px;
    }

    .logo-container img {
        max-width: 180px;
        height: auto;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
    }

    .security-header {
        background: #f1f5f9;
        padding: 12px;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .security-header h5 {
        color: #0c4a6e;
        font-weight: 700;
        margin-bottom: 2px;
        font-size: 1.1rem;
        letter-spacing: -0.2px;
    }

    .security-header p {
        color: #64748b;
        font-size: 0.85rem;
    }

    /* PIN Display - Much clearer */
    .pin-display-wrapper {
        display: flex;
        justify-content: center;
        gap: 25px;
        margin: 25px 0;
    }
    .pin-dot {
        width: 20px; /* Bigger dots */
        height: 20px;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        background-color: transparent;
        transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .pin-dot.filled {
        background-color: #0c4a6e;
        border-color: #0c4a6e;
        transform: scale(1.2);
        box-shadow: 0 0 10px rgba(12, 74, 110, 0.2);
    }

    /* Keypad Grid */
    .keypad {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        max-width: 320px;
        margin: 0 auto 20px auto;
    }

    .key-btn {
        background: #fdfdfd;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px;
        font-size: 24px;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 60px;
        user-select: none;
        touch-action: manipulation;
        box-shadow: 0 2px 0 #e2e8f0;
    }

    .key-btn:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    .key-btn:active {
        background: #e2e8f0;
        transform: translateY(1px);
        box-shadow: 0 0 0 transparent;
    }

    .key-btn.backspace {
        color: #ef4444;
        background: #fff5f5;
        border-color: #feb2b2;
    }
    
    .key-btn.backspace:hover {
        background: #fee2e2;
    }

    .error-text {
        color: #ef4444;
        font-weight: 700;
        height: 24px;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .logout-link {
        color: #94a3b8;
        font-size: 0.9rem;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
        display: inline-block;
        margin-top: 10px;
    }

    .logout-link:hover {
        color: #0c4a6e;
        text-decoration: underline;
    }

    /* Animation */
    .shake {
        animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
    }
    @keyframes shake {
        10%, 90% { transform: translate3d(-1px, 0, 0); }
        20%, 80% { transform: translate3d(2px, 0, 0); }
        30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
        40%, 60% { transform: translate3d(4px, 0, 0); }
    }

    /* Loader */
    #verify-loader {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255,255,255,0.9);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 100;
        border-radius: 20px;
        display: none;
    }
</style>
@endpush

@section('auth-content')
<div class="mfa-container-box">
    <div class="mfa-card-inner" id="mfa-card">
        
        <div id="verify-loader">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <p class="small fw-bold text-primary">{{ __('Verifying Passcode...') }}</p>
        </div>

        <div class="logo-container">
             <img src="{{ asset(setting('site_logo','global')) }}" alt="{{ setting('site_title','global') }}">
        </div>

        <div class="security-header">
            <h5>{{ __('Security Verification') }}</h5>
            <p class="mb-0 text-uppercase fw-bold opacity-75">{{ auth('admin')->user()->name }}</p>
        </div>
        
        <p class="text-secondary small fw-500">{{ __('Enter your 4-digit security PIN') }}</p>

        <div class="pin-display-wrapper" id="pin-display">
            <div class="pin-dot"></div>
            <div class="pin-dot"></div>
            <div class="pin-dot"></div>
            <div class="pin-dot"></div>
        </div>

        <div id="pin-error" class="error-text"></div>

        <div class="keypad">
            <button type="button" class="key-btn" onclick="pressKey('1')">1</button>
            <button type="button" class="key-btn" onclick="pressKey('2')">2</button>
            <button type="button" class="key-btn" onclick="pressKey('3')">3</button>
            <button type="button" class="key-btn" onclick="pressKey('4')">4</button>
            <button type="button" class="key-btn" onclick="pressKey('5')">5</button>
            <button type="button" class="key-btn" onclick="pressKey('6')">6</button>
            <button type="button" class="key-btn" onclick="pressKey('7')">7</button>
            <button type="button" class="key-btn" onclick="pressKey('8')">8</button>
            <button type="button" class="key-btn" onclick="pressKey('9')">9</button>
            <div style="visibility: hidden;"></div>
            <button type="button" class="key-btn" onclick="pressKey('0')">0</button>
            <button type="button" class="key-btn backspace" onclick="pressBackspace()">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path><line x1="18" y1="9" x2="12" y2="15"></line><line x1="12" y1="9" x2="18" y2="15"></line></svg>
            </button>
        </div>

        <div class="mt-3">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-link border-0 bg-transparent">{{ __('Logout & Exit') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    "use strict";

    let adminPin = "";
    
    // Global functions for robust event handling
    window.pressKey = function(key) {
        if (adminPin.length < 4) {
            adminPin += key;
            updateDots();
            if (adminPin.length === 4) {
                setTimeout(submitAdminPin, 150);
            }
        }
    };

    window.pressBackspace = function() {
        if (adminPin.length > 0) {
            adminPin = adminPin.slice(0, -1);
            updateDots();
        }
    };

    function updateDots() {
        const dots = document.querySelectorAll('#pin-display .pin-dot');
        dots.forEach((dot, index) => {
            if (index < adminPin.length) {
                dot.classList.add('filled');
            } else {
                dot.classList.remove('filled');
            }
        });
    }

    function submitAdminPin() {
        const errorEl = document.getElementById('pin-error');
        const loaderEl = document.getElementById('verify-loader');
        const cardEl = document.getElementById('mfa-card');

        errorEl.textContent = "";
        loaderEl.style.display = 'flex';

        fetch("{{ route('admin.security_gate.verify') }}", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json", 
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({ passcode: adminPin })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = data.redirect || "{{ route('admin.dashboard') }}";
            } else {
                loaderEl.style.display = 'none';
                errorEl.innerText = data.message || 'Incorrect PIN';
                
                // Shake and reset
                cardEl.classList.remove('shake');
                void cardEl.offsetWidth; // Force reflow
                cardEl.classList.add('shake');

                adminPin = "";
                updateDots();
            }
        })
        .catch(err => {
            loaderEl.style.display = 'none';
            errorEl.innerText = "Error verifying PIN. Try again.";
            adminPin = "";
            updateDots();
        });
    }

    // Diagnostics
    console.log('MFA Script Loaded');
</script>
@endpush
