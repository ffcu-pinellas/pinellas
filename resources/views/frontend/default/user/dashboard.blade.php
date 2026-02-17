@extends('frontend::layouts.user')

@section('title')
    {{ __('Dashboard') }}
@endsection

@section('content')
<div class="row mb-5">
    <div class="col-12">
        <div class="welcome-section p-4 rounded-3 text-white" style="background-color: var(--navigation-bar-color); background-image: url('https://my.pinellasfcu.org/images/fi-assets/pinellas-federal-credit-union/pinellas-federal-credit-union-background-landscape-2c77924b.png'); background-size: cover; background-position: center;">
            <h1 class="welcome-text mb-1">Items for you, {{ auth()->user()->first_name }}</h1>
            <p class="date-text mb-0">{{ now()->format('l, F j, Y') }}</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column: Accounts & Transactions -->
    <div class="col-lg-8">
        <!-- Accounts Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="h5 fw-bold mb-3" style="color: var(--body-text-theme-color);">Accounts</h3>
            </div>
            
            <!-- Checking -->
            <div class="col-md-6 mb-3">
                <div class="banno-card h-100">
                    <div class="account-card-header">
                        <span class="account-name">Checking</span>
                        <i class="fas fa-ellipsis-v text-muted"></i>
                    </div>
                    <div class="account-balance mb-1">{{ $currency }} {{ number_format(auth()->user()->balance, 2) }}</div>
                    <div class="account-details">Available Balance</div>
                    <div class="mt-3 text-end">
                        <a href="{{ route('user.fund_transfer.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">Transfer</a>
                    </div>
                </div>
            </div>

            <!-- Savings (Mock for visual completeness if not dynamic) -->
            <div class="col-md-6 mb-3">
                <div class="banno-card h-100">
                    <div class="account-card-header">
                        <span class="account-name">Primary Savings</span>
                        <i class="fas fa-ellipsis-v text-muted"></i>
                    </div>
                    <div class="account-balance mb-1">{{ $currency }} {{ number_format(50.00, 2) }}</div> <!-- Hardcoded for visual match or dynamic if available -->
                    <div class="account-details">Current Balance</div>
                    <div class="mt-3 text-end">
                        <a href="{{ route('user.fund_transfer.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">Transfer</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="banno-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h6 fw-bold mb-0">Recent Transactions</h3>
                <a href="{{ route('user.fund_transfer.log') }}" class="text-decoration-none" style="font-size: 13px; font-weight: 600;">View All</a>
            </div>
            <ul class="txn-list">
                @forelse($recentTransactions as $transaction)
                    <li class="txn-item">
                        <div class="txn-date text-uppercase">
                            <div style="font-weight: 700; font-size: 14px; color: var(--body-text-primary-color);">{{ $transaction->created_at->format('M') }}</div>
                            <div style="font-size: 18px;">{{ $transaction->created_at->format('d') }}</div>
                        </div>
                        <div class="txn-desc">
                            <div class="text-truncate">{{ $transaction->description ?? 'Transfer' }}</div>
                            <div class="small text-muted">{{ $transaction->type == 'credit' ? 'Deposit' : 'Withdrawal' }}</div>
                        </div>
                        <div class="txn-amount {{ $transaction->type == 'debit' ? 'text-danger' : 'text-success' }}">
                            {{ $transaction->type == 'debit' ? '-' : '+' }}{{ $currency }}{{ number_format($transaction->amount, 2) }}
                        </div>
                    </li>
                @empty
                    <li class="text-center py-4 text-muted">No recent transactions</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Right Column: Sidebar Widgets -->
    <div class="col-lg-4">
        <!-- Messages Widget -->
        <div class="banno-card mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-light rounded-circle p-2 me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-comment-alt text-primary"></i>
                </div>
                <div>
                    <h4 class="h6 fw-bold mb-0">Messages</h4>
                    <p class="small text-muted mb-0">Secure support chat</p>
                </div>
            </div>
            <a href="{{ route('user.messages') }}" class="btn btn-outline-primary w-100 rounded-pill">Start a conversation</a>
        </div>

        <!-- Bill Pay Widget -->
        <div class="banno-card mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-light rounded-circle p-2 me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                </div>
                <div>
                    <h4 class="h6 fw-bold mb-0">Bill Pay</h4>
                    <p class="small text-muted mb-0">Manage your payments</p>
                </div>
            </div>
            <a href="{{ route('user.bill-pay.index') }}" class="btn btn-outline-primary w-100 rounded-pill">Pay a bill</a>
        </div>

        <!-- Card Management Widget -->
        <div class="banno-card">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-light rounded-circle p-2 me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-credit-card text-primary"></i>
                </div>
                <div>
                    <h4 class="h6 fw-bold mb-0">Card Management</h4>
                    <p class="small text-muted mb-0">Control your cards</p>
                </div>
            </div>
            <button class="btn btn-outline-primary w-100 rounded-pill">Manage Cards</button>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    // Initialize tooltips or other JS interactions here if needed
</script>
@endpush
