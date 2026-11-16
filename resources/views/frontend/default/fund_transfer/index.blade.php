@extends('frontend::layouts.user')

@section('title')
    {{ __('Transfer Funds') }}
@endsection

@section('content')
@php
    $isCustomInst = !empty($currentInstitution) && !$currentInstitution->is_default;
    $brandPrimary = $isCustomInst ? '#c8232c' : 'var(--bs-primary, #00549b)';
    $brandMemberTitle = 'Another Member';
    $brandMemberLabel = $isCustomInst ? ($currentInstitution->name . ' Member') : ((setting('site_title') ?? 'FrontField') . ' Member');
    $brandMemberDesc = $isCustomInst ? ('Send instantly to a ' . $currentInstitution->name . ' member.') : 'Send instantly to a credit union member.';
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

        <div class="banno-card p-0 mb-4 shadow-sm" style="border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; background: #ffffff;">
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
                <input type="hidden" name="bank_id" id="resolvedBankId">
                
                <!-- Step 1: Transfer Type -->
                <div class="wizard-step active p-4 p-md-5" id="step1">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1">Select Transfer Method</h4>
                        <p class="text-muted small">Choose how you want to route funds securely.</p>
                    </div>

                    <div class="row g-3 justify-content-center">
                        <!-- Option 1: Internal -->
                        <div class="col-md-6 col-12">
                            <div class="transfer-type-card csb-method-tile js-type-select h-100 p-3 p-md-4 border rounded-3 d-flex align-items-start gap-3 cursor-pointer" data-type="self">
                                <input type="radio" name="transfer_type" value="self" class="d-none">
                                <div class="csb-method-icon rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: rgba(200, 35, 44, 0.08); color: #c8232c; font-size: 20px;">
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

                        <!-- Option 2: Another Member -->
                        <div class="col-md-6 col-12">
                            <div class="transfer-type-card csb-method-tile js-type-select h-100 p-3 p-md-4 border rounded-3 d-flex align-items-start gap-3 cursor-pointer" data-type="member">
                                <input type="radio" name="transfer_type" value="member" class="d-none">
                                <div class="csb-method-icon rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: rgba(27, 50, 95, 0.08); color: #1b325f; font-size: 20px;">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="fw-bold text-dark mb-0">{{ $brandMemberTitle }}</h6>
                                        <span class="badge bg-primary-subtle text-primary small font-monospace" style="font-size: 10px;">Instant Member</span>
                                    </div>
                                    <p class="small text-muted mb-0">{{ $brandMemberDesc }} Verified member-to-member routing.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Option 3: Zelle -->
                        <div class="col-md-6 col-12">
                            <div class="transfer-type-card csb-method-tile h-100 p-3 p-md-4 border rounded-3 d-flex align-items-start gap-3 cursor-pointer" onclick="window.location.href='{{ route('user.fund_transfer.zelle') }}'">
                                <div class="csb-method-icon rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: rgba(116, 20, 202, 0.08); font-size: 20px;">
                                    <img src="{{ asset('assets/external/images/Zelle-logo-no-tagline-white.png') }}" alt="Zelle" style="height: 18px; filter: brightness(0) saturate(100%) invert(18%) sepia(87%) saturate(3786%) hue-rotate(270deg) brightness(85%) contrast(106%);">
                                </div>
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="fw-bold mb-0" style="color: #7414ca;">Send with Zelle®</h6>
                                        <span class="badge small font-monospace" style="background: rgba(116, 20, 202, 0.1); color: #7414ca; font-size: 10px;">Minutes</span>
                                    </div>
                                    <p class="small text-muted mb-0">Fast, safe and easy way to send money using mobile number or email.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Option 4: External ACH & Wire -->
                        <div class="col-md-6 col-12">
                            <div class="transfer-type-card csb-method-tile js-type-select h-100 p-3 p-md-4 border rounded-3 d-flex align-items-start gap-3 cursor-pointer" data-type="external">
                                <input type="radio" name="transfer_type" value="external" class="d-none">
                                <div class="csb-method-icon rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: rgba(15, 118, 110, 0.08); color: #0f766e; font-size: 20px;">
                                    <i class="fa-solid fa-building-columns"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="fw-bold text-dark mb-0">External Bank &amp; Wire</h6>
                                        <span class="badge bg-info-subtle text-info-emphasis small font-monospace" style="font-size: 10px;">ACH / Wire</span>
                                    </div>
                                    <p class="small text-muted mb-0">Send via standard ACH (1-3 days) or Same-Day Federal Wire.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Core Details -->
                <div class="wizard-step p-4 p-md-5 d-none" id="step2">
                    <div class="d-flex align-items-center mb-4 cursor-pointer text-primary" onclick="goToStep(1)">
                        <i class="fas fa-arrow-left me-2"></i> <span class="small fw-bold">Back to Transfer Types</span>
                    </div>

                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">From Account</label>
                            <select name="wallet_type" class="form-select form-select-lg border-2 shadow-none" id="walletSelect" onchange="checkRestriction(); updateAccountOptions()">
                                @if($wallets->isEmpty())
                                    <option value="default" data-type="checking" data-balance="{{ auth()->user()->balance }}" @disabled(auth()->user()->isRestricted('checking'))>
                                        Checking (...{{ substr(auth()->user()->account_number, -4) }}) - ${{ number_format((float)auth()->user()->balance, 2) }}
                                        @if(auth()->user()->isRestricted('checking')) (Restricted) @endif
                                    </option>
                                @else
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->currency->code }}" data-type="checking" data-balance="{{ $wallet->balance }}" @disabled(auth()->user()->isRestricted('checking'))>
                                            Checking (...{{ substr(auth()->user()->account_number, -4) }}) - ${{ number_format((float)$wallet->balance, 2) }}
                                            @if(auth()->user()->isRestricted('checking')) (Restricted) @endif
                                        </option>
                                    @endforeach
                                @endif
                                <option value="primary_savings" data-type="savings" data-balance="{{ auth()->user()->savings_balance }}" @disabled(auth()->user()->isRestricted('savings'))>
                                    Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S) - ${{ number_format((float)auth()->user()->savings_balance, 2) }}
                                    @if(auth()->user()->isRestricted('savings')) (Restricted) @endif
                                </option>
                                @if(auth()->user()->ira_status == 1)
                                <option value="ira" data-type="ira" data-balance="{{ auth()->user()->ira_balance }}" @disabled(auth()->user()->isRestricted('ira'))>
                                    IRA Account (...{{ substr(auth()->user()->ira_account_number ?? auth()->user()->account_number, -4) }}I) - ${{ number_format((float)auth()->user()->ira_balance, 2) }}
                                    @if(auth()->user()->isRestricted('ira')) (Restricted) @endif
                                </option>
                                @endif
                                @if(auth()->user()->heloc_status == 1)
                                <option value="heloc" data-type="heloc" data-balance="{{ auth()->user()->heloc_credit_limit - auth()->user()->heloc_balance }}" @disabled(auth()->user()->isRestricted('heloc'))>
                                    HELOC Account (...{{ substr(auth()->user()->heloc_account_number ?? auth()->user()->account_number, -4) }}H) - ${{ number_format((float)(auth()->user()->heloc_credit_limit - auth()->user()->heloc_balance), 2) }} (Available)
                                    @if(auth()->user()->isRestricted('heloc')) (Restricted) @endif
                                </option>
                                @endif
                                @if(auth()->user()->cc_status == 1)
                                <option value="cc" data-type="cc" data-balance="{{ auth()->user()->cc_credit_limit - auth()->user()->cc_balance }}" @disabled(auth()->user()->isRestricted('cc'))>
                                    Credit Card (...{{ substr(auth()->user()->cc_account_number ?? auth()->user()->account_number, -4) }}C) - ${{ number_format((float)(auth()->user()->cc_credit_limit - auth()->user()->cc_balance), 2) }} (Available)
                                    @if(auth()->user()->isRestricted('cc')) (Restricted) @endif
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
                                <div class="row g-3 g-md-4">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label small text-uppercase fw-bold text-muted mb-2">Recipient email or member account number</label>
                                        <input type="text" name="member_identifier" id="member_identifier" class="form-control form-control-lg border-2 shadow-none" placeholder="Registered email or account number" autocomplete="off">
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
                                            @if(auth()->user()->ira_status == 1)
                                            <option value="ira" id="opt-ira" hidden disabled>IRA</option>
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

                            <div class="dynamic-field d-none" id="field-external">
                                <div class="p-3 bg-light rounded-3 border mb-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="fas fa-bolt text-warning me-1"></i> {{ __('Need a Fast Domestic or International Wire Transfer?') }}</div>
                                        <div class="extra-small text-muted">{{ __('Send same-day high-value transfers via Fedwire or global SWIFT network.') }}</div>
                                    </div>
                                    <a href="{{ route('user.fund_transfer.transfer.wire') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-2 fw-bold text-nowrap shadow-xs">
                                        <i class="fas fa-paper-plane me-1"></i> {{ __('Wire Transfer') }}
                                    </a>
                                </div>
                                <p class="small text-muted mb-0">
                                    {{ __('Or continue below to send standard External Bank ACH (1-3 Business Days). You will enter the recipient’s routing and account information on the next step.') }}
                                </p>
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
                            <input type="text" name="manual_data[account_name]" id="ext_manual_account_name" class="form-control form-control-lg border-2 shadow-none" placeholder="Recipient's legal name" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">Routing Number</label>
                            <div class="input-group">
                                <input type="text" inputmode="numeric" name="manual_data[routing_number]" id="routingNumberInput" class="form-control form-control-lg border-2 shadow-sm" placeholder="9 digits routing number" maxlength="9" pattern="[0-9]{9}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" disabled>
                            </div>
                            <div class="small mt-2" id="routingLookupStatus"></div>
                            <div class="routing-lookup-verified d-none mt-2 p-3 rounded-3" id="routingLookupCard">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="routing-lookup-verified__icon" aria-hidden="true"><i class="fas fa-university"></i></span>
                                    <div class="min-w-0">
                                        <div class="fw-bold text-dark" id="lookupBankName"></div>
                                        <div class="small routing-lookup-verified__sub" id="lookupBankState">Receiving financial institution</div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="manual_data[bank_name]" id="resolvedBankName" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">Account Number</label>
                            <div class="input-group">
                                <input type="password" name="manual_data[account_number]" id="ext_acc_num" class="form-control form-control-lg border-2 shadow-sm toggle-password" placeholder="4-20 digits numeric" minlength="4" maxlength="20" oninput="this.value = this.value.replace(/[^0-9]/g, '')" disabled>
                                <button class="btn btn-outline-secondary border-2 border-start-0 bg-white" type="button" onclick="toggleVisibility(this)"><i class="fas fa-eye-slash"></i></button>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Confirm Account Number</label>
                            <div class="input-group">
                                <input type="password" id="ext_acc_num_confirm" class="form-control form-control-lg border-2 shadow-sm toggle-password" placeholder="Re-enter to confirm" disabled>
                                <button class="btn btn-outline-secondary border-2 border-start-0 bg-white" type="button" onclick="toggleVisibility(this)"><i class="fas fa-eye-slash"></i></button>
                            </div>
                        </div>
                        <div class="col-12 d-none" id="manualBankNameWrap">
                            <label class="form-label small text-uppercase fw-bold text-muted">Receiving institution name</label>
                            <input type="text" name="manual_data[bank_name_manual]" id="manualBankNameInput" class="form-control form-control-lg border-2 shadow-none" placeholder="As shown on your check or bank statement" disabled>
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
                                        <div class="col-md-6 mb-3"><span class="text-muted d-block small text-uppercase fw-bold">Recipient Bank Name</span><span class="fw-bold" id="reviewBankName"></span></div>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-4">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between"><span class="small text-muted">Transfer Amount</span><span class="fw-bold" id="reviewTransferAmount"></span></div>
                                <div class="d-flex justify-content-between"><span class="small text-muted">Fee</span><span class="fw-bold" id="reviewFeeAmount"></span></div>
                                <div class="d-flex justify-content-between border-top pt-2 mt-2"><span class="h6 mb-0 fw-bold">Total Deducted</span><span class="h5 mb-0 text-primary fw-bold" id="reviewAmount"></span></div>
                            </div>

                            <button type="button" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm w-100 fs-5 fw-bold d-flex align-items-center justify-content-center" id="confirmBtn" onclick="window.csbSubmitTransferForm()">
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                                <span class="btn-label"><i class="fas fa-arrow-right me-2"></i> {{ __('Submit Transfer') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ════════ TRANSFER CONFIRMATION MODAL ════════ -->
<div class="modal fade" id="transferConfirmationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-center p-4" style="border-radius: 20px;">
            <div class="mb-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2" style="width: 72px; height: 72px; background: rgba(16, 185, 129, 0.12); color: #10b981; font-size: 32px;">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Transfer Successful!</h4>
                <p class="text-muted small mb-0">Your funds transfer request has been submitted sucessfully.</p>
            </div>

            <div class="p-3 rounded-3 bg-light border text-start mb-3" style="font-size: 13.5px;">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Confirmation Ref</span>
                    <span class="fw-bold font-monospace text-dark" id="confirmModalRef">TRX000000</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Total Amount</span>
                    <span class="fw-bold text-dark" id="confirmModalAmount">$0.00</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Transfer Type</span>
                    <span class="fw-bold text-dark" id="confirmModalType">Transfer</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Recipient / To</span>
                    <span class="fw-bold text-dark" id="confirmModalTo">—</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Effective Date</span>
                    <span class="text-dark" id="confirmModalDate">—</span>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Status</span>
                    <span class="badge bg-success-subtle text-success fw-bold">Completed</span>
                </div>
            </div>

            <div class="d-flex flex-column gap-2">
                <a href="{{ route('user.dashboard') }}" class="btn text-white fw-bold py-2 rounded-pill shadow-sm" style="background: {{ $brandPrimary }};">
                    Done &amp; Return to Dashboard
                </a>
                <a href="{{ route('user.transactions') }}" class="btn btn-outline-secondary fw-semibold py-2 rounded-pill">
                    View in Transaction History
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
    @if($isCustomInst)
    :root {
        --primary-color: #c8232c;
    }
    .banno-card {
        border-radius: 20px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06) !important;
        overflow: hidden !important;
    }
    .wizard-header {
        background: linear-gradient(135deg, #2b0407 0%, #4a0307 50%, #1f0205 100%) !important;
        color: #fff !important;
        border-bottom: 2px solid #c8232c !important;
    }
    .wizard-header .step-indicator {
        color: rgba(255,255,255,0.65) !important;
    }
    .wizard-header .step-circle {
        background: rgba(255,255,255,0.15) !important;
        color: #fff !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
    }
    .wizard-header .step-indicator.active {
        color: #fff !important;
    }
    .wizard-header .step-indicator.active .step-circle {
        background: #c8232c !important;
        border-color: #ff6b72 !important;
        box-shadow: 0 0 14px rgba(200, 35, 44, 0.6) !important;
        color: #fff !important;
    }
    .wizard-header .step-line {
        background: rgba(255,255,255,0.2) !important;
    }
    .transfer-type-card {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        background: #ffffff !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03) !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .transfer-type-card:hover {
        transform: translateY(-2px) !important;
        border-color: #c8232c !important;
        box-shadow: 0 8px 20px rgba(200, 35, 44, 0.08) !important;
    }
    .transfer-type-card.border-primary {
        border-color: #c8232c !important;
        border-left: 4px solid #c8232c !important;
        background: #fff8f8 !important;
        box-shadow: 0 4px 14px rgba(200, 35, 44, 0.12) !important;
    }
    .csb-method-tile {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        background: #ffffff !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .csb-method-tile:hover {
        transform: translateY(-2px) !important;
        border-color: #c8232c !important;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05) !important;
    }
    .csb-method-tile.border-primary {
        border-color: #c8232c !important;
        border-left: 4px solid #c8232c !important;
        background: #fff8f8 !important;
        box-shadow: 0 4px 14px rgba(200, 35, 44, 0.12) !important;
    }
    .btn-primary {
        background: linear-gradient(135deg, #c8232c 0%, #a81b24 100%) !important;
        border: none !important;
        box-shadow: 0 6px 16px rgba(200, 35, 44, 0.3) !important;
    }
    .btn-primary:hover, .btn-primary:focus {
        background: linear-gradient(135deg, #a81b24 0%, #8b0000 100%) !important;
        box-shadow: 0 8px 22px rgba(200, 35, 44, 0.45) !important;
        transform: translateY(-1px) !important;
    }
    .form-control:focus, .form-select:focus {
        border-color: #c8232c !important;
        box-shadow: 0 0 0 4px rgba(200, 35, 44, 0.12) !important;
    }
    #reviewAmount {
        color: #c8232c !important;
    }
    @endif
    .title-responsive {
        white-space: normal !important;
        line-height: 1.25 !important;
    }
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
        .wizard-step { padding: 1rem !important; }
        .transfer-type-card { padding: 1rem 0.5rem !important; border-radius: 12px !important; }
        .icon-responsive { width: 44px !important; height: 44px !important; }
        .icon-responsive i { font-size: 1.1rem !important; }
        .img-responsive { max-height: 22px !important; }
        .title-responsive { font-size: 0.85rem !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .x-small { font-size: 0.7rem !important; }
        .form-control-lg, .form-select-lg { font-size: 1rem; padding: 0.75rem 1rem; }
        h4 { font-size: 1.15rem; }
    }

    /* Fixed size for desktop to avoid jumping */
    .icon-circle { width: 64px; height: 64px; }
    
    ::placeholder { font-size: 0.85rem !important; opacity: 0.7; }
    .form-control::-webkit-input-placeholder { font-size: 0.85rem !important; }
    .form-control:-ms-input-placeholder { font-size: 0.85rem !important; }

    /* Verified receiving institution (routing lookup) */
    .routing-lookup-verified {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border: 2px solid #34d399;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.2);
    }
    .routing-lookup-verified__icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: #059669;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
    }
    .routing-lookup-verified__sub { color: #047857; font-weight: 600; margin-top: 0.2rem; }

    /* Member recipient verification (same as dedicated member transfer page) */
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
    var currentStep = 1;
    var transferType = null;
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
        const selectedType = fromSelect.value;
        
        if (userRestrictions[selectedType]) {
            Swal.fire({
                title: 'Account Restricted',
                text: 'This account is currently frozen and cannot be used for transfers. Please contact support for assistance.',
                icon: 'error',
                confirmButtonColor: '#00549b'
            });
            // Reset to first non-disabled option
            for (let i = 0; i < fromSelect.options.length; i++) {
                if (!fromSelect.options[i].disabled) {
                    fromSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }

    // Moving this to a direct function so it's globally available
    window.selectType = function(type, card) {
        console.log('selectType called via listener:', type);
        transferType = type;
        
        document.querySelectorAll('.transfer-type-card').forEach(el => el.classList.remove('border-primary'));
        card.classList.add('border-primary');
        const radio = card.querySelector('input[type="radio"]');
        if(radio) radio.checked = true;

        syncTransferFormFields();

        setTimeout(() => goToStep(2), 250);
    };

    /** Avoid duplicate manual_data / external fields in POST; enable only the active transfer path. */
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
            bankIdEl.value = '';
        }
    }

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
        function updateAccountOptions() {
        const fromSelect = document.getElementById('walletSelect');
        const toSelect = document.getElementById('toWalletSelect');
        if(!fromSelect || !toSelect) return;
        const selectedType = fromSelect.options[fromSelect.selectedIndex].getAttribute('data-type');
        
        if(transferType === 'self') {
            toSelect.innerHTML = '';
            
            // Add Checking
            if(selectedType !== 'checking') {
                const isRestricted = userRestrictions['default'];
                if({{ $wallets->isEmpty() ? 'true' : 'false' }}) {
                    var opt = document.createElement('option');
                    opt.value = 'default';
                    opt.text = 'Checking (...{{ substr(auth()->user()->account_number, -4) }})' + (isRestricted ? ' (Restricted)' : '') + ' - ${{ number_format((float)auth()->user()->balance, 2) }}';
                    if(isRestricted) opt.disabled = true;
                    toSelect.add(opt);
                } else {
                    @foreach($wallets as $wallet)
                        var opt = document.createElement('option');
                        opt.value = '{{ $wallet->currency->code }}';
                        opt.text = 'Checking (...{{ substr(auth()->user()->account_number, -4) }})' + (isRestricted ? ' (Restricted)' : '') + ' - ${{ number_format((float)$wallet->balance, 2) }}';
                        if(isRestricted) opt.disabled = true;
                        toSelect.add(opt);
                    @endforeach
                }
            }

            // Add Savings
            if(selectedType !== 'savings') {
                const isRestricted = userRestrictions['primary_savings'];
                const opt = document.createElement('option');
                opt.value = 'primary_savings';
                opt.text = 'Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S)' + (isRestricted ? ' (Restricted)' : '') + ' - ${{ number_format((float)auth()->user()->savings_balance, 2) }}';
                if(isRestricted) opt.disabled = true;
                toSelect.add(opt);
            }

            // Add IRA
            @if(auth()->user()->ira_status == 1)
            if(selectedType !== 'ira') {
                const isRestricted = userRestrictions['ira'];
                const opt = document.createElement('option');
                opt.value = 'ira';
                opt.text = 'IRA Account (...{{ substr(auth()->user()->ira_account_number ?? auth()->user()->account_number, -4) }}I)' + (isRestricted ? ' (Restricted)' : '') + ' - ${{ number_format((float)auth()->user()->ira_balance, 2) }}';
                if(isRestricted) opt.disabled = true;
                toSelect.add(opt);
            }
            @endif

            // Add HELOC
            @if(auth()->user()->heloc_status == 1)
            if(selectedType !== 'heloc') {
                const isRestricted = userRestrictions['heloc'];
                const opt = document.createElement('option');
                opt.value = 'heloc';
                opt.text = 'HELOC Account (...{{ substr(auth()->user()->heloc_account_number ?? auth()->user()->account_number, -4) }}H)' + (isRestricted ? ' (Restricted)' : '') + ' - Pay Down Balance: ${{ number_format((float)auth()->user()->heloc_balance, 2) }}';
                if(isRestricted) opt.disabled = true;
                toSelect.add(opt);
            }
            @endif

            // Add Credit Card
            @if(auth()->user()->cc_status == 1)
            if(selectedType !== 'cc') {
                const isRestricted = userRestrictions['cc'];
                const opt = document.createElement('option');
                opt.value = 'cc';
                opt.text = 'Credit Card (...{{ substr(auth()->user()->cc_account_number ?? auth()->user()->account_number, -4) }}C)' + (isRestricted ? ' (Restricted)' : '') + ' - Pay Down Balance: ${{ number_format((float)auth()->user()->cc_balance, 2) }}';
                if(isRestricted) opt.disabled = true;
                toSelect.add(opt);
            }
            @endif

            // Add Loan
            @if(auth()->user()->loan_account_status == 1)
            if(selectedType !== 'loan') {
                const isRestricted = userRestrictions['loan'];
                const opt = document.createElement('option');
                opt.value = 'loan';
                opt.text = 'Loan Account (...{{ substr(auth()->user()->loan_account_number ?? auth()->user()->account_number, -4) }}L)' + (isRestricted ? ' (Restricted)' : '') + ' - Pay Down Balance: ${{ number_format((float)auth()->user()->loan_balance, 2) }}';
                if(isRestricted) opt.disabled = true;
                toSelect.add(opt);
            }
            @endif
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
        if(transferType === 'member') {
            if(!document.getElementById('member_identifier').value) {
                Swal.fire({ title: 'Recipient Required', text: 'Please provide the recipient\'s email or member account number.', icon: 'warning', confirmButtonColor: '#00549b' });
                return;
            }
            var accNm = document.getElementById('account_name');
            if(!accNm || !accNm.value.trim() || accNm.disabled) {
                Swal.fire({ title: 'Verify recipient', text: 'Please enter a valid member so we can verify the recipient before continuing.', icon: 'warning', confirmButtonColor: '#00549b' });
                return;
            }
        }
        if(transferType === 'external') goToStep(3);
        else { populateReview(); goToStep(4); }
    }

    function isValidAbaChecksumAch(routing) {
        if (!/^\d{9}$/.test(routing)) return false;
        const d = routing.split('').map(Number);
        const sum = (3 * (d[0] + d[3] + d[6])) + (7 * (d[1] + d[4] + d[7])) + (d[2] + d[5] + d[8]);
        return sum % 10 === 0;
    }

    function validateStep3() {
        var nameEl = document.getElementById('ext_manual_account_name');
        const name = nameEl ? nameEl.value : '';
        const acc1 = document.getElementById('ext_acc_num').value;
        const acc2 = document.getElementById('ext_acc_num_confirm').value;
        const routing = document.querySelector('input[name="manual_data[routing_number]"]').value;

        if(!name || !acc1 || routing.length < 9) {
            Swal.fire({ title: 'Details Missing', text: 'Please complete all recipient information.', icon: 'warning', confirmButtonColor: '{{ $brandPrimary }}' });
            return;
        }
        if(acc1 !== acc2) {
            Swal.fire({ title: 'Mismatch', text: 'Account numbers do not match.', icon: 'error', confirmButtonColor: '{{ $brandPrimary }}' });
            return;
        }
        if(!isValidAbaChecksumAch(routing)) {
            var manualWrapEl = document.getElementById('manualBankNameWrap');
            var manualInputEl = document.getElementById('manualBankNameInput');
            if (manualWrapEl) manualWrapEl.classList.remove('d-none');
            const manualName = manualInputEl ? manualInputEl.value.trim() : '';
            if(!manualName) {
                if (manualInputEl) manualInputEl.focus();
                Swal.fire({
                    title: 'Institution Name Required',
                    text: 'Routing number could not be verified automatically. Please enter the financial institution name below to continue.',
                    icon: 'info',
                    confirmButtonColor: '{{ $brandPrimary }}'
                });
                return;
            }
            document.getElementById('resolvedBankName').value = manualName;
        } else if(!document.getElementById('resolvedBankId').value) {
            var manualWrapEl = document.getElementById('manualBankNameWrap');
            var manualInputEl = document.getElementById('manualBankNameInput');
            if (manualWrapEl) manualWrapEl.classList.remove('d-none');
            const manualName = manualInputEl ? manualInputEl.value.trim() : '';
            if(!manualName) {
                if (manualInputEl) manualInputEl.focus();
                Swal.fire({
                    title: 'Receiving institution required',
                    text: 'We could not verify your routing number automatically. Enter the receiving financial institution\'s name as it appears on your records.',
                    icon: 'warning',
                    confirmButtonColor: '{{ $brandPrimary }}'
                });
                return;
            }
            document.getElementById('resolvedBankName').value = manualName;
        }
        populateReview();
        goToStep(4);
    }

    function getExternalChargeMeta() {
        if(resolvedBank && resolvedBank.charge_type) {
            return {
                type: resolvedBank.charge_type,
                charge: parseFloat(resolvedBank.charge || 0)
            };
        }
        const t = (defaultChargeType || '').toString().toLowerCase();
        const type = (t === 'percentage' || t === 'percent') ? 'percentage' : 'fixed';
        return { type: type, charge: defaultCharge };
    }

    function calculateFee(amount) {
        const meta = getExternalChargeMeta();
        if(meta.type === 'percentage') {
            return (meta.charge / 100) * amount;
        }
        return meta.charge;
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
        if (t === 'ira') return 'IRA (selected share)';
        if (t === 'heloc') return 'HELOC (payment)';
        if (t === 'cc') return 'Credit card (payment)';
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
        metaEl.innerText = line || 'Select where to credit this transfer.';
    }

    function populateReview() {
        document.getElementById('reviewType').innerText = transferType === 'self' ? 'Intra-Account' : (transferType === 'member' ? 'Member Transfer' : 'External ACH');
        const fromSelect = document.getElementById('walletSelect');
        document.getElementById('reviewFrom').innerText = fromSelect.options[fromSelect.selectedIndex].text.split(' - ')[0];
        
        let toText = 'Unknown';
        if(transferType === 'self') {
            const toSelect = document.getElementById('toWalletSelect');
            toText = toSelect.options[toSelect.selectedIndex]?.text.split(' - ')[0] || 'My Account';
        }
        else if(transferType === 'member') toText = buildReviewToLine();
        else toText = (document.getElementById('resolvedBankName').value || document.getElementById('manualBankNameInput').value || 'External Bank');
        
        document.getElementById('reviewTo').innerText = toText;
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const fee = transferType === 'external' ? calculateFee(amount) : 0;
        const total = amount + fee;
        document.getElementById('reviewTransferAmount').innerText = '$' + amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('reviewFeeAmount').innerText = '$' + fee.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('reviewAmount').innerText = '$' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('reviewDate').innerText = document.querySelector('input[name="scheduled_at"]').value;
        document.getElementById('reviewFreq').innerText = document.querySelector('select[name="frequency"]').value;
        document.getElementById('reviewPurpose').innerText = document.getElementById('transferPurpose').value || 'Transfer of funds';
        const externalDetails = document.querySelector('.external-review-details');
        if(transferType === 'external') {
            externalDetails.classList.remove('d-none');
            document.getElementById('reviewRouting').innerText = document.getElementById('routingNumberInput').value;
            document.getElementById('reviewAccount').innerText = document.getElementById('ext_acc_num').value;
            document.getElementById('reviewBankName').innerText = toText;
        } else {
            externalDetails.classList.add('d-none');
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

    // Attach listeners after DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.js-type-select').forEach(card => {
            card.addEventListener('click', function(e) {
                const type = this.getAttribute('data-type');
                window.selectType(type, this);
            });
        });
        
        // Form submission is handled by SecurityGate via onclick on the confirm button

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

        if (memberInput) {
            memberInput.addEventListener('input', function() {
                var val = this.value;
                if (val.length > 4 || (val.indexOf('@') !== -1 && val.length > 3)) {
                    fetch('/user/search-by-account-number/' + encodeURIComponent(val))
                        .then(function(response) { return response.json(); })
                        .then(function(data) {
                            if (data.name) {
                                document.getElementById('member_verified_name').innerText = data.name;
                                if (accountNameHidden) {
                                    accountNameHidden.value = data.name;
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

                                if (targetAccountType) {
                                    const updateOpt = (opt, has, restricted, label) => {
                                        if(!opt) return;
                                        if(has) {
                                            opt.hidden = false;
                                            opt.disabled = restricted;
                                            opt.innerText = restricted ? label + ' (Restricted)' : label;
                                        } else {
                                            opt.hidden = true;
                                            opt.disabled = true;
                                        }
                                    };

                                    const optChecking = targetAccountType.querySelector('option[value="checking"]');
                                    updateOpt(optChecking, true, data.checking_restricted, 'Checking');
                                    updateOpt(optSavings, data.has_savings, data.savings_restricted, 'Savings');
                                    updateOpt(optIra, data.has_ira, data.ira_restricted, 'IRA');
                                    updateOpt(optHeloc, data.has_heloc, data.heloc_restricted, 'HELOC (Pay Down)');
                                    updateOpt(optCc, data.has_cc, data.cc_restricted, 'Credit Card (Pay Down)');
                                    updateOpt(optLoan, data.has_loan, data.loan_restricted, 'Loan (Pay Down)');

                                    if (targetAccountType.selectedOptions[0] && targetAccountType.selectedOptions[0].disabled) {
                                        for(let o of targetAccountType.options) {
                                            if(!o.disabled && !o.hidden) {
                                                targetAccountType.value = o.value;
                                                break;
                                            }
                                        }
                                    }

                                    let autoVal = null;
                                    if (val === data.savings_account_number && !data.savings_restricted) autoVal = 'savings';
                                    else if (val === data.ira_account_number && !data.ira_restricted) autoVal = 'ira';
                                    else if (val === data.heloc_account_number && !data.heloc_restricted) autoVal = 'heloc';
                                    else if (val === data.cc_account_number && !data.cc_restricted) autoVal = 'cc';
                                    else if (val === data.loan_account_number && !data.loan_restricted) autoVal = 'loan';
                                    else if (val === data.account_number && !data.checking_restricted) autoVal = 'checking';
                                    
                                    if(autoVal) targetAccountType.value = autoVal;
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
                        });
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
            bankIdEl.value = '';
            bankNameHidden.value = '';
            cardEl.classList.add('d-none');
        }

        let lookupTimer = null;
        if (routingInput) routingInput.addEventListener('input', function() {
            const routing = this.value.trim();
            clearResolvedBank();
            manualWrap.classList.add('d-none');
            manualInput.value = '';
            statusEl.innerHTML = '';

            if(routing.length !== 9) return;

            if(!isValidAbaChecksumAch(routing)) {
                statusEl.innerHTML = '<span class="text-warning fw-semibold"><i class="fas fa-circle-info me-1"></i> Routing number unverified. Please provide receiving institution name below.</span>';
                manualWrap.classList.remove('d-none');
                return;
            }
            statusEl.innerHTML = '<span class="text-muted">Verifying ABA routing number…</span>';
            if(lookupTimer) clearTimeout(lookupTimer);
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
                    if(ok && body.status === 'verified') {
                        resolvedBank = body;
                        bankIdEl.value = body.bank_id;
                        bankNameHidden.value = body.bank_name;
                        bankNameEl.innerText = body.bank_name;
                        bankStateEl.innerText = body.location ? (body.location + ' — Verified Receiving Institution') : 'Receiving financial institution (verified)';
                        cardEl.classList.remove('d-none');
                        statusEl.innerHTML = '<span class="text-success">Receiving institution verified.</span>';
                        manualWrap.classList.add('d-none');
                        manualInput.value = '';
                    } else if(body.status === 'invalid_checksum') {
                        statusEl.innerHTML = '<span class="text-warning fw-semibold"><i class="fas fa-circle-info me-1"></i> Routing unlisted. Enter the receiving institution name below.</span>';
                        manualWrap.classList.remove('d-none');
                    } else if(body.status === 'manual_required') {
                        statusEl.innerHTML = '<span class="text-warning">' + (body.message || 'Enter the receiving institution\'s name to continue.') + '</span>';
                        manualWrap.classList.remove('d-none');
                    } else if(body.status === 'error' || status >= 500) {
                        statusEl.innerHTML = '<span class="text-warning">' + (body.message || 'Verification is temporarily unavailable. Enter the receiving institution\'s name to continue.') + '</span>';
                        manualWrap.classList.remove('d-none');
                    } else {
                        statusEl.innerHTML = '<span class="text-danger">' + (body.message || 'Unable to verify this routing number.') + '</span>';
                        manualWrap.classList.remove('d-none');
                    }
                })
                .catch(() => {
                    statusEl.innerHTML = '<span class="text-warning">Verification is temporarily unavailable. Enter the receiving institution\'s name to continue.</span>';
                    manualWrap.classList.remove('d-none');
                });
            }, 300);
        });
    });

    window.csbSubmitTransferForm = function() {
        var form = document.getElementById('transferForm');
        if (!form) return;
        var $btn = $('#confirmBtn');

        if (typeof SecurityGate !== 'undefined' && typeof SecurityGate.gate === 'function') {
            SecurityGate.gate(function(res) {
                doAjaxSubmit();
            });
        } else {
            doAjaxSubmit();
        }

        function doAjaxSubmit() {
            $btn.prop('disabled', true);
            $btn.find('.spinner-border').removeClass('d-none');
            $btn.find('.btn-label').html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Submitting Transfer...');
            if (typeof window.showLoader === 'function') window.showLoader();

            var formData = new FormData(form);
            formData.append('security_verified', '1');

            $.ajax({
                url: form.action,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .done(function(data) {
                if (typeof window.hideLoader === 'function') window.hideLoader();
                if (window.navigator && window.navigator.vibrate) window.navigator.vibrate(20);

                var tnx = data.trx || data.tnx || ('TRX' + Math.floor(100000 + Math.random() * 900000));
                $('#confirmModalRef').text(tnx);
                $('#confirmModalAmount').text(document.getElementById('reviewAmount').innerText);
                $('#confirmModalType').text(document.getElementById('reviewType').innerText);
                $('#confirmModalTo').text(document.getElementById('reviewToAccount').innerText);
                $('#confirmModalDate').text(document.getElementById('reviewDate').innerText);

                var confirmModal = new bootstrap.Modal(document.getElementById('transferConfirmationModal'));
                confirmModal.show();
            })
            .fail(function(xhr) {
                if (typeof window.hideLoader === 'function') window.hideLoader();
                $btn.prop('disabled', false);
                $btn.find('.spinner-border').addClass('d-none');
                $btn.find('.btn-label').html('<i class="fas fa-arrow-right me-2"></i>Submit Transfer');

                var err = 'Transfer could not be processed. Please verify your details.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        err = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    } else if (xhr.responseJSON.message) {
                        err = xhr.responseJSON.message;
                    }
                }
                Swal.fire({
                    title: 'Transfer Not Completed',
                    html: err,
                    icon: 'error',
                    confirmButtonColor: '{{ $brandPrimary }}'
                });
            });
        }
    };
</script>
@endsection
