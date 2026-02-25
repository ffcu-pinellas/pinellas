/**
 * Pinellas Biometrics Module
 * Interface for native FaceID/TouchID/Fingerprint authentication
 */
const PinellasBiometrics = {
    plugin: null,
    isAvailable: false,

    async init() {
        if (typeof window.Capacitor === 'undefined') {
            console.warn("[PinellasBiometrics] Capacitor not found. Are you in a browser?");
            return;
        }

        try {
            console.log("[PinellasBiometrics] Checking for NativeBiometric plugin...");
            const { NativeBiometric } = window.Capacitor.Plugins;

            if (!NativeBiometric) {
                console.error("[PinellasBiometrics] NativeBiometric plugin is NOT available in window.Capacitor.Plugins.");
                return;
            }

            this.plugin = NativeBiometric;

            const result = await this.plugin.isAvailable();
            this.isAvailable = result.isAvailable;
            console.log("[PinellasBiometrics] Device Hardware Available:", this.isAvailable, result);
        } catch (e) {
            console.warn("[PinellasBiometrics] Initialization error:", e);
        }
    },

    async enroll(username, password) {
        if (!this.isAvailable) return;

        try {
            await this.plugin.setCredentials({
                username: username,
                password: password,
                server: "pinellascu.com",
                requireAuthentication: true, // This forces a prompt on retrieval
            });
            localStorage.setItem('biometrics_enrolled', 'true');
            if (typeof window.bioLog === 'function') window.bioLog("Enrolled successfully with high security.");
            return true;
        } catch (e) {
            console.error("Enrollment failed:", e);
            if (typeof window.bioLog === 'function') window.bioLog("Enrollment error: " + e.message, "error");
            return false;
        }
    },

    async authenticate() {
        if (!this.isAvailable) return;

        try {
            if (typeof window.bioLog === 'function') window.bioLog("Challenging identity...");

            // On many devices, getCredentials with requireAuthentication:true handles the prompt.
            // But we verifyIdentity first for a consistent UX text.
            await this.plugin.verifyIdentity({
                reason: "Sign in to Pinellas Federal Credit Union",
                title: "Biometric Login",
                subtitle: "Identify yourself to continue",
                description: "Use your biometric credential to sign in securely.",
            });

            if (typeof window.bioLog === 'function') window.bioLog("Identity verified. Fetching credentials...");
            const credentials = await this.plugin.getCredentials({
                server: "pinellascu.com",
            });
            return credentials;
        } catch (e) {
            console.warn("[PinellasBiometrics] Authentication cancelled or failed:", e);
            if (typeof window.bioLog === 'function') window.bioLog("Auth failed/cancelled: " + e.message, "warn");
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
            // Auto-trigger on Login page after a short delay for "Smooth" feel
            setTimeout(() => {
                if (typeof window.bioLog === 'function') window.bioLog("Auto-triggering challenge...");
                PinellasBiometrics.challenge();
            }, 1500);
        }
    });
});
