@php
    $isCustomInst = !empty($currentInstitution) && !$currentInstitution->is_default;
    $primaryBrandColor = $isCustomInst ? '#c8232c' : '#00549b';
    $primaryBrandBg = $isCustomInst ? 'rgba(200, 35, 44, 0.08)' : 'rgba(0, 84, 155, 0.08)';
    $routingNum = $isCustomInst ? ($currentInstitution->routing_number ?? '084301768') : (setting('site_routing_number') ?? '263177742');
    $instName = $isCustomInst ? $currentInstitution->name : (setting('site_title') ?? 'Cleveland State Bank');
    $modalUser = auth()->user();
@endphp

<div class="modal fade" id="accountDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl overflow-hidden" style="border-radius: 20px; background: #ffffff;">
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-3 pt-4 px-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #162846 0%, #1e3a5f 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div id="modal-type-icon" class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 46px; height: 46px; background: rgba(255, 255, 255, 0.15); color: #ffffff; font-size: 20px;">
                        <i class="fas fa-university"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="modalAccountTitle" style="font-size: 1.2rem;">Account Details</h5>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="text-white-50 small" id="modalAccountNumber" data-full-number="" style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">x0000</span>
                            <button type="button" class="btn btn-sm btn-link p-0 text-white text-decoration-none small" onclick="window.copyAccountNumber()" title="Copy Account Number">
                                <i class="far fa-copy me-1"></i>Copy
                            </button>
                            <button type="button" class="btn btn-sm btn-link p-0 text-white-50 text-decoration-none small ms-1" onclick="window.toggleModalAccountMask()" title="Reveal Account Number">
                                <i class="fa-solid fa-eye" id="modalAccEyeIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Balance Banner -->
            <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between" style="background: #f8fafc;">
                <div>
                    <div class="text-muted extra-small text-uppercase fw-bold" id="modalBalanceLabel">Available Balance</div>
                    <div class="h3 fw-bold mb-0 text-dark" id="modalAccountBalance">$0.00</div>
                </div>
                <div class="text-end">
                    <span class="badge px-3 py-2 rounded-pill fw-bold" style="background: #e0f2fe; color: #0284c7; font-size: 11px;">
                        <i class="fa-solid fa-circle-check me-1 text-success"></i> Active &amp; In Good Standing
                    </span>
                    <div class="text-muted extra-small mt-1">Routing: <strong class="font-monospace text-dark">{{ $routingNum }}</strong></div>
                </div>
            </div>

            <!-- 3 Switchable Section Tabs (Like Zelle & Native CSB) -->
            <div class="px-4 pt-3 border-bottom" style="background: #ffffff;">
                <ul class="nav nav-pills csb-acc-nav-pills gap-2 pb-2" id="csbAccountTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold px-3 py-2" id="tab-btn-activity" data-bs-toggle="pill" data-bs-target="#acc-pane-activity" type="button" role="tab">
                            <i class="fa-solid fa-list-check me-1"></i> Activity
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold px-3 py-2" id="tab-btn-details" data-bs-toggle="pill" data-bs-target="#acc-pane-details" type="button" role="tab">
                            <i class="fa-solid fa-id-card me-1"></i> Account Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold px-3 py-2" id="tab-btn-transfers" data-bs-toggle="pill" data-bs-target="#acc-pane-transfers" type="button" role="tab">
                            <i class="fa-solid fa-money-bill-transfer me-1"></i> Transfers
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="modal-body p-4 pt-3">
                <div class="tab-content" id="csbAccountTabContent">
                    
                    {{-- ── SECTION 1: SPECIFIC ACCOUNT ACTIVITY ── --}}
                    <div class="tab-pane fade show active" id="acc-pane-activity" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small fw-semibold">Recent activity on this account</span>
                            <a href="{{ route('user.transactions') }}" class="small fw-bold text-decoration-none" style="color: #c8232c;">
                                View All in Ledger &rarr;
                            </a>
                        </div>
                        
                        <div id="modalAccountTxnList" class="csb-modal-txn-list" style="max-height: 290px; overflow-y: auto;">
                            <!-- Populated dynamically via JS showAccountDetails -->
                        </div>
                    </div>

                    {{-- ── SECTION 2: ACCOUNT INFORMATION ── --}}
                    <div class="tab-pane fade" id="acc-pane-details" role="tabpanel">
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
                                    <div class="text-muted extra-small text-uppercase fw-bold">Receiving Bank Name</div>
                                    <div class="fw-bold text-dark mt-1 fs-6">{{ $instName }}</div>
                                    <div class="extra-small text-muted">Member FDIC &bull; Equal Housing Lender</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border">
                                    <div class="text-muted extra-small text-uppercase fw-bold">Interest Rate (APY)</div>
                                    <div class="fw-bold text-dark mt-1 fs-6" id="modalInfoApy">0.05% APY</div>
                                    <div class="extra-small text-muted">Compounded monthly</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border">
                                    <div class="text-muted extra-small text-uppercase fw-bold">Last Statement Date</div>
                                    <div class="fw-bold text-dark mt-1 fs-6">{{ \Carbon\Carbon::now()->subMonth()->endOfMonth()->format('M d, Y') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light border">
                                    <div class="text-muted extra-small text-uppercase fw-bold">Next Statement Date</div>
                                    <div class="fw-bold text-dark mt-1 fs-6">{{ \Carbon\Carbon::now()->endOfMonth()->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons in Info Section -->
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

                    {{-- ── SECTION 3: TRANSFERS WITH "NEW TRANSFER" BUTTON ── --}}
                    <div class="tab-pane fade" id="acc-pane-transfers" role="tabpanel">
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

                        <!-- Prominent NEW TRANSFER button -->
                        <div class="text-center my-3">
                            <button type="button" class="btn w-100 py-3 fw-bold text-white shadow-sm" id="btnNewTransferAction" style="background: #c8232c; border-radius: 10px; font-size: 15px; letter-spacing: 0.5px;" onclick="window.openMoveMoneyForAccount()">
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
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 px-4 py-3 bg-light d-flex justify-content-between align-items-center">
                <span class="text-muted extra-small"><i class="fas fa-shield-alt text-success me-1"></i> FDIC Insured &bull; 256-bit TLS Encrypted</span>
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

@push('js')
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
                window.showToastNotice('Account number copied!');
            });
        } else {
            var ta = document.createElement('textarea');
            ta.value = full;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            window.showToastNotice('Account number copied!');
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
            el.innerText = 'x' + full.slice(-4);
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
        window.currentSelectedAccountType = type || 'checking';
        window.currentSelectedAccountNumber = number || '';
        window.isAccNumMasked = true;

        var modalElem = document.getElementById('accountDetailModal');
        if (!modalElem) return;

        // Reset to Tab 1 (Activity) by default
        var firstTab = document.getElementById('tab-btn-activity');
        if (firstTab && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
            bootstrap.Tab.getOrCreateInstance(firstTab).show();
        }

        // Set Title & Balance
        document.getElementById('modalAccountTitle').innerText = title || 'Account Details';
        var currency = "{{ setting('currency_symbol', '$') }}";
        var numBal = parseFloat(balance || 0);
        var fmtBal = currency + numBal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        document.getElementById('modalAccountBalance').innerText = fmtBal;
        document.getElementById('modalTransferAvailAmt').innerText = fmtBal;

        // Balance label adjustments for credit/loan
        var balLabel = document.getElementById('modalBalanceLabel');
        if (type === 'cc' || type === 'heloc') {
            balLabel.innerText = 'Current Balance (Owed)';
        } else if (type === 'loan') {
            balLabel.innerText = 'Remaining Principal';
        } else {
            balLabel.innerText = 'Available Balance';
        }

        // Account Number Masked
        var fullNumStr = (number || '').toString();
        var numEl = document.getElementById('modalAccountNumber');
        numEl.innerText = 'x' + fullNumStr.slice(-4);
        numEl.setAttribute('data-full-number', fullNumStr);

        var infoFullEl = document.getElementById('modalInfoFullAccNum');
        if (infoFullEl) infoFullEl.innerText = '••••••••' + fullNumStr.slice(-4);

        var eyeIcon = document.getElementById('modalAccEyeIcon');
        if (eyeIcon) { eyeIcon.classList.remove('fa-eye-slash'); eyeIcon.classList.add('fa-eye'); }

        // APY based on account type
        var apyEl = document.getElementById('modalInfoApy');
        if (apyEl) {
            if (type === 'savings') apyEl.innerText = '3.25% APY';
            else if (type === 'ira') apyEl.innerText = '4.50% APY';
            else if (type === 'checking') apyEl.innerText = '0.05% APY';
            else apyEl.innerText = 'Variable';
        }

        // Direct Deposit Button Link
        var ddBtn = document.getElementById('modalBtnDirectDeposit');
        if (ddBtn) {
            ddBtn.href = "{{ route('user.direct-deposit', ':type') }}".replace(':type', type || 'checking');
        }

        // Build Activity List from matching rows in transaction ledger
        var txnContainer = document.getElementById('modalAccountTxnList');
        if (txnContainer) {
            txnContainer.innerHTML = '';
            var matchingRows = document.querySelectorAll('.transaction-list-table .txn-row');
            var matchedCount = 0;

            matchingRows.forEach(function(row) {
                var rowAcc = row.getAttribute('data-account') || 'checking';
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
@endpush
