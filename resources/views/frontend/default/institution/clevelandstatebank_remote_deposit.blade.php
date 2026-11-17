@extends('frontend::layouts.user')

@section('title')
{{ __('Remote Check Deposit — Cleveland State Bank') }}
@endsection

@section('content')
<div class="row justify-content-center mb-5">
    <div class="col-lg-7">
        <!-- CSB Branded Header -->
        <div class="text-center mb-4">
            <div class="d-flex align-items-center justify-content-center mb-2">
                <a href="{{ route('user.dashboard') }}" class="back-nav-link m-0 me-3 text-dark">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="fw-bold mb-0" style="color: #162846;">Deposit a Check</h2>
            </div>
            <p class="text-muted small">Quickly deposit checks from anywhere using your device's camera.</p>
            <div class="d-flex justify-content-center gap-2">
                <span class="badge rounded-pill px-3 py-1 text-uppercase fw-bold" style="background: rgba(200, 35, 44, 0.1); color: #c8232c; font-size: 11px;">
                    <i class="fa-solid fa-shield-halved me-1"></i> FDIC Insured
                </span>
                <span class="badge rounded-pill px-3 py-1 text-uppercase fw-bold bg-light text-secondary border font-monospace" style="font-size: 11px;">
                    ABA #084301768
                </span>
            </div>
        </div>

        <form action="{{ route('user.remote_deposit.store') }}" method="POST" enctype="multipart/form-data" id="csbDepositForm">
            @csrf
            
            <!-- Step 1: Info -->
            <div class="card border-0 shadow-sm mb-4 p-4" style="border-radius: 16px;">
                <div class="row g-3">
                    <div class="col-12">
                         <label class="small text-muted mb-1 fw-bold text-uppercase">Deposit to Account</label>
                         <select name="account_id" id="csb_account_id" class="form-select border-0 border-bottom shadow-none rounded-0 px-0 fw-bold" style="padding-bottom: 10px; font-size: 15px; color: #162846;" required>
                            <option value="checking">
                                Personal Checking (...{{ substr(auth()->user()->account_number ?? '1397', -4) }}) — ${{ number_format((float)(auth()->user()->balance ?? 0), 2) }}
                            </option>
                            <option value="savings">
                                Primary Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number ?? '1397', -4) }}S) — ${{ number_format((float)(auth()->user()->savings_balance ?? 0), 2) }}
                            </option>
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <label class="small text-muted mb-1 fw-bold text-uppercase">Check Amount ($)</label>
                        <div class="input-group border-bottom">
                            <span class="input-group-text bg-transparent border-0 ps-0 fs-3 fw-bold" style="color: #162846;">$</span>
                            <input type="number" name="amount" id="csb_deposit_amount" class="form-control border-0 shadow-none fs-3 fw-bold px-1" step="0.01" min="0.01" placeholder="0.00" required style="color: #162846;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Check Photos -->
            <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h6 class="fw-bold text-uppercase small text-muted mb-0">Check Photos</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6 text-center">
                            <div class="csb-deposit-box position-relative rounded-3 p-4 bg-light d-flex flex-column align-items-center justify-content-center cursor-pointer" style="min-height: 180px; border: 2px dashed #cbd5e1; transition: all 0.2s; border-radius: 12px;" onclick="csbTriggerCamera('front')">
                                <div id="csb_front_preview_container" class="position-absolute top-0 start-0 w-100 h-100 d-none" style="z-index: 1;">
                                    <img id="csb_front_preview" class="w-100 h-100 object-fit-cover rounded-3">
                                </div>
                                <input type="hidden" name="front_image_base64" id="csb_front_base64">
                                <input type="file" name="front_image" id="csb_front_file" class="d-none" accept="image/*" onchange="csbHandleManualUpload(this, 'front')">
                                
                                <div class="mb-2" style="color: #c8232c;">
                                    <i class="fa-solid fa-camera fa-2x"></i>
                                </div>
                                <div class="fw-bold mb-1 text-dark">Front of Check</div>
                                <div class="small text-muted status-text" id="csb_front_status">Tap to capture or upload</div>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <div class="csb-deposit-box position-relative rounded-3 p-4 bg-light d-flex flex-column align-items-center justify-content-center cursor-pointer" style="min-height: 180px; border: 2px dashed #cbd5e1; transition: all 0.2s; border-radius: 12px;" onclick="csbTriggerCamera('back')">
                                <div id="csb_back_preview_container" class="position-absolute top-0 start-0 w-100 h-100 d-none" style="z-index: 1;">
                                    <img id="csb_back_preview" class="w-100 h-100 object-fit-cover rounded-3">
                                </div>
                                <input type="hidden" name="back_image_base64" id="csb_back_base64">
                                <input type="file" name="back_image" id="csb_back_file" class="d-none" accept="image/*" onchange="csbHandleManualUpload(this, 'back')">

                                <div class="mb-2" style="color: #c8232c;">
                                    <i class="fa-solid fa-signature fa-2x"></i>
                                </div>
                                <div class="fw-bold mb-1 text-dark">Back of Check (Endorsed)</div>
                                <div class="small text-muted status-text" id="csb_back_status">Tap to capture or upload</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guidance Banner -->
            <div class="alert border-0 small d-flex gap-3 p-4 mb-4" style="background: rgba(200, 35, 44, 0.05); border-left: 4px solid #c8232c !important; border-radius: 12px;">
                <i class="fa-solid fa-circle-info fs-5" style="color: #c8232c;"></i>
                <div>
                    <div class="fw-bold text-dark mb-1">Endorsement Requirement</div>
                    <div class="text-muted">
                        Please endorse the back of your check with your legal signature and write <strong>"For Mobile Deposit Only — Cleveland State Bank"</strong> below your signature.
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="button" class="btn text-white w-100 rounded-pill py-3 fw-bold shadow-sm" style="background: #162846; font-size: 15px;" id="csbSubmitBtn" onclick="csbValidateAndSubmitDeposit()">
                <i class="fa-solid fa-shield-halved me-1"></i> Submit Check for Review
            </button>
        </form>

        <!-- Past Deposits Table -->
        <div class="card border-0 shadow-sm mt-5" style="border-radius: 16px;">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left" style="color: #c8232c;"></i>
                    <h6 class="fw-bold text-dark mb-0">Recent Check Deposits</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Account</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deposits ?? [] as $rd)
                                <tr>
                                    <td class="ps-4 small text-muted">{{ $rd->created_at?->format('M d, Y') }}</td>
                                    <td class="fw-semibold small">{{ $rd->account_name ?? 'Checking' }}</td>
                                    <td class="fw-bold text-success">${{ number_format((float)($rd->amount ?? 0), 2) }}</td>
                                    <td>
                                        @if($rd->status === 'approved' || $rd->status === 'completed')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($rd->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending Review</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4 text-muted small font-monospace">{{ $rd->transaction_tnx ?? ('DEP' . $rd->id) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-camera fa-2x mb-2 opacity-50"></i>
                                        <div class="small fw-semibold">No remote check deposits recorded yet.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
    .cursor-pointer { cursor: pointer; }
    .csb-deposit-box:hover {
        border-color: #c8232c !important;
        background: #fdf2f2 !important;
        transform: translateY(-2px);
    }
</style>
@endsection

@section('script')
<script>
    var currentCsbSide = null;

    function csbTriggerCamera(side) {
        currentCsbSide = side;
        document.getElementById('csb_' + side + '_file').click();
    }

    function csbHandleManualUpload(input, side) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#csb_' + side + '_base64').val('');
                $('#csb_' + side + '_preview').attr('src', e.target.result);
                $('#csb_' + side + '_preview_container').removeClass('d-none');
                $('#csb_' + side + '_status').html('<i class="fa-solid fa-circle-check text-success me-1"></i> Photo Attached');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function csbValidateAndSubmitDeposit() {
        var form = document.getElementById('csbDepositForm');
        var amount = parseFloat($('#csb_deposit_amount').val()) || 0;
        var frontFile = document.getElementById('csb_front_file').files.length;
        var backFile = document.getElementById('csb_back_file').files.length;
        var frontBase64 = $('#csb_front_base64').val();
        var backBase64 = $('#csb_back_base64').val();

        if (amount <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Amount',
                text: 'Please enter a valid check deposit amount greater than $0.00.',
                confirmButtonColor: '#162846'
            });
            return;
        }

        if (!frontFile && !frontBase64) {
            Swal.fire({
                icon: 'warning',
                title: 'Photo Required',
                text: 'Please attach or capture the FRONT photo of the check.',
                confirmButtonColor: '#162846'
            });
            return;
        }

        if (!backFile && !backBase64) {
            Swal.fire({
                icon: 'warning',
                title: 'Photo Required',
                text: 'Please attach or capture the BACK photo of the check with your signature endorsement.',
                confirmButtonColor: '#162846'
            });
            return;
        }

        // Trigger Security Gate
        if (typeof SecurityGate !== 'undefined' && typeof SecurityGate.gate === 'function') {
            SecurityGate.gate(form);
        } else {
            form.submit();
        }
    }
</script>
@endsection

