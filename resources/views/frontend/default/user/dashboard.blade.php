@extends('frontend::layouts.user')

@section('title')
{{ __('Dashboard') }}
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <h2 class="section-title">{{ __('Accounts') }}</h2>
    </div>

    <!-- Main Account Card -->
    <div class="col-12">
        <div class="jha-card mb-4" style="border-left: 6px solid var(--body-text-theme-color);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-1" style="font-weight: 600; color: var(--body-text-theme-color);">{{ __('Standard Checking') }}</h4>
                    <p class="text-muted mb-0">...{{ substr($user->account_number, -4) }}</p>
                </div>
                <div class="text-end">
                    <h3 class="mb-0" style="font-weight: 700; color: var(--body-text-primary-color);">
                        {{ setting('currency_symbol','global').number_format($user->balance, 2) }}
                    </h3>
                    <p class="text-muted small mb-0">{{ __('Available Balance') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub-Accounts / Features -->
    <div class="col-md-6 col-12">
        <div class="jha-card">
            <h5 class="fw-bold mb-3"><i class="fas fa-piggy-bank me-2 text-theme"></i> Savings & IRAs</h5>
            <div class="d-flex justify-content-between border-bottom py-2">
                <span>Savings Account</span>
                <span class="fw-bold">{{ setting('currency_symbol','global') }}0.00</span>
            </div>
            @if(setting('user_dps','permission'))
            <div class="d-flex justify-content-between py-2">
                <span>Individual Retirement Account (IRA)</span>
                <span class="fw-bold">{{ setting('currency_symbol','global').number_format($dps_mature_amount, 2) }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="col-md-6 col-12">
        <div class="jha-card">
            <h5 class="fw-bold mb-3"><i class="fas fa-landmark me-2 text-theme"></i> Investments & Loans</h5>
            @if(setting('user_fdr','permission'))
            <div class="d-flex justify-content-between border-bottom py-2">
                <span>Certificate of Deposit (CD)</span>
                <span class="fw-bold">{{ setting('currency_symbol','global').number_format($fdr_mature_amount, 2) }}</span>
            </div>
            @endif
            @if(setting('user_loan','permission'))
            <div class="d-flex justify-content-between py-2">
                <span>Active Loans</span>
                <span class="fw-bold text-danger">{{ setting('currency_symbol','global').number_format($total_loan_amount, 2) }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="col-12 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="section-title mb-0">{{ __('Recent Activity') }}</h2>
            <a href="{{ route('user.transactions') }}" class="btn btn-link text-theme text-decoration-none fw-bold">View all</a>
        </div>
        
        <div class="jha-card p-0 overflow-hidden">
            @forelse ($recentTransactions as $transaction)
            <div class="d-flex align-items-center justify-content-between py-3 px-4 border-bottom transaction-item" style="transition: background 0.2s;">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box text-center" style="width: 40px;">
                        @if($transaction->type->value == 'deposit' || $transaction->type->value == 'manual_deposit')
                            <i class="fas fa-arrow-alt-circle-down text-success fa-lg"></i>
                        @elseif($transaction->type->value == 'withdraw' || $transaction->type->value == 'manual_withdraw')
                            <i class="fas fa-arrow-alt-circle-up text-danger fa-lg"></i>
                        @else
                            <i class="fas fa-exchange-alt text-theme fa-lg"></i>
                        @endif
                    </div>
                    <div>
                        <div class="fw-bold" style="color: var(--body-text-primary-color);">{{ $transaction->description }}</div>
                        <div class="text-muted small">{{ $transaction->created_at->format('M d, Y') }}</div>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-bold {{ isPlusTransaction($transaction->type) ? 'text-success' : 'text-danger' }}" style="font-size: 16px;">
                        {{ isPlusTransaction($transaction->type) ? '+' : '-' }}{{ $transaction->amount.' '.transaction_currency($transaction) }}
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-info-circle fa-2x mb-2"></i>
                <p>{{ __('No recent activity found.') }}</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@section('style')
<style>
    .transaction-item:hover {
        background: rgba(0, 84, 155, 0.02);
    }
</style>
@endsection
