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

                    <!-- Account Bridge (Internal Only) -->
                    <div id="account-bridge-ui" class="d-none">
                        <div class="account-bridge-container">
                            <div class="account-card source" id="bridge-source-card">
                                <span class="label">From Account</span>
                                <span class="acc-name" id="bridge-source-name">Checking (...{{ substr(auth()->user()->account_number, -4) }})</span>
                                <span class="acc-balance" id="bridge-source-balance">$ {{ number_format(auth()->user()->balance, 2) }}</span>
                                <span class="new-balance d-none" id="bridge-source-new-info">New: <span id="bridge-source-new-val"></span></span>
                            </div>
                            
                            <div class="swap-btn-wrapper" onclick="swapAccounts()" title="Swap Accounts">
                                <i class="fas fa-exchange-alt"></i>
                            </div>

                            <div class="account-card destination" id="bridge-dest-card">
                                <span class="label">To Account</span>
                                <span class="acc-name" id="bridge-dest-name">Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S)</span>
                                <span class="acc-balance" id="bridge-dest-balance">$ {{ number_format(auth()->user()->savings_balance, 2) }}</span>
                                <span class="new-balance d-none" id="bridge-dest-new-info">New: <span id="bridge-dest-new-val"></span></span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12" id="standardAccountSelect">
                            <label class="form-label small text-uppercase fw-bold text-muted">From Account</label>
                            <select name="wallet_type" class="form-select form-select-lg border-2 shadow-none" id="walletSelect" onchange="updateAccountOptions()">
                                @if($wallets->isEmpty())
                                    <option value="default" data-name="Checking (...{{ substr(auth()->user()->account_number, -4) }})" data-type="checking" data-can-from="true" data-balance="{{ auth()->user()->balance }}">
                                        Checking (...{{ substr(auth()->user()->account_number, -4) }}) - $ {{ number_format(auth()->user()->balance, 2) }}
                                    </option>
                                @else
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->currency->code }}" data-name="Checking (...{{ substr(auth()->user()->account_number, -4) }})" data-type="checking" data-can-from="true" data-balance="{{ $wallet->balance }}">
                                            Checking (...{{ substr(auth()->user()->account_number, -4) }}) - $ {{ number_format($wallet->balance, 2) }}
                                        </option>
                                    @endforeach
                                @endif
                                <option value="primary_savings" data-name="Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S)" data-type="savings" data-can-from="true" data-balance="{{ auth()->user()->savings_balance }}">
                                    Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S) - $ {{ number_format(auth()->user()->savings_balance, 2) }}
                                </option>
                                @if(auth()->user()->heloc_status == 1)
                                <option value="heloc" data-name="HELOC Account (...{{ substr(auth()->user()->heloc_account_number ?? auth()->user()->account_number, -4) }}H)" data-type="heloc" data-can-from="true" data-balance="{{ auth()->user()->heloc_credit_limit - auth()->user()->heloc_balance }}">
                                    HELOC Account (...{{ substr(auth()->user()->heloc_account_number ?? auth()->user()->account_number, -4) }}H) - $ {{ number_format(auth()->user()->heloc_credit_limit - auth()->user()->heloc_balance, 2) }} (Available)
                                </option>
                                @endif
                                @if(auth()->user()->ira_status == 1)
                                <option value="ira" data-name="IRA Account (...{{ substr(auth()->user()->ira_account_number ?? auth()->user()->account_number, -4) }}I)" data-type="ira" data-can-from="false" data-balance="{{ auth()->user()->ira_balance }}" disabled class="bg-light opacity-50">
                                    IRA Account (...{{ substr(auth()->user()->ira_account_number ?? auth()->user()->account_number, -4) }}I) - Contribution Only
                                </option>
                                @endif
                                @if(auth()->user()->cc_status == 1)
                                <option value="cc" data-name="Credit Card (...{{ substr(auth()->user()->cc_account_number ?? auth()->user()->account_number, -4) }}C)" data-type="cc" data-can-from="false" data-balance="{{ auth()->user()->cc_credit_limit - auth()->user()->cc_balance }}" disabled class="bg-light opacity-50">
                                    Credit Card (...{{ substr(auth()->user()->cc_account_number ?? auth()->user()->account_number, -4) }}C) - Pay Down Only
                                </option>
                                @endif
                                @if(auth()->user()->loan_account_status == 1)
                                <option value="loan" data-name="Loan Account (...{{ substr(auth()->user()->loan_account_number ?? auth()->user()->account_number, -4) }}L)" data-type="loan" data-can-from="false" data-balance="{{ auth()->user()->loan_balance }}" disabled class="bg-light opacity-50">
                                    Loan Account (...{{ substr(auth()->user()->loan_account_number ?? auth()->user()->account_number, -4) }}L) - Pay Down Only
                                </option>
                                @endif
                            </select>
                        </div>

                        <div class="col-12" id="toRecipientSection">
                            <div class="dynamic-field d-none" id="field-self">
                                <label class="form-label small text-uppercase fw-bold text-muted">To Account</label>
                                <select name="to_wallet" class="form-select form-select-lg border-2 shadow-none" id="toWalletSelect" onchange="updateBridgeUI()"></select>
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
                                <div class="alert alert-primary border-0 rounded-4 p-4 mb-0">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                            <i class="fas fa-search"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold">Smart Bank Discovery Enabled</h6>
                                            <p class="small mb-0 opacity-75">We'll automatically detect the bank and branch instantly once you provide the routing number in the next step.</p>
                                        </div>
                                    </div>
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
                            <div id="quickAmountContainer" class="d-none">
                                <div class="quick-amounts">
                                    <button type="button" class="btn-quick" onclick="setQuickAmount(0.25)">25%</button>
                                    <button type="button" class="btn-quick" onclick="setQuickAmount(0.50)">50%</button>
                                    <button type="button" class="btn-quick" onclick="setQuickAmount(1.00)">100%</button>
                                    <button type="button" class="btn-quick d-none" id="payoffBtn" onclick="setPayoffAmount()">Full Payoff</button>
                                </div>
                            </div>
                            <div class="small mt-2" id="balanceFeedback"></div>
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
                            <label class="form-label small text-uppercase fw-bold text-muted">Routing Number</label>
                            <div class="input-group">
                                <input type="text" inputmode="numeric" name="manual_data[routing_number]" id="routing_number" class="form-control form-control-lg border-2 shadow-sm" placeholder="9 digits numeric" maxlength="9" pattern="[0-9]{9}" oninput="lookupRouting(this.value)">
                                <span class="input-group-text bg-light border-2 border-start-0 d-none" id="routing_loader"><span class="spinner-border spinner-border-sm text-primary"></span></span>
                            </div>
                            <div id="routing_feedback" class="mt-2 small d-none">
                                <div class="d-flex align-items-center p-2 rounded-3 bg-light border">
                                    <div class="bank-logo-placeholder me-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 800; font-size: 14px;" id="bank_initials">?</div>
                                    <div>
                                        <div class="fw-bold text-dark" id="bank_name_found">Detecting Bank...</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Verified Routing Number</div>
                                    </div>
                                    <div class="ms-auto">
                                         <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold" onclick="showManualBank()">Edit Name</button>
                                    </div>
                                </div>
                            </div>
                            <div id="manual_bank_container" class="mt-2 d-none">
                                <label class="form-label small text-uppercase fw-bold text-muted">Bank Name (Manual)</label>
                                <input type="text" name="manual_data[bank_name]" id="bank_name_manual" class="form-control border-2 shadow-none" placeholder="Enter bank name manually">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Full Name</label>
                            <input type="text" name="manual_data[account_name]" class="form-control form-control-lg border-2 shadow-none" placeholder="Recipient's legal name">
                        </div>
                        <div class="col-md-12">
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
                    
                    <h4 class="mb-4 text-center fw-bold">Transfer Receipt</h4>
                    
                    <div class="receipt-card bg-white mx-auto shadow-lg" style="max-width: 450px; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden;">
                        <div class="receipt-header p-4 text-center" style="background: linear-gradient(135deg, #00549b 0%, #003d70 100%); color: #fff;">
                            <div class="mb-2 small text-uppercase opacity-75 fw-bold">Total Transfer Amount</div>
                            <div class="h1 mb-1 fw-bold" id="reviewAmount"></div>
                            <div class="small opacity-75" id="reviewDateSub">Scheduled for today</div>
                        </div>
                        
                        <div class="receipt-body p-4">
                            <div class="receipt-row d-flex justify-content-between mb-3">
                                <span class="text-muted small fw-bold text-uppercase">From</span>
                                <span class="fw-bold text-end" id="reviewFrom"></span>
                            </div>
                            <div class="receipt-row d-flex justify-content-between mb-3">
                                <span class="text-muted small fw-bold text-uppercase">To</span>
                                <span class="fw-bold text-end" id="reviewTo"></span>
                            </div>
                            <div class="receipt-row d-flex justify-content-between mb-3 border-top pt-3">
                                <span class="text-muted small fw-bold text-uppercase">Delivery</span>
                                <span class="fw-bold text-capitalize" id="reviewType"></span>
                            </div>
                            <div class="receipt-row d-flex justify-content-between mb-3">
                                <span class="text-muted small fw-bold text-uppercase">Scheduled For</span>
                                <span class="fw-bold" id="reviewDate"></span>
                            </div>
                            <div class="receipt-row d-flex justify-content-between mb-3">
                                <span class="text-muted small fw-bold text-uppercase">Frequency</span>
                                <span class="fw-bold text-capitalize" id="reviewFreq"></span>
                            </div>
                            <div class="receipt-row d-flex justify-content-between mb-3">
                                <span class="text-muted small fw-bold text-uppercase">Purpose</span>
                                <span class="fw-bold" id="reviewPurpose"></span>
                            </div>
                            
                            <div class="external-review-details d-none">
                                <div class="receipt-row d-flex justify-content-between mb-3">
                                    <span class="text-muted small fw-bold text-uppercase">Routing #</span>
                                    <span class="fw-bold" id="reviewRouting"></span>
                                </div>
                                <div class="receipt-row d-flex justify-content-between mb-3">
                                    <span class="text-muted small fw-bold text-uppercase">Account #</span>
                                    <span class="fw-bold" id="reviewAccount"></span>
                                </div>
                            </div>
                            
                            <div class="receipt-row d-flex justify-content-between border-top pt-3 mb-1">
                                <span class="text-muted small fw-bold text-uppercase">Est. Delivery</span>
                                <span class="fw-bold text-success" id="reviewDeliveryEst">1-3 Business Days</span>
                            </div>

                            <div class="alert alert-info border-0 mt-4 small" style="background: rgba(0, 84, 155, 0.05); color: #00549b; border-radius: 12px;">
                                <i class="fas fa-info-circle me-2"></i> Our team will review this transfer. You'll be notified when it's processed.
                            </div>

                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm w-100 fs-5 fw-bold mt-3" id="confirmBtn" onclick="event.preventDefault(); SecurityGate.gate(document.getElementById('transferForm'));">
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                <i class="fas fa-shield-alt me-2"></i> Confirm & Send
                            </button>
                        </div>
                        
                        <div class="receipt-footer p-3 bg-light text-center small text-muted border-top">
                            <i class="fas fa-lock me-1"></i> SECURE 256-BIT ENCRYPTION
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
    /* Account Bridge Styles */
    .account-bridge-container { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; position: relative; }
    .account-card { flex: 1; padding: 20px; border: 2px solid #edeff2; border-radius: 16px; background: #fff; transition: all 0.3s; position: relative; min-height: 120px; }
    .account-card.source { border-color: #e2e8f0; }
    .account-card.destination { border-color: #e2e8f0; }
    .account-card .label { font-size: 0.75rem; font-weight: 800; color: #64748b; text-uppercase; margin-bottom: 8px; display: block; }
    .account-card .acc-name { font-weight: 700; font-size: 1.05rem; color: #1e293b; margin-bottom: 4px; display: block; }
    .account-card .acc-balance { font-size: 1.25rem; font-weight: 800; color: #00549b; display: block; }
    .account-card .new-balance { font-size: 0.85rem; color: #10b981; font-weight: 600; margin-top: 5px; display: block; }
    .account-card .new-balance.negative { color: #ef4444; }
    
    .swap-btn-wrapper { width: 44px; height: 44px; background: #fff; border: 2px solid #00549b; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #00549b; cursor: pointer; z-index: 2; transition: all 0.3s; box-shadow: 0 4px 10px rgba(0, 84, 155, 0.15); }
    .swap-btn-wrapper:hover { background: #00549b; color: #fff; transform: rotate(180deg); }

    .quick-amounts { display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap; }
    .btn-quick { padding: 6px 14px; border: 1px solid #e2e8f0; background: #fff; border-radius: 20px; font-size: 0.85rem; font-weight: 600; color: #475569; transition: all 0.2s; }
    .btn-quick:hover { border-color: #00549b; color: #00549b; background: rgba(0, 84, 155, 0.05); }

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
        .account-bridge-container { flex-direction: column; }
        .swap-btn-wrapper { margin-top: -30px; margin-bottom: -30px; }
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

    window.selectType = function(type, card) {
        transferType = type;
        document.querySelectorAll('.transfer-type-card').forEach(el => el.classList.remove('border-primary'));
        card.classList.add('border-primary');
        const radio = card.querySelector('input[type="radio"]');
        if(radio) radio.checked = true;
        setTimeout(() => goToStep(2), 250);
    };

    function goToStep(step) {
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.add('d-none'));
        const stepEl = document.getElementById('step' + step);
        if(stepEl) {
            stepEl.classList.remove('d-none');
        }
        updateIndicators(step);
        currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        if(step === 2) setupStep2();
    }

    function updateIndicators(step) {
        document.querySelectorAll('.step-indicator').forEach((el, index) => {
            const stepNum = index + 1;
            el.classList.remove('active', 'text-success');
            if (stepNum === step) el.classList.add('active');
            else if (stepNum < step) el.classList.add('text-success');
        });
    }

    function setupStep2() {
        document.querySelectorAll('.dynamic-field').forEach(el => el.classList.add('d-none'));
        const field = document.getElementById('field-' + transferType);
        if(field) field.classList.remove('d-none');
        
        if (transferType === 'self') {
            document.getElementById('account-bridge-ui').classList.remove('d-none');
            document.getElementById('standardAccountSelect').classList.add('d-none');
            document.getElementById('quickAmountContainer').classList.remove('d-none');
        } else {
            document.getElementById('account-bridge-ui').classList.add('d-none');
            document.getElementById('standardAccountSelect').classList.remove('d-none');
            document.getElementById('quickAmountContainer').classList.add('d-none');
        }
        updateAccountOptions();
    }

    function updateAccountOptions() {
        const fromSelect = document.getElementById('walletSelect');
        const toSelect = document.getElementById('toWalletSelect');
        if(!fromSelect || !toSelect) return;
        
        const optionsArr = Array.from(fromSelect.options);
        const selectedOpt = fromSelect.options[fromSelect.selectedIndex];
        const selectedValue = selectedOpt.value;
        const selectedType = selectedOpt.getAttribute('data-type');

        if(transferType === 'self') {
            toSelect.innerHTML = '';
            optionsArr.forEach(opt => {
                if(opt.value !== selectedValue) {
                    const newOpt = opt.cloneNode(true);
                    newOpt.disabled = false;
                    newOpt.classList.remove('opacity-50', 'bg-light');
                    toSelect.add(newOpt);
                }
            });
        }
        updateBridgeUI();
        validateBalance();
    }

    function swapAccounts() {
        if (transferType !== 'self') return;
        const fromSelect = document.getElementById('walletSelect');
        const toSelect = document.getElementById('toWalletSelect');
        
        const currentFrom = fromSelect.value;
        const targetToValue = toSelect.value;
        
        // Smarter swap: only swap if destination is a valid source
        const targetToOpt = toSelect.options[toSelect.selectedIndex];
        const canFrom = targetToOpt.getAttribute('data-can-from') === 'true';
        
        if (!canFrom) {
            Swal.fire({ 
                title: 'Limited Account', 
                text: 'This account type (IRA/Loan/CC) can only receive funds and cannot be used as a transfer source.', 
                icon: 'info', 
                confirmButtonColor: '#00549b' 
            });
            return;
        }

        fromSelect.value = targetToValue;
        updateAccountOptions();
        toSelect.value = currentFrom;
        
        updateBridgeUI();
        validateBalance();
    }

    function updateBridgeUI() {
        if (transferType !== 'self') return;
        const fromSelect = document.getElementById('walletSelect');
        const toSelect = document.getElementById('toWalletSelect');
        const sourceOpt = fromSelect.options[fromSelect.selectedIndex];
        const destOpt = toSelect.options[toSelect.selectedIndex];
        
        if (sourceOpt) {
            document.getElementById('bridge-source-name').innerText = sourceOpt.getAttribute('data-name');
            document.getElementById('bridge-source-balance').innerText = '$ ' + parseFloat(sourceOpt.getAttribute('data-balance')).toLocaleString('en-US', {minimumFractionDigits: 2});
        }
        if (destOpt) {
            document.getElementById('bridge-dest-name').innerText = destOpt.getAttribute('data-name');
            document.getElementById('bridge-dest-balance').innerText = '$ ' + parseFloat(destOpt.getAttribute('data-balance')).toLocaleString('en-US', {minimumFractionDigits: 2});
            
            const toType = destOpt.getAttribute('data-type');
            const payoffBtn = document.getElementById('payoffBtn');
            if (['heloc', 'cc', 'loan'].includes(toType)) payoffBtn.classList.remove('d-none');
            else payoffBtn.classList.add('d-none');
        }
        validateBalance();
    }

    function setQuickAmount(percent) {
        const fromSelect = document.getElementById('walletSelect');
        const balance = parseFloat(fromSelect.options[fromSelect.selectedIndex].getAttribute('data-balance')) || 0;
        document.getElementById('amount').value = (balance * percent).toFixed(2);
        validateBalance();
    }

    function setPayoffAmount() {
        const toSelect = document.getElementById('toWalletSelect');
        const balance = parseFloat(toSelect.options[toSelect.selectedIndex].getAttribute('data-balance')) || 0;
        document.getElementById('amount').value = balance.toFixed(2);
        validateBalance();
    }

    function validateBalance() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const fromSelect = document.getElementById('walletSelect');
        const balance = parseFloat(fromSelect.options[fromSelect.selectedIndex].getAttribute('data-balance'));
        const feedback = document.getElementById('balanceFeedback');
        
        if (transferType === 'self') {
            const sourceInfo = document.getElementById('bridge-source-new-info');
            const destInfo = document.getElementById('bridge-dest-new-info');
            const sourceVal = document.getElementById('bridge-source-new-val');
            const destVal = document.getElementById('bridge-dest-new-val');
            
            if (amount > 0) {
                sourceInfo.classList.remove('d-none');
                destInfo.classList.remove('d-none');
                
                const sNew = balance - amount;
                sourceVal.innerText = '$ ' + sNew.toLocaleString('en-US', {minimumFractionDigits: 2});
                sourceVal.className = sNew < 0 ? 'text-danger' : 'text-success';
                
                const toSelect = document.getElementById('toWalletSelect');
                const dBal = parseFloat(toSelect.options[toSelect.selectedIndex]?.getAttribute('data-balance')) || 0;
                const toType = toSelect.options[toSelect.selectedIndex]?.getAttribute('data-type');
                
                let dNew = dBal + amount;
                if (['heloc', 'cc', 'loan'].includes(toType)) dNew = dBal - amount; 
                
                destVal.innerText = '$ ' + dNew.toLocaleString('en-US', {minimumFractionDigits: 2});
            } else {
                sourceInfo.classList.add('d-none');
                destInfo.classList.add('d-none');
            }
        }
        
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
            Swal.fire({ title: 'Recipient Required', text: 'Please provide recipient\'s info.', icon: 'warning', confirmButtonColor: '#00549b' });
            return;
        }

        if(transferType === 'self' && amount < 1000) {
            populateReview();
            confirmInstantTransfer();
            return;
        }

        if(transferType === 'external') goToStep(3);
        else { populateReview(); goToStep(4); }
    }

    function confirmInstantTransfer() {
        Swal.fire({
            title: 'Confirm Transfer',
            html: `You are moving <b>$${parseFloat(document.getElementById('amount').value).toLocaleString()}</b> instantly.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Transfer Now',
            confirmButtonColor: '#00549b',
            cancelButtonText: 'Edit Details'
        }).then((result) => {
            if (result.isConfirmed) SecurityGate.gate(document.getElementById('transferForm'));
        });
    }

    function validateStep3() {
        const name = document.querySelector('input[name="manual_data[account_name]"]').value;
        const acc1 = document.getElementById('ext_acc_num').value;
        const acc2 = document.getElementById('ext_acc_num_confirm').value;
        const routing = document.getElementById('routing_number').value;

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
        document.getElementById('reviewFrom').innerText = fromSelect.options[fromSelect.selectedIndex].getAttribute('data-name');
        
        let toText = 'Unknown';
        if(transferType === 'self') toText = document.getElementById('toWalletSelect').options[document.getElementById('toWalletSelect').selectedIndex]?.getAttribute('data-name') || 'My Account';
        else if(transferType === 'member') toText = 'Member: ' + document.getElementById('member_identifier').value;
        else toText = document.getElementById('bank_name_found').innerText;
        
        document.getElementById('reviewTo').innerText = toText;
        document.getElementById('reviewAmount').innerText = '$' + parseFloat(document.getElementById('amount').value).toLocaleString();
        
        const scheduledAt = document.querySelector('input[name="scheduled_at"]').value;
        const freqText = document.querySelector('select[name="frequency"]').value;
        const purposeText = document.getElementById('transferPurpose').value || 'Transfer Funds';

        if(document.getElementById('reviewDate')) document.getElementById('reviewDate').innerText = scheduledAt;
        if(document.getElementById('reviewFreq')) document.getElementById('reviewFreq').innerText = freqText;
        if(document.getElementById('reviewPurpose')) document.getElementById('reviewPurpose').innerText = purposeText;
        
        const dateSub = document.getElementById('reviewDateSub');
        const today = new Date().toISOString().split('T')[0];
        dateSub.innerText = scheduledAt === today ? 'Scheduled for today' : 'Scheduled for ' + scheduledAt;

        const deliveryEst = document.getElementById('reviewDeliveryEst');
        if (transferType === 'self') {
            deliveryEst.innerText = 'Instant Transfer';
            document.querySelectorAll('.external-review-details').forEach(el => el.classList.add('d-none'));
        } else {
            deliveryEst.innerText = transferType === 'member' ? 'Same Day' : '1-3 Business Days';
            document.querySelectorAll('.external-review-details').forEach(el => el.classList.remove('d-none'));
            document.getElementById('reviewRouting').innerText = document.getElementById('routing_number').value;
            document.getElementById('reviewAccount').innerText = '****' + document.getElementById('ext_acc_num').value.slice(-4);
        }
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

    function showManualBank() {
        document.getElementById('manual_bank_container').classList.remove('d-none');
    }

    function lookupRouting(rn) {
        const val = rn.replace(/[^0-9]/g, '');
        document.getElementById('routing_number').value = val;
        const feedback = document.getElementById('routing_feedback');
        const loader = document.getElementById('routing_loader');
        const bankNameText = document.getElementById('bank_name_found');
        const bankInitials = document.getElementById('bank_initials');

        if (val.length < 9) { feedback.classList.add('d-none'); return; }
        loader.classList.remove('d-none');
        
        fetch(`/user/fund-transfer/routing-lookup/${val}`)
            .then(res => res.json())
            .then(data => {
                loader.classList.add('d-none');
                feedback.classList.remove('d-none');
                if (data.name) {
                    bankNameText.innerText = data.name;
                    bankInitials.innerText = data.name.charAt(0);
                    bankInitials.style.background = '#00549b';
                } else {
                    bankNameText.innerText = 'US Bank Account';
                    bankInitials.innerText = 'US';
                    bankInitials.style.background = '#64748b';
                }
            })
            .catch(() => {
                loader.classList.add('d-none');
                feedback.classList.remove('d-none');
                bankNameText.innerText = 'Domestic Bank Found';
                bankInitials.innerText = 'BK';
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.js-type-select').forEach(card => {
            card.addEventListener('click', function() {
                window.selectType(this.getAttribute('data-type'), this);
            });
        });
        
        const memberInput = document.getElementById('member_identifier');
        if(memberInput) {
            memberInput.addEventListener('input', function() {
                const val = this.value;
                const nameDisplay = document.getElementById('member_name_display_text');
                if(val.length > 4) {
                    fetch('/user/search-by-account-number/' + encodeURIComponent(val))
                        .then(res => res.json())
                        .then(data => {
                            if(data.name) {
                                nameDisplay.innerText = data.name;
                                nameDisplay.classList.add('fw-bold', 'text-dark');
                            } else {
                                nameDisplay.innerText = 'Member not Found!';
                                nameDisplay.classList.add('text-danger');
                            }
                        });
                }
            });
        }
    });
</script>
@endsection
