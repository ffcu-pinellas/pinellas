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
                <button type="button" id="btn-continue" class="primary-btn">Continue</button>
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
                <button type="button" class="biometric-btn">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.81 4.47c-.08 0-.16-.02-.23-.06C15.66 3.42 14 3 12.01 3c-1.98 0-3.66.42-4.57.5-3.03.3-5.32 2.62-5.32 5.65 0 .41.34.75.75.75s.75-.34.75-.75c0-2.31 1.76-4.14 4.07-4.37.75-.07 2.37-.48 4.32-.48 1.7 0 3.12.35 4.6 1.25.35.21.81.1.1.45.65.21-.24.16-.54.08-.74zm2.19 5.64c-.18 0-.36-.06-.51-.19-.32-.26-.37-.73-.11-1.05.74-.91 1.12-2.11 1.12-3.37 0-1.87-.79-3.7-2.16-5.02-.3-.29-.31-.76-.02-1.06.29-.3.77-.31 1.07-.02C21.12 1.05 22 3.13 22 5.5c0 1.63-.48 3.18-1.44 4.35-.15.18-.36.26-.56.26zM5.5 11c-.13 0-.27-.03-.4-.1-.36-.19-.5-.63-.31-.99 1-1.91 2.97-3.41 5.22-4 2.25-.59 4.74-.35 6.74.65.37.19.52.64.33 1.01-.19.37-.64.51-1.01.32-1.67-.84-3.71-1.04-5.59-.55-1.87.49-3.51 1.73-4.34 3.32-.12.22-.35.34-.64.34zM12 21c-.41 0-.75-.34-.75-.75V11c0-.41.34-.75.75-.75s.75.34.75.75v9.25c0 .41-.34.75-.75.75zM12 10c-.41 0-.75-.34-.75-.75s.34-.75.75-.75 2.5.9 2.5 4.5v1.25c0 .41-.34.75-.75.75s-.75-.34-.75-.75V13c0-2.43-1-3-1-3z"/>
                    </svg>
                    Sign in with a passkey
                </button>
                <button type="submit" class="primary-btn">Sign in</button>
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
                    stepUsername.hidden = true;
                    stepPassword.hidden = false;
                    passwordField.focus();
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
                stepPassword.hidden = true;
                stepUsername.hidden = false;
                usernameField.focus();
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
        });
    </script>
@endpush
