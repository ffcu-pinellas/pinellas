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
    
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pinellas-custom.css') }}">
    
    <style>
        /* Sidebar Fixes */
        .sidebar {
            width: 260px !important;
        }
        .sidebar-nav-item {
            font-size: 0.85rem !important; /* 13-14px */
            padding: 12px 20px !important;
            font-weight: 400 !important;
        }
        .sidebar-nav-item i {
            font-size: 0.85rem !important;
            width: 24px;
            text-align: center;
        }
        .user-info-banno .username {
            font-size: 0.8rem !important;
            font-weight: 500 !important;
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
    <header class="d-lg-none header-pinellas" style="position: fixed; top: 0; width: 100%; z-index: 1001; background: var(--navigation-bar-color); height: 60px; display: flex; align-items: center; padding: 0 16px;">
        <button class="btn btn-link text-white p-0 me-3" id="sidebarToggle">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" alt="Pinellas FCU" style="height: 28px;">
    </header>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay"></div>

    <!-- Banno Sidebar -->
    <aside class="sidebar" id="content">
        <div class="fi-logo" style="padding: 20px 24px;">
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
                <i class="fas fa-exchange-alt"></i> Transfers
            </a>
            <a href="{{ route('user.fund_transfer.index') }}" class="sidebar-nav-item">
                <i class="fas fa-users"></i> Member Transfers
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
                <div class="user-profile-banno toggle-button" style="cursor: pointer; padding: 12px 20px; display: flex; align-items: center; gap: 12px; border-top: 1px solid var(--divider-default-color);">
                    <div class="user-avatar-banno" style="width: 32px; height: 32px; font-size: 14px;">
                         <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info-banno flex-grow-1">
                        <div class="username">{{ strtoupper($user->full_name) }}</div>
                    </div>
                    <i class="fas fa-chevron-up small text-muted"></i>
                </div>
                <ul class="dropdown-menu banno-profile-dropdown border-0 shadow-lg w-100 p-2 sidebar-user-menu-dropup" style="border-radius: 12px; margin-bottom: 8px;">
                    <li>
                        <a href="{{ route('user.dashboard') }}" class="dropdown-item py-2 d-flex align-items-center">
                            <i class="fas fa-columns me-3 text-primary icon"></i> 
                            <div>Organize dashboard</div>
                        </a>
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
        <!-- Global Desktop Header (Top Right Profile) -->
        <div class="d-none d-lg-flex justify-content-end align-items-center py-3 px-4 position-absolute top-0 end-0" style="z-index: 1000; width: 100%; pointer-events: none;">
             <div style="pointer-events: auto;">
                <div class="dropdown">
                    <button class="btn p-0 border-0 bg-transparent" type="button" id="topProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; font-size: 14px; border: 2px solid rgba(255,255,255,0.5); background: rgba(255,255,255,0.2); color: white; font-weight: 600;">
                            {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2" aria-labelledby="topProfileDropdown" style="border-radius: 12px; margin-top: 10px; min-width: 200px;">
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('user.setting.show') }}">
                                <i class="far fa-user-circle me-3 text-muted" style="width: 20px;"></i>
                                <span class="fw-medium">Profile</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('user.messages') }}">
                                <i class="far fa-question-circle me-3 text-muted" style="width: 20px;"></i>
                                <span class="fw-medium">Support</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('user.setting.show') }}">
                                <i class="fas fa-cog me-3 text-muted" style="width: 20px;"></i>
                                <span class="fw-medium">Settings</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-3" style="width: 20px;"></i>
                                <span class="fw-medium">Sign out</span>
                            </a>
                        </li>
                    </ul>
                </div>
             </div>
        </div>

        <div class="container-fluid py-4" style="min-height: 100%; display: flex; flex-direction: column;">
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
        // Fix for Sidebar Profile Dropdown (Dropup)
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



