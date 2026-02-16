@extends('frontend::layouts.user')

@section('title')
{{ __('Dashboard') }}
@endsection

@section('content')

<!-- Hero Section with "Hi, Name" -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner p-4 rounded-3 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%); min-height: 250px;">
            <div class="position-relative z-1">
                <h1 class="display-5 fw-bold mb-4">Hi, {{ auth()->user()->first_name }}</h1>
                
                <h5 class="mb-3">Accounts</h5>
                
                <!-- Accounts Carousel/Grid -->
                <div class="d-flex gap-3 overflow-auto pb-2" style="scrollbar-width: none;">
                    
                    <!-- Checking Account -->
                    <div class="account-card flex-shrink-0 p-3 rounded-3 text-white position-relative" style="background: rgba(0, 50, 100, 0.6); min-width: 280px; backdrop-filter: blur(5px);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">0010 CHECKING</span>
                            <i class="fas fa-ellipsis-h"></i>
                        </div>
                        <div class="small opacity-75 mb-3">x{{ substr(auth()->user()->account_number, -4) }}</div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="opacity-75 small">Available</div>
                            <div class="fs-4 fw-bold">{{ setting('currency_symbol','global').number_format(auth()->user()->balance, 2) }}</div>
                        </div>
                    </div>

                    <!-- Savings Account (Using DPS as proxy or static if none) -->
                    <div class="account-card flex-shrink-0 p-3 rounded-3 text-white position-relative" style="background: rgba(0, 50, 100, 0.6); min-width: 280px; backdrop-filter: blur(5px);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">0000 SAVINGS</span>
                            <i class="fas fa-ellipsis-h"></i>
                        </div>
                        <div class="small opacity-75 mb-3">x90S00</div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="opacity-75 small">Available</div>
                            <div class="fs-4 fw-bold">{{ setting('currency_symbol','global').number_format($dps_mature_amount ?? 0, 2) }}</div>
                        </div>
                    </div>

                    <!-- Add more cards if needed (Loans, etc) -->
                    @if(auth()->user()->loan->count() > 0)
                    <div class="account-card flex-shrink-0 p-3 rounded-3 text-white position-relative" style="background: rgba(0, 50, 100, 0.6); min-width: 280px; backdrop-filter: blur(5px);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">LOANS</span>
                            <i class="fas fa-ellipsis-h"></i>
                        </div>
                        <div class="small opacity-75 mb-3">Active</div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="opacity-75 small">Balance</div>
                            <div class="fs-4 fw-bold">{{ setting('currency_symbol','global').number_format($total_loan_amount ?? 0, 2) }}</div>
                        </div>
                    </div>
                    @endif

                </div>

                <!-- Quick Actions Grid within Hero -->
                <div class="d-flex gap-2 mt-4 flex-wrap">
                    <a href="{{ route('user.fund_transfer.index') }}" class="btn btn-primary d-flex flex-column align-items-center justify-content-center p-2" style="width: 80px; height: 80px; background: rgba(0, 50, 100, 0.8); border: none;">
                        <i class="fas fa-exchange-alt fs-4 mb-2"></i>
                        <span style="font-size: 10px;">Transfer</span>
                    </a>
                    <a href="#" class="btn btn-primary d-flex flex-column align-items-center justify-content-center p-2" style="width: 80px; height: 80px; background: rgba(0, 50, 100, 0.8); border: none;">
                        <i class="fas fa-user-friends fs-4 mb-2"></i>
                        <span style="font-size: 10px;">Pay a person</span>
                    </a>
                    <a href="#" class="btn btn-primary d-flex flex-column align-items-center justify-content-center p-2" style="width: 80px; height: 80px; background: rgba(0, 50, 100, 0.8); border: none;">
                        <i class="fas fa-file-invoice-dollar fs-4 mb-2"></i>
                        <span style="font-size: 10px;">Pay a bill</span>
                    </a>
                    <a href="{{ route('user.ticket.index') }}" class="btn btn-primary d-flex flex-column align-items-center justify-content-center p-2" style="width: 80px; height: 80px; background: rgba(0, 50, 100, 0.8); border: none;">
                        <i class="fas fa-comment-alt fs-4 mb-2"></i>
                        <span style="font-size: 10px;">Message</span>
                    </a>
                    <a href="{{ route('user.fund_transfer.index') }}" class="btn btn-primary d-flex flex-column align-items-center justify-content-center p-2" style="width: 80px; height: 80px; background: rgba(0, 50, 100, 0.8); border: none;">
                        <i class="fas fa-university fs-4 mb-2"></i>
                        <span style="font-size: 10px; text-align: center; line-height: 1.1;">Member Transfers</span>
                    </a>
                    <a href="#" class="btn btn-primary d-flex flex-column align-items-center justify-content-center p-2" style="width: 80px; height: 80px; background: rgba(0, 50, 100, 0.8); border: none;">
                        <i class="fas fa-file-alt fs-4 mb-2"></i>
                        <span style="font-size: 10px;">Documents</span>
                    </a>
                </div>

            </div>
            <!-- Background Image Overlay -->
            <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: url('https://www.pinellasfcu.org/templates/pinellas/images/slider/slide-1.jpg'); background-size: cover; background-position: center; opacity: 0.2; z-index: 0;"></div>
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
                <a href="{{ route('user.ticket.index') }}" class="btn btn-danger px-4 rounded-pill">Start a conversation</a>
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
                    <i class="fas fa-file-invoice-dollar fs-2 text-primary mb-2"></i>
                    <div class="small text-dark">Pay a bill</div>
                </div>
                <div class="text-center">
                    <i class="fas fa-user fs-2 text-primary mb-2"></i>
                    <div class="small text-dark">Pay a person</div>
                </div>
                <div class="text-center">
                    <i class="fas fa-cog fs-2 text-primary mb-2"></i>
                    <div class="small text-dark">Manage payments</div>
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
                <i class="fas fa-upload fs-2 text-muted mb-2"></i>
                <div class="small text-muted">No recent deposits</div>
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
