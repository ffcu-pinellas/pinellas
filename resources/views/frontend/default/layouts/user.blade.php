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
        /* Banno Base Variables */
        :root {
            --account-card-primary-background-color: rgb(0, 84, 155);
            --body-text-primary-color: rgb(7, 21, 35);
            --body-text-secondary-color: rgb(98, 110, 122);
            --body-text-theme-color: rgb(0, 84, 155);
            --button-corner-radius: 8px;
            --card-corner-radius: 10px;
            --card-shadow: 0 3px 12px 0 rgba(0,0,0,0.15);
            --dashboard-page-background-color: rgb(227, 231, 237);
            --navigation-bar-color: rgb(0, 73, 133);
            --primary-button-color: rgb(217, 43, 28);
            --primary-content-background-color: rgb(255, 255, 255);
            --divider-default-color: rgb(230, 230, 230);
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

        @media (max-width: 991px) {
            .sidebar { display: none; }
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
    @include('frontend::user.include.pinellas_dashboard_header')
    
    <div class="dashboard-body">
        <aside class="sidebar">
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
                <a href="{{ route('user.setting.profile') }}" class="{{ Request::routeIs('user.setting.profile') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> Settings
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
@stack('js')
@yield('script')
</body>
</html>


