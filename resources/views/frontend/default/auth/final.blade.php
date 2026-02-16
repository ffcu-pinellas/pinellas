@extends('frontend::layouts.pinellas_auth')

@section('title')
    {{ __('Success') }}
@endsection

@section('content')
    <div style="text-align: center;">
        <div style="margin-bottom: 30px;">
            <svg viewBox="0 0 24 24" width="80" height="80" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>

        <h3 style="color: var(--body-text-primary-color); margin-bottom: 20px;">
            @if(setting('referral_signup_bonus','permission'))
                {{ __('Congratulations! You have earned :bonus by signing up.',['bonus' => $currencySymbol.setting('signup_bonus','fee')]) }}
            @else
                {{ __('Congratulations! Your account has been successfully created.') }}
            @endif
        </h3>
        
        <p style="color: var(--body-text-secondary-color); font-size: 16px; margin-bottom: 40px; line-height: 1.5;">
            {{ __('Welcome to Pinellas Federal Credit Union. You can now access your dashboard and manage your finances with ease.') }}
        </p>

        <div class="action-row" style="justify-content: center;">
            <a href="{{ route('user.dashboard') }}" class="primary-btn" style="width: 100%; text-decoration: none;">
                {{ __('Go to Dashboard') }}
            </a>
        </div>
    </div>
@endsection

@push('script')
<script type="text/javascript" src="{{ asset('front/js/confetti.min.js') }}"></script>
<script>
    'use strict';
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof confetti !== 'undefined') {
            setTimeout(function() {
                confetti.start();
                setTimeout(function() {
                    confetti.stop();
                }, 5000);
            }, 500);
        }
    });
</script>
@endpush
