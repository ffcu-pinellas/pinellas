@extends('frontend::layouts.user')

@section('title')
    {{ __('Transfer History') }}
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('front/css/daterangepicker.css') }}">
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10 col-12">
        <div class="d-flex align-items-center justify-content-between mb-5">
            <div>
                <h1 class="h3 fw-bold mb-1">Transfer History</h1>
                <p class="text-muted mb-0">Track and manage your recent money movements.</p>
            </div>
            <a href="{{ route('user.fund_transfer.index') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-plus me-2"></i> New Transfer
            </a>
        </div>

        <!-- Filter Card -->
        <div class="site-card border-0 shadow-sm mb-4">
            <div class="site-card-body p-4">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase fw-bold mb-2 d-block">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-search"></i></span>
                            <input type="text" name="trx" class="form-control border-light bg-light" value="{{ request('trx') }}" placeholder="Transaction ID...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase fw-bold mb-2 d-block">Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-calendar"></i></span>
                            <input type="text" name="daterange" class="form-control border-light bg-light" value="{{ request('daterange') }}" placeholder="Select dates...">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" name="filter" class="btn btn-dark rounded-pill px-4 fw-bold flex-grow-1">Apply</button>
                        @if (request()->has('filter'))
                            <button type="button" class="btn btn-outline-danger rounded-pill px-4 fw-bold reset-filter">Reset</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- History List -->
        <div class="site-card border-0 shadow-sm overflow-hidden">
            <div class="site-card-body p-0">
                <div class="activity-list">
                    @forelse ($transactions as $transaction)
                        <div class="activity-item p-4 d-flex align-items-center justify-content-between border-bottom hover-bg-light transition-all">
                            <div class="d-flex align-items-center gap-4">
                                <div class="activity-icon bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="fas fa-paper-plane text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-1">{{ $transaction->description }}</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small text-muted fw-600">{{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y') }}</span>
                                        <span class="text-muted small">•</span>
                                        <span class="small text-muted fw-600">{{ $transaction->tnx }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold fs-5 text-dark mb-1">
                                    - {{ $transaction->amount . ' ' . $transaction->currency }}
                                </div>
                                <div class="badge-rounded">
                                    @switch($transaction->status->value)
                                        @case('pending')
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-1 rounded-pill small fw-bold">Pending</span>
                                        @break
                                        @case('success')
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill small fw-bold">Completed</span>
                                        @break
                                        @case('failed')
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-1 rounded-pill small fw-bold">Cancelled</span>
                                        @break
                                    @endswitch
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-5">
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-history text-muted fs-2"></i>
                            </div>
                            <h5 class="fw-bold">No transfers found</h5>
                            <p class="text-muted mb-0">Your recent transfer history will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            
            @if($transactions->hasPages())
                <div class="p-4 border-top bg-light bg-opacity-50">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('script')
    <script src="{{ asset('front/js/moment.min.js') }}"></script>
    <script src="{{ asset('front/js/daterangepicker.min.js') }}"></script>
    <script>
        "use strict";
        $(document).ready(function() {
            $('input[name="daterange"]').daterangepicker({
                opens: 'left',
                autoUpdateInput: false,
                locale: { cancelLabel: 'Clear' }
            });

            $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            });

            $('input[name="daterange"]').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            $('.reset-filter').on('click', function() {
                window.location.href = "{{ route('user.fund_transfer.transfer.log') }}";
            });
        });
    </script>
    <style>
        .hover-bg-light:hover { background-color: rgba(0,0,0,0.02); }
        .transition-all { transition: all 0.2s ease; }
        .fw-600 { font-weight: 600; }
    </style>
@endsection

