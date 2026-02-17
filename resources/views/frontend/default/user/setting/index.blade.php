@extends('frontend::layouts.user')

@section('title')
    {{ __('Personal Settings') }}
@endsection

@section('content')
<div class="row">
    @include('frontend::user.setting.include.__settings_nav')
    <div class="col-xl-9 col-lg-8 col-md-12 col-12">
        <!-- Banno Profile Card -->
        <div class="site-card overflow-hidden border-0 shadow-sm mb-4">
            <div class="site-card-body p-0">
                <div class="profile-header-banno p-5 d-flex align-items-center gap-4 bg-white border-bottom">
                    <div class="avatar-edit-wrapper position-relative">
                         <div class="rounded-circle overflow-hidden shadow-sm border border-light" style="width: 120px; height: 120px;">
                            <img src="{{ $user->avatar ? asset($user->avatar) : asset('front/images/user.png') }}" class="w-100 h-100 object-fit-cover" alt="Avatar">
                         </div>
                         <a href="javascript:void(0)" onclick="$('#avatar_input').click()" class="position-absolute bottom-0 end-0 bg-primary text-white p-2 rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border: 3px solid #fff;">
                            <i class="fas fa-camera small"></i>
                         </a>
                    </div>
                    <div class="profile-name-info">
                        <h2 class="fw-bold mb-1 text-dark">{{ $user->full_name }}</h2>
                        <div class="text-muted small fw-600">
                             Member Since {{ \Carbon\Carbon::parse($user->created_at)->format('M Y') }} • Account #{{ substr($user->account_number, 0, 4) . '****' . substr($user->account_number, -4) }}
                        </div>
                    </div>
                </div>

                <div class="p-5">
                    <h6 class="fw-bold text-uppercase small text-muted mb-4">Personal Details</h6>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small text-muted d-block mb-1 text-uppercase fw-bold">Username</label>
                            <div class="fw-600 text-dark">{{ $user->username }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted d-block mb-1 text-uppercase fw-bold">Gender</label>
                            <div class="fw-600 text-dark">{{ ucfirst($user->gender) ?: 'Not Specified' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted d-block mb-1 text-uppercase fw-bold">Date of Birth</label>
                            <div class="fw-600 text-dark">{{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') : 'Not Provided' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted d-block mb-1 text-uppercase fw-bold">Phone Number</label>
                            <div class="fw-600 text-dark">{{ $user->phone }}</div>
                        </div>
                        <div class="col-12">
                            <label class="small text-muted d-block mb-1 text-uppercase fw-bold">Address</label>
                            <div class="fw-600 text-dark">
                                {{ $user->address }}<br>
                                {{ $user->city }}, {{ $user->zip_code }}, {{ $user->country }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-top">
                        <button type="button" class="btn btn-outline-primary rounded-pill px-4 fw-bold" onclick="$('#edit_profile_form').slideToggle()">
                            <i class="fas fa-edit me-2"></i> Edit Profile Information
                        </button>
                    </div>

                    <div id="edit_profile_form" style="display: none;" class="mt-4 pt-4 border-top">
                        <form action="{{ route('user.setting.profile-update') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="avatar" id="avatar_input" style="display: none;" accept=".jpg,.png,.jpeg">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">First Name</label>
                                    <input type="text" class="form-control" name="first_name" value="{{ $user->first_name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" value="{{ $user->last_name }}" required>
                                </div>
                                <div class="col-md-12 mt-4">
                                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">Save Changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Privacy Section -->
        <div class="site-card border-0 shadow-sm p-5">
            <h6 class="fw-bold text-uppercase small text-muted mb-4">Privacy & Interface</h6>
            <div class="settings-actions">
                <div class="banno-toggle d-flex justify-content-between align-items-center mb-4">
                    <div class="banno-toggle-info pe-4">
                        <h6 class="fw-bold mb-1">Show running balance</h6>
                        <p class="small text-muted mb-0">Display the current balance for each account on your dashboard.</p>
                    </div>
                    <div class="banno-switch active" onclick="$(this).toggleClass('active')"></div>
                </div>
                <div class="banno-toggle d-flex justify-content-between align-items-center">
                    <div class="banno-toggle-info pe-4">
                        <h6 class="fw-bold mb-1">Quick Login</h6>
                        <p class="small text-muted mb-0">Enable biometric or PIN login on authorized mobile devices.</p>
                    </div>
                    <div class="banno-switch" onclick="$(this).toggleClass('active')"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
    .fw-600 { font-weight: 600; }
    .banno-switch {
        width: 50px;
        height: 26px;
        background-color: #e9ecef;
        border-radius: 20px;
        position: relative;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        flex-shrink: 0;
    }
    .banno-switch::after {
        content: '';
        position: absolute;
        width: 22px;
        height: 22px;
        background-color: #fff;
        border-radius: 50%;
        top: 2px;
        left: 2px;
        transition: transform 0.3s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .banno-switch.active { background-color: #28a745; }
    .banno-switch.active::after { transform: translateX(24px); }
</style>
@endsection


