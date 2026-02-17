@extends('frontend::layouts.user')

@section('title')
    {{ __('Accounts') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="site-card">
                <div class="site-card-header">
                    <h3 class="title">{{ __('My Accounts') }}</h3>
                    <div class="card-header-links">
                        <a href="{{ route('user.dashboard') }}" class="card-header-link">{{ __('Dashboard') }}</a>
                    </div>
                </div>
                <div class="site-card-body">
                    <div class="row">
                        <!-- Checking Account (Main Wallet) -->
                        <div class="col-md-6 mb-4">
                            <div class="p-4 rounded-3 text-white" style="background: var(--hero-gradient-start); background: linear-gradient(135deg, var(--hero-gradient-start) 0%, var(--hero-gradient-end) 100%);">
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <div>
                                        <h5 class="fw-bold mb-1">Total Checking</h5>
                                        <div class="small opacity-75">Available Balance</div>
                                    </div>
                                    <i class="fas fa-wallet fs-3 opacity-50"></i>
                                </div>
                                <h2 class="mb-3 fw-bold">{{ setting('currency_symbol', 'global') }}{{ number_format($checkingBalance, 2) }}</h2>
                                <div class="d-flex justify-content-between align-items-center border-top border-white border-opacity-25 pt-3">
                                    <span class="small opacity-75">Account ****{{ substr(auth()->user()->account_number, -4) }}</span>
                                    <a href="{{ route('user.transactions') }}" class="text-white text-decoration-none small fw-bold">History <i class="fas fa-arrow-right ms-1"></i></a>
                                </div>
                            </div>
                        </div>

                        <!-- Savings Accounts Loop -->
                        @foreach($savingsAccounts as $account)
                        <div class="col-md-6 mb-4">
                            <div class="p-4 rounded-3 text-white" style="background: #1e3a8a;"> <!-- Darker Blue for Savings -->
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <div>
                                        <h5 class="fw-bold mb-1">{{ $account->type }}</h5>
                                        <div class="small opacity-75">Savings Account</div>
                                    </div>
                                    <i class="fas fa-piggy-bank fs-3 opacity-50"></i>
                                </div>
                                <h2 class="mb-3 fw-bold">{{ setting('currency_symbol', 'global') }}{{ number_format($account->balance, 2) }}</h2>
                                <div class="d-flex justify-content-between align-items-center border-top border-white border-opacity-25 pt-3">
                                    <span class="small opacity-75">Account ****{{ substr($account->account_number, -4) }}</span>
                                    <span class="badge bg-white bg-opacity-10">{{ number_format($account->interest_rate, 2) }}% APY</span>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <!-- Add New Savings Account (Placeholder functionality) -->
                        <div class="col-md-6 mb-4">
                            <div class="h-100 p-4 rounded-3 border border-2 border-dashed d-flex flex-column align-items-center justify-content-center text-muted" style="min-height: 200px; cursor: pointer;">
                                <i class="fas fa-plus-circle fs-2 mb-2"></i>
                                <span class="fw-bold">Open a new account</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
