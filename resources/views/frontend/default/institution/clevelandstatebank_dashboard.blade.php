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

    <!-- 1. Move Money / Multi-Transfer Hub Modal -->
    <div class="modal fade" id="moveMoneyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background-color: #c8232c; color: #fff;">
                    <h5 class="modal-title fw-bold" style="font-size: 15px; letter-spacing: 0.5px;">MOVE MONEY & TRANSFERS</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <ul class="nav nav-pills nav-fill mb-3" id="transferTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-member">Member to Member</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-external">External ACH</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-wire">Wire Transfer</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-zelle" style="color: #7414ca; font-weight: bold;">Zelle®</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="transferTabContent">
                        <!-- Member to Member -->
                        <div class="tab-pane fade show active" id="tab-member">
                            <form method="POST" action="{{ route('user.fund_transfer.transfer') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Recipient Account Number or Username</label>
                                    <input type="text" name="account_number" class="form-control" placeholder="Enter recipient account #" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Transfer Amount ($)</label>
                                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Memo / Description</label>
                                    <input type="text" name="description" class="form-control" placeholder="e.g. Funds transfer">
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold" style="background-color: #28623b; border: none;">SEND TRANSFER NOW</button>
                            </form>
                        </div>

                        <!-- External ACH -->
                        <div class="tab-pane fade" id="tab-external">
                            <form method="POST" action="{{ route('user.fund_transfer.transfer') }}">
                                @csrf
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Recipient Routing Number</label>
                                        <input type="text" name="routing_number" class="form-control" placeholder="9-digit ABA" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Recipient Account Number</label>
                                        <input type="text" name="account_number" class="form-control" placeholder="Account #" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Recipient Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" placeholder="e.g. Chase, Wells Fargo">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Amount ($)</label>
                                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold" style="background-color: #28623b; border: none;">INITIATE ACH TRANSFER</button>
                            </form>
                        </div>

                        <!-- Domestic Wire -->
                        <div class="tab-pane fade" id="tab-wire">
                            <form method="POST" action="{{ route('user.fund_transfer.wire.post') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Beneficiary Name</label>
                                    <input type="text" name="beneficiary_name" class="form-control" placeholder="Full legal name" required>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Wire Routing (ABA)</label>
                                        <input type="text" name="routing_number" class="form-control" placeholder="9-digit wire routing" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Account Number</label>
                                        <input type="text" name="account_number" class="form-control" placeholder="Account #" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Amount ($)</label>
                                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold" style="background-color: #c8232c; border: none;">DISPATCH WIRE TRANSFER</button>
                            </form>
                        </div>

                        <!-- Zelle Instant Transfer -->
                        <div class="tab-pane fade" id="tab-zelle">
                            <form method="POST" action="{{ route('user.fund_transfer.zelle.submit') }}">
                                @csrf
                                <div class="text-center mb-3">
                                    <span style="font-size: 24px; font-weight: 900; color: #7414ca;">Zelle®</span>
                                    <div class="small text-muted">Send money in minutes with enrolled email or mobile number.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Recipient Mobile Number or Email</label>
                                    <input type="text" name="contact" class="form-control" placeholder="(555) 123-4567 or email@example.com" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Amount ($)</label>
                                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold" style="background-color: #7414ca; border: none;">SEND WITH ZELLE®</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Remote Check Deposit Modal -->
    <div class="modal fade" id="remoteDepositModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background-color: #c8232c; color: #fff;">
                    <h5 class="modal-title fw-bold" style="font-size: 15px;">REMOTE CHECK DEPOSIT</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="{{ route('user.remote_deposit.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Deposit Amount ($)</label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Front of Check</label>
                            <input type="file" name="front_image" class="form-control" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Back of Check (Endorsed)</label>
                            <input type="file" name="back_image" class="form-control" accept="image/*" required>
                            <div class="small text-muted mt-1">Please write "For Mobile Deposit Only at Cleveland State Bank" under your signature.</div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold" style="background-color: #28623b;">SUBMIT CHECK DEPOSIT</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Manage Cards Modal -->
    <div class="modal fade" id="manageCardsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background-color: #262626; color: #fff;">
                    <h5 class="modal-title fw-bold" style="font-size: 15px;">MANAGE DEBIT CARDS</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    @if(isset($userCard) && $userCard)
                        <div class="p-4 rounded-3 text-white mb-3 shadow" style="background: linear-scale(135deg, #1e355b, #0f1d33); text-align: left; position: relative;">
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold tracking-wider">Cleveland State Bank</span>
                                <span class="fw-bold text-uppercase">{{ $userCard->type ?? 'VISA' }}</span>
                            </div>
                            <div class="fs-5 tracking-widest mb-3 font-monospace">
                                {{ chunk_split($userCard->card_number, 4, ' ') }}
                            </div>
                            <div class="d-flex justify-content-between small text-white-50">
                                <div>
                                    <div>CARDHOLDER</div>
                                    <div class="text-white fw-bold">{{ $userCard->card_holder_name ?? $user->full_name }}</div>
                                </div>
                                <div>
                                    <div>EXPIRES</div>
                                    <div class="text-white fw-bold">{{ $userCard->expiry_month }}/{{ substr($userCard->expiry_year, -2) }}</div>
                                </div>
                                <div>
                                    <div>CVV</div>
                                    <div class="text-white fw-bold">{{ $userCard->cvv }}</div>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('user.cards.toggle-status') }}">
                            @csrf
                            <input type="hidden" name="card_id" value="{{ $userCard->id }}">
                            <button type="submit" class="btn btn-outline-danger w-100 fw-bold">
                                {{ $userCard->status == 'active' ? '❄️ FREEZE CARD' : 'UNFREEZE CARD' }}
                            </button>
                        </form>
                    @else
                        <div class="py-4 text-muted">
                            <i class="fa-solid fa-credit-card fa-3x mb-3 text-muted"></i>
                            <h5>No active card on file</h5>
                            <p class="small">Contact your branch or representative to issue your Cleveland State Bank Visa debit card.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Ava AI Chatbot Modal -->
    <div class="modal fade" id="avaAssistantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background-color: #1e355b; color: #fff;">
                    <h5 class="modal-title fw-bold" style="font-size: 15px;"><i class="fa-solid fa-robot text-success me-2"></i>Ava AI Banking Assistant</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <div id="avaChatBox" style="height: 300px; overflow-y: auto; background: #f8fafc; padding: 12px; border-radius: 4px; border: 1px solid #e2e8f0;" class="mb-3">
                        <div class="p-2 mb-2 bg-white rounded shadow-sm small text-dark" style="max-width: 85%;">
                            <strong>Ava:</strong> Hello {{ $user->first_name }}! How can I assist you with your Cleveland State Bank accounts today?
                        </div>
                    </div>
                    <form id="avaQueryForm" onsubmit="sendAvaMessage(event)">
                        <div class="input-group">
                            <input type="text" id="avaInput" class="form-control" placeholder="Ask Ava a question..." required>
                            <button class="btn btn-primary" type="submit" style="background-color: #1e355b;">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Dispute Transaction Modal -->
    <div class="modal fade" id="disputeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" style="font-size: 15px;">DISPUTE A TRANSACTION</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="{{ route('user.ticket.store') }}">
                        @csrf
                        <input type="hidden" name="title" value="Transaction Dispute Claim">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Transaction Reference or Amount</label>
                            <input type="text" name="transaction_ref" class="form-control" placeholder="e.g. MCD HOLD DCU -$29.31" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Reason for Dispute</label>
                            <select name="reason" class="form-select" required>
                                <option value="Unauthorized Charge">Unauthorized Charge</option>
                                <option value="Incorrect Amount Billed">Incorrect Amount Billed</option>
                                <option value="Merchandise Not Received">Merchandise Not Received</option>
                                <option value="Duplicate Charge">Duplicate Charge</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Additional Details</label>
                            <textarea name="description" rows="3" class="form-control" placeholder="Provide any additional context regarding this charge..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 fw-bold">FILE OFFICIAL DISPUTE</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var sidebar = document.getElementById('csbSidebar');
        var toggleBtn = document.getElementById('sidebarToggleBtn');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
            });
        }
    });

    function sendAvaMessage(e) {
        e.preventDefault();
        var input = document.getElementById('avaInput');
        var query = input.value.trim();
        if (!query) return;

        var chatBox = document.getElementById('avaChatBox');
        chatBox.innerHTML += '<div class="p-2 mb-2 bg-primary text-white ms-auto rounded shadow-sm small text-end" style="max-width: 80%;">' + query + '</div>';
        input.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;

        $.ajax({
            url: "{{ route('user.ava.query') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                query: query
            },
            success: function(res) {
                var reply = res.response || res.message || "I am processing your inquiry. For sensitive operations, please contact member support.";
                chatBox.innerHTML += '<div class="p-2 mb-2 bg-white rounded shadow-sm small text-dark" style="max-width: 85%;"><strong>Ava:</strong> ' + reply + '</div>';
                chatBox.scrollTop = chatBox.scrollHeight;
            },
            error: function() {
                chatBox.innerHTML += '<div class="p-2 mb-2 bg-white rounded shadow-sm small text-dark" style="max-width: 85%;"><strong>Ava:</strong> I could not complete your request at this moment. Please try again or reach out to your Account Officer.</div>';
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });
    }
    </script>
</body>
</html>