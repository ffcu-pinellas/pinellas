@extends('frontend::layouts.user')

@section('title')
    {{ __('Payment Sent') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8 col-12">
        <div class="site-card border-0 shadow-lg overflow-hidden" style="border-radius: 20px;">
            <!-- Zelle Branded Success Header -->
            <div style="background: linear-gradient(135deg, #741B6B 0%, #4B1045 100%); padding: 40px 20px; text-align: center; position: relative;">
                <div style="position: absolute; top: 15px; left: 20px;">
                    <img src="{{ asset('assets/external/images/zelle logo2025.png') }}" alt="Zelle" style="height: 25px; filter: brightness(0) invert(1); opacity: 0.9;">
                </div>
                
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white text-success rounded-circle" style="width: 70px; height: 70px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                        <i class="fas fa-check fs-2"></i>
                    </div>
                </div>
                <h2 class="fw-bold text-white mb-1" style="letter-spacing: -0.5px;">{{ __('Money\'s on its way!') }}</h2>
                <p class="text-white-50 mb-0">{{ __('Your Zelle® payment is being processed.') }}</p>
            </div>

            <div class="site-card-body p-4 p-md-5 text-center">
                <div class="mb-4">
                    <div class="display-5 fw-bold text-dark mb-1">
                        {{ setting('currency_symbol', 'global') }}{{ number_format($responseData['amount'], 2) }}
                    </div>
                    <div class="text-muted small uppercase fw-bold" style="letter-spacing: 1px;">{{ __('TOTAL AMOUNT SENT') }}</div>
                </div>

                <div class="bg-light rounded-4 p-4 mb-4 text-start border border-light">
                    <div class="d-flex justify-content-between mb-3 align-items-start">
                        <span class="text-muted small">{{ __('Recipient') }}</span>
                        <span class="fw-bold text-dark text-end" style="max-width: 60%;">{{ $responseData['account'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 align-items-center">
                        <span class="text-muted small">{{ __('Status') }}</span>
                        <span class="badge bg-warning-soft text-warning rounded-pill px-3">{{ __('Pending') }}</span>
                    </div>
                    @if(!empty($responseData['memo']))
                    <div class="d-flex justify-content-between mb-3 align-items-center">
                        <span class="text-muted small">{{ __('Memo') }}</span>
                        <span class="fw-bold text-dark text-end" style="max-width: 60%;">{{ $responseData['memo'] }}</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between mb-0 pt-3 border-top border-2 border-white">
                        <span class="text-muted small">{{ __('Transaction ID') }}</span>
                        <span class="small fw-bold text-primary font-monospace">{{ $responseData['tnx'] }}</span>
                    </div>
                </div>

                <div class="d-grid gap-3">
                    <a href="{{ route('user.dashboard') }}" class="btn py-3 fw-bold rounded-pill shadow-sm" style="background-color: #741B6B; color: white;">
                        {{ __('Go to Dashboard') }}
                    </a>
                    <a href="{{ route('user.fund_transfer.index') }}" class="btn btn-link text-decoration-none fw-bold" style="color: #741B6B;">
                        {{ __('Send More Money with Zelle') }}
                    </a>
                </div>
                
                <div class="mt-5 pt-4 border-top">
                    <p class="small text-muted mb-0">
                        {{ __('Zelle and the Zelle related marks are wholly owned by Early Warning Services, LLC.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-warning-soft {
        background-color: rgba(255, 193, 7, 0.1);
    }
    .text-warning {
        color: #ff9800 !important;
    }
    .rounded-4 {
        border-radius: 1rem !important;
    }
</style>
@endsection
