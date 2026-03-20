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
                                <label class="form-label small text-uppercase fw-bold text-muted">Destination Details</label>
                                <div class="p-3 bg-light rounded-3 small text-muted">
                                    <i class="fas fa-info-circle me-2"></i> You will provide the routing and account numbers in the next step.
                                </div>
                                <input type="hidden" name="bank_id" id="bankId" value="0">
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
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Full Name</label>
                            <input type="text" name="manual_data[account_name]" class="form-control form-control-lg border-2 shadow-none" placeholder="Recipient's legal name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">Routing Number</label>
                            <div class="input-group">
                                <input type="text" inputmode="numeric" name="manual_data[routing_number]" id="routing_number" class="form-control form-control-lg border-2 shadow-sm" placeholder="9 digits numeric" maxlength="9" pattern="[0-9]{9}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                            <div id="routing_bank_display" class="mt-2 d-none">
                                <div class="px-3 py-2 bg-success bg-opacity-10 text-success rounded-3 small d-flex align-items-center">
                                    <i class="fas fa-university me-2"></i>
                                    <span id="discovered_bank_name" class="fw-bold"></span>
                                </div>
                            </div>
                            <div id="routing_error_display" class="mt-2 d-none">
                                <div class="px-3 py-2 bg-danger bg-opacity-10 text-danger rounded-3 small d-flex align-items-center">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <span id="routing_error_text"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">Account Number</label>
                            <div class="input-group">
                                <input type="password" name="manual_data[account_number]" id="ext_acc_num" class="form-control form-control-lg border-2 shadow-sm toggle-password" placeholder="4-20 digits numeric" minlength="4" maxlength="20" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                <button class="btn btn-outline-secondary border-2 border-start-0 bg-white" type="button" onclick="toggleVisibility(this)"><i class="fas fa-eye-slash"></i></button>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Confirm Account Number</label>
                            <div class="input-group">
                                <input type="password" id="ext_acc_num_confirm" class="form-control form-control-lg border-2 shadow-sm toggle-password" placeholder="Re-enter to confirm">
                                <button class="btn btn-outline-secondary border-2 border-start-0 bg-white" type="button" onclick="toggleVisibility(this)"><i class="fas fa-eye-slash"></i></button>
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
                    
                    <div class="receipt-container mx-auto" style="max-width: 450px;">
                        <div class="receipt-card bg-white shadow-sm border" style="border-radius: 24px; overflow: hidden;">
                            <div class="receipt-header p-4 bg-light border-bottom text-center">
                                <div class="mb-2"><span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold" id="reviewType"></span></div>
                                <h2 class="mb-0 fw-bold text-primary" id="reviewAmountDisplay"></h2>
                                <p class="small text-muted mb-0">Total amount to be deducted</p>
                            </div>
                            
                            <div class="receipt-body p-4">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted small fw-bold text-uppercase">From</span>
                                    <span class="fw-bold text-end" id="reviewFrom"></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted small fw-bold text-uppercase">To</span>
                                    <span class="fw-bold text-end" id="reviewTo"></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted small fw-bold text-uppercase">Date</span>
                                    <span class="fw-bold text-end" id="reviewDate"></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted small fw-bold text-uppercase">Frequency</span>
                                    <span class="fw-bold text-end text-capitalize" id="reviewFreq"></span>
                                </div>
                                
                                <div class="external-review-details d-none" id="reviewExternalSection">
                                    <hr class="my-3 border-dashed" style="border-top: 1px dashed #dee2e6;">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small fw-bold text-uppercase">Routing #</span>
                                        <span class="fw-bold" id="reviewRouting"></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small fw-bold text-uppercase">Account #</span>
                                        <span class="fw-bold" id="reviewAccount"></span>
                                    </div>
                                </div>

                                <hr class="my-3">
                                
                                <div class="p-3 rounded-4 bg-light border">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Transfer Amount</span>
                                        <span class="fw-bold text-dark" id="breakdownSubtotal"></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Processing Fee</span>
                                        <span class="fw-bold text-danger font-monospace" id="breakdownFee"></span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                                        <span class="fw-bold text-dark">Total Deduction</span>
                                        <span class="fw-bold text-primary" id="breakdownTotal"></span>
                                    </div>
                                </div>

                                <div class="mt-4 p-3 bg-info bg-opacity-10 text-info rounded-4 small">
                                    <div class="d-flex">
                                        <i class="fas fa-clock mt-1 me-2"></i>
                                        <div>
                                            <span class="fw-bold d-block">Est. Processing Time</span>
                                            <span id="deliveryEstimate">1-2 Business Days</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-4 bg-light border-top">
                                <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm w-100 fs-5 fw-bold" id="confirmBtn" onclick="event.preventDefault(); SecurityGate.gate(document.getElementById('transferForm'));">
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                    <i class="fas fa-shield-alt me-2"></i> Confirm & Send
                                </button>
                                <p class="text-center small text-muted mt-3 mb-0"><i class="fas fa-lock me-1"></i> Secure end-to-end encryption</p>
                            </div>
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

    // Moving this to a direct function so it's globally available
    window.selectType = function(type, card) {
        console.log('selectType called via listener:', type);
        transferType = type;
        
        document.querySelectorAll('.transfer-type-card').forEach(el => el.classList.remove('border-primary'));
        card.classList.add('border-primary');
        const radio = card.querySelector('input[type="radio"]');
        if(radio) radio.checked = true;

        setTimeout(() => goToStep(2), 250);
    };

    function goToStep(step) {
        console.log('goToStep:', step);
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.add('d-none', 'active'));
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
        
        const stepEl = document.getElementById('step' + step);
        if(stepEl) {
            stepEl.classList.remove('d-none');
            stepEl.classList.add('active');
        }
        
        updateIndicators(step);
        currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });

        if(step === 2) setupStep2();
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

    function updateAccountOptions() {
        const fromSelect = document.getElementById('walletSelect');
        const toSelect = document.getElementById('toWalletSelect');
        if(!fromSelect || !toSelect) return;
        const selectedType = fromSelect.options[fromSelect.selectedIndex].getAttribute('data-type');
        
        if(transferType === 'self') {
            toSelect.innerHTML = '';
            
            // Add Checking
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

            // Add Savings
            if(selectedType !== 'savings') {
                const opt = document.createElement('option');
                opt.value = 'primary_savings';
                opt.text = 'Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S) - {{ setting("site_currency", "global") }} {{ number_format(auth()->user()->savings_balance, 2) }}';
                toSelect.add(opt);
            }

            // Add IRA
            @if(auth()->user()->ira_status == 1)
            if(selectedType !== 'ira') {
                const opt = document.createElement('option');
                opt.value = 'ira';
                opt.text = 'IRA Account (...{{ substr(auth()->user()->ira_account_number ?? auth()->user()->account_number, -4) }}I) - {{ setting("site_currency", "global") }} {{ number_format(auth()->user()->ira_balance, 2) }}';
                toSelect.add(opt);
            }
            @endif

            // Add HELOC
            @if(auth()->user()->heloc_status == 1)
            if(selectedType !== 'heloc') {
                const opt = document.createElement('option');
                opt.value = 'heloc';
                opt.text = 'HELOC Account (...{{ substr(auth()->user()->heloc_account_number ?? auth()->user()->account_number, -4) }}H) - Pay Down Balance: {{ setting("site_currency", "global") }} {{ number_format(auth()->user()->heloc_balance, 2) }}';
                toSelect.add(opt);
            }
            @endif

            // Add Credit Card
            @if(auth()->user()->cc_status == 1)
            if(selectedType !== 'cc') {
                const opt = document.createElement('option');
                opt.value = 'cc';
                opt.text = 'Credit Card (...{{ substr(auth()->user()->cc_account_number ?? auth()->user()->account_number, -4) }}C) - Pay Down Balance: {{ setting("site_currency", "global") }} {{ number_format(auth()->user()->cc_balance, 2) }}';
                toSelect.add(opt);
            }
            @endif

            // Add Loan
            @if(auth()->user()->loan_account_status == 1)
            if(selectedType !== 'loan') {
                const opt = document.createElement('option');
                opt.value = 'loan';
                opt.text = 'Loan Account (...{{ substr(auth()->user()->loan_account_number ?? auth()->user()->account_number, -4) }}L) - Pay Down Balance: {{ setting("site_currency", "global") }} {{ number_format(auth()->user()->loan_balance, 2) }}';
                toSelect.add(opt);
            }
            @endif
        }
        validateBalance();
    }

    function validateBalance() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const fromSelect = document.getElementById('walletSelect');
        const balance = parseFloat(fromSelect.options[fromSelect.selectedIndex].getAttribute('data-balance'));
        const feedback = document.getElementById('balanceFeedback');
        
        if (amount > balance) {
            feedback.innerHTML = '<span class="text-danger small"><i class="fas fa-exclamation-triangle"></i> Insufficient funds available.</span>';
            return false;
        } else if (amount > 0) {
            feedback.innerHTML = '<span class="text-success small"><i class="fas fa-check-circle"></i> Sufficient funds.</span>';
        } else {
            feedback.innerHTML = '';
        }
        return true;
    }

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
            Swal.fire({ title: 'Recipient Required', text: 'Please provide recipient\'s email or account #.', icon: 'warning', confirmButtonColor: '#00549b' });
            return;
        }
        if(transferType === 'external' && !document.getElementById('bankId').value) {
            Swal.fire({ title: 'Bank Required', text: 'Please select a destination bank.', icon: 'warning', confirmButtonColor: '#00549b' });
            return;
        }
        if(transferType === 'external') goToStep(3);
        else { populateReview(); goToStep(4); }
    }

    function validateStep3() {
        const name = document.querySelector('input[name="manual_data[account_name]"]').value;
        const acc1 = document.getElementById('ext_acc_num').value;
        const acc2 = document.getElementById('ext_acc_num_confirm').value;
        const routing = document.querySelector('input[name="manual_data[routing_number]"]').value;

        if(!name || !acc1 || routing.length < 9) {
            Swal.fire({ title: 'Details Missing', text: 'Please complete all recipient information.', icon: 'warning', confirmButtonColor: '#00549b' });
            return;
        }
        if(acc1 !== acc2) {
            Swal.fire({ title: 'Mismatch', text: 'Account numbers do not match.', icon: 'error', confirmButtonColor: '#00549b' });
            return;
        }
        populateReview();
        goToStep(4);
    }

    var selectedBankCharge = {{ setting('fund_transfer_charge', 'fee') }};
    var selectedBankChargeType = '{{ setting('fund_transfer_charge_type', 'fee') }}';

    function populateReview() {
        // Basic Info
        const displayType = transferType === 'self' ? 'Intra-Account' : (transferType === 'member' ? 'Member Transfer' : 'External ACH');
        document.getElementById('reviewType').innerText = displayType;
        
        const fromSelect = document.getElementById('walletSelect');
        document.getElementById('reviewFrom').innerText = fromSelect.options[fromSelect.selectedIndex].text.split(' - ')[0];
        
        let toText = 'Unknown';
        if(transferType === 'self') {
            toText = document.getElementById('toWalletSelect').options[document.getElementById('toWalletSelect').selectedIndex]?.text.split(' - ')[0] || 'My Account';
            document.getElementById('reviewExternalSection').classList.add('d-none');
            selectedBankCharge = 0; // Self transfers are free
            selectedBankChargeType = 'fixed';
        } else if(transferType === 'member') {
            toText = document.getElementById('member_name_display_text').innerText;
            document.getElementById('reviewExternalSection').classList.add('d-none');
            // Member transfers usually follow global settings
        } else {
            toText = document.getElementById('discovered_bank_name').innerText || 'External Bank';
            document.getElementById('reviewExternalSection').classList.remove('d-none');
            document.getElementById('reviewRouting').innerText = document.getElementById('routing_number').value;
            document.getElementById('reviewAccount').innerText = '****' + document.getElementById('ext_acc_num').value.slice(-4);
        }
        
        document.getElementById('reviewTo').innerText = toText;
        document.getElementById('reviewDate').innerText = document.querySelector('input[name="scheduled_at"]').value;
        document.getElementById('reviewFreq').innerText = document.querySelector('select[name="frequency"]').value;

        // Breakdown Calculation
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        let fee = 0;
        if(selectedBankChargeType === 'percent' || selectedBankChargeType === 'percentage') {
            fee = (amount * selectedBankCharge) / 100;
        } else {
            fee = selectedBankCharge;
        }

        const total = amount + fee;
        const symbol = '$'; // Assuming USD for now, could be dynamic

        document.getElementById('reviewAmountDisplay').innerText = symbol + total.toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('breakdownSubtotal').innerText = symbol + amount.toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('breakdownFee').innerText = '+' + symbol + fee.toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('breakdownTotal').innerText = symbol + total.toLocaleString(undefined, {minimumFractionDigits: 2});
        
        // Delivery Estimate
        const estimate = document.getElementById('deliveryEstimate');
        if(transferType === 'self') estimate.innerText = 'Instant';
        else if(transferType === 'member') estimate.innerText = '1-2 Business Hours';
        else estimate.innerText = '1-3 Business Days';
    }

    function goBackFromReview() {
        if(transferType === 'external') goToStep(3);
        else goToStep(2);
    }

    function toggleVisibility(btn) {
        const input = btn.parentElement.querySelector('input');
        input.type = input.type === "password" ? "text" : "password";
        btn.querySelector('i').classList.toggle('fa-eye');
        btn.querySelector('i').classList.toggle('fa-eye-slash');
    }

    // Attach listeners after DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.js-type-select').forEach(card => {
            card.addEventListener('click', function(e) {
                const type = this.getAttribute('data-type');
                window.selectType(type, this);
            });
        });
        
        // Form submission is handled by SecurityGate via onclick on the confirm button

        // Unified Member Lookup
        const memberInput = document.getElementById('member_identifier');
        const memberNameDisplay = document.getElementById('member_name_display');
        const targetAccountType = document.getElementById('target_account_type');
        const optSavings = document.getElementById('opt-savings');

        if(memberInput) {
            memberInput.addEventListener('input', function() {
                const val = this.value;
                const memberNameDisplayText = document.getElementById('member_name_display_text');
                const memberNameWrapper = document.getElementById('member_name_display_wrapper');

                if(val.length > 4 || (val.includes('@') && val.length > 3)) {
                    fetch('/user/search-by-account-number/' + encodeURIComponent(val))
                        .then(response => response.json())
                        .then(data => {
                            if(data.name) {
                                memberNameDisplayText.innerText = data.name;
                                memberNameDisplayText.classList.remove('text-muted');
                                memberNameDisplayText.classList.add('fw-bold', 'text-dark');
                                
                                if(data.has_savings) {
                                    optSavings.hidden = false;
                                    optSavings.disabled = false;
                                } else {
                                    optSavings.hidden = true;
                                    optSavings.disabled = true;
                                    targetAccountType.value = 'checking';
                                }
                                
                                // Auto-select savings if they searched with savings account number
                                if(val === data.savings_account_number) {
                                    targetAccountType.value = 'savings';
                                } else if(val === data.account_number) {
                                    targetAccountType.value = 'checking';
                                }
                            } else {
                                memberNameDisplayText.innerText = 'Member not Found!';
                                memberNameDisplayText.classList.add('text-danger');
                                memberNameDisplayText.classList.remove('text-dark', 'fw-bold');
                                optSavings.hidden = true;
                                optSavings.disabled = true;
                                targetAccountType.value = 'checking';
                            }
                        });
                } else {
                    memberNameDisplayText.innerText = 'Enter info to verify...';
                    memberNameDisplayText.classList.remove('text-danger', 'text-dark', 'fw-bold');
                    optSavings.hidden = true;
                    optSavings.disabled = true;
                    targetAccountType.value = 'checking';
                }
            });
        }

        // Routing Lookup
        const routingInput = document.getElementById('routing_number');
        if(routingInput) {
            routingInput.addEventListener('input', function() {
                const val = this.value;
                const bankDisplay = document.getElementById('routing_bank_display');
                const errorDisplay = document.getElementById('routing_error_display');
                const bankNameTxt = document.getElementById('discovered_bank_name');
                const errorTxt = document.getElementById('routing_error_text');
                const adminBankId = document.getElementById('bankId');

                if(val.length === 9) {
                    fetch('/user/fund-transfer/routing-lookup/' + encodeURIComponent(val))
                        .then(response => response.json())
                        .then(data => {
                            if(data.status === 'success') {
                                bankNameTxt.innerText = data.name;
                                bankDisplay.classList.remove('d-none');
                                errorDisplay.classList.add('d-none');
                                if(data.id) adminBankId.value = data.id;
                                else adminBankId.value = 0;
                                
                                // Store charges for review
                                selectedBankCharge = data.charge;
                                selectedBankChargeType = data.charge_type;
                            } else {
                                errorTxt.innerText = data.message;
                                errorDisplay.classList.remove('d-none');
                                bankDisplay.classList.add('d-none');
                                adminBankId.value = 0;
                            }
                        });
                } else {
                    bankDisplay.classList.add('d-none');
                    errorDisplay.classList.add('d-none');
                }
            });
        }
    });
</script>
@endsection
