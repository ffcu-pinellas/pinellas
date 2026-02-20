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
            <button type="button" class="btn btn-link text-decoration-none" data-bs-toggle="modal" data-bs-target="#historyModal">
                <i class="fas fa-history me-1"></i> View History
            </button>
        </div>

        <form action="{{ route('user.remote_deposit.store') }}" method="POST" enctype="multipart/form-data" id="depositForm">
            @csrf
            
            <!-- Step 1: Info -->
            <div class="site-card mb-4 p-4">
                <div class="row g-3">
                    <div class="col-12">
                         <label class="small text-muted mb-1 fw-bold text-uppercase">Deposit to</label>
                         <select name="account_id" class="form-select border-0 border-bottom shadow-none rounded-0 px-0 fw-600" style="padding-bottom: 10px; font-size: 14px;" required>
                            <option value="checking">Personal Checking (...{{ substr(auth()->user()->account_number, -4) }}) - ${{ number_format(auth()->user()->balance, 2) }}</option>
                            <option value="savings">Primary Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}) - ${{ number_format(auth()->user()->savings_balance, 2) }}</option>
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

    <!-- History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Deposit History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    @if($deposits->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <tbody>
                                @foreach($deposits as $deposit)
                                    <tr class="border-bottom">
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 text-primary" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-camera"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $deposit->created_at->format('M d, Y') }}</div>
                                                    <div class="small text-muted text-capitalize">{{ $deposit->account_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4 py-3">
                                            <div class="fw-bold text-dark">+{{ setting('site_currency') }} {{ number_format($deposit->amount, 2) }}</div>
                                            @if($deposit->status == 'pending')
                                                <span class="badge bg-warning text-dark bg-opacity-25 rounded-pill px-3">Pending</span>
                                            @elseif($deposit->status == 'approved')
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Approved</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Rejected</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" alt="No Data" style="height: 60px; opacity: 0.5;" class="mb-3">
                        <p class="text-muted small mb-0">No past deposits found.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    // Adjust select font size via JS if needed, or CSS below
</script>
@endpush

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
