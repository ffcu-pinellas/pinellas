<!-- Bank Inactivity Session Warning Modal -->
<div class="modal fade" id="sessionInactivityModal" tabindex="-1" aria-labelledby="sessionInactivityModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(20px);">
            <div class="modal-body p-4 text-center">
                
                <!-- Pulsing Bank Shield Icon -->
                <div class="inactivity-icon-wrap mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 72px; height: 72px; border-radius: 50%; background: rgba(59, 130, 246, 0.1); color: #3b82f6; position: relative;">
                    <i data-lucide="shield-alert" style="width: 36px; height: 36px;"></i>
                    <div class="pulse-ring" style="position: absolute; width: 100%; height: 100%; border-radius: 50%; border: 2px solid #3b82f6; animation: pulseRing 1.8s infinite ease-in-out;"></div>
                </div>

                <h4 class="fw-bold text-dark mb-2" id="sessionInactivityModalLabel">{{ __('Are you still there?') }}</h4>
                <p class="text-muted small mb-4 px-2" style="line-height: 1.5;">
                    {{ __('For your security, your online banking session is about to expire due to inactivity. You will be automatically logged out in:') }}
                </p>

                <!-- Digital Countdown Timer -->
                <div class="d-inline-flex align-items-center justify-content-center px-4 py-2 mb-4 rounded-pill" style="background: #f1f5f9; border: 1px solid #e2e8f0;">
                    <i data-lucide="clock" class="me-2 text-warning" style="width: 20px; height: 20px;"></i>
                    <span id="inactivityCountdownTimer" class="h3 fw-bold m-0 font-monospace text-dark">01:00</span>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                    <button type="button" id="btnKeepSessionAlive" class="btn btn-primary rounded-pill py-2.5 fw-semibold d-flex align-items-center justify-content-center shadow-sm">
                        <i data-lucide="check-circle" class="me-2" style="width: 18px; height: 18px;"></i>
                        {{ __('Yes, Keep Me Logged In') }}
                    </button>
                    <button type="button" id="btnLogOutNow" class="btn btn-outline-danger rounded-pill py-2.5 fw-semibold d-flex align-items-center justify-content-center mt-1">
                        <i data-lucide="log-out" class="me-2" style="width: 18px; height: 18px;"></i>
                        {{ __('Log Out Now') }}
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
#sessionInactivityModal .modal-backdrop, 
.modal-backdrop.show {
    backdrop-filter: blur(12px) !important;
    background-color: rgba(15, 23, 42, 0.75) !important;
}
@keyframes pulseRing {
    0% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.15); opacity: 0.3; }
    100% { transform: scale(0.95); opacity: 0.8; }
}
</style>

<script>
    window.SESSION_LIFETIME_MINUTES = {{ (int) setting('session_lifetime', 'system', 120) }};
</script>
<script src="{{ asset('assets/frontend/js/session_inactivity.js') }}"></script>
