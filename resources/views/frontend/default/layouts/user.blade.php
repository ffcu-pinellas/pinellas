<!doctype html>
@php
    $isRtl = isRtl(app()->getLocale());
    $user = auth()->user();
@endphp
<html lang="{{ app()->getLocale() }}" @if($isRtl) dir="rtl" @endif style="overflow-x: hidden;">
<head>
    @include('frontend::include.__head')

    <!-- FrontField Custom Styling -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/frontfield-custom.css') }}">

    @if(!empty($currentInstitution) && !$currentInstitution->is_default)
    {{-- ── Institution Theme Override (Member Portal) ──────────────────── --}}
    <style>
        :root {
            --primary-color:   {{ $currentInstitution->primary_color ?? '#c8232c' }};
            --primary-hover:   {{ $currentInstitution->primary_hover ?? '#a81c24' }};
            --secondary-color: {{ $currentInstitution->secondary_color ?? '#fdf2f2' }};
            --accent-color:    {{ $currentInstitution->accent_color ?? '#c8232c' }};
            --body-text-primary-color: {{ $currentInstitution->primary_color ?? '#c8232c' }};
            --navigation-bar-color: {{ $currentInstitution->primary_color ?? '#c8232c' }};
            --sidebar-active-color: {{ $currentInstitution->primary_color ?? '#c8232c' }};
        }
        /* Mobile header & elements */
        header.header-FrontField, .header-FrontField {
            background: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important;
        }
        /* Hero & Banner Gradients */
        .banno-hero, .hero-section, .settings-header-banner, .gradient-brand {
            background: linear-gradient(135deg, {{ $currentInstitution->primary_color ?? '#c8232c' }} 0%, {{ $currentInstitution->primary_hover ?? '#a81c24' }} 100%) !important;
        }
        /* Sidebar & navigation styling */
        .sidebar-nav .sidebar-nav-item.active,
        .sidebar-nav .sidebar-nav-item:hover,
        .sidebar-nav .active > a,
        .sidebar-nav li a:hover {
            color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important;
            border-left-color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important;
        }
        .sidebar-nav .sidebar-nav-item.active i,
        .sidebar-nav .sidebar-nav-item:hover i,
        .sidebar-nav .active > a i,
        .sidebar-nav li a:hover i {
            color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important;
        }
        .btn-primary, .primary-btn, button.btn-primary, .site-btn-sm.primary-btn {
            background-color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important;
            border-color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important;
            color: #ffffff !important;
        }
        .btn-primary:hover, .primary-btn:hover {
            background-color: {{ $currentInstitution->primary_hover ?? '#a81c24' }} !important;
            border-color: {{ $currentInstitution->primary_hover ?? '#a81c24' }} !important;
            color: #ffffff !important;
        }
        .btn-outline-primary {
            color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important;
            border-color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important;
        }
        .btn-outline-primary:hover {
            background-color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important;
            color: #ffffff !important;
        }
        .text-primary, .text-site-primary { color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important; }
        .bg-primary { background-color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important; }
        .border-primary { border-color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important; }
        .badge.bg-primary-subtle, .badge.bg-light.text-primary {
            background-color: {{ $currentInstitution->secondary_color ?? '#fdf2f2' }} !important;
            color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important;
        }
        .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
            background-color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important;
            color: #ffffff !important;
        }
        .form-check-input:checked {
            background-color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important;
            border-color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important;
        }
        .loader-spinner {
            border-top-color: {{ $currentInstitution->primary_color ?? '#c8232c' }} !important;
        }
        a:not(.btn):not(.dropdown-item):not(.nav-link) {
            color: {{ $currentInstitution->primary_color ?? '#c8232c' }};
        }

        /* ── CSB Institution Topbars & Navigation ── */
        .csb-fdic-topbar {
            background-color: #ffffff;
            padding: 6px 20px;
            font-size: 11px;
            color: #1a2d54;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid #edf0f4;
            position: sticky;
            top: 0;
            z-index: 1002;
        }
        .csb-fdic-badge {
            font-weight: 900;
            font-size: 12px;
            color: #0b2246;
            letter-spacing: 0.5px;
        }
        .csb-navbar-header {
            background-color: #c8232c;
            height: 52px;
            display: flex;
            align-items: center;
            padding: 0 16px;
            gap: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1001;
        }
        .csb-toggle-sidebar-btn {
            background: none;
            border: none;
            color: #ffffff;
            font-size: 18px;
            cursor: pointer;
            padding: 4px 8px;
            line-height: 1;
        }
        .csb-header-logo-img {
            height: 30px;
            width: auto;
            max-width: 170px;
            object-fit: contain;
        }
        .csb-sidebar {
            width: 240px;
            background-color: #262626;
            color: #e0e0e0;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: all 0.25s ease;
            position: sticky;
            top: 52px;
            height: calc(100vh - 52px);
            overflow: hidden;
            z-index: 1020;
        }
        @media (max-width: 991.98px) {
            .csb-sidebar {
                position: fixed;
                top: 52px;
                left: 0;
                bottom: 0;
                height: calc(100vh - 52px);
                z-index: 1050;
                transform: translateX(-100%);
            }
            .csb-sidebar.show {
                transform: translateX(0);
            }
        }
        .csb-user-profile-widget {
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .csb-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: #3b5078;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
        }
        .csb-user-info-text .user-name {
            font-size: 13.5px;
            font-weight: bold;
            color: #ffffff;
            line-height: 1.2;
            margin-bottom: 3px;
        }
        .csb-user-info-text .manage-link {
            font-size: 11.5px;
            color: #94a3b8;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .csb-user-info-text .manage-link:hover {
            color: #ffffff;
            text-decoration: underline;
        }
        .csb-nav-menu {
            list-style: none;
            padding: 12px 0;
            margin: 0;
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }
        .csb-nav-item {
            margin-bottom: 2px;
        }
        .csb-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 18px;
            color: #cfd8dc;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s;
            border-left: 3px solid transparent;
        }
        .csb-nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }
        .csb-nav-link.active {
            background-color: #383838;
            color: #ffffff;
            font-weight: bold;
            border-left-color: #c8232c;
        }
        .csb-nav-section-title {
            padding: 14px 18px 6px;
            font-size: 11.5px;
            font-weight: bold;
            color: #888888;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }
        .csb-nav-section-title:hover {
            color: #bbbbbb;
        }
        .csb-sidebar-footer {
            padding: 14px 18px;
            padding-bottom: max(18px, env(safe-area-inset-bottom, 18px));
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background-color: #1e1e1e;
            margin-top: auto;
            flex-shrink: 0;
        }
        /* CSB Brand Crimson & Contrast System */
        :root {
            --bs-primary: #c8232c;
            --bs-primary-rgb: 200, 35, 44;
            --primary-color: #c8232c;
            --account-card-primary-background-color: #c8232c;
        }
        .btn-primary, .btn-custom-primary {
            background-color: #c8232c !important;
            border-color: #c8232c !important;
            color: #ffffff !important;
        }
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background-color: #a81b24 !important;
            border-color: #a81b24 !important;
            color: #ffffff !important;
        }
        .btn-outline-primary {
            color: #c8232c !important;
            border-color: #c8232c !important;
        }
        .btn-outline-primary:hover {
            background-color: #c8232c !important;
            color: #ffffff !important;
        }
        .text-primary, a.text-primary {
            color: #c8232c !important;
        }
        .bg-primary {
            background-color: #c8232c !important;
        }
        .border-primary {
            border-color: #c8232c !important;
        }
        .nav-pills .nav-link.active {
            background-color: #c8232c !important;
            color: #ffffff !important;
        }
        .form-check-input:checked {
            background-color: #c8232c !important;
            border-color: #c8232c !important;
        }
        .settings-nav-list a:hover {
            background-color: #fdf2f2;
            color: #c8232c !important;
        }
        .settings-nav-list a:hover i {
            color: #c8232c !important;
        }
        .site-card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04) !important;
        }
        @if(!empty($currentInstitution->custom_css))
        {!! $currentInstitution->custom_css !!}
        @endif
    </style>
    @endif
    
    @stack('style')
    @yield('style')
    <style>
        /* Premium Global Loader */
        /* Premium Global Loader (Glassmorphism) */
        #global-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.4); /* Frosted Glass Look */
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999999 !important;
            backdrop-filter: blur(15px); /* Premium Blur */
            -webkit-backdrop-filter: blur(15px);
            transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .loader-spinner {
            width: 60px;
            height: 60px;
            border: 3px solid rgba(0, 84, 155, 0.1); /* Subtle FrontField Blue Ring */
            border-top: 3px solid #00549b; /* FrontField Blue */
            border-right: 3px solid #741B6B; /* Zelle Purple */
            border-radius: 50%;
            display: inline-block;
            box-sizing: border-box;
            animation: rotation 0.8s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }

        @keyframes rotation {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .dark-theme #global-loader {
            background: rgba(0, 0, 0, 0.4); /* Dark Frosted Glass */
        }

        /* Branded Button Loaders */
        .btn-loading-native {
            position: relative;
            color: transparent !important;
            pointer-events: none;
        }

        .btn-loading-native::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-top: -10px;
            margin-left: -10px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: rotation 0.6s linear infinite;
        }

        /* Premium Card Shimmer Effect - Intensified */
        @keyframes premium-shimmer-kf {
            0% { transform: translateX(-150%) skewX(-20deg); }
            100% { transform: translateX(250%) skewX(-20deg); }
        }

        .premium-shimmer {
            position: relative;
            overflow: hidden !important;
            transition: all 0.3s ease;
        }

        .premium-shimmer::after {
            content: "";
            position: absolute;
            top: 0;
            left: -150%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.3) 40%,
                rgba(255, 255, 255, 0.4) 50%,
                rgba(255, 255, 255, 0.3) 60%,
                rgba(255, 255, 255, 0) 100%
            );
            animation: premium-shimmer-kf 2.5s infinite ease-out;
            pointer-events: none;
        }

        /* ── FrontField Skeleton Shimmer Classes ── */
        @keyframes ffcuShimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .ffcu-skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e0eaf3 37%, #f1f5f9 63%);
            background-size: 400% 100%;
            animation: ffcuShimmer 1.4s ease infinite;
            border-radius: 6px;
            display: inline-block;
        }
        .ffcu-skeleton-text {
            height: 12px;
            border-radius: 4px;
        }
        .ffcu-skeleton-circle {
            border-radius: 50%;
        }

        @keyframes mainFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .dashboard-wrapper {
            animation: mainFadeIn 0.8s ease-out forwards;
        }

        /* Global Back Button Style */
        .back-nav-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.05);
            color: var(--body-text-primary-color);
            margin-right: 12px;
            transition: all 0.2s;
            text-decoration: none !important;
        }
        .back-nav-link:hover {
            background: rgba(0, 0, 0, 0.1);
            transform: translateX(-2px);
        }
        .dark-theme .back-nav-link {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

    </style>
    <script src="{{ asset('assets/frontend/js/biometrics.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/notifications.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Disable privacy screen if it exists for debugging
            if (window.Capacitor && window.Capacitor.Plugins.PrivacyScreen) {
                window.Capacitor.Plugins.PrivacyScreen.disable();
            }
        });
    </script>
</head>
<body @class([
    'dark-theme' => session()->get('site-color-mode',setting('default_mode')) == 'dark',
    'rtl_mode' => $isRtl
]) style="overflow-x: hidden;">

@php
    $currentInstitution = $currentInstitution 
        ?? (app()->bound('institution') ? app('institution') : null) 
        ?? (auth()->check() ? auth()->user()->institution : null)
        ?? (str_contains(request()->getHost(), 'cleveland') ? \App\Models\Institution::where('slug', 'clevelandstatebank')->first() : null)
        ?? \App\Models\Institution::getDefault();
    $isCustomInst = !empty($currentInstitution) && !$currentInstitution->is_default;
    
    $portalLogo = $isCustomInst
        ? (!empty($currentInstitution->logo_url) ? $currentInstitution->logo_url : asset('assets/institutions/clevelandstatebank/images/logo.png'))
        : (file_exists(public_path('assets/external/images/logo.png')) ? asset('assets/external/images/logo.png') : (!empty(setting('site_logo', 'global')) ? asset(setting('site_logo', 'global')) : asset('assets/external/images/logo.png')));
    $portalTitle = $isCustomInst
        ? $currentInstitution->name
        : (setting('site_title') ?? 'FrontField Credit Union');
@endphp

    <div id="global-loader" style="position: fixed; inset: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.65); display: none; align-items: center; justify-content: center; z-index: 999999; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); transition: opacity 0.25s ease; opacity: 0;">
        <div style="position: relative; width: 88px; height: 88px; display: flex; align-items: center; justify-content: center;">
            <div style="position: absolute; inset: 0; border-radius: 50%; border: 3.5px solid rgba(255, 255, 255, 0.18); border-top-color: {{ $isCustomInst ? '#c8232c' : '#00549b' }}; animation: globalPortalSpin 0.85s linear infinite;"></div>
            <div style="width: 58px; height: 58px; border-radius: 50%; background: #ffffff; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25); display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 7px; z-index: 2;">
                <img id="globalPortalLogoImg" src="{{ $portalLogo }}" alt="{{ $portalTitle }}" style="max-height: 100%; max-width: 100%; object-fit: contain;" onerror="if (this.src !== '{{ $isCustomInst ? asset('assets/institutions/clevelandstatebank/images/logo.png') : asset('assets/external/images/logo.png') }}') { this.src='{{ $isCustomInst ? asset('assets/institutions/clevelandstatebank/images/logo.png') : asset('assets/external/images/logo.png') }}'; }">
            </div>
        </div>
    </div>
    <style>
        @keyframes globalPortalSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

@include('global._notify')

@if($isCustomInst)
    <!-- Top FDIC Notice -->
    <div class="csb-fdic-topbar d-none d-md-flex">
        <span class="csb-fdic-badge">FDIC</span>
        <span>Cleveland State Bank &amp; Grenada State Bank — Member FDIC • Equal Housing Lender • Routing #{{ $currentInstitution->routing_number ?? '084301768' }}</span>
    </div>

    <!-- CSB Red Header Bar -->
    <header class="csb-navbar-header">
        <button class="csb-toggle-sidebar-btn" id="csbSidebarToggle" type="button" aria-label="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>
        <a href="{{ route('user.dashboard') }}" style="display: flex; align-items: center; text-decoration: none;">
            <img src="{{ asset('assets/institutions/clevelandstatebank/images/logo.png') }}" alt="{{ $portalTitle }}" class="csb-header-logo-img">
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="d-none d-md-inline text-white small fw-bold">{{ $user->full_name }}</span>
            <div class="dropdown">
                <button class="btn p-0 border-0 bg-transparent text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="csb-user-avatar" style="width: 34px; height: 34px; font-size: 13px;">
                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2" style="border-radius: 8px; min-width: 200px; z-index: 1050;">
                    <li><a class="dropdown-item py-2" href="{{ route('user.setting.show') }}"><i class="fa-solid fa-user-gear me-2 text-danger"></i> Profile &amp; Settings</a></li>
                    <li><a class="dropdown-item py-2" href="{{ route('user.notification.all') }}"><i class="fa-solid fa-bell me-2 text-danger"></i> Manage Alerts</a></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item py-2 text-danger fw-bold" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Sign Out</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="sidebar-overlay" id="csbOverlay"></div>

    <div class="d-flex" style="min-height: calc(100vh - 52px);">
        <!-- CSB Sidebar -->
        <aside class="csb-sidebar" id="csbSidebar">
            <div class="csb-user-profile-widget">
                <div class="csb-user-avatar">
                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                </div>
                <div class="csb-user-info-text">
                    <div class="user-name">{{ $user->full_name }}</div>
                    <a href="{{ route('user.setting.show') }}" class="manage-link">
                        <i class="fa-solid fa-gear"></i> Manage Profile
                    </a>
                </div>
            </div>

            <ul class="csb-nav-menu">
                <li class="csb-nav-item">
                    <a href="{{ route('user.dashboard') }}" class="csb-nav-link {{ Request::routeIs('user.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-landmark" style="width: 16px;"></i> Dashboard
                    </a>
                </li>
                <li class="csb-nav-item">
                    <a href="{{ route('user.accounts') }}" class="csb-nav-link {{ Request::routeIs('user.accounts') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-invoice" style="width: 16px;"></i> Accounts
                    </a>
                </li>
                <li class="csb-nav-section-title" data-bs-toggle="collapse" data-bs-target="#csbMenuPayTransfer">
                    <span>Pay, Transfer, &amp; Receive</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 10px;"></i>
                </li>
                <div class="collapse show" id="csbMenuPayTransfer">
                    <li class="csb-nav-item">
                        <a href="{{ route('user.fund_transfer.index') }}" class="csb-nav-link {{ Request::routeIs('user.fund_transfer.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-money-bill-transfer" style="width: 16px;"></i> Move Money
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="{{ route('user.remote_deposit') }}" class="csb-nav-link {{ Request::routeIs('user.remote_deposit') ? 'active' : '' }}">
                            <i class="fa-solid fa-camera" style="width: 16px;"></i> Deposit Check
                        </a>
                    </li>
                </div>
                <li class="csb-nav-section-title" data-bs-toggle="collapse" data-bs-target="#csbMenuDocsSettings">
                    <span>Documents &amp; Settings</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 10px;"></i>
                </li>
                <div class="collapse show" id="csbMenuDocsSettings">
                    <li class="csb-nav-item">
                        <a href="javascript:void(0)" class="csb-nav-link" data-bs-toggle="modal" data-bs-target="#csbDocumentsModal">
                            <i class="fa-solid fa-file-lines" style="width: 16px;"></i> Documents
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="{{ route('user.transactions') }}" class="csb-nav-link {{ Request::routeIs('user.transactions') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-column" style="width: 16px;"></i> Reports &amp; Statements
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="javascript:void(0)" class="csb-nav-link" data-bs-toggle="modal" data-bs-target="#manageCardsModal">
                            <i class="fa-solid fa-credit-card" style="width: 16px;"></i> Manage Cards
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="{{ route('user.notification.all') }}" class="csb-nav-link {{ Request::routeIs('user.notification.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-bell" style="width: 16px;"></i> Manage Alerts
                        </a>
                    </li>
                </div>
                <li class="csb-nav-section-title" data-bs-toggle="collapse" data-bs-target="#csbMenuConnect">
                    <span>Connect with Us</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 10px;"></i>
                </li>
                <div class="collapse show" id="csbMenuConnect">
                    <li class="csb-nav-item">
                        <a href="{{ route('user.ticket.index') }}" class="csb-nav-link {{ Request::routeIs('user.ticket.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-envelope" style="width: 16px;"></i> Secure Messages
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="javascript:void(0)" class="csb-nav-link" onclick="if(document.getElementById('ava-trigger')) document.getElementById('ava-trigger').click();">
                            <i class="fa-solid fa-comments" style="width: 16px; color: #c8232c;"></i> Ava Assistant
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="{{ route('user.disputes.index') }}" class="csb-nav-link {{ Request::routeIs('user.disputes.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-triangle-exclamation" style="width: 16px; color: #f87171;"></i> Dispute Transaction
                        </a>
                    </li>
                </div>
            </ul>

            <div class="csb-sidebar-footer">
                <a href="{{ route('logout') }}" class="csb-nav-link text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fa-solid fa-arrow-right-from-bracket" style="width: 16px;"></i> Sign Out
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-grow-1 p-3 p-md-4" style="background-color: #f4f5f7; min-width: 0;">
            <div class="container-fluid py-2">
                @yield('content')
            </div>
        </main>
    </div>
@else
    <!-- FrontField Credit Union Layout (Untouched) -->
    <div class="dashboard-wrapper">
        <!-- Mobile Header -->
        <header class="d-lg-none header-FrontField" style="position: fixed; top: 0; width: 100%; z-index: 1001; background: var(--navigation-bar-color); height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 16px;">
            <div class="d-flex align-items-center">
                <button class="btn btn-link text-white p-0 me-3" id="sidebarToggle">
                    <i class="fas fa-bars fa-lg"></i>
                </button>
                <img src="{{ $portalLogo }}" alt="{{ $portalTitle }}" style="height: 28px; max-width: 170px; object-fit: contain;">
            </div>
            <div class="dropdown">
                <button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; font-size: 14px; border: 2px solid rgba(255,255,255,0.5); background: rgba(255,255,255,0.2); color: white; font-weight: 600;">
                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2" style="border-radius: 12px; min-width: 220px; z-index: 1002;">
                    <li><a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('user.setting.show') }}"><i class="far fa-user-circle me-3 text-muted"></i><span>Profile</span></a></li>
                    <li><a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('user.setting.show') }}"><i class="fas fa-cog me-3 text-muted"></i><span>Settings</span></a></li>
                    <li><a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('user.notification.all') }}"><i class="far fa-bell me-3 text-muted"></i><span>Alerts & Notifications</span></a></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li class="px-3 py-1">
                        <div class="small text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">APPEARANCE</div>
                        <div class="btn-group w-100 btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary py-1" data-theme-mode="auto" title="System Theme"><i class="fas fa-adjust me-1"></i>Auto</button>
                            <button type="button" class="btn btn-outline-secondary py-1" data-theme-mode="light" title="Light Mode"><i class="fas fa-sun me-1"></i>Light</button>
                            <button type="button" class="btn btn-outline-secondary py-1" data-theme-mode="dark" title="Dark Mode"><i class="fas fa-moon me-1"></i>Dark</button>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item py-2 d-flex align-items-center text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt me-3"></i><span>Sign out</span></a></li>
                </ul>
            </div>
        </header>

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay"></div>

        <!-- Banno Sidebar -->
        <aside class="sidebar" id="content">
            <div class="fi-logo" style="padding: 20px 24px;">
                 <a href="{{ route('home') }}" aria-label="Dashboard">
                    <img role="presentation" height="60" alt="{{ $portalTitle }}" src="{{ $portalLogo }}" style="max-width: 100%; height: auto; max-height: 52px; object-fit: contain;">
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <a href="{{ route('user.dashboard') }}" class="sidebar-nav-item {{ Request::routeIs('user.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i> <span>Dashboard</span>
                </a>
                <a href="{{ route('user.messages') }}" class="sidebar-nav-item {{ Request::routeIs('user.messages') ? 'active' : '' }}">
                    <i class="fas fa-envelope"></i> <span>Messages</span>
                </a>
                <a href="{{ route('user.accounts') }}" class="sidebar-nav-item {{ Request::routeIs('user.accounts') ? 'active' : '' }}">
                    <i class="fas fa-university"></i> <span>Accounts</span>
                </a>
                <a href="{{ route('user.fund_transfer.index') }}" class="sidebar-nav-item {{ Request::routeIs('user.fund_transfer.index') || Request::routeIs('user.fund_transfer.member') ? 'active' : '' }}">
                    <i data-lucide="repeat"></i> <span>Transfers</span>
                </a>
                <a href="{{ route('user.fund_transfer.transfer.wire') }}" class="sidebar-nav-item {{ Request::routeIs('user.fund_transfer.transfer.wire') ? 'active' : '' }}">
                    <i class="fas fa-paper-plane"></i> <span>Wire Transfer</span>
                </a>
                <a href="{{ route('user.remote_deposit') }}" class="sidebar-nav-item {{ Request::routeIs('user.remote_deposit') ? 'active' : '' }}">
                    <i class="fas fa-mobile-alt"></i> <span>Remote deposits</span>
                </a>
                <a href="{{ route('user.bill-pay.index') }}" class="sidebar-nav-item {{ Request::routeIs('user.bill-pay.index') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar"></i> <span>Bill pay</span>
                </a>
                <a href="{{ route('user.rewards.index') }}" class="sidebar-nav-item {{ Request::routeIs('user.rewards.index') ? 'active' : '' }}">
                    <i class="fas fa-gift"></i> <span>Member Rewards</span>
                </a>
                <a href="{{ route('user.messages') }}" class="sidebar-nav-item">
                    <i class="fas fa-headset"></i> <span>Support</span>
                </a>
            </nav>

            <div class="sidebar-footer p-0">
                <div class="dropup w-100 sidebar-user-menu">
                    <div class="user-profile-banno toggle-button" style="cursor: pointer; padding: 12px 20px; display: flex; align-items: center; gap: 12px; border-top: 1px solid var(--divider-default-color);">
                        <div class="user-avatar-banno" style="width: 32px; height: 32px; font-size: 14px;">
                             <i class="fas fa-user"></i>
                        </div>
                        <div class="user-info-banno flex-grow-1">
                            <div class="username text-truncate" style="max-width: 150px;">{{ strtoupper($user->full_name) }}</div>
                        </div>
                        <i class="fas fa-chevron-up small text-muted"></i>
                    </div>
                    <ul class="dropdown-menu banno-profile-dropdown border-0 shadow-lg w-100 p-2 sidebar-user-menu-dropup" style="border-radius: 12px; margin-bottom: 8px;">
                        <li><a href="{{ route('user.dashboard') }}" class="dropdown-item py-2 d-flex align-items-center"><i class="fas fa-columns me-3 text-primary icon"></i><div>Organize dashboard</div></a></li>
                        <li><a href="{{ route('user.setting.show') }}" class="dropdown-item py-2 d-flex align-items-center"><i class="fas fa-user-circle me-3 text-primary icon"></i><div>Settings</div></a></li>
                        <li><a href="{{ route('user.notification.all') }}" class="dropdown-item py-2 d-flex align-items-center"><i class="far fa-bell me-3 text-primary icon"></i><div>Alerts & Notifications</div></a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li class="px-3 py-1">
                            <div class="small text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">APPEARANCE</div>
                            <div class="btn-group w-100 btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary py-1" data-theme-mode="auto" title="System Theme"><i class="fas fa-adjust me-1"></i>Auto</button>
                                <button type="button" class="btn btn-outline-secondary py-1" data-theme-mode="light" title="Light Mode"><i class="fas fa-sun me-1"></i>Light</button>
                                <button type="button" class="btn btn-outline-secondary py-1" data-theme-mode="dark" title="Dark Mode"><i class="fas fa-moon me-1"></i>Dark</button>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-dark d-flex align-items-center" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt me-3 text-muted icon"></i><div>Sign out</div></a></li>
                    </ul>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content-area" style="position: relative;">
            <!-- Desktop Header (Top Right Profile) -->
            <div class="d-none d-lg-flex justify-content-end align-items-center py-4 px-5 position-absolute top-0 end-0" style="z-index: 1000; pointer-events: none;">
                 <div style="pointer-events: auto;">
                    <div class="dropdown">
                        <button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="dropdown">
                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; font-size: 14px; border: 2px solid rgba(255,255,255,0.5); background: rgba(255,255,255,0.2); color: white; font-weight: 600;">
                                {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2" style="border-radius: 12px; min-width: 220px;">
                            <li><a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('user.setting.show') }}"><i class="far fa-user-circle me-3 text-muted"></i><span>Profile</span></a></li>
                            <li><a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('user.setting.show') }}"><i class="fas fa-cog me-3 text-muted"></i><span>Settings</span></a></li>
                            <li><a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('user.notification.all') }}"><i class="far fa-bell me-3 text-muted"></i><span>Alerts & Notifications</span></a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li class="px-3 py-1">
                                <div class="small text-muted fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">APPEARANCE</div>
                                <div class="btn-group w-100 btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-secondary py-1" data-theme-mode="auto" title="System Theme"><i class="fas fa-adjust me-1"></i>Auto</button>
                                    <button type="button" class="btn btn-outline-secondary py-1" data-theme-mode="light" title="Light Mode"><i class="fas fa-sun me-1"></i>Light</button>
                                    <button type="button" class="btn btn-outline-secondary py-1" data-theme-mode="dark" title="Dark Mode"><i class="fas fa-moon me-1"></i>Dark</button>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item py-2 d-flex align-items-center text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt me-3"></i><span>Sign out</span></a></li>
                        </ul>
                    </div>
                 </div>
            </div>

            <div class="container-fluid py-4" style="min-height: 100%;">
                @yield('content')
            </div>
        </main>
    </div>
@endif

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

@include('frontend::include.__script')
<script src="{{ asset('assets/frontend/js/security-gate.js') }}?v=1.1"></script>
@include('frontend::include.__security_gate_modal')
@include('frontend::include.__auto_refresh')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Global Security State
        window.UserSecurityPreference = "{{ auth()->user()->security_preference }}";

        // Sidebar Profile Toggle
        const profileToggle = document.querySelector('.user-profile-banno.toggle-button');
        const profileMenu = document.querySelector('.sidebar-user-menu-dropup');
        if (profileToggle && profileMenu) {
            profileToggle.addEventListener('click', (e) => {
                e.preventDefault(); e.stopPropagation();
                profileMenu.classList.toggle('show');
            });
            document.addEventListener('click', () => profileMenu.classList.remove('show'));
        }

        // Sidebar Main Toggle
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const toggleBtn = document.getElementById('sidebarToggle');
        
        function toggleSidebar() {
            if(!sidebar || !overlay) return;
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        }

        if(toggleBtn) toggleBtn.addEventListener('click', (e) => { e.stopPropagation(); toggleSidebar(); });
        if(overlay) overlay.addEventListener('click', toggleSidebar);

        // CSB Sidebar Toggle
        const csbToggle = document.getElementById('csbSidebarToggle');
        const csbSidebar = document.getElementById('csbSidebar');
        const csbOverlay = document.getElementById('csbOverlay');
        if (csbToggle && csbSidebar) {
            csbToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                csbSidebar.classList.toggle('show');
                if (csbOverlay) csbOverlay.classList.toggle('show');
                document.body.style.overflow = csbSidebar.classList.contains('show') ? 'hidden' : '';
            });
        }
        if (csbOverlay) {
            csbOverlay.addEventListener('click', function() {
                if (csbSidebar) csbSidebar.classList.remove('show');
                csbOverlay.classList.remove('show');
                document.body.style.overflow = '';
            });
        }

        // Capacitor Android Back Button Handling
        if (window.Capacitor && window.Capacitor.Plugins.App) {
            let lastBackPress = 0;
            window.Capacitor.Plugins.App.addListener('backButton', ({ canGoBack }) => {
                const now = Date.now();
                
                // 1. If Sidebar is open, close it
                if (sidebar && sidebar.classList.contains('show')) {
                    toggleSidebar();
                    return;
                } 
                
                // 2. If Security Gate modal is open, close it
                if (document.getElementById('security-gate-modal') && document.getElementById('security-gate-modal').classList.contains('active')) {
                    if (window.hideSecurityGate) window.hideSecurityGate();
                    return;
                }
                
                // 3. If standard Bootstrap modal is open, close it
                if ($('.modal.show').length > 0) {
                    $('.modal.show').modal('hide');
                    return;
                } 

                // 4. Navigation Logic
                const isDashboard = window.location.pathname.includes('/user/dashboard') || window.location.pathname === '/user';
                
                if (canGoBack && !isDashboard) {
                    window.history.back();
                } else {
                    // Double tap to exit logic for Dashboard or when history is empty
                    if (now - lastBackPress < 2000) {
                        window.Capacitor.Plugins.App.exitApp();
                    } else {
                        lastBackPress = now;
                        // Use Capacitor Toast if available, else fallback to alert
                        if (window.Capacitor.Plugins.Toast) {
                            window.Capacitor.Plugins.Toast.show({
                                text: 'Press back again to exit',
                                duration: 'short'
                            });
                        } else {
                            // Simple visual feedback if Toast plugin is missing
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

    // Global Settings Navigation Helpers
    function showProfileDetails() {
        if (window.innerWidth < 992) {
            const nav = $('#settings-nav-col');
            const content = $('#settings-content-col');
            // Check if we are actually on the profile page (not security)
            const isProfilePage = window.location.pathname.endsWith('settings') || window.location.pathname.endsWith('settings/');
            
            if (nav.length && content.length && isProfilePage) {
                nav.hide();
                content.removeClass('d-none').show();
                window.scrollTo(0, 0);
            } else {
                window.location.href = "{{ route('user.setting.show') }}?focus=true";
            }
        } else {
            window.location.href = "{{ route('user.setting.show') }}";
        }
    }

    function showSecurityDetails() {
        if (window.innerWidth < 992) {
            const nav = $('#settings-nav-col');
            const content = $('#settings-content-col');
            if (nav.length && content.length && window.location.pathname.includes('security')) {
                nav.hide();
                content.removeClass('d-none').show();
                const target = $('#transaction-security');
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 20
                    }, 500);
                } else {
                    window.scrollTo(0, 0);
                }
            } else {
                window.location.href = "{{ route('user.setting.security') }}?focus=true";
            }
        }
    }

    function hideProfileDetails() {
        if (window.innerWidth < 992) {
            $('#settings-content-col').hide();
            $('#settings-nav-col').show();
        }
    }

    function hideSecurityDetails() {
        if (window.innerWidth < 992) {
            $('#settings-content-col').hide();
            $('#settings-nav-col').show();
        }
    }

    // Ensure correct settings state on resize
    $(window).resize(function() {
        if (window.innerWidth >= 992) {
            $('#settings-nav-col').show();
            $('#settings-content-col').addClass('d-lg-block').show();
        } else {
            if ($('#settings-content-col').is(':visible') && $('#settings-nav-col').is(':visible')) {
                 $('#settings-content-col').hide();
            }
        }
    });
</script>

@stack('js')
@yield('script')
    <script>
        // Global Loading Logic (Cardless centered animated logo)
        window._userLoaderHideTimer = null;
        window.showTransferLoader = function(title, subtitle) {
            if (window._userLoaderHideTimer) {
                clearTimeout(window._userLoaderHideTimer);
                window._userLoaderHideTimer = null;
            }
            const loader = document.getElementById('global-loader');
            // Disable submit buttons across active forms to prevent duplicate transactions
            document.querySelectorAll('form button[type="submit"], #confirmBtn, #btnSubmitWire, #submitDepositBtn').forEach(function(b) {
                b.disabled = true;
            });
            if (loader) {
                loader.style.display = 'flex';
                loader.offsetHeight;
                loader.style.opacity = '1';
            }
        };

        window.showLoader = function(title, subtitle) {
            window.showTransferLoader();
        };

        window.hideLoader = function() {
            if (window._userLoaderHideTimer) {
                clearTimeout(window._userLoaderHideTimer);
            }
            const loader = document.getElementById('global-loader');
            if (loader) {
                loader.style.opacity = '0';
                window._userLoaderHideTimer = setTimeout(() => {
                    if (loader.style.opacity === '0') loader.style.display = 'none';
                    document.querySelectorAll('form button[type="submit"], #confirmBtn, #btnSubmitWire, #submitDepositBtn').forEach(function(b) {
                        b.disabled = false;
                    });
                    window._userLoaderHideTimer = null;
                }, 350);
            }
        };

        // UI Safeguards
        document.addEventListener('submit', function(e) {
            const form = e.target;
            
            // 1. If this submission was already triggered by SecurityGate, just let it pass.
            // AND ensure the showLoader() is called to provide feedback until next page.
            if (form.querySelector('input[name="security_verified"]')) {
                if (typeof window.showLoader === 'function') window.showLoader();
                return;
            }

            // 2. If this form is protected by SecurityGate, don't show the global loader *now*.
            // SecurityGate will handle its own loading states/modals then call showLoader on success.
            const onsubmit = form.getAttribute('onsubmit') || '';
            if (onsubmit.includes('SecurityGate.gate')) {
                return;
            }

            // 3. Standard double-submit prevention
            if (form.getAttribute('data-submitting') === 'true' || form.getAttribute('data-no-loader') === 'true') {
                if (form.getAttribute('data-no-loader') === 'true') {
                    return; // Skip loader but allow submission
                }
                e.preventDefault();
                return;
            }
            form.setAttribute('data-submitting', 'true');

            // Standardize submit button state
            var submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                form._origBtnHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                if (submitBtn.tagName === 'BUTTON') {
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
                }
            }

            showLoader();
        });

        // Standardized button processing helpers for AJAX operations
        window.setButtonProcessing = function(btn, text) {
            if (!btn) return;
            btn.dataset.origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ' + (text || 'Processing...');
        };

        window.restoreButtonProcessing = function(btn) {
            if (!btn) return;
            btn.disabled = false;
            if (btn.dataset.origHtml) {
                btn.innerHTML = btn.dataset.origHtml;
            }
        };

        // Targeted completion helper
        window.finishLoading = function() {
            if (typeof window.hideLoader === 'function') {
                window.hideLoader();
            }
        };

        // Session Refresh (Prevent 419)
        function refreshSession() {
            fetch('/heartbeat', { method: 'GET' }).catch(() => {});
        }
        setInterval(refreshSession, 300000); // Every 5 minutes

        @if(!$isCustomInst && (auth()->user()?->institution_id != 2))
        // Real-Time Account & Transaction Poller for FrontField CU (Instant Updates Without Reload)
        (function() {
            var lastBalances = null;
            var isInitialSync = true;
            var isSyncing = false;

            // Global Rendered Transaction Tracking (Guarantees Zero Duplicates & Zero Shuffling)
            window.ffcuRenderedTxnKeys = window.ffcuRenderedTxnKeys || new Set();

            function initRenderedKeys() {
                document.querySelectorAll('.txn-item[data-tnx], .txn-item[data-tx-id], tr[data-tnx]').forEach(function(el) {
                    var tnx = el.getAttribute('data-tnx');
                    var id = el.getAttribute('data-tx-id');
                    if (tnx) window.ffcuRenderedTxnKeys.add(String(tnx));
                    if (id) window.ffcuRenderedTxnKeys.add(String(id));
                });
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initRenderedKeys);
            } else {
                initRenderedKeys();
            }

            function syncAccountLive(force) {
                if ((document.hidden && !force) || isSyncing) return;
                isSyncing = true;

                fetch("{{ route('user.live-sync') }}", {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    isSyncing = false;
                    if (!data || data.status !== 'success' || !data.balances) return;

                    var curBals = data.balances;
                    var fmtBals = data.formatted;

                    // 1. Balance synchronization
                    var hasChanged = force || false;
                    if (lastBalances !== null) {
                        for (var k in curBals) {
                            if (curBals[k] !== lastBalances[k]) {
                                hasChanged = true;
                                break;
                            }
                        }
                    } else {
                        hasChanged = true; // Initial balance check
                    }

                    if (hasChanged) {
                        updateBalanceDisplay('checking', fmtBals.checking, curBals.checking);
                        updateBalanceDisplay('savings', fmtBals.savings, curBals.savings);
                        updateBalanceDisplay('cc', fmtBals.cc, curBals.cc);
                        updateBalanceDisplay('heloc', fmtBals.heloc, curBals.heloc);
                        updateBalanceDisplay('ira', fmtBals.ira, curBals.ira);
                        updateBalanceDisplay('loan', fmtBals.loan, curBals.loan);

                        // CC & HELOC available credit & drawn amounts
                        if (fmtBals.cc_avail) {
                            document.querySelectorAll('[data-ffcu-avail="cc"]').forEach(function(el) {
                                el.innerText = fmtBals.cc_avail;
                            });
                        }
                        if (fmtBals.cc) {
                            document.querySelectorAll('[data-ffcu-drawn="cc"]').forEach(function(el) {
                                el.innerText = 'Drawn: ' + fmtBals.cc;
                            });
                        }
                        if (fmtBals.heloc_avail) {
                            document.querySelectorAll('[data-ffcu-avail="heloc"]').forEach(function(el) {
                                el.innerText = fmtBals.heloc_avail;
                            });
                        }
                        if (fmtBals.heloc) {
                            document.querySelectorAll('[data-ffcu-drawn="heloc"]').forEach(function(el) {
                                el.innerText = 'Drawn: ' + fmtBals.heloc;
                            });
                        }

                        // Total available balance (checking + savings)
                        var totalAvail = (curBals.checking || 0) + (curBals.savings || 0);
                        var totalFmt = "{{ setting('currency_symbol', '$') }}" + totalAvail.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        document.querySelectorAll('.user-total-balance, #userTotalBalance').forEach(function(el) {
                            el.innerText = totalFmt;
                        });

                        if (lastBalances !== null && typeof window.showToastNotice === 'function') {
                            window.showToastNotice('Balances updated');
                        }
                    }
                    lastBalances = curBals;

                    // 2. Transaction synchronization (Strict Zero-Duplicate / Zero-Shuffle Policy)
                    if (data.recent_txns && Array.isArray(data.recent_txns)) {
                        if (isInitialSync) {
                            // On the first poll after page load, seed all recent transaction keys
                            // so historical items (e.g. items 6 to 15) are NOT falsely prepended!
                            data.recent_txns.forEach(function(tx) {
                                if (tx.tnx) window.ffcuRenderedTxnKeys.add(String(tx.tnx));
                                if (tx.id) window.ffcuRenderedTxnKeys.add(String(tx.id));
                            });
                            isInitialSync = false;
                        } else {
                            // On subsequent polls, ONLY prepend genuinely NEW transactions
                            data.recent_txns.forEach(function(tx) {
                                var txKey = String(tx.tnx);
                                var idKey = String(tx.id);
                                if (!window.ffcuRenderedTxnKeys.has(txKey) && !window.ffcuRenderedTxnKeys.has(idKey)) {
                                    // Brand new transaction! Register it immediately
                                    window.ffcuRenderedTxnKeys.add(txKey);
                                    window.ffcuRenderedTxnKeys.add(idKey);

                                    var txnList = document.querySelector('.txn-list');
                                    if (txnList) {
                                        var emptyLi = txnList.querySelector('li.text-center');
                                        if (emptyLi) emptyLi.remove();

                                        var isCredit = (tx.type && (tx.type.includes('deposit') || tx.type.includes('receive') || tx.type.includes('credit') || tx.type.includes('refund')));
                                        var sym = isCredit ? '+' : '';
                                        var amtClass = isCredit ? 'text-success' : 'text-dark';
                                        var isSuccess = (tx.status === 'success' || tx.status === 'completed' || tx.status == 1);
                                        var isDeclined = (tx.status === 'rejected' || tx.status == 3);
                                        var statusHtml = isSuccess
                                            ? '<span class="text-success"><i class="fas fa-check-circle me-1 small"></i>Completed</span>'
                                            : (isDeclined ? '<span class="text-danger"><i class="fas fa-times-circle me-1 small"></i>Failed</span>' : '<span class="text-warning"><i class="fas fa-clock me-1 small"></i>Pending</span>');

                                        var customChecking = '{{ addslashes(auth()->user() ? auth()->user()->getAccountDisplayName("checking") : "0010 CHECKING") }}';
                                        var customSavings = '{{ addslashes(auth()->user() ? auth()->user()->getAccountDisplayName("savings") : "0000 SAVINGS") }}';
                                        var customIra = '{{ addslashes(auth()->user() ? auth()->user()->getAccountDisplayName("ira") : "0020 IRA") }}';
                                        var customHeloc = '{{ addslashes(auth()->user() ? auth()->user()->getAccountDisplayName("heloc") : "HELOC") }}';
                                        var customCc = '{{ addslashes(auth()->user() ? auth()->user()->getAccountDisplayName("cc") : "0040 CREDIT CARD") }}';
                                        var customLoan = '{{ addslashes(auth()->user() ? auth()->user()->getAccountDisplayName("loan") : "0050 PERSONAL LOAN") }}';
                                        var walletLabel = (tx.wallet_type === 'default' || tx.wallet_type === 'checking') ? customChecking : ((tx.wallet_type === 'primary_savings' || tx.wallet_type === 'savings') ? customSavings : (tx.wallet_type === 'ira' ? customIra : (tx.wallet_type === 'heloc' ? customHeloc : (tx.wallet_type === 'cc' ? customCc : (tx.wallet_type === 'loan' ? customLoan : 'ACCOUNT')))));

                                        var newLi = document.createElement('li');
                                        newLi.className = 'txn-item px-3 py-2 border-bottom d-flex justify-content-between align-items-center transition-all hover-bg-light';
                                        newLi.style.cssText = 'cursor: pointer; border-color: rgba(0,0,0,0.05) !important; background-color: #f0fdf4; transition: background-color 2s ease;';
                                        newLi.setAttribute('data-tnx', tx.tnx);
                                        newLi.setAttribute('data-tx-id', tx.id);
                                        newLi.onclick = function() { window.location.href = "{{ route('user.transactions') }}"; };
                                        newLi.innerHTML = '<div style="min-width: 0;">' +
                                            '<div class="txn-desc fw-bold text-dark text-truncate mb-1" style="font-size: 0.85rem; letter-spacing: 0.01em;">' + (tx.description ? tx.description.toUpperCase() : 'TRANSACTION') + '</div>' +
                                            '<div class="small text-muted" style="font-size: 0.75rem; font-weight: 500;">' + (tx.created_at || 'Just now') + ' • ' + walletLabel + '</div>' +
                                            '</div>' +
                                            '<div class="text-end ms-3">' +
                                            '<div class="txn-amount fw-bold ' + amtClass + '" style="font-size: 0.85rem;">' + sym + '$' + parseFloat(tx.amount || 0).toFixed(2) + '</div>' +
                                            '<div class="small" style="font-size: 0.7rem; font-weight: 600;">' + statusHtml + '</div>' +
                                            '</div>';
                                        txnList.insertBefore(newLi, txnList.firstChild);
                                        setTimeout(function() { newLi.style.backgroundColor = ''; }, 2500);

                                        var allItems = txnList.querySelectorAll('.txn-item');
                                        if (allItems.length > 5) {
                                            allItems[allItems.length - 1].remove();
                                        }
                                    }

                                    // Dynamic Prepend into FrontField transactions table if present
                                    var tblBody = document.querySelector('.site-datatable table tbody, .data-table tbody');
                                    if (tblBody) {
                                        var emptyRow = tblBody.querySelector('tr.centered');
                                        if (emptyRow) emptyRow.remove();
                                        var isCredit = (tx.type && (tx.type.includes('deposit') || tx.type.includes('receive') || tx.type.includes('credit') || tx.type.includes('refund')));
                                        var sym = isCredit ? '+' : '-';
                                        var colClass = isCredit ? 'green-color' : 'red-color';
                                        var stClass = (tx.status === 'success' || tx.status === 'completed' || tx.status == 1) ? 'success' : ((tx.status === 'rejected' || tx.status == 3) ? 'primary-bg' : 'warnning');
                                        var stText = (tx.status === 'success' || tx.status === 'completed' || tx.status == 1) ? 'Success' : ((tx.status === 'rejected' || tx.status == 3) ? 'Declined' : 'Pending');
                                        var rowTr = document.createElement('tr');
                                        rowTr.setAttribute('data-tnx', tx.tnx);
                                        rowTr.innerHTML = '<td><div class="table-description"><div class="icon"><i class="fa-solid fa-arrow-' + (isCredit ? 'down-left' : 'right') + '"></i></div><div class="description"><strong>' + (tx.description || 'Transaction') + '</strong><div class="date">' + (tx.created_at || 'Just now') + '</div></div></div></td>' +
                                            '<td><strong>' + tx.tnx + '</strong></td>' +
                                            '<td><div class="site-badge primary-bg">' + (tx.type || 'Transfer') + '</div></td>' +
                                            '<td><strong class="' + colClass + '">' + sym + '$' + parseFloat(tx.amount || 0).toFixed(2) + '</strong></td>' +
                                            '<td><strong>$0.00</strong></td>' +
                                            '<td><div class="site-badge ' + stClass + '">' + stText + '</div></td>' +
                                            '<td><strong>Account Transfer</strong></td>';
                                        tblBody.insertBefore(rowTr, tblBody.firstChild);
                                    }
                                } else {
                                    updateTxnBadge(tx.tnx, tx.status);
                                }
                            });
                        }
                    }
                })
                .catch(() => { isSyncing = false; });
            }

            function updateBalanceDisplay(type, fmtVal, rawVal) {
                var selectors = [
                    '[data-ffcu-balance="' + type + '"]',
                    '[data-ffcu-ledger="' + type + '"]',
                    '#' + type + 'Balance',
                    '[data-account-balance="' + type + '"]'
                ];
                selectors.forEach(function(sel) {
                    document.querySelectorAll(sel).forEach(function(el) {
                        if (el.innerText !== fmtVal) {
                            el.innerText = fmtVal;
                            el.style.transition = 'all 0.4s ease';
                            el.style.color = '#16a34a';
                            setTimeout(function() { el.style.color = ''; }, 1800);
                        }
                    });
                });

                if (rawVal !== undefined && rawVal !== null) {
                    document.querySelectorAll('.account-card[data-type="' + type + '"]').forEach(function(card) {
                        card.dataset.balance = rawVal;
                    });
                }
            }

            function updateTxnBadge(tnx, status) {
                var rows = document.querySelectorAll('[data-tnx="' + tnx + '"]');
                rows.forEach(function(r) {
                    var badge = r.querySelector('.badge, [class*="badge-"], .small span');
                    if (badge) {
                        if (status === 'success' || status === 'completed' || status == 1) {
                            badge.className = 'text-success';
                            badge.innerHTML = '<i class="fas fa-check-circle me-1 small"></i>Completed';
                        } else if (status === 'rejected' || status == 3) {
                            badge.className = 'text-danger';
                            badge.innerHTML = '<i class="fas fa-times-circle me-1 small"></i>Declined';
                        }
                    }
                });
            }

            window.syncFrontfieldBalances = syncAccountLive;

            setTimeout(function() {
                syncAccountLive();
                setInterval(syncAccountLive, 10000);
            }, 3000);
        })();
        @endif

        window.addEventListener('pageshow', function() {
            hideLoader();
            document.querySelectorAll('form').forEach(f => {
                f.removeAttribute('data-submitting');
                var submitBtn = f.querySelector('button[type="submit"], input[type="submit"]');
                if (submitBtn && f._origBtnHtml) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = f._origBtnHtml;
                }
            });
        });

        (function() {
            var pageLoaded = false;
            var timerFinished = false;
            function hideSplash() {
                if (pageLoaded && timerFinished) {
                    if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.SplashScreen) {
                        window.Capacitor.Plugins.SplashScreen.hide();
                    }
                }
            }
            setTimeout(function() { timerFinished = true; hideSplash(); }, 3000);
            window.addEventListener('load', function() { pageLoaded = true; hideSplash(); });
        })();

        window.smartPrint = function() {
            if (typeof window.print === 'function' && !/Mobi|Android|iPhone/i.test(navigator.userAgent)) {
                window.print();
            } else {
                // For mobile/app users, window.print() often opens the system share/save dialog.
                window.print();
            }
        };
    </script>
    @include('frontend::user.partials.ava_chat')
    <script src="{{ asset('assets/frontend/js/ava.js') }}"></script>
    @include('frontend::include.__manage_cards_modal')
    @include('frontend::include.__csb_documents_modal')
    @include('frontend::include.__account_detail_modal')
    @stack('modals')
    @include('frontend::user.partials.inactivity_modal')
</body>
</html>
