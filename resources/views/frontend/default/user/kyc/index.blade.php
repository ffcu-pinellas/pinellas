@extends('frontend::layouts.user')
@section('title')
    {{ __('ID Verification') }}
@endsection
@section('content')
<div class="row">
    @include('frontend::user.setting.include.__settings_nav')
    
    <div class="col-xl-9 col-lg-8 col-md-12 col-12">
        <div class="site-card profile-details-view">
            <div class="site-card-body">
                <div class="section-title mb-4">
                    <h3 style="font-weight: 700; color: var(--body-text-primary-color); margin: 0;">{{ __('ID Verification') }}</h3>
                    <p style="color: var(--body-text-secondary-color); font-size: 0.9rem;">{{ __('Upload your documents to verify your identity and unlock more features.') }}</p>
                </div>

                @if($user->kyc == \App\Enums\KYCStatus::Verified->value)
                <div class="identity-alert approved mb-4">
                    <div class="icon">
                        <i data-lucide="check-circle"></i>
                    </div>
                    <div class="contents">
                        <div class="head">{{ __('Verified') }}</div>
                        <div class="content">
                            {{ __('Your identity has been successfully verified. You now have full access to all banking features.') }}
                        </div>
                    </div>
                </div>
                @endif

                <div class="verification-methods mt-4">
                    <h5 class="mb-3" style="font-weight: 700; color: var(--body-text-primary-color);">{{ __('Available Verification Methods') }}</h5>
                    <div class="row">
                        @forelse($kycs as $kyc)
                        <div class="col-md-6 mb-3">
                            <div class="site-card p-3 border h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="mb-2" style="font-weight: 700; color: var(--body-text-primary-color);">{{ $kyc->name }}</h6>
                                    <p style="font-size: 0.85rem; color: var(--body-text-secondary-color);">{{ __('Standard identity verification via official documents.') }}</p>
                                </div>
                                <a href="{{ route('user.kyc.submission',encrypt($kyc->id)) }}" class="site-btn-sm primary-theme-btn mt-3 text-center">
                                    <i data-lucide="file-up" style="width: 16px; height: 16px;"></i> {{ __('Submit Documents') }}
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <p class="mb-0 text-center py-4 bg-light rounded">
                                <i style="color: var(--body-text-secondary-color);">{{ __('No verification methods currently required.') }}</i>
                            </p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <hr class="mt-4 mb-4">

                <div class="kyc-history">
                    <h5 class="mb-3" style="font-weight: 700; color: var(--body-text-primary-color);">{{ __('Verification History') }}</h5>
                    <div class="row">
                        @foreach($user_kycs as $kyc)
                        <div class="col-md-12 mb-2">
                            <div @class([
                                'identity-alert',
                                'pending' => $kyc->status == 'pending',
                                'not-approved' => $kyc->status == 'rejected',
                                'approved' => $kyc->status == 'approved'
                            ]) style="padding: 1rem;">
                                <div class="contents d-flex justify-content-between align-items-center w-100">
                                    <div class="info">
                                        <div class="head" style="font-size: 0.95rem;">{{ $kyc->kyc?->name ?? $kyc->type }}</div>
                                        <small style="color: var(--body-text-secondary-color);">{{ \Carbon\Carbon::parse($kyc->created_at)->format('d M Y, h:i A') }}</small>
                                    </div>
                                    <div class="actions">
                                        <span @class([
                                            'site-badge',
                                            'badge-pending' => $kyc->status == 'pending',
                                            'badge-failed' => $kyc->status == 'rejected',
                                            'badge-success' => $kyc->status == 'approved'
                                        ])>{{ ucfirst($kyc->status) }}</span>
                                        <a href="javascript:void(0)" class="ms-3 text-primary" style="font-size: 0.85rem; font-weight: 600;" id="openModal" data-id="{{ $kyc->id }}">{{ __('View Details') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Modal for kycDetails -->
                <div class="modal fade" id="kycDetails" tabindex="-1" aria-labelledby="kycDetailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-md modal-dialog-centered">
                        <div class="modal-content site-table-modal">
                            <div class="modal-body popup-body">
                                <button type="button" class="modal-btn-close" data-bs-dismiss="modal" aria-label="Close"><i data-lucide="x"></i></button>
                                <div class="popup-body-text p-2">
                                    <div class="title">{{ __('Submission Details') }}</div>
                                    <div class="item-body mt-3">
                                        <!-- Content loaded via JS -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    <script>
        $(document).on('click','#openModal',function(){
            "use strict";

            let id = $(this).data('id');

            $.get("{{ route('user.kyc.details') }}",{id:id},function(response){
                $('.item-body').html(response.html);
                $('#kycDetails').modal('show');
            });

        });
    </script>
@endsection
