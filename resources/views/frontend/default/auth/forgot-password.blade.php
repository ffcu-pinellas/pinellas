@extends('frontend::layouts.pinellas_auth')

@section('title')
    {{ __('Forgot Password') }}
@endsection

@section('content')
    <h3>{{ $data['title'] ?? __('Forgot Password') }}</h3>
    
    <div style="text-align: center; color: var(--body-text-secondary-color); font-size: 15px; margin-bottom: 25px; line-height: 1.5;">
        {{ __('Enter your registered email address or username to receive password reset instructions.') }}
    </div>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="input-group">
            <label for="email">{{ __('Username or Email') }}<span class="text-danger">*</span></label>
            <input type="text" name="email" id="email" value="{{ old('email') }}" class="input-box" 
                   placeholder="Enter your email or username" required autofocus>
        </div>

        <div class="action-row" style="margin-top: 30px;">
            <a href="{{ route('login') }}" class="enroll-link">{{ __('Remember your password?') }}<br>{{ __('Sign in here') }}</a>
            <button type="submit" class="primary-btn">{{ __('Send Reset Link') }}</button>
        </div>
    </form>
@endsection
