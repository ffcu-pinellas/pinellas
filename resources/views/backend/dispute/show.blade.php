@extends('backend.layouts.app')
@section('title')
    {{ __('Dispute') }} #{{ $dispute->claim_number }}
@endsection
@section('content')
@php
    $canFinancialAct = ! (auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin')
        && ! auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'))
        || auth('admin')->user()->can('officer-balance-manage');
    $badgeClass = match($dispute->status) {
        'open' => 'warnning',
        'provisional_credited' => 'pending',
        'resolved_fraud' => 'success',
        'denied' => 'danger',
        default => 'primary',
    };
@endphp
<div class="main-content">
    <div class="page-title">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <div class="title-content d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h2 class="title mb-1">{{ __('Claim') }} #{{ $dispute->claim_number }}</h2>
                            <div class="d-flex align-items-center gap-2">
                                <span class="site-badge {{ $badgeClass }}">{{ $dispute->status_label }}</span>
                                <span class="text-muted small">{{ $walletLabel }}</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.disputes.index') }}" class="site-btn-sm primary-btn">
                            <i data-lucide="arrow-left"></i> {{ __('Back to Queue') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="site-card mb-4">
                    <div class="site-card-header">
                        <h4 class="card-title mb-0">{{ __('Claim Investigation Details') }}</h4>
                    </div>
                    <div class="site-card-body">
                        <table class="table table-bordered table-sm mb-3">
                            <tr>
                                <th width="34%">{{ __('Member') }}</th>
                                <td>
                                    @if($dispute->user)
                                        <a href="{{ route('admin.user.edit', $dispute->user->id) }}" class="fw-bold">{{ $dispute->user->full_name }}</a>
                                        ({{ '@' . $dispute->user->username }})
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            <tr><th>{{ __('Original Txn Ref') }}</th><td><span class="fw-semibold">{{ $dispute->tnx }}</span></td></tr>
                            <tr><th>{{ __('Wallet Affected') }}</th><td>{{ $walletLabel }}</td></tr>
                            <tr><th>{{ __('Disputed Amount') }}</th><td class="fw-bold text-dark">${{ number_format((float) $dispute->disputed_amount, 2) }}</td></tr>
                            <tr><th>{{ __('Reg E Reason') }}</th><td><strong>{{ $dispute->category_label }}</strong></td></tr>
                            <tr><th>{{ __('Payee / Merchant') }}</th><td>{{ $dispute->merchant_name ?: '—' }}</td></tr>
                            <tr><th>{{ __('Filed Date') }}</th><td>{{ $dispute->created_at?->format('M d, Y H:i T') }}</td></tr>
                            @if($dispute->provisional_credit_tnx)
                                <tr><th>{{ __('Provisional / Refund Ref') }}</th><td><span class="fw-semibold">{{ $dispute->provisional_credit_tnx }}</span></td></tr>
                            @endif
                            @if($dispute->clawback_tnx)
                                <tr><th>{{ __('Reversal / Clawback Ref') }}</th><td><span class="fw-semibold">{{ $dispute->clawback_tnx }}</span></td></tr>
                            @endif
                        </table>

                        <div class="p-3 bg-light rounded border mb-3">
                            <div class="small text-muted fw-bold mb-1">{{ __('MEMBER STATEMENT') }}</div>
                            <div>{{ $dispute->description }}</div>
                        </div>

                        @if($dispute->evidence_path)
                            <div class="p-3 border rounded mb-3">
                                <div class="small text-muted fw-bold mb-2">{{ __('UPLOADED SUPPORTING EVIDENCE') }}</div>
                                @if(\Illuminate\Support\Str::endsWith(strtolower($dispute->evidence_path), '.pdf'))
                                    <a href="{{ route('admin.disputes.attachment', $dispute->id) }}" target="_blank" class="site-btn-sm primary-btn">
                                        <i data-lucide="file-text"></i> {{ __('View Uploaded PDF') }}
                                    </a>
                                @else
                                    <a href="{{ route('admin.disputes.attachment', $dispute->id) }}" target="_blank">
                                        <img src="{{ route('admin.disputes.attachment', $dispute->id) }}" alt="Dispute Evidence" class="img-fluid rounded border" style="max-height: 280px;">
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if($dispute->resolution_notes)
                            <div class="p-3 rounded border" style="background: #f8fafc;">
                                <strong class="small text-uppercase text-muted d-block mb-1">{{ __('Latest Determination / Notes') }}</strong>
                                <div>{{ $dispute->resolution_notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                @if($dispute->isActive() && $canFinancialAct)
                    <div class="site-card mb-4">
                        <div class="site-card-header">
                            <h4 class="card-title mb-0">{{ __('Reg E Resolution Actions') }}</h4>
                        </div>
                        <div class="site-card-body">
                            @if($dispute->status === 'open')
                                <form action="{{ route('admin.disputes.provisional-credit', $dispute->id) }}" method="POST" class="mb-3 pb-3 border-bottom"
                                      onsubmit="return confirm('Issue a provisional credit of ${{ number_format((float) $dispute->disputed_amount, 2) }} to {{ $walletLabel }}?');">
                                    @csrf
                                    <label class="form-label small fw-bold">{{ __('Issue Temporary Provisional Credit') }}</label>
                                    <div class="input-group">
                                        <input type="text" name="resolution_notes" class="form-control" placeholder="Optional note (e.g., Reg E provisional credit pending review)">
                                        <button type="submit" class="btn btn-primary fw-semibold">{{ __('Issue Provisional Credit') }}</button>
                                    </div>
                                </form>
                            @endif

                            <form action="{{ route('admin.disputes.resolve-fraud', $dispute->id) }}" method="POST" class="mb-3 pb-3 border-bottom"
                                  onsubmit="return confirm('Confirm fraud/error in member favor? This finalizes the refund permanently.');">
                                @csrf
                                <label class="form-label small fw-bold">{{ __('Resolve In Member Favor (Fraud / Error Confirmed)') }}</label>
                                <div class="input-group">
                                    <input type="text" name="resolution_notes" class="form-control" placeholder="Final finding note (optional)">
                                    <button type="submit" class="btn btn-success fw-semibold">{{ __('Confirm Fraud / Uphold') }}</button>
                                </div>
                            </form>

                            <form action="{{ route('admin.disputes.deny', $dispute->id) }}" method="POST"
                                  onsubmit="return confirm('Deny this dispute? {{ $dispute->status === 'provisional_credited' ? 'WARNING: This will reverse/claw back the $' . number_format((float) $dispute->disputed_amount, 2) . ' provisional credit from the member wallet.' : '' }}');">
                                @csrf
                                <label class="form-label small fw-bold">
                                    {{ __('Deny Claim (Valid Charge)') }}
                                    @if($dispute->status === 'provisional_credited')
                                        — <span class="text-danger">{{ __('Auto-Reverses Provisional Credit') }}</span>
                                    @endif
                                </label>
                                <div class="input-group">
                                    <input type="text" name="resolution_notes" class="form-control" required minlength="5" placeholder="Required explanation for member determination letter">
                                    <button type="submit" class="btn btn-outline-danger fw-semibold">{{ __('Deny Claim') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-5 mb-4">
                <div class="site-card mb-4">
                    <div class="site-card-header">
                        <h4 class="card-title mb-0">{{ __('Add Investigation Note') }}</h4>
                    </div>
                    <div class="site-card-body">
                        <form action="{{ route('admin.disputes.note', $dispute->id) }}" method="POST">
                            @csrf
                            <textarea name="note" rows="3" class="form-control mb-2" required minlength="3" placeholder="Log merchant outreach, IP comparison, or callback notes..."></textarea>
                            <button type="submit" class="site-btn-sm primary-btn">{{ __('Save Timeline Note') }}</button>
                        </form>
                    </div>
                </div>

                <div class="site-card">
                    <div class="site-card-header">
                        <h4 class="card-title mb-0">{{ __('Reg E Audit Timeline') }}</h4>
                    </div>
                    <div class="site-card-body">
                        @forelse(array_reverse($dispute->timeline ?? []) as $event)
                            <div class="pb-3 mb-3 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <strong class="small">{{ $event['event'] ?? 'Update' }}</strong>
                                    <span class="text-muted small">{{ $event['at'] ?? '' }}</span>
                                </div>
                                <div class="text-primary small">{{ $event['actor'] ?? 'System' }}</div>
                                @if(!empty($event['note']))
                                    <div class="small text-muted mt-1">{{ $event['note'] }}</div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted small mb-0">{{ __('No events logged.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
