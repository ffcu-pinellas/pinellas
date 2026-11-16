{{-- ── MANAGE CARDS PRE-PAGE MODAL (3D Flip & Quick Controls) ── --}}
@php
    $modalUser = auth()->user();
    $modalInst = app()->bound('institution') ? app('institution') : null;
    $isCustomModalInst = $modalInst && !$modalInst->is_default;
    
    $modalCardLogo = $isCustomModalInst
        ? ($modalInst->logo_white_url ?: asset('assets/institutions/clevelandstatebank/images/logo-white.png'))
        : (file_exists(public_path('assets/external/images/frontfield_logo_white_1774915533306.png')) ? asset('assets/external/images/frontfield_logo_white_1774915533306.png') : asset(setting('site_logo', 'global')));
    
    $modalCardTitle = $isCustomModalInst ? $modalInst->name : (setting('site_title') ?? 'FrontField Credit Union');
    $modalCardPhone = $isCustomModalInst ? ($modalInst->support_phone ?? '662-843-9461') : (setting('site_phone') ?? '1-800-FRONTFIELD');
    $modalCardBgGradient = $isCustomModalInst ? 'linear-gradient(135deg, #c8232c 0%, #8b0000 50%, #4a0307 100%)' : 'linear-gradient(135deg, #00549b 0%, #00305b 100%)';
    $modalCardDisclaimer = $isCustomModalInst ? 'Member FDIC | Equal Housing Lender' : 'Federally Insured by NCUA';
    
    $userCardRecord = $userCard ?? null;
    if (!$userCardRecord && $modalUser) {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('user_cards')) {
                $userCardRecord = \App\Models\UserCard::where('user_id', $modalUser->id)->first();
            }
        } catch (\Throwable $ce) {
            $userCardRecord = null;
        }
    }
@endphp

<div class="modal fade" id="manageCardsModal" tabindex="-1" aria-hidden="true" style="z-index: 2050;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background-color: #1e293b; color: #fff; border-bottom: 2px solid {{ $isCustomModalInst ? '#c8232c' : '#00549b' }};">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-credit-card" style="color: {{ $isCustomModalInst ? '#c8232c' : '#38bdf8' }};"></i>
                    <h5 class="modal-title fw-bold mb-0" style="font-size: 15px; letter-spacing: 0.5px;">MANAGE DEBIT CARDS</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                @if($userCardRecord && $modalUser)
                    <!-- 3D Card Container -->
                    <div class="csb-card-flip-wrap" onclick="csbToggleCardFlip(this)" title="Click to flip card" style="perspective: 1000px; height: 220px; cursor: pointer; margin: 0 auto 16px auto; max-width: 360px; position: relative;">
                        <div class="csb-card-flip-inner" style="position: relative; width: 100%; height: 100%; text-align: left; transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1); transform-style: preserve-3d;">
                            <!-- Front Face -->
                            <div class="csb-card-face csb-card-front" style="position: absolute; width: 100%; height: 100%; -webkit-backface-visibility: hidden; backface-visibility: hidden; border-radius: 16px; padding: 20px 22px; color: #fff; background: {{ $modalCardBgGradient }}; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; justify-content: space-between;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $modalCardLogo }}" alt="{{ $modalCardTitle }}" style="height: 24px; max-width: 140px; object-fit: contain;">
                                    </div>
                                    <span class="badge text-uppercase" style="background: rgba(255,255,255,0.15); font-size: 10px; letter-spacing: 1px;">
                                        {{ $userCardRecord->type ?? 'DEBIT' }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-3 my-2">
                                    <i class="fa-solid fa-microchip fa-2x" style="color: #eab308;"></i>
                                    <i class="fa-solid fa-wifi fa-rotate-90 text-white-50"></i>
                                </div>
                                <div class="text-center my-1" style="overflow: hidden; width: 100%;">
                                    <span class="csb-card-num-masked font-monospace text-white" style="font-size: clamp(14px, 4.4vw, 19px); letter-spacing: clamp(1.5px, 0.5vw, 2.5px); white-space: nowrap !important; display: inline-block;">
                                        •••• •••• •••• {{ substr((string)($userCardRecord->card_number ?? '4000123456789010'), -4) }}
                                    </span>
                                    <span class="csb-card-num-full font-monospace text-white d-none" style="font-size: clamp(13px, 4.2vw, 18px); letter-spacing: clamp(1px, 0.4vw, 2px); white-space: nowrap !important; display: inline-block;">
                                        {{ trim(chunk_split((string)($userCardRecord->card_number ?? '4000123456789010'), 4, ' ')) }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-end mt-2">
                                    <div class="text-start">
                                        <div class="text-white-50" style="font-size: 9px; text-transform: uppercase;">Cardholder</div>
                                        <div class="fw-bold text-white small">{{ strtoupper($modalUser->full_name ?: ($userCardRecord->card_holder_name ?? 'MEMBER')) }}</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-white-50" style="font-size: 9px; text-transform: uppercase;">Expires</div>
                                        <div class="fw-bold text-white small">{{ $userCardRecord->expiry_month ?? '12' }}/{{ substr((string)($userCardRecord->expiry_year ?? '2028'), -2) }}</div>
                                    </div>
                                    <div>
                                        <i class="fa-brands fa-cc-visa fa-2x text-white opacity-75"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Back Face -->
                            <div class="csb-card-face csb-card-back" style="position: absolute; width: 100%; height: 100%; -webkit-backface-visibility: hidden; backface-visibility: hidden; border-radius: 16px; color: #fff; background: {{ $modalCardBgGradient }}; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25); transform: rotateY(180deg); overflow: hidden;">
                                <div style="background: #0f172a; height: 38px; width: 100%; margin-top: 18px;"></div>
                                <div style="background: repeating-linear-gradient(45deg, #f0f0f0, #f0f0f0 4px, #e2e8f0 4px, #e2e8f0 8px); height: 30px; margin: 12px 18px; border-radius: 4px; display: flex; align-items: center; justify-content: flex-end; padding: 0 10px;">
                                    <span id="csbCardCvvDisplay" style="color: #0f172a; font-family: monospace; font-weight: bold; font-size: 13px; letter-spacing: 1.5px;">{{ $userCardRecord->cvv ?? '719' }}</span>
                                </div>
                                <div class="p-3 text-start">
                                    <div class="text-white-50" style="font-size: 10px; line-height: 1.4;">
                                         AUTHORIZED SIGNATURE • NOT VALID UNLESS SIGNED
                                    </div>
                                    <div class="bg-white rounded my-1" style="height: 18px; opacity: 0.85;"></div>
                                    <div class="text-white-50 mt-2" style="font-size: 9.5px; line-height: 1.3;">
                                        For 24/7 customer service or lost cards: <strong>{{ $modalCardPhone }}</strong>.<br>
                                        {{ $modalCardTitle }} — {{ $modalCardDisclaimer }}.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-muted small mb-3">
                        <i class="fa-solid fa-arrows-rotate me-1"></i> Tap card to flip
                    </div>

                    <!-- Card Controls -->
                    <div class="bg-light p-3 rounded text-start mb-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle d-inline-block {{ ($userCardRecord->status ?? 'active') == 'active' ? 'bg-success' : 'bg-danger' }}" style="width: 10px; height: 10px;"></span>
                                <span class="small fw-bold {{ ($userCardRecord->status ?? 'active') == 'active' ? 'text-success' : 'text-danger' }}">
                                    {{ ($userCardRecord->status ?? 'active') == 'active' ? 'Card Active' : 'Card Frozen' }}
                                </span>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" id="csbDetailsSwitch" onchange="csbToggleCardDetails(this)">
                                <label class="form-check-label small" for="csbDetailsSwitch">Show details</label>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button" class="btn {{ ($userCardRecord->status ?? 'active') == 'active' ? 'btn-outline-danger' : 'btn-outline-success' }} w-100 fw-bold btn-sm py-2" onclick="csbToggleCardFreeze(this, {{ $userCardRecord->id ?? 0 }})">
                                    <i class="fa-solid {{ ($userCardRecord->status ?? 'active') == 'active' ? 'fa-snowflake' : 'fa-lock-open' }} me-1"></i>
                                    {{ ($userCardRecord->status ?? 'active') == 'active' ? 'FREEZE CARD' : 'UNFREEZE CARD' }}
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary w-100 fw-bold btn-sm py-2" onclick="csbResetPin({{ $userCardRecord->id ?? 0 }})">
                                    <i class="fa-solid fa-key me-1"></i> RESET PIN
                                </button>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('user.cards') }}" class="small text-muted text-decoration-none">
                        <i class="fa-solid fa-sliders me-1"></i> View Full Card Management Hub &rarr;
                    </a>
                @else
                    <div class="py-4 text-muted">
                        <i class="fa-solid fa-credit-card fa-3x mb-3 text-secondary"></i>
                        <h5 class="fw-bold">No Card Issued</h5>
                        <p class="small">Contact your branch officer to issue your Visa® Debit Card.</p>
                        <a href="{{ route('user.ticket.index') }}" class="btn btn-danger btn-sm fw-bold">Request Card</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
if (typeof csbToggleCardFlip === 'undefined') {
    window.csbToggleCardFlip = function(el) {
        var inner = el.querySelector('.csb-card-flip-inner');
        if (inner) {
            if (inner.style.transform === 'rotateY(180deg)') {
                inner.style.transform = 'rotateY(0deg)';
            } else {
                inner.style.transform = 'rotateY(180deg)';
            }
        }
    };
}

if (typeof csbToggleCardDetails === 'undefined') {
    window.csbToggleCardDetails = function(checkbox) {
        var masked = document.querySelector('.csb-card-num-masked');
        var full = document.querySelector('.csb-card-num-full');
        if (checkbox.checked) {
            if (masked) masked.classList.add('d-none');
            if (full) full.classList.remove('d-none');
        } else {
            if (masked) masked.classList.remove('d-none');
            if (full) full.classList.add('d-none');
        }
    };
}

if (typeof csbToggleCardFreeze === 'undefined') {
    window.csbToggleCardFreeze = function(btn, cardId) {
        if (!cardId) { alert("No card found."); return; }
        if (typeof triggerSecurityGate === 'function') {
            triggerSecurityGate(function() {
                $.post("{{ route('user.cards.toggle-status') }}", {
                    _token: "{{ csrf_token() }}",
                    card_id: cardId
                }).done(function(res) {
                    alert(res.message || "Card status updated successfully.");
                    window.location.reload();
                }).fail(function(xhr) {
                    alert(xhr.responseJSON?.message || "Could not update card status.");
                });
            });
        } else {
            $.post("{{ route('user.cards.toggle-status') }}", {
                _token: "{{ csrf_token() }}",
                card_id: cardId
            }).done(function(res) {
                alert(res.message || "Card status updated successfully.");
                window.location.reload();
            }).fail(function(xhr) {
                alert(xhr.responseJSON?.message || "Could not update card status.");
            });
        }
    };
}

if (typeof csbResetPin === 'undefined') {
    window.csbResetPin = function(cardId) {
        if (!cardId) { alert("No card found."); return; }
        var newPin = prompt("Enter new 4-digit PIN for your debit card:");
        if (!newPin) return;
        if (!/^\d{4}$/.test(newPin)) {
            alert("PIN must be exactly 4 digits.");
            return;
        }
        if (typeof triggerSecurityGate === 'function') {
            triggerSecurityGate(function() {
                $.post("{{ route('user.cards.reset-pin') }}", {
                    _token: "{{ csrf_token() }}",
                    card_id: cardId,
                    pin: newPin,
                    pin_confirmation: newPin
                }).done(function(res) {
                    alert(res.message || "Card PIN reset successfully.");
                }).fail(function(xhr) {
                    alert(xhr.responseJSON?.message || "Could not reset card PIN.");
                });
            });
        } else {
            $.post("{{ route('user.cards.reset-pin') }}", {
                _token: "{{ csrf_token() }}",
                card_id: cardId,
                pin: newPin,
                pin_confirmation: newPin
            }).done(function(res) {
                alert(res.message || "Card PIN reset successfully.");
            }).fail(function(xhr) {
                alert(xhr.responseJSON?.message || "Could not reset card PIN.");
            });
        }
    };
}
</script>
