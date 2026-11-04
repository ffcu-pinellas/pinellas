<script src="{{ asset('assets/global/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/global/js/jquery-migrate.js') }}"></script>
<script src="{{ asset('assets/global/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/global/js/daterangepicker.min.js') }}"></script>
<script src="{{ asset('assets/global/js/lucide.min.js') }}"></script>
<script src="{{ asset('assets/front/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/front/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/front/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('assets/global/js/simple-notify.min.js') }}"></script>
<script src="{{ asset('assets/global/js/custom.js?v=1.1') }}"></script>
<script src="{{ asset('assets/front/js/magnific.min.js') }}"></script>
<script src="{{ asset('assets/front/js/swiper.min.js') }}"></script>
<script src="{{ asset('assets/front/js/cookie.js') }}"></script>
<script src="{{ asset('assets/front/js/main.js?v=1.0') }}"></script>
<script src="{{ asset('assets/front/js/parallax.min.js') }}"></script>
<script src="{{ asset('assets/front/js/parallax-scroll.js') }}"></script>
<script src="{{ asset('assets/front/js/meanmenu.min.js') }}"></script>
<script src="{{ asset('assets/front/js/aos.js') }}"></script>
<script>
    "use strict";

    // Enhanced Auto Dark Mode & System Preference Detection
    window.setThemeMode = function(mode) {
        if (!['auto', 'light', 'dark'].includes(mode)) mode = 'auto';
        localStorage.setItem('site_theme_mode', mode);
        
        var isDark = false;
        if (mode === 'dark') {
            isDark = true;
        } else if (mode === 'light') {
            isDark = false;
        } else {
            // Auto (System)
            isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        }

        if (isDark) {
            $('body').addClass('dark-theme');
            $('html').addClass('dark-theme');
        } else {
            $('body').removeClass('dark-theme');
            $('html').removeClass('dark-theme');
        }

        // Sync active state on theme switcher UI buttons if present
        $('[data-theme-mode]').removeClass('active text-primary fw-bold');
        $('[data-theme-mode="' + mode + '"]').addClass('active text-primary fw-bold');

        // Notify server
        $.get('{{ route("mode-theme") }}', { mode: mode });
    };

    // Initialize on page load
    (function() {
        var currentMode = localStorage.getItem('site_theme_mode') || 'auto';
        var systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (currentMode === 'dark' || (currentMode === 'auto' && systemDark)) {
            $('body').addClass('dark-theme');
            $('html').addClass('dark-theme');
        } else {
            $('body').removeClass('dark-theme');
            $('html').removeClass('dark-theme');
        }
        $('[data-theme-mode="' + currentMode + '"]').addClass('active text-primary fw-bold');
    })();

    // Listen to System OS Theme changes in real-time
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            var mode = localStorage.getItem('site_theme_mode') || 'auto';
            if (mode === 'auto') {
                if (e.matches) {
                    $('body').addClass('dark-theme');
                    $('html').addClass('dark-theme');
                } else {
                    $('body').removeClass('dark-theme');
                    $('html').removeClass('dark-theme');
                }
            }
        });
    }

    // Toggle click handler (cycles Auto -> Dark -> Light -> Auto)
    $(".color-switcher").on('click', function (e) {
        e.preventDefault();
        var currentMode = localStorage.getItem('site_theme_mode') || 'auto';
        var nextMode = currentMode === 'auto' ? 'dark' : (currentMode === 'dark' ? 'light' : 'auto');
        window.setThemeMode(nextMode);
    });

    $(document).on('click', '[data-theme-mode]', function(e) {
        e.preventDefault();
        var mode = $(this).data('theme-mode');
        window.setThemeMode(mode);
    });

    let isDisabled = "{{ setting('site_animation','permission') == 1 ?  'mobile' : 'true' }}"

    $(window).on('load', function () {
        AOS.init({
            duration: 1000,
            mirror: true,
            once: true,
            disable: isDisabled == 'true' ? true : 'mobile',
        });
    });

</script>

@include('global.__t_notify')
@if(auth()->check())
    <script src="{{ asset('assets/global/js/pusher.min.js') }}"></script>
    @include('global.__notification_script',['for'=>'user','userId' => auth()->user()->id])
@endif
@php
    $googleAnalytics = plugin_active('Google Analytics');
    $tawkChat = plugin_active('Tawk Chat');
    $fb = plugin_active('Facebook Messenger');
@endphp

@if($googleAnalytics)
    @include('frontend::plugin.google_analytics',['GoogleAnalyticsId' => json_decode($googleAnalytics?->data,true)['app_id']])
@endif
@if($tawkChat)
    @include('frontend::plugin.tawk',['data' => json_decode($tawkChat->data, true)])
@endif
@if($fb)
    @include('frontend::plugin.fb',['data' => json_decode($fb->data, true)])
@endif
