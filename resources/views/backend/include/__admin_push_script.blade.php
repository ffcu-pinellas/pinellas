@if(auth('admin')->check())
<script>
    // Admin Mobile Push Token Bridge
    document.addEventListener('deviceready', function() {
        if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.PushNotifications) {
            const PushNotifications = window.Capacitor.Plugins.PushNotifications;

            PushNotifications.requestPermissions().then(result => {
                if (result.receive === 'granted') {
                    PushNotifications.register();
                }
            });

            PushNotifications.addListener('registration', (token) => {
                const fcmToken = token.value;
                console.log('Admin Push Registration success:', fcmToken);
                
                // Show a subtle console alert to confirm registration for devs/admins
                console.info('%c Pinellas Admin: Device registered for push notifications. ', 'background: #00aeef; color: #fff');

                // Send token to backend
                fetch('{{ route('admin.update-push-token') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ token: fcmToken })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Admin FCM token saved successfully');
                    }
                })
                .catch(err => console.error('Error saving Admin FCM token:', err));
            });

            PushNotifications.addListener('registrationError', (error) => {
                console.error('Push registration error: ', JSON.stringify(error));
            });
            
            // Handle notification arrival while app is open
            PushNotifications.addListener('pushNotificationReceived', (notification) => {
                console.log('Push received: ', notification);
            });
        }
    }, false);
</script>
@endif
