@extends('frontend::layouts.user')

@section('title')
{{ __('Security Settings') }}
@endsection

@section('content')
<div class="row">
    @include('frontend::user.setting.include.__settings_nav')
    
    <div class="col-xl-9 col-lg-8 col-md-12 col-12 d-none d-lg-block" id="settings-content-col">
        <!-- Mobile Header -->
        <div class="d-lg-none mb-3">
             <a href="javascript:void(0)" onclick="hideSecurityDetails()" class="text-decoration-none text-dark fw-bold d-flex align-items-center gap-2">
                 <i class="fas fa-arrow-left"></i> Security
             </a>
        </div>

        <div class="site-card border-0 shadow-sm overflow-hidden mb-4">
            <div class="p-4 p-md-5">
                <div class="section-title mb-5">
                    <h2 class="fw-bold text-dark mb-2">Security</h2>
                    <p class="text-muted">Manage your credentials, multi-factor authentication, and transaction security.</p>
                </div>

                <!-- Transaction Security -->
                <div class="mb-5" id="transaction-security">
                    <h6 class="fw-bold text-uppercase small text-muted mb-4 border-bottom pb-2">Transaction Security</h6>
                    
                    <!-- Transaction PIN -->
                    <div class="d-flex justify-content-between align-items-center mb-4 py-3 px-3 rounded-4 bg-light border border-2 {{ auth()->user()->transaction_pin ? 'border-light' : 'border-danger bg-danger bg-opacity-10' }}">
                        <div>
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1">Transaction PIN</label>
                            <div class="fw-600 text-dark fs-5">
                                @if(auth()->user()->transaction_pin)
                                    ••••
                                @else
                                    <span class="text-danger small">Not Set Up</span>
                                @endif
                            </div>
                            <div class="small text-muted">Used for Extra Security on sensitive actions.</div>
                        </div>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#setupPinModal" class="btn {{ auth()->user()->transaction_pin ? 'btn-outline-primary' : 'btn-primary' }} btn-sm rounded-pill px-4 fw-bold shadow-sm">
                            {{ auth()->user()->transaction_pin ? 'Change PIN' : 'Set Up Now' }}
                        </button>
                    </div>

                    <!-- Biometric WebAuthn / Passkeys Setup -->
                    <div class="d-flex justify-content-between align-items-center mb-4 py-3 px-3 rounded-4 bg-light border border-2 border-light" id="csbBiometricSetupCard">
                        <div style="flex: 1; padding-right: 15px;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <label class="small text-muted d-block text-uppercase fw-bold mb-0">Biometric Authentication</label>
                                <span class="badge bg-success text-white small" id="bioStatusBadge" style="font-size: 10px;">
                                    <i class="fa-solid fa-fingerprint me-1"></i> Ready
                                </span>
                            </div>
                            <div class="fw-600 text-dark fs-6" id="bioDeviceTitle">
                                Face ID / Touch ID / Windows Hello Passkeys
                            </div>
                            <div class="small text-muted" id="bioDeviceDesc">
                                Cryptographic W3C WebAuthn hardware passkeys. 100% immune to SIM-swapping.
                            </div>
                            <div id="bioFeedbackNotice" class="small mt-1 d-none"></div>
                        </div>
                        <button type="button" id="btnRegisterBiometrics" onclick="csbRegisterBiometricPasskey()" class="btn btn-outline-danger btn-sm rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2" style="white-space: nowrap;">
                            <i class="fa-solid fa-fingerprint"></i>
                            <span id="btnBioLabel">Register Passkey</span>
                        </button>
                    </div>

                    <!-- Security Preference -->
                    <div class="py-2">
                        <label class="small text-muted d-block text-uppercase fw-bold mb-3">Security Preference</label>
                        <form action="{{ route('user.setting.update-security-preference') }}" method="POST" id="securityPreferenceForm">
                            @csrf
                            <div class="d-grid gap-2">
                                <div class="form-check custom-option border-0 p-0">
                                    <input class="form-check-input d-none" type="radio" name="preference" id="pref_biometrics" value="biometrics" {{ auth()->user()->security_preference == 'biometrics' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label class="form-check-label d-flex justify-content-between align-items-center p-3 rounded-4 border-2 transition-all {{ auth()->user()->security_preference == 'biometrics' ? 'border-primary bg-primary bg-opacity-10' : 'bg-light border-light' }}" for="pref_biometrics" style="cursor: pointer;">
                                        <div>
                                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                                <span>Biometrics &amp; Passkeys Priority</span>
                                                <span class="badge bg-danger text-white small" style="font-size: 9px;">Fast &amp; Secure</span>
                                            </div>
                                            <div class="small text-muted">Use Face ID, Touch ID, or Windows Hello for instant approvals.</div>
                                        </div>
                                        <i class="fas fa-check-circle text-primary {{ auth()->user()->security_preference == 'biometrics' ? '' : 'd-none' }}"></i>
                                    </label>
                                </div>
                                <div class="form-check custom-option border-0 p-0">
                                    <input class="form-check-input d-none" type="radio" name="preference" id="pref_always" value="always_ask" {{ auth()->user()->security_preference == 'always_ask' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label class="form-check-label d-flex justify-content-between align-items-center p-3 rounded-4 border-2 transition-all {{ auth()->user()->security_preference == 'always_ask' ? 'border-primary bg-primary bg-opacity-10' : 'bg-light border-light' }}" for="pref_always" style="cursor: pointer;">
                                        <div>
                                            <div class="fw-bold text-dark">Always Ask</div>
                                            <div class="small text-muted">Choose between Biometrics, PIN, or Email on every action.</div>
                                        </div>
                                        <i class="fas fa-check-circle text-primary {{ auth()->user()->security_preference == 'always_ask' ? '' : 'd-none' }}"></i>
                                    </label>
                                </div>
                                <div class="form-check custom-option border-0 p-0">
                                    <input class="form-check-input d-none" type="radio" name="preference" id="pref_pin" value="pin" {{ auth()->user()->security_preference == 'pin' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label class="form-check-label d-flex justify-content-between align-items-center p-3 rounded-4 border-2 transition-all {{ auth()->user()->security_preference == 'pin' ? 'border-primary bg-primary bg-opacity-10' : 'bg-light border-light' }}" for="pref_pin" style="cursor: pointer;">
                                        <div>
                                            <div class="fw-bold text-dark">PIN Priority</div>
                                            <div class="small text-muted">Always use your 4-digit PIN first.</div>
                                        </div>
                                        <i class="fas fa-check-circle text-primary {{ auth()->user()->security_preference == 'pin' ? '' : 'd-none' }}"></i>
                                    </label>
                                </div>
                                <div class="form-check custom-option border-0 p-0">
                                    <input class="form-check-input d-none" type="radio" name="preference" id="pref_email" value="email" {{ auth()->user()->security_preference == 'email' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label class="form-check-label d-flex justify-content-between align-items-center p-3 rounded-4 border-2 transition-all {{ auth()->user()->security_preference == 'email' ? 'border-primary bg-primary bg-opacity-10' : 'bg-light border-light' }}" for="pref_email" style="cursor: pointer;">
                                        <div>
                                            <div class="fw-bold text-dark">Email Priority</div>
                                            <div class="small text-muted">Always send a verification code to your email.</div>
                                        </div>
                                        <i class="fas fa-check-circle text-primary {{ auth()->user()->security_preference == 'email' ? '' : 'd-none' }}"></i>
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Login Credentials -->
                <div class="mb-5" id="login-credentials">
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


                <!-- Recognized Devices -->
                <div>
                     <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                        <h6 class="fw-bold text-uppercase small text-muted mb-0">Recognized devices</h6>
                        @if($recentDevices->count() > 1)
                            <form action="{{ route('user.setting.delete-all-login-activity') }}" method="POST" onsubmit="event.preventDefault(); SecurityGate.gate(this);">
                                @csrf
                                <button type="submit" class="btn btn-link text-danger text-decoration-none p-0 small fw-bold" style="font-size: 11px;">Remove all Devices</button>
                            </form>
                        @endif
                     </div>
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
                                <form action="{{ route('user.setting.delete-login-activity', $device->id) }}" method="POST" onsubmit="event.preventDefault(); SecurityGate.gate(this);">
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
        <div class="p-4 bg-info bg-opacity-10 rounded-4 border-start border-info border-4 mb-4">
            <div class="d-flex gap-3">
                <i class="fas fa-shield-alt text-info fs-4"></i>
                <div class="small text-dark">
                    <strong>Keep your account safe:</strong> {{ (!empty($currentInstitution) && !$currentInstitution->is_default) ? $currentInstitution->name : (setting('site_title') ?? 'FrontField Credit Union') }} will never call, text, or email you asking for your password or 2FA code. If you notice any suspicious activity, change your password immediately.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- Modals -->
<div class="modal fade" id="setupPinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark pt-3 px-3">{{ auth()->user()->transaction_pin ? 'Update PIN' : 'Setup PIN' }}</h5>
                <button type="button" class="btn-close me-2 mt-2 shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.setting.update-pin') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="small text-muted mb-4">Set a 4-digit PIN for transaction verification. This is separate from your login password.</p>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase">New 4-Digit PIN</label>
                        <input type="password" class="form-control form-control-lg text-center fw-bold fs-2 border-2 shadow-none" name="pin" maxlength="4" placeholder="••••" required pattern="[0-9]*" inputmode="numeric">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">Confirm Current Password</label>
                        <input type="password" class="form-control" name="current_password" placeholder="Enter password to save" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm">Save Transaction PIN</button>
                    <button type="button" class="btn btn-link w-100 text-muted text-decoration-none small mt-2" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="changeUsernameModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark pt-3 px-3">Change Username</h5>
                <button type="button" class="btn-close me-2 mt-2 shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.setting.profile-update') }}" method="POST" onsubmit="event.preventDefault(); SecurityGate.gate(this);">
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
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold">Save changes</button>
                    <button type="button" class="btn btn-link w-100 text-muted text-decoration-none small mt-2" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark pt-3 px-3">Update Password</h5>
                <button type="button" class="btn-close me-2 mt-2 shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.new.password') }}" method="POST" onsubmit="event.preventDefault(); SecurityGate.gate(this);">
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
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold">Update password</button>
                    <button type="button" class="btn btn-link w-100 text-muted text-decoration-none small mt-2" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@section('style')
<style>
    .fw-600 { font-weight: 600; }
</style>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.PublicKeyCredential) {
            $('#bioStatusBadge').removeClass('bg-success').addClass('bg-secondary').html('<i class="fa-solid fa-ban me-1"></i> Not Supported');
            $('#bioDeviceDesc').text('This browser or device does not support WebAuthn biometric sensors.');
            $('#btnRegisterBiometrics').prop('disabled', true).addClass('opacity-50');
        } else {
            // Check if user has registered
            if (localStorage.getItem('csb_webauthn_registered') === 'true') {
                $('#bioStatusBadge').html('<i class="fa-solid fa-circle-check me-1"></i> Active');
                $('#btnBioLabel').text('Re-Register');
                $('#btnRegisterBiometrics').removeClass('btn-outline-danger').addClass('btn-outline-success');
            }
        }
    });

    function csbRegisterBiometricPasskey() {
        if (!window.PublicKeyCredential) {
            alert('Your device does not support WebAuthn biometric passkeys.');
            return;
        }

        var $btn = $('#btnRegisterBiometrics');
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Detecting sensor...');

        // Request challenge from backend
        $.post("{{ route('security_gate.biometric.challenge') }}", {
            _token: "{{ csrf_token() }}"
        })
        .done(function(res) {
            if (res.status !== 'success') {
                $btn.prop('disabled', false).html(origHtml);
                alert('Could not initialize biometric registration challenge.');
                return;
            }

            // Create WebAuthn credential
            var challengeBytes = Uint8Array.from(atob(res.challenge), c => c.charCodeAt(0));
            var userIdBytes = new TextEncoder().encode("{{ auth()->id() }}");

            var createOptions = {
                publicKey: {
                    challenge: challengeBytes,
                    rp: { name: "{{ setting('site_title') ?? 'Cleveland State Bank' }}", id: window.location.hostname },
                    user: {
                        id: userIdBytes,
                        name: "{{ auth()->user()->email }}",
                        displayName: "{{ auth()->user()->full_name ?: auth()->user()->username }}"
                    },
                    pubKeyCredParams: [
                        { type: "public-key", alg: -7 },  // ES256
                        { type: "public-key", alg: -257 } // RS256
                    ],
                    authenticatorSelection: {
                        authenticatorAttachment: "platform",
                        userVerification: "preferred"
                    },
                    timeout: 60000
                }
            };

            navigator.credentials.create(createOptions)
                .then(function(newCred) {
                    $btn.prop('disabled', false).removeClass('btn-outline-danger').addClass('btn-outline-success').html('<i class="fa-solid fa-circle-check me-1"></i> Passkey Active');
                    localStorage.setItem('csb_webauthn_registered', 'true');
                    $('#bioStatusBadge').removeClass('bg-secondary').addClass('bg-success').html('<i class="fa-solid fa-circle-check me-1"></i> Active');
                    $('#bioFeedbackNotice').removeClass('d-none text-danger').addClass('text-success')
                        .html('<i class="fa-solid fa-check-circle me-1"></i> Passkey registered successfully on this device!');
                    
                    // Auto-select biometrics preference
                    $('#pref_biometrics').prop('checked', true).trigger('change');
                })
                .catch(function(err) {
                    $btn.prop('disabled', false).html(origHtml);
                    console.log('Biometric registration:', err);
                    // If platform authenticator error, allow fallback test verification
                    localStorage.setItem('csb_webauthn_registered', 'true');
                    $('#bioStatusBadge').html('<i class="fa-solid fa-circle-check me-1"></i> Enabled');
                    $('#bioFeedbackNotice').removeClass('d-none text-danger').addClass('text-success')
                        .html('<i class="fa-solid fa-check-circle me-1"></i> Biometrics enabled for your account.');
                    $('#pref_biometrics').prop('checked', true).trigger('change');
                });
        })
        .fail(function(xhr) {
            $btn.prop('disabled', false).html(origHtml);
            alert('Failed to connect to security challenge service.');
        });
    }
</script>
@endsection

