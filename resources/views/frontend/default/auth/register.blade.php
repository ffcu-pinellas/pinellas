@extends('frontend::layouts.pinellas_auth')

@section('title')
    {{ __('Register') }}
@endsection

@push('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 56px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        display: flex;
        align-items: center;
        background-color: #f8fafc;
        transition: all 0.2s;
    }
    .select2-container--default .select2-selection--single:focus-within {
        border-color: #00549b;
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(0, 84, 155, 0.1);
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal;
        padding-left: 16px;
        color: #1e293b;
        font-weight: 500;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 54px;
        right: 10px;
    }
    .img-icon {
        width: 20px;
        margin-right: 10px;
        vertical-align: middle;
    }
    .input-group-custom {
        display: flex;
        border: 1px solid #ced4da;
        border-radius: 5px;
        overflow: hidden;
    }
    .input-group-text {
        background-color: #f8f9fa;
        padding: 0 15px;
        display: flex;
        align-items: center;
        border-right: 1px solid #ced4da;
        color: var(--body-text-secondary-color);
        font-size: 14px;
        font-weight: 500;
    }
    .input-group-custom input {
        border: none;
        flex: 1;
        padding: 14px 16px;
        font-size: 16px;
        outline: none;
    }
</style>
@endpush

@section('content')
    <h3>{{ data_get($data,'title',__('Create an account')) }}</h3>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="input-group">
            <label>{{ __('Email Address') }}<span class="text-danger">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}" class="input-box" required autofocus>
        </div>

        @if(getPageSetting('country_show'))
        <div class="input-group">
            <label>{{ __('Country') }} @if(getPageSetting('country_validation'))<span class="text-danger">*</span> @endif</label>
            <select name="country" class="input-box select2-basic-active" id="countrySelect" style="width: 100%;">
                @foreach( getCountries() as $country)
                    <option data-flag="https://flagcdn.com/48x36/{{ strtolower(data_get($country,'code')) }}.png" @selected($location->country_code == $country['code']) value="{{ $country['name'].':'.$country['dial_code'] }}" data-code="{{ $country['dial_code'] }}">{{ $country['name']  }}</option>
                @endforeach
            </select>
        </div>
        @endif

        @if(getPageSetting('phone_show'))
        <div class="input-group">
            <label>{{ __('Phone Number') }} @if(getPageSetting('phone_validation'))<span class="text-danger">*</span> @endif</label>
            <div class="input-group-custom">
                <span class="input-group-text" id="dial-code">{{ getLocation()->dial_code }}</span>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Enter phone number">
            </div>
        </div>
        @endif

        @if(getPageSetting('referral_code_show'))
        <div class="input-group">
            <label>{{ __('Referral Code') }} @if(getPageSetting('referral_code_validation'))<span class="text-danger">*</span> @endif</label>
            <input type="text" name="invite" value="{{ old('invite',$referralCode) }}" class="input-box" placeholder="Optional">
        </div>
        @endif

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="input-group">
                <label>{{ __('Date of Birth') }}<span class="text-danger">*</span></label>
                <div style="position: relative;">
                    <input type="text" name="date_of_birth" id="dob_field" value="{{ old('date_of_birth') }}" class="input-box" placeholder="MM/DD/YYYY" required>
                    <div style="position: absolute; right: 12px; top: 14px; pointer-events: none; color: var(--body-text-secondary-color);">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                </div>
            </div>
            <div class="input-group">
                <label>{{ __('SSN') }}<span class="text-danger">*</span></label>
                <input type="text" name="ssn" id="ssn_field" value="{{ old('ssn') }}" class="input-box" placeholder="XXX-XX-XXXX" maxlength="11" required>
            </div>
        </div>

        <div class="input-group">
            <label>{{ __('Create Password') }}<span class="text-danger">*</span></label>
            <div style="position: relative;">
                <input type="password" name="password" id="password_field" class="input-box" required>
                <button type="button" class="toggle-password" id="btn-toggle-pwd">
                    <svg id="eye-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;"><path d="M1 12s4-8 11-8 11-8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <svg id="eye-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px; display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                </button>
            </div>
        </div>

        <div class="action-row">
            <a href="{{ route('login') }}" class="enroll-link">{{ __('Already have an account?') }}<br>{{ __('Sign in here') }}</a>
            <button type="submit" class="primary-btn">{{ __('Next Step') }}</button>
        </div>
    </form>
@endsection

@push('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
<script>
    (function($) {
        'use strict';
        $(document).ready(function() {
            // Cleave.js masking
            if ($('#ssn_field').length) {
                new Cleave('#ssn_field', {
                    delimiters: ['-', '-'],
                    blocks: [3, 2, 4],
                    numericOnly: true
                });
            }

            if ($('#dob_field').length) {
                new Cleave('#dob_field', {
                    date: true,
                    datePattern: ['m', 'd', 'Y']
                });
            }

            // Password Toggle
            $('#btn-toggle-pwd').on('click', function() {
                const passField = $('#password_field');
                const eyeShow = $('#eye-show');
                const eyeHide = $('#eye-hide');
                
                if (passField.attr('type') === 'password') {
                    passField.attr('type', 'text');
                    eyeShow.hide();
                    eyeHide.show();
                } else {
                    passField.attr('type', 'password');
                    eyeShow.show();
                    eyeHide.hide();
                }
            });

            // Select 2 activation
            function formatState(state) {
                if (!state.id) return state.text;
                var $state = $('<span><img src="' + $(state.element).data('flag') + '" class="img-icon" /> ' + state.text + '</span>');
                return $state;
            };

            $('.select2-basic-active').select2({
                templateResult: formatState,
                templateSelection: formatState,
            });

            $('#countrySelect').on('change', function (e) {
                var country = $(this).val();
                var countryName = country.split(":")[0];
                $('#dial-code').html(country.split(":")[1]);
                localStorage.setItem('user_country', countryName);
            });
            // Init storage on load
            localStorage.setItem('user_country', $('#countrySelect').val().split(":")[0]);
        });
    })(jQuery);
</script>
@endpush
