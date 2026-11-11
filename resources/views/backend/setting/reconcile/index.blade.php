@extends('backend.setting.index')
@section('setting-title')
    {{ __('Audit & Ledger Reconciliation') }}
@endsection
@section('title')
    {{ __('Audit & Ledger Reconciliation') }}
@endsection
@section('setting-content')
    <div class="col-xl-12">
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="site-card h-100">
                    <div class="site-card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">{{ __('Members Audited') }}</div>
                            <h3 class="mb-0 mt-1 fw-bold">{{ $report['total_users_scanned'] }}</h3>
                        </div>
                        <span class="badge bg-primary p-2"><i data-lucide="users"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="site-card h-100">
                    <div class="site-card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">{{ __('Wallets With Discrepancies') }}</div>
                            <h3 class="mb-0 mt-1 fw-bold {{ $report['discrepancy_users'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $report['discrepancy_users'] }}
                            </h3>
                        </div>
                        <span class="badge {{ $report['discrepancy_users'] > 0 ? 'bg-danger' : 'bg-success' }} p-2">
                            <i data-lucide="scale"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="site-card h-100">
                    <div class="site-card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">{{ __('Total Ledger Variance') }}</div>
                            <h3 class="mb-0 mt-1 fw-bold">
                                {{ setting('currency_symbol', 'global') ?? '$' }}{{ number_format($report['total_absolute_drift'], 2) }}
                            </h3>
                        </div>
                        <span class="badge bg-warning text-dark p-2"><i data-lucide="alert-circle"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="site-card">
            <div class="site-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h3 class="title mb-0">{{ __('Wallet vs Settled Ledger Audit') }}</h3>
                    <small class="text-muted">{{ __('Compares Checking, Savings & IRA stored balances against the net sum of settled & pre-debited transactions.') }}</small>
                </div>
                <form method="GET" action="{{ route('admin.settings.reconcile.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
                    <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" style="width: 220px;" placeholder="{{ __('Search user, email, acct #') }}">
                    <select name="filter" class="form-select form-select-sm" style="width: 170px;" onchange="this.form.submit()">
                        <option value="mismatches" {{ $filter === 'mismatches' ? 'selected' : '' }}>{{ __('Discrepancies Only') }}</option>
                        <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>{{ __('All Audited Accounts') }}</option>
                    </select>
                    <button type="submit" class="site-btn-xs primary-btn">{{ __('Scan') }}</button>
                </form>
            </div>
            <div class="site-card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Member') }}</th>
                            <th>{{ __('Wallet') }}</th>
                            <th>{{ __('Stored Balance') }}</th>
                            <th>{{ __('Ledger Net Sum') }}</th>
                            <th>{{ __('Variance (Drift)') }}</th>
                            <th>{{ __('Txns Audited') }}</th>
                            <th class="text-end">{{ __('Auto-Heal Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $cur = setting('currency_symbol', 'global') ?? '$'; @endphp
                        @forelse($report['audits'] as $audit)
                            @foreach($audit['wallets'] as $w)
                                @if($filter === 'mismatches' && $w['is_match'])
                                    @continue
                                @endif
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.user.edit', $audit['user']->id) }}" class="fw-bold text-decoration-none">
                                            {{ $audit['user']->full_name }}
                                        </a>
                                        <div class="small text-muted">{{ '@' . $audit['user']->username }} • ••••{{ substr((string) $audit['user']->account_number, -4) }}</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $w['label'] }}</span></td>
                                    <td class="fw-semibold">{{ $cur }}{{ number_format($w['stored'], 2) }}</td>
                                    <td class="fw-semibold">{{ $cur }}{{ number_format($w['computed'], 2) }}</td>
                                    <td>
                                        @if($w['is_match'])
                                            <span class="badge bg-success-subtle text-success">{{ __('Balanced ($0.00)') }}</span>
                                        @else
                                            <span class="badge bg-danger">
                                                {{ $w['delta'] > 0 ? '+' : '-' }}{{ $cur }}{{ number_format($w['abs_delta'], 2) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $w['txn_count'] }}</td>
                                    <td class="text-end">
                                        @if(!$w['is_match'])
                                            <div class="d-inline-flex gap-1">
                                                <form method="POST" action="{{ route('admin.settings.reconcile.sync', [$audit['user']->id, $w['wallet_key']]) }}" onsubmit="return confirm('Overwrite stored {{ $w['label'] }} balance to match ledger ({{ $cur }}{{ number_format($w['computed'], 2) }})?')">
                                                    @csrf
                                                    <button type="submit" class="site-btn-xs primary-btn" title="Set stored balance = ledger sum">
                                                        🔧 {{ __('Sync Balance') }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.settings.reconcile.balancing', [$audit['user']->id, $w['wallet_key']]) }}" onsubmit="return confirm('Insert an official RECON balancing transaction so ledger matches stored balance ({{ $cur }}{{ number_format($w['stored'], 2) }})?')">
                                                    @csrf
                                                    <button type="submit" class="site-btn-xs dark-btn" title="Insert balancing entry keeping stored balance">
                                                        ➕ {{ __('Balancing Entry') }}
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-muted small">✓ {{ __('Verified') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i data-lucide="check-circle" class="text-success mb-2"></i>
                                    <div class="fw-semibold">{{ __('Zero ledger discrepancies detected.') }}</div>
                                    <small>{{ __('All audited Checking, Savings & IRA wallet balances match their settled transaction histories.') }}</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
