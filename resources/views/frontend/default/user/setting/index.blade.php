@extends('frontend::layouts.user')

@section('title')
    {{ __('Personal Settings') }}
@endsection

@section('content')
<div class="row">
    @include('frontend::user.setting.include.__settings_nav')
    <div class="col-xl-9 col-lg-8 col-md-12 col-12">
        <!-- Banno Profile Card (Image 6 Style) -->
        <div class="site-card overflow-hidden border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="profile-header-banno p-4 d-flex align-items-center gap-4 bg-white">
                <div class="avatar-display position-relative">
                     <div class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center text-secondary fw-bold" style="width: 140px; height: 140px; background-color: #f0f2f5; border: 4px solid #fff; font-size: 32px;">
                        @if($user->avatar)
                            <img src="{{ asset($user->avatar) }}" class="w-100 h-100 object-fit-cover" alt="Avatar">
                        @else
                            Avatar
                        @endif
                     </div>
                     <button type="button" class="position-absolute bottom-0 end-0 bg-primary text-white border-0 p-0 rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" onclick="$('#avatar_input').click()">
                        <i class="fas fa-camera small"></i>
                     </button>
                </div>
                <div class="profile-name-info">
                    <h1 class="fw-bold mb-1 text-dark" style="font-size: 36px;">{{ $user->full_name }}</h1>
                    <div class="text-muted fs-5 fw-500">
                         Member Since {{ \Carbon\Carbon::parse($user->created_at)->format('M Y') }} • Account #{{ substr($user->account_number, 0, 4) . '****' . substr($user->account_number, -4) }}
                    </div>
                </div>
            </div>

            <div class="p-4 pt-5 bg-white">
                <h6 class="fw-bold text-uppercase small text-muted mb-4 ls-1">PERSONAL DETAILS</h6>
                
                <div class="row g-4 mb-5">
                    <div class="col-12">
                        <label class="small text-muted d-block mb-1 text-uppercase fw-bold ls-1">USERNAME</label>
                        <div class="fs-5 text-dark fw-500">{{ $user->username }}</div>
                    </div>
                    <div class="col-12">
                        <label class="small text-muted d-block mb-1 text-uppercase fw-bold ls-1">GENDER</label>
                        <div class="fs-5 text-dark fw-500">{{ ucfirst($user->gender) ?: 'Female' }}</div>
                    </div>
                    <div class="col-12">
                        <label class="small text-muted d-block mb-1 text-uppercase fw-bold ls-1">DATE OF BIRTH</label>
                        <div class="fs-5 text-dark fw-500">{{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') : 'Oct 21, 1956' }}</div>
                    </div>
                    <div class="col-12">
                        <label class="small text-muted d-block mb-1 text-uppercase fw-bold ls-1">PHONE NUMBER</label>
                        <div class="fs-5 text-dark fw-500">{{ $user->phone ?: '+234' }}</div>
                    </div>
                </div>

                <div class="mt-4 pb-4">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4 fw-bold" onclick="$('#edit_profile_form').slideToggle()">
                        Update profile
                    </button>
                </div>

                <div id="edit_profile_form" style="display: none;" class="mt-4 pt-4 border-top">
                    <!-- existing form hidden by default -->
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


