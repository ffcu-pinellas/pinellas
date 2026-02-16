@extends('frontend::layouts.pinellas_auth')

@section('title')
    {{ __('Reset Password') }}
@endsection

@section('content')
    <h3>{{ __('Reset Password') }}</h3>
    
    <div style="text-align: center; color: var(--body-text-secondary-color); font-size: 15px; margin-bottom: 25px; line-height: 1.5;">
        {{ __('Please enter your new password below.') }}
    </div>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="input-group">
            <label for="email">{{ __('Email Address') }}<span class="text-danger">*</span></label>
            <input type="email" name="email" id="email" value="{{ old('email', $request->email) }}" class="input-box" required readonly>
        </div>

        <div class="input-group">
            <label for="password">{{ __('New Password') }}<span class="text-danger">*</span></label>
            <input type="password" name="password" id="password" class="input-box" required autofocus>
        </div>

        <div class="input-group">
            <label for="password_confirmation">{{ __('Confirm New Password') }}<span class="text-danger">*</span></label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="input-box" required>
        </div>

        <div class="action-row" style="margin-top: 30px;">
            <button type="submit" class="primary-btn" style="width: 100%;">{{ __('Update Password') }}</button>
        </div>
    </form>
@endsection
