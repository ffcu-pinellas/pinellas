@extends('frontend::layouts.user')
@section('title')
    {{ __('Transfer Success') }}
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-12">
            <div class="site-card text-center py-5 px-4 shadow-sm">
                <div class="mb-5 px-md-4">
                    <div class="d-flex justify-content-between position-relative mb-4 mt-2">
                        <div class="progress position-absolute start-0 top-50 translate-middle-y w-100" style="height: 4px; z-index: 0;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        
                        <div class="step-item text-center" style="z-index: 1;">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mb-2 mx-auto shadow-sm" style="width: 32px; height: 32px;">
                                <i class="fas fa-check small"></i>
                            </div>
                            <span class="small fw-bold text-success">{{ __('Submitted') }}</span>
                        </div>
                        
                        <div class="step-item text-center" style="z-index: 1;">
                            <div class="bg-white text-primary border border-primary rounded-circle d-flex align-items-center justify-content-center mb-2 mx-auto shadow-sm" style="width: 32px; height: 32px;">
                                <i class="fas fa-search small"></i>
                            </div>
                            <span class="small fw-bold text-primary">{{ __('Reviewing') }}</span>
                        </div>
                        
                        <div class="step-item text-center" style="z-index: 1;">
                            <div class="bg-white text-muted border border-2 rounded-circle d-flex align-items-center justify-content-center mb-2 mx-auto" style="width: 32px; height: 32px;">
                                <i class="fas fa-cog small"></i>
                            </div>
                            <span class="small text-muted">{{ __('Processing') }}</span>
                        </div>
                        
                        <div class="step-item text-center" style="z-index: 1;">
                            <div class="bg-white text-muted border border-2 rounded-circle d-flex align-items-center justify-content-center mb-2 mx-auto" style="width: 32px; height: 32px;">
                                <i class="fas fa-paper-plane small"></i>
                            </div>
                            <span class="small text-muted">{{ __('Sent') }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle mb-3 shadow" style="width: 80px; height: 80px;">
                            <i class="fas fa-check fs-1"></i>
                        </div>
                        <h2 class="fw-bold text-dark mb-1">{{ __('Transfer Initialized') }}</h2>
                        <p class="text-muted small">{{ __('Your transfer has been submitted for processing. You will be notified once it is completed. Contact support for more information') }}</p>
                    </div>
                </div>

                <div class="bg-light rounded-3 p-4 mb-4 text-start">
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted">{{ __('Amount') }}</span>
                        <span class="fw-bold text-dark">{{ setting('currency_symbol','global').number_format($responseData['amount'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted">{{ __('To Account') }}</span>
                        <span class="fw-bold text-dark">{{ $responseData['account'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-0">
                        <span class="text-muted">{{ __('Transaction ID') }}</span>
                        <span class="small fw-bold text-primary">{{ $responseData['tnx'] }}</span>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('user.dashboard') }}" class="btn btn-primary rounded-pill py-2 fw-bold">
                        {{ __('Go to Dashboard') }}
                    </a>
                    <a href="{{ route('user.fund_transfer.index') }}" class="btn btn-outline-primary rounded-pill py-2 fw-bold">
                        {{ __('Make Another Transfer') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
