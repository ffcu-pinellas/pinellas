<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
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

/* ── CSB 3D Flip Account Tiles (Parity with 0.2s cubic-bezier(0.4, 0, 0.2, 1)) ── */
.csb-account-card {
    perspective: 1000px;
    min-height: 105px;
    height: 105px;
    cursor: pointer;
    flex: 1 1 280px;
    max-width: 360px;
    margin-bottom: 0;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    position: relative;
    background: transparent !important;
    border: none !important;
    padding: 0 !important;
    box-shadow: none !important;
}

.csb-account-card-inner {
    position: relative;
    width: 100%;
    height: 100%;
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    transform-style: preserve-3d;
    border-radius: 8px;
}

/* Flip on hover (desktop) or when is-flipped class is present (mobile tap) */
.csb-account-card:hover .csb-account-card-inner,
.csb-account-card.is-flipped .csb-account-card-inner {
    transform: rotateX(180deg);
}

.csb-account-card-front,
.csb-account-card-back {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    border-radius: 8px;
    box-sizing: border-box;
    padding: 14px 18px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.csb-account-card-front {
    z-index: 2;
    transform: rotateX(0deg);
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.csb-account-card-back {
    transform: rotateX(180deg);
    background: linear-gradient(135deg, #162846 0%, #0b1a30 100%) !important;
    border: 1px solid rgba(200, 35, 44, 0.45);
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(22, 40, 70, 0.15);
}

.csb-account-card-back .ledger-title {
    font-size: 11px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #94a3b8;
    font-weight: 700;
}

.csb-account-card-back .ledger-badge {
    background: rgba(200, 35, 44, 0.25);
    color: #fca5a5;
    font-size: 9.5px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
    letter-spacing: 0.5px;
}

.csb-account-card-back .ledger-amount {
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
}

.csb-account-card-back .ledger-meta {
    font-size: 10.5px;
    color: #cbd5e1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

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
    margin-bottom: 8px;
    font-size: 13.5px;
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
    font-size: 12.5px;
}

.tile-balance {
    font-size: 22px;
    font-weight: bold;
    color: #1e293b;
    line-height: 1.1;
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

/* Category icon */
.txn-cat-icon {
    flex-shrink: 0;
    transition: transform 0.2s ease;
}

/* Mobile-First Transaction History Responsive Card Tiles (<768px) */
@media (max-width: 767px) {
    .transaction-list-table {
        display: flex !important;
        flex-direction: column !important;
        gap: 10px !important;
    }
    .txn-row {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        padding: 12px 14px !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04) !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        transition: all 0.2s ease !important;
        margin-bottom: 0 !important;
    }
    .txn-row:active {
        background: #f8fafc !important;
        transform: scale(0.99);
    }
    .txn-row.is-expanded {
        border-color: #cbd5e1 !important;
        box-shadow: 0 4px 12px rgba(22, 40, 70, 0.08) !important;
    }
    .txn-desc {
        max-width: 175px !important;
        font-size: 13px !important;
    }
    .txn-amount {
        font-size: 14px !important;
    }
    .txn-settlement-details {
        display: none;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
        font-size: 12px;
        color: #334155;
        width: 100%;
    }
    .txn-row.is-expanded .txn-settlement-details {
        display: block !important;
        animation: csbTxnExpandFade 0.2s ease-out;
    }
}

@media (min-width: 768px) {
    .txn-settlement-details {
        display: none !important;
    }
}

@keyframes csbTxnExpandFade {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes csbNewTxnHighlightAnim {
    0% {
        background-color: rgba(200, 35, 44, 0.18) !important;
        transform: translateY(-4px);
    }
    60% {
        background-color: rgba(200, 35, 44, 0.08) !important;
    }
    100% {
        background-color: #ffffff !important;
        transform: translateY(0);
    }
}

.csb-new-txn-highlight {
    animation: csbNewTxnHighlightAnim 3s ease-out forwards !important;
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
    .account-tiles-grid .account-tile-card,
    .account-tiles-grid .csb-account-card {
        flex: 0 0 270px !important;
        max-width: 270px !important;
        min-width: 270px !important;
        scroll-snap-align: start;
        margin-bottom: 0 !important;
        min-height: 105px !important;
        height: 105px !important;
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
.account-tiles-grid .account-tile-card,
.account-tiles-grid .csb-account-card {
    flex: 1 1 280px;
    max-width: 360px;
    margin-bottom: 0;
}
.account-tiles-grid .account-tile-card {
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

/* ── Mobile Floating Quick-Action Dock (Original CSB Navy Design) ── */
.csb-mobile-dock {
    display: none;
}
@media (max-width: 768px) {
    .csb-mobile-dock {
        display: flex;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 64px;
        background: #162846; /* Official CSB Dark Navy */
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.25);
        z-index: 1040;
        justify-content: space-around;
        align-items: center;
        padding: 4px 6px env(safe-area-inset-bottom, 8px) 6px;
    }
    .csb-dock-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        text-decoration: none;
        font-size: 10.5px;
        font-weight: 600;
        flex: 1;
        padding: 4px 0;
        transition: color 0.15s ease, transform 0.15s ease;
        border: none;
        background: transparent;
        cursor: pointer;
    }
    .csb-dock-item:active, .csb-dock-item:hover {
        color: #ffffff;
        transform: translateY(-1px);
    }
    .csb-dock-item i {
        font-size: 17px;
        margin-bottom: 3px;
        display: block;
        color: #cbd5e1;
    }
    .csb-dock-item:hover i {
        color: #ffffff;
    }
    .csb-dock-item.csb-dock-primary {
        color: #ffffff;
    }
    .csb-dock-item.csb-dock-primary .csb-dock-icon-circle {
        background: linear-gradient(135deg, #c8232c, #a51821);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: -16px;
        box-shadow: 0 4px 14px rgba(200, 35, 44, 0.45);
        color: #ffffff;
        font-size: 16px;
        border: 2px solid #162846;
    }
    .dashboard-content, .dashboard-wrapper {
        padding-bottom: 78px !important;
    }
}

/* ── Skeleton Loading Shimmer Placeholders ── */
@keyframes csbShimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
.csb-skeleton {
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 37%, #f1f5f9 63%);
    background-size: 400% 100%;
    animation: csbShimmer 1.4s ease infinite;
    border-radius: 6px;
    display: inline-block;
}
.csb-skeleton-text {
    height: 12px;
    border-radius: 4px;
}

/* ── Interactive Ledger Live Search & Export ── */
.csb-txn-search-wrap {
    position: relative;
    max-width: 190px;
}
.csb-txn-search-wrap input {
    height: 32px;
    padding-left: 28px;
    padding-right: 12px;
    border-radius: 20px;
    border: 1px solid #cbd5e1;
    font-size: 12px;
    transition: all 0.2s ease;
    background: #ffffff;
}
.csb-txn-search-wrap input:focus {
    border-color: #c8232c;
    box-shadow: 0 0 0 3px rgba(200, 35, 44, 0.15);
    outline: none;
}
.csb-btn-export-csv {
    height: 32px;
    padding: 0 12px;
    border-radius: 20px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.csb-btn-export-csv:hover {
    background: #f8fafc;
    color: #1e293b;
    border-color: #94a3b8;
}
</style>
</head>
<body>

    <!-- Global Loader (CSB Clean Brand, No Shield, Clear Progress Text) -->
    <style>
    #global-loader {
        position: fixed;
        inset: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 999999;
        display: none;
        align-items: center;
        justify-content: center;
        transition: opacity 0.25s ease;
        opacity: 0;
    }
    .csb-loader-spinner-wrapper {
        position: relative;
        width: 88px;
        height: 88px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .csb-loader-spinner-ring {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 3.5px solid rgba(255, 255, 255, 0.18);
        border-top-color: #c8232c;
        animation: csbSpin 0.85s linear infinite;
    }
    .csb-loader-logo-inner {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        padding: 7px;
        z-index: 2;
    }
    .csb-loader-logo-inner img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    @keyframes csbSpin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    </style>
    <div id="global-loader" role="alert" aria-busy="true">
        <div class="csb-loader-spinner-wrapper">
            <div class="csb-loader-spinner-ring"></div>
            <div class="csb-loader-logo-inner">
                <img src="{{ asset('assets/institutions/clevelandstatebank/images/logo.png') }}" alt="Cleveland State Bank">
            </div>
        </div>
    </div>
    <script>
        // Pre-define csbHandleTileTap early so tiles never trigger ReferenceError
        window.csbHandleTileTap = function(el, event, title, acct, bal, limit, type) {
            if (typeof window.showAccountDetails === 'function') {
                window.showAccountDetails(title, acct, bal, limit, type);
            } else {
                setTimeout(function() {
                    if (typeof window.showAccountDetails === 'function') {
                        window.showAccountDetails(title, acct, bal, limit, type);
                    }
                }, 150);
            }
        };

        window._csbLoaderHideTimer = null;
        window.showTransferLoader = function(title, subtitle) {
            if (window._csbLoaderHideTimer) {
                clearTimeout(window._csbLoaderHideTimer);
                window._csbLoaderHideTimer = null;
            }
            var el = document.getElementById('global-loader');
            document.querySelectorAll('form button[type=submit], input[type=submit], #rdSubmitBtn, .btn-submit, .csb-action-submit').forEach(function(btn) {
                btn.disabled = true;
            });
            if (el) {
                el.style.display = 'flex';
                setTimeout(function() { el.style.opacity = '1'; }, 10);
            }
        };
        window.showLoader = function(title, subtitle) {
            window.showTransferLoader();
        };
        window.hideLoader = function() {
            if (window._csbLoaderHideTimer) {
                clearTimeout(window._csbLoaderHideTimer);
            }
            var el = document.getElementById('global-loader');
            if (el) {
                el.style.opacity = '0';
                window._csbLoaderHideTimer = setTimeout(function() {
                    el.style.display = 'none';
                    document.querySelectorAll('form button[type=submit], input[type=submit], #rdSubmitBtn, .btn-submit, .csb-action-submit').forEach(function(btn) {
                        btn.disabled = false;
                    });
                    window._csbLoaderHideTimer = null;
                }, 250);
            }
        };
    </script>
    @include('global._notify')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show m-3 mb-0" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show m-3 mb-0" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
            <img src="{{ (app()->bound('institution') && app('institution')->logo) ? app('institution')->logo : asset('assets/institutions/clevelandstatebank/images/logo-white.png') }}" alt="Cleveland State Bank" class="csb-header-logo-img">
        </a>
    </header>

    <!-- Main Shell -->
    <div class="csb-layout-wrapper">
        <!-- Backdrop for mobile sidebar drawer -->
        <div class="csb-sidebar-backdrop" id="csbSidebarBackdrop" onclick="window.closeMobileSidebar()"></div>

        <!-- Left Charcoal Sidebar -->
        <aside class="csb-sidebar" id="csbSidebar">
            <!-- Mobile Drawer Header -->
            <div class="csb-mobile-sidebar-header">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('assets/institutions/clevelandstatebank/images/logo-white.png') }}" alt="Cleveland State Bank" style="height: 24px; max-width: 140px; object-fit: contain;">
                </div>
                <button type="button" class="btn-close btn-close-white" id="csbSidebarCloseBtn" onclick="window.closeMobileSidebar()" aria-label="Close"></button>
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
                        <a href="javascript:void(0)" class="csb-nav-link" data-bs-toggle="modal" data-bs-target="#csbDocumentsModal">
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
                        <a href="javascript:void(0)" class="csb-nav-link" data-bs-toggle="modal" data-bs-target="#csbAlertPreferencesModal">
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
                    <a href="javascript:void(0)" class="action-link" onclick="window.csbOpenManageFavorites()"><i class="fa-regular fa-star"></i> Manage Favorites</a>
                    <a href="javascript:void(0)" class="action-link" onclick="window.csbOpenManageTiles()"><i class="fa-solid fa-table-cells-large"></i> Manage Tiles</a>
                </div>
            </div>

            @php
                $currency = '$';
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
                    <div class="summary-amount" data-csb-summary="deposits">{{ $currency }}{{ number_format($totalDepositAmount, 2) }}</div>
                </div>
                <div class="summary-block">
                    <div class="summary-label">Total Loans {{ $loanAccountCount }} {{ $loanAccountCount === 1 ? 'Account' : 'Accounts' }}</div>
                    <div class="summary-amount" data-csb-summary="loans">{{ $currency }}{{ number_format($totalLoanSum, 2) }}</div>
                </div>
            </div>

            <!-- Account Tiles Grid (Checking + Savings + IRA + HELOC + Credit Card + Loan Account) -->
            <div class="account-tiles-grid">
                <!-- Checking Card 3D Flip Tile -->
                <div class="csb-account-card" role="button" onclick="csbHandleTileTap(this, event, 'Personal Checking', '{{ $user->account_number ?? '1397' }}', {{ $checkingBal }}, 0, 'checking')">
                    <div class="csb-account-card-inner">
                        <!-- Front Face: Available Balance -->
                        <div class="csb-account-card-front">
                            <div class="tile-header">
                                <span class="tile-name">Checking <i class="fa-solid fa-star star-icon"></i></span>
                                <span class="tile-account-no">*{{ substr($user->account_number ?? '1397', -4) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div class="tile-balance" data-csb-balance="checking">
                                    {{ $currency }}{{ number_format($checkingBal, 2) }}
                                </div>
                                <span class="badge bg-light text-muted border extra-small" style="font-size: 10px;">Available</span>
                            </div>
                        </div>
                        <!-- Back Face: Ledger Balance & Routing -->
                        <div class="csb-account-card-back">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="ledger-title">LEDGER BALANCE</span>
                                <span class="ledger-badge">POSTED</span>
                            </div>
                            <div class="ledger-amount" data-csb-ledger="checking">
                                {{ $currency }}{{ number_format($checkingBal, 2) }}
                            </div>
                            <div class="ledger-meta">
                                <span>Routing: 084301768</span>
                                <span>Holds: $0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Primary Savings 3D Flip Tile -->
                @if($hasSavings)
                <div class="csb-account-card" role="button" onclick="csbHandleTileTap(this, event, 'Primary Savings', '{{ $user->savings_account_number ?? $user->account_number ?? '4902' }}', {{ $savingsBal }}, 0, 'savings')">
                    <div class="csb-account-card-inner">
                        <div class="csb-account-card-front">
                            <div class="tile-header">
                                <span class="tile-name">Primary Savings <i class="fa-solid fa-star star-icon"></i></span>
                                <span class="tile-account-no">*{{ substr($user->savings_account_number ?? $user->account_number ?? '4902', -4) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div class="tile-balance" data-csb-balance="savings">
                                    {{ $currency }}{{ number_format($savingsBal, 2) }}
                                </div>
                                <span class="badge bg-light text-muted border extra-small" style="font-size: 10px;">Available</span>
                            </div>
                        </div>
                        <div class="csb-account-card-back">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="ledger-title">LEDGER BALANCE</span>
                                <span class="ledger-badge">POSTED</span>
                            </div>
                            <div class="ledger-amount" data-csb-ledger="savings">
                                {{ $currency }}{{ number_format($savingsBal, 2) }}
                            </div>
                            <div class="ledger-meta">
                                <span>Routing: 084301768</span>
                                <span>Holds: $0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- IRA 3D Flip Tile -->
                @if($hasIra)
                <div class="csb-account-card" role="button" onclick="csbHandleTileTap(this, event, 'IRA Account', '{{ $user->ira_account_number ?? $user->account_number ?? '0000' }}', {{ $iraBal }}, 0, 'ira')">
                    <div class="csb-account-card-inner">
                        <div class="csb-account-card-front">
                            <div class="tile-header">
                                <span class="tile-name">IRA Account <i class="fa-solid fa-piggy-bank text-muted ms-1"></i></span>
                                <span class="tile-account-no">*{{ substr($user->ira_account_number ?? $user->account_number ?? '0000', -4) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div class="tile-balance" data-csb-balance="ira">
                                    {{ $currency }}{{ number_format($iraBal, 2) }}
                                </div>
                                <span class="badge bg-light text-muted border extra-small" style="font-size: 10px;">Available</span>
                            </div>
                        </div>
                        <div class="csb-account-card-back">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="ledger-title">LEDGER BALANCE</span>
                                <span class="ledger-badge">POSTED</span>
                            </div>
                            <div class="ledger-amount" data-csb-ledger="ira">
                                {{ $currency }}{{ number_format($iraBal, 2) }}
                            </div>
                            <div class="ledger-meta">
                                <span>Routing: 084301768</span>
                                <span>Holds: $0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- HELOC 3D Flip Tile -->
                @if($hasHeloc)
                <div class="csb-account-card" role="button" onclick="csbHandleTileTap(this, event, 'Home Equity Line of Credit', '{{ $user->heloc_account_number ?? $user->account_number ?? '0000' }}', {{ $helocBal }}, {{ (float)($user->heloc_credit_limit ?? 0) }}, 'heloc')">
                    <div class="csb-account-card-inner">
                        <div class="csb-account-card-front">
                            <div class="tile-header">
                                <span class="tile-name">HELOC <i class="fa-solid fa-house-chimney text-muted ms-1"></i></span>
                                <span class="tile-account-no">*{{ substr($user->heloc_account_number ?? $user->account_number, -4) }}H00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div class="tile-balance" data-csb-balance="heloc">
                                    {{ $currency }}{{ number_format($helocBal, 2) }}
                                </div>
                                <span class="badge bg-light text-muted border extra-small" style="font-size: 10px;">Current Balance</span>
                            </div>
                        </div>
                        <div class="csb-account-card-back">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="ledger-title">AVAILABLE CREDIT</span>
                                <span class="badge bg-success-subtle text-success fw-bold" style="font-size: 10px;">LINE</span>
                            </div>
                            <div class="ledger-amount text-success">
                                {{ $currency }}{{ number_format(max(0, (float)($user->heloc_credit_limit ?? 0) - (float)$helocBal), 2) }}
                            </div>
                            <div class="ledger-meta">
                                <span>Limit: {{ $currency }}{{ number_format((float)($user->heloc_credit_limit ?? 0), 2) }}</span>
                                <span>Drawn: {{ $currency }}{{ number_format($helocBal, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Credit Card 3D Flip Tile -->
                @if($hasCc)
                <div class="csb-account-card" role="button" onclick="csbHandleTileTap(this, event, 'Platinum Rewards Credit Card', '{{ $user->cc_account_number ?? $user->account_number ?? '9538' }}', {{ $ccBal }}, {{ $ccLimit }}, 'cc')">
                    <div class="csb-account-card-inner">
                        <div class="csb-account-card-front">
                            <div class="tile-header">
                                <span class="tile-name">Credit Card <i class="fa-solid fa-credit-card text-muted ms-1"></i></span>
                                <span class="tile-account-no">*{{ substr($user->cc_account_number ?? $user->account_number ?? '9538', -4) }}C00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div class="tile-balance" data-csb-balance="cc">
                                    {{ $currency }}{{ number_format($ccBal, 2) }}
                                </div>
                                <span class="badge bg-light text-muted border extra-small" style="font-size: 10px;">Current Balance</span>
                            </div>
                        </div>
                        <div class="csb-account-card-back">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="ledger-title">AVAILABLE CREDIT</span>
                                <span class="badge bg-success-subtle text-success fw-bold" style="font-size: 10px;">CARD</span>
                            </div>
                            <div class="ledger-amount text-success">
                                {{ $currency }}{{ number_format(max(0, (float)$ccLimit - (float)$ccBal), 2) }}
                            </div>
                            <div class="ledger-meta">
                                <span>Limit: {{ $currency }}{{ number_format($ccLimit, 2) }}</span>
                                <span>Drawn: {{ $currency }}{{ number_format($ccBal, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Loan Account 3D Flip Tile -->
                @if($hasLoan)
                <div class="csb-account-card" role="button" onclick="csbHandleTileTap(this, event, 'Personal Loan Account', '{{ $user->loan_account_number ?? $user->account_number ?? '8859' }}', {{ $loanBal }}, {{ $loanOrig }}, 'loan')">
                    <div class="csb-account-card-inner">
                        <div class="csb-account-card-front">
                            <div class="tile-header">
                                <span class="tile-name">Loan Account <i class="fa-solid fa-file-contract text-muted ms-1"></i></span>
                                <span class="tile-account-no">*{{ substr($user->loan_account_number ?? $user->account_number ?? '8859', -4) }}L00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div class="tile-balance" data-csb-balance="loan">
                                    {{ $currency }}{{ number_format($loanBal, 2) }}
                                </div>
                                <span class="badge bg-light text-muted border extra-small" style="font-size: 10px;">Principal Due</span>
                            </div>
                        </div>
                        <div class="csb-account-card-back">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="ledger-title">PRINCIPAL PAYOFF</span>
                                <span class="badge bg-info-subtle text-info fw-bold" style="font-size: 10px;">LOAN</span>
                            </div>
                            <div class="ledger-amount text-info" data-csb-ledger="loan">
                                {{ $currency }}{{ number_format($loanBal, 2) }}
                            </div>
                            <div class="ledger-meta">
                                <span>Fixed Rate</span>
                                <span>Original: {{ $currency }}{{ number_format($loanOrig, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Two Column Lower Cards -->
            <div class="dashboard-grid-2">
                <!-- Left: Recent Transactions -->
                <div class="csb-widget-card" id="recentTxnWidgetCard">
                    <div class="widget-header d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">Recent Transactions</h3>
                        <!-- 3-Dot Dropdown Menu (Matching original bank design) -->
                        <div class="dropdown">
                            <button class="btn btn-sm text-secondary border-0 p-1 shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px; border-radius: 50%;">
                                <i class="fa-solid fa-ellipsis-vertical fs-5"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="border-radius: 8px; font-size: 13px;">
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="window.csbToggleTxnSearch()">
                                        <i class="fa-solid fa-magnifying-glass text-muted" style="width: 16px;"></i> Search Transactions
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="csbExportClientCsv()">
                                        <i class="fa-solid fa-file-csv text-success" style="width: 16px;"></i> Export to CSV
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-muted" href="javascript:void(0)" onclick="$('#recentTxnWidgetCard').slideToggle(200)">
                                        <i class="fa-regular fa-eye-slash" style="width: 16px;"></i> Hide Tile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-muted" href="javascript:void(0)" onclick="window.csbOpenManageTiles()">
                                        <i class="fa-solid fa-table-cells-large" style="width: 16px;"></i> Manage Tiles
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Clean Account Selector (Matching Mobile App Screenshot) -->
                    <div class="px-3 pt-3 pb-2">
                        <select class="form-select border shadow-none fw-semibold" id="csbTxnAccountSelect" onchange="csbOnAccountSelectChanged(this.value)" style="border-radius: 6px; font-size: 14px; background-color: #ffffff; color: #1e293b;">
                            <option value="all" selected>All Accounts</option>
                            <option value="checking">Checking *{{ substr($user->account_number ?? '1397', -4) }}</option>
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
                    </div>

                    <!-- Collapsible Search Box -->
                    <div id="csbTxnSearchToggleWrap" class="px-3 pb-2" style="display: none;">
                        <div class="position-relative">
                            <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; pointer-events: none;"></i>
                            <input type="text" id="csbTxnLiveSearch" class="form-control form-control-sm ps-4 shadow-none" placeholder="Search merchant, amount, or date..." oninput="csbFilterTxnLive(this.value)" style="border-radius: 6px;">
                        </div>
                    </div>
                    <div class="widget-body">
                        <!-- Shimmer Skeleton Placeholder (Used when switching views) -->
                        <div id="csbTxnSkeletonWrap" class="d-none py-2">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div style="flex: 1;"><div class="csb-skeleton csb-skeleton-text" style="width: 55%;"></div><div class="csb-skeleton csb-skeleton-text" style="width: 25%;"></div></div>
                                <div class="csb-skeleton csb-skeleton-text" style="width: 20%; height: 16px;"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div style="flex: 1;"><div class="csb-skeleton csb-skeleton-text" style="width: 65%;"></div><div class="csb-skeleton csb-skeleton-text" style="width: 30%;"></div></div>
                                <div class="csb-skeleton csb-skeleton-text" style="width: 22%; height: 16px;"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <div style="flex: 1;"><div class="csb-skeleton csb-skeleton-text" style="width: 50%;"></div><div class="csb-skeleton csb-skeleton-text" style="width: 20%;"></div></div>
                                <div class="csb-skeleton csb-skeleton-text" style="width: 18%; height: 16px;"></div>
                            </div>
                        </div>

                        @if(isset($recentTransactions) && count($recentTransactions) > 0)
                            <div class="transaction-list-table">
                                @foreach($recentTransactions as $tx)
                                @php
                                    $rawType = $tx->type instanceof \BackedEnum ? $tx->type->value : (string)($tx->type ?? '');
                                    $typeLower = strtolower($rawType);
                                    $incomingTypes = ['deposit', 'manual_deposit', 'receive_money', 'refund', 'signup_bonus', 'portfolio_bonus', 'referral', 'reward_redeem'];
                                    $isIncoming = in_array($typeLower, $incomingTypes) && ((float)($tx->amount ?? 0) > 0);
                                    $isNegative = !$isIncoming;
                                    $wType = strtolower((string)($tx->wallet_type ?? 'checking'));
                                    if ($wType === 'default' || empty($wType)) $wType = 'checking';
                                    if (in_array($wType, ['primary_savings', 'savings_primary'])) $wType = 'savings';
                                    if (in_array($wType, ['credit_card'])) $wType = 'cc';
                                    if (in_array($wType, ['loan_account'])) $wType = 'loan';
                                    $rawDesc = $tx->description ?? (ucfirst(str_replace('_', ' ', $rawType)) ?: 'Transaction');
                                    $txAmtStr = ($isNegative ? '-' : '+') . $currency . number_format(abs((float)($tx->amount ?? 0)), 2);
                                    
                                    $txCreatedAt = null;
                                    try {
                                        if ($tx->created_at instanceof \Carbon\Carbon) {
                                            $txCreatedAt = $tx->created_at;
                                        } elseif (!empty($tx->created_at)) {
                                            $txCreatedAt = \Carbon\Carbon::parse($tx->created_at);
                                        }
                                    } catch (\Throwable $te) {
                                        $txCreatedAt = null;
                                    }
                                    $txDateStr = $txCreatedAt ? $txCreatedAt->format('M d, Y h:i A') : date('M d, Y');
                                    $txDateBadge = $txCreatedAt ? $txCreatedAt->format('m/d/Y') : date('m/d/Y');
                                    
                                    $rawStatus = $tx->status instanceof \BackedEnum ? $tx->status->value : (string)($tx->status ?? 'completed');
                                    $txStatusStr = ucfirst($rawStatus);
                                    $txMethodStr = ucfirst(str_replace('_', ' ', $rawType));
                                    $txReceiptUrl = (!empty($tx->tnx) && \Illuminate\Support\Facades\Route::has('user.transactions.receipt')) ? route('user.transactions.receipt', $tx->tnx) : '#';
                                    $txDisputeUrl = (!empty($tx->tnx) && \Illuminate\Support\Facades\Route::has('user.disputes.create')) ? route('user.disputes.create', $tx->tnx) : '#';
                                    $catData = match(true) {
                                        str_contains($typeLower, 'zelle') => ['icon' => 'fa-solid fa-bolt', 'bg' => '#f3e8ff', 'color' => '#7e22ce'],
                                        str_contains($typeLower, 'wire') || str_contains($typeLower, 'external') || str_contains($typeLower, 'ach') => ['icon' => 'fa-solid fa-building-columns', 'bg' => '#fee2e2', 'color' => '#c8232c'],
                                        str_contains($typeLower, 'member') => ['icon' => 'fa-solid fa-user-group', 'bg' => '#ecfdf5', 'color' => '#059669'],
                                        $isIncoming => ['icon' => 'fa-solid fa-arrow-down', 'bg' => '#ecfdf5', 'color' => '#16a34a'],
                                        str_contains($typeLower, 'card') => ['icon' => 'fa-solid fa-credit-card', 'bg' => '#e0e7ff', 'color' => '#4338ca'],
                                        str_contains($typeLower, 'loan') => ['icon' => 'fa-solid fa-file-invoice-dollar', 'bg' => '#fef3c7', 'color' => '#d97706'],
                                        default => ['icon' => 'fa-solid fa-arrow-right-arrow-left', 'bg' => '#f1f5f9', 'color' => '#1b325f'],
                                    };
                                @endphp
                                <div class="txn-row" data-account="{{ $wType }}" style="cursor: pointer;"
                                     data-tx-id="{{ $tx->id }}"
                                     data-tx-desc="{{ e($rawDesc) }}"
                                     data-tx-trx="{{ $tx->tnx ?? '' }}"
                                     data-tx-amount="{{ $txAmtStr }}"
                                     data-tx-date="{{ $txDateStr }}"
                                     data-tx-status="{{ $txStatusStr }}"
                                     data-tx-method="{{ $txMethodStr }}"
                                     data-tx-receipt="{{ $txReceiptUrl }}"
                                     data-tx-dispute="{{ $txDisputeUrl }}"
                                     data-tx-note="{{ e($tx->member_notes ?? '') }}"
                                     data-tx-attachment="{{ $tx->receipt_attachment ? asset($tx->receipt_attachment) : '' }}"
                                     onclick="csbShowTxnFromEl(this, event)"
                                     title="Click to view details, add note/receipt, or dispute">
                                    <div class="txn-main-card d-flex align-items-center justify-content-between w-100">
                                        <div class="d-flex align-items-center gap-2" style="flex: 1 1 auto; min-width: 0; padding-right: 8px;">
                                            <div class="txn-cat-icon rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; min-width: 36px; background: {{ $catData['bg'] }}; color: {{ $catData['color'] }}; font-size: 14px;">
                                                <i class="{{ $catData['icon'] }}"></i>
                                            </div>
                                            <div style="min-width: 0;">
                                                <div class="txn-desc d-flex align-items-center gap-1">
                                                    <span class="text-truncate">{{ $tx->description ?? (ucfirst(str_replace('_', ' ', $rawType)) ?: 'Transaction') }}</span>
                                                    @if(!empty($tx->receipt_attachment))
                                                        <i class="fa-solid fa-paperclip text-muted small" title="Receipt attached" style="font-size: 11px;"></i>
                                                    @endif
                                                    @if(!empty($tx->member_notes))
                                                        <i class="fa-solid fa-note-sticky text-warning small" title="Note added" style="font-size: 11px;"></i>
                                                    @endif
                                                </div>
                                                <div class="txn-date d-flex align-items-center gap-1">
                                                    <span class="badge bg-light text-muted border" style="font-weight: 500; font-size: 10px;">{{ $txDateBadge }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end" style="flex-shrink: 0; white-space: nowrap;">
                                            <div class="txn-amount {{ $isNegative ? 'negative' : 'positive' }}">
                                                @if($isNegative)
                                                    -{{ $currency }}{{ number_format(abs((float)($tx->amount ?? 0)), 2) }}
                                                @else
                                                    +{{ $currency }}{{ number_format(abs((float)($tx->amount ?? 0)), 2) }}
                                                @endif
                                            </div>
                                            <div class="small txn-status-badge" style="font-size: 0.72rem; font-weight: 600; margin-top: 2px;">
                                                @if(in_array(strtolower($rawStatus), ['pending', '2']))
                                                    <span style="color: #d97706;"><i class="fa-solid fa-clock me-1 small"></i>Pending</span>
                                                @elseif(in_array(strtolower($rawStatus), ['success', 'completed', '1']))
                                                    <span style="color: #059669;"><i class="fa-solid fa-circle-check me-1 small"></i>Completed</span>
                                                @else
                                                    <span class="text-danger"><i class="fa-solid fa-circle-xmark me-1 small"></i>Failed</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Mobile Expandable Settlement Details -->
                                    <div class="txn-settlement-details">
                                        <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Ref #:</span> <span class="font-monospace fw-bold">{{ $tx->tnx ?? 'N/A' }}</span></div>
                                        <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Account:</span> <span class="text-capitalize">{{ $wType }}</span></div>
                                        <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Type:</span> <span>{{ $txMethodStr }}</span></div>
                                        @if(!empty($tx->member_notes))
                                        <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Notes:</span> <span>{{ $tx->member_notes }}</span></div>
                                        @endif
                                        @php
                                            $txRawStatusLower = strtolower($rawStatus);
                                            $txIsSettled = in_array($txRawStatusLower, ['success', 'completed', '1'], true);
                                            $txDisputableTypes = (class_exists(\App\Services\DisputeService::class) && defined('\App\Services\DisputeService::DEBIT_DISPUTABLE_TYPES'))
                                                ? \App\Services\DisputeService::DEBIT_DISPUTABLE_TYPES
                                                : ['fund_transfer', 'wire_transfer', 'bill_pay'];
                                            $txIsDisputableDebit = $isNegative && (
                                                in_array($typeLower, $txDisputableTypes, true) ||
                                                str_contains($typeLower, 'transfer') ||
                                                str_contains($typeLower, 'wire') ||
                                                str_contains($typeLower, 'zelle') ||
                                                str_contains($typeLower, 'bill')
                                            );
                                            $txWithin90Days = (!$txCreatedAt || $txCreatedAt->gte(now()->subDays(90)));
                                            $txIsEligibleForDispute = $txIsSettled && $txIsDisputableDebit && $txWithin90Days && !str_starts_with(strtoupper((string)($tx->tnx ?? '')), 'RECON-');
                                        @endphp
                                        <div class="d-flex justify-content-between align-items-center pt-2 gap-2 flex-wrap">
                                            <div class="d-flex align-items-center gap-2">
                                                @if(!empty($tx->tnx))
                                                <a href="{{ $txReceiptUrl }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 11px;" onclick="event.stopPropagation();">
                                                    <i class="fa-solid fa-receipt me-1"></i> Receipt
                                                </a>
                                                @endif
                                                @if($txIsEligibleForDispute)
                                                <a href="{{ $txDisputeUrl }}" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 11px; font-weight: 600;" onclick="event.stopPropagation();" title="File a Regulation E Dispute for this transaction">
                                                    <i class="fa-solid fa-flag me-1"></i> Dispute
                                                </a>
                                                @endif
                                            </div>
                                            <span class="text-muted" style="font-size: 11px;"><i class="fa-solid fa-clock me-1"></i>{{ $txDateStr }}</span>
                                        </div>
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
                            <form id="csbFormSelf" class="csb-transfer-form" action="{{ route('user.fund_transfer.transfer') }}" method="POST">
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
                                                Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}) — ${{ number_format((float)($user->savings_balance ?? 0), 2) }}
                                            </option>
                                            @if($user->ira_status == 1)
                                            <option value="ira" data-balance="{{ $user->ira_balance ?? 0 }}">
                                                IRA (*{{ substr($user->ira_account_number ?? $user->account_number, -4) }}) — ${{ number_format((float)($user->ira_balance ?? 0), 2) }}
                                            </option>
                                            @endif
                                            @if($user->heloc_status == 1)
                                            <option value="heloc" data-balance="{{ ($user->heloc_credit_limit ?? 0) - ($user->heloc_balance ?? 0) }}">
                                                HELOC (*{{ substr($user->heloc_account_number ?? $user->account_number, -4) }}) — Available: ${{ number_format(($user->heloc_credit_limit ?? 0) - ($user->heloc_balance ?? 0), 2) }}
                                            </option>
                                            @endif
                                            @if($user->cc_status == 1)
                                            <option value="cc" data-balance="{{ ($user->cc_credit_limit ?? 0) - ($user->cc_balance ?? 0) }}">
                                                Credit Card (*{{ substr($user->cc_account_number ?? $user->account_number, -4) }}) — Available: ${{ number_format(($user->cc_credit_limit ?? 0) - ($user->cc_balance ?? 0), 2) }}
                                            </option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">To Account</label>
                                        <select name="to_wallet" id="csbSelfToWallet" class="form-select shadow-none" required>
                                            <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }})</option>
                                            <option value="default">Checking (*{{ substr($user->account_number ?? '1397', -4) }})</option>
                                            @if($user->ira_status == 1)
                                            <option value="ira">IRA (*{{ substr($user->ira_account_number ?? $user->account_number, -4) }})</option>
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
                            <form id="csbFormMember" class="csb-transfer-form" action="{{ route('user.fund_transfer.transfer') }}" method="POST">
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
                                            <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}) — ${{ number_format((float)($user->savings_balance ?? 0), 2) }}</option>
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
                            <form id="csbFormExternal" class="csb-transfer-form" action="{{ route('user.fund_transfer.transfer') }}" method="POST">
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
                                            <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}) — ${{ number_format((float)($user->savings_balance ?? 0), 2) }}</option>
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
                            <form id="csbFormWire" class="csb-wire-form" action="{{ route('user.fund_transfer.transfer.wire.post') }}" method="POST">
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
                                            <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}) — ${{ number_format((float)($user->savings_balance ?? 0), 2) }}</option>
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
                            <form id="csbFormZelle" class="csb-zelle-form" action="{{ route('user.fund_transfer.zelle.submit') }}" method="POST">
                                @csrf
                                <div class="p-3 mb-3 text-center rounded-3" style="background: linear-gradient(135deg, #1b325f 0%, #7414ca 100%); color:#fff;">
                                    <div class="d-flex align-items-center justify-content-center gap-3 mb-1">
                                        <img src="{{ (app()->bound('institution') && app('institution')->logo) ? app('institution')->logo : asset('assets/institutions/clevelandstatebank/images/logo-white.png') }}" 
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
                                            <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}) — ${{ number_format((float)($user->savings_balance ?? 0), 2) }}</option>
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

    {{-- ── 2. REMOTE CHECK DEPOSIT (Two-Step Flow: Capture -> Review & Dedicated History) ── --}}
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
                        <input type="hidden" name="security_verified" value="1">

                        <!-- Step 1: Capture Step -->
                        <div id="rdCaptureStep">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="small text-muted fw-bold text-uppercase mb-1">Deposit To Account</label>
                                    <select name="account_id" id="csbRdAccountSelect" class="form-select border shadow-none fw-bold" style="font-size: 13.5px;" required>
                                        <option value="checking" data-label="Personal Checking (*{{ substr($user->account_number ?? '1397', -4) }})">
                                            Personal Checking (*{{ substr($user->account_number ?? '1397', -4) }}) — ${{ number_format((float)($user->balance ?? 0), 2) }}
                                        </option>
                                        @if(!empty($user->savings_account_number) || $user->savings_balance !== null)
                                        <option value="savings" data-label="Primary Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }})">
                                            Primary Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}) — ${{ number_format((float)($user->savings_balance ?? 0), 2) }}
                                        </option>
                                        @endif
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
                                    <div class="csb-check-capture-card text-center p-3 rounded w-100" onclick="document.getElementById('csbFrontFile').click();" style="border: 2px dashed #cfd8dc; background: #fbfbfb; cursor: pointer; min-height: 155px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; transition: all 0.2s;">
                                        <div id="csbFrontPreviewWrap" class="d-none position-absolute top-0 start-0 w-100 h-100 p-1 bg-white rounded" style="pointer-events: none;">
                                            <img id="csbFrontPreview" src="" class="w-100 h-100 rounded" style="object-fit: contain;">
                                        </div>
                                        <div class="capture-inner" style="pointer-events: none;">
                                            <i class="fa-solid fa-camera fa-2x mb-2" style="color: #c8232c;"></i>
                                            <div class="fw-bold text-dark small">Front of Check</div>
                                            <div class="text-muted" style="font-size: 11px;">Tap to capture or upload</div>
                                        </div>
                                        <input type="file" id="csbFrontFile" name="front_image" class="d-none" accept="image/*,.pdf" required onchange="csbPreviewImage(this, 'csbFrontPreview', 'csbFrontPreviewWrap')">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted fw-bold text-uppercase mb-1">Back of Check (Endorsed)</label>
                                    <div class="csb-check-capture-card text-center p-3 rounded w-100" onclick="document.getElementById('csbBackFile').click();" style="border: 2px dashed #cfd8dc; background: #fbfbfb; cursor: pointer; min-height: 155px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; transition: all 0.2s;">
                                        <div id="csbBackPreviewWrap" class="d-none position-absolute top-0 start-0 w-100 h-100 p-1 bg-white rounded" style="pointer-events: none;">
                                            <img id="csbBackPreview" src="" class="w-100 h-100 rounded" style="object-fit: contain;">
                                        </div>
                                        <div class="capture-inner" style="pointer-events: none;">
                                            <i class="fa-solid fa-signature fa-2x mb-2" style="color: #c8232c;"></i>
                                            <div class="fw-bold text-dark small">Back of Check</div>
                                            <div class="text-muted" style="font-size: 11px;">Tap to capture or upload</div>
                                        </div>
                                        <input type="file" id="csbBackFile" name="back_image" class="d-none" accept="image/*,.pdf" required onchange="csbPreviewImage(this, 'csbBackPreview', 'csbBackPreviewWrap')">
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

                            <button type="button" class="btn w-100 fw-bold text-white py-2" style="background:#28623b; font-size: 14px;" onclick="csbProceedToReviewDeposit()">
                                REVIEW CHECK DEPOSIT &rarr;
                            </button>
                        </div>

                        <!-- Step 2: Review & Confirm Step -->
                        <div id="rdReviewStep" class="d-none">
                            <div class="p-3 rounded-3 bg-light border mb-3">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-clipboard-check text-success me-2"></i>Review Deposit Details</h6>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="text-muted small">Deposit To Account</span>
                                    <span class="fw-bold text-dark small" id="rdReviewAccountName">—</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="text-muted small">Check Amount</span>
                                    <span class="fw-bold fs-5 text-success" id="rdReviewAmountDisplay">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <span class="text-muted small">Estimated Availability</span>
                                    <span class="fw-semibold text-dark small">1–2 Business Days</span>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="extra-small fw-bold text-muted text-uppercase mb-1">Front Check Photo</label>
                                    <div class="border rounded p-1 bg-white" style="height: 100px; display: flex; align-items: center; justify-content: center;">
                                        <img id="rdReviewFrontImg" src="" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="extra-small fw-bold text-muted text-uppercase mb-1">Back Check Photo</label>
                                    <div class="border rounded p-1 bg-white" style="height: 100px; display: flex; align-items: center; justify-content: center;">
                                        <img id="rdReviewBackImg" src="" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary w-50 py-2 fw-semibold" onclick="csbBackToDepositCapture()">
                                    &larr; Back to Edit
                                </button>
                                <button type="submit" id="rdSubmitBtn" class="btn w-50 fw-bold text-white py-2" style="background:#28623b; font-size: 14px;">
                                    <i class="fa-solid fa-check me-1"></i> CONFIRM &amp; SUBMIT
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Success Receipt Step -->
                        <div id="rdReceiptStep" class="d-none text-center py-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px; background: rgba(40, 167, 69, 0.1); color: #28a745; font-size: 26px;">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Check Deposit Submitted!</h5>
                            <p class="text-muted small mb-3">Your check has been securely queued and is currently under review by our operations team.</p>
                            
                            <div class="p-3 rounded-3 bg-light border text-start mb-3 mx-auto" style="max-width: 400px;">
                                <div class="d-flex justify-content-between py-1 border-bottom small">
                                    <span class="text-muted">Deposit Reference:</span>
                                    <span class="fw-bold text-dark" id="rdReceiptTnx">—</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom small">
                                    <span class="text-muted">Deposited Amount:</span>
                                    <span class="fw-bold text-success" id="rdReceiptAmount">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 small">
                                    <span class="text-muted">Account:</span>
                                    <span class="fw-bold text-dark" id="rdReceiptAccount">—</span>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="csbResetDepositModal()">
                                Done
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="javascript:void(0)" onclick="csbOpenDepositHistory()" class="small text-muted text-decoration-none">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> View Full Deposit History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2B. CSB DEDICATED DEPOSIT HISTORY MODAL (Zero Leakage to FrontField) ── --}}
    <div class="modal fade" id="csbDepositHistoryModal" tabindex="-1" aria-hidden="true" style="z-index: 2060;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header py-3 px-4" style="background:#162846; color:#ffffff;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left" style="color: #c8232c;"></i>
                        <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 15px;">REMOTE CHECK DEPOSIT HISTORY</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Account</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-end">Reference</th>
                                </tr>
                            </thead>
                            <tbody id="csbDepositHistoryBody">
                                @forelse($recentCheckDeposits ?? [] as $rd)
                                    <tr>
                                        <td class="small text-muted">{{ $rd->created_at?->format('M d, Y') }}</td>
                                        <td class="fw-semibold small">{{ $rd->account_name ?? 'Checking' }}</td>
                                        <td class="fw-bold text-success">${{ number_format((float)($rd->amount ?? 0), 2) }}</td>
                                        <td>
                                            @if($rd->status === 'approved' || $rd->status === 'completed')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($rd->status === 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-muted small font-monospace">{{ $rd->transaction_tnx ?? ('DEP' . $rd->id) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-camera fa-2x mb-2 opacity-50"></i>
                                            <div class="small fw-semibold">No check deposits recorded yet.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="csbBackToDepositModal()">
                        &larr; New Deposit
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
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

                    <!-- Receipt Attachment & Member Note Section -->
                    <div class="mt-3 p-3 text-start rounded-3 border bg-white" style="box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <input type="hidden" id="csbTxnModalId" value="">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="fw-bold text-dark small mb-0">
                                <i class="fa-solid fa-note-sticky me-1" style="color: #c8232c;"></i> Notes &amp; Receipt Attachment
                            </label>
                            <span class="badge bg-light text-secondary border small" style="font-size: 10px;">Tax &amp; Expenses</span>
                        </div>
                        
                        <div class="mb-2">
                            <textarea id="csbTxnMemberNote" class="form-control form-control-sm" rows="2" placeholder="Add expense notes (e.g. Travel reimbursement, tax deductible receipt)..." style="font-size: 12.5px; border-radius: 8px;"></textarea>
                        </div>

                        <!-- Existing Attachment Preview if present -->
                        <div id="csbTxnAttachmentBox" class="mb-2 d-none">
                            <div class="p-2 bg-light rounded border d-flex align-items-center justify-content-between small">
                                <div class="d-flex align-items-center gap-2 overflow-hidden" style="max-width: 80%;">
                                    <i class="fa-solid fa-paperclip text-primary"></i>
                                    <a href="#" target="_blank" id="csbTxnAttachmentLink" class="text-truncate text-primary fw-semibold" style="font-size: 11.5px;">receipt_file</a>
                                </div>
                                <span class="badge bg-success text-white small" style="font-size: 10px;">Attached</span>
                            </div>
                        </div>

                        <!-- File Input & Save Button -->
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <label for="csbTxnReceiptFileInput" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 mb-0 d-inline-flex align-items-center gap-1" style="font-size: 11.5px; cursor: pointer;">
                                <i class="fa-solid fa-cloud-arrow-up" style="color: #c8232c;"></i>
                                <span id="csbTxnFileLabel">Attach Receipt / Invoice</span>
                            </label>
                            <input type="file" id="csbTxnReceiptFileInput" accept="image/jpeg,image/png,image/webp,application/pdf" class="d-none" onchange="csbOnReceiptFileChosen(this)">
                            <button type="button" class="btn btn-sm text-white rounded-pill px-3 py-1 ms-auto fw-bold" id="csbBtnSaveReceipt" onclick="csbSaveTxnReceiptNote()" style="background: #c8232c; font-size: 12px;">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save
                            </button>
                        </div>
                        <div id="csbTxnSaveFeedback" class="small mt-1 text-success d-none"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 gap-2 flex-column">
                    <a href="#" id="csbBtnDownloadReceipt" class="btn w-100 rounded-pill py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm text-white" style="background: #c8232c; border: none;">
                        <i class="fa-solid fa-file-pdf"></i> Download Official Receipt
                    </a>
                    <a href="#" id="csbBtnDisputeTxn" class="btn w-100 rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 d-none" style="background: #ffffff; color: #1e293b; border: 1px solid #cbd5e1;">
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

    {{-- ── 7. TRANSFER SUCCESS CONFIRMATION MODAL (BANK-GRADE OFFICIAL VOUCHER) ── --}}
    <div class="modal fade" id="csbTransferSuccessModal" tabindex="-1" aria-hidden="true" style="z-index: 2060;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
            <div class="modal-content border-0 shadow-lg text-center p-4" style="border-radius: 18px; border-top: 5px solid #c8232c !important;">
                <!-- Official Bank Header & Trust Badges -->
                <div class="d-flex align-items-center justify-content-between pb-3 mb-2 border-bottom">
                    <div class="text-start">
                        <div class="fw-bold" style="color: #162846; font-size: 15px; letter-spacing: 0.3px;">CLEVELAND STATE BANK</div>
                        <div class="text-muted" style="font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Official Settlement Voucher</div>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge bg-light text-dark border px-2 py-1 font-monospace" style="font-size: 10px;" title="ABA Routing Number"><i class="fa-solid fa-building-columns text-danger me-1"></i>ABA #084301768</span>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-bold" style="font-size: 10px;"><i class="fa-solid fa-building-columns me-1"></i>FDIC</span>
                    </div>
                </div>

                <div class="mx-auto my-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 68px; height: 68px; background: rgba(5, 150, 105, 0.12); color: #059669; font-size: 30px; box-shadow: 0 0 0 6px rgba(5, 150, 105, 0.05);" id="csbTransferConfirmIconWrap">
                    <i class="fa-solid fa-circle-check" id="csbTransferConfirmIcon"></i>
                </div>
                <h4 class="fw-bold mb-1" id="csbTransferConfirmTitle" style="color: #162846; font-size: 20px;">Transfer Successful</h4>
                <p class="text-secondary small mb-3" id="csbTransferConfirmMsg" style="font-size: 13px;">Your transfer has been processed and submitted successfully.</p>
                
                <div class="display-6 fw-bold mb-3" id="csbTransferConfirmAmount" style="color: #162846; font-size: 2.1rem; letter-spacing: -0.5px;">$0.00</div>

                <div class="p-3 rounded-3 bg-light text-start mb-3 border" style="font-size: 12.5px;">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted fw-semibold">Confirmation Ref</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold font-monospace px-2 py-0.5 rounded bg-white border text-dark" id="csbTransferConfirmTrx" style="font-size: 12px; letter-spacing: 0.5px;">TRX...</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary p-1 lh-1" onclick="navigator.clipboard.writeText($('#csbTransferConfirmTrx').text().trim()); if(typeof Swal !== 'undefined'){ Swal.fire({ icon: 'success', title: 'Copied!', text: 'Transaction reference copied to clipboard.', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' }); }" title="Copy Reference"><i class="fa-regular fa-copy"></i></button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted fw-semibold">Transfer Type</span>
                        <span class="fw-bold text-dark" id="csbTransferConfirmType">External ACH</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted fw-semibold">Funding Account</span>
                        <span class="fw-semibold text-dark" id="csbTransferConfirmFrom">Checking (*4864)</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted fw-semibold">Recipient / To</span>
                        <span class="fw-semibold text-dark text-end" id="csbTransferConfirmTo">—</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom d-none" id="csbConfirmBankRow">
                        <span class="text-muted fw-semibold">Receiving Institution</span>
                        <span class="fw-semibold text-dark text-end" id="csbTransferConfirmBank">—</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted fw-semibold">Originating ABA</span>
                        <span class="font-monospace text-muted fw-semibold">084301768</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted fw-semibold">Date &amp; Time</span>
                        <span class="text-muted" id="csbTransferConfirmDate">Nov 18, 2026</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted fw-semibold">Transfer Fee</span>
                        <span class="text-success fw-bold" id="csbTransferConfirmFee">$0.00 (Free)</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <span class="text-muted fw-semibold">Status</span>
                        <span class="badge bg-success-subtle text-success fw-bold px-3 py-1 rounded-pill" id="csbTransferConfirmStatus">Completed</span>
                    </div>
                    <div class="mt-2 pt-2 border-top text-center text-muted" style="font-size: 11px;">
                        <i class="fa-solid fa-lock text-success me-1"></i> Bank-Grade 256-Bit Encrypted Transfer • Cleveland State Bank
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary w-50 py-2 fw-semibold rounded-pill" onclick="window.print();">
                        <i class="fa-solid fa-print me-1"></i> Print Receipt
                    </button>
                    <button type="button" class="btn text-white w-50 py-2 fw-bold rounded-pill shadow-sm" style="background: #162846;" data-bs-dismiss="modal">
                        <i class="fa-solid fa-arrow-left me-1"></i> Return to Dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 7. PRE-FILLED DIRECT DEPOSIT SWITCHER MODAL ── --}}
    <div class="modal fade" id="directDepositModal" tabindex="-1" aria-hidden="true" style="z-index: 2060;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #1b325f 0%, #0d1b2a 100%);">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar fs-5 text-warning"></i>
                        <h5 class="modal-title fw-bold mb-0" style="font-size: 15px; letter-spacing: 0.5px;">DIRECT DEPOSIT AUTHORIZATION</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="badge px-3 py-2 rounded-pill font-monospace mb-2" style="background: rgba(27, 50, 95, 0.08); color: #1b325f; font-size: 11px;">
                            <i class="fa-solid fa-building-columns me-1 text-success"></i> OFFICIAL NACHA ACH FORMAT
                        </div>
                        <h5 class="fw-bold mb-1 text-dark">Pre-Filled Direct Deposit Switcher</h5>
                        <p class="text-muted small mb-0">Provide this authorization to your employer HR or payroll department to switch your paychecks directly to Cleveland State Bank.</p>
                    </div>

                    <!-- Authorization Summary Card -->
                    <div class="p-3 rounded-3 bg-light border mb-3" style="font-size: 13px;">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Financial Institution</span>
                            <span class="fw-bold text-dark">Cleveland State Bank (Member FDIC)</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom align-items-center">
                            <span class="text-muted">ABA Routing Number</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="font-monospace fw-bold text-dark" id="csbDirectDepRouting">084301768</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" onclick="csbCopyText('084301768', 'Routing number copied!')" style="font-size: 11px;">
                                    <i class="fa-regular fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom align-items-center">
                            <span class="text-muted">Target Account</span>
                            <select class="form-select form-select-sm w-auto fw-bold text-dark" id="csbDirectDepAccountSelect" onchange="csbUpdateDirectDepDisplay(this.value)">
                                <option value="checking" data-num="{{ $user->account_number ?? '1397' }}">Checking (*{{ substr($user->account_number ?? '1397', -4) }})</option>
                                @if($hasSavings)
                                <option value="savings" data-num="{{ $user->savings_account_number ?? $user->account_number ?? '4902' }}">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '4902', -4) }})</option>
                                @endif
                            </select>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom align-items-center">
                            <span class="text-muted">Account Number</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="font-monospace fw-bold text-dark" id="csbDirectDepAccountNum">{{ $user->account_number ?? '1397' }}</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" onclick="csbCopyDirectDepAcc()" style="font-size: 11px;">
                                    <i class="fa-regular fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Account Holder</span>
                            <span class="fw-bold text-dark">{{ $user->first_name }} {{ $user->last_name }}</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2">
                            <span class="text-muted">Allocation</span>
                            <span class="text-success fw-bold">100% of Net Pay (Full Direct Deposit)</span>
                        </div>
                    </div>

                    <div id="csbCopyAlert" class="alert alert-success py-2 px-3 small d-none text-center">
                        <i class="fa-solid fa-check-circle me-1"></i> Copied to clipboard!
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-column gap-2 mt-4">
                        <a href="{{ route('user.direct-deposit', 'checking') }}" id="csbBtnDownloadDirectDep" class="btn py-2 text-white fw-bold rounded-pill d-flex align-items-center justify-content-center gap-2 shadow-sm" style="background: linear-gradient(135deg, #c8232c 0%, #a51821 100%); border: none;">
                            <i class="fa-solid fa-file-pdf"></i> Download Official PDF Form
                        </a>
                        <button type="button" class="btn btn-outline-secondary py-2 fw-semibold rounded-pill d-flex align-items-center justify-content-center gap-2" onclick="csbPrintDirectDepositNotice()">
                            <i class="fa-solid fa-print"></i> Print Authorization Notice
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════ MANAGE FAVORITES MODAL ════════ -->
    <div class="modal fade" id="csbManageFavoritesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 18px;">
                <div class="modal-header border-0 pb-2 pt-3 px-4 d-flex justify-content-between align-items-center" style="background:#162846; color:#ffffff;">
                    <h5 class="modal-title fw-bold mb-0 text-white"><i class="fa-solid fa-star text-warning me-2"></i> Manage Favorites</h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">Choose which accounts display the gold favorite badge on your dashboard.</p>
                    <div class="d-flex flex-column gap-2" id="csbFavoritesList">
                        <label class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border cursor-pointer">
                            <span class="fw-bold text-dark">Checking (*{{ substr($user->account_number ?? '1397', -4) }})</span>
                            <input type="checkbox" class="form-check-input csb-fav-check fs-5" data-acc="checking" checked>
                        </label>
                        @if($hasSavings)
                        <label class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border cursor-pointer">
                            <span class="fw-bold text-dark">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '4902', -4) }})</span>
                            <input type="checkbox" class="form-check-input csb-fav-check fs-5" data-acc="savings">
                        </label>
                        @endif
                        @if($hasIra)
                        <label class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border cursor-pointer">
                            <span class="fw-bold text-dark">IRA (*{{ substr($user->ira_account_number ?? $user->account_number ?? '0000', -4) }})</span>
                            <input type="checkbox" class="form-check-input csb-fav-check fs-5" data-acc="ira">
                        </label>
                        @endif
                        @if($hasHeloc)
                        <label class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border cursor-pointer">
                            <span class="fw-bold text-dark">HELOC (*{{ substr($user->heloc_account_number ?? $user->account_number ?? '0000', -4) }})</span>
                            <input type="checkbox" class="form-check-input csb-fav-check fs-5" data-acc="heloc">
                        </label>
                        @endif
                        @if($hasCc)
                        <label class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border cursor-pointer">
                            <span class="fw-bold text-dark">Credit Card (*{{ substr($user->cc_account_number ?? $user->account_number ?? '0000', -4) }})</span>
                            <input type="checkbox" class="form-check-input csb-fav-check fs-5" data-acc="cc">
                        </label>
                        @endif
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="button" class="btn text-white w-100 py-2 fw-bold rounded-pill" style="background:#c8232c;" onclick="csbSaveFavorites()">Save Favorites</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════ MANAGE TILES MODAL ════════ -->
    <div class="modal fade" id="csbManageTilesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 18px;">
                <div class="modal-header border-0 pb-2 pt-3 px-4 d-flex justify-content-between align-items-center" style="background:#162846; color:#ffffff;">
                    <h5 class="modal-title fw-bold mb-0 text-white"><i class="fa-solid fa-table-cells-large text-light me-2"></i> Manage Dashboard Tiles</h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">Toggle which widgets appear on your dashboard home screen.</p>
                    <div class="d-flex flex-column gap-2" id="csbTilesToggleList">
                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border">
                            <div>
                                <div class="fw-bold text-dark">Account Overview Tiles</div>
                                <div class="extra-small text-muted">Checking, savings, and credit cards</div>
                            </div>
                            <div class="form-check form-switch fs-5 mb-0">
                                <input class="form-check-input" type="checkbox" id="tileToggleAccounts" checked onchange="csbToggleTile('accounts', this.checked)">
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border">
                            <div>
                                <div class="fw-bold text-dark">Recent Transactions</div>
                                <div class="extra-small text-muted">Live ledger activity list</div>
                            </div>
                            <div class="form-check form-switch fs-5 mb-0">
                                <input class="form-check-input" type="checkbox" id="tileToggleRecentTxn" checked onchange="csbToggleTile('recentTxn', this.checked)">
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border">
                            <div>
                                <div class="fw-bold text-dark">Upcoming Transactions</div>
                                <div class="extra-small text-muted">Scheduled drafts and debits</div>
                            </div>
                            <div class="form-check form-switch fs-5 mb-0">
                                <input class="form-check-input" type="checkbox" id="tileToggleUpcomingTxn" checked onchange="csbToggleTile('upcomingTxn', this.checked)">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="button" class="btn text-white w-100 py-2 fw-bold rounded-pill" style="background:#162846;" data-bs-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════ OPTION 5: ALERT PREFERENCES HUB MODAL ════════ -->
    <div class="modal fade" id="csbAlertPreferencesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-2 pt-3 px-4 d-flex justify-content-between align-items-center" style="background:#162846; color:#ffffff;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-bell text-danger fs-5"></i>
                        <h5 class="modal-title fw-bold mb-0 text-white">Alert Preferences Hub</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-4">Set real-time banking notifications for account security, low balances, and money movements.</p>
                    
                    <form id="csbAlertPreferencesForm" onsubmit="event.preventDefault(); csbSaveAlertPreferences();">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="fw-bold text-dark"><i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> Low Balance Alert</div>
                                        <div class="form-check form-switch fs-5 mb-0">
                                            <input class="form-check-input" type="checkbox" name="low_balance_alert" value="1" checked id="prefLowBalToggle">
                                        </div>
                                    </div>
                                    <div class="small text-muted mb-2">Notify me when checking balance drops below:</div>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white fw-bold">$</span>
                                        <input type="number" step="1" name="low_balance_threshold" class="form-control" value="100.00" min="10">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="fw-bold text-dark"><i class="fa-solid fa-bolt text-danger me-1"></i> Large Transaction Alert</div>
                                        <div class="form-check form-switch fs-5 mb-0">
                                            <input class="form-check-input" type="checkbox" name="large_txn_alert" value="1" checked id="prefLargeTxnToggle">
                                        </div>
                                    </div>
                                    <div class="small text-muted mb-2">Notify me when any debit exceeds:</div>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white fw-bold">$</span>
                                        <input type="number" step="1" name="large_txn_threshold" class="form-control" value="250.00" min="25">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="fa-solid fa-money-check-dollar text-success me-1"></i> Direct Deposit Alert</div>
                                        <div class="extra-small text-muted">Immediate alert when payroll lands</div>
                                    </div>
                                    <div class="form-check form-switch fs-5 mb-0">
                                        <input class="form-check-input" type="checkbox" name="direct_deposit_alert" value="1" checked>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="fa-solid fa-credit-card text-primary me-1"></i> Card Debit &amp; Swipe Alert</div>
                                        <div class="extra-small text-muted">Notify on in-store and online purchases</div>
                                    </div>
                                    <div class="form-check form-switch fs-5 mb-0">
                                        <input class="form-check-input" type="checkbox" name="card_charge_alert" value="1" checked>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="fa-solid fa-building-columns text-info me-1"></i> Wire &amp; Zelle Alert</div>
                                        <div class="extra-small text-muted">Settlement of wires and peer payments</div>
                                    </div>
                                    <div class="form-check form-switch fs-5 mb-0">
                                        <input class="form-check-input" type="checkbox" name="wire_transfer_alert" value="1" checked>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="fa-solid fa-mobile-screen-button text-secondary me-1"></i> Push Notifications</div>
                                        <div class="extra-small text-muted">In-app &amp; device browser push notifications</div>
                                    </div>
                                    <div class="form-check form-switch fs-5 mb-0">
                                        <input class="form-check-input" type="checkbox" name="push_alerts_enabled" value="1" checked>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="csbAlertSaveSuccess" class="alert alert-success mt-3 py-2 small d-none text-center">
                            <i class="fa-solid fa-circle-check me-1"></i> Alert preferences updated and activated successfully!
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn text-white w-100 py-2 fw-bold rounded-pill" style="background:#c8232c;" id="btnSaveAlertPrefs">
                                Save Alert Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════════ MOBILE FLOATING QUICK-ACTION DOCK ════════════════ -->
    <nav class="csb-mobile-dock" aria-label="Mobile Banking Quick Actions">
        <a href="javascript:void(0)" class="csb-dock-item" onclick="window.openMobileSidebar()">
            <i class="fa-solid fa-bars"></i>
            <span>Menu</span>
        </a>
        <a href="javascript:void(0)" class="csb-dock-item" data-bs-toggle="modal" data-bs-target="#remoteDepositModal">
            <i class="fa-solid fa-camera"></i>
            <span>Deposit</span>
        </a>
        <a href="javascript:void(0)" class="csb-dock-item csb-dock-primary" data-bs-toggle="modal" data-bs-target="#moveMoneyModal" title="Quick Transfer">
            <div class="csb-dock-icon-circle">
                <i class="fa-solid fa-right-left"></i>
            </div>
            <span style="color: #ffffff; font-weight: 700; margin-top: 2px;">Transfer</span>
        </a>
        <a href="javascript:void(0)" class="csb-dock-item" data-bs-toggle="modal" data-bs-target="#manageCardsModal">
            <i class="fa-solid fa-credit-card"></i>
            <span>Cards</span>
        </a>
        <a href="{{ route('user.setting.show') }}" class="csb-dock-item">
            <i class="fa-solid fa-gear"></i>
            <span>Profile</span>
        </a>
    </nav>

    @include('frontend::include.__script')

    <script>
    // ── Global Mobile Sidebar Functions ──
    window.openMobileSidebar = function() {
        var sidebar = document.getElementById('csbSidebar');
        var backdrop = document.getElementById('csbSidebarBackdrop');
        if (sidebar) sidebar.classList.add('show');
        if (backdrop) backdrop.classList.add('show');
        document.body.style.overflow = 'hidden';
    };
    window.closeMobileSidebar = function() {
        var sidebar = document.getElementById('csbSidebar');
        var backdrop = document.getElementById('csbSidebarBackdrop');
        if (sidebar) sidebar.classList.remove('show');
        if (backdrop) backdrop.classList.remove('show');
        document.body.style.overflow = '';
    };

    document.addEventListener('DOMContentLoaded', function () {
        var sidebar = document.getElementById('csbSidebar');
        var toggleBtn = document.getElementById('sidebarToggleBtn');
        var closeBtn = document.getElementById('csbSidebarCloseBtn');
        var backdrop = document.getElementById('csbSidebarBackdrop');

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
                csbOnAccountSelectChanged($(this).val());
            });
        }

        // Clean up transfer results when modal closes
        var moveModal = document.getElementById('moveMoneyModal');
        if (moveModal) {
            moveModal.addEventListener('hidden.bs.modal', function () {
                document.querySelectorAll('.csb-tx-result').forEach(function(el){ el.classList.add('d-none'); el.innerHTML=''; });
            });
        }

        // Apply saved tile preferences
        try {
            var savedTiles = JSON.parse(localStorage.getItem('csb_dashboard_tiles') || '{}');
            if (savedTiles.accounts === false) $('.accounts-grid').hide();
            if (savedTiles.recentTxn === false) $('#recentTxnWidgetCard').hide();
            if (savedTiles.upcomingTxn === false) $('#upcomingTxnWidgetCard').hide();
        } catch(e) {}

        // Apply saved favorites
        try {
            var savedFavs = JSON.parse(localStorage.getItem('csb_favorite_accounts') || '["checking"]');
            $('.account-card').each(function() {
                var accType = $(this).attr('data-account-type');
                if (accType && savedFavs.includes(accType)) {
                    if (!$(this).find('.account-type-row i.fa-star').length) {
                        $(this).find('.account-type-row').append('<i class="fa-solid fa-star ms-1" style="color: #f59e0b; font-size: 11px;"></i>');
                    }
                }
            });
        } catch(e) {}
    });

    // ── 3-Dot Search Toggle ──
    window.csbToggleTxnSearch = function() {
        var $wrap = $('#csbTxnSearchToggleWrap');
        if (!$wrap.length) return;
        $wrap.slideToggle(180, function() {
            if ($(this).is(':visible')) {
                $('#csbTxnLiveSearch').focus();
            }
        });
    };

    window.csbClearTxnSearch = function() {
        $('#csbTxnLiveSearch').val('');
        csbFilterTxnLive('');
        $('#csbTxnSearchToggleWrap').slideUp(180);
    };

    // ── Manage Favorites Feature ──
    window.csbOpenManageFavorites = function() {
        var saved = [];
        try {
            saved = JSON.parse(localStorage.getItem('csb_favorite_accounts') || '["checking"]');
        } catch(e) {
            saved = ['checking'];
        }
        $('.csb-fav-check').each(function() {
            var acc = $(this).attr('data-acc');
            $(this).prop('checked', saved.includes(acc));
        });
        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('csbManageFavoritesModal'));
        modal.show();
    };

    window.csbSaveFavorites = function() {
        var selected = [];
        $('.csb-fav-check:checked').each(function() {
            selected.push($(this).attr('data-acc'));
        });
        if (!selected.length) selected = ['checking'];
        localStorage.setItem('csb_favorite_accounts', JSON.stringify(selected));

        // Update star badges on dashboard
        $('.account-card').each(function() {
            var accType = $(this).attr('data-account-type');
            if (accType) {
                var $star = $(this).find('.account-type-row i.fa-star');
                if (selected.includes(accType)) {
                    if (!$star.length) {
                        $(this).find('.account-type-row').append('<i class="fa-solid fa-star ms-1" style="color: #f59e0b; font-size: 11px;"></i>');
                    }
                } else {
                    $star.remove();
                }
            }
        });

        if (window.navigator && window.navigator.vibrate) window.navigator.vibrate(15);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('csbManageFavoritesModal'))?.hide();
    };

    // ── Manage Dashboard Tiles Feature ──
    window.csbOpenManageTiles = function() {
        var tiles = {};
        try {
            tiles = JSON.parse(localStorage.getItem('csb_dashboard_tiles') || '{"accounts":true,"recentTxn":true,"upcomingTxn":true}');
        } catch(e) {
            tiles = { accounts: true, recentTxn: true, upcomingTxn: true };
        }
        $('#tileToggleAccounts').prop('checked', tiles.accounts !== false);
        $('#tileToggleRecentTxn').prop('checked', tiles.recentTxn !== false);
        $('#tileToggleUpcomingTxn').prop('checked', tiles.upcomingTxn !== false);

        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('csbManageTilesModal'));
        modal.show();
    };

    window.csbToggleTile = function(tileKey, isVisible) {
        var tiles = {};
        try {
            tiles = JSON.parse(localStorage.getItem('csb_dashboard_tiles') || '{"accounts":true,"recentTxn":true,"upcomingTxn":true}');
        } catch(e) {
            tiles = { accounts: true, recentTxn: true, upcomingTxn: true };
        }
        tiles[tileKey] = isVisible;
        localStorage.setItem('csb_dashboard_tiles', JSON.stringify(tiles));

        if (tileKey === 'accounts') {
            if (isVisible) $('.accounts-grid').slideDown(200); else $('.accounts-grid').slideUp(200);
        } else if (tileKey === 'recentTxn') {
            if (isVisible) $('#recentTxnWidgetCard').slideDown(200); else $('#recentTxnWidgetCard').slideUp(200);
        } else if (tileKey === 'upcomingTxn') {
            if (isVisible) $('#upcomingTxnWidgetCard').slideDown(200); else $('#upcomingTxnWidgetCard').slideUp(200);
        }
        if (window.navigator && window.navigator.vibrate) window.navigator.vibrate(10);
    };

    // ── Option 5: Granular Alert Preferences AJAX Save ──
    window.csbSaveAlertPreferences = function(e) {
        if (e && e.preventDefault) e.preventDefault();
        var $btn = $('#btnSaveAlertPrefs');
        var origText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Saving Alert Preferences...');

        var form = document.getElementById('csbAlertPreferencesForm');
        var formData = new FormData(form);

        $.ajax({
            url: "{{ route('user.setting.update-notification-preferences') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        })
        .done(function(res) {
            $('#csbAlertSaveSuccess').removeClass('d-none').hide().fadeIn(200);
            if (window.navigator && window.navigator.vibrate) window.navigator.vibrate([15, 50, 15]);
            setTimeout(function() {
                var modal = bootstrap.Modal.getInstance(document.getElementById('csbAlertPreferencesModal'));
                if (modal) modal.hide();
                $('#csbAlertSaveSuccess').addClass('d-none');
            }, 1200);
        })
        .fail(function(xhr) {
            var msg = xhr.responseJSON?.message || 'Could not update alert preferences. Please try again.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Preferences Error', text: msg, confirmButtonColor: '#162846' });
            } else {
                alert(msg);
            }
        })
        .always(function() {
            $btn.prop('disabled', false).html(origText);
        });
    };

    // ── Option 6: Native Pull-to-Refresh & Soft Haptics on Mobile ──
    (function initPullToRefresh() {
        if (!('ontouchstart' in window)) return;
        var startY = 0;
        var currentY = 0;
        var isPulling = false;
        var ptrEl = document.createElement('div');
        ptrEl.id = 'csbMobilePtr';
        ptrEl.style.cssText = 'position:fixed; top:-60px; left:0; right:0; height:50px; display:flex; align-items:center; justify-content:center; gap:8px; font-size:12.5px; font-weight:700; color:#162846; background:rgba(255,255,255,0.96); backdrop-filter:blur(8px); box-shadow:0 4px 15px rgba(0,0,0,0.08); z-index:9999; transition:top 0.2s cubic-bezier(0.1, 0.9, 0.2, 1);';
        ptrEl.innerHTML = '<i class="fa-solid fa-arrows-rotate text-danger ptr-icon" style="transition:transform 0.2s ease;"></i> <span class="ptr-text">Pull down to refresh ledger</span>';
        document.body.appendChild(ptrEl);

        window.addEventListener('touchstart', function(e) {
            if (window.scrollY === 0) {
                startY = e.touches[0].pageY;
                isPulling = true;
            } else {
                isPulling = false;
            }
        }, { passive: true });

        window.addEventListener('touchmove', function(e) {
            if (!isPulling || window.scrollY > 0) return;
            currentY = e.touches[0].pageY;
            var diff = currentY - startY;
            if (diff > 15) {
                var offset = Math.min(diff * 0.45, 65);
                ptrEl.style.top = (offset - 60) + 'px';
                var icon = ptrEl.querySelector('.ptr-icon');
                if (icon) icon.style.transform = 'rotate(' + (diff * 2) + 'deg)';
                if (offset >= 45) {
                    ptrEl.querySelector('.ptr-text').innerText = 'Release to refresh ledger';
                } else {
                    ptrEl.querySelector('.ptr-text').innerText = 'Pull down to refresh';
                }
            }
        }, { passive: true });

        window.addEventListener('touchend', function() {
            if (!isPulling) return;
            isPulling = false;
            var diff = currentY - startY;
            if (diff > 90) {
                if (window.navigator && window.navigator.vibrate) window.navigator.vibrate(15);
                ptrEl.style.top = '10px';
                ptrEl.querySelector('.ptr-text').innerHTML = '<span class="spinner-border spinner-border-sm text-danger me-1"></span> Refreshing...';
                setTimeout(function() {
                    csbOnAccountSelectChanged($('#csbTxnAccountSelect').val());
                    ptrEl.style.top = '-60px';
                    setTimeout(function() {
                        ptrEl.querySelector('.ptr-text').innerText = 'Pull down to refresh ledger';
                    }, 300);
                }, 600);
            } else {
                ptrEl.style.top = '-60px';
            }
            startY = 0;
            currentY = 0;
        }, { passive: true });
    })();

    // ── Interactive Ledger Filtering & Live Search ──
    function csbOnAccountSelectChanged(val) {
        var $skel = $('#csbTxnSkeletonWrap');
        if ($skel.length) {
            $('.transaction-list-table .txn-row').addClass('d-none');
            $skel.removeClass('d-none');
            setTimeout(function() {
                $skel.addClass('d-none');
                csbFilterTxnLive($('#csbTxnLiveSearch').val());
            }, 250);
        } else {
            csbFilterTxnLive($('#csbTxnLiveSearch').val());
        }
    }

    function csbFilterTxnLive(query) {
        var q = (query || '').toLowerCase().trim();
        var selectedAcc = $('#csbTxnAccountSelect').val() || 'all';
        var visibleCount = 0;
        
        $('.transaction-list-table .txn-row').each(function () {
            var $row = $(this);
            var rowAcc = $row.attr('data-account') || 'checking';
            var desc = ($row.attr('data-tx-desc') || '').toLowerCase();
            var trx = ($row.attr('data-tx-trx') || '').toLowerCase();
            var amount = ($row.attr('data-tx-amount') || '').toLowerCase();
            var date = ($row.attr('data-tx-date') || '').toLowerCase();
            
            var matchesAcc = (selectedAcc === 'all' || rowAcc === selectedAcc);
            var matchesQuery = !q || desc.includes(q) || trx.includes(q) || amount.includes(q) || date.includes(q);
            
            if (matchesAcc && matchesQuery) {
                $row.removeClass('d-none').show();
                visibleCount++;
            } else {
                $row.addClass('d-none').hide();
            }
        });

        if (visibleCount === 0) {
            $('#csbNoTxnForAccount').removeClass('d-none').html(
                q ? '<i class="fa-solid fa-magnifying-glass fa-2x mb-2 d-block text-muted" style="opacity: 0.4;"></i>No transactions match "' + $('<div>').text(q).html() + '"'
                  : '<i class="fa-regular fa-folder-open fa-2x mb-2 d-block" style="opacity: 0.35;"></i>No recent activity found for this account.'
            );
        } else {
            $('#csbNoTxnForAccount').addClass('d-none');
        }
    }

    function csbExportClientCsv() {
        var rows = [];
        rows.push(['Date', 'Description', 'Reference', 'Amount', 'Status', 'Method', 'Account']);
        
        $('.transaction-list-table .txn-row:visible').each(function () {
            var $row = $(this);
            rows.push([
                '"' + ($row.attr('data-tx-date') || '').replace(/"/g, '""') + '"',
                '"' + ($row.attr('data-tx-desc') || '').replace(/"/g, '""') + '"',
                '"' + ($row.attr('data-tx-trx') || '').replace(/"/g, '""') + '"',
                '"' + ($row.attr('data-tx-amount') || '').replace(/"/g, '""') + '"',
                '"' + ($row.attr('data-tx-status') || '').replace(/"/g, '""') + '"',
                '"' + ($row.attr('data-tx-method') || '').replace(/"/g, '""') + '"',
                '"' + ($row.attr('data-account') || '').replace(/"/g, '""') + '"'
            ]);
        });
        
        if (rows.length <= 1) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'info', title: 'Export Transactions', text: 'No transactions visible to export.', confirmButtonColor: '#162846' });
            } else {
                alert('No transactions visible to export.');
            }
            return;
        }
        
        var csvContent = rows.map(function(e){ return e.join(','); }).join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        var url = URL.createObjectURL(blob);
        var today = new Date().toISOString().slice(0, 10);
        link.setAttribute('href', url);
        link.setAttribute('download', 'CSB_Transactions_' + today + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // ── CSB 3D Flip Account Tile Tap & Modal Handler ──
    window.csbHandleTileTap = function(el, event, title, acct, bal, limit, type) {
        if (typeof window.showAccountDetails === 'function') {
            window.showAccountDetails(title, acct, bal, limit, type);
        }
    };

    $(document).on('click touchstart', function(e) {
        if (!$(e.target).closest('.csb-account-card').length) {
            $('.csb-account-card.is-flipped').removeClass('is-flipped');
        }
    });

    // ── Transaction Details, Receipt Upload & Dispute Modal Popup ──
    function csbShowTxnFromEl(el, event) {
        if (window.innerWidth < 768) {
            if (event && event.target && $(event.target).closest('a, button, input').length) {
                return;
            }
            $(el).toggleClass('is-expanded');
            return;
        }
        var $el = $(el);
        csbShowTxnDetails(
            $el.attr('data-tx-desc') || 'Transaction',
            $el.attr('data-tx-trx') || 'N/A',
            $el.attr('data-tx-amount') || '$0.00',
            $el.attr('data-tx-date') || '',
            $el.attr('data-tx-status') || 'Completed',
            $el.attr('data-tx-method') || 'Transfer',
            $el.attr('data-tx-receipt') || '#',
            $el.attr('data-tx-dispute') || '#',
            $el.attr('data-tx-id') || '',
            $el.attr('data-tx-note') || '',
            $el.attr('data-tx-attachment') || ''
        );
    }

    function csbShowTxnDetails(desc, trx, amount, date, status, method, receiptUrl, disputeUrl, id, note, attachment) {
        $('#csbTxnModalTitle').text(desc);
        $('#csbTxnModalTrx').text(trx);
        $('#csbTxnModalAmount').text(amount);
        $('#csbTxnModalDate').text(date);
        var sLower = (status || '').toLowerCase();
        if (sLower === 'pending' || sLower === '2') {
            $('#csbTxnModalStatus').removeClass('text-success text-danger').addClass('text-warning fw-bold').text('Pending');
        } else if (sLower === 'rejected' || sLower === 'failed' || sLower === '3') {
            $('#csbTxnModalStatus').removeClass('text-success text-warning').addClass('text-danger fw-bold').text('Declined');
        } else {
            $('#csbTxnModalStatus').removeClass('text-warning text-danger').addClass('text-success fw-bold').text('Completed');
        }
        $('#csbTxnModalMethod').text(method);
        $('#csbBtnDownloadReceipt').attr('href', receiptUrl);
        var isOutgoing = (amount || '').trim().indexOf('-') === 0;
        if (isOutgoing && disputeUrl && disputeUrl !== '#') {
            $('#csbBtnDisputeTxn').removeClass('d-none').attr('href', disputeUrl);
        } else {
            $('#csbBtnDisputeTxn').addClass('d-none');
        }
        
        $('#csbTxnModalId').val(id || '');
        $('#csbTxnMemberNote').val(note || '');
        $('#csbTxnReceiptFileInput').val('');
        $('#csbTxnFileLabel').text('Attach Receipt / Invoice');
        $('#csbTxnSaveFeedback').addClass('d-none').text('');
        
        if (attachment) {
            $('#csbTxnAttachmentBox').removeClass('d-none');
            $('#csbTxnAttachmentLink').attr('href', attachment).text(attachment.split('/').pop());
        } else {
            $('#csbTxnAttachmentBox').addClass('d-none');
            $('#csbTxnAttachmentLink').attr('href', '#').text('');
        }
        
        var modal = new bootstrap.Modal(document.getElementById('trxViewDetailsBox'));
        modal.show();
    }

    function csbOnReceiptFileChosen(input) {
        if (input.files && input.files[0]) {
            $('#csbTxnFileLabel').text(input.files[0].name);
        } else {
            $('#csbTxnFileLabel').text('Attach Receipt / Invoice');
        }
    }

    function csbSaveTxnReceiptNote() {
        var txnId = $('#csbTxnModalId').val();
        if (!txnId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Transaction identifier not found.', confirmButtonColor: '#162846' });
            } else {
                alert('Transaction identifier not found.');
            }
            return;
        }
        var note = $('#csbTxnMemberNote').val();
        var fileInput = document.getElementById('csbTxnReceiptFileInput');
        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('member_notes', note);
        if (fileInput && fileInput.files[0]) {
            formData.append('receipt_file', fileInput.files[0]);
        }
        
        var $btn = $('#csbBtnSaveReceipt');
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: '/user/transactions/receipt-note/' + txnId,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                $btn.prop('disabled', false).html(origHtml);
                $('#csbTxnSaveFeedback').removeClass('d-none text-danger').addClass('text-success')
                    .html('<i class="fa-solid fa-circle-check me-1"></i> Saved successfully!');
                
                // Update matching row in ledger DOM
                var $row = $('.txn-row[data-tx-id="' + txnId + '"]');
                if ($row.length) {
                    $row.attr('data-tx-note', note);
                    if (res.attachment_url) {
                        $row.attr('data-tx-attachment', res.attachment_url);
                    }
                }
                if (res.attachment_url) {
                    $('#csbTxnAttachmentBox').removeClass('d-none');
                    $('#csbTxnAttachmentLink').attr('href', res.attachment_url).text(res.attachment_url.split('/').pop());
                }
                setTimeout(function() {
                    $('#csbTxnSaveFeedback').fadeOut();
                }, 3000);
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(origHtml);
                var err = xhr.responseJSON?.message || 'Failed to save receipt/note.';
                $('#csbTxnSaveFeedback').removeClass('d-none text-success').addClass('text-danger')
                    .html('<i class="fa-solid fa-triangle-exclamation me-1"></i> ' + err);
            }
        });
    }

    // ── Direct Deposit Helpers ──
    function csbCopyText(text, msg) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                csbShowCopyNotice(msg);
            });
        } else {
            var temp = $('<input>');
            $('body').append(temp);
            temp.val(text).select();
            document.execCommand('copy');
            temp.remove();
            csbShowCopyNotice(msg);
        }
    }

    function csbCopyDirectDepAcc() {
        var num = $('#csbDirectDepAccountNum').text().trim();
        csbCopyText(num, 'Account number copied!');
    }

    function csbShowCopyNotice(msg) {
        var $alert = $('#csbCopyAlert');
        $alert.text(msg || 'Copied to clipboard!').removeClass('d-none');
        setTimeout(function() { $alert.addClass('d-none'); }, 2500);
    }

    function csbUpdateDirectDepDisplay(type) {
        var $opt = $('#csbDirectDepAccountSelect option:selected');
        var accNum = $opt.attr('data-num') || '';
        $('#csbDirectDepAccountNum').text(accNum);
        var baseDownloadUrl = "{{ url('user/direct-deposit') }}";
        $('#csbBtnDownloadDirectDep').attr('href', baseDownloadUrl + '/' + type);
    }

    function csbPrintDirectDepositNotice() {
        var w = window.open('', '_blank');
        var accNum = $('#csbDirectDepAccountNum').text().trim();
        var accType = $('#csbDirectDepAccountSelect option:selected').text().trim();
        var html = '<!DOCTYPE html><html><head><title>Direct Deposit Authorization — Cleveland State Bank</title>' +
            '<style>body{font-family:Arial,sans-serif;padding:30px;line-height:1.6;color:#1e293b}h2{color:#c8232c}table{width:100%;border-collapse:collapse;margin:20px 0}td{padding:10px;border-bottom:1px solid #e2e8f0}.bold{font-weight:bold;width:40%}</style>' +
            '</head><body>' +
            '<h2>Cleveland State Bank</h2>' +
            '<p><strong>Official Direct Deposit Authorization Notice</strong></p>' +
            '<hr>' +
            '<table>' +
            '<tr><td class="bold">Employee / Member Name:</td><td>{{ e($user->first_name) }} {{ e($user->last_name) }}</td></tr>' +
            '<tr><td class="bold">Financial Institution:</td><td>Cleveland State Bank (Member FDIC)</td></tr>' +
            '<tr><td class="bold">ABA Routing Number:</td><td>084301768</td></tr>' +
            '<tr><td class="bold">Account Number:</td><td>' + accNum + '</td></tr>' +
            '<tr><td class="bold">Account Type:</td><td>' + accType + '</td></tr>' +
            '<tr><td class="bold">Deposit Allocation:</td><td>100% of Net Pay</td></tr>' +
            '</table>' +
            '<p style="margin-top:30px;font-size:12px;color:#64748b;">Member FDIC • Equal Housing Lender • Routing #084301768 • Phone: 662-843-9461</p>' +
            '<script>window.print();<\/script>' +
            '</body></html>';
        w.document.write(html);
        w.document.close();
    }

    // ── 2FA Security Gate Integration ──
    let pendingTransferCallback = null;

    window.SecurityGateOnSuccess = function(res) {
        if (typeof pendingTransferCallback === 'function') {
            let cb = pendingTransferCallback;
            pendingTransferCallback = null;
            cb(res);
        }
    };

    function triggerSecurityGate(callback) {
        pendingTransferCallback = callback;
        if (typeof SecurityGate !== 'undefined' && typeof SecurityGate.gate === 'function') {
            SecurityGate.onCancelCb = function() {
                pendingTransferCallback = null;
                if (typeof window.hideLoader === 'function') window.hideLoader();
            };
            SecurityGate.gate(function(res) {
                if (typeof pendingTransferCallback === 'function') {
                    let cb = pendingTransferCallback;
                    pendingTransferCallback = null;
                    cb(res);
                } else if (typeof callback === 'function') {
                    callback(res);
                }
            });
        } else {
            if (typeof callback === 'function') callback();
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
        if (routing === '084301768') return true;
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
            $('#csbRoutingStatus').html('<span class="text-warning fw-semibold"><i class="fa-solid fa-circle-info me-1"></i> Routing unlisted. Please enter the receiving bank name below to proceed.</span>');
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
            var revModal = bootstrap.Modal.getInstance(reviewModalEl) || bootstrap.Modal.getOrCreateInstance(reviewModalEl);
            if (revModal) revModal.hide();
        }
        var moveModalEl = document.getElementById('moveMoneyModal');
        if (moveModalEl) {
            var mModal = bootstrap.Modal.getInstance(moveModalEl) || bootstrap.Modal.getOrCreateInstance(moveModalEl);
            if (mModal) mModal.hide();
        }

        exec.callback(exec.details);
    });

    // ── Dynamic Transaction Prepend Without Page Reload ──
    function csbPrependTransactionRow(res, details) {
        var amt = details && details.amount ? details.amount.replace('$', '') : (res && res.amount ? parseFloat(res.amount).toFixed(2) : '0.00');
        var amtFormatted = '$' + amt;
        var trx = (res && res.trx) ? res.trx : ('TRX-' + Math.random().toString(36).substring(2, 9).toUpperCase());
        var type = details && details.badge ? details.badge : 'Fund Transfer';
        var typeUpper = type.toUpperCase();
        var isPending = typeUpper.includes('EXTERNAL') || typeUpper.includes('MEMBER') || typeUpper.includes('WIRE') || typeUpper.includes('ZELLE');
        var statusStr = isPending ? 'Pending' : 'Completed';
        
        var wType = (details && details.walletType ? details.walletType : 'checking').toLowerCase();
        if (wType === 'default' || wType === 'primary_checking') wType = 'checking';
        if (wType === 'primary_savings' || wType === 'savings_primary') wType = 'savings';
        if (wType === 'credit_card') wType = 'cc';
        if (wType === 'loan_account') wType = 'loan';

        var desc = type;
        if (details && details.to) {
            desc += ' - ' + details.to;
        }

        var now = new Date();
        var dateShort = (now.getMonth() + 1).toString().padStart(2, '0') + '/' + now.getDate().toString().padStart(2, '0') + '/' + now.getFullYear();
        var dateLong = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        var receiptUrl = '{{ url("/user/transactions/receipt") }}/' + encodeURIComponent(trx);
        var disputeUrl = '{{ url("/user/disputes/create") }}/' + encodeURIComponent(trx);

        // Determine category icon
        var catIcon = 'fa-solid fa-arrow-right-arrow-left';
        var catBg = '#fee2e2';
        var catColor = '#c8232c';
        if (typeUpper.includes('ZELLE')) {
            catIcon = 'fa-solid fa-bolt';
            catBg = '#f3e8ff';
            catColor = '#7e22ce';
        } else if (typeUpper.includes('MEMBER')) {
            catIcon = 'fa-solid fa-user-group';
            catBg = '#ecfdf5';
            catColor = '#059669';
        } else if (typeUpper.includes('WIRE') || typeUpper.includes('EXTERNAL') || typeUpper.includes('ACH')) {
            catIcon = 'fa-solid fa-building-columns';
            catBg = '#fee2e2';
            catColor = '#c8232c';
        } else if (typeUpper.includes('CARD')) {
            catIcon = 'fa-solid fa-credit-card';
            catBg = '#e0e7ff';
            catColor = '#4338ca';
        }

        var statusBadge = isPending 
            ? '<span style="color: #d97706;"><i class="fa-solid fa-clock me-1 small"></i>Pending</span>'
            : '<span style="color: #059669;"><i class="fa-solid fa-circle-check me-1 small"></i>Completed</span>';

        var escapedDesc = $('<div>').text(desc).html();
        var escapedType = $('<div>').text(type).html();
        var fromText = details && details.from ? $('<div>').text(details.from).html() : 'Checking';
        var toText = details && details.to ? $('<div>').text(details.to).html() : 'Recipient';
        var memoText = details && details.memo ? $('<div>').text(details.memo).html() : '';

        var rowHtml = '<div class="txn-row csb-new-txn-highlight" data-account="' + wType + '" style="cursor: pointer;"' +
            ' data-tx-id="' + trx + '"' +
            ' data-tx-desc="' + escapedDesc + '"' +
            ' data-tx-trx="' + trx + '"' +
            ' data-tx-amount="-' + amtFormatted + '"' +
            ' data-tx-date="' + dateLong + '"' +
            ' data-tx-status="' + statusStr + '"' +
            ' data-tx-method="' + escapedType + '"' +
            ' data-tx-receipt="' + receiptUrl + '"' +
            ' data-tx-dispute="' + disputeUrl + '"' +
            ' data-tx-note="' + memoText + '"' +
            ' data-tx-attachment=""' +
            ' onclick="csbShowTxnFromEl(this, event)"' +
            ' title="Click to view details, add note/receipt, or dispute">' +
            '  <div class="txn-main-card d-flex align-items-center justify-content-between w-100">' +
            '    <div class="d-flex align-items-center gap-2" style="flex: 1 1 auto; min-width: 0; padding-right: 8px;">' +
            '      <div class="txn-cat-icon rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; min-width: 36px; background: ' + catBg + '; color: ' + catColor + '; font-size: 14px;">' +
            '        <i class="' + catIcon + '"></i>' +
            '      </div>' +
            '      <div style="min-width: 0;">' +
            '        <div class="txn-desc d-flex align-items-center gap-1">' +
            '          <span class="text-truncate">' + escapedDesc + '</span>' +
            '          <span class="badge rounded-pill bg-danger-subtle text-danger" style="font-size: 9px; padding: 2px 6px;">NEW</span>' +
            '        </div>' +
            '        <div class="txn-date d-flex align-items-center gap-1">' +
            '          <span class="badge bg-light text-muted border" style="font-weight: 500; font-size: 10px;">' + dateShort + '</span>' +
            '        </div>' +
            '      </div>' +
            '    </div>' +
            '    <div class="text-end" style="flex-shrink: 0; white-space: nowrap;">' +
            '      <div class="txn-amount negative">-' + amtFormatted + '</div>' +
            '      <div class="small txn-status-badge" style="font-size: 0.72rem; font-weight: 600; margin-top: 2px;">' +
            '        ' + statusBadge +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '  <!-- Mobile Expandable Settlement Details -->' +
            '  <div class="txn-settlement-details">' +
            '    <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Ref #:</span> <span class="font-monospace fw-bold">' + trx + '</span></div>' +
            '    <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Account:</span> <span class="text-capitalize">' + wType + '</span></div>' +
            '    <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Type:</span> <span>' + escapedType + '</span></div>' +
            (memoText ? '    <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Notes:</span> <span>' + memoText + '</span></div>' : '') +
            '    <div class="d-flex justify-content-between align-items-center pt-2 gap-2 flex-wrap">' +
            '      <div class="d-flex align-items-center gap-2">' +
            '        <a href="' + receiptUrl + '" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 11px;" onclick="event.stopPropagation();"><i class="fa-solid fa-receipt me-1"></i> Receipt</a>' +
            (disputeUrl && disputeUrl !== '#' ? '        <a href="' + disputeUrl + '" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 11px; font-weight: 600;" onclick="event.stopPropagation();"><i class="fa-solid fa-flag me-1"></i> Dispute</a>' : '') +
            '      </div>' +
            '      <span class="text-muted" style="font-size: 11px;"><i class="fa-solid fa-clock me-1"></i>' + dateLong + '</span>' +
            '    </div>' +
            '  </div>' +
            '</div>';

        var $container = $('#recentTxnWidgetCard .widget-body');
        var $table = $container.find('.transaction-list-table');
        if (!$table.length) {
            $container.find('.py-4.text-center').remove();
            $table = $('<div class="transaction-list-table"></div>');
            $container.prepend($table);
        }
        $('#csbNoTxnForAccount').addClass('d-none');

        $table.prepend(rowHtml);

        var currentFilter = $('#csbTxnAccountSelect').val();
        if (currentFilter && currentFilter !== 'all' && currentFilter !== wType) {
            $('#csbTxnAccountSelect').val('all');
            $('.transaction-list-table .txn-row').removeClass('d-none');
        }

        // Instant DOM Account Balance & Totals Reflection
        var delta = parseFloat(amt) || 0;
        if (delta > 0) {
            var isDeposit = typeUpper.includes('DEPOSIT') || typeUpper.includes('CREDIT') || typeUpper.includes('INCOMING');
            var $balEl = $('[data-csb-balance="' + wType + '"]');
            if ($balEl.length) {
                var curText = $balEl.first().text().replace(/[^0-9.-]+/g, '');
                var curVal = parseFloat(curText) || 0;
                var newVal = isDeposit ? (curVal + delta) : Math.max(0, curVal - delta);
                var formattedNew = '$' + newVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                $balEl.text(formattedNew);
                if (typeof flashBalanceUpdate === 'function') {
                    $balEl.each(function() { flashBalanceUpdate(this); });
                }
            }

            // Recalculate summary total deposits if checking or savings
            if (wType === 'checking' || wType === 'savings' || wType === 'ira') {
                var totalDep = 0;
                $('[data-csb-balance]').each(function() {
                    var bType = $(this).attr('data-csb-balance');
                    if (bType === 'checking' || bType === 'savings' || bType === 'ira') {
                        var val = parseFloat($(this).text().replace(/[^0-9.-]+/g, '')) || 0;
                        totalDep += val;
                    }
                });
                var totalDepFmt = '$' + totalDep.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                $('[data-csb-summary="deposits"]').text(totalDepFmt);
            }
        }
    }

    function csbShowTransferConfirmation(res, details) {
        if (typeof window.hideLoader === 'function') window.hideLoader();
        var amt = details && details.amount ? details.amount : (res && res.amount ? '$' + parseFloat(res.amount).toFixed(2) : '$0.00');
        var trx = (res && res.trx) ? res.trx : ('TRX-' + Math.random().toString(36).substring(2, 9).toUpperCase());
        var type = details && details.badge ? details.badge : 'Fund Transfer';
        var from = details && details.from ? details.from : 'Checking';
        var to = details && details.to ? details.to : 'Recipient';
        var bank = details && details.bank ? details.bank : '';

        var typeUpper = type.toUpperCase();
        var isPending = typeUpper.includes('EXTERNAL') || typeUpper.includes('MEMBER') || typeUpper.includes('WIRE') || typeUpper.includes('ZELLE');

        if (isPending) {
            $('#csbTransferConfirmTitle').text('Transfer Submitted');
            $('#csbTransferConfirmMsg').text('Your fund transfer request has been submitted and is currently pending.');
            $('#csbTransferConfirmIconWrap').css({ background: 'rgba(234, 179, 8, 0.15)', color: '#ca8a04', 'box-shadow': '0 0 0 6px rgba(234, 179, 8, 0.05)' });
            $('#csbTransferConfirmIcon').attr('class', 'fa-solid fa-clock');
            $('#csbTransferConfirmStatus').attr('class', 'badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill').text('Pending');
        } else {
            $('#csbTransferConfirmTitle').text('Transfer Successful');
            $('#csbTransferConfirmMsg').text('Your intra-account transfer has been completed successfully.');
            $('#csbTransferConfirmIconWrap').css({ background: 'rgba(5, 150, 105, 0.12)', color: '#059669', 'box-shadow': '0 0 0 6px rgba(5, 150, 105, 0.05)' });
            $('#csbTransferConfirmIcon').attr('class', 'fa-solid fa-circle-check');
            $('#csbTransferConfirmStatus').attr('class', 'badge bg-success-subtle text-success fw-bold px-3 py-1 rounded-pill').text('Completed');
        }

        $('#csbTransferConfirmAmount').text(amt);
        $('#csbTransferConfirmTrx').text(trx);
        $('#csbTransferConfirmType').text(type);
        $('#csbTransferConfirmFrom').text(from);
        $('#csbTransferConfirmTo').text(to);
        if (bank) {
            $('#csbConfirmBankRow').removeClass('d-none');
            $('#csbTransferConfirmBank').text(bank);
        } else {
            $('#csbConfirmBankRow').addClass('d-none');
        }
        $('#csbTransferConfirmDate').text(new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }));

        var successModalEl = document.getElementById('csbTransferSuccessModal');
        if (successModalEl) {
            var successModal = bootstrap.Modal.getOrCreateInstance(successModalEl);
            successModal.show();
        }
    }

    // ── Generic Transfer Form Submitter (with Loader & AJAX Confirmation) ──
    function executeTransfer($form, resSelector, url, details) {
        var $btn = $form.find('button[type=submit]');
        var origText = $btn.data('orig') || $btn.html();
        $btn.data('orig', origText);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Processing Transfer...');

        if (typeof window.showTransferLoader === 'function') {
            window.showTransferLoader('Processing Transfer...', 'Sending transaction safely...');
        } else if (typeof window.showLoader === 'function') {
            window.showLoader();
        }

        // Ensure security_verified is set in form data
        if (!$form.find('input[name="security_verified"]').length) {
            $form.append('<input type="hidden" name="security_verified" value="1">');
        } else {
            $form.find('input[name="security_verified"]').val('1');
        }

        var formData = new FormData($form[0]);
        formData.set('security_verified', '1');

        $.ajax({
            url: url || "{{ route('user.fund_transfer.transfer') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .done(function(res) {
            $form[0].reset();
            var moveModalEl = document.getElementById('moveMoneyModal');
            if (moveModalEl) {
                var mModal = bootstrap.Modal.getInstance(moveModalEl) || bootstrap.Modal.getOrCreateInstance(moveModalEl);
                if (mModal) mModal.hide();
            }

            csbPrependTransactionRow(res, details);
            csbShowTransferConfirmation(res, details);

            if (typeof window.csbSyncAccountLive === 'function') {
                setTimeout(function() { window.csbSyncAccountLive(true); }, 300);
            }
        })
        .fail(function(xhr) {
            var errors = xhr.responseJSON?.errors;
            var err = errors ? Object.values(errors).flat().join('<br>') : (xhr.responseJSON?.message || '');
            if (!err || err === 'Server Error') {
                err = 'Transfer could not be processed at this time. Please verify your account balance and recipient details and try again.';
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Transfer Notice',
                    html: err,
                    confirmButtonColor: '#162846'
                });
            } else {
                alert(err);
            }
        })
        .always(function() {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            $btn.prop('disabled', false).html(origText);
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
        var fromVal = $('#csbSelfFromWallet').val();
        var fromText = $('#csbSelfFromWallet option:selected').text().split('—')[0].trim();
        var toText = $('#csbSelfToWallet option:selected').text().trim();
        var memo = $form.find('input[name="purpose"]').val().trim();

        var details = {
            walletType: fromVal || 'checking',
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
        var fromVal = $form.find('select[name="wallet_type"]').val();
        var fromText = $form.find('select[name="wallet_type"] option:selected').text().split('—')[0].trim();
        var verifiedName = $('#csbMemberVerifiedName').text().trim() || accName;
        var toMeta = $('#csbMemberVerifiedMeta').text().trim() || $('#csbMemberIdentifier').val();
        var memo = $form.find('input[name="purpose"]').val().trim();
        var freq = $form.find('select[name="frequency"] option:selected').text().trim();

        var details = {
            walletType: fromVal || 'checking',
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
        var fromVal = $form.find('select[name="wallet_type"]').val();
        var fromText = $form.find('select[name="wallet_type"] option:selected').text().split('—')[0].trim();
        var memo = $form.find('input[name="purpose"]').val().trim();
        var freq = $form.find('select[name="frequency"] option:selected').text().trim();

        var details = {
            walletType: fromVal || 'checking',
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
        var fromVal = $form.find('select[name="wallet_type"]').val();
        var fromText = $form.find('select[name="wallet_type"] option:selected').text().split('—')[0].trim();
        var memo = $form.find('input[name="memo"]').val().trim();

        var details = {
            walletType: fromVal || 'checking',
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
        var fromVal = $form.find('select[name="wallet_type"]').val();
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
            walletType: fromVal || 'checking',
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

    // ── Remote Deposit (Two-Step Flow & In-Modal History) ──
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

    window.csbProceedToReviewDeposit = function() {
        var amount = parseFloat($('#csbRdAmount').val() || 0);
        var frontFile = document.getElementById('csbFrontFile').files[0];
        var backFile = document.getElementById('csbBackFile').files[0];
        var $result = $('#rdResult');
        $result.addClass('d-none').text('');

        if (isNaN(amount) || amount <= 0) {
            $result.removeClass('d-none alert-success').addClass('alert alert-danger small')
                .html('<i class="fa-solid fa-circle-exclamation me-1"></i> Please enter a valid check amount.');
            return;
        }

        if (!frontFile || !backFile) {
            $result.removeClass('d-none alert-success').addClass('alert alert-danger small')
                .html('<i class="fa-solid fa-circle-exclamation me-1"></i> Please capture or upload both the front and back of your check.');
            return;
        }

        var selectedAccOpt = $('#csbRdAccountSelect option:selected');
        var accLabel = selectedAccOpt.attr('data-label') || selectedAccOpt.text().split('—')[0].trim();

        $('#rdReviewAccountName').text(accLabel);
        $('#rdReviewAmountDisplay').text('$' + amount.toFixed(2));
        $('#rdReviewFrontImg').attr('src', $('#csbFrontPreview').attr('src'));
        $('#rdReviewBackImg').attr('src', $('#csbBackPreview').attr('src'));

        $('#rdCaptureStep').addClass('d-none');
        $('#rdReviewStep').removeClass('d-none');
    };

    window.csbBackToDepositCapture = function() {
        $('#rdReviewStep').addClass('d-none');
        $('#rdCaptureStep').removeClass('d-none');
    };

    window.csbOpenDepositHistory = function() {
        var depModal = bootstrap.Modal.getInstance(document.getElementById('remoteDepositModal'));
        if (depModal) depModal.hide();

        setTimeout(function() {
            var histModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('csbDepositHistoryModal'));
            histModal.show();
        }, 200);
    };

    window.csbBackToDepositModal = function() {
        var histModal = bootstrap.Modal.getInstance(document.getElementById('csbDepositHistoryModal'));
        if (histModal) histModal.hide();

        setTimeout(function() {
            var depModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('remoteDepositModal'));
            depModal.show();
        }, 200);
    };

    window.csbResetDepositModal = function() {
        $('#csbRemoteDepositForm')[0].reset();
        $('#csbFrontPreviewWrap, #csbBackPreviewWrap').addClass('d-none');
        $('#rdReceiptStep, #rdReviewStep').addClass('d-none');
        $('#rdCaptureStep').removeClass('d-none');
        $('#rdResult').addClass('d-none');

        var depModal = bootstrap.Modal.getInstance(document.getElementById('remoteDepositModal'));
        if (depModal) depModal.hide();
    };

    $('#csbRemoteDepositForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#rdSubmitBtn');
        var origText = $btn.html();
        var fd = new FormData(this);
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Submitting Check...');

        $.ajax({
            url: "{{ route('user.remote_deposit.store') }}",
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        })
        .done(function(res) {
            var tnx = res.txn || ('DEP' + Math.floor(100000 + Math.random() * 900000));
            var amountVal = res.amount || $('#csbRdAmount').val();
            var accVal = res.account || $('#rdReviewAccountName').text();

            $('#rdReceiptTnx').text(tnx);
            $('#rdReceiptAmount').text('$' + parseFloat(amountVal || 0).toFixed(2));
            $('#rdReceiptAccount').text(accVal);

            $('#rdReviewStep').addClass('d-none');
            $('#rdReceiptStep').removeClass('d-none');

            // Dynamically prepend new row to CSB Deposit History Modal without reload
            var dateFmt = res.created_at || (new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }));
            var newDepositRow = '<tr>' +
                '<td class="small text-muted">' + dateFmt + '</td>' +
                '<td class="fw-semibold small">' + accVal + '</td>' +
                '<td class="fw-bold text-success">$' + parseFloat(amountVal || 0).toFixed(2) + '</td>' +
                '<td><span class="badge bg-warning text-dark">Pending</span></td>' +
                '<td class="text-end text-muted small font-monospace">' + tnx + '</td>' +
                '</tr>';
            $('#csbDepositHistoryBody tr:has(td[colspan])').remove();
            $('#csbDepositHistoryBody').prepend(newDepositRow);

            // Dynamically prepend to Recent Transactions on CSB Dashboard without reload
            if (typeof csbPrependTransactionRow === 'function') {
                csbPrependTransactionRow({ trx: tnx, amount: amountVal }, {
                    amount: '$' + parseFloat(amountVal || 0).toFixed(2),
                    badge: 'Check Deposit',
                    to: accVal,
                    walletType: (accVal.toLowerCase().includes('savings') ? 'savings' : 'checking')
                });
            }

            if (typeof window.csbSyncAccountLive === 'function') {
                setTimeout(function() { window.csbSyncAccountLive(true); }, 300);
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Check Submitted!',
                    text: 'Your deposit is pending review and typically clears within 1–2 business days.',
                    confirmButtonColor: '#162846'
                });
            }
        })
        .fail(function(xhr) {
            var errors = xhr.responseJSON?.errors;
            var msg = errors ? Object.values(errors).flat().join('<br>') : (xhr.responseJSON?.message || 'Check submission failed. Please try again.');
            
            $('#rdReviewStep').addClass('d-none');
            $('#rdCaptureStep').removeClass('d-none');
            $('#rdResult').removeClass('d-none alert-success').addClass('alert alert-danger small')
                .html('<i class="fa-solid fa-circle-exclamation me-1"></i> ' + msg);
        })
        .always(function() {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            $btn.prop('disabled', false).html(origText);
        });
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

    // ── Real-Time CSB Account & Approval Auto-Sync Poller ──
    (function() {
        var lastBalances = null;
        var lastTxns = null;
        var isSyncing = false;

        function syncAccountLive(force) {
            if ((document.hidden && !force) || isSyncing) return;
            isSyncing = true;

            fetch("{{ route('user.live-sync') }}", {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                isSyncing = false;
                if (!data || data.status !== 'success' || !data.balances) return;

                var curBals = data.balances;
                var fmtBals = data.formatted;

                if (lastBalances !== null || force) {
                    var hasChanged = force || false;
                    for (var k in curBals) {
                        if (!lastBalances || curBals[k] !== lastBalances[k]) {
                            hasChanged = true;
                            break;
                        }
                    }

                    if (hasChanged) {
                        // 1. Update checking
                        if (fmtBals.checking) {
                            document.querySelectorAll('[data-csb-balance="checking"], #checkingBalance').forEach(function(el) {
                                el.textContent = fmtBals.checking;
                                flashBalanceUpdate(el);
                            });
                            document.querySelectorAll('[data-csb-ledger="checking"]').forEach(function(el) {
                                el.textContent = fmtBals.checking;
                            });
                        }

                        // 2. Update savings
                        if (fmtBals.savings) {
                            document.querySelectorAll('[data-csb-balance="savings"], #savingsBalance').forEach(function(el) {
                                el.textContent = fmtBals.savings;
                                flashBalanceUpdate(el);
                            });
                            document.querySelectorAll('[data-csb-ledger="savings"]').forEach(function(el) {
                                el.textContent = fmtBals.savings;
                            });
                        }

                        // 3. Update IRA, HELOC, CC, Loan
                        if (fmtBals.ira) {
                            document.querySelectorAll('[data-csb-balance="ira"]').forEach(function(el) {
                                el.textContent = fmtBals.ira;
                                flashBalanceUpdate(el);
                            });
                            document.querySelectorAll('[data-csb-ledger="ira"]').forEach(function(el) {
                                el.textContent = fmtBals.ira;
                            });
                        }
                        if (fmtBals.heloc) {
                            document.querySelectorAll('[data-csb-balance="heloc"]').forEach(function(el) {
                                el.textContent = fmtBals.heloc;
                                flashBalanceUpdate(el);
                            });
                        }
                        if (fmtBals.cc) {
                            document.querySelectorAll('[data-csb-balance="cc"]').forEach(function(el) {
                                el.textContent = fmtBals.cc;
                                flashBalanceUpdate(el);
                            });
                        }
                        if (fmtBals.loan) {
                            document.querySelectorAll('[data-csb-balance="loan"]').forEach(function(el) {
                                el.textContent = fmtBals.loan;
                                flashBalanceUpdate(el);
                            });
                            document.querySelectorAll('[data-csb-ledger="loan"]').forEach(function(el) {
                                el.textContent = fmtBals.loan;
                            });
                        }

                        // 4. Update Total Deposits
                        var totalDep = (parseFloat(curBals.checking) || 0) + (parseFloat(curBals.savings) || 0) + (parseFloat(curBals.ira) || 0);
                        var totalDepFmt = "{{ $currency }}" + totalDep.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        document.querySelectorAll('[data-csb-summary="deposits"]').forEach(function(el) {
                            el.textContent = totalDepFmt;
                            flashBalanceUpdate(el);
                        });

                        // 5. Update form selects
                        updateSelectBalance('#csbSelfFromWallet option[value="checking"]', fmtBals.checking);
                        updateSelectBalance('#csbSelfFromWallet option[value="savings"]', fmtBals.savings);
                        updateSelectBalance('#csbSelfToWallet option[value="checking"]', fmtBals.checking);
                        updateSelectBalance('#csbSelfToWallet option[value="savings"]', fmtBals.savings);
                        updateSelectBalance('#csbMemberFromWallet option[value="checking"]', fmtBals.checking);
                        updateSelectBalance('#csbMemberFromWallet option[value="savings"]', fmtBals.savings);
                        updateSelectBalance('#csbExtFromWallet option[value="checking"]', fmtBals.checking);
                        updateSelectBalance('#csbExtFromWallet option[value="savings"]', fmtBals.savings);
                        updateSelectBalance('#csbWireFromWallet option[value="checking"]', fmtBals.checking);
                        updateSelectBalance('#csbWireFromWallet option[value="savings"]', fmtBals.savings);
                        updateSelectBalance('#csbZelleFromWallet option[value="checking"]', fmtBals.checking);
                        updateSelectBalance('#csbZelleFromWallet option[value="savings"]', fmtBals.savings);
                        updateSelectBalance('#csbRdAccountSelect option[value="checking"]', fmtBals.checking);
                        updateSelectBalance('#csbRdAccountSelect option[value="savings"]', fmtBals.savings);
                    }
                }
                lastBalances = curBals;

                // Check recent transactions status update
                if (data.recent_txns && Array.isArray(data.recent_txns)) {
                    if (lastTxns !== null) {
                        data.recent_txns.forEach(function(tx) {
                            var oldTx = lastTxns.find(function(o) { return o.id === tx.id || o.tnx === tx.tnx; });
                            if (oldTx && oldTx.status !== tx.status) {
                                var isApproved = (tx.status === 'success' || tx.status === 'completed' || tx.status == 1);
                                var isRejected = (tx.status === 'rejected' || tx.status == 3);
                                var statusLabel = isApproved ? 'Approved' : (isRejected ? 'Declined' : tx.status);

                                var rows = document.querySelectorAll('.txn-row[data-tx-trx="' + tx.tnx + '"], .txn-row[data-tx-id="' + tx.id + '"]');
                                rows.forEach(function(r) {
                                    r.setAttribute('data-tx-status', statusLabel);
                                    var badge = r.querySelector('.badge');
                                    if (badge) {
                                        if (isApproved) {
                                            badge.className = 'badge bg-success-subtle text-success border border-success-subtle';
                                            badge.textContent = 'Approved';
                                        } else if (isRejected) {
                                            badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle';
                                            badge.textContent = 'Declined';
                                        }
                                    }
                                });

                                if (typeof Swal !== 'undefined') {
                                    const Toast = Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 4500,
                                        timerProgressBar: true
                                    });
                                    Toast.fire({
                                        icon: isApproved ? 'success' : 'info',
                                        title: 'Transaction ' + (isApproved ? 'Approved' : statusLabel),
                                        text: (tx.description || tx.tnx) + ' has been updated.'
                                    });
                                }
                            }
                        });
                    }
                    lastTxns = data.recent_txns;
                }
            })
            .catch(function() { isSyncing = false; });
        }

        function flashBalanceUpdate(el) {
            if (!el) return;
            var origColor = el.style.color;
            el.style.transition = 'color 0.4s ease';
            el.style.color = '#16a34a';
            setTimeout(function() { el.style.color = origColor || ''; }, 2200);
        }

        function updateSelectBalance(selector, formattedVal) {
            var opt = document.querySelector(selector);
            if (opt && formattedVal) {
                var parts = opt.textContent.split('—');
                if (parts.length > 0) {
                    opt.textContent = parts[0].trim() + ' — ' + formattedVal;
                }
            }
        }

        // Defensive Modal Toggle Interceptor: Prevents Bootstrap backdrop null crashes
        $(document).on('click', '[data-bs-toggle="modal"]', function(e) {
            var target = $(this).attr('data-bs-target') || $(this).attr('href');
            if (target && target.startsWith('#') && !$(target).length) {
                e.preventDefault();
                e.stopPropagation();
                console.warn('Target modal element not found: ' + target);
            }
        });

        window.csbSyncAccountLive = syncAccountLive;

        setTimeout(function() {
            syncAccountLive();
            setInterval(syncAccountLive, 4000);
        }, 2000);
    })();
    </script>
    <script src="{{ asset('assets/front/js/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/security-gate.js') }}?v=1.1"></script>
    @include('frontend::include.__security_gate_modal')
    @include('frontend::include.__account_detail_modal')
    @include('frontend::include.__manage_cards_modal')
    @include('frontend::include.__csb_documents_modal')
    @include('frontend::user.partials.ava_chat')
    <script src="{{ asset('assets/frontend/js/ava.js') }}"></script>
    @stack('js')
</body>
</html>
