@extends('frontend::layouts.user')

@section('title')
    {{ __('Transfer Funds') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10 col-12">
        <div class="text-center mb-5">
            <h2 class="mb-3">Transfer Funds</h2>
            <p class="text-muted">Send money to other banks or between your accounts.</p>
        </div>

        <div class="banno-card p-0 overflow-hidden mb-4">
            <div class="wizard-header p-4 border-bottom bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="step-indicator active" data-step="1">1. Type</div>
                    <div class="step-line flex-grow-1 mx-3 bg-secondary opacity-25" style="height: 2px;"></div>
                    <div class="step-indicator" data-step="2">2. Details</div>
                    <div class="step-line flex-grow-1 mx-3 bg-secondary opacity-25" style="height: 2px;"></div>
                    <div class="step-indicator" data-step="3">3. Review</div>
                </div>
            </div>

            <form action="{{ route('user.fund_transfer.transfer') }}" method="POST" id="transferForm">
                @csrf
                <input type="hidden" name="charge_type" value="percentage">
                
                <!-- Step 1: Transfer Type -->
                <div class="wizard-step p-5" id="step1">
                    <h4 class="mb-4 text-center">Who are you sending money to?</h4>
                    <div class="row g-4 justify-content-center">
                        <div class="col-md-4">
                            <label class="transfer-type-card h-100 p-4 border rounded-3 text-center d-flex flex-column align-items-center cursor-pointer radio-label">
                                <input type="radio" name="transfer_type" value="self" class="d-none" onchange="selectType('self')">
                                <div class="icon-circle bg-primary bg-opacity-10 text-primary mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fas fa-wallet fa-lg"></i>
                                </div>
                                <h6 class="mb-2">My Accounts</h6>
                                <p class="small text-muted mb-0">Transfer between your checking and savings.</p>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="transfer-type-card h-100 p-4 border rounded-3 text-center d-flex flex-column align-items-center cursor-pointer radio-label">
                                <input type="radio" name="transfer_type" value="member" class="d-none" onchange="selectType('member')">
                                <div class="icon-circle bg-success bg-opacity-10 text-success mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fas fa-users fa-lg"></i>
                                </div>
                                <h6 class="mb-2">Another Member</h6>
                                <p class="small text-muted mb-0">Send instantly to another credit union member.</p>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="transfer-type-card h-100 p-4 border rounded-3 text-center d-flex flex-column align-items-center cursor-pointer radio-label">
                                <input type="radio" name="transfer_type" value="external" class="d-none" onchange="selectType('external')">
                                <div class="icon-circle bg-info bg-opacity-10 text-info mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fas fa-university fa-lg"></i>
                                </div>
                                <h6 class="mb-2">External Bank</h6>
                                <p class="small text-muted mb-0">Send via ACH or Wire to another bank.</p>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Details -->
                <div class="wizard-step p-5 d-none" id="step2">
                    <div class="d-flex align-items-center mb-4 cursor-pointer" onclick="goToStep(1)">
                        <i class="fas fa-arrow-left me-2 text-muted"></i> <span class="text-muted small">Back to Type</span>
                    </div>

                    <!-- From Account -->
                    <div class="mb-4">
                        <label class="form-label small text-uppercase text-muted">From Account</label>
                        <select name="wallet_type" class="form-select form-select-lg border-2" id="walletSelect" onchange="updateToAccounts()">
                            @foreach($wallets as $wallet)
                                <option value="{{ $wallet->currency->code }}" data-type="checking" data-balance="{{ $wallet->balance }}">
                                    Checking - {{ $wallet->currency->symbol . $wallet->balance }}
                                </option>
                            @endforeach
                            <option value="primary_savings" data-type="savings" data-balance="{{ auth()->user()->savings_balance }}">
                                Savings - {{ setting('site_currency') }} {{ auth()->user()->savings_balance }}
                            </option>
                        </select>
                    </div>

                    <!-- Dynamic 'To' Section -->
                    <div id="toSection">
                        <!-- Case: Self -->
                        <div class="dynamic-field d-none" id="field-self">
                            <label class="form-label small text-uppercase text-muted">To Account</label>
                            <select name="to_wallet" class="form-select form-select-lg border-2" id="toWalletSelect">
                                <!-- Populated by JS -->
                            </select>
                        </div>

                        <!-- Case: Member -->
                        <div class="dynamic-field d-none" id="field-member">
                            <label class="form-label small text-uppercase text-muted">Recipient Email or Account</label>
                            <input type="text" name="email" class="form-control form-control-lg border-2" placeholder="Start typing...">
                        </div>

                        <!-- Case: External -->
                        <div class="dynamic-field d-none" id="field-external">
                            <div class="mb-3">
                                <label class="form-label small text-uppercase text-muted">Recipient Bank</label>
                                <select name="bank_id" class="form-select form-select-lg border-2" id="bankId">
                                    <option value="" selected disabled>Select Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Beneficiary Selection for External -->
                            <div class="mb-3">
                                <label class="form-label small text-uppercase text-muted">Select Recipient</label>
                                <div class="input-group">
                                    <select name="beneficiary_id" class="form-select form-select-lg border-2" id="beneficiarySelect" onchange="toggleManualFields()">
                                         <option value="">-- New Recipient --</option>
                                         <!-- Populated by JS/AJAX ideally, but for now we rely on user adding new -->
                                    </select>
                                </div>
                            </div>

                            <div id="manualFields">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small text-uppercase text-muted">Routing Number</label>
                                        <input type="text" name="manual_data[routing_number]" class="form-control border-2" placeholder="9 digits" maxlength="9">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-uppercase text-muted">Account Name</label>
                                        <input type="text" name="manual_data[account_name]" class="form-control border-2" placeholder="e.g. John Doe">
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small text-uppercase text-muted">Account Number</label>
                                        <input type="password" name="manual_data[account_number]" id="ext_acc_num" class="form-control border-2" placeholder="Account Number">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-uppercase text-muted">Confirm Account Number</label>
                                        <input type="text" id="ext_acc_num_confirm" class="form-control border-2" placeholder="Confirm Number">
                                        <div class="invalid-feedback">Account numbers must match.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Amount -->
                    <div class="mt-4">
                        <label class="form-label small text-uppercase text-muted">Amount</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-2 border-end-0">$</span>
                            <input type="number" step="0.01" class="form-control border-2 border-start-0" id="amount" name="amount" placeholder="0.00" style="font-size: 1.5rem;" required>
                        </div>
                        <div class="small text-muted mt-2" id="limitText"></div>
                    </div>
                    
                    <!-- Frequency -->
                    <div class="mt-4">
                         <label class="form-label small text-uppercase text-muted">Frequency</label>
                         <select name="frequency" class="form-select form-select-lg border-2">
                             <option value="once">One-time Transfer</option>
                             <option value="daily">Daily</option>
                             <option value="weekly">Weekly</option>
                             <option value="monthly">Monthly</option>
                         </select>
                    </div>

                    <!-- Next Button -->
                    <div class="mt-5 text-end">
                        <button type="button" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm" onclick="validateStep2()">
                            Next: Review <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                    
                    <!-- Hidden input for "charge" logic if needed by backend -->
                    <div style="display:none">
                        <input type="text" class="fee-display">
                        <input type="text" class="total-display">
                    </div>
                </div>

                <!-- Step 3: Review -->
                <div class="wizard-step p-5 d-none" id="step3">
                    <div class="d-flex align-items-center mb-4 cursor-pointer" onclick="goToStep(2)">
                        <i class="fas fa-arrow-left me-2 text-muted"></i> <span class="text-muted small">Back to Details</span>
                    </div>
                    
                    <h4 class="mb-4">Review Transfer</h4>
                    
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Type</span>
                                <span class="fw-bold text-capitalize" id="reviewType"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">From</span>
                                <span class="fw-bold" id="reviewFrom"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">To</span>
                                <span class="fw-bold" id="reviewTo"></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0">Total Amount</span>
                                <span class="h4 mb-0 text-primary" id="reviewAmount"></span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm w-100 fs-5">
                        <i class="fas fa-paper-plane me-2"></i> Confirm Transfer
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
    .step-indicator {
        font-size: 0.9rem;
        color: #adb5bd;
        font-weight: 500;
        white-space: nowrap;
    }
    .step-indicator.active {
        color: var(--primary-color);
        font-weight: 700;
    }
    .radio-label {
        transition: all 0.2s;
    }
    .radio-label:hover, .radio-label:has(input:checked) {
        background-color: #f8f9fa;
        border-color: var(--primary-color) !important;
        transform: translateY(-2px);
    }
    .radio-label:has(input:checked) .icon-circle {
        background-color: var(--primary-color) !important;
        color: white !important;
    }
    .radio-label:has(input:checked) p {
        color: var(--primary-color) !important;
    }
</style>
@endsection

@section('script')
<script>
    let currentStep = 1;
    let transferType = null;

    function selectType(type) {
        transferType = type;
        // Visual feedback
        document.querySelectorAll('.transfer-type-card').forEach(el => el.classList.remove('border-primary'));
        event.target.closest('.transfer-type-card').classList.add('border-primary');
        
        setTimeout(() => {
            goToStep(2);
        }, 300);
    }

    function goToStep(step) {
        // Hide all steps
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.add('d-none'));
        // Show target step
        document.getElementById('step' + step).classList.remove('d-none');
        
        // Update header indicators
        updateIndicators(step);
        currentStep = step;

        if(step === 2) {
            setupStep2();
        }
    }

    function updateIndicators(step) {
        document.querySelectorAll('.step-indicator').forEach((el, index) => {
            if(index + 1 === step) el.classList.add('active');
            else el.classList.remove('active');
        });
    }

    function setupStep2() {
        // Hide all dynamic fields
        document.querySelectorAll('.dynamic-field').forEach(el => el.classList.add('d-none'));
        
        // Show relevant field
        document.getElementById('field-' + transferType).classList.remove('d-none');
        
        // Trigger logic
        updateToAccounts();
    }

    function updateToAccounts() {
        if(transferType !== 'self') return;
        
        const fromSelect = document.getElementById('walletSelect');
        const toSelect = document.getElementById('toWalletSelect');
        const selectedOption = fromSelect.options[fromSelect.selectedIndex];
        const type = selectedOption.getAttribute('data-type'); // checking or savings

        toSelect.innerHTML = ''; // Clear

        if(type === 'checking') {
            // Add Savings option
            const opt = document.createElement('option');
            opt.value = 'primary_savings';
            opt.text = 'Savings - ' + '{{ setting("site_currency") }} {{ auth()->user()->savings_balance }}';
            toSelect.add(opt);
        } else {
             // Add Checking options
             @foreach($wallets as $wallet)
                var opt = document.createElement('option');
                opt.value = '{{ $wallet->currency->code }}';
                opt.text = 'Checking - {{ $wallet->currency->symbol . $wallet->balance }}';
                toSelect.add(opt);
             @endforeach
        }
    }

    function toggleManualFields() {
        // Implementation for Beneficiary vs Manual logic if needed
    }

    function validateStep2() {
        const amount = document.getElementById('amount').value;
        if(!amount || amount <= 0) {
            alert('Please enter a valid amount.');
            return;
        }

        if(transferType === 'external') {
            const acc1 = document.getElementById('ext_acc_num').value;
            const acc2 = document.getElementById('ext_acc_num_confirm').value;
            if(acc1 !== acc2) {
                alert('Account numbers do not match.');
                return;
            }
            if(!/^\d+$/.test(acc1)) {
                alert('Account number must be numeric.');
                return;
            }
        }
        
        populateReview();
        goToStep(3);
    }

    function populateReview() {
        document.getElementById('reviewType').innerText = transferType;
        
        const fromSelect = document.getElementById('walletSelect');
        document.getElementById('reviewFrom').innerText = fromSelect.options[fromSelect.selectedIndex].text;
        
        let toText = '';
        if(transferType === 'self') {
            const toSelect = document.getElementById('toWalletSelect');
            toText = toSelect.options[toSelect.selectedIndex].text;
        } else if(transferType === 'member') {
            toText = document.querySelector('input[name="email"]').value;
        } else {
             const bankSelect = document.getElementById('bankId');
             const bankName = bankSelect.options[bankSelect.selectedIndex].text;
             toText = bankName + ' (Ext)';
        }
        document.getElementById('reviewTo').innerText = toText;
        
        document.getElementById('reviewAmount').innerText = '$' + document.getElementById('amount').value;
    }
</script>
@endsection
