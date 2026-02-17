@extends('frontend::layouts.user')

@section('title')
{{ __('Dashboard') }}
@endsection

@section('content')

<!-- High Fidelity Banno Header -->
<div class="banno-header-block">
    <div class="banno-header-grid">
        <h1 class="banno-greeting">Hi, {{ auth()->user()->first_name }}</h1>
        <div class="text-white opacity-75 mb-4">You have 2 messages</div>
    </div>
    
    <!-- Floating Account Cards Overlay -->
    <div class="account-cards-overlay">
        <div class="banno-account-card">
            <div class="acc-name">SHARE SAVINGS</div>
            <div class="acc-num">...{{ substr($user->account_number, -4) }}</div>
            <div class="acc-balance">{{ setting('currency_symbol','global').number_format($user->balance, 2) }}</div>
            <div class="small opacity-75 mt-1">Available balance</div>
        </div>
        
        <div class="banno-account-card" style="background: var(--account-card-secondary-background-color);">
            <div class="acc-name">RESTORE CHECKING</div>
            <div class="acc-num">...8821</div>
            <div class="acc-balance">{{ setting('currency_symbol','global') }}1,240.52</div>
            <div class="small opacity-75 mt-1">Available balance</div>
        </div>
        
        <div class="banno-account-card" style="background: #3b8712;">
            <div class="acc-name">MEMBER REWARDS</div>
            <div class="acc-num">Points: 4,520</div>
            <div class="acc-balance">$45.20</div>
            <div class="small opacity-75 mt-1">Cash value</div>
        </div>
    </div>
</div>

<div class="row" style="margin-top: 20px;">
    <!-- Main Content Area (Activity) -->
    <div class="col-lg-8">
        
        <!-- Action Quick Grid -->
        <div class="banno-action-grid">
            <a href="{{ route('user.fund_transfer.index') }}" class="banno-action-item">
                <i class="fas fa-exchange-alt"></i>
                <span class="banno-action-label">Transfer</span>
            </a>
            <a href="{{ route('user.fund_transfer.index') }}" class="banno-action-item">
                <i class="fas fa-user-friends"></i>
                <span class="banno-action-label">Pay person</span>
            </a>
            <a href="{{ route('user.bill-pay.index') }}" class="banno-action-item">
                <i class="fas fa-file-invoice-dollar"></i>
                <span class="banno-action-label">Bill pay</span>
            </a>
            <a href="{{ route('user.remote_deposit') }}" class="banno-action-item">
                <i class="fas fa-mobile-alt"></i>
                <span class="banno-action-label">Deposit</span>
            </a>
            <a href="{{ route('user.messages') }}" class="banno-action-item">
                <i class="fas fa-comment-dots"></i>
                <span class="banno-action-label">Support</span>
            </a>
        </div>

        <!-- Activity List Card -->
        <div class="site-card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">Activity</h5>
                <div class="d-flex gap-3 text-secondary">
                    <i class="fas fa-search cursor-pointer"></i>
                    <i class="fas fa-sliders-h cursor-pointer"></i>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="transaction-list">
                    @forelse ($recentTransactions as $transaction)
                    <div class="d-flex align-items-center justify-content-between py-3 px-4 border-bottom border-light">
                        <div class="flex-grow-1">
                            <div class="fw-bold" style="color: var(--body-text-primary-color); font-size: 15px;">{{ $transaction->description }}</div>
                            <div class="text-muted small">
                                {{ $transaction->created_at->format('M d') }} • ...{{ substr(auth()->user()->account_number, -4) }}
                            </div>
                        </div>
                        <div class="text-end">
                             <div class="fw-bold" style="color: {{ isPlusTransaction($transaction->type) ? '#3b8712' : 'var(--body-text-primary-color)' }}; font-size: 16px;">
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
            
            <div class="card-footer bg-white border-0 p-3 pt-0">
                <a href="{{ route('user.transactions') }}" class="btn btn-light w-100 text-primary fw-bold py-2" style="background: #f8f9fa; border-radius: 8px;">See more activity</a>
            </div>
        </div>
    </div>

    <!-- Right Sidebar Widgets -->
    <div class="col-lg-4">
        <!-- Messages Widget -->
        <div class="jha-card p-4">
             <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Messages</h6>
                <span class="badge bg-primary rounded-pill">2</span>
            </div>
            <div class="message-preview small text-muted mb-3">
                <strong>PFCU Support</strong>: Your card replacement has been...
            </div>
            <a href="{{ route('user.messages') }}" class="btn btn-outline-primary w-100 btn-sm rounded-pill fw-bold">View all</a>
        </div>

        <!-- Card Management -->
        <div class="jha-card p-4">
            <h6 class="fw-bold mb-3">Cards</h6>
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="bg-light rounded p-2">
                    <i class="fas fa-credit-card text-primary"></i>
                </div>
                <div>
                    <div class="fw-bold small">Personal Debit</div>
                    <div class="text-muted" style="font-size: 11px;">...{{ substr(auth()->user()->account_number, -4) }}</div>
                </div>
                <div class="ms-auto">
                    <span class="badge bg-success-light text-success rounded-pill" style="font-size: 10px;">ACTIVE</span>
                </div>
            </div>
            <button class="btn btn-link p-0 text-primary fw-bold small text-decoration-none">Manage my cards</button>
        </div>
    </div>
</div>

<div class="row mt-4 mb-5">
    <div class="col-12 text-center">
        <button class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm" style="border-style: dashed; background: transparent; font-size: 13px;">
             Organize dashboard
        </button>
    </div>
</div>

@endsection

@section('style')
<style>
    .bg-success-light { background-color: #e8f5e9; }
    .cursor-pointer { cursor: pointer; }
    .transaction-list .d-flex:hover { background-color: #fcfcfc; }
</style>
@endsection

