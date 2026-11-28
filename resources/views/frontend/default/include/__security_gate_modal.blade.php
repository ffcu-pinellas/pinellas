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
$method = $method ?? 'pin';
@endphp

@if($isCsb)
    <!-- CSB Security Gate Modal - Exact Replica of Login MFA -->
    <div class="modal fade" id="securityGateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 3px; overflow: hidden;">
                <div class="modal-body p-0">
                    <div class="csb-auth-modal">
                        <!-- Modal Red Header -->
                        <div class="csb-modal-header">
                            <span class="csb-modal-title">AUTHENTICATE</span>
                            <button type="button" class="csb-modal-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                        </div>

                        <div class="csb-modal-body">
                            <!-- Green Shield with Key -->
                            <div class="csb-shield-wrap">
                                <svg class="csb-shield-icon" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2L4 5V11C4 16.5 7.5 21.5 12 22.8C16.5 21.5 20 16.5 20 11V5L12 2Z" stroke="#5cb85c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="10.5" r="2.2" stroke="#5cb85c" stroke-width="1.8"/>
                                    <path d="M12 12.7V17M12 15H13.8" stroke="#5cb85c" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>

                            <p class="csb-security-notice">
                                For added security we have enabled Multi-Factor Authentication (MFA) for this action.
                            </p>

                            <!-- Section: SELECT A DEVICE -->
                            <div class="csb-section-label">SELECT A DEVICE</div>
                            <div class="csb-device-box">
                                <div class="csb-device-info">
                                    <span class="csb-device-tag">DEVICE</span>
                                    <span class="csb-device-name">{{ $sgAuthUser->first_name ?: ($sgAuthUser->username ?: 'Registered Device') }}</span>
                                </div>
                                <div class="csb-device-arrow">&rarr;</div>
                            </div>

                            <!-- Remember Device -->
                            <div class="csb-remember-row">
                                <label class="csb-remember-label">
                                    <input type="checkbox" id="trustDevice" name="trust_device" checked>
                                    <span>Remember Device</span>
                                </label>
                            </div>

                            <!-- Section: CONFIRM VIA -->
                            <div class="csb-section-label">CONFIRM VIA</div>
                            <p class="csb-confirm-desc">
                                We need to confirm that you have access to the selected device by entering your security PIN, receiving a one-time passcode via email, or using biometrics.
                            </p>

                            <!-- 3 Action Cards (Exact Screenshot Aesthetic) -->
                            <div class="csb-methods-grid">
                                <!-- Card 1: Security PIN -->
                                <div class="csb-method-card @if($method === 'pin') active @endif" id="cardPin" onclick="SecurityGate.selectMethod('pin')" {{ !$sgHasPin ? 'disabled' : '' }}>
                                    <div class="csb-method-icon">
                                        <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                    </div>
                                    <div class="csb-method-text">Security PIN</div>
                                </div>

                                <!-- Card 2: Passcode via Email -->
                                <div class="csb-method-card @if($method === 'email') active @endif" id="cardEmail" onclick="SecurityGate.selectMethod('email')">
                                    <div class="csb-method-icon">
                                        <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                                            <path d="M12 18h.01"/>
                                            <path d="M17 6h-6"/>
                                        </svg>
                                    </div>
                                    <div class="csb-method-text">Passcode via Email</div>
                                </div>

                                <!-- Card 3: Biometrics -->
                                <div class="csb-method-card @if($method === 'biometric') active @endif" id="cardBiometric" onclick="SecurityGate.selectMethod('biometric')">
                                    <div class="csb-method-icon">
                                        <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 11c0 3.866-3.134 7-7 7a9.98 9.98 0 0 1-2-.2"/>
                                            <path d="M12 11a7 7 0 0 1 7 7c0 .068 0 .135-.002.202"/>
                                            <path d="M12 7c2.21 0 4 1.79 4 4a6.98 6.98 0 0 1-.36 2.2"/>
                                            <path d="M8.36 13.2A6.98 6.98 0 0 1 8 11c0-2.21 1.79-4 4-4"/>
                                            <path d="M12 3a9 9 0 0 1 9 9c0 1.25-.26 2.45-.73 3.53"/>
                                        </svg>
                                    </div>
                                    <div class="csb-method-text">Biometrics</div>
                                </div>
                            </div>

                            <!-- Dynamic Instruction Line -->
                            <p class="csb-instruction-text" id="methodInstruction">
                                Please enter the 4-digit security PIN associated with your account.
                            </p>

                            <!-- Passcode Input Section (for PIN & Email) -->
                            <div id="passcodeSection" style="display: block;">
                                <div class="csb-section-label">ENTER PASSCODE</div>
                                <div class="csb-passcode-helper" id="passcodeHelper">
                                    <span>Enter your 4-digit security PIN to authorize this device</span><br>
                                    <span>Or choose to receive a one-time passcode via email</span>
                                </div>

                                <div class="csb-input-container">
                                    <input
                                        type="password"
                                        id="passcodeInput"
                                        placeholder="PASSCODE"
                                        class="csb-passcode-input"
                                        autocomplete="one-time-code"
                                        maxlength="8"
                                        autofocus
                                    >
                                </div>

                                <!-- Resend Email Link -->
                                <div id="emailResendContainer" class="csb-resend-row" style="display: none;">
                                    <span>Didn't receive code? </span>
                                    <button type="button" class="csb-resend-link" id="btnResendEmail" onclick="SecurityGate.resendEmail()">Resend Code</button>
                                    <span id="resendTimerSpan" style="color: #718096; margin-left: 4px;"></span>
                                </div>
                            </div>

                            <!-- Biometric Section -->
                            <div id="biometricSection" style="display: none;" class="csb-bio-box">
                                <div class="csb-bio-icon-pulse">
                                    <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="#5cb85c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 11c0 3.866-3.134 7-7 7a9.98 9.98 0 0 1-2-.2"/>
                                        <path d="M12 11a7 7 0 0 1 7 7c0 .068 0 .135-.002.202"/>
                                        <path d="M12 7c2.21 0 4 1.79 4 4a6.98 6.98 0 0 1-.36 2.2"/>
                                        <path d="M8.36 13.2A6.98 6.98 0 0 1 8 11c0-2.21 1.79-4 4-4"/>
                                        <path d="M12 3a9 9 0 0 1 9 9c0 1.25-.26 2.45-.73 3.53"/>
                                    </svg>
                                </div>
                                <button type="button" class="csb-bio-btn-trigger" id="btnTriggerBio" onclick="SecurityGate.triggerBiometricAuth()">
                                    Authenticate with Touch ID / Face ID
                                </button>
                                <div class="csb-bio-status" id="bioStatusText">Click above to verify Face ID / Touch ID</div>
                            </div>

                            <!-- Error Box -->
                            <div id="csbErrorAlert" class="csb-error-box"></div>

                            <!-- SUBMIT Button -->
                            <button type="button" id="btnSubmitMfa" class="csb-submit-btn" onclick="SecurityGate.submitVerification()">
                                <span id="btnSubmitText">SUBMIT</span>
                                <span id="btnSubmitSpinner" class="csb-spinner" style="display: none;"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* CSB Security Gate Modal Styles - Exact Copy from Login MFA */
    .csb-auth-modal {
        width: 100%;
        max-width: 540px;
        background: #ffffff;
        box-shadow: 0 12px 42px rgba(0, 0, 0, 0.22);
        border-radius: 3px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        margin: auto;
    }

    /* Modal Red Header */
    .csb-modal-header {
        background-color: #c8232c;
        height: 46px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        position: relative;
    }

    .csb-modal-title {
        color: #ffffff;
        font-size: 13.5px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        margin: 0 auto;
    }

    .csb-modal-close {
        color: #ffffff;
        font-size: 17px;
        text-decoration: none;
        line-height: 1;
        opacity: 0.9;
        transition: opacity 0.2s;
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
    }

    .csb-modal-close:hover {
        opacity: 1;
    }

    /* Modal Body */
    .csb-modal-body {
        padding: 28px 32px 34px;
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }

    /* Green Shield Icon */
    .csb-shield-wrap {
        text-align: center;
        margin-bottom: 14px;
    }

    .csb-shield-icon {
        width: 52px;
        height: 52px;
        display: inline-block;
    }

    /* Notice text */
    .csb-security-notice {
        font-size: 13.5px;
        color: #3e4857;
        text-align: center;
        line-height: 1.45;
        margin-bottom: 22px;
        padding: 0 10px;
    }

    /* Section Headings */
    .csb-section-label {
        font-size: 11px;
        font-weight: 700;
        color: #4a5568;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 8px;
    }

    /* Device Box */
    .csb-device-box {
        background-color: #eef2f6;
        border: 1px solid #d8e0e8;
        border-radius: 2px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }

    .csb-device-info {
        display: flex;
        flex-direction: column;
    }

    .csb-device-tag {
        font-size: 10px;
        font-weight: 700;
        color: #718096;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .csb-device-name {
        font-size: 15px;
        font-weight: 600;
        color: #1a202c;
    }

    .csb-device-arrow {
        color: #718096;
        font-size: 16px;
        font-weight: bold;
    }

    /* Remember Device Row */
    .csb-remember-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 22px;
    }

    .csb-remember-label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 13.5px;
        color: #2d3748;
        user-select: none;
    }

    .csb-remember-label input[type="checkbox"] {
        width: 17px;
        height: 17px;
        accent-color: #1a2d54;
        cursor: pointer;
    }

    /* Confirm Via Description */
    .csb-confirm-desc {
        font-size: 12.5px;
        color: #4a5568;
        line-height: 1.45;
        margin-bottom: 14px;
    }

    /* Methods Grid (3 Side-by-Side Cards) */
    .csb-methods-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 18px;
    }

    .csb-method-card {
        border-radius: 2px;
        padding: 14px 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
        min-height: 82px;
    }

    .csb-method-card[disabled] {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* Active State (Matches green card from screenshot) */
    .csb-method-card.active {
        background-color: #5cb85c;
        border: 1px solid #4cae4c;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(92, 184, 92, 0.35);
    }

    .csb-method-card.active .csb-method-icon svg {
        stroke: #ffffff;
        fill: none;
    }

    .csb-method-card.active .csb-method-text {
        color: #ffffff;
        font-weight: 600;
    }

    /* Inactive State (Matches white/grey card from screenshot) */
    .csb-method-card:not(.active) {
        background-color: #ffffff;
        border: 1px solid #c9d2db;
        color: #1a2d54;
    }

    .csb-method-card:not(.active):hover {
        border-color: #9cb1c4;
        background-color: #f8fafc;
    }

    .csb-method-card:not(.active) .csb-method-icon svg {
        stroke: #1a2d54;
        fill: none;
    }

    .csb-method-card:not(.active) .csb-method-text {
        color: #1a2d54;
        font-weight: 500;
    }

    .csb-method-icon {
        width: 28px;
        height: 28px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .csb-method-icon svg {
        width: 24px;
        height: 24px;
    }

    .csb-method-text {
        font-size: 11.5px;
        line-height: 1.25;
    }

    /* Instruction Text below methods */
    .csb-instruction-text {
        font-size: 12.5px;
        color: #4a5568;
        line-height: 1.45;
        margin-bottom: 16px;
    }

    /* Passcode Helper Lines */
    .csb-passcode-helper {
        font-size: 12px;
        color: #556274;
        line-height: 1.4;
        margin-bottom: 10px;
    }

    /* Passcode Input Field */
    .csb-input-container {
        position: relative;
        margin-bottom: 14px;
    }

    .csb-passcode-input {
        width: 100%;
        height: 48px;
        background-color: #ffffff;
        border: 1px solid #b8bec5;
        border-radius: 2px;
        padding: 0 14px;
        font-size: 15px;
        font-family: inherit;
        color: #1a202c;
        letter-spacing: 1.5px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .csb-passcode-input:focus {
        border-color: #c8232c;
        box-shadow: 0 0 0 2px rgba(200, 35, 44, 0.15);
    }

    .csb-passcode-input::placeholder {
        color: #8894a4;
        letter-spacing: 0.8px;
        font-size: 13.5px;
        font-weight: 500;
    }

    /* Resend Row */
    .csb-resend-row {
        font-size: 12.5px;
        color: #4a5568;
        margin-top: -6px;
        margin-bottom: 14px;
    }

    .csb-resend-link {
        color: #1a2d54;
        font-weight: 600;
        text-decoration: underline;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
        font-size: inherit;
    }

    .csb-resend-link:hover {
        color: #c8232c;
    }

    /* Biometric Trigger Card */
    .csb-bio-box {
        background-color: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 4px;
        padding: 20px 16px;
        text-align: center;
        margin-bottom: 16px;
    }

    .csb-bio-icon-pulse {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        background-color: rgba(92, 184, 92, 0.12);
        border: 2px solid rgba(92, 184, 92, 0.35);
        margin: 0 auto 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: csbPulse 2s infinite ease-in-out;
    }

    @keyframes csbPulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(92, 184, 92, 0.3); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(92, 184, 92, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(92, 184, 92, 0); }
    }

    .csb-bio-btn-trigger {
        background-color: #5cb85c;
        color: #ffffff;
        border: none;
        border-radius: 2px;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .csb-bio-btn-trigger:hover {
        background-color: #4cae4c;
    }

    .csb-bio-btn-trigger:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .csb-bio-status {
        font-size: 12px;
        color: #64748b;
        margin-top: 8px;
    }

    /* Error Banner */
    .csb-error-box {
        background-color: #fff1f2;
        border: 1px solid #fecdd3;
        color: #b91c1c;
        padding: 10px 14px;
        border-radius: 2px;
        font-size: 12.5px;
        font-weight: 500;
        margin-bottom: 14px;
        display: none;
    }

    /* Submit Button */
    .csb-submit-btn {
        width: 100%;
        height: 46px;
        background-color: #e2e8f0;
        color: #4a5568;
        border: none;
        border-radius: 2px;
        font-size: 13.5px;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .csb-submit-btn.ready,
    .csb-submit-btn:hover:not(:disabled) {
        background-color: #c8232c;
        color: #ffffff;
    }

    .csb-submit-btn:disabled {
        cursor: not-allowed;
        opacity: 0.65;
    }

    .csb-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-top: 2px solid transparent;
        border-radius: 50%;
        animation: csbSpin 0.7s linear infinite;
        display: inline-block;
    }

    @keyframes csbSpin {
        to { transform: rotate(360deg); }
    }

    @media (max-width: 576px) {
        .csb-modal-body {
            padding: 20px 18px 26px;
        }
        .csb-methods-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        .csb-method-card {
            min-height: 54px;
            flex-direction: row;
            gap: 10px;
            padding: 10px 14px;
        }
        .csb-method-icon {
            margin-bottom: 0;
        }
    }
    </style>

    <script>
    // CSB Security Gate JS - Adapted from Login MFA for Security Gate Routes
    window.SecurityGate = window.SecurityGate || {};

    Object.assign(window.SecurityGate, {
        currentTarget: window.SecurityGate.currentTarget || null,
        onSuccessCb: window.SecurityGate.onSuccessCb || null,
        onCancelCb: window.SecurityGate.onCancelCb || null,
        isVerified: false,
        gateId: null,
        selectedMethod: 'pin',
        currentPin: "",
        cachedChallenge: null,
        emailDispatched: false,
        resendCooldown: 0,
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        maskedEmail: "{{ $sgMaskedEmail }}",

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

            const preference = window.UserSecurityPreference;
            if (preference === 'biometrics' || preference === 'biometric') {
                this.selectMethod('biometric');
            } else if (preference === 'email') {
                this.selectMethod('email');
            } else if (preference === 'pin') {
                this.selectMethod('pin');
            } else {
                $('#securityGateModal').modal('show');
            }
        },

        reset: function() {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            this.selectedMethod = 'pin';
            this.currentPin = "";
            this.cachedChallenge = null;
            this.emailDispatched = false;
            this.resendCooldown = 0;

            this.selectMethod('pin');
            $('#securityGateModal').modal('show');

            const errEl = document.getElementById('csbErrorAlert');
            if (errEl) {
                errEl.style.display = 'none';
                errEl.textContent = '';
            }
        },

        selectMethod: function(method) {
            this.selectedMethod = method;
            this.hideError();

            // Toggle card classes
            document.querySelectorAll('.csb-method-card').forEach(c => c.classList.remove('active'));
            if (method === 'pin') document.getElementById('cardPin').classList.add('active');
            if (method === 'email') document.getElementById('cardEmail').classList.add('active');
            if (method === 'biometric') document.getElementById('cardBiometric').classList.add('active');

            const instr = document.getElementById('methodInstruction');
            const passcodeSec = document.getElementById('passcodeSection');
            const bioSec = document.getElementById('biometricSection');
            const emailResend = document.getElementById('emailResendContainer');
            const passInput = document.getElementById('passcodeInput');
            const submitBtn = document.getElementById('btnSubmitMfa');
            const helper = document.getElementById('passcodeHelper');

            if (method === 'biometric') {
                instr.textContent = "Please authenticate using Touch ID, Face ID, or your device biometric sensor.";
                passcodeSec.style.display = "none";
                bioSec.style.display = "block";
                submitBtn.style.display = "none";
                const bioStatus = document.getElementById('bioStatusText');
                if (bioStatus) bioStatus.textContent = "Click above to verify Face ID / Touch ID";
                const triggerBtn = document.getElementById('btnTriggerBio');
                if (triggerBtn) {
                    triggerBtn.disabled = false;
                    triggerBtn.textContent = "Authenticate with Touch ID / Face ID";
                }
            } else {
                bioSec.style.display = "none";
                passcodeSec.style.display = "block";
                submitBtn.style.display = "flex";

                if (method === 'email') {
                    instr.textContent = "Please enter the 6-digit one-time passcode sent to your registered email (" + this.maskedEmail + ").";
                    if (helper) {
                        helper.innerHTML = "<span>Enter the 6-digit passcode sent to your email to authorize this device</span><br><span>Or select Security PIN or Biometrics above</span>";
                    }
                    emailResend.style.display = "block";
                    if (!this.emailDispatched) {
                        this.resendEmail(true);
                    }
                } else {
                    instr.textContent = "Please enter the 4-digit security PIN associated with your account.";
                    if (helper) {
                        helper.innerHTML = "<span>Enter your 4-digit security PIN to authorize this device</span><br><span>Or choose to receive a one-time passcode via email</span>";
                    }
                    emailResend.style.display = "none";
                }

                passInput.value = "";
                passInput.focus();
                this.updateSubmitButtonState();
            }
        },

        updateSubmitButtonState: function() {
            const passInput = document.getElementById('passcodeInput');
            const submitBtn = document.getElementById('btnSubmitMfa');
            if (passInput && passInput.value.trim().length >= 4) {
                submitBtn.classList.add('ready');
            } else {
                submitBtn.classList.remove('ready');
            }
        },

        hideError: function() {
            const errEl = document.getElementById('csbErrorAlert');
            if (errEl) {
                errEl.style.display = 'none';
                errEl.textContent = '';
            }
        },

        showError: function(msg) {
            const errEl = document.getElementById('csbErrorAlert');
            if (errEl) {
                errEl.textContent = msg || "The passcode entered is invalid. Please try again.";
                errEl.style.display = 'block';
            }
        },

        setSubmitting: function(isSubmitting) {
            const btn = document.getElementById('btnSubmitMfa');
            const text = document.getElementById('btnSubmitText');
            const spinner = document.getElementById('btnSubmitSpinner');
            if (btn) btn.disabled = isSubmitting;
            if (isSubmitting) {
                if (text) text.style.display = 'none';
                if (spinner) spinner.style.display = 'inline-block';
            } else {
                if (text) text.style.display = 'inline';
                if (spinner) spinner.style.display = 'none';
            }
        },

        submitVerification: function() {
            this.hideError();
            const inputVal = document.getElementById('passcodeInput').value.trim();
            if (!inputVal) {
                this.showError("Please enter your passcode to proceed.");
                document.getElementById('passcodeInput').focus();
                return;
            }

            const trust = document.getElementById('trustDevice').checked ? 1 : 0;
            this.setSubmitting(true);

            const type = (this.selectedMethod === 'email') ? 'email' : 'pin';
            const verifyUrl = "{{ Route::has('user.security_gate.verify') ? route('user.security_gate.verify') : url('/user/security-gate/verify') }}";

            fetch(verifyUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": this.csrfToken
                },
                body: JSON.stringify({
                    type: type,
                    value: inputVal,
                    gate_id: this.gateId,
                    trust_device: trust
                })
            })
            .then(res => res.json())
            .then(data => {
                this.setSubmitting(false);
                if (data.status === 'success') {
                    this.completeSuccess(data);
                } else {
                    this.showError(data.message || "Invalid passcode entered. Please check and try again.");
                    document.getElementById('passcodeInput').value = "";
                    document.getElementById('passcodeInput').focus();
                    this.updateSubmitButtonState();
                }
            })
            .catch(err => {
                this.setSubmitting(false);
                this.showError("A connection error occurred. Please try again.");
            });
        },

        resendEmail: function(silent = false) {
            if (this.resendCooldown > 0) return;

            const btn = document.getElementById('btnResendEmail');
            const timerSpan = document.getElementById('resendTimerSpan');
            if (btn) btn.disabled = true;

            const resendUrl = "{{ Route::has('user.security_gate.send-code') ? route('user.security_gate.send-code') : url('/user/security-gate/send-code') }}";

            fetch(resendUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": this.csrfToken
                },
                body: JSON.stringify({
                    gate_id: this.gateId,
                    action: "Verification"
                })
            })
            .then(res => res.json())
            .then(data => {
                this.emailDispatched = true;
                if (!silent && data.message) {
                    this.hideError();
                }
                this.startResendTimer(60);
            })
            .catch(() => {
                if (btn) btn.disabled = false;
            });
        },

        startResendTimer: function(seconds) {
            this.resendCooldown = seconds;
            const btn = document.getElementById('btnResendEmail');
            const timerSpan = document.getElementById('resendTimerSpan');
            if (btn) btn.style.display = "none";
            if (timerSpan) timerSpan.textContent = `(${this.resendCooldown}s)`;

            const interval = setInterval(() => {
                this.resendCooldown--;
                if (this.resendCooldown <= 0) {
                    clearInterval(interval);
                    if (btn) {
                        btn.style.display = "inline";
                        btn.disabled = false;
                    }
                    if (timerSpan) timerSpan.textContent = "";
                } else {
                    if (timerSpan) timerSpan.textContent = `(${this.resendCooldown}s)`;
                }
            }, 1000);
        },

        prepareBiometricChallenge: function() {
            const self = this;
            const token = this.csrfToken;
            if (!token) return;

            const challengeUrl = "{{ Route::has('user.security_gate.biometric.challenge.direct') ? route('user.security_gate.biometric.challenge.direct') : url('/user/security-gate/biometric/challenge') }}";

            fetch(challengeUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": token
                },
                body: JSON.stringify({ gate_id: this.gateId })
            })
            .then(res => res.json())
            .then(res => {
                if (res && res.status === 'success' && res.challenge) {
                    self.cachedChallenge = res.challenge;
                }
            })
            .catch(() => {
                self.cachedChallenge = null;
            });
        },

        executeBiometricAssertion: function(challengeStr) {
            const self = this;
            const statusEl = document.getElementById('bioStatusText');
            const triggerBtn = document.getElementById('btnTriggerBio');
            this.hideError();

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
                    if (statusEl) statusEl.textContent = "Biometric verified! Signing in...";
                    const trust = document.getElementById('trustDevice').checked ? 1 : 0;

                    const verifyUrl = "{{ Route::has('user.security_gate.verify') ? route('user.security_gate.verify') : url('/user/security-gate/verify') }}";

                    return fetch(verifyUrl, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": self.csrfToken
                        },
                        body: JSON.stringify({
                            type: 'biometric',
                            value: assertion.id || 'passkey_authenticated',
                            gate_id: self.gateId,
                            trust_device: trust
                        })
                    });
                })
                .then(res => res.json())
                .then(result => {
                    if (result.status === 'success') {
                        if (statusEl) statusEl.textContent = "Authenticated successfully! Redirecting...";
                        self.completeSuccess(result);
                    } else {
                        if (triggerBtn) {
                            triggerBtn.disabled = false;
                            triggerBtn.textContent = "Authenticate with Touch ID / Face ID";
                        }
                        if (statusEl) statusEl.textContent = result.message || "Biometric validation failed.";
                        self.showError(result.message);
                    }
                })
                .catch(err => {
                    if (triggerBtn) {
                        triggerBtn.disabled = false;
                        triggerBtn.textContent = "Authenticate with Touch ID / Face ID";
                    }
                    console.warn('Biometric scan cancelled or not recognized:', err);
                    if (statusEl) statusEl.textContent = "Biometric prompt was cancelled or not recognized. Click above to try again, or choose Security PIN / Passcode via Email.";
                });
            } catch (e) {
                if (triggerBtn) {
                    triggerBtn.disabled = false;
                    triggerBtn.textContent = "Authenticate with Touch ID / Face ID";
                }
                if (statusEl) statusEl.textContent = "Biometric verification error. Please choose PIN or Email instead.";
                self.showError("Biometric verification error. Please choose PIN or Email instead.");
            }
        },

        triggerBiometricAuth: function() {
            const self = this;
            const statusEl = document.getElementById('bioStatusText');
            const triggerBtn = document.getElementById('btnTriggerBio');
            this.hideError();

            if (!window.PublicKeyCredential) {
                if (statusEl) statusEl.textContent = "Biometric authentication is not supported on this browser or device. Please use Security PIN or Passcode via Email.";
                return;
            }

            if (statusEl) statusEl.textContent = "Connecting to biometric sensor...";
            if (triggerBtn) {
                triggerBtn.disabled = true;
                triggerBtn.textContent = "Waiting for Face ID / Touch ID...";
            }

            // If challenge was already pre-fetched, execute immediately inside user gesture activation
            if (this.cachedChallenge) {
                const ch = this.cachedChallenge;
                this.cachedChallenge = null;
                this.executeBiometricAssertion(ch);
                return;
            }

            const challengeUrl = "{{ Route::has('user.security_gate.biometric.challenge.direct') ? route('user.security_gate.biometric.challenge.direct') : url('/user/security-gate/biometric/challenge') }}";

            fetch(challengeUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": this.csrfToken
                },
                body: JSON.stringify({ gate_id: this.gateId })
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.challenge) {
                    self.executeBiometricAssertion(res.challenge);
                } else {
                    if (triggerBtn) {
                        triggerBtn.disabled = false;
                        triggerBtn.textContent = "Authenticate with Touch ID / Face ID";
                    }
                    if (statusEl) statusEl.textContent = "Could not start biometric session. Please use PIN or Email.";
                    self.showError('Could not start biometric session. Please use PIN or Email.');
                }
            })
            .catch(() => {
                if (triggerBtn) {
                    triggerBtn.disabled = false;
                    triggerBtn.textContent = "Authenticate with Touch ID / Face ID";
                }
                if (statusEl) statusEl.textContent = "Could not start biometric session. Please use PIN or Email.";
                self.showError('Could not start biometric session. Please use PIN or Email.');
            });
        },

        completeSuccess: function(res) {
            this.isVerified = true;
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
                $(target).find('button[type="submit"], #confirmBtn').prop('disabled', true);
                HTMLFormElement.prototype.submit.call(target);
                return;
            }

            const fallbackForm = document.querySelector('#csbTransferReviewModal.show form') ||
                                 document.querySelector('#csbTransferForm') ||
                                 document.querySelector('#transferForm') ||
                                 document.querySelector('#wireForm') ||
                                 document.querySelector('#zelleForm') ||
                                 document.querySelector('#depositForm') ||
                                 document.querySelector('#csbDepositForm') ||
                                 document.querySelector('#csbRemoteDepositForm') ||
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
                $(fallbackForm).find('button[type="submit"], #confirmBtn').prop('disabled', true);
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
            this.selectMethod('pin');
        }
    });

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const passInput = document.getElementById('passcodeInput');
        if (passInput) {
            passInput.addEventListener('input', function() {
                window.SecurityGate.updateSubmitButtonState();
            });
            passInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    window.SecurityGate.submitVerification();
                }
            });
        }

        const preference = window.UserSecurityPreference;
        if (preference === 'biometrics' || preference === 'biometric') {
            window.SecurityGate.selectMethod('biometric');
        } else if (preference === 'email') {
            window.SecurityGate.selectMethod('email');
        } else {
            window.SecurityGate.selectMethod('pin');
        }
    });

    window.triggerSecurityGate = function(target, callback) {
        window.SecurityGate.gate(target, callback);
    };
    </script>

@else
    <!-- FFCU Security Gate Modal (Original) -->
    <!-- Security Gate Modal -->
    <div class="modal fade" id="securityGateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold text-dark pt-3 px-3">{{ __('Multi-Factor Authentication Verification') }}</h5>
                    <button type="button" class="btn-close me-2 mt-2" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                    <div class="method-icon" style="background: rgba(2, 132, 199, 0.1); color: #0284c7;">
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
                                    <div class="method-icon" style="background: rgba(2, 132, 199, 0.1); color: #0284c7;">
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
                                    <div class="method-icon" style="background: rgba(2, 132, 199, 0.1); color: #0284c7;">
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
                        </div>

                        <!-- Email Verification Section -->
                        <div id="sg-email-verify" class="d-none text-center">
                            <h4 class="fw-bold text-dark mb-2">{{ __('Verification Required') }}</h4>
                            <p class="text-secondary small mb-1">{{ __('We\'ve sent a verification code to') }}</p>
                            <p class="fw-bold text-dark small mb-4">{{ $sgMaskedEmail }}</p>

                            <div class="mb-4">
                                <input type="text" id="sg-email-code-input" class="input-box otp-input" placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>

                            <button type="button" class="primary-btn w-100 mb-3" id="sg-verify-btn" onclick="SecurityGate.submitVerification()">{{ __('Verify Code') }}</button>

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
    #securityGateModal {
        z-index: 10600 !important;
    }
    #securityGateModal + .modal-backdrop,
    .modal-backdrop.security-gate-backdrop {
        z-index: 10590 !important;
    }
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
        background: rgba(2, 132, 199, 0.08);
        color: var(--primary-color, #0284c7);
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

            const challengeUrl = "{{ Route::has('user.security_gate.biometric.challenge.direct') ? route('user.security_gate.biometric.challenge.direct') : url('/user/security-gate/biometric/challenge') }}";

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
            const challengeUrl = "{{ Route::has('user.security_gate.biometric.challenge.direct') ? route('user.security_gate.biometric.challenge.direct') : url('/user/security-gate/biometric/challenge') }}";

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
                $(target).find('button[type="submit"], #confirmBtn').prop('disabled', true);
                HTMLFormElement.prototype.submit.call(target);
                return;
            }

            const fallbackForm = document.querySelector('#csbTransferReviewModal.show form') ||
                                 document.querySelector('#csbTransferForm') ||
                                 document.querySelector('#transferForm') ||
                                 document.querySelector('#wireForm') ||
                                 document.querySelector('#zelleForm') ||
                                 document.querySelector('#depositForm') ||
                                 document.querySelector('#csbDepositForm') ||
                                 document.querySelector('#csbRemoteDepositForm') ||
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
                $(fallbackForm).find('button[type="submit"], #confirmBtn').prop('disabled', true);
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
@endif