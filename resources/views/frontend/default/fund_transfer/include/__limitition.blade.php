<div class="modal fade" id="limitBox" tabindex="-1" aria-labelledby="limitBoxLabel" aria-hidden="true" style="z-index: 99999 !important;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="z-index: 100000 !important;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; z-index: 100001 !important; background-color: #ffffff;">
            <div class="modal-header border-bottom p-4 pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-sliders-h fs-6"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" id="limitBoxLabel">{{ __('Wire Transfer Limits & Policy') }}</h5>
                        <p class="text-muted extra-small mb-0">{{ __('Applicable limits, velocity controls and fee schedules') }}</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0 bg-transparent">
                        <span class="text-muted fw-semibold small text-uppercase">{{ __('Min. Per Transaction') }}</span>
                        <span class="fw-bold text-dark">{{ $currencySymbol ?? '$' }}{{ number_format(auth()->user()->getEffectiveWireMinLimit($data?->minimum_transfer ?? 50.00), 2) }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0 bg-transparent">
                        <span class="text-muted fw-semibold small text-uppercase">{{ __('Max. Per Transaction') }}</span>
                        <span class="fw-bold text-dark">{{ $currencySymbol ?? '$' }}{{ number_format(auth()->user()->getEffectiveWireMaxLimit($data?->maximum_transfer ?? 500000.00), 2) }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0 bg-transparent">
                        <span class="text-muted fw-semibold small text-uppercase">{{ __('Daily Maximum Volume') }}</span>
                        <span class="fw-bold text-dark">{{ $currencySymbol ?? '$' }}{{ number_format(auth()->user()->getEffectiveWireDailyLimit($data?->daily_limit_maximum_amount ?? 1000000.00), 2) }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0 bg-transparent">
                        <span class="text-muted fw-semibold small text-uppercase">{{ __('Daily Transfer Count Limit') }}</span>
                        <span class="fw-bold text-dark">{{ $data?->daily_limit_maximum_count ?? 10 }} {{ __('Transfers / Day') }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0 bg-transparent">
                        <span class="text-muted fw-semibold small text-uppercase">{{ __('Monthly Maximum Volume') }}</span>
                        <span class="fw-bold text-dark">{{ $currencySymbol ?? '$' }}{{ number_format($data?->monthly_limit_maximum_amount ?? 5000000.00, 2) }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0 bg-transparent">
                        <span class="text-muted fw-semibold small text-uppercase">{{ __('Domestic Fedwire Fee') }}</span>
                        <span class="badge bg-primary px-3 py-2 rounded-pill fw-bold">
                            {{ ($data?->charge_type ?? 'fixed') === 'percentage' ? ($data?->charge . '%') : (($currencySymbol ?? '$') . number_format($data?->charge ?? 25.00, 2)) }}
                        </span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 px-0 bg-transparent">
                        <span class="text-muted fw-semibold small text-uppercase">{{ __('International SWIFT Fee') }}</span>
                        <span class="badge bg-info text-dark px-3 py-2 rounded-pill fw-bold">
                            {{ ($data?->international_charge_type ?? 'fixed') === 'percentage' ? ($data?->international_charge . '%') : (($currencySymbol ?? '$') . number_format($data?->international_charge ?? 45.00, 2)) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top p-3">
                <button type="button" class="btn btn-primary rounded-pill w-100 py-2 fw-bold shadow-sm" data-bs-dismiss="modal">
                    {{ __('I Understand') }}
                </button>
            </div>
        </div>
    </div>
</div>
