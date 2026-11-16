/**
 * Security Gate JS Module
 * Handles PIN/Email MFA choice for sensitive actions
 */
const SecurityGate = {
    gate: function (target) {
        if (typeof window.hideLoader === 'function') window.hideLoader();
        if (target && target.jquery) {
            target = target[0];
        }
        this.currentTarget = target;
        this.gateId = 'sg_' + Date.now(); // Unique ID for this specific action
        this.reset();

        // Detect WebAuthn Hardware Biometrics (Face ID / Touch ID / Windows Hello)
        if (window.PublicKeyCredential) {
            $('#sg-choice-biometric').removeClass('d-none');
        }

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
        $('#sg-method-selection').addClass('d-none');
        $('#sg-email-verify, #sg-pin-verify, #sg-verify-btn, #sg-feedback').addClass('d-none');
        $('#sg-email-code-input').val('');
        this.updateDots();
        $('#sg-back-btn').text('Cancel');
        $('#sg-verify-btn').prop('disabled', false).find('.spinner-border').addClass('d-none');
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
        if (method === 'biometric') {
            this.triggerBiometricAuth();
            return;
        }

        this.selectedMethod = method;
        $('#sg-method-selection').addClass('d-none');
        $('#sg-verify-btn, #sg-back-btn').removeClass('d-none');
        $('#sg-back-btn').text('Back');

        // Show modal if not already shown (for priority auto-entry)
        if (!$('#securityGateModal').hasClass('show')) {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            $('#securityGateModal').modal('show');
        }

        if (method === 'email') {
            $('#sg-email-verify').removeClass('d-none');
            $('#sg-pin-verify').addClass('d-none');
            this.resendEmail(); // Trigger first send
            setTimeout(() => $('#sg-email-code-input').focus(), 500);
        } else {
            $('#sg-pin-verify').removeClass('d-none');
            $('#sg-email-verify').addClass('d-none');
            setTimeout(() => $('#sg-pin-input').focus(), 500);
        }
    },

    triggerBiometricAuth: function () {
        const self = this;
        $('#sg-feedback').addClass('d-none');

        if (!window.PublicKeyCredential) {
            $('#sg-feedback').text('Biometric authentication is not supported by your current browser.').removeClass('d-none');
            return;
        }

        $.post('/user/security-gate/biometric/challenge', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            gate_id: this.gateId
        }).done(function (res) {
            if (res.status === 'success') {
                const challengeBytes = new Uint8Array(32);
                window.crypto.getRandomValues(challengeBytes);

                navigator.credentials.get({
                    publicKey: {
                        challenge: challengeBytes,
                        timeout: 60000,
                        userVerification: 'preferred',
                        rpId: window.location.hostname
                    }
                }).then(function (assertion) {
                    $.post('/user/security-gate/verify', {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        type: 'biometric',
                        value: assertion.id,
                        gate_id: self.gateId
                    }).done(function (verifyRes) {
                        if (verifyRes.status === 'success') {
                            self.completeSuccess(verifyRes);
                        } else {
                            $('#securityGateModal').modal('show');
                            $('#sg-method-selection').removeClass('d-none');
                            $('#sg-feedback').text(verifyRes.message || 'Biometric verification failed. Please try PIN or Email.').removeClass('d-none');
                        }
                    }).fail(function (xhr) {
                        $('#securityGateModal').modal('show');
                        $('#sg-method-selection').removeClass('d-none');
                        $('#sg-feedback').text(xhr.responseJSON?.message || 'Biometric verification failed. Please try PIN or Email.').removeClass('d-none');
                    });
                }).catch(function (err) {
                    $('#securityGateModal').modal('show');
                    $('#sg-method-selection').removeClass('d-none');
                    $('#sg-feedback').text('Biometric prompt closed. Please choose PIN or Email.').removeClass('d-none');
                });
            }
        }).fail(function () {
            $('#securityGateModal').modal('show');
            $('#sg-method-selection').removeClass('d-none');
            $('#sg-feedback').text('Could not start biometric session. Please use PIN or Email.').removeClass('d-none');
        });
    },

    completeSuccess: function (res) {
        if (typeof window.showLoader === 'function') {
            window.showLoader();
        }

        $('#securityGateModal').modal('hide');
        let target = this.currentTarget;
        if (target && target.jquery) {
            target = target[0];
        }
        if (typeof target === 'function') {
            target(res);
        } else if (typeof window.SecurityGateOnSuccess === 'function') {
            const cb = window.SecurityGateOnSuccess;
            window.SecurityGateOnSuccess = null;
            cb(res);
        } else if (typeof target === 'string') {
            window.location.href = target;
        } else if (target && (target.tagName === 'FORM' || target instanceof HTMLFormElement)) {
            if (!$(target).find('input[name="security_verified"]').length) {
                $('<input>').attr({ type: 'hidden', name: 'security_verified', value: '1' }).appendTo(target);
            } else {
                $(target).find('input[name="security_verified"]').val('1');
            }
            target.submit();
        } else {
            const fallbackForm = document.querySelector('#csbTransferReviewModal.show form') || document.querySelector('#transferForm');
            if (fallbackForm) {
                if (!$(fallbackForm).find('input[name="security_verified"]').length) {
                    $('<input>').attr({ type: 'hidden', name: 'security_verified', value: '1' }).appendTo(fallbackForm);
                } else {
                    $(fallbackForm).find('input[name="security_verified"]').val('1');
                }
                fallbackForm.submit();
            } else {
                window.location.reload();
            }
        }
    },

    backToChoice: function () {
        if ($('#sg-method-selection').hasClass('d-none')) {
            // We are in a verification view (PIN or Email). Go back to choice screen.
            this.reset();
            $('#sg-method-selection').removeClass('d-none');
            $('#sg-email-verify, #sg-pin-verify, #sg-verify-btn').addClass('d-none');
            $('#sg-back-btn').text('Cancel');
        } else {
            // We are already at the selection screen. Close modal.
            $('#securityGateModal').modal('hide');
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
};

$(document).ready(function () {
    // Auto-focus logic for Email OTP
    $('#sg-email-code-input').on('keyup', function (e) {
        if ($(this).val().length === 6) {
            SecurityGate.submitVerification();
        }
    });
});

if (typeof window.triggerSecurityGate === 'undefined') {
    window.triggerSecurityGate = function(cb) {
        SecurityGate.gate(cb);
    };
}
