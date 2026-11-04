<div class="site-card mb-0">
    <div class="site-card-header">
        <h3 class="title-small">{{ __('Account Informations') }}</h3>
    </div>
    <div class="site-card-body">
        <div class="row">
            <form action="{{route('admin.user.status-update',$user->id)}}" method="post">
                @csrf

                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('Account Status') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="accSta1"
                                name="status"
                                value="1"
                                @if($user->status) checked @endif
                            />
                            <label for="accSta1">{{ __('Active') }}</label>
                            <input
                                type="radio"
                                id="accSta2"
                                name="status"
                                value="0"
                                @if(!$user->status) checked @endif
                            />
                            <label for="accSta2">{{ __('Disabled') }}</label>
                            
                            @if(auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'))
                            <input
                                type="radio"
                                id="accStaClosed"
                                name="status"
                                value="2"
                                @if($user->status == 2) checked @endif
                            />
                            <label for="accStaClosed">{{ __('Closed') }}</label>
                            @endif
                        </div>
                    </div>
                </div>

                @if(auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'))
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('Email Verification') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="emaSta1"
                                name="email_verified"
                                value="1"
                                @if($user->email_verified_at != null) checked @endif
                            />
                            <label for="emaSta1">{{ __('Verified') }}</label>
                            <input
                                type="radio"
                                id="emaSta2"
                                name="email_verified"
                                value="0"
                                @if($user->email_verified_at == null) checked @endif
                            />
                            <label for="emaSta2">{{ __('Unverified') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('KYC Verification') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="kyc1"
                                name="kyc"
                                value="1"
                                @if($user->kyc == 1) checked @endif
                            />
                            <label for="kyc1">{{ __('Verified') }}</label>
                            <input
                                type="radio"
                                id="kyc2"
                                name="kyc"
                                value="0"
                                @if($user->kyc != 1) checked @endif
                            />
                            <label for="kyc2">{{ __('Unverified') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('2FA Verification') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="2fa1"
                                name="two_fa"
                                value="1"
                                @if($user->two_fa) checked @endif
                            />
                            <label for="2fa1">{{ __('Active') }}</label>
                            <input
                                type="radio"
                                id="2fa2"
                                name="two_fa"
                                value="0"
                                @if(!$user->two_fa) checked @endif
                            />
                            <label for="2fa2">{{ __('Disabled') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('OTP Verification') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="otp-active"
                                name="otp_status"
                                value="1"
                                @if($user->otp_status) checked @endif
                            />
                            <label for="otp-active">{{ __('Active') }}</label>
                            <input
                                type="radio"
                                id="otp-disabled"
                                name="otp_status"
                                value="0"
                                @if(!$user->otp_status) checked @endif
                            />
                            <label for="otp-disabled">{{ __('Disabled') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('Deposit Status') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="depo1"
                                name="deposit_status"
                                value="1"
                                @if($user->deposit_status) checked @endif
                            />
                            <label for="depo1">{{ __('Active') }}</label>
                            <input
                                type="radio"
                                id="depo2"
                                name="deposit_status"
                                value="0"
                                @if(!$user->deposit_status) checked @endif
                            />
                            <label for="depo2">{{ __('Disabled') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('Withdraw Status') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="wid1"
                                name="withdraw_status"
                                value="1"
                                @if($user->withdraw_status) checked @endif
                            />
                            <label for="wid1">{{ __('Active') }}</label>
                            <input
                                type="radio"
                                id="wid2"
                                name="withdraw_status"
                                value="0"
                                @if(!$user->withdraw_status) checked @endif
                            />
                            <label for="wid2">{{ __('Disabled') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('Fund Transfer Status') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="trans1"
                                name="transfer_status"
                                value="1"
                                @if($user->transfer_status) checked @endif
                            />
                            <label for="trans1">{{ __('Active') }}</label>
                            <input
                                type="radio"
                                id="trans2"
                                name="transfer_status"
                                value="0"
                                @if(!$user->transfer_status) checked @endif
                            />
                            <label for="trans2">{{ __('Disabled') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('DPS Status') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="dps-active"
                                name="dps_status"
                                value="1"
                                @if($user->dps_status) checked @endif
                            />
                            <label for="dps-active">{{ __('Active') }}</label>
                            <input
                                type="radio"
                                id="dps-disabled"
                                name="dps_status"
                                value="0"
                                @if(!$user->dps_status) checked @endif
                            />
                            <label for="dps-disabled">{{ __('Disabled') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('FDR Status') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="fdr-active"
                                name="fdr_status"
                                value="1"
                                @if($user->fdr_status) checked @endif
                            />
                            <label for="fdr-active">{{ __('Active') }}</label>
                            <input
                                type="radio"
                                id="fdr-disabled"
                                name="fdr_status"
                                value="0"
                                @if(!$user->fdr_status) checked @endif
                            />
                            <label for="fdr-disabled">{{ __('Disabled') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('Loan Status') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="loan-active"
                                name="loan_account_status"
                                value="1"
                                @if($user->loan_account_status) checked @endif
                            />
                            <label for="loan-active">{{ __('Active') }}</label>
                            <input
                                type="radio"
                                id="loan-disabled"
                                name="loan_account_status"
                                value="0"
                                @if(!$user->loan_account_status) checked @endif
                            />
                            <label for="loan-disabled">{{ __('Disabled') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('Pay Bill Status') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="pay-active"
                                name="pay_bill_status"
                                value="1"
                                @if($user->pay_bill_status) checked @endif
                            />
                            <label for="pay-active">{{ __('Active') }}</label>
                            <input
                                type="radio"
                                id="pay-disabled"
                                name="pay_bill_status"
                                value="0"
                                @if(!$user->pay_bill_status) checked @endif
                            />
                            <label for="pay-disabled">{{ __('Disabled') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('Portfolio Status') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="portfolio-active"
                                name="portfolio_status"
                                value="1"
                                @if($user->portfolio_status) checked @endif
                            />
                            <label for="portfolio-active">{{ __('Active') }}</label>
                            <input
                                type="radio"
                                id="portfolio-disabled"
                                name="portfolio_status"
                                value="0"
                                @if(!$user->portfolio_status) checked @endif
                            />
                            <label for="portfolio-disabled">{{ __('Disabled') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('Reward Status') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="reward-active"
                                name="reward_status"
                                value="1"
                                @if($user->reward_status) checked @endif
                            />
                            <label for="reward-active">{{ __('Active') }}</label>
                            <input
                                type="radio"
                                id="reward-disabled"
                                name="reward_status"
                                value="0"
                                @if(!$user->reward_status) checked @endif
                            />
                            <label for="reward-disabled">{{ __('Disabled') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <h5 class="heading">{{ __('Referral Status') }}</h5>
                        <div class="switch-field">
                            <input
                                type="radio"
                                id="referral-active"
                                name="referral_status"
                                value="1"
                                @if($user->referral_status) checked @endif
                            />
                            <label for="referral-active">{{ __('Active') }}</label>
                            <input
                                type="radio"
                                id="referral-disabled"
                                name="referral_status"
                                value="0"
                                @if(!$user->referral_status) checked @endif
                            />
                            <label for="referral-disabled">{{ __('Disabled') }}</label>
                        </div>
                    </div>
                </div>
                @endif
                <!-- Share Dividend & APY Accrual Settings -->
                <div class="col-xl-12">
                    <div class="profile-card-single">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="heading mb-0">{{ __('Share Dividend / APY Accrual') }}</h5>
                            <span class="badge {{ $user->dividend_status ? 'bg-success' : 'bg-secondary' }}" style="font-size: 11px;">
                                {{ $user->dividend_status ? __('ACTIVE') : __('OFF (Default)') }}
                            </span>
                        </div>
                        <div class="switch-field mb-3">
                            <input
                                type="radio"
                                id="divSta1"
                                name="dividend_status"
                                value="1"
                                @if($user->dividend_status) checked @endif
                            />
                            <label for="divSta1">{{ __('Enabled') }}</label>
                            <input
                                type="radio"
                                id="divSta0"
                                name="dividend_status"
                                value="0"
                                @if(!$user->dividend_status) checked @endif
                            />
                            <label for="divSta0">{{ __('Disabled (Off)') }}</label>
                        </div>

                        <div class="row g-2 text-start">
                            <div class="col-md-6">
                                <label class="small text-muted mb-1">{{ __('Custom APY Rate (%)') }}</label>
                                <input type="number" step="0.01" min="0" max="99.99" name="dividend_apy" class="form-control form-control-sm" placeholder="Default: {{ setting('savings_dividend_apy', '3.25') }}%" value="{{ $user->dividend_apy }}">
                                <small class="text-muted" style="font-size: 10px;">{{ __('Leave blank to inherit global default APY.') }}</small>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted mb-1">{{ __('Target Account') }}</label>
                                <select name="dividend_wallet" class="form-select form-select-sm">
                                    <option value="primary_savings" {{ ($user->dividend_wallet ?? 'primary_savings') === 'primary_savings' ? 'selected' : '' }}>{{ __('Primary Savings') }}</option>
                                    <option value="default" {{ ($user->dividend_wallet ?? '') === 'default' ? 'selected' : '' }}>{{ __('Checking Account') }}</option>
                                </select>
                            </div>
                        </div>

                        @if($user->last_dividend_at)
                            <div class="text-muted small mt-2" style="font-size: 11px;">
                                <i data-lucide="check-circle" style="width: 12px; height: 12px;" class="text-success me-1"></i>
                                {{ __('Last Paid:') }} {{ \Carbon\Carbon::parse($user->last_dividend_at)->format('M d, Y h:i A') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="site-btn-sm primary-btn w-100 centered">
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
