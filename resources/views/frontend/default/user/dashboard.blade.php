@extends('frontend::layouts.user')

@section('title')
{{ __('Dashboard') }}
@endsection

@section('content')

<!-- Banno Top Greeting -->
<div class="row align-items-center mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h1 class="h3 fw-bold mb-0" style="color: var(--body-text-primary-color);">Hi, {{ auth()->user()->first_name }}</h1>
        <a href="#" class="btn btn-link text-decoration-none text-primary fw-600 small">See more</a>
    </div>
</div>

<div class="row">
    <!-- Left Column: Activity/Transactions -->
    <div class="col-lg-8 mb-4">
        <div class="site-card">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Activity</h5>
                <div class="d-flex gap-3">
                    <i class="fas fa-search text-muted"></i>
                    <i class="fas fa-ellipsis-h text-muted"></i>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="transaction-list">
                    @forelse ($recentTransactions as $transaction)
                    <div class="d-flex align-items-center justify-content-between py-3 px-4 border-bottom border-light">
                        <div class="flex-grow-1">
                            <div class="fw-600" style="color: var(--body-text-primary-color);">{{ $transaction->description }}</div>
                            <div class="text-muted small">
                                {{ $transaction->created_at->format('M d') }} • ...{{ substr(auth()->user()->account_number, -4) }}
                            </div>
                        </div>
                        <div class="fw-bold text-end">
                             <div style="color: {{ isPlusTransaction($transaction->type) ? 'var(--body-text-deposit-color, #3b8712)' : 'var(--body-text-primary-color)' }}">
                                {{ isPlusTransaction($transaction->type) ? '+' : '-' }}{{ setting('currency_symbol','global').number_format($transaction->amount, 2) }}
                             </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-3x text-light mb-3"></i>
                        <p class="text-muted">No recent activity found.</p>
                    </div>
                    @endforelse
                </div>
            </div>
            
            <div class="card-footer bg-transparent border-0 p-3">
                <a href="{{ route('user.transactions') }}" class="btn btn-light w-100 text-primary fw-bold" style="background: var(--secondary-content-background-color); border-radius: 8px;">See more activity</a>
            </div>
        </div>
    </div>

    <!-- Right Column: Quick Action Widgets -->
    <div class="col-lg-4">
        
        <!-- Transfers Widget -->
        <div class="site-card mb-4">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Transfers</h5>
                <i class="fas fa-ellipsis-h text-muted"></i>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-6 text-center">
                        <a href="{{ route('user.fund_transfer.index') }}" class="text-decoration-none d-block p-3 rounded-3 hover-bg-light">
                            <i class="fas fa-exchange-alt fs-3 text-primary mb-2"></i>
                            <div class="small fw-600 text-dark">Make a transfer</div>
                        </a>
                    </div>
                    <div class="col-6 text-center">
                        <a href="{{ route('user.fund_transfer.index') }}" class="text-decoration-none d-block p-3 rounded-3 hover-bg-light">
                            <i class="fas fa-university fs-3 text-primary mb-2"></i>
                            <div class="small fw-600 text-dark">Member Transfers</div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top p-3 text-center">
                <div class="text-muted small"><i class="far fa-calendar-alt me-2"></i>No transfers scheduled</div>
            </div>
        </div>

        <!-- Bill Pay Widget -->
        <div class="site-card mb-4">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Bill pay</h5>
                <i class="fas fa-ellipsis-h text-muted"></i>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-2">
                    <div class="col-4 text-center">
                        <a href="{{ route('user.bill-pay.index') }}" class="text-decoration-none d-block py-3 hover-bg-light rounded text-center">
                            <i class="fas fa-file-invoice-dollar fs-3 text-primary mb-2"></i>
                            <div style="font-size: 11px;" class="fw-600 text-dark">Pay a bill</div>
                        </a>
                    </div>
                    <div class="col-4 text-center">
                        <a href="{{ route('user.bill-pay.index') }}" class="text-decoration-none d-block py-3 hover-bg-light rounded text-center">
                            <i class="fas fa-user-friends fs-3 text-primary mb-2"></i>
                            <div style="font-size: 11px;" class="fw-600 text-dark">Pay a person</div>
                        </a>
                    </div>
                    <div class="col-4 text-center">
                        <a href="{{ route('user.bill-pay.index') }}" class="text-decoration-none d-block py-3 hover-bg-light rounded text-center">
                            <i class="fas fa-cog fs-3 text-primary mb-2"></i>
                            <div style="font-size: 11px;" class="fw-600 text-dark">Manage payments</div>
                        </a>
                    </div>
                </div>
            </div>
             <div class="card-footer bg-transparent border-top p-3 text-center">
                <div class="text-muted small"><i class="fas fa-history me-2"></i>No recent payments</div>
            </div>
        </div>

        <!-- Card Management -->
        <div class="site-card mb-4">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Cards</h5>
                <i class="fas fa-ellipsis-h text-muted"></i>
            </div>
            <div class="card-body p-0">
                <div class="p-4 border-bottom border-light d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-icon bg-light rounded p-2">
                            <i class="fas fa-credit-card text-muted"></i>
                        </div>
                        <div>
                            <div class="fw-600">Personal Debit</div>
                            <div class="small text-muted">...{{ substr(auth()->user()->account_number, -4) }}</div>
                        </div>
                    </div>
                    <span class="badge bg-success rounded-pill fw-normal">Active</span>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 p-3 text-center">
                <button class="btn btn-link text-decoration-none fw-bold small">Manage cards</button>
            </div>
        </div>

        <!-- Remote Deposits -->
        <div class="site-card mb-4">
             <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Remote deposits</h5>
                <i class="fas fa-ellipsis-h text-muted"></i>
            </div>
             <div class="card-body text-center py-5">
                <a href="{{ route('user.remote_deposit') }}" class="text-decoration-none d-block">
                    <i class="fas fa-camera fa-2x text-muted mb-3"></i>
                    <div class="text-dark fw-600">Deposit a check</div>
                </a>
             </div>
        </div>

    </div>
</div>

<div class="row mt-4 mb-5">
    <div class="col-12 text-center">
        <button class="btn btn-outline-secondary rounded-pill px-4 fw-600 shadow-sm" style="border-style: dashed; background: transparent;">
             Organize dashboard
        </button>
    </div>
</div>

@endsection

@section('style')
<style>
    .fw-600 { font-weight: 600; }
    .hover-bg-light:hover {
        background-color: var(--secondary-content-background-color);
        transition: background-color 0.2s ease;
    }
    .transaction-list .d-flex:last-child {
        border-bottom: none !important;
    }
    .badge {
        font-size: 11px;
        padding: 4px 10px;
    }
</style>
@endsection

