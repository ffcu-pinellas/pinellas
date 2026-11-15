<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<title>Dashboard | Cleveland State Bank Online Banking</title>

<link rel="apple-touch-icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}">
<link rel="icon" href="{{ asset('assets/institutions/clevelandstatebank/images/favicon.png') }}" type="image/x-icon">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    background-color: #f4f5f7;
    color: #262626;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ── Top FDIC Notice ── */
.csb-fdic-topbar {
    background-color: #ffffff;
    padding: 6px 20px;
    font-size: 11px;
    color: #1a2d54;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid #edf0f4;
}

.csb-fdic-badge {
    font-weight: 900;
    font-size: 12.5px;
    color: #0b2246;
    letter-spacing: 0.5px;
}

/* ── Red Header Bar (#c8232c) ── */
.csb-navbar-header {
    background-color: #c8232c;
    height: 50px;
    display: flex;
    align-items: center;
    padding: 0 16px;
    gap: 16px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
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
    height: 32px;
    width: auto;
    object-fit: contain;
}

/* ── Layout Shell ── */
.csb-layout-wrapper {
    display: flex;
    flex: 1;
    min-height: calc(100vh - 80px);
}

/* ── Left Sidebar (#262626) ── */
.csb-sidebar {
    width: 240px;
    background-color: #262626;
    color: #e0e0e0;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    transition: all 0.25s ease;
    position: sticky;
    top: 80px;
    height: calc(100vh - 80px);
    overflow: hidden;
    z-index: 99;
}

.csb-sidebar.collapsed {
    margin-left: -240px;
}

.csb-user-profile-widget {
    padding: 20px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.csb-user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #3b5078;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
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
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    background-color: #1e1e1e;
    margin-top: auto;
    flex-shrink: 0;
}

.csb-logout-btn {
    background: none;
    border: none;
    color: #ef5350;
    font-size: 12.5px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 0;
}

.csb-logout-btn:hover {
    color: #e53935;
    text-decoration: underline;
}

/* ── Main Content Area ── */
.csb-main-content {
    flex: 1;
    padding: 28px 36px 40px;
    background-color: #ffffff;
    overflow-y: auto;
}

.overview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.overview-title {
    font-size: 22px;
    font-weight: bold;
    color: #222222;
    letter-spacing: -0.2px;
}

.overview-actions {
    display: flex;
    gap: 16px;
    font-size: 13px;
}

.action-link {
    color: #475569;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
}

.action-link:hover {
    color: #1b325f;
    text-decoration: underline;
}

/* ── Balance Summary Row ── */
.balance-summary-row {
    display: flex;
    gap: 40px;
    margin-bottom: 24px;
}

.summary-block .summary-label {
    font-size: 12.5px;
    color: #64748b;
    margin-bottom: 4px;
}

.summary-block .summary-amount {
    font-size: 22px;
    font-weight: bold;
    color: #1e293b;
}

/* ── Account Cards / Tiles ── */
.account-tile-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 20px 24px;
    max-width: 340px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 24px;
}

.tile-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    font-size: 14px;
}

.tile-name {
    font-weight: bold;
    color: #1e293b;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.star-icon {
    color: #f59e0b;
    font-size: 13px;
}

.tile-account-no {
    color: #64748b;
    font-weight: 500;
    font-size: 13px;
}

.tile-balance {
    font-size: 24px;
    font-weight: bold;
    color: #1e293b;
}

/* ── Buttons Row ── */
.accounts-btn-row {
    display: flex;
    gap: 12px;
    margin-bottom: 36px;
}

.btn-view-all {
    background-color: #28623b;
    color: #ffffff;
    border: none;
    border-radius: 2px;
    padding: 9px 22px;
    font-size: 12.5px;
    font-weight: bold;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: background-color 0.15s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.btn-view-all:hover {
    background-color: #205030;
    color: #ffffff;
}

.btn-add-account {
    background-color: #ffffff;
    color: #334155;
    border: 1px solid #94a3b8;
    border-radius: 2px;
    padding: 9px 22px;
    font-size: 12.5px;
    font-weight: bold;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.15s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.btn-add-account:hover {
    background-color: #f8fafc;
    border-color: #64748b;
    color: #0f172a;
}

/* ── Two Column Bottom Cards ── */
.dashboard-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.csb-widget-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    overflow: hidden;
}

.widget-header {
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f1f5f9;
}

.widget-header h3 {
    font-size: 15px;
    font-weight: bold;
    color: #1e293b;
    margin: 0;
}

.widget-controls {
    display: flex;
    align-items: center;
    gap: 12px;
}

.widget-dropdown-select {
    border: 1px solid #cbd5e0;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 12px;
    font-weight: 500;
    color: #334155;
    outline: none;
    background: #fff;
}

.widget-dots-menu {
    color: #94a3b8;
    background: none;
    border: none;
    font-size: 16px;
    cursor: pointer;
    padding: 0 4px;
}

.widget-dots-menu:hover {
    color: #475569;
}

.widget-body {
    padding: 16px 20px;
}

/* Transactions list */
.transaction-list-table {
    width: 100%;
    border-collapse: collapse;
}

.txn-row {
    border-bottom: 1px solid #f1f5f9;
    padding: 12px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.txn-row:last-child {
    border-bottom: none;
}

.txn-desc {
    font-size: 12.5px;
    font-weight: 600;
    color: #1e293b;
    text-transform: uppercase;
    margin-bottom: 2px;
    max-width: 320px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.txn-date {
    font-size: 11px;
    color: #94a3b8;
}

.txn-amount {
    font-size: 13.5px;
    font-weight: bold;
    text-align: right;
}

.txn-amount.negative {
    color: #1e293b;
}

.txn-amount.positive {
    color: #16a34a;
}

/* Favorite Payees empty state */
.payees-empty-box {
    padding: 24px 12px;
    text-align: left;
}

.payees-empty-box h4 {
    font-size: 13.5px;
    font-weight: bold;
    color: #1e293b;
    margin-bottom: 8px;
}

.payees-empty-box p {
    font-size: 12px;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 16px;
}

.btn-add-payee {
    color: #1b325f;
    font-size: 12px;
    font-weight: bold;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-add-payee:hover {
    text-decoration: underline;
}

/* Responsive Layout */
@media (max-width: 991.98px) {
    .dashboard-grid-2 {
        grid-template-columns: 1fr;
    }
    .csb-main-content {
        padding: 16px 16px 36px !important;
    }
    .csb-sidebar {
        position: fixed !important;
        top: 0 !important;
        bottom: 0 !important;
        left: 0 !important;
        width: 280px !important;
        max-width: 85vw !important;
        height: 100vh !important;
        height: 100dvh !important;
        z-index: 1070 !important;
        transform: translateX(-100%) !important;
        margin-left: 0 !important;
        box-shadow: none;
        display: flex !important;
        flex-direction: column !important;
    }
    .csb-sidebar.show {
        transform: translateX(0) !important;
        box-shadow: 4px 0 30px rgba(0, 0, 0, 0.5) !important;
    }
    .csb-mobile-sidebar-header {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        background-color: #1a1a1a;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .csb-sidebar-backdrop {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.55);
        backdrop-filter: blur(2px);
        z-index: 1065;
    }
    .csb-sidebar-backdrop.show {
        display: block;
    }
}
@media (min-width: 992px) {
    .csb-mobile-sidebar-header {
        display: none !important;
    }
    .csb-sidebar-backdrop {
        display: none !important;
    }
}

@media (max-width: 768px) {
    .account-tiles-grid {
        display: flex !important;
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        gap: 12px !important;
        padding-bottom: 8px;
        margin-right: -16px;
        padding-right: 16px;
        scrollbar-width: none;
        -ms-overflow-style: none;
        scroll-snap-type: x mandatory;
    }
    .account-tiles-grid::-webkit-scrollbar {
        display: none;
    }
    .account-tiles-grid .account-tile-card {
        flex: 0 0 270px !important;
        max-width: 270px !important;
        min-width: 270px !important;
        scroll-snap-align: start;
        margin-bottom: 0 !important;
    }
    .txn-desc {
        max-width: calc(100vw - 190px) !important;
        font-size: 11.5px !important;
    }
    .widget-header {
        flex-wrap: wrap;
        gap: 8px;
    }
    .widget-dropdown-select {
        max-width: 170px;
    }
}

/* ── Account Tiles Grid ── */
.account-tiles-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 24px;
}
.account-tiles-grid .account-tile-card {
    flex: 1 1 280px;
    max-width: 360px;
    margin-bottom: 0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.account-tiles-grid .account-tile-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}

/* ── Mobile-Optimized Move Money Method Pills ── */
.csb-transfer-methods-nav {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    gap: 8px;
    padding-bottom: 6px;
    scrollbar-width: none;
}
.csb-transfer-methods-nav::-webkit-scrollbar {
    display: none;
}
.csb-transfer-methods-nav .nav-item {
    flex: 0 0 auto;
}
.csb-transfer-methods-nav .nav-link {
    font-size: 13px;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 20px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}
.csb-transfer-methods-nav .nav-link:hover {
    background: #e2e8f0;
    color: #1e293b;
}
.csb-transfer-methods-nav .nav-link.active {
    background: #c8232c !important;
    color: #ffffff !important;
    border-color: #c8232c !important;
    box-shadow: 0 4px 10px rgba(200, 35, 44, 0.25);
}
.csb-transfer-methods-nav .nav-link.csb-zelle-pill {
    color: #7414ca;
    border-color: rgba(116, 20, 202, 0.2);
}
.csb-transfer-methods-nav .nav-link.csb-zelle-pill.active {
    background: #7414ca !important;
    color: #ffffff !important;
    border-color: #7414ca !important;
    box-shadow: 0 4px 10px rgba(116, 20, 202, 0.25);
}

/* ── CSB Crimson 3D Card Flip Styles ── */
.csb-card-flip-wrap {
    perspective: 1000px;
    height: 220px;
    cursor: pointer;
    margin: 0 auto 16px auto;
    max-width: 360px;
    position: relative;
}
.csb-card-flip-inner {
    position: relative;
    width: 100%;
    height: 100%;
    text-align: left;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    transform-style: preserve-3d;
}
.csb-card-flip-wrap.flipped .csb-card-flip-inner {
    transform: rotateY(180deg);
}
.csb-card-face {
    position: absolute;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    border-radius: 16px;
    overflow: hidden;
    color: #fff;
    box-shadow: 0 14px 28px rgba(139, 0, 0, 0.35), 0 2px 6px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
}
.csb-card-front {
    background: linear-gradient(135deg, #c8232c 0%, #8b0000 50%, #4a0307 100%);
    padding: 20px 22px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transform: rotateY(0deg);
    position: relative;
}
.csb-card-front::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 85% 15%, rgba(255,255,255,0.22) 0%, transparent 60%),
                repeating-linear-gradient(45deg, rgba(255,255,255,0.03) 0px, rgba(255,255,255,0.03) 2px, transparent 2px, transparent 6px);
    pointer-events: none;
}
.csb-card-back {
    background: linear-gradient(135deg, #c8232c 0%, #8b0000 50%, #4a0307 100%);
    transform: rotateY(180deg);
    padding: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.csb-card-magnetic-strip {
    height: 42px;
    background: #111111;
    margin-top: 18px;
    width: 100%;
}
.csb-card-cvv-strip {
    background: #f8fafc;
    height: 32px;
    margin: 12px 20px 0 20px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 14px;
    color: #0f172a;
    font-family: monospace;
    font-weight: 800;
    font-size: 14px;
    letter-spacing: 3px;
    border-radius: 4px;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
}

/* ── CSB 2FA Security Gate Overrides ── */
#securityGateModal .modal-content {
    border-radius: 16px !important;
    overflow: hidden;
}
#securityGateModal .modal-header {
    background: #c8232c !important;
    color: #fff !important;
    padding: 16px 20px !important;
}
#securityGateModal .modal-header h5 {
    color: #fff !important;
    font-size: 15px !important;
    margin: 0 !important;
    padding: 0 !important;
}
#securityGateModal .btn-close {
    filter: brightness(0) invert(1) !important;
}
#securityGateModal .primary-btn {
    background: #c8232c !important;
    border: none !important;
    border-radius: 6px !important;
    font-weight: bold !important;
}
#securityGateModal .method-card:hover:not([disabled]) {
    border-color: #c8232c !important;
    border-left-color: #c8232c !important;
    background: #fdf2f2 !important;
}
#securityGateModal .method-icon {
    background: rgba(200, 35, 44, 0.08) !important;
    color: #c8232c !important;
}
#securityGateModal .key-btn.backspace {
    color: #c8232c !important;
}
#securityGateModal .pin-dot.filled {
    background-color: #c8232c !important;
    border-color: #c8232c !important;
}
#securityGateModal .forgot-link {
    color: #c8232c !important;
}
</style>
</head>
<body>

    <!-- Top FDIC Notice -->
    <div class="csb-fdic-topbar">
        <span class="csb-fdic-badge">FDIC</span>
        <span>FDIC-Insured - Backed by the full faith and credit of the U.S. Government</span>
    </div>

    <!-- Red Header Bar (#c8232c) -->
    <header class="csb-navbar-header">
        <button type="button" class="csb-toggle-sidebar-btn" id="sidebarToggleBtn" title="Toggle Sidebar">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <a href="{{ route('user.dashboard') }}" style="display: flex; align-items: center; text-decoration: none;">
            <img src="{{ asset('assets/institutions/clevelandstatebank/images/logo-white.png') }}" alt="Cleveland State Bank / Grenada State Bank" class="csb-header-logo-img">
        </a>
    </header>

    <!-- Main Shell -->
    <div class="csb-layout-wrapper">
        <!-- Backdrop for mobile sidebar drawer -->
        <div class="csb-sidebar-backdrop" id="csbSidebarBackdrop"></div>

        <!-- Left Charcoal Sidebar -->
        <aside class="csb-sidebar" id="csbSidebar">
            <!-- Mobile Drawer Header -->
            <div class="csb-mobile-sidebar-header">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('assets/institutions/clevelandstatebank/images/logo-white.png') }}" alt="Cleveland State Bank" style="height: 24px; max-width: 140px; object-fit: contain;">
                </div>
                <button type="button" class="btn-close btn-close-white" id="csbSidebarCloseBtn" aria-label="Close"></button>
            </div>

            <!-- Member Profile -->
            <div class="csb-user-profile-widget">
                <div class="csb-user-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="csb-user-info-text">
                    <div class="user-name">{{ $user->full_name }}</div>
                    <a href="{{ route('user.setting.show') }}" class="manage-link">
                        <i class="fa-solid fa-gear"></i> Manage Profile
                    </a>
                </div>
            </div>

            <!-- Navigation Links -->
            <ul class="csb-nav-menu">
                <li class="csb-nav-item">
                    <a href="{{ route('user.dashboard') }}" class="csb-nav-link active">
                        <i class="fa-solid fa-landmark" style="width: 16px;"></i> Dashboard
                    </a>
                </li>
                <li class="csb-nav-item">
                    <a href="{{ route('user.accounts') }}" class="csb-nav-link">
                        <i class="fa-solid fa-file-invoice" style="width: 16px;"></i> Accounts
                    </a>
                </li>

                <!-- Pay, Transfer, & Receive Accordion -->
                <li class="csb-nav-section-title" data-bs-toggle="collapse" data-bs-target="#menuPayTransfer">
                    <span>Pay, Transfer, & Receive</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 10px;"></i>
                </li>
                <div class="collapse show" id="menuPayTransfer">
                    <li class="csb-nav-item">
                        <a href="javascript:void(0)" class="csb-nav-link" data-bs-toggle="modal" data-bs-target="#moveMoneyModal">
                            <i class="fa-solid fa-money-bill-transfer" style="width: 16px;"></i> Move Money
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="javascript:void(0)" class="csb-nav-link" data-bs-toggle="modal" data-bs-target="#remoteDepositModal">
                            <i class="fa-solid fa-camera" style="width: 16px;"></i> Deposit Check
                        </a>
                    </li>
                </div>

                <!-- Documents & Settings Accordion -->
                <li class="csb-nav-section-title" data-bs-toggle="collapse" data-bs-target="#menuDocsSettings">
                    <span>Documents & Settings</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 10px;"></i>
                </li>
                <div class="collapse show" id="menuDocsSettings">
                    <li class="csb-nav-item">
                        <a href="{{ route('user.documents.index') }}" class="csb-nav-link">
                            <i class="fa-solid fa-file-lines" style="width: 16px;"></i> Documents
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="{{ route('user.transactions') }}" class="csb-nav-link">
                            <i class="fa-solid fa-chart-column" style="width: 16px;"></i> Reports & Statements
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="javascript:void(0)" class="csb-nav-link" data-bs-toggle="modal" data-bs-target="#manageCardsModal">
                            <i class="fa-solid fa-credit-card" style="width: 16px;"></i> Manage Cards
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="{{ route('user.notification.all') }}" class="csb-nav-link">
                            <i class="fa-solid fa-bell" style="width: 16px;"></i> Manage Alerts
                        </a>
                    </li>
                </div>

                <!-- Connect with Us Accordion -->
                <li class="csb-nav-section-title" data-bs-toggle="collapse" data-bs-target="#menuConnect">
                    <span>Connect with Us</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 10px;"></i>
                </li>
                <div class="collapse show" id="menuConnect">
                    <li class="csb-nav-item">
                        <a href="{{ route('user.ticket.index') }}" class="csb-nav-link">
                            <i class="fa-solid fa-envelope" style="width: 16px;"></i> Secure Messages
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="javascript:void(0)" class="csb-nav-link" onclick="if(document.getElementById('ava-trigger')) document.getElementById('ava-trigger').click();">
                            <i class="fa-solid fa-comments" style="width: 16px; color: #c8232c;"></i> Ava Assistant
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="javascript:void(0)" class="csb-nav-link" data-bs-toggle="modal" data-bs-target="#disputeModal">
                            <i class="fa-solid fa-triangle-exclamation" style="width: 16px; color: #f87171;"></i> Dispute Transaction
                        </a>
                    </li>
                </div>
            </ul>

            <!-- Logout -->
            <div class="csb-sidebar-footer">
                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                    @csrf
                    <button type="submit" class="csb-logout-btn">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="csb-main-content">
            <!-- Account Overview Header -->
            <div class="overview-header">
                <h1 class="overview-title">Account Overview</h1>
                <div class="overview-actions">
                    <a href="javascript:void(0)" class="action-link"><i class="fa-regular fa-star"></i> Manage Favorites</a>
                    <a href="javascript:void(0)" class="action-link"><i class="fa-solid fa-table-cells-large"></i> Manage Tiles</a>
                </div>
            </div>

            @php
                $checkingBal = (float)($user->balance ?? 0);
                $savingsBal = (float)($user->savings_balance ?? 0);
                $hasSavings = !empty($user->savings_account_number) || $user->savings_balance !== null;
                $iraBal = (float)($user->ira_balance ?? 0);
                $hasIra = (bool)($user->ira_status == 1);
                $helocBal = (float)($user->heloc_balance ?? 0);
                $hasHeloc = (bool)($user->heloc_status == 1);

                $hasCc = (bool)($user->cc_status == 1);
                $ccBal = (float)($user->cc_balance ?? 0);
                $ccLimit = (float)($user->cc_credit_limit ?? 0);
                $ccAvailable = max(0, $ccLimit - $ccBal);

                $hasLoan = (bool)($user->loan_account_status == 1 || $user->loan_status == 1 || (isset($user->loan_balance) && (float)$user->loan_balance > 0));
                $loanBal = (float)($user->loan_balance ?? 0);
                $loanOrig = (float)($user->loan_original_amount ?? 0);

                $depositAccountCount = 1 + ($hasSavings ? 1 : 0) + ($hasIra ? 1 : 0);
                $totalDepositAmount = $checkingBal + ($hasSavings ? $savingsBal : 0) + ($hasIra ? $iraBal : 0);

                $loanAccountCount = ($hasHeloc ? 1 : 0) + ($hasCc ? 1 : 0) + ($hasLoan ? 1 : 0) + ($totalRunningLoan ?? 0);
                $totalLoanSum = ($hasHeloc ? $helocBal : 0) + ($hasCc ? $ccBal : 0) + ($hasLoan ? $loanBal : 0) + (float)($total_loan_amount ?? 0);
            @endphp

            <!-- Balances Row -->
            <div class="balance-summary-row">
                <div class="summary-block">
                    <div class="summary-label">Total Deposits {{ $depositAccountCount }} {{ $depositAccountCount === 1 ? 'Account' : 'Accounts' }}</div>
                    <div class="summary-amount">{{ $currency }}{{ number_format($totalDepositAmount, 2) }}</div>
                </div>
                <div class="summary-block">
                    <div class="summary-label">Total Loans {{ $loanAccountCount }} {{ $loanAccountCount === 1 ? 'Account' : 'Accounts' }}</div>
                    <div class="summary-amount">{{ $currency }}{{ number_format($totalLoanSum, 2) }}</div>
                </div>
            </div>

            <!-- Account Tiles Grid (Checking + Savings + IRA + HELOC + Credit Card + Loan Account) -->
            <div class="account-tiles-grid">
                <!-- Checking Card Tile -->
                <div class="account-tile-card" role="button" onclick="window.showAccountDetails('Personal Checking', '{{ $user->account_number }}', {{ $checkingBal }}, 0, 'checking')">
                    <div class="tile-header">
                        <span class="tile-name">Checking <i class="fa-solid fa-star star-icon"></i></span>
                        <span class="tile-account-no">*{{ substr($user->account_number ?? '1397', -4) }}</span>
                    </div>
                    <div class="tile-balance">
                        {{ $currency }}{{ number_format($checkingBal, 2) }}
                    </div>
                </div>

                <!-- Primary Savings Card Tile -->
                @if($hasSavings)
                <div class="account-tile-card" role="button" onclick="window.showAccountDetails('Primary Savings', '{{ $user->savings_account_number ?? $user->account_number ?? '4902' }}', {{ $savingsBal }}, 0, 'savings')">
                    <div class="tile-header">
                        <span class="tile-name">Primary Savings <i class="fa-solid fa-star star-icon"></i></span>
                        <span class="tile-account-no">*{{ substr($user->savings_account_number ?? $user->account_number ?? '4902', -4) }}</span>
                    </div>
                    <div class="tile-balance">
                        {{ $currency }}{{ number_format($savingsBal, 2) }}
                    </div>
                </div>
                @endif

                <!-- IRA Card Tile -->
                @if($hasIra)
                <div class="account-tile-card" role="button" onclick="window.showAccountDetails('IRA Account', '{{ $user->ira_account_number ?? $user->account_number }}', {{ $iraBal }}, 0, 'ira')">
                    <div class="tile-header">
                        <span class="tile-name">IRA Account <i class="fa-solid fa-piggy-bank text-muted ms-1"></i></span>
                        <span class="tile-account-no">*{{ substr($user->ira_account_number ?? $user->account_number, -4) }}</span>
                    </div>
                    <div class="tile-balance">
                        {{ $currency }}{{ number_format($iraBal, 2) }}
                    </div>
                </div>
                @endif

                <!-- HELOC Card Tile -->
                @if($hasHeloc)
                <div class="account-tile-card" role="button" onclick="window.showAccountDetails('HELOC Account', '{{ $user->heloc_account_number ?? $user->account_number }}', {{ $helocBal }}, {{ (float)($user->heloc_credit_limit ?? 0) }}, 'heloc')">
                    <div class="tile-header">
                        <span class="tile-name">HELOC <i class="fa-solid fa-house-chimney text-muted ms-1"></i></span>
                        <span class="tile-account-no">*{{ substr($user->heloc_account_number ?? $user->account_number, -4) }}</span>
                    </div>
                    <div class="tile-balance">
                        {{ $currency }}{{ number_format($helocBal, 2) }}
                    </div>
                    @if((float)($user->heloc_credit_limit ?? 0) > 0)
                    <div class="small text-muted" style="font-size: 11px; margin-top: 4px;">
                        Avail Credit: {{ $currency }}{{ number_format(max(0, (float)$user->heloc_credit_limit - $helocBal), 2) }}
                    </div>
                    @endif
                </div>
                @endif

                <!-- Credit Card Tile -->
                @if($hasCc)
                <div class="account-tile-card" role="button" onclick="window.showAccountDetails('Credit Card', '{{ $user->cc_account_number ?? $user->account_number ?? '9538' }}', {{ $ccBal }}, {{ $ccLimit }}, 'cc')">
                    <div class="tile-header">
                        <span class="tile-name">Credit Card <i class="fa-solid fa-credit-card text-muted ms-1"></i></span>
                        <span class="tile-account-no">*{{ substr($user->cc_account_number ?? $user->account_number ?? '9538', -4) }}</span>
                    </div>
                    <div class="tile-balance">
                        {{ $currency }}{{ number_format($ccBal, 2) }}
                    </div>
                    @if($ccLimit > 0)
                    <div class="small text-muted" style="font-size: 11px; margin-top: 4px;">
                        Avail Credit: {{ $currency }}{{ number_format($ccAvailable, 2) }}
                    </div>
                    @endif
                </div>
                @endif

                <!-- Loan Account Tile -->
                @if($hasLoan)
                <div class="account-tile-card" role="button" onclick="window.showAccountDetails('Loan Account', '{{ $user->loan_account_number ?? $user->account_number ?? '8859' }}', {{ $loanBal }}, {{ $loanOrig }}, 'loan')">
                    <div class="tile-header">
                        <span class="tile-name">Loan Account <i class="fa-solid fa-file-contract text-muted ms-1"></i></span>
                        <span class="tile-account-no">*{{ substr($user->loan_account_number ?? $user->account_number ?? '8859', -4) }}</span>
                    </div>
                    <div class="tile-balance">
                        {{ $currency }}{{ number_format($loanBal, 2) }}
                    </div>
                    @if($loanOrig > 0)
                    <div class="small text-muted" style="font-size: 11px; margin-top: 4px;">
                        Original: {{ $currency }}{{ number_format($loanOrig, 2) }}
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Actions Row -->
            <div class="accounts-btn-row">
                <a href="{{ route('user.accounts') }}" class="btn-view-all">View All Accounts</a>
                <a href="javascript:void(0)" class="btn-add-account" data-bs-toggle="modal" data-bs-target="#moveMoneyModal">Move Money</a>
            </div>

            <!-- Two Column Lower Cards -->
            <div class="dashboard-grid-2">
                <!-- Left: Recent Transactions -->
                <div class="csb-widget-card">
                    <div class="widget-header">
                        <h3>Recent Transactions</h3>
                        <div class="widget-controls">
                            <select class="widget-dropdown-select" id="csbTxnAccountSelect">
                                <option value="checking" selected>Checking *{{ substr($user->account_number ?? '1397', -4) }}</option>
                                @if($hasSavings)
                                <option value="savings">Savings *{{ substr($user->savings_account_number ?? $user->account_number ?? '4902', -4) }}</option>
                                @endif
                                @if($hasIra)
                                <option value="ira">IRA *{{ substr($user->ira_account_number ?? $user->account_number, -4) }}</option>
                                @endif
                                @if($hasHeloc)
                                <option value="heloc">HELOC *{{ substr($user->heloc_account_number ?? $user->account_number, -4) }}</option>
                                @endif
                                @if($hasCc)
                                <option value="cc">Credit Card *{{ substr($user->cc_account_number ?? $user->account_number, -4) }}</option>
                                @endif
                                @if($hasLoan)
                                <option value="loan">Loan *{{ substr($user->loan_account_number ?? $user->account_number, -4) }}</option>
                                @endif
                            </select>
                            <button type="button" class="widget-dots-menu" title="Options">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                        </div>
                    </div>
                    <div class="widget-body">
                        @if(isset($recentTransactions) && count($recentTransactions) > 0)
                            <div class="transaction-list-table">
                                @foreach($recentTransactions as $tx)
                                @php
                                    $rawType = $tx->type instanceof \BackedEnum ? $tx->type->value : (string)($tx->type ?? '');
                                    $typeLower = strtolower($rawType);
                                    $isNegative = in_array($typeLower, ['withdraw', 'debit', 'fee', 'transfer_out', 'wire_out', 'zelle_out', 'payment']) || (isset($tx->amount) && (float)$tx->amount < 0);
                                    $wType = strtolower((string)($tx->wallet_type ?? 'checking'));
                                    if ($wType === 'default' || empty($wType)) $wType = 'checking';
                                    if (in_array($wType, ['primary_savings', 'savings_primary'])) $wType = 'savings';
                                    if (in_array($wType, ['credit_card'])) $wType = 'cc';
                                    if (in_array($wType, ['loan_account'])) $wType = 'loan';
                                    $rawDesc = $tx->description ?? (ucfirst(str_replace('_', ' ', $rawType)) ?: 'Transaction');
                                    $txAmtStr = ($isNegative ? '-' : '+') . $currency . number_format(abs((float)($tx->amount ?? 0)), 2);
                                    $txDateStr = $tx->created_at ? $tx->created_at->format('M d, Y h:i A') : date('M d, Y');
                                    $rawStatus = $tx->status instanceof \BackedEnum ? $tx->status->value : (string)($tx->status ?? 'completed');
                                    $txStatusStr = ucfirst($rawStatus);
                                    $txMethodStr = ucfirst(str_replace('_', ' ', $rawType));
                                    $txReceiptUrl = !empty($tx->tnx) ? route('user.transactions.receipt', $tx->tnx) : '#';
                                    $txDisputeUrl = !empty($tx->tnx) ? route('user.disputes.create', $tx->tnx) : '#';
                                @endphp
                                <div class="txn-row" data-account="{{ $wType }}" style="cursor: pointer;"
                                     data-tx-desc="{{ e($rawDesc) }}"
                                     data-tx-trx="{{ $tx->tnx ?? '' }}"
                                     data-tx-amount="{{ $txAmtStr }}"
                                     data-tx-date="{{ $txDateStr }}"
                                     data-tx-status="{{ $txStatusStr }}"
                                     data-tx-method="{{ $txMethodStr }}"
                                     data-tx-receipt="{{ $txReceiptUrl }}"
                                     data-tx-dispute="{{ $txDisputeUrl }}"
                                     onclick="csbShowTxnFromEl(this)"
                                     title="Click to view transaction details & dispute options">
                                    <div style="flex: 1 1 auto; min-width: 0; padding-right: 8px;">
                                        <div class="txn-desc">{{ $tx->description ?? (ucfirst(str_replace('_', ' ', $rawType)) ?: 'Transaction') }}</div>
                                        <div class="txn-date">{{ $tx->created_at ? $tx->created_at->format('m/d/Y') : date('m/d/Y') }}</div>
                                    </div>
                                    <div class="txn-amount {{ $isNegative ? 'negative' : 'positive' }}" style="flex-shrink: 0; white-space: nowrap;">
                                        @if($isNegative)
                                            -{{ $currency }}{{ number_format(abs((float)($tx->amount ?? 0)), 2) }}
                                        @else
                                            +{{ $currency }}{{ number_format(abs((float)($tx->amount ?? 0)), 2) }}
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div id="csbNoTxnForAccount" class="py-4 text-center text-muted d-none" style="font-size: 13px;">
                                <i class="fa-regular fa-folder-open fa-2x mb-2 d-block" style="opacity: 0.35;"></i>
                                No recent activity found for this account.
                            </div>
                        @else
                            <div class="py-4 text-center text-muted" style="font-size: 13px;">
                                <i class="fa-regular fa-folder-open fa-2x mb-2 d-block" style="opacity: 0.35;"></i>
                                No recent transactions found for this account.
                            </div>
                        @endif

                        <div style="text-align: right; margin-top: 14px;">
                            <a href="{{ route('user.transactions') }}" style="font-size: 12px; color: #c8232c; font-weight: bold; text-decoration: none;">View All Transactions &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Right: Favorite Payees -->
                <div class="csb-widget-card">
                    <div class="widget-header">
                        <h3>Favorite Payees</h3>
                        <div class="widget-controls">
                            <button type="button" class="widget-dots-menu" title="Options">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                        </div>
                    </div>
                    <div class="widget-body">
                        <div class="payees-empty-box">
                            <h4>No Payees Available</h4>
                            <p>You have no favorite Payees. Select the star icon on a payee to add them to your favorites list.</p>
                            <a href="javascript:void(0)" class="btn-add-payee" data-bs-toggle="modal" data-bs-target="#moveMoneyModal">
                                <i class="fa-solid fa-circle-plus"></i> Add Payee / Send Money
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>

    </div>

    <!-- ════════════════ MODALS & BANKING HUBS ════════════════ -->

    {{-- ── 1. MOVE MONEY / MULTI-TRANSFER HUB ── --}}
    <div class="modal fade" id="moveMoneyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header" style="background-color: #c8232c; color: #fff;">
                    <h5 class="modal-title fw-bold" style="font-size: 15px; letter-spacing: 0.5px;">MOVE MONEY &amp; TRANSFERS</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="csbTransferHub">
                    <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                        <span class="small text-muted">Select transfer method to move funds securely:</span>
                        <span class="badge" style="background: rgba(200,35,44,0.1); color: #c8232c; font-weight: 600;">FDIC Insured Transfers</span>
                    </div>

                    <ul class="nav nav-pills csb-transfer-methods-nav mb-3" id="transferTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-self">
                                <i class="fa-solid fa-arrow-right-arrow-left"></i> Self / Internal
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-member">
                                <i class="fa-solid fa-users"></i> Member Transfer
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-external">
                                <i class="fa-solid fa-building-columns"></i> External ACH
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-wire">
                                <i class="fa-solid fa-paper-plane"></i> Wire
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link csb-zelle-pill" data-bs-toggle="pill" data-bs-target="#tab-zelle">
                                <img src="{{ asset('assets/external/images/zelle small logo.png') }}" alt="" style="height: 14px; width: 14px; vertical-align: -1px; margin-right: 4px;"> Zelle®
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="transferTabContent">

                        {{-- 1. Self / Internal Transfer (Direct - No 2FA required) --}}
                        <div class="tab-pane fade show active" id="tab-self">
                            <form id="csbFormSelf" class="csb-transfer-form">
                                @csrf
                                <input type="hidden" name="transfer_type" value="self">
                                <input type="hidden" name="charge_type" value="percentage">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">From Account</label>
                                        <select name="wallet_type" id="csbSelfFromWallet" class="form-select shadow-none" onchange="csbSyncSelfToOptions()">
                                            <option value="default" data-balance="{{ $user->balance }}" selected>
                                                Checking (*{{ substr($user->account_number ?? '1397', -4) }}) — ${{ number_format((float)($user->balance ?? 0), 2) }}
                                            </option>
                                            <option value="primary_savings" data-balance="{{ $user->savings_balance ?? 0 }}">
                                                Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}S) — ${{ number_format((float)($user->savings_balance ?? 0), 2) }}
                                            </option>
                                            @if($user->ira_status == 1)
                                            <option value="ira" data-balance="{{ $user->ira_balance ?? 0 }}">
                                                IRA (*{{ substr($user->ira_account_number ?? $user->account_number, -4) }}I) — ${{ number_format((float)($user->ira_balance ?? 0), 2) }}
                                            </option>
                                            @endif
                                            @if($user->heloc_status == 1)
                                            <option value="heloc" data-balance="{{ ($user->heloc_credit_limit ?? 0) - ($user->heloc_balance ?? 0) }}">
                                                HELOC (*{{ substr($user->heloc_account_number ?? $user->account_number, -4) }}H) — Available: ${{ number_format(($user->heloc_credit_limit ?? 0) - ($user->heloc_balance ?? 0), 2) }}
                                            </option>
                                            @endif
                                            @if($user->cc_status == 1)
                                            <option value="cc" data-balance="{{ ($user->cc_credit_limit ?? 0) - ($user->cc_balance ?? 0) }}">
                                                Credit Card (*{{ substr($user->cc_account_number ?? $user->account_number, -4) }}C) — Available: ${{ number_format(($user->cc_credit_limit ?? 0) - ($user->cc_balance ?? 0), 2) }}
                                            </option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">To Account</label>
                                        <select name="to_wallet" id="csbSelfToWallet" class="form-select shadow-none" required>
                                            <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}S)</option>
                                            <option value="default">Checking (*{{ substr($user->account_number ?? '1397', -4) }})</option>
                                            @if($user->ira_status == 1)
                                            <option value="ira">IRA (*{{ substr($user->ira_account_number ?? $user->account_number, -4) }}I)</option>
                                            @endif
                                            @if($user->heloc_status == 1)
                                            <option value="heloc">HELOC (Pay Down)</option>
                                            @endif
                                            @if($user->cc_status == 1)
                                            <option value="cc">Credit Card (Pay Down)</option>
                                            @endif
                                            @if($user->loan_account_status == 1)
                                            <option value="loan">Loan (Pay Down)</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Amount ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 fw-bold">$</span>
                                            <input type="number" step="0.01" min="0.01" name="amount" id="csbSelfAmount" class="form-control border-start-0 fw-bold shadow-none" placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Frequency</label>
                                        <select name="frequency" class="form-select shadow-none">
                                            <option value="once" selected>One-time Transfer</option>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Memo / Purpose (Optional)</label>
                                        <input type="text" name="purpose" class="form-control shadow-none" placeholder="e.g. Savings allocation, budget buffer">
                                    </div>
                                </div>
                                <div id="resSelf" class="csb-tx-result d-none mt-3"></div>
                                <div class="mt-4">
                                    <button type="submit" class="btn w-100 fw-bold text-white py-2" style="background:#1b325f; border-radius:6px;">
                                        REVIEW TRANSFER &rarr;
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- 2. Member Transfer (Protected by 2FA) --}}
                        <div class="tab-pane fade" id="tab-member">
                            <form id="csbFormMember" class="csb-transfer-form">
                                @csrf
                                <input type="hidden" name="transfer_type" value="member">
                                <input type="hidden" name="bank_id" value="0">
                                <input type="hidden" name="charge_type" value="percentage">
                                <input type="hidden" name="manual_data[account_name]" id="csbMemberAccName">
                                <input type="hidden" id="csbRecipientChkLast4" value="">
                                <input type="hidden" id="csbRecipientSavLast4" value="">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">From Account</label>
                                        <select name="wallet_type" class="form-select shadow-none" required>
                                            <option value="default">Checking (*{{ substr($user->account_number ?? '1397', -4) }}) — ${{ number_format((float)($user->balance ?? 0), 2) }}</option>
                                            <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}S) — ${{ number_format((float)($user->savings_balance ?? 0), 2) }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Credit To Account Type</label>
                                        <select name="target_account_type" id="csbMemberTargetType" class="form-select shadow-none">
                                            <option value="checking" selected>Recipient Checking</option>
                                            <option value="savings" id="csbOptSavings" hidden disabled>Recipient Savings</option>
                                            <option value="ira" id="csbOptIra" hidden disabled>Recipient IRA</option>
                                            <option value="heloc" id="csbOptHeloc" hidden disabled>Recipient HELOC</option>
                                            <option value="cc" id="csbOptCc" hidden disabled>Recipient Credit Card</option>
                                            <option value="loan" id="csbOptLoan" hidden disabled>Recipient Loan</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Recipient Account #, Username or Email</label>
                                        <div class="input-group">
                                            <input type="text" name="member_identifier" id="csbMemberIdentifier" class="form-control shadow-none" placeholder="Enter recipient's registered account number, email or username" required autocomplete="off" oninput="csbLookupMember(this.value)">
                                            <span class="input-group-text bg-light" id="csbMemberSpinner" style="display:none;"><i class="fa-solid fa-spinner fa-spin text-muted"></i></span>
                                        </div>
                                        {{-- Verified Recipient Display --}}
                                        <div id="csbMemberVerifySuccess" class="d-none mt-2 p-2 px-3 rounded-2" style="background:#f0fdf4; border:1px solid #86efac;">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-circle-check text-success fs-5"></i>
                                                <div>
                                                    <strong class="text-dark" id="csbMemberVerifiedName"></strong>
                                                    <span class="badge bg-success ms-2">Verified Member</span>
                                                    <div class="extra-small text-muted" id="csbMemberVerifiedMeta"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="csbMemberVerifyError" class="d-none mt-2 p-2 px-3 rounded-2 text-danger small" style="background:#fef2f2; border:1px solid #fca5a5;">
                                            <i class="fa-solid fa-circle-exclamation me-1"></i> No member was found with that information. Please verify and try again.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Amount ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 fw-bold">$</span>
                                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control border-start-0 fw-bold shadow-none" placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Frequency</label>
                                        <select name="frequency" class="form-select shadow-none">
                                            <option value="once" selected>One-time Transfer</option>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Memo / Note</label>
                                        <input type="text" name="purpose" class="form-control shadow-none" placeholder="e.g. Shared expense, monthly split">
                                    </div>
                                </div>
                                <div id="resMember" class="csb-tx-result d-none mt-3"></div>
                                <div class="mt-4">
                                    <button type="submit" class="btn w-100 fw-bold text-white py-2" style="background:#28623b; border-radius:6px;">
                                        REVIEW &amp; SEND TO MEMBER &rarr;
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- 3. External ACH (Protected by 2FA) --}}
                        <div class="tab-pane fade" id="tab-external">
                            <form id="csbFormExternal" class="csb-transfer-form">
                                @csrf
                                <input type="hidden" name="transfer_type" value="external">
                                <input type="hidden" name="charge_type" value="percentage">
                                <input type="hidden" name="bank_id" id="csbResolvedBankId">
                                <input type="hidden" name="manual_data[bank_name]" id="csbResolvedBankName">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">From Account</label>
                                        <select name="wallet_type" class="form-select shadow-none" required>
                                            <option value="default">Checking (*{{ substr($user->account_number ?? '1397', -4) }}) — ${{ number_format((float)($user->balance ?? 0), 2) }}</option>
                                            <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}S) — ${{ number_format((float)($user->savings_balance ?? 0), 2) }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Recipient Full Legal Name</label>
                                        <input type="text" name="manual_data[account_name]" id="csbExtAccName" class="form-control shadow-none" placeholder="Account holder legal name" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">9-Digit ABA Routing Number</label>
                                        <div class="input-group">
                                            <input type="text" inputmode="numeric" name="manual_data[routing_number]" id="csbExtRouting" class="form-control shadow-none" placeholder="9-digit ABA routing number" maxlength="9" pattern="[0-9]{9}" required oninput="csbLookupRouting(this.value)">
                                            <span class="input-group-text bg-light" id="csbRoutingSpinner" style="display:none;"><i class="fa-solid fa-spinner fa-spin text-muted"></i></span>
                                        </div>
                                        <div class="small mt-1" id="csbRoutingStatus"></div>
                                        {{-- Verified Bank Card --}}
                                        <div id="csbRoutingVerifiedCard" class="d-none mt-2 p-3 rounded-2" style="background:#f0fdf4; border:1px solid #86efac;">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-building-columns text-success fs-5"></i>
                                                <div>
                                                    <div class="fw-bold text-dark" id="csbLookupBankName"></div>
                                                    <div class="extra-small text-muted" id="csbLookupBankLoc">Receiving Financial Institution (Verified)</div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Manual Bank Name Fallback --}}
                                        <div id="csbManualBankWrap" class="d-none mt-2">
                                            <label class="form-label extra-small fw-bold text-muted text-uppercase">Receiving Bank Name (As on statement)</label>
                                            <input type="text" name="manual_data[bank_name_manual]" id="csbManualBankInput" class="form-control form-control-sm shadow-none" placeholder="e.g. Wells Fargo, Chase Bank">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Account Number</label>
                                        <div class="input-group">
                                            <input type="password" name="manual_data[account_number]" id="csbExtAccNum" class="form-control shadow-none" placeholder="Recipient account number" minlength="4" maxlength="20" required oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                            <button type="button" class="btn btn-outline-secondary" onclick="csbTogglePwd('csbExtAccNum', this)"><i class="fa-solid fa-eye-slash"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Confirm Account Number</label>
                                        <div class="input-group">
                                            <input type="password" id="csbExtAccNumConfirm" class="form-control shadow-none" placeholder="Re-enter to confirm" minlength="4" maxlength="20" required oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                            <button type="button" class="btn btn-outline-secondary" onclick="csbTogglePwd('csbExtAccNumConfirm', this)"><i class="fa-solid fa-eye-slash"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Amount ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 fw-bold">$</span>
                                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control border-start-0 fw-bold shadow-none" placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Frequency</label>
                                        <select name="frequency" class="form-select shadow-none">
                                            <option value="once" selected>One-time Transfer</option>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Memo / Purpose (Optional)</label>
                                        <input type="text" name="purpose" class="form-control shadow-none" placeholder="e.g. Invoice payment, rent deposit">
                                    </div>
                                </div>
                                <div id="resExternal" class="csb-tx-result d-none mt-3"></div>
                                <div class="mt-4">
                                    <button type="submit" class="btn w-100 fw-bold text-white py-2" style="background:#28623b; border-radius:6px;">
                                        REVIEW &amp; SEND ACH TRANSFER &rarr;
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- 4. Wire Transfer (Protected by 2FA) --}}
                        <div class="tab-pane fade" id="tab-wire">
                            <form id="csbFormWire" class="csb-wire-form">
                                @csrf
                                <input type="hidden" name="wire_type" id="csbWireType" value="domestic">

                                <div class="mb-3">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-sm btn-dark active" id="btnWireDom" onclick="csbSwitchWireType('domestic')">
                                            <i class="fa-solid fa-flag-usa me-1"></i> Domestic Fedwire
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-dark" id="btnWireIntl" onclick="csbSwitchWireType('international')">
                                            <i class="fa-solid fa-globe me-1"></i> International SWIFT
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Funding Account</label>
                                        <select name="wallet_type" class="form-select shadow-none" required>
                                            <option value="default">Checking (*{{ substr($user->account_number ?? '1397', -4) }}) — ${{ number_format((float)($user->balance ?? 0), 2) }}</option>
                                            <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}S) — ${{ number_format((float)($user->savings_balance ?? 0), 2) }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Wire Principal ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 fw-bold">$</span>
                                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control border-start-0 fw-bold shadow-none" placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Beneficiary Full / Business Legal Name</label>
                                        <input type="text" name="beneficiary_name" class="form-control shadow-none" placeholder="Beneficiary official legal name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Beneficiary Street Address</label>
                                        <input type="text" name="beneficiary_address" class="form-control shadow-none" placeholder="Street address">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">City, State &amp; ZIP</label>
                                        <div class="row g-1">
                                            <div class="col-5"><input type="text" name="beneficiary_city" class="form-control shadow-none" placeholder="City"></div>
                                            <div class="col-3"><input type="text" name="beneficiary_state" class="form-control shadow-none" placeholder="State"></div>
                                            <div class="col-4"><input type="text" name="beneficiary_zip" class="form-control shadow-none" placeholder="ZIP"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Account Number / IBAN</label>
                                        <input type="text" name="account_number" class="form-control shadow-none" placeholder="Account or IBAN" required>
                                    </div>
                                    <div class="col-md-6" id="csbWireDomWrap">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Wire ABA Routing #</label>
                                        <input type="text" name="routing_number" class="form-control shadow-none" placeholder="9-digit Fedwire routing" maxlength="9">
                                    </div>
                                    <div class="col-md-6 d-none" id="csbWireIntlWrap">
                                        <label class="form-label small fw-bold text-muted text-uppercase">SWIFT / BIC Code</label>
                                        <input type="text" name="swift_bic" class="form-control shadow-none" placeholder="8 or 11 character SWIFT code">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Receiving Bank Name</label>
                                        <input type="text" name="bank_name" class="form-control shadow-none" placeholder="Receiving financial institution">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Receiving Bank Address</label>
                                        <input type="text" name="bank_address" class="form-control shadow-none" placeholder="Bank branch city, state">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Wire Memo / Purpose / Escrow Ref</label>
                                        <input type="text" name="memo" class="form-control shadow-none" placeholder="e.g. Real estate closing, escrow reference #">
                                    </div>
                                </div>
                                <div id="resWire" class="csb-tx-result d-none mt-3"></div>
                                <div class="mt-4">
                                    <button type="submit" class="btn w-100 fw-bold text-white py-2" style="background:#c8232c; border-radius:6px;">
                                        REVIEW &amp; DISPATCH WIRE &rarr;
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- 5. Zelle® (Protected by 2FA) --}}
                        <div class="tab-pane fade" id="tab-zelle">
                            <form id="csbFormZelle" class="csb-zelle-form">
                                @csrf
                                <div class="p-3 mb-3 text-center rounded-3" style="background: linear-gradient(135deg, #1b325f 0%, #7414ca 100%); color:#fff;">
                                    <div class="d-flex align-items-center justify-content-center gap-3 mb-1">
                                        <img src="{{ asset('assets/institutions/clevelandstatebank/images/csb-stacked-logo.png') }}" 
                                             alt="Cleveland State Bank" 
                                             style="height:26px; filter:brightness(0) invert(1);"
                                             onerror="this.onerror=null; this.src='{{ asset('assets/institutions/clevelandstatebank/images/logo-white.png') }}';">
                                        <div style="width:1px; height:22px; background:rgba(255,255,255,0.4);"></div>
                                        <img src="{{ asset('assets/external/images/Zelle-logo-no-tagline-white.png') }}" 
                                             alt="Zelle®" 
                                             style="height:26px; width:auto; object-fit:contain;">
                                    </div>
                                    <div class="extra-small opacity-75">Fast, safe and easy way to send money within minutes.</div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">From Account</label>
                                        <select name="wallet_type" class="form-select shadow-none" required>
                                            <option value="default">Checking (*{{ substr($user->account_number ?? '1397', -4) }}) — ${{ number_format((float)($user->balance ?? 0), 2) }}</option>
                                            <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}S) — ${{ number_format((float)($user->savings_balance ?? 0), 2) }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Amount ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 fw-bold" style="color:#7414ca;">$</span>
                                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control border-start-0 fw-bold shadow-none" placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Recipient U.S. Mobile or Email</label>
                                        <div class="input-group">
                                            <input type="text" name="contact" id="csbZelleContact" class="form-control shadow-none" placeholder="(555) 123-4567 or recipient@email.com" required autocomplete="off" oninput="csbFormatZellePhone(this); csbVerifyZelleContact(this.value)">
                                            <span class="input-group-text bg-light" id="csbZelleSpinner" style="display:none;"><i class="fa-solid fa-spinner fa-spin text-muted"></i></span>
                                        </div>
                                        {{-- Zelle Enrolled Notice Box --}}
                                        <div id="csbZelleNoticeBox" class="d-none mt-2 p-2 px-3 rounded-2" style="background:#fdf4ff; border-left:4px solid #7414ca;">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-circle-check fs-5" style="color:#7414ca;"></i>
                                                <div>
                                                    <strong style="color:#581c87;" id="csbZelleNoticeTitle">Enrolled with Zelle®</strong>
                                                    <div class="extra-small text-muted" id="csbZelleNoticeSub"></div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- External Name if Not Enrolled --}}
                                        <div id="csbZelleExternalNameWrap" class="d-none mt-2">
                                            <label class="form-label extra-small fw-bold text-muted text-uppercase">Recipient First &amp; Last Name</label>
                                            <input type="text" name="external_name" class="form-control form-control-sm shadow-none" placeholder="Legal full name of recipient">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">What's this for? (Optional)</label>
                                        <input type="text" name="purpose" class="form-control shadow-none" placeholder="e.g. Dinner split, utilities">
                                    </div>
                                </div>
                                <div id="resZelle" class="csb-tx-result d-none mt-3"></div>
                                <div class="mt-4">
                                    <button type="submit" class="btn w-100 fw-bold text-white py-2" style="background:#7414ca; border-radius:6px;">
                                        REVIEW &amp; SEND WITH ZELLE® &rarr;
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>{{-- end tab-content --}}
                </div>{{-- end csbTransferHub --}}
            </div>
        </div>
    </div>

    {{-- ── 2. REMOTE CHECK DEPOSIT ── --}}
    <div class="modal fade" id="remoteDepositModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header" style="background-color:#c8232c; color:#fff;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-camera"></i>
                        <h5 class="modal-title fw-bold mb-0" style="font-size:15px; letter-spacing: 0.5px;">REMOTE CHECK DEPOSIT</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="rdResult" class="d-none mb-3"></div>
                    <form id="csbRemoteDepositForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold text-uppercase mb-1">Deposit To Account</label>
                                <select name="account_id" class="form-select border shadow-none fw-bold" style="font-size: 13.5px;" required>
                                    <option value="checking">
                                        Personal Checking (*{{ substr($user->account_number ?? '1397', -4) }}) — ${{ number_format((float)($user->balance ?? 0), 2) }}
                                    </option>
                                    <option value="savings">
                                        Primary Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}) — ${{ number_format((float)($user->savings_balance ?? 0), 2) }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold text-uppercase mb-1">Check Amount ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 fw-bold fs-5 text-dark">$</span>
                                    <input type="number" step="0.01" min="0.01" name="amount" id="csbRdAmount" class="form-control border-start-0 fs-5 fw-bold" placeholder="0.00" required>
                                </div>
                            </div>
                        </div>

                        <!-- Check Photos Front & Back -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold text-uppercase mb-1">Front of Check</label>
                                <div class="csb-check-capture-card text-center p-3 rounded" style="border: 2px dashed #cfd8dc; background: #fbfbfb; cursor: pointer; min-height: 155px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; transition: all 0.2s;" onclick="$('#csbFrontFile').click()">
                                    <div id="csbFrontPreviewWrap" class="d-none position-absolute top-0 start-0 w-100 h-100 p-1 bg-white rounded">
                                        <img id="csbFrontPreview" src="" class="w-100 h-100 rounded" style="object-fit: contain;">
                                    </div>
                                    <div class="capture-inner">
                                        <i class="fa-solid fa-camera fa-2x mb-2" style="color: #c8232c;"></i>
                                        <div class="fw-bold text-dark small">Front of Check</div>
                                        <div class="text-muted" style="font-size: 11px;">Tap to capture or upload</div>
                                    </div>
                                    <input type="file" id="csbFrontFile" name="front_image" class="d-none" accept="image/*,.pdf" capture="environment" required onchange="csbPreviewImage(this, 'csbFrontPreview', 'csbFrontPreviewWrap')">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold text-uppercase mb-1">Back of Check (Endorsed)</label>
                                <div class="csb-check-capture-card text-center p-3 rounded" style="border: 2px dashed #cfd8dc; background: #fbfbfb; cursor: pointer; min-height: 155px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; transition: all 0.2s;" onclick="$('#csbBackFile').click()">
                                    <div id="csbBackPreviewWrap" class="d-none position-absolute top-0 start-0 w-100 h-100 p-1 bg-white rounded">
                                        <img id="csbBackPreview" src="" class="w-100 h-100 rounded" style="object-fit: contain;">
                                    </div>
                                    <div class="capture-inner">
                                        <i class="fa-solid fa-signature fa-2x mb-2" style="color: #c8232c;"></i>
                                        <div class="fw-bold text-dark small">Back of Check</div>
                                        <div class="text-muted" style="font-size: 11px;">Tap to capture or upload</div>
                                    </div>
                                    <input type="file" id="csbBackFile" name="back_image" class="d-none" accept="image/*,.pdf" capture="environment" required onchange="csbPreviewImage(this, 'csbBackPreview', 'csbBackPreviewWrap')">
                                </div>
                            </div>
                        </div>

                        <!-- Endorsement Guidance -->
                        <div class="alert alert-secondary border-0 small d-flex gap-2 p-3 mb-3" style="background: rgba(200, 35, 44, 0.05); border-radius: 8px;">
                            <i class="fa-solid fa-circle-info mt-1" style="color: #c8232c;"></i>
                            <div>
                                <div class="fw-bold text-dark mb-1">Endorsement Guidance</div>
                                <div class="text-muted" style="font-size: 12px;">
                                    Sign the back of your check and write <strong>"For Mobile Deposit Only — Cleveland State Bank"</strong> below your signature.
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="rdSubmitBtn" class="btn w-100 fw-bold text-white py-2" style="background:#28623b; font-size: 14px;">
                            <i class="fa-solid fa-paper-plane me-1"></i> SUBMIT CHECK FOR REVIEW
                        </button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="{{ route('user.remote_deposit') }}" class="small text-muted text-decoration-none">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> View Full Deposit History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. MANAGE CARDS (FrontField-Style 3D Card Flipping) ── --}}
    <div class="modal fade" id="manageCardsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header" style="background-color: #1e293b; color: #fff; border-bottom: 2px solid #c8232c;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-credit-card" style="color: #c8232c;"></i>
                        <h5 class="modal-title fw-bold mb-0" style="font-size: 15px; letter-spacing: 0.5px;">MANAGE DEBIT CARDS</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    @php
                        $userCardRecord = $userCard ?? null;
                        if (!$userCardRecord) {
                            try {
                                if (\Illuminate\Support\Facades\Schema::hasTable('user_cards')) {
                                    $userCardRecord = \App\Models\UserCard::where('user_id', $user->id)->first();
                                }
                            } catch (\Throwable $ce) {
                                $userCardRecord = null;
                            }
                        }
                    @endphp
                    @if($userCardRecord)
                        <!-- 3D Card Container -->
                        <div class="csb-card-flip-wrap" onclick="csbToggleCardFlip(this)" title="Click to flip card">
                            <div class="csb-card-flip-inner">
                                <!-- Front Face -->
                                <div class="csb-card-face csb-card-front">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ asset('assets/institutions/clevelandstatebank/images/logo-white.png') }}" alt="Cleveland State Bank" style="height: 24px; max-width: 140px; object-fit: contain;">
                                        </div>
                                        <span class="badge text-uppercase" style="background: rgba(255,255,255,0.15); font-size: 10px; letter-spacing: 1px;">
                                            {{ $userCardRecord->type ?? 'DEBIT' }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3 my-2">
                                        <i class="fa-solid fa-microchip fa-2x" style="color: #eab308;"></i>
                                        <i class="fa-solid fa-wifi fa-rotate-90 text-white-50"></i>
                                    </div>
                                    <div class="text-center my-1" style="overflow: hidden; width: 100%;">
                                        <span class="csb-card-num-masked font-monospace text-white" style="font-size: clamp(14px, 4.4vw, 19px); letter-spacing: clamp(1.5px, 0.5vw, 2.5px); white-space: nowrap !important; display: inline-block;">
                                            •••• •••• •••• {{ substr((string)($userCardRecord->card_number ?? '4000123456789010'), -4) }}
                                        </span>
                                        <span class="csb-card-num-full font-monospace text-white d-none" style="font-size: clamp(13px, 4.2vw, 18px); letter-spacing: clamp(1px, 0.4vw, 2px); white-space: nowrap !important; display: inline-block;">
                                            {{ trim(chunk_split((string)($userCardRecord->card_number ?? '4000123456789010'), 4, ' ')) }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-end mt-2">
                                        <div class="text-start">
                                            <div class="text-white-50" style="font-size: 9px; text-transform: uppercase;">Cardholder</div>
                                            <div class="fw-bold text-white small">{{ strtoupper($user->full_name ?: ($userCardRecord->card_holder_name ?? 'MEMBER')) }}</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-white-50" style="font-size: 9px; text-transform: uppercase;">Expires</div>
                                            <div class="fw-bold text-white small">{{ $userCardRecord->expiry_month ?? '12' }}/{{ substr((string)($userCardRecord->expiry_year ?? '2028'), -2) }}</div>
                                        </div>
                                        <div>
                                            <i class="fa-brands fa-cc-visa fa-2x text-white opacity-75"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Back Face -->
                                <div class="csb-card-face csb-card-back">
                                    <div class="csb-card-magnetic-strip"></div>
                                    <div class="csb-card-cvv-strip">
                                        <span id="csbCardCvvDisplay">{{ $userCardRecord->cvv ?? '719' }}</span>
                                    </div>
                                    <div class="p-3 text-start">
                                        <div class="text-white-50" style="font-size: 10px; line-height: 1.4;">
                                             AUTHORIZED SIGNATURE • NOT VALID UNLESS SIGNED
                                        </div>
                                        <div class="bg-white rounded my-1" style="height: 22px; opacity: 0.85;"></div>
                                        <div class="text-white-50 mt-2" style="font-size: 9.5px; line-height: 1.3;">
                                            For 24/7 customer service or lost cards: <strong>662-843-9461</strong>.<br>
                                            Cleveland State Bank — Member FDIC | Equal Housing Lender.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-muted small mb-3">
                            <i class="fa-solid fa-arrows-rotate me-1"></i> Tap card to flip
                        </div>

                        <!-- Card Controls -->
                        <div class="bg-light p-3 rounded text-start mb-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-block {{ ($userCardRecord->status ?? 'active') == 'active' ? 'bg-success' : 'bg-danger' }}" style="width: 10px; height: 10px;"></span>
                                    <span class="small fw-bold {{ ($userCardRecord->status ?? 'active') == 'active' ? 'text-success' : 'text-danger' }}">
                                        {{ ($userCardRecord->status ?? 'active') == 'active' ? 'Card Active' : 'Card Frozen' }}
                                    </span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="csbDetailsSwitch" onchange="csbToggleCardDetails(this)">
                                    <label class="form-check-label small" for="csbDetailsSwitch">Show details</label>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <button type="button" class="btn {{ ($userCardRecord->status ?? 'active') == 'active' ? 'btn-outline-danger' : 'btn-outline-success' }} w-100 fw-bold btn-sm py-2" onclick="csbToggleCardFreeze(this, {{ $userCardRecord->id ?? 0 }})">
                                        <i class="fa-solid {{ ($userCardRecord->status ?? 'active') == 'active' ? 'fa-snowflake' : 'fa-lock-open' }} me-1"></i>
                                        {{ ($userCardRecord->status ?? 'active') == 'active' ? 'FREEZE CARD' : 'UNFREEZE CARD' }}
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-secondary w-100 fw-bold btn-sm py-2" onclick="csbResetPin({{ $userCardRecord->id ?? 0 }})">
                                        <i class="fa-solid fa-key me-1"></i> RESET PIN
                                    </button>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('user.cards') }}" class="small text-muted text-decoration-none">
                            <i class="fa-solid fa-sliders me-1"></i> View Full Card Management Hub &rarr;
                        </a>
                    @else
                        <div class="py-4 text-muted">
                            <i class="fa-solid fa-credit-card fa-3x mb-3 text-secondary"></i>
                            <h5 class="fw-bold">No Card Issued</h5>
                            <p class="small">Contact your Cleveland State Bank branch officer to issue your Visa® Debit Card.</p>
                            <a href="{{ route('user.ticket.index') }}" class="btn btn-danger btn-sm fw-bold">Request Card</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2FA Security Gate Modal & Floating Corner Ava Assistant ── --}}
    @include('frontend::include.__security_gate_modal')
    @include('frontend::user.partials.ava_chat')

    {{-- ── 5. DISPUTE TRANSACTION ── --}}
    <div class="modal fade" id="disputeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" style="font-size:15px;">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>DISPUTE A TRANSACTION
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        File a Reg E dispute claim for unauthorized or erroneous transactions. Your case will be reviewed within 10 business days and a provisional credit may be issued.
                    </p>
                    <div id="disputeResult" class="d-none mb-3"></div>
                    <form id="csbDisputeForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Transaction Description / Reference</label>
                            <input type="text" name="transaction_ref" class="form-control" placeholder="e.g. Card purchase, wire, or reference #" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Dispute Category</label>
                            <select name="reason" class="form-select" required>
                                <option value="">— Select reason —</option>
                                <option value="Unauthorized Charge">Unauthorized / Fraudulent Charge</option>
                                <option value="Incorrect Amount Billed">Incorrect Amount Billed</option>
                                <option value="Merchandise Not Received">Merchandise / Service Not Received</option>
                                <option value="Duplicate Charge">Duplicate Transaction</option>
                                <option value="Transaction Not Recognized">Transaction Not Recognized</option>
                                <option value="ATM Dispute">ATM / Cash Dispute</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Additional Details</label>
                            <textarea name="description" rows="3" class="form-control" placeholder="Describe the issue in detail..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 fw-bold">
                            <i class="fa-solid fa-file-circle-exclamation me-1"></i> FILE OFFICIAL DISPUTE
                        </button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="{{ route('user.disputes.index') }}" class="small text-danger fw-bold">
                            View All My Dispute Cases →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 6. TRANSACTION DETAILS & DISPUTE MODAL ── --}}
    <div class="modal fade" id="trxViewDetailsBox" tabindex="-1" style="z-index: 2050;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 60px; height: 60px; font-size: 24px; background: rgba(200, 35, 44, 0.1); color: #c8232c;">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <h4 class="fw-bold mb-1 modal-title-val" id="csbTxnModalTitle">Transaction</h4>
                    <div class="text-muted small mb-4 modal-date-val" id="csbTxnModalDate">Date</div>
                    
                    <div class="display-5 fw-bold mb-4 modal-amount-val" id="csbTxnModalAmount">$0.00</div>
                    
                    <div class="p-3 text-start mb-0 rounded-3 bg-light border">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Status</span>
                            <span class="fw-bold small text-success" id="csbTxnModalStatus">Completed</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Transaction Ref</span>
                            <span class="fw-bold small font-monospace" id="csbTxnModalTrx">TRX12345</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Method</span>
                            <span class="fw-bold small" id="csbTxnModalMethod">System</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 gap-2 flex-column">
                    <a href="#" id="csbBtnDownloadReceipt" class="btn w-100 rounded-pill py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm text-white" style="background: #c8232c; border: none;">
                        <i class="fa-solid fa-file-pdf"></i> Download Official Receipt
                    </a>
                    <a href="#" id="csbBtnDisputeTxn" class="btn w-100 rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2" style="background: #ffffff; color: #1e293b; border: 1px solid #cbd5e1;">
                        <i class="fa-solid fa-flag text-danger"></i> Dispute this transaction (Reg E)
                    </a>
                    <button type="button" class="btn btn-link text-muted text-decoration-none w-100 py-1 fw-bold" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 6.5. TRANSFER REVIEW MODAL ── --}}
    <div class="modal fade" id="csbTransferReviewModal" tabindex="-1" aria-hidden="true" style="z-index: 2055;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
            <div class="modal-content border-0 shadow-lg text-center" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #c8232c 0%, #8b0000 100%);">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-clipboard-check fs-5"></i>
                        <h5 class="modal-title fw-bold mb-0" style="font-size: 15px; letter-spacing: 0.5px;">REVIEW TRANSFER DETAILS</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="csbReturnToMoveMoney()"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <span class="badge px-3 py-2 rounded-pill font-monospace" id="csbReviewTypeBadge" style="background: rgba(200, 35, 44, 0.1); color: #c8232c; font-size: 11.5px;">EXTERNAL ACH</span>
                        <div class="display-6 fw-bold text-dark mt-2" id="csbReviewAmount">$0.00</div>
                        <div class="extra-small text-muted" id="csbReviewFeeText">No Transfer Fee • Standard Settlement</div>
                    </div>

                    <div class="p-3 rounded-3 bg-light text-start border mb-4" style="font-size: 13.5px;">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">From Account</span>
                            <span class="fw-bold text-dark" id="csbReviewFromAcc">—</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Recipient / To</span>
                            <span class="fw-bold text-dark text-end" id="csbReviewToAcc">—</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom d-none" id="csbReviewBankRow">
                            <span class="text-muted">Receiving Bank</span>
                            <span class="fw-bold text-dark text-end" id="csbReviewBank">—</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom d-none" id="csbReviewRoutingRow">
                            <span class="text-muted">Routing / ABA</span>
                            <span class="font-monospace text-dark" id="csbReviewRouting">—</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom" id="csbReviewFrequencyRow">
                            <span class="text-muted">Frequency</span>
                            <span class="fw-semibold text-dark" id="csbReviewFrequency">One-time Transfer</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom d-none" id="csbReviewMemoRow">
                            <span class="text-muted">Memo / Purpose</span>
                            <span class="text-dark text-end" id="csbReviewMemo">—</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2">
                            <span class="text-muted">Estimated Delivery</span>
                            <span class="text-success fw-bold" id="csbReviewDelivery">Immediate (Within minutes)</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary w-50 py-2 fw-semibold rounded-pill" onclick="csbReturnToMoveMoney()">
                            <i class="fa-solid fa-arrow-left me-1"></i> Edit Details
                        </button>
                        <button type="button" class="btn text-white w-50 py-2 fw-bold rounded-pill shadow-sm" id="csbReviewConfirmBtn" style="background: #c8232c;">
                            Authorize &amp; Send <i class="fa-solid fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 7. TRANSFER SUCCESS CONFIRMATION MODAL ── --}}
    <div class="modal fade" id="csbTransferSuccessModal" tabindex="-1" aria-hidden="true" style="z-index: 2060;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
            <div class="modal-content border-0 shadow-lg text-center p-4" style="border-radius: 20px;">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 64px; height: 64px; background: rgba(34, 197, 94, 0.12); color: #16a34a; font-size: 28px;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Transfer Successful</h4>
                <p class="text-muted small mb-2" id="csbTransferConfirmMsg">Your transfer has been processed and submitted successfully.</p>
                
                <div class="display-6 fw-bold text-dark mb-3" id="csbTransferConfirmAmount">$0.00</div>

                <div class="p-3 rounded-3 bg-light text-start mb-3 border" style="font-size: 13px;">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Confirmation Ref</span>
                        <div class="d-flex align-items-center gap-1">
                            <span class="fw-bold font-monospace text-primary" id="csbTransferConfirmTrx">TRX...</span>
                            <button type="button" class="btn btn-sm btn-link p-0 text-muted" onclick="navigator.clipboard.writeText($('#csbTransferConfirmTrx').text()); alert('Confirmation reference copied!');" title="Copy Reference"><i class="fa-regular fa-copy"></i></button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Transfer Type</span>
                        <span class="fw-semibold text-dark" id="csbTransferConfirmType">External ACH</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">From Account</span>
                        <span class="fw-semibold text-dark" id="csbTransferConfirmFrom">Checking (*4864)</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Recipient / To</span>
                        <span class="fw-semibold text-dark text-end" id="csbTransferConfirmTo">—</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom d-none" id="csbConfirmBankRow">
                        <span class="text-muted">Receiving Institution</span>
                        <span class="fw-semibold text-dark text-end" id="csbTransferConfirmBank">—</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Date &amp; Time</span>
                        <span class="text-muted" id="csbTransferConfirmDate">Nov 15, 2026</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Transfer Fee</span>
                        <span class="text-success fw-semibold">$0.00 (Free)</span>
                    </div>
                    <div class="d-flex justify-content-between pt-1">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-success-subtle text-success fw-semibold">Completed</span>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary w-50 py-2 fw-semibold rounded-pill" onclick="window.print();">
                        <i class="fa-solid fa-print me-1"></i> Print Receipt
                    </button>
                    <button type="button" class="btn text-white w-50 py-2 fw-bold rounded-pill" style="background: #c8232c;" onclick="window.location.reload();">
                        Done &amp; Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/frontend/js/security-gate.js') }}?v=2.2"></script>
    <script src="{{ asset('assets/frontend/js/ava.js') }}"></script>

    <script>
    // ── Sidebar Toggle & Drawer ──
    document.addEventListener('DOMContentLoaded', function () {
        var sidebar = document.getElementById('csbSidebar');
        var toggleBtn = document.getElementById('sidebarToggleBtn');
        var closeBtn = document.getElementById('csbSidebarCloseBtn');
        var backdrop = document.getElementById('csbSidebarBackdrop');

        function openMobileSidebar() {
            if (sidebar) sidebar.classList.add('show');
            if (backdrop) backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function closeMobileSidebar() {
            if (sidebar) sidebar.classList.remove('show');
            if (backdrop) backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (window.innerWidth < 992) {
                    if (sidebar && sidebar.classList.contains('show')) {
                        closeMobileSidebar();
                    } else {
                        openMobileSidebar();
                    }
                } else {
                    if (sidebar) sidebar.classList.toggle('collapsed');
                }
            });
        }
        if (closeBtn) closeBtn.addEventListener('click', closeMobileSidebar);
        if (backdrop) backdrop.addEventListener('click', closeMobileSidebar);

        // Account filter for Recent Transactions widget
        var $accountSelect = $('#csbTxnAccountSelect');
        if ($accountSelect.length) {
            $accountSelect.on('change', function () {
                var selectedAcc = $(this).val();
                var visibleCount = 0;
                $('.transaction-list-table .txn-row').each(function () {
                    var rowAcc = $(this).data('account') || 'checking';
                    if (selectedAcc === 'all' || rowAcc === selectedAcc) {
                        $(this).show();
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });
                if (visibleCount === 0) {
                    $('#csbNoTxnForAccount').removeClass('d-none');
                } else {
                    $('#csbNoTxnForAccount').addClass('d-none');
                }
            });
        }

        // Clean up transfer results when modal closes
        var moveModal = document.getElementById('moveMoneyModal');
        if (moveModal) {
            moveModal.addEventListener('hidden.bs.modal', function () {
                document.querySelectorAll('.csb-tx-result').forEach(function(el){ el.classList.add('d-none'); el.innerHTML=''; });
            });
        }
    });

    // ── Transaction Details & Dispute Modal Popup ──
    function csbShowTxnFromEl(el) {
        var $el = $(el);
        csbShowTxnDetails(
            $el.attr('data-tx-desc') || 'Transaction',
            $el.attr('data-tx-trx') || 'N/A',
            $el.attr('data-tx-amount') || '$0.00',
            $el.attr('data-tx-date') || '',
            $el.attr('data-tx-status') || 'Completed',
            $el.attr('data-tx-method') || 'Transfer',
            $el.attr('data-tx-receipt') || '#',
            $el.attr('data-tx-dispute') || '#'
        );
    }

    function csbShowTxnDetails(desc, trx, amount, date, status, method, receiptUrl, disputeUrl) {
        $('#csbTxnModalTitle').text(desc);
        $('#csbTxnModalTrx').text(trx);
        $('#csbTxnModalAmount').text(amount);
        $('#csbTxnModalDate').text(date);
        $('#csbTxnModalStatus').text(status);
        $('#csbTxnModalMethod').text(method);
        $('#csbBtnDownloadReceipt').attr('href', receiptUrl);
        $('#csbBtnDisputeTxn').attr('href', disputeUrl);
        var modal = new bootstrap.Modal(document.getElementById('trxViewDetailsBox'));
        modal.show();
    }

    // ── 2FA Security Gate Integration ──
    let pendingTransferCallback = null;

    window.SecurityGateOnSuccess = function(res) {
        if (typeof pendingTransferCallback === 'function') {
            let cb = pendingTransferCallback;
            pendingTransferCallback = null;
            cb();
        }
    };

    function triggerSecurityGate(callback) {
        pendingTransferCallback = callback;
        if (typeof SecurityGate !== 'undefined' && typeof SecurityGate.gate === 'function') {
            SecurityGate.gate({});
        } else {
            callback();
        }
    }

    // ── Password Eye Toggle Helper ──
    function csbTogglePwd(inputId, btn) {
        var inp = document.getElementById(inputId);
        if (!inp) return;
        var icon = btn.querySelector('i');
        if (inp.type === 'password') {
            inp.type = 'text';
            if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
        } else {
            inp.type = 'password';
            if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
        }
    }

    // ── Federal Reserve Mod-10 Routing Checksum ──
    function isValidAbaChecksumAch(routing) {
        if (!/^\d{9}$/.test(routing)) return false;
        const d = routing.split('').map(Number);
        const sum = (3 * (d[0] + d[3] + d[6])) + (7 * (d[1] + d[4] + d[7])) + (d[2] + d[5] + d[8]);
        return sum % 10 === 0;
    }

    // ── Self Transfer Account Sync ──
    function csbSyncSelfToOptions() {
        var fromVal = $('#csbSelfFromWallet').val();
        var $to = $('#csbSelfToWallet');
        $to.find('option').prop('disabled', false).show();
        $to.find('option[value="' + fromVal + '"]').prop('disabled', true).hide();
        if ($to.val() === fromVal) {
            $to.find('option:not(:disabled):first').prop('selected', true);
        }
    }

    // ── Member Recipient Live Verification ──
    let csbMemberTimer = null;
    function csbLookupMember(val) {
        val = (val || '').trim();
        clearTimeout(csbMemberTimer);
        $('#csbMemberSpinner').hide();
        $('#csbMemberVerifySuccess, #csbMemberVerifyError').addClass('d-none');
        $('#csbMemberAccName, #csbRecipientChkLast4, #csbRecipientSavLast4').val('');

        if (val.length < 3) return;

        $('#csbMemberSpinner').show();
        csbMemberTimer = setTimeout(function() {
            fetch('/user/search-by-account-number/' + encodeURIComponent(val))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    $('#csbMemberSpinner').hide();
                    if (data && data.name) {
                        $('#csbMemberVerifiedName').text(data.name);
                        $('#csbMemberAccName').val(data.name);
                        $('#csbRecipientChkLast4').val(data.checking_last4 || '');
                        $('#csbRecipientSavLast4').val(data.savings_last4 || '');
                        var metaText = data.checking_last4 ? ('Checking (...*' + data.checking_last4 + ')') : '';
                        if (data.has_savings && data.savings_last4) {
                            metaText += ' • Savings (...*' + data.savings_last4 + ')';
                            $('#csbOptSavings').prop('disabled', false).prop('hidden', false);
                        } else {
                            $('#csbOptSavings').prop('disabled', true).prop('hidden', true);
                        }
                        if (data.has_ira) $('#csbOptIra').prop('disabled', false).prop('hidden', false);
                        if (data.has_heloc) $('#csbOptHeloc').prop('disabled', false).prop('hidden', false);
                        if (data.has_cc) $('#csbOptCc').prop('disabled', false).prop('hidden', false);
                        if (data.has_loan) $('#csbOptLoan').prop('disabled', false).prop('hidden', false);

                        $('#csbMemberVerifiedMeta').text(metaText);
                        $('#csbMemberVerifySuccess').removeClass('d-none');
                    } else {
                        $('#csbMemberVerifyError').removeClass('d-none');
                    }
                })
                .catch(function() {
                    $('#csbMemberSpinner').hide();
                    $('#csbMemberVerifyError').removeClass('d-none');
                });
        }, 350);
    }

    // ── External ACH Routing Lookup ──
    let csbRoutingTimer = null;
    function csbLookupRouting(val) {
        val = (val || '').trim();
        clearTimeout(csbRoutingTimer);
        $('#csbResolvedBankId, #csbResolvedBankName').val('');
        $('#csbRoutingVerifiedCard, #csbManualBankWrap').addClass('d-none');
        $('#csbRoutingStatus').html('');
        $('#csbRoutingSpinner').hide();

        if (val.length !== 9) return;

        if (!isValidAbaChecksumAch(val)) {
            $('#csbRoutingStatus').html('<span class="text-warning fw-semibold"><i class="fa-solid fa-triangle-exclamation me-1"></i> ABA routing mod-10 unlisted. Please enter the bank name manually below.</span>');
            $('#csbManualBankWrap').removeClass('d-none');
            return;
        }

        $('#csbRoutingSpinner').show();
        $('#csbRoutingStatus').html('<span class="text-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i> Verifying routing number with Federal Reserve directory...</span>');

        csbRoutingTimer = setTimeout(function() {
            fetch("{{ route('user.fund_transfer.lookup-routing') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ routing_number: val })
            })
            .then(function(r) { return r.json(); })
            .then(function(body) {
                $('#csbRoutingSpinner').hide();
                if (body && body.status === 'verified') {
                    $('#csbResolvedBankId').val(body.bank_id);
                    $('#csbResolvedBankName').val(body.bank_name);
                    $('#csbLookupBankName').text(body.bank_name);
                    $('#csbLookupBankLoc').text(body.location ? (body.location + ' — Verified Receiving Institution') : 'Verified Receiving Institution');
                    $('#csbRoutingVerifiedCard').removeClass('d-none');
                    $('#csbRoutingStatus').html('<span class="text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i> Institution recognized.</span>');
                    $('#csbManualBankWrap').addClass('d-none');
                } else {
                    $('#csbRoutingStatus').html('<span class="text-warning fw-semibold"><i class="fa-solid fa-circle-info me-1"></i> Routing lookup unlisted. Please enter institution name below.</span>');
                    $('#csbManualBankWrap').removeClass('d-none');
                }
            })
            .catch(function() {
                $('#csbRoutingSpinner').hide();
                $('#csbRoutingStatus').html('<span class="text-warning fw-semibold"><i class="fa-solid fa-circle-info me-1"></i> Verification service offline. Please enter bank name manually.</span>');
                $('#csbManualBankWrap').removeClass('d-none');
            });
        }, 300);
    }

    // ── Wire Transfer Domestic / International Toggle ──
    function csbSwitchWireType(type) {
        $('#csbWireType').val(type);
        if (type === 'international') {
            $('#btnWireDom').removeClass('btn-dark active').addClass('btn-outline-dark');
            $('#btnWireIntl').removeClass('btn-outline-dark').addClass('btn-dark active');
            $('#csbWireDomWrap').addClass('d-none');
            $('#csbWireIntlWrap').removeClass('d-none');
        } else {
            $('#btnWireIntl').removeClass('btn-dark active').addClass('btn-outline-dark');
            $('#btnWireDom').removeClass('btn-outline-dark').addClass('btn-dark active');
            $('#csbWireIntlWrap').addClass('d-none');
            $('#csbWireDomWrap').removeClass('d-none');
        }
    }

    // ── Zelle Phone Auto-Formatter: (123) 456-7890 ──
    function csbFormatZellePhone(input) {
        var v = input.value;
        if (/@/.test(v)) return; // Email input, don't format as phone
        var numbers = v.replace(/\D/g, '').substring(0, 10);
        var char = {0:'(', 3:') ', 6:'-'};
        var formatted = '';
        for (var i = 0; i < numbers.length; i++) {
            formatted += (char[i] || '') + numbers[i];
        }
        input.value = formatted;
    }

    // ── Zelle Live Recipient Enrollment Check ──
    let csbZelleTimer = null;
    function csbVerifyZelleContact(val) {
        val = (val || '').trim();
        clearTimeout(csbZelleTimer);
        $('#csbZelleSpinner').hide();
        $('#csbZelleNoticeBox, #csbZelleExternalNameWrap').addClass('d-none');

        var isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
        var isPhone = val.replace(/\D/g, '').length === 10;
        if (!isEmail && !isPhone) return;

        $('#csbZelleSpinner').show();
        csbZelleTimer = setTimeout(function() {
            fetch('/user/fund-transfer/zelle/verify-contact?contact=' + encodeURIComponent(val))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    $('#csbZelleSpinner').hide();
                    if (data && data.enrolled) {
                        $('#csbZelleNoticeTitle').text('Enrolled with Zelle®');
                        $('#csbZelleNoticeSub').text(data.name ? ('Recipient: ' + data.name + ' • Ready to receive instant payment') : 'Recipient is enrolled and will receive money directly.');
                        $('#csbZelleNoticeBox').removeClass('d-none');
                        $('#csbZelleExternalNameWrap').addClass('d-none');
                    } else {
                        $('#csbZelleExternalNameWrap').removeClass('d-none');
                    }
                })
                .catch(function() {
                    $('#csbZelleSpinner').hide();
                    $('#csbZelleExternalNameWrap').removeClass('d-none');
                });
        }, 400);
    }

    // ── Transfer Review Flow Management ──
    let pendingTransferExecution = null;

    function csbOpenTransferReview(details, onConfirm) {
        $('#csbReviewTypeBadge').text(details.badge || 'TRANSFER');
        if (details.badgeBg) {
            $('#csbReviewTypeBadge').css({ background: details.badgeBg, color: details.badgeColor || '#ffffff' });
        } else {
            $('#csbReviewTypeBadge').css({ background: 'rgba(200, 35, 44, 0.1)', color: '#c8232c' });
        }
        $('#csbReviewAmount').text(details.amount);
        $('#csbReviewFromAcc').text(details.from);
        $('#csbReviewToAcc').text(details.to);
        
        if (details.bank) {
            $('#csbReviewBank').text(details.bank);
            $('#csbReviewBankRow').removeClass('d-none');
        } else {
            $('#csbReviewBankRow').addClass('d-none');
        }

        if (details.routing) {
            $('#csbReviewRouting').text(details.routing);
            $('#csbReviewRoutingRow').removeClass('d-none');
        } else {
            $('#csbReviewRoutingRow').addClass('d-none');
        }

        $('#csbReviewFrequency').text(details.frequency || 'One-time Transfer');

        if (details.memo) {
            $('#csbReviewMemo').text(details.memo);
            $('#csbReviewMemoRow').removeClass('d-none');
        } else {
            $('#csbReviewMemoRow').addClass('d-none');
        }

        $('#csbReviewDelivery').text(details.delivery || 'Immediate (Within minutes)');
        $('#csbReviewFeeText').text(details.feeText || 'No Transfer Fee • Standard Settlement');

        pendingTransferExecution = {
            details: details,
            callback: onConfirm
        };

        var moveModalEl = document.getElementById('moveMoneyModal');
        if (moveModalEl) {
            var moveModal = bootstrap.Modal.getInstance(moveModalEl);
            if (moveModal) moveModal.hide();
        }
        var reviewModal = new bootstrap.Modal(document.getElementById('csbTransferReviewModal'));
        reviewModal.show();
    }

    function csbReturnToMoveMoney() {
        var reviewModalEl = document.getElementById('csbTransferReviewModal');
        if (reviewModalEl) {
            var revModal = bootstrap.Modal.getInstance(reviewModalEl);
            if (revModal) revModal.hide();
        }
        var moveModal = new bootstrap.Modal(document.getElementById('moveMoneyModal'));
        moveModal.show();
    }

    $('#csbReviewConfirmBtn').on('click', function() {
        if (!pendingTransferExecution) return;
        var exec = pendingTransferExecution;
        
        var reviewModalEl = document.getElementById('csbTransferReviewModal');
        if (reviewModalEl) {
            var revModal = bootstrap.Modal.getInstance(reviewModalEl);
            if (revModal) revModal.hide();
        }

        exec.callback(exec.details);
    });

    // ── Generic Transfer AJAX Dispatcher ──
    function executeTransfer($form, resId, url, details) {
        var $btn = $form.find('button[type=submit]');
        var origText = $btn.data('orig') || $btn.html();
        $btn.data('orig', origText);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Processing Transfer...');

        if (typeof window.showLoader === 'function') window.showLoader();

        $.ajax({
            url: url,
            type: 'POST',
            data: $form.serialize(),
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .done(function(res) {
            var tnx = res.trx || res.tnx || ('TRX' + Math.floor(100000 + Math.random() * 900000));
            var msg = res.message || 'Transfer submitted and processed successfully.';

            // Populate rich confirmation modal
            $('#csbTransferConfirmTrx').text(tnx);
            $('#csbTransferConfirmMsg').text(msg);
            
            if (details) {
                $('#csbTransferConfirmAmount').text(details.amount);
                $('#csbTransferConfirmType').text(details.badge || 'Transfer');
                $('#csbTransferConfirmFrom').text(details.from);
                $('#csbTransferConfirmTo').text(details.to);
                if (details.bank) {
                    $('#csbTransferConfirmBank').text(details.bank);
                    $('#csbConfirmBankRow').removeClass('d-none');
                } else {
                    $('#csbConfirmBankRow').addClass('d-none');
                }
            }

            var nowStr = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            $('#csbTransferConfirmDate').text(nowStr);

            var confirmModal = new bootstrap.Modal(document.getElementById('csbTransferSuccessModal'));
            confirmModal.show();

            $form[0].reset();
            $('#csbMemberVerifySuccess, #csbRoutingVerifiedCard, #csbZelleNoticeBox, #csbManualBankWrap').addClass('d-none');
        })
        .fail(function(xhr) {
            var moveModal = new bootstrap.Modal(document.getElementById('moveMoneyModal'));
            moveModal.show();

            var errors = xhr.responseJSON?.errors;
            var msg = errors ? Object.values(errors).flat().join('<br>') : (xhr.responseJSON?.message || 'Transfer could not be processed. Please verify your details and available balance.');
            csbTxError(resId, msg);
        })
        .always(function() {
            $btn.prop('disabled', false).html($btn.data('orig') || 'SUBMIT');
            if (typeof window.hideLoader === 'function') window.hideLoader();
        });
    }

    // 1. Self / Internal Transfer
    $('#csbFormSelf').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var amt = parseFloat($form.find('input[name="amount"]').val()) || 0;
        if (amt <= 0) {
            csbTxError('#resSelf', 'Please enter a valid transfer amount.');
            return;
        }
        var fromText = $('#csbSelfFromWallet option:selected').text().split('—')[0].trim();
        var toText = $('#csbSelfToWallet option:selected').text().trim();
        var memo = $form.find('input[name="purpose"]').val().trim();

        var details = {
            badge: 'SELF / INTERNAL TRANSFER',
            badgeBg: 'rgba(27, 50, 95, 0.1)',
            badgeColor: '#1b325f',
            amount: '$' + amt.toFixed(2),
            from: fromText,
            to: toText,
            delivery: 'Immediate (Within minutes)',
            feeText: 'No Transfer Fee • Internal Transfer',
            memo: memo
        };

        csbOpenTransferReview(details, function(d) {
            executeTransfer($form, '#resSelf', "{{ route('user.fund_transfer.transfer') }}", d);
        });
    });

    // 2. Member Transfer: Protected by 2FA Security Gate
    $('#csbFormMember').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var amt = parseFloat($form.find('input[name="amount"]').val()) || 0;
        if (amt <= 0) {
            csbTxError('#resMember', 'Please enter a valid transfer amount.');
            return;
        }
        var accName = $('#csbMemberAccName').val();
        if (!accName) {
            csbTxError('#resMember', 'Please enter a valid recipient and wait for verification.');
            return;
        }
        var fromText = $form.find('select[name="wallet_type"] option:selected').text().split('—')[0].trim();
        var verifiedName = $('#csbMemberVerifiedName').text().trim() || accName;
        var toMeta = $('#csbMemberVerifiedMeta').text().trim() || $('#csbMemberIdentifier').val();
        var memo = $form.find('input[name="purpose"]').val().trim();
        var freq = $form.find('select[name="frequency"] option:selected').text().trim();

        var details = {
            badge: 'MEMBER TRANSFER',
            badgeBg: 'rgba(40, 98, 59, 0.1)',
            badgeColor: '#28623b',
            amount: '$' + amt.toFixed(2),
            from: fromText,
            to: verifiedName + (toMeta ? ' (' + toMeta + ')' : ''),
            frequency: freq,
            delivery: 'Immediate (Within seconds)',
            feeText: 'No Transfer Fee • Instant Credit',
            memo: memo
        };

        csbOpenTransferReview(details, function(d) {
            triggerSecurityGate(function() {
                executeTransfer($form, '#resMember', "{{ route('user.fund_transfer.transfer') }}", d);
            });
        });
    });

    // 3. External ACH Transfer: Protected by 2FA Security Gate
    $('#csbFormExternal').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var amt = parseFloat($form.find('input[name="amount"]').val()) || 0;
        if (amt <= 0) {
            csbTxError('#resExternal', 'Please enter a valid transfer amount.');
            return;
        }
        var routing = $('#csbExtRouting').val().trim();
        if (routing.length !== 9) {
            csbTxError('#resExternal', 'Please enter a valid 9-digit ABA routing number.');
            return;
        }
        var acc1 = $('#csbExtAccNum').val();
        var acc2 = $('#csbExtAccNumConfirm').val();
        if (acc1 !== acc2) {
            csbTxError('#resExternal', 'Account numbers do not match. Please verify both fields.');
            return;
        }
        var resolvedBank = $('#csbResolvedBankName').val().trim();
        var manualBank = $('#csbManualBankInput').val().trim();
        var bankName = resolvedBank || manualBank;

        if (!bankName) {
            $('#csbManualBankWrap').removeClass('d-none');
            $('#csbManualBankInput').focus();
            csbTxError('#resExternal', 'Routing lookup unlisted. Please enter the receiving bank name below to proceed.');
            return;
        }

        if (!resolvedBank && manualBank) {
            $('#csbResolvedBankName').val(manualBank);
        }

        var recipientName = $('#csbExtAccName').val().trim();
        var fromText = $form.find('select[name="wallet_type"] option:selected').text().split('—')[0].trim();
        var memo = $form.find('input[name="purpose"]').val().trim();
        var freq = $form.find('select[name="frequency"] option:selected').text().trim();

        var details = {
            badge: 'EXTERNAL ACH TRANSFER',
            badgeBg: 'rgba(200, 35, 44, 0.1)',
            badgeColor: '#c8232c',
            amount: '$' + amt.toFixed(2),
            from: fromText,
            to: recipientName + ' (*' + acc1.slice(-4) + ')',
            bank: bankName,
            routing: routing,
            frequency: freq,
            delivery: '1–3 Business Days (Standard ACH)',
            feeText: 'No Transfer Fee • Standard ACH Settlement',
            memo: memo
        };

        csbOpenTransferReview(details, function(d) {
            triggerSecurityGate(function() {
                executeTransfer($form, '#resExternal', "{{ route('user.fund_transfer.transfer') }}", d);
            });
        });
    });

    // 4. Wire Transfer: Protected by 2FA Security Gate
    $('#csbFormWire').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var amt = parseFloat($form.find('input[name="amount"]').val()) || 0;
        if (amt <= 0) {
            csbTxError('#resWire', 'Please enter a valid wire amount.');
            return;
        }
        var wireType = $('#csbWireType').val();
        var beneName = $form.find('input[name="beneficiary_name"]').val().trim();
        var bankName = $form.find('input[name="bank_name"]').val().trim();
        var routing = (wireType === 'international') ? $form.find('input[name="swift_code"]').val().trim() : $form.find('input[name="routing_number"]').val().trim();
        var fromText = $form.find('select[name="wallet_type"] option:selected').text().split('—')[0].trim();
        var memo = $form.find('input[name="memo"]').val().trim();

        var details = {
            badge: wireType === 'international' ? 'INTERNATIONAL SWIFT WIRE' : 'DOMESTIC FEDWIRE',
            badgeBg: 'rgba(27, 50, 95, 0.15)',
            badgeColor: '#1b325f',
            amount: '$' + amt.toFixed(2),
            from: fromText,
            to: beneName,
            bank: bankName,
            routing: routing,
            delivery: 'Same-Day Fedwire / SWIFT Clearing',
            feeText: 'Wire Settlement • Reg E & Regulatory Review',
            memo: memo
        };

        csbOpenTransferReview(details, function(d) {
            triggerSecurityGate(function() {
                executeTransfer($form, '#resWire', "{{ route('user.fund_transfer.transfer.wire.post') }}", d);
            });
        });
    });

    // 5. Zelle® Transfer: Protected by 2FA Security Gate
    $('#csbFormZelle').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var amt = parseFloat($form.find('input[name="amount"]').val()) || 0;
        if (amt <= 0) {
            csbTxError('#resZelle', 'Please enter a valid transfer amount.');
            return;
        }
        var contact = $('#csbZelleContact').val().trim();
        if (!contact) {
            csbTxError('#resZelle', 'Please enter a valid recipient phone or email.');
            return;
        }
        var fromText = $form.find('select[name="wallet_type"] option:selected').text().split('—')[0].trim();
        var enrolledSub = $('#csbZelleNoticeSub').text().trim();
        var externalName = $form.find('input[name="external_name"]').val().trim();
        var recipientDisplay = contact;
        if (enrolledSub && enrolledSub.indexOf('Recipient:') !== -1) {
            var extracted = enrolledSub.replace('Recipient:', '').split('•')[0].trim();
            recipientDisplay = extracted + ' (' + contact + ')';
        } else if (externalName) {
            recipientDisplay = externalName + ' (' + contact + ')';
        }
        var memo = $form.find('input[name="purpose"]').val().trim();

        var details = {
            badge: 'ZELLE® INSTANT PAYMENT',
            badgeBg: 'rgba(116, 20, 202, 0.12)',
            badgeColor: '#7414ca',
            amount: '$' + amt.toFixed(2),
            from: fromText,
            to: recipientDisplay,
            delivery: 'Within minutes (Instant Zelle® Settlement)',
            feeText: 'No Transfer Fee • Zelle® Network',
            memo: memo
        };

        csbOpenTransferReview(details, function(d) {
            triggerSecurityGate(function() {
                executeTransfer($form, '#resZelle', "{{ route('user.fund_transfer.zelle.submit') }}", d);
            });
        });
    });

    function csbTxSuccess(resId, msg) {
        $(resId).removeClass('d-none alert-danger').addClass('alert alert-success small').html('<i class="fa-solid fa-check-circle me-1"></i> ' + msg);
    }
    function csbTxError(resId, msg) {
        $(resId).removeClass('d-none alert-success').addClass('alert alert-danger small').html('<i class="fa-solid fa-circle-exclamation me-1"></i> ' + msg);
    }

    // ── Remote Deposit (AJAX file upload & preview) ──
    function csbPreviewImage(input, imgId, wrapId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#' + imgId).attr('src', e.target.result);
                $('#' + wrapId).removeClass('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    $('#csbRemoteDepositForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#rdSubmitBtn');
        var fd = new FormData(this);
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Uploading...');

        $.ajax({
            url: "{{ route('user.remote_deposit.store') }}",
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
        })
        .done(function(res) {
            $('#rdResult').removeClass('d-none alert-danger').addClass('alert alert-success small')
                .html('<i class="fa-solid fa-check-circle me-1"></i> ' + (res.message || 'Check deposit submitted! It will be reviewed and credited within 1–2 business days.'));
            $('#csbRemoteDepositForm')[0].reset();
            $('#csbFrontPreviewWrap, #csbBackPreviewWrap').addClass('d-none');
        })
        .fail(function(xhr) {
            var errors = xhr.responseJSON?.errors;
            var msg = errors ? Object.values(errors).flat().join('<br>') : (xhr.responseJSON?.message || 'Upload failed. Please check the files and try again.');
            $('#rdResult').removeClass('d-none alert-success').addClass('alert alert-danger small')
                .html('<i class="fa-solid fa-circle-exclamation me-1"></i> ' + msg);
        })
        .always(function() { $btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i> SUBMIT CHECK FOR REVIEW'); });
    });

    // ── 3D Card Management Handlers ──
    function csbToggleCardFlip(el) {
        $(el).toggleClass('flipped');
    }

    function csbToggleCardDetails(checkbox) {
        if (checkbox.checked) {
            $('.csb-card-num-masked').addClass('d-none');
            $('.csb-card-num-full').removeClass('d-none');
        } else {
            $('.csb-card-num-masked').removeClass('d-none');
            $('.csb-card-num-full').addClass('d-none');
        }
    }

    function csbToggleCardFreeze(btn, cardId) {
        var $btn = $(btn);
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Updating...');
        triggerSecurityGate(function() {
            $.post("{{ route('user.cards.toggle-status') }}", {
                _token: "{{ csrf_token() }}",
                card_id: cardId
            })
            .done(function(res) {
                window.location.reload();
            })
            .fail(function(xhr) {
                $btn.prop('disabled', false).text('FREEZE / UNFREEZE');
                alert(xhr.responseJSON?.message || 'Could not update card status.');
            });
        });
    }

    function csbResetPin(cardId) {
        var newPin = prompt("Please enter a new 4-digit PIN for your debit card:");
        if (!newPin) return;
        if (!/^\d{4}$/.test(newPin)) {
            alert("PIN must be exactly 4 digits.");
            return;
        }
        triggerSecurityGate(function() {
            $.post("{{ route('user.cards.reset-pin') }}", {
                _token: "{{ csrf_token() }}",
                card_id: cardId,
                pin: newPin,
                pin_confirmation: newPin
            })
            .done(function(res) {
                alert(res.message || "Card PIN reset successfully.");
            })
            .fail(function(xhr) {
                alert(xhr.responseJSON?.message || "Could not reset card PIN.");
            });
        });
    }

    // ── Dispute Form (AJAX → ticket store) ──
    $('#csbDisputeForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $(this).find('button[type=submit]');
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Filing...');
        var reason = $(this).find('[name=reason]').val();
        var txRef  = $(this).find('[name=transaction_ref]').val();
        var full   = 'Transaction: ' + txRef + '\nReason: ' + reason + '\n\n' + $(this).find('[name=description]').val();
        var payload = {
            _token: "{{ csrf_token() }}",
            title: 'Transaction Dispute — ' + txRef,
            message: full,
            priority: 'high',
        };

        $.post("{{ route('user.ticket.store') }}", payload)
            .done(function(res) {
                $('#disputeResult').removeClass('d-none alert-danger').addClass('alert alert-success small')
                    .html('<i class="fa-solid fa-check-circle me-1"></i> Your dispute has been filed. Our team will respond within 10 business days. <a href="{{ route("user.disputes.index") }}" class="alert-link">View Cases →</a>');
                $('#csbDisputeForm')[0].reset();
            })
            .fail(function(xhr) {
                var msg = xhr.responseJSON?.message || 'Could not file dispute. Please try again.';
                $('#disputeResult').removeClass('d-none alert-success').addClass('alert alert-danger small')
                    .html('<i class="fa-solid fa-circle-exclamation me-1"></i> ' + msg);
            })
            .always(function() { $btn.prop('disabled', false).html('<i class="fa-solid fa-file-circle-exclamation me-1"></i> FILE OFFICIAL DISPUTE'); });
    });
    </script>
    <script src="{{ asset('assets/front/js/apexcharts.js') }}"></script>
    @include('frontend::include.__account_detail_modal')
    @stack('js')
</body>
</html>
