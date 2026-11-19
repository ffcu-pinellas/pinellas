/**
 * Security Gate JS Module
 * Handles PIN/Email MFA choice for sensitive actions
 */
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

    gate: function (target, callback) {
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

        this.gateId = 'sg_' + Date.now(); // Unique ID for this specific action
        this.reset();
        this.prepareBiometricChallenge(); // Pre-fetch challenge so user gesture is instant

        // Biometric Passkey button is ALWAYS visible in method selection
        $('#sg-choice-biometric').removeClass('d-none');

        // Priority Handling: Auto-navigate based on preference
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

    reset: function () {
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

    pressKey: function (num) {
        if (this.currentPin.length < 4) {
            this.currentPin += num;
            this.updateDots();
            if (this.currentPin.length === 4) {
                this.submitVerification();
            }
        }
    },

    backspace: function () {
        if (this.currentPin.length > 0) {
            this.currentPin = this.currentPin.slice(0, -1);
            this.updateDots();
        }
    },

    updateDots: function () {
        for (let i = 1; i <= 4; i++) {
            const dot = $('#sg-dot-' + i);
            if (i <= this.currentPin.length) {
                dot.addClass('filled');
            } else {
                dot.removeClass('filled');
            }
        }
    },

    selectMethod: function (method) {
        this.selectedMethod = method;
        $('#sg-method-selection').addClass('d-none');
        $('#sg-feedback').addClass('d-none').text('');

        // Show modal if not already shown (for priority auto-entry)
        if (!$('#securityGateModal').hasClass('show')) {
            if (typeof window.hideLoader === 'function') window.hideLoader();
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
            this.resendEmail(); // Trigger first send
            setTimeout(() => $('#sg-email-code-input').focus(), 500);
        } else {
            $('#sg-verify-btn').addClass('d-none');
            $('#sg-pin-verify').removeClass('d-none');
            $('#sg-email-verify').addClass('d-none');
            setTimeout(() => $('#sg-pin-input').focus(), 500);
        }
    },

    prepareBiometricChallenge: function () {
        const self = this;
        const token = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
        if (!token) return;

        $.ajax({
            url: '/security/webauthn-challenge',
            type: 'POST',
            data: { _token: token, gate_id: this.gateId },
            dataType: 'json'
        }).done(function (res) {
            if (res && res.status === 'success' && res.challenge) {
                self.cachedChallenge = res.challenge;
            }
        }).fail(function () {
            $.ajax({
                url: '/user/security-gate/biometric/challenge',
                type: 'POST',
                data: { _token: token, gate_id: self.gateId },
                dataType: 'json'
            }).done(function (res2) {
                if (res2 && res2.status === 'success' && res2.challenge) {
                    self.cachedChallenge = res2.challenge;
                }
            }).fail(function () {
                self.cachedChallenge = null;
            });
        });
    },

    executeBiometricAssertion: function (challengeStr) {
        const self = this;
        const $btn = $('#sg-btn-scan-biometric');
        const origHtml = '<i class="fa-solid fa-fingerprint me-2"></i> Confirm with Face ID / Touch ID';
        const token = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();

        try {
            const challengeBytes = new Uint8Array(challengeStr.match(/.{1,2}/g).map(byte => parseInt(byte, 16)));

            navigator.credentials.get({
                publicKey: {
                    challenge: challengeBytes,
                    timeout: 60000,
                    userVerification: 'preferred',
                    rpId: window.location.hostname
                }
            }).then(function (assertion) {
                $btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Verifying...');
                $.post('/user/security-gate/verify', {
                    _token: token,
                    type: 'biometric',
                    value: assertion ? (assertion.id || 'passkey_authenticated') : 'passkey_authenticated',
                    gate_id: self.gateId
                }).done(function (verifyRes) {
                    $btn.prop('disabled', false).html(origHtml);
                    if (verifyRes.status === 'success') {
                        self.completeSuccess(verifyRes);
                    } else {
                        $('#sg-feedback').text(verifyRes.message || 'Biometric verification failed. Please try PIN or Email.').removeClass('d-none');
                    }
                }).fail(function (xhr) {
                    $btn.prop('disabled', false).html(origHtml);
                    $('#sg-feedback').text(xhr.responseJSON?.message || 'Biometric verification failed. Please try PIN or Email.').removeClass('d-none');
                });
            }).catch(function (err) {
                $btn.prop('disabled', false).html(origHtml);
                console.log("Biometric auth rejected or timed out:", err);
                if (err.name === 'NotAllowedError' || err.name === 'AbortError' || err.name === 'TimeoutError') {
                    $('#sg-feedback').text('Biometric prompt was closed or timed out. Tap scan to retry or select another method.').removeClass('d-none');
                } else {
                    $('#sg-feedback').text(err.message || 'Biometric authentication was cancelled. Choose Passcode or Email.').removeClass('d-none');
                }
            });
        } catch (e) {
            $btn.prop('disabled', false).html(origHtml);
            console.error("Biometric assertion exception:", e);
            $('#sg-feedback').text('Biometric verification error. Please choose PIN or Email instead.').removeClass('d-none');
        }
    },

    triggerBiometricAuth: function () {
        const self = this;
        const $btn = $('#sg-btn-scan-biometric');
        const origHtml = '<i class="fa-solid fa-fingerprint me-2"></i> Confirm with Face ID / Touch ID';
        $('#sg-feedback').addClass('d-none').text('');

        if (!window.PublicKeyCredential) {
            $('#sg-feedback').text('Biometric authentication is not supported by your current browser. Please choose PIN or Email.').removeClass('d-none');
            return;
        }

        // Show loading state: "Preparing your security check..."
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Preparing your security check...');

        const token = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();

        // Fetch fresh challenge via AJAX before calling navigator.credentials.get()
        const requestChallenge = function (url, onSuccess, onFailure) {
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: token,
                    gate_id: self.gateId
                },
                dataType: 'json'
            }).done(function (res) {
                if (res && res.status === 'success' && res.challenge) {
                    onSuccess(res.challenge);
                } else {
                    onFailure();
                }
            }).fail(function () {
                onFailure();
            });
        };

        requestChallenge('/security/webauthn-challenge', function (challenge) {
            $btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Waiting for Face ID / Touch ID...');
            self.executeBiometricAssertion(challenge);
        }, function () {
            requestChallenge('/user/security-gate/biometric/challenge', function (challenge) {
                $btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Waiting for Face ID / Touch ID...');
                self.executeBiometricAssertion(challenge);
            }, function () {
                $btn.prop('disabled', false).html(origHtml);
                $('#sg-feedback').text('Could not start biometric session. Please use PIN or Email.').removeClass('d-none');
            });
        });
    },

    completeSuccess: function (res) {
        this.isVerified = true;
        if (typeof window.hideLoader === 'function') {
            window.hideLoader();
        }
        if (typeof window.showLoader === 'function') {
            window.showLoader();
        }

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
                             document.querySelector('#csbTransferForm') ||
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

    backToChoice: function () {
        if ($('#sg-method-selection').hasClass('d-none')) {
            // We are in a verification view (Biometric, PIN or Email). Go back to choice screen.
            this.reset();
            $('#sg-method-selection').removeClass('d-none');
            // Biometric Passkey button is ALWAYS visible in method selection
            $('#sg-choice-biometric').removeClass('d-none');
            $('#sg-biometric-verify, #sg-email-verify, #sg-pin-verify, #sg-verify-btn').addClass('d-none');
            $('#sg-back-btn').text('Cancel');
        } else {
            // We are already at the selection screen. Close modal.
            $('#securityGateModal').modal('hide');
            if (!this.isVerified && typeof this.onCancelCb === 'function') {
                const cancelCb = this.onCancelCb;
                this.onCancelCb = null;
                cancelCb();
            }
        }
    },

    resendEmail: function () {
        $('#sg-feedback').addClass('d-none');
        $.post('/user/security-gate/send-code', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            gate_id: this.gateId,
            action: 'Verification'
        }).done(function (res) {
            $('#sg-feedback').text(res.message || 'Verification code sent to your email.').removeClass('d-none').css('color', '#28a745');
            setTimeout(() => $('#sg-feedback').addClass('d-none').css('color', '#ff3b30'), 5000);
        }).fail(function (xhr) {
            const res = xhr.responseJSON;
            if (res?.status === 'fallback') {
                $('#sg-feedback').text(res.message).removeClass('d-none').addClass('alert-warning');
                setTimeout(() => {
                    SecurityGate.selectMethod(res.method);
                }, 3000);
            } else if (res?.status === 'locked_out') {
                $('#sg-feedback').text(res.message).removeClass('d-none');
                setTimeout(() => window.location.reload(), 3000);
            } else {
                $('#sg-feedback').text(res?.message || 'Error sending code.').removeClass('d-none').css('color', '#ff3b30');
            }
        });
    },

    submitVerification: function () {
        // Ensure PIN is 4 digits if PIN mode
        if (this.selectedMethod === 'pin' && this.currentPin.length < 4) return;

        const value = (this.selectedMethod === 'pin') ? this.currentPin : $('#sg-email-code-input').val();
        const feedback = $('#sg-feedback');
        const modalBody = $('#sg-modal-body'); 
        const btn = $('#sg-verify-btn');
        const spinner = btn.find('.spinner-border');

        feedback.addClass('d-none');
        btn.prop('disabled', true);
        spinner.removeClass('d-none');

        $.post('/user/security-gate/verify', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            type: this.selectedMethod,
            value: value,
            gate_id: this.gateId
        })
        .done((res) => {
            if (res.status === 'success') {
                this.completeSuccess(res);
            } else {
                this.handleError(res.message || 'Verification failed.', res.status, res.method);
            }
        }).fail((xhr) => {
            btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
            const res = xhr.responseJSON;

            if (xhr.status === 419 || xhr.status === 401) {
                window.location.reload();
                return;
            }
            this.handleError(res?.message || 'Multi-Factor Verification failed.', res?.status, res?.method);
        });
    },

    handleError: function(message, status = 'error', method = null) {
        const feedback = $('#sg-feedback');
        const modalBody = $('#sg-modal-body');
        
        feedback.text(message).removeClass('d-none').css('color', '#ff3b30');
        
        if (status === 'fallback' && method) {
            feedback.css('color', '#856404'); // Warning brown-ish
            setTimeout(() => {
                feedback.addClass('d-none').css('color', '#ff3b30');
                this.selectMethod(method);
            }, 3000);
        } else if (status === 'locked_out') {
            setTimeout(() => window.location.reload(), 3000);
        } else {
            // Shake Animation only for normal errors
            modalBody.removeClass('shake');
            void modalBody[0].offsetWidth; // Trigger reflow
            modalBody.addClass('shake');
        }

        if (this.selectedMethod === 'pin') {
            this.currentPin = "";
            this.updateDots();
        } else {
            $('#sg-email-code-input').val('');
        }
        $('#sg-verify-btn').prop('disabled', false).find('.spinner-border').addClass('d-none');
    }
});

$(document).ready(function () {
    // Auto-focus logic for Email OTP
    $('#sg-email-code-input').on('keyup', function (e) {
        if ($(this).val().length === 6) {
            SecurityGate.submitVerification();
        }
    });

    $('#securityGateModal').on('hidden.bs.modal', function () {
        if (!SecurityGate.isVerified && typeof SecurityGate.onCancelCb === 'function') {
            const cancelCb = SecurityGate.onCancelCb;
            SecurityGate.onCancelCb = null;
            cancelCb();
        }
    });
});

window.triggerSecurityGate = function(target, cb) {
    window.SecurityGate.gate(target, cb);
};
