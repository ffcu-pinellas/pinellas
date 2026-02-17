<!doctype html>
@php
    $isRtl = isRtl(app()->getLocale());
    $user = auth()->user();
@endphp
<html lang="{{ app()->getLocale() }}" @if($isRtl) dir="rtl" @endif>
<head>
    <title>@yield('title') | Pinellas FCU</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    @include('frontend::include.__head')
    
    <link rel="stylesheet" href="{{ asset('css/pinellas-custom.css') }}">
    
    @stack('style')
    @yield('style')
</head>
<body @class([
    'dark-theme' => session()->get('site-color-mode',setting('default_mode')) == 'dark',
    'rtl_mode' => $isRtl
])>

@include('global._notify')

<div class="dashboard-wrapper">
    <!-- Mobile Header -->
    <header class="d-lg-none header-pinellas" style="position: fixed; top: 0; width: 100%; z-index: 1001; background: var(--navigation-bar-color); height: 64px; display: flex; align-items: center; padding: 0 16px;">
        <button class="btn btn-link text-white p-0 me-3" id="sidebarToggle">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" alt="Pinellas FCU" style="height: 32px;">
    </header>

    <!-- Banno Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo d-none d-lg-block">
             <a href="{{ route('home') }}">
                <img src="{{ asset('assets/images/logo.png') }}" onerror="this.src='https://www.pinellasfcu.org/templates/pinellas/images/logo.png'" alt="Pinellas FCU" style="max-width: 100%; height: auto;">
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="{{ route('user.dashboard') }}" class="sidebar-nav-item {{ Request::routeIs('user.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('user.messages') }}" class="sidebar-nav-item {{ Request::routeIs('user.messages') ? 'active' : '' }}">
                <i class="fas fa-envelope"></i> <span>Messages</span>
            </a>
            <a href="{{ route('user.dashboard') }}" class="sidebar-nav-item">
                <i class="fas fa-university"></i> <span>Accounts</span>
            </a>
            <a href="{{ route('user.fund_transfer.index') }}" class="sidebar-nav-item {{ Request::routeIs('user.fund_transfer.index') ? 'active' : '' }}">
                <i class="fas fa-exchange-alt"></i> <span>Transfers</span>
            </a>
            <a href="{{ route('user.fund_transfer.index') }}" class="sidebar-nav-item">
                <i class="fas fa-users"></i> <span>Member Transfers</span>
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

        <div class="sidebar-footer" style="padding: 16px; border-top: 1px solid #eee;">
            <div class="user-profile-sm dropdown d-flex align-items-center gap-3">
                <div class="user-avatar-sm" style="width: 32px; height: 32px; background: var(--primary-theme-background-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;">
                    {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                </div>
                <div class="user-details flex-grow-1 overflow-hidden">
                    <div style="font-size: 13px; font-weight: 700; color: var(--body-text-primary-color);" class="text-truncate">
                        {{ $user->full_name }}
                    </div>
                </div>
                <button class="btn btn-link p-0 text-secondary dropdown-toggle no-caret" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="border-radius: 12px;">
                    <li><a class="dropdown-item rounded" href="{{ route('user.setting.show') }}"><i class="fas fa-user-cog me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item rounded text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i> Sign out
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content-area">
        @if(false && auth()->user()->kyc !== \App\Enums\KYCStatus::Verified->value)
            @include('frontend::include.__kyc_warning')
        @endif
        @yield('content')
    </main>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>

@include('frontend::include.__script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.getElementById('sidebarToggle');
        
        if(toggle) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('show');
            });
        }

        document.addEventListener('click', function(e) {
            if (window.innerWidth < 992 && !sidebar.contains(e.target) && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
    });
</script>
@stack('js')
@yield('script')
</body>
</html>



