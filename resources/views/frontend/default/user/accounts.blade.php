@extends('frontend::layouts.user')

@section('title')
    {{ __('Accounts') }}
@endsection

@section('content')
<!-- Banno Accounts Header (Blue Water Theme) -->
<div class="accounts-header-banno overflow-hidden mb-5" style="background: var(--account-card-primary-background-color) url('https://www.pinellasfcu.org/templates/pinellas/images/bg-main.jpg') center/cover; margin: -32px -32px 32px -32px; padding: 64px 32px 100px 32px; position: relative; border-radius: 0 0 20px 20px;">
    <div class="position-relative z-1">
        <h1 class="text-white display-5 fw-bold mb-2">Accounts</h1>
        <div class="text-white opacity-75 small fw-600">PINELLAS FEDERAL CREDIT UNION</div>
    </div>
    <!-- Water ripple decoration -->
    <div class="position-absolute bottom-0 start-0 w-100" style="height: 50px; background: linear-gradient(to top, rgba(255,255,255,0.1), transparent);"></div>
</div>

<div class="row" style="margin-top: -80px;">
    <!-- Account Cards Grid -->
    <div class="col-12">
        <div class="row g-4">
            
            <!-- Checking Account Card -->
            <div class="col-lg-4 col-md-6">
                <div class="site-card h-100 shadow-lg border-0" style="border-radius: 12px; transition: transform 0.3s ease;">
                    <div class="p-4 bg-white">
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <h5 class="fw-bold mb-0" style="color: var(--account-card-primary-background-color);">Personal Checking</h5>
                                <div class="text-muted small">{{ auth()->user()->account_number }}</div>
                            </div>
                            <div class="dropdown">
                                <i class="fas fa-ellipsis-v text-muted" role="button" data-bs-toggle="dropdown"></i>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                    <li><a class="dropdown-item" href="{{ route('user.transactions') }}">View activity</a></li>
                                    <li><a class="dropdown-item" href="{{ route('user.fund_transfer.index') }}">Transfer funds</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="balance-section">
                            <div class="text-muted small mb-1">Available balance</div>
                            <div class="h3 fw-bold m-0" style="color: var(--body-text-primary-color);">
                                {{ setting('currency_symbol','$').number_format($checkingBalance, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Primary Savings Account Card -->
            <div class="col-lg-4 col-md-6">
                <div class="site-card h-100 shadow-lg border-0" style="border-radius: 12px; transition: transform 0.3s ease;">
                    <div class="p-4 bg-white">
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <h5 class="fw-bold mb-0" style="color: var(--account-card-primary-background-color);">Primary Savings</h5>
                                <div class="text-muted small">{{ $savingsAccountNumber }}</div>
                            </div>
                            <div class="dropdown">
                                <i class="fas fa-ellipsis-v text-muted" role="button" data-bs-toggle="dropdown"></i>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                    <li><a class="dropdown-item" href="{{ route('user.transactions') }}">View activity</a></li>
                                    <li><a class="dropdown-item" href="{{ route('user.fund_transfer.index') }}">Transfer funds</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="balance-section">
                            <div class="text-muted small mb-1">Available balance</div>
                            <div class="h3 fw-bold m-0" style="color: var(--body-text-primary-color);">
                                {{ setting('currency_symbol','$').number_format($savingsBalance, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Accounts can be added here if needed in future -->

            <!-- Add New Account -->
            <div class="col-lg-4 col-md-6">
                <div class="site-card h-100 border-0" style="border-radius: 12px; border: 2px dashed #ddd !important; background: transparent !important; box-shadow: none !important;">
                    <div class="p-4 h-100 d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="rounded-circle bg-white shadow-sm p-3 mb-3">
                            <i class="fas fa-plus text-primary fa-lg"></i>
                        </div>
                        <h6 class="fw-bold">Open a new account</h6>
                        <p class="text-muted small px-3">Start earning more with our high-yield savings options.</p>
                        <a href="{{ route('register') }}" class="btn btn-primary rounded-pill px-4 mt-2">Get started</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@section('style')
<style>
    .accounts-header-banno h1 {
        letter-spacing: -1px;
    }
    .fw-600 { font-weight: 600; }
    .site-card:hover {
        transform: translateY(-8px);
    }
    .balance-section {
        margin-top: 30px;
    }
</style>
@endsection

