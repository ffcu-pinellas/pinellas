{{-- ── CSB DOCUMENT TYPES MODAL & DOCUMENT FILTER WIZARD ── --}}
@php
    $userAccounts = [];
    if (auth()->check()) {
        $u = auth()->user();
        $userAccounts[] = ['type' => 'checking', 'label' => 'Checking (*' . substr($u->account_number ?? '1397', -4) . ')'];
        if ($u->savings_account_number || $u->savings_balance !== null) {
            $userAccounts[] = ['type' => 'savings', 'label' => 'Primary Savings (*' . substr($u->savings_account_number ?? $u->account_number ?? '4902', -4) . ')'];
        }
        if ($u->ira_status == 1) {
            $userAccounts[] = ['type' => 'ira', 'label' => 'IRA Account (*' . substr($u->ira_account_number ?? $u->account_number, -4) . ')'];
        }
        if ($u->heloc_status == 1) {
            $userAccounts[] = ['type' => 'heloc', 'label' => 'HELOC (*' . substr($u->heloc_account_number ?? $u->account_number, -4) . ')'];
        }
        if ($u->cc_status == 1) {
            $userAccounts[] = ['type' => 'cc', 'label' => 'Credit Card (*' . substr($u->cc_account_number ?? $u->account_number, -4) . ')'];
        }
        if ($u->loan_account_status == 1) {
            $userAccounts[] = ['type' => 'loan', 'label' => 'Loan Account (*' . substr($u->loan_account_number ?? $u->account_number ?? '8859', -4) . ')'];
        }
    }
@endphp

<!-- 1. DOCUMENT TYPES MODAL (Matching CSB Mobile UI) -->
<div class="modal fade" id="csbDocumentsModal" tabindex="-1" aria-hidden="true" style="z-index: 2060;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 14px;">
            <div class="modal-header py-3 px-4" style="background: #c8232c; color: #ffffff; border-bottom: none;">
                <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 15px; letter-spacing: 0.5px;">DOCUMENT TYPES</h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush csb-doc-type-list">
                    <a href="{{ route('user.documents.index') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-3 px-4 border-bottom">
                        <span class="fw-semibold text-dark" style="font-size: 14px;">All Documents</span>
                        <i class="fa-solid fa-chevron-right text-muted small"></i>
                    </a>
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-3 px-4 border-bottom" onclick="openDocTypeAction('notices')">
                        <span class="fw-semibold text-dark" style="font-size: 14px;">Notices</span>
                        <i class="fa-solid fa-chevron-right text-muted small"></i>
                    </a>
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-3 px-4 border-bottom" onclick="openDocTypeAction('statements')">
                        <span class="fw-semibold text-dark" style="font-size: 14px;">Statements</span>
                        <i class="fa-solid fa-chevron-right text-muted small"></i>
                    </a>
                    <a href="{{ route('user.direct-deposit', 'checking') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-3 px-4 border-bottom" download>
                        <span class="fw-semibold text-dark" style="font-size: 14px;">Direct Deposit Form (PDF)</span>
                        <i class="fa-solid fa-download text-muted small"></i>
                    </a>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light justify-content-center">
                <span class="text-muted extra-small"><i class="fa-solid fa-lock text-success me-1"></i> Official Cleveland State Bank Electronic Documents</span>
            </div>
        </div>
    </div>
</div>

<!-- 2. DOCUMENT FILTER WIZARD MODAL (Matching CSB Mobile UI) -->
<div class="modal fade" id="csbDocFilterModal" tabindex="-1" aria-hidden="true" style="z-index: 2070;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 14px;">
            <div class="modal-header py-3 px-4" style="background: #c8232c; color: #ffffff; border-bottom: none;">
                <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 15px; letter-spacing: 0.5px;">DOCUMENT FILTER WIZARD</h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="csbDocFilterForm" data-no-loader="true" onsubmit="handleDocFilterSubmit(event)">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Account</label>
                        <select class="form-select shadow-none" id="csbFilterAccount" required>
                            <option value="all" selected>All Accounts (Consolidated)</option>
                            @foreach($userAccounts as $acc)
                                <option value="{{ $acc['type'] }}">{{ $acc['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Document Type</label>
                        <select class="form-select shadow-none" id="csbFilterType">
                            <option value="statement" selected>Monthly E-Statement (PDF)</option>
                            <option value="direct_deposit">Direct Deposit Authorization Form</option>
                            <option value="notice">Official Notices &amp; Disclosures</option>
                            <option value="tax">Tax Documents (1099-INT)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Date Range / Period</label>
                        <select class="form-select shadow-none" id="csbFilterRange" onchange="csbToggleCustomDates(this.value)">
                            <option value="1m" selected>Last 30 Days</option>
                            <option value="3m">Last 90 Days (Quarterly)</option>
                            <option value="6m">Last 6 Months</option>
                            <option value="1y">Last 1 Year (Annual)</option>
                            <option value="custom">Custom Date Range...</option>
                        </select>
                    </div>

                    <div id="csbCustomDateFields" class="row g-2 mb-3 d-none">
                        <div class="col-6">
                            <label class="form-label extra-small fw-bold text-muted text-uppercase">From Date</label>
                            <input type="date" id="csbDocFromDate" class="form-control form-control-sm" value="{{ now()->subMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label extra-small fw-bold text-muted text-uppercase">To Date</label>
                            <input type="date" id="csbDocToDate" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="alert alert-light border small text-muted d-flex align-items-center gap-2 mb-4">
                        <i class="fa-solid fa-circle-info text-primary"></i>
                        <span>Documents are retained online for up to 7 years in compliance with Federal financial records retention rules.</span>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn fw-bold text-white py-2" style="background: #162846; border-radius: 8px;">
                            <i class="fa-solid fa-filter me-1"></i> GENERATE / FILTER DOCUMENTS
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="backToDocTypes()">
                            &larr; Back to Document Types
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 3. OFFICIAL CSB LOCATIONS MODAL -->
<div class="modal fade" id="csbLocationsModal" tabindex="-1" aria-hidden="true" style="z-index: 2060;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 500px;">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 14px;">
            <div class="modal-header py-3 px-4" style="background: #162846; color: #ffffff; border-bottom: 2px solid #c8232c;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-location-dot" style="color: #c8232c;"></i>
                    <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 15px;">BRANCH &amp; ATM LOCATIONS</h5>
                </div>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="p-2 mb-3 bg-light rounded text-muted extra-small">
                    <strong>Routing / Transit: 084301768</strong> &bull; Member FDIC &bull; Equal Housing Lender
                </div>

                <!-- Location 1 -->
                <div class="card mb-3 border shadow-xs">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Cleveland Main Office</h6>
                                <p class="text-muted small mb-1"><i class="fa-solid fa-map-pin text-danger me-1"></i> 100 S Court St, Cleveland, MS 38732</p>
                                <p class="text-muted extra-small mb-1"><i class="fa-solid fa-phone text-muted me-1"></i> (662) 843-9461</p>
                                <div class="badge bg-success-subtle text-success extra-small">Lobby &bull; Drive-Thru &bull; 24/7 ATM</div>
                            </div>
                            <a href="tel:6628439461" class="btn btn-sm btn-outline-dark rounded-circle" title="Call Branch"><i class="fa-solid fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Location 2 -->
                <div class="card mb-3 border shadow-xs">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">West End Branch</h6>
                                <p class="text-muted small mb-1"><i class="fa-solid fa-map-pin text-danger me-1"></i> 710 W Sunflower Rd, Cleveland, MS 38732</p>
                                <p class="text-muted extra-small mb-1"><i class="fa-solid fa-phone text-muted me-1"></i> (662) 843-9461</p>
                                <div class="badge bg-success-subtle text-success extra-small">Full Service &bull; Drive-Up ATM</div>
                            </div>
                            <a href="tel:6628439461" class="btn btn-sm btn-outline-dark rounded-circle" title="Call Branch"><i class="fa-solid fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Location 3 -->
                <div class="card mb-3 border shadow-xs">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Grenada State Bank Branch</h6>
                                <p class="text-muted small mb-1"><i class="fa-solid fa-map-pin text-danger me-1"></i> 1990 Commerce St, Grenada, MS 38901</p>
                                <p class="text-muted extra-small mb-1"><i class="fa-solid fa-phone text-muted me-1"></i> (662) 226-5500</p>
                                <div class="badge bg-success-subtle text-success extra-small">Branch &bull; 24/7 ATM</div>
                            </div>
                            <a href="tel:6622265500" class="btn btn-sm btn-outline-dark rounded-circle" title="Call Branch"><i class="fa-solid fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Location 4 -->
                <div class="card mb-2 border shadow-xs">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Boyle 24/7 Drive-Up ATM</h6>
                                <p class="text-muted small mb-1"><i class="fa-solid fa-map-pin text-danger me-1"></i> Hwy 61 South, Boyle, MS 38730</p>
                                <div class="badge bg-primary-subtle text-primary extra-small">Fee-Free ATM for CSB Cards</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light justify-content-between">
                <span class="text-muted extra-small">All branches closed on Federal Reserve Bank holidays.</span>
                <button type="button" class="btn btn-sm btn-secondary fw-bold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function openDocTypeAction(action) {
    var typesModal = bootstrap.Modal.getInstance(document.getElementById('csbDocumentsModal'));
    if (typesModal) typesModal.hide();

    if (action === 'statements') {
        setTimeout(function() {
            var wiz = bootstrap.Modal.getOrCreateInstance(document.getElementById('csbDocFilterModal'));
            wiz.show();
        }, 300);
    } else if (action === 'notices') {
        window.location.href = "{{ route('user.documents.index') }}";
    }
}

function backToDocTypes() {
    var wiz = bootstrap.Modal.getInstance(document.getElementById('csbDocFilterModal'));
    if (wiz) wiz.hide();
    setTimeout(function() {
        var typesModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('csbDocumentsModal'));
        typesModal.show();
    }, 300);
}

function csbToggleCustomDates(val) {
    var customRow = document.getElementById('csbCustomDateFields');
    if (customRow) {
        if (val === 'custom') {
            customRow.classList.remove('d-none');
        } else {
            customRow.classList.add('d-none');
        }
    }
}

function handleDocFilterSubmit(e) {
    e.preventDefault();
    var type = document.getElementById('csbFilterType').value;
    var acc = document.getElementById('csbFilterAccount').value;
    var period = document.getElementById('csbFilterRange').value;

    var wiz = bootstrap.Modal.getInstance(document.getElementById('csbDocFilterModal'));
    if (wiz) wiz.hide();

    if (type === 'statement') {
        var url = "{{ route('user.transactions.export.pdf') }}?period=" + encodeURIComponent(period);
        if (acc === 'all') {
            url += "&accounts[]=checking&accounts[]=savings&accounts[]=ira&accounts[]=heloc&accounts[]=cc&accounts[]=loan";
        } else {
            url += "&accounts[]=" + encodeURIComponent(acc);
        }

        if (period === 'custom') {
            var fromDate = document.getElementById('csbDocFromDate').value;
            var toDate = document.getElementById('csbDocToDate').value;
            if (fromDate) url += "&from_date=" + encodeURIComponent(fromDate);
            if (toDate) url += "&to_date=" + encodeURIComponent(toDate);
        }

        if (typeof window.showLoader === 'function') {
            window.showLoader('Generating Statement PDF...');
        }

        fetch(url)
            .then(function(res) {
                if (!res.ok) throw new Error('Download failed');
                return res.blob();
            })
            .then(function(blob) {
                var blobUrl = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = blobUrl;
                a.download = 'CSB_Official_Statement.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(blobUrl);
                a.remove();
            })
            .catch(function(err) {
                console.error(err);
                if (typeof window.hideLoader === 'function') {
                    window.hideLoader();
                }
                window.location.href = url; // Fallback
            })
            .finally(function() {
                if (typeof window.hideLoader === 'function') {
                    window.hideLoader();
                }
            });
    } else if (type === 'direct_deposit') {
        var depType = (acc === 'all') ? 'checking' : acc;
        window.location.href = "{{ route('user.direct-deposit', ':type') }}".replace(':type', depType);
    } else {
        window.location.href = "{{ route('user.documents.index') }}";
    }
}
</script>

