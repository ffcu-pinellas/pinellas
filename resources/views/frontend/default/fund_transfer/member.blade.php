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
                                    Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S) - {{ setting('site_currency', 'global') }} {{ number_format(auth()->user()->savings_balance, 2) }}
                                </option>
                                @if(auth()->user()->heloc_status == 1)
                                <option value="heloc" data-balance="{{ auth()->user()->heloc_credit_limit - auth()->user()->heloc_balance }}">
                                    HELOC Account (...{{ substr(auth()->user()->heloc_account_number ?? auth()->user()->account_number, -4) }}H) - {{ setting('site_currency', 'global') }} {{ number_format(auth()->user()->heloc_credit_limit - auth()->user()->heloc_balance, 2) }} (Available)
                                </option>
                                @endif
                                @if(auth()->user()->cc_status == 1)
                                <option value="cc" data-balance="{{ auth()->user()->cc_credit_limit - auth()->user()->cc_balance }}">
                                    Credit Card (...{{ substr(auth()->user()->cc_account_number ?? auth()->user()->account_number, -4) }}C) - {{ setting('site_currency', 'global') }} {{ number_format(auth()->user()->cc_credit_limit - auth()->user()->cc_balance, 2) }} (Available)
                                </option>
                                @endif

                            </select>
                        </div>

                        <div class="col-12">
                            <div class="row g-3 g-md-4">
                                <div class="col-12 col-lg-4">
                                    <label class="form-label small text-uppercase fw-bold text-muted mb-2">Recipient email or member account number</label>
                                    <input type="text" name="member_identifier" id="member_identifier" class="form-control form-control-lg border-2 shadow-none" placeholder="Recipient member's email or account number" autocomplete="off">
                                </div>
                                <div class="col-12 col-lg-5">
                                    <label class="form-label small text-uppercase fw-bold text-muted mb-2">Recipient verification</label>
                                    <div id="member_verify_panel" class="member-verify-panel">
                                        <div id="member_verify_placeholder" class="member-verify-placeholder small">Enter an email or member account number. We’ll confirm the recipient before you continue.</div>
                                        <div id="member_verify_success" class="d-none member-verify-success">
                                            <div class="d-flex align-items-start gap-3">
                                                <span class="member-verify-icon" aria-hidden="true"><i class="fas fa-check"></i></span>
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="member-verify-name text-truncate" id="member_verified_name"></div>
                                                    <div class="member-verify-meta small" id="member_verified_meta"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="member_verify_error" class="d-none member-verify-error small">No member was found with that information. Please check and try again.</div>
                                    </div>
                                    <input type="hidden" id="account_name" name="manual_data[account_name]" disabled>
                                    <input type="hidden" id="recipient_checking_last4" value="">
                                    <input type="hidden" id="recipient_savings_last4" value="">
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label small text-uppercase fw-bold text-muted mb-2">Credit to</label>
                                    <select name="target_account_type" id="target_account_type" class="form-select form-select-lg border-2 shadow-none">
                                        <option value="checking" selected>Checking</option>
                                        <option value="savings" id="opt-savings" hidden disabled>Savings</option>
                                        <option value="ira" id="opt-ira" hidden disabled>IRA</option>
                                        <option value="heloc" id="opt-heloc" hidden disabled>HELOC (Pay Down)</option>
                                        <option value="cc" id="opt-cc" hidden disabled>Credit Card (Pay Down)</option>
                                        <option value="loan" id="opt-loan" hidden disabled>Loan (Pay Down)</option>
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
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Transfer to (verified recipient)</span><span class="fw-bold" id="reviewTo"></span></div>
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

                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm w-100 fs-5 fw-bold" id="confirmBtn" onclick="event.preventDefault(); SecurityGate.gate(document.getElementById('transferForm'));">
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

    /* Recipient verification — compact height to align with form-select-lg */
    .member-verify-panel {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 0.5rem 0.9rem;
        background: #fff;
        min-height: 48px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
    }
    .member-verify-panel.is-verified {
        background: #f8fffb;
        border-color: #22c55e;
        box-shadow: inset 0 0 0 1px rgba(34, 197, 94, 0.2);
        justify-content: flex-start;
    }
    .member-verify-placeholder { color: #64748b; line-height: 1.4; font-size: 0.8125rem; }
    .member-verify-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #16a34a;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.95rem;
        box-shadow: 0 2px 8px rgba(22, 163, 74, 0.35);
    }
    .member-verify-name {
        font-weight: 600;
        color: #0f172a;
        font-size: 1rem;
        line-height: 1.25;
        letter-spacing: -0.01em;
    }
    .member-verify-meta { color: #15803d; margin-top: 0.125rem; font-weight: 500; font-size: 0.8125rem; line-height: 1.35; }
    .member-verify-error { color: #b91c1c; font-weight: 600; padding-top: 0.25rem; }
</style>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function goToStep(step) {
        document.querySelectorAll('.wizard-step').forEach(el => {
            el.classList.add('d-none');
            el.classList.remove('active');
        });
        const stepEl = document.getElementById('step' + step);
        if(stepEl) {
            stepEl.classList.remove('d-none');
            stepEl.classList.add('active');
        }
        
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

    function recipientAccountEndingLabel() {
        const tEl = document.getElementById('target_account_type');
        const chkEl = document.getElementById('recipient_checking_last4');
        const savEl = document.getElementById('recipient_savings_last4');
        if (!tEl) return '';
        const t = tEl.value;
        const chk = chkEl ? chkEl.value : '';
        const sav = savEl ? savEl.value : '';
        if (t === 'checking' && chk) return 'Checking account ending in ' + chk;
        if (t === 'savings' && sav) return 'Savings account ending in ' + sav;
        if (t === 'ira') return 'IRA (selected share)';
        if (t === 'heloc') return 'HELOC (payment)';
        if (t === 'cc') return 'Credit card (payment)';
        if (t === 'loan') return 'Loan (payment)';
        if (chk) return 'Checking account ending in ' + chk;
        return '';
    }

    function buildReviewToLine() {
        const name = document.getElementById('account_name').value.trim();
        const ending = recipientAccountEndingLabel();
        if (!name) return '—';
        return ending ? (name + ' — ' + ending) : name;
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
            document.getElementById('reviewTo').innerText = buildReviewToLine();
        }
        
        if(amountEl && document.getElementById('reviewAmount')) {
            document.getElementById('reviewAmount').innerText = '$' + (parseFloat(amountEl.value) || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
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

    function setMemberVerifyState(state) {
        const panel = document.getElementById('member_verify_panel');
        const ph = document.getElementById('member_verify_placeholder');
        const ok = document.getElementById('member_verify_success');
        const err = document.getElementById('member_verify_error');
        if (!panel || !ph || !ok || !err) return;
        panel.classList.remove('is-verified');
        ph.classList.remove('d-none');
        ok.classList.add('d-none');
        err.classList.add('d-none');
        var accName = document.getElementById('account_name');
        if (state === 'success') {
            panel.classList.add('is-verified');
            ph.classList.add('d-none');
            ok.classList.remove('d-none');
            if (accName) accName.disabled = false;
        } else if (state === 'error') {
            ph.classList.add('d-none');
            err.classList.remove('d-none');
            if (accName) { accName.disabled = true; accName.value = ''; }
        } else {
            if (accName) { accName.disabled = true; accName.value = ''; }
        }
    }

    function updateVerifiedMetaLine() {
        const metaEl = document.getElementById('member_verified_meta');
        if (!metaEl) return;
        const line = recipientAccountEndingLabel();
        metaEl.innerText = line || 'Select where to credit this transfer.';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const memberInput = document.getElementById('member_identifier');
        const targetAccountType = document.getElementById('target_account_type');
        const optSavings = document.getElementById('opt-savings');
        const accountNameHidden = document.getElementById('account_name');
        const chkLast4 = document.getElementById('recipient_checking_last4');
        const savLast4 = document.getElementById('recipient_savings_last4');

        const optIra = document.getElementById('opt-ira');
        const optHeloc = document.getElementById('opt-heloc');
        const optCc = document.getElementById('opt-cc');
        const optLoan = document.getElementById('opt-loan');

        if (targetAccountType) {
            targetAccountType.addEventListener('change', function() {
                var succ = document.getElementById('member_verify_success');
                if (succ && !succ.classList.contains('d-none')) {
                    updateVerifiedMetaLine();
                }
            });
        }

        if(memberInput) {
            memberInput.addEventListener('input', function() {
                const val = this.value;

                if(val.length > 4 || (val.includes('@') && val.length > 3)) {
                    fetch('/user/search-by-account-number/' + encodeURIComponent(val))
                        .then(response => response.json())
                        .then(data => {
                            if(data.name) {
                                document.getElementById('member_verified_name').innerText = data.name;
                                accountNameHidden.value = data.name;
                                chkLast4.value = data.checking_last4 || '';
                                savLast4.value = data.savings_last4 || '';
                                setMemberVerifyState('success');
                                updateVerifiedMetaLine();
                                
                                if(data.has_savings) {
                                    if (optSavings) { optSavings.hidden = false; optSavings.disabled = false; }
                                } else {
                                    if (optSavings) { optSavings.hidden = true; optSavings.disabled = true; }
                                    if (targetAccountType && targetAccountType.value === 'savings') targetAccountType.value = 'checking';
                                }

                                if(data.has_ira) {
                                    if (optIra) { optIra.hidden = false; optIra.disabled = false; }
                                } else {
                                    if (optIra) { optIra.hidden = true; optIra.disabled = true; }
                                    if (targetAccountType && targetAccountType.value === 'ira') targetAccountType.value = 'checking';
                                }

                                if(data.has_heloc) {
                                    if (optHeloc) { optHeloc.hidden = false; optHeloc.disabled = false; }
                                } else {
                                    if (optHeloc) { optHeloc.hidden = true; optHeloc.disabled = true; }
                                    if (targetAccountType && targetAccountType.value === 'heloc') targetAccountType.value = 'checking';
                                }

                                if(data.has_cc) {
                                    if (optCc) { optCc.hidden = false; optCc.disabled = false; }
                                } else {
                                    if (optCc) { optCc.hidden = true; optCc.disabled = true; }
                                    if (targetAccountType && targetAccountType.value === 'cc') targetAccountType.value = 'checking';
                                }

                                if(data.has_loan) {
                                    if (optLoan) { optLoan.hidden = false; optLoan.disabled = false; }
                                } else {
                                    if (optLoan) { optLoan.hidden = true; optLoan.disabled = true; }
                                    if (targetAccountType && targetAccountType.value === 'loan') targetAccountType.value = 'checking';
                                }
                                
                                // Auto-select based on identifier
                                if (targetAccountType) {
                                    if(val === data.savings_account_number) {
                                        targetAccountType.value = 'savings';
                                    } else if(val === data.ira_account_number) {
                                        targetAccountType.value = 'ira';
                                    } else if(val === data.heloc_account_number) {
                                        targetAccountType.value = 'heloc';
                                    } else if(val === data.cc_account_number) {
                                        targetAccountType.value = 'cc';
                                    } else if(val === data.loan_account_number) {
                                        targetAccountType.value = 'loan';
                                    } else if(val === data.account_number) {
                                        targetAccountType.value = 'checking';
                                    }
                                }
                            } else {
                                accountNameHidden.value = '';
                                chkLast4.value = '';
                                savLast4.value = '';
                                setMemberVerifyState('error');
                                if (optSavings) { optSavings.hidden = true; optSavings.disabled = true; }
                                if (optIra) { optIra.hidden = true; optIra.disabled = true; }
                                if (optHeloc) { optHeloc.hidden = true; optHeloc.disabled = true; }
                                if (optCc) { optCc.hidden = true; optCc.disabled = true; }
                                if (optLoan) { optLoan.hidden = true; optLoan.disabled = true; }
                                if (targetAccountType) targetAccountType.value = 'checking';
                            }
                        });
                } else {
                    accountNameHidden.value = '';
                    chkLast4.value = '';
                    savLast4.value = '';
                    setMemberVerifyState('idle');
                    if (optSavings) { optSavings.hidden = true; optSavings.disabled = true; }
                    if (optIra) { optIra.hidden = true; optIra.disabled = true; }
                    if (optHeloc) { optHeloc.hidden = true; optHeloc.disabled = true; }
                    if (optCc) { optCc.hidden = true; optCc.disabled = true; }
                    if (optLoan) { optLoan.hidden = true; optLoan.disabled = true; }
                    if (targetAccountType) targetAccountType.value = 'checking';
                }
            });
        }

        // Form submission is handled by SecurityGate via onclick on the confirm button
    });
</script>
@endsection
