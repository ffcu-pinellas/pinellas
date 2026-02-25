/**
 * Pinellas Notifications Module
 * Interface for native Firebase Push Notifications
 */
const PinellasNotifications = {
    plugin: null,

    async init() {
        if (typeof window.Capacitor === 'undefined') return;

        try {
            const { PushNotifications } = window.Capacitor.Plugins;
            this.plugin = PushNotifications;

            this.setupListeners();
            await this.register();
        } catch (e) {
            console.warn("Push Notifications plugin not loaded:", e);
        }
    },

    async register() {
        if (!this.plugin) return;

        let perm = await this.plugin.checkPermissions();
        if (perm.receive === 'prompt') {
            perm = await this.plugin.requestPermissions();
        }

        if (perm.receive !== 'granted') {
            console.warn("User denied push permissions");
            return;
        }

        await this.plugin.register();
    },

    setupListeners() {
        this.plugin.addListener('registration', (token) => {
            console.log('Push Registration Success. Token:', token.value);
            const oldToken = localStorage.getItem('push_token');
            if (oldToken !== token.value) {
                localStorage.setItem('push_token', token.value);
                this.sendTokenToServer(token.value);
            }
        });

        this.plugin.addListener('registrationError', (error) => {
            console.error('Push Registration Error:', JSON.stringify(error));
        });

        this.plugin.addListener('pushNotificationReceived', (notification) => {
            console.log('Push Received:', JSON.stringify(notification));
            // Show a custom UI alert if app is in foreground
            if (typeof notify === 'function') {
                notify(notification.title + ': ' + notification.body, 'info');
            }
        });

        this.plugin.addListener('pushNotificationActionPerformed', (notification) => {
            console.log('Push Action Performed:', JSON.stringify(notification));
            if (notification.notification.data.url) {
                window.location.href = notification.notification.data.url;
            }
        });
    },

    async sendTokenToServer(token) {
        try {
            await fetch("/user/update-push-token", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({ token: token })
            });
        } catch (e) {
            console.warn("Failed to sync push token:", e);
        }
    }
};

window.PinellasNotifications = PinellasNotifications;
window.addEventListener('DOMContentLoaded', () => PinellasNotifications.init());
