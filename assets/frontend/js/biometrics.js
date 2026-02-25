/**
 * Pinellas Biometrics Module
 * Interface for native FaceID/TouchID/Fingerprint authentication
 */
const PinellasBiometrics = {
    plugin: null,
    isAvailable: false,

    async init() {
        if (typeof window.Capacitor === 'undefined') return;

        try {
            const { NativeBiometric } = window.Capacitor.Plugins;
            this.plugin = NativeBiometric;

            const result = await this.plugin.isAvailable();
            this.isAvailable = result.isAvailable;
            console.log("Biometrics available:", this.isAvailable);
        } catch (e) {
            console.warn("Biometrics plugin not loaded:", e);
        }
    },

    async enroll(username, password) {
        if (!this.isAvailable) return;

        try {
            await this.plugin.setCredentials({
                username: username,
                password: password,
                server: "pinellascu.com",
            });
            localStorage.setItem('biometrics_enrolled', 'true');
            return true;
        } catch (e) {
            console.error("Enrollment failed:", e);
            return false;
        }
    },

    async authenticate() {
        if (!this.isAvailable) return;

        try {
            const credentials = await this.plugin.getCredentials({
                server: "pinellascu.com",
            });
            return credentials;
        } catch (e) {
            console.warn("Authentication cancelled or failed:", e);
            return null;
        }
    },

    async clear() {
        if (!this.plugin) return;
        try {
            await this.plugin.deleteCredentials({
                server: "pinellascu.com",
            });
            localStorage.removeItem('biometrics_enrolled');
        } catch (e) {
            console.error("Failed to clear credentials:", e);
        }
    },

    async challenge() {
        if (!this.isAvailable || localStorage.getItem('biometrics_enrolled') !== 'true') return true;

        const isLoginPage = window.location.pathname.includes('login');

        try {
            const credentials = await this.authenticate();
            if (credentials && credentials.username && credentials.password) {
                if (isLoginPage) {
                    // Sign-In Flow: Fill and submit login form
                    const uField = document.getElementById('username_field');
                    const pField = document.getElementById('password_field');
                    const finalEmail = document.getElementById('final-email');
                    const form = document.querySelector('form[action$="/login"]');

                    if (uField && pField && form) {
                        uField.value = credentials.username;
                        if (finalEmail) finalEmail.value = credentials.username;
                        pField.value = credentials.password;

                        const rememberCheck = document.querySelector('input[name="remember"]');
                        if (rememberCheck) rememberCheck.checked = true;

                        if (typeof window.showLoader === 'function') window.showLoader('Signing in...');
                        form.submit();
                    }
                }
                // Unlock Flow: Return true so the calling layer hides the overlay
                return true;
            }

            // If failed/cancelled on a PROTECTED page, force logout to be safe
            if (!isLoginPage) {
                window.location.href = "/logout-force";
            }
            return false;
        } catch (e) {
            if (!isLoginPage) window.location.href = "/logout-force";
            return false;
        }
    }
};

window.PinellasBiometrics = PinellasBiometrics;
window.addEventListener('DOMContentLoaded', () => {
    PinellasBiometrics.init().then(() => {
        const enrolled = localStorage.getItem('biometrics_enrolled') === 'true';
        const isLoginPage = window.location.pathname.includes('login');

        if (enrolled) {
            // Auto-trigger on Login page or protected pages
            if (isLoginPage || !window.location.pathname.includes('register')) {
                // Short delay to let everything settle
                setTimeout(() => PinellasBiometrics.challenge(), 800);
            }
        }
    });
});

// Re-challenge on Resume for protected pages
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible' &&
        !window.location.pathname.includes('login') &&
        !window.location.pathname.includes('register') &&
        localStorage.getItem('biometrics_enrolled') === 'true') {
        PinellasBiometrics.challenge();
    }
});
