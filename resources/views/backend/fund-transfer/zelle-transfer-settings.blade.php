@extends('backend.layouts.app')
@section('title')
    {{ __('Zelle® Transfer Settings') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-xl-12">
                        <div class="title-content d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: #741B6B; width: 44px; height: 44px;">
                                    <img src="{{ asset('assets/external/images/zelle logo2025.png') }}" alt="Zelle" style="height: 18px; filter: brightness(0) invert(1);">
                                </div>
                                <div>
                                    <h2 class="title mb-0" style="color: #2b3457; font-weight: 700;">{{ __('Zelle® Transfer Settings') }}</h2>
                                    <p class="text-muted small mb-0">{{ __('Configure global velocity limits, daily caps, fees, and network controls for Zelle payments.') }}</p>
                                </div>
                            </div>
                            <a href="{{ route('admin.fund.transfer.zelle') }}" class="title-btn">
                                <i data-lucide="activity"></i>{{ __('Zelle Transfer Activity') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-3">
            <div class="row justify-content-center">
                <div class="col-xl-9">
                    <div class="site-card border-0 shadow-sm" style="border-radius: 14px;">
                        <div class="site-card-body p-4 p-md-5">
                            <form action="{{ route('admin.zelle.transfer.post') }}" class="row g-4" method="post">
                                @csrf

                                {{-- Global Status Switch --}}
                                <div class="col-12">
                                    <div class="p-3 border rounded-3 d-flex justify-content-between align-items-center flex-wrap gap-3" style="background: #fdfafc; border-left: 4px solid #741B6B !important;">
                                        <div>
                                            <h6 class="fw-bold mb-1" style="color: #4a1144;">{{ __('Global Zelle® Transfer System Status') }}</h6>
                                            <p class="text-muted small mb-0">{{ __('Enable or disable the Zelle payment network for all members system-wide.') }}</p>
                                        </div>
                                        <div class="switch-field" style="margin: 0;">
                                            <input type="radio" id="status_active" name="status" value="1" @checked(($zelleSetting?->status ?? 1) == 1) />
                                            <label for="status_active" style="padding: 6px 20px; font-weight: 600;">{{ __('Active') }}</label>
                                            <input type="radio" id="status_disabled" name="status" value="0" @checked(($zelleSetting?->status ?? 1) == 0) />
                                            <label for="status_disabled" style="padding: 6px 20px; font-weight: 600;">{{ __('Disabled') }}</label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick Information Callout --}}
                                <div class="col-12">
                                    <div class="p-3 rounded-3 bg-light border d-flex align-items-center gap-3">
                                        <i class="fas fa-info-circle text-primary fs-4"></i>
                                        <div class="small text-muted">
                                            <strong>{{ __('Per-User Limit Overrides:') }}</strong> {{ __('These settings apply globally to all members. To grant custom limits (e.g. $5,000 or $10,000/day) to a specific user, edit their profile under') }} <strong>{{ __('Customer Management -> Edit User') }}</strong>.
                                        </div>
                                    </div>
                                </div>

                                {{-- Section: Velocity & Limits --}}
                                <div class="col-12">
                                    <h5 class="fw-bold mb-1" style="color: #741B6B; border-bottom: 2px solid rgba(116, 27, 107, 0.15); padding-bottom: 8px;">
                                        <i class="fas fa-sliders-h me-2"></i>{{ __('Transaction & Volume Limits') }}
                                    </h5>
                                </div>

                                <div class="col-xl-6 col-md-6">
                                    <div class="site-input-groups">
                                        <label class="box-input-label fw-bold" for="minimum_transfer">{{ __('Minimum Transfer Amount:') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white fw-bold" style="color: #741B6B;">{{ $currencySymbol }}</span>
                                            <input type="number" step="0.01" min="0.01" name="minimum_transfer" id="minimum_transfer" class="form-control form-control-lg fw-bold" value="{{ $zelleSetting?->minimum_transfer ?? 1.00 }}" required />
                                        </div>
                                        <span class="extra-small text-muted">{{ __('Minimum amount allowed per Zelle payment (Default: $1.00)') }}</span>
                                    </div>
                                </div>

                                <div class="col-xl-6 col-md-6">
                                    <div class="site-input-groups">
                                        <label class="box-input-label fw-bold" for="maximum_transfer">{{ __('Maximum Per-Transaction Limit:') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white fw-bold" style="color: #741B6B;">{{ $currencySymbol }}</span>
                                            <input type="number" step="0.01" min="0.01" name="maximum_transfer" id="maximum_transfer" class="form-control form-control-lg fw-bold text-primary" value="{{ $zelleSetting?->maximum_transfer ?? 2500.00 }}" required />
                                        </div>
                                        <span class="extra-small text-muted">{{ __('Maximum allowed for a single Zelle transaction (Default: $2,500.00)') }}</span>
                                    </div>
                                </div>

                                <div class="col-xl-6 col-md-6">
                                    <div class="site-input-groups">
                                        <label class="box-input-label fw-bold" for="daily_limit_maximum_amount">{{ __('Daily Maximum Transfer Limit (Amount):') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white fw-bold" style="color: #741B6B;">{{ $currencySymbol }}</span>
                                            <input type="number" step="0.01" min="0.01" name="daily_limit_maximum_amount" id="daily_limit_maximum_amount" class="form-control form-control-lg fw-bold text-success" value="{{ $zelleSetting?->daily_limit_maximum_amount ?? 2500.00 }}" required />
                                        </div>
                                        <span class="extra-small text-muted">{{ __('Total maximum Zelle volume a user can send within 24 hours (Default: $2,500.00)') }}</span>
                                    </div>
                                </div>

                                <div class="col-xl-6 col-md-6">
                                    <div class="site-input-groups">
                                        <label class="box-input-label fw-bold" for="daily_limit_maximum_count">{{ __('Daily Maximum Transfer Count:') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white fw-bold"><i class="fas fa-hashtag"></i></span>
                                            <input type="number" min="1" name="daily_limit_maximum_count" id="daily_limit_maximum_count" class="form-control form-control-lg fw-bold" value="{{ $zelleSetting?->daily_limit_maximum_count ?? 10 }}" required />
                                        </div>
                                        <span class="extra-small text-muted">{{ __('Maximum number of Zelle payments allowed per day (Default: 10)') }}</span>
                                    </div>
                                </div>

                                <div class="col-xl-6 col-md-6">
                                    <div class="site-input-groups">
                                        <label class="box-input-label fw-bold" for="monthly_limit_maximum_amount">{{ __('Monthly Maximum Transfer Limit (Amount):') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white fw-bold" style="color: #741B6B;">{{ $currencySymbol }}</span>
                                            <input type="number" step="0.01" min="0.01" name="monthly_limit_maximum_amount" id="monthly_limit_maximum_amount" class="form-control form-control-lg fw-bold" value="{{ $zelleSetting?->monthly_limit_maximum_amount ?? 10000.00 }}" required />
                                        </div>
                                        <span class="extra-small text-muted">{{ __('Total maximum Zelle volume allowed in a calendar month (Default: $10,000.00)') }}</span>
                                    </div>
                                </div>

                                <div class="col-xl-6 col-md-6">
                                    <div class="site-input-groups">
                                        <label class="box-input-label fw-bold" for="monthly_limit_maximum_count">{{ __('Monthly Maximum Transfer Count:') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white fw-bold"><i class="fas fa-hashtag"></i></span>
                                            <input type="number" min="1" name="monthly_limit_maximum_count" id="monthly_limit_maximum_count" class="form-control form-control-lg fw-bold" value="{{ $zelleSetting?->monthly_limit_maximum_count ?? 50 }}" required />
                                        </div>
                                        <span class="extra-small text-muted">{{ __('Maximum number of Zelle transactions per month (Default: 50)') }}</span>
                                    </div>
                                </div>

                                {{-- Section: Fee Configuration --}}
                                <div class="col-12 mt-4">
                                    <h5 class="fw-bold mb-1" style="color: #741B6B; border-bottom: 2px solid rgba(116, 27, 107, 0.15); padding-bottom: 8px;">
                                        <i class="fas fa-receipt me-2"></i>{{ __('Fee Configuration (Optional)') }}
                                    </h5>
                                </div>

                                <div class="col-xl-12">
                                    <div class="site-input-groups position-relative">
                                        <label class="box-input-label fw-bold" for="charge">{{ __('Zelle® Transfer Processing Fee:') }}</label>
                                        <div class="position-relative">
                                            <input type="number" step="0.01" min="0" class="box-input form-control form-control-lg" name="charge" id="charge" value="{{ $zelleSetting?->charge ?? 0.00 }}"/>
                                            <div class="prcntcurr">
                                                <select name="charge_type" class="form-select">
                                                    <option value="fixed" {{ ($zelleSetting?->charge_type ?? 'fixed') == 'fixed' ? 'selected' : '' }}>{{ $currencySymbol }} (Fixed Fee)</option>
                                                    <option value="percentage" {{ ($zelleSetting?->charge_type ?? '') == 'percentage' ? 'selected' : '' }}>{{ __('% (Percentage)') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <span class="extra-small text-muted">{{ __('Standard Zelle transfers are typically free ($0.00).') }}</span>
                                    </div>
                                </div>

                                {{-- Section: Instructions & Disclosures --}}
                                <div class="col-12 mt-4">
                                    <h5 class="fw-bold mb-1" style="color: #741B6B; border-bottom: 2px solid rgba(116, 27, 107, 0.15); padding-bottom: 8px;">
                                        <i class="fas fa-file-contract me-2"></i>{{ __('Zelle® Disclosures & Instructions') }}
                                    </h5>
                                </div>

                                <div class="col-xl-12">
                                    <div class="site-input-groups fw-normal">
                                        <label for="instructions" class="box-input-label">{{ __('Instructions displayed to members on the Zelle payment page:') }}</label>
                                        <div class="site-editor">
                                            <textarea class="summernote" name="instructions" id="instructions">
                                                {!! $zelleSetting?->instructions ?? '<p>Zelle® is a fast, safe and easy way to send money directly between almost any bank accounts in the U.S., typically within minutes. With just an email address or U.S. mobile phone number, you can send money to people you trust.</p>' !!}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-12 mt-4">
                                    <button type="submit" class="site-btn primary-btn w-100 py-3 fw-bold fs-6 shadow-sm" style="background-color: #741B6B; border-color: #741B6B;">
                                        <i class="fas fa-save me-2"></i> {{ __('Save Zelle® Transfer Settings') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
