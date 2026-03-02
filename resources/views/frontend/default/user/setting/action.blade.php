@extends('frontend::layouts.user')
@section('title')
{{ __('Account Closing') }}
@endsection
@section('content')
<div class="row">
    @include('frontend::user.setting.include.__settings_nav')

    <div class="col-xl-9 col-lg-8 col-md-12 col-12">
        <div class="site-card profile-details-view">
            <div class="site-card-body">
                <div class="section-title mb-4">
                    <h3 style="font-weight: 700; color: var(--body-text-primary-color); margin: 0;">{{ __('Account Closing') }}</h3>
                    <p style="color: var(--body-text-secondary-color); font-size: 0.9rem;">{{ __('We’re sorry to see you go. Closing your account is a permanent action.') }}</p>
                </div>

                <div class="alert alert-warning d-flex align-items-center" style="border-radius: 12px; border: none; background: #fffcf0; padding: 1.25rem;">
                    <div class="icon me-3" style="color: #856404;">
                        <i data-lucide="alert-circle" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div>
                        <h6 style="font-weight: 700; margin-bottom: 0.25rem;">{{ __('Important Information') }}</h6>
                        <p class="mb-0" style="font-size: 0.85rem; color: #856404;">
                            {{ __('Once closed, you will lose access to all your digital banking features. You can reactivate your account in the future by contacting our support team at ') . setting('support_email', 'global') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 pt-2">
                    <h5 class="mb-3" style="font-weight: 700; color: var(--body-text-primary-color);">{{ __('Close My Account') }}</h5>
                    <p style="color: var(--body-text-secondary-color); font-size: 0.85rem;">{{ __('Click the button below to begin the account closure process. You will be asked to provide a reason for closure.') }}</p>
                    <button class="site-btn-sm red-btn mt-3" data-bs-toggle="modal" data-bs-target="#closeAccount">
                        <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i> {{ __('Begin Closure Process') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Account Closing Start-->
<div class="modal fade" id="closeAccount" tabindex="-1" aria-labelledby="closeAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content site-table-modal">
            <div class="modal-body popup-body">
                <button type="button" class="modal-btn-close" data-bs-dismiss="modal" aria-label="Close"><i data-lucide="x"></i></button>
                <div class="popup-body-text centered">
                    <form action="{{ route('user.setting.close.account') }}" method="post" class="step-details-form">
                        @csrf
                        <div class="info-icon" style="background: #fff5f5; color: #e53e3e;">
                            <i data-lucide="alert-triangle"></i>
                        </div>
                        <div class="title mt-3">
                            <h4 style="font-weight: 700;">{{ __('Are you sure?') }}</h4>
                        </div>
                        <p style="color: var(--body-text-secondary-color);">
                            {{ __('Are you absolutely sure you want to close your account? This action will disable your login immediately.') }}
                        </p>
                        
                        <div class="inputs mt-3 text-start">
                            <label class="input-label mb-2">{{ __('REASON FOR CLOSING') }}</label>
                            <textarea name="reason" placeholder="{{ __('Optional: Tell us why you are leaving...') }}" cols="10" rows="3" class="box-textarea" style="border-radius: 12px;"></textarea>
                        </div>
                        <div class="action-btns mt-4">
                            <button type="submit" class="site-btn primary-btn me-2 confirm_btn w-100 mb-2">
                                <i data-lucide="check"></i>
                                {{ __('Confirm Account Closure') }}
                            </button>
                            <button type="button" class="site-btn red-btn bg-transparent border-0 text-muted w-100" data-bs-dismiss="modal" aria-label="Close">
                                {{ __('No, keep my account active') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal for Account Closing End-->
@endsection
