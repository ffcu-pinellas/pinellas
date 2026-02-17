<div class="modal fade" id="limitBox" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0">{{ __('Wire Transfer Limits') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="list-group list-group-flush border-top">
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0">
                        <span class="text-muted fw-bold small text-uppercase">{{ __('Min. per transaction') }}</span>
                        <span class="fw-bold text-dark">{{ $data->minimum_transfer }} {{ $currency }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0">
                        <span class="text-muted fw-bold small text-uppercase">{{ __('Max. per transaction') }}</span>
                        <span class="fw-bold text-dark">{{ $data->maximum_transfer }} {{ $currency }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0">
                        <span class="text-muted fw-bold small text-uppercase">{{ __('Daily Max. amount') }}</span>
                        <span class="fw-bold text-dark">{{ $data->daily_limit_maximum_amount }} {{ $currency }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0">
                        <span class="text-muted fw-bold small text-uppercase">{{ __('Monthly Max. amount') }}</span>
                        <span class="fw-bold text-dark">{{ $data->monthly_limit_maximum_amount }} {{ $currency }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0">
                        <span class="text-muted fw-bold small text-uppercase">{{ __('Daily count limit') }}</span>
                        <span class="fw-bold text-dark">{{ $data->daily_limit_maximum_count }} {{ __('Times') }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom px-0">
                        <span class="text-muted fw-bold small text-uppercase">{{ __('Monthly count limit') }}</span>
                        <span class="fw-bold text-dark">{{ $data->monthly_limit_maximum_count }} {{ __('Times') }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 px-0">
                        <span class="text-muted fw-bold small text-uppercase">{{ __('Per transaction fee') }}</span>
                        <span class="fw-bold text-primary">{{ $data->charge }} {{ $data->charge_type == 'percentage' ? '%' : $currency }}</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-primary rounded-pill w-100 py-3 fw-bold shadow-sm" data-bs-dismiss="modal">
                    {{ __('I understand') }}
                </button>
            </div>
        </div>
    </div>
</div>

