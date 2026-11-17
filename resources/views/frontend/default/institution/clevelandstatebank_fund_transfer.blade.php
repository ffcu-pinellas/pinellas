@extends('frontend::layouts.user')

@section('title')
    {{ __('Move Money & Transfers — Cleveland State Bank') }}
@endsection

@section('content')
@php
    $user = auth()->user() ?? auth('web')->user();
@endphp
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10 col-12">
        <!-- CSB Institutional Header -->
        <div class="csb-transfer-hero mb-4 p-4 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #1b325f 0%, #0d1b2a 100%); color: #ffffff;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('user.dashboard') }}" class="btn btn-sm btn-outline-light rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="badge rounded-pill text-uppercase px-2 py-1" style="background: #c8232c; font-size: 10px; letter-spacing: 0.8px;">
                                <i class="fa-solid fa-shield-halved me-1"></i> FDIC Insured Transfers
                            </span>
                            <span class="text-white-50 font-monospace small" style="font-size: 11px;">ABA #084301768</span>
                        </div>
                        <h3 class="fw-bold mb-0 text-white" style="letter-spacing: -0.5px;">Move Money &amp; Transfers</h3>
                    </div>
                </div>
                <div class="text-sm-end text-white-50 small d-none d-md-block" style="font-size: 12px; line-height: 1.4;">
                    <strong class="text-white">Cleveland State Bank</strong><br>
                    Official NACHA ACH &amp; Real-Time Network
                </div>
            </div>
        </div>

        <div class="card p-0 mb-4 shadow-sm border-0" style="border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0 !important; background: #ffffff;">
            <!-- Executive Step Header -->
            <div class="wizard-header px-4 py-3 border-bottom" style="background: #f8fafc;">
                <div class="d-flex justify-content-between align-items-center flex-row">
                    <div class="step-indicator active d-flex align-items-center" data-step="1">
                        <div class="step-circle me-2">1</div>
                        <span class="d-none d-sm-inline fw-semibold">Transfer Type</span>
                    </div>
                    <div class="step-line flex-grow-1 mx-2 mx-md-3" style="height: 2px; background: #e2e8f0;"></div>
                    <div class="step-indicator d-flex align-items-center" data-step="2">
                        <div class="step-circle me-2">2</div>
                        <span class="d-none d-sm-inline fw-semibold">Transfer Details</span>
                    </div>
                    <div class="step-line flex-grow-1 mx-2 mx-md-3" style="height: 2px; background: #e2e8f0;"></div>
                    <div class="step-indicator d-flex align-items-center" data-step="3">
                        <div class="step-circle me-2">3</div>
                        <span class="d-none d-sm-inline fw-semibold">Review &amp; Authorize</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('user.fund_transfer.transfer') }}" method="POST" id="transferForm">
                @csrf
                <input type="hidden" name="charge_type" value="percentage">
                <input type="hidden" name="bank_id" id="resolvedBankId" value="0">
                <input type="hidden" name="transfer_type" id="csbSelectedTransferType" value="self">
                
                <!-- ════════ Step 1: Transfer Type ════════ -->
                <div class="wizard-step active p-4 p-md-5" id="step1">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1">Select Transfer Method</h4>
                        <p class="text-muted small">Choose how you want to route funds securely.</p>
                    </div>

                    <div class="row g-3 justify-content-center">
                        <!-- Option 1: Internal -->
                        <div class="col-md-6 col-12">
                            <div class="transfer-type-card csb-method-tile js-type-select h-100 p-3 p-md-4 border rounded-3 d-flex align-items-start gap-3 cursor-pointer" data-type="self">

                                <div class="csb-method-icon rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: rgba(22, 40, 70, 0.08); color: #162846; font-size: 20px;">
                                    <i class="fa-solid fa-wallet"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="fw-bold text-dark mb-0">Between My Accounts</h6>
                                        <span class="badge bg-success-subtle text-success small font-monospace" style="font-size: 10px;">Immediate</span>
                                    </div>
                                    <p class="small text-muted mb-0">Transfer funds between Checking, Savings, IRA, or Loan pay-downs.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Option 2: Member -->
                        <div class="col-md-6 col-12">
                            <div class="transfer-type-card csb-method-tile js-type-select h-100 p-3 p-md-4 border rounded-3 d-flex align-items-start gap-3 cursor-pointer" data-type="member">

                                <div class="csb-method-icon rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: rgba(16, 185, 129, 0.08); color: #10b981; font-size: 20px;">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="fw-bold text-dark mb-0">Cleveland State Bank Member</h6>
                                        <span class="badge bg-success-subtle text-success small font-monospace" style="font-size: 10px;">Instant</span>
                                    </div>
                                    <p class="small text-muted mb-0">Send instantly to another Cleveland State Bank customer.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Option 3: Zelle -->
                        <div class="col-md-6 col-12">
                            <div class="transfer-type-card csb-method-tile h-100 p-3 p-md-4 border rounded-3 d-flex align-items-start gap-3 cursor-pointer" onclick="window.location.href='{{ route('user.fund_transfer.zelle') }}'">
                                <div class="csb-method-icon rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: rgba(116, 27, 107, 0.08); color: #741b6b; font-size: 20px;">
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

                        <!-- Option 4: External ACH -->
                        <div class="col-md-6 col-12">
                            <div class="transfer-type-card csb-method-tile js-type-select h-100 p-3 p-md-4 border rounded-3 d-flex align-items-start gap-3 cursor-pointer" data-type="external">

                                <div class="csb-method-icon rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: rgba(200, 35, 44, 0.08); color: #c8232c; font-size: 20px;">
                                    <i class="fa-solid fa-building-columns"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="fw-bold text-dark mb-0">External Bank ACH</h6>
                                        <span class="badge bg-secondary-subtle text-secondary small font-monospace" style="font-size: 10px;">1–3 Days</span>
                                    </div>
                                    <p class="small text-muted mb-0">Send via NACHA ACH network to any U.S. financial institution.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ════════ Step 2: Transfer Details ════════ -->
                <div class="wizard-step p-4 p-md-5 d-none" id="step2">
                    <div class="d-flex align-items-center mb-4 cursor-pointer text-decoration-none" style="color: #162846;" onclick="goToStep(1)">
                        <i class="fas fa-arrow-left me-2"></i> <span class="small fw-bold">Back to Transfer Types</span>
                    </div>

                    <!-- From Account -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">From Account</label>
                        <select class="form-select shadow-none form-select-lg" name="wallet_type" id="fromAccountSelect" required style="font-size: 15px; border-radius: 10px;">
                            <option value="default" data-balance="{{ (float)($user->balance ?? 0) }}" data-name="Checking" selected>
                                Checking (*{{ substr($user->account_number ?? '1397', -4) }}) — $ {{ number_format((float)($user->balance ?? 0), 2) }}
                            </option>
                            <option value="primary_savings" data-balance="{{ (float)($user->savings_balance ?? 0) }}" data-name="Savings">
                                Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }}) — $ {{ number_format((float)($user->savings_balance ?? 0), 2) }}
                            </option>
                            @if($user->ira_status == 1)
                            <option value="ira" data-balance="{{ (float)($user->ira_balance ?? 0) }}" data-name="IRA">
                                IRA (*{{ substr($user->ira_account_number ?? $user->account_number, -4) }}) — $ {{ number_format((float)($user->ira_balance ?? 0), 2) }}
                            </option>
                            @endif
                        </select>
                    </div>

                    <!-- Fast Wire Banner -->
                    <div class="p-3 mb-4 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2 border" style="background: #fffbeb; border-color: #fde68a !important;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-warning fs-5"><i class="fa-solid fa-bolt"></i></span>
                            <div class="small">
                                <strong class="text-dark">Need a Same-Day Fedwire or International Wire?</strong><br>
                                <span class="text-muted">High-value immediate settlement via Federal Reserve Wire Network.</span>
                            </div>
                        </div>
                        <a href="{{ Route::has('user.fund_transfer.transfer.wire') ? route('user.fund_transfer.transfer.wire') : url('/user/fund-transfer/transfer/wire') }}" class="btn btn-sm text-white fw-bold px-3 py-2 rounded-pill" style="background: #162846; font-size: 12px;">
                            <i class="fa-solid fa-paper-plane me-1"></i> Wire Transfer
                        </a>
                    </div>

                    <!-- Dynamic Fields by Transfer Type -->
                    <div id="dynamicFields">
                        <!-- 1. Self Fields -->
                        <div id="selfFields" class="d-none">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted small text-uppercase">To My Account</label>
                                <select class="form-select shadow-none form-select-lg" name="to_wallet" id="toWalletSelect" style="font-size: 15px; border-radius: 10px;">
                                    <option value="primary_savings">Savings (*{{ substr($user->savings_account_number ?? $user->account_number ?? '1397', -4) }})</option>
                                    <option value="default">Checking (*{{ substr($user->account_number ?? '1397', -4) }})</option>
                                    @if($user->ira_status == 1)
                                    <option value="ira">IRA (*{{ substr($user->ira_account_number ?? $user->account_number, -4) }})</option>
                                    @endif
                                    @if($user->heloc_status == 1)
                                    <option value="heloc">HELOC (Pay Down)</option>
                                    @endif
                                    @if($user->cc_status == 1)
                                    <option value="cc">Credit Card (Pay Down)</option>
                                    @endif
                                    @if($user->loan_account_status == 1)
                                    <option value="loan">Personal Loan (Pay Down)</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <!-- 2. Member Fields -->
                        <div id="memberFields" class="d-none">
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Recipient Account #, Username or Email</label>
                                    <div class="input-group">
                                        <input type="text" name="member_identifier" id="memberIdentifierInput" class="form-control form-control-lg shadow-none" placeholder="Enter recipient's registered account number, email or username" autocomplete="off" oninput="csbLookupMemberWizard(this.value)">
                                        <span class="input-group-text bg-light" id="memberLookupSpinner" style="display:none;"><i class="fa-solid fa-spinner fa-spin text-muted"></i></span>
                                    </div>
                                    <div id="memberLookupSuccess" class="d-none mt-2 p-2 px-3 rounded-2" style="background:#f0fdf4; border:1px solid #86efac;">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-circle-check text-success fs-5"></i>
                                            <div>
                                                <strong class="text-dark" id="memberVerifiedName"></strong>
                                                <span class="badge bg-success ms-2">Verified Customer</span>
                                                <div class="extra-small text-muted" id="memberVerifiedMeta"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="memberLookupError" class="d-none mt-2 p-2 px-3 rounded-2 text-danger small" style="background:#fef2f2; border:1px solid #fca5a5;">
                                        <i class="fa-solid fa-circle-exclamation me-1"></i> No customer found. Please verify the account number or email.
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Credit To Account Type</label>
                                    <select name="target_account_type" class="form-select shadow-none">
                                        <option value="checking" selected>Checking Account</option>
                                        <option value="savings">Savings Account</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 3. External ACH Fields -->
                        <div id="externalFields" class="d-none">
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Recipient Full Legal Name</label>
                                    <input type="text" name="manual_data[account_name]" id="externalAccName" class="form-control form-control-lg shadow-none" placeholder="First and last legal name">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-muted small text-uppercase">9-Digit ABA Routing Number</label>
                                    <div class="input-group">
                                        <input type="text" inputmode="numeric" name="manual_data[routing_number]" id="externalRoutingInput" class="form-control form-control-lg shadow-none" placeholder="9-digit routing number" maxlength="9" pattern="[0-9]{9}" oninput="csbLookupRoutingWizard(this.value)">
                                        <span class="input-group-text bg-light" id="routingLookupSpinner" style="display:none;"><i class="fa-solid fa-spinner fa-spin text-muted"></i></span>
                                    </div>
                                    <div class="small mt-1" id="routingLookupStatus"></div>

                                    <!-- Verified Bank Card -->
                                    <div id="routingVerifiedCard" class="d-none mt-2 p-3 rounded-2" style="background:#f0fdf4; border:1px solid #86efac;">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-building-columns text-success fs-5"></i>
                                            <div>
                                                <div class="fw-bold text-dark" id="lookupBankName"></div>
                                                <div class="extra-small text-muted" id="lookupBankLoc">Receiving Financial Institution (Verified)</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Manual Bank Name Fallback -->
                                    <div id="manualBankGroup" class="d-none mt-2">
                                        <label class="form-label extra-small fw-bold text-muted text-uppercase">Receiving Bank Name</label>
                                        <input type="text" name="manual_data[bank_name_manual]" id="manualBankNameInput" class="form-control form-control-sm shadow-none" placeholder="Enter bank name as shown on statement">
                                    </div>
                                    <input type="hidden" name="manual_data[bank_name]" id="resolvedBankNameHidden">
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Account Number</label>
                                    <div class="input-group">
                                        <input type="password" name="manual_data[account_number]" id="externalAccNum" class="form-control form-control-lg shadow-none" placeholder="Recipient account number" minlength="4" maxlength="20" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                        <button type="button" class="btn btn-outline-secondary" onclick="csbTogglePwd('externalAccNum', this)"><i class="fa-solid fa-eye-slash"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Confirm Account Number</label>
                                    <div class="input-group">
                                        <input type="password" id="externalAccNumConfirm" class="form-control form-control-lg shadow-none" placeholder="Re-enter account number" minlength="4" maxlength="20" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                        <button type="button" class="btn btn-outline-secondary" onclick="csbTogglePwd('externalAccNumConfirm', this)"><i class="fa-solid fa-eye-slash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Common Fields: Amount, Frequency, Memo -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-muted small text-uppercase">Amount ($)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-end-0 fw-bold">$</span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="transferAmountInput" class="form-control border-start-0 fw-bold shadow-none" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-muted small text-uppercase">Frequency</label>
                            <select name="frequency" id="transferFrequencySelect" class="form-select form-select-lg shadow-none">
                                <option value="once" selected>One-time Transfer</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-muted small text-uppercase">Memo / Purpose (Optional)</label>
                            <input type="text" name="purpose" id="transferMemoInput" class="form-control form-control-lg shadow-none" placeholder="e.g. Monthly rent, savings split, contractor payment">
                        </div>
                    </div>

                    <div id="step2ErrorNotice" class="alert alert-danger d-none py-2 small mb-4"></div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn text-white rounded-pill py-3 fw-bold shadow-sm" style="background: #162846; font-size: 15px;" onclick="validateAndGoToReview()">
                            Continue to Review &rarr;
                        </button>
                    </div>
                </div>

                <!-- ════════ Step 3: Review & Authorize ════════ -->
                <div class="wizard-step p-4 p-md-5 d-none" id="step3">
                    <div class="d-flex align-items-center mb-4 cursor-pointer text-decoration-none" style="color: #162846;" onclick="goToStep(2)">
                        <i class="fas fa-arrow-left me-2"></i> <span class="small fw-bold">Back to Transfer Details</span>
                    </div>

                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1">Review &amp; Authorize Transfer</h4>
                        <p class="text-muted small">Please verify all transaction details before 2FA confirmation.</p>
                    </div>

                    <div class="p-4 rounded-3 border mb-4" style="background: #f8fafc; font-size: 14px;">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Transfer Type</span>
                            <span class="badge rounded-pill px-3 py-1 fw-bold" id="reviewBadge" style="background: rgba(22, 40, 70, 0.1); color: #162846;">Internal</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">From Account</span>
                            <span class="fw-bold text-dark" id="reviewFrom"></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Recipient / To</span>
                            <span class="fw-bold text-dark" id="reviewTo"></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom d-none" id="reviewBankRow">
                            <span class="text-muted">Receiving Institution</span>
                            <span class="fw-semibold text-dark" id="reviewBank"></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom d-none" id="reviewRoutingRow">
                            <span class="text-muted">ABA Routing #</span>
                            <span class="font-monospace text-dark" id="reviewRouting"></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Transfer Amount</span>
                            <span class="fw-bold text-dark fs-5" id="reviewAmount"></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Transfer Fee</span>
                            <span class="text-success fw-bold">Free ($0.00)</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Delivery Speed</span>
                            <span class="text-muted fw-semibold" id="reviewDelivery">Immediate</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Frequency</span>
                            <span class="text-muted" id="reviewFrequency">One-time Transfer</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <span class="text-muted">Memo / Purpose</span>
                            <span class="text-muted" id="reviewMemo">None</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn text-white rounded-pill px-5 py-3 shadow-sm w-100 fs-5 fw-bold" style="background: #c8232c;" id="confirmBtn" onclick="csbSubmitTransferForm()">
                            <i class="fa-solid fa-shield-halved me-2"></i> Authorize &amp; Submit Transfer
                        </button>
                    </div>

                    <div class="text-center text-muted extra-small mt-3" style="font-size: 11.5px;">
                        <i class="fa-solid fa-lock me-1"></i> 256-bit Encrypted NACHA Network &bull; FDIC Insured
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
    .wizard-header .step-circle {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 12px;
    }
    .wizard-header .step-indicator.active .step-circle {
        background: #c8232c;
        color: #ffffff;
    }
    .wizard-header .step-indicator.active span {
        color: #162846;
        font-weight: 700;
    }
    .csb-method-tile {
        background: #ffffff;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        transition: all 0.2s ease;
    }
    .csb-method-tile:hover {
        border-color: #162846 !important;
        background: #f8fafc;
        transform: translateY(-2px);
    }
    .csb-method-tile.selected {
        border-color: #c8232c !important;
        background: rgba(200, 35, 44, 0.02) !important;
        box-shadow: 0 0 0 1px #c8232c;
    }
    .extra-small {
        font-size: 11px;
    }
</style>
@endsection

@section('script')
<script>
    let currentStep = 1;
    let selectedType = 'self';

    function goToStep(step) {
        $('.wizard-step').addClass('d-none');
        $('#step' + step).removeClass('d-none');
        $('.step-indicator').removeClass('active');
        for (let i = 1; i <= step; i++) {
            $('.step-indicator[data-step="' + i + '"]').addClass('active');
        }
        currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Step 1: Type Selection
    $('.js-type-select').on('click', function() {
        $('.csb-method-tile').removeClass('selected');
        $(this).addClass('selected');
        selectedType = $(this).data('type');
        $('#csbSelectedTransferType').val(selectedType);
        $('input[name="transfer_type"][value="' + selectedType + '"]').prop('checked', true);

        // Configure Step 2: Disable all dynamic fields first
        $('#selfFields, #memberFields, #externalFields').addClass('d-none');
        $('#selfFields select, #selfFields input, #memberFields select, #memberFields input, #externalFields select, #externalFields input').prop('disabled', true);

        if (selectedType === 'self') {
            $('#selfFields').removeClass('d-none');
            $('#selfFields select, #selfFields input').prop('disabled', false);
            $('#toWalletSelect').prop('required', true);
            $('#memberIdentifierInput, #externalAccName, #externalRoutingInput, #externalAccNum, #externalAccNumConfirm').prop('required', false);
        } else if (selectedType === 'member') {
            $('#memberFields').removeClass('d-none');
            $('#memberFields select, #memberFields input').prop('disabled', false);
            $('#memberIdentifierInput').prop('required', true);
            $('#toWalletSelect, #externalAccName, #externalRoutingInput, #externalAccNum, #externalAccNumConfirm').prop('required', false);
        } else if (selectedType === 'external') {
            $('#externalFields').removeClass('d-none');
            $('#externalFields select, #externalFields input').prop('disabled', false);
            $('#externalAccName, #externalRoutingInput, #externalAccNum, #externalAccNumConfirm').prop('required', true);
            $('#toWalletSelect, #memberIdentifierInput').prop('required', false);
        }

        setTimeout(function() {
            goToStep(2);
        }, 150);
    });

    // Toggle Password Visibility
    function csbTogglePwd(id, btn) {
        var input = document.getElementById(id);
        var icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }

    // Routing Lookup
    function csbLookupRoutingWizard(val) {
        val = (val || '').replace(/\D/g, '');
        var $status = $('#routingLookupStatus');
        var $card = $('#routingVerifiedCard');
        var $manual = $('#manualBankGroup');
        var $spin = $('#routingLookupSpinner');

        if (val.length < 9) {
            $status.html('');
            $card.addClass('d-none');
            $manual.addClass('d-none');
            return;
        }

        // Whitelist CSB
        if (val === '084301768') {
            $spin.hide();
            $status.html('');
            $card.removeClass('d-none');
            $('#lookupBankName').text('Cleveland State Bank');
            $('#lookupBankLoc').text('Cleveland, MS • Internal Institution');
            $('#resolvedBankNameHidden').val('Cleveland State Bank');
            $('#resolvedBankId').val('0');
            $manual.addClass('d-none');
            return;
        }

        $spin.show();
        $status.html('<span class="text-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i> Looking up institution directory...</span>');

        $.get('/user/fund-transfer/routing-lookup', { routing_number: val })
            .done(function(res) {
                $spin.hide();
                if (res.status === 'verified') {
                    $status.html('');
                    $card.removeClass('d-none');
                    $('#lookupBankName').text(res.bank_name);
                    $('#lookupBankLoc').text(res.location || 'Verified Financial Institution');
                    $('#resolvedBankNameHidden').val(res.bank_name);
                    $('#resolvedBankId').val(res.bank_id || '0');
                    $manual.addClass('d-none');
                } else {
                    $card.addClass('d-none');
                    $manual.removeClass('d-none');
                    $status.html('<span class="text-muted"><i class="fa-solid fa-circle-info me-1"></i> Unlisted institution. Please enter the bank name below.</span>');
                }
            })
            .fail(function() {
                $spin.hide();
                $card.addClass('d-none');
                $manual.removeClass('d-none');
                $status.html('<span class="text-muted"><i class="fa-solid fa-circle-info me-1"></i> Directory lookup unavailable. Please enter the receiving bank name below.</span>');
            });
    }

    // Member Lookup
    let memberTimer = null;
    function csbLookupMemberWizard(val) {
        clearTimeout(memberTimer);
        val = (val || '').trim();
        var $spin = $('#memberLookupSpinner');
        var $succ = $('#memberLookupSuccess');
        var $err  = $('#memberLookupError');

        if (val.length < 2) {
            $spin.hide();
            $succ.addClass('d-none');
            $err.addClass('d-none');
            return;
        }

        $spin.show();
        memberTimer = setTimeout(function() {
            $.get('/user/exist/' + encodeURIComponent(val))
                .done(function(res) {
                    $spin.hide();
                    if (res.data) {
                        $err.addClass('d-none');
                        $succ.removeClass('d-none');
                        $('#memberVerifiedName').text(res.data.name || res.data.username || 'Cleveland State Bank Customer');
                        $('#memberVerifiedMeta').text('Account: ' + (res.data.account_number || val));
                    } else {
                        $succ.addClass('d-none');
                        $err.removeClass('d-none');
                    }
                })
                .fail(function() {
                    $spin.hide();
                    $succ.addClass('d-none');
                    $err.removeClass('d-none');
                });
        }, 400);
    }

    // Validation & Step 3 Review
    function validateAndGoToReview() {
        var $errNotice = $('#step2ErrorNotice');
        $errNotice.addClass('d-none').html('');

        var amt = parseFloat($('#transferAmountInput').val()) || 0;
        if (amt <= 0) {
            $errNotice.removeClass('d-none').html('<i class="fa-solid fa-circle-exclamation me-1"></i> Please enter a valid transfer amount.');
            return;
        }

        var fromOpt = $('#fromAccountSelect option:selected');
        var fromBal = parseFloat(fromOpt.data('balance')) || 0;
        if (amt > fromBal) {
            $errNotice.removeClass('d-none').html('<i class="fa-solid fa-circle-exclamation me-1"></i> Insufficient funds in ' + fromOpt.data('name') + '. Available: $' + fromBal.toFixed(2));
            return;
        }

        var fromText = fromOpt.text().split('—')[0].trim();
        var toText = '';
        var bankName = '';
        var routing = '';
        var badgeText = 'INTERNAL TRANSFER';
        var delivery = 'Immediate';

        if (selectedType === 'self') {
            toText = $('#toWalletSelect option:selected').text().trim();
            badgeText = 'BETWEEN MY ACCOUNTS';
            delivery = 'Immediate';
        } else if (selectedType === 'member') {
            var memberId = $('#memberIdentifierInput').val().trim();
            if (!memberId) {
                $errNotice.removeClass('d-none').html('<i class="fa-solid fa-circle-exclamation me-1"></i> Please enter recipient member details.');
                return;
            }
            toText = $('#memberVerifiedName').text() || memberId;
            badgeText = 'MEMBER-TO-MEMBER';
            delivery = 'Immediate Settlement';
        } else if (selectedType === 'external') {
            var recName = $('#externalAccName').val().trim();
            if (!recName) {
                $errNotice.removeClass('d-none').html('<i class="fa-solid fa-circle-exclamation me-1"></i> Please enter the recipient legal name.');
                return;
            }
            routing = $('#externalRoutingInput').val().trim();
            if (routing.length !== 9) {
                $errNotice.removeClass('d-none').html('<i class="fa-solid fa-circle-exclamation me-1"></i> Please enter a valid 9-digit ABA routing number.');
                return;
            }
            var acc1 = $('#externalAccNum').val().trim();
            var acc2 = $('#externalAccNumConfirm').val().trim();
            if (!acc1 || acc1 !== acc2) {
                $errNotice.removeClass('d-none').html('<i class="fa-solid fa-circle-exclamation me-1"></i> Account numbers do not match. Please verify both fields.');
                return;
            }
            bankName = $('#resolvedBankNameHidden').val().trim() || $('#manualBankNameInput').val().trim();
            if (!bankName) {
                $('#manualBankGroup').removeClass('d-none');
                $errNotice.removeClass('d-none').html('<i class="fa-solid fa-circle-exclamation me-1"></i> Please enter the receiving bank name to continue.');
                return;
            }
            toText = recName + ' (*' + acc1.slice(-4) + ')';
            badgeText = 'EXTERNAL BANK ACH';
            delivery = '1–3 Business Days (Standard ACH)';
        }

        var freq = $('#transferFrequencySelect option:selected').text().trim();
        var memo = $('#transferMemoInput').val().trim() || 'None';

        // Fill review modal
        $('#reviewBadge').text(badgeText);
        $('#reviewFrom').text(fromText);
        $('#reviewTo').text(toText);
        $('#reviewAmount').text('$ ' + amt.toFixed(2));
        $('#reviewDelivery').text(delivery);
        $('#reviewFrequency').text(freq);
        $('#reviewMemo').text(memo);

        if (bankName) {
            $('#reviewBank').text(bankName);
            $('#reviewBankRow').removeClass('d-none');
        } else {
            $('#reviewBankRow').addClass('d-none');
        }

        if (routing) {
            $('#reviewRouting').text(routing);
            $('#reviewRoutingRow').removeClass('d-none');
        } else {
            $('#reviewRoutingRow').addClass('d-none');
        }

        goToStep(3);
    }

    function csbSubmitTransferForm() {
        var form = document.getElementById('transferForm');
        var $btn = $('#confirmBtn');
        var origHtml = $btn.html();

        // Ensure security_verified hidden input is present on the form
        if (!$('#transferForm').find('input[name="security_verified"]').length) {
            $('<input>').attr({ type: 'hidden', name: 'security_verified', value: '1' }).appendTo('#transferForm');
        } else {
            $('#transferForm').find('input[name="security_verified"]').val('1');
        }

        // Ensure only active fields for selectedType are enabled, inactive are disabled
        if (selectedType === 'self') {
            $('#selfFields select, #selfFields input').prop('disabled', false);
            $('#memberFields select, #memberFields input, #externalFields select, #externalFields input').prop('disabled', true);
        } else if (selectedType === 'member') {
            $('#memberFields select, #memberFields input').prop('disabled', false);
            $('#selfFields select, #selfFields input, #externalFields select, #externalFields input').prop('disabled', true);
        } else if (selectedType === 'external') {
            $('#externalFields select, #externalFields input').prop('disabled', false);
            $('#selfFields select, #selfFields input, #memberFields select, #memberFields input').prop('disabled', true);
        }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Authorizing &amp; Routing Transfer...');

        if (typeof SecurityGate !== 'undefined' && typeof SecurityGate.gate === 'function') {
            SecurityGate.onCancelCb = function() {
                $btn.prop('disabled', false).html(origHtml);
            };
            SecurityGate.gate(form);
        } else {
            if (typeof window.showLoader === 'function') window.showLoader();
            HTMLFormElement.prototype.submit.call(form);
        }
    }

    $(function() {
        $('#selfFields select, #selfFields input').prop('disabled', false);
        $('#memberFields select, #memberFields input, #externalFields select, #externalFields input').prop('disabled', true);
    });
</script>
@endsection

