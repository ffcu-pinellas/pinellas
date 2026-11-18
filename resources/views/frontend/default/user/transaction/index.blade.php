@php use App\Enums\TxnStatus; @endphp
@extends('frontend::layouts.user')

@section('title')
    {{ __('Activity') }}
@endsection

@push('style')
    <style>
        .filter-chip {
            border-radius: 50px;
            padding: 6px 16px;
            font-size: 13px;
            font-weight: 600;
            background: white;
            border: 1px solid var(--divider-default-color);
            color: var(--body-text-secondary-color);
            transition: all 0.2s;
            cursor: pointer;
            white-space: nowrap;
        }
        .filter-chip.active {
            background: var(--body-text-theme-color);
            color: white;
            border-color: var(--body-text-theme-color);
        }
        .activity-row {
            transition: background 0.2s;
            cursor: pointer;
        }
        .activity-row:hover {
            background-color: var(--secondary-content-background-color);
        }
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--secondary-content-background-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--body-text-theme-color);
        }
        .btn-check:checked + .btn-outline-light {
            background-color: var(--primary-color, #00549b) !important;
            color: white !important;
            border-color: var(--primary-color, #00549b) !important;
            box-shadow: 0 4px 12px rgba(0, 84, 155, 0.3);
            transform: translateY(-1px);
        }
        .btn-outline-light:hover {
            border-color: var(--primary-color, #00549b) !important;
            color: var(--primary-color, #00549b) !important;
        }
        .modal-content {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.98);
        }
        .dark-theme .modal-content {
            background: rgba(30, 30, 30, 0.98);
            color: white;
        }
        .form-check-input:checked {
            background-color: var(--primary-color, #00549b);
            border-color: var(--primary-color, #00549b);
        }
        /* Daterangepicker in Modal fixes */
        .daterangepicker {
            z-index: 1100 !important;
            font-family: inherit;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
            border: 1px solid #e2e8f0 !important;
        }
        .daterangepicker .drp-buttons .btn {
            border-radius: 50px;
            font-weight: bold;
            padding: 4px 15px;
        }
        #p1m + label, #p3m + label, #p6m + label, #p1y + label, #pcustom + label {
            min-width: 85px;
            text-align: center;
        }

        /* Mobile-First Responsive Card Tiles (< 768px) */
        @media (max-width: 767.98px) {
            .site-card .transaction-list {
                padding: 12px;
                background-color: #f8fafc;
            }
            .activity-row {
                flex-direction: column !important;
                align-items: stretch !important;
                background: #ffffff !important;
                border-radius: 14px !important;
                border: 1px solid #e2e8f0 !important;
                margin-bottom: 12px !important;
                padding: 14px 16px !important;
                box-shadow: 0 2px 6px rgba(0,0,0,0.03);
                overflow: hidden !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            .activity-row-mobile {
                width: 100% !important;
                overflow: hidden !important;
            }
            .activity-row:hover {
                background: #ffffff !important;
                box-shadow: 0 4px 12px rgba(0,0,0,0.07);
            }
            .activity-row-desktop-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 100%;
            }
            .activity-row-desktop-end {
                display: none !important;
            }
            .mobile-card-header {
                display: flex !important;
                align-items: flex-start;
                justify-content: space-between;
                width: 100%;
            }
            .mobile-card-meta {
                display: flex !important;
                align-items: center;
                justify-content: space-between;
                margin-top: 10px;
                padding-top: 8px;
                border-top: 1px dashed #e2e8f0;
            }
            .mobile-settlement-details {
                display: none;
                margin-top: 10px;
                padding: 12px;
                background: #f8fafc;
                border-radius: 10px;
                border: 1px solid #edf2f7;
            }
            .mobile-expand-chevron {
                transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .mobile-expand-chevron.rotate-180 {
                transform: rotate(180deg);
            }
        }
        @media (min-width: 768px) {
            .mobile-card-header,
            .mobile-card-meta,
            .mobile-settlement-details,
            .mobile-expand-toggle {
                display: none !important;
            }
        }
    </style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <a href="{{ route('user.dashboard') }}" class="back-nav-link">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="h3 fw-bold mb-0">Activity</h1>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#eStatementModal">
                    <i class="fas fa-file-pdf me-1"></i> eStatement
                </button>
                <a href="{{ route('user.transactions.export.csv', $queries) }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2" title="Export CSV">
                    <i class="fas fa-file-csv"></i>
                </a>
            </div>
        </div>

        <div class="site-card mb-4 p-4">
            <form id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="small text-muted mb-1 fw-bold">SEARCH</label>
                        <div class="input-group border-bottom">
                            <span class="input-group-text bg-transparent border-0 ps-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="trx" class="form-control border-0 shadow-none" placeholder="Search by description or ID" value="{{ request('trx') }}">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="small text-muted mb-1 fw-bold">ACCOUNT</label>
                        <select name="wallet" class="form-select border-0 border-bottom shadow-none rounded-0">
                            <option value="" @selected(!request('wallet'))>All Accounts</option>
                            <option value="checking" @selected(request('wallet') == 'checking')>Personal Checking</option>
                            <option value="savings" @selected(request('wallet') == 'savings')>Primary Savings</option>
                            @if(auth()->user()->ira_status == 1)
                            <option value="ira" @selected(request('wallet') == 'ira')>IRA Account</option>
                            @endif
                            @if(auth()->user()->heloc_status == 1)
                            <option value="heloc" @selected(request('wallet') == 'heloc')>HELOC Account</option>
                            @endif
                            @if(auth()->user()->cc_status == 1)
                            <option value="cc" @selected(request('wallet') == 'cc')>Credit Card</option>
                            @endif
                            @if(auth()->user()->loan_account_status == 1)
                            <option value="loan" @selected(request('wallet') == 'loan')>Loan Account</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="small text-muted mb-1 fw-bold">DATE RANGE</label>
                        <div class="input-group border-bottom">
                            <span class="input-group-text bg-transparent border-0 ps-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                            <input type="text" name="daterange" class="form-control border-0 shadow-none" value="{{ request('daterange') }}" placeholder="All time">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4">
                         <label class="small text-muted mb-1 fw-bold">TYPE</label>
                         <select name="type" class="form-select border-0 border-bottom shadow-none rounded-0">
                             <option value="all" @selected(request('type') == 'all')>All types</option>
                             <option value="deposit" @selected(request('type') == 'deposit')>Deposits</option>
                             <option value="fund_transfer" @selected(request('type') == 'fund_transfer')>Transfers</option>
                             <option value="withdraw" @selected(request('type') == 'withdraw')>Withdrawals</option>
                             <option value="pay_bill" @selected(request('type') == 'pay_bill')>Bill Payments</option>
                         </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        @if(request('wallet'))
            @php
                $walletMap = [
                    'checking' => 'Personal Checking',
                    'savings' => 'Primary Savings',
                    'ira' => 'IRA Account',
                    'heloc' => 'HELOC Account',
                    'cc' => 'Credit Card',
                    'loan' => 'Loan Account',
                ];
                $walletDisplay = $walletMap[strtolower(request('wallet'))] ?? (strtoupper(request('wallet')) . ' Account');
            @endphp
            <div class="mb-4 d-flex align-items-center gap-2">
                <div class="filter-chip active d-flex align-items-center gap-2" style="background: var(--bs-primary, var(--primary-color, #00549b)); border-color: var(--bs-primary, var(--primary-color, #00549b)); color: white;">
                    <i class="fas fa-university small opacity-75"></i>
                    <span>{{ $walletDisplay }}</span>
                    <a href="{{ request()->fullUrlWithQuery(['wallet' => null]) }}" class="text-white ms-1 hover-opacity">
                        <i class="fas fa-times-circle"></i>
                    </a>
                </div>
                <div class="text-muted small">Showing transactions for {{ $walletDisplay }} only</div>
            </div>
        @endif

        <!-- Activity List -->
        <div class="site-card overflow-hidden">
            <div class="transaction-list">
                @forelse ($transactions as $transaction)
                @php
                    $existingDispute = isset($disputeMap) ? $disputeMap->get($transaction->id) : null;
                    $txnTypeVal = $transaction->type->value;
                    $walletNorm = strtolower((string) ($transaction->wallet_type ?: 'default'));
                    $isEligibleDispute = ! $existingDispute
                        && $transaction->status->value === 'success'
                        && in_array($txnTypeVal, \App\Services\DisputeService::DEBIT_DISPUTABLE_TYPES, true)
                        && in_array($walletNorm, ['default', 'checking', 'primary_savings', 'savings', 'savings_primary', 'ira'], true)
                        && ! str_starts_with(strtoupper((string) $transaction->tnx), 'RECON-')
                        && $transaction->target_type !== \App\Models\TransactionDispute::class
                        && (! $transaction->created_at || $transaction->created_at->gte(now()->subDays(\App\Services\DisputeService::REG_E_WINDOW_DAYS)));
                @endphp
                <div class="activity-row d-flex align-items-center justify-content-between py-3 px-4 border-bottom border-light" 
                     data-bs-toggle="modal" data-bs-target="#trxViewDetailsBox"
                     data-title="{{ $transaction->description }}"
                     data-trx="{{ $transaction->tnx }}"
                     data-amount="{{ isPlusTransaction($transaction->type) ? '+' : '-' }}{{ setting('currency_symbol','global').number_format($transaction->amount, 2) }}"
                     data-date="{{ $transaction->created_at->format('M d, Y h:i A') }}"
                     data-status="{{ $transaction->status->value }}"
                     data-method="{{ $transaction->method ?: 'System' }}"
                     data-dispute-url="{{ $isEligibleDispute ? route('user.disputes.create', $transaction->tnx) : '' }}"
                     data-dispute-claim="{{ $existingDispute?->claim_number ?? '' }}"
                     data-dispute-status="{{ $existingDispute?->status_label ?? '' }}"
                     data-dispute-view-url="{{ $existingDispute ? route('user.disputes.show', $existingDispute->claim_number) : '' }}">
                    
                    @php
                        $statusRaw = is_object($transaction->status) ? ($transaction->status->value ?? (string)$transaction->status) : (string)$transaction->status;
                        $statusLower = strtolower(trim((string)$statusRaw));
                    @endphp

                    {{-- Desktop layout row (screens >= 768px) --}}
                    <div class="activity-row-desktop d-none d-md-flex align-items-center justify-content-between w-100">
                        <div class="d-flex align-items-center gap-3" style="flex: 1 1 0; min-width: 0;">
                            <div class="activity-icon flex-shrink-0">
                                @if ($transaction->type->value == 'deposit' || $transaction->type->value == 'manual_deposit')
                                    <i class="fas fa-arrow-down"></i>
                                @elseif(Str::contains($transaction->type->value, 'transfer') || $transaction->type->value == 'receive_money')
                                    <i data-lucide="repeat"></i>
                                @elseif(Str::contains($transaction->type->value, 'loan'))
                                    <i class="fas fa-file-invoice-dollar"></i>
                                @else
                                    <i class="fas fa-shopping-cart"></i>
                                @endif
                            </div>
                            <div style="flex: 1 1 0; min-width: 0;">
                                <div class="fw-bold text-dark" style="word-break: break-word; overflow-wrap: break-word; white-space: normal;">{{ $transaction->description }}</div>
                                <div class="text-muted small">
                                    {{ $transaction->created_at->format('M d') }} • {{ $transaction->tnx }}
                                    @if($existingDispute)
                                        <span class="badge ms-1 rounded-pill" style="background: #eef6fb; color: #00549b; border: 1px solid rgba(0, 84, 155, 0.18); font-size: 10px; font-weight: 600;">
                                            {{ $existingDispute->status_label }} • #{{ $existingDispute->claim_number }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0" style="white-space: nowrap; flex-shrink: 0; margin-left: 16px;">
                            <div class="fw-bold {{ isPlusTransaction($transaction->type) ? 'text-success' : 'text-dark' }}" style="white-space: nowrap;">
                                {{ isPlusTransaction($transaction->type) ? '+' : '-' }}{{ setting('currency_symbol','global').number_format($transaction->amount, 2) }}
                            </div>
                            <div class="small">
                                @if($statusLower === 'success' || $statusLower === 'completed')
                                    <span class="text-success"><i class="fas fa-check-circle me-1 small"></i>Completed</span>
                                @elseif($statusLower === 'pending')
                                    <span class="badge badge-warning" style="background:#f59e0b;color:#fff;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;"><i class="fa fa-clock" style="margin-right:4px;"></i>Pending</span>
                                @else
                                    <span class="text-danger"><i class="fas fa-times-circle me-1 small"></i>Failed</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Mobile Card Tile (screens < 768px) --}}
                    <div class="activity-row-mobile d-md-none w-100" style="overflow: hidden;">
                        {{-- Top header: Icon, Merchant/Description, and Signed Amount --}}
                        <div class="d-flex align-items-center justify-content-between mb-2" style="width: 100%; gap: 10px;">
                            <div class="d-flex align-items-center gap-2" style="flex: 1 1 0; min-width: 0;">
                                <div class="activity-icon flex-shrink-0" style="width: 38px; height: 38px; min-width: 38px; font-size: 0.9rem;">
                                    @if ($transaction->type->value == 'deposit' || $transaction->type->value == 'manual_deposit')
                                        <i class="fas fa-arrow-down"></i>
                                    @elseif(Str::contains($transaction->type->value, 'transfer') || $transaction->type->value == 'receive_money')
                                        <i data-lucide="repeat"></i>
                                    @elseif(Str::contains($transaction->type->value, 'loan'))
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    @else
                                        <i class="fas fa-shopping-cart"></i>
                                    @endif
                                </div>
                                <div style="flex: 1 1 0; min-width: 0;">
                                    <div class="fw-bold text-dark d-block" style="font-size: 0.90rem; word-break: break-word; overflow-wrap: break-word; white-space: normal;" title="{{ $transaction->description }}">{{ $transaction->description }}</div>
                                    <div class="text-muted extra-small" style="font-size: 0.75rem; word-break: break-word; overflow-wrap: break-word; white-space: normal;">{{ ucwords(str_replace('_', ' ', $transaction->type->value)) }}</div>
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0" style="white-space: nowrap; flex-shrink: 0; margin-left: auto;">
                                <div class="fw-bolder {{ isPlusTransaction($transaction->type) ? 'text-success' : 'text-dark' }}" style="font-size: 0.98rem; font-variant-numeric: tabular-nums; white-space: nowrap;">
                                    {{ isPlusTransaction($transaction->type) ? '+' : '-' }}{{ setting('currency_symbol','global').number_format($transaction->amount, 2) }}
                                </div>
                            </div>
                        </div>

                        {{-- Metadata badges: Timestamp & Status badge & Expand indicator --}}
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-light">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-light text-muted fw-normal" style="font-size: 0.72rem;">
                                    <i class="far fa-calendar-alt me-1"></i> {{ $transaction->created_at->format('M d, Y h:i A') }}
                                </span>
                                @if($statusLower === 'success' || $statusLower === 'completed')
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 0.72rem;">
                                        <i class="fas fa-check-circle me-1"></i> Completed
                                    </span>
                                @elseif($statusLower === 'pending')
                                    <span class="badge badge-warning" style="background:#f59e0b;color:#fff;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;"><i class="fa fa-clock" style="margin-right:4px;"></i>Pending</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger fw-bold" style="font-size: 0.72rem;">
                                        <i class="fas fa-times-circle me-1"></i> Failed
                                    </span>
                                @endif
                            </div>
                            <div class="text-muted small d-flex align-items-center gap-1 cursor-pointer">
                                <span class="extra-small text-muted" style="font-size: 0.72rem;">Details</span>
                                <i class="fas fa-chevron-down mobile-expand-chevron" style="font-size: 0.7rem;"></i>
                            </div>
                        </div>

                        {{-- Expandable Settlement Details --}}
                        <div class="mobile-settlement-details">
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                <span class="text-muted extra-small">Reference / Tracking #</span>
                                <span class="font-monospace fw-bold text-dark extra-small">{{ $transaction->tnx }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                <span class="text-muted extra-small">Channel / Method</span>
                                <span class="fw-semibold text-dark extra-small">{{ $transaction->method ?: 'System Network' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                <span class="text-muted extra-small">Account Category</span>
                                <span class="fw-semibold text-dark extra-small text-uppercase">{{ str_replace('_', ' ', $transaction->wallet_type ?: 'Checking') }}</span>
                            </div>
                            @if($existingDispute)
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                <span class="text-muted extra-small">Dispute Status</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary extra-small">{{ $existingDispute->status_label }} (#{{ $existingDispute->claim_number }})</span>
                            </div>
                            @endif
                            <div class="d-flex justify-content-end gap-2 mt-2 pt-1">
                                <a href="{{ route('user.transactions.receipt', $transaction->tnx) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 extra-small" onclick="event.stopPropagation();">
                                    <i class="fas fa-file-pdf me-1"></i> Receipt
                                </a>
                                @if($isEligibleDispute)
                                <a href="{{ route('user.disputes.create', $transaction->tnx) }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 extra-small" onclick="event.stopPropagation();">
                                    <i class="fas fa-shield-alt me-1"></i> Dispute
                                </a>
                                @elseif($existingDispute)
                                <a href="{{ route('user.disputes.show', $existingDispute->claim_number) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 extra-small" onclick="event.stopPropagation();">
                                    <i class="fas fa-eye me-1"></i> View Claim
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-search fa-2x text-muted opacity-50"></i>
                    </div>
                    <p class="text-muted">No activity match your filters.</p>
                </div>
                @endforelse
            </div>
            
            <div class="p-4 bg-light d-flex justify-content-center">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>

@push('modals')
<!-- eStatement Modal -->
<div class="modal fade" id="eStatementModal" tabindex="-1" style="z-index: 2000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold mb-0">Download eStatement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.transactions.export.pdf') }}" method="GET" data-no-loader="true">
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4">Select the period and accounts you want to include in your official bank statement.</p>
                    
                    <label class="small text-muted mb-2 fw-bold d-block">SELECT PERIOD</label>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <input type="radio" class="btn-check" name="period" id="p1m" value="1m" checked>
                        <label class="btn btn-outline-light text-dark border-1 rounded-pill px-3 py-2 fw-bold" for="p1m">1 Month</label>

                        <input type="radio" class="btn-check" name="period" id="p3m" value="3m">
                        <label class="btn btn-outline-light text-dark border-1 rounded-pill px-3 py-2 fw-bold" for="p3m">3 Months</label>

                        <input type="radio" class="btn-check" name="period" id="p6m" value="6m">
                        <label class="btn btn-outline-light text-dark border-1 rounded-pill px-3 py-2 fw-bold" for="p6m">6 Months</label>

                        <input type="radio" class="btn-check" name="period" id="p1y" value="1y">
                        <label class="btn btn-outline-light text-dark border-1 rounded-pill px-3 py-2 fw-bold" for="p1y">1 Year</label>
                        
                        <input type="radio" class="btn-check" name="period" id="pcustom" value="custom">
                        <label class="btn btn-outline-light text-dark border-1 rounded-pill px-4 py-2 fw-bold" for="pcustom">Custom</label>
                    </div>

                    <div id="customDateRange" class="mt-3 d-none">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="small text-muted mb-1 fw-bold">FROM DATE</label>
                                <div class="input-group border-bottom border-light">
                                    <span class="input-group-text bg-transparent border-0 ps-0"><i class="fas fa-calendar-alt text-muted small"></i></span>
                                    <input type="text" name="from_date" id="fromDate" class="form-control border-0 shadow-none bg-transparent ps-1" placeholder="MM/DD/YYYY" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted mb-1 fw-bold">TO DATE</label>
                                <div class="input-group border-bottom border-light">
                                    <span class="input-group-text bg-transparent border-0 ps-0"><i class="fas fa-calendar-alt text-muted small"></i></span>
                                    <input type="text" name="to_date" id="toDate" class="form-control border-0 shadow-none bg-transparent ps-1" placeholder="MM/DD/YYYY" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>

                    <label class="small text-muted mb-2 fw-bold d-block text-uppercase">Include Accounts</label>
                    <div class="site-card p-3 mb-4" style="background: var(--secondary-content-background-color) !important;">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="accounts[]" value="checking" id="accChecking" checked>
                            <label class="form-check-label fw-bold small text-dark" for="accChecking">
                                Checking Account (**** {{ substr(auth()->user()->account_number, -4) }})
                            </label>
                        </div>
                        @if(auth()->user()->savings_account_number)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="accounts[]" value="savings" id="accSavings" checked>
                            <label class="form-check-label fw-bold small text-dark" for="accSavings">
                                Savings Account (**** {{ substr(auth()->user()->savings_account_number, -4) }})
                            </label>
                        </div>
                        @endif
                        @if(auth()->user()->ira_account_number && auth()->user()->ira_status)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="accounts[]" value="ira" id="accIra">
                            <label class="form-check-label fw-bold small text-dark" for="accIra">
                                IRA Account (**** {{ substr(auth()->user()->ira_account_number, -4) }})
                            </label>
                        </div>
                        @endif
                        @if(auth()->user()->heloc_account_number && auth()->user()->heloc_status)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="accounts[]" value="heloc" id="accHeloc">
                            <label class="form-check-label fw-bold small text-dark" for="accHeloc">
                                HELOC (**** {{ substr(auth()->user()->heloc_account_number, -4) }})
                            </label>
                        </div>
                        @endif
                        @if(auth()->user()->cc_account_number && auth()->user()->cc_status)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="accounts[]" value="cc" id="accCC">
                            <label class="form-check-label fw-bold small text-dark" for="accCC">
                                Credit Card (**** {{ substr(auth()->user()->cc_account_number, -4) }})
                            </label>
                        </div>
                        @endif
                        @if(auth()->user()->loan_account_number && auth()->user()->loan_account_status)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="accounts[]" value="loan" id="accLoan">
                            <label class="form-check-label fw-bold small text-dark" for="accLoan">
                                Loan Account (**** {{ substr(auth()->user()->loan_account_number, -4) }})
                            </label>
                        </div>
                        @endif
                    </div>

                    <div class="p-3 rounded-3 mb-2" style="background: rgba(0, 84, 155, 0.05); border: 1px dashed rgba(0, 84, 155, 0.2);">
                        <div class="form-check form-switch ps-5">
                            <input class="form-check-input ms-n5" type="checkbox" name="email_statement" id="emailStatementToggle" value="1">
                            <label class="form-check-label fw-bold small text-dark ps-2" for="emailStatementToggle">
                                <i class="fas fa-envelope-open-text me-2 text-primary"></i> Send a copy to my email
                            </label>
                        </div>
                        <div class="small text-muted mt-1 ps-4 ms-2" style="font-size: 10px;">
                            Verification required for some email providers.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm">
                        <i class="fas fa-cloud-download-alt me-2"></i> Process eStatement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- High Fidelity Detail Modal -->
<div class="modal fade" id="trxViewDetailsBox" tabindex="-1" style="z-index: 2000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="activity-icon mx-auto mb-3" style="width: 60px; height: 60px; font-size: 24px;">
                     <i class="fas fa-receipt"></i>
                </div>
                <h4 class="fw-bold mb-1 modal-title-val">Description</h4>
                <div class="text-muted small mb-4 modal-date-val">Date</div>
                
                <div class="display-5 fw-bold mb-4 modal-amount-val">$0.00</div>
                
                <div class="site-card p-3 text-start mb-0" style="background: var(--secondary-content-background-color) !important;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Status</span>
                        <span class="fw-bold small modal-status-val">Completed</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Transaction ID</span>
                        <span class="fw-bold small modal-trx-val">TRX12345</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Method</span>
                        <span class="fw-bold small modal-method-val">System</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 gap-2 flex-column">
                <a href="#" id="btnDownloadReceipt" class="btn btn-primary w-100 rounded-pill py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-lg" style="background: var(--primary-color, #00549b); border: none;">
                    <i class="fas fa-file-pdf"></i> Download Receipt
                </a>
                <a href="#" id="btnDisputeTxn" class="btn w-100 rounded-pill py-2 fw-semibold d-none align-items-center justify-content-center gap-2" style="background: #ffffff; color: #1e293b; border: 1px solid #cbd5e1;">
                    <i class="fas fa-flag text-muted"></i> Dispute this transaction
                </a>
                <a href="#" id="btnViewDisputeClaim" class="btn w-100 rounded-pill py-2 fw-semibold d-none align-items-center justify-content-center gap-2" style="background: #eef6fb; color: var(--primary-color, #00549b); border: 1px solid rgba(0, 84, 155, 0.22);">
                    <i class="fas fa-file-alt"></i> <span>View Dispute Claim</span>
                </a>
                <button type="button" class="btn btn-link text-muted text-decoration-none w-100 py-2 fw-bold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('js')
    <script>
        $(function() {
            // General daterange picker
            $('input[name="daterange"]').daterangepicker({
                opens: 'left',
                autoUpdateInput: false,
                locale: { cancelLabel: 'Clear' }
            });

            $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            });
            
            // Separate date pickers for "From" and "To" in Modal
            $('#fromDate, #toDate').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                autoUpdateInput: true,
                maxDate: moment(),
                parentEl: '#eStatementModal',
                locale: { format: 'MM/DD/YYYY' }
            });

            $('#fromDate, #toDate').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY'));
            });

            // Handle "stuck" state on download
            $('#eStatementModal form').on('submit', function() {
                var $btn = $(this).find('button[type="submit"]');
                var originalHtml = $btn.html();
                
                $btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin me-2"></i> Generating...');
                
                // Reset button and hide modal after a short delay (browser handles the download stream)
                setTimeout(function() {
                    $btn.prop('disabled', false).html(originalHtml);
                    $('#eStatementModal').modal('hide');
                    if (typeof window.hideLoader === 'function') {
                        window.hideLoader();
                    }
                }, 4000);
            });

            // Toggle custom date range field
            $('input[name="period"]').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#customDateRange').removeClass('d-none').hide().fadeIn();
                } else {
                    $('#customDateRange').fadeOut(function() {
                        $(this).addClass('d-none');
                    });
                }
            });

            // Mobile-first expandable card settlement details
            $('.activity-row').on('click', function(e) {
                if (window.innerWidth < 768) {
                    if ($(e.target).closest('a, button').length) {
                        return;
                    }
                    e.preventDefault();
                    e.stopPropagation();
                    var $details = $(this).find('.mobile-settlement-details');
                    var $chevron = $(this).find('.mobile-expand-chevron');
                    $details.slideToggle(200);
                    $chevron.toggleClass('rotate-180');
                }
            });

            // Modal detail population (desktop only)
            $('#trxViewDetailsBox').on('show.bs.modal', function (event) {
                if (window.innerWidth < 768) {
                    return false;
                }
                var button = $(event.relatedTarget);
                var modal = $(this);
                modal.find('.modal-title-val').text(button.data('title'));
                modal.find('.modal-amount-val').text(button.data('amount'));
                modal.find('.modal-date-val').text(button.data('date'));
                modal.find('.modal-trx-val').text(button.data('trx'));
                modal.find('.modal-status-val').text(button.data('status'));
                modal.find('.modal-method-val').text(button.data('method'));
                
                // Set Download Receipt Link (Universal Download Support)
                var receiptUrl = "{{ route('user.transactions.receipt', ':tnx') }}".replace(':tnx', button.data('trx'));
                modal.find('#btnDownloadReceipt').attr('href', receiptUrl);

                // Toggle Dispute Transaction vs View Claim button cleanly
                var disputeUrl = button.attr('data-dispute-url') || '';
                var claimNum = button.attr('data-dispute-claim') || '';
                var claimStatus = button.attr('data-dispute-status') || '';
                var claimViewUrl = button.attr('data-dispute-view-url') || '';

                var $btnDispute = modal.find('#btnDisputeTxn');
                var $btnViewClaim = modal.find('#btnViewDisputeClaim');

                if (claimNum && claimViewUrl) {
                    $btnDispute.addClass('d-none').removeClass('d-flex');
                    $btnViewClaim.attr('href', claimViewUrl)
                        .removeClass('d-none').addClass('d-flex')
                        .find('span').text('Claim #' + claimNum + ' (' + claimStatus + ')');
                } else if (disputeUrl) {
                    $btnViewClaim.addClass('d-none').removeClass('d-flex');
                    $btnDispute.attr('href', disputeUrl)
                        .removeClass('d-none').addClass('d-flex');
                } else {
                    $btnDispute.addClass('d-none').removeClass('d-flex');
                    $btnViewClaim.addClass('d-none').removeClass('d-flex');
                }
            });
        });
    </script>
@endpush
@endsection

