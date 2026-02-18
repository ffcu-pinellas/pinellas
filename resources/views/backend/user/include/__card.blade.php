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
                                <div class="site-card">
                                    <div class="site-card-body">
                                        <div class="profile-text-data">
                                            <div class="attribute">{{ __('Cardholder Name') }}</div>
                                            <div class="value">
                                                {{ $card?->cardHolder?->name }}
                                            </div>
                                        </div>
                                        <div class="profile-text-data">
                                            <div class="attribute">{{ __('Card Number') }}</div>
                                            <div class="value">
                                                **** **** **** {{ $card?->last_four_digits }}
                                            </div>
                                        </div>
                                        <div class="profile-text-data">
                                            <div class="attribute">{{ __('Card Expiry') }}</div>
                                            <div class="value">
                                                {{ $card?->expiration_month }} / {{ $card?->expiration_year }}
                                            </div>
                                        </div>
                                        <div class="profile-text-data">
                                            <div class="attribute">{{ __('Card Balance') }}</div>
                                            <div class="value">
                                                {{ setting('currency_symbol','global') . $card->amount }}
                                            </div>
                                        </div>
                                        <div class="profile-text-data">
                                            <div class="attribute">{{ __('Card Status') }}</div>
                                            <div class="value">
                                                <div
                                                    class="site-badge {{ $card?->status == 'active' ? 'success' : ($card?->status == 'blocked' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($card?->status) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @can('virtual-card-status-change')
                                            <!-- Activate/Deactivate -->
                                            <a href="{{ route('admin.user.card.status.update', $card->id) }}"
                                                class="site-btn-sm {{ $card?->status == 'active' ? 'red':'green' }}-btn">
                                                {!! $card->status == 'active' ? '<i data-lucide="shield-off"></i>'.__('Freeze') : '<i data-lucide="shield-check"></i>'.__('Unfreeze') !!}
                                            </a>
                                            
                                            <!-- Block Button -->
                                            @if($card->status != 'blocked')
                                            <form action="{{ route('admin.cards.update', $card->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently block this card?');">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="blocked">
                                                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                                <button type="submit" class="site-btn-sm red-btn">
                                                    <i data-lucide="x-circle"></i> {{ __('Block') }}
                                                </button>
                                            </form>
                                            @endif
                                            @endcan
                                            
                                            @can('virtual-card-topup')
                                            <button type="button" class="site-btn-sm primary-btn" data-bs-toggle="modal" data-bs-target="#topUpCard_{{ $card->id }}">
                                                <i data-lucide="plus-circle"></i> {{ __('Top Up') }}
                                            </button>
                                            @endcan
                                            <!-- Add Manage/Details Link if needed -->
                                            <a href="{{ route('admin.cards.edit', $card->id) }}" class="site-btn-sm blue-btn"><i data-lucide="settings"></i> Manage</a>
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
@endif
