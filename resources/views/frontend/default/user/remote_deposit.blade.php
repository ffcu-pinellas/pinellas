@extends('frontend::layouts.user')

@section('title')
{{ __('Remote Deposit') }}
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 col-md-10 mx-auto">
        <div class="site-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h5 class="fw-bold mb-0">Remote Check Deposit</h5>
                <i class="fas fa-camera text-primary fs-4"></i>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info border-0 d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-info-circle fs-4 me-3"></i>
                    <div>
                        <strong>Tip:</strong> Endorse the back of your check with "For Mobile Deposit Only to Pinellas FCU".
                    </div>
                </div>

                <form action="#" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Deposit To</label>
                        <select class="form-select form-select-lg" aria-label="Select Account">
                            <option selected>0010 CHECKING (...{{ substr(auth()->user()->account_number, -4) }})</option>
                            <option value="1">0000 SAVINGS (...90S0)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Amount</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">{{ setting('currency_symbol','global') }}</span>
                            <input type="text" class="form-control border-start-0 ps-0" placeholder="0.00">
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase">Front of Check</label>
                            <div class="border-2 border-dashed rounded-3 p-4 text-center bg-light position-relative" style="border-style: dashed; border-color: #dee2e6;">
                                <i class="fas fa-camera fs-1 text-muted opacity-25 mb-2"></i>
                                <div class="small text-muted">Click to capture front</div>
                                <input type="file" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase">Back of Check</label>
                            <div class="border-2 border-dashed rounded-3 p-4 text-center bg-light position-relative" style="border-style: dashed; border-color: #dee2e6;">
                                <i class="fas fa-signature fs-1 text-muted opacity-25 mb-2"></i>
                                <div class="small text-muted">Click to capture back</div>
                                <input type="file" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold rounded-pill">Submit Deposit</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
