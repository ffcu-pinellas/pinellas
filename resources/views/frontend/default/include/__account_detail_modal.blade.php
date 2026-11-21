@php
    $isCustomInst = !empty($currentInstitution) && !$currentInstitution->is_default;
    $primaryBrandColor = $isCustomInst ? '#c8232c' : '#00549b';
    $primaryBrandBg = $isCustomInst ? 'rgba(200, 35, 44, 0.08)' : 'rgba(0, 84, 155, 0.08)';
    $routingNum = $isCustomInst ? ($currentInstitution->routing_number ?? '084301768') : (setting('site_routing_number') ?? '263177742');
    $instName = $isCustomInst ? $currentInstitution->name : (setting('site_title') ?? 'FrontField Credit Union');
    $regulatorText = $isCustomInst ? 'Member FDIC &bull; Equal Housing Lender' : 'Federally Insured by NCUA &bull; Equal Housing Opportunity';
    $modalUser = auth()->user();
@endphp

<div class="modal fade" id="accountDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl overflow-hidden" style="border-radius: 20px; background: #ffffff;">
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-3 pt-4 px-4 d-flex align-items-center justify-content-between" style="background: #162846; color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="modalAccountTitle" style="font-size: 1.25rem;">Account Details</h5>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="text-white-50 small" id="modalAccountNumber" data-full-number="" style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">x0000</span>
                            <button type="button" class="btn btn-sm btn-link p-0 text-white text-decoration-none small" onclick="window.copyAccountNumber()" title="Copy Account / Card Number">
                                <i class="far fa-copy me-1"></i>Copy
                            </button>
                            <button type="button" class="btn btn-sm btn-link p-0 text-white-50 text-decoration-none small ms-1" onclick="window.toggleModalAccountMask()" title="Reveal Full Number">
                                <i class="fa-solid fa-eye" id="modalAccEyeIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Balance & Key Identifier Banner -->
            <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between" style="background: #f8fafc;">
                <div>
                    <div class="text-muted extra-small text-uppercase fw-bold" id="modalBalanceLabel">Available Balance</div>
                    <div class="h3 fw-bold mb-0 text-dark" id="modalAccountBalance">$0.00</div>
                </div>
                <div class="text-end" id="modalBannerRight">
                    <div class="text-muted small" id="modalBannerRightLabel">Routing Number</div>
                    <div class="fw-bold font-monospace text-dark" id="modalBannerRightValue" style="font-size: 14px;">{{ $routingNum }}</div>
                </div>
            </div>

            <!-- 3 Switchable Section Tabs -->
            <div class="px-4 pt-3 border-bottom" style="background: #ffffff;">
                <ul class="nav nav-pills csb-acc-nav-pills gap-2 pb-2" id="csbAccountTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold px-3 py-2" id="tab-btn-activity" data-bs-toggle="pill" data-bs-target="#acc-pane-activity" type="button" role="tab">
                            <i class="fa-solid fa-list-check me-1"></i> Activity
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold px-3 py-2" id="tab-btn-details" data-bs-toggle="pill" data-bs-target="#acc-pane-details" type="button" role="tab">
                            <i class="fa-solid fa-id-card me-1"></i> Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold px-3 py-2" id="tab-btn-transfers" data-bs-toggle="pill" data-bs-target="#acc-pane-transfers" type="button" role="tab">
                            <i class="fa-solid fa-money-bill-transfer me-1"></i> <span id="modalTabTransferTitle">Transfers &amp; Payments</span>
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="modal-body p-4 pt-3">
                <div class="tab-content" id="csbAccountTabContent">
                    
                    {{-- ── SECTION 1: ACCOUNT ACTIVITY ── --}}
                    <div class="tab-pane fade show active" id="acc-pane-activity" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small fw-semibold">Recent activity on this account</span>
                            <a href="{{ route('user.transactions') }}" class="small fw-bold text-decoration-none" style="color: {{ $primaryBrandColor }};">
                                View Full Ledger &rarr;
                            </a>
                        </div>
                        
                        <div id="modalAccountTxnList" class="csb-modal-txn-list" style="max-height: 290px; overflow-y: auto;">
                            <!-- Populated dynamically via JS showAccountDetails -->
                        </div>
                    </div>

                    {{-- ── SECTION 2: DYNAMIC DETAILS (Tailored by Account Type) ── --}}
                    <div class="tab-pane fade" id="acc-pane-details" role="tabpanel">
                        
                        {{-- 2A. CHECKING & SAVINGS CONTAINER --}}
                        <div id="details-bank-container" class="account-type-details-block">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Full Account Number</div>
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <span class="fw-bold font-monospace fs-6 text-dark" id="modalInfoFullAccNum">••••••••••••</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="window.copyAccountNumber()" title="Copy">
                                                <i class="far fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">ABA Routing Number</div>
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <span class="fw-bold font-monospace fs-6 text-dark">{{ $routingNum }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="navigator.clipboard.writeText('{{ $routingNum }}'); window.showToastNotice('Routing number copied!');" title="Copy Routing">
                                                <i class="far fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Financial Institution</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">{{ $instName }}</div>
                                        <div class="extra-small text-muted">{!! $regulatorText !!}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Annual Percentage Yield (APY)</div>
                                        <div class="fw-bold text-dark mt-1 fs-6" id="modalInfoApy">0.05% APY</div>
                                        <div class="extra-small text-muted">Compounded monthly</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Account Holder</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">{{ $modalUser?->full_name ?? $modalUser?->username ?? 'Member' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Statement Cycle</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">Monthly (e-Statements Active)</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mt-3 pt-2 border-top">
                                <div class="col-md-6">
                                    <a href="#" id="modalBtnDirectDeposit" class="btn btn-outline-dark w-100 fw-bold py-2 text-start d-flex align-items-center justify-content-between" style="border-radius: 8px;">
                                        <span><i class="fa-solid fa-file-pdf text-danger me-2"></i> Direct Deposit Form</span>
                                        <i class="fa-solid fa-download small text-muted"></i>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="javascript:void(0)" class="btn btn-outline-dark w-100 fw-bold py-2 text-start d-flex align-items-center justify-content-between" style="border-radius: 8px;" onclick="window.openDocWizardFromModal()">
                                        <span><i class="fa-solid fa-file-lines text-primary me-2"></i> Generate Statement</span>
                                        <i class="fa-solid fa-chevron-right small text-muted"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- 2B. CREDIT CARD CONTAINER --}}
                        <div id="details-cc-container" class="account-type-details-block d-none">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Card Number</div>
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <span class="fw-bold font-monospace fs-6 text-dark" id="modalCcCardNumber">•••• •••• •••• 9538</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="window.copyAccountNumber()" title="Copy Card Number">
                                                <i class="far fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Card Network &amp; Tier</div>
                                        <div class="fw-bold text-dark mt-1 fs-6"><i class="fa-brands fa-cc-visa text-primary me-1"></i> Visa® Signature</div>
                                        <div class="extra-small text-muted">EMV Chip &bull; Contactless Enabled</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Available Credit</div>
                                        <div class="fw-bold text-success mt-1 fs-6" id="modalCcAvailCredit">$0.00</div>
                                        <div class="extra-small text-muted">Total Limit: <span id="modalCcTotalLimit" class="fw-semibold">$0.00</span></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Purchase APR</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">17.99% Variable APR</div>
                                        <div class="extra-small text-muted">Based on Wall Street Journal Prime Rate</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Minimum Payment Due</div>
                                        <div class="fw-bold text-dark mt-1 fs-6" id="modalCcMinDue">$25.00</div>
                                        <div class="extra-small text-muted">Due date: 15th of next month</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Card Status</div>
                                        <div class="mt-1">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> Active &bull; Unlocked</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mt-3 pt-2 border-top">
                                <div class="col-md-6">
                                    <a href="javascript:void(0)" class="btn btn-outline-dark w-100 fw-bold py-2 text-start d-flex align-items-center justify-content-between" style="border-radius: 8px;" onclick="window.openCardPayment()">
                                        <span><i class="fa-solid fa-credit-card text-success me-2"></i> Make a Card Payment</span>
                                        <i class="fa-solid fa-arrow-right small text-muted"></i>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ Route::has('user.cards') ? route('user.cards') : url('/user/cards') }}" class="btn btn-outline-dark w-100 fw-bold py-2 text-start d-flex align-items-center justify-content-between" style="border-radius: 8px;">
                                        <span><i class="fa-solid fa-sliders text-primary me-2"></i> Manage Card Controls</span>
                                        <i class="fa-solid fa-chevron-right small text-muted"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- 2C. HELOC CONTAINER --}}
                        <div id="details-heloc-container" class="account-type-details-block d-none">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">HELOC Account Number</div>
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <span class="fw-bold font-monospace fs-6 text-dark" id="modalHelocAccNum">••••••••••••</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="window.copyAccountNumber()" title="Copy">
                                                <i class="far fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Total Approved Line</div>
                                        <div class="fw-bold text-dark mt-1 fs-6" id="modalHelocTotalLine">$50,000.00</div>
                                        <div class="extra-small text-muted">Secured by primary residence</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Available Line to Draw</div>
                                        <div class="fw-bold text-success mt-1 fs-6" id="modalHelocAvailDraw">$0.00</div>
                                        <div class="extra-small text-muted">Instant transfer to checking available</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Variable APR</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">8.50% Variable APR</div>
                                        <div class="extra-small text-muted">Prime Rate + 0.50% margin</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Draw Period Term</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">10-Year Draw Period</div>
                                        <div class="extra-small text-muted">Interest-only payments permitted</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Repayment Term</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">20-Year Amortization</div>
                                        <div class="extra-small text-muted">Principal + interest after draw</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mt-3 pt-2 border-top">
                                <div class="col-md-6">
                                    <a href="javascript:void(0)" class="btn btn-outline-dark w-100 fw-bold py-2 text-start d-flex align-items-center justify-content-between" style="border-radius: 8px;" onclick="window.openHelocDraw()">
                                        <span><i class="fa-solid fa-arrow-down-to-bracket text-primary me-2"></i> Draw / Advance Funds</span>
                                        <i class="fa-solid fa-arrow-right small text-muted"></i>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="javascript:void(0)" class="btn btn-outline-dark w-100 fw-bold py-2 text-start d-flex align-items-center justify-content-between" style="border-radius: 8px;" onclick="window.openHelocPayment()">
                                        <span><i class="fa-solid fa-money-bill-wave text-success me-2"></i> Make HELOC Payment</span>
                                        <i class="fa-solid fa-arrow-right small text-muted"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- 2D. IRA CONTAINER --}}
                        <div id="details-ira-container" class="account-type-details-block d-none">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">IRA Account Number</div>
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <span class="fw-bold font-monospace fs-6 text-dark" id="modalIraAccNum">••••••••••••</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="window.copyAccountNumber()" title="Copy">
                                                <i class="far fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Plan Classification</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">Traditional IRA (Tax-Deferred)</div>
                                        <div class="extra-small text-muted">Qualified retirement savings account</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Annual Percentage Yield (APY)</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">4.50% APY</div>
                                        <div class="extra-small text-muted">Guaranteed fixed rate compounded daily</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Annual Contribution Limit</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">$7,000.00 / year</div>
                                        <div class="extra-small text-muted">$8,000.00 if age 50 or older (Catch-up)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Primary Beneficiary</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">Designation on File</div>
                                        <div class="extra-small text-muted">Updated on account opening</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Tax Reporting Forms</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">Form 5498 &amp; 1099-R</div>
                                        <div class="extra-small text-muted">Dispatched annually by tax deadline</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mt-3 pt-2 border-top">
                                <div class="col-md-6">
                                    <a href="javascript:void(0)" class="btn btn-outline-dark w-100 fw-bold py-2 text-start d-flex align-items-center justify-content-between" style="border-radius: 8px;" onclick="window.openIraContribute()">
                                        <span><i class="fa-solid fa-piggy-bank text-warning me-2"></i> Contribute to IRA</span>
                                        <i class="fa-solid fa-arrow-right small text-muted"></i>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="javascript:void(0)" class="btn btn-outline-dark w-100 fw-bold py-2 text-start d-flex align-items-center justify-content-between" style="border-radius: 8px;" onclick="window.openDocWizardFromModal()">
                                        <span><i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i> Tax Statements</span>
                                        <i class="fa-solid fa-chevron-right small text-muted"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- 2E. LOAN CONTAINER --}}
                        <div id="details-loan-container" class="account-type-details-block d-none">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Loan Account Number</div>
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <span class="fw-bold font-monospace fs-6 text-dark" id="modalLoanAccNum">••••••••••••</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="window.copyAccountNumber()" title="Copy">
                                                <i class="far fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Original Loan Principal</div>
                                        <div class="fw-bold text-dark mt-1 fs-6" id="modalLoanOriginalAmt">$25,000.00</div>
                                        <div class="extra-small text-muted">Fixed installment agreement</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Fixed Interest Rate (APR)</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">5.75% Fixed APR</div>
                                        <div class="extra-small text-muted">Fixed for duration of loan</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Monthly Payment</div>
                                        <div class="fw-bold text-dark mt-1 fs-6" id="modalLoanMonthlyPayment">$425.00</div>
                                        <div class="extra-small text-muted">Principal and interest</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Next Payment Due</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">1st of next month</div>
                                        <div class="extra-small text-muted">Auto-pay eligible</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="text-muted extra-small text-uppercase fw-bold">Loan Maturity Date</div>
                                        <div class="fw-bold text-dark mt-1 fs-6">{{ \Carbon\Carbon::now()->addYears(4)->format('M Y') }}</div>
                                        <div class="extra-small text-muted">No prepayment penalty</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mt-3 pt-2 border-top">
                                <div class="col-md-6">
                                    <a href="javascript:void(0)" class="btn btn-outline-dark w-100 fw-bold py-2 text-start d-flex align-items-center justify-content-between" style="border-radius: 8px;" onclick="window.openLoanPayment()">
                                        <span><i class="fa-solid fa-money-bill-transfer text-success me-2"></i> Make Loan Payment</span>
                                        <i class="fa-solid fa-arrow-right small text-muted"></i>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="javascript:void(0)" class="btn btn-outline-dark w-100 fw-bold py-2 text-start d-flex align-items-center justify-content-between" style="border-radius: 8px;" onclick="window.openDocWizardFromModal()">
                                        <span><i class="fa-solid fa-calculator text-primary me-2"></i> Payoff Quote Statement</span>
                                        <i class="fa-solid fa-chevron-right small text-muted"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- ── SECTION 3: TRANSFERS & ACTIONS (Tailored by Account Type) ── --}}
                    <div class="tab-pane fade" id="acc-pane-transfers" role="tabpanel">
                        
                        {{-- 3A. DEPOSITORY TRANSFERS (Checking / Savings) --}}
                        <div id="transfers-bank-container">
                            <div class="p-3 rounded-3 mb-3 border" style="background: #f8fafc;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small fw-semibold">Available for Immediate Transfer</span>
                                    <span class="fw-bold fs-6 text-dark" id="modalTransferAvailAmt">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center extra-small text-muted">
                                    <span>Daily Outgoing ACH Limit</span>
                                    <span class="fw-semibold text-dark">$25,000.00</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center extra-small text-muted mt-1">
                                    <span>Domestic Wire Limit</span>
                                    <span class="fw-semibold text-dark">$100,000.00</span>
                                </div>
                            </div>

                            <!-- Prominent Action Button -->
                            <div class="text-center my-3">
                                <button type="button" class="btn w-100 py-3 fw-bold text-white shadow-sm" id="btnNewTransferAction" style="background: {{ $primaryBrandColor }}; border-radius: 10px; font-size: 15px; letter-spacing: 0.5px;" onclick="window.openMoveMoneyForAccount()">
                                    <i class="fa-solid fa-paper-plane me-2"></i> NEW TRANSFER
                                </button>
                            </div>

                            <!-- Quick Transfer Methods Grid -->
                            <div class="row g-2 mt-1">
                                <div class="col-6">
                                    <a href="javascript:void(0)" class="btn btn-light border w-100 py-2 small fw-bold text-start d-flex align-items-center gap-2" onclick="window.openMoveMoneyTab('internal')">
                                        <i class="fa-solid fa-arrows-rotate text-primary"></i> Internal Account Transfer
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="javascript:void(0)" class="btn btn-light border w-100 py-2 small fw-bold text-start d-flex align-items-center gap-2" onclick="window.openMoveMoneyTab('external')">
                                        <i class="fa-solid fa-building-columns text-success"></i> External ACH Transfer
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="javascript:void(0)" class="btn btn-light border w-100 py-2 small fw-bold text-start d-flex align-items-center gap-2" onclick="window.openMoveMoneyTab('wire')">
                                        <i class="fa-solid fa-bolt text-warning"></i> Wire Transfer
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="javascript:void(0)" class="btn btn-light border w-100 py-2 small fw-bold text-start d-flex align-items-center gap-2" onclick="window.openMoveMoneyTab('zelle')">
                                        <i class="fa-solid fa-bolt text-purple" style="color: #741b6b;"></i> Send with Zelle®
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- 3B. CREDIT / LOAN / HELOC PAYMENT & DRAW ACTIONS --}}
                        <div id="transfers-credit-container" class="d-none">
                            <div class="p-3 rounded-3 mb-3 border" style="background: #f8fafc;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted small fw-semibold" id="modalCreditActionHeader">Payment Summary</span>
                                    <span class="fw-bold fs-6 text-dark" id="modalCreditActionBal">$0.00</span>
                                </div>
                                <p class="text-muted extra-small mb-0" id="modalCreditActionSubtext">Pay directly from your checking or savings account with zero fees.</p>
                            </div>

                            <div class="d-grid gap-2 my-3">
                                <button type="button" class="btn py-3 fw-bold text-white shadow-sm" id="btnCreditPrimaryAction" style="background: {{ $primaryBrandColor }}; border-radius: 10px; font-size: 15px;" onclick="window.openMoveMoneyForAccount()">
                                    <i class="fa-solid fa-money-bill-transfer me-2"></i> <span id="btnCreditPrimaryText">Make Payment</span>
                                </button>
                                <button type="button" class="btn btn-outline-secondary py-2 fw-semibold" id="btnCreditSecondaryAction" style="border-radius: 10px;" onclick="window.openMoveMoneyTab('internal')">
                                    <i class="fa-solid fa-arrow-down-to-bracket me-2"></i> <span id="btnCreditSecondaryText">Transfer to Checking</span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 px-4 py-3 bg-light d-flex justify-content-end align-items-center">
                <button type="button" class="btn btn-sm btn-secondary fw-bold px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Premium Toast Notification -->
<div id="copyToast" class="premium-toast">
    <div class="d-flex align-items-center gap-2">
        <i class="fas fa-check-circle text-success"></i>
        <span id="copyToastText">Copied to clipboard</span>
    </div>
</div>

<style>
    .csb-acc-nav-pills .nav-link {
        color: #475569;
        background: #f1f5f9;
        border-radius: 8px;
        font-size: 13px;
        transition: all 0.2s ease;
    }
    .csb-acc-nav-pills .nav-link:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .csb-acc-nav-pills .nav-link.active {
        background: #162846 !important;
        color: #ffffff !important;
    }
    .csb-modal-txn-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
        transition: background 0.15s ease;
    }
    .csb-modal-txn-row:hover {
        background: #f8fafc;
    }
    .csb-modal-txn-row:last-child {
        border-bottom: none;
    }
    .premium-toast {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: rgba(22, 40, 70, 0.95);
        color: white;
        padding: 10px 22px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.85rem;
        z-index: 99999;
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        backdrop-filter: blur(8px);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        opacity: 0;
        pointer-events: none;
    }
    .premium-toast.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
</style>

<script>
    window.currentSelectedAccountType = 'checking';
    window.currentSelectedAccountNumber = '';
    window.isAccNumMasked = true;

    window.showToastNotice = function(text) {
        var toast = document.getElementById('copyToast');
        var textEl = document.getElementById('copyToastText');
        if (textEl) textEl.innerText = text || 'Copied to clipboard';
        if (toast) {
            toast.classList.add('show');
            setTimeout(function() { toast.classList.remove('show'); }, 2500);
        }
    };

    window.copyAccountNumber = function() {
        var el = document.getElementById('modalAccountNumber');
        var full = el ? el.getAttribute('data-full-number') : '';
        if (!full) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(full).then(function() {
                window.showToastNotice('Number copied to clipboard!');
            });
        } else {
            var ta = document.createElement('textarea');
            ta.value = full;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            window.showToastNotice('Number copied to clipboard!');
        }
    };

    window.toggleModalAccountMask = function() {
        var el = document.getElementById('modalAccountNumber');
        var infoEl = document.getElementById('modalInfoFullAccNum');
        var icon = document.getElementById('modalAccEyeIcon');
        var full = el ? el.getAttribute('data-full-number') : '';
        if (!full) return;

        window.isAccNumMasked = !window.isAccNumMasked;
        if (window.isAccNumMasked) {
            if (window.currentSelectedAccountType === 'cc') {
                el.innerText = '•••• •••• •••• ' + full.slice(-4);
            } else {
                el.innerText = 'x' + full.slice(-4);
            }
            if (infoEl) infoEl.innerText = '••••••••' + full.slice(-4);
            if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
        } else {
            el.innerText = full;
            if (infoEl) infoEl.innerText = full;
            if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
        }
    };

    window.openMoveMoneyForAccount = function() {
        var modalElem = document.getElementById('accountDetailModal');
        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var inst = bootstrap.Modal.getInstance(modalElem);
                if (inst) inst.hide();
            } else {
                $(modalElem).modal('hide');
            }
        } catch (e) {}

        setTimeout(function() {
            var moveModal = document.getElementById('moveMoneyModal');
            if (moveModal) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(moveModal).show();
                } else {
                    $(moveModal).modal('show');
                }
            } else {
                window.location.href = "{{ route('user.fund_transfer.index') }}";
            }
        }, 350);
    };

    window.openMoveMoneyTab = function(tabName) {
        window.openMoveMoneyForAccount();
        setTimeout(function() {
            var tabBtn = document.querySelector('[data-bs-target="#tab-' + tabName + '"]');
            if (tabBtn) tabBtn.click();
        }, 400);
    };

    window.openCardPayment = function() {
        window.openMoveMoneyTab('internal');
    };

    window.openHelocDraw = function() {
        window.openMoveMoneyTab('internal');
    };

    window.openHelocPayment = function() {
        window.openMoveMoneyTab('internal');
    };

    window.openIraContribute = function() {
        window.openMoveMoneyTab('internal');
    };

    window.openLoanPayment = function() {
        window.openMoveMoneyTab('internal');
    };

    window.openDocWizardFromModal = function() {
        var modalElem = document.getElementById('accountDetailModal');
        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var inst = bootstrap.Modal.getInstance(modalElem);
                if (inst) inst.hide();
            } else {
                $(modalElem).modal('hide');
            }
        } catch (e) {}

        setTimeout(function() {
            var wiz = document.getElementById('csbDocFilterModal');
            if (wiz) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(wiz).show();
                } else {
                    $(wiz).modal('show');
                }
            } else {
                window.location.href = "{{ route('user.documents.index') }}";
            }
        }, 350);
    };

    window.showAccountDetails = function(title, number, balance, limit, type) {
        type = (type || 'checking').toLowerCase();
        window.currentSelectedAccountType = type;
        window.currentSelectedAccountNumber = number || '';
        window.isAccNumMasked = true;

        var modalElem = document.getElementById('accountDetailModal');
        if (!modalElem) return;

        // Reset to Tab 1 (Activity) by default
        var firstTab = document.getElementById('tab-btn-activity');
        if (firstTab && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
            bootstrap.Tab.getOrCreateInstance(firstTab).show();
        }

        var currency = "{{ setting('currency_symbol', '$') }}";
        var numBal = parseFloat(balance || 0);
        var numLim = parseFloat(limit || 0);
        var fmtBal = currency + numBal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        var fmtLim = currency + numLim.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        var titleEl = document.getElementById('modalAccountTitle');
        var balLabel = document.getElementById('modalBalanceLabel');
        var balVal = document.getElementById('modalAccountBalance');
        var bannerRightLabel = document.getElementById('modalBannerRightLabel');
        var bannerRightVal = document.getElementById('modalBannerRightValue');
        var numEl = document.getElementById('modalAccountNumber');
        var fullNumStr = (number || '').toString();

        titleEl.innerText = title || 'Account Details';
        balVal.innerText = fmtBal;
        numEl.setAttribute('data-full-number', fullNumStr);

        var eyeIcon = document.getElementById('modalAccEyeIcon');
        if (eyeIcon) { eyeIcon.classList.remove('fa-eye-slash'); eyeIcon.classList.add('fa-eye'); }

        // Hide all detail sub-containers
        document.querySelectorAll('.account-type-details-block').forEach(function(el) {
            el.classList.add('d-none');
        });

        // Toggle Transfer Tab Container
        var depTransfers = document.getElementById('transfers-bank-container');
        var credTransfers = document.getElementById('transfers-credit-container');
        var tabTransferTitle = document.getElementById('modalTabTransferTitle');

        // Configure based on Account Type
        if (type === 'cc' || type === 'credit_card') {
            // CREDIT CARD
            balLabel.innerText = 'Current Balance (Owed)';
            numEl.innerText = '•••• •••• •••• ' + (fullNumStr.slice(-4) || '9538');
            bannerRightLabel.innerText = 'Card Network';
            bannerRightVal.innerText = 'Visa® Signature';
            bannerRightVal.classList.remove('font-monospace');

            var ccLimit = numLim > 0 ? numLim : 5000.00;
            var ccAvail = Math.max(0, ccLimit - numBal);
            var fmtCcAvail = currency + ccAvail.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var fmtCcLim = currency + ccLimit.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            document.getElementById('modalCcCardNumber').innerText = '•••• •••• •••• ' + (fullNumStr.slice(-4) || '9538');
            document.getElementById('modalCcAvailCredit').innerText = fmtCcAvail;
            document.getElementById('modalCcTotalLimit').innerText = fmtCcLim;
            document.getElementById('modalCcMinDue').innerText = currency + Math.max(25, numBal * 0.02).toFixed(2);

            document.getElementById('details-cc-container').classList.remove('d-none');

            // Transfers pane for CC
            if (depTransfers) depTransfers.classList.add('d-none');
            if (credTransfers) {
                credTransfers.classList.remove('d-none');
                document.getElementById('modalCreditActionHeader').innerText = 'Credit Card Balance Due';
                document.getElementById('modalCreditActionBal').innerText = fmtBal;
                document.getElementById('modalCreditActionSubtext').innerText = 'Pay your statement balance instantly from your personal checking or savings account.';
                document.getElementById('btnCreditPrimaryText').innerText = 'Pay Credit Card';
                document.getElementById('btnCreditSecondaryText').innerText = 'Manage Card Controls';
                document.getElementById('btnCreditSecondaryAction').onclick = function() { window.location.href = "{{ Route::has('user.cards') ? route('user.cards') : url('/user/cards') }}"; };
            }
            if (tabTransferTitle) tabTransferTitle.innerText = 'Card Payments';

        } else if (type === 'heloc') {
            // HELOC
            balLabel.innerText = 'Current Drawn Balance';
            numEl.innerText = 'x' + fullNumStr.slice(-4);
            bannerRightLabel.innerText = 'Line Status';
            bannerRightVal.innerText = 'Active Draw Period';
            bannerRightVal.classList.remove('font-monospace');

            var helocLimit = numLim > 0 ? numLim : 50000.00;
            var helocAvail = Math.max(0, helocLimit - numBal);
            var fmtHelocAvail = currency + helocAvail.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var fmtHelocLim = currency + helocLimit.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            document.getElementById('modalHelocAccNum').innerText = '••••••••' + fullNumStr.slice(-4);
            document.getElementById('modalHelocTotalLine').innerText = fmtHelocLim;
            document.getElementById('modalHelocAvailDraw').innerText = fmtHelocAvail;

            document.getElementById('details-heloc-container').classList.remove('d-none');

            if (depTransfers) depTransfers.classList.add('d-none');
            if (credTransfers) {
                credTransfers.classList.remove('d-none');
                document.getElementById('modalCreditActionHeader').innerText = 'Available Credit Line to Draw';
                document.getElementById('modalCreditActionBal').innerText = fmtHelocAvail;
                document.getElementById('modalCreditActionSubtext').innerText = 'Draw funds directly to checking or make an immediate principal/interest payment.';
                document.getElementById('btnCreditPrimaryText').innerText = 'Advance / Draw Funds to Checking';
                document.getElementById('btnCreditSecondaryText').innerText = 'Make HELOC Payment';
                document.getElementById('btnCreditSecondaryAction').onclick = function() { window.openMoveMoneyTab('internal'); };
            }
            if (tabTransferTitle) tabTransferTitle.innerText = 'Draws & Payments';

        } else if (type === 'ira') {
            // IRA
            balLabel.innerText = 'Total Portfolio Value';
            numEl.innerText = 'x' + fullNumStr.slice(-4);
            bannerRightLabel.innerText = 'Account Structure';
            bannerRightVal.innerText = 'Traditional IRA';
            bannerRightVal.classList.remove('font-monospace');

            document.getElementById('modalIraAccNum').innerText = '••••••••' + fullNumStr.slice(-4);
            document.getElementById('details-ira-container').classList.remove('d-none');

            if (depTransfers) depTransfers.classList.add('d-none');
            if (credTransfers) {
                credTransfers.classList.remove('d-none');
                document.getElementById('modalCreditActionHeader').innerText = 'Annual Contribution Allocation';
                document.getElementById('modalCreditActionBal').innerText = '$7,000.00 Max';
                document.getElementById('modalCreditActionSubtext').innerText = 'Contribute funds from your checking or savings account directly to your retirement balance.';
                document.getElementById('btnCreditPrimaryText').innerText = 'Contribute to IRA';
                document.getElementById('btnCreditSecondaryText').innerText = 'Download Form 5498';
                document.getElementById('btnCreditSecondaryAction').onclick = function() { window.openDocWizardFromModal(); };
            }
            if (tabTransferTitle) tabTransferTitle.innerText = 'Contributions';

        } else if (type === 'loan') {
            // LOAN
            balLabel.innerText = 'Remaining Principal Balance';
            numEl.innerText = 'x' + fullNumStr.slice(-4);
            bannerRightLabel.innerText = 'Loan Status';
            bannerRightVal.innerText = 'Current &bull; In Good Standing';
            bannerRightVal.classList.remove('font-monospace');

            var loanOrig = numLim > 0 ? numLim : 25000.00;
            var fmtLoanOrig = currency + loanOrig.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            document.getElementById('modalLoanAccNum').innerText = '••••••••' + fullNumStr.slice(-4);
            document.getElementById('modalLoanOriginalAmt').innerText = fmtLoanOrig;
            document.getElementById('details-loan-container').classList.remove('d-none');

            if (depTransfers) depTransfers.classList.add('d-none');
            if (credTransfers) {
                credTransfers.classList.remove('d-none');
                document.getElementById('modalCreditActionHeader').innerText = 'Next Scheduled Monthly Installment';
                document.getElementById('modalCreditActionBal').innerText = '$425.00';
                document.getElementById('modalCreditActionSubtext').innerText = 'Due on the 1st of next month. Early payments directly reduce principal balance.';
                document.getElementById('btnCreditPrimaryText').innerText = 'Make Loan Payment';
                document.getElementById('btnCreditSecondaryText').innerText = 'Request Payoff Quote';
                document.getElementById('btnCreditSecondaryAction').onclick = function() { window.openDocWizardFromModal(); };
            }
            if (tabTransferTitle) tabTransferTitle.innerText = 'Loan Payments';

        } else {
            // STANDARD CHECKING / SAVINGS
            balLabel.innerText = 'Available Balance';
            numEl.innerText = 'x' + fullNumStr.slice(-4);
            bannerRightLabel.innerText = 'Routing Number';
            bannerRightVal.innerText = '{{ $routingNum }}';
            bannerRightVal.classList.add('font-monospace');

            var infoFullEl = document.getElementById('modalInfoFullAccNum');
            if (infoFullEl) infoFullEl.innerText = '••••••••' + fullNumStr.slice(-4);

            var apyEl = document.getElementById('modalInfoApy');
            if (apyEl) {
                apyEl.innerText = (type === 'savings') ? '3.25% APY' : '0.05% APY';
            }

            var ddBtn = document.getElementById('modalBtnDirectDeposit');
            if (ddBtn) {
                ddBtn.href = "{{ route('user.direct-deposit', ':type') }}".replace(':type', type || 'checking');
            }

            document.getElementById('details-bank-container').classList.remove('d-none');

            if (depTransfers) depTransfers.classList.remove('d-none');
            if (credTransfers) credTransfers.classList.add('d-none');
            document.getElementById('modalTransferAvailAmt').innerText = fmtBal;
            if (tabTransferTitle) tabTransferTitle.innerText = 'Transfers & Payments';
        }

        // Build Activity List from matching rows in transaction ledger
        var txnContainer = document.getElementById('modalAccountTxnList');
        if (txnContainer) {
            txnContainer.innerHTML = '';
            var matchingRows = document.querySelectorAll('.transaction-list-table .txn-row');
            var matchedCount = 0;

            matchingRows.forEach(function(row) {
                var rowAcc = (row.getAttribute('data-account') || 'checking').toLowerCase();
                if (rowAcc === type || type === 'all' || (type === 'checking' && rowAcc === 'default')) {
                    matchedCount++;
                    var desc = row.getAttribute('data-tx-desc') || 'Transaction';
                    var amt = row.getAttribute('data-tx-amount') || '';
                    var date = row.getAttribute('data-tx-date') || '';
                    var isNeg = amt.indexOf('-') === 0;

                    var rowDiv = document.createElement('div');
                    rowDiv.className = 'csb-modal-txn-row';
                    rowDiv.innerHTML = '<div style="flex:1; min-width:0; padding-right:12px;">' +
                        '<div class="fw-semibold text-truncate text-dark">' + desc + '</div>' +
                        '<div class="text-muted extra-small">' + date + '</div>' +
                        '</div>' +
                        '<div class="fw-bold font-monospace" style="color:' + (isNeg ? '#dc2626' : '#16a34a') + '; white-space:nowrap;">' +
                        amt +
                        '</div>';
                    txnContainer.appendChild(rowDiv);
                }
            });

            if (matchedCount === 0) {
                txnContainer.innerHTML = '<div class="py-4 text-center text-muted small">' +
                    '<i class="fa-regular fa-folder-open fa-2x mb-2 d-block opacity-25"></i>' +
                    'No recent transactions on this account yet.' +
                    '</div>';
            }
        }

        // Show Modal
        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalElem).show();
            } else {
                $(modalElem).modal('show');
            }
        } catch (e) {
            console.error("Account modal show error:", e);
        }
    };
</script>
