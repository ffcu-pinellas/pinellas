@extends('frontend::layouts.pinellas_auth')

@section('title')
    {{ __('Verify Email') }}
@endsection

@section('content')
    <h3>{{ __('Email Verification') }}</h3>
    
    <div style="text-align: center; color: var(--body-text-secondary-color); font-size: 15px; margin-bottom: 25px; line-height: 1.5;">
        {{ __('We have sent a verification link to your email address. Please check your inbox and follow the instructions to complete your registration.') }}
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="success-msg">
            {{ __('A new verification link has been sent to the email address you provided.') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <div class="action-row" style="justify-content: center; margin-top: 30px;">
            <button type="submit" class="primary-btn" style="width: 100%;">
                {{ __('Resend Verification Email') }}
            </button>
        </div>
    </form>

    <div style="text-align: center; margin-top: 30px;">
        <p style="font-size: 14px; color: var(--body-text-secondary-color);">
            {{ __('Already verified?') }} <a href="{{ route('login') }}" class="enroll-link" style="display: inline;">{{ __('Sign in here') }}</a>
        </p>
    </div>
@endsection
