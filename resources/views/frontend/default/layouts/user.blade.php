<!doctype html>
@php
    $isRtl = isRtl(app()->getLocale());
    $user = auth()->user();
@endphp
<html lang="{{ app()->getLocale() }}" @if($isRtl) dir="rtl" @endif style="overflow-x: hidden;">
<head>
    @include('frontend::include.__head')
    <meta name="theme-color" content="#00549b">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Pinellas Custom Styling -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/pinellas-custom.css') }}">
    
    @stack('style')
    @yield('style')
    <script src="{{ asset('public/assets/frontend/js/security-gate.js') }}" defer></script>
</head>
<body @class([
    'dark-theme' => session()->get('site-color-mode',setting('default_mode')) == 'dark',
    'rtl_mode' => $isRtl
]) style="overflow-x: hidden;">

@include('global._notify')

<div class="dashboard-wrapper">
    <!-- Mobile Header -->
    <header class="d-lg-none header-pinellas" style="position: fixed; top: 0; width: 100%; z-index: 1001; background: var(--navigation-bar-color); height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 16px;">
        <div class="d-flex align-items-center">
            <button class="btn btn-link text-white p-0 me-3" id="sidebarToggle">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" alt="Pinellas FCU" style="height: 28px;">
        </div>
        <div class="dropdown">
            <button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; font-size: 14px; border: 2px solid rgba(255,255,255,0.5); background: rgba(255,255,255,0.2); color: white; font-weight: 600;">
                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2" style="border-radius: 12px; min-width: 200px; z-index: 1002;">
                <li><a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('user.setting.show') }}"><i class="far fa-user-circle me-3 text-muted"></i><span>Profile</span></a></li>
                <li><a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('user.setting.show') }}"><i class="fas fa-cog me-3 text-muted"></i><span>Settings</span></a></li>
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
                <img role="presentation" height="60" alt="Pinellas FCU" src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" style="max-width: 100%; height: auto;">
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
                <i class="fas fa-exchange-alt"></i> <span>Transfers</span>
            </a>
            <a href="{{ route('user.fund_transfer.member') }}" class="sidebar-nav-item">
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
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2" style="border-radius: 12px; min-width: 200px;">
                        <li><a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('user.setting.show') }}"><i class="far fa-user-circle me-3 text-muted"></i><span>Profile</span></a></li>
                        <li><a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('user.setting.show') }}"><i class="fas fa-cog me-3 text-muted"></i><span>Settings</span></a></li>
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

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

@include('frontend::include.__script')
@include('frontend::include.__security_gate_modal')

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
</body>
</html>
