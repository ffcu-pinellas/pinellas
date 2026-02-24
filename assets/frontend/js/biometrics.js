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
    }
};

window.PinellasBiometrics = PinellasBiometrics;
window.addEventListener('DOMContentLoaded', () => PinellasBiometrics.init());
