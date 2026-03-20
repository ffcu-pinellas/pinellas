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
                        <div class="col-12" id="walletSelectWrapper">
                            <label class="form-label small text-uppercase fw-bold text-muted">From Account</label>
                            <select name="wallet_type" class="form-select form-select-lg border-2 shadow-none" id="walletSelect" onchange="updateAccountOptions()">
                                @if($wallets->isEmpty())
                                    <option value="default" data-name="Checking" data-number="...{{ substr(auth()->user()->account_number, -4) }}" data-type="checking" data-balance="{{ auth()->user()->balance }}">
                                        Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ setting('site_currency', 'global') . auth()->user()->balance }}
                                    </option>
                                @else
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->currency->code }}" data-name="Checking" data-number="...{{ substr(auth()->user()->account_number, -4) }}" data-type="checking" data-balance="{{ $wallet->balance }}">
                                            Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ $wallet->currency->symbol . $wallet->balance }}
                                        </option>
                                    @endforeach
                                @endif
                                <option value="primary_savings" data-name="Savings" data-number="...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S" data-type="savings" data-balance="{{ auth()->user()->savings_balance }}">
                                    Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S) - {{ setting('site_currency', 'global') }} {{ number_format(auth()->user()->savings_balance, 2) }}
                                </option>
                                @if(auth()->user()->heloc_status == 1)
                                <option value="heloc" data-name="HELOC" data-number="...{{ substr(auth()->user()->heloc_account_number ?? auth()->user()->account_number, -4) }}H" data-type="heloc" data-balance="{{ auth()->user()->heloc_credit_limit - auth()->user()->heloc_balance }}">
                                    HELOC Account (...{{ substr(auth()->user()->heloc_account_number ?? auth()->user()->account_number, -4) }}H) - {{ setting('site_currency', 'global') }} {{ number_format(auth()->user()->heloc_credit_limit - auth()->user()->heloc_balance, 2) }} (Available)
                                </option>
                                @endif
                                @if(auth()->user()->cc_status == 1)
                                <option value="cc" data-name="Credit Card" data-number="...{{ substr(auth()->user()->cc_account_number ?? auth()->user()->account_number, -4) }}C" data-type="cc" data-balance="{{ auth()->user()->cc_credit_limit - auth()->user()->cc_balance }}">
                                    Credit Card (...{{ substr(auth()->user()->cc_account_number ?? auth()->user()->account_number, -4) }}C) - {{ setting('site_currency', 'global') }} {{ number_format(auth()->user()->cc_credit_limit - auth()->user()->cc_balance, 2) }} (Available)
                                </option>
                                @endif

                            </select>
                        </div>

                        <div class="col-12">
                            <!-- Internal Transfer ("Account Bridge") -->
                            <div class="dynamic-field d-none" id="field-self">
                                <div class="account-bridge">
                                    <div class="row align-items-center g-3">
                                        <div class="col-12 col-md-5">
                                            <label class="form-label small text-uppercase fw-bold text-muted mb-2">From Account</label>
                                            <div class="bridge-card active" id="fromCard" onclick="openAccountSelector('from')">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-bold" id="fromCardName">Checking</span>
                                                    <i class="fas fa-chevron-down small text-muted"></i>
                                                </div>
                                                <div class="small text-muted mb-3" id="fromCardNumber">...{{ substr(auth()->user()->account_number, -4) }}</div>
                                                <div class="h5 mb-0 fw-bold" id="fromCardBalance">{{ setting('site_currency', 'global') }} {{ number_format(auth()->user()->balance, 2) }}</div>
                                                <div class="balance-preview" id="fromBalancePreview">New Balance: <span class="value">--</span></div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12 col-md-2 p-0 text-center">
                                            <div class="swap-container">
                                                <button type="button" class="btn btn-swap" onclick="swapAccounts()" title="Swap Accounts">
                                                    <i class="fas fa-arrows-alt-h fa-lg d-none d-md-block"></i>
                                                    <i class="fas fa-arrows-alt-v fa-lg d-md-none"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-5">
                                            <label class="form-label small text-uppercase fw-bold text-muted mb-2">To Account</label>
                                            <div class="bridge-card" id="toCard" onclick="openAccountSelector('to')">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-bold" id="toCardName">Savings</span>
                                                    <i class="fas fa-chevron-down small text-muted"></i>
                                                </div>
                                                <div class="small text-muted mb-3" id="toCardNumber">...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S</div>
                                                <div class="h5 mb-0 fw-bold" id="toCardBalance">{{ setting('site_currency', 'global') }} {{ number_format(auth()->user()->savings_balance, 2) }}</div>
                                                <div class="balance-preview" id="toBalancePreview">New Balance: <span class="value">--</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Hidden Selects for compatibility with existing form logic -->
                                    <select name="to_wallet" id="toWalletSelect" class="d-none">
                                        <option value="primary_savings" selected>Savings</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Member Transfer -->
                            <div class="dynamic-field d-none" id="field-member">
                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small text-uppercase fw-bold text-muted mb-2">Recipient Info</label>
                                        <input type="text" name="member_identifier" id="member_identifier" class="form-control form-control-lg border-2 shadow-none" placeholder="Email or Account #">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small text-uppercase fw-bold text-muted mb-2">Member Verification</label>
                                        <div id="member_card_preview" class="p-3 border-2 border-dashed bg-light d-flex align-items-center justify-content-center" style="border-radius: 12px; min-height: 52px; transition: all 0.3s;">
                                            <div id="member_initial_state" class="text-muted small">
                                                <i class="fas fa-user-check me-2"></i> Enter info to verify Member Account
                                            </div>
                                            <div id="member_success_state" class="d-none w-100">
                                                <div class="d-flex align-items-center">
                                                    <div class="member-avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: 700;">
                                                        <span id="member_initials">UN</span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark lh-1 mb-1" id="member_full_name">User Name</div>
                                                        <div class="small text-success fw-bold d-flex align-items-center">
                                                            <i class="fas fa-check-circle me-1"></i> Verified Member
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="member_error_state" class="d-none text-danger small fw-bold">
                                                <i class="fas fa-times-circle me-2"></i> Not Found
                                            </div>
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
                            
                            <!-- External Transfer Placeholder for Step 2 -->
                            <div class="dynamic-field d-none" id="field-external">
                                <div class="alert alert-info border-0 shadow-sm" style="border-radius: 15px; background: rgba(0, 84, 155, 0.05);">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle fa-lg me-3 text-primary"></i>
                                        <div>
                                            <h6 class="mb-1 fw-bold text-primary">External Bank Transfer</h6>
                                            <p class="small mb-0 text-muted">You will enter recipient bank details in the next step.</p>
                                        </div>
                                    </div>
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
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Full Name</label>
                            <input type="text" name="manual_data[account_name]" class="form-control form-control-lg border-2 shadow-none" placeholder="Recipient's legal name">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Recipient Bank</label>
                            <!-- Manual Bank Selection removed in favor of Auto-Discovery -->
                            <div class="p-3 border-2 bg-light d-flex align-items-center" style="border-radius: 12px; min-height: 52px;">
                                <div id="bank_auto_name" class="fw-bold text-muted small">
                                    <i class="fas fa-university me-2"></i> Verify bank from routing number...
                                </div>
                            </div>
                            <input type="hidden" name="bank_id" id="bankId" value="">
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

<!-- Account Selector Modal -->
<div class="modal fade" id="accountSelectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="selectorTitle">Select Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="accountList" class="d-grid gap-2">
                    <!-- Dynamic Account Items -->
                </div>
            </div>
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

    /* Account Bridge Styles */
    .account-bridge {
        background: #f8f9fa;
        border-radius: 24px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
    }
    .bridge-card {
        background: white;
        border: 2px solid #edeff2;
        border-radius: 18px;
        padding: 1.5rem;
        transition: all 0.3s;
        cursor: pointer;
        height: 100%;
    }
    .bridge-card.active {
        border-color: var(--primary-color);
        box-shadow: 0 8px 20px rgba(0, 84, 155, 0.1);
    }
    .bridge-card:hover:not(.active) {
        border-color: #dee2e6;
        transform: translateY(-2px);
    }
    .swap-container {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        z-index: 2;
    }
    .btn-swap {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: white;
        border: 2px solid #edeff2;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .btn-swap:hover {
        background: var(--primary-color);
        color: white;
        transform: translate(-50%, -50%) rotate(180deg) !important;
    }
    .balance-preview {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 0.5rem;
    }
    .balance-preview .value {
        font-weight: 700;
        color: var(--primary-color);
    }

    /* Mobile Bridge */
    @media (max-width: 768px) {
        .account-bridge { padding: 1rem; }
        .swap-container {
            position: relative;
            left: 0;
            top: 0;
            transform: none;
            margin: 1rem 0;
            display: flex;
            justify-content: center;
        }
        .btn-swap { transform: rotate(90deg); }
        .btn-swap:hover { transform: rotate(270deg) !important; }
    }
    
    /* Member Card Preview Styles */
    #member_card_preview {
        height: 64px;
        border-radius: 12px;
        background-color: #f8fafc;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    #member_card_preview.bg-white {
        border-color: #22c55e !important;
        background-color: #fff !important;
    }
    .member-avatar {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        font-size: 14px;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    #member_success_state {
        animation: slideIn 0.4s ease-out;
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
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
    var activeBridgeSide = 'from';

    // Account data extracted from the main select
    var accounts = [];
    var defaultBankId = '{{ $banks->first()?->id ?? 1 }}';
    document.addEventListener('DOMContentLoaded', function() {
        const walletSelect = document.getElementById('walletSelect');
        Array.from(walletSelect.options).forEach(opt => {
            accounts.push({
                value: opt.value,
                name: opt.getAttribute('data-name'),
                number: opt.getAttribute('data-number'),
                balance: parseFloat(opt.getAttribute('data-balance')),
                type: opt.getAttribute('data-type')
            });
        });
        
        // Initial setup for default account cards
        updateBridgeCards();
        
        // Listen to amount changes for balance preview
        document.getElementById('amount').addEventListener('input', updateBalancePreviews);
    });

    window.selectType = function(type, card) {
        transferType = type;
        document.querySelectorAll('.transfer-type-card').forEach(el => el.classList.remove('border-primary'));
        card.classList.add('border-primary');
        const radio = card.querySelector('input[type="radio"]');
        if(radio) radio.checked = true;

        if(type === 'self') {
            document.getElementById('walletSelectWrapper').classList.add('d-none');
        } else {
            document.getElementById('walletSelectWrapper').classList.remove('d-none');
        }

        setTimeout(() => goToStep(2), 250);
    };

    function updateBridgeCards() {
        const fromVal = document.getElementById('walletSelect').value;
        const toVal = document.getElementById('toWalletSelect').value;
        
        const fromAcc = accounts.find(a => a.value === fromVal) || accounts[0];
        const toAcc = accounts.find(a => a.value === toVal) || accounts[1] || accounts[0];

        // Update From Card
        document.getElementById('fromCardName').innerText = fromAcc.name;
        document.getElementById('fromCardNumber').innerText = fromAcc.number;
        document.getElementById('fromCardBalance').innerText = '{{ setting("site_currency", "global") }} ' + fromAcc.balance.toLocaleString(undefined, {minimumFractionDigits: 2});
        
        // Update To Card
        document.getElementById('toCardName').innerText = toAcc.name;
        document.getElementById('toCardNumber').innerText = toAcc.number;
        document.getElementById('toCardBalance').innerText = '{{ setting("site_currency", "global") }} ' + toAcc.balance.toLocaleString(undefined, {minimumFractionDigits: 2});
        
        updateBalancePreviews();
    }

    function openAccountSelector(side) {
        activeBridgeSide = side;
        const modal = new bootstrap.Modal(document.getElementById('accountSelectorModal'));
        const list = document.getElementById('accountList');
        const title = document.getElementById('selectorTitle');
        title.innerText = side === 'from' ? 'Transfer From' : 'Transfer To';
        list.innerHTML = '';

        const currentFrom = document.getElementById('walletSelect').value;
        const currentTo = document.getElementById('toWalletSelect').value;

        accounts.forEach(acc => {
            // Pay-into logic: If it's the From side, exclude Loan, CC, and IRA
            if(side === 'from' && ['loan', 'cc', 'ira'].includes(acc.type)) return;
            
            // Exclude the account already selected on the other side
            if(side === 'from' && acc.value === currentTo) return;
            if(side === 'to' && acc.value === currentFrom) return;

            const item = document.createElement('div');
            item.className = 'bridge-card mb-2 p-3 d-flex justify-content-between align-items-center';
            item.innerHTML = `
                <div>
                    <div class="fw-bold">${acc.name}</div>
                    <div class="small text-muted">${acc.number}</div>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-primary">{{ setting("site_currency", "global") }} ${acc.balance.toLocaleString(undefined, {minimumFractionDigits: 2})}</div>
                </div>
            `;
            item.onclick = () => {
                if(side === 'from') {
                    document.getElementById('walletSelect').value = acc.value;
                } else {
                    document.getElementById('toWalletSelect').innerHTML = `<option value="${acc.value}" selected>${acc.name}</option>`;
                }
                updateBridgeCards();
                modal.hide();
            };
            list.appendChild(item);
        });

        modal.show();
    }

    function swapAccounts() {
        const fromSelect = document.getElementById('walletSelect');
        const toSelect = document.getElementById('toWalletSelect');
        
        const oldFrom = fromSelect.value;
        const oldTo = toSelect.value;
        
        // Check if we can swap (pay-into constraint)
        const toAcc = accounts.find(a => a.value === oldTo);
        if(['loan', 'cc', 'ira'].includes(toAcc.type)) {
            Swal.fire({ title: 'Cannot Swap', text: toAcc.name + ' cannot be used as a source account.', icon: 'info', confirmButtonColor: '#00549b' });
            return;
        }

        fromSelect.value = oldTo;
        toSelect.innerHTML = `<option value="${oldFrom}" selected>${accounts.find(a => a.value === oldFrom).name}</option>`;
        
        const btn = document.querySelector('.btn-swap');
        btn.style.transform = 'translate(-50%, -50%) rotate(180deg)';
        setTimeout(() => {
            btn.style.transform = '';
            updateBridgeCards();
        }, 300);
    }

    function updateBalancePreviews() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const fromVal = document.getElementById('walletSelect').value;
        const toVal = document.getElementById('toWalletSelect').value;
        
        const fromAcc = accounts.find(a => a.value === fromVal);
        const toAcc = accounts.find(a => a.value === toVal);

        if(fromAcc) {
            const newFrom = fromAcc.balance - amount;
            document.getElementById('fromBalancePreview').querySelector('.value').innerText = '{{ setting("site_currency", "global") }} ' + newFrom.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('fromBalancePreview').querySelector('.value').classList.toggle('text-danger', newFrom < 0);
        }
        
        if(toAcc) {
            // For Pay-down accounts (Loan, CC, HELOC balance), the balance is how much they OWE.
            // But here the balance passed is "Available" for HELOC/CC, or we should show real balance.
            // Let's assume balance is what they HAVE or what is AVAILABLE.
            const newTo = toAcc.balance + amount;
            document.getElementById('toBalancePreview').querySelector('.value').innerText = '{{ setting("site_currency", "global") }} ' + newTo.toLocaleString(undefined, {minimumFractionDigits: 2});
        }
    }

    function goToStep(step) {
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.add('d-none'));
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
        if(transferType !== 'self') {
            validateBalance();
            return;
        }
        updateBridgeCards();
    }

    function validateBalance() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const fromSelect = document.getElementById('walletSelect');
        const fromOpt = fromSelect.options[fromSelect.selectedIndex];
        if(!fromOpt) return true;

        const balance = parseFloat(fromOpt.getAttribute('data-balance'));
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
        if(transferType === 'external') goToStep(3);
        else { populateReview(); goToStep(4); }
    }

    function validateStep3() {
        const name = document.querySelector('input[name="manual_data[account_name]"]').value;
        const acc1 = document.getElementById('ext_acc_num').value;
        const acc2 = document.getElementById('ext_acc_num_confirm').value;
        const routing = document.querySelector('input[name="manual_data[routing_number]"]').value;

        if(!name || !acc1 || !routing || routing.length < 9 || !document.getElementById('bankId').value) {
            Swal.fire({ title: 'Details Missing', text: 'Please complete all recipient information (Name, Bank, Routing, Account #).', icon: 'warning', confirmButtonColor: '#00549b' });
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
        if(transferType === 'self') {
            const toVal = document.getElementById('toWalletSelect').value;
            toText = accounts.find(a => a.value === toVal)?.name || 'My Account';
        }
        else if(transferType === 'member') toText = 'Member: ' + (document.getElementById('member_full_name').innerText || document.getElementById('member_identifier').value);
        else {
            toText = document.getElementById('bank_auto_name').innerText || 'External Bank';
            
            // Add Routing Display for External
            document.querySelector('.external-review-details').classList.remove('d-none');
            document.getElementById('reviewRouting').innerText = document.querySelector('input[name="manual_data[routing_number]"]').value;
            document.getElementById('reviewAccount').innerText = '****' + document.getElementById('ext_acc_num').value.slice(-4);
        }
        
        document.getElementById('reviewTo').innerText = toText;
        document.getElementById('reviewAmount').innerText = '{{ setting("site_currency", "global") }} ' + parseFloat(document.getElementById('amount').value).toLocaleString(undefined, {minimumFractionDigits: 2});
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

        // Bank Routing Lookup
        const routingInput = document.querySelector('input[name="manual_data[routing_number]"]');
        if(routingInput) {
            routingInput.addEventListener('input', function() {
                const val = this.value;
                if(val.length === 9) {
                    document.getElementById('routingValidationDisplay').classList.remove('d-none');
                    document.getElementById('detectedBankName').innerText = 'Verifying Routing Number...';
                    document.getElementById('detectedBankLocation').innerText = '';
                    
                    fetch('/user/fund-transfer/routing-lookup/' + val)
                        .then(res => res.json())
                        .then(data => {
                            if(data.status === 'success') {
                                document.getElementById('detectedBankName').innerText = data.bank_name;
                                document.getElementById('detectedBankName').classList.remove('text-muted');
                                document.getElementById('detectedBankName').classList.add('text-primary');
                                document.getElementById('detectedBankLocation').innerText = (data.city ? data.city + ', ' : '') + (data.state || '');
                                
                                // Auto-fill bank display and hidden ID
                                const autoBankDisplay = document.getElementById('bank_auto_name');
                                autoBankDisplay.innerHTML = `<i class="fas fa-university me-2 text-primary"></i> <span class="text-dark">${data.bank_name}</span>`;
                                autoBankDisplay.classList.remove('text-muted');
                                
                                // Set bankId - if it's an external bank, we might need a specific ID.
                                // Use the ID from discovery if available, otherwise use our system default.
                                document.getElementById('bankId').value = data.bank_id || defaultBankId || '1'; 
                            } else {
                                document.getElementById('detectedBankName').innerText = 'Invalid Routing Number';
                                document.getElementById('detectedBankName').classList.add('text-danger');
                                document.getElementById('detectedBankName').classList.remove('text-primary');
                                document.getElementById('bank_auto_name').innerHTML = '<i class="fas fa-exclamation-triangle me-2 text-danger"></i> <span class="text-danger">Invalid Routing</span>';
                            }
                        })
                        .catch(() => {
                            document.getElementById('detectedBankName').innerText = 'Verification Unavailable';
                        });
                } else {
                    document.getElementById('routingValidationDisplay').classList.add('d-none');
                }
            });
        }
        
        // Unified Member Lookup
        const memberInput = document.getElementById('member_identifier');
        const targetAccountType = document.getElementById('target_account_type');
        const optSavings = document.getElementById('opt-savings');

        if(memberInput) {
            memberInput.addEventListener('input', function() {
                const val = this.value;
                const memberNameDisplayText = document.getElementById('member_name_display_text');

                if(val.length > 4 || (val.includes('@') && val.length > 3)) {
                    fetch('/user/search-by-account-number/' + encodeURIComponent(val))
                        .then(response => response.json())
                        .then(data => {
                            const initialS = document.getElementById('member_initial_state');
                            const successS = document.getElementById('member_success_state');
                            const errorS = document.getElementById('member_error_state');
                            const card = document.getElementById('member_card_preview');

                            if(data.name) {
                                initialS.classList.add('d-none');
                                errorS.classList.add('d-none');
                                successS.classList.remove('d-none');
                                card.classList.remove('border-dashed');
                                card.classList.add('border-solid', 'bg-white', 'shadow-sm');
                                
                                document.getElementById('member_full_name').innerText = data.name;
                                const initials = data.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                                document.getElementById('member_initials').innerText = initials;
                                
                                if(data.has_savings) {
                                    optSavings.hidden = false;
                                    optSavings.disabled = false;
                                } else {
                                    optSavings.hidden = true;
                                    optSavings.disabled = true;
                                    targetAccountType.value = 'checking';
                                }
                            } else {
                                initialS.classList.add('d-none');
                                successS.classList.add('d-none');
                                errorS.classList.remove('d-none');
                                card.classList.add('border-dashed');
                                card.classList.remove('bg-white', 'shadow-sm');
                            }
                        });
                } else {
                    document.getElementById('member_initial_state').classList.remove('d-none');
                    document.getElementById('member_success_state').classList.add('d-none');
                    document.getElementById('member_error_state').classList.add('d-none');
                    document.getElementById('member_card_preview').classList.add('border-dashed');
                    document.getElementById('member_card_preview').classList.remove('bg-white', 'shadow-sm');
                    optSavings.hidden = true;
                    optSavings.disabled = true;
                    targetAccountType.value = 'checking';
                }
            });
        }
    });
</script>
</script>
@endsection
