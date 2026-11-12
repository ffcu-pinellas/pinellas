@extends('frontend::layouts.user')

@section('title')
    {{ __('Dispute Center') }}
@endsection

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('user.transactions') }}" class="back-nav-link">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h3 fw-bold mb-0">{{ __('Reg E Dispute Center') }}</h1>
                <p class="text-muted small mb-0">{{ __('Track unauthorized charge claims, provisional credits, and investigation resolutions.') }}</p>
            </div>
        </div>
        <a href="{{ route('user.transactions') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
            <i class="fas fa-list-ul me-1"></i> {{ __('File New From Activity') }}
        </a>
    </div>

    <!-- Summary KPI Strip -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ route('user.disputes.index') }}" class="text-decoration-none">
                <div class="site-card p-3 h-100 {{ $statusFilter === 'all' ? 'border border-primary' : '' }}">
                    <div class="text-muted small fw-bold text-uppercase">{{ __('Total Claims') }}</div>
                    <div class="h3 fw-bold mb-0 mt-1 text-dark">{{ $counts['all'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('user.disputes.index', ['status' => 'active']) }}" class="text-decoration-none">
                <div class="site-card p-3 h-100 {{ $statusFilter === 'active' ? 'border border-warning' : '' }}">
                    <div class="text-muted small fw-bold text-uppercase">{{ __('Active Review') }}</div>
                    <div class="h3 fw-bold mb-0 mt-1 text-warning">{{ $counts['active'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('user.disputes.index', ['status' => 'provisional_credited']) }}" class="text-decoration-none">
                <div class="site-card p-3 h-100 {{ $statusFilter === 'provisional_credited' ? 'border border-info' : '' }}">
                    <div class="text-muted small fw-bold text-uppercase">{{ __('Provisional Credits') }}</div>
                    <div class="h3 fw-bold mb-0 mt-1 text-info">{{ $counts['provisional_credited'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('user.disputes.index', ['status' => 'resolved']) }}" class="text-decoration-none">
                <div class="site-card p-3 h-100 {{ $statusFilter === 'resolved' ? 'border border-success' : '' }}">
                    <div class="text-muted small fw-bold text-uppercase">{{ __('Closed / Resolved') }}</div>
                    <div class="h3 fw-bold mb-0 mt-1 text-success">{{ $counts['resolved'] }}</div>
                </div>
            </a>
        </div>
    </div>

    <div class="site-card overflow-hidden">
        <div class="p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">{{ __('Your Dispute Claims') }}</h5>
            <span class="badge bg-light text-dark border rounded-pill px-3 py-2">{{ __('Regulation E Protected') }}</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">{{ __('Claim #') }}</th>
                        <th>{{ __('Transaction / Payee') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Filed') }}</th>
                        <th class="text-end pe-4">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disputes as $dispute)
                        @php
                            $badgeClass = match($dispute->status) {
                                'open' => 'bg-warning text-dark',
                                'provisional_credited' => 'bg-info text-white',
                                'resolved_fraud' => 'bg-success text-white',
                                'denied' => 'bg-danger text-white',
                                default => 'bg-secondary text-white',
                            };
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('user.disputes.show', $dispute->claim_number) }}" class="fw-bold text-primary text-decoration-none">
                                    #{{ $dispute->claim_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $dispute->merchant_name ?: ($dispute->transaction?->description ?? 'Debit Transaction') }}</div>
                                <div class="text-muted small"><code>{{ $dispute->tnx }}</code></div>
                            </td>
                            <td class="small">{{ $dispute->category_label }}</td>
                            <td class="fw-bold">${{ number_format((float) $dispute->disputed_amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2">{{ $dispute->status_label }}</span>
                            </td>
                            <td class="text-muted small">{{ $dispute->created_at?->format('M d, Y') }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('user.disputes.show', $dispute->claim_number) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                    {{ __('View Timeline') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                                    <i class="fas fa-shield-alt fa-2x text-muted opacity-50"></i>
                                </div>
                                <h6 class="fw-bold">{{ __('No dispute claims found') }}</h6>
                                <p class="text-muted small mb-3">{{ __('To dispute an unauthorized or incorrect charge, open any completed debit in Activity and click Dispute Transaction.') }}</p>
                                <a href="{{ route('user.transactions') }}" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
                                    {{ __('Go to Activity') }}
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($disputes->hasPages())
            <div class="p-3 bg-light d-flex justify-content-center">
                {{ $disputes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

