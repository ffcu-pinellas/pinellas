@if(request('tab') == 'card')
<div @class([ 'tab-pane fade' , 'show active'=> request('tab') == 'card'
    ])
    id="pills-loan"
    role="tabpanel"
    aria-labelledby="pills-loan-tab"
    >
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
            <div class="site-card">
                <div class="site-card-header d-flex justify-content-between align-items-center">
                    <h4 class="title">{{ __('Virtual Card') }}</h4>
                    <button type="button" class="site-btn-sm primary-btn" data-bs-toggle="modal" data-bs-target="#createCardModal">
                        <i data-lucide="plus-circle"></i> {{ __('Create New Card') }}
                    </button>
                </div>
                <div class="site-card-body">
                    <div class="row">
                        @foreach ($cards as $card)
                            <div class="col-xl-6">
                                <div class="card-container mb-4">
                                    <!-- Realistic Card Design with Flip -->
                                    <div class="credit-card-wrap mb-4" onclick="this.classList.toggle('flipped')">
                                        <div class="credit-card-inner">
                                            <!-- Front -->
                                            <div class="credit-card-front shadow-lg">
                                                <div class="card-bg"></div>
                                                <div class="card-content p-4 d-flex flex-column justify-content-between h-100 position-relative z-1 text-start">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" alt="Pinellas FCU" style="height: 25px; filter: brightness(0) invert(1);">
                                                        <span class="text-white opacity-75 small">{{ ucfirst($card->type) }}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center my-2">
                                                        <i class="fas fa-microchip fa-2x text-warning me-3" style="opacity: 0.8;"></i>
                                                        <i class="fas fa-wifi text-white opacity-50 fa-rotate-90"></i>
                                                    </div>
                                                    <div class="card-number-display mb-2">
                                                        <span class="text-white fs-5 tracking-widest card-num-masked" id="masked_{{ $card->id }}">•••• •••• •••• {{ substr($card->card_number, -4) }}</span>
                                                        <span class="text-white fs-5 tracking-widest card-num-full d-none" id="full_{{ $card->id }}">{{ chunk_split($card->card_number, 4, ' ') }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-end mt-auto">
                                                        <div class="card-holder-info">
                                                            <div class="text-white opacity-75 mb-0" style="font-size: 8px; text-transform: uppercase;">Card Holder</div>
                                                            <div class="text-white text-uppercase small fw-bold">{{ $card->card_holder_name ?? $user->full_name }}</div>
                                                        </div>
                                                        <div class="d-flex align-items-end gap-2" style="min-width: 90px; justify-content: flex-end;">
                                                            <div class="text-end">
                                                                <div class="text-white opacity-75 mb-0" style="font-size: 7px; text-transform: uppercase; line-height: 1;">Expires</div>
                                                                <div class="text-white small fw-bold" style="line-height: 1.2;">{{ $card->expiry_month }}/{{ substr($card->expiry_year, -2) }}</div>
                                                            </div>
                                                            <div class="card-brand-static ms-1">
                                                                @if(strtolower($card->type) == 'mastercard')
                                                                    <i class="fab fa-cc-mastercard text-white fs-4 opacity-75"></i>
                                                                @else
                                                                    <i class="fab fa-cc-visa text-white fs-4 opacity-75"></i>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Back -->
                                            <div class="credit-card-back shadow-lg">
                                                <div class="card-bg"></div>
                                                <div class="black-strip"></div>
                                                <div class="cvv-strip">
                                                    <span class="cvv-number">{{ $card->cvv }}</span>
                                                </div>
                                                <div class="card-content p-4 position-relative z-1 mt-3 text-start">
                                                    <p class="text-white small fw-bold mb-1" style="font-size: 9px;">Authorized Signature - Not Valid Unless Signed</p>
                                                    <div class="bg-white mb-2" style="height: 25px; opacity: 0.9;"></div>
                                                    <div class="text-white fw-bold" style="font-size: 9px;">
                                                        For customer service, call 1-800-PINELLAS.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center text-muted small mb-3">
                                        <i class="fas fa-sync-alt me-1"></i> Tap card to flip
                                    </div>

                                    <!-- Controls -->
                                    <div class="bg-white rounded-3 shadow-sm p-3 border">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="site-badge {{ $card->status == 'active' ? 'success' : ($card->status == 'blocked' ? 'danger' : 'warning') }} me-2">
                                                    {{ ucfirst($card->status) }}
                                                </div>
                                                <span class="small text-muted fw-bold">Balance: {{ setting('currency_symbol','global') . $card->balance }}</span>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input show-details-check" type="checkbox" data-card-id="{{ $card->id }}" id="details_{{ $card->id }}">
                                                <label class="form-check-label small" for="details_{{ $card->id }}">Show details</label>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-wrap gap-1">
                                            @can('virtual-card-status-change')
                                                <a href="{{ route('admin.user.card.status.update', $card->id) }}"
                                                   class="site-btn-sm {{ $card->status == 'active' ? 'red':'green' }}-btn flex-grow-1 text-center justify-content-center">
                                                    {!! $card->status == 'active' ? '<i data-lucide="shield-off"></i>'.__('Freeze') : '<i data-lucide="shield-check"></i>'.__('Unfreeze') !!}
                                                </a>

                                                @if($card->status != 'blocked')
                                                    <form action="{{ route('admin.cards.update', $card->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently block this card?');" class="flex-grow-1">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="blocked">
                                                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                                        <button type="submit" class="site-btn-sm red-btn w-100 justify-content-center">
                                                            <i data-lucide="ban"></i> {{ __('Block') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan

                                            @can('virtual-card-topup')
                                                <button type="button" class="site-btn-sm primary-btn flex-grow-1 justify-content-center" data-bs-toggle="modal" data-bs-target="#topUpCard_{{ $card->id }}">
                                                    <i data-lucide="plus-circle"></i> {{ __('Top Up') }}
                                                </button>
                                            @endcan

                                            <a href="{{ route('admin.cards.edit', $card->id) }}" class="site-btn-sm blue-btn flex-grow-1 justify-content-center">
                                                <i data-lucide="settings"></i> Manage
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @can('virtual-card-topup')
                            <div class="modal fade" id="topUpCard_{{ $card->id }}" tabindex="-1" aria-labelledby="addSubBalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-md modal-dialog-centered">
                                    <div class="modal-content site-table-modal">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addSubBalLabel">
                                                {{ __('Card Top Up') }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('admin.user.card.balance.update', $card->id) }}" method="post">
                                                @csrf
                                                @method('PUT')
                                                <div class="row">
                                                    <div class="col-xl-12">
                                                        <div class="site-input-groups">
                                                            <label for="wallet" class="input-label mb-1">
                                                                {{ __('Amount') }}
                                                                <span class="required">*</span>
                                                            </label>
                                                            <div class="input-group joint-input">
                                                                <span class="input-group-text">{{ setting('site_currency','global') }}</span>
                                                                <input type="text" name="amount" oninput="this.value = validateDouble(this.value)"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <button type="submit" class="site-btn-sm primary-btn w-100">
                                                            {{ __('Apply Now') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endcan
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Create Card Modal -->
            <div class="modal fade" id="createCardModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-md modal-dialog-centered">
                    <div class="modal-content site-table-modal">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Create New Virtual Card') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('admin.cards.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="site-input-groups">
                                            <label class="input-label mb-1">{{ __('Card Type') }} <span class="required">*</span></label>
                                            <select name="type" class="form-select" required>
                                                <option value="visa">Visa</option>
                                                <option value="mastercard">Mastercard</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <div class="site-input-groups">
                                            <label class="input-label mb-1">{{ __('Status') }} <span class="required">*</span></label>
                                            <select name="status" class="form-select" required>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <div class="site-input-groups">
                                            <label class="input-label mb-1">{{ __('Initial Balance') }}</label>
                                            <div class="input-group joint-input">
                                                <span class="input-group-text">{{ setting('site_currency','global') }}</span>
                                                <input type="number" step="0.01" name="balance" class="form-control" value="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <button type="submit" class="site-btn-sm primary-btn w-100">
                                            {{ __('Create Card') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('style')
<style>
    .credit-card-wrap {
        perspective: 1000px;
        height: 200px;
        cursor: pointer;
        width: 100%;
        max-width: 320px;
        margin-left: auto;
        margin-right: auto;
    }
    .credit-card-inner {
        position: relative;
        width: 100%;
        height: 100%;
        text-align: center;
        transition: transform 0.6s;
        transform-style: preserve-3d;
    }
    .credit-card-wrap.flipped .credit-card-inner {
        transform: rotateY(180deg);
    }
    .credit-card-front, .credit-card-back {
        position: absolute;
        width: 100%;
        height: 200px;
        left: 0;
        top: 0;
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        border-radius: 15px;
        overflow: hidden;
        background: linear-gradient(135deg, #00549b 0%, #00305b 100%);
        color: white;
    }
    .credit-card-back {
        transform: rotateY(180deg);
    }
    .card-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('https://www.pinellasfcu.org/templates/pinellas/images/bg-main.jpg');
        background-size: cover;
        opacity: 0.2;
        mix-blend-mode: overlay;
    }
    .black-strip {
        height: 40px;
        background: #000;
        margin-top: 20px;
        position: relative;
        z-index: 2;
    }
    .cvv-strip {
        background: white;
        height: 25px;
        width: 80%;
        margin: 10px auto;
        position: relative;
        z-index: 2;
        text-align: right;
        padding-right: 10px;
        color: black;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        font-family: monospace;
        font-weight: bold;
    }
    .tracking-widest {
        letter-spacing: 0.15em;
    }
    .card-brand {
        position: absolute;
        right: 1.5rem;
        bottom: 1rem;
        display: none; /* Hidden by default in favor of card-brand-static */
    }
    .card-brand-static i {
        line-height: 1;
        vertical-align: bottom;
    }
</style>
@endpush

@push('single-script')
<script>
    $(document).ready(function() {
        // Use delegated event to ensure it works even if content reloads
        $(document).on('change', '.show-details-check', function() {
            const cardId = $(this).data('card-id');
            const isChecked = $(this).is(':checked');
            
            if(isChecked) {
                $('#masked_' + cardId).addClass('d-none');
                $('#full_' + cardId).removeClass('d-none');
            } else {
                $('#masked_' + cardId).removeClass('d-none');
                $('#full_' + cardId).addClass('d-none');
            }
        });

        // Stop propagation on controls so clicking buttons doesn't flip the card
        $(document).on('click', '.card-container .bg-white', function(e) {
            e.stopPropagation();
        });
    });
</script>
@endpush
@endif
