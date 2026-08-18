@extends('frontend::layouts.user')

@section('title')
    {{ __('Wire Transfer Settlement Details') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10 col-12 mt-2">
        <div class="site-card border-0 shadow-lg overflow-hidden mb-5 receipt-print-container" style="border-radius: 12px; background: #ffffff;">
            
            {{-- Official Financial Wire Header --}}
            <div class="receipt-header" style="background: linear-gradient(135deg, #00549b 0%, #002d5a 100%); padding: 18px 24px; border-bottom: 1px solid rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: space-between;">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('assets/external/images/frontfield_logo_white_1774915533306.png') }}" alt="{{ setting('site_title', 'global') ?? 'FrontField Credit Union' }}" style="height: 28px;">
                    <div style="width: 1px; height: 20px; background-color: rgba(255,255,255,0.25);"></div>
                    <div class="d-flex align-items-center gap-1 text-white">
                        <i class="fas fa-shield-alt text-warning small"></i>
                        <span class="fw-bold extra-small text-uppercase tracking-wide" style="letter-spacing: 0.5px;">{{ __('Fedwire / SWIFT Settlement') }}</span>
                    </div>
                </div>
                <a href="{{ route('user.dashboard') }}" class="text-white text-decoration-none fw-semibold small close-link" style="opacity: 0.85;">
                    <i class="fas fa-times me-1"></i> {{ __('Close') }}
                </a>
            </div>

            <div class="p-4 p-md-4 receipt-body">
                {{-- Action Bar --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="badge bg-warning bg-opacity-25 text-dark px-3 py-1 rounded-pill extra-small fw-bold font-monospace mb-1">
                            <i class="fas fa-clock text-warning me-1"></i> {{ __('SETTLEMENT PENDING') }}
                        </span>
                        <h4 class="fw-bold text-dark m-0">{{ __('Wire Transfer Settlement Advisory') }}</h4>
                    </div>
                    <button type="button" onclick="window.print();" class="btn btn-outline-primary rounded-pill btn-sm px-3 py-1 fw-semibold shadow-xs print-action-btn">
                        <i class="fas fa-file-pdf me-1"></i> {{ __('Print / Save PDF') }}
                    </button>
                </div>

                {{-- Financial Settlement Hold Advisory --}}
                <div class="p-3 rounded-2 mb-3 settlement-advisory-box" style="background-color: #f8fafc; border-left: 4px solid #00549b; border-right: 1px solid #e2e8f0; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-info-circle text-primary mt-1 small"></i>
                        <div>
                            <strong class="text-dark extra-small d-block mb-1">{{ __('Order Acknowledged & Funds Reserved') }}</strong>
                            <p class="text-muted mb-0 extra-small" style="line-height: 1.5;">
                                {{ __('Your wire transfer instruction has been accepted and assigned a settlement tracking number. Funds have been debited from your funding account and placed in a settlement clearing hold pending final dispatch across the Federal Reserve Fedwire or global SWIFT network.') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Transaction Data Table --}}
                <div class="transaction-details-list">
                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Settlement Status') }}</span>
                        <div class="text-end">
                            <span class="badge bg-warning text-dark px-2 py-1 rounded-pill fw-bold extra-small">{{ __('Pending Clearance') }}</span>
                        </div>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Confirmation / Tracking #') }}</span>
                        <span class="fw-bold text-dark font-monospace text-end small">{{ $receipt['tnx'] ?? 'TRX' }}</span>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Order Date & Time') }}</span>
                        <span class="fw-bold text-dark text-end extra-small">{{ $receipt['created_at'] ?? now()->format('M d, Y h:i A T') }}</span>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Wire Classification') }}</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-pill fw-bold extra-small">{{ $receipt['wire_type'] ?? 'Domestic Wire (Fedwire)' }}</span>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Funding Account') }}</span>
                        <div class="text-end">
                            <span class="fw-bold text-dark d-block small">{{ $receipt['source_account'] ?? 'Checking Account' }}</span>
                            <span class="text-muted extra-small">{{ strtoupper(auth()->user()->full_name) }}</span>
                        </div>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-start border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Beneficiary Entity') }}</span>
                        <div class="text-end">
                            <span class="fw-bold text-dark d-block small">{{ $receipt['beneficiary'] ?? '' }}</span>
                            @if(!empty($receipt['beneficiary_address']))
                                <span class="text-muted extra-small">{{ $receipt['beneficiary_address'] }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-start border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Receiving Institution') }}</span>
                        <div class="text-end">
                            <span class="fw-bold text-dark d-block small">{{ $receipt['bank'] ?? '' }}</span>
                            @if(!empty($receipt['routing']))
                                <span class="text-muted extra-small">ABA / Fedwire: <strong>{{ $receipt['routing'] }}</strong></span>
                            @elseif(!empty($receipt['swift']))
                                <span class="text-muted extra-small">SWIFT/BIC: <strong>{{ $receipt['swift'] }}</strong></span>
                            @endif
                        </div>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Beneficiary Account / IBAN') }}</span>
                        <span class="font-monospace fw-bold text-dark text-end small">{{ $receipt['account'] ?? '' }}</span>
                    </div>

                    @if(!empty($receipt['memo']))
                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Payment Reference / Memo') }}</span>
                        <span class="text-dark text-end extra-small">{{ $receipt['memo'] }}</span>
                    </div>
                    @endif

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Wire Principal Amount') }}</span>
                        <span class="fw-bold text-dark text-end small">{{ $receipt['amount'] ?? '$0.00' }}</span>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted extra-small text-uppercase fw-semibold">{{ __('Wire Settlement Fee') }}</span>
                        <span class="fw-bold text-primary text-end extra-small">{{ $receipt['fee'] ?? '$0.00' }}</span>
                    </div>

                    <div class="detail-item py-2 d-flex justify-content-between align-items-center bg-light px-3 rounded-2 mt-2">
                        <span class="text-dark fw-bold text-uppercase extra-small">{{ __('Total Funds Debited') }}</span>
                        <span class="fw-bolder text-dark text-end fs-6">{{ $receipt['total'] ?? '$0.00' }}</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mt-3 pt-3 border-top receipt-actions">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> {{ __('Print Wire Advisory') }}
                    </button>
                    <a href="{{ route('user.fund_transfer.transfer.wire') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold btn-sm">
                        <i class="fas fa-plus me-1"></i> {{ __('Send Another Wire') }}
                    </a>
                    <a href="{{ route('user.dashboard') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold btn-sm shadow-xs">
                        <i class="fas fa-th-large me-1"></i> {{ __('Account Dashboard') }}
                    </a>
                </div>

                <div class="mt-3 pt-2 text-center border-top receipt-footer-disclaimer">
                    <p class="extra-small text-muted mb-0" style="font-size: 0.72rem; line-height: 1.4;">
                        {{ __('Federally insured by NCUA. FrontField Credit Union routes wire transfers via the Federal Reserve Fedwire® Funds Service and SWIFT messaging networks. All wire transfers are subject to federal regulatory review and clearing timeframes.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

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
        nav, .sidebar, .header-main, .footer, .btn, .close-link, button, a[href="{{ route('user.dashboard') }}"], .receipt-actions, .print-action-btn { 
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
