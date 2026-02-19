@extends('frontend::layouts.user')

@section('title')
{{ __('Security Settings') }}
@endsection

@section('content')
<div class="row">
    @include('frontend::user.setting.include.__settings_nav')
    <div class="col-xl-9 col-lg-8 col-md-12 col-12">
        <div class="site-card border-0 shadow-sm overflow-hidden mb-4">
            <div class="p-5">
                <div class="section-title mb-5">
                    <h2 class="fw-bold text-dark mb-2">Security</h2>
                    <p class="text-muted">Manage your credentials, multi-factor authentication, and recognized devices.</p>
                </div>

                <!-- Login Credentials -->
                <div class="mb-5">
                    <h6 class="fw-bold text-uppercase small text-muted mb-4 border-bottom pb-2">Login Credentials</h6>
                    <div class="d-flex justify-content-between align-items-center mb-4 py-2">
                        <div>
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1">Username</label>
                            <div class="fw-600 text-dark fs-5">{{ auth()->user()->username }}</div>
                        </div>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#changeUsernameModal" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">Change</button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <div>
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1">Password</label>
                            <div class="fw-600 text-dark fs-5">••••••••••••</div>
                        </div>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#changePasswordModal" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">Update</button>
                    </div>
                </div>

                <!-- 2FA Section -->
                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <h6 class="fw-bold text-uppercase small text-muted mb-0">Two-factor authentication</h6>
                        @if($user->google2fa_secret == null)
                            <a href="{{ route('user.setting.two.fa') }}" class="banno-switch"></a>
                        @else
                            <div class="banno-switch {{ $user->two_fa ? 'active' : '' }}" onclick="$('#2fa-details').slideToggle()"></div>
                        @endif
                    </div>
                    <p class="small text-muted mb-4">Add an extra layer of security to your account by requiring a code from your mobile device to sign in.</p>
                    
                    <div id="2fa-details" class="p-4 bg-light rounded-3 {{ $user->google2fa_secret == null ? 'd-none' : '' }}">
                             <div class="row align-items-center">
                                <div class="col-md-auto mb-3 mb-md-0">
                                    <div class="bg-white p-2 rounded shadow-sm">
                                        @php
                                            $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());
                                            $inlineUrl = $google2fa->getQRCodeInline(setting('site_title','global'),$user->email,$user->google2fa_secret);
                                        @endphp
                                        <img src="{!! $inlineUrl !!}" alt="QR Code" class="img-fluid">
                                    </div>
                                </div>
                                <div class="col-md">
                                    <form action="{{ route('user.setting.action-2fa') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-uppercase">Verifier Code</label>
                                            <input type="password" class="form-control mb-3" name="one_time_password" placeholder="000 000">
                                            @if($user->two_fa)
                                                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold" name="status" value="disable">Disable 2FA</button>
                                            @else
                                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold" name="status" value="enable">Complete Setup</button>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                             </div>
                        </div>
                </div>

                <!-- Connected Devices -->
                <div>
                     <h6 class="fw-bold text-uppercase small text-muted mb-4 border-bottom pb-2">Recognized devices</h6>
                     <div class="device-list">
                        @forelse($recentDevices as $device)
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary" style="width: 48px; height: 48px;">
                                        @if($device->platform == 'Windows' || $device->platform == 'OS X' || $device->platform == 'Linux')
                                            <i class="fas fa-desktop fs-5"></i>
                                        @else
                                            <i class="fas fa-mobile-alt fs-5"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $device->browser }} on {{ $device->platform }}</div>
                                        <div class="small text-muted">{{ $device->ip }} • {{ $device->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                <form action="{{ route('user.setting.delete-login-activity', $device->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-link text-danger text-decoration-none small fw-bold">Remove</button>
                                </form>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <p class="text-muted small mb-0">No other recognized devices found.</p>
                            </div>
                        @endforelse
                     </div>
                </div>
            </div>
        </div>

        <!-- Security Warning -->
        <div class="p-4 bg-info bg-opacity-10 rounded-3 border-start border-info border-4">
            <div class="d-flex gap-3">
                <i class="fas fa-shield-alt text-info fs-4"></i>
                <div class="small text-dark">
                    <strong>Keep your account safe:</strong> Pinellas FCU will never call, text, or email you asking for your password or 2FA code. If you notice any suspicious activity, change your password immediately.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="changeUsernameModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark pt-3 px-3">Change Username</h5>
                <button type="button" class="btn-close me-2 mt-2" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.setting.profile-update') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="small text-muted mb-4">Your username is used to sign in to your accounts. It must be unique and between 6-20 characters.</p>
                    <input type="hidden" name="first_name" value="{{ $user->first_name }}">
                    <input type="hidden" name="last_name" value="{{ $user->last_name }}">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">New Username</label>
                        <input type="text" class="form-control" name="username" value="{{ $user->username }}" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Save changes</button>
                    <button type="button" class="btn btn-link w-100 text-muted text-decoration-none small mt-2" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark pt-3 px-3">Update Password</h5>
                <button type="button" class="btn-close me-2 mt-2" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.new.password') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">Current Password</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">New Password</label>
                        <input type="password" class="form-control" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">Confirm New Password</label>
                        <input type="password" class="form-control" name="new_confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Update password</button>
                    <button type="button" class="btn btn-link w-100 text-muted text-decoration-none small mt-2" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
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
        transition: all 0.3s;
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
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .banno-switch.active { background-color: #28a745; }
    .banno-switch.active::after { transform: translateX(24px); }
</style>
@endsection

