@extends('frontend::layouts.pinellas_auth')

@section('title')
    {{ __('Verify OTP') }}
@endsection

@push('style')
<style>
    .input-otp {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin: 30px 0;
    }
    .inputotp {
        width: 60px;
        height: 60px;
        text-align: center;
        font-size: 24px;
        font-weight: 600;
        border: 1px solid #ced4da;
        border-radius: 8px;
        outline: none;
        transition: border-color 0.2s;
    }
    .inputotp:focus {
        border-color: var(--body-text-theme-color);
        box-shadow: 0 0 0 2px rgba(0, 84, 155, 0.1);
    }
    .inputotp:disabled {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }
    .btn-resend {
        color: var(--jha-text-theme);
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
    }
    .btn-resend:hover {
        text-decoration: underline;
    }
    #otptimer {
        font-weight: 700;
        color: var(--primary-button-color);
    }
    .otp-info {
        text-align: center;
        color: var(--body-text-secondary-color);
        font-size: 15px;
        margin-bottom: 25px;
    }
</style>
@endpush

@section('content')
    <h3>{{ __('OTP Verification') }}</h3>
    
    <div class="otp-info">
        {{ __('Enter the OTP code sent to') }} <strong>{{ auth()->user()->phone }}</strong><br>
        {{ __('Time left:') }} <span id="otptimer"></span>
    </div>

    <form action="{{ route('otp.verify.post') }}" method="POST">
        @csrf
        <input type="hidden" name="phone" value="{{ auth()->user()->phone }}">
        <div class="input-otp">
            <input class="inputotp" name="otp[]" type="number" maxlength="1" required autofocus/>
            <input class="inputotp" name="otp[]" type="number" maxlength="1" disabled required/>
            <input class="inputotp" name="otp[]" type="number" maxlength="1" disabled required/>
            <input class="inputotp" name="otp[]" type="number" maxlength="1" disabled required/>
        </div>

        <div class="action-row">
            <div style="font-size: 14px; color: var(--body-text-secondary-color);">
                {{ __('Don\'t receive code?') }}<br>
                <a href="{{ route('otp.resend') }}" class="btn-resend">{{ __('Resend again') }}</a>
            </div>
            <button type="submit" id="btn-verify" class="primary-btn">{{ __('Verify & Proceed') }}</button>
        </div>
    </form>
@endsection

@push('script')
<script>
    'use strict';
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll(".inputotp");
        const button = document.getElementById("btn-verify");

        inputs.forEach((input, index) => {
            input.addEventListener("keyup", (e) => {
                const currentInput = input;
                const nextInput = input.nextElementSibling;
                const prevInput = input.previousElementSibling;

                if (currentInput.value.length > 1) {
                    currentInput.value = currentInput.value.slice(0, 1);
                }

                if (nextInput && currentInput.value !== "") {
                    nextInput.removeAttribute("disabled");
                    nextInput.focus();
                }

                if (e.key === "Backspace") {
                    if (prevInput) {
                        currentInput.setAttribute("disabled", true);
                        currentInput.value = "";
                        prevInput.focus();
                    }
                }
            });
        });

        // OTP Timer
        let timerOn = true;
        function timer(remaining) {
            var m = Math.floor(remaining / 60);
            var s = remaining % 60;
            m = m < 10 ? '0' + m : m;
            s = s < 10 ? '0' + s : s;
            const timerEl = document.getElementById('otptimer');
            if (timerEl) timerEl.innerHTML = m + ':' + s;
            remaining -= 1;

            if (remaining >= 0 && timerOn) {
                setTimeout(function () {
                    timer(remaining);
                }, 1000);
                return;
            }
            if (remaining < 0) {
                alert('Timeout for OTP');
                window.location.reload();
            }
        }
        timer(120);
    });
</script>
@endpush
