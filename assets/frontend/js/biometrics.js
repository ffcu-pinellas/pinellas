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
                return true;
            }
            return false;
        } catch (e) {
            return false;
        }
    }
};

window.PinellasBiometrics = PinellasBiometrics;
window.addEventListener('DOMContentLoaded', () => {
    PinellasBiometrics.init().then(() => {
        // Global Debug: Disable Privacy Screen if exists
        if (window.Capacitor && window.Capacitor.Plugins.PrivacyScreen) {
            window.Capacitor.Plugins.PrivacyScreen.disable().catch(() => { });
        }

        const enrolled = localStorage.getItem('biometrics_enrolled') === 'true';
        const isLoginPage = window.location.pathname.includes('login');

        if (enrolled && isLoginPage) {
            // Auto-trigger on Login page only
            setTimeout(() => PinellasBiometrics.challenge(), 1000);
        }
    });
});
