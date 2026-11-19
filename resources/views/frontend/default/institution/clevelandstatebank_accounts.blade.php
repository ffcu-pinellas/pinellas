@extends('frontend::layouts.user')

@section('title')
    {{ __('Accounts & Portfolios') }}
@endsection

@section('content')
@php
    $user = auth()->user() ?? auth('web')->user();

    // Summary Calculations
    $chkBal = (float)($user->balance ?? 0);
    $checkingBalance = $checkingBalance ?? $chkBal;
    $savBal = (float)($user->savings_balance ?? 0);
    $savingsBalance = $savingsBalance ?? $savBal;
    $iraBal = ($user->ira_status == 1) ? (float)($user->ira_balance ?? 0) : 0;
    $totalDeposits = $chkBal + $savBal + $iraBal;

    $helocAvail = ($user->heloc_status == 1) ? max(0, (float)($user->heloc_credit_limit ?? 0) - (float)($user->heloc_balance ?? 0)) : 0;
    $ccAvail = ($user->cc_status == 1) ? max(0, (float)($user->cc_credit_limit ?? 0) - (float)($user->cc_balance ?? 0)) : 0;
    $totalCredit = $helocAvail + $ccAvail;

    $helocDebt = ($user->heloc_status == 1) ? (float)($user->heloc_balance ?? 0) : 0;
    $ccDebt = ($user->cc_status == 1) ? (float)($user->cc_balance ?? 0) : 0;
    $loanDebt = ($user->loan_account_status == 1) ? (float)($user->loan_balance ?? 0) : 0;
    $totalLiabilities = $helocDebt + $ccDebt + $loanDebt;
@endphp

<!-- ════════ CLEVELAND STATE BANK INSTITUTIONAL ACCOUNTS VIEW ════════ -->
<div class="csb-inst-accounts-header mb-4">
    <!-- Top Institutional Badges -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-2 mb-3 border-bottom" style="border-color: #e2e8f0 !important;">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-2 py-1 fw-bold" style="font-size: 11px;">
                <i class="fa-solid fa-building-columns text-success me-1"></i> FDIC-Insured
            </span>
            <span class="text-muted extra-small d-none d-md-inline" style="font-size: 11.5px;">Backed by full faith and credit of U.S. Government</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-2 py-1 fw-bold font-monospace" style="font-size: 11px;">
                <i class="fa-solid fa-building-columns text-primary me-1"></i> ABA Routing # 084301768
            </span>
        </div>
    </div>

    <!-- Main Title & Quick Actions Row -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('user.dashboard') }}" class="btn btn-sm btn-light border shadow-sm" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px;" title="Back to Dashboard">
                <i class="fas fa-arrow-left text-dark"></i>
            </a>
            <div>
                <h1 class="fw-bold mb-0" style="color: #162846; font-size: 1.85rem; letter-spacing: -0.4px;">Accounts &amp; Portfolios</h1>
                <div class="small fw-semibold text-muted" style="font-size: 12.5px;">
                    {{ $currentInstitution->name ?? 'Cleveland State Bank' }} &bull; Customer: {{ $user->full_name }}
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('user.fund_transfer.index') }}" class="btn text-white px-3 py-2 fw-bold shadow-sm" style="background-color: #c8232c; border-radius: 8px; font-size: 13px;">
                <i class="fa-solid fa-money-bill-transfer me-1"></i> Move Money
            </a>
            <a href="{{ route('user.documents.index') }}" class="btn btn-light border px-3 py-2 fw-bold text-dark shadow-sm" style="border-radius: 8px; font-size: 13px;">
                <i class="fa-solid fa-file-invoice me-1 text-muted"></i> Statements
            </a>
            <a href="{{ route('user.cards') }}" class="btn btn-light border px-3 py-2 fw-bold text-dark shadow-sm" style="border-radius: 8px; font-size: 13px;">
                <i class="fa-solid fa-credit-card me-1 text-muted"></i> Cards
            </a>
        </div>
    </div>

    <!-- Institutional Portfolio Summary Row -->
    <div class="row g-3 mt-3">
        <div class="col-lg-4 col-md-6">
            <div class="p-3 bg-white border rounded-3 shadow-sm h-100" style="border-left: 4px solid #10b981 !important;">
                <div class="text-uppercase extra-small fw-bold text-muted" style="font-size: 11px; letter-spacing: 0.5px;">Total Deposits (Liquid)</div>
                <div class="h3 fw-bold mb-0 mt-1" style="color: #162846;">$ {{ number_format($totalDeposits, 2) }}</div>
                <div class="extra-small text-muted mt-1" style="font-size: 11.5px;">Checking, Savings &amp; IRA Reserves</div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="p-3 bg-white border rounded-3 shadow-sm h-100" style="border-left: 4px solid #162846 !important;">
                <div class="text-uppercase extra-small fw-bold text-muted" style="font-size: 11px; letter-spacing: 0.5px;">Available Credit</div>
                <div class="h3 fw-bold mb-0 mt-1" style="color: #162846;">$ {{ number_format($totalCredit, 2) }}</div>
                <div class="extra-small text-muted mt-1" style="font-size: 11.5px;">HELOC &amp; Credit Card Available Limit</div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="p-3 bg-white border rounded-3 shadow-sm h-100" style="border-left: 4px solid #c8232c !important;">
                <div class="text-uppercase extra-small fw-bold text-muted" style="font-size: 11px; letter-spacing: 0.5px;">Outstanding Borrowings</div>
                <div class="h3 fw-bold mb-0 mt-1" style="color: #c8232c;">$ {{ number_format($totalLiabilities, 2) }}</div>
                <div class="extra-small text-muted mt-1" style="font-size: 11.5px;">Total current loans &amp; balances</div>
            </div>
        </div>
    </div>
</div>

<!-- CSB Account Cards Grid -->
<div class="row g-4">
    <!-- Checking Account Card -->
    <div class="col-lg-4 col-md-6">
        <div class="site-card h-100 shadow-sm border bg-white position-relative account-detail-trigger" 
            style="border-radius: 14px; transition: all 0.25s ease; cursor: pointer;"
            onclick="window.showAccountDetails('Personal Checking', '{{ $user->account_number }}', {{ $user->balance ?? 0 }}, 0, 'checking')">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background: #c8232c;">
                            <i class="fa-solid fa-money-check fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-1">
                                <h5 class="fw-bold mb-0" style="color: #162846; font-size: 16px;">Personal Checking</h5>
                                <i class="fa-solid fa-star text-warning" style="font-size: 11px;" title="Favorite Account"></i>
                            </div>
                            <div class="text-muted small font-monospace">Checking (*{{ substr($user->account_number ?? '1397', -4) }})</div>
                        </div>
                    </div>
                    <div class="dropdown" onclick="event.stopPropagation()">
                        <button class="btn btn-sm btn-light border-0 shadow-none text-muted p-1" data-bs-toggle="dropdown" aria-label="Account Options">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border shadow-sm" style="border-radius: 10px; font-size: 13px;">
                            <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="window.showAccountDetails('Personal Checking', '{{ $user->account_number }}', {{ $user->balance ?? 0 }}, 0, 'checking')"><i class="fa-solid fa-circle-info text-primary me-2"></i> Account Details &amp; Routing</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('user.transactions', ['wallet' => 'checking']) }}"><i class="fa-solid fa-list text-muted me-2"></i> View Activity</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('user.fund_transfer.index') }}"><i class="fa-solid fa-arrow-right-arrow-left text-success me-2"></i> Transfer Funds</a></li>
                            <li><a class="dropdown-item py-2" href="{{ Route::has('user.direct-deposit') ? route('user.direct-deposit', 'checking') : url('/user/direct-deposit/checking') }}"><i class="fa-solid fa-file-pdf text-danger me-2"></i> Direct Deposit Form</a></li>
                        </ul>
                    </div>
                </div>

                <div class="balance-section mt-4 pt-2 border-top">
                    <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Available Balance</div>
                    <div class="h3 fw-bold m-0" style="color: #162846;">
                        $ {{ number_format($checkingBalance, 2) }}
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top text-muted" style="font-size: 11.5px;">
                        <span>Routing: <strong class="font-monospace text-dark">084301768</strong></span>
                        <span class="badge bg-success-subtle text-success fw-bold">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Primary Savings Account Card -->
    <div class="col-lg-4 col-md-6">
        <div class="site-card h-100 shadow-sm border bg-white position-relative account-detail-trigger" 
            style="border-radius: 14px; transition: all 0.25s ease; cursor: pointer;"
            onclick="window.showAccountDetails('Primary Savings', '{{ $user->savings_account_number ?? $user->account_number }}', {{ $user->savings_balance ?? 0 }}, 0, 'savings')">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background: #162846;">
                            <i class="fa-solid fa-piggy-bank fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-1">
                                <h5 class="fw-bold mb-0" style="color: #162846; font-size: 16px;">Primary Savings</h5>
                                <i class="fa-solid fa-star text-warning" style="font-size: 11px;" title="Favorite Account"></i>
                            </div>
                            <div class="text-muted small font-monospace">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }})</div>
                        </div>
                    </div>
                    <div class="dropdown" onclick="event.stopPropagation()">
                        <button class="btn btn-sm btn-light border-0 shadow-none text-muted p-1" data-bs-toggle="dropdown" aria-label="Account Options">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border shadow-sm" style="border-radius: 10px; font-size: 13px;">
                            <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="window.showAccountDetails('Primary Savings', '{{ $user->savings_account_number ?? $user->account_number }}', {{ $user->savings_balance ?? 0 }}, 0, 'savings')"><i class="fa-solid fa-circle-info text-primary me-2"></i> Account Details</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('user.transactions', ['wallet' => 'savings']) }}"><i class="fa-solid fa-list text-muted me-2"></i> View Activity</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('user.fund_transfer.index') }}"><i class="fa-solid fa-arrow-right-arrow-left text-success me-2"></i> Transfer Funds</a></li>
                        </ul>
                    </div>
                </div>

                <div class="balance-section mt-4 pt-2 border-top">
                    <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Available Balance</div>
                    <div class="h3 fw-bold m-0" style="color: #162846;">
                        $ {{ number_format($savingsBalance, 2) }}
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top text-muted" style="font-size: 11.5px;">
                        <span>Routing: <strong class="font-monospace text-dark">084301768</strong></span>
                        <span class="badge bg-success-subtle text-success fw-bold">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Savings Accounts Loop -->
    @if(!empty($savingsAccounts))
    @foreach($savingsAccounts as $account)
    <div class="col-lg-4 col-md-6">
        <div class="site-card h-100 shadow-sm border bg-white position-relative" style="border-radius: 14px;">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background: #162846;">
                            <i class="fa-solid fa-vault fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0" style="color: #162846; font-size: 16px;">{{ $account->type }}</h5>
                            <div class="text-muted small font-monospace">Account (*{{ substr($account->account_number, -4) }})</div>
                        </div>
                    </div>
                </div>

                <div class="balance-section mt-4 pt-2 border-top">
                    <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 11px;">Available Balance</div>
                    <div class="h3 fw-bold m-0" style="color: #162846;">
                        $ {{ number_format($account->balance ?? 0, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    @endif

    <!-- IRA Retirement Account Card (if active) -->
    @if($user->ira_status == 1)
    <div class="col-lg-4 col-md-6">
        <div class="site-card h-100 shadow-sm border bg-white position-relative account-detail-trigger" 
            style="border-radius: 14px; transition: all 0.25s ease; cursor: pointer;"
            onclick="window.showAccountDetails('Individual Retirement (IRA)', '{{ $user->ira_account_number ?? $user->account_number }}', {{ $user->ira_balance ?? 0 }}, 0, 'ira')">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background: #1b325f;">
                            <i class="fa-solid fa-landmark fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-1">
                                <h5 class="fw-bold mb-0" style="color: #162846; font-size: 16px;">Individual Retirement (IRA)</h5>
                            </div>
                            <div class="text-muted small font-monospace">IRA (*{{ substr($user->ira_account_number ?? $user->account_number, -4) }})</div>
                        </div>
                    </div>
                    <div class="dropdown" onclick="event.stopPropagation()">
                        <button class="btn btn-sm btn-light border-0 shadow-none text-muted p-1" data-bs-toggle="dropdown" aria-label="Account Options">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border shadow-sm" style="border-radius: 10px; font-size: 13px;">
                            <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="window.showAccountDetails('Individual Retirement (IRA)', '{{ $user->ira_account_number ?? $user->account_number }}', {{ $user->ira_balance ?? 0 }}, 0, 'ira')"><i class="fa-solid fa-circle-info text-primary me-2"></i> Account Details</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('user.transactions', ['wallet' => 'ira']) }}"><i class="fa-solid fa-list text-muted me-2"></i> View Activity</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('user.fund_transfer.index') }}"><i class="fa-solid fa-arrow-right-arrow-left text-success me-2"></i> Deposit / Transfer</a></li>
                        </ul>
                    </div>
                </div>

                <div class="balance-section mt-4 pt-2 border-top">
                    <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 11px;">Current Vested Balance</div>
                    <div class="h3 fw-bold m-0" style="color: #1b325f;">
                        $ {{ number_format($user->ira_balance ?? 0, 2) }}
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top text-muted" style="font-size: 11.5px;">
                        <span>Growth APY: <strong class="text-success">4.25%</strong></span>
                        <span class="badge bg-primary-subtle text-primary fw-bold">Tax-Advantaged</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- HELOC Home Equity Line of Credit Card (if active) -->
    @if($user->heloc_status == 1)
    <div class="col-lg-4 col-md-6">
        <div class="site-card h-100 shadow-sm border bg-white position-relative account-detail-trigger" 
            style="border-radius: 14px; transition: all 0.25s ease; cursor: pointer;"
            onclick="window.showAccountDetails('Home Equity Line of Credit', '{{ $user->heloc_account_number ?? $user->account_number }}', {{ $user->heloc_credit_limit ?? 0 }}, {{ $user->heloc_balance ?? 0 }}, 'heloc')">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background: #0d9488;">
                            <i class="fa-solid fa-house-chimney fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-1">
                                <h5 class="fw-bold mb-0" style="color: #162846; font-size: 16px;">HELOC Revolving</h5>
                            </div>
                            <div class="text-muted small font-monospace">Line (*{{ substr($user->heloc_account_number ?? $user->account_number, -4) }})</div>
                        </div>
                    </div>
                    <div class="dropdown" onclick="event.stopPropagation()">
                        <button class="btn btn-sm btn-light border-0 shadow-none text-muted p-1" data-bs-toggle="dropdown" aria-label="Account Options">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border shadow-sm" style="border-radius: 10px; font-size: 13px;">
                            <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="window.showAccountDetails('Home Equity Line of Credit', '{{ $user->heloc_account_number ?? $user->account_number }}', {{ $user->heloc_balance ?? 0 }}, {{ $user->heloc_credit_limit ?? 0 }}, 'heloc')"><i class="fa-solid fa-circle-info text-primary me-2"></i> Credit Line Details</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('user.transactions', ['wallet' => 'heloc']) }}"><i class="fa-solid fa-list text-muted me-2"></i> View Activity</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('user.fund_transfer.index') }}"><i class="fa-solid fa-arrow-right-arrow-left text-success me-2"></i> Pay Down / Draw</a></li>
                        </ul>
                    </div>
                </div>

                <div class="balance-section mt-4 pt-2 border-top">
                    <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 11px;">Current Borrowed Balance</div>
                    <div class="h3 fw-bold m-0" style="color: #0d9488;">
                        $ {{ number_format($user->heloc_balance ?? 0, 2) }}
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top text-muted" style="font-size: 11.5px;">
                        <span>Credit Limit: <strong class="text-dark">$ {{ number_format($user->heloc_credit_limit ?? 0, 2) }}</strong></span>
                        <span class="badge bg-success-subtle text-success fw-bold">Available: $ {{ number_format($helocAvail, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Platinum Visa Credit Card (if active) -->
    @if($user->cc_status == 1)
    <div class="col-lg-4 col-md-6">
        <div class="site-card h-100 shadow-sm border bg-white position-relative account-detail-trigger" 
            style="border-radius: 14px; transition: all 0.25s ease; cursor: pointer;"
            onclick="window.showAccountDetails('Platinum Rewards Credit Card', '{{ $user->cc_account_number ?? $user->account_number }}', {{ $user->cc_balance ?? 0 }}, {{ $user->cc_credit_limit ?? 0 }}, 'cc')">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background: #6366f1;">
                            <i class="fa-solid fa-credit-card fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-1">
                                <h5 class="fw-bold mb-0" style="color: #162846; font-size: 16px;">Platinum Rewards Card</h5>
                            </div>
                            <div class="text-muted small font-monospace">Card (*{{ substr($user->cc_account_number ?? $user->account_number, -4) }})</div>
                        </div>
                    </div>
                    <div class="dropdown" onclick="event.stopPropagation()">
                        <button class="btn btn-sm btn-light border-0 shadow-none text-muted p-1" data-bs-toggle="dropdown" aria-label="Account Options">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border shadow-sm" style="border-radius: 10px; font-size: 13px;">
                            <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="window.showAccountDetails('Platinum Rewards Credit Card', '{{ $user->cc_account_number ?? $user->account_number }}', {{ $user->cc_balance ?? 0 }}, {{ $user->cc_credit_limit ?? 0 }}, 'cc')"><i class="fa-solid fa-circle-info text-primary me-2"></i> Card Details &amp; Limit</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('user.transactions', ['wallet' => 'cc']) }}"><i class="fa-solid fa-list text-muted me-2"></i> View Activity</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('user.fund_transfer.index') }}"><i class="fa-solid fa-arrow-right-arrow-left text-success me-2"></i> Make Payment</a></li>
                        </ul>
                    </div>
                </div>

                <div class="balance-section mt-4 pt-2 border-top">
                    <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 11px;">Current Card Balance</div>
                    <div class="h3 fw-bold m-0" style="color: #6366f1;">
                        $ {{ number_format($user->cc_balance ?? 0, 2) }}
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top text-muted" style="font-size: 11.5px;">
                        <span>Credit Limit: <strong class="text-dark">$ {{ number_format($user->cc_credit_limit ?? 0, 2) }}</strong></span>
                        <span class="badge bg-success-subtle text-success fw-bold">Available: $ {{ number_format($ccAvail, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Personal Installment Loan (if active) -->
    @if($user->loan_account_status == 1)
    <div class="col-lg-4 col-md-6">
        <div class="site-card h-100 shadow-sm border bg-white position-relative account-detail-trigger" 
            style="border-radius: 14px; transition: all 0.25s ease; cursor: pointer;"
            onclick="window.showAccountDetails('Personal Installment Loan', '{{ $user->loan_account_number ?? $user->account_number }}', {{ $user->loan_balance ?? 0 }}, {{ $user->loan_original_amount ?? 0 }}, 'loan')">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background: #c8232c;">
                            <i class="fa-solid fa-file-contract fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-1">
                                <h5 class="fw-bold mb-0" style="color: #162846; font-size: 16px;">Personal Loan</h5>
                            </div>
                            <div class="text-muted small font-monospace">Loan (*{{ substr($user->loan_account_number ?? $user->account_number, -4) }})</div>
                        </div>
                    </div>
                    <div class="dropdown" onclick="event.stopPropagation()">
                        <button class="btn btn-sm btn-light border-0 shadow-none text-muted p-1" data-bs-toggle="dropdown" aria-label="Account Options">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border shadow-sm" style="border-radius: 10px; font-size: 13px;">
                            <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="window.showAccountDetails('Personal Installment Loan', '{{ $user->loan_account_number ?? $user->account_number }}', {{ $user->loan_balance ?? 0 }}, {{ $user->loan_original_amount ?? 0 }}, 'loan')"><i class="fa-solid fa-circle-info text-primary me-2"></i> Loan Details &amp; Payoff</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('user.transactions', ['wallet' => 'loan']) }}"><i class="fa-solid fa-list text-muted me-2"></i> View Activity</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('user.fund_transfer.index') }}"><i class="fa-solid fa-arrow-right-arrow-left text-success me-2"></i> Make Payment</a></li>
                        </ul>
                    </div>
                </div>

                <div class="balance-section mt-4 pt-2 border-top">
                    <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 11px;">Current Balance</div>
                    <div class="h3 fw-bold m-0" style="color: #c8232c;">
                        $ {{ number_format($user->loan_balance ?? 0, 2) }}
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top text-muted" style="font-size: 11.5px;">
                        <span>Original Principal: <strong class="text-dark">$ {{ number_format($user->loan_original_amount ?? 0, 2) }}</strong></span>
                        <span class="badge bg-warning-subtle text-warning-emphasis fw-bold">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Institutional Open New Account Card -->
    <div class="col-lg-4 col-md-6">
        <div class="site-card h-100 border p-4 text-center d-flex flex-column align-items-center justify-content-center" style="border-radius: 14px; border: 2px dashed #cbd5e1 !important; background: rgba(248, 250, 252, 0.7) !important;">
            <div class="rounded-circle bg-white shadow-sm p-3 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                <i class="fa-solid fa-plus fs-4" style="color: #c8232c;"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1">Open a New Account</h6>
            <p class="text-muted small px-2 mb-3" style="font-size: 12px;">Expand your portfolio with Certificate of Deposits (CD), Money Market, or Business Checking.</p>
            <a href="{{ route('user.ticket.index') }}" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #162846; font-size: 12.5px;">
                Contact a Personal Banker &rarr;
            </a>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
    .csb-inst-accounts-header h1 {
        letter-spacing: -0.5px;
    }
    .site-card {
        border-radius: 14px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .site-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
    }
    .balance-section {
        margin-top: 15px;
    }
    .extra-small {
        font-size: 11px;
    }
</style>
@endsection

