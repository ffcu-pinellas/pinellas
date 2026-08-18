<div class="modal fade" id="limitBox" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0 text-dark">{{ __('Wire Transfer Limits & Policy') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="list-group list-group-flush border-top">
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0">
                        <span class="text-muted fw-bold small text-uppercase">{{ __('Min. per transaction') }}</span>
                        <span class="fw-bold text-dark">{{ $currencySymbol ?? '$' }}{{ number_format(auth()->user()->getEffectiveWireMinLimit($data?->minimum_transfer ?? 0), 2) }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0">
                        <span class="text-muted fw-bold small text-uppercase">{{ __('Max. per transaction') }}</span>
                        <span class="fw-bold text-dark">{{ $currencySymbol ?? '$' }}{{ number_format(auth()->user()->getEffectiveWireMaxLimit($data?->maximum_transfer ?? 500000), 2) }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0">
                        <span class="text-muted fw-bold small text-uppercase">{{ __('Daily Max. amount') }}</span>
                        <span class="fw-bold text-dark">{{ $currencySymbol ?? '$' }}{{ number_format(auth()->user()->getEffectiveWireDailyLimit($data?->daily_limit_maximum_amount ?? 1000000), 2) }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0">
                        <span class="text-muted fw-bold small text-uppercase">{{ __('Daily transfer count') }}</span>
                        <span class="fw-bold text-dark">{{ $data?->daily_limit_maximum_count ?? 10 }} {{ __('Transfers') }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0">
                        <span class="text-muted fw-bold small text-uppercase">{{ __('Monthly Max. volume') }}</span>
                        <span class="fw-bold text-dark">{{ $currencySymbol ?? '$' }}{{ number_format($data?->monthly_limit_maximum_amount ?? 5000000, 2) }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0">
                        <span class="text-muted fw-bold small text-uppercase">{{ __('Domestic Wire Fee') }}</span>
                        <span class="fw-bold text-primary">{{ ($data?->charge_type ?? 'fixed') === 'percentage' ? ($data?->charge . '%') : (($currencySymbol ?? '$') . number_format($data?->charge ?? 25.00, 2)) }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 px-0">
                        <span class="text-muted fw-bold small text-uppercase">{{ __('International Wire Fee') }}</span>
                        <span class="fw-bold text-primary">{{ ($data?->international_charge_type ?? 'fixed') === 'percentage' ? ($data?->international_charge . '%') : (($currencySymbol ?? '$') . number_format($data?->international_charge ?? 45.00, 2)) }}</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-primary rounded-pill w-100 py-3 fw-bold shadow-sm" data-bs-dismiss="modal">
                    {{ __('I Understand') }}
                </button>
            </div>
        </div>
    </div>
</div>
