@extends('frontend::layouts.user')

@section('title')
    {{ __('Claim') }} #{{ $dispute->claim_number }}
@endsection

@section('content')
@php
    $badgeClass = match($dispute->status) {
        'open' => 'bg-warning text-dark',
        'provisional_credited' => 'bg-info text-white',
        'resolved_fraud' => 'bg-success text-white',
        'denied' => 'bg-danger text-white',
        default => 'bg-secondary text-white',
    };
    $step = match($dispute->status) {
        'open' => 2,
        'provisional_credited' => 3,
        'resolved_fraud', 'denied' => 4,
        default => 1,
    };
@endphp

<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('user.disputes.index') }}" class="back-nav-link">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h1 class="h3 fw-bold mb-0">{{ __('Claim') }} #{{ $dispute->claim_number }}</h1>
                            <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2">{{ $dispute->status_label }}</span>
                        </div>
                        <p class="text-muted small mb-0">{{ __('Filed') }} {{ $dispute->created_at?->format('M d, Y h:i A') }} • {{ $walletLabel }}</p>
                    </div>
                </div>
                <a href="{{ route('user.disputes.pdf', $dispute->claim_number) }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold btn-sm">
                    <i class="fas fa-file-pdf me-2"></i> {{ __('Download Reg E Notice (PDF)') }}
                </a>
            </div>

            <!-- Visual 4-Stage Reg E Stepper -->
            <div class="site-card p-4 mb-4">
                <div class="row text-center g-3">
                    <div class="col-3">
                        <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center bg-success text-white" style="width:36px;height:36px;">
                            <i class="fas fa-check small"></i>
                        </div>
                        <div class="small fw-bold">{{ __('Filed') }}</div>
                    </div>
                    <div class="col-3">
                        <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center {{ $step >= 2 ? 'bg-primary text-white' : 'bg-light text-muted' }}" style="width:36px;height:36px;">
                            <i class="fas fa-search small"></i>
                        </div>
                        <div class="small fw-bold">{{ __('Investigating') }}</div>
                    </div>
                    <div class="col-3">
                        <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center {{ ($dispute->provisional_credit_tnx || $step >= 4) ? 'bg-info text-white' : 'bg-light text-muted' }}" style="width:36px;height:36px;">
                            <i class="fas fa-hand-holding-usd small"></i>
                        </div>
                        <div class="small fw-bold">{{ __('Provisional Credit') }}</div>
                    </div>
                    <div class="col-3">
                        <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center {{ $step === 4 ? ($dispute->status === 'resolved_fraud' ? 'bg-success text-white' : 'bg-danger text-white') : 'bg-light text-muted' }}" style="width:36px;height:36px;">
                            <i class="fas {{ $dispute->status === 'denied' ? 'fa-times' : 'fa-flag-checkered' }} small"></i>
                        </div>
                        <div class="small fw-bold">{{ __('Final Decision') }}</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <!-- Claim Details -->
                    <div class="site-card p-4 mb-4">
                        <h5 class="fw-bold mb-3">{{ __('Dispute Summary') }}</h5>
                        <div class="row g-3 small">
                            <div class="col-6">
                                <div class="text-muted">{{ __('Disputed Amount') }}</div>
                                <div class="fs-5 fw-bold text-dark">${{ number_format((float) $dispute->disputed_amount, 2) }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">{{ __('Original Transaction') }}</div>
                                <div class="fw-bold"><code>{{ $dispute->tnx }}</code></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">{{ __('Reg E Category') }}</div>
                                <div class="fw-semibold">{{ $dispute->category_label }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">{{ __('Payee / Description') }}</div>
                                <div class="fw-semibold">{{ $dispute->merchant_name ?: '—' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted mb-1">{{ __('Your Statement') }}</div>
                                <div class="p-3 bg-light rounded-3">{{ $dispute->description }}</div>
                            </div>
                            @if($dispute->provisional_credit_tnx || $dispute->clawback_tnx)
                                <div class="col-12">
                                    <div class="p-3 rounded-3 border border-info bg-info bg-opacity-10">
                                        @if($dispute->provisional_credit_tnx)
                                            <div class="mb-1"><strong>{{ __('Credit Reference:') }}</strong> <code>{{ $dispute->provisional_credit_tnx }}</code> (${{ number_format((float) $dispute->disputed_amount, 2) }})</div>
                                        @endif
                                        @if($dispute->clawback_tnx)
                                            <div class="text-danger"><strong>{{ __('Reversal Reference:') }}</strong> <code>{{ $dispute->clawback_tnx }}</code> (-${{ number_format((float) $dispute->disputed_amount, 2) }})</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if($dispute->resolution_notes)
                                <div class="col-12">
                                    <div class="text-muted mb-1">{{ __('Investigation Finding') }}</div>
                                    <div class="p-3 bg-light border-start border-4 border-primary rounded-3">{{ $dispute->resolution_notes }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Evidence Section -->
                    <div class="site-card p-4">
                        <h5 class="fw-bold mb-3">{{ __('Supporting Documentation') }}</h5>
                        @if($dispute->evidence_path)
                            @php
                                $isPdf = Str::endsWith(strtolower($dispute->evidence_path), '.pdf');
                            @endphp
                            <div class="mb-3 p-3 bg-light rounded-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <i class="fas {{ $isPdf ? 'fa-file-pdf text-danger' : 'fa-image text-primary' }} me-2"></i>
                                    <span class="small fw-semibold">{{ basename($dispute->evidence_path) }}</span>
                                </div>
                                <a href="{{ asset($dispute->evidence_path) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    {{ __('Open Attachment') }}
                                </a>
                            </div>
                        @else
                            <p class="text-muted small mb-3">{{ __('No receipt or screenshot uploaded yet.') }}</p>
                        @endif

                        @if($dispute->isActive())
                            <form action="{{ route('user.disputes.evidence', $dispute->claim_number) }}" method="POST" enctype="multipart/form-data" class="border-top pt-3 mt-2">
                                @csrf
                                <label class="form-label small fw-bold">{{ __('Upload Additional Receipt / Proof') }}</label>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <input type="file" name="evidence" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="evidence_note" class="form-control form-control-sm" placeholder="Optional note about this file">
                                    </div>
                                    <div class="col-4">
                                        <button type="submit" class="btn btn-sm btn-primary w-100 rounded-pill fw-bold">{{ __('Upload') }}</button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Audit Timeline -->
                <div class="col-lg-5">
                    <div class="site-card p-4 h-100">
                        <h5 class="fw-bold mb-3">{{ __('Claim Activity Timeline') }}</h5>
                        @forelse(array_reverse($dispute->timeline ?? []) as $item)
                            <div class="pb-3 mb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <span class="fw-bold small text-dark">{{ $item['event'] ?? 'Update' }}</span>
                                    <span class="text-muted" style="font-size: 11px; white-space: nowrap;">{{ $item['at'] ?? '' }}</span>
                                </div>
                                <div class="text-primary small fw-semibold mb-1">{{ $item['actor'] ?? 'System' }}</div>
                                @if(!empty($item['note']))
                                    <div class="text-muted small">{{ $item['note'] }}</div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted small">{{ __('No timeline events recorded.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

