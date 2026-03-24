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
                    <p class="text-muted small mb-3 text-center">Please select a verification method.</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary p-3 rounded-4 d-flex align-items-center gap-3 text-start border-2 shadow-sm sg-choice-btn" onclick="SecurityGate.selectMethod('email')" id="sg-choice-email">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                                <i class="fas fa-envelope text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark lh-1 mb-1">Email Verification Code</div>
                                <div class="small text-muted">Get a 6-digit code via email</div>
                            </div>
                        </button>

                        <button type="button" class="btn btn-outline-primary p-3 rounded-4 d-flex align-items-center gap-3 text-start border-2 shadow-sm sg-choice-btn" onclick="SecurityGate.selectMethod('pin')" id="sg-choice-pin" {{ !auth()->user()->transaction_pin ? 'disabled' : '' }}>
                            <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                                <i class="fas fa-key text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark lh-1 mb-1">Security Passcode</div>
                                <div class="small text-muted">{{ auth()->user()->transaction_pin ? 'Enter your 4-digit Passcode' : 'Passcode not set up yet' }}</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Email Code Input -->
                <div id="sg-email-verify" class="d-none text-center">
                    <div class="small text-muted mb-1">Code sent to</div>
                    <div class="fw-bold text-dark mb-3">{{ substr(auth()->user()->email, 0, 3) . '***' . substr(auth()->user()->email, strpos(auth()->user()->email, '@')) }}</div>
                    
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

                <!-- PIN Input -->
                <div id="sg-pin-verify" class="d-none">
                    <div class="pin-display mb-3" style="display: flex; justify-content: center; gap: 15px;">
                        <div class="pin-dot" id="sg-dot-1" style="width: 14px; height: 14px; border: 2px solid var(--body-text-theme-color); border-radius: 50%;"></div>
                        <div class="pin-dot" id="sg-dot-2" style="width: 14px; height: 14px; border: 2px solid var(--body-text-theme-color); border-radius: 50%;"></div>
                        <div class="pin-dot" id="sg-dot-3" style="width: 14px; height: 14px; border: 2px solid var(--body-text-theme-color); border-radius: 50%;"></div>
                        <div class="pin-dot" id="sg-dot-4" style="width: 14px; height: 14px; border: 2px solid var(--body-text-theme-color); border-radius: 50%;"></div>
                    </div>

                    <div class="keypad mx-auto" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; max-width: 220px;">
                        @for($i = 1; $i <= 9; $i++)
                            <button type="button" class="btn btn-outline-light text-dark fw-bold rounded-circle p-0" style="width: 55px; height: 55px; font-size: 20px; border-color: #f1f3f5; background: #fff;" onclick="SecurityGate.pressKey({{ $i }})">{{ $i }}</button>
                        @endfor
                        <div style="width: 55px;"></div>
                        <button type="button" class="btn btn-outline-light text-dark fw-bold rounded-circle p-0" style="width: 55px; height: 55px; font-size: 20px; border-color: #f1f3f5; background: #fff;" onclick="SecurityGate.pressKey(0)">0</button>
                        <button type="button" class="btn btn-outline-light text-danger rounded-circle p-0 border-0" style="width: 55px; height: 55px; background: transparent;" onclick="SecurityGate.backspace()">
                            <i class="fas fa-backspace" style="font-size: 22px;"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm d-none" id="sg-verify-btn" onclick="SecurityGate.submitVerification()">
                    <span class="spinner-border spinner-border-sm d-none me-2"></span> Verify & Proceed
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
.pin-dot.filled { background-color: var(--body-text-theme-color); transform: scale(1.1); }
.error-text { color: #ff3b30 !important; font-weight: 700 !important; }
.shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }
@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>
