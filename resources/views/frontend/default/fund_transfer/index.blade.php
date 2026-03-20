@extends('frontend::layouts.user')

@section('title')
    {{ __('Transfer Funds') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10 col-12">
        <div class="text-center mb-4">
            <div class="d-flex align-items-center justify-content-center mb-2">
                <a href="{{ route('user.dashboard') }}" class="back-nav-link m-0 me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2 class="mb-0">Transfer Funds</h2>
            </div>
            <p class="text-muted small">Send money to other banks or between your accounts.</p>
        </div>

        <div class="banno-card p-0 mb-4 shadow-sm">
            <div class="wizard-header p-3 p-md-4 border-bottom bg-light">
                <div class="d-flex justify-content-between align-items-center flex-row">
                    <div class="step-indicator active d-flex align-items-center" data-step="1">
                        <div class="step-circle me-2">1</div>
                        <span class="d-none d-sm-inline">Type</span>
                    </div>
                    <div class="step-line flex-grow-1 mx-2 mx-md-3 bg-secondary opacity-25" style="height: 2px;"></div>
                    <div class="step-indicator d-flex align-items-center" data-step="2">
                        <div class="step-circle me-2">2</div>
                        <span class="d-none d-sm-inline">Details</span>
                    </div>
                    <div class="step-line flex-grow-1 mx-2 mx-md-3 bg-secondary opacity-25" style="height: 2px;"></div>
                    <div class="step-indicator d-flex align-items-center" data-step="3">
                        <div class="step-circle me-2">3</div>
                        <span class="d-none d-sm-inline">Review</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('user.fund_transfer.transfer') }}" method="POST" id="transferForm">
                @csrf
                <input type="hidden" name="charge_type" value="percentage">
                {{-- Hidden fields for backend: bank_id=0 for external ACH (manual entry), bank_name stored in manual_data --}}
                <input type="hidden" name="bank_id" id="hiddenBankId" value="0">
                <input type="hidden" name="manual_data[bank_name]" id="hiddenBankName" value="">
                <input type="hidden" name="transfer_type" id="hiddenTransferType" value="">
                
                <!-- Step 1: Transfer Type -->
                <div class="wizard-step active p-3 p-md-5" id="step1">
                    <h4 class="mb-4 text-center fw-bold">Who are you sending money to?</h4>
                    <div class="row g-3 g-md-4 justify-content-center">
                        <div class="col-12 col-md-4">
                            <div class="transfer-type-card js-type-select h-100 p-4 border rounded-4 text-center d-flex flex-column align-items-center cursor-pointer" data-type="self">
                                <input type="radio" name="transfer_type" value="self" class="d-none">
                                <div class="icon-circle bg-primary bg-opacity-10 text-primary mb-3 rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width: 64px; height: 64px;">
                                    <i class="fas fa-wallet fa-lg"></i>
                                </div>
                                <h6 class="mb-2 fw-bold">My Accounts</h6>
                                <p class="small text-muted mb-0">Transfer between checking and savings.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="transfer-type-card js-type-select h-100 p-4 border rounded-4 text-center d-flex flex-column align-items-center cursor-pointer" data-type="member">
                                <input type="radio" name="transfer_type" value="member" class="d-none">
                                <div class="icon-circle bg-success bg-opacity-10 text-success mb-3 rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width: 64px; height: 64px;">
                                    <i class="fas fa-users fa-lg"></i>
                                </div>
                                <h6 class="mb-2 fw-bold">Another Member</h6>
                                <p class="small text-muted mb-0">Send instantly to a credit union member.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="transfer-type-card js-type-select h-100 p-4 border rounded-4 text-center d-flex flex-column align-items-center cursor-pointer" data-type="external">
                                <input type="radio" name="transfer_type" value="external" class="d-none">
                                <div class="icon-circle bg-info bg-opacity-10 text-info mb-3 rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width: 64px; height: 64px;">
                                    <i class="fas fa-university fa-lg"></i>
                                </div>
                                <h6 class="mb-2 fw-bold">External Bank</h6>
                                <p class="small text-muted mb-0">Send via ACH or Wire to another bank.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Core Details -->
                <div class="wizard-step p-4 p-md-5 d-none" id="step2">
                    <div class="d-flex align-items-center mb-4 cursor-pointer text-primary" onclick="goToStep(1)">
                        <i class="fas fa-arrow-left me-2"></i> <span class="small fw-bold">Back to Type</span>
                    </div>

                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">From Account</label>
                            <select name="wallet_type" class="form-select form-select-lg border-2 shadow-none" id="walletSelect" onchange="updateAccountOptions()">
                                @if($wallets->isEmpty())
                                    <option value="default" data-type="checking" data-balance="{{ auth()->user()->balance }}">
                                        Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ setting('site_currency', 'global') . auth()->user()->balance }}
                                    </option>
                                @else
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->currency->code }}" data-type="checking" data-balance="{{ $wallet->balance }}">
                                            Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ $wallet->currency->symbol . $wallet->balance }}
                                        </option>
                                    @endforeach
                                @endif
                                <option value="primary_savings" data-type="savings" data-balance="{{ auth()->user()->savings_balance }}">
                                    Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S) - {{ setting('site_currency', 'global') }} {{ number_format(auth()->user()->savings_balance, 2) }}
                                </option>
                                @if(auth()->user()->heloc_status == 1)
                                <option value="heloc" data-type="heloc" data-balance="{{ auth()->user()->heloc_credit_limit - auth()->user()->heloc_balance }}">
                                    HELOC Account (...{{ substr(auth()->user()->heloc_account_number ?? auth()->user()->account_number, -4) }}H) - {{ setting('site_currency', 'global') }} {{ number_format(auth()->user()->heloc_credit_limit - auth()->user()->heloc_balance, 2) }} (Available)
                                </option>
                                @endif
                                @if(auth()->user()->cc_status == 1)
                                <option value="cc" data-type="cc" data-balance="{{ auth()->user()->cc_credit_limit - auth()->user()->cc_balance }}">
                                    Credit Card (...{{ substr(auth()->user()->cc_account_number ?? auth()->user()->account_number, -4) }}C) - {{ setting('site_currency', 'global') }} {{ number_format(auth()->user()->cc_credit_limit - auth()->user()->cc_balance, 2) }} (Available)
                                </option>
                                @endif

                            </select>
                        </div>

                        <div class="col-12" id="toRecipientSection">
                            <div class="dynamic-field d-none" id="field-self">
                                <label class="form-label small text-uppercase fw-bold text-muted">To Account</label>
                                <select name="to_wallet" class="form-select form-select-lg border-2 shadow-none" id="toWalletSelect"></select>
                            </div>

                            <div class="dynamic-field d-none" id="field-member">
                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small text-uppercase fw-bold text-muted mb-2">Recipient Info</label>
                                        <input type="text" name="member_identifier" id="member_identifier" class="form-control form-control-lg border-2 shadow-none" placeholder="Email or Account #">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small text-uppercase fw-bold text-muted mb-2">Member's Name</label>
                                        <div id="member_name_display_wrapper" class="form-control form-control-lg border-2 bg-light d-flex align-items-center" style="min-height: 50px; color: #64748b;">
                                            <span id="member_name_display_text">Enter info to verify...</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small text-uppercase fw-bold text-muted mb-2">Target Account</label>
                                        <select name="target_account_type" id="target_account_type" class="form-select form-select-lg border-2 shadow-none">
                                            <option value="checking" selected>Checking</option>
                                            <option value="savings" id="opt-savings" hidden disabled>Savings</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="dynamic-field d-none" id="field-external">
                                <label class="form-label small text-uppercase fw-bold text-muted">Recipient Bank – Routing Number</label>
                                <div class="input-group input-group-lg mb-2">
                                    <span class="input-group-text bg-white border-2 border-end-0">
                                        <i class="fas fa-university text-primary"></i>
                                    </span>
                                    <input type="text" id="routingNumberInput" inputmode="numeric"
                                        class="form-control border-2 border-start-0 shadow-none"
                                        placeholder="Enter 9-digit ABA routing number"
                                        maxlength="9" oninput="this.value=this.value.replace(/\D/g,''); handleRoutingInput(this.value)">
                                    <span class="input-group-text bg-white border-2 border-start-0" id="routingSpinner" style="display:none!important">
                                        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                                    </span>
                                </div>
                                {{-- Auto-discovery result --}}
                                <div id="bankNameResult" class="mb-3" style="display:none">
                                    <div class="d-flex align-items-center gap-2 p-3 rounded-3 border bg-light">
                                        <i class="fas fa-check-circle text-success" id="bankOkIcon" style="display:none"></i>
                                        <i class="fas fa-exclamation-triangle text-warning" id="bankWarnIcon" style="display:none"></i>
                                        <span id="bankNameText" class="fw-bold"></span>
                                    </div>
                                </div>
                                {{-- Manual fallback: shown when lookup fails or user wants to override --}}
                                <div id="manualBankSection" style="display:none">
                                    <label class="form-label small text-uppercase fw-bold text-muted">Bank Name <span class="text-danger">*</span> <small class="text-muted fw-normal">(Enter manually)</small></label>
                                    <input type="text" id="manualBankNameInput"
                                        class="form-control form-control-lg border-2 shadow-none"
                                        placeholder="e.g. Chase Bank, Bank of America..."
                                        oninput="syncBankName(this.value)">
                                </div>
                                <div class="mt-2">
                                    <a href="#" class="small text-primary" onclick="event.preventDefault(); toggleManualBank()" id="manualBankToggleLink">
                                        <i class="fas fa-keyboard me-1"></i> Enter bank name manually
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">Amount</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-2 border-end-0">$</span>
                                <input type="number" step="0.01" class="form-control border-2 border-start-0 shadow-none" id="amount" name="amount" placeholder="0.00" oninput="validateBalance()">
                            </div>
                            <div class="small mt-1" id="balanceFeedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">Frequency</label>
                            <select name="frequency" class="form-select form-select-lg border-2 shadow-none">
                                <option value="once">One-time Transfer</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="dateField">
                            <label class="form-label small text-uppercase fw-bold text-muted">Scheduled Date</label>
                            <input type="date" name="scheduled_at" class="form-control form-control-lg border-2 shadow-none" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Purpose / Memo</label>
                            <input type="text" name="purpose" id="transferPurpose" class="form-control form-control-lg border-2 shadow-none" placeholder="e.g. Personal, Shared Expense">
                        </div>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="button" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm fw-bold w-100 w-md-auto" onclick="validateStep2()">
                            Continue To Review <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Account Details (External Specific) -->
                <div class="wizard-step p-4 p-md-5 d-none" id="step3">
                    <div class="d-flex align-items-center mb-4 cursor-pointer text-primary" onclick="goToStep(2)">
                        <i class="fas fa-arrow-left me-2"></i> <span class="small fw-bold">Back to Details</span>
                    </div>

                    <h4 class="mb-4 fw-bold">Recipient Information</h4>
                    <div class="row g-4">
                        {{-- Read-only routing number (carried from Step 2) --}}
                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">Routing Number</label>
                            <input type="text" id="step3RoutingDisplay"
                                class="form-control form-control-lg border-2 shadow-none bg-light"
                                placeholder="Recipient's Routing Number" readonly>
                            {{-- The actual POSTed value --}}
                            <input type="hidden" name="manual_data[routing_number]" id="hiddenRoutingNumber">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">Recipient's Bank Name</label>
                            <input type="text" id="step3BankDisplay"
                                class="form-control form-control-lg border-2 shadow-none bg-light"
                                placeholder="Recipient's Bank Name" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Recipient's Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="manual_data[account_name]" id="extAccountName"
                                class="form-control form-control-lg border-2 shadow-none"
                                placeholder="Recipient's legal name as it appears on their account">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">Recipient's Account Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="manual_data[account_number]" id="ext_acc_num"
                                    class="form-control form-control-lg border-2 shadow-sm"
                                    placeholder="4-20 digits" minlength="4" maxlength="20"
                                    oninput="this.value=this.value.replace(/\D/g,'')">
                                <button class="btn btn-outline-secondary border-2 border-start-0 bg-white"
                                    type="button" onclick="toggleVisibility(this)"><i class="fas fa-eye-slash"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">Confirm Account Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" id="ext_acc_num_confirm"
                                    class="form-control form-control-lg border-2 shadow-sm"
                                    placeholder="Re-enter to confirm">
                                <button class="btn btn-outline-secondary border-2 border-start-0 bg-white"
                                    type="button" onclick="toggleVisibility(this)"><i class="fas fa-eye-slash"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="button" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm fw-bold w-100 w-md-auto" onclick="validateStep3()">
                            Review Transfer <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 4: Review -->
                <div class="wizard-step p-4 p-md-5 d-none" id="step4">
                    <div class="d-flex align-items-center mb-4 cursor-pointer text-primary" onclick="goBackFromReview()">
                        <i class="fas fa-arrow-left me-2"></i> <span class="small fw-bold">Edit Details</span>
                    </div>
                    
                    <h4 class="mb-4 text-center fw-bold">Transfer Review</h4>
                    
                    <div class="card bg-white border-0 mb-4 shadow-sm" style="border-radius: 20px; border: 1px solid var(--border-color) !important;">
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Payment From</span><span class="fw-bold" id="reviewFrom"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Transfer To</span><span class="fw-bold" id="reviewTo"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Delivery Method</span><span class="fw-bold text-capitalize" id="reviewType"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Frequency</span><span class="fw-bold text-capitalize" id="reviewFreq"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Date</span><span class="fw-bold" id="reviewDate"></span></div>
                                <div class="col-12"><span class="text-muted d-block small text-uppercase fw-bold">Purpose</span><span class="fw-bold" id="reviewPurpose"></span></div>
                                
                                {{-- External-only: bank, routing, account details --}}
                                <div class="col-12 external-review-details d-none">
                                    <hr class="my-3">
                                    <div class="row g-3">
                                        <div class="col-md-4"><span class="text-muted d-block small text-uppercase fw-bold">Recipient's Bank Name</span><span class="fw-bold" id="reviewBankName"></span></div>
                                        <div class="col-md-4"><span class="text-muted d-block small text-uppercase fw-bold">Recipient's Routing Number</span><span class="fw-bold" id="reviewRouting"></span></div>
                                        <div class="col-md-4"><span class="text-muted d-block small text-uppercase fw-bold">Recipient's Account Number</span><span class="fw-bold" id="reviewAccount"></span></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Receipt-style fee breakdown --}}
                            <div class="mt-4 p-3 rounded-3" style="background: #f8fafc; border: 1px dashed #d1d5db;">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Transfer Amount</span>
                                    <span class="fw-semibold" id="receiptTransfer">—</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2" id="receiptFeeRow">
                                    <span class="text-muted">Service Fee</span>
                                    <span class="fw-semibold text-muted" id="receiptFee">—</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Total Deducted</span>
                                    <span class="h4 mb-0 text-primary fw-bold" id="reviewAmount">—</span>
                                </div>
                            </div>

                            {{-- Notice for external/member --}}
                            <div id="reviewNotice" class="alert alert-info border-0 mt-3 small d-none" role="alert">
                                <i class="fas fa-info-circle me-1"></i>
                                <span id="reviewNoticeText"></span>
                            </div>

                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm w-100 fs-5 fw-bold mt-3" id="confirmBtn" onclick="event.preventDefault(); SecurityGate.gate(document.getElementById('transferForm'));">
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                <i class="fas fa-shield-alt me-2"></i> Submit Transfer
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
    .wizard-step { transition: all 0.3s ease; }
    .cursor-pointer { cursor: pointer; }
    .transfer-type-card { transition: all 0.25s; border: 2px solid #edeff2 !important; background: #fff; }
    .transfer-type-card:hover { transform: translateY(-5px); border-color: var(--primary-color) !important; box-shadow: 0 10px 25px rgba(0,0,0,0.08)!important; }
    .transfer-type-card.border-primary { border-color: var(--primary-color) !important; background: rgba(0, 84, 155, 0.03); }
    
    .step-circle {
        width: 32px; height: 32px; border-radius: 50%; background: #edeff2; color: #64748b;
        display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700;
    }
    .step-indicator.active .step-circle { background: var(--primary-color); color: white; box-shadow: 0 4px 10px rgba(0, 84, 155, 0.2); }
    .step-indicator.text-success .step-circle { background: #198754; color: white; }
    .step-indicator { font-size: 0.95rem; color: #64748b; font-weight: 600; }
    .step-indicator.active { color: var(--primary-color); }

    .icon-circle { transition: all 0.25s; }
    .transfer-type-card:hover .icon-circle { transform: scale(1.1); }

    /* Mobile Refinements */
    @media (max-width: 768px) {
        .wizard-step { padding: 1.5rem !important; }
        .transfer-type-card { padding: 1.5rem !important; margin-bottom: 0.5rem; }
        .form-control-lg, .form-select-lg { font-size: 1rem; padding: 0.75rem 1rem; }
        h4 { font-size: 1.25rem; }
    }
    
    ::placeholder { font-size: 0.85rem !important; opacity: 0.7; }
    .form-control::-webkit-input-placeholder { font-size: 0.85rem !important; }
    .form-control:-ms-input-placeholder { font-size: 0.85rem !important; }
</style>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var currentStep = 1;
    var transferType = null;
    // State for routing lookup
    var resolvedBankName = '';  // final bank name (auto or manual)
    var routingLookupTimer = null;

    // Fee settings passed from backend
    var feeType  = '{{ setting("fund_transfer_charge_type", "fee") }}';
    var feeValue = parseFloat('{{ setting("fund_transfer_charge", "fee") }}') || 0;

    // ─── Transfer type selection ────────────────────────────────────────────
    window.selectType = function(type, card) {
        transferType = type;
        document.querySelectorAll('.transfer-type-card').forEach(el => el.classList.remove('border-primary'));
        card.classList.add('border-primary');
        const radio = card.querySelector('input[type="radio"]');
        if(radio) radio.checked = true;
        // Sync hidden transfer_type field (for backend reference in description)
        document.getElementById('hiddenTransferType').value = type;
        setTimeout(() => goToStep(2), 250);
    };

    // ─── Step navigation ────────────────────────────────────────────────────
    function goToStep(step) {
        document.querySelectorAll('.wizard-step').forEach(el => { el.classList.add('d-none'); el.classList.remove('active'); });
        const stepEl = document.getElementById('step' + step);
        if(stepEl) { stepEl.classList.remove('d-none'); stepEl.classList.add('active'); }
        updateIndicators(step);
        currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        if(step === 2) setupStep2();
        if(step === 3) populateStep3();
    }

    function updateIndicators(step) {
        document.querySelectorAll('.step-indicator').forEach((el, index) => {
            const stepNum = index + 1;
            el.classList.remove('active', 'text-primary', 'text-success');
            if (stepNum === step) el.classList.add('active');
            else if (stepNum < step) el.classList.add('text-success');
        });
    }

    function setupStep2() {
        document.querySelectorAll('.dynamic-field').forEach(el => el.classList.add('d-none'));
        const field = document.getElementById('field-' + transferType);
        if(field) field.classList.remove('d-none');
        updateAccountOptions();
    }

    // ─── Populate Step 3 (external): copy routing + bank name from step 2 ──
    function populateStep3() {
        const routing = document.getElementById('routingNumberInput')?.value || '';
        document.getElementById('step3RoutingDisplay').value = routing;
        document.getElementById('hiddenRoutingNumber').value  = routing;
        document.getElementById('step3BankDisplay').value     = resolvedBankName || '(Not verified)';
    }

    // ─── Account dropdown options (self transfer) ──────────────────────────
    function updateAccountOptions() {
        const fromSelect = document.getElementById('walletSelect');
        const toSelect   = document.getElementById('toWalletSelect');
        if(!fromSelect || !toSelect) return;
        const selectedType = fromSelect.options[fromSelect.selectedIndex].getAttribute('data-type');

        if(transferType === 'self') {
            toSelect.innerHTML = '';
            if(selectedType !== 'checking') {
                if({{ $wallets->isEmpty() ? 'true' : 'false' }}) {
                    var opt = document.createElement('option');
                    opt.value = 'default';
                    opt.text = 'Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ setting("site_currency", "global") }} {{ number_format(auth()->user()->balance, 2) }}';
                    toSelect.add(opt);
                } else {
                    @foreach($wallets as $wallet)
                        var opt = document.createElement('option');
                        opt.value = '{{ $wallet->currency->code }}';
                        opt.text = 'Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ $wallet->currency->symbol . number_format($wallet->balance, 2) }}';
                        toSelect.add(opt);
                    @endforeach
                }
            }
            if(selectedType !== 'savings') {
                const opt = document.createElement('option');
                opt.value = 'primary_savings';
                opt.text = 'Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S) - {{ setting("site_currency", "global") }} {{ number_format(auth()->user()->savings_balance, 2) }}';
                toSelect.add(opt);
            }
            @if(auth()->user()->ira_status == 1)
            if(selectedType !== 'ira') {
                const opt = document.createElement('option');
                opt.value = 'ira';
                opt.text = 'IRA Account (...{{ substr(auth()->user()->ira_account_number ?? auth()->user()->account_number, -4) }}I) - {{ setting("site_currency", "global") }} {{ number_format(auth()->user()->ira_balance, 2) }}';
                toSelect.add(opt);
            }
            @endif
            @if(auth()->user()->heloc_status == 1)
            if(selectedType !== 'heloc') {
                const opt = document.createElement('option');
                opt.value = 'heloc';
                opt.text = 'HELOC (...{{ substr(auth()->user()->heloc_account_number ?? auth()->user()->account_number, -4) }}H) - Pay Down: {{ setting("site_currency", "global") }} {{ number_format(auth()->user()->heloc_balance, 2) }}';
                toSelect.add(opt);
            }
            @endif
            @if(auth()->user()->cc_status == 1)
            if(selectedType !== 'cc') {
                const opt = document.createElement('option');
                opt.value = 'cc';
                opt.text = 'Credit Card (...{{ substr(auth()->user()->cc_account_number ?? auth()->user()->account_number, -4) }}C) - Pay Down: {{ setting("site_currency", "global") }} {{ number_format(auth()->user()->cc_balance, 2) }}';
                toSelect.add(opt);
            }
            @endif
            @if(auth()->user()->loan_account_status == 1)
            if(selectedType !== 'loan') {
                const opt = document.createElement('option');
                opt.value = 'loan';
                opt.text = 'Loan (...{{ substr(auth()->user()->loan_account_number ?? auth()->user()->account_number, -4) }}L) - Pay Down: {{ setting("site_currency", "global") }} {{ number_format(auth()->user()->loan_balance, 2) }}';
                toSelect.add(opt);
            }
            @endif
        }
        validateBalance();
    }

    // ─── Routing Number Auto-Discovery ──────────────────────────────────────
    function handleRoutingInput(value) {
        clearTimeout(routingLookupTimer);
        const resultBox  = document.getElementById('bankNameResult');
        const spinner    = document.getElementById('routingSpinner');
        const bankText   = document.getElementById('bankNameText');
        const okIcon     = document.getElementById('bankOkIcon');
        const warnIcon   = document.getElementById('bankWarnIcon');
        const manualSect = document.getElementById('manualBankSection');
        const toggleLink = document.getElementById('manualBankToggleLink');

        // Reset state
        resolvedBankName = '';
        document.getElementById('hiddenBankName').value = '';

        if(value.length < 9) {
            resultBox.style.display = 'none';
            manualSect.style.display  = 'none';
            toggleLink.innerHTML = '<i class="fas fa-keyboard me-1"></i> Enter bank name manually';
            return;
        }

        // Show spinner
        spinner.style.removeProperty('display');
        resultBox.style.display = 'none';

        // Debounce: wait 600ms after last keystroke
        routingLookupTimer = setTimeout(() => {
            fetch('/user/lookup-routing/' + encodeURIComponent(value), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                spinner.style.display = 'none';
                okIcon.style.display   = 'none';
                warnIcon.style.display = 'none';
                resultBox.style.display = 'block';

                if(data.status === 'success' && data.bank_name) {
                    resolvedBankName = data.bank_name;
                    document.getElementById('hiddenBankName').value = data.bank_name;
                    bankText.textContent = '✓ ' + data.bank_name;
                    bankText.className   = 'fw-bold text-success';
                    okIcon.style.display = 'inline';
                    // Hide manual section if auto succeeded, but keep toggle link visible
                    manualSect.style.display = 'none';
                    toggleLink.innerHTML = '<i class="fas fa-keyboard me-1"></i> Change bank name manually';
                } else {
                    // Not found → show manual input automatically
                    bankText.textContent  = 'Bank not found. Please enter name below.';
                    bankText.className    = 'fw-bold text-warning';
                    warnIcon.style.display = 'inline';
                    manualSect.style.display = 'block';
                    toggleLink.innerHTML   = '<i class="fas fa-times-circle me-1"></i> Hide manual input';
                    // If user had something in manual input already, use it
                    const manual = document.getElementById('manualBankNameInput').value.trim();
                    if(manual) { resolvedBankName = manual; document.getElementById('hiddenBankName').value = manual; }
                }
            })
            .catch(() => {
                spinner.style.display = 'none';
                bankText.textContent = 'Lookup unavailable. Please enter bank name below.';
                bankText.className   = 'fw-bold text-warning';
                warnIcon.style.display = 'inline';
                resultBox.style.display = 'block';
                manualSect.style.display = 'block';
            });
        }, 600);
    }

    // Sync manual bank name to hidden field
    function syncBankName(val) {
        resolvedBankName = val.trim();
        document.getElementById('hiddenBankName').value = val.trim();
    }

    // Toggle manual bank section
    function toggleManualBank() {
        const manualSect = document.getElementById('manualBankSection');
        const toggleLink = document.getElementById('manualBankToggleLink');
        const isVisible  = manualSect.style.display === 'block';
        manualSect.style.display = isVisible ? 'none' : 'block';
        toggleLink.innerHTML = isVisible
            ? '<i class="fas fa-keyboard me-1"></i> Enter bank name manually'
            : '<i class="fas fa-times-circle me-1"></i> Hide manual input';
    }

    // ─── Balance Validation ─────────────────────────────────────────────────
    function validateBalance() {
        const amount   = parseFloat(document.getElementById('amount').value) || 0;
        const fromSelect = document.getElementById('walletSelect');
        const balance  = parseFloat(fromSelect.options[fromSelect.selectedIndex].getAttribute('data-balance'));
        const feedback = document.getElementById('balanceFeedback');
        if(amount > balance) {
            feedback.innerHTML = '<span class="text-danger small"><i class="fas fa-exclamation-triangle"></i> Insufficient funds available.</span>';
            return false;
        } else if(amount > 0) {
            feedback.innerHTML = '<span class="text-success small"><i class="fas fa-check-circle"></i> Sufficient funds.</span>';
        } else {
            feedback.innerHTML = '';
        }
        return true;
    }

    // ─── Fee calculation helper ─────────────────────────────────────────────
    function calcFee(amount) {
        if(feeType === 'percentage') return (feeValue / 100) * amount;
        return feeValue;
    }

    // ─── Step 2 Validation ──────────────────────────────────────────────────
    function validateStep2() {
        const amount = document.getElementById('amount').value;
        if(!amount || amount <= 0) {
            Swal.fire({ title: 'Invalid Amount', text: 'Please enter a value greater than 0.', icon: 'warning', confirmButtonColor: '#00549b' });
            return;
        }
        if(!validateBalance()) {
            Swal.fire({ title: 'Balance Too Low', text: 'You do not have enough funds for this transfer.', icon: 'error', confirmButtonColor: '#00549b' });
            return;
        }
        if(transferType === 'member' && !document.getElementById('member_identifier').value) {
            Swal.fire({ title: 'Recipient Required', text: "Please provide the recipient's email or account #.", icon: 'warning', confirmButtonColor: '#00549b' });
            return;
        }
        if(transferType === 'external') {
            const routing = document.getElementById('routingNumberInput')?.value || '';
            if(routing.length < 9) {
                Swal.fire({ title: 'Routing Number Required', text: 'Please enter a valid 9-digit ABA routing number.', icon: 'warning', confirmButtonColor: '#00549b' });
                return;
            }
            if(!resolvedBankName) {
                Swal.fire({ title: 'Bank Name Required', text: 'Bank name could not be verified automatically. Please enter the bank name manually.', icon: 'warning', confirmButtonColor: '#00549b' });
                // Show the manual input automatically
                document.getElementById('manualBankSection').style.display = 'block';
                return;
            }
            goToStep(3);
        } else {
            populateReview();
            goToStep(4);
        }
    }

    // ─── Step 3 Validation (external recipient details) ──────────────────────
    function validateStep3() {
        const name    = document.getElementById('extAccountName')?.value.trim();
        const acc1    = document.getElementById('ext_acc_num').value;
        const acc2    = document.getElementById('ext_acc_num_confirm').value;
        const routing = document.getElementById('hiddenRoutingNumber').value;

        if(!name) {
            Swal.fire({ title: 'Name Required', text: "Please enter the recipient's full name.", icon: 'warning', confirmButtonColor: '#00549b' });
            return;
        }
        if(!acc1 || acc1.length < 4) {
            Swal.fire({ title: 'Account Number Required', text: 'Please enter a valid account number.', icon: 'warning', confirmButtonColor: '#00549b' });
            return;
        }
        if(acc1 !== acc2) {
            Swal.fire({ title: 'Account Mismatch', text: 'Account numbers do not match. Please re-enter.', icon: 'error', confirmButtonColor: '#00549b' });
            return;
        }
        if(routing.length < 9) {
            Swal.fire({ title: 'Routing Number Error', text: 'Routing number is missing. Please go back and re-enter it.', icon: 'error', confirmButtonColor: '#00549b' });
            return;
        }
        populateReview();
        goToStep(4);
    }

    // ─── Review Panel Population ─────────────────────────────────────────────
    function populateReview() {
        const amount     = parseFloat(document.getElementById('amount').value) || 0;
        const fee        = calcFee(amount);
        const total      = amount + fee;
        const currSymbol = '$';

        // Basic fields
        document.getElementById('reviewType').innerText    = transferType === 'self' ? 'Intra-Account' : (transferType === 'member' ? 'Member Transfer' : 'External ACH');
        const fromSelect = document.getElementById('walletSelect');
        document.getElementById('reviewFrom').innerText    = fromSelect.options[fromSelect.selectedIndex].text.split(' - ')[0];
        document.getElementById('reviewDate').innerText    = document.querySelector('input[name="scheduled_at"]').value;
        document.getElementById('reviewFreq').innerText    = document.querySelector('select[name="frequency"]').value;
        document.getElementById('reviewPurpose').innerText = document.getElementById('transferPurpose').value || 'Transfer of funds';

        // Receipt breakdown
        document.getElementById('receiptTransfer').innerText = currSymbol + amount.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
        if(fee > 0) {
            document.getElementById('receiptFee').innerText = '+ ' + currSymbol + fee.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
            document.getElementById('receiptFeeRow').style.display = '';
        } else {
            document.getElementById('receiptFee').innerText  = 'None';
            document.getElementById('receiptFeeRow').style.display = '';
        }
        document.getElementById('reviewAmount').innerText  = currSymbol + total.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});

        // Transfer destination
        let toText = 'Unknown';
        if(transferType === 'self') {
            toText = document.getElementById('toWalletSelect').options[0]?.text.split(' - ')[0] || 'My Account';
        } else if(transferType === 'member') {
            toText = 'Member: ' + document.getElementById('member_identifier').value;
        } else {
            toText = resolvedBankName || 'External Bank';
        }
        document.getElementById('reviewTo').innerText = toText;

        // External-specific details
        const extDetails = document.querySelector('.external-review-details');
        if(transferType === 'external') {
            extDetails.classList.remove('d-none');
            document.getElementById('reviewBankName').innerText = resolvedBankName || '—';
            document.getElementById('reviewRouting').innerText  = document.getElementById('hiddenRoutingNumber').value || '—';
            const accNum = document.getElementById('ext_acc_num').value;
            document.getElementById('reviewAccount').innerText  = '••••' + accNum.slice(-4);
        } else {
            extDetails.classList.add('d-none');
        }

        // Informational notice
        const notice     = document.getElementById('reviewNotice');
        const noticeText = document.getElementById('reviewNoticeText');
        if(transferType === 'external') {
            notice.classList.remove('d-none');
            noticeText.innerText = 'External ACH transfers are reviewed by our team within 1–2 business days. Funds are held until approved.';
        } else if(transferType === 'member') {
            notice.classList.remove('d-none');
            noticeText.innerText = 'Member transfers are reviewed before processing. You will be notified once it is approved.';
        } else {
            notice.classList.add('d-none');
        }
    }

    // ─── Back navigation from review ────────────────────────────────────────
    function goBackFromReview() {
        if(transferType === 'external') goToStep(3);
        else goToStep(2);
    }

    // ─── Eye toggle on password fields ──────────────────────────────────────
    function toggleVisibility(btn) {
        const input = btn.parentElement.querySelector('input');
        input.type = input.type === 'password' ? 'text' : 'password';
        btn.querySelector('i').classList.toggle('fa-eye');
        btn.querySelector('i').classList.toggle('fa-eye-slash');
    }

    // ─── DOM ready ───────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        // Card click → select type
        document.querySelectorAll('.js-type-select').forEach(card => {
            card.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                window.selectType(type, this);
            });
        });

        // Member lookup
        const memberInput = document.getElementById('member_identifier');
        const optSavings  = document.getElementById('opt-savings');
        const targetAccountType = document.getElementById('target_account_type');

        if(memberInput) {
            memberInput.addEventListener('input', function() {
                const val = this.value;
                const memberNameDisplayText = document.getElementById('member_name_display_text');

                if(val.length > 4 || (val.includes('@') && val.length > 3)) {
                    fetch('/user/search-by-account-number/' + encodeURIComponent(val))
                        .then(r => r.json())
                        .then(data => {
                            if(data.name) {
                                memberNameDisplayText.innerText = data.name;
                                memberNameDisplayText.classList.remove('text-muted');
                                memberNameDisplayText.classList.add('fw-bold', 'text-dark');
                                if(data.has_savings) {
                                    optSavings.hidden = false;
                                    optSavings.disabled = false;
                                } else {
                                    optSavings.hidden = true; optSavings.disabled = true;
                                    targetAccountType.value = 'checking';
                                }
                                if(val === data.savings_account_number) targetAccountType.value = 'savings';
                                else if(val === data.account_number)    targetAccountType.value = 'checking';
                            } else {
                                memberNameDisplayText.innerText = 'Member not found!';
                                memberNameDisplayText.classList.add('text-danger');
                                memberNameDisplayText.classList.remove('text-dark', 'fw-bold');
                                optSavings.hidden = true; optSavings.disabled = true;
                                targetAccountType.value = 'checking';
                            }
                        });
                } else {
                    memberNameDisplayText.innerText = 'Enter info to verify...';
                    memberNameDisplayText.classList.remove('text-danger', 'text-dark', 'fw-bold');
                    if(optSavings) { optSavings.hidden = true; optSavings.disabled = true; }
                    if(targetAccountType) targetAccountType.value = 'checking';
                }
            });
        }
    });
</script>
@endsection
