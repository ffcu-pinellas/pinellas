@extends('frontend::layouts.user')

@section('title')
{{ __('Dashboard') }}
@endsection

@section('content')

<!-- Hero Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner p-4 rounded-3 text-white position-relative overflow-hidden" 
             style="background: linear-gradient(135deg, var(--hero-gradient-start) 0%, var(--hero-gradient-end) 100%); min-height: 300px;">
            
            <div class="position-relative z-1">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="display-6 fw-bold m-0">Hi, {{ auth()->user()->first_name }}</h1>
                    <a href="{{ route('user.setting.show') }}" class="text-white text-decoration-none">
                        <i class="fas fa-cog fs-5"></i>
                    </a>
                </div>
                
                <h6 class="mb-3 opacity-75 text-uppercase fw-bold" style="font-size: 0.8rem; letter-spacing: 1px;">Accounts</h6>
                
                <!-- Accounts Carousel -->
                <div class="d-flex gap-3 overflow-auto pb-3" style="scrollbar-width: none; -ms-overflow-style: none;">
                    
                    <!-- Checking Account -->
                    <div class="account-card flex-shrink-0 p-4 rounded-3 text-white position-relative shadow-sm" 
                         style="background: rgba(255, 255, 255, 0.1); min-width: 300px; border: 1px solid rgba(255,255,255,0.1);">
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <div class="fw-bold fs-5">Personal Checking</div>
                                <div class="small opacity-75">...{{ substr(auth()->user()->account_number, -4) }}</div>
                            </div>
                            <i class="fas fa-ellipsis-v opacity-50"></i>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <div class="small opacity-75 mb-1">Available Balance</div>
                                <div class="display-6 fw-bold">{{ setting('currency_symbol','global').number_format(auth()->user()->balance, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Savings Account (Using DPS) -->
                    <div class="account-card flex-shrink-0 p-4 rounded-3 text-white position-relative shadow-sm" 
                         style="background: rgba(255, 255, 255, 0.1); min-width: 300px; border: 1px solid rgba(255,255,255,0.1);">
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <div class="fw-bold fs-5">Regular Savings</div>
                                <div class="small opacity-75">...90S0</div>
                            </div>
                            <i class="fas fa-ellipsis-v opacity-50"></i>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <div class="small opacity-75 mb-1">Available Balance</div>
                                <div class="display-6 fw-bold">{{ setting('currency_symbol','global').number_format($dps_mature_amount ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Loans (Dynamic) -->
                    @if(auth()->user()->loan->count() > 0)
                    <div class="account-card flex-shrink-0 p-4 rounded-3 text-white position-relative shadow-sm" 
                         style="background: rgba(255, 255, 255, 0.1); min-width: 300px; border: 1px solid rgba(255,255,255,0.1);">
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <div class="fw-bold fs-5">Personal Loan</div>
                                <div class="small opacity-75">Active</div>
                            </div>
                            <i class="fas fa-ellipsis-v opacity-50"></i>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                             <div>
                                <div class="small opacity-75 mb-1">Current Balance</div>
                                <div class="display-6 fw-bold">{{ setting('currency_symbol','global').number_format($total_loan_amount ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

                <!-- Quick Actions Grid -->
                <div class="row g-2 mt-4 px-1">
                    <div class="col-3">
                        <a href="{{ route('user.fund_transfer.index') }}" class="btn btn-light w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 rounded-3 shadow-sm" style="min-height: 90px;">
                            <i class="fas fa-exchange-alt fs-4 mb-2 text-primary"></i>
                            <span class="small fw-bold text-dark text-nowrap">Transfer</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="#" class="btn btn-light w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 rounded-3 shadow-sm" style="min-height: 90px;">
                            <i class="fas fa-user-friends fs-4 mb-2 text-primary"></i>
                            <span class="small fw-bold text-dark text-nowrap">Pay a Person</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="#" class="btn btn-light w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 rounded-3 shadow-sm" style="min-height: 90px;">
                            <i class="fas fa-file-invoice-dollar fs-4 mb-2 text-primary"></i>
                            <span class="small fw-bold text-dark text-nowrap">Pay a Bill</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="{{ route('user.remote_deposit') }}" class="btn btn-light w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 rounded-3 shadow-sm" style="min-height: 90px;">
                            <i class="fas fa-camera fs-4 mb-2 text-primary"></i>
                            <span class="small fw-bold text-dark text-nowrap">Remote Deposit</span>
                        </a>
                    </div>
                </div>

            </div>
            
            <!-- Background Decoration -->
            <div class="position-absolute top-0 end-0 opacity-10" style="transform: translate(30%, -30%);">
                <i class="fas fa-university" style="font-size: 300px;"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Transactions Column -->
    <div class="col-lg-7 col-md-12 mb-4">
        <div class="site-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 px-3 pt-3">
                <h5 class="fw-bold mb-0">{{ __('Transactions') }}</h5>
                <div>
                    <i class="fas fa-search me-3 text-muted"></i>
                    <i class="fas fa-ellipsis-h text-muted"></i>
                </div>
            </div>
            
            <div class="px-3">
                @forelse ($recentTransactions as $transaction)
                <div class="d-flex align-items-center justify-content-between py-3 border-bottom border-light">
                    <div>
                        <div class="fw-bold text-dark">{{ $transaction->description }}</div>
                        <div class="text-muted small">{{ $transaction->created_at->format('M d, Y') }}, 0010 CHECKING</div>
                    </div>
                    <div class="fw-bold {{ isPlusTransaction($transaction->type) ? 'text-success' : '' }}" style="{{ !isPlusTransaction($transaction->type) ? 'color: #333;' : '' }}">
                        {{ setting('currency_symbol','global').number_format($transaction->amount, 2) }}
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <p>{{ __('No recent transactions.') }}</p>
                </div>
                @endforelse
            </div>
            
            <div class="p-3">
                <a href="{{ route('user.transactions') }}" class="btn btn-light w-100 text-primary fw-bold" style="background: #f0f4f8;">See more</a>
            </div>
        </div>
    </div>

    <!-- Right Column: Messages, Transfers -->
    <div class="col-lg-5 col-md-12">
        <!-- Messages -->
        <div class="site-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 px-3 pt-3">
                <h5 class="fw-bold mb-0">Messages</h5>
                <div>
                    <i class="fas fa-comment-medical me-3 text-muted"></i>
                    <i class="fas fa-ellipsis-h text-muted"></i>
                </div>
            </div>
            <div class="text-center py-4">
                <p class="text-primary fw-bold mb-2">Pinellas FCU</p>
                <div class="d-flex justify-content-center mb-3">
                    <!-- Placeholder Avatars -->
                    <div class="d-flex">
                        <div class="rounded-circle bg-secondary overflow-hidden mx-1" style="width: 40px; height: 40px;">
                            <img src="https://i.pravatar.cc/150?u=a" alt="Amy" class="w-100 h-100 object-fit-cover">
                        </div>
                        <div class="rounded-circle bg-secondary overflow-hidden mx-1" style="width: 40px; height: 40px;">
                            <img src="https://i.pravatar.cc/150?u=c" alt="Carol" class="w-100 h-100 object-fit-cover">
                        </div>
                        <div class="rounded-circle bg-secondary overflow-hidden mx-1" style="width: 40px; height: 40px;">
                            <img src="https://i.pravatar.cc/150?u=co" alt="Colleen" class="w-100 h-100 object-fit-cover">
                        </div>
                    </div>
                </div>
                <div class="small text-muted mb-4 px-4">
                    President's Day<br>
                    We are closed on February 16th. We will open for regular hours on February 17th.
                </div>
                <a href="{{ route('user.messages') }}" class="btn btn-danger px-4 rounded-pill">Start a conversation</a>
            </div>
        </div>

        <!-- Transfers Widget -->
        <div class="site-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 px-3 pt-3">
                <h5 class="fw-bold mb-0">Transfers</h5>
                <i class="fas fa-ellipsis-h text-muted"></i>
            </div>
            <div class="d-flex justify-content-around py-3 px-2">
                <a href="{{ route('user.fund_transfer.index') }}" class="text-center text-decoration-none">
                    <i class="fas fa-exchange-alt fs-2 text-primary mb-2"></i>
                    <div class="small text-dark">Make a transfer</div>
                </a>
                <a href="{{ route('user.fund_transfer.index') }}" class="text-center text-decoration-none">
                    <i class="fas fa-university fs-2 text-primary mb-2"></i>
                    <div class="small text-dark">Member Transfers</div>
                </a>
            </div>
            <div class="border-top p-3 text-center text-muted small">
                <i class="fas fa-calendar-alt me-2"></i> No transfers scheduled.
            </div>
        </div>

        <!-- Bill Pay -->
        <div class="site-card mb-4">
             <div class="d-flex justify-content-between align-items-center mb-3 px-3 pt-3">
                <h5 class="fw-bold mb-0">Bill pay</h5>
                <i class="fas fa-ellipsis-h text-muted"></i>
            </div>
            <div class="d-flex justify-content-around py-3 px-2">
                <div class="text-center">
                    <a href="#" class="text-decoration-none">
                        <i class="fas fa-file-invoice-dollar fs-2 text-primary mb-2"></i>
                        <div class="small text-dark">Pay a bill</div>
                    </a>
                </div>
                <div class="text-center">
                    <a href="#" class="text-decoration-none">
                        <i class="fas fa-user fs-2 text-primary mb-2"></i>
                        <div class="small text-dark">Pay a person</div>
                    </a>
                </div>
                <div class="text-center">
                     <a href="#" class="text-decoration-none">
                        <i class="fas fa-cog fs-2 text-primary mb-2"></i>
                        <div class="small text-dark">Manage payments</div>
                    </a>
                </div>
            </div>
            <div class="border-top p-3 text-center text-muted small">
                <i class="fas fa-dollar-sign me-2"></i> No recent payments
            </div>
        </div>

         <!-- Remote Deposits -->
        <div class="site-card mb-4">
             <div class="d-flex justify-content-between align-items-center mb-3 px-3 pt-3">
                <h5 class="fw-bold mb-0">Remote deposits</h5>
                <i class="fas fa-ellipsis-h text-muted"></i>
            </div>
             <div class="text-center py-4">
                <a href="{{ route('user.remote_deposit') }}" class="text-decoration-none">
                    <i class="fas fa-upload fs-2 text-muted mb-2"></i>
                    <div class="small text-muted">Deposit a check</div>
                </a>
             </div>
        </div>
    </div>
</div>

@endsection

@section('style')
<style>
    .welcome-banner {
        /* Fallback if image doesn't load */
         background-color: #0083b0;
    }
    .account-card {
        transition: transform 0.2s;
    }
    .account-card:hover {
        transform: translateY(-2px);
    }
    .site-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
</style>
@endsection
