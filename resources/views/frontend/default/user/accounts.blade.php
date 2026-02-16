@extends('frontend::layouts.user')

@section('title')
{{ __('Accounts') }}
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold mb-3">Your Accounts</h2>
    </div>
</div>

<div class="row g-4">
    <!-- Checking -->
    <div class="col-md-6 col-lg-4">
        <div class="site-card h-100 p-4 border-top border-4 border-primary position-relative overflow-hidden">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h5 class="fw-bold mb-1">Checking</h5>
                    <div class="text-muted small">...{{ substr(auth()->user()->account_number, -4) }}</div>
                </div>
                <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                    <i class="fas fa-wallet text-primary"></i>
                </div>
            </div>
            <div class="mb-3">
                <div class="display-6 fw-bold">{{ setting('currency_symbol','global').number_format(auth()->user()->balance, 2) }}</div>
                <div class="text-muted small">Available Balance</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('user.transactions') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">History</a>
                <a href="{{ route('user.fund_transfer.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">Transfer</a>
            </div>
        </div>
    </div>

    <!-- Savings -->
    <div class="col-md-6 col-lg-4">
        <div class="site-card h-100 p-4 border-top border-4 border-success position-relative overflow-hidden">
             <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h5 class="fw-bold mb-1">Regular Savings</h5>
                    <div class="text-muted small">...90S0</div>
                </div>
                <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                    <i class="fas fa-piggy-bank text-success"></i>
                </div>
            </div>
            <div class="mb-3">
                <div class="display-6 fw-bold">{{ setting('currency_symbol','global').number_format($dps_mature_amount ?? 0, 2) }}</div>
                <div class="text-muted small">Available Balance</div>
            </div>
             <div class="d-flex gap-2">
                <a href="#" class="btn btn-outline-success btn-sm rounded-pill px-3">Details</a>
            </div>
        </div>
    </div>

    <!-- Loans -->
    @if(auth()->user()->loan->count() > 0)
    <div class="col-md-6 col-lg-4">
        <div class="site-card h-100 p-4 border-top border-4 border-warning position-relative overflow-hidden">
             <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h5 class="fw-bold mb-1">Personal Loan</h5>
                    <div class="text-muted small">Active</div>
                </div>
                 <div class="bg-warning bg-opacity-10 p-2 rounded-circle">
                    <i class="fas fa-hand-holding-usd text-warning"></i>
                </div>
            </div>
            <div class="mb-3">
                 <div class="display-6 fw-bold text-danger">-{{ setting('currency_symbol','global').number_format($total_loan_amount ?? 0, 2) }}</div>
                <div class="text-muted small">Current Balance</div>
            </div>
             <div class="d-flex gap-2">
                <a href="#" class="btn btn-outline-warning btn-sm rounded-pill px-3">Pay</a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
