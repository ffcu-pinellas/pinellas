@extends('frontend::layouts.user')

@section('title')
    {{ __('Transaction Details') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 col-md-10 col-12">
        <div class="site-card border-0 shadow-lg overflow-hidden" style="border-radius: 12px; background: #fff;">
            <!-- Co-Branded Header (Official Zelle Purple) -->
            <div style="background: linear-gradient(135deg, #6d1ed4 0%, #4B1045 100%); padding: 25px 24px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: space-between;">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('assets/external/images/pinellas_logo_white_1774915533306.png') }}" alt="Pinellas FCU" style="height: 32px;">
                    <div style="width: 1px; height: 24px; background-color: rgba(255,255,255,0.3);"></div>
                    <img src="{{ asset('assets/external/images/zelle logo2025.png') }}" alt="Zelle" style="height: 22px; filter: brightness(0) invert(1);">
                </div>
                <a href="{{ route('user.dashboard') }}" class="text-white text-decoration-none fw-bold small" style="opacity: 0.8;">{{ __('Close') }}</a>
            </div>
            
            <div class="p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold text-dark m-0">{{ __('Details') }}</h2>
                    <a href="javascript:void(0)" onclick="window.print()" class="text-primary text-decoration-none fw-bold small">
                        <i class="fas fa-print me-1"></i> {{ __('Print or Save') }}
                    </a>
                </div>

                <div class="p-4 rounded-3 mb-5" style="background-color: #f8f9fa; border-left: 5px solid #0d6efd;">
                    <p class="text-dark mb-0" style="line-height: 1.6; font-size: 1.05rem;">
                        {{ __('Your payment has been submitted and, for your protection, is undergoing a security review which could take up to 24 hours. No action is needed on your part. We\'ll text or call if we need more information.') }}
                    </p>
                </div>

                <!-- Transaction Data Table -->
                <div class="transaction-details-list">
                    <div class="detail-item py-3 d-flex justify-content-between align-items-start border-bottom">
                        <span class="text-muted">{{ __('Status') }}</span>
                        <div class="text-end">
                            <span class="fw-bold text-dark d-block" style="font-size: 1.1rem;">{{ __('Hold') }}</span>
                            <span class="text-muted small">{{ __('Zelle® payment') }}</span>
                        </div>
                    </div>

                    <div class="detail-item py-3 d-flex justify-content-between align-items-start border-bottom">
                        <span class="text-muted">{{ __('Recipient') }}</span>
                        <div class="text-end">
                            <span class="fw-bold text-dark d-block" style="font-size: 1.05rem;">{{ $responseData['account'] }}</span>
                        </div>
                    </div>
                    
                    <div class="detail-item py-3 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted">{{ __('From') }}</span>
                        <div class="text-end">
                            <span class="fw-bold text-dark d-block" style="font-size: 1.05rem;">{{ $responseData['from_account'] }}</span>
                            <span class="text-muted small">{{ strtoupper(auth()->user()->full_name) }}</span>
                        </div>
                    </div>

                    <div class="detail-item py-3 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted">{{ __('Amount') }}</span>
                        <span class="fw-bold text-dark text-end" style="font-size: 1.1rem;">{{ setting('currency_symbol', 'global') }}{{ number_format($responseData['amount'], 2) }}</span>
                    </div>

                    <div class="detail-item py-3 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted">{{ __('Date') }}</span>
                        <span class="fw-bold text-dark text-end">{{ $responseData['date'] }}</span>
                    </div>

                    @if(!empty($responseData['memo']))
                    <div class="detail-item py-3 d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted">{{ __('Memo') }}</span>
                        <span class="fw-bold text-dark text-end">{{ $responseData['memo'] }}</span>
                    </div>
                    @endif

                    <div class="detail-item py-3 d-flex justify-content-between align-items-center">
                        <span class="text-muted">{{ __('Confirmation #') }}</span>
                        <span class="fw-bold text-dark text-end font-monospace">{{ $responseData['tnx'] }}</span>
                    </div>
                </div>

                <div class="mt-5 text-center">
                    <a href="{{ route('user.fund_transfer.index') }}" class="btn px-5 py-3 fw-bold rounded-pill shadow-sm" style="background-color: #741B6B; color: white;">
                        {{ __('Send More Money with Zelle') }}
                    </a>
                </div>
                
                <div class="mt-5 pt-4 text-center">
                    <p class="small text-muted mb-0">
                        {{ __('Zelle and the Zelle related marks are wholly owned by Early Warning Services, LLC.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .detail-item:last-child {
        border-bottom: none;
    }
    .transaction-details-list {
        margin-top: 2rem;
    }
    @media print {
        .btn, .close-link, .sidebar, .header-main { display: none !important; }
        .site-card { shadow: none !important; border: 1px solid #ddd !important; }
    }
</style>
@endsection
