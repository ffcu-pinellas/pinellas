@php use App\Enums\TxnStatus; @endphp
@extends('frontend::layouts.user')

@section('title')
    {{ __('Activity') }}
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('front/css/daterangepicker.css') }}">
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
    </style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold mb-0">Activity</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('user.transactions.export.csv', $queries) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fas fa-file-export me-1"></i> Export
                </a>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="site-card mb-4 p-4">
            <form id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="small text-muted mb-1 fw-bold">SEARCH</label>
                        <div class="input-group border-bottom">
                            <span class="input-group-text bg-transparent border-0 ps-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="trx" class="form-control border-0 shadow-none" placeholder="Search by description or ID" value="{{ request('trx') }}">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <label class="small text-muted mb-1 fw-bold">DATE RANGE</label>
                        <div class="input-group border-bottom">
                            <span class="input-group-text bg-transparent border-0 ps-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                            <input type="text" name="daterange" class="form-control border-0 shadow-none" value="{{ request('daterange') }}" placeholder="All time">
                        </div>
                    </div>
                    <div class="col-lg-3">
                         <label class="small text-muted mb-1 fw-bold">TYPE</label>
                         <select name="type" class="form-select border-0 border-bottom shadow-none rounded-0">
                             <option value="all" @selected(request('type') == 'all')>All types</option>
                             <option value="deposit" @selected(request('type') == 'deposit')>Deposits</option>
                             <option value="fund_transfer" @selected(request('type') == 'fund_transfer')>Transfers</option>
                             <option value="withdraw" @selected(request('type') == 'withdraw')>Withdrawals</option>
                             <option value="pay_bill" @selected(request('type') == 'pay_bill')>Bill Payments</option>
                         </select>
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Activity List -->
        <div class="site-card overflow-hidden">
            <div class="transaction-list">
                @forelse ($transactions as $transaction)
                <div class="activity-row d-flex align-items-center justify-content-between py-3 px-4 border-bottom border-light" 
                     onclick="window.location.href='{{ route('user.transactions') }}?details={{ $transaction->tnx }}'"
                     data-bs-toggle="modal" data-bs-target="#trxViewDetailsBox"
                     data-title="{{ $transaction->description }}"
                     data-trx="{{ $transaction->tnx }}"
                     data-amount="{{ isPlusTransaction($transaction->type) ? '+' : '-' }}{{ setting('currency_symbol','global').number_format($transaction->amount, 2) }}"
                     data-date="{{ $transaction->created_at->format('M d, Y h:i A') }}"
                     data-status="{{ $transaction->status->value }}"
                     data-method="{{ $transaction->method ?: 'System' }}">
                    
                    <div class="d-flex align-items-center gap-3">
                        <div class="activity-icon">
                            @if ($transaction->type->value == 'deposit' || $transaction->type->value == 'manual_deposit')
                                <i class="fas fa-arrow-down"></i>
                            @elseif(Str::contains($transaction->type->value, 'transfer'))
                                <i class="fas fa-exchange-alt"></i>
                            @elseif(Str::contains($transaction->type->value, 'loan'))
                                <i class="fas fa-file-invoice-dollar"></i>
                            @else
                                <i class="fas fa-shopping-cart"></i>
                            @endif
                        </div>
                        <div>
                            <div class="fw-bold text-dark">{{ $transaction->description }}</div>
                            <div class="text-muted small">
                                {{ $transaction->created_at->format('M d') }} • {{ $transaction->tnx }}
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold {{ isPlusTransaction($transaction->type) ? 'text-success' : 'text-dark' }}">
                            {{ isPlusTransaction($transaction->type) ? '+' : '-' }}{{ setting('currency_symbol','global').number_format($transaction->amount, 2) }}
                        </div>
                        <div class="small">
                            @if($transaction->status->value == 'success')
                                <span class="text-success"><i class="fas fa-check-circle me-1 small"></i>Completed</span>
                            @elseif($transaction->status->value == 'pending')
                                <span class="text-warning"><i class="fas fa-clock me-1 small"></i>Pending</span>
                            @else
                                <span class="text-danger"><i class="fas fa-times-circle me-1 small"></i>Failed</span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="fas fa-info-circle fa-3x text-light mb-3"></i>
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

<!-- High Fidelity Detail Modal -->
<div class="modal fade" id="trxViewDetailsBox" tabindex="-1">
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
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-primary w-100 rounded-pill py-2 fw-bold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script src="{{ asset('front/js/moment.min.js') }}"></script>
    <script src="{{ asset('front/js/daterangepicker.min.js') }}"></script>
    <script>
        $(function() {
            $('input[name="daterange"]').daterangepicker({
                opens: 'left',
                autoUpdateInput: false,
                locale: { cancelLabel: 'Clear' }
            });

            $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            });

            // Modal detail population
            $('#trxViewDetailsBox').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                modal.find('.modal-title-val').text(button.data('title'));
                modal.find('.modal-amount-val').text(button.data('amount'));
                modal.find('.modal-date-val').text(button.data('date'));
                modal.find('.modal-trx-val').text(button.data('trx'));
                modal.find('.modal-status-val').text(button.data('status'));
                modal.find('.modal-method-val').text(button.data('method'));
            });
        });
    </script>
@endpush
@endsection

