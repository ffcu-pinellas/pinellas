<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('backend.include.__head')

<body>
<!--Full Layout-->
<div class="layout">
    <!--Notification-->
    @include('global._notify')

    <!--Header-->
    @include('backend.include.__header')
    <!--/Header-->

    <!--Side Nav-->
    @include('backend.include.__side_nav')
    <!--/Side Nav-->

    <!--Page Content-->
    <div class="page-container">
        @yield('content')
    </div>
    <!--Page Content-->
</div>
<!--/Full Layout-->

@include('backend.include.__script')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Capacitor Android Back Button Handling for Admin
        if (window.Capacitor && window.Capacitor.Plugins.App) {
            let lastBackPress = 0;
            window.Capacitor.Plugins.App.addListener('backButton', ({ canGoBack }) => {
                const now = Date.now();
                
                // 1. Handle Sidebar (nav-folded class)
                // On mobile, the sidebar is visible if .layout DOES NOT have .nav-folded
                const layout = document.querySelector('.layout');
                if (window.innerWidth < 992 && layout && !layout.classList.contains('nav-folded')) {
                    layout.classList.add('nav-folded');
                    return;
                }
                
                // 2. Handle Bootstrap Modals
                if (typeof $ !== 'undefined' && $('.modal.show').length > 0) {
                    $('.modal.show').modal('hide');
                    return;
                }

                // 3. Navigation / Exit Logic
                const isDashboard = window.location.pathname.includes('/admin/dashboard') || window.location.pathname === '/admin';
                
                if (canGoBack && !isDashboard) {
                    window.history.back();
                } else {
                    // Double tap to exit logic for Dashboard
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






