@extends('frontend::layouts.pinellas_auth')

@section('title')
    {{ __('2FA Security') }}
@endsection

@section('content')
    <h3>{{ __('2FA Security') }}</h3>
    
    <div style="text-align: center; color: var(--body-text-secondary-color); font-size: 15px; margin-bottom: 25px; line-height: 1.5;">
        {{ __('Please enter the') }} <strong>{{ __('OTP') }}</strong> {{ __('generated on your Authenticator App.') }}<br>
        <span style="font-size: 13px;">{{ __('The code refreshes every 30 seconds.') }}</span>
    </div>

    <form method="POST" action="{{ route('user.setting.2fa.verify') }}">
        @csrf
        <div class="input-group">
            <label for="one_time_password">{{ __('One Time Password') }}</label>
            <input type="text" name="one_time_password" id="one_time_password" class="input-box" 
                   placeholder="123456" autofocus required autocomplete="one-time-code" maxlength="6">
        </div>

        <div class="action-row" style="justify-content: center;">
            <button type="submit" class="primary-btn" style="width: 100%;">
                {{ __('Authenticate Now') }}
            </button>
        </div>
    </form>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ route('home') }}" class="enroll-link" style="font-size: 13px;">{{ __('Cancel and return to home') }}</a>
    </div>
@endsection
