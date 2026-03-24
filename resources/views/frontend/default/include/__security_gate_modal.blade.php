<!-- Security Gate Modal -->
<div class="modal fade" id="securityGateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark pt-3 px-3">Multi-Factor Authentication Verification</h5>
                <button type="button" class="btn-close me-2 mt-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <p class="text-muted small mb-4 px-1">Please select a method to verify this action.</p>
                
                <!-- Method Selection -->
                <div id="sg-method-selection">
                    <div class="d-grid gap-3">
                        <button type="button" class="btn btn-outline-primary p-3 rounded-4 d-flex align-items-center gap-3 text-start transition-all sg-choice-btn" onclick="SecurityGate.selectMethod('email')" id="sg-choice-email">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                                <i class="fas fa-envelope text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Email Verification Code</div>
                                <div class="small text-muted">Get a 6-digit code via email</div>
                            </div>
                        </button>

                        <button type="button" class="btn btn-outline-primary p-3 rounded-4 d-flex align-items-center gap-3 text-start transition-all sg-choice-btn" onclick="SecurityGate.selectMethod('pin')" id="sg-choice-pin" {{ !auth()->user()->transaction_pin ? 'disabled' : '' }}>
                            <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                                <i class="fas fa-key text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Multi-Factor Authentication PIN</div>
                                <div class="small text-muted">{{ auth()->user()->transaction_pin ? 'Enter your 4-digit PIN' : 'PIN not set up yet' }}</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Email Code Input -->
                <div id="sg-email-verify" class="d-none">
                    <div class="text-center mb-4">
                        <div class="small text-muted mb-2">Verification code sent to</div>
                        <div class="fw-bold text-dark">{{ substr(auth()->user()->email, 0, 3) . '***' . substr(auth()->user()->email, strpos(auth()->user()->email, '@')) }}</div>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mb-4" id="sg-otp-inputs">
                        <input type="text" maxlength="6" class="form-control form-control-lg text-center fw-bold fs-2 border-2 rounded-4 shadow-none" style="letter-spacing: 0.5em;" placeholder="000000" id="sg-email-code-input">
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn btn-link text-primary text-decoration-none small fw-bold" onclick="SecurityGate.resendEmail()">Resend Code</button>
                    </div>
                </div>

                <!-- PIN Input (New Premium Design) -->
                <div id="sg-pin-verify" class="d-none">
                    <div class="text-center mb-4">
                        <label class="form-label fw-bold text-dark">{{ __('Enter 4-Digit Passcode') }}</label>
                    </div>
                    
                    <div class="pin-display mb-4" style="display: flex; justify-content: center; gap: 20px;">
                        <div class="pin-dot" id="sg-dot-1" style="width: 16px; height: 16px; border: 2px solid var(--body-text-theme-color); border-radius: 50%;"></div>
                        <div class="pin-dot" id="sg-dot-2" style="width: 16px; height: 16px; border: 2px solid var(--body-text-theme-color); border-radius: 50%;"></div>
                        <div class="pin-dot" id="sg-dot-3" style="width: 16px; height: 16px; border: 2px solid var(--body-text-theme-color); border-radius: 50%;"></div>
                        <div class="pin-dot" id="sg-dot-4" style="width: 16px; height: 16px; border: 2px solid var(--body-text-theme-color); border-radius: 50%;"></div>
                    </div>

                    <div class="keypad mx-auto" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; max-width: 240px;">
                        @for($i = 1; $i <= 9; $i++)
                            <button type="button" class="btn btn-outline-light text-dark fw-bold rounded-circle p-0" style="width: 60px; height: 60px; font-size: 20px; border-color: #eee;" onclick="SecurityGate.pressKey({{ $i }})">{{ $i }}</button>
                        @endfor
                        <div style="width: 60px;"></div>
                        <button type="button" class="btn btn-outline-light text-dark fw-bold rounded-circle p-0" style="width: 60px; height: 60px; font-size: 20px; border-color: #eee;" onclick="SecurityGate.pressKey(0)">0</button>
                        <button type="button" class="btn btn-outline-light text-muted rounded-circle p-0 border-0" style="width: 60px; height: 60px;" onclick="SecurityGate.backspace()">
                            <i class="fas fa-backspace"></i>
                        </button>
                    </div>
                </div>

                <div id="sg-feedback" class="alert alert-danger d-none mt-3 p-2 small border-0 text-center rounded-3"></div>
            </div>

            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm d-none" id="sg-verify-btn" onclick="SecurityGate.submitVerification()">
                    <span class="spinner-border spinner-border-sm d-none me-2"></span> Verify & Proceed
                </button>
                <button type="button" class="btn btn-link w-100 text-muted text-decoration-none small mt-2" onclick="SecurityGate.backToChoice()" id="sg-back-btn">Cancel</button>
            </div>
        </div>
    </div>
</div>

<style>
.sg-choice-btn:hover { background-color: rgba(0, 84, 155, 0.05); transform: translateY(-2px); border-color: var(--primary-color) !important; }
.transition-all { transition: all 0.2s ease; }
#sg-otp-inputs input { width: 100%; max-width: 250px; }
</style>
