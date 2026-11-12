@extends('frontend::layouts.user')

@section('title')
    {{ __('Dispute Transaction') }} — {{ $txn->tnx }}
@endsection

@push('style')
<style>
    #securityGateModal { z-index: 2060 !important; }
    .dispute-reason-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        cursor: pointer;
        background: #ffffff;
        transition: border-color 0.15s ease, background-color 0.15s ease;
    }
    .dispute-reason-card:hover {
        border-color: #00549b;
        background: #f8fafc;
    }
    .btn-check:checked + .dispute-reason-card {
        border-color: #00549b;
        background: #eef6fb;
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
                    <h1 class="h4 fw-bold mb-0 text-dark">{{ __('Dispute Transaction') }}</h1>
                    <p class="text-muted small mb-0">{{ __('Submit a Regulation E inquiry for an unauthorized debit, duplicate charge, or billing error.') }}</p>
                </div>
            </div>

            <!-- Disputed Transaction Summary -->
            <div class="banno-card p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <span class="badge rounded-pill px-3 py-1 mb-2" style="background: #eef6fb; color: #00549b; border: 1px solid rgba(0, 84, 155, 0.18); font-weight: 600;">{{ $walletLabel }}</span>
                        <h5 class="fw-bold mb-1 text-dark">{{ $txn->description }}</h5>
                        <div class="text-muted small">
                            <span>{{ __('Transaction ID:') }} <span class="fw-semibold text-dark">{{ $txn->tnx }}</span></span>
                            <span class="mx-2">•</span>
                            <span>{{ $txn->created_at?->format('M d, Y h:i A') }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ $txn->method ?: 'System' }}</span>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small text-uppercase fw-semibold">{{ __('Transaction Amount') }}</div>
                        <div class="h4 fw-bold text-dark mb-0">-${{ number_format($maxAmount, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="banno-card p-4">
                @if ($errors->any())
                    <div class="alert border mb-4" style="background: #fef2f2; border-color: #fecaca !important; color: #991b1b; border-radius: 12px;">
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
                        <label class="form-label fw-semibold small text-uppercase text-muted mb-2">{{ __('Reason for Dispute') }} <span class="text-dark">*</span></label>
                        <div class="row g-2">
                            @php
                                $descriptions = [
                                    'unauthorized_charge' => 'I did not authorize this transfer, payment, or debit.',
                                    'duplicate_billing' => 'My account was debited more than once for the same transaction.',
                                    'not_received' => 'I paid for goods or services that were never delivered or were cancelled.',
                                    'incorrect_amount' => 'The amount debited differs from my authorized receipt or instruction.',
                                    'other' => 'Another billing error, recurring subscription issue, or claim reason.',
                                ];
                            @endphp
                            @foreach($categories as $key => $label)
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check dispute-category-radio" name="category" id="cat_{{ $key }}" value="{{ $key }}"
                                           {{ old('category', 'unauthorized_charge') === $key ? 'checked' : '' }} required>
                                    <label class="dispute-reason-card w-100 h-100" for="cat_{{ $key }}">
                                        <div class="fw-semibold text-dark small mb-1">{{ $label }}</div>
                                        <div class="text-muted" style="font-size: 12px; line-height: 1.4;">{{ $descriptions[$key] ?? '' }}</div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div id="otherReasonGroup" class="mt-3 {{ old('category') === 'other' ? '' : 'd-none' }}">
                            <label class="form-label fw-semibold small text-uppercase text-muted">{{ __('Specify Your Reason') }} <span class="text-dark">*</span></label>
                            <input type="text" name="other_reason" id="otherReasonInput" class="form-control"
                                   value="{{ old('other_reason') }}"
                                   maxlength="200"
                                   {{ old('category') === 'other' ? 'required' : '' }}
                                   placeholder="e.g., Cancelled subscription still billed, ATM dispensed short cash, etc.">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-uppercase text-muted">{{ __('Payee / Merchant Name') }}</label>
                            <input type="text" name="merchant_name" class="form-control"
                                   value="{{ old('merchant_name', $txn->description) }}"
                                   placeholder="Payee or merchant shown on statement">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-uppercase text-muted">{{ __('Disputed Amount (USD)') }} <span class="text-dark">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-semibold">$</span>
                                <input type="number" step="0.01" min="0.01" max="{{ $maxAmount }}"
                                       name="disputed_amount" class="form-control fw-semibold"
                                       value="{{ old('disputed_amount', number_format($maxAmount, 2, '.', '')) }}" required>
                            </div>
                            <div class="form-text">{{ __('Maximum eligible claim amount:') }} ${{ number_format($maxAmount, 2) }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-muted">{{ __('Details of Claim') }} <span class="text-dark">*</span></label>
                        <textarea name="description" rows="4" class="form-control" required minlength="15" maxlength="2000"
                                  placeholder="Explain what occurred, when you noticed the error, and whether you contacted the merchant or recipient...">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-uppercase text-muted">{{ __('Supporting Documentation (Optional)') }}</label>
                        <input type="file" name="evidence" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf">
                        <div class="form-text">{{ __('Attach a receipt, screenshot, or correspondence (JPG, PNG, WEBP, or PDF up to 5MB).') }}</div>
                    </div>

                    <div class="p-3 rounded-3 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="attestationCheck" name="attestation" {{ old('attestation') ? 'checked' : '' }} required>
                            <label class="form-check-label small text-dark" for="attestationCheck">
                                <strong>{{ __('Regulation E Declaration:') }}</strong>
                                {{ __('I affirm the information provided above is true and accurate. I understand FrontField Credit Union may issue a provisional credit during investigation and may reverse that credit if the transaction is determined authorized or valid.') }}
                            </label>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <a href="{{ route('user.transactions') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-semibold">
                            {{ __('Verify & Submit Dispute') }}
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
