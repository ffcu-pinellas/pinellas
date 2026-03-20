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
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-1 rounded-pill small fw-bold">Pending Review</span>
                                        @break
                                        @case('success')
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill small fw-bold">Completed</span>
                                        @break
                                        @case('failed')
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-1 rounded-pill small fw-bold">Cancelled</span>
                                        @break
                                    @endswitch
                                </div>
                                @if($transaction->transfer_type->value !== 'own_bank_transfer')
                                    <button class="btn btn-link btn-sm text-primary p-0 mt-1 small fw-bold text-decoration-none" onclick="toggleProgress(this)">
                                        Track Status <i class="fas fa-chevron-down ms-1"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        @if($transaction->transfer_type->value !== 'own_bank_transfer')
                            <div class="progress-track-wrapper d-none bg-light bg-opacity-50 p-4 border-bottom">
                                <div class="progress-stepper d-flex justify-content-between position-relative mx-auto" style="max-width: 600px;">
                                    <!-- Background Line -->
                                    <div class="position-absolute top-50 start-0 end-0 translate-middle-y bg-secondary opacity-25" style="height: 2px; z-index: 0;"></div>
                                    <div class="position-absolute top-50 start-0 translate-middle-y bg-primary transition-all" style="height: 2px; z-index: 1; width: {{ $transaction->status->value == 'success' ? '100' : '33' }}%;"></div>

                                    @php
                                        $steps = [
                                            ['label' => 'Submitted', 'icon' => 'check-circle', 'done' => true],
                                            ['label' => 'Reviewing', 'icon' => 'shield-alt', 'done' => true],
                                            ['label' => 'Processing', 'icon' => 'sync', 'done' => $transaction->status->value == 'success'],
                                            ['label' => 'Sent', 'icon' => 'paper-plane', 'done' => $transaction->status->value == 'success']
                                        ];
                                    @endphp

                                    @foreach($steps as $index => $step)
                                        <div class="step-item text-center position-relative" style="z-index: 2; width: 80px;">
                                            <div class="step-icon mx-auto rounded-circle d-flex align-items-center justify-content-center shadow-sm {{ $step['done'] ? 'bg-primary text-white' : 'bg-white text-muted border' }}" style="width: 32px; height: 32px; transition: all 0.3s;">
                                                <i class="fas fa-{{ $step['icon'] }} small"></i>
                                            </div>
                                            <div class="step-label mt-2 small fw-bold {{ $step['done'] ? 'text-dark' : 'text-muted' }}">{{ $step['label'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="text-center mt-3 small text-muted">
                                    @if($transaction->status->value == 'pending')
                                        <i class="fas fa-info-circle me-1"></i> Our team is currently reviewing your transfer. Estimated completion: 1-2 business hours.
                                    @elseif($transaction->status->value == 'success')
                                        <i class="fas fa-check-circle text-success me-1"></i> Funds have been successfully sent to the recipient bank.
                                    @else
                                        <i class="fas fa-times-circle text-danger me-1"></i> Transfer was cancelled. Please check your secure messages for details.
                                    @endif
                                </div>
                            </div>
                        @endif
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

        function toggleProgress(btn) {
            const wrapper = btn.closest('.activity-item').nextElementSibling;
            wrapper.classList.toggle('d-none');
            const icon = btn.querySelector('i');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        }
    </script>
    <style>
        .hover-bg-light:hover { background-color: rgba(0,0,0,0.02); }
        .transition-all { transition: all 0.2s ease; }
        .fw-600 { font-weight: 600; }
    </style>
@endsection

