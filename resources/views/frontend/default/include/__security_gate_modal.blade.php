<!-- Security Gate Modal -->
<div class="modal fade" id="securityGateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark pt-3 px-3">Multi-Factor Authentication Verification</h5>
                <button type="button" class="btn-close me-2 mt-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0" id="sg-modal-body">
                <!-- Feedback Area -->
                <div id="sg-feedback" class="error-text small d-none text-center px-4 pt-3"></div>

                <div class="mfa-card-inner p-4 pt-2">
                    <!-- Method Selection -->
                    <div id="sg-method-selection">
                        <h4 class="fw-bold text-dark mb-2">{{ __('Verification Required') }}</h4>
                        <p class="text-secondary small mb-4">{{ __('Please select a verification method to continue.') }}</p>
                        <div class="d-grid gap-3">
                            <button type="button" class="btn btn-outline-primary p-3 rounded-3 d-flex align-items-center gap-3 text-start border-2 shadow-sm sg-choice-btn" onclick="SecurityGate.selectMethod('email')" id="sg-choice-email">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                                    <i class="fas fa-envelope text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark lh-1 mb-1">{{ __('Email Verification') }}</div>
                                    <div class="small text-muted">{{ __('6-digit code via email') }}</div>
                                </div>
                            </button>

                            <button type="button" class="btn btn-outline-primary p-3 rounded-3 d-flex align-items-center gap-3 text-start border-2 shadow-sm sg-choice-btn" onclick="SecurityGate.selectMethod('pin')" id="sg-choice-pin" {{ !auth()->user()->transaction_pin ? 'disabled' : '' }}>
                                <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                                    <i class="fas fa-key text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark lh-1 mb-1">{{ __('Security Passcode') }}</div>
                                    <div class="small text-muted">{{ auth()->user()->transaction_pin ? __('Enter your 4-digit Passcode') : __('Passcode not set up yet') }}</div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Email Verification Section -->
                    <div id="sg-email-verify" class="d-none text-center">
                        <h4 class="fw-bold text-dark mb-2">{{ __('Verification Required') }}</h4>
                        <p class="text-secondary small mb-1">{{ __('We\'ve sent a verification code to') }}</p>
                        <p class="fw-bold text-dark small mb-4">{{ substr(auth()->user()->email, 0, 3) . '***' . substr(auth()->user()->email, strpos(auth()->user()->email, '@')) }}</p>
                        
                        <div class="mb-4">
                            <input type="text" id="sg-email-code-input" class="input-box otp-input" placeholder="000000" maxlength="6" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        
                        <button type="button" class="primary-btn w-100 mb-3" id="sg-verify-btn" onclick="SecurityGate.submitVerification()">{{ __('Verify Code') }}</button>

                        <div class="mt-4">
                            <div class="mb-2">
                                <a href="javascript:void(0)" class="forgot-link" onclick="SecurityGate.resendEmail()">{{ __('Resend Code') }}</a>
                            </div>
                            <div class="mt-2 text-muted small" style="opacity: 0.5;">— or —</div>
                            <div class="mt-2">
                                <a href="javascript:void(0)" class="forgot-link" onclick="SecurityGate.backToChoice()">{{ __('Use Passcode instead') }}</a>
                            </div>
                        </div>
                    </div>

                    <!-- PIN Verification Section -->
                    <div id="sg-pin-verify" class="d-none text-center">
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
    /* Literal Copy from mfa.blade.php */
    .mfa-card-inner { text-align: center; width: 100%; }
    .pin-display-wrapper { display: flex; justify-content: center; gap: 20px; margin-bottom: 25px; }
    .pin-dot { width: 14px; height: 14px; border: 2px solid #555; border-radius: 50%; transition: all 0.2s ease; }
    .pin-dot.filled { background-color: #555; transform: scale(1.1); }
    .keypad { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; max-width: 280px; margin-left: auto; margin-right: auto; }
    .key-btn { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 12px; font-size: 20px; font-weight: 600; color: #333; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; height: 52px; user-select: none; }
    .key-btn:hover { background: #e9ecef; border-color: #dee2e6; }
    .key-btn:active { background: #dee2e6; transform: scale(0.95); }
    .key-btn.invisible { visibility: hidden; }
    .key-btn.backspace { color: #e31837; }
    .key-btn.backspace svg { width: 24px; height: 24px; stroke-width: 2.5px; }
    .forgot-link { color: #0056b3; text-decoration: none; font-size: 14px; font-weight: 500; cursor: pointer; display: block; margin: 5px 0; }
    .forgot-link:hover { text-decoration: underline; }
    .input-box.otp-input { letter-spacing: 8px; font-size: 24px; text-align: center; font-weight: 700; max-width: 240px; margin: 0 auto; border: 2px solid #ddd; border-radius: 8px; padding: 10px; }
    .error-text { color: #ff3b30 !important; font-weight: 700 !important; margin-top: 8px; min-height: 20px; }
    .primary-btn { background: #e31837 !important; color: #fff !important; border: none !important; border-radius: 8px !important; padding: 12px !important; font-weight: 700 !important; transition: all 0.2s; }
    .primary-btn:hover { opacity: 0.9; transform: translateY(-1px); }
    .shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }
    @keyframes shake {
        10%, 90% { transform: translate3d(-1px, 0, 0); }
        20%, 80% { transform: translate3d(2px, 0, 0); }
        30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
        40%, 60% { transform: translate3d(4px, 0, 0); }
    }
    .sg-choice-btn:hover { background-color: rgba(0, 84, 155, 0.05); transform: translateY(-2px); border-color: #0056b3 !important; }
</style>
