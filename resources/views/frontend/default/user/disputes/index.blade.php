@extends('frontend::layouts.user')

@section('title')
    {{ __('Transaction Disputes') }}
@endsection

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('user.transactions') }}" class="back-nav-link">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h4 fw-bold mb-0 text-dark">{{ __('Transaction Disputes') }}</h1>
                <p class="text-muted small mb-0">{{ __('Track Regulation E claims, provisional credits, and investigation outcomes.') }}</p>
            </div>
        </div>
        <a href="{{ route('user.transactions') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
            {{ __('Back to Activity') }}
        </a>
    </div>

    <!-- Summary Strip -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ route('user.disputes.index') }}" class="text-decoration-none">
                <div class="banno-card p-3 h-100" style="{{ $statusFilter === 'all' ? 'border: 1.5px solid #00549b !important;' : 'border: 1px solid #e2e8f0 !important;' }}">
                    <div class="text-muted small fw-semibold text-uppercase">{{ __('Total Claims') }}</div>
                    <div class="h4 fw-bold mb-0 mt-1 text-dark">{{ $counts['all'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('user.disputes.index', ['status' => 'active']) }}" class="text-decoration-none">
                <div class="banno-card p-3 h-100" style="{{ $statusFilter === 'active' ? 'border: 1.5px solid #00549b !important;' : 'border: 1px solid #e2e8f0 !important;' }}">
                    <div class="text-muted small fw-semibold text-uppercase">{{ __('Active Review') }}</div>
                    <div class="h4 fw-bold mb-0 mt-1 text-dark">{{ $counts['active'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('user.disputes.index', ['status' => 'provisional_credited']) }}" class="text-decoration-none">
                <div class="banno-card p-3 h-100" style="{{ $statusFilter === 'provisional_credited' ? 'border: 1.5px solid #00549b !important;' : 'border: 1px solid #e2e8f0 !important;' }}">
                    <div class="text-muted small fw-semibold text-uppercase">{{ __('Provisional Credit') }}</div>
                    <div class="h4 fw-bold mb-0 mt-1 text-primary">{{ $counts['provisional_credited'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('user.disputes.index', ['status' => 'resolved']) }}" class="text-decoration-none">
                <div class="banno-card p-3 h-100" style="{{ $statusFilter === 'resolved' ? 'border: 1.5px solid #00549b !important;' : 'border: 1px solid #e2e8f0 !important;' }}">
                    <div class="text-muted small fw-semibold text-uppercase">{{ __('Closed') }}</div>
                    <div class="h4 fw-bold mb-0 mt-1 text-dark">{{ $counts['resolved'] }}</div>
                </div>
            </a>
        </div>
    </div>

    <div class="banno-card overflow-hidden">
        <div class="p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">{{ __('Submitted Claims') }}</h5>
            <span class="badge rounded-pill px-3 py-2" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;">{{ __('Regulation E Protected') }}</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">{{ __('Claim #') }}</th>
                        <th>{{ __('Transaction / Payee') }}</th>
                        <th>{{ __('Reason') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Filed') }}</th>
                        <th class="text-end pe-4">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disputes as $dispute)
                        @php
                            $badgeStyle = match($dispute->status) {
                                'open' => 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;',
                                'provisional_credited' => 'background: #eef6fb; color: #00549b; border: 1px solid rgba(0, 84, 155, 0.2);',
                                'resolved_fraud' => 'background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;',
                                'denied' => 'background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;',
                                default => 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;',
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
                                <div class="text-muted small">{{ $dispute->tnx }}</div>
                            </td>
                            <td class="small">{{ $dispute->category_label }}</td>
                            <td class="fw-semibold text-dark">${{ number_format((float) $dispute->disputed_amount, 2) }}</td>
                            <td>
                                <span class="badge rounded-pill px-3 py-1" style="{{ $badgeStyle }} font-weight: 600;">{{ $dispute->status_label }}</span>
                            </td>
                            <td class="text-muted small">{{ $dispute->created_at?->format('M d, Y') }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('user.disputes.show', $dispute->claim_number) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                    {{ __('View Details') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-file-alt fa-2x text-muted opacity-50 mb-2"></i>
                                <h6 class="fw-semibold text-dark">{{ __('No dispute claims on file') }}</h6>
                                <p class="text-muted small mb-3">{{ __('To dispute a transaction, select it in Activity and click Dispute this transaction below Download Receipt.') }}</p>
                                <a href="{{ route('user.transactions') }}" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold">
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
