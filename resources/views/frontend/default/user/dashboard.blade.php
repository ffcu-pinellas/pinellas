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
                    @if($widget == 'transactions')
                        <!-- Recent Transactions (Widget Index: transactions) -->
                        <div class="banno-card h-100 overflow-hidden" style="padding: 0;">
                            <div class="px-4 pt-4 pb-2 d-flex justify-content-between align-items-center">
                                <h3 class="h6 fw-bold mb-0" style="font-size: 1.1rem;">Transactions</h3>
                                <div class="d-flex gap-3 align-items-center">
                                    <i class="fas fa-search text-muted small pointer"></i>
                                    <i class="fas fa-ellipsis-h text-muted pointer"></i>
                                </div>
                            </div>
                            <ul class="txn-list list-unstyled m-0">
                                @forelse($recentTransactions->take(5) as $transaction)
                                    @php
                                        $txnType = is_object($transaction->type) ? $transaction->type->value : $transaction->type;
                                        $isDebit = in_array($txnType, ['subtract', 'debit', 'withdraw', 'send_money', 'fund_transfer']);
                                        $label = $transaction->description ?? 'Transaction';
                                    @endphp
                                    <li class="txn-item px-4 py-3 border-bottom" style="cursor: pointer;" onclick="window.location='{{ route('user.transactions') }}'">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="txn-desc text-truncate fw-bold text-dark" style="max-width: 70%;">{{ $label }}</div>
                                            <div class="txn-amount fw-bold {{ $isDebit ? '' : 'text-success' }}" style="font-size: 1rem;">
                                                {{ $isDebit ? '' : '+' }}${{ number_format($transaction->amount, 2) }}
                                            </div>
                                        </div>
                                        <div class="small text-muted mt-1 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                            {{ $transaction->created_at->format('M j, Y') }} {{ auth()->user()->account_number }} CHECKING
                                        </div>
                                    </li>
                                @empty
                                    <li class="text-center py-4 text-muted">No recent transactions</li>
                                @endforelse
                            </ul>
                            <div class="p-3 text-center">
                                <a href="{{ route('user.transactions') }}" class="btn btn-light rounded-pill px-4 fw-bold text-primary" style="background: #eef6fb; border: none;">See more</a>
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
<!-- Organizer Button -->
<div class="row mt-5 mb-5 pb-5">
    <div class="col-12 d-flex justify-content-center">
        <button type="button" class="btn btn-dark rounded-3 px-4 py-2 opacity-75 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#organizeModal" style="background-color: #3d454d; border: none; font-size: 14px; font-weight: 500;">
            Organize dashboard
        </button>
    </div>
</div>

<!-- Organizer Modal -->
<div class="modal fade" id="organizeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="background-color: #f4f6f8;">
            <div class="modal-header border-0 bg-white p-4">
                <div>
                    <h5 class="modal-title fw-bold" id="organizeModalLabel">Organize dashboard</h5>
                    <p class="text-muted small mb-0">Drag & drop to reorder</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Accounts Section (Static for now as per image) -->
                <div class="bg-white p-3 rounded-3 shadow-sm mb-3">
                    <div class="fw-bold">Accounts</div>
                </div>
                
                <!-- Sortable Grid -->
                <!-- We exclude 'accounts' from sortable if it's treated separately/statically at top, but usually it's a widget too. -->
                <!-- Image 3 shows Accounts separate at top. But my code treats 'accounts' as a widget. -->
                <!-- I will filter 'accounts' out of the sortable grid if present, or keep it if user wants it draggable. -->
                <!-- Image 3 shows 'Accounts' as a static block above the grid. -->
                <div id="modal-widgets-grid" class="row g-3">
                    @foreach($user_widgets as $widget)
                        @if($widget == 'accounts') @continue @endif
                        <div class="col-6 widget-tile" data-id="{{ $widget }}">
                            <div class="bg-white p-3 rounded-3 shadow-sm d-flex align-items-center justify-content-between cursor-move h-100">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-grip-vertical text-muted"></i>
                                    <span class="fw-bold text-capitalize">{{ str_replace('_', ' ', $widget) }}</span>
                                </div>
                                <i class="fas fa-times text-muted"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-3">
                     <a href="#" class="text-decoration-none small fw-bold text-primary">+ Add a card</a>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                 <button type="button" class="btn btn-link text-primary fw-bold text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                 <button type="button" class="btn btn-link text-primary fw-bold text-decoration-none" id="save-dashboard-order-btn">Done</button>
            </div>
        </div>
    </div>
</div>

<a href="#" class="fab-help"><i class="fas fa-question small"></i></a>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Sortable on the Modal Grid, not the main dashboard
        const el = document.getElementById('modal-widgets-grid');
        const sortable = Sortable.create(el, {
            animation: 150,
            handle: '.cursor-move', // Make the whole tile handle or specific icon
            ghostClass: 'bg-light'
        });

        // Save Layout
        document.getElementById('save-dashboard-order-btn').addEventListener('click', function() {
            let order = sortable.toArray();
            
            // Add 'accounts' back to the start of the order since it's static in the modal
            // Check if 'accounts' is already there (unlikely due to blade exclusion)
            if (!order.includes('accounts')) {
                order.unshift('accounts');
            }
            
            // Allow button to indicate loading
            const btn = this;
            const originalText = btn.innerText;
            btn.innerText = 'Saving...';
            btn.disabled = true;

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
                // Reload to reflect changes
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save layout.');
                btn.innerText = originalText;
                btn.disabled = false;
            });
        });
    });
</script>
@endpush
