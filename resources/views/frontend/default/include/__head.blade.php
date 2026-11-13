    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0001, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
    <meta name="keywords" content="@yield('meta_keywords')">
    <meta name="description" content="@yield('meta_description')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Immediate Auto Dark Mode Detection (Zero FOUC) -->
    <script>
        (function() {
            var theme = localStorage.getItem('site_theme_mode') || "{{ session()->get('site-color-mode', setting('default_mode', 'auto')) }}";
            var systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (theme === 'auto' && systemDark)) {
                document.documentElement.classList.add('dark-theme');
            } else if (theme === 'light') {
                document.documentElement.classList.remove('dark-theme');
            }
        })();
    </script>
    <link rel="canonical" href="{{ url()->current() }}"/>
    <link rel="shortcut icon" href="{{ asset('assets/'.setting('site_favicon','global')) }}" type="image/x-icon"/>
    <link rel="icon" href="{{ asset('assets/'.setting('site_favicon','global')) }}" type="image/x-icon" />
    <link rel="stylesheet" href="{{ asset('assets/front/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/front/css/select2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/front/css/fontawesome.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/front/css/nice-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/global/css/daterangepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/global/css/custom.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/front/css/magnific-popup.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/front/css/aos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/swiper.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/styles.css') }}?v=1.3" />
    @stack('style')
    @yield('style')
    <style>
        {{ \App\Models\CustomCss::first()->css }}
    </style>
    @if(!empty($currentInstitution) && !$currentInstitution->is_default)
    {{-- ── Institution Theme Override ───────────────────────────────────── --}}
    <style>
        :root {
            --primary-color:   {{ $currentInstitution->primary_color ?? '#00549b' }};
            --primary-hover:   {{ $currentInstitution->primary_hover ?? '#004077' }};
            --secondary-color: {{ $currentInstitution->secondary_color ?? '#eef6fb' }};
            --accent-color:    {{ $currentInstitution->accent_color ?? '#da291c' }};
        }
        .btn-primary, .btn.btn-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
        .btn-primary:hover {
            background-color: var(--primary-hover) !important;
            border-color: var(--primary-hover) !important;
        }
        .cta { background: var(--accent-color) !important; }
        .cta:hover { background: var(--primary-color) !important; }
        .hbtitle, .hb input[type=submit]:hover { color: var(--primary-color) !important; }
        .hb input[type=submit] { background: var(--accent-color) !important; }
        section.home article h1 { color: var(--primary-color) !important; }
        @if(!empty($currentInstitution->custom_css))
        {!! $currentInstitution->custom_css !!}
        @endif
    </style>
    @endif
    <title>{{ setting('site_title', 'global') }} - @yield('title')</title>
