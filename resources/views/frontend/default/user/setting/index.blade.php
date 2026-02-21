@extends('frontend::layouts.user')

@section('title')
    {{ __('Personal Settings') }}
@endsection

@section('content')
<div class="row">
    @include('frontend::user.setting.include.__settings_nav')
    <div class="col-xl-9 col-lg-8 col-md-12 col-12 d-none d-lg-block" id="settings-content-col">
        <!-- Profile Header with Back Button (Mobile Only) -->
        <div class="d-lg-none mb-3">
             <a href="javascript:void(0)" onclick="hideProfileDetails()" class="text-decoration-none text-dark fw-bold d-flex align-items-center gap-2">
                 <i class="fas fa-arrow-left"></i> Profile
             </a>
        </div>

        <!-- Banno Profile Card (Image 5 Style) -->
        <div class="site-card overflow-hidden border-0 shadow-sm mb-4" style="border-radius: 8px;">
            <div class="site-card-body p-0">
                <div class="profile-header-banno p-4 d-flex align-items-start gap-3 bg-white border-bottom">
                    <div class="avatar-display position-relative flex-shrink-0">
                         <div class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center text-secondary fw-bold" style="width: 80px; height: 80px; background-color: #f0f2f5; font-size: 24px;">
                            @if($user->avatar)
                                <img src="{{ asset($user->avatar) }}" class="w-100 h-100 object-fit-cover" alt="Avatar">
                            @else
                                Avatar
                            @endif
                         </div>
                         <button type="button" class="position-absolute bottom-0 end-0 bg-primary text-white border-0 p-0 rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;" onclick="$('#avatar_input').click()">
                            <i class="fas fa-camera small" style="font-size: 12px;"></i>
                         </button>
                    </div>
                    <div class="profile-name-info pt-1">
                        <h1 class="fw-bold mb-1 text-dark" style="font-size: 20px;">{{ $user->full_name }}</h1>
                        <div class="text-muted small" style="font-size: 13px; line-height: 1.4;">
                             Member Since<br>
                             {{ \Carbon\Carbon::parse($user->created_at)->format('M Y') }} • Account<br>
                             #{{ substr($user->account_number, 0, 4) . '***' . substr($user->account_number, -4) }}
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-white">
                    <h6 class="fw-bold text-uppercase small text-muted mb-4 ls-1" style="font-size: 11px;">PERSONAL DETAILS</h6>
                    
                    <div class="row mb-4">
                        <div class="col-6">
                            <label class="small text-muted d-block mb-1 text-uppercase fw-bold ls-1" style="font-size: 10px;">FIRST NAME</label>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fs-6 text-dark fw-500">{{ $user->first_name }}</div>
                                <a href="javascript:void(0)" onclick="$('#edit_profile_form').slideDown(); $('[name=first_name]').focus()" class="text-decoration-none small fw-bold">Edit</a>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted d-block mb-1 text-uppercase fw-bold ls-1" style="font-size: 10px;">LAST NAME</label>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fs-6 text-dark fw-500">{{ $user->last_name }}</div>
                                <a href="javascript:void(0)" onclick="$('#edit_profile_form').slideDown(); $('[name=last_name]').focus()" class="text-decoration-none small fw-bold">Edit</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="small text-muted d-block mb-1 text-uppercase fw-bold ls-1" style="font-size: 10px;">USERNAME</label>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fs-6 text-dark fw-500">{{ $user->username }}</div>
                            <a href="javascript:void(0)" onclick="$('#changeUsernameModal').modal('show')" class="text-decoration-none small fw-bold">Edit</a>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="small text-muted d-block mb-1 text-uppercase fw-bold ls-1" style="font-size: 10px;">GENDER</label>
                        <div class="fs-6 text-dark fw-500">{{ ucfirst($user->gender) ?: 'Female' }}</div>
                    </div>

                    <div class="mb-4">
                         <label class="small text-muted d-block mb-1 text-uppercase fw-bold ls-1" style="font-size: 10px;">DATE OF BIRTH</label>
                         <div class="fs-6 text-dark fw-500">{{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') : 'Oct 21, 1956' }}</div>
                    </div>

                    <div class="mb-4 position-relative">
                        <label class="small text-muted d-block mb-1 text-uppercase fw-bold ls-1" style="font-size: 10px;">PHONE</label>
                        <div class="d-flex justify-content-between align-items-center">
                             <div>
                                <div class="small text-muted">Mobile</div>
                                <div class="fs-6 text-dark fw-500">{{ $user->phone ?: '(737) 410-5689' }}</div>
                             </div>
                             <a href="javascript:void(0)" onclick="$('#edit_profile_form').slideDown(); $('[name=phone]').focus()" class="text-decoration-none small fw-bold">Edit phone numbers</a>
                        </div>
                    </div>
                    
                     <div class="mb-4 position-relative">
                        <label class="small text-muted d-block mb-1 text-uppercase fw-bold ls-1" style="font-size: 10px;">EMAIL</label>
                        <div class="d-flex justify-content-between align-items-center">
                             <div class="w-100">
                                <div class="small text-muted mb-1">Profile details for digital banking alerts and statements.</div>
                                <div class="fs-6 text-dark fw-500 text-uppercase mb-2">{{ $user->email }}</div>
                                <a href="javascript:void(0)" onclick="$('#edit_profile_form').slideDown(); $('[name=email]').focus()" class="text-decoration-none small fw-bold">Edit email</a>
                             </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="small text-muted d-block mb-1 text-uppercase fw-bold ls-1" style="font-size: 10px;">PREFERRED FIRST NAME</label>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fs-6 text-dark fw-500">{{ $user->preferred_first_name ?: $user->first_name }}</div>
                            <a href="javascript:void(0)" onclick="$('#edit_profile_form').slideDown(); $('[name=preferred_first_name]').focus()" class="text-decoration-none small fw-bold">Edit</a>
                        </div>
                    </div>
                
                    <div class="mb-4">
                        <label class="small text-muted d-block mb-1 text-uppercase fw-bold ls-1" style="font-size: 10px;">ADDRESS</label>
                         <div class="mb-2">
                             <div class="fs-6 text-dark fw-500 text-uppercase">{{ $user->address }}</div>
                             <div class="fs-6 text-dark fw-500 text-uppercase">{{ $user->city }}, {{ $user->zip_code }}</div>
                         </div>
                         <a href="javascript:void(0)" onclick="$('#edit_profile_form').slideDown(); $('[name=address]').focus()" class="text-decoration-none small fw-bold">Edit address</a>
                    </div>

                    <!-- Alerts Section -->
                    <div class="mt-5 pt-4 border-top">
                        <h6 class="fw-bold text-uppercase small text-muted mb-4 ls-1" style="font-size: 11px;">ALERTS</h6>
                        <form action="{{ route('user.setting.newsletter.action') }}" method="POST">
                            @csrf
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <div class="fs-6 text-dark fw-500">Push Notifications</div>
                                    <div class="small text-muted">Receive alerts on your mobile device</div>
                                </div>
                                <label class="banno-switch {{ $user->notifications_permission['all_push_notifications'] ?? 0 ? 'active' : '' }}">
                                    <input type="checkbox" name="all_push_notifications" {{ $user->notifications_permission['all_push_notifications'] ?? 0 ? 'checked' : '' }} onchange="$(this).closest('.banno-switch').toggleClass('active'); this.form.submit()" style="opacity: 0; width: 0; height: 0;">
                                </label>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <div class="fs-6 text-dark fw-500">Email Notifications</div>
                                    <div class="small text-muted">Receive updates and alerts via email</div>
                                </div>
                                <label class="banno-switch {{ $user->notifications_permission['deposit_email_notificaitons'] ?? 0 ? 'active' : '' }}">
                                    <input type="checkbox" name="email_notifications" {{ $user->notifications_permission['deposit_email_notificaitons'] ?? 0 ? 'checked' : '' }} onchange="$(this).closest('.banno-switch').toggleClass('active'); this.form.submit()" style="opacity: 0; width: 0; height: 0;">
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
                
                 <!-- Hidden Edit Form -->
                <div id="edit_profile_form" style="display: none;" class="p-4 border-top bg-light">
                    <form action="{{ route('user.setting.profile-update') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="avatar" id="avatar_input" style="display: none;" accept=".jpg,.png,.jpeg">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">First Name</label>
                                <input type="text" class="form-control" name="first_name" value="{{ $user->first_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Preferred First Name</label>
                                <input type="text" class="form-control" name="preferred_first_name" value="{{ $user->preferred_first_name }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Last Name</label>
                                <input type="text" class="form-control" name="last_name" value="{{ $user->last_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Email Address</label>
                                <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Phone</label>
                                <input type="text" class="form-control" name="phone" value="{{ $user->phone }}">
                            </div>
                             <div class="col-md-6">
                                <label class="form-label small fw-bold">City</label>
                                <input type="text" class="form-control" name="city" value="{{ $user->city }}">
                            </div>
                             <div class="col-md-12">
                                <label class="form-label small fw-bold">Address</label>
                                <input type="text" class="form-control" name="address" value="{{ $user->address }}">
                            </div>
                            <div class="col-md-12 mt-3">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Changes</button>
                                <button type="button" class="btn btn-link text-muted text-decoration-none small fw-bold ms-2" onclick="$('#edit_profile_form').slideUp()">Cancel</button>
                            </div>
                        </div>
                    </form>
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

@section('script')
<script>
    // Local scripts for profile page only (if any)
</script>
@endsection
