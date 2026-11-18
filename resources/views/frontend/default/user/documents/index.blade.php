@extends('frontend::layouts.user')

@section('title')
    {{ __('Documents & Statements') }}
@endsection

@php
    $u = auth()->user();
    $instName = (!empty($currentInstitution) && !$currentInstitution->is_default) 
        ? $currentInstitution->name 
        : (setting('site_title') ?? 'FrontField Credit Union');
    
    $docAccounts = [];
    if ($u) {
        $docAccounts[] = ['id' => 'checking', 'label' => 'Checking (*' . substr($u->account_number ?? '1397', -4) . ')'];
        if (!empty($u->savings_account_number) || $u->savings_balance !== null) {
            $docAccounts[] = ['id' => 'savings', 'label' => 'Primary Savings (*' . substr($u->savings_account_number ?? $u->account_number ?? '4902', -4) . ')'];
        }
        if ($u->ira_status == 1) {
            $docAccounts[] = ['id' => 'ira', 'label' => 'IRA Account (*' . substr($u->ira_account_number ?? $u->account_number, -4) . ')'];
        }
        if ($u->heloc_status == 1) {
            $docAccounts[] = ['id' => 'heloc', 'label' => 'HELOC (*' . substr($u->heloc_account_number ?? $u->account_number, -4) . ')'];
        }
        if ($u->cc_status == 1) {
            $docAccounts[] = ['id' => 'cc', 'label' => 'Credit Card (*' . substr($u->cc_account_number ?? $u->account_number, -4) . ')'];
        }
        if ($u->loan_account_status == 1) {
            $docAccounts[] = ['id' => 'loan', 'label' => 'Loan Account (*' . substr($u->loan_account_number ?? $u->account_number ?? '8859', -4) . ')'];
        }
    }
@endphp

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="h4 fw-bold mb-1">{{ __('Documents & Statements Hub') }}</h2>
                <p class="text-muted small mb-0">{{ __('Access your official e-statements, payroll direct deposit authorization forms, and electronic agreements.') }}</p>
            </div>
            @if(isset($pendingCount) && $pendingCount > 0)
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                    <i class="fas fa-pen-nib me-1"></i> {{ $pendingCount }} {{ __('Awaiting Your Signature') }}
                </span>
            @endif
        </div>
    </div>

    <!-- Quick Services Grid -->
    <div class="row g-4 mb-4">
        <!-- 1. E-Statement Generator Card -->
        <div class="col-lg-7 col-md-12">
            <div class="site-card p-4 h-100 shadow-sm border-0" style="border-radius: 16px; background: #ffffff;">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(0, 84, 155, 0.1); color: var(--primary-color, #00549b);">
                        <i class="fas fa-file-invoice fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ __('Official Account E-Statement') }}</h5>
                        <div class="text-muted extra-small">{{ __('Generate certified monthly or custom period transaction statements.') }}</div>
                    </div>
                </div>

                <form id="docStatementForm" data-no-loader="true" onsubmit="generateStatement(event)">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">{{ __('Select Account') }}</label>
                            <select class="form-select shadow-none" id="statementAccountSelect" style="font-size: 13.5px;" required>
                                <option value="all" selected>{{ __('All Accounts (Consolidated)') }}</option>
                                @foreach($docAccounts as $acc)
                                    <option value="{{ $acc['id'] }}">{{ $acc['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">{{ __('Statement Period') }}</label>
                            <select class="form-select shadow-none" id="statementPeriodSelect" onchange="toggleCustomStatementDates(this.value)" style="font-size: 13.5px;" required>
                                <option value="1m" selected>{{ __('Last 30 Days') }}</option>
                                <option value="3m">{{ __('Last 90 Days (Quarterly)') }}</option>
                                <option value="6m">{{ __('Last 6 Months') }}</option>
                                <option value="1y">{{ __('Last 1 Year (Annual)') }}</option>
                                <option value="custom">{{ __('Custom Date Range...') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Custom Date Fields -->
                    <div id="statementCustomDateWrap" class="row g-3 mb-3 d-none">
                        <div class="col-6">
                            <label class="form-label extra-small fw-bold text-muted text-uppercase mb-1">{{ __('Start Date') }}</label>
                            <input type="date" id="statementFromDate" class="form-control form-control-sm shadow-none" value="{{ now()->subMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label extra-small fw-bold text-muted text-uppercase mb-1">{{ __('End Date') }}</label>
                            <input type="date" id="statementToDate" class="form-control form-control-sm shadow-none" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="p-2 mb-3 rounded-3 bg-light border small text-muted d-flex align-items-center gap-2">
                        <i class="fas fa-file-contract text-success"></i>
                        <span style="font-size: 12px;">{{ __('Includes official institution header, verified ledger entries, and audit authentication.') }}</span>
                    </div>

                    <button type="submit" id="btnGenerateStatement" class="btn text-white w-100 py-2 fw-bold shadow-sm" style="background: var(--primary-color, #00549b); border-radius: 8px;">
                        <i class="fas fa-file-pdf me-2"></i> {{ __('Generate & Download Statement (PDF)') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- 2. Direct Deposit Form Card -->
        <div class="col-lg-5 col-md-12">
            <div class="site-card p-4 h-100 shadow-sm border-0" style="border-radius: 16px; background: #ffffff;">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(40, 167, 69, 0.1); color: #28a745;">
                        <i class="fas fa-money-check-dollar fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ __('Direct Deposit Authorization') }}</h5>
                        <div class="text-muted extra-small">{{ __('Prefilled form for employers, payroll, or benefits.') }}</div>
                    </div>
                </div>

                <p class="small text-muted mb-3">
                    {{ __('Easily switch your direct deposit to :bank. Download a customized PDF complete with your official account routing information to give to your HR or payroll administrator.', ['bank' => $instName]) }}
                </p>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase mb-1">{{ __('Deposit Into') }}</label>
                    <select class="form-select shadow-none" id="ddAccountSelect" style="font-size: 13.5px;">
                        <option value="checking" selected>{{ __('Personal Checking') }} (*{{ substr($u->account_number ?? '1397', -4) }})</option>
                        @if(!empty($u->savings_account_number) || $u->savings_balance !== null)
                            <option value="savings">{{ __('Primary Savings') }} (*{{ substr($u->savings_account_number ?? $u->account_number ?? '4902', -4) }})</option>
                        @endif
                    </select>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="button" onclick="downloadDirectDepositForm()" class="btn btn-outline-success py-2 fw-bold" style="border-radius: 8px;">
                        <i class="fas fa-download me-2"></i> {{ __('Download Direct Deposit Form (PDF)') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Member Disclosures & Electronic Signatures Table -->
    <div class="row">
        <div class="col-12">
            <div class="site-card p-3 p-md-4 border-0 shadow-sm" style="border-radius: 16px; background: #ffffff;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-file-signature text-muted me-2"></i>{{ __('Electronic Disclosures & Agreements') }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Document Title') }}</th>
                                <th>{{ __('Issued') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Expires / Signed') }}</th>
                                <th class="text-end">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($signatures ?? [] as $sig)
                                @php
                                    $docTitle = data_get($sig->metadata, 'title') ?? ($sig->documentHistory?->title ?? 'Official Member Agreement');
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $docTitle }}</td>
                                    <td class="text-muted small">{{ $sig->created_at?->format('M d, Y') }}</td>
                                    <td>
                                        @if($sig->status === 'signed')
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> {{ __('Executed & Signed') }}</span>
                                        @elseif($sig->status === 'pending')
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> {{ __('Action Required') }}</span>
                                        @elseif($sig->status === 'declined')
                                            <span class="badge bg-danger">{{ __('Declined') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Expired') }}</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        @if($sig->status === 'signed' && $sig->signed_at)
                                            {{ __('Signed') }} {{ $sig->signed_at->format('M d, Y H:i') }} UTC
                                        @elseif($sig->expires_at)
                                            {{ __('Due by') }} {{ $sig->expires_at->format('M d, Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($sig->status === 'pending')
                                            <a href="{{ route('user.documents.sign.show', $sig->external_signature_id) }}" class="btn btn-sm btn-primary fw-semibold">
                                                <i class="fas fa-signature me-1"></i> {{ __('Review & Sign') }}
                                            </a>
                                        @else
                                            <a href="{{ route('user.documents.sign.show', $sig->external_signature_id) }}" class="btn btn-sm btn-outline-secondary me-1">
                                                {{ __('View') }}
                                            </a>
                                            <a href="{{ route('user.documents.download', $sig->external_signature_id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-pdf me-1"></i> {{ __('Download PDF') }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-2x mb-2 opacity-50"></i>
                                        <div class="fw-semibold">{{ __('No electronic signature requests on file yet.') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(isset($signatures) && method_exists($signatures, 'hasPages') && $signatures->hasPages())
                    <div class="mt-3">
                        {{ $signatures->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function toggleCustomStatementDates(val) {
        var wrap = document.getElementById('statementCustomDateWrap');
        if (wrap) {
            if (val === 'custom') {
                wrap.classList.remove('d-none');
            } else {
                wrap.classList.add('d-none');
            }
        }
    }

    function generateStatement(e) {
        e.preventDefault();
        var acc = document.getElementById('statementAccountSelect').value;
        var period = document.getElementById('statementPeriodSelect').value;
        var $btn = $('#btnGenerateStatement');
        var origHtml = $btn.html();

        if (typeof window.showLoader === 'function') {
            window.showLoader('Generating Official eStatement PDF...');
        }
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Preparing PDF Statement...');

        var url = "{{ route('user.transactions.export.pdf') }}?period=" + encodeURIComponent(period);

        if (acc === 'all') {
            url += "&accounts[]=checking&accounts[]=savings&accounts[]=ira&accounts[]=heloc&accounts[]=cc&accounts[]=loan";
        } else {
            url += "&accounts[]=" + encodeURIComponent(acc);
        }

        if (period === 'custom') {
            var from = document.getElementById('statementFromDate').value;
            var to = document.getElementById('statementToDate').value;
            if (from) url += "&from_date=" + encodeURIComponent(from);
            if (to) url += "&to_date=" + encodeURIComponent(to);
        }

        fetch(url)
            .then(function(res) {
                if (!res.ok) throw new Error('Statement download failed with status ' + res.status);
                return res.blob();
            })
            .then(function(blob) {
                var blobUrl = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = blobUrl;
                a.download = 'Official_eStatement.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(blobUrl);
                a.remove();
            })
            .catch(function(err) {
                console.error(err);
                window.location.href = url; // Fallback
            })
            .finally(function() {
                if (typeof window.hideLoader === 'function') {
                    window.hideLoader();
                }
                $btn.prop('disabled', false).html(origHtml);
            });
    }

    function downloadDirectDepositForm() {
        var accType = document.getElementById('ddAccountSelect').value;
        var url = "{{ route('user.direct-deposit', ':type') }}".replace(':type', accType);
        window.location.href = url;
    }
</script>
@endsection

