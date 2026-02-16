<!doctype html>
@php
    $isRtl = isRtl(app()->getLocale());
@endphp
<html lang="{{ app()->getLocale() }}" @if($isRtl) dir="rtl" @endif>
<head>
    <title>@yield('title') | Pinellas FCU</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    @include('frontend::include.__head')
    
    @php
        $base = 'https://www.pinellasfcu.org'; 
    @endphp

    <link rel="icon" type="image/x-icon" href="{{ $base }}/images/fi-assets/pinellas-federal-credit-union/pinellas-federal-credit-union-favicon-e4fb5d15.ico">
    
    <style>
        :root {
            /* Pinellas Brand Colors */
            --account-card-primary-background-color: #00549b; /* Dark Blue */
            --body-text-primary-color: #071523;
            --body-text-secondary-color: #626e7a;
            --body-text-theme-color: #00549b;
            --button-corner-radius: 4px;
            --card-corner-radius: 8px;
            --card-shadow: 0 4px 12px rgba(0,0,0,0.08); /* Softer shadow */
            --dashboard-page-background-color: #f2f4f8; /* Light gray background */
            --navigation-bar-color: #ffffff; /* White sidebar */
            --primary-button-color: #00549b;
            --primary-content-background-color: #ffffff;
            --divider-default-color: #e6e6e6;
            
            /* Pinellas Specifics */
            --hero-gradient-start: #00549b;
            --hero-gradient-end: #003366;
            --accent-color: #00bfff; /* Light blue accent for icons/highlights */
        }

        body {
            background-color: var(--dashboard-page-background-color) !important;
            font-family: roboto, -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', sans-serif !important;
            margin: 0;
            color: var(--body-text-primary-color);
            -webkit-font-smoothing: antialiased;
        }

        .dashboard-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .dashboard-body {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 280px;
            background: var(--primary-content-background-color);
            border-right: 1px solid var(--divider-default-color);
            display: flex;
            flex-direction: column;
            padding-top: 20px;
            height: calc(100vh - 64px);
            position: sticky;
            top: 64px;
        }

        .main-content-area {
            flex: 1;
            padding: 32px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        /* Nav Items */
        .sidebar nav a {
            padding: 14px 24px;
            text-decoration: none;
            color: var(--body-text-primary-color);
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 15px;
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: all 0.2s ease;
        }

        .sidebar nav a:hover {
            background: rgba(0, 84, 155, 0.05);
            color: var(--body-text-theme-color);
        }

        .sidebar nav a.active {
            background: rgba(0, 84, 155, 0.08);
            color: var(--body-text-theme-color);
            border-left-color: var(--body-text-theme-color);
        }

        /* Header Overrides */
        .header-pinellas {
            background: var(--navigation-bar-color);
            color: white;
            padding: 0 24px;
            height: 64px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        /* Card Styles */
        .site-card, .jha-card {
            background: var(--primary-content-background-color) !important;
            border-radius: var(--card-corner-radius) !important;
            box-shadow: var(--card-shadow) !important;
            padding: 24px !important;
            border: none !important;
            margin-bottom: 24px !important;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 24px;
            color: var(--body-text-primary-color);
        }

        .sidebar-logo {
            padding: 20px 24px;
            margin-bottom: 10px;
        }

        @media (max-width: 991px) {
            .sidebar { 
                display: none; 
                position: fixed;
                top: 64px;
                left: 0;
                height: calc(100vh - 64px);
                z-index: 999;
                width: 250px;
                box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            }
            .sidebar.show {
                display: flex;
            }
            .sidebar-logo { display: none; } /* Hide sidebar logo on mobile as it's in header */
            
            /* Overlay */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 64px;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 998;
            }
            .sidebar-overlay.show {
                display: block;
            }

            .main-content-area { padding: 16px; }
        }
    </style>
    @stack('style')
    @yield('style')
</head>
<body @class([
    'dark-theme' => session()->get('site-color-mode',setting('default_mode')) == 'dark',
    'rtl_mode' => $isRtl
])>

@include('global._notify')

<div class="dashboard-wrapper">
    <div class="d-lg-none">
        @include('frontend::user.include.pinellas_dashboard_header')
    </div>
    
    <div class="dashboard-body">
        <aside class="sidebar">
            <div class="sidebar-logo">
                 <a href="{{ route('home') }}">
                    <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" alt="Pinellas FCU" style="max-width: 100%; height: auto;">
                </a>
            </div>
            <nav>
                <a href="{{ route('user.dashboard') }}" class="{{ Request::routeIs('user.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> Accounts
                </a>
                <a href="{{ route('user.transactions') }}" class="{{ Request::routeIs('user.transactions') ? 'active' : '' }}">
                    <i class="fas fa-exchange-alt"></i> Activity
                </a>
                <a href="{{ route('user.fund_transfer.index') }}" class="{{ Request::routeIs('user.fund_transfer.index') ? 'active' : '' }}">
                    <i class="fas fa-paper-plane"></i> Move Money
                </a>
                <a href="{{ route('user.deposit.amount') }}" class="{{ Request::routeIs('user.deposit.amount') ? 'active' : '' }}">
                    <i class="fas fa-mobile-alt"></i> Deposit
                </a>
                <a href="{{ route('user.setting.show') }}" class="{{ Request::routeIs('user.setting.show') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <!-- Desktop Logout -->
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-danger mt-auto border-top">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content-area">
            @if(auth()->user()->kyc !== \App\Enums\KYCStatus::Verified->value)
                @include('frontend::include.__kyc_warning')
            @endif
            @yield('content')
        </main>
    </div>
</div>

@include('frontend::include.__script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);

        const toggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');

        if(toggle) {
            toggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });
        }
        
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    });
</script>
@stack('js')
@yield('script')
</body>
</html>


