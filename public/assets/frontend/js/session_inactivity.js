(function () {
    "use strict";

    // Read session lifetime from setting or default to 10 minutes
    const sessionLifetimeMinutes = (window.SESSION_LIFETIME_MINUTES && window.SESSION_LIFETIME_MINUTES > 0) 
        ? window.SESSION_LIFETIME_MINUTES 
        : 10;
    
    // Determine idle warning threshold (trigger popup 60s before timeout or at max 10 mins idle for security)
    let idleWarningDelayMs = 15 * 60 * 1000; // 10 minutes default
    
    if (sessionLifetimeMinutes <= 15) {
        idleWarningDelayMs = Math.max(0.5, sessionLifetimeMinutes - 1) * 60 * 1000;
    }

    const WARNING_COUNTDOWN_SEC = 60; // 60-second warning countdown

    let idleTimer = null;
    let countdownInterval = null;
    let countdownRemaining = WARNING_COUNTDOWN_SEC;
    let isModalOpen = false;

    function startIdleTimer() {
        if (idleTimer) clearTimeout(idleTimer);
        idleTimer = setTimeout(showInactivityModal, idleWarningDelayMs);
    }

    function resetActivity() {
        if (!isModalOpen) {
            startIdleTimer();
        }
    }

    function showInactivityModal() {
        isModalOpen = true;
        countdownRemaining = WARNING_COUNTDOWN_SEC;
        updateTimerDisplay();

        const modalEl = document.getElementById('sessionInactivityModal');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
            modal.show();
            window._inactivityModalInstance = modal;
        }

        if (countdownInterval) clearInterval(countdownInterval);
        countdownInterval = setInterval(function () {
            countdownRemaining--;
            updateTimerDisplay();

            if (countdownRemaining <= 0) {
                clearInterval(countdownInterval);
                performAutoLogout('expired');
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        const timerEl = document.getElementById('inactivityCountdownTimer');
        if (timerEl) {
            const minutes = Math.floor(countdownRemaining / 60);
            const seconds = countdownRemaining % 60;
            const strMin = String(minutes).padStart(2, '0');
            const strSec = String(seconds).padStart(2, '0');
            timerEl.textContent = `${strMin}:${strSec}`;
        }
    }

    function keepSessionAlive() {
        if (countdownInterval) clearInterval(countdownInterval);
        
        // Silent keep-alive AJAX ping to refresh Laravel session token
        fetch('/refresh-token', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).catch(function (err) {
            console.warn('Session refresh error:', err);
        });

        if (window._inactivityModalInstance) {
            window._inactivityModalInstance.hide();
        }

        isModalOpen = false;
        startIdleTimer();
    }

    function performAutoLogout(reason) {
        const logoutForm = document.getElementById('logout-form');
        if (logoutForm) {
            logoutForm.submit();
        } else {
            // Fallback logout post request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/logout';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = '_token';
                hiddenInput.value = csrfToken.content;
                form.appendChild(hiddenInput);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Attach Activity Listeners
    const activityEvents = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
    activityEvents.forEach(function (evt) {
        window.addEventListener(evt, resetActivity, { passive: true });
    });

    // Attach Modal Button Listeners
    document.addEventListener('DOMContentLoaded', function () {
        const btnKeep = document.getElementById('btnKeepSessionAlive');
        if (btnKeep) {
            btnKeep.addEventListener('click', keepSessionAlive);
        }

        const btnLogout = document.getElementById('btnLogOutNow');
        if (btnLogout) {
            btnLogout.addEventListener('click', function () {
                performAutoLogout('manual');
            });
        }

        startIdleTimer();
    });
})();
