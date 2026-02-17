@extends('frontend::layouts.user')
@section('title')
    {{ __('Transfer Success') }}
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-12">
            <div class="site-card text-center py-5 px-4 shadow-sm">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-check fs-1"></i>
                    </div>
                    <h2 class="fw-bold text-dark">{{ __('Success!') }}</h2>
                    <p class="text-muted">{{ $message }}</p>
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
