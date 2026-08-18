@extends('frontend::layouts.user')

@section('title')
    {{ __('Wire Transfer') }}
@endsection

@section('content')
<div class="row justify-content-center">
    @include('frontend::fund_transfer.include.__header')

    <div class="col-xl-9 col-lg-11 col-12 mt-3">
        {{-- Page Header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
            <div>
                <h4 class="fw-bold mb-1 text-dark">{{ __('Domestic & International Wire Transfer') }}</h4>
                <p class="text-muted mb-0 small">{{ __('Send high-value wire transfers via Fedwire or global SWIFT network.') }}</p>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold" data-bs-toggle="modal" data-bs-target="#limitBox">
                    <i class="fas fa-sliders-h me-1"></i> {{ __('View Limits & Policy') }}
                </button>
            </div>
        </div>

        {{-- Progress Wizard Steps Indicator --}}
        <div class="card border-0 shadow-xs rounded-3 p-3 mb-3 bg-white">
            <div class="d-flex justify-content-between align-items-center position-relative px-2 px-md-4">
                <div class="wizard-step-indicator active d-flex align-items-center gap-2" id="stepIndicator1">
                    <span class="step-num rounded-circle d-flex align-items-center justify-content-center fw-bold">1</span>
                    <span class="step-title small fw-bold d-none d-sm-inline">{{ __('Transfer Setup') }}</span>
                </div>
                <div class="wizard-step-line flex-grow-1 mx-2 mx-md-3"></div>
                <div class="wizard-step-indicator d-flex align-items-center gap-2" id="stepIndicator2">
                    <span class="step-num rounded-circle d-flex align-items-center justify-content-center fw-bold">2</span>
                    <span class="step-title small fw-bold d-none d-sm-inline">{{ __('Recipient & Bank') }}</span>
                </div>
                <div class="wizard-step-line flex-grow-1 mx-2 mx-md-3"></div>
                <div class="wizard-step-indicator d-flex align-items-center gap-2" id="stepIndicator3">
                    <span class="step-num rounded-circle d-flex align-items-center justify-content-center fw-bold">3</span>
                    <span class="step-title small fw-bold d-none d-sm-inline">{{ __('Review & Settle') }}</span>
                </div>
            </div>
        </div>

        {{-- Main Transfer Wizard Form Card --}}
        <div class="site-card border-0 shadow-sm rounded-3 overflow-hidden mb-4 bg-white">
            <form action="{{ route('user.fund_transfer.transfer.wire.post') }}" method="POST" id="wireForm" enctype="multipart/form-data">
                @csrf

                {{-- ========================================================================= --}}
                {{-- STEP 1: WIRE TYPE & AMOUNT SETUP --}}
                {{-- ========================================================================= --}}
                <div class="wizard-pane p-4 p-md-4" id="wireStep1">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                        <i class="fas fa-wallet text-primary me-2"></i> {{ __('Step 1: Wire Transfer Setup') }}
                    </h6>

                    {{-- Wire Type Switcher --}}
                    <div class="mb-3">
                        <label class="form-label extra-small text-uppercase fw-bold text-muted mb-2 d-block">{{ __('Select Wire Transfer Classification') }}</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="wire-type-card p-3 border rounded-3 d-flex align-items-center cursor-pointer h-100 position-relative transition-all active" id="cardDomestic">
                                    <input type="radio" name="wire_type" value="domestic" class="form-check-input me-3 mt-0" checked onchange="handleWireTypeChange('domestic')">
                                    <div>
                                        <div class="fw-bold text-dark mb-0 fs-6">{{ __('Domestic Wire (Fedwire)') }}</div>
                                        <div class="text-muted extra-small">{{ __('Same-day settlement to US financial institutions via ABA Routing.') }}</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="wire-type-card p-3 border rounded-3 d-flex align-items-center cursor-pointer h-100 position-relative transition-all" id="cardInternational">
                                    <input type="radio" name="wire_type" value="international" class="form-check-input me-3 mt-0" onchange="handleWireTypeChange('international')">
                                    <div>
                                        <div class="fw-bold text-dark mb-0 fs-6">{{ __('International Wire (SWIFT)') }}</div>
                                        <div class="text-muted extra-small">{{ __('Cross-border settlement via SWIFT/BIC & IBAN code.') }}</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- From Account --}}
                        <div class="col-md-6">
                            <label class="form-label extra-small text-uppercase fw-bold text-muted">{{ __('Funding Account') }} <span class="text-danger">*</span></label>
                            <select name="wallet_type" class="form-select border shadow-none rounded-2 py-2" id="wireWalletSelect" onchange="updateSummaryCalculations()" required>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc['id'] }}" 
                                            data-name="{{ $acc['name'] }}"
                                            data-number="{{ $acc['account_number'] }}"
                                            data-balance="{{ $acc['balance'] }}" 
                                            @disabled($acc['is_restricted'])>
                                        {{ $acc['name'] }} (...{{ substr($acc['account_number'], -4) }}) - {{ $currencySymbol }}{{ number_format($acc['balance'], 2) }}
                                        @if($acc['is_restricted']) (Restricted) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Wire Amount --}}
                        <div class="col-md-6">
                            <label class="form-label extra-small text-uppercase fw-bold text-muted">{{ __('Wire Principal Amount') }} <span class="text-danger">*</span></label>
                            <div class="input-group shadow-xs rounded-2 overflow-hidden border">
                                <span class="input-group-text bg-light border-0 fw-bold text-muted">{{ $currencySymbol }}</span>
                                <input type="number" step="0.01" class="form-control border-0 fw-bold py-2" name="amount" id="wireAmountInput" required placeholder="0.00" oninput="updateSummaryCalculations()">
                            </div>
                            <div class="d-flex justify-content-between extra-small text-muted mt-1 px-1">
                                <span>{{ __('Min:') }} {{ $currencySymbol }}{{ number_format($user->getEffectiveWireMinLimit($data?->minimum_transfer ?? 50.00), 2) }}</span>
                                <span>{{ __('Max:') }} {{ $currencySymbol }}{{ number_format($user->getEffectiveWireMaxLimit($data?->maximum_transfer ?? 500000.00), 2) }}</span>
                            </div>
                            <div id="step1Error" class="text-danger small mt-1 d-none"></div>
                        </div>

                        {{-- Step 1 Live Summary Preview --}}
                        <div class="col-12 mt-3">
                            <div class="p-3 rounded-3 border bg-light d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                                <div>
                                    <span class="text-muted extra-small text-uppercase d-block">{{ __('Estimated Total Settlement Debit:') }}</span>
                                    <span class="fw-bold fs-5 text-dark" id="step1TotalDebit">{{ $currencySymbol }}0.00</span>
                                    <span class="text-muted extra-small ms-2" id="step1FeeBreakdown">({{ __('Includes Wire Fee:') }} {{ $currencySymbol }}25.00)</span>
                                </div>
                                <div>
                                    <span class="badge bg-primary px-3 py-2 rounded-pill font-monospace" id="step1TypeBadge">{{ __('Domestic Fedwire') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" onclick="validateAndGoToStep2()">
                            {{ __('Next: Recipient Details') }} <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- ========================================================================= --}}
                {{-- STEP 2: BENEFICIARY & RECEIVING BANK DETAILS --}}
                {{-- ========================================================================= --}}
                <div class="wizard-pane p-4 p-md-4 d-none" id="wireStep2">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                        <i class="fas fa-university text-primary me-2"></i> {{ __('Step 2: Recipient & Receiving Bank Details') }}
                    </h6>

                    <div class="row g-3">
                        {{-- Beneficiary Name --}}
                        <div class="col-md-6">
                            <label class="form-label extra-small text-uppercase fw-bold text-muted">{{ __('Beneficiary Full / Business Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border rounded-2 py-2" name="beneficiary_name" id="wireBeneficiaryName" required placeholder="{{ __('e.g., Apex Global Escrow LLC') }}">
                        </div>

                        {{-- Beneficiary Physical Address --}}
                        <div class="col-md-6">
                            <label class="form-label extra-small text-uppercase fw-bold text-muted">{{ __('Beneficiary Physical Street Address') }}</label>
                            <input type="text" class="form-control border rounded-2 py-2" name="beneficiary_address" id="wireBeneficiaryAddress" placeholder="{{ __('e.g., 100 Main St, Suite 400, New York, NY') }}">
                        </div>

                        {{-- Receiving Bank Name --}}
                        <div class="col-md-6">
                            <label class="form-label extra-small text-uppercase fw-bold text-muted">{{ __('Receiving Bank Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border rounded-2 py-2" name="bank_name" id="wireBankName" required placeholder="{{ __('e.g., JPMorgan Chase Bank, N.A.') }}">
                        </div>

                        {{-- Domestic ABA Routing Input --}}
                        <div class="col-md-6" id="fieldDomesticRouting">
                            <label class="form-label extra-small text-uppercase fw-bold text-muted">{{ __('ABA / Fedwire Routing Number (9 Digits)') }} <span class="text-danger">*</span></label>
                            <input type="text" inputmode="numeric" class="form-control border rounded-2 py-2" name="routing_number" id="wireRoutingNumber" maxlength="9" placeholder="{{ __('9 digits routing number') }}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); performRoutingLookup(this.value)">
                            <div id="routingLookupStatus" class="extra-small mt-1"></div>
                            <div class="routing-lookup-verified d-none mt-2 p-2 rounded-2" id="routingLookupCard">
                                <span class="routing-lookup-verified__icon" aria-hidden="true"><i class="fas fa-university"></i></span>
                                <div>
                                    <div class="fw-bold text-dark small" id="lookupBankName"></div>
                                    <div class="extra-small routing-lookup-verified__sub" id="lookupBankState">{{ __('Receiving financial institution (verified)') }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- International SWIFT/BIC Input --}}
                        <div class="col-md-6 d-none" id="fieldInternationalSwift">
                            <label class="form-label extra-small text-uppercase fw-bold text-muted">{{ __('SWIFT / BIC Code (8-11 Characters)') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border rounded-2 py-2 text-uppercase" name="swift_code" id="wireSwiftCode" maxlength="11" placeholder="{{ __('e.g., CHASUS33XXX') }}">
                        </div>

                        {{-- Account / IBAN Number --}}
                        <div class="col-md-6">
                            <label class="form-label extra-small text-uppercase fw-bold text-muted" id="accountNumberFieldLabel">{{ __('Beneficiary Account Number') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border rounded-2 py-2" name="account_number" id="wireAccountNumber" required placeholder="{{ __('Enter recipient account number') }}">
                        </div>

                        {{-- International Country Selector --}}
                        <div class="col-md-6 d-none" id="fieldInternationalCountry">
                            <label class="form-label extra-small text-uppercase fw-bold text-muted">{{ __('Beneficiary Bank Country') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border rounded-2 py-2" name="country" id="wireCountry" placeholder="{{ __('e.g., United Kingdom, Germany, Japan') }}">
                        </div>

                        {{-- International Intermediary Bank --}}
                        <div class="col-12 d-none" id="fieldInternationalIntermediary">
                            <label class="form-label extra-small text-uppercase fw-bold text-muted">{{ __('Intermediary / Correspondent Bank (Optional)') }}</label>
                            <input type="text" class="form-control border rounded-2 py-2" name="intermediary_bank" id="wireIntermediaryBank" placeholder="{{ __('e.g., Bank of New York Mellon (SWIFT: IRVTUS3N)') }}">
                        </div>

                        {{-- Payment Memo / Reference --}}
                        <div class="col-12">
                            <label class="form-label extra-small text-uppercase fw-bold text-muted">{{ __('Payment Reference / Memo (Optional)') }}</label>
                            <input type="text" class="form-control border rounded-2 py-2" name="memo" id="wireMemo" placeholder="{{ __('e.g., Invoice #1042 / Escrow Account Deposit') }}">
                        </div>

                        {{-- Dynamic Custom Admin Fields (Filtered for genuine custom additions only) --}}
                        @if(!empty($fields) && is_array($fields))
                            @foreach ($fields as $key => $field)
                                <div class="{{ ($field['type'] ?? '') == 'textarea' ? 'col-12' : 'col-md-6' }}">
                                    <label class="form-label extra-small text-uppercase fw-bold text-muted">
                                        {{ $field['name'] }}
                                        @if (($field['validation'] ?? '') == 'required') <span class="text-danger">*</span> @endif
                                    </label>
                                    
                                    @if (($field['type'] ?? '') == 'file')
                                        <div class="border border-dashed rounded-2 p-2 text-center bg-light">
                                            <input type="file" name="data[{{ $field['name'] }}]" class="form-control form-control-sm" @if (($field['validation'] ?? '') == 'required') required @endif>
                                            <div class="extra-small text-muted mt-1">{{ __('Accepted formats: PDF, JPG, PNG') }}</div>
                                        </div>
                                    @elseif(($field['type'] ?? '') == 'textarea')
                                        <textarea class="form-control border rounded-2" name="data[{{ $field['name'] }}]" rows="2" @if (($field['validation'] ?? '') == 'required') required @endif placeholder="Enter {{ strtolower($field['name']) }}..."></textarea>
                                    @else
                                        <input type="text" class="form-control border rounded-2 py-2" name="data[{{ $field['name'] }}]" @if (($field['validation'] ?? '') == 'required') required @endif placeholder="Enter {{ strtolower($field['name']) }}...">
                                    @endif
                                </div>
                            @endforeach
                        @endif

                        <div id="step2Error" class="col-12 text-danger small d-none"></div>
                    </div>

                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-light border rounded-pill px-4 py-2 fw-semibold text-muted" onclick="goToStep(1)">
                            <i class="fas fa-arrow-left me-1"></i> {{ __('Back: Setup') }}
                        </button>
                        <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" onclick="validateAndGoToStep3()">
                            {{ __('Next: Review & Authorize') }} <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- ========================================================================= --}}
                {{-- STEP 3: REVIEW, FINANCIAL DISCLOSURE & AUTHORIZATION --}}
                {{-- ========================================================================= --}}
                <div class="wizard-pane p-4 p-md-4 d-none" id="wireStep3">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                        <i class="fas fa-clipboard-check text-primary me-2"></i> {{ __('Step 3: Review & Authorize Settlement') }}
                    </h6>

                    <div class="p-3 rounded-3 border bg-light bg-opacity-75 mb-3">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <span class="text-muted extra-small text-uppercase d-block">{{ __('Funding Source') }}</span>
                                <strong class="text-dark small" id="revSourceAccount">-</strong>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <span class="text-muted extra-small text-uppercase d-block">{{ __('Wire Classification') }}</span>
                                <span class="badge bg-primary px-2 py-1 rounded-pill small" id="revWireType">Domestic Fedwire</span>
                            </div>

                            <div class="col-12"><hr class="my-1"></div>

                            <div class="col-sm-6">
                                <span class="text-muted extra-small text-uppercase d-block">{{ __('Beneficiary Entity') }}</span>
                                <strong class="text-dark small" id="revBeneficiaryName">-</strong>
                                <div class="text-muted extra-small" id="revBeneficiaryAddress"></div>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <span class="text-muted extra-small text-uppercase d-block">{{ __('Receiving Institution') }}</span>
                                <strong class="text-dark small" id="revBankName">-</strong>
                                <div class="text-muted extra-small" id="revRoutingSwift"></div>
                            </div>

                            <div class="col-12"><hr class="my-1"></div>

                            <div class="col-sm-6">
                                <span class="text-muted extra-small text-uppercase d-block">{{ __('Beneficiary Account / IBAN') }}</span>
                                <span class="font-monospace fw-bold text-dark small" id="revAccountNumber">-</span>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <span class="text-muted extra-small text-uppercase d-block">{{ __('Transfer Principal') }}</span>
                                <span class="fw-bold text-dark small" id="revPrincipalAmount">{{ $currencySymbol }}0.00</span>
                            </div>

                            <div class="col-12"><hr class="my-1"></div>

                            <div class="col-sm-6">
                                <span class="text-muted extra-small text-uppercase d-block">{{ __('Wire Settlement & Processing Fee') }}</span>
                                <span class="fw-bold text-primary small" id="revWireFee">{{ $currencySymbol }}0.00</span>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <span class="text-muted extra-small text-uppercase d-block">{{ __('Total Settlement Debit') }}</span>
                                <span class="fw-bold text-dark fs-5" id="revTotalDebit">{{ $currencySymbol }}0.00</span>
                            </div>
                        </div>
                    </div>

                    {{-- Professional Financial Settlement Notice --}}
                    <div class="alert alert-info border-0 rounded-2 extra-small d-flex align-items-start gap-2 mb-3 p-3">
                        <i class="fas fa-shield-alt fs-6 text-primary mt-1"></i>
                        <div>
                            <strong>{{ __('Settlement Policy Notice:') }}</strong><br>
                            {{ __('Funds (transfer principal and applicable wire processing fees) are debited immediately upon order authorization and placed into a pending settlement hold until clearing and final funds dispatch.') }}
                        </div>
                    </div>

                    {{-- Mandatory Authorization Checkbox --}}
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="authCheckbox" required>
                        <label class="form-check-label extra-small text-muted" for="authCheckbox">
                            {{ __('I certify that I am authorized to initiate this wire transfer and that the recipient bank and routing details provided above are verified and accurate. I understand that wire transfers are irrevocable once dispatched.') }}
                        </label>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <button type="button" class="btn btn-light border rounded-pill px-4 py-2 fw-semibold text-muted" onclick="goToStep(2)">
                            <i class="fas fa-arrow-left me-1"></i> {{ __('Back: Edit') }}
                        </button>
                        <button type="submit" 
                                id="btnSubmitWire"
                                onclick="event.preventDefault(); submitWireTransferWithSecurity();"
                                class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-xs">
                            <i class="fas fa-paper-plane me-1"></i> {{ __('Authorize & Submit Wire') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Professional Regulatory & Wire Instructions Notice --}}
        <div class="card border-0 bg-white shadow-xs rounded-3 p-3 mb-5">
            <h6 class="fw-bold text-dark mb-2 small">
                <i class="fas fa-info-circle text-primary me-1"></i> {{ __('Wire Transfer Terms & Cutoff Times') }}
            </h6>
            <div class="extra-small text-muted">
                @if(!empty($data?->instructions) && !str_contains($data->instructions, 'retrieved the existing JSON data'))
                    {!! $data->instructions !!}
                @else
                    <p class="mb-1"><strong>{{ __('Wire Transfer Processing & Cutoff Times:') }}</strong></p>
                    <ul class="mb-0 ps-3">
                        <li><strong>{{ __('Domestic Fedwire:') }}</strong> {{ __('Outgoing domestic wire instructions submitted and authorized prior to 3:00 PM EST on business days are processed on the same business day. Orders received after cutoff will process the following business day.') }}</li>
                        <li><strong>{{ __('International SWIFT:') }}</strong> {{ __('Outgoing international wire transfers are subject to intermediary correspondent banking clearing times (typically 1–3 business days).') }}</li>
                        <li><strong>{{ __('Final Settlement:') }}</strong> {{ __('Wire transfers are irrevocable once dispatched across the Federal Reserve Fedwire or SWIFT settlement networks. Please ensure all beneficiary and routing details are accurate before authorization.') }}</li>
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Limit and Policy Modal placed cleanly at page root to avoid backdrop overlay issues --}}
@include('frontend::fund_transfer.include.__limitition')

<style>
    .wizard-step-indicator {
        opacity: 0.5;
        transition: all 0.2s ease;
    }
    .wizard-step-indicator.active {
        opacity: 1;
    }
    .wizard-step-indicator .step-num {
        width: 28px;
        height: 28px;
        background-color: #e9ecef;
        color: #6c757d;
        font-size: 13px;
        border-radius: 50%;
    }
    .wizard-step-indicator.active .step-num {
        background-color: #5d78ff;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(93, 120, 255, 0.3);
    }
    .wizard-step-line {
        height: 2px;
        background-color: #dee2e6;
    }
    .wire-type-card {
        border-color: #dee2e6;
        cursor: pointer;
        background-color: #ffffff;
    }
    .wire-type-card:hover {
        border-color: #5d78ff;
        background-color: #f8faff;
    }
    .wire-type-card.active {
        border-color: #5d78ff !important;
        background-color: #f0f4ff !important;
        box-shadow: 0 2px 8px rgba(93, 120, 255, 0.15);
    }
    .extra-small {
        font-size: 0.78rem;
    }
    .shadow-xs {
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    /* Verified receiving institution (routing lookup) */
    .routing-lookup-verified {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
    }
    .routing-lookup-verified__icon {
        width: 1.9rem;
        height: 1.9rem;
        border-radius: 50%;
        background: #10b981;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.85rem;
    }
    .routing-lookup-verified__sub { 
        color: #047857; 
        font-weight: 600; 
    }
    /* Fixed Modal Stacking Context to guarantee no grey overlay on modal */
    body > #limitBox {
        z-index: 99999 !important;
        position: fixed !important;
    }
    .modal-backdrop {
        z-index: 99990 !important;
    }
</style>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const limitModal = document.getElementById('limitBox');
        if (limitModal && limitModal.parentElement !== document.body) {
            document.body.appendChild(limitModal);
        }
    });

    const domesticFee = {{ (float)($data?->charge ?? 25.00) }};
    const domesticFeeType = '{{ $data?->charge_type ?? "fixed" }}';
    const intlFee = {{ (float)($data?->international_charge ?? 45.00) }};
    const intlFeeType = '{{ $data?->international_charge_type ?? "fixed" }}';
    const currencySymbol = '{{ $currencySymbol }}';
    const minLimit = {{ (float)$user->getEffectiveWireMinLimit($data?->minimum_transfer ?? 50.00) }};
    const maxLimit = {{ (float)$user->getEffectiveWireMaxLimit($data?->maximum_transfer ?? 500000.00) }};

    let currentWireType = 'domestic';

    function handleWireTypeChange(type) {
        currentWireType = type;
        const domCard = document.getElementById('cardDomestic');
        const intlCard = document.getElementById('cardInternational');
        const domRoutingField = document.getElementById('fieldDomesticRouting');
        const intlSwiftField = document.getElementById('fieldInternationalSwift');
        const intlCountryField = document.getElementById('fieldInternationalCountry');
        const intlIntermediaryField = document.getElementById('fieldInternationalIntermediary');
        const accNumberLabel = document.getElementById('accountNumberFieldLabel');
        const routingInput = document.getElementById('wireRoutingNumber');
        const swiftInput = document.getElementById('wireSwiftCode');
        const countryInput = document.getElementById('wireCountry');
        const badge = document.getElementById('step1TypeBadge');

        if (type === 'domestic') {
            domCard.classList.add('active');
            intlCard.classList.remove('active');
            domRoutingField.classList.remove('d-none');
            intlSwiftField.classList.add('d-none');
            intlCountryField.classList.add('d-none');
            intlIntermediaryField.classList.add('d-none');
            accNumberLabel.innerHTML = '{{ __("Beneficiary Account Number") }} <span class="text-danger">*</span>';
            routingInput.setAttribute('required', 'required');
            swiftInput.removeAttribute('required');
            countryInput.removeAttribute('required');
            badge.innerText = '{{ __("Domestic Fedwire") }}';
        } else {
            intlCard.classList.add('active');
            domCard.classList.remove('active');
            domRoutingField.classList.add('d-none');
            intlSwiftField.classList.remove('d-none');
            intlCountryField.classList.remove('d-none');
            intlIntermediaryField.classList.remove('d-none');
            accNumberLabel.innerHTML = '{{ __("Beneficiary IBAN / Account Number") }} <span class="text-danger">*</span>';
            routingInput.removeAttribute('required');
            swiftInput.setAttribute('required', 'required');
            countryInput.setAttribute('required', 'required');
            badge.innerText = '{{ __("International SWIFT") }}';
        }
        updateSummaryCalculations();
    }

    function calculateCurrentFee(amountVal) {
        if (currentWireType === 'domestic') {
            return domesticFeeType === 'percentage' ? ((domesticFee / 100) * amountVal) : domesticFee;
        } else {
            return intlFeeType === 'percentage' ? ((intlFee / 100) * amountVal) : intlFee;
        }
    }

    function updateSummaryCalculations() {
        const amountVal = parseFloat(document.getElementById('wireAmountInput').value) || 0;
        const fee = calculateCurrentFee(amountVal);
        const total = amountVal + fee;

        document.getElementById('step1TotalDebit').innerText = currencySymbol + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('step1FeeBreakdown').innerText = '(' + '{{ __("Includes Wire Fee:") }} ' + currencySymbol + fee.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')';
    }

    function goToStep(stepNum) {
        document.getElementById('wireStep1').classList.add('d-none');
        document.getElementById('wireStep2').classList.add('d-none');
        document.getElementById('wireStep3').classList.add('d-none');

        document.getElementById('stepIndicator1').classList.remove('active');
        document.getElementById('stepIndicator2').classList.remove('active');
        document.getElementById('stepIndicator3').classList.remove('active');

        if (stepNum === 1) {
            document.getElementById('wireStep1').classList.remove('d-none');
            document.getElementById('stepIndicator1').classList.add('active');
        } else if (stepNum === 2) {
            document.getElementById('wireStep2').classList.remove('d-none');
            document.getElementById('stepIndicator1').classList.add('active');
            document.getElementById('stepIndicator2').classList.add('active');
        } else if (stepNum === 3) {
            document.getElementById('wireStep3').classList.remove('d-none');
            document.getElementById('stepIndicator1').classList.add('active');
            document.getElementById('stepIndicator2').classList.add('active');
            document.getElementById('stepIndicator3').classList.add('active');
            populateReviewStep();
        }
        window.scrollTo({ top: 120, behavior: 'smooth' });
    }

    function validateAndGoToStep2() {
        const amountVal = parseFloat(document.getElementById('wireAmountInput').value) || 0;
        const errDiv = document.getElementById('step1Error');
        errDiv.classList.add('d-none');

        const walletSelect = document.getElementById('wireWalletSelect');
        const selectedOpt = walletSelect.options[walletSelect.selectedIndex];
        const availBalance = parseFloat(selectedOpt.getAttribute('data-balance')) || 0;
        const fee = calculateCurrentFee(amountVal);
        const total = amountVal + fee;

        if (amountVal <= 0) {
            errDiv.innerText = '{{ __("Please enter a valid wire transfer amount.") }}';
            errDiv.classList.remove('d-none');
            return;
        }

        if (minLimit > 0 && amountVal < minLimit) {
            errDiv.innerText = '{{ __("Minimum wire transfer amount is ") }}' + currencySymbol + minLimit.toFixed(2);
            errDiv.classList.remove('d-none');
            return;
        }

        if (maxLimit > 0 && amountVal > maxLimit) {
            errDiv.innerText = '{{ __("Maximum wire transfer amount is ") }}' + currencySymbol + maxLimit.toFixed(2);
            errDiv.classList.remove('d-none');
            return;
        }

        if (total > availBalance) {
            errDiv.innerText = '{{ __("Insufficient funds. Total debit of ") }}' + currencySymbol + total.toFixed(2) + '{{ __(" exceeds available balance of ") }}' + currencySymbol + availBalance.toFixed(2);
            errDiv.classList.remove('d-none');
            return;
        }

        goToStep(2);
    }

    function validateAndGoToStep3() {
        const errDiv = document.getElementById('step2Error');
        errDiv.classList.add('d-none');

        const beneficiaryName = document.getElementById('wireBeneficiaryName').value.trim();
        const bankName = document.getElementById('wireBankName').value.trim();
        const accNumber = document.getElementById('wireAccountNumber').value.trim();

        if (!beneficiaryName) {
            errDiv.innerText = '{{ __("Please provide the beneficiary full name or business entity name.") }}';
            errDiv.classList.remove('d-none');
            return;
        }

        if (!bankName) {
            errDiv.innerText = '{{ __("Please enter the receiving bank name.") }}';
            errDiv.classList.remove('d-none');
            return;
        }

        if (currentWireType === 'domestic') {
            const routing = document.getElementById('wireRoutingNumber').value.trim();
            if (!/^\d{9}$/.test(routing)) {
                errDiv.innerText = '{{ __("Please enter a valid 9-digit ABA Fedwire routing number.") }}';
                errDiv.classList.remove('d-none');
                return;
            }
        } else {
            const swift = document.getElementById('wireSwiftCode').value.trim();
            const country = document.getElementById('wireCountry').value.trim();
            if (swift.length < 8 || swift.length > 11) {
                errDiv.innerText = '{{ __("Please enter a valid 8-11 character SWIFT/BIC code.") }}';
                errDiv.classList.remove('d-none');
                return;
            }
            if (!country) {
                errDiv.innerText = '{{ __("Please specify the beneficiary bank country.") }}';
                errDiv.classList.remove('d-none');
                return;
            }
        }

        if (!accNumber) {
            errDiv.innerText = '{{ __("Please enter the recipient account number or IBAN.") }}';
            errDiv.classList.remove('d-none');
            return;
        }

        goToStep(3);
    }

    function populateReviewStep() {
        const walletSelect = document.getElementById('wireWalletSelect');
        const selectedOpt = walletSelect.options[walletSelect.selectedIndex];
        const amountVal = parseFloat(document.getElementById('wireAmountInput').value) || 0;
        const fee = calculateCurrentFee(amountVal);
        const total = amountVal + fee;

        document.getElementById('revSourceAccount').innerText = selectedOpt.getAttribute('data-name') + ' (... ' + selectedOpt.getAttribute('data-number').slice(-4) + ')';
        document.getElementById('revWireType').innerText = currentWireType === 'domestic' ? '{{ __("Domestic Fedwire") }}' : '{{ __("International SWIFT") }}';
        document.getElementById('revBeneficiaryName').innerText = document.getElementById('wireBeneficiaryName').value.trim();
        document.getElementById('revBeneficiaryAddress').innerText = document.getElementById('wireBeneficiaryAddress').value.trim() || '';
        document.getElementById('revBankName').innerText = document.getElementById('wireBankName').value.trim();

        if (currentWireType === 'domestic') {
            document.getElementById('revRoutingSwift').innerText = 'ABA: ' + document.getElementById('wireRoutingNumber').value.trim();
        } else {
            document.getElementById('revRoutingSwift').innerText = 'SWIFT: ' + document.getElementById('wireSwiftCode').value.trim().toUpperCase() + ' (' + document.getElementById('wireCountry').value.trim() + ')';
        }

        document.getElementById('revAccountNumber').innerText = document.getElementById('wireAccountNumber').value.trim();
        document.getElementById('revPrincipalAmount').innerText = currencySymbol + amountVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('revWireFee').innerText = currencySymbol + fee.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('revTotalDebit').innerText = currencySymbol + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    let lookupTimer = null;
    function performRoutingLookup(routing) {
        const cleanRouting = routing.trim().replace(/\D/g, '');
        const statusEl = document.getElementById('routingLookupStatus');
        const cardEl = document.getElementById('routingLookupCard');
        const bankNameEl = document.getElementById('lookupBankName');
        const bankInput = document.getElementById('wireBankName');

        cardEl.classList.add('d-none');
        statusEl.innerHTML = '';

        if (cleanRouting.length !== 9) return;

        statusEl.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i> {{ __("Verifying ABA routing number…") }}</span>';

        if (lookupTimer) clearTimeout(lookupTimer);
        lookupTimer = setTimeout(() => {
            fetch("{{ route('user.fund_transfer.lookup-routing') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ routing_number: cleanRouting })
            })
            .then(async response => {
                const text = await response.text();
                let body = {};
                try { body = text ? JSON.parse(text) : {}; } catch (e) { body = {}; }
                return { ok: response.ok, status: response.status, body };
            })
            .then(({ok, status, body}) => {
                if (ok && body.status === 'verified') {
                    bankNameEl.innerText = body.bank_name;
                    cardEl.classList.remove('d-none');
                    statusEl.innerHTML = '<span class="text-success extra-small fw-semibold"><i class="fas fa-check-circle me-1"></i> {{ __("Receiving institution verified.") }}</span>';
                    if (bankInput) {
                        bankInput.value = body.bank_name;
                    }
                } else if (body.status === 'manual_required') {
                    statusEl.innerHTML = '<span class="text-warning extra-small">' + (body.message || '{{ __("Enter the receiving institution\'s name manually.") }}') + '</span>';
                } else {
                    statusEl.innerHTML = '<span class="text-muted extra-small"><i class="fas fa-info-circle me-1"></i> {{ __("Valid 9-digit ABA format") }}</span>';
                }
            })
            .catch(() => {
                statusEl.innerHTML = '<span class="text-muted extra-small"><i class="fas fa-info-circle me-1"></i> {{ __("Valid 9-digit ABA format") }}</span>';
            });
        }, 200);
    }

    function submitWireTransferWithSecurity() {
        const authCb = document.getElementById('authCheckbox');
        if (!authCb.checked) {
            alert('{{ __("Please acknowledge the settlement and wire authorization terms to continue.") }}');
            return;
        }
        SecurityGate.gate(document.getElementById('wireForm'));
    }
</script>
@endsection
