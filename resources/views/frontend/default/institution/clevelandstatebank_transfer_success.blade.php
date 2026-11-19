@extends('frontend::layouts.user')

@section('title')
    {{ __('Transfer Completed — Cleveland State Bank') }}
@endsection

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-lg-6 col-md-8 col-12">
        <!-- CSB Institutional Confirmation Receipt -->
        <div class="card border-0 shadow-lg text-center p-4 p-md-5" style="border-radius: 20px; background: #ffffff;">
            <!-- Top Badges -->
            <div class="d-flex align-items-center justify-content-between pb-3 mb-4 border-bottom">
                <span class="badge rounded-pill px-3 py-1 text-uppercase fw-bold" style="background: rgba(200, 35, 44, 0.1); color: #c8232c; font-size: 11px; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-building-columns me-1"></i> FDIC Insured
                </span>
                <span class="text-muted extra-small font-monospace" style="font-size: 11px;">ABA #084301768</span>
            </div>

            @php
                $isSelf = ($responseData['type'] ?? '') === 'self';
            @endphp
            <!-- Animated Success / Pending Icon -->
            <div class="mb-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 shadow-sm" style="width: 80px; height: 80px; background: {{ $isSelf ? 'rgba(16, 185, 129, 0.12)' : 'rgba(234, 179, 8, 0.15)' }}; color: {{ $isSelf ? '#10b981' : '#ca8a04' }}; font-size: 38px;">
                    <i class="fa-solid {{ $isSelf ? 'fa-check' : 'fa-clock' }}"></i>
                </div>
                <h3 class="fw-bold mb-1" style="color: #162846; letter-spacing: -0.5px;">{{ $isSelf ? 'Transfer Successful!' : 'Transfer Initialized' }}</h3>
                <p class="text-muted small mb-0">{{ $isSelf ? ($message ?? 'Your fund transfer request has been submitted and processed successfully.') : 'Your fund transfer request has been submitted and is currently pending.' }}</p>
            </div>

            <!-- Receipt Breakdown Table -->
            <div class="p-4 rounded-3 border text-start my-4" style="background: #f8fafc; font-size: 13.5px;">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Confirmation #</span>
                    <span class="fw-bold font-monospace text-dark" style="font-size: 14px;">{{ $responseData['tnx'] ?? ('TRX' . strtoupper(substr(md5(time()), 0, 10))) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Transfer Amount</span>
                    <span class="fw-bold text-dark" style="font-size: 16px;">$ {{ number_format((float)($responseData['amount'] ?? 0), 2) }}</span>
                </div>
                @if(!empty($responseData['account']))
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Beneficiary / Account</span>
                    <span class="fw-semibold text-dark">{{ $responseData['account'] }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Delivery Speed</span>
                    <span class="text-muted fw-semibold">{{ $isSelf ? 'Immediate Settlement' : '1–3 Business Days (Standard ACH)' }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Status</span>
                    @if($isSelf)
                        <span class="badge bg-success-subtle text-success fw-bold px-2 py-1">Completed</span>
                    @else
                        <span class="badge bg-warning text-dark fw-bold px-2 py-1">Pending</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2">
                    <span class="text-muted">Date &amp; Time</span>
                    <span class="text-muted small">{{ now()->format('M d, Y • h:i A') }}</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <a href="{{ route('user.dashboard') }}" class="btn text-white py-2.5 fw-bold shadow-sm" style="background: #162846; border-radius: 10px; font-size: 14px;">
                    <i class="fa-solid fa-arrow-left me-2"></i> Return to Dashboard
                </a>
                <a href="{{ route('user.fund_transfer.index') }}" class="btn btn-outline-secondary py-2 fw-semibold small" style="border-color: #cbd5e1; border-radius: 10px; font-size: 13px;">
                    Make Another Transfer
                </a>
            </div>

            <div class="mt-4 pt-3 border-top text-center text-muted extra-small" style="font-size: 11px;">
                Cleveland State Bank &bull; Official NACHA ACH Network &bull; Equal Housing Lender
            </div>
        </div>
    </div>
</div>
@endsection

