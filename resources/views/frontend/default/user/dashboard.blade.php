@extends('frontend::layouts.user')

@section('title')
    {{ __('Dashboard') }}
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-section p-4 rounded-3 text-white position-relative" style="background-color: var(--navigation-bar-color); background-image: url('https://my.pinellasfcu.org/images/fi-assets/pinellas-federal-credit-union/pinellas-federal-credit-union-background-landscape-2c77924b.png'); background-size: cover; background-position: center; min-height: 240px;">
            <div class="position-relative z-1">
                <h1 class="welcome-text mb-4 mt-2" style="font-size: 32px; font-weight: 700; color: #fff !important;">Hi, {{ strtoupper(auth()->user()->first_name) }}</h1>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-uppercase opacity-75">Accounts</span>
                        <div class="d-flex gap-3 align-items-center">
                            <a href="{{ route('user.accounts') }}" class="text-white text-decoration-none small fw-bold opacity-75">View all</a>
                            <i class="fas fa-ellipsis-h opacity-75"></i>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Accounts Carousel -->
                <div class="banno-accounts-scroll d-flex gap-2 overflow-auto pb-2" style="scrollbar-width: none; -ms-overflow-style: none;">
                    <!-- Checking Account -->
                    <div class="flex-grow-1" style="min-width: 280px; width: 100%;">
                        <div class="p-3 rounded-3 h-100" style="background: rgba(0, 84, 155, 0.9); border: 1px solid rgba(255,255,255,0.2);">
                            <div class="d-flex justify-content-between align-items-start small fw-bold mb-1">
                                <span>0010 CHECKING</span>
                                <span>${{ number_format(auth()->user()->balance, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <span class="opacity-75" style="font-size: 11px;">x{{ substr(auth()->user()->account_number, -4) }}S10</span>
                                <span class="opacity-75" style="font-size: 11px;">Available</span>
                            </div>
                        </div>
                    </div>

                    <!-- Primary Savings Account -->
                    <div class="flex-grow-1" style="min-width: 280px; width: 100%;">
                        <div class="p-3 rounded-3 h-100" style="background: rgba(0, 84, 155, 0.9); border: 1px solid rgba(255,255,255,0.2);">
                            <div class="d-flex justify-content-between align-items-start small fw-bold mb-1">
                                <span>0000 SAVINGS</span>
                                <span>${{ number_format(auth()->user()->savings_balance, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <span class="opacity-75" style="font-size: 11px;">x{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S00</span>
                                <span class="opacity-75" style="font-size: 11px;">Available</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accounts-dots d-flex justify-content-center gap-1 mt-2 mb-3">
                    <div class="accounts-dot active" style="width: 6px; height: 6px; background: #fff; border-radius: 50%;"></div>
                    <div class="accounts-dot" style="width: 6px; height: 6px; background: rgba(255,255,255,0.3); border-radius: 50%;"></div>
                    <div class="accounts-dot" style="width: 6px; height: 6px; background: rgba(255,255,255,0.3); border-radius: 50%;"></div>
                </div>

                <!-- 6 Quick Actions -->
                <div class="banno-quick-actions d-flex flex-nowrap overflow-auto pb-2 gap-2" style="scrollbar-width: none; -ms-overflow-style: none;">
                    <a href="{{ route('user.fund_transfer.index') }}" class="banno-action-btn flex-shrink-0" style="min-width: 80px;">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Transfer</span>
                    </a>
                    <a href="{{ route('user.bill-pay.index') }}" class="banno-action-btn flex-shrink-0" style="min-width: 80px;">
                        <i class="fas fa-user"></i>
                        <span>Pay a person</span>
                    </a>
                    <a href="{{ route('user.bill-pay.index') }}" class="banno-action-btn flex-shrink-0" style="min-width: 80px;">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Pay a bill</span>
                    </a>
                    <a href="{{ route('user.messages') }}" class="banno-action-btn flex-shrink-0" style="min-width: 80px;">
                        <i class="fas fa-comment-medical"></i>
                        <span>Message</span>
                    </a>
                    <a href="{{ route('user.fund_transfer.member') }}" class="banno-action-btn flex-shrink-0" style="min-width: 80px;">
                        <i class="fas fa-university"></i>
                        <span>Member Transfers</span>
                    </a>
                    <a href="#" class="banno-action-btn flex-shrink-0" style="min-width: 80px;">
                        <i class="fas fa-file-alt"></i>
                        <span>Documents</span>
                    </a>
                </div>
            </div>
            <!-- RL Initials Avatar in Header -->
            <div class="position-absolute top-0 end-0 m-4">
                <div class="user-avatar-banno" style="width: 40px; height: 40px; border: 2px solid rgba(255,255,255,0.5); background: rgba(255,255,255,0.2); color: white;">
                    {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column: Accounts & Transactions -->
<div class="row">
    <!-- Left Column: Accounts & Transactions -->
    <div class="col-lg-6 col-12 mb-4">

        <!-- Recent Transactions -->
        <!-- Recent Transactions -->
        <div class="banno-card h-100 overflow-hidden">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h6 fw-bold mb-0">Transactions</h3>
                <div class="d-flex gap-3 align-items-center">
                    <i class="fas fa-search text-muted small"></i>
                    <i class="fas fa-ellipsis-h text-muted"></i>
                </div>
            </div>
            <ul class="txn-list">
                @forelse($recentTransactions as $transaction)
                    @php
                        $txnType = is_object($transaction->type) ? $transaction->type->value : $transaction->type;
                        $isDebit = in_array($txnType, ['subtract', 'debit', 'withdraw', 'send_money', 'fund_transfer']);
                        $isDeposit = in_array($txnType, ['deposit', 'credit', 'manual_deposit', 'receive_money']);
                        
                        // Map type to label
                        $label = 'Transaction';
                        if($isDeposit) $label = 'Deposit';
                        elseif($isDebit) $label = 'Withdrawal';
                        elseif($txnType == 'fund_transfer') $label = 'Transfer';
                        elseif($txnType == 'receive_money') $label = 'Received';
                        else $label = ucwords(str_replace('_', ' ', $txnType));
                    @endphp
                    <li class="txn-item">
                        <div class="txn-date text-uppercase">
                            <div style="font-weight: 700; font-size: 14px; color: var(--body-text-primary-color);">{{ $transaction->created_at->format('M') }}</div>
                            <div style="font-size: 18px;">{{ $transaction->created_at->format('d') }}</div>
                        </div>
                        <div class="txn-desc">
                            <div class="text-truncate">{{ $transaction->description ?? $label }}</div>
                            <div class="small text-muted">{{ $label }}</div>
                        </div>
                        <div class="txn-amount {{ $isDebit ? 'text-danger' : 'text-success' }}">
                            {{ $isDebit ? '-' : '+' }}${{ number_format($transaction->amount, 2) }}
                        </div>
                    </li>
                @empty
                    <li class="text-center py-4 text-muted">No recent transactions</li>
                @endforelse
            </ul>
            <div class="mt-4 pt-2 border-top text-center">
                <a href="{{ route('user.fund_transfer.transfer.log') }}" class="text-decoration-none fw-bold" style="font-size: 14px;">See more</a>
            </div>
        </div>
    </div>

    <!-- Right Column: Sidebar Widgets -->
    <div class="col-lg-6 col-12">
        <div id="dashboard-widgets" class="row">
            @foreach($widgetOrder as $widget)
                <div class="col-12 mb-4" data-id="{{ $widget }}">
                    @if($widget == 'messages')
                        <!-- Messages Widget -->
                        <div class="banno-card text-center h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h6 fw-bold mb-0">Messages</h3>
                                <div class="d-flex gap-3">
                                    <i class="fas fa-comment-medical text-muted"></i>
                                    <i class="fas fa-ellipsis-h text-muted" style="cursor: grab;"></i>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="small fw-bold text-muted mb-3">Pinellas FCU</div>
                                <div class="d-flex justify-content-center gap-2 mb-3">
                                    <div class="rounded-circle bg-light border" style="width: 50px; height: 50px; overflow: hidden;">
                                        <img src="https://ui-avatars.com/api/?name=Lisa&background=random" alt="Lisa" class="w-100">
                                    </div>
                                    <div class="rounded-circle bg-light border" style="width: 60px; height: 60px; overflow: hidden; margin-top: -5px;">
                                        <img src="https://ui-avatars.com/api/?name=Kim&background=random" alt="Kim" class="w-100">
                                    </div>
                                    <div class="rounded-circle bg-light border" style="width: 50px; height: 50px; overflow: hidden;">
                                        <img src="https://ui-avatars.com/api/?name=Amy&background=random" alt="Amy" class="w-100">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center gap-4 small text-muted mb-3">
                                    <span>Lisa</span>
                                    <span>Kim</span>
                                    <span>Amy</span>
                                </div>
                                <p class="small fw-bold mb-1">We typically reply within one business day</p>
                                <p class="small text-muted px-4">We typically respond within one business day. Please call (727) 586-4422 us if you have an urgent issue.</p>
                            </div>
                            
                            <a href="{{ route('user.messages') }}" class="btn btn-primary w-100 rounded-pill py-2 fw-bold" style="border: none;">Start a conversation</a>
                        </div>
                    @elseif($widget == 'promo')
                        <!-- Keep us in the loop Promo Card -->
                        <div class="banno-card p-0 overflow-hidden h-100">
                            <img src="https://www.pinellasfcu.org/templates/pinellas/images/bg-main.jpg" alt="Promo" class="w-100" style="height: 150px; object-fit: cover;">
                            <div class="p-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-check-circle text-primary opacity-75"></i>
                                    <span class="small fw-bold text-primary">Update profile</span>
                                </div>
                                <h4 class="h6 fw-bold mb-2">Keep us in the loop</h4>
                                <p class="small text-muted mb-3">As life changes, let us know the best way we can reach you.</p>
                                <a href="{{ route('user.setting.show') }}" class="btn btn-light w-100 rounded-pill py-2 small fw-bold">Get started</a>
                            </div>
                        </div>
                    @elseif($widget == 'bill_pay')
                        <!-- Bill Pay Widget -->
                        <div class="banno-card h-100">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light rounded-circle p-2 me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                                </div>
                                <div>
                                    <h4 class="h6 fw-bold mb-0">Bill Pay</h4>
                                    <p class="small text-muted mb-0">Manage your payments</p>
                                </div>
                                <div class="ms-auto">
                                    <i class="fas fa-ellipsis-h text-muted" style="cursor: grab;"></i>
                                </div>
                            </div>
                            <a href="{{ route('user.bill-pay.index') }}" class="btn btn-outline-primary w-100 rounded-pill">Pay a bill</a>
                        </div>
                    @elseif($widget == 'cards')
                        <!-- Card Management Widget -->
                        <div class="banno-card h-100">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light rounded-circle p-2 me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-credit-card text-primary"></i>
                                </div>
                                <div>
                                    <h4 class="h6 fw-bold mb-0">Card Management</h4>
                                    <p class="small text-muted mb-0">Control your cards</p>
                                </div>
                                 <div class="ms-auto">
                                    <i class="fas fa-ellipsis-h text-muted" style="cursor: grab;"></i>
                                </div>
                            </div>
                            <a href="{{ route('user.cards') }}" class="btn btn-outline-primary w-100 rounded-pill">Manage Cards</a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
<div class="row mt-5 mb-5 pb-5">
    <div class="col-12 d-flex justify-content-center">
        <button id="save-dashboard-layout" class="btn btn-dark rounded-3 px-4 py-2 opacity-75 d-flex align-items-center gap-2" style="background-color: #3d454d; border: none; font-size: 14px; font-weight: 500;">
            <i class="fas fa-save"></i>
            Save Layout
        </button>
    </div>
</div>

<a href="#" class="fab-help"><i class="fas fa-question small"></i></a>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('dashboard-widgets');
        const sortable = Sortable.create(el, {
            animation: 150,
            handle: '.fa-ellipsis-h', // Use the menu icon as handle
            ghostClass: 'bg-light'
        });

        // Save Layout
        document.getElementById('save-dashboard-layout').addEventListener('click', function() {
            const order = sortable.toArray();
            
            fetch("{{ route('user.dashboard.save-order') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ order: order })
            })
            .then(response => response.json())
            .then(data => {
                alert('Dashboard configuration saved!');
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
</script>
@endpush
