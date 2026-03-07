<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<!--Head-->
@include('backend.include.__head')
<!--/Head-->
<body>


<!--Auth Page-->
<div class="admin-auth">
    <!--Notification-->
    @include('global._notify')
    
    @yield('auth-content')
</div>
<!--/Auth Page-->

<!--Script-->
@include('backend.include.__script')
<!--/Script-->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Capacitor Android Back Button Handling for Admin Auth
        if (window.Capacitor && window.Capacitor.Plugins.App) {
            let lastBackPress = 0;
            window.Capacitor.Plugins.App.addListener('backButton', ({ canGoBack }) => {
                const now = Date.now();
                
                // 1. Handle Bootstrap Modals
                if (typeof $ !== 'undefined' && $('.modal.show').length > 0) {
                    $('.modal.show').modal('hide');
                    return;
                }

                // 2. Navigation / Exit Logic
                if (canGoBack) {
                    window.history.back();
                } else {
                    // Double tap to exit logic when history is empty (at the start of Admin Auth/Login)
                    if (now - lastBackPress < 2000) {
                        window.Capacitor.Plugins.App.exitApp();
                    } else {
                        lastBackPress = now;
                        if (window.Capacitor.Plugins.Toast) {
                            window.Capacitor.Plugins.Toast.show({
                                text: 'Press back again to exit',
                                duration: 'short'
                            });
                        } else {
                            const toastElem = document.createElement('div');
                            toastElem.textContent = 'Press back again to exit';
                            toastElem.style.cssText = 'position:fixed; bottom:100px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.8); color:white; padding:8px 16px; border-radius:20px; z-index:10000; font-size:14px;';
                            document.body.appendChild(toastElem);
                            setTimeout(() => toastElem.remove(), 2000);
                        }
                    }
                }
            });
        }
    });
</script>


</body>
</html>
