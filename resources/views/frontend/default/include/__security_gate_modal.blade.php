<!-- Security Gate Modal -->
<div class="modal fade" id="securityGateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark pt-3 px-3">Multi-Factor Authentication Verification</h5>
                <button type="button" class="btn-close me-2 mt-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 pt-0" id="sg-modal-body">
                <!-- Feedback Area -->
                <div id="sg-feedback" class="error-text small d-none mb-3 text-center"></div>

                <!-- Method Selection -->
                <div id="sg-method-selection">
                    <p class="text-secondary small mb-3 text-center">{{ __('Please select a verification method to continue.') }}</p>
                    <div class="d-grid gap-2">
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

                <div id="sg-email-verify" class="d-none text-center">
                    <p class="text-secondary small mb-1">{{ __('We\'ve sent a verification code to') }}</p>
                    <p class="fw-bold text-dark small mb-3">{{ substr(auth()->user()->email, 0, 3) . '***' . substr(auth()->user()->email, strpos(auth()->user()->email, '@')) }}</p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3" id="sg-otp-inputs">
                        <input type="text" maxlength="6" class="form-control form-control-lg text-center fw-bold fs-3 border-2 rounded-4 shadow-none" style="letter-spacing: 0.3em; width: 220px;" placeholder="000000" id="sg-email-code-input">
                    </div>
                    
                    <div class="mt-3">
                        <div class="mb-2">
                            <button type="button" class="btn btn-link text-primary text-decoration-none small fw-bold p-0" onclick="SecurityGate.resendEmail()">Resend Code</button>
                        </div>
                        <div class="mt-1 text-muted small" style="opacity: 0.5;">— or —</div>
                        <div class="mt-1">
                            <button type="button" class="btn btn-link text-muted text-decoration-none small" onclick="SecurityGate.backToChoice()">Use Passcode instead</button>
                        </div>
                    </div>
                </div>

                <div id="sg-pin-verify" class="d-none text-center">
                    <p class="text-secondary small mb-3">{{ __('Enter your 4-digit passcode to continue.') }}</p>
                    <div class="pin-display mb-4" style="display: flex; justify-content: center; gap: 20px;">
                        <div class="pin-dot" id="sg-dot-1"></div>
                        <div class="pin-dot" id="sg-dot-2"></div>
                        <div class="pin-dot" id="sg-dot-3"></div>
                        <div class="pin-dot" id="sg-dot-4"></div>
                    </div>

                    <div class="keypad mx-auto" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; max-width: 320px;">
                        @for($i = 1; $i <= 9; $i++)
                            <button type="button" class="key-btn" onclick="SecurityGate.pressKey({{ $i }})">{{ $i }}</button>
                        @endfor
                        <div class="key-btn invisible"></div>
                        <button type="button" class="key-btn" onclick="SecurityGate.pressKey(0)">0</button>
                        <button type="button" class="key-btn backspace" onclick="SecurityGate.backspace()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path><line x1="18" y1="9" x2="12" y2="15"></line><line x1="12" y1="9" x2="18" y2="15"></line></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="primary-btn w-100 mb-3 d-none" id="sg-verify-btn" onclick="SecurityGate.submitVerification()">
                    <span class="spinner-border spinner-border-sm d-none me-2"></span> {{ __('Verify Code') }}
                </button>
                <div class="w-100 text-center mt-3">
                    <a href="javascript:void(0)" class="text-muted text-decoration-none small" onclick="SecurityGate.backToChoice()" id="sg-back-btn">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.sg-choice-btn:hover { background-color: rgba(0, 84, 155, 0.05); transform: translateY(-2px); border-color: var(--primary-color) !important; }
.transition-all { transition: all 0.2s ease; }
.pin-dot { width: 14px; height: 14px; border: 2px solid var(--body-text-theme-color); border-radius: 50%; transition: all 0.2s ease; }
.pin-dot.filled { background-color: var(--body-text-theme-color); transform: scale(1.1); }
.error-text { color: #ff3b30 !important; font-weight: 700 !important; }
.key-btn { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 12px; font-size: 20px; font-weight: 600; color: var(--body-text-primary-color); cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; height: 60px; user-select: none; width: 100%; }
.key-btn:hover { background: #e9ecef; border-color: #dee2e6; }
.key-btn.invisible { visibility: hidden; }
.key-btn.backspace { color: var(--primary-button-color); }
.key-btn.backspace svg { width: 24px; height: 24px; stroke-width: 2.5px; }
.shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }
@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>
