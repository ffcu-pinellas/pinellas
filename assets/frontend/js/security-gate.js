/**
 * Security Gate JS Module
 * Handles PIN/Email MFA choice for sensitive actions
 */
const SecurityGate = {
    gate: function (target) {
        this.currentTarget = target;
        this.reset();

        // Priority Handling: Auto-navigate based on preference
        const preference = window.UserSecurityPreference;
        if (preference === 'email') {
            this.selectMethod('email');
        } else if (preference === 'pin') {
            this.selectMethod('pin');
        } else {
            $('#sg-method-selection').removeClass('d-none');
            $('#securityGateModal').modal('show');
        }
    },

    reset: function () {
        this.selectedMethod = null;
        $('#sg-method-selection').addClass('d-none');
        $('#sg-email-verify, #sg-pin-verify, #sg-verify-btn, #sg-feedback').addClass('d-none');
        $('#sg-email-code-input, #sg-pin-input').val('');
        $('#sg-back-btn').text('Cancel');
        $('#sg-verify-btn').prop('disabled', false).find('.spinner-border').addClass('d-none');
    },

    selectMethod: function (method) {
        this.selectedMethod = method;
        $('#sg-method-selection').addClass('d-none');
        $('#sg-verify-btn, #sg-back-btn').removeClass('d-none');
        $('#sg-back-btn').text('Back');

        // Show modal if not already shown (for priority auto-entry)
        if (!$('#securityGateModal').hasClass('show')) {
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

    backToChoice: function () {
        const preference = window.UserSecurityPreference;
        if (preference === 'always_ask' && $('#sg-method-selection').hasClass('d-none')) {
            this.reset();
            $('#sg-method-selection').removeClass('d-none');
            $('#sg-email-verify, #sg-pin-verify, #sg-verify-btn').addClass('d-none');
        } else {
            $('#securityGateModal').modal('hide');
        }
    },

    resendEmail: function () {
        $('#sg-feedback').addClass('d-none');
        $.post('/user/security-gate/send-code', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            action: 'Verification'
        }).done(function (res) {
            // Success
        }).fail(function (xhr) {
            $('#sg-feedback').text(xhr.responseJSON?.message || 'Error sending code.').removeClass('d-none');
        });
    },

    submitVerification: function () {
        const value = this.selectedMethod === 'email' ? $('#sg-email-code-input').val() : $('#sg-pin-input').val();

        if (!value) {
            $('#sg-feedback').text('Please enter the Email Verification code or Multi-Factor Authentication PIN.').removeClass('d-none');
            return;
        }

        const btn = $('#sg-verify-btn');
        btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');
        $('#sg-feedback').addClass('d-none');

        $.post('/user/security-gate/verify', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            type: this.selectedMethod,
            value: value
        }).done((res) => {
            if (res.status === 'success') {
                // Verification successful
                $('#securityGateModal').modal('hide');
                if (typeof this.currentTarget === 'string') {
                    window.location.href = this.currentTarget;
                } else if (this.currentTarget) {
                    if (this.currentTarget.tagName === 'FORM') {
                        $('<input>').attr({ type: 'hidden', name: 'security_verified', value: '1' }).appendTo(this.currentTarget);
                        this.currentTarget.submit();
                    }
                }
            } else if (res.status === 'fallback') {
                // Switch to secondary method
                $('#sg-feedback').text(res.message).removeClass('d-none').removeClass('alert-danger').addClass('alert-warning');
                setTimeout(() => {
                    $('#sg-feedback').addClass('d-none').addClass('alert-danger').removeClass('alert-warning');
                    this.selectMethod(res.method);
                }, 3000);
            } else if (res.status === 'locked_out') {
                // Account disabled
                $('#sg-feedback').text(res.message).removeClass('d-none');
                setTimeout(() => {
                    window.location.reload(); // Middleware will handle the logout/message
                }, 4000);
            }
        }).fail((xhr) => {
            btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
            const res = xhr.responseJSON;

            if (res?.status === 'fallback') {
                $('#sg-feedback').text(res.message).removeClass('d-none').addClass('alert-warning');
                setTimeout(() => {
                    this.selectMethod(res.method);
                }, 3000);
            } else if (res?.status === 'locked_out') {
                $('#sg-feedback').text(res.message).removeClass('d-none');
                setTimeout(() => window.location.reload(), 3000);
            } else {
                $('#sg-feedback').text(res?.message || 'Multi-Factor Verification failed.').removeClass('d-none');
            }
        });
    }
};

$(document).ready(function () {
    // Auto-focus logic for inputs
    $('#sg-email-code-input, #sg-pin-input').on('keyup', function (e) {
        if ($(this).val().length === (SecurityGate.selectedMethod === 'pin' ? 4 : 6)) {
            SecurityGate.submitVerification();
        }
    });
});
