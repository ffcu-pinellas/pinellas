@extends('backend.layouts.app')
@section('title')
    {{ __('Reg E Transaction Disputes') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="title-content d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h2 class="title mb-0">{{ __('Transaction Disputes (Reg E)') }}</h2>
                            <span class="site-badge primary">{{ __('Ledger Synced') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <a href="{{ route('admin.disputes.index') }}" class="text-decoration-none">
                        <div class="site-card h-100 {{ $status === 'all' ? 'border border-primary' : '' }}">
                            <div class="site-card-body">
                                <div class="text-muted small">{{ __('All Claims') }}</div>
                                <h3 class="mb-0 fw-bold mt-1 text-dark">{{ $stats['all'] }}</h3>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="{{ route('admin.disputes.index', ['status' => 'open']) }}" class="text-decoration-none">
                        <div class="site-card h-100 {{ $status === 'open' ? 'border border-primary' : '' }}">
                            <div class="site-card-body">
                                <div class="text-muted small">{{ __('Open — Pending Review') }}</div>
                                <h3 class="mb-0 fw-bold mt-1 text-dark">{{ $stats['open'] }}</h3>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="{{ route('admin.disputes.index', ['status' => 'provisional_credited']) }}" class="text-decoration-none">
                        <div class="site-card h-100 {{ $status === 'provisional_credited' ? 'border border-primary' : '' }}">
                            <div class="site-card-body">
                                <div class="text-muted small">{{ __('Provisional Credit Active') }}</div>
                                <h3 class="mb-0 fw-bold mt-1 text-primary">{{ $stats['provisional_credited'] }}</h3>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="{{ route('admin.disputes.index', ['status' => 'resolved_fraud']) }}" class="text-decoration-none">
                        <div class="site-card h-100 {{ $status === 'resolved_fraud' ? 'border border-primary' : '' }}">
                            <div class="site-card-body">
                                <div class="text-muted small">{{ __('Finalized Claims') }}</div>
                                <h3 class="mb-0 fw-bold mt-1 text-dark">{{ $stats['resolved'] }}</h3>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="site-card">
                <div class="site-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h4 class="card-title mb-0">{{ __('Dispute Claims') }}</h4>
                    <form method="GET" action="{{ route('admin.disputes.index') }}" class="d-flex gap-2">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <input type="text" name="search" class="form-control form-control-sm" value="{{ $search }}" placeholder="Claim #, TRX, or member...">
                        <button type="submit" class="site-btn-sm primary-btn">{{ __('Search') }}</button>
                    </form>
                </div>
                <div class="site-card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('Claim #') }}</th>
                                <th>{{ __('Member') }}</th>
                                <th>{{ __('Orig. Txn') }}</th>
                                <th>{{ __('Reason') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Filed') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($disputes as $dispute)
                                @php
                                    $badge = match($dispute->status) {
                                        'open' => 'warnning',
                                        'provisional_credited' => 'pending',
                                        'resolved_fraud' => 'success',
                                        'denied' => 'danger',
                                        default => 'primary',
                                    };
                                @endphp
                                <tr>
                                    <td><a href="{{ route('admin.disputes.show', $dispute->id) }}" class="fw-bold text-primary">#{{ $dispute->claim_number }}</a></td>
                                    <td>
                                        @if($dispute->user)
                                            <a href="{{ route('admin.user.edit', $dispute->user->id) }}" class="fw-bold">{{ $dispute->user->full_name }}</a>
                                            <div class="text-muted small">{{ '@' . $dispute->user->username }}</div>
                                        @else
                                            <span class="text-muted">{{ __('Deleted Member') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $dispute->tnx }}</span>
                                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($dispute->merchant_name ?: ($dispute->transaction?->description ?? ''), 35) }}</div>
                                    </td>
                                    <td class="small">{{ $dispute->category_label }}</td>
                                    <td class="fw-bold text-dark">${{ number_format((float) $dispute->disputed_amount, 2) }}</td>
                                    <td><span class="site-badge {{ $badge }}">{{ $dispute->status_label }}</span></td>
                                    <td class="small text-muted">{{ $dispute->created_at?->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.disputes.show', $dispute->id) }}" class="site-btn-sm primary-btn">
                                            <i data-lucide="eye"></i> {{ __('Inspect') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">{{ __('No transaction disputes found for this filter.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $disputes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
