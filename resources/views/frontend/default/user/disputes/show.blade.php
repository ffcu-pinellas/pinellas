@extends('frontend::layouts.user')

@section('title')
    {{ __('Claim') }} #{{ $dispute->claim_number }}
@endsection

@section('content')
@php
    $badgeStyle = match($dispute->status) {
        'open' => 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;',
        'provisional_credited' => 'background: #eef6fb; color: #00549b; border: 1px solid rgba(0, 84, 155, 0.2);',
        'resolved_fraud' => 'background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;',
        'denied' => 'background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;',
        default => 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;',
    };
    $step = match($dispute->status) {
        'open' => 2,
        'provisional_credited' => 3,
        'resolved_fraud', 'denied' => 4,
        default => 1,
    };
    $activeNode = 'background: #00549b; color: #ffffff;';
    $doneNode = 'background: #eef6fb; color: #00549b; border: 1.5px solid #00549b;';
    $idleNode = 'background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0;';
    $deniedNode = 'background: #fef2f2; color: #991b1b; border: 1.5px solid #fecaca;';
    $upheldNode = 'background: #ecfdf5; color: #065f46; border: 1.5px solid #a7f3d0;';
@endphp

<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('user.transactions') }}" class="back-nav-link" title="{{ __('Back to Activity') }}">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h1 class="h4 fw-bold mb-0 text-dark">{{ __('Claim') }} #{{ $dispute->claim_number }}</h1>
                            <span class="badge rounded-pill px-3 py-1" style="{{ $badgeStyle }} font-weight: 600;">{{ $dispute->status_label }}</span>
                        </div>
                        <p class="text-muted small mb-0">{{ __('Filed') }} {{ $dispute->created_at?->format('M d, Y h:i A') }} • {{ $walletLabel }}</p>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('user.disputes.index') }}" class="btn btn-outline-secondary rounded-pill px-3 fw-semibold btn-sm">
                        {{ __('All Claims') }}
                    </a>
                    <a href="{{ route('user.disputes.pdf', $dispute->claim_number) }}" class="btn btn-outline-primary rounded-pill px-4 fw-semibold btn-sm">
                        <i class="fas fa-file-pdf me-1"></i> {{ __('Reg E Notice (PDF)') }}
                    </a>
                </div>
            </div>

            <!-- Institutional Monochromatic Navy 4-Stage Stepper -->
            <div class="banno-card p-4 mb-4">
                <div class="row text-center g-3">
                    <div class="col-3">
                        <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center fw-bold small"
                             style="width:34px;height:34px;{{ $step > 1 ? $doneNode : $activeNode }}">
                            <i class="fas fa-check small"></i>
                        </div>
                        <div class="small fw-semibold text-dark">{{ __('Filed') }}</div>
                    </div>
                    <div class="col-3">
                        <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center fw-bold small"
                             style="width:34px;height:34px;{{ $step === 2 ? $activeNode : ($step > 2 ? $doneNode : $idleNode) }}">
                            @if($step > 2)<i class="fas fa-check small"></i>@else 2 @endif
                        </div>
                        <div class="small fw-semibold {{ $step >= 2 ? 'text-dark' : 'text-muted' }}">{{ __('Under Review') }}</div>
                    </div>
                    <div class="col-3">
                        <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center fw-bold small"
                             style="width:34px;height:34px;{{ ($dispute->provisional_credit_tnx || $step >= 4) ? ($step === 3 ? $activeNode : $doneNode) : $idleNode }}">
                            @if($dispute->provisional_credit_tnx || $step >= 4)<i class="fas fa-check small"></i>@else 3 @endif
                        </div>
                        <div class="small fw-semibold {{ ($dispute->provisional_credit_tnx || $step >= 4) ? 'text-dark' : 'text-muted' }}">{{ __('Provisional Credit') }}</div>
                    </div>
                    <div class="col-3">
                        <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center fw-bold small"
                             style="width:34px;height:34px;{{ $step === 4 ? ($dispute->status === 'denied' ? $deniedNode : $upheldNode) : $idleNode }}">
                            @if($step === 4)
                                <i class="fas {{ $dispute->status === 'denied' ? 'fa-times' : 'fa-check' }} small"></i>
                            @else
                                4
                            @endif
                        </div>
                        <div class="small fw-semibold {{ $step === 4 ? 'text-dark' : 'text-muted' }}">{{ __('Final Decision') }}</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <!-- Claim Details -->
                    <div class="banno-card p-4 mb-4">
                        <h5 class="fw-bold mb-3 text-dark">{{ __('Dispute Summary') }}</h5>
                        <div class="row g-3 small">
                            <div class="col-6">
                                <div class="text-muted">{{ __('Disputed Amount') }}</div>
                                <div class="fs-5 fw-bold text-dark">${{ number_format((float) $dispute->disputed_amount, 2) }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">{{ __('Original Transaction') }}</div>
                                <div class="fw-semibold text-dark">{{ $dispute->tnx }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">{{ __('Category') }}</div>
                                <div class="fw-semibold text-dark">{{ $dispute->category_label }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">{{ __('Payee / Description') }}</div>
                                <div class="fw-semibold text-dark">{{ $dispute->merchant_name ?: '—' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted mb-1">{{ __('Member Statement') }}</div>
                                <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">{{ $dispute->description }}</div>
                            </div>
                            @if($dispute->provisional_credit_tnx || $dispute->clawback_tnx)
                                <div class="col-12">
                                    <div class="p-3 rounded-3" style="background: #eef6fb; border: 1px solid rgba(0, 84, 155, 0.2);">
                                        @if($dispute->provisional_credit_tnx)
                                            <div class="mb-1 text-dark"><strong>{{ __('Credit Reference:') }}</strong> {{ $dispute->provisional_credit_tnx }} (${{ number_format((float) $dispute->disputed_amount, 2) }})</div>
                                        @endif
                                        @if($dispute->clawback_tnx)
                                            <div style="color: #991b1b;"><strong>{{ __('Reversal Reference:') }}</strong> {{ $dispute->clawback_tnx }} (-${{ number_format((float) $dispute->disputed_amount, 2) }})</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if($dispute->resolution_notes)
                                <div class="col-12">
                                    <div class="text-muted mb-1">{{ __('Investigation Finding') }}</div>
                                    <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 3px solid #00549b;">{{ $dispute->resolution_notes }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Evidence Section -->
                    <div class="banno-card p-4">
                        <h5 class="fw-bold mb-3 text-dark">{{ __('Supporting Documentation') }}</h5>
                        @if($dispute->evidence_path)
                            @php
                                $isPdf = Str::endsWith(strtolower($dispute->evidence_path), '.pdf');
                            @endphp
                            <div class="mb-3 p-3 rounded-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                <div>
                                    <i class="fas {{ $isPdf ? 'fa-file-pdf' : 'fa-paperclip' }} text-primary me-2"></i>
                                    <span class="small fw-semibold text-dark">{{ basename($dispute->evidence_path) }}</span>
                                </div>
                                <a href="{{ route('user.disputes.attachment', $dispute->claim_number) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    {{ __('View File') }}
                                </a>
                            </div>
                        @else
                            <p class="text-muted small mb-3">{{ __('No supporting receipt or screenshot attached yet.') }}</p>
                        @endif

                        @if($dispute->isActive())
                            <form action="{{ route('user.disputes.evidence', $dispute->claim_number) }}" method="POST" enctype="multipart/form-data" class="border-top pt-3 mt-2">
                                @csrf
                                <label class="form-label small fw-semibold">{{ __('Attach Additional Receipt / Documentation') }}</label>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <input type="file" name="evidence" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="evidence_note" class="form-control form-control-sm" placeholder="Optional note about this attachment">
                                    </div>
                                    <div class="col-4">
                                        <button type="submit" class="btn btn-sm btn-primary w-100 rounded-pill fw-semibold">{{ __('Upload') }}</button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Audit Timeline -->
                <div class="col-lg-5">
                    <div class="banno-card p-4 h-100">
                        <h5 class="fw-bold mb-3 text-dark">{{ __('Claim History') }}</h5>
                        @forelse(array_reverse($dispute->timeline ?? []) as $item)
                            @php
                                $cleanActor = str_ireplace(
                                    ['Executive Operations (Telegram)', '(Telegram)', 'Telegram'],
                                    ['Member Protection & Dispute Operations', '(Dispute Resolution Team)', 'Dispute Resolution Team'],
                                    (string) ($item['actor'] ?? 'System')
                                );
                            @endphp
                            <div class="pb-3 mb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <span class="fw-semibold small text-dark">{{ $item['event'] ?? 'Update' }}</span>
                                    <span class="text-muted" style="font-size: 11px; white-space: nowrap;">{{ $item['at'] ?? '' }}</span>
                                </div>
                                <div class="small fw-semibold mb-1" style="color: #00549b;">{{ $cleanActor }}</div>
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
