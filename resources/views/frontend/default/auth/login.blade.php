@extends('frontend::layouts.pinellas_auth')

@section('title')
    {{ __('Login') }}
@endsection

@section('content')
    <!-- Step 1: Username -->
    <div id="step-username" @if(request('email') || $errors->any()) hidden @endif>
        <form id="username-form">
            <div class="input-group">
                <label for="username_field">Username</label>
                <input type="text" id="username_field" class="input-box" autocomplete="username" autofocus>
            </div>
            
            <div style="text-align: right; margin-top: -10px;">
                <a href="{{ route('password.request') }}" class="forgot-link">Forgot?</a>
            </div>

            <div class="action-row">
                <a href="{{ route('register') }}" class="enroll-link">First time user?<br>Enroll now.</a>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="biometric-btn d-none" id="btn-biometric-login-step1" title="Sign in with Biometrics">
                        <i class="fas fa-fingerprint" style="font-size: 24px;"></i>
                    </button>
                    <button type="button" id="btn-continue" class="primary-btn">Continue</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Step 2: Password -->
    <div id="step-password" @if(!request('email') && !$errors->any()) hidden @endif>
        <div class="user-preview">
            <div class="u-info">
                <span class="u-label">Username</span>
                <span class="u-val" id="display-username">{{ request('email') ?? (old('email') ?? '') }}</span>
            </div>
            <a href="javascript:void(0)" id="link-switch" class="switch-link">Switch</a>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <input type="hidden" name="email" id="final-email" value="{{ request('email') ?? (old('email') ?? '') }}">
            
            <div class="input-group">
                <label for="password_field">Enter your password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password_field" class="input-box" autocomplete="current-password" required>
                    <button type="button" class="toggle-password" id="btn-toggle-pwd" aria-label="Toggle password visibility">
                        <svg id="eye-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;"><path d="M1 12s4-8 11-8 11-8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg id="eye-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px; display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: var(--body-text-secondary-color);">
                    <input type="checkbox" name="remember" style="width: 18px; height: 18px; cursor: pointer;">
                    {{ __('Remember Me') }}
                </label>
                <a href="{{ route('password.request') }}" class="forgot-link">Forgot?</a>
            </div>

            @if(isset($googleReCaptcha) && $googleReCaptcha)
                <div style="margin: 20px 0; display: flex; justify-content: center;">
                    <div class="g-recaptcha" data-sitekey="{{ json_decode($googleReCaptcha->data,true)['site_key'] }}"></div>
                </div>
            @endif

            <div class="action-row">
                <button type="submit" class="primary-btn w-100">Sign in</button>
            </div>
        </form>
    </div>
@endsection

@push('script')
    @if(isset($googleReCaptcha) && $googleReCaptcha)
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stepUsername = document.getElementById('step-username');
            const stepPassword = document.getElementById('step-password');
            const btnContinue = document.getElementById('btn-continue');
            const usernameField = document.getElementById('username_field');
            const passwordField = document.getElementById('password_field');
            const displayUsername = document.getElementById('display-username');
            const finalEmail = document.getElementById('final-email');
            const linkSwitch = document.getElementById('link-switch');
            const btnTogglePwd = document.getElementById('btn-toggle-pwd');
            const eyeShow = document.getElementById('eye-show');
            const eyeHide = document.getElementById('eye-hide');

            // Handle Continue button
            btnContinue.addEventListener('click', function() {
                const username = usernameField.value.trim();
                if (username) {
                    displayUsername.textContent = username;
                    finalEmail.value = username;
                    
                    // Animation: Slide out left
                    stepUsername.style.animation = 'fadeOut 0.3s ease-in forwards';
                    setTimeout(() => {
                        stepUsername.hidden = true;
                        stepPassword.hidden = false;
                        stepPassword.style.animation = 'fadeInUp 0.5s ease-out';
                        passwordField.focus();
                    }, 300);
                } else {
                    usernameField.reportValidity();
                }
            });

            // Handle Enter key on username field
            usernameField.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    btnContinue.click();
                }
            });

            // Handle Switch user
            linkSwitch.addEventListener('click', function() {
                // Animation: Slide out
                stepPassword.style.animation = 'fadeOut 0.2s ease-in forwards';
                setTimeout(() => {
                    stepPassword.hidden = true;
                    stepUsername.hidden = false;
                    stepUsername.style.animation = 'fadeInUp 0.4s ease-out';
                    usernameField.focus();
                }, 200);
            });

            // Handle Password Toggle
            btnTogglePwd.addEventListener('click', function() {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    eyeShow.style.display = 'none';
                    eyeHide.style.display = 'block';
                } else {
                    passwordField.type = 'password';
                    eyeShow.style.display = 'block';
                    eyeHide.style.display = 'none';
                }
            });

            // Initial Biometric UI check: Show if available, even if not enrolled
            async function showBioButton() {
                if (window.PinellasBiometrics) {
                    await window.PinellasBiometrics.init();
                    if (window.PinellasBiometrics.isAvailable) {
                        const btn = document.getElementById('btn-biometric-login-step1');
                        btn.classList.remove('d-none');
                        
                        // If not enrolled, dim it slightly or show it as an "off" state
                        if (localStorage.getItem('biometrics_enrolled') !== 'true') {
                            btn.style.opacity = '0.5';
                        }
                    }
                }
            }

            document.getElementById('btn-biometric-login-step1').addEventListener('click', () => {
                if (localStorage.getItem('biometrics_enrolled') === 'true') {
                    window.PinellasBiometrics.challenge();
                } else {
                    // Call to Action
                    if (confirm("Biometric Login is available for your device! Would you like to enable it now? You can do this in Security Settings after signing in.")) {
                        // Just a reminder, they need to sign in first
                    }
                }
            });

            showBioButton();
        });
    </script>
@endpush
