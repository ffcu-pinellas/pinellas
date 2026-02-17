@extends('frontend::layouts.user')

@section('title')
    {{ __('Bill Pay') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10 col-12">
        <div class="text-center mb-5">
            <h1 class="h2 fw-bold mb-3">Pay a bill</h1>
            <p class="text-muted">Fast, secure payments to your favorite companies and people.</p>
        </div>

        <div class="site-card border-0 shadow-sm overflow-hidden mb-5">
            <div class="site-card-body p-4 bg-light bg-opacity-50 border-bottom">
                <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border-0">
                    <span class="input-group-text bg-white border-0 ps-4">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" id="billerSearch" class="form-control border-0 py-3" placeholder="Search for a company or person..." style="font-size: 1.1rem;">
                </div>
            </div>

            <div class="site-card-body p-0">
                <div class="biller-list">
                    @forelse($billers as $biller)
                        <div class="biller-item p-4 d-flex align-items-center justify-content-between border-bottom hover-bg-light transition-all pointer" data-bs-toggle="modal" data-bs-target="#payModal{{ $biller->id }}">
                            <div class="d-flex align-items-center gap-4">
                                <div class="biller-avatar bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 56px; height: 56px;">
                                    {{ substr($biller->name, 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">{{ $biller->name }}</h6>
                                    <div class="text-muted small fw-600 text-uppercase" style="letter-spacing: 0.5px;">{{ $biller->type }}</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="text-end d-none d-md-block">
                                    <div class="small text-muted mb-1">Limit</div>
                                    <div class="fw-bold small">{{ $biller->min_amount }} - {{ $biller->max_amount > 0 ? $biller->max_amount : '∞' }}</div>
                                </div>
                                <button class="btn btn-outline-primary rounded-pill px-4 fw-bold">Pay</button>
                            </div>
                        </div>

                        {{-- Modern Pay Modal --}}
                        <div class="modal fade" id="payModal{{ $biller->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
                                    <div class="modal-header border-0 p-4 pb-0">
                                        <h5 class="fw-bold mb-0">Pay {{ $biller->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('user.bill-pay.pay') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="biller_id" value="{{ $biller->id }}">
                                        <div class="modal-body p-4">
                                            <div class="mb-4">
                                                <label class="small text-muted text-uppercase fw-bold mb-2 d-block">Pay From Account</label>
                                                <select name="account_type" class="form-select border-2 rounded-3 p-2 fw-600" required>
                                                    <option value="default">{{ __('Checking Account (...') . substr(auth()->user()->account_number, -4) . ')' }} - {{ setting('site_currency', 'global') }} {{ auth()->user()->balance }}</option>
                                                    @foreach($savingsAccounts as $savings)
                                                        <option value="savings_{{ $savings->id }}">{{ __('Savings Account (...') . substr($savings->account_number, -4) . ')' }} - {{ setting('site_currency', 'global') }} {{ $savings->balance }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <label class="small text-muted text-uppercase fw-bold mb-2 d-block">Amount</label>
                                                <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden border-2 border">
                                                    <span class="input-group-text bg-white border-0 fw-bold">{{ setting('site_currency', 'global') }}</span>
                                                    <input type="number" step="0.01" class="form-control border-0 fw-bold" name="amount" required placeholder="0.00" style="font-size: 1.5rem;">
                                                </div>
                                            </div>

                                            @php $labels = json_decode($biller->label, true) ?? []; @endphp
                                            @foreach($labels as $label)
                                                <div class="mb-3">
                                                    <label class="small text-muted text-uppercase fw-bold mb-2 d-block">{{ $label }}</label>
                                                    <input type="text" class="form-control border-2 rounded-3 p-2" name="data[{{ $label }}]" required>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="modal-footer border-0 p-4 pt-0">
                                            <button type="submit" class="btn btn-primary rounded-pill w-100 py-3 fw-bold shadow-sm">Send Payment</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-5">
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
                                <i class="fas fa-search text-muted fs-1"></i>
                            </div>
                            <h4 class="fw-bold">No billers found</h4>
                            <p class="text-muted">Try searching for a different company or name.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- History/Scheduled Links -->
        <div class="row g-4">
            <div class="col-md-6">
                <a href="#" class="site-card border-0 shadow-sm p-4 d-flex align-items-center gap-4 text-decoration-none hover-up transition-all">
                    <div class="icon-circle bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-history fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Payment History</h6>
                        <p class="text-muted mb-0 small">View your past bill payments.</p>
                    </div>
                    <i class="fas fa-chevron-right ms-auto text-muted"></i>
                </a>
            </div>
            <div class="col-md-6">
                <a href="#" class="site-card border-0 shadow-sm p-4 d-flex align-items-center gap-4 text-decoration-none hover-up transition-all">
                    <div class="icon-circle bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-calendar-alt fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Scheduled Payments</h6>
                        <p class="text-muted mb-0 small">Manage your upcoming items.</p>
                    </div>
                    <i class="fas fa-chevron-right ms-auto text-muted"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        "use strict";
        
        // Premium biller search
        $('#billerSearch').on('keyup', function() {
            let value = $(this).val().toLowerCase();
            $(".biller-item").each(function() {
                let name = $(this).find('h6').text().toLowerCase();
                let type = $(this).find('.text-muted').text().toLowerCase();
                $(this).toggle(name.includes(value) || type.includes(value));
            });
        });
    });
</script>
<style>
    .hover-bg-light:hover { background-color: rgba(0,0,0,0.02); }
    .pointer { cursor: pointer; }
    .transition-all { transition: all 0.2s ease; }
    .hover-up:hover { transform: translateY(-5px); }
</style>
@endsection

