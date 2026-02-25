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
            if (typeof window.bioLog === 'function') window.bioLog("Securing identity...");

            // Step 1: Explicit Verify Identity (triggers the OS dialog)
            await this.plugin.verifyIdentity({
                reason: "Identify yourself to sign in securely.",
                title: "Biometric Login",
                subtitle: "Pinellas FCU Security",
            });

            if (typeof window.bioLog === 'function') window.bioLog("Identity confirmed. Accessing vault...");

            // Step 2: Retrieve Credentials
            const credentials = await this.plugin.getCredentials({
                server: "pinellascu.com",
            });

            if (typeof window.bioLog === 'function') window.bioLog("Access granted.");
            return credentials;
        } catch (e) {
            console.warn("[PinellasBiometrics] Auth Error:", e);
            const errMsg = e.message || "User cancelled";

            if (typeof window.bioLog === 'function') {
                window.bioLog("Auth Status: " + errMsg, "warn");
                if (errMsg.toLowerCase().includes("crypto") || errMsg.toLowerCase().includes("keystore")) {
                    window.bioLog("ACTION REQUIRED: Go to Settings -> Turn Biometrics OFF then ON to refresh security keys.", "error");
                }
            }
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
            // Disabled auto-trigger for now to allow manual icon testing
            if (typeof window.bioLog === 'function') window.bioLog("Biometrics ready. Tap the fingerprint icon to sign in.");
        }
    });
});
