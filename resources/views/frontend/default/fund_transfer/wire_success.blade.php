@extends('frontend::layouts.user')

@section('title')
    {{ __('Wire Transfer Submitted') }}
@endsection

@section('content')
<div class="row justify-content-center">
    @include('frontend::fund_transfer.include.__header')

    <div class="col-xl-7 col-lg-9 col-md-11 col-12 mt-4">
        <div class="site-card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
            {{-- Top Banner --}}
            <div class="bg-primary bg-opacity-10 p-4 p-md-5 text-center border-bottom">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow-sm mb-3" style="width: 70px; height: 70px;">
                    <i class="fas fa-paper-plane fa-2x"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ __('Wire Transfer Submitted') }}</h3>
                <p class="text-muted small mb-3">{{ __('Your wire transfer order has been recorded and is currently pending final dispatch.') }}</p>
                <div class="d-inline-block">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill font-monospace fw-bold fs-6 shadow-xs">
                        <i class="fas fa-clock me-1"></i> {{ __('Status: Pending') }}
                    </span>
                </div>
            </div>

            {{-- Receipt Body --}}
            <div class="site-card-body p-4 p-md-5">
                <div class="p-4 rounded-3 border bg-light bg-opacity-50 mb-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase d-block">{{ __('Reference Number') }}</span>
                            <strong class="font-monospace text-dark fs-6">{{ $receipt['tnx'] ?? 'TRX' }}</strong>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small text-uppercase d-block">{{ __('Order Timestamp') }}</span>
                            <strong class="text-dark">{{ $receipt['created_at'] ?? now()->format('M d, Y h:i A') }}</strong>
                        </div>
                        
                        <div class="col-12"><hr class="my-1"></div>

                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase d-block">{{ __('Source Account') }}</span>
                            <span class="fw-bold text-dark">{{ $receipt['source_account'] ?? 'Checking' }}</span>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small text-uppercase d-block">{{ __('Wire Type') }}</span>
                            <span class="badge bg-secondary rounded-pill px-2 py-1">{{ $receipt['wire_type'] ?? 'Domestic Wire' }}</span>
                        </div>

                        <div class="col-12"><hr class="my-1"></div>

                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase d-block">{{ __('Beneficiary Name') }}</span>
                            <span class="fw-bold text-dark">{{ $receipt['beneficiary'] ?? '' }}</span>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small text-uppercase d-block">{{ __('Receiving Bank') }}</span>
                            <span class="fw-bold text-dark">{{ $receipt['bank'] ?? '' }}</span>
                        </div>

                        <div class="col-12"><hr class="my-1"></div>

                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase d-block">{{ __('Beneficiary Account') }}</span>
                            <span class="font-monospace fw-bold text-dark">{{ $receipt['account'] ?? '' }}</span>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small text-uppercase d-block">{{ __('Wire Principal') }}</span>
                            <span class="fw-bold text-dark">{{ $receipt['amount'] ?? '' }}</span>
                        </div>

                        <div class="col-12"><hr class="my-1"></div>

                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase d-block">{{ __('Wire Processing Fee') }}</span>
                            <span class="fw-bold text-primary">{{ $receipt['fee'] ?? '' }}</span>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small text-uppercase d-block">{{ __('Total Debited Amount') }}</span>
                            <span class="fw-bolder text-dark fs-5">{{ $receipt['total'] ?? '' }}</span>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info border-0 rounded-3 small d-flex align-items-start gap-2">
                    <i class="fas fa-info-circle fs-5 mt-1 text-primary"></i>
                    <div>
                        <strong>{{ __('What happens next?') }}</strong><br>
                        {{ __('Funds have been debited from your account balance. Your wire order will be validated against the Fedwire / SWIFT clearing networks. You will receive an email confirmation once processing is completed.') }}
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mt-4 pt-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-bold" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> {{ __('Print Receipt') }}
                    </button>
                    <a href="{{ route('user.fund_transfer.transfer.wire') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold">
                        <i class="fas fa-plus me-1"></i> {{ __('Send Another Wire') }}
                    </a>
                    <a href="{{ route('user.dashboard') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                        <i class="fas fa-home me-1"></i> {{ __('Go to Dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
