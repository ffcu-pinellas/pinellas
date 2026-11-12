@extends('frontend::layouts.user')

@section('title')
    {{ __('Dispute Transaction') }} — {{ $txn->tnx }}
@endsection

@push('style')
<style>
    #securityGateModal { z-index: 2060 !important; }
    .dispute-reason-card {
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 16px;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .dispute-reason-card:hover { border-color: #00549b; background: rgba(0, 84, 155, 0.03); }
    .btn-check:checked + .dispute-reason-card {
        border-color: #00549b;
        background: rgba(0, 84, 155, 0.07);
        box-shadow: 0 4px 12px rgba(0, 84, 155, 0.12);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10 col-12">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('user.transactions') }}" class="back-nav-link">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="h3 fw-bold mb-0">{{ __('File Regulation E Transaction Dispute') }}</h1>
                    <p class="text-muted small mb-0">{{ __('Report unauthorized account activity, duplicate billing, or an error on your statement.') }}</p>
                </div>
            </div>

            <!-- Disputed Transaction Summary -->
            <div class="site-card p-4 mb-4 border-start border-4 border-primary">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 mb-2">{{ $walletLabel }}</span>
                        <h5 class="fw-bold mb-1">{{ $txn->description }}</h5>
                        <div class="text-muted small">
                            <span>Ref: <code>{{ $txn->tnx }}</code></span>
                            <span class="mx-2">•</span>
                            <span>{{ $txn->created_at?->format('M d, Y h:i A') }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ $txn->method ?: 'System' }}</span>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small text-uppercase fw-bold">{{ __('Total Debited') }}</div>
                        <div class="h3 fw-bold text-danger mb-0">-${{ number_format($maxAmount, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="site-card p-4">
                @if ($errors->any())
                    <div class="alert alert-danger rounded-3">
                        <ul class="mb-0 ps-3 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="disputeSubmitForm"
                      action="{{ route('user.disputes.store', $txn->tnx) }}"
                      method="POST"
                      enctype="multipart/form-data"
                      onsubmit="event.preventDefault(); SecurityGate.gate(this);">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted mb-2">{{ __('1. Select Regulation E Dispute Reason') }} <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            @php
                                $descriptions = [
                                    'unauthorized_charge' => 'I did not authorize this transfer, payment, or debit.',
                                    'duplicate_billing' => 'My account was debited more than once for the same transaction.',
                                    'not_received' => 'I paid for goods or services that were never delivered or cancelled.',
                                    'incorrect_amount' => 'The amount debited differs from my authorized receipt or instruction.',
                                    'other' => 'Another billing error, recurring subscription issue, or claim reason.',
                                ];
                            @endphp
                            @foreach($categories as $key => $label)
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check dispute-category-radio" name="category" id="cat_{{ $key }}" value="{{ $key }}"
                                           {{ old('category', 'unauthorized_charge') === $key ? 'checked' : '' }} required>
                                    <label class="dispute-reason-card w-100 h-100" for="cat_{{ $key }}">
                                        <div class="fw-bold text-dark small">{{ $label }}</div>
                                        <div class="text-muted" style="font-size: 12px;">{{ $descriptions[$key] ?? '' }}</div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div id="otherReasonGroup" class="mt-3 {{ old('category') === 'other' ? '' : 'd-none' }}">
                            <label class="form-label fw-bold small text-uppercase text-muted">{{ __('Specify Your Reason') }} <span class="text-danger">*</span></label>
                            <input type="text" name="other_reason" id="otherReasonInput" class="form-control"
                                   value="{{ old('other_reason') }}"
                                   maxlength="200"
                                   {{ old('category') === 'other' ? 'required' : '' }}
                                   placeholder="e.g., Cancelled subscription still billed, ATM dispensed short cash, etc.">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-muted">{{ __('2. Payee / Merchant Name') }}</label>
                            <input type="text" name="merchant_name" class="form-control"
                                   value="{{ old('merchant_name', $txn->description) }}"
                                   placeholder="Payee or merchant shown on receipt">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-muted">{{ __('3. Disputed Amount (USD)') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">$</span>
                                <input type="number" step="0.01" min="0.01" max="{{ $maxAmount }}"
                                       name="disputed_amount" class="form-control fw-bold"
                                       value="{{ old('disputed_amount', number_format($maxAmount, 2, '.', '')) }}" required>
                            </div>
                            <div class="form-text">{{ __('Maximum eligible claim amount:') }} ${{ number_format($maxAmount, 2) }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">{{ __('4. Detailed Explanation') }} <span class="text-danger">*</span></label>
                        <textarea name="description" rows="4" class="form-control" required minlength="15" maxlength="2000"
                                  placeholder="Explain what happened, when you noticed the error, and whether you contacted the merchant/recipient...">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">{{ __('5. Supporting Evidence (Optional)') }}</label>
                        <input type="file" name="evidence" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf">
                        <div class="form-text">{{ __('Upload a receipt, screenshot, or cancellation email (JPG, PNG, WEBP, or PDF, max 5MB).') }}</div>
                    </div>

                    <div class="alert alert-light border rounded-3 mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="attestationCheck" name="attestation" {{ old('attestation') ? 'checked' : '' }} required>
                            <label class="form-check-label small" for="attestationCheck">
                                <strong>{{ __('Regulation E Good-Faith Declaration:') }}</strong>
                                {{ __('I affirm under penalty of perjury that the information above is true and accurate. I understand FrontField Credit Union may issue a temporary provisional credit during investigation, and may reverse that credit if the transaction is proven authorized or valid.') }}
                            </label>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <a href="{{ route('user.transactions') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-shield-alt me-2"></i> {{ __('Verify & Submit Claim') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(function () {
        function syncOtherReasonField() {
            var isOther = $('input[name="category"]:checked').val() === 'other';
            var $group = $('#otherReasonGroup');
            var $input = $('#otherReasonInput');
            if (isOther) {
                $group.removeClass('d-none');
                $input.prop('required', true);
            } else {
                $group.addClass('d-none');
                $input.prop('required', false);
            }
        }
        $(document).on('change', '.dispute-category-radio', syncOtherReasonField);
        syncOtherReasonField();
    });
</script>
@endpush
