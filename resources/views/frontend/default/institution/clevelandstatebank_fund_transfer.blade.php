@extends('frontend::layouts.user')

@section('title')
{{ __('Move Money & Transfers') }}
@endsection

@section('content')
<script>
window.selectTransferType = window.selectTransferType || function(type, card) {
    if (typeof window.selectType === 'function') {
        window.selectType(type, card);
    } else {
        window._pendingTransferType = { type: type, card: card };
    }
};
</script>
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10 col-12">

        <!-- CSB Clean Banking Header -->
        <div class="card border-0 shadow-sm mb-3" style="background: #ffffff; border-radius: 14px; border-left: 5px solid #c8232c !important;">
            <div class="card-body py-3 px-3 px-md-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('user.dashboard') }}" class="btn btn-light border d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; border-radius: 8px;" aria-label="Back to Dashboard" title="Back to Dashboard">
                            <i class="fa-solid fa-arrow-left text-dark"></i>
                        </a>
                        <div>
                            <h2 class="fw-bold mb-0 text-dark" style="font-size: 1.25rem; letter-spacing: -0.3px;">Move Money &amp; Transfers</h2>
                            <div class="text-muted small" style="font-size: 12px;">Fast, secure transfers between your accounts, to other members, and external banks.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold" style="font-size: 11px;">
                            Member FDIC
                        </span>
                        <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold font-monospace" style="font-size: 11px;">
                            Routing # 084301768
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="banno-card p-0 mb-4 shadow-sm">
            <!-- Wizard Stepper Header -->
            <div class="wizard-header p-3 p-md-4 border-bottom bg-light">
                <div class="d-flex justify-content-between align-items-center flex-row">
                    <div class="step-indicator active d-flex align-items-center" data-step="1">
                        <div class="step-circle me-2">1</div>
                        <span class="d-none d-sm-inline fw-semibold">Transfer Type</span>
                    </div>
                    <div class="step-line flex-grow-1 mx-2 mx-md-3 bg-secondary opacity-25" style="height: 2px;"></div>
                    <div class="step-indicator d-flex align-items-center" data-step="2">
                        <div class="step-circle me-2">2</div>
                        <span class="d-none d-sm-inline fw-semibold">Details</span>
                    </div>
                    <div class="step-line flex-grow-1 mx-2 mx-md-3 bg-secondary opacity-25" style="height: 2px;"></div>
                    <div class="step-indicator d-flex align-items-center" data-step="3" id="stepIndicator3">
                        <div class="step-circle me-2">3</div>
                        <span class="d-none d-sm-inline fw-semibold" id="step3Label">Recipient</span>
                    </div>
                    <div class="step-line flex-grow-1 mx-2 mx-md-3 bg-secondary opacity-25" style="height: 2px;" id="stepLine3"></div>
                    <div class="step-indicator d-flex align-items-center" data-step="4" id="stepIndicator4">
                        <div class="step-circle me-2" id="stepCircle4">4</div>
                        <span class="d-none d-sm-inline fw-semibold">Review &amp; Authorize</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('user.fund_transfer.transfer') }}" method="POST" id="transferForm">
                @csrf
                <input type="hidden" name="charge_type" value="percentage">
                <input type="hidden" name="bank_id" id="resolvedBankId" value="0">
                
                <!-- ════════ Step 1: Transfer Type ════════ -->
                <div class="wizard-step active p-3 p-md-5" id="step1">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1">Select Transfer Method</h4>
                        <p class="text-muted small">Choose how you want to route funds securely.</p>
                    </div>

                    <div class="row g-3 g-md-4 justify-content-center">
                        <!-- Option 1: Internal -->
                        <div class="col-md-6 col-12">
                            <div class="transfer-type-card csb-method-tile js-type-select h-100 p-3 p-md-4 border rounded-3 d-flex align-items-start gap-3 cursor-pointer" data-type="self" onclick="window.selectTransferType('self', this)">
                                <input type="radio" name="transfer_type" value="self" class="d-none" checked>
                                <div class="csb-method-icon rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(22, 40, 70, 0.08); color: #162846; font-size: 20px;">
                                    <i class="fa-solid fa-wallet"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="fw-bold text-dark mb-0">Between My Accounts</h6>
                                        <span class="badge bg-success-subtle text-success small font-monospace" style="font-size: 10px;">Immediate</span>
                                    </div>
                                    <p class="small text-muted mb-0">Transfer funds between Checking, Savings, IRA, or Loan and Credit Card pay-downs.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Option 2: Member / Customer -->
                        <div class="col-md-6 col-12">
                            <div class="transfer-type-card csb-method-tile js-type-select h-100 p-3 p-md-4 border rounded-3 d-flex align-items-start gap-3 cursor-pointer" data-type="member" onclick="window.selectTransferType('member', this)">
                                <input type="radio" name="transfer_type" value="member" class="d-none">
                                <div class="csb-method-icon rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.08); color: #10b981; font-size: 20px;">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="fw-bold text-dark mb-0">Cleveland State Bank Customer</h6>
                                        <span class="badge bg-success-subtle text-success small font-monospace" style="font-size: 10px;">Instant</span>
                                    </div>
                                    <p class="small text-muted mb-0">Send instantly to another Cleveland State Bank account holder.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Option 3: Zelle -->
                        <div class="col-md-6 col-12">
                            <div class="transfer-type-card csb-method-tile h-100 p-3 p-md-4 border rounded-3 d-flex align-items-start gap-3 cursor-pointer" onclick="window.location.href='{{ route('user.fund_transfer.zelle') }}'">
                                <div class="csb-method-icon rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(116, 27, 107, 0.08); color: #741b6b; font-size: 20px;">
                                    <img src="{{ asset('assets/external/images/zelle small logo.png') }}" alt="Zelle" style="height: 20px; object-fit: contain;">
                                </div>
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="fw-bold mb-0" style="color: #741b6b;">Send with Zelle&reg;</h6>
                                        <span class="badge small font-monospace" style="background: rgba(116, 27, 107, 0.1); color: #741b6b; font-size: 10px;">Within Minutes</span>
                                    </div>
                                    <p class="small text-muted mb-0">Fast, safe and easy way to send money using mobile number or email.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Option 4: External ACH & Wire -->
                        <div class="col-md-6 col-12">
                            <div class="transfer-type-card csb-method-tile js-type-select h-100 p-3 p-md-4 border rounded-3 d-flex align-items-start gap-3 cursor-pointer" data-type="external" onclick="window.selectTransferType('external', this)">
                                <input type="radio" name="transfer_type" value="external" class="d-none">
                                <div class="csb-method-icon rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(200, 35, 44, 0.08); color: #c8232c; font-size: 20px;">
                                    <i class="fa-solid fa-building-columns"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="fw-bold text-dark mb-0">External Bank ACH &amp; Wire</h6>
                                        <span class="badge bg-secondary-subtle text-secondary small font-monospace" style="font-size: 10px;">1–3 Days</span>
                                    </div>
                                    <p class="small text-muted mb-0">Send via NACHA ACH network or same-day wire to any U.S. financial institution.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ════════ Step 2: Core Details ════════ -->
                <div class="wizard-step p-4 p-md-5 d-none" id="step2">
                    <div class="d-flex align-items-center mb-4 cursor-pointer text-decoration-none" style="color: #162846;" onclick="goToStep(1)">
                        <i class="fa-solid fa-arrow-left me-2"></i> <span class="small fw-bold">Back to Transfer Types</span>
                    </div>

                    <div class="row g-4">
                        <!-- From Account -->
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">From Account</label>
                            <select name="wallet_type" class="form-select form-select-lg border-2 shadow-none" id="walletSelect" onchange="checkRestriction(); updateAccountOptions()" style="border-radius: 10px; font-size: 15px;">
                                @if($wallets->isEmpty())
                                    <option value="default" data-type="checking" data-balance="{{ (float)auth()->user()->balance }}" @disabled(auth()->user()->isRestricted('checking')) selected>
                                        Personal Checking (*{{ substr(auth()->user()->account_number ?? '1397', -4) }}) — $ {{ number_format((float)auth()->user()->balance, 2) }}
                                        @if(auth()->user()->isRestricted('checking')) (Restricted) @endif
                                    </option>
                                @else
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->currency->code }}" data-type="checking" data-balance="{{ (float)$wallet->balance }}" @disabled(auth()->user()->isRestricted('checking')) selected>
                                            Personal Checking (*{{ substr(auth()->user()->account_number ?? '1397', -4) }}) — {{ $wallet->currency->symbol . number_format((float)$wallet->balance, 2) }}
                                            @if(auth()->user()->isRestricted('checking')) (Restricted) @endif
                                        </option>
                                    @endforeach
                                @endif
                                <option value="primary_savings" data-type="savings" data-balance="{{ (float)auth()->user()->savings_balance }}" @disabled(auth()->user()->isRestricted('savings'))>
                                    Primary Savings (*{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number ?? '1397', -4) }}) — $ {{ number_format((float)auth()->user()->savings_balance, 2) }}
                                    @if(auth()->user()->isRestricted('savings')) (Restricted) @endif
                                </option>
                                @if(auth()->user()->ira_status == 1)
                                <option value="ira" data-type="ira" data-balance="{{ (float)auth()->user()->ira_balance }}" @disabled(auth()->user()->isRestricted('ira'))>
                                    Individual Retirement (IRA) (*{{ substr(auth()->user()->ira_account_number ?? auth()->user()->account_number, -4) }}) — $ {{ number_format((float)auth()->user()->ira_balance, 2) }}
                                    @if(auth()->user()->isRestricted('ira')) (Restricted) @endif
                                </option>
                                @endif
                                @if(auth()->user()->heloc_status == 1)
                                <option value="heloc" data-type="heloc" data-balance="{{ max(0, (float)(auth()->user()->heloc_credit_limit - auth()->user()->heloc_balance)) }}" @disabled(auth()->user()->isRestricted('heloc'))>
                                    HELOC Line (*{{ substr(auth()->user()->heloc_account_number ?? auth()->user()->account_number, -4) }}) — $ {{ number_format(max(0, (float)(auth()->user()->heloc_credit_limit - auth()->user()->heloc_balance)), 2) }} (Available)
                                    @if(auth()->user()->isRestricted('heloc')) (Restricted) @endif
                                </option>
                                @endif
                                @if(auth()->user()->cc_status == 1)
                                <option value="cc" data-type="cc" data-balance="{{ max(0, (float)(auth()->user()->cc_credit_limit - auth()->user()->cc_balance)) }}" @disabled(auth()->user()->isRestricted('cc'))>
                                    Platinum Rewards Card (*{{ substr(auth()->user()->cc_account_number ?? auth()->user()->account_number, -4) }}) — $ {{ number_format(max(0, (float)(auth()->user()->cc_credit_limit - auth()->user()->cc_balance)), 2) }} (Available)
                                    @if(auth()->user()->isRestricted('cc')) (Restricted) @endif
                                </option>
                                @endif
                            </select>
                        </div>

                        <!-- Recipient / Destination Section (Dynamic) -->
                        <div class="col-12" id="toRecipientSection">
                            <!-- 1. Self Transfer: To Account -->
                            <div class="dynamic-field d-none" id="field-self">
                                <label class="form-label small text-uppercase fw-bold text-muted">To Account</label>
                                <select name="to_wallet" class="form-select form-select-lg border-2 shadow-none" id="toWalletSelect" style="border-radius: 10px; font-size: 15px;"></select>
                            </div>

                            <!-- 2. Customer Transfer: Recipient Lookup & Destination -->
                            <div class="dynamic-field d-none" id="field-member">
                                <div class="row g-3 g-md-4">
                                    <div class="col-12 col-lg-5">
                                        <label class="form-label small text-uppercase fw-bold text-muted mb-2">Recipient Customer Account # or Email</label>
                                        <input type="text" name="member_identifier" id="member_identifier" class="form-control form-control-lg border-2 shadow-none" placeholder="Account number, email, or username" autocomplete="off" style="border-radius: 10px; font-size: 15px;">
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label small text-uppercase fw-bold text-muted mb-2">Customer Verification</label>
                                        <div id="member_verify_panel" class="member-verify-panel">
                                            <div id="member_verify_placeholder" class="member-verify-placeholder small text-muted">Enter recipient details. We'll verify the customer immediately.</div>
                                            <div id="member_verify_success" class="d-none member-verify-success">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-success fs-5"><i class="fa-solid fa-circle-check"></i></span>
                                                    <div class="flex-grow-1 min-w-0">
                                                        <div class="fw-bold text-dark text-truncate" id="member_verified_name"></div>
                                                        <div class="extra-small text-success fw-semibold" id="member_verified_meta"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="member_verify_error" class="d-none member-verify-error small text-danger fw-semibold">
                                                <i class="fa-solid fa-circle-exclamation me-1"></i> No customer found. Please verify details.
                                            </div>
                                        </div>
                                        <input type="hidden" id="account_name" name="manual_data[account_name]" disabled>
                                        <input type="hidden" id="recipient_checking_last4" value="">
                                        <input type="hidden" id="recipient_savings_last4" value="">
                                    </div>
                                    <div class="col-12 col-lg-3">
                                        <label class="form-label small text-uppercase fw-bold text-muted mb-2">Credit To</label>
                                        <select name="target_account_type" id="target_account_type" class="form-select form-select-lg border-2 shadow-none" style="border-radius: 10px; font-size: 15px;">
                                            <option value="checking" selected>Checking Account</option>
                                            <option value="savings" id="opt-savings" hidden disabled>Savings Account</option>
                                            @if(auth()->user()->ira_status == 1)
                                            <option value="ira" id="opt-ira" hidden disabled>IRA Account</option>
                                            @endif
                                            @if(auth()->user()->heloc_status == 1)
                                            <option value="heloc" id="opt-heloc" hidden disabled>HELOC (Pay Down)</option>
                                            @endif
                                            @if(auth()->user()->cc_status == 1)
                                            <option value="cc" id="opt-cc" hidden disabled>Credit Card (Pay Down)</option>
                                            @endif
                                            @if(auth()->user()->loan_account_status == 1)
                                            <option value="loan" id="opt-loan" hidden disabled>Loan (Pay Down)</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. External Transfer: Wire Option Banner -->
                            <div class="dynamic-field d-none" id="field-external">
                                <div class="p-3 bg-light rounded-3 border mb-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3" style="border-color: #e2e8f0 !important;">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="fa-solid fa-bolt text-warning me-1"></i> Need a Same-Day Domestic or International Wire?</div>
                                        <div class="extra-small text-muted">High-value immediate settlement via Federal Reserve Wire Network.</div>
                                    </div>
                                    <a href="{{ Route::has('user.fund_transfer.transfer.wire') ? route('user.fund_transfer.transfer.wire') : url('/user/fund-transfer/transfer/wire') }}" class="btn btn-sm text-white fw-bold px-3 py-2 rounded-pill text-nowrap" style="background: #162846; font-size: 12px;">
                                        <i class="fa-solid fa-paper-plane me-1"></i> Wire Transfer
                                    </a>
                                </div>
                                <p class="small text-muted mb-0">
                                    Or continue below to send standard External Bank ACH (1-3 Business Days). You will enter the recipient's routing and account details on the next step.
                                </p>
                            </div>
                        </div>

                        <!-- Amount Field -->
                        <div class="col-md-6 col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Amount ($)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-2 border-end-0 fw-bold">$</span>
                                <input type="number" step="0.01" min="0.01" class="form-control border-2 border-start-0 shadow-none fw-bold" id="amount" name="amount" placeholder="0.00" oninput="validateBalance()" style="font-size: 18px;">
                            </div>
                            <div class="small mt-1" id="balanceFeedback"></div>
                        </div>

                        <!-- Frequency Field -->
                        <div class="col-md-6 col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Frequency</label>
                            <select name="frequency" id="transferFrequencySelect" class="form-select form-select-lg border-2 shadow-none" style="border-radius: 10px; font-size: 15px;">
                                <option value="once" selected>One-time Transfer</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <!-- Scheduled Date -->
                        <div class="col-md-6 col-12" id="dateField">
                            <label class="form-label small text-uppercase fw-bold text-muted">Scheduled Date</label>
                            <input type="date" name="scheduled_at" class="form-control form-control-lg border-2 shadow-none" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" style="border-radius: 10px; font-size: 15px;">
                        </div>

                        <!-- Purpose / Memo -->
                        <div class="col-md-6 col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Memo / Purpose (Optional)</label>
                            <input type="text" name="purpose" id="transferPurpose" class="form-control form-control-lg border-2 shadow-none" placeholder="e.g. Personal, Shared Expense, Rent" style="border-radius: 10px; font-size: 15px;">
                        </div>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="button" class="btn text-white py-2.5 px-4 shadow-sm fw-bold w-100 w-md-auto" style="background: #162846; border-radius: 10px; font-size: 14.5px;" onclick="validateStep2()">
                            Continue To Review <i class="fa-solid fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- ════════ Step 3: Recipient & Routing (External Specific) ════════ -->
                <div class="wizard-step p-4 p-md-5 d-none" id="step3">
                    <div class="d-flex align-items-center mb-4 cursor-pointer text-decoration-none" style="color: #162846;" onclick="goToStep(2)">
                        <i class="fa-solid fa-arrow-left me-2"></i> <span class="small fw-bold">Back to Transfer Details</span>
                    </div>

                    <h4 class="mb-4 fw-bold text-dark">Recipient Institution &amp; Account Information</h4>
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Recipient Full Legal Name</label>
                            <input type="text" name="manual_data[account_name]" id="ext_manual_account_name" class="form-control form-control-lg border-2 shadow-none" placeholder="Recipient's legal personal or business name" disabled style="border-radius: 10px; font-size: 15px;">
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">9-Digit ABA Routing Number</label>
                            <div class="input-group">
                                <input type="text" inputmode="numeric" name="manual_data[routing_number]" id="routingNumberInput" class="form-control form-control-lg border-2 shadow-none" placeholder="9-digit routing number" maxlength="9" pattern="[0-9]{9}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" disabled style="border-radius: 10px; font-size: 15px;">
                            </div>
                            <div class="small mt-2" id="routingLookupStatus"></div>

                            <!-- Verified Receiving Bank Card -->
                            <div class="routing-lookup-verified d-none mt-2 p-3 rounded-3" id="routingLookupCard" style="background: #f0fdf4; border: 1.5px solid #86efac;">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="rounded-3 d-inline-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 42px; height: 42px; background: #059669; font-size: 18px;">
                                        <i class="fa-solid fa-building-columns"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="fw-bold text-dark" id="lookupBankName"></div>
                                        <div class="small text-success fw-semibold" id="lookupBankState">Receiving financial institution (verified)</div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="manual_data[bank_name]" id="resolvedBankName" disabled>
                        </div>

                        <div class="col-md-6 col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Account Number</label>
                            <div class="input-group">
                                <input type="password" name="manual_data[account_number]" id="ext_acc_num" class="form-control form-control-lg border-2 shadow-none" placeholder="Recipient account number" minlength="4" maxlength="20" oninput="this.value = this.value.replace(/[^0-9]/g, '')" disabled style="border-radius: 10px 0 0 10px; font-size: 15px;">
                                <button class="btn btn-outline-secondary border-2 border-start-0 bg-white" type="button" onclick="toggleVisibility(this)" style="border-radius: 0 10px 10px 0;"><i class="fa-solid fa-eye-slash"></i></button>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Confirm Account Number</label>
                            <div class="input-group">
                                <input type="password" id="ext_acc_num_confirm" class="form-control form-control-lg border-2 shadow-none" placeholder="Re-enter to confirm account number" disabled style="border-radius: 10px 0 0 10px; font-size: 15px;">
                                <button class="btn btn-outline-secondary border-2 border-start-0 bg-white" type="button" onclick="toggleVisibility(this)" style="border-radius: 0 10px 10px 0;"><i class="fa-solid fa-eye-slash"></i></button>
                            </div>
                        </div>

                        <div class="col-12 d-none" id="manualBankNameWrap">
                            <label class="form-label small text-uppercase fw-bold text-muted">Receiving Institution Name</label>
                            <input type="text" name="manual_data[bank_name_manual]" id="manualBankNameInput" class="form-control form-control-lg border-2 shadow-none" placeholder="As shown on check or bank statement" disabled style="border-radius: 10px; font-size: 15px;">
                        </div>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="button" class="btn text-white py-2.5 px-4 shadow-sm fw-bold w-100 w-md-auto" style="background: #162846; border-radius: 10px; font-size: 14.5px;" onclick="validateStep3()">
                            Review Transfer <i class="fa-solid fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- ════════ Step 4: Transfer Review & Authorize ════════ -->
                <div class="wizard-step p-4 p-md-5 d-none" id="step4">
                    <div class="d-flex align-items-center mb-4 cursor-pointer text-decoration-none" style="color: #162846;" onclick="goBackFromReview()">
                        <i class="fa-solid fa-arrow-left me-2"></i> <span class="small fw-bold">Edit Details</span>
                    </div>

                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1">Review &amp; Authorize Transfer</h4>
                        <p class="text-muted small">Please verify all transaction details before 2FA confirmation.</p>
                    </div>

                    <div class="card bg-white border-0 mb-4 shadow-sm" style="border-radius: 20px; border: 1.5px solid #e2e8f0 !important;">
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Payment From</span><span class="fw-bold text-dark" id="reviewFrom"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Transfer To</span><span class="fw-bold text-dark" id="reviewTo"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Delivery Method</span><span class="fw-bold text-capitalize" id="reviewType"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Frequency</span><span class="fw-bold text-capitalize text-dark" id="reviewFreq"></span></div>
                                <div class="col-md-6"><span class="text-muted d-block small text-uppercase fw-bold">Date</span><span class="fw-bold text-dark" id="reviewDate"></span></div>
                                <div class="col-12"><span class="text-muted d-block small text-uppercase fw-bold">Purpose / Memo</span><span class="fw-bold text-dark" id="reviewPurpose"></span></div>

                                <div class="col-12 external-review-details d-none">
                                    <hr class="my-3">
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><span class="text-muted d-block small text-uppercase fw-bold">Routing #</span><span class="fw-bold font-monospace text-dark" id="reviewRouting"></span></div>
                                        <div class="col-md-6 mb-3"><span class="text-muted d-block small text-uppercase fw-bold">Account #</span><span class="fw-bold font-monospace text-dark" id="reviewAccount"></span></div>
                                        <div class="col-md-6 mb-3"><span class="text-muted d-block small text-uppercase fw-bold">Recipient Bank Name</span><span class="fw-bold text-dark" id="reviewBankName"></span></div>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-4">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2"><span class="small text-muted">Transfer Amount</span><span class="fw-bold text-dark" id="reviewTransferAmount"></span></div>
                                <div class="d-flex justify-content-between mb-2"><span class="small text-muted">Fee</span><span class="fw-bold text-success" id="reviewFeeAmount">$0.00</span></div>
                                <div class="d-flex justify-content-between border-top pt-2 mt-2"><span class="h6 mb-0 fw-bold text-dark">Total Deducted</span><span class="h5 mb-0 fw-bold" style="color: #c8232c;" id="reviewAmount"></span></div>
                            </div>

                            <button type="button" class="btn text-white py-2.5 px-4 shadow-sm w-100 fw-bold" style="background: #c8232c; border-radius: 10px; font-size: 15px;" id="confirmBtn" onclick="window.submitCsbTransfer();">
                                <span class="spinner-border spinner-border-sm me-2 d-none" id="confirmSpinner" role="status" aria-hidden="true"></span>
                                <span id="confirmBtnText"><i class="fa-solid fa-lock me-2"></i> Authorize Transfer</span>
                            </button>
                        </div>
                    </div>

                    <div class="text-center text-muted extra-small mt-3" style="font-size: 11.5px;">
                        <i class="fa-solid fa-lock me-1"></i> 256-bit Encrypted &bull; Member FDIC
                    </div>
                </div>

                <!-- CSB Institutional Transfer Processing Overlay -->
                <div id="transferProcessingOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(15, 23, 42, 0.78); z-index: 999999; backdrop-filter: blur(8px);">
                    <div class="card border-0 rounded-4 shadow-2-strong text-center p-4 p-md-5" style="max-width: 420px; width: 92%; background: #ffffff; border-radius: 22px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35); border-top: 5px solid #c8232c !important;">
                        <!-- Dual-Ring Animated Radar / Spinner -->
                        <div class="position-relative d-inline-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <div class="spinner-border" style="width: 80px; height: 80px; border-width: 4px; color: #c8232c;" role="status"></div>
                            <div class="position-absolute d-flex align-items-center justify-content-center rounded-circle bg-light" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-shield-halved fs-4" style="color: #c8232c;"></i>
                            </div>
                        </div>
                        
                        <h4 class="fw-bold text-dark mb-1" style="letter-spacing: -0.3px;">Processing Transfer</h4>
                        <p class="text-muted small mb-3">Routing payment securely via Cleveland State Bank ACH &amp; Wire Network</p>

                        <!-- Steps Status Card -->
                        <div class="bg-light rounded-3 p-3 text-start mb-3 border">
                            <div class="d-flex align-items-center gap-2 mb-2 text-success small fw-semibold">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Authorization verified</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 small fw-bold" style="color: #c8232c;">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                <span>Transmitting transaction payload...</span>
                            </div>
                        </div>

                        <div class="alert alert-warning py-2 px-3 mb-3 border-0 small text-start d-flex align-items-center gap-2" style="background: #fff8e6; color: #855d00; border-radius: 10px; font-size: 12px;">
                            <i class="fa-solid fa-triangle-exclamation flex-shrink-0"></i>
                            <span>Please do not refresh, close, or leave this screen until confirmed.</span>
                        </div>

                        <div class="text-muted extra-small" style="font-size: 11px;">
                            <i class="fa-solid fa-lock me-1"></i> 256-bit TLS Encrypted &bull; Member FDIC &bull; Routing #084301768
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
    .wizard-step { transition: opacity 0.3s ease; }
    .cursor-pointer { cursor: pointer; }
    .csb-method-tile {
        transition: all 0.25s;
        border: 2px solid #e2e8f0 !important;
        background: #ffffff;
    }
    .csb-method-tile:hover {
        transform: translateY(-4px);
        border-color: #162846 !important;
        box-shadow: 0 10px 25px rgba(22, 40, 70, 0.08) !important;
    }
    .csb-method-tile.border-primary,
    .csb-method-tile.selected {
        border-color: #c8232c !important;
        background: rgba(200, 35, 44, 0.03) !important;
        box-shadow: 0 0 0 1px #c8232c;
    }

    .step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
    }
    .step-indicator.active .step-circle {
        background: #c8232c;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(200, 35, 44, 0.25);
    }
    .step-indicator.text-success .step-circle {
        background: #16a34a;
        color: #ffffff;
    }
    .step-indicator {
        font-size: 0.95rem;
        color: #64748b;
        font-weight: 600;
    }
    .step-indicator.active {
        color: #162846;
        font-weight: 700;
    }

    .member-verify-panel {
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        padding: 0.6rem 0.9rem;
        background: #ffffff;
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
    }
    .extra-small {
        font-size: 11px;
    }
</style>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var currentStep = 1;
    var transferType = 'self';
    var resolvedBank = null;
    var defaultChargeType = @json(setting('fund_transfer_charge_type', 'fee'));
    var defaultCharge = parseFloat(@json((float) setting('fund_transfer_charge', 'fee')));

    const userRestrictions = {
        'default': {{ auth()->user()->isRestricted('checking') ? 'true' : 'false' }},
        'primary_savings': {{ auth()->user()->isRestricted('savings') ? 'true' : 'false' }},
        'ira': {{ auth()->user()->isRestricted('ira') ? 'true' : 'false' }},
        'heloc': {{ auth()->user()->isRestricted('heloc') ? 'true' : 'false' }},
        'cc': {{ auth()->user()->isRestricted('cc') ? 'true' : 'false' }},
        'loan': {{ auth()->user()->isRestricted('loan') ? 'true' : 'false' }}
    };

    function checkRestriction() {
        const fromSelect = document.getElementById('walletSelect');
        if (!fromSelect) return;
        const selectedType = fromSelect.value;
        
        if (userRestrictions[selectedType]) {
            Swal.fire({
                title: 'Account Restricted',
                text: 'This account is currently frozen and cannot be used for transfers. Please contact customer support.',
                icon: 'error',
                confirmButtonColor: '#162846'
            });
            for (let i = 0; i < fromSelect.options.length; i++) {
                if (!fromSelect.options[i].disabled) {
                    fromSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }

    window.selectTransferType = function(type, card) {
        transferType = type;
        document.querySelectorAll('.transfer-type-card').forEach(el => el.classList.remove('border-primary', 'selected'));
        if (card) {
            card.classList.add('border-primary', 'selected');
            const radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        } else {
            const el = document.querySelector('.js-type-select[data-type="' + type + '"]');
            if (el) {
                el.classList.add('border-primary', 'selected');
                const radio = el.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
            }
        }

        syncTransferFormFields();
        setTimeout(function() {
            goToStep(2);
        }, 120);
    };

    window.csbSelectType = window.selectTransferType;

    function syncTransferFormFields() {
        var step3 = document.getElementById('step3');
        var external = transferType === 'external';
        if (step3) {
            step3.querySelectorAll('input, select, textarea').forEach(function(el) {
                el.disabled = !external;
            });
        }
        var accName = document.getElementById('account_name');
        if (accName) {
            if (transferType !== 'member') {
                accName.disabled = true;
                accName.value = '';
            }
        }
        var bankIdEl = document.getElementById('resolvedBankId');
        if (bankIdEl && transferType !== 'external') {
            bankIdEl.value = '0';
        }
    }

    function goToStep(step) {
        document.querySelectorAll('.wizard-step').forEach(function(el) {
            el.classList.add('d-none');
            el.classList.remove('active');
        });

        const stepEl = document.getElementById('step' + step);
        if (stepEl) {
            stepEl.classList.remove('d-none');
            stepEl.classList.add('active');
        }

        updateIndicators(step);
        currentStep = step;

        if (step === 2) setupStep2();
    }

    window.goToStep = goToStep;

    function updateIndicators(step) {
        const isExternal = transferType === 'external';
        const step3Ind = document.getElementById('stepIndicator3');
        const stepLine3 = document.getElementById('stepLine3');
        const step3Lbl = document.getElementById('step3Label');
        const step4Ind = document.getElementById('stepIndicator4');
        const step4Circ = document.getElementById('stepCircle4');

        if (!isExternal) {
            if (step3Lbl) step3Lbl.innerText = 'Review & Authorize';
            if (stepLine3) stepLine3.classList.add('d-none');
            if (step4Ind) step4Ind.classList.add('d-none');
        } else {
            if (step3Lbl) step3Lbl.innerText = 'Recipient';
            if (stepLine3) stepLine3.classList.remove('d-none');
            if (step4Ind) step4Ind.classList.remove('d-none');
        }

        document.querySelectorAll('.step-indicator').forEach(function(el) {
            const stepNum = parseInt(el.getAttribute('data-step') || '1');
            el.classList.remove('active', 'text-success');

            let activeStepNum = step;
            if (!isExternal && step === 4) activeStepNum = 3;

            if (stepNum === activeStepNum) {
                el.classList.add('active');
            } else if (stepNum < activeStepNum) {
                el.classList.add('text-success');
            }
        });
    }

    function setupStep2() {
        document.querySelectorAll('.dynamic-field').forEach(el => el.classList.add('d-none'));
        const field = document.getElementById('field-' + transferType);
        if (field) field.classList.remove('d-none');
        updateAccountOptions();
    }

    function updateAccountOptions() {
        const fromSelect = document.getElementById('walletSelect');
        const toSelect = document.getElementById('toWalletSelect');
        if (!fromSelect || !toSelect) return;
        const selectedType = fromSelect.options[fromSelect.selectedIndex].getAttribute('data-type');

        if (transferType === 'self') {
            toSelect.innerHTML = '';

            // Add Checking
            if (selectedType !== 'checking') {
                const isRestricted = userRestrictions['default'];
                var opt = document.createElement('option');
                opt.value = 'default';
                opt.text = 'Personal Checking (*{{ substr(auth()->user()->account_number ?? "1397", -4) }})' + (isRestricted ? ' (Restricted)' : '') + ' — $ {{ number_format((float)auth()->user()->balance, 2) }}';
                if (isRestricted) opt.disabled = true;
                toSelect.add(opt);
            }

            // Add Savings
            if (selectedType !== 'savings') {
                const isRestricted = userRestrictions['primary_savings'];
                var opt = document.createElement('option');
                opt.value = 'primary_savings';
                opt.text = 'Primary Savings (*{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number ?? "1397", -4) }})' + (isRestricted ? ' (Restricted)' : '') + ' — $ {{ number_format((float)auth()->user()->savings_balance, 2) }}';
                if (isRestricted) opt.disabled = true;
                toSelect.add(opt);
            }

            // Add IRA
            @if(auth()->user()->ira_status == 1)
            if (selectedType !== 'ira') {
                const isRestricted = userRestrictions['ira'];
                var opt = document.createElement('option');
                opt.value = 'ira';
                opt.text = 'Individual Retirement (IRA) (*{{ substr(auth()->user()->ira_account_number ?? auth()->user()->account_number, -4) }})' + (isRestricted ? ' (Restricted)' : '') + ' — $ {{ number_format((float)auth()->user()->ira_balance, 2) }}';
                if (isRestricted) opt.disabled = true;
                toSelect.add(opt);
            }
            @endif

            // Add HELOC
            @if(auth()->user()->heloc_status == 1)
            if (selectedType !== 'heloc') {
                const isRestricted = userRestrictions['heloc'];
                var opt = document.createElement('option');
                opt.value = 'heloc';
                opt.text = 'HELOC Line (*{{ substr(auth()->user()->heloc_account_number ?? auth()->user()->account_number, -4) }})' + (isRestricted ? ' (Restricted)' : '') + ' — Pay Down Balance: $ {{ number_format((float)auth()->user()->heloc_balance, 2) }}';
                if (isRestricted) opt.disabled = true;
                toSelect.add(opt);
            }
            @endif

            // Add Credit Card
            @if(auth()->user()->cc_status == 1)
            if (selectedType !== 'cc') {
                const isRestricted = userRestrictions['cc'];
                var opt = document.createElement('option');
                opt.value = 'cc';
                opt.text = 'Platinum Rewards Card (*{{ substr(auth()->user()->cc_account_number ?? auth()->user()->account_number, -4) }})' + (isRestricted ? ' (Restricted)' : '') + ' — Pay Down Balance: $ {{ number_format((float)auth()->user()->cc_balance, 2) }}';
                if (isRestricted) opt.disabled = true;
                toSelect.add(opt);
            }
            @endif

            // Add Loan
            @if(auth()->user()->loan_account_status == 1)
            if (selectedType !== 'loan') {
                const isRestricted = userRestrictions['loan'];
                var opt = document.createElement('option');
                opt.value = 'loan';
                opt.text = 'Personal Loan (*{{ substr(auth()->user()->loan_account_number ?? auth()->user()->account_number, -4) }})' + (isRestricted ? ' (Restricted)' : '') + ' — Pay Down Balance: $ {{ number_format((float)auth()->user()->loan_balance, 2) }}';
                if (isRestricted) opt.disabled = true;
                toSelect.add(opt);
            }
            @endif
        }
        validateBalance();
    }

    function validateBalance() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const fromSelect = document.getElementById('walletSelect');
        const balance = parseFloat(fromSelect.options[fromSelect.selectedIndex].getAttribute('data-balance') || '0');
        const feedback = document.getElementById('balanceFeedback');

        if (amount > balance) {
            feedback.innerHTML = '<span class="text-danger small fw-semibold"><i class="fa-solid fa-circle-exclamation me-1"></i> Insufficient funds available.</span>';
            return false;
        } else if (amount > 0) {
            feedback.innerHTML = '<span class="text-success small fw-semibold"><i class="fa-solid fa-circle-check me-1"></i> Sufficient funds.</span>';
        } else {
            feedback.innerHTML = '';
        }
        return true;
    }

    function validateStep2() {
        const amount = document.getElementById('amount').value;
        if (!amount || parseFloat(amount) <= 0) {
            Swal.fire({ title: 'Invalid Amount', text: 'Please enter a transfer amount greater than $0.00.', icon: 'warning', confirmButtonColor: '#162846' });
            return;
        }
        if (!validateBalance()) {
            Swal.fire({ title: 'Insufficient Funds', text: 'You do not have enough available funds in this account for this transfer.', icon: 'error', confirmButtonColor: '#162846' });
            return;
        }

        if (transferType === 'member') {
            if (!document.getElementById('member_identifier').value.trim()) {
                Swal.fire({ title: 'Recipient Required', text: 'Please provide the recipient\'s customer account number or email.', icon: 'warning', confirmButtonColor: '#162846' });
                return;
            }
            var accNm = document.getElementById('account_name');
            if (!accNm || !accNm.value.trim() || accNm.disabled) {
                Swal.fire({ title: 'Verify Customer', text: 'Please enter a valid Cleveland State Bank customer so we can confirm the recipient before continuing.', icon: 'warning', confirmButtonColor: '#162846' });
                return;
            }
        }

        if (transferType === 'external') {
            goToStep(3);
        } else {
            populateReview();
            goToStep(4);
        }
    }

    function isValidAbaChecksumAch(routing) {
        if (routing === '084301768') return true;
        if (!/^\d{9}$/.test(routing)) return false;
        const d = routing.split('').map(Number);
        const sum = (3 * (d[0] + d[3] + d[6])) + (7 * (d[1] + d[4] + d[7])) + (d[2] + d[5] + d[8]);
        return sum % 10 === 0;
    }

    function validateStep3() {
        var nameEl = document.getElementById('ext_manual_account_name');
        const name = nameEl ? nameEl.value.trim() : '';
        const acc1 = document.getElementById('ext_acc_num').value.trim();
        const acc2 = document.getElementById('ext_acc_num_confirm').value.trim();
        const routing = document.querySelector('input[name="manual_data[routing_number]"]').value.trim();

        if (!name || !acc1 || routing.length < 9) {
            Swal.fire({ title: 'Details Missing', text: 'Please complete all recipient and receiving bank fields.', icon: 'warning', confirmButtonColor: '#162846' });
            return;
        }
        if (acc1 !== acc2) {
            Swal.fire({ title: 'Account Number Mismatch', text: 'The account numbers entered do not match. Please verify.', icon: 'error', confirmButtonColor: '#162846' });
            return;
        }
        if (!document.getElementById('resolvedBankId').value || document.getElementById('resolvedBankId').value === '0') {
            const manualName = document.getElementById('manualBankNameInput').value.trim();
            if (!manualName && routing !== '084301768') {
                var mWrap = document.getElementById('manualBankNameWrap');
                if (mWrap) mWrap.classList.remove('d-none');
                var mInput = document.getElementById('manualBankNameInput');
                if (mInput) {
                    mInput.removeAttribute('disabled');
                    mInput.focus();
                }
                Swal.fire({ title: 'Receiving Institution Required', text: 'Please enter the receiving bank name as it appears on records.', icon: 'warning', confirmButtonColor: '#162846' });
                return;
            }
            if (manualName) {
                document.getElementById('resolvedBankName').value = manualName;
            }
        }
        populateReview();
        goToStep(4);
    }

    function calculateFee(amount) {
        return 0.00;
    }

    function setMemberVerifyState(state) {
        var panel = document.getElementById('member_verify_panel');
        var ph = document.getElementById('member_verify_placeholder');
        var ok = document.getElementById('member_verify_success');
        var err = document.getElementById('member_verify_error');
        var accName = document.getElementById('account_name');
        if (!panel || !ph || !ok || !err) return;

        panel.classList.remove('is-verified');
        ph.classList.remove('d-none');
        ok.classList.add('d-none');
        err.classList.add('d-none');

        if (state === 'success') {
            panel.classList.add('is-verified');
            ph.classList.add('d-none');
            ok.classList.remove('d-none');
            if (accName && transferType === 'member') accName.disabled = false;
        } else if (state === 'error') {
            ph.classList.add('d-none');
            err.classList.remove('d-none');
            if (accName) { accName.disabled = true; accName.value = ''; }
        } else {
            if (accName) { accName.disabled = true; accName.value = ''; }
        }
    }

    function recipientAccountEndingLabel() {
        var tEl = document.getElementById('target_account_type');
        if (!tEl) return '';
        var t = tEl.value;
        var chkEl = document.getElementById('recipient_checking_last4');
        var savEl = document.getElementById('recipient_savings_last4');
        var chk = chkEl ? chkEl.value : '';
        var sav = savEl ? savEl.value : '';
        if (t === 'checking' && chk) return 'Checking account ending in ' + chk;
        if (t === 'savings' && sav) return 'Savings account ending in ' + sav;
        if (t === 'ira') return 'IRA Account';
        if (t === 'heloc') return 'HELOC (payment)';
        if (t === 'cc') return 'Credit Card (payment)';
        if (t === 'loan') return 'Loan (payment)';
        if (chk) return 'Checking account ending in ' + chk;
        return '';
    }

    function buildReviewToLine() {
        var accEl = document.getElementById('account_name');
        var name = accEl ? accEl.value.trim() : '';
        var ending = recipientAccountEndingLabel();
        if (!name) return '—';
        return ending ? (name + ' — ' + ending) : name;
    }

    function updateVerifiedMetaLine() {
        var metaEl = document.getElementById('member_verified_meta');
        if (!metaEl) return;
        var line = recipientAccountEndingLabel();
        metaEl.innerText = line || 'Verified Cleveland State Bank customer.';
    }

    function populateReview() {
        document.getElementById('reviewType').innerText = transferType === 'self' ? 'Between My Accounts' : (transferType === 'member' ? 'Cleveland State Bank Customer' : 'External Bank ACH');
        const fromSelect = document.getElementById('walletSelect');
        document.getElementById('reviewFrom').innerText = fromSelect.options[fromSelect.selectedIndex].text.split('—')[0].trim();

        let toText = 'Unknown';
        if (transferType === 'self') {
            const toSelect = document.getElementById('toWalletSelect');
            toText = toSelect.options[toSelect.selectedIndex]?.text.split('—')[0].trim() || 'My Account';
        } else if (transferType === 'member') {
            toText = buildReviewToLine();
        } else {
            toText = (document.getElementById('resolvedBankName').value || document.getElementById('manualBankNameInput').value || 'External Financial Institution');
        }

        document.getElementById('reviewTo').innerText = toText;
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const fee = transferType === 'external' ? calculateFee(amount) : 0;
        const total = amount + fee;

        document.getElementById('reviewTransferAmount').innerText = '$ ' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('reviewFeeAmount').innerText = fee > 0 ? ('$ ' + fee.toFixed(2)) : 'Free ($0.00)';
        document.getElementById('reviewAmount').innerText = '$ ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('reviewDate').innerText = document.querySelector('input[name="scheduled_at"]').value;
        document.getElementById('reviewFreq').innerText = document.querySelector('select[name="frequency"]').value;
        document.getElementById('reviewPurpose').innerText = document.getElementById('transferPurpose').value || 'Transfer of funds';

        const externalDetails = document.querySelector('.external-review-details');
        if (transferType === 'external') {
            externalDetails.classList.remove('d-none');
            document.getElementById('reviewRouting').innerText = document.getElementById('routingNumberInput').value;
            document.getElementById('reviewAccount').innerText = '••••' + document.getElementById('ext_acc_num').value.slice(-4);
            document.getElementById('reviewBankName').innerText = toText;
        } else {
            externalDetails.classList.add('d-none');
        }
    }

    function goBackFromReview() {
        if (transferType === 'external') goToStep(3);
        else goToStep(2);
    }

    function toggleVisibility(btn) {
        const input = btn.parentElement.querySelector('input');
        input.type = input.type === "password" ? "text" : "password";
        btn.querySelector('i').classList.toggle('fa-eye');
        btn.querySelector('i').classList.toggle('fa-eye-slash');
    }

    function initTransferIndex() {
        document.querySelectorAll('.js-type-select').forEach(card => {
            card.onclick = function(e) {
                const type = this.getAttribute('data-type');
                window.selectTransferType(type, this);
            };
        });

        syncTransferFormFields();

        var memberInput = document.getElementById('member_identifier');
        var targetAccountType = document.getElementById('target_account_type');
        var optSavings = document.getElementById('opt-savings');
        var accountNameHidden = document.getElementById('account_name');
        var chkLast4 = document.getElementById('recipient_checking_last4');
        var savLast4 = document.getElementById('recipient_savings_last4');
        var optIra = document.getElementById('opt-ira');
        var optHeloc = document.getElementById('opt-heloc');
        var optCc = document.getElementById('opt-cc');
        var optLoan = document.getElementById('opt-loan');

        function hideOptionalTargetOpts() {
            if (optSavings) { optSavings.hidden = true; optSavings.disabled = true; }
            if (optIra) { optIra.hidden = true; optIra.disabled = true; }
            if (optHeloc) { optHeloc.hidden = true; optHeloc.disabled = true; }
            if (optCc) { optCc.hidden = true; optCc.disabled = true; }
            if (optLoan) { optLoan.hidden = true; optLoan.disabled = true; }
        }

        if (targetAccountType) {
            targetAccountType.addEventListener('change', function() {
                var succ = document.getElementById('member_verify_success');
                if (succ && !succ.classList.contains('d-none')) updateVerifiedMetaLine();
            });
        }

        var memberDebounceTimer = null;
        if (memberInput) {
            memberInput.addEventListener('input', function() {
                clearTimeout(memberDebounceTimer);
                var val = this.value.trim();
                if (val.length >= 2) {
                    memberDebounceTimer = setTimeout(function() {
                        fetch('{{ route("user.fund_transfer.lookup_recipient") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ identifier: val })
                        })
                        .then(function(response) { return response.json().then(function(data) { return { status: response.status, body: data }; }); })
                        .then(function(res) {
                            var data = res.body;
                            if (res.status === 200 && data.status === 'success') {
                                document.getElementById('member_verified_name').innerText = data.masked_name || data.name || data.full_name;
                                if (accountNameHidden) {
                                    accountNameHidden.value = data.full_name || data.name;
                                }
                                if (chkLast4) chkLast4.value = data.checking_last4 || '';
                                if (savLast4) savLast4.value = data.savings_last4 || '';
                                setMemberVerifyState('success');
                                updateVerifiedMetaLine();

                                if (data.has_savings && optSavings) {
                                    optSavings.hidden = false;
                                    optSavings.disabled = false;
                                } else {
                                    if (optSavings) { optSavings.hidden = true; optSavings.disabled = true; }
                                    if (targetAccountType && targetAccountType.value === 'savings') targetAccountType.value = 'checking';
                                }

                                if (optIra) {
                                    if (data.has_ira) { optIra.hidden = false; optIra.disabled = false; }
                                    else { optIra.hidden = true; optIra.disabled = true; if (targetAccountType && targetAccountType.value === 'ira') targetAccountType.value = 'checking'; }
                                }
                                if (optHeloc) {
                                    if (data.has_heloc) { optHeloc.hidden = false; optHeloc.disabled = false; }
                                    else { optHeloc.hidden = true; optHeloc.disabled = true; if (targetAccountType && targetAccountType.value === 'heloc') targetAccountType.value = 'checking'; }
                                }
                                if (optCc) {
                                    if (data.has_cc) { optCc.hidden = false; optCc.disabled = false; }
                                    else { optCc.hidden = true; optCc.disabled = true; if (targetAccountType && targetAccountType.value === 'cc') targetAccountType.value = 'checking'; }
                                }
                                if (optLoan) {
                                    if (data.has_loan) { optLoan.hidden = false; optLoan.disabled = false; }
                                    else { optLoan.hidden = true; optLoan.disabled = true; if (targetAccountType && targetAccountType.value === 'loan') targetAccountType.value = 'checking'; }
                                }

                                updateVerifiedMetaLine();
                            } else {
                                if (accountNameHidden) accountNameHidden.value = '';
                                if (chkLast4) chkLast4.value = '';
                                if (savLast4) savLast4.value = '';
                                setMemberVerifyState('error');
                                hideOptionalTargetOpts();
                                if (targetAccountType) targetAccountType.value = 'checking';
                            }
                        })
                        .catch(function(err) {
                            if (accountNameHidden) accountNameHidden.value = '';
                            if (chkLast4) chkLast4.value = '';
                            if (savLast4) savLast4.value = '';
                            setMemberVerifyState('error');
                            hideOptionalTargetOpts();
                        });
                    }, 400);
                } else {
                    if (accountNameHidden) accountNameHidden.value = '';
                    if (chkLast4) chkLast4.value = '';
                    if (savLast4) savLast4.value = '';
                    setMemberVerifyState('idle');
                    hideOptionalTargetOpts();
                    if (targetAccountType) targetAccountType.value = 'checking';
                }
            });
        }

        const routingInput = document.getElementById('routingNumberInput');
        const statusEl = document.getElementById('routingLookupStatus');
        const cardEl = document.getElementById('routingLookupCard');
        const bankNameEl = document.getElementById('lookupBankName');
        const bankStateEl = document.getElementById('lookupBankState');
        const bankIdEl = document.getElementById('resolvedBankId');
        const bankNameHidden = document.getElementById('resolvedBankName');
        const manualWrap = document.getElementById('manualBankNameWrap');
        const manualInput = document.getElementById('manualBankNameInput');

        function clearResolvedBank() {
            resolvedBank = null;
            bankIdEl.value = '0';
            bankNameHidden.value = '';
            cardEl.classList.add('d-none');
        }

        let lookupTimer = null;
        if (routingInput) routingInput.addEventListener('input', function() {
            const routing = this.value.trim();
            clearResolvedBank();
            manualWrap.classList.add('d-none');
            manualInput.setAttribute('disabled', 'disabled');
            manualInput.value = '';
            statusEl.innerHTML = '';

            if (routing.length !== 9) return;

            // Whitelist Cleveland State Bank routing
            if (routing === '084301768') {
                bankIdEl.value = '0';
                bankNameHidden.value = 'Cleveland State Bank';
                bankNameEl.innerText = 'Cleveland State Bank';
                bankStateEl.innerText = 'Cleveland, MS — Internal Institution';
                cardEl.classList.remove('d-none');
                statusEl.innerHTML = '<span class="text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i> Cleveland State Bank verified.</span>';
                return;
            }

            if (!isValidAbaChecksumAch(routing)) {
                statusEl.innerHTML = '<span class="text-warning fw-semibold"><i class="fa-solid fa-circle-info me-1"></i> Unlisted routing number. Please enter receiving bank name below.</span>';
                manualWrap.classList.remove('d-none');
                manualInput.removeAttribute('disabled');
                return;
            }

            statusEl.innerHTML = '<span class="text-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i> Verifying ABA routing number directory...</span>';
            if (lookupTimer) clearTimeout(lookupTimer);
            lookupTimer = setTimeout(() => {
                fetch("{{ route('user.fund_transfer.lookup-routing') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ routing_number: routing })
                })
                .then(async response => {
                    const text = await response.text();
                    let body = {};
                    try { body = text ? JSON.parse(text) : {}; } catch (e) { body = {}; }
                    return { ok: response.ok, status: response.status, body };
                })
                .then(({ok, status, body}) => {
                    if (ok && body.status === 'verified') {
                        resolvedBank = body;
                        bankIdEl.value = body.bank_id;
                        bankNameHidden.value = body.bank_name;
                        bankNameEl.innerText = body.bank_name;
                        bankStateEl.innerText = body.location ? (body.location + ' — Verified Receiving Institution') : 'Receiving financial institution (verified)';
                        cardEl.classList.remove('d-none');
                        statusEl.innerHTML = '<span class="text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i> Receiving institution verified.</span>';
                        manualWrap.classList.add('d-none');
                        manualInput.setAttribute('disabled', 'disabled');
                        manualInput.value = '';
                    } else {
                        statusEl.innerHTML = '<span class="text-warning fw-semibold"><i class="fa-solid fa-circle-info me-1"></i> Unlisted institution. Enter bank name below.</span>';
                        manualWrap.classList.remove('d-none');
                        manualInput.removeAttribute('disabled');
                    }
                })
                .catch(() => {
                    statusEl.innerHTML = '<span class="text-warning fw-semibold"><i class="fa-solid fa-circle-info me-1"></i> Directory lookup unavailable. Enter bank name below.</span>';
                    manualWrap.classList.remove('d-none');
                    manualInput.removeAttribute('disabled');
                });
            }, 300);
        });
    }

    window.submitCsbTransfer = function() {
        var form = document.getElementById('transferForm');
        if (!form) return;
        
        if (window.SecurityGate && typeof window.SecurityGate.gate === 'function') {
            window.SecurityGate.gate(form, function() {
                window.showTransferLoader();
                HTMLFormElement.prototype.submit.call(form);
            });
        } else {
            window.showTransferLoader();
            HTMLFormElement.prototype.submit.call(form);
        }
    };

    window.showTransferLoader = function() {
        var overlay = document.getElementById('transferProcessingOverlay');
        if (overlay) overlay.classList.remove('d-none');
        var btn = document.getElementById('confirmBtn');
        if (btn) {
            btn.disabled = true;
            var spinner = document.getElementById('confirmSpinner');
            if (spinner) spinner.classList.remove('d-none');
            var text = document.getElementById('confirmBtnText');
            if (text) text.innerHTML = ' Processing Transfer...';
        }
        if (typeof window.showLoader === 'function') {
            window.showLoader();
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initTransferIndex();
            if (window._pendingTransferType) {
                window.selectType(window._pendingTransferType.type, window._pendingTransferType.card);
                delete window._pendingTransferType;
            }
        });
    } else {
        initTransferIndex();
        if (window._pendingTransferType) {
            window.selectType(window._pendingTransferType.type, window._pendingTransferType.card);
            delete window._pendingTransferType;
        }
    }
</script>
@endsection
