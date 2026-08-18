@extends('frontend::layouts.user')

@section('title')
    {{ __('Wire Transfer') }}
@endsection

@section('content')
<div class="row justify-content-center">
    @include('frontend::fund_transfer.include.__header')

    <div class="col-xl-9 col-lg-11 col-12 mt-4">
        {{-- Page Header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-1 text-dark">{{ __('Domestic & International Wire Transfer') }}</h2>
                <p class="text-muted mb-0 small">{{ __('Send high-value, secure funds externally via Fedwire or global SWIFT network.') }}</p>
            </div>
            <div>
                <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#limitBox">
                    <i class="fas fa-sliders-h me-1"></i> {{ __('View Limits & Fees') }}
                </button>
            </div>
        </div>

        {{-- Main Transfer Form Card --}}
        <div class="site-card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <form action="{{ route('user.fund_transfer.transfer.wire.post') }}" method="POST" id="wireForm" enctype="multipart/form-data">
                @csrf
                <div class="site-card-body p-4 p-md-5">
                    
                    {{-- 1. Wire Type Switcher --}}
                    <div class="mb-4 pb-3 border-bottom">
                        <label class="form-label small text-uppercase fw-bold text-muted mb-3 d-block">{{ __('Select Wire Transfer Type') }}</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="wire-type-option p-3 border rounded-3 d-flex align-items-center cursor-pointer h-100 position-relative transition-all active" id="optionDomestic">
                                    <input type="radio" name="wire_type" value="domestic" class="form-check-input me-3 mt-0" checked onchange="toggleWireType('domestic')">
                                    <div>
                                        <div class="fw-bold text-dark mb-0">{{ __('Domestic Wire (Fedwire)') }}</div>
                                        <div class="text-muted extra-small">{{ __('Same-day delivery to US banks via ABA Routing.') }}</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="wire-type-option p-3 border rounded-3 d-flex align-items-center cursor-pointer h-100 position-relative transition-all" id="optionInternational">
                                    <input type="radio" name="wire_type" value="international" class="form-check-input me-3 mt-0" onchange="toggleWireType('international')">
                                    <div>
                                        <div class="fw-bold text-dark mb-0">{{ __('International Wire (SWIFT)') }}</div>
                                        <div class="text-muted extra-small">{{ __('Cross-border wire via SWIFT/BIC & IBAN code.') }}</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        {{-- 2. Source Account Selector --}}
                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">{{ __('From Account') }} <span class="text-danger">*</span></label>
                            <select name="wallet_type" class="form-select form-select-lg border-2 shadow-none rounded-3" id="wireWalletSelect" onchange="updateSummary()" required>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc['id'] }}" 
                                            data-balance="{{ $acc['balance'] }}" 
                                            @disabled($acc['is_restricted'])>
                                        {{ $acc['name'] }} (...{{ substr($acc['account_number'], -4) }}) - {{ $currencySymbol }}{{ number_format($acc['balance'], 2) }}
                                        @if($acc['is_restricted']) (Restricted) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 3. Wire Amount --}}
                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">{{ __('Wire Amount') }} <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg shadow-xs rounded-3 overflow-hidden border border-2">
                                <span class="input-group-text bg-light border-0 fw-bold text-muted">{{ $currencySymbol }}</span>
                                <input type="number" step="0.01" class="form-control border-0 fw-bold" name="amount" id="wireAmountInput" required placeholder="0.00" oninput="updateSummary()">
                            </div>
                            <div class="d-flex justify-content-between extra-small text-muted mt-1 px-1">
                                <span>{{ __('Min:') }} {{ $currencySymbol }}{{ number_format($user->getEffectiveWireMinLimit($data?->minimum_transfer ?? 0), 2) }}</span>
                                <span>{{ __('Max:') }} {{ $currencySymbol }}{{ number_format($user->getEffectiveWireMaxLimit($data?->maximum_transfer ?? 500000), 2) }}</span>
                            </div>
                        </div>

                        {{-- Section Divider: Beneficiary Details --}}
                        <div class="col-12 mt-4 pt-2">
                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                                <i class="fas fa-user-circle me-1"></i> {{ __('Beneficiary Information') }}
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">{{ __('Beneficiary Full Name / Business Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg border-2 rounded-3" name="beneficiary_name" required placeholder="{{ __('e.g., Apex Global Escrow LLC') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">{{ __('Beneficiary Physical Street Address') }}</label>
                            <input type="text" class="form-control form-control-lg border-2 rounded-3" name="beneficiary_address" placeholder="{{ __('e.g., 100 Main St, Suite 400, New York, NY') }}">
                        </div>

                        {{-- Section Divider: Receiving Bank Details --}}
                        <div class="col-12 mt-4 pt-2">
                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                                <i class="fas fa-university me-1"></i> {{ __('Receiving Bank Information') }}
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">{{ __('Receiving Bank Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg border-2 rounded-3" name="bank_name" id="wireBankName" required placeholder="{{ __('e.g., JPMorgan Chase Bank, N.A.') }}">
                        </div>

                        {{-- Domestic ABA Routing Input --}}
                        <div class="col-md-6" id="domesticRoutingCol">
                            <label class="form-label small text-uppercase fw-bold text-muted">{{ __('ABA / Fedwire Routing Number (9 Digits)') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg border-2 rounded-3" name="routing_number" id="wireRoutingNumber" maxlength="9" placeholder="{{ __('e.g., 021000021') }}" oninput="lookupWireRouting(this.value)">
                            <div id="routingFeedback" class="extra-small mt-1 text-muted"></div>
                        </div>

                        {{-- International SWIFT/BIC Input --}}
                        <div class="col-md-6 d-none" id="internationalSwiftCol">
                            <label class="form-label small text-uppercase fw-bold text-muted">{{ __('SWIFT / BIC Code (8-11 Characters)') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg border-2 rounded-3 text-uppercase" name="swift_code" id="wireSwiftCode" maxlength="11" placeholder="{{ __('e.g., CHASUS33XXX') }}">
                        </div>

                        {{-- Account / IBAN Number --}}
                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted" id="accountNumberLabel">{{ __('Account Number') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg border-2 rounded-3" name="account_number" required placeholder="{{ __('Enter recipient account or IBAN number') }}">
                        </div>

                        {{-- International Country Selector --}}
                        <div class="col-md-6 d-none" id="internationalCountryCol">
                            <label class="form-label small text-uppercase fw-bold text-muted">{{ __('Beneficiary Bank Country') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg border-2 rounded-3" name="country" id="wireCountry" placeholder="{{ __('e.g., United Kingdom, Germany, Japan') }}">
                        </div>

                        {{-- International Intermediary Bank --}}
                        <div class="col-12 d-none" id="internationalIntermediaryCol">
                            <label class="form-label small text-uppercase fw-bold text-muted">{{ __('Intermediary / Correspondent Bank (Optional)') }}</label>
                            <input type="text" class="form-control form-control-lg border-2 rounded-3" name="intermediary_bank" placeholder="{{ __('e.g., Bank of New York Mellon (SWIFT: IRVTUS3N)') }}">
                        </div>

                        {{-- Memo / Reference --}}
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">{{ __('Payment Reference / Memo (Optional)') }}</label>
                            <input type="text" class="form-control form-control-lg border-2 rounded-3" name="memo" placeholder="{{ __('e.g., Invoice #1042 / Escrow Account Deposit') }}">
                        </div>

                        {{-- 4. Dynamic Admin Fields --}}
                        @if(!empty($fields) && is_array($fields))
                            @foreach ($fields as $key => $field)
                                <div class="{{ ($field['type'] ?? '') == 'textarea' ? 'col-12' : 'col-md-6' }}">
                                    <label class="form-label small text-uppercase fw-bold text-muted">
                                        {{ $field['name'] }}
                                        @if (($field['validation'] ?? '') == 'required') <span class="text-danger">*</span> @endif
                                    </label>
                                    
                                    @if (($field['type'] ?? '') == 'file')
                                        <div class="border-2 border-dashed rounded-3 p-3 text-center bg-light position-relative">
                                            <input type="file" name="data[{{ $field['name'] }}]" class="form-control" @if (($field['validation'] ?? '') == 'required') required @endif>
                                            <div class="extra-small text-muted mt-1">{{ __('Accepted formats: PDF, JPG, PNG') }}</div>
                                        </div>
                                    @elseif(($field['type'] ?? '') == 'textarea')
                                        <textarea class="form-control border-2 rounded-3" name="data[{{ $field['name'] }}]" rows="3" @if (($field['validation'] ?? '') == 'required') required @endif placeholder="Enter {{ strtolower($field['name']) }}..."></textarea>
                                    @else
                                        <input type="text" class="form-control form-control-lg border-2 rounded-3" name="data[{{ $field['name'] }}]" @if (($field['validation'] ?? '') == 'required') required @endif placeholder="Enter {{ strtolower($field['name']) }}...">
                                    @endif
                                </div>
                            @endforeach
                        @endif

                        {{-- 5. Real-Time Fee & Summary Card --}}
                        <div class="col-12 mt-4">
                            <div class="p-4 rounded-3 border bg-light bg-opacity-75">
                                <h6 class="fw-bold mb-3 text-dark d-flex justify-content-between align-items-center">
                                    <span>{{ __('Wire Transfer Summary') }}</span>
                                    <span class="badge bg-primary px-3 py-2 rounded-pill font-monospace" id="summaryWireTypeBadge">{{ __('Domestic Fedwire') }}</span>
                                </h6>
                                <div class="row g-2">
                                    <div class="col-6 text-muted small">{{ __('Transfer Amount:') }}</div>
                                    <div class="col-6 text-end fw-bold text-dark" id="summaryAmount">{{ $currencySymbol }}0.00</div>

                                    <div class="col-6 text-muted small">{{ __('Wire Processing Fee:') }}</div>
                                    <div class="col-6 text-end fw-bold text-primary" id="summaryFee">{{ $currencySymbol }}{{ number_format($data?->charge ?? 25.00, 2) }}</div>

                                    <div class="col-12"><hr class="my-2"></div>

                                    <div class="col-6 fw-bold text-dark">{{ __('Total Amount to Debit:') }}</div>
                                    <div class="col-6 text-end fw-bolder text-dark fs-5" id="summaryTotal">{{ $currencySymbol }}{{ number_format($data?->charge ?? 25.00, 2) }}</div>
                                </div>
                                <div class="mt-3 extra-small text-muted text-center">
                                    <i class="fas fa-shield-alt text-success me-1"></i> {{ __('Funds will be debited upon submission with Pending status until processing completes.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Footer Submit Button --}}
                <div class="site-card-footer p-4 bg-light bg-opacity-50 text-center border-top">
                    <button type="submit" 
                            onclick="event.preventDefault(); SecurityGate.gate(document.getElementById('wireForm'));"
                            class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm btn-lg w-100 w-md-auto">
                        <i class="fas fa-paper-plane me-2"></i> {{ __('Authorize & Submit Wire Transfer') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Regulatory & Wire Instructions Notice --}}
        @if(!empty($data?->instructions))
            <div class="card border-0 bg-white shadow-xs rounded-4 p-4 mb-5">
                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-info-circle text-primary me-1"></i> {{ __('Wire Transfer Terms & Cutoff Times') }}</h6>
                <div class="small text-muted">
                    {!! $data->instructions !!}
                </div>
            </div>
        @endif
    </div>
</div>

@include('frontend::fund_transfer.include.__limitition')

<style>
    .wire-type-option {
        border-color: #dee2e6;
        cursor: pointer;
    }
    .wire-type-option:hover {
        border-color: #5d78ff;
        background-color: #f8faff;
    }
    .wire-type-option.active {
        border-color: #5d78ff !important;
        background-color: #f0f4ff !important;
        box-shadow: 0 2px 8px rgba(93, 120, 255, 0.15);
    }
    .extra-small {
        font-size: 0.75rem;
    }
    .shadow-xs {
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
</style>
@endsection

@section('script')
<script>
    const domesticFee = {{ (float)($data?->charge ?? 25.00) }};
    const domesticFeeType = '{{ $data?->charge_type ?? "fixed" }}';
    const intlFee = {{ (float)($data?->international_charge ?? 45.00) }};
    const intlFeeType = '{{ $data?->international_charge_type ?? "fixed" }}';
    const currencySymbol = '{{ $currencySymbol }}';

    let currentWireType = 'domestic';

    function toggleWireType(type) {
        currentWireType = type;
        const domOpt = document.getElementById('optionDomestic');
        const intlOpt = document.getElementById('optionInternational');
        const domCol = document.getElementById('domesticRoutingCol');
        const intlSwiftCol = document.getElementById('internationalSwiftCol');
        const intlCountryCol = document.getElementById('internationalCountryCol');
        const intlIntermediaryCol = document.getElementById('internationalIntermediaryCol');
        const accLabel = document.getElementById('accountNumberLabel');
        const routingInput = document.getElementById('wireRoutingNumber');
        const swiftInput = document.getElementById('wireSwiftCode');
        const countryInput = document.getElementById('wireCountry');
        const badge = document.getElementById('summaryWireTypeBadge');

        if (type === 'domestic') {
            domOpt.classList.add('active');
            intlOpt.classList.remove('active');
            domCol.classList.remove('d-none');
            intlSwiftCol.classList.add('d-none');
            intlCountryCol.classList.add('d-none');
            intlIntermediaryCol.classList.add('d-none');
            accLabel.innerHTML = '{{ __("Account Number") }} <span class="text-danger">*</span>';
            routingInput.setAttribute('required', 'required');
            swiftInput.removeAttribute('required');
            countryInput.removeAttribute('required');
            badge.innerText = '{{ __("Domestic Fedwire") }}';
        } else {
            intlOpt.classList.add('active');
            domOpt.classList.remove('active');
            domCol.classList.add('d-none');
            intlSwiftCol.classList.remove('d-none');
            intlCountryCol.classList.remove('d-none');
            intlIntermediaryCol.classList.remove('d-none');
            accLabel.innerHTML = '{{ __("IBAN / Account Number") }} <span class="text-danger">*</span>';
            routingInput.removeAttribute('required');
            swiftInput.setAttribute('required', 'required');
            countryInput.setAttribute('required', 'required');
            badge.innerText = '{{ __("International SWIFT") }}';
        }
        updateSummary();
    }

    function updateSummary() {
        const amountVal = parseFloat(document.getElementById('wireAmountInput').value) || 0;
        let fee = 0;

        if (currentWireType === 'domestic') {
            fee = domesticFeeType === 'percentage' ? ((domesticFee / 100) * amountVal) : domesticFee;
        } else {
            fee = intlFeeType === 'percentage' ? ((intlFee / 100) * amountVal) : intlFee;
        }

        const total = amountVal + fee;

        document.getElementById('summaryAmount').innerText = currencySymbol + amountVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('summaryFee').innerText = currencySymbol + fee.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('summaryTotal').innerText = currencySymbol + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function lookupWireRouting(routing) {
        if (routing.length === 9) {
            const feedback = document.getElementById('routingFeedback');
            feedback.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i> Verifying routing...</span>';
            
            fetch('{{ route("user.fund_transfer.lookup-routing") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ routing_number: routing })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status && data.bank_name) {
                    feedback.innerHTML = '<span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> ' + data.bank_name + '</span>';
                    const bankInput = document.getElementById('wireBankName');
                    if (!bankInput.value) {
                        bankInput.value = data.bank_name;
                    }
                } else {
                    feedback.innerHTML = '<span class="text-muted">' + (data.message || 'Routing verified') + '</span>';
                }
            })
            .catch(() => {
                feedback.innerHTML = '';
            });
        } else {
            document.getElementById('routingFeedback').innerHTML = '';
        }
    }
</script>
@endsection
