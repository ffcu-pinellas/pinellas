@extends('frontend::layouts.user')

@section('title')
{{ __('Remote Deposit') }}
@endsection

@section('content')
<div class="row justify-content-center mb-5">
    <div class="col-lg-7">
        <!-- Banno Header -->
        <div class="text-center mb-5">
            <h1 class="h2 fw-bold mb-3">Deposit a check</h1>
            <p class="text-muted">Quickly deposit checks from anywhere using your device's camera.</p>
        </div>

        <form action="{{ route('user.remote_deposit.store') }}" method="POST" enctype="multipart/form-data" id="depositForm">
            @csrf
            
            <!-- Step 1: Info -->
            <div class="site-card mb-4 p-4">
                <div class="row g-3">
                    <div class="col-12">
                         <label class="small text-muted mb-1 fw-bold text-uppercase">Deposit to</label>
                         <select name="account_id" class="form-select border-0 border-bottom shadow-none rounded-0 px-0 fs-5 fw-600" style="padding-bottom: 10px;" required>
                            <option value="checking">Personal Checking (...{{ substr(auth()->user()->account_number, -4) }})</option>
                            @php
                                $savingsAccounts = \App\Models\SavingsAccount::where('user_id', auth()->id())->get();
                            @endphp
                            @foreach($savingsAccounts as $savings)
                                <option value="savings_{{ $savings->id }}">{{ $savings->type }} (...{{ substr($savings->account_number, -4) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <label class="small text-muted mb-1 fw-bold text-uppercase">Amount</label>
                        <div class="input-group border-bottom">
                            <span class="input-group-text bg-transparent border-0 ps-0 fs-4 fw-600">$</span>
                            <input type="number" name="amount" class="form-control border-0 shadow-none fs-4 fw-600 px-1" step="0.01" min="0.01" placeholder="0.00" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Photos -->
            <div class="site-card mb-4 overflow-hidden">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h6 class="fw-bold text-uppercase small text-muted mb-0">Check Photos</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6 text-center">
                            <div class="deposit-capture-box position-relative rounded-3 p-4 bg-light d-flex flex-column align-items-center justify-content-center" style="min-height: 180px; border: 2px dashed #ddd; transition: all 0.2s;">
                                <label class="w-100 h-100 position-absolute top-0 start-0 cursor-pointer mb-0">
                                    <input type="file" name="front_image" class="d-none" accept="image/*" capture="environment" required>
                                </label>
                                <div class="capture-icon mb-2">
                                    <i class="fas fa-camera fa-2x text-primary"></i>
                                </div>
                                <div class="fw-bold mb-1">Front of check</div>
                                <div class="small text-muted status-text">Tap to capture</div>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <div class="deposit-capture-box position-relative rounded-3 p-4 bg-light d-flex flex-column align-items-center justify-content-center" style="min-height: 180px; border: 2px dashed #ddd; transition: all 0.2s;">
                                <label class="w-100 h-100 position-absolute top-0 start-0 cursor-pointer mb-0">
                                    <input type="file" name="back_image" class="d-none" accept="image/*" capture="environment" required>
                                </label>
                                <div class="capture-icon mb-2">
                                    <i class="fas fa-signature fa-2x text-primary"></i>
                                </div>
                                <div class="fw-bold mb-1">Back of check</div>
                                <div class="small text-muted status-text">Tap to capture</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guidance -->
            <div class="alert alert-secondary border-0 small d-flex gap-3 p-4 mb-4" style="background: rgba(0,0,0,0.03); border-radius: 12px;">
                <i class="fas fa-lightbulb text-warning fa-lg"></i>
                <div>
                    <div class="fw-bold text-dark mb-1">Endorsement guidance</div>
                    <div class="text-muted">
                        Be sure to endorse the back of your check with your signature and "For Mobile Deposit Only to Pinellas FCU".
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow">
                Submit deposit
            </button>
        </form>
    </div>
</div>
@endsection

@section('style')
<style>
    .fw-600 { font-weight: 600; }
    .deposit-capture-box:hover {
        border-color: var(--body-text-theme-color) !important;
        background-color: var(--secondary-content-background-color) !important;
    }
    .deposit-capture-box.captured {
        border-color: #28a745 !important;
        background-color: #f8fff9 !important;
    }
    .deposit-capture-box.captured .capture-icon i { color: #28a745 !important; }
</style>
@endsection

@push('js')
<script>
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const box = this.closest('.deposit-capture-box');
                const status = box.querySelector('.status-text');
                box.classList.add('captured');
                status.textContent = 'Check photo captured';
                status.classList.replace('text-muted', 'text-success');
                status.classList.add('fw-bold');
            }
        });
    });
</script>
@endpush

```
