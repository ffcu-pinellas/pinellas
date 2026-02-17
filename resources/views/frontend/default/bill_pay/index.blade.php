@extends('frontend::layouts.user')
@section('title')
    {{ __('Bill Pay') }}
@endsection
@section('content')
    <div class="row">
        {{-- Navigation Sidebar (Same pattern as settings) --}}
        <div class="col-xl-3 col-lg-4 col-md-12 col-12">
            <div class="site-card profile-nav">
                <div class="site-card-body p-0">
                    <div class="settings-nav-group">
                        <div class="nav-group-title">BILL PAY</div>
                        <ul class="settings-nav-list">
                            <li class="active">
                                <a href="{{ route('user.bill-pay.index') }}">
                                    <i data-lucide="list"></i>
                                    <span>{{ __('Available Billers') }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i data-lucide="history"></i>
                                    <span>{{ __('Payment History') }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i data-lucide="calendar"></i>
                                    <span>{{ __('Scheduled') }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="col-xl-9 col-lg-8 col-md-12 col-12">
            <div class="site-card profile-details-view">
                <div class="site-card-header">
                    <h3 class="title">{{ __('Pay a Bill') }}</h3>
                </div>
                <div class="site-card-body">
                    <div class="bill-pay-search mb-4">
                        <div class="inputs">
                            <input type="text" id="billerSearch" class="box-input" placeholder="Search for a company or person...">
                        </div>
                    </div>

                    <div class="biller-list">
                        @forelse($billers as $biller)
                            <div class="biller-item site-card mb-3 pointer" data-bs-toggle="modal" data-bs-target="#payModal{{ $biller->id }}">
                                <div class="site-card-body d-flex align-items-center justify-content-between py-3">
                                    <div class="biller-info d-flex align-items-center">
                                        <div class="biller-icon me-3">
                                            <div class="icon-circle bg-light">
                                                <i data-lucide="building-2"></i>
                                            </div>
                                        </div>
                                        <div class="biller-meta">
                                            <div class="biller-name fw-bold">{{ $biller->name }}</div>
                                            <div class="biller-type text-muted small">{{ $biller->type }}</div>
                                        </div>
                                    </div>
                                    <div class="biller-action">
                                        <button class="site-btn-sm primary-btn">{{ __('Pay') }}</button>
                                    </div>
                                </div>
                            </div>

                            {{-- Pay Modal --}}
                            <div class="modal fade" id="payModal{{ $biller->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ __('Pay :biller', ['biller' => $biller->name]) }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('user.bill-pay.pay') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="biller_id" value="{{ $biller->id }}">
                                            <div class="modal-body">
                                                <div class="biller-summary mb-4 p-3 bg-light rounded">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="small text-muted">{{ __('Category') }}</div>
                                                            <div class="fw-bold">{{ $biller->type }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="small text-muted">{{ __('Limits') }}</div>
                                                            <div class="fw-bold">{{ $biller->min_amount }} - {{ $biller->max_amount > 0 ? $biller->max_amount : 'No Limit' }}</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="inputs mb-3">
                                                    <label class="input-label">{{ __('Pay From Account') }}</label>
                                                    <select name="account_type" class="form-select" required>
                                                        <option value="default">{{ __('Checking Account (...') . substr(auth()->user()->account_number, -4) . ')' }} - {{ setting('site_currency', 'global') }} {{ auth()->user()->balance }}</option>
                                                        @foreach($savingsAccounts as $savings)
                                                            <option value="savings_{{ $savings->id }}">{{ __('Savings Account (...') . substr($savings->account_number, -4) . ')' }} - {{ setting('site_currency', 'global') }} {{ $savings->balance }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="inputs mb-3">
                                                    <label class="input-label">{{ __('Amount to Pay') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">{{ setting('site_currency', 'global') }}</span>
                                                        <input type="number" step="0.01" class="form-control" name="amount" required placeholder="0.00">
                                                    </div>
                                                </div>

                                                @foreach(json_decode($biller->label, true) as $label)
                                                    <div class="inputs mb-3">
                                                        <label class="input-label">{{ $label }}</label>
                                                        <input type="text" class="box-input" name="data[{{ $label }}]" required>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="site-btn-sm red-btn" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                <button type="submit" class="site-btn-sm primary-btn">{{ __('Send Payment') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-5">
                                <i data-lucide="search-x" size="48" class="text-muted mb-3"></i>
                                <p class="text-muted">{{ __('No billers found or available.') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            "use strict";
            
            // Simple biller search
            $('#billerSearch').on('keyup', function() {
                let value = $(this).val().toLowerCase();
                $(".biller-item").filter(function() {
                    $(this).toggle($(this).find('.biller-name').text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
@endsection

@push('style')
<style>
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .biller-item {
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #eee;
    }
    .biller-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-color: var(--primary-color);
    }
</style>
@endpush
