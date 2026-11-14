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
    padding: 16px 18px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
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

/* Responsive */
@media (max-width: 900px) {
    .dashboard-grid-2 {
        grid-template-columns: 1fr;
    }
    .csb-sidebar {
        position: fixed;
        top: 50px;
        bottom: 0;
        left: 0;
        z-index: 999;
    }
    /* ── 3D Card Flip Styles ── */
    .csb-card-flip-wrap {
        perspective: 1000px;
        height: 220px;
        cursor: pointer;
        margin: 0 auto 16px auto;
        max-width: 360px;
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
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.25);
    }
    .csb-card-front {
        background: linear-gradient(135deg, #182848 0%, #0f172a 100%);
        padding: 20px 22px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .csb-card-back {
        background: linear-gradient(135deg, #182848 0%, #0f172a 100%);
        transform: rotateY(180deg);
        padding: 0;
    }
    .csb-card-magnetic-strip {
        height: 40px;
        background: #000;
        margin-top: 18px;
        width: 100%;
    }
    .csb-card-cvv-strip {
        background: #e2e8f0;
        height: 30px;
        margin: 12px 20px 0 20px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-right: 12px;
        color: #1e293b;
        font-family: monospace;
        font-weight: bold;
        font-size: 13px;
        letter-spacing: 2px;
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

        <!-- Left Charcoal Sidebar -->
        <aside class="csb-sidebar" id="csbSidebar">
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
                    <a href="{{ route('user.dashboard') }}" class="csb-nav-link">
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

            <!-- Balances Row -->
            <div class="balance-summary-row">
                <div class="summary-block">
                    <div class="summary-label">Total Deposits 1 Accounts</div>
                    <div class="summary-amount">{{ $currency }}{{ number_format($user->balance, 2) }}</div>
                </div>
                <div class="summary-block">
                    <div class="summary-label">Total Loans 0 Accounts</div>
                    <div class="summary-amount">{{ $currency }}{{ number_format($total_loan_amount ?? 0, 2) }}</div>
                </div>
            </div>

            <!-- Checking Card Tile -->
            <div class="account-tile-card">
                <div class="tile-header">
                    <span class="tile-name">Checking <i class="fa-solid fa-star star-icon"></i></span>
                    <span class="tile-account-no">*{{ substr($user->account_number ?? '1397', -4) }}</span>
                </div>
                <div class="tile-balance">
                    {{ $currency }}{{ number_format($user->balance, 2) }}
                </div>
            </div>

            <!-- Actions Row -->
            <div class="accounts-btn-row">
                <a href="{{ route('user.dashboard') }}" class="btn-view-all">View All Accounts</a>
                <a href="javascript:void(0)" class="btn-add-account" data-bs-toggle="modal" data-bs-target="#moveMoneyModal">Add Account</a>
            </div>

            <!-- Two Column Lower Cards -->
            <div class="dashboard-grid-2">
                <!-- Left: Recent Transactions -->
                <div class="csb-widget-card">
                    <div class="widget-header">
                        <h3>Recent Transactions</h3>
                        <div class="widget-controls">
                            <select class="widget-dropdown-select">
                                <option selected>Checking *{{ substr($user->account_number ?? '1397', -4) }}</option>
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
                                <div class="txn-row">
                                    <div>
                                        <div class="txn-desc">{{ $tx->description ?? $tx->type ?? 'Transaction' }}</div>
                                        <div class="txn-date">{{ $tx->created_at ? $tx->created_at->format('m/d/Y') : date('m/d/Y') }}</div>
                                    </div>
                                    <div class="txn-amount {{ ($tx->type == 'deposit' || $tx->type == 'credit' || (isset($tx->amount) && $tx->amount > 0 && !str_contains(strtolower($tx->type ?? ''), 'withdraw'))) ? 'positive' : 'negative' }}">
                                        @if($tx->type == 'withdraw' || $tx->type == 'debit' || (isset($tx->amount) && $tx->amount < 0))
                                            -{{ $currency }}{{ number_format(abs($tx->amount), 2) }}
                                        @else
                                            +{{ $currency }}{{ number_format(abs($tx->amount), 2) }}
                                        @endif
                                    </div>
                                </div>
                                @endforeach
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

                    <ul class="nav nav-pills nav-fill mb-3" id="transferTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-self">Self / Internal</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-member">Member</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-external">External ACH</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-wire">Wire</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-zelle" style="color:#7414ca;font-weight:bold;">Zelle®</button>
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
                                                Checking (*{{ substr($user->account_number ?? '1397', -4) }}) — ${{ number_format($user->balance, 2) }}
                                            </option>
                                            <option value="primary_savings" data-balance="{{ $user->savings_balance ?? 0 }}">
                                                Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}S) — ${{ number_format($user->savings_balance ?? 0, 2) }}
                                            </option>
                                            @if($user->ira_status == 1)
                                            <option value="ira" data-balance="{{ $user->ira_balance ?? 0 }}">
                                                IRA (*{{ substr($user->ira_account_number ?? $user->account_number, -4) }}I) — ${{ number_format($user->ira_balance ?? 0, 2) }}
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
                                        <i class="fa-solid fa-arrow-right-arrow-left me-1"></i> TRANSFER WITHIN MY ACCOUNTS
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
                                            <option value="default">Checking (*{{ substr($user->account_number ?? '1397', -4) }}) — ${{ number_format($user->balance, 2) }}</option>
                                            <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}S) — ${{ number_format($user->savings_balance ?? 0, 2) }}</option>
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
                                        <i class="fa-solid fa-shield-halved me-1"></i> VERIFY 2FA &amp; SEND TO MEMBER
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
                                            <option value="default">Checking (*{{ substr($user->account_number ?? '1397', -4) }}) — ${{ number_format($user->balance, 2) }}</option>
                                            <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}S) — ${{ number_format($user->savings_balance ?? 0, 2) }}</option>
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
                                        <i class="fa-solid fa-shield-halved me-1"></i> VERIFY 2FA &amp; INITIATE ACH TRANSFER
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
                                            <option value="default">Checking (*{{ substr($user->account_number ?? '1397', -4) }}) — ${{ number_format($user->balance, 2) }}</option>
                                            <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}S) — ${{ number_format($user->savings_balance ?? 0, 2) }}</option>
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
                                        <i class="fa-solid fa-shield-halved me-1"></i> VERIFY 2FA &amp; DISPATCH WIRE
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
                                        <img src="{{ asset('assets/external/images/csb-stacked-logo.png') }}" alt="Cleveland State Bank" style="height:26px; filter:brightness(0) invert(1);">
                                        <div style="width:1px; height:20px; background:rgba(255,255,255,0.4);"></div>
                                        <span style="font-size:24px; font-weight:900; letter-spacing:0.5px;">Zelle®</span>
                                    </div>
                                    <div class="extra-small opacity-75">Fast, safe and easy way to send money within minutes.</div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">From Account</label>
                                        <select name="wallet_type" class="form-select shadow-none" required>
                                            <option value="default">Checking (*{{ substr($user->account_number ?? '1397', -4) }}) — ${{ number_format($user->balance, 2) }}</option>
                                            <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}S) — ${{ number_format($user->savings_balance ?? 0, 2) }}</option>
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
                                        <i class="fa-solid fa-shield-halved me-1"></i> VERIFY 2FA &amp; SEND WITH ZELLE®
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
                                        Personal Checking (*{{ substr($user->account_number ?? '1397', -4) }}) — ${{ number_format($user->balance, 2) }}
                                    </option>
                                    <option value="savings">
                                        Primary Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}) — ${{ number_format($user->savings_balance ?? 0, 2) }}
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
                        $userCardRecord = $userCard ?? \App\Models\Card::where('user_id', $user->id)->first();
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
                                    <div class="text-center my-1">
                                        <span class="csb-card-num-masked font-monospace fs-4 text-white" style="letter-spacing: 3px;">
                                            •••• •••• •••• {{ substr($userCardRecord->card_number, -4) }}
                                        </span>
                                        <span class="csb-card-num-full font-monospace fs-4 text-white d-none" style="letter-spacing: 3px;">
                                            {{ chunk_split($userCardRecord->card_number, 4, ' ') }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-end mt-2">
                                        <div class="text-start">
                                            <div class="text-white-50" style="font-size: 9px; text-transform: uppercase;">Cardholder</div>
                                            <div class="fw-bold text-white small">{{ strtoupper($userCardRecord->card_holder_name ?? $user->full_name) }}</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-white-50" style="font-size: 9px; text-transform: uppercase;">Expires</div>
                                            <div class="fw-bold text-white small">{{ $userCardRecord->expiry_month }}/{{ substr($userCardRecord->expiry_year, -2) }}</div>
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
                                    <span class="rounded-circle d-inline-block {{ $userCardRecord->status == 'active' ? 'bg-success' : 'bg-danger' }}" style="width: 10px; height: 10px;"></span>
                                    <span class="small fw-bold {{ $userCardRecord->status == 'active' ? 'text-success' : 'text-danger' }}">
                                        {{ $userCardRecord->status == 'active' ? 'Card Active' : 'Card Frozen' }}
                                    </span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="csbDetailsSwitch" onchange="csbToggleCardDetails(this)">
                                    <label class="form-check-label small" for="csbDetailsSwitch">Show details</label>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <button type="button" class="btn {{ $userCardRecord->status == 'active' ? 'btn-outline-danger' : 'btn-outline-success' }} w-100 fw-bold btn-sm py-2" onclick="csbToggleCardFreeze(this, {{ $userCardRecord->id }})">
                                        <i class="fa-solid {{ $userCardRecord->status == 'active' ? 'fa-snowflake' : 'fa-lock-open' }} me-1"></i>
                                        {{ $userCardRecord->status == 'active' ? 'FREEZE CARD' : 'UNFREEZE CARD' }}
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-secondary w-100 fw-bold btn-sm py-2" onclick="csbResetPin({{ $userCardRecord->id }})">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/frontend/js/security-gate.js') }}?v=2.2"></script>
    <script src="{{ asset('assets/frontend/js/ava.js') }}"></script>

    <script>
    // ── Sidebar Toggle ──
    document.addEventListener('DOMContentLoaded', function () {
        var sidebar = document.getElementById('csbSidebar');
        var toggleBtn = document.getElementById('sidebarToggleBtn');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
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
            $('#csbRoutingStatus').html('<span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i> Invalid ABA Routing mod-10 checksum. Please check the 9 digits.</span>');
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
                    $('#csbRoutingStatus').html('<span class="text-warning"><i class="fa-solid fa-circle-info me-1"></i> Routing verified by checksum. Please provide institution name below.</span>');
                    $('#csbManualBankWrap').removeClass('d-none');
                }
            })
            .catch(function() {
                $('#csbRoutingSpinner').hide();
                $('#csbRoutingStatus').html('<span class="text-warning"><i class="fa-solid fa-circle-info me-1"></i> Verification service offline. Please enter bank name manually.</span>');
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

    // ── Generic Transfer AJAX Dispatcher ──
    function executeTransfer($form, resId, url) {
        var $btn = $form.find('button[type=submit]');
        var origText = $btn.data('orig') || $btn.html();
        $btn.data('orig', origText);
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Processing...');

        $.post(url, $form.serialize())
            .done(function(res) {
                csbTxSuccess(resId, res.message || 'Transfer submitted successfully. Please check your transaction history.');
                $form[0].reset();
                $('#csbMemberVerifySuccess, #csbRoutingVerifiedCard, #csbZelleNoticeBox').addClass('d-none');
            })
            .fail(function(xhr) {
                var errors = xhr.responseJSON?.errors;
                var msg = errors ? Object.values(errors).flat().join('<br>') : (xhr.responseJSON?.message || 'Transfer could not be processed. Please try again.');
                csbTxError(resId, msg);
            })
            .always(function() {
                $btn.prop('disabled', false).html($btn.data('orig') || 'SUBMIT');
            });
    }

    // 1. Self / Internal Transfer: Submits directly without 2FA
    $('#csbFormSelf').on('submit', function(e) {
        e.preventDefault();
        var amt = parseFloat($('#csbSelfAmount').val()) || 0;
        if (amt <= 0) {
            csbTxError('#resSelf', 'Please enter a valid transfer amount.');
            return;
        }
        executeTransfer($(this), '#resSelf', "{{ route('user.fund_transfer.transfer') }}");
    });

    // 2. Member Transfer: Protected by 2FA Security Gate
    $('#csbFormMember').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var accName = $('#csbMemberAccName').val();
        if (!accName) {
            csbTxError('#resMember', 'Please enter a valid recipient and wait for verification.');
            return;
        }
        triggerSecurityGate(function() {
            executeTransfer($form, '#resMember', "{{ route('user.fund_transfer.transfer') }}");
        });
    });

    // 3. External ACH Transfer: Protected by 2FA Security Gate
    $('#csbFormExternal').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var routing = $('#csbExtRouting').val();
        if (!isValidAbaChecksumAch(routing)) {
            csbTxError('#resExternal', 'The 9-digit ABA routing number failed mod-10 checksum.');
            return;
        }
        var acc1 = $('#csbExtAccNum').val();
        var acc2 = $('#csbExtAccNumConfirm').val();
        if (acc1 !== acc2) {
            csbTxError('#resExternal', 'Account numbers do not match. Please verify both fields.');
            return;
        }
        if (!$('#csbResolvedBankId').val() && !$('#csbManualBankInput').val().trim()) {
            csbTxError('#resExternal', 'Please enter the receiving bank name.');
            return;
        }
        triggerSecurityGate(function() {
            executeTransfer($form, '#resExternal', "{{ route('user.fund_transfer.transfer') }}");
        });
    });

    // 4. Wire Transfer: Protected by 2FA Security Gate
    $('#csbFormWire').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        triggerSecurityGate(function() {
            executeTransfer($form, '#resWire', "{{ route('user.fund_transfer.transfer.wire.post') }}");
        });
    });

    // 5. Zelle® Transfer: Protected by 2FA Security Gate
    $('#csbFormZelle').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        triggerSecurityGate(function() {
            executeTransfer($form, '#resZelle', "{{ route('user.fund_transfer.zelle.submit') }}");
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
</body>
</html>
