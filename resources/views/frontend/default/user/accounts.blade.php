@extends('frontend::layouts.user')

@section('title')
    {{ __('Account Settings') }}
@endsection

@section('content')
    <div class="row">
        @include('frontend::user.setting.include.__settings_nav')
        <div class="col-xl-9 col-lg-8 col-md-12 col-12">
            <div class="site-card profile-details-view">
                <div class="site-card-body">
                    <div class="section-title mb-4">
                        <h3 style="font-weight: 700; color: var(--body-text-primary-color); margin: 0;">{{ __('Account Settings') }}</h3>
                        <p style="color: var(--body-text-secondary-color); font-size: 0.9rem;">{{ __('Manage your checking and savings accounts display preferences.') }}</p>
                    </div>

                    <div class="accounts-settings-list mt-4">
                        <!-- Checking Account -->
                        <div class="banno-toggle">
                            <div class="banno-toggle-info">
                                <h6 class="mb-1">{{ __('Total Checking') }} <span class="badge bg-light text-dark ms-2 fw-normal" style="font-size: 0.7rem;">****{{ substr(auth()->user()->account_number, -4) }}</span></h6>
                                <p>{{ __('Current Balance: ') }}{{ setting('site_currency', 'global') }}{{ number_format($checkingBalance, 2) }}</p>
                            </div>
                            <div class="settings-actions">
                                <div class="banno-switch active" onclick="$(this).toggleClass('active')"></div>
                            </div>
                        </div>

                        <!-- Savings Accounts -->
                        @foreach($savingsAccounts as $account)
                        <div class="banno-toggle">
                            <div class="banno-toggle-info">
                                <h6 class="mb-1">{{ $account->type }} <span class="badge bg-light text-dark ms-2 fw-normal" style="font-size: 0.7rem;">****{{ substr($account->account_number, -4) }}</span></h6>
                                <p>{{ __('Current Balance: ') }}{{ setting('site_currency', 'global') }}{{ number_format($account->balance, 2) }} • {{ number_format($account->interest_rate, 2) }}% APY</p>
                            </div>
                            <div class="settings-actions">
                                <div class="banno-switch active" onclick="$(this).toggleClass('active')"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <hr class="mt-4 mb-4">

                    <div class="settings-footer-actions">
                         <h5 class="mb-3" style="font-weight: 700; color: var(--body-text-primary-color);">{{ __('New Account') }}</h5>
                         <p style="color: var(--body-text-secondary-color); font-size: 0.85rem;" class="mb-3">{{ __('Interested in opening another savings account? Our team reflects on your profile to offer the best rates.') }}</p>
                         <button type="button" class="site-btn-sm primary-theme-btn">
                             <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> {{ __('Open a new account') }}
                         </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
