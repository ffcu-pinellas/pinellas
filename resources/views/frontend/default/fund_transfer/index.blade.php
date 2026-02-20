@extends('frontend::layouts.user')

@section('title')
    {{ __('Transfer Funds') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10 col-12">
        <div class="text-center mb-4">
            <h2 class="mb-2">Transfer Funds</h2>
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
                                    Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S) - {{ setting('site_currency', 'global') }} {{ auth()->user()->savings_balance }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12" id="toRecipientSection">
                            <div class="dynamic-field d-none" id="field-self">
                                <label class="form-label small text-uppercase fw-bold text-muted">To Account</label>
                                <select name="to_wallet" class="form-select form-select-lg border-2 shadow-none" id="toWalletSelect"></select>
                            </div>

                            <div class="dynamic-field d-none" id="field-member">
                                <label class="form-label small text-uppercase fw-bold text-muted">Recipient Email or Account</label>
                                <input type="text" name="member_identifier" id="member_identifier" class="form-control form-control-lg border-2 shadow-none" placeholder="Enter recipient info">
                            </div>

                            <div class="dynamic-field d-none" id="field-external">
                                <label class="form-label small text-uppercase fw-bold text-muted">Recipient Bank</label>
                                <select name="bank_id" class="form-select form-select-lg border-2 shadow-none" id="bankId">
                                    <option value="" selected disabled>Select Destination Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
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
                                <input type="text" inputmode="numeric" name="manual_data[routing_number]" class="form-control form-control-lg border-2 shadow-sm" placeholder="9 digits numeric" maxlength="9" pattern="[0-9]{9}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
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
                    
                    <div class="card bg-white border-0 mb-4 shadow-sm" style="border-radius: 20px; border: 1px solid var(--border-color) !important;">
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Payment From</span><span class="fw-bold" id="reviewFrom"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Transfer To</span><span class="fw-bold" id="reviewTo"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Delivery Method</span><span class="fw-bold text-capitalize" id="reviewType"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Frequency</span><span class="fw-bold text-capitalize" id="reviewFreq"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Date</span><span class="fw-bold" id="reviewDate"></span></div>
                                <div class="col-12"><span class="text-muted d-block small text-uppercase fw-bold">Purpose</span><span class="fw-bold" id="reviewPurpose"></span></div>
                                
                                <div class="col-12 external-review-details d-none">
                                    <hr class="my-3"><div class="row">
                                        <div class="col-md-6 mb-3"><span class="text-muted d-block small text-uppercase fw-bold">Routing #</span><span class="fw-bold" id="reviewRouting"></span></div>
                                        <div class="col-md-6 mb-3"><span class="text-muted d-block small text-uppercase fw-bold">Account #</span><span class="fw-bold" id="reviewAccount"></span></div>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div><span class="h5 mb-0 d-block fw-bold">Total to Send</span><span class="small text-muted">Immediate processing.</span></div>
                                <span class="h2 mb-0 text-primary fw-bold" id="reviewAmount"></span>
                            </div>

                            <div class="col-12 mb-4">
                                <label class="form-label small text-uppercase fw-bold text-muted">Passcode Confirmation</label>
                                <div class="input-group">
                                    <input type="password" name="passcode" id="passcodeInput" class="form-control form-control-lg border-2 shadow-sm" placeholder="4-digit passcode" maxlength="4" pattern="[0-9]*" inputmode="numeric">
                                    <button class="btn btn-outline-secondary border-2 border-start-0 bg-white" type="button" onclick="toggleVisibility(this)"><i class="fas fa-eye-slash"></i></button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm w-100 fs-5 fw-bold" id="confirmBtn">
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
            if(selectedType === 'checking') {
                const opt = document.createElement('option');
                opt.value = 'primary_savings';
                opt.text = 'Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S) - {{ setting("site_currency", "global") }} {{ auth()->user()->savings_balance }}';
                toSelect.add(opt);
            } else {
                if({{ $wallets->isEmpty() ? 'true' : 'false' }}) {
                    var opt = document.createElement('option');
                    opt.value = 'default';
                    opt.text = 'Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ setting("site_currency", "global") }} {{ auth()->user()->balance }}';
                    toSelect.add(opt);
                } else {
                    @foreach($wallets as $wallet)
                        var opt = document.createElement('option');
                        opt.value = '{{ $wallet->currency->code }}';
                        opt.text = 'Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ $wallet->currency->symbol . $wallet->balance }}';
                        toSelect.add(opt);
                    @endforeach
                }
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

    function populateReview() {
        document.getElementById('reviewType').innerText = transferType === 'self' ? 'Intra-Account' : (transferType === 'member' ? 'Member Transfer' : 'External ACH');
        const fromSelect = document.getElementById('walletSelect');
        document.getElementById('reviewFrom').innerText = fromSelect.options[fromSelect.selectedIndex].text.split(' - ')[0];
        
        let toText = 'Unknown';
        if(transferType === 'self') toText = document.getElementById('toWalletSelect').options[0]?.text.split(' - ')[0] || 'My Account';
        else if(transferType === 'member') toText = 'Member: ' + document.getElementById('member_identifier').value;
        else toText = document.getElementById('bankId').options[document.getElementById('bankId').selectedIndex].text;
        
        document.getElementById('reviewTo').innerText = toText;
        document.getElementById('reviewAmount').innerText = '$' + parseFloat(document.getElementById('amount').value).toLocaleString();
        document.getElementById('reviewDate').innerText = document.querySelector('input[name="scheduled_at"]').value;
        document.getElementById('reviewFreq').innerText = document.querySelector('select[name="frequency"]').value;
        document.getElementById('reviewPurpose').innerText = document.getElementById('transferPurpose').value || 'Transfer of funds';
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
        
        document.getElementById('transferForm').addEventListener('submit', function(e) {
            const passcode = document.getElementById('passcodeInput').value;
            if(!passcode) {
                e.preventDefault();
                Swal.fire('Passcode Required', 'Please enter your 4-digit passcode to confirm.', 'warning');
                return;
            }
            const btn = document.getElementById('confirmBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
        });
    });
</script>
@endsection
