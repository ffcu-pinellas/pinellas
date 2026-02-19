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

        <div class="banno-card p-0 mb-4">
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
                <div class="wizard-step active p-5" id="step1">
                    <h4 class="mb-4 text-center">Who are you sending money to?</h4>
                    <div class="row g-4 justify-content-center">
                        <div class="col-md-4">
                            <label class="transfer-type-card h-100 p-4 border rounded-3 text-center d-flex flex-column align-items-center cursor-pointer radio-label" onclick="selectType('self', event)">
                                <input type="radio" name="transfer_type" value="self" class="d-none">
                                <div class="icon-circle bg-primary bg-opacity-10 text-primary mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fas fa-wallet fa-lg"></i>
                                </div>
                                <h6 class="mb-2">My Accounts</h6>
                                <p class="small text-muted mb-0">Transfer between your checking and savings.</p>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="transfer-type-card h-100 p-4 border rounded-3 text-center d-flex flex-column align-items-center cursor-pointer radio-label" onclick="selectType('member', event)">
                                <input type="radio" name="transfer_type" value="member" class="d-none">
                                <div class="icon-circle bg-success bg-opacity-10 text-success mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fas fa-users fa-lg"></i>
                                </div>
                                <h6 class="mb-2">Another Member</h6>
                                <p class="small text-muted mb-0">Send instantly to another credit union member.</p>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="transfer-type-card h-100 p-4 border rounded-3 text-center d-flex flex-column align-items-center cursor-pointer radio-label" onclick="selectType('external', event)">
                                <input type="radio" name="transfer_type" value="external" class="d-none">
                                <div class="icon-circle bg-info bg-opacity-10 text-info mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fas fa-university fa-lg"></i>
                                </div>
                                <h6 class="mb-2">External Bank</h6>
                                <p class="small text-muted mb-0">Send via ACH or Wire to another bank.</p>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Core Details -->
                <div class="wizard-step p-5 d-none" id="step2">
                    <div class="d-flex align-items-center mb-4 cursor-pointer" onclick="goToStep(1)">
                        <i class="fas fa-arrow-left me-2 text-muted"></i> <span class="text-muted small">Back to Type</span>
                    </div>

                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label small text-uppercase text-muted">From Account</label>
                            <select name="wallet_type" class="form-select form-select-lg border-2 shadow-none" id="walletSelect" onchange="updateAccountOptions()">
                                @foreach($wallets as $wallet)
                                    <option value="{{ $wallet->currency->code }}" data-type="checking" data-balance="{{ $wallet->balance }}">
                                        Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ $wallet->currency->symbol . $wallet->balance }}
                                    </option>
                                @endforeach
                                <option value="primary_savings" data-type="savings" data-balance="{{ auth()->user()->savings_balance }}">
                                    Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S) - {{ setting('site_currency') }} {{ auth()->user()->savings_balance }}
                                </option>
                            </select>
                        </div>

                        <!-- Dynamic 'To' Recipient Section -->
                        <div class="col-12" id="toRecipientSection">
                            <!-- Case: Self -->
                            <div class="dynamic-field d-none" id="field-self">
                                <label class="form-label small text-uppercase text-muted">To Account</label>
                                <select name="to_wallet" class="form-select form-select-lg border-2 shadow-none" id="toWalletSelect">
                                    <!-- Populated by JS -->
                                </select>
                            </div>

                            <!-- Case: Member -->
                            <div class="dynamic-field d-none" id="field-member">
                                <label class="form-label small text-uppercase text-muted">Recipient Email or Account</label>
                                <input type="text" name="member_identifier" id="member_identifier" class="form-control form-control-lg border-2 shadow-none" placeholder="Enter recipient info">
                            </div>

                            <!-- Case: External -->
                            <div class="dynamic-field d-none" id="field-external">
                                <label class="form-label small text-uppercase text-muted">Recipient Bank</label>
                                <select name="bank_id" class="form-select form-select-lg border-2 shadow-none" id="bankId">
                                    <option value="" selected disabled>Select Destination Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-uppercase text-muted">Amount</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-2 border-end-0">$</span>
                                <input type="number" step="0.01" class="form-control border-2 border-start-0 shadow-none" id="amount" name="amount" placeholder="0.00" oninput="validateBalance()">
                            </div>
                            <div class="small mt-1" id="balanceFeedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-uppercase text-muted">Frequency</label>
                            <select name="frequency" class="form-select form-select-lg border-2 shadow-none" onchange="toggleDateField()">
                                <option value="once">One-time Transfer</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="dateField">
                            <label class="form-label small text-uppercase text-muted">Scheduled Date</label>
                            <input type="date" name="scheduled_at" class="form-control form-control-lg border-2 shadow-none" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small text-uppercase text-muted">Purpose / Memo</label>
                            <input type="text" name="purpose" id="transferPurpose" class="form-control form-control-lg border-2 shadow-none" placeholder="e.g. Rent, Grocery, Personal">
                        </div>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="button" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm" onclick="validateStep2()">
                            Continue <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Account Details (External Specific) -->
                <div class="wizard-step p-5 d-none" id="step3">
                    <div class="d-flex align-items-center mb-4 cursor-pointer" onclick="goToStep(2)">
                        <i class="fas fa-arrow-left me-2 text-muted"></i> <span class="text-muted small">Back to Base Details</span>
                    </div>

                    <h4 class="mb-4">Recipient Information</h4>
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label small text-uppercase text-muted">Full Name</label>
                            <input type="text" name="manual_data[account_name]" class="form-control form-control-lg border-2 shadow-none" placeholder="Recipient's legal name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-uppercase text-muted">Routing Number</label>
                            <div class="input-group">
                                <input type="password" name="manual_data[routing_number]" class="form-control form-control-lg border-2 shadow-none toggle-password" placeholder="9 digits" maxlength="9">
                                <button class="btn btn-outline-secondary border-2 border-start-0 bg-white" type="button" onclick="toggleVisibility(this)">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-uppercase text-muted">Account Number</label>
                            <div class="input-group">
                                <input type="password" name="manual_data[account_number]" id="ext_acc_num" class="form-control form-control-lg border-2 shadow-none toggle-password" placeholder="Checking/Savings Account #">
                                <button class="btn btn-outline-secondary border-2 border-start-0 bg-white" type="button" onclick="toggleVisibility(this)">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-uppercase text-muted">Confirm Account Number</label>
                            <div class="input-group">
                                <input type="password" id="ext_acc_num_confirm" class="form-control form-control-lg border-2 shadow-none toggle-password" placeholder="Re-enter to confirm">
                                <button class="btn btn-outline-secondary border-2 border-start-0 bg-white" type="button" onclick="toggleVisibility(this)">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="button" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm" onclick="validateStep3()">
                            Review Transfer <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 4: Review -->
                <div class="wizard-step p-5 d-none" id="step4">
                    <div class="d-flex align-items-center mb-4 cursor-pointer" onclick="goBackFromReview()">
                        <i class="fas fa-arrow-left me-2 text-muted"></i> <span class="text-muted small">Edit Details</span>
                    </div>
                    
                    <h4 class="mb-4 text-center">Transfer Review</h4>
                    
                    <div class="card bg-white border-2 mb-4 shadow-sm" style="border-radius: 15px;">
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <span class="text-muted d-block small text-uppercase">Payment From</span>
                                    <span class="fw-bold" id="reviewFrom"></span>
                                </div>
                                <div class="col-md-6">
                                    <span class="text-muted d-block small text-uppercase">Transfer To</span>
                                    <span class="fw-bold" id="reviewTo"></span>
                                </div>
                                <div class="col-md-6">
                                    <span class="text-muted d-block small text-uppercase">Delivery Method</span>
                                    <span class="fw-bold text-capitalize" id="reviewType"></span>
                                </div>
                                <div class="col-md-6">
                                    <span class="text-muted d-block small text-uppercase">Frequency</span>
                                    <span class="fw-bold text-capitalize" id="reviewFreq"></span>
                                </div>
                                <div class="col-md-6">
                                    <span class="text-muted d-block small text-uppercase">Transfer Date</span>
                                    <span class="fw-bold" id="reviewDate"></span>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted d-block small text-uppercase">Purpose</span>
                                    <span class="fw-bold" id="reviewPurpose"></span>
                                </div>
                                
                                <div class="col-12 external-review-details d-none">
                                    <hr class="my-3">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <span class="text-muted d-block small text-uppercase">Routing Number</span>
                                            <span class="fw-bold" id="reviewRouting"></span>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <span class="text-muted d-block small text-uppercase">Account Number</span>
                                            <span class="fw-bold" id="reviewAccount"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="h5 mb-0 d-block">Total to Send</span>
                                    <span class="small text-muted">Funds will be available shortly after approval.</span>
                                </div>
                                <span class="h2 mb-0 text-primary" id="reviewAmount"></span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm w-100 fs-5 fw-bold mt-2" id="confirmBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <i class="fas fa-shield-alt me-2"></i> Submit Transfer
                    </button>
                    
                    <div class="text-center mt-3">
                        <p class="small text-muted mb-0">By clicking submit, you authorize Pinellas FCU to initiate this transfer.</p>
                    </div>
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
    var currentStep = 1;
    var transferType = null;

    function selectType(type, e) {
        transferType = type;
        const card = e.currentTarget;
        const radio = card.querySelector('input[type="radio"]');
        if(radio) radio.checked = true;

        // Visual feedback
        document.querySelectorAll('.transfer-type-card').forEach(el => el.classList.remove('border-primary'));
        card.classList.add('border-primary');
        
        setTimeout(() => {
            goToStep(2);
        }, 300);
    }

    function toggleVisibility(btn) {
        const input = btn.parentElement.querySelector('input');
        const icon = btn.querySelector('i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        } else {
            input.type = "password";
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        }
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
            const stepNum = index + 1;
            el.classList.remove('active', 'text-primary');
            if (stepNum === step) {
                el.classList.add('active', 'text-primary');
            } else if (stepNum < step) {
                el.classList.add('text-success'); // Completed look
            }
        });
    }

    function setupStep2() {
        // Hide all dynamic fields
        document.querySelectorAll('.dynamic-field').forEach(el => el.classList.add('d-none'));
        
        // Show relevant field
        document.getElementById('field-' + transferType).classList.remove('d-none');
        
        // Reset and Update
        updateAccountOptions();
        toggleDateField(); // Initial check
    }

    function toggleDateField() {
        // Option to hide date for daily etc, but usually we just want to know when it starts
        // We'll keep it simple: date can be used as 'Start Date' for recurring.
    }

    function updateAccountOptions() {
        const fromSelect = document.getElementById('walletSelect');
        const toSection = document.getElementById('field-self');
        const toSelect = document.getElementById('toWalletSelect');
        const selectedVal = fromSelect.value;
        const selectedType = fromSelect.options[fromSelect.selectedIndex].getAttribute('data-type');

        if(transferType === 'self') {
            toSelect.innerHTML = '';
            // If from Checking, to must be Savings
            if(selectedType === 'checking') {
                const opt = document.createElement('option');
                opt.value = 'primary_savings';
                opt.text = 'Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S) - {{ setting("site_currency") }} {{ auth()->user()->savings_balance }}';
                toSelect.add(opt);
            } else {
                // If from Savings, to must be one of the Checking accounts
                @foreach($wallets as $wallet)
                    var opt = document.createElement('option');
                    opt.value = '{{ $wallet->currency->code }}';
                    opt.text = 'Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ $wallet->currency->symbol . $wallet->balance }}';
                    toSelect.add(opt);
                @endforeach
            }
        }
        validateBalance();
    }

    function validateBalance() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const fromSelect = document.getElementById('walletSelect');
        const balance = parseFloat(fromSelect.options[fromSelect.selectedIndex].getAttribute('data-balance'));
        const feedback = document.getElementById('balanceFeedback');

        if (amount > balance) {
            feedback.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Insufficient funds.';
            feedback.className = 'small mt-1 text-danger';
            return false;
        } else if (amount > 0) {
            feedback.innerHTML = '<i class="fas fa-check-circle me-1"></i> Amount is within balance.';
            feedback.className = 'small mt-1 text-success';
        } else {
            feedback.innerHTML = '';
        }
        return true;
    }

    function validateStep2() {
        const amount = document.getElementById('amount').value;
        if(!amount || amount <= 0) {
            Swal.fire('Error', 'Please enter a valid amount.', 'error');
            return;
        }
        if(!validateBalance()) {
            Swal.fire('Insufficient Funds', 'You do not have enough funds in the selected account.', 'warning');
            return;
        }

        if(transferType === 'member' && !document.getElementById('member_identifier').value) {
            Swal.fire('Missing Information', 'Please enter the recipient\'s email or account number.', 'warning');
            return;
        }

        if(transferType === 'external') {
            if(!document.getElementById('bankId').value) {
                Swal.fire('Select Bank', 'Please select a destination bank.', 'warning');
                return;
            }
            goToStep(3);
        } else {
            populateReview();
            goToStep(4);
        }
    }

    function validateStep3() {
        const name = document.querySelector('input[name="manual_data[account_name]"]').value;
        const acc1 = document.getElementById('ext_acc_num').value;
        const acc2 = document.getElementById('ext_acc_num_confirm').value;
        const routing = document.querySelector('input[name="manual_data[routing_number]"]').value;

        if(!name || !acc1 || !routing) {
            Swal.fire('Missing Information', 'Please fill in all recipient details.', 'warning');
            return;
        }
        if(acc1 !== acc2) {
            Swal.fire('Mismatch', 'Account numbers do not match.', 'error');
            return;
        }
        if(routing.length < 9) {
            Swal.fire('Invalid Routing', 'Routing number must be 9 digits.', 'warning');
            return;
        }

        populateReview();
        goToStep(4);
    }

    function goBackFromReview() {
        if(transferType === 'external') goToStep(3);
        else goToStep(2);
    }

    function populateReview() {
        document.getElementById('reviewType').innerText = transferType === 'self' ? 'Intra-Account' : (transferType === 'member' ? 'Member Transfer' : 'External ACH/Wire');
        
        const fromSelect = document.getElementById('walletSelect');
        document.getElementById('reviewFrom').innerText = fromSelect.options[fromSelect.selectedIndex].text.split(' - ')[0];
        
        let toText = '';
        const extReview = document.querySelector('.external-review-details');
        extReview.classList.add('d-none');

        if(transferType === 'self') {
            const toSelect = document.getElementById('toWalletSelect');
            toText = toSelect.options[toSelect.selectedIndex].text.split(' - ')[0];
        } else if(transferType === 'member') {
            toText = 'Member: ' + document.getElementById('member_identifier').value;
        } else {
            const bankSelect = document.getElementById('bankId');
            toText = bankSelect.options[bankSelect.selectedIndex].text;
            extReview.classList.remove('d-none');
            document.getElementById('reviewRouting').innerText = '****' + document.querySelector('input[name="manual_data[routing_number]"]').value.slice(-4);
            document.getElementById('reviewAccount').innerText = '****' + document.getElementById('ext_acc_num').value.slice(-4);
        }
        document.getElementById('reviewTo').innerText = toText;
        document.getElementById('reviewFreq').innerText = document.querySelector('select[name="frequency"]').value;
        document.getElementById('reviewDate').innerText = document.querySelector('input[name="scheduled_at"]').value;
        document.getElementById('reviewPurpose').innerText = document.getElementById('transferPurpose').value || 'N/A';
        document.getElementById('reviewAmount').innerText = '$' + document.getElementById('amount').value;
    }

    // Form submission handling
    document.getElementById('transferForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('confirmBtn');
        btn.disabled = true;
        btn.querySelector('.spinner-border').classList.remove('d-none');
        btn.querySelector('i').classList.add('d-none');
    });

</script>
@endsection
