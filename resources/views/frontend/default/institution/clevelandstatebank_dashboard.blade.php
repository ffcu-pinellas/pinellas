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
                        <a href="{{ route('user.fund_transfer.log') }}" class="csb-nav-link">
                            <i class="fa-solid fa-file-lines" style="width: 16px;"></i> Documents
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="{{ route('user.fund_transfer.log') }}" class="csb-nav-link">
                            <i class="fa-solid fa-chart-column" style="width: 16px;"></i> Reports
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="javascript:void(0)" class="csb-nav-link" data-bs-toggle="modal" data-bs-target="#manageCardsModal">
                            <i class="fa-solid fa-credit-card" style="width: 16px;"></i> Manage Cards
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="{{ route('user.setting.show') }}" class="csb-nav-link">
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
                            <i class="fa-solid fa-comments" style="width: 16px;"></i> Secure Messages
                        </a>
                    </li>
                    <li class="csb-nav-item">
                        <a href="javascript:void(0)" class="csb-nav-link" data-bs-toggle="modal" data-bs-target="#avaAssistantModal">
                            <i class="fa-solid fa-robot" style="width: 16px; color: #4ade80;"></i> Ava Assistant
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
                            <div class="transaction-list-table">
                                <div class="txn-row">
                                    <div>
                                        <div class="txn-desc">MCD HOLD DCU YOUR CHECK ORDER MINNEAPOLIS MN</div>
                                        <div class="txn-date">{{ date('m/d/Y') }}</div>
                                    </div>
                                    <div class="txn-amount negative">
                                        -$29.31
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div style="text-align: right; margin-top: 14px;">
                            <a href="{{ route('user.fund_transfer.log') }}" style="font-size: 12px; color: #1b325f; font-weight: bold; text-decoration: none;">View All Transactions &rarr;</a>
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
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background-color: #c8232c; color: #fff;">
                    <h5 class="modal-title fw-bold" style="font-size: 15px; letter-spacing: 0.5px;">MOVE MONEY &amp; TRANSFERS</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    {{-- Security Gate Step (shows first, hidden after unlock) --}}
                    <div id="csbSecGate" class="p-4">
                        <div class="text-center mb-3">
                            <i class="fa-solid fa-shield-halved fa-2x text-danger mb-2"></i>
                            <p class="small text-muted mb-1">For your security, please verify your identity before transferring funds.</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Account Password</label>
                            <input type="password" id="csbSecPassword" class="form-control" placeholder="Enter your password">
                        </div>
                        <div id="csbSecMsg" class="alert alert-danger d-none small py-2"></div>
                        <button type="button" class="btn btn-danger w-100 fw-bold" onclick="csbUnlockTransfer()">
                            <i class="fa-solid fa-unlock me-1"></i> UNLOCK &amp; CONTINUE
                        </button>
                    </div>

                    {{-- Transfer Tabs (hidden until security unlocked) --}}
                    <div id="csbTransferHub" class="p-4 d-none">
                        <div class="alert alert-success py-2 small mb-3">
                            <i class="fa-solid fa-check-circle me-1"></i> Identity verified. You may now initiate a transfer.
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

                            {{-- Self / Internal Transfer --}}
                            <div class="tab-pane fade show active" id="tab-self">
                                <form id="csbFormSelf" class="csb-transfer-form">
                                    @csrf
                                    <input type="hidden" name="transfer_type" value="self">
                                    <input type="hidden" name="wallet_type" value="default">
                                    <input type="hidden" name="frequency" value="once">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Move To</label>
                                        <select name="to_wallet" class="form-select" required>
                                            <option value="primary_savings">Primary Savings</option>
                                            <option value="ira">IRA Account</option>
                                            <option value="heloc">HELOC</option>
                                            <option value="cc">Credit Card</option>
                                            <option value="loan">Loan Payment</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Amount ($)</label>
                                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Memo</label>
                                        <input type="text" name="purpose" class="form-control" placeholder="Optional note">
                                    </div>
                                    <div id="resSelf" class="csb-tx-result d-none"></div>
                                    <button type="submit" class="btn w-100 fw-bold text-white" style="background:#1b325f;">TRANSFER WITHIN MY ACCOUNTS</button>
                                </form>
                            </div>

                            {{-- Member to Member --}}
                            <div class="tab-pane fade" id="tab-member">
                                <form id="csbFormMember" class="csb-transfer-form">
                                    @csrf
                                    <input type="hidden" name="transfer_type" value="member">
                                    <input type="hidden" name="wallet_type" value="default">
                                    <input type="hidden" name="frequency" value="once">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Recipient — Account # or Username or Email</label>
                                        <input type="text" name="member_identifier" class="form-control" placeholder="Enter account number, username, or email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Amount ($)</label>
                                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Memo</label>
                                        <input type="text" name="purpose" class="form-control" placeholder="e.g. Rent payment">
                                    </div>
                                    <div id="resMember" class="csb-tx-result d-none"></div>
                                    <button type="submit" class="btn w-100 fw-bold text-white" style="background:#28623b;">SEND TO MEMBER</button>
                                </form>
                            </div>

                            {{-- External ACH --}}
                            <div class="tab-pane fade" id="tab-external">
                                <form id="csbFormExternal" class="csb-transfer-form">
                                    @csrf
                                    <input type="hidden" name="transfer_type" value="external">
                                    <input type="hidden" name="wallet_type" value="default">
                                    <input type="hidden" name="frequency" value="once">
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">ABA Routing Number</label>
                                            <input type="text" name="manual_data[routing_number]" class="form-control" placeholder="9-digit ABA" maxlength="9" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Account Number</label>
                                            <input type="text" name="manual_data[account_number]" class="form-control" placeholder="Account #" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Account Holder Name</label>
                                        <input type="text" name="manual_data[account_name]" class="form-control" placeholder="Full legal name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Bank Name</label>
                                        <input type="text" name="manual_data[bank_name]" class="form-control" placeholder="e.g. Chase, Wells Fargo">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Amount ($)</label>
                                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                    </div>
                                    <div id="resExternal" class="csb-tx-result d-none"></div>
                                    <button type="submit" class="btn w-100 fw-bold text-white" style="background:#28623b;">INITIATE ACH TRANSFER</button>
                                </form>
                            </div>

                            {{-- Wire Transfer --}}
                            <div class="tab-pane fade" id="tab-wire">
                                <form id="csbFormWire" class="csb-wire-form">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Beneficiary Name</label>
                                        <input type="text" name="beneficiary_name" class="form-control" placeholder="Full legal name" required>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Wire ABA Routing #</label>
                                            <input type="text" name="routing_number" class="form-control" placeholder="9-digit routing" maxlength="9" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Account Number</label>
                                            <input type="text" name="account_number" class="form-control" placeholder="Account #" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Bank / Institution Name</label>
                                        <input type="text" name="bank_name" class="form-control" placeholder="Receiving institution">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Amount ($)</label>
                                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Wire Memo / Purpose</label>
                                        <input type="text" name="memo" class="form-control" placeholder="e.g. Payment for services">
                                    </div>
                                    <div id="resWire" class="csb-tx-result d-none"></div>
                                    <button type="submit" class="btn w-100 fw-bold text-white" style="background:#c8232c;">DISPATCH WIRE TRANSFER</button>
                                </form>
                            </div>

                            {{-- Zelle® --}}
                            <div class="tab-pane fade" id="tab-zelle">
                                <form id="csbFormZelle" class="csb-zelle-form">
                                    @csrf
                                    <div class="text-center mb-3">
                                        <span style="font-size:28px;font-weight:900;color:#7414ca;">Zelle®</span>
                                        <div class="small text-muted">Send money instantly to anyone with a U.S. bank account.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Recipient Mobile or Email</label>
                                        <input type="text" name="contact" class="form-control" placeholder="(555) 123-4567 or email@example.com" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Amount ($)</label>
                                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Memo (optional)</label>
                                        <input type="text" name="memo" class="form-control" placeholder="e.g. Dinner split">
                                    </div>
                                    <div id="resZelle" class="csb-tx-result d-none"></div>
                                    <button type="submit" class="btn w-100 fw-bold text-white" style="background:#7414ca;">SEND WITH ZELLE®</button>
                                </form>
                            </div>

                        </div>{{-- end tab-content --}}
                    </div>{{-- end csbTransferHub --}}
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2. REMOTE CHECK DEPOSIT ── --}}
    <div class="modal fade" id="remoteDepositModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background-color:#c8232c;color:#fff;">
                    <h5 class="modal-title fw-bold" style="font-size:15px;">REMOTE CHECK DEPOSIT</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="rdResult" class="d-none mb-3"></div>
                    <form id="csbRemoteDepositForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Deposit Amount ($)</label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" min="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Front of Check</label>
                            <input type="file" name="front_image" class="form-control" accept="image/*,.pdf" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Back of Check (Endorsed)</label>
                            <input type="file" name="back_image" class="form-control" accept="image/*,.pdf" required>
                            <div class="small text-muted mt-1">Write "For Mobile Deposit Only — Cleveland State Bank" on the back.</div>
                        </div>
                        <button type="submit" id="rdSubmitBtn" class="btn w-100 fw-bold text-white" style="background:#28623b;">
                            SUBMIT CHECK FOR REVIEW
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. MANAGE CARDS ── --}}
    <div class="modal fade" id="manageCardsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background-color:#262626;color:#fff;">
                    <h5 class="modal-title fw-bold" style="font-size:15px;">MANAGE DEBIT CARDS</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    @if(isset($userCard) && $userCard)
                        <div class="p-4 rounded-3 text-white mb-3 shadow" style="background:linear-gradient(135deg,#1e355b,#0f1d33);text-align:left;">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold" style="font-size:13px;letter-spacing:1px;">CLEVELAND STATE BANK</span>
                                <span class="fw-bold text-uppercase" style="font-size:13px;">{{ $userCard->type ?? 'VISA' }}</span>
                            </div>
                            <div class="font-monospace mb-3" style="font-size:17px;letter-spacing:3px;">
                                •••• •••• •••• {{ substr($userCard->card_number, -4) }}
                            </div>
                            <div class="d-flex justify-content-between" style="font-size:12px;">
                                <div>
                                    <div class="text-white-50">CARDHOLDER</div>
                                    <div class="fw-bold">{{ strtoupper($userCard->card_holder_name ?? $user->full_name) }}</div>
                                </div>
                                <div>
                                    <div class="text-white-50">EXPIRES</div>
                                    <div class="fw-bold">{{ $userCard->expiry_month }}/{{ substr($userCard->expiry_year, -2) }}</div>
                                </div>
                                <div>
                                    <div class="text-white-50">CVV</div>
                                    <div class="fw-bold">•••</div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <span class="badge {{ $userCard->status == 'active' ? 'bg-success' : 'bg-danger' }} mb-2">
                                {{ strtoupper($userCard->status ?? 'Active') }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('user.cards.toggle-status') }}">
                            @csrf
                            <input type="hidden" name="card_id" value="{{ $userCard->id }}">
                            <button type="submit" class="btn {{ $userCard->status == 'active' ? 'btn-outline-danger' : 'btn-outline-success' }} w-100 fw-bold">
                                {{ $userCard->status == 'active' ? '❄️ FREEZE CARD' : '✅ UNFREEZE CARD' }}
                            </button>
                        </form>
                    @else
                        <div class="py-4 text-muted">
                            <i class="fa-solid fa-credit-card fa-3x mb-3"></i>
                            <h5>No Card on File</h5>
                            <p class="small">Contact your branch officer to issue your Cleveland State Bank Visa debit card.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── 4. AVA AI ASSISTANT ── --}}
    <div class="modal fade" id="avaAssistantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background:#1e355b;color:#fff;">
                    <h5 class="modal-title fw-bold" style="font-size:15px;">
                        <i class="fa-solid fa-robot me-2" style="color:#4ade80;"></i>Ava — AI Banking Assistant
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 d-flex flex-column" style="height:420px;">
                    <div id="avaChatBox" style="flex:1;overflow-y:auto;background:#f8fafc;padding:12px;border-radius:4px;border:1px solid #e2e8f0;margin-bottom:10px;">
                        <div style="display:flex;gap:8px;margin-bottom:10px;">
                            <div style="width:28px;height:28px;border-radius:50%;background:#1e355b;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;">
                                <i class="fa-solid fa-robot"></i>
                            </div>
                            <div class="p-2 bg-white rounded shadow-sm small text-dark" style="max-width:85%;">
                                Hello, <strong>{{ $user->first_name }}</strong>! I'm Ava, your Cleveland State Bank digital assistant. I can help with account balances, transfer inquiries, card status, and more. How can I help you today?
                            </div>
                        </div>
                    </div>
                    <form id="avaQueryForm" onsubmit="sendAvaMessage(event)">
                        <div class="input-group">
                            <input type="text" id="avaInput" class="form-control form-control-sm" placeholder="Ask Ava anything..." required>
                            <button class="btn btn-sm text-white" type="submit" style="background:#1e355b;">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                            <input type="text" name="transaction_ref" class="form-control" placeholder="e.g. MCD HOLD DCU -$29.31 or txn ID" required>
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

        // Reset security gate when modal closes
        document.getElementById('moveMoneyModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('csbSecGate').classList.remove('d-none');
            document.getElementById('csbTransferHub').classList.add('d-none');
            document.getElementById('csbSecPassword').value = '';
            document.getElementById('csbSecMsg').classList.add('d-none');
            document.querySelectorAll('.csb-tx-result').forEach(function(el){ el.classList.add('d-none'); el.innerHTML=''; });
        });
    });

    // ── Security Unlock (password verify) ──
    function csbUnlockTransfer() {
        var pw = document.getElementById('csbSecPassword').value.trim();
        if (!pw) { csbSecError('Please enter your account password.'); return; }
        var btn = document.querySelector('#csbSecGate button');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Verifying...';

        $.post("{{ route('user.verify.password') }}", { _token: "{{ csrf_token() }}", password: pw })
            .done(function(res) {
                if (res && (res.success || res.status === 'success' || res.verified)) {
                    document.getElementById('csbSecGate').classList.add('d-none');
                    document.getElementById('csbTransferHub').classList.remove('d-none');
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-unlock me-1"></i> UNLOCK & CONTINUE';
                    csbSecError(res.message || 'Incorrect password. Please try again.');
                }
            })
            .fail(function(xhr) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-unlock me-1"></i> UNLOCK & CONTINUE';
                var msg = xhr.responseJSON?.message || 'Verification failed. Please try again.';
                csbSecError(msg);
            });
    }

    function csbSecError(msg) {
        var el = document.getElementById('csbSecMsg');
        el.textContent = msg;
        el.classList.remove('d-none');
    }

    // ── Generic transfer form submit (member / self / external) ──
    $(document).on('submit', '.csb-transfer-form', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type=submit]');
        var resId = '#res' + $form.attr('id').replace('csbForm','').charAt(0).toUpperCase() + $form.attr('id').replace('csbForm','').slice(1);
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Processing...');

        $.post("{{ route('user.fund_transfer.transfer') }}", $form.serialize())
            .done(function(res) {
                csbTxSuccess(resId, res.message || 'Transfer submitted successfully. Please check your transaction history.');
            })
            .fail(function(xhr) {
                var errors = xhr.responseJSON?.errors;
                var msg = errors ? Object.values(errors).flat().join('<br>') : (xhr.responseJSON?.message || 'Transfer could not be processed. Please try again.');
                csbTxError(resId, msg);
            })
            .always(function() {
                $btn.prop('disabled', false).html($btn.data('orig') || $btn.text().replace('Processing...','').trim() || 'SUBMIT');
            });
    });

    // ── Wire transfer ──
    $(document).on('submit', '.csb-wire-form', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type=submit]');
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Dispatching...');

        $.post("{{ route('user.fund_transfer.wire.post') }}", $form.serialize())
            .done(function(res) {
                csbTxSuccess('#resWire', res.message || 'Wire transfer dispatched. Processing may take 1–3 business days.');
            })
            .fail(function(xhr) {
                var errors = xhr.responseJSON?.errors;
                var msg = errors ? Object.values(errors).flat().join('<br>') : (xhr.responseJSON?.message || 'Wire transfer failed. Please verify your details.');
                csbTxError('#resWire', msg);
            })
            .always(function() { $btn.prop('disabled', false).text('DISPATCH WIRE TRANSFER'); });
    });

    // ── Zelle transfer ──
    $(document).on('submit', '.csb-zelle-form', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type=submit]');
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Sending...');

        $.post("{{ route('user.fund_transfer.zelle.submit') }}", $form.serialize())
            .done(function(res) {
                csbTxSuccess('#resZelle', res.message || 'Zelle® payment sent! Funds typically arrive within minutes.');
            })
            .fail(function(xhr) {
                var errors = xhr.responseJSON?.errors;
                var msg = errors ? Object.values(errors).flat().join('<br>') : (xhr.responseJSON?.message || 'Zelle® transfer could not be processed.');
                csbTxError('#resZelle', msg);
            })
            .always(function() { $btn.prop('disabled', false).text('SEND WITH ZELLE®'); });
    });

    function csbTxSuccess(resId, msg) {
        $(resId).removeClass('d-none alert-danger').addClass('alert alert-success small').html('<i class="fa-solid fa-check-circle me-1"></i> ' + msg);
    }
    function csbTxError(resId, msg) {
        $(resId).removeClass('d-none alert-success').addClass('alert alert-danger small').html('<i class="fa-solid fa-circle-exclamation me-1"></i> ' + msg);
    }

    // ── Remote Deposit (AJAX file upload) ──
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
        })
        .fail(function(xhr) {
            var errors = xhr.responseJSON?.errors;
            var msg = errors ? Object.values(errors).flat().join('<br>') : (xhr.responseJSON?.message || 'Upload failed. Please check the files and try again.');
            $('#rdResult').removeClass('d-none alert-success').addClass('alert alert-danger small')
                .html('<i class="fa-solid fa-circle-exclamation me-1"></i> ' + msg);
        })
        .always(function() { $btn.prop('disabled', false).html('SUBMIT CHECK FOR REVIEW'); });
    });

    // ── Dispute Form (AJAX → ticket store) ──
    $('#csbDisputeForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $(this).find('button[type=submit]');
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Filing...');
        var data = $(this).serialize();
        // Merge reason into description for ticket
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

    // ── Ava AI Assistant ──
    function sendAvaMessage(e) {
        e.preventDefault();
        var input = document.getElementById('avaInput');
        var query = input.value.trim();
        if (!query) return;

        var chatBox = document.getElementById('avaChatBox');
        chatBox.innerHTML += '<div style="display:flex;justify-content:flex-end;margin-bottom:10px;">'
            + '<div class="p-2 rounded small text-white" style="max-width:80%;background:#1e355b;">' + $('<span>').text(query).html() + '</div>'
            + '</div>';
        input.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;

        // Typing indicator
        var typingId = 'ava-typing-' + Date.now();
        chatBox.innerHTML += '<div id="' + typingId + '" style="display:flex;gap:8px;margin-bottom:10px;">'
            + '<div style="width:28px;height:28px;border-radius:50%;background:#1e355b;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;"><i class="fa-solid fa-robot"></i></div>'
            + '<div class="p-2 bg-white rounded shadow-sm small text-muted">Ava is typing…</div></div>';
        chatBox.scrollTop = chatBox.scrollHeight;

        $.post("{{ route('user.ava.query') }}", { _token: "{{ csrf_token() }}", query: query })
            .done(function(res) {
                document.getElementById(typingId)?.remove();
                var reply = res.response || res.message || 'I appreciate your question. For account-specific inquiries, please contact your Account Officer directly.';
                chatBox.innerHTML += '<div style="display:flex;gap:8px;margin-bottom:10px;">'
                    + '<div style="width:28px;height:28px;border-radius:50%;background:#1e355b;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;"><i class="fa-solid fa-robot"></i></div>'
                    + '<div class="p-2 bg-white rounded shadow-sm small text-dark" style="max-width:85%;"><strong>Ava:</strong> ' + reply + '</div></div>';
                chatBox.scrollTop = chatBox.scrollHeight;
            })
            .fail(function() {
                document.getElementById(typingId)?.remove();
                chatBox.innerHTML += '<div style="display:flex;gap:8px;margin-bottom:10px;">'
                    + '<div style="width:28px;height:28px;border-radius:50%;background:#1e355b;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;"><i class="fa-solid fa-robot"></i></div>'
                    + '<div class="p-2 bg-white rounded shadow-sm small text-dark" style="max-width:85%;"><strong>Ava:</strong> I\'m having trouble connecting right now. Please try again shortly or contact Member Support.</div></div>';
                chatBox.scrollTop = chatBox.scrollHeight;
            });
    }
    </script>
</body>
</html>
