@extends('frontend::layouts.user')
@section('title')
{{ __('Security Settings') }}
@endsection
@section('content')
<div class="row">
    @include('frontend::user.setting.include.__settings_nav')
    <div class="col-xl-9 col-lg-8 col-md-12 col-12">
        <div class="site-card">
            <div class="site-card-body profile-details-view">
                <div class="section-title mb-4">
                    <h3 style="font-weight: 700; color: var(--body-text-primary-color); margin: 0;">{{ __('Security') }}</h3>
                    <p style="color: var(--body-text-secondary-color); font-size: 0.9rem;">{{ __('Manage your account security, authentication, and connected devices.') }}</p>
                </div>

                <!-- Username & Password Section -->
                <div class="security-group mb-5">
                    <h5 class="security-group-title" style="font-weight: 700; color: var(--primary-color); font-size: 1rem; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">{{ __('Login Credentials') }}</h5>
                    
                    <div class="security-row d-flex justify-content-between align-items-center mb-3">
                        <div class="security-info">
                            <label style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--body-text-secondary-color); font-weight: 700; margin-bottom: 2px;">{{ __('Username') }}</label>
                            <span style="font-size: 1rem; color: var(--body-text-primary-color);">{{ auth()->user()->username }}</span>
                        </div>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#changeUsernameModal" class="btn btn-sm outline-btn" style="border: 1px solid #ddd; color: var(--primary-color); font-weight: 600; border-radius: 4px; padding: 4px 12px;">{{ __('Edit') }}</button>
                    </div>

                    <div class="security-row d-flex justify-content-between align-items-center">
                        <div class="security-info">
                            <label style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--body-text-secondary-color); font-weight: 700; margin-bottom: 2px;">{{ __('Password') }}</label>
                            <span style="font-size: 1rem; color: var(--body-text-primary-color);">••••••••••••</span>
                        </div>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#changePasswordModal" class="btn btn-sm outline-btn" style="border: 1px solid #ddd; color: var(--primary-color); font-weight: 600; border-radius: 4px; padding: 4px 12px;">{{ __('Edit') }}</button>
                    </div>
                </div>

                <!-- 2-Step Verification Section -->
                <div class="security-group mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="security-group-title" style="font-weight: 700; color: var(--primary-color); font-size: 1rem; margin: 0;">{{ __('2-step verification') }}</h5>
                        <div class="banno-switch {{ $user->two_fa ? 'active' : '' }}" onclick="window.location.href='#2fa-section-expanded'"></div>
                    </div>
                    <p style="color: var(--body-text-secondary-color); font-size: 0.85rem;">{{ __('Add an extra layer of security to your account by requiring more than just a password to log in.') }}</p>

                    <div id="2fa-section-expanded" class="mt-4 pt-4 border-top">
                        @if($user->google2fa_secret !== null)
                            @php
                                $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());
                                $inlineUrl = $google2fa->getQRCodeInline(setting('site_title','global'),$user->email,$user->google2fa_secret);
                            @endphp
                            <div class="row align-items-center">
                                <div class="col-md-4 text-center">
                                    <div class="mb-3">
                                        @if(Str::of($inlineUrl)->startsWith('data:image/'))
                                        <img src="{{ $inlineUrl }}" style="max-width: 150px; border: 4px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                        @else
                                        {!! $inlineUrl !!}
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <form action="{{ route('user.setting.action-2fa') }}" method="POST">
                                        @csrf
                                        <div class="inputs mb-3">
                                            <label class="input-label" style="font-size: 0.8rem; font-weight: 700; color: var(--body-text-secondary-color);">
                                                @if($user->two_fa)
                                                    {{ __('ENTER PASSWORD TO DISABLE') }}
                                                @else
                                                    {{ __('ENTER PIN FROM GOOGLE AUTHENTICATOR APP') }}
                                                @endif
                                            </label>
                                            <input type="password" class="box-input" name="one_time_password" placeholder="••••••" style="border: 1px solid #ddd; border-radius: 8px; padding: 10px;">
                                        </div>
                                        @if($user->two_fa)
                                            <button type="submit" class="site-btn-sm red-btn" value="disable" name="status">
                                                <i class="fas fa-times-circle"></i> {{ __('Disable 2FA') }}
                                            </button>
                                        @else
                                            <button type="submit" class="site-btn-sm primary-theme-btn" value="enable" name="status">
                                                <i class="fas fa-shield-alt"></i> {{ __('Enable 2FA') }}
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('user.setting.2fa') }}" class="site-btn-sm primary-theme-btn">
                                <i class="fas fa-qrcode"></i> {{ __('Setup 2FA Authentication') }}
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Passkeys Section (High-Fidelity Placeholder for future Banno roadmap) -->
                <div class="security-group mb-5">
                    <h5 class="security-group-title" style="font-weight: 700; color: var(--primary-color); font-size: 1rem; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">{{ __('Passkeys') }}</h5>
                    <p style="color: var(--body-text-secondary-color); font-size: 0.85rem;">{{ __('Passkeys are a safer, easier alternative to passwords. Use your fingerprint, face, or screen lock to sign in.') }}</p>
                    <div class="security-row d-flex justify-content-between align-items-center bg-light p-3 rounded" style="border: 1px dashed #ccc;">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fas fa-key" style="font-size: 1.2rem; color: #666;"></i>
                            <span style="font-size: 0.9rem; color: #666; font-style: italic;">{{ __('No passkeys registered yet.') }}</span>
                        </div>
                        <button disabled class="btn btn-sm" style="color: #999; font-weight: 600;">{{ __('Add Passkey') }}</button>
                    </div>
                </div>

                <!-- Recently Used Devices Section -->
                <div class="security-group mb-4">
                    <h5 class="security-group-title" style="font-weight: 700; color: var(--primary-color); font-size: 1rem; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">{{ __('Recently used devices') }}</h5>
                    
                    @if($recentDevices->count() > 0)
                        <div class="device-list">
                            @foreach($recentDevices as $device)
                                <div class="device-item d-flex justify-content-between align-items-center mb-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="device-icon d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #f8f9fa; border-radius: 12px; color: var(--primary-color);">
                                            @if($device->platform == 'Windows' || $device->platform == 'OS X' || $device->platform == 'Linux')
                                                <i class="fas fa-desktop" style="font-size: 1.4rem;"></i>
                                            @else
                                                <i class="fas fa-mobile-alt" style="font-size: 1.4rem;"></i>
                                            @endif
                                        </div>
                                        <div class="device-details">
                                            <h6 style="margin: 0; font-weight: 700; color: var(--body-text-primary-color);">{{ $device->browser }} on {{ $device->platform }}</h6>
                                            <p style="margin: 0; font-size: 0.8rem; color: var(--body-text-secondary-color);">{{ $device->ip }} • {{ $device->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <form action="{{ route('user.setting.delete-login-activity', $device->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-link text-decoration-none p-0" style="color: #dc3545; font-weight: 600; font-size: 0.85rem;">{{ __('Remove') }}</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history" style="font-size: 2rem; color: #ddd; margin-bottom: 10px; display: block;"></i>
                            <span style="color: var(--body-text-secondary-color);">{{ __('No recent login activity found.') }}</span>
                        </div>
                    @endif
                </div>

                <div class="mt-5 pt-3 border-top">
                    <p style="font-size: 0.8rem; color: var(--body-text-secondary-color); line-height: 1.4;">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ __('If you see a device you do not recognize, we recommend changing your password immediately and removing the session from this list.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Username Modal -->
<div class="modal fade" id="changeUsernameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: var(--primary-color);">{{ __('Change Username') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.setting.profile-update') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-4">{{ __('Your username is used to sign in to your account. It must be unique.') }}</p>
                    
                    {{-- Hidden fields required by profileUpdate validator if we use the same route --}}
                    <input type="hidden" name="first_name" value="{{ $user->first_name }}">
                    <input type="hidden" name="last_name" value="{{ $user->last_name }}">
                    <input type="hidden" name="gender" value="{{ $user->gender }}">
                    <input type="hidden" name="phone" value="{{ $user->phone }}">
                    <input type="hidden" name="date_of_birth" value="{{ $user->date_of_birth }}">
                    <input type="hidden" name="city" value="{{ $user->city }}">
                    <input type="hidden" name="zip_code" value="{{ $user->zip_code }}">
                    <input type="hidden" name="address" value="{{ $user->address }}">

                    <div class="inputs mb-3">
                        <label class="input-label small fw-bold text-uppercase" style="color: var(--body-text-secondary-color);">{{ __('New Username') }}</label>
                        <input type="text" class="box-input" name="username" value="{{ $user->username }}" required style="border-radius: 8px;">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-link text-decoration-none text-muted fw-600" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="site-btn-sm primary-theme-btn" style="padding: 10px 24px;">{{ __('Save Changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: var(--primary-color);">{{ __('Update Password') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.new-password') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-4">{{ __('For your security, do not share your password with anyone.') }}</p>
                    
                    <div class="inputs mb-3">
                        <label class="input-label small fw-bold text-uppercase" style="color: var(--body-text-secondary-color);">{{ __('Current Password') }}</label>
                        <input type="password" class="box-input" name="current_password" required placeholder="••••••••••••" style="border-radius: 8px;">
                    </div>
                    
                    <div class="inputs mb-3">
                        <label class="input-label small fw-bold text-uppercase" style="color: var(--body-text-secondary-color);">{{ __('New Password') }}</label>
                        <input type="password" class="box-input" name="new_password" required placeholder="••••••••••••" style="border-radius: 8px;">
                    </div>

                    <div class="inputs mb-3">
                        <label class="input-label small fw-bold text-uppercase" style="color: var(--body-text-secondary-color);">{{ __('Confirm New Password') }}</label>
                        <input type="password" class="box-input" name="new_confirm_password" required placeholder="••••••••••••" style="border-radius: 8px;">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-link text-decoration-none text-muted fw-600" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="site-btn-sm primary-theme-btn" style="padding: 10px 24px;">{{ __('Change Password') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>
</div>

<style>
.banno-switch {
    width: 44px;
    height: 24px;
    background-color: #e9ecef;
    border-radius: 20px;
    position: relative;
    cursor: pointer;
    transition: background-color 0.3s ease;
    border: 1px solid #dee2e6;
}
.banno-switch::after {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    background-color: #fff;
    border-radius: 50%;
    top: 2px;
    left: 2px;
    transition: transform 0.3s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.banno-switch.active {
    background-color: #28a745;
    border-color: #218838;
}
.banno-switch.active::after {
    transform: translateX(20px);
}
.security-row:hover {
    background-color: #fcfcfc;
}
.site-btn-sm {
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 600;
    transition: all 0.2s ease;
}
.red-btn { background-color: #dc3545; color: white; border: none; }
.red-btn:hover { background-color: #c82333; }
.primary-theme-btn { background-color: var(--primary-color); color: white; border: none; }
.primary-theme-btn:hover { filter: brightness(1.1); }
</style>
@endsection
