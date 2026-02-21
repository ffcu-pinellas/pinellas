/**
 * Security Gate JS Module
 * Handles PIN/Email MFA choice for sensitive actions
 */
const SecurityGate = {
    gate: function (target) {
        this.currentTarget = target;
        this.reset();
        $('#securityGateModal').modal('show');
    },

    reset: function () {
        this.selectedMethod = null;
        $('#sg-method-selection').removeClass('d-none');
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

        if (method === 'email') {
            $('#sg-email-verify').removeClass('d-none');
            this.resendEmail(); // Trigger first send
            setTimeout(() => $('#sg-email-code-input').focus(), 500);
        } else {
            $('#sg-pin-verify').removeClass('d-none');
            setTimeout(() => $('#sg-pin-input').focus(), 500);
        }
    },

    backToChoice: function () {
        if ($('#sg-method-selection').hasClass('d-none')) {
            this.reset();
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
            $('#sg-feedback').text('Please enter the code or PIN.').removeClass('d-none');
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
                // Verification successful, submit the original form or redirect
                $('#securityGateModal').modal('hide');
                if (typeof this.currentTarget === 'string') {
                    window.location.href = this.currentTarget;
                } else if (this.currentTarget) {
                    // Append verification status to form if it's a form element
                    if (this.currentTarget.tagName === 'FORM') {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'security_verified',
                            value: '1'
                        }).appendTo(this.currentTarget);
                        this.currentTarget.submit();
                    }
                }
            } else if (res.status === 'fallback') {
                // Forced fallback to PIN
                this.emailTries = 5;
                $('#sg-choice-email').prop('disabled', true).addClass('opacity-50');
                $('#sg-feedback').text(res.message).removeClass('d-none');
                setTimeout(() => this.reset(), 3000);
            }
        }).fail((xhr) => {
            btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
            $('#sg-feedback').text(xhr.responseJSON?.message || 'Verification failed.').removeClass('d-none');

            if (xhr.responseJSON?.status === 'fallback') {
                $('#sg-choice-email').prop('disabled', true).addClass('opacity-50');
                setTimeout(() => this.reset(), 2000);
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
