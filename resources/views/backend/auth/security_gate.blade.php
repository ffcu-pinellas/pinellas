@extends('backend.auth.index')

@section('title')
    {{ __('Access Security Gate') }}
@endsection

@section('style')
<style>
    /* Better Viewport Centering */
    .admin-auth {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    }

    .mfa-container-box {
        max-width: 520px; /* Wider card to reduce side space */
        width: 100%;
        margin: 0 auto;
    }
    
    .mfa-card-inner {
        text-align: center;
        background: #ffffff;
        padding: 45px 35px;
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        position: relative;
    }
    
    .logo-container {
        margin-bottom: 30px;
    }

    .logo-container img {
        max-width: 200px; /* Slightly larger logo */
        height: auto;
    }

    .security-header {
        background: #f8fafc;
        padding: 15px;
        border-radius: 16px;
        margin-bottom: 25px;
        border: 1px solid #e2e8f0;
    }

    .security-header h5 {
        color: #0f172a;
        font-weight: 800;
        margin-bottom: 4px;
        font-size: 1.25rem;
    }

    .security-header p {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }

    /* PIN Display */
    .pin-display-wrapper {
        display: flex;
        justify-content: center;
        gap: 25px;
        margin: 30px 0;
    }
    .pin-dot {
        width: 22px;
        height: 22px;
        border: 2.5px solid #e2e8f0;
        border-radius: 50%;
        background-color: transparent;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .pin-dot.filled {
        background-color: #003d73;
        border-color: #003d73;
        transform: scale(1.25);
        box-shadow: 0 0 15px rgba(0, 61, 115, 0.3);
    }

    /* Keypad Grid */
    .keypad {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        max-width: 340px;
        margin: 0 auto 30px auto;
    }

    .key-btn {
        background: #fdfdfd;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 15px;
        font-size: 26px;
        font-weight: 700;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 65px;
        user-select: none;
        touch-action: manipulation;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .key-btn:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .key-btn:active {
        background: #e2e8f0;
        transform: translateY(0);
    }

    .key-btn.backspace {
        color: #ef4444;
        background: #fff5f5;
    }

    .error-text {
        color: #ef4444;
        font-weight: 700;
        height: 30px;
        font-size: 15px;
        margin-bottom: 10px;
    }

    .logout-link {
        color: #64748b;
        font-size: 1rem;
        text-decoration: none;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 12px;
        transition: all 0.2s;
    }

    .logout-link:hover {
        background: #f1f5f9;
        color: #0f172a;
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
        background: rgba(255,255,255,0.95);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 100;
        border-radius: 24px;
        display: none;
    }
</style>
@endsection

@section('auth-content')
<div class="mfa-container-box">
    <div class="mfa-card-inner" id="mfa-card">
        
        <div id="verify-loader">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
            <p class="h6 fw-bold text-primary">{{ __('Securing Connection...') }}</p>
        </div>

        <div class="logo-container">
             <img src="{{ asset(setting('site_logo','global')) }}" alt="{{ setting('site_title','global') }}">
        </div>

        <div class="security-header">
            <h5 class="text-uppercase">{{ __('Security Check') }}</h5>
            <p class="mb-0 text-primary">{{ auth('admin')->user()->name }}</p>
        </div>
        
        <p class="text-secondary fw-bold">{{ __('Enter your 4-digit security PIN') }}</p>

        <div class="pin-display-wrapper" id="pin-display">
            <div class="pin-dot"></div>
            <div class="pin-dot"></div>
            <div class="pin-dot"></div>
            <div class="pin-dot"></div>
        </div>

        <div id="pin-error" class="error-text"></div>

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
            <div style="visibility: hidden;"></div>
            <button type="button" class="key-btn" data-key="0">0</button>
            <button type="button" class="key-btn backspace" id="btn-backspace">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path><line x1="18" y1="9" x2="12" y2="15"></line><line x1="12" y1="9" x2="18" y2="15"></line></svg>
            </button>
        </div>

        <div class="mt-4">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-link border-0 bg-transparent">{{ __('Logout & Exit') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    "use strict";

    (function() {
        let adminPinValue = "";
        
        function initMFA() {
            const keypadButtons = document.querySelectorAll('.key-btn[data-key]');
            const backspaceButton = document.getElementById('btn-backspace');

            keypadButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const key = this.getAttribute('data-key');
                    if (adminPinValue.length < 4) {
                        adminPinValue += key;
                        updateDotsVisuals();
                        if (adminPinValue.length === 4) {
                            setTimeout(submitMFA, 200);
                        }
                    }
                });
            });

            if (backspaceButton) {
                backspaceButton.addEventListener('click', function() {
                    if (adminPinValue.length > 0) {
                        adminPinValue = adminPinValue.slice(0, -1);
                        updateDotsVisuals();
                    }
                });
            }
        }

        function updateDotsVisuals() {
            const dots = document.querySelectorAll('#pin-display .pin-dot');
            dots.forEach((dot, index) => {
                if (index < adminPinValue.length) {
                    dot.classList.add('filled');
                } else {
                    dot.classList.remove('filled');
                }
            });
        }

        function submitMFA() {
            const errorEl = document.getElementById('pin-error');
            const loaderEl = document.getElementById('verify-loader');
            const cardEl = document.getElementById('mfa-card');

            if (errorEl) errorEl.textContent = "";
            if (loaderEl) loaderEl.style.display = 'flex';

            fetch("{{ route('admin.security_gate.verify') }}", {
                method: "POST",
                headers: { 
                    "Content-Type": "application/json", 
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ passcode: adminPinValue })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = data.redirect || "{{ route('admin.dashboard') }}";
                } else {
                    if (loaderEl) loaderEl.style.display = 'none';
                    if (errorEl) errorEl.innerText = data.message || 'Incorrect security PIN';
                    
                    if (cardEl) {
                        cardEl.classList.remove('shake');
                        void cardEl.offsetWidth; 
                        cardEl.classList.add('shake');
                    }

                    adminPinValue = "";
                    updateDotsVisuals();
                }
            })
            .catch(err => {
                if (loaderEl) loaderEl.style.display = 'none';
                if (errorEl) errorEl.innerText = "Connection error. Please try again.";
                adminPinValue = "";
                updateDotsVisuals();
            });
        }

        // Initialize on load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMFA);
        } else {
            initMFA();
        }
    })();
</script>
@endsection
