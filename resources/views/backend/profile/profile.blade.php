@extends('backend.layouts.app')
@section('title')
    {{ __('Details of Admin') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="title-content">
                            <h2 class="title">{{ __('Details of Admin') }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <form action="{{ route('admin.profile-update') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12">
                        <div class="profile-card">
                            <div class="top mb-0">
                                <div class="wrap-custom-file mb-2">
                                    <input
                                        type="file"
                                        name="avatar"
                                        id="admin_profile_image"
                                        accept=".gif, .jpg, .png"
                                    />
                                    <label for="admin_profile_image" class="file-ok"
                                           style="background-image: url({{ asset(auth()->user()->avatar) }})">
                                        <img
                                            class="upload-icon"
                                            src="{{ asset('global/materials/upload.svg') }}"
                                            alt=""
                                        />
                                        <span>{{ __('Update Profile Image') }}</span>
                                    </label>
                                </div>
                                <div class="title-des mb-0">
                                    <h4>{{ auth()->user()->name }}</h4>
                                    <p class="mb-0"> {{  str_replace('-', ' ', auth()->user()->getRoleNames()->first() )  }} </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-9 col-lg-9 col-md-8 col-sm-12">
                        <div class="site-card">
                            <div class="site-card-header">
                                <h3 class="title">{{ __("Information's") }}</h3>
                            </div>
                            <div class="site-card-body">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('Name:') }}</label>
                                            <input type="text" class="box-input" name="name"
                                                   value="{{ Auth::user()->name }}" required="">
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('Email:') }}</label>
                                            <input type="email" class="box-input" name="email"
                                                   value="{{ Auth::user()->email }}" required="">
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('Phone:') }}</label>
                                            <input type="text" class="box-input" name="phone"
                                                   value="{{ Auth::user()->phone }}" required="">
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('Joining Date:') }}</label>
                                            <input type="text" class="box-input" value="{{ Auth::user()->created_at }}"
                                                   required="" disabled>
                                        </div>
                                    </div>

                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <hr class="my-4">
                                        <h4 class="mb-3">{{ __('Security Gate (MFA)') }}</h4>
                                    </div>

                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="passcode" class="box-input-label">{{ __('New 4-Digit Security PIN:') }}</label>
                                            <input type="password" class="box-input" name="passcode" id="passcode"
                                                   placeholder="****" maxlength="4" pattern="\d{4}" 
                                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                            <small class="text-muted">{{ __('Leave blank to keep existing PIN. Numeric only.') }}</small>
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label class="box-input-label">{{ __('Security Gate Status:') }}</label>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" name="passcode_status" id="passcode_status" 
                                                       {{ Auth::user()->passcode_status ? 'checked' : '' }}>
                                                <label class="form-check-label" for="passcode_status">
                                                    {{ Auth::user()->passcode_status ? __('Enabled') : __('Disabled') }}
                                                </label>
                                            </div>
                                            <small class="text-muted">{{ __('Toggle to enable/disable PIN verification on login.') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <button type="submit"
                                                class="site-btn-sm primary-btn w-100 centered">{{ __('Save Changes') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection

