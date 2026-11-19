@php
    $sgAuthUser = $user ?? auth('web')->user() ?? auth()->user();
    $currentInst = app()->bound('institution') ? app('institution') : ($sgAuthUser?->institution ?? null);
    $isCustomInst = $currentInst && !$currentInst->is_default;
    $isCsb = ($isCustomInst && ($currentInst->slug === 'clevelandstatebank' || str_contains(request()->getHost(), 'clevelandstatebank')))
             || (isset($sgAuthUser) && $sgAuthUser->institution && $sgAuthUser->institution->slug === 'clevelandstatebank')
             || str_contains(request()->getHost(), 'clevelandstatebank');

    $sgUserEmail = $sgAuthUser->email ?? '';
    $sgAtPos = strpos($sgUserEmail, '@');
    $sgMaskedEmail = ($sgAtPos !== false)
        ? substr($sgUserEmail, 0, min(3, $sgAtPos)) . '***' . substr($sgUserEmail, $sgAtPos)
        : ($sgUserEmail ?: 'registered email');
    $sgHasPin = !empty($sgAuthUser?->transaction_pin) || !empty($sgAuthUser?->passcode);
@endphp

<!-- Security Gate Modal -->
<div class="modal fade" id="securityGateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; @if($isCsb) border-top: 4px solid #c8232c !important; @endif">
            <div class="modal-header border-0 pb-0">
                @if($isCsb)
                <div class="d-flex align-items-center justify-content-between w-100 pt-2 px-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 rounded-pill fw-bold" style="font-size: 10px;">
                            <i class="fa-solid fa-building-columns me-1"></i>FDIC
                        </span>
                        <h5 class="fw-bold mb-0" style="color: #162846; font-size: 16px;">{{ __('Security Verification') }}</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @else
                <h5 class="fw-bold text-dark pt-3 px-3">{{ __('Multi-Factor Authentication Verification') }}</h5>
                <button type="button" class="btn-close me-2 mt-2" data-bs-dismiss="modal" aria-label="Close"></button>
                @endif
            </div>
            
            <div class="modal-body p-0" id="sg-modal-body">
                <!-- Feedback Area -->
                <div id="sg-feedback" class="error-text small d-none text-center px-4 pt-3"></div>

                <div class="mfa-card-inner p-4 pt-2">
                    <!-- Method Selection -->
                    <div id="sg-method-selection">
                        <p class="text-secondary small mb-3">{{ __('Please select a verification method to continue.') }}</p>
                        
                        <div class="method-list">
                            <button type="button" class="method-card sg-choice-btn" onclick="SecurityGate.selectMethod('biometric')" id="sg-choice-biometric">
                                <div class="method-icon" style="background: {{ $isCsb ? 'rgba(200, 35, 44, 0.1)' : 'rgba(2, 132, 199, 0.1)' }}; color: {{ $isCsb ? '#c8232c' : '#0284c7' }};">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 11c0 3.5-1.5 6-4 7.5"></path>
                                        <path d="M12 11c0-2.5 1.5-4 4-4s4 1.5 4 4c0 3.5-1.5 6-4 7.5"></path>
                                        <path d="M7 11c0-4 2.5-7 5-7s5 3 5 7"></path>
                                        <path d="M12 21a9 9 0 0 0 9-9c0-5-4-9-9-9s-9 4-9 9a9 9 0 0 0 3 6.7"></path>
                                    </svg>
                                </div>
                                <div class="method-info">
                                    <span class="title">{{ __('Biometric Passkey') }}</span>
                                    <span class="subtitle">{{ __('Face ID / Touch ID / Windows Hello') }}</span>
                                </div>
                                <div class="chevron">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                </div>
                            </button>

                            <button type="button" class="method-card sg-choice-btn" onclick="SecurityGate.selectMethod('email')" id="sg-choice-email">
                                <div class="method-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                </div>
                                <div class="method-info">
                                    <span class="title">{{ __('Email Verification') }}</span>
                                    <span class="subtitle">{{ __('6-digit code via email') }}</span>
                                </div>
                                <div class="chevron">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                </div>
                            </button>

                            <button type="button" class="method-card sg-choice-btn" onclick="SecurityGate.selectMethod('pin')" id="sg-choice-pin" {{ !$sgHasPin ? 'disabled' : '' }}>
                                <div class="method-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3 2.5-2.5"></path></svg>
                                </div>
                                <div class="method-info">
                                    <span class="title">{{ __('Security Passcode') }}</span>
                                    <span class="subtitle">{{ $sgHasPin ? __('Enter your 4-digit Passcode') : __('Passcode not set up yet') }}</span>
                                </div>
                                <div class="chevron">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Biometric Verification Section -->
                    <div id="sg-biometric-verify" class="d-none text-center">
                        @if($isCsb)
                        <div class="text-center py-2">
                            <!-- FDIC Trust Badge -->
                            <div class="mb-3">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill fw-bold" style="font-size: 11px;">
                                    <i class="fa-solid fa-building-columns me-1"></i> FDIC Insured • Cleveland State Bank
                                </span>
                            </div>

                            <!-- Multi-layered Circular Icon with Pulse Ring Animation -->
                            <div class="csb-biometric-pulse-wrap">
                                <i class="fa-solid fa-fingerprint"></i>
                            </div>

                            <h4 class="fw-bold mb-1" style="color: #162846; font-size: 20px;">{{ __('Biometric Authentication') }}</h4>
                            <p class="text-secondary small mb-3">{{ __('Use Face ID, Touch ID, or Windows Hello for instant access.') }}</p>

                            <!-- High-Polish Crimson Button Gradient -->
                            <button type="button" class="btn csb-btn-biometric mb-3 shadow-sm" id="sg-btn-scan-biometric" onclick="SecurityGate.triggerBiometricAuth()">
                                <i class="fa-solid fa-fingerprint me-2"></i> {{ __('Confirm with Face ID / Touch ID') }}
                            </button>

                            <!-- Clean Secondary Option Cards with Icons & Chevrons -->
                            <div class="switch-link-wrapper mt-3 text-start">
                                @if($sgHasPin)
                                <a href="javascript:void(0)" class="csb-secondary-auth-card" onclick="SecurityGate.selectMethod('pin')">
                                    <div class="csb-secondary-auth-icon"><i class="fa-solid fa-keypad"></i></div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold small">{{ __('Use Security Passcode instead') }}</div>
                                        <div class="text-muted" style="font-size: 11px;">{{ __('Enter your 4-digit security PIN') }}</div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-muted small"></i>
                                </a>
                                @endif
                                <a href="javascript:void(0)" class="csb-secondary-auth-card" onclick="SecurityGate.selectMethod('email')">
                                    <div class="csb-secondary-auth-icon"><i class="fa-solid fa-envelope"></i></div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold small">{{ __('Use Email Verification Code instead') }}</div>
                                        <div class="text-muted" style="font-size: 11px;">{{ __('One-time verification code sent to your email') }}</div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-muted small"></i>
                                </a>
                                <div class="text-center mt-2">
                                    <a href="javascript:void(0)" class="forgot-link small" onclick="SecurityGate.backToChoice()">
                                        <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Back to Options') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 72px; height: 72px; background: rgba(2, 132, 199, 0.1); color: #0284c7; font-size: 32px;">
                            <i class="fa-solid fa-fingerprint"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">{{ __('Biometric Authentication') }}</h4>
                        <p class="text-secondary small mb-4">{{ __('Use Face ID, Touch ID, or Windows Hello passkey to authorize this action.') }}</p>

                        <button type="button" class="btn text-white w-100 py-3 fw-bold rounded-pill mb-3 shadow-sm" id="sg-btn-scan-biometric" onclick="SecurityGate.triggerBiometricAuth()" style="background: #0284c7; font-size: 15px;">
                            <i class="fa-solid fa-fingerprint me-2"></i> {{ __('Confirm with Face ID / Touch ID') }}
                        </button>

                        <div class="mt-4">
                            @if($sgHasPin)
                            <div class="mb-2">
                                <a href="javascript:void(0)" class="forgot-link" onclick="SecurityGate.selectMethod('pin')">{{ __('Use Passcode instead') }}</a>
                            </div>
                            @endif
                            <div class="mb-2">
                                <a href="javascript:void(0)" class="forgot-link" onclick="SecurityGate.selectMethod('email')">{{ __('Use Email Verification Code instead') }}</a>
                            </div>
                            <div class="mt-2 text-muted small" style="opacity: 0.5;">— or —</div>
                            <div class="mt-2">
                                <a href="javascript:void(0)" class="forgot-link" onclick="SecurityGate.backToChoice()">{{ __('Back to Options') }}</a>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Email Verification Section -->
                    <div id="sg-email-verify" class="d-none text-center">
                        @if($isCsb)
                        <div class="mb-3">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill fw-bold" style="font-size: 11px;">
                                <i class="fa-solid fa-building-columns me-1"></i> FDIC Insured • Cleveland State Bank
                            </span>
                        </div>
                        @endif
                        <h4 class="fw-bold text-dark mb-2">{{ __('Verification Required') }}</h4>
                        <p class="text-secondary small mb-1">{{ __('We\'ve sent a verification code to') }}</p>
                        <p class="fw-bold text-dark small mb-4">{{ $sgMaskedEmail }}</p>
                        
                        <div class="mb-4">
                            <input type="text" id="sg-email-code-input" class="input-box otp-input" placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        
                        <button type="button" class="primary-btn w-100 mb-3" id="sg-verify-btn" onclick="SecurityGate.submitVerification()" @if($isCsb) style="background: linear-gradient(135deg, #c8232c 0%, #a81c24 100%) !important; border-radius: 10px !important;" @endif>{{ __('Verify Code') }}</button>

                        <div class="mt-4">
                            <div class="mb-2">
                                <a href="javascript:void(0)" class="forgot-link" onclick="SecurityGate.resendEmail()">{{ __('Resend Code') }}</a>
                            </div>
                            <div class="mt-2 text-muted small" style="opacity: 0.5;">— or —</div>
                            <div class="mt-2">
                                <a href="javascript:void(0)" class="forgot-link" onclick="SecurityGate.backToChoice()">{{ __('Back to Options') }}</a>
                            </div>
                        </div>
                    </div>

                    <!-- PIN Verification Section -->
                    <div id="sg-pin-verify" class="d-none text-center">
                        @if($isCsb)
                        <div class="mb-3">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill fw-bold" style="font-size: 11px;">
                                <i class="fa-solid fa-building-columns me-1"></i> FDIC Insured • Cleveland State Bank
                            </span>
                        </div>
                        @endif
                        <h4 class="fw-bold text-dark mb-2">{{ __('Verification Required') }}</h4>
                        <p class="text-secondary small mb-4">{{ __('Enter your 4-digit passcode to continue.') }}</p>

                        <div class="pin-display-wrapper mb-4" id="sg-pin-display">
                            <div class="pin-dot" id="sg-dot-1"></div>
                            <div class="pin-dot" id="sg-dot-2"></div>
                            <div class="pin-dot" id="sg-dot-3"></div>
                            <div class="pin-dot" id="sg-dot-4"></div>
                        </div>

                        <div class="keypad">
                            @for($i = 1; $i <= 9; $i++)
                                <button type="button" class="key-btn" onclick="SecurityGate.pressKey({{ $i }})">{{ $i }}</button>
                            @endfor
                            <div class="key-btn invisible"></div>
                            <button type="button" class="key-btn" onclick="SecurityGate.pressKey(0)">0</button>
                            <button type="button" class="key-btn backspace" onclick="SecurityGate.backspace()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path><line x1="18" y1="9" x2="12" y2="15"></line><line x1="12" y1="9" x2="18" y2="15"></line></svg>
                            </button>
                        </div>
                        
                        <div class="switch-link-wrapper mt-3">
                            <a href="javascript:void(0)" class="forgot-link" onclick="SecurityGate.selectMethod('email')">{{ __('Use Email Verification Code instead') }}</a>
                            <div class="mt-2">
                                <a href="javascript:void(0)" class="forgot-link small" onclick="SecurityGate.backToChoice()">{{ __('Back to Options') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 p-4 pt-0 text-center d-block">
                <a href="javascript:void(0)" class="text-muted text-decoration-none small" onclick="SecurityGate.backToChoice()" id="sg-back-btn">Cancel</a>
            </div>
        </div>
    </div>
</div>

<style>
@if($isCsb)
    @keyframes csbBiometricPulse {
        0% { box-shadow: 0 0 0 0 rgba(200, 35, 44, 0.28); }
        70% { box-shadow: 0 0 0 16px rgba(200, 35, 44, 0); }
        100% { box-shadow: 0 0 0 0 rgba(200, 35, 44, 0); }
    }
    .csb-biometric-pulse-wrap {
        width: 78px;
        height: 78px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(200, 35, 44, 0.08) 0%, rgba(22, 40, 70, 0.04) 100%);
        border: 2px solid rgba(200, 35, 44, 0.22);
        color: #c8232c;
        font-size: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px auto;
        animation: csbBiometricPulse 2.4s infinite ease-in-out;
    }
    .csb-btn-biometric {
        background: linear-gradient(135deg, #c8232c 0%, #a81c24 100%) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 10px !important;
        padding: 13px 20px !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        letter-spacing: 0.3px !important;
        box-shadow: 0 4px 14px rgba(200, 35, 44, 0.28) !important;
        transition: all 0.2s ease !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }
    .csb-btn-biometric:hover:not(:disabled) {
        background: linear-gradient(135deg, #b51f27 0%, #90161d 100%) !important;
        box-shadow: 0 6px 18px rgba(200, 35, 44, 0.38) !important;
        transform: translateY(-1px) !important;
        color: #ffffff !important;
    }
    .csb-btn-biometric:active:not(:disabled) {
        transform: translateY(0) !important;
        box-shadow: 0 2px 8px rgba(200, 35, 44, 0.28) !important;
    }
    .csb-auth-alert {
        background: #fef2f2;
        border: 1px solid #fee2e2;
        color: #dc2626;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
    }
    .csb-secondary-auth-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 14px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        text-decoration: none !important;
        color: #162846 !important;
        transition: all 0.2s ease;
        margin-bottom: 8px;
    }
    .csb-secondary-auth-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        color: #c8232c !important;
    }
    .csb-secondary-auth-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: rgba(22, 40, 70, 0.05);
        color: #162846;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    #securityGateModal .pin-dot.filled {
        background-color: #c8232c !important;
        border-color: #c8232c !important;
    }
    #securityGateModal .pin-dot {
        border-color: #162846 !important;
    }
    #securityGateModal .key-btn.backspace {
        color: #c8232c !important;
    }
    #securityGateModal .method-card:hover {
        border-color: #c8232c !important;
        border-left-color: #c8232c !important;
        background: #fdf2f2 !important;
    }
    #securityGateModal .forgot-link {
        color: #c8232c !important;
    }
@endif
    #securityGateModal {
        z-index: 10600 !important;
    }
    #securityGateModal + .modal-backdrop,
    .modal-backdrop.security-gate-backdrop {
        z-index: 10590 !important;
    }
    /* Literal Copy from mfa.blade.php */
    /* PIN dots inside modal */
    .pin-display-wrapper { display: flex; justify-content: center; gap: 20px; margin-bottom: 25px; }
    .pin-dot { width: 14px; height: 14px; border: 2px solid #555 !important; border-radius: 50%; transition: all 0.2s ease; background-color: transparent !important; }
    .pin-dot.filled { background-color: #555 !important; transform: scale(1.1); }
    .keypad { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; max-width: 280px; margin-left: auto; margin-right: auto; }
    .key-btn { 
        background: #f8f9fa; 
        border: 1px solid #e9ecef; 
        border-radius: 8px; 
        padding: 8px; 
        font-size: 18px; 
        font-weight: 600; 
        color: #333; 
        cursor: pointer; 
        transition: all 0.2s; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        height: 46px; 
        user-select: none; 
        touch-action: manipulation;
    }
    .key-btn:hover { background: #e9ecef; border-color: #dee2e6; }
    .key-btn:active { background: #dee2e6; transform: scale(0.95); }
    .key-btn.invisible { visibility: hidden; }
    .key-btn.backspace { color: #e31837; }
    .key-btn.backspace svg { width: 24px; height: 24px; stroke-width: 2.5px; }
    .forgot-link { color: #0056b3; text-decoration: none; font-size: 14px; font-weight: 500; cursor: pointer; display: block; margin: 5px 0; }
    .forgot-link:hover { text-decoration: underline; }
    .input-box.otp-input { letter-spacing: 8px; font-size: 24px; text-align: center; font-weight: 700; max-width: 240px; margin: 0 auto; border: 2px solid #ddd; border-radius: 8px; padding: 10px; }
    .error-text { color: #ff3b30 !important; font-weight: 700 !important; margin-top: 8px; min-height: 20px; }
    .primary-btn { background: var(--primary-color, #c8232c) !important; color: #fff !important; border: none !important; border-radius: 8px !important; padding: 12px !important; font-weight: 700 !important; transition: all 0.2s; }
    .primary-btn:hover { opacity: 0.9; transform: translateY(-1px); }
    .shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }
    @keyframes shake {
        10%, 90% { transform: translate3d(-1px, 0, 0); }
        20%, 80% { transform: translate3d(2px, 0, 0); }
        30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
        40%, 60% { transform: translate3d(4px, 0, 0); }
    }
    /* Method Selection Styling */
    .method-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .method-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        text-align: left;
        cursor: pointer;
        transition: all 0.2s ease;
        width: 100%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        border-left: 4px solid transparent;
        appearance: none;
        -webkit-appearance: none;
        color: inherit;
    }

    .method-card:hover:not([disabled]) {
        border-color: var(--primary-color, #00549b);
        border-left-color: var(--primary-color, #00549b);
        background: #f8fbff;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.06);
    }

    .method-card[disabled] {
        opacity: 0.6;
        cursor: not-allowed;
        background: #f8f9fa;
    }

    .method-icon {
        width: 42px;
        height: 42px;
        background: rgba(200, 35, 44, 0.08);
        color: var(--primary-color, #c8232c);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 18px;
    }

    .method-info {
        flex-grow: 1;
    }

    .method-info .title {
        font-weight: 700;
        color: #0d1e3a;
        font-size: 14px;
        margin-bottom: 2px;
        display: block;
    }

    .method-info .subtitle {
        font-size: 11px;
        color: #64748b;
        display: block;
    }

    .method-icon svg {
        display: block;
    }

    .method-card .chevron {
        color: #ccd2d8;
        flex-shrink: 0;
    }

    .sg-choice-btn:hover { background-color: rgba(0, 84, 155, 0.05); transform: translateY(-2px); border-color: #0056b3 !important; }
</style>

<script>
window.UserSecurityPreference = "{{ $sgAuthUser->security_preference ?? 'always_ask' }}";

window.SecurityGate = window.SecurityGate || {};

Object.assign(window.SecurityGate, {
        currentTarget: window.SecurityGate.currentTarget || null,
        onSuccessCb: window.SecurityGate.onSuccessCb || null,
        onCancelCb: window.SecurityGate.onCancelCb || null,
        isVerified: false,
        gateId: null,
        selectedMethod: null,
        currentPin: "",
        cachedChallenge: null,

        gate: function(target, callback) {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            this.isVerified = false;

            let cb = null;
            let tgt = null;

            if (typeof target === 'function') {
                cb = target;
                tgt = target;
            } else if (typeof callback === 'function') {
                cb = callback;
                tgt = target;
            } else {
                tgt = target;
            }

            if (tgt && tgt.jquery) {
                tgt = tgt[0];
            }

            if (typeof tgt === 'string') {
                try {
                    const elem = document.querySelector(tgt);
                    if (elem) {
                        tgt = elem;
                    }
                } catch (e) {}
            }

            this.currentTarget = tgt;
            if (cb) {
                this.onSuccessCb = cb;
            }

            this.gateId = 'sg_' + Date.now();
            this.reset();
            this.prepareBiometricChallenge();

            // Biometric Passkey button is ALWAYS visible in method selection
            $('#sg-choice-biometric').removeClass('d-none');

            const preference = window.UserSecurityPreference;
            if (preference === 'biometrics' || preference === 'biometric') {
                this.selectMethod('biometric');
            } else if (preference === 'email') {
                this.selectMethod('email');
            } else if (preference === 'pin') {
                this.selectMethod('pin');
            } else {
                $('#sg-method-selection').removeClass('d-none');
                $('#securityGateModal').modal('show');
            }
        },

        reset: function() {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            this.selectedMethod = null;
            this.currentPin = "";
            this.cachedChallenge = null;
            $('#sg-method-selection').addClass('d-none');
            $('#sg-biometric-verify, #sg-email-verify, #sg-pin-verify, #sg-verify-btn, #sg-feedback').addClass('d-none');
            $('#sg-email-code-input').val('');
            this.updateDots();
            $('#sg-back-btn').text('Cancel');
            $('#sg-verify-btn').prop('disabled', false).find('.spinner-border').addClass('d-none');
            // Biometric Passkey button is ALWAYS visible in method selection
            $('#sg-choice-biometric').removeClass('d-none');
        },

        pressKey: function(num) {
            if (this.currentPin.length < 4) {
                this.currentPin += num;
                this.updateDots();
                if (this.currentPin.length === 4) {
                    this.submitVerification();
                }
            }
        },

        backspace: function() {
            if (this.currentPin.length > 0) {
                this.currentPin = this.currentPin.slice(0, -1);
                this.updateDots();
            }
        },

        updateDots: function() {
            for (let i = 1; i <= 4; i++) {
                const dot = $('#sg-dot-' + i);
                if (i <= this.currentPin.length) {
                    dot.addClass('filled');
                } else {
                    dot.removeClass('filled');
                }
            }
        },

        selectMethod: function(method) {
            this.selectedMethod = method;
            $('#sg-method-selection').addClass('d-none');
            $('#sg-feedback').addClass('d-none').text('');

            if (!$('#securityGateModal').hasClass('show')) {
                $('#securityGateModal').modal('show');
            }

            if (method === 'biometric') {
                $('#sg-biometric-verify').removeClass('d-none');
                $('#sg-email-verify, #sg-pin-verify, #sg-verify-btn').addClass('d-none');
                $('#sg-back-btn').removeClass('d-none').text('Cancel');
                const self = this;
                setTimeout(function() {
                    self.triggerBiometricAuth();
                }, 150);
                return;
            }

            $('#sg-biometric-verify').addClass('d-none');
            $('#sg-back-btn').removeClass('d-none').text('Back');

            if (method === 'email') {
                $('#sg-verify-btn').removeClass('d-none');
                $('#sg-email-verify').removeClass('d-none');
                $('#sg-pin-verify').addClass('d-none');
                this.resendEmail();
                setTimeout(() => $('#sg-email-code-input').focus(), 500);
            } else {
                $('#sg-verify-btn').addClass('d-none');
                $('#sg-pin-verify').removeClass('d-none');
                $('#sg-email-verify').addClass('d-none');
            }
        },

        prepareBiometricChallenge: function() {
            const self = this;
            const token = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";
            if (!token) return;

            const challengeUrl = "{{ Route::has('user.security_gate.biometric.challenge') ? route('user.security_gate.biometric.challenge') : url('/user/security-gate/biometric/challenge') }}";

            $.post(challengeUrl, {
                _token: token,
                gate_id: this.gateId
            }).done(function(res) {
                if (res && res.status === 'success' && res.challenge) {
                    self.cachedChallenge = res.challenge;
                }
            }).fail(function() {
                self.cachedChallenge = null;
            });
        },

        executeBiometricAssertion: function(challengeStr) {
            const self = this;
            const $btn = $('#sg-btn-scan-biometric');
            const origHtml = '<i class="fa-solid fa-fingerprint me-2"></i> {{ __("Confirm with Face ID / Touch ID") }}';
            const token = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

            try {
                const challengeBytes = new Uint8Array(challengeStr.match(/.{1,2}/g).map(byte => parseInt(byte, 16)));

                navigator.credentials.get({
                    publicKey: {
                        challenge: challengeBytes,
                        timeout: 60000,
                        userVerification: 'preferred',
                        rpId: window.location.hostname
                    }
                }).then(function(assertion) {
                    $btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Verifying...');
                    const verifyUrl = "{{ Route::has('user.security_gate.verify') ? route('user.security_gate.verify') : url('/user/security-gate/verify') }}";
                    $.post(verifyUrl, {
                        _token: token,
                        type: 'biometric',
                        value: assertion ? (assertion.id || 'passkey_authenticated') : 'passkey_authenticated',
                        gate_id: self.gateId
                    }).done(function(verifyRes) {
                        $btn.prop('disabled', false).html(origHtml);
                        if (verifyRes.status === 'success') {
                            self.completeSuccess(verifyRes);
                        } else {
                            $('#sg-feedback').text(verifyRes.message || 'Biometric verification failed. Please try PIN or Email.').removeClass('d-none');
                        }
                    }).fail(function(xhr) {
                        $btn.prop('disabled', false).html(origHtml);
                        $('#sg-feedback').text(xhr.responseJSON?.message || 'Biometric verification failed. Please try PIN or Email.').removeClass('d-none');
                    });
                }).catch(function(err) {
                    $btn.prop('disabled', false).html(origHtml);
                    console.log("Biometric auth rejected or timed out:", err);
                    if (err.name === 'NotAllowedError') {
                        $('#sg-feedback').text('Biometric prompt was closed or timed out. Tap "Confirm with Face ID / Touch ID" below to try again, or choose another method.').removeClass('d-none');
                    } else {
                        $('#sg-feedback').text('Biometric authentication could not be completed. Tap button to retry or select another method.').removeClass('d-none');
                    }
                });
            } catch (e) {
                $btn.prop('disabled', false).html(origHtml);
                console.error("Biometric assertion exception:", e);
                $('#sg-feedback').text('Biometric verification error. Please choose PIN or Email instead.').removeClass('d-none');
            }
        },

        triggerBiometricAuth: function() {
            const self = this;
            const $btn = $('#sg-btn-scan-biometric');
            const origHtml = '<i class="fa-solid fa-fingerprint me-2"></i> {{ __("Confirm with Face ID / Touch ID") }}';
            $('#sg-feedback').addClass('d-none').text('');

            if (!window.PublicKeyCredential) {
                $('#sg-feedback').text('Biometric authentication is not supported by your current browser. Please choose PIN or Email.').removeClass('d-none');
                return;
            }

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Waiting for Face ID / Touch ID...');

            // If challenge was already pre-fetched, execute immediately inside user gesture activation
            if (this.cachedChallenge) {
                const ch = this.cachedChallenge;
                this.cachedChallenge = null;
                this.executeBiometricAssertion(ch);
                return;
            }

            const token = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";
            const challengeUrl = "{{ Route::has('user.security_gate.biometric.challenge') ? route('user.security_gate.biometric.challenge') : url('/user/security-gate/biometric/challenge') }}";

            $.post(challengeUrl, {
                _token: token,
                gate_id: this.gateId
            }).done(function(res) {
                if (res.status === 'success' && res.challenge) {
                    self.executeBiometricAssertion(res.challenge);
                } else {
                    $btn.prop('disabled', false).html(origHtml);
                    $('#sg-feedback').text('Could not start biometric session. Please use PIN or Email.').removeClass('d-none');
                }
            }).fail(function() {
                $btn.prop('disabled', false).html(origHtml);
                $('#sg-feedback').text('Could not start biometric session. Please use PIN or Email.').removeClass('d-none');
            });
        },

        completeSuccess: function(res) {
            this.isVerified = true;
            if (typeof window.hideLoader === 'function') window.hideLoader();
            if (typeof window.showLoader === 'function') window.showLoader();
            $('#securityGateModal').modal('hide');

            let target = this.currentTarget || (window.SecurityGate && window.SecurityGate.currentTarget);
            if (target && target.jquery) {
                target = target[0];
            }

            let cb = this.onSuccessCb || (window.SecurityGate && window.SecurityGate.onSuccessCb) || window.SecurityGateOnSuccess || window.pendingTransferCallback;

            if (typeof cb === 'function') {
                this.onSuccessCb = null;
                if (window.SecurityGate) window.SecurityGate.onSuccessCb = null;
                if (typeof window.SecurityGateOnSuccess === 'function' && window.SecurityGateOnSuccess === cb) {
                    window.SecurityGateOnSuccess = null;
                }
                if (typeof window.pendingTransferCallback === 'function' && window.pendingTransferCallback === cb) {
                    window.pendingTransferCallback = null;
                }
                cb(res);
                return;
            } else if (typeof target === 'function') {
                target(res);
                return;
            } else if (typeof target === 'string') {
                try {
                    const elem = document.querySelector(target);
                    if (elem && (elem.tagName === 'FORM' || elem instanceof HTMLFormElement)) {
                        target = elem;
                    } else if (elem) {
                        elem.click();
                        return;
                    } else if (target.startsWith('/') || target.startsWith('http')) {
                        window.location.href = target;
                        return;
                    }
                } catch (e) {
                    if (target.startsWith('/') || target.startsWith('http')) {
                        window.location.href = target;
                        return;
                    }
                }
            }

            if (target && (target.tagName === 'FORM' || target instanceof HTMLFormElement)) {
                if (!$(target).find('input[name="security_verified"]').length) {
                    $('<input>').attr({ type: 'hidden', name: 'security_verified', value: '1' }).appendTo(target);
                } else {
                    $(target).find('input[name="security_verified"]').val('1');
                }
                HTMLFormElement.prototype.submit.call(target);
                return;
            }

            const fallbackForm = document.querySelector('#csbTransferReviewModal.show form') ||
                                 document.querySelector('#transferForm') ||
                                 document.querySelector('#csbFormMember') ||
                                 document.querySelector('#csbFormExternal') ||
                                 document.querySelector('#csbFormWire') ||
                                 document.querySelector('#csbFormZelle') ||
                                 document.querySelector('form.security-gated');

            if (fallbackForm) {
                if (!$(fallbackForm).find('input[name="security_verified"]').length) {
                    $('<input>').attr({ type: 'hidden', name: 'security_verified', value: '1' }).appendTo(fallbackForm);
                } else {
                    $(fallbackForm).find('input[name="security_verified"]').val('1');
                }
                HTMLFormElement.prototype.submit.call(fallbackForm);
                return;
            }

            const pendingConfirmBtn = document.querySelector('#csbReviewConfirmBtn');
            if (pendingConfirmBtn && typeof window.pendingTransferExecution === 'object' && window.pendingTransferExecution) {
                var exec = window.pendingTransferExecution;
                exec.callback(exec.details);
                return;
            }

            console.warn('SecurityGate verified successfully, but no target or callback was found to resume.');
            window.location.reload();
        },

        backToChoice: function() {
            if ($('#sg-method-selection').hasClass('d-none')) {
                this.reset();
                $('#sg-method-selection').removeClass('d-none');
                // Biometric Passkey button is ALWAYS visible in method selection
                $('#sg-choice-biometric').removeClass('d-none');
                $('#sg-email-verify, #sg-pin-verify, #sg-verify-btn').addClass('d-none');
                $('#sg-back-btn').text('Cancel');
            } else {
                $('#securityGateModal').modal('hide');
                if (!this.isVerified && typeof this.onCancelCb === 'function') {
                    const cancelCb = this.onCancelCb;
                    this.onCancelCb = null;
                    cancelCb();
                }
            }
        },

        resendEmail: function() {
            $('#sg-feedback').addClass('d-none');
            const token = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";
            const sendCodeUrl = "{{ Route::has('user.security_gate.send-code') ? route('user.security_gate.send-code') : url('/user/security-gate/send-code') }}";
            $.post(sendCodeUrl, {
                _token: token,
                gate_id: this.gateId,
                action: 'Verification'
            }).done(function(res) {
                $('#sg-feedback').text(res.message || 'Verification code sent to your email.').removeClass('d-none').css('color', '#28a745');
                setTimeout(() => $('#sg-feedback').addClass('d-none').css('color', '#ff3b30'), 5000);
            }).fail(function(xhr) {
                const res = xhr.responseJSON;
                if (res?.status === 'fallback') {
                    $('#sg-feedback').text(res.message).removeClass('d-none').addClass('alert-warning');
                    setTimeout(() => SecurityGate.selectMethod(res.method), 3000);
                } else if (res?.status === 'locked_out') {
                    $('#sg-feedback').text(res.message).removeClass('d-none');
                    setTimeout(() => window.location.reload(), 3000);
                } else {
                    $('#sg-feedback').text(res?.message || 'Error sending code.').removeClass('d-none').css('color', '#ff3b30');
                }
            });
        },

        submitVerification: function() {
            if (this.selectedMethod === 'pin' && this.currentPin.length < 4) return;
            const value = (this.selectedMethod === 'pin') ? this.currentPin : $('#sg-email-code-input').val();
            const feedback = $('#sg-feedback');
            const btn = $('#sg-verify-btn');
            const token = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

            feedback.addClass('d-none');
            btn.prop('disabled', true);

            const verifyUrl = "{{ Route::has('user.security_gate.verify') ? route('user.security_gate.verify') : url('/user/security-gate/verify') }}";
            $.post(verifyUrl, {
                _token: token,
                type: this.selectedMethod,
                value: value,
                gate_id: this.gateId
            }).done((res) => {
                if (res.status === 'success') {
                    this.completeSuccess(res);
                } else {
                    this.handleError(res.message || 'Verification failed.', res.status, res.method);
                }
            }).fail((xhr) => {
                btn.prop('disabled', false);
                const res = xhr.responseJSON;
                if (xhr.status === 419 || xhr.status === 401) {
                    window.location.reload();
                    return;
                }
                this.handleError(res?.message || 'Verification failed.', res?.status, res?.method);
            });
        },

        handleError: function(message, status = 'error', method = null) {
            const feedback = $('#sg-feedback');
            const modalBody = $('#sg-modal-body');
            feedback.text(message).removeClass('d-none').css('color', '#ff3b30');

            if (status === 'fallback' && method) {
                feedback.css('color', '#856404');
                setTimeout(() => {
                    feedback.addClass('d-none').css('color', '#ff3b30');
                    this.selectMethod(method);
                }, 3000);
            } else if (status === 'locked_out') {
                setTimeout(() => window.location.reload(), 3000);
            } else {
                modalBody.removeClass('shake');
                void modalBody[0].offsetWidth;
                modalBody.addClass('shake');
            }

            if (this.selectedMethod === 'pin') {
                this.currentPin = "";
                this.updateDots();
            } else {
                $('#sg-email-code-input').val('');
            }
            $('#sg-verify-btn').prop('disabled', false);
        }
    });

window.triggerSecurityGate = function(target, callback) {
    window.SecurityGate.gate(target, callback);
};

document.addEventListener('DOMContentLoaded', function() {
    var sgModalEl = document.getElementById('securityGateModal');
    if (sgModalEl) {
        sgModalEl.addEventListener('hidden.bs.modal', function() {
            if (!window.SecurityGate.isVerified && typeof window.SecurityGate.onCancelCb === 'function') {
                var cancelCb = window.SecurityGate.onCancelCb;
                window.SecurityGate.onCancelCb = null;
                cancelCb();
            }
        });
    }
});
</script>
