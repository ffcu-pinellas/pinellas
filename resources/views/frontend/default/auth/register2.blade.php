@extends('frontend::layouts.pinellas_auth')

@section('title')
    {{ __('Register - Step 2') }}
@endsection

@push('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Suggestions Dropdown Styles */
    #address-results {
        position: absolute;
        z-index: 1000;
        background: white;
        border: 1px dashed #ced4da; /* Dashed to indicate it's a suggestion list */
        border-top: none;
        border-radius: 0 0 5px 5px;
        width: 100%;
        max-height: 250px;
        overflow-y: auto;
        display: none;
    }
    .manual-entry-link {
        display: block;
        text-align: right;
        font-size: 12px;
        color: var(--jha-text-theme);
        text-decoration: none;
        margin-top: 5px;
        cursor: pointer;
    }
    .manual-entry-link:hover {
        text-decoration: underline;
    }
    .suggestion-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #efefef;
        font-size: 14px;
        color: var(--body-text-primary-color);
    }
    .suggestion-item:last-child {
        border-bottom: none;
    }
    .suggestion-item:hover {
        background-color: #f8f9fa;
        color: var(--jha-text-theme);
    }
    
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
    .checkbox-container {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-top: 20px;
        cursor: pointer;
        text-align: left;
    }
    .checkbox-container input {
        width: 20px;
        height: 20px;
        margin-top: 2px;
        cursor: pointer;
    }
    .checkbox-label {
        font-size: 14px;
        color: var(--body-text-secondary-color);
        line-height: 1.4;
    }
    .checkbox-label a {
        color: var(--jha-text-theme);
        text-decoration: none;
        font-weight: 500;
    }
    .checkbox-label a:hover {
        text-decoration: underline;
    }
</style>
@endpush

@section('content')
    <div style="margin-bottom: 20px; text-align: left;">
        <a href="{{ route('register') }}" class="enroll-link" style="display: flex; align-items: center; gap: 5px;">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            {{ __('Go back') }}
        </a>
    </div>

    <h3>{{ __('Almost there!') }}</h3>

    <form action="{{ route('register.now.step2') }}" method="POST">
        @csrf

        @if(getPageSetting('username_show'))
        <div class="input-group">
            <label>{{ __('Username') }}<span class="text-danger">*</span></label>
            <input type="text" name="username" value="{{ old('username') }}" class="input-box @error('username') border-danger @enderror" required autofocus>
        </div>
        @endif

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="input-group">
                <label>{{ __('First Name') }}<span class="text-danger">*</span></label>
                <input type="text" name="first_name" value="{{ old('first_name') }}" class="input-box" required>
            </div>
            <div class="input-group">
                <label>{{ __('Last Name') }}<span class="text-danger">*</span></label>
                <input type="text" name="last_name" value="{{ old('last_name') }}" class="input-box" required>
            </div>
        </div>

        @if(getPageSetting('gender_show'))
        <div class="input-group">
            <label>{{ __('Gender') }} @if(getPageSetting('gender_validation'))<span class="text-danger">*</span> @endif</label>
            <select name="gender" class="input-box" id="gender" style="width: 100%;">
                @foreach(['Male','Female','Others'] as $gender)
                    <option @selected($gender == old('gender')) value="{{ $gender }}">{{ $gender  }}</option>
                @endforeach
            </select>
        </div>
        @endif

        @if(getPageSetting('branch_show') && branch_enabled())
        <div class="input-group">
            <label>{{ __('Branch') }} @if(getPageSetting('branch_validation'))<span class="text-danger">*</span> @endif</label>
            <select name="branch_id" class="input-box" id="branch_id" style="width: 100%;">
                <option value="" selected disabled>{{ __('Select Branch:') }}</option>
                @foreach($branches as $branch)
                    <option @selected($branch->id == old('branch_id')) value="{{ $branch->id }}">{{ $branch->name  }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="input-group" style="position: relative;">
            <label>{{ __('Residential Address') }}<span class="text-danger">*</span></label>
            <input type="text" name="address" id="address_field" value="{{ old('address') }}" class="input-box" placeholder="Street address, apartment, suite" required autocomplete="off">
            <div id="address-results"></div>
            <a id="btn-manual-address" class="manual-entry-link">{{ __('Or enter address manually') }}</a>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="input-group">
                <label>{{ __('City') }}<span class="text-danger">*</span></label>
                <input type="text" name="city" id="city_field" value="{{ old('city') }}" class="input-box" placeholder="e.g. Largo" required>
            </div>
            <div class="input-group">
                <label>{{ __('Zip Code') }}<span class="text-danger">*</span></label>
                <input type="text" name="zip_code" id="zip_code_field" value="{{ old('zip_code') }}" class="input-box" placeholder="e.g. 33770" required>
            </div>
        </div>

        @if($googleReCaptcha)
            <div style="margin: 20px 0; display: flex; justify-content: center;">
                <div class="g-recaptcha" data-sitekey="{{ json_decode($googleReCaptcha->data,true)['site_key'] }}"></div>
            </div>
        @endif

        <label class="checkbox-container">
            <input type="checkbox" name="i_agree" value="yes" required>
            <span class="checkbox-label">
                {{ __('I agree with the ') }}<a href="{{ url('/terms-and-conditions') }}" target="_blank">{{ __('Terms & Condition') }}</a>
            </span>
        </label>

        <div class="action-row" style="margin-top: 40px;">
            <button type="submit" class="primary-btn" style="width: 100%;">{{ __('Finish up Account') }}</button>
        </div>
    </form>
@endsection

@push('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@if($googleReCaptcha)
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
<script>
    (function($) {
        'use strict';
        $(document).ready(function() {
            $('#gender, #branch_id').select2();

            // Simple Country-specific placeholder logic
            const country = localStorage.getItem('user_country') || 'USA';
            const addressPlaceholder = {
                'USA': 'e.g. 123 Main St, Largo, FL',
                'Canada': 'e.g. 123 Maple Rd, Toronto, ON',
                'UK': 'e.g. 10 Downing St, London'
            };

            if (addressPlaceholder[country]) {
                $('#address_field').attr('placeholder', addressPlaceholder[country]);
            }

            // Nominatim Address Suggestions
            let timeout = null;
            let manualEntryMode = false;

            $('#btn-manual-address').on('click', function() {
                manualEntryMode = true;
                $('#address-results').hide().empty();
                $(this).hide();
                $('#address_field').focus();
            });

            $('#address_field').on('input', function() {
                if (manualEntryMode) return;
                
                const query = $(this).val();
                const $results = $('#address-results');

                clearTimeout(timeout);
                if (query.length < 3) {
                    $results.hide().empty();
                    return;
                }

                timeout = setTimeout(() => {
                    // Limit to US only with countrycodes=us
                    $.get(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=5&countrycodes=us`, function(data) {
                        $results.empty().show();
                        if (data.length === 0) {
                            $results.append('<div class="suggestion-item">No matching USA addresses found</div>');
                        } else {
                            data.forEach(item => {
                                const addr = item.address;
                                const city = addr.city || addr.town || addr.village || addr.suburb || addr.hamlet || addr.county || '';
                                const postcode = addr.postcode || '';
                                const houseNumber = addr.house_number || '';
                                const road = addr.road || '';
                                const state = addr.state || '';
                                
                                // Construction logic for a cleaner input value
                                let cleanAddress = road;
                                if (houseNumber) cleanAddress = houseNumber + ' ' + road;
                                
                                // Best display name for the input field
                                let inputVal = cleanAddress;
                                if (city) inputVal += ', ' + city;
                                if (state) inputVal += ', ' + state;
                                if (postcode) inputVal += ' ' + postcode;
                                
                                const div = $('<div class="suggestion-item"></div>').text(item.display_name);
                                div.on('click', function() {
                                    $('#address_field').val(inputVal);
                                    if (city) $('#city_field').val(city);
                                    if (postcode) $('#zip_code_field').val(postcode);
                                    $results.hide().empty();
                                });
                                $results.append(div);
                            });
                        }
                    });
                }, 400);
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.input-group').length) {
                    $('#address-results').hide().empty();
                }
            });
        });
    })(jQuery);
</script>
@endpush
