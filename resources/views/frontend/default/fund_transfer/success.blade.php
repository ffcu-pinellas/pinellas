@extends('frontend::layouts.user')

@section('title')
    {{ __('Proof of Transfer Receipt') }}
@endsection

@section('content')
@php
    $logoUrl = asset('assets/external/images/frontfield_logo_white_1774915533306.png');
    $siteTitle = setting('site_title', 'global') ?? 'FrontField Credit Union';
    $headerGradient = 'linear-gradient(135deg, #00549b 0%, #002d5a 100%)';
    $currencySym = setting('currency_symbol', 'global') ?? '$';
    
    $isCompleted = ($responseData['status_label'] ?? '') === __('Completed') || ($responseData['type'] ?? '') === 'self';
    $statusText = $isCompleted ? __('COMPLETED') : __('PENDING CLEARANCE');
    $statusBadgeClass = $isCompleted ? 'bg-success text-white' : 'bg-warning text-dark';
    
    $transferTypeLabel = match($responseData['type'] ?? '') {
        'self' => __('Intra-Account Transfer (Self)'),
        'member' => __('Member-to-Member Transfer'),
        'external' => __('External ACH Bank Transfer'),
        default => __('Electronic Fund Transfer')
    };

    $amountFormatted = is_numeric($responseData['amount'] ?? null) 
        ? $currencySym . number_format($responseData['amount'], 2) 
        : ($responseData['amount'] ?? '$0.00');

    $feeFormatted = isset($responseData['charge']) && is_numeric($responseData['charge']) && $responseData['charge'] > 0
        ? $currencySym . number_format($responseData['charge'], 2)
        : '$0.00';

    $totalAmount = ($responseData['final_amount'] ?? null) ?? ($responseData['total'] ?? null) ?? ($responseData['amount'] ?? 0);
    $totalFormatted = is_numeric($totalAmount) ? $currencySym . number_format($totalAmount, 2) : $amountFormatted;
    $tnxId = $responseData['tnx'] ?? 'TRX-' . strtoupper(Str::random(10));
    $timestamp = $responseData['created_at'] ?? now()->format('M d, Y h:i A T');
@endphp

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10 col-12 mt-2">
        <div class="site-card border-0 shadow-lg overflow-hidden mb-5 receipt-print-container" style="border-radius: 12px; background: #ffffff;">
            
            {{-- Official Financial Voucher Header --}}
            <div class="receipt-header" style="background: {{ $headerGradient }}; padding: 18px 24px; border-bottom: 1px solid rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: space-between;">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $logoUrl }}" alt="{{ $siteTitle }}" style="height: 28px;" onerror="this.style.display='none'">
                    <div style="width: 1px; height: 20px; background-color: rgba(255,255,255,0.25);"></div>
                    <div class="d-flex align-items-center gap-1 text-white">
                        <i class="fas fa-shield-alt text-warning small"></i>
                        <span class="fw-bold extra-small text-uppercase tracking-wide" style="letter-spacing: 0.5px;">{{ __('Federally Insured by NCUA') }}</span>
                    </div>
                </div>
                <a href="{{ route('user.dashboard') }}" class="text-white text-decoration-none fw-semibold small close-link" style="opacity: 0.85;">
                    <i class="fas fa-times me-1"></i> {{ __('Close') }}
                </a>
            </div>

            <div class="p-4 p-md-4 receipt-body">
                {{-- Action Bar --}}
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <span class="badge {{ $statusBadgeClass }} px-3 py-1 rounded-pill extra-small fw-bold font-monospace mb-1">
                            <i class="fas {{ $isCompleted ? 'fa-check-circle' : 'fa-clock' }} me-1"></i> {{ $statusText }}
                        </span>
                        <h4 class="fw-bold text-dark m-0">{{ __('Electronic Transfer Settlement Voucher') }}</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" onclick="window.print();" class="btn btn-outline-primary rounded-pill btn-sm px-3 py-1 fw-semibold shadow-xs print-action-btn">
                            <i class="fas fa-print me-1"></i> {{ __('Print / Save PDF') }}
                        </button>
                    </div>
                </div>

                {{-- Status Advisory Box --}}
                <div class="p-3 rounded-2 mb-3 settlement-advisory-box" style="background-color: #f8fafc; border-left: 4px solid #00549b; border-right: 1px solid #e2e8f0; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-info-circle text-primary mt-1 small"></i>
                        <div>
                            @if($isCompleted)
                                <strong class="text-dark extra-small d-block mb-1">{{ __('Transfer Settled & Completed') }}</strong>
                                <p class="text-muted mb-0 extra-small" style="line-height: 1.5;">
                                    {{ __('Your intra-account transfer has been successfully processed and verified. Available balances have been adjusted and credited in real-time across your FrontField Credit Union accounts.') }}
                                </p>
                            @else
                                <strong class="text-dark extra-small d-block mb-1">{{ __('Order Acknowledged & In Transit') }}</strong>
                                <p class="text-muted mb-0 extra-small" style="line-height: 1.5;">
                                    {{ __('Your transfer request has been recorded and submitted for automated clearing. Funds have been debited from your primary balance. Please retain this receipt as proof of transaction.') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Transaction Data Table --}}
                <div class="transaction-details-list">
                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Settlement Status') }}</span>
                        <div class="text-end">
                            <span class="badge {{ $statusBadgeClass }} px-2 py-1 rounded-pill fw-bold extra-small">{{ $responseData['status_label'] ?? $statusText }}</span>
                        </div>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Confirmation / Tracking #') }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark font-monospace text-end small" id="receiptTnxText">{{ $tnxId }}</span>
                            <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none copy-tnx-btn" onclick="copyReceiptTnx('{{ $tnxId }}')" title="{{ __('Copy Reference') }}">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Order Date & Time') }}</span>
                        <span class="fw-bold text-dark text-end extra-small">{{ $timestamp }}</span>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Transfer Classification') }}</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-pill fw-bold extra-small">{{ $transferTypeLabel }}</span>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Funding Source') }}</span>
                        <div class="text-end">
                            <span class="fw-bold text-dark d-block small">{{ $responseData['source_account'] ?? 'Primary Checking' }}</span>
                            <span class="text-muted extra-small">{{ strtoupper(auth()->user()->full_name) }}</span>
                        </div>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-start border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Recipient / Destination') }}</span>
                        <div class="text-end">
                            <span class="fw-bold text-dark d-block small">{{ $responseData['recipient_name'] ?? ($responseData['account'] ?? 'N/A') }}</span>
                            @if(!empty($responseData['account']))
                                <span class="text-muted font-monospace extra-small">{{ __('Account') }}: {{ $responseData['account'] }}</span>
                            @endif
                        </div>
                    </div>

                    @if(!empty($responseData['memo']))
                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Transfer Memo / Note') }}</span>
                        <span class="text-dark text-end extra-small">{{ $responseData['memo'] }}</span>
                    </div>
                    @endif

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Principal Transfer Amount') }}</span>
                        <span class="fw-bold text-dark text-end small">{{ $amountFormatted }}</span>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Transfer Settlement Fee') }}</span>
                        <span class="fw-bold text-primary text-end extra-small">{{ $feeFormatted }}</span>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center bg-light px-3 rounded-2 mt-2">
                        <span class="text-dark fw-bold text-uppercase extra-small">{{ __('Total Debited Amount') }}</span>
                        <span class="fw-bolder text-dark text-end fs-6">{{ $totalFormatted }}</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mt-4 pt-3 border-top receipt-actions">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> {{ __('Print Receipt') }}
                    </button>
                    <a href="{{ route('user.fund_transfer.index') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold btn-sm">
                        <i class="fas fa-plus me-1"></i> {{ __('Make Another Transfer') }}
                    </a>
                    <a href="{{ route('user.dashboard') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold btn-sm shadow-xs">
                        <i class="fas fa-th-large me-1"></i> {{ __('Return to Dashboard') }}
                    </a>
                </div>

                <div class="mt-3 pt-2 text-center border-top receipt-footer-disclaimer">
                    <p class="extra-small text-muted mb-0" style="font-size: 0.72rem; line-height: 1.4;">
                        {{ __('Federally insured by NCUA. FrontField Credit Union routes member and automated clearing house (ACH) transfers under Regulation E guidelines. Funds transfers are subject to fraud screening and network clearing timeframes. Equal Housing Lender.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyReceiptTnx(tnx) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(tnx).then(function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '{{ __("Reference copied to clipboard") }}',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        });
    }
}
</script>

<style>
    .extra-small {
        font-size: 0.76rem;
    }
    .shadow-xs {
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    @media print {
        @page {
            size: letter portrait;
            margin: 8mm 10mm;
        }
        html, body { 
            background: #fff !important; 
            padding: 0 !important;
            margin: 0 !important;
            height: auto !important;
            font-size: 12px !important;
        }
        nav, .sidebar, .header-main, .footer, .btn, .close-link, button, a[href="{{ route('user.dashboard') }}"], .receipt-actions, .print-action-btn, .copy-tnx-btn { 
            display: none !important; 
        }
        .row { margin: 0 !important; width: 100% !important; }
        .col-lg-8, .col-md-10, .col-12 { width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; padding: 0 !important; margin: 0 !important; }
        .site-card { 
            border: 1px solid #ccc !important; 
            box-shadow: none !important; 
            margin: 0 auto !important;
            width: 100% !important;
            max-width: 100% !important;
            border-radius: 0 !important;
            page-break-inside: avoid !important;
            page-break-after: avoid !important;
        }
        .receipt-body {
            padding: 14px 20px !important;
        }
        .receipt-header {
            padding: 12px 20px !important;
            background: #00549b !important;
            color: #fff !important;
        }
        .settlement-advisory-box {
            padding: 8px 12px !important;
            margin-bottom: 8px !important;
        }
        .detail-item {
            padding-top: 3px !important;
            padding-bottom: 3px !important;
        }
        .receipt-footer-disclaimer {
            margin-top: 10px !important;
            padding-top: 8px !important;
        }
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
@endsection
