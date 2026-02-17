@extends('frontend::layouts.user')
@section('title')
    {{ __('Personal Settings') }}
@endsection
@section('content')
<div class="row">
    @include('frontend::user.setting.include.__settings_nav')
    <div class="col-xl-9 col-lg-8 col-md-12 col-12">
        <div class="site-card profile-details-view">
            <div class="site-card-body">
                <div class="profile-header-banno">
                    <div class="avatar-edit-wrapper" style="position: relative;">
                         <img src="{{ $user->avatar ? asset($user->avatar) : asset('front/images/user.png') }}" class="profile-avatar-large" alt="Avatar">
                         <a href="javascript:void(0)" onclick="$('#avatar_input').click()" style="position: absolute; bottom: 0; right: 0; background: var(--body-text-theme-color); color: #fff; padding: 4px; border-radius: 50%;"><i data-lucide="camera" style="width: 16px; height: 16px;"></i></a>
                    </div>
                    <div class="profile-name-info">
                        <h2>{{ $user->full_name }}</h2>
                        <span class="member-number-masked">Member Since {{ \Carbon\Carbon::parse($user->created_at)->format('M Y') }} • Account #{{ substr($user->account_number, 0, 4) . '****' . substr($user->account_number, -4) }}</span>
                    </div>
                </div>

                <div class="profile-data-grid">
                    <div class="profile-data-item">
                        <label>{{ __('User Name') }}</label>
                        <span>{{ $user->username }}</span>
                    </div>
                    <div class="profile-data-item">
                        <label>{{ __('Gender') }}</label>
                        <span>{{ ucfirst($user->gender) ?: 'Not Specified' }}</span>
                    </div>
                    <div class="profile-data-item">
                        <label>{{ __('Date of Birth') }}</label>
                        <span>{{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') : 'Not Provided' }}</span>
                    </div>
                    <div class="profile-data-item">
                        <label>{{ __('Phone') }}</label>
                        <span>{{ $user->phone }}</span>
                    </div>
                    <div class="profile-data-item" style="grid-column: span 2;">
                        <label>{{ __('Address') }}</label>
                        <span>{{ $user->address }}, {{ $user->city }}, {{ $user->zip_code }}, {{ $user->country }}</span>
                    </div>
                </div>

                <hr class="mt-4 mb-4">

                <div class="settings-actions">
                    <h5 class="mb-3" style="font-weight: 700; color: var(--body-text-primary-color);">{{ __('Privacy & Display') }}</h5>
                    <div class="banno-toggle">
                        <div class="banno-toggle-info">
                            <h6>{{ __('Show running balance') }}</h6>
                            <p>{{ __('Show the current balance for each account on the dashboard.') }}</p>
                        </div>
                        <div class="banno-switch active" onclick="$(this).toggleClass('active')"></div>
                    </div>
                    <div class="banno-toggle">
                        <div class="banno-toggle-info">
                            <h6>{{ __('Quick Login') }}</h6>
                            <p>{{ __('Enable biometric or PIN login on mobile devices.') }}</p>
                        </div>
                        <div class="banno-switch" onclick="$(this).toggleClass('active')"></div>
                    </div>
                </div>
                
                <div class="mt-4 pt-3">
                     <button type="button" class="site-btn-sm primary-theme-btn" onclick="$('#edit_profile_form').slideToggle()">{{ __('Edit Profile Information') }}</button>
                </div>

                <div id="edit_profile_form" style="display: none;" class="mt-4 pt-4 border-top">
                    <!-- Update Form -->
                    <form action="{{ route('user.setting.profile-update') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="avatar" id="avatar_input" style="display: none;" accept=".jpg,.png,.jpeg">
                        <div class="row">
                            <div class="col-xl-6 col-md-12">
                                <div class="inputs">
                                    <label class="form-label">{{ __('First Name') }}</label>
                                    <input type="text" class="box-input" name="first_name" value="{{ $user->first_name }}" />
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-12">
                                <div class="inputs">
                                    <label class="form-label">{{ __('Last Name') }}</label>
                                    <input type="text" class="box-input" name="last_name" value="{{ $user->last_name }}" />
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-12">
                                <div class="inputs">
                                    <label class="form-label">{{ __('Username') }}</label>
                                    <input type="text" class="box-input" name="username" value="{{ $user->username }}" />
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-12">
                                <div class="inputs">
                                    <label class="form-label">{{ __('Gender') }}</label>
                                    <select name="gender" class="page-count box-input" required>
                                        @foreach(['Male','Female','Other'] as $gender)
                                            <option value="{{$gender}}"  @selected($user->gender == $gender)>{{$gender}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-12">
                                <div class="inputs">
                                    <label class="form-label">{{ __('Date of Birth') }}</label>
                                    <input type="date" name="date_of_birth" class="box-input" value="{{ $user->date_of_birth }}" />
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-12">
                                <div class="inputs">
                                    <label class="form-label">{{ __('Phone') }}</label>
                                    <input type="text" class="box-input" name="phone" value="{{ $user->phone }}" />
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-12">
                                <div class="inputs">
                                    <label class="form-label">{{ __('City') }}</label>
                                    <input type="text" class="box-input" name="city" value="{{ $user->city }}" />
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-12">
                                <div class="inputs">
                                    <label class="form-label">{{ __('Zip') }}</label>
                                    <input type="text" class="box-input" name="zip_code" value="{{ $user->zip_code }}" />
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-12">
                                <div class="inputs">
                                    <label class="form-label">{{ __('Address') }}</label>
                                    <input type="text" class="box-input" name="address" value="{{ $user->address }}" />
                                </div>
                            </div>
                            <div class="col-xl-12 col-md-12 mt-3">
                                <button type="submit" class="site-btn">{{ __('Update Profile') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

