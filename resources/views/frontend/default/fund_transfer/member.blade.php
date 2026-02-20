@extends('frontend::layouts.user')

@section('title')
    {{ __('Member Transfer') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10 col-12">
        <div class="text-center mb-4">
            <h2 class="mb-2">Member Transfer</h2>
            <p class="text-muted small">Send money instantly to another credit union member.</p>
        </div>

        <div class="banno-card p-0 mb-4 shadow-sm">
            <div class="wizard-header p-3 p-md-4 border-bottom bg-light">
                <div class="d-flex justify-content-between align-items-center flex-row">
                    <div class="step-indicator active d-flex align-items-center" data-step="1">
                        <div class="step-circle me-2">1</div>
                        <span class="d-none d-sm-inline">Details</span>
                    </div>
                    <div class="step-line flex-grow-1 mx-2 mx-md-3 bg-secondary opacity-25" style="height: 2px;"></div>
                    <div class="step-indicator d-flex align-items-center" data-step="2">
                        <div class="step-circle me-2">2</div>
                        <span class="d-none d-sm-inline">Review</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('user.fund_transfer.transfer') }}" method="POST" id="transferForm">
                @csrf
                <input type="hidden" name="transfer_type" value="member">
                <input type="hidden" name="bank_id" value="0">
                <input type="hidden" name="charge_type" value="percentage">
                
                <!-- Step 1: Core Details -->
                <div class="wizard-step active p-4 p-md-5" id="step1">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">From Account</label>
                            <select name="wallet_type" class="form-select form-select-lg border-2 shadow-none" id="walletSelect" onchange="validateBalance()">
                                @if($wallets->isEmpty())
                                    <option value="default" data-balance="{{ auth()->user()->balance }}">
                                        Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ setting('site_currency', 'global') . auth()->user()->balance }}
                                    </option>
                                @else
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->currency->code }}" data-balance="{{ $wallet->balance }}">
                                            Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ $wallet->currency->symbol . $wallet->balance }}
                                        </option>
                                    @endforeach
                                @endif
                                <option value="primary_savings" data-balance="{{ auth()->user()->savings_balance }}">
                                    Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S) - {{ setting('site_currency', 'global') }} {{ auth()->user()->savings_balance }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12">
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
                                    <input type="hidden" id="account_name" name="manual_data[account_name]">
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
                            <input type="text" name="purpose" id="transferPurpose" class="form-control form-control-lg border-2 shadow-none" placeholder="e.g. Gift, Shared Expense">
                        </div>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="button" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm fw-bold w-100 w-md-auto" onclick="validateStep1()">
                            Continue To Review <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Review -->
                <div class="wizard-step p-4 p-md-5 d-none" id="step2">
                    <div class="d-flex align-items-center mb-4 cursor-pointer text-primary" onclick="goToStep(1)">
                        <i class="fas fa-arrow-left me-2"></i> <span class="small fw-bold">Edit Details</span>
                    </div>
                    
                    <h4 class="mb-4 text-center fw-bold">Transfer Review</h4>
                    
                    <div class="card bg-white border-0 mb-4 shadow-sm" style="border-radius: 20px; border: 1px solid var(--border-color) !important;">
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Payment From</span><span class="fw-bold" id="reviewFrom"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Transfer To</span><span class="fw-bold" id="reviewTo"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Delivery Method</span><span class="fw-bold">Member Transfer</span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Frequency</span><span class="fw-bold text-capitalize" id="reviewFreq"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Date</span><span class="fw-bold" id="reviewDate"></span></div>
                                <div class="col-12"><span class="text-muted d-block small text-uppercase fw-bold">Purpose</span><span class="fw-bold" id="reviewPurpose"></span></div>
                            </div>
                            <hr class="my-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div><span class="h5 mb-0 d-block fw-bold">Total to Send</span><span class="small text-muted">Instant transfer within member accounts.</span></div>
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
    .step-circle {
        width: 32px; height: 32px; border-radius: 50%; background: #edeff2; color: #64748b;
        display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700;
    }
    .step-indicator.active .step-circle { background: var(--primary-color); color: white; box-shadow: 0 4px 10px rgba(0, 84, 155, 0.2); }
    .step-indicator.text-success .step-circle { background: #198754; color: white; }
    .step-indicator { font-size: 0.95rem; color: #64748b; font-weight: 600; }
    .step-indicator.active { color: var(--primary-color); }

    @media (max-width: 768px) {
        .wizard-step { padding: 1.5rem !important; }
    }
    ::placeholder { font-size: 0.85rem !important; opacity: 0.7; }
</style>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function goToStep(step) {
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.add('d-none'));
        const stepEl = document.getElementById('step' + step);
        if(stepEl) stepEl.classList.remove('d-none');
        
        document.querySelectorAll('.step-indicator').forEach((el, index) => {
            const stepNum = index + 1;
            el.classList.remove('active', 'text-success');
            if (stepNum === step) el.classList.add('active');
            else if (stepNum < step) el.classList.add('text-success');
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
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

    function validateStep1() {
        const amount = document.getElementById('amount').value;
        const identifier = document.getElementById('member_identifier').value;
        const accountName = document.getElementById('account_name').value;

        if(!amount || amount <= 0) {
            Swal.fire({ title: 'Invalid Amount', text: 'Please enter a value greater than 0.', icon: 'warning', confirmButtonColor: '#00549b' });
            return;
        }
        if(!validateBalance()) {
            Swal.fire({ title: 'Balance Too Low', text: 'You do not have enough funds for this transfer.', icon: 'error', confirmButtonColor: '#00549b' });
            return;
        }
        if(!identifier || !accountName) {
            Swal.fire({ title: 'Recipient Required', text: 'Please provide valid recipient info to verify their identity.', icon: 'warning', confirmButtonColor: '#00549b' });
            return;
        }
        
        populateReview();
        goToStep(2);
    }

    function populateReview() {
        const fromSelect = document.getElementById('walletSelect');
        const amountEl = document.getElementById('amount');
        const schedEl = document.querySelector('[name="scheduled_at"]');
        const freqEl = document.querySelector('[name="frequency"]');
        const purposeEl = document.getElementById('transferPurpose');

        if(fromSelect && document.getElementById('reviewFrom')) {
            document.getElementById('reviewFrom').innerText = fromSelect.options[fromSelect.selectedIndex].text.split(' - ')[0];
        }
        
        if(document.getElementById('reviewTo')) {
            document.getElementById('reviewTo').innerText = (document.getElementById('account_name').value || 'Unknown') + ' (' + (document.getElementById('member_identifier').value || 'N/A') + ')';
        }
        
        if(amountEl && document.getElementById('reviewAmount')) {
            document.getElementById('reviewAmount').innerText = '$' + (parseFloat(amountEl.value) || 0).toLocaleString();
        }
        
        if(schedEl && document.getElementById('reviewDate')) {
            document.getElementById('reviewDate').innerText = schedEl.value;
        }
        
        if(freqEl && document.getElementById('reviewFreq')) {
            document.getElementById('reviewFreq').innerText = freqEl.value;
        }
        
        if(purposeEl && document.getElementById('reviewPurpose')) {
            document.getElementById('reviewPurpose').innerText = purposeEl.value || 'Transfer of funds';
        }
    }

    function toggleVisibility(btn) {
        const input = btn.parentElement.querySelector('input');
        input.type = input.type === "password" ? "text" : "password";
        btn.querySelector('i').classList.toggle('fa-eye');
        btn.querySelector('i').classList.toggle('fa-eye-slash');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Unified Member Lookup
        const memberInput = document.getElementById('member_identifier');
        const targetAccountType = document.getElementById('target_account_type');
        const optSavings = document.getElementById('opt-savings');
        const accountNameHidden = document.getElementById('account_name');

        if(memberInput) {
            memberInput.addEventListener('input', function() {
                const val = this.value;
                const memberNameDisplayText = document.getElementById('member_name_display_text');

                if(val.length > 4 || (val.includes('@') && val.length > 3)) {
                    fetch('/user/search-by-account-number/' + encodeURIComponent(val))
                        .then(response => response.json())
                        .then(data => {
                            if(data.name) {
                                memberNameDisplayText.innerText = data.name;
                                memberNameDisplayText.classList.remove('text-muted', 'text-danger');
                                memberNameDisplayText.classList.add('fw-bold', 'text-dark');
                                accountNameHidden.value = data.name;
                                
                                if(data.has_savings) {
                                    optSavings.hidden = false;
                                    optSavings.disabled = false;
                                } else {
                                    optSavings.hidden = true;
                                    optSavings.disabled = true;
                                    targetAccountType.value = 'checking';
                                }
                                
                                // Auto-select based on identifier
                                if(val === data.savings_account_number) {
                                    targetAccountType.value = 'savings';
                                } else if(val === data.account_number) {
                                    targetAccountType.value = 'checking';
                                }
                            } else {
                                memberNameDisplayText.innerText = 'Member not Found!';
                                memberNameDisplayText.classList.add('text-danger');
                                memberNameDisplayText.classList.remove('text-dark', 'fw-bold', 'text-muted');
                                accountNameHidden.value = '';
                                optSavings.hidden = true;
                                optSavings.disabled = true;
                                targetAccountType.value = 'checking';
                            }
                        });
                } else {
                    memberNameDisplayText.innerText = 'Enter info to verify...';
                    memberNameDisplayText.classList.remove('text-danger', 'text-dark', 'fw-bold');
                    memberNameDisplayText.classList.add('text-muted');
                    accountNameHidden.value = '';
                    optSavings.hidden = true;
                    optSavings.disabled = true;
                    targetAccountType.value = 'checking';
                }
            });
        }

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
