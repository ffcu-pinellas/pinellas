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
    
    <style>
        /* Sidebar Fixes */
        .sidebar-nav-item {
            font-size: 0.9rem !important; /* Reduce from absurd size */
            padding: 10px 16px !important;
        }
        .sidebar-nav-item bannoweb-shared-icons {
            font-size: 1.1rem !important;
        }
        .fi-logo img {
            height: 40px !important; /* Reduce logo size if huge */
        }
        /* Mobile overflow fix for quick actions was already applied inline, but global override here if needed */
        .banno-quick-actions::-webkit-scrollbar {
            display: none;
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
    <!-- Mobile Header -->
    <header class="d-lg-none header-pinellas" style="position: fixed; top: 0; width: 100%; z-index: 1001; background: var(--navigation-bar-color); height: 64px; display: flex; align-items: center; padding: 0 16px;">
        <button class="btn btn-link text-white p-0 me-3" id="sidebarToggle">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" alt="Pinellas FCU" style="height: 32px;">
    </header>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay"></div>

    <!-- Banno Sidebar -->
    <aside class="sidebar" id="content">
        <div class="fi-logo" style="padding: 24px;">
             <a href="{{ route('home') }}" aria-label="Dashboard">
                <img role="presentation" height="60" alt="Pinellas FCU" src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" style="max-width: 100%; height: auto;">
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="{{ route('user.dashboard') }}" class="sidebar-nav-item {{ Request::routeIs('user.dashboard') ? 'active' : '' }}">
                <bannoweb-shared-icons><i class="fas fa-th-large"></i></bannoweb-shared-icons> 
                <div class="ms-2">Dashboard</div>
            </a>
            <a href="{{ route('user.messages') }}" class="sidebar-nav-item {{ Request::routeIs('user.messages') ? 'active' : '' }}">
                <bannoweb-shared-icons><i class="fas fa-envelope"></i></bannoweb-shared-icons>
                <div class="ms-2">Messages</div>
            </a>
            <a href="{{ route('user.accounts') }}" class="sidebar-nav-item {{ Request::routeIs('user.accounts') ? 'active' : '' }}">
                <bannoweb-shared-icons><i class="fas fa-university"></i></bannoweb-shared-icons>
                <div class="ms-2">Accounts</div>
            </a>
            <a href="{{ route('user.fund_transfer.index') }}" class="sidebar-nav-item {{ Request::routeIs('user.fund_transfer.index') ? 'active' : '' }}">
                <bannoweb-shared-icons><i class="fas fa-exchange-alt"></i></bannoweb-shared-icons>
                <div class="ms-2">Transfers</div>
            </a>
            <a href="{{ route('user.fund_transfer.index') }}" class="sidebar-nav-item">
                <bannoweb-shared-icons><i class="fas fa-users"></i></bannoweb-shared-icons>
                <div class="ms-2">Member Transfers</div>
            </a>
            <a href="{{ route('user.remote_deposit') }}" class="sidebar-nav-item {{ Request::routeIs('user.remote_deposit') ? 'active' : '' }}">
                <bannoweb-shared-icons><i class="fas fa-mobile-alt"></i></bannoweb-shared-icons>
                <div class="ms-2">Remote deposits</div>
            </a>
            <a href="{{ route('user.bill-pay.index') }}" class="sidebar-nav-item {{ Request::routeIs('user.bill-pay.index') ? 'active' : '' }}">
                <bannoweb-shared-icons><i class="fas fa-file-invoice-dollar"></i></bannoweb-shared-icons>
                <div class="ms-2">Bill pay</div>
            </a>
            <a href="{{ route('user.rewards.index') }}" class="sidebar-nav-item {{ Request::routeIs('user.rewards.index') ? 'active' : '' }}">
                <bannoweb-shared-icons><i class="fas fa-gift"></i></bannoweb-shared-icons>
                <div class="ms-2">Member Rewards</div>
            </a>
            <a href="{{ route('user.messages') }}" class="sidebar-nav-item">
                <bannoweb-shared-icons><i class="fas fa-headset"></i></bannoweb-shared-icons>
                <div class="ms-2">Support</div>
            </a>
        </nav>

        <div class="sidebar-footer p-0">
            <div class="dropup w-100 sidebar-user-menu">
                <div class="user-profile-banno toggle-button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer; padding: 16px 24px; display: flex; align-items: center; gap: 12px; border-top: 1px solid var(--divider-default-color);">
                    <div class="user-avatar-banno">
                         <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info-banno flex-grow-1">
                        <div class="username">{{ strtoupper($user->full_name) }}</div>
                    </div>
                    <i class="fas fa-chevron-up small text-muted"></i>
                </div>
                <ul class="dropdown-menu banno-profile-dropdown border-0 shadow-lg w-100 p-2 sidebar-user-menu-dropup" style="border-radius: 12px; margin-bottom: 8px;">
                    <li>
                        <button type="button" class="dropdown-item py-2 d-flex align-items-center">
                            <i class="fas fa-plus-circle me-3 text-primary icon"></i> 
                            <div>Add an account</div>
                        </button>
                    </li>
                    <li>
                        <a href="{{ route('user.setting.show') }}" class="dropdown-item py-2 d-flex align-items-center">
                            <i class="fas fa-user-circle me-3 text-primary icon"></i> 
                            <div>Personal settings</div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('user.setting.show') }}" class="dropdown-item py-2 d-flex align-items-center">
                            <i class="fas fa-tools me-3 text-primary icon"></i> 
                            <div>Account settings</div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item py-2 text-dark d-flex align-items-center" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-3 text-muted icon"></i> 
                            <div>Sign out</div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content-area">
        <div class="container-fluid" style="height: 100%; display: flex; flex-direction: column; padding-bottom: 20px;">
            @if(false && auth()->user()->kyc !== \App\Enums\KYCStatus::Verified->value)
                @include('frontend::include.__kyc_warning')
            @endif
            @yield('content')
        </div>
    </main>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>

@include('frontend::include.__script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fix for Desktop Profile Dropdown (Dropup) in Sidebar
        const profileToggle = document.querySelector('.user-profile-banno.toggle-button');
        const profileMenu = document.querySelector('.sidebar-user-menu-dropup');
        
        if (profileToggle && profileMenu) {
            profileToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                profileMenu.classList.toggle('show');
            });
            
            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!profileToggle.contains(e.target) && !profileMenu.contains(e.target)) {
                    profileMenu.classList.remove('show');
                }
            });
        }

        // Sidebar Toggle Logic
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const toggleBtn = document.getElementById('sidebarToggle');
        
        function toggleSidebar() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        }

        if(toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });
        }

        if(overlay) {
            overlay.addEventListener('click', toggleSidebar);
        }

        // Close sidebar on link click (mobile)
        document.querySelectorAll('.sidebar-nav-item').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    toggleSidebar();
                }
            });
        });
        
        // Smart Header Logic
        let lastScrollTop = 0;
        const header = document.querySelector('.header-pinellas');
        
        if(header) {
            window.addEventListener('scroll', function() {
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > lastScrollTop && scrollTop > 64) {
                    // Scrolling Down
                    header.classList.add('header-hidden');
                } else {
                    // Scrolling Up
                    header.classList.remove('header-hidden');
                }
                lastScrollTop = scrollTop;
            });
        }
    });
</script>
@stack('js')
@yield('script')
</body>
</html>



