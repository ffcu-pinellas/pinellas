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

<style>
    .swal2-container {
        z-index: 999999 !important;
    }
</style>
<div class="modal fade" id="manageCardsModal" tabindex="-1" aria-hidden="true" style="z-index: 2055;">
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
                                    @php
                                        $rawCardNum = (string)($userCardRecord->card_number ?? '4000123456789010');
                                        $c1 = substr($rawCardNum, 0, 4) ?: '4000';
                                        $c2 = substr($rawCardNum, 4, 4) ?: '1234';
                                        $c3 = substr($rawCardNum, 8, 4) ?: '5678';
                                        $c4 = substr($rawCardNum, 12, 4) ?: '9010';
                                    @endphp
                                    <div class="csb-card-num-masked" style="width: 100%;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; font-family: 'Courier New', Courier, monospace; font-weight: bold; font-size: clamp(13px, 4vw, 18px); letter-spacing: 1.5px; white-space: nowrap !important;">
                                            <span>••••</span>
                                            <span>••••</span>
                                            <span>••••</span>
                                            <span>{{ $c4 }}</span>
                                        </div>
                                    </div>
                                    <div class="csb-card-num-full d-none" style="width: 100%;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; font-family: 'Courier New', Courier, monospace; font-weight: bold; font-size: clamp(12px, 3.8vw, 17px); letter-spacing: 1px; white-space: nowrap !important;">
                                            <span>{{ $c1 }}</span>
                                            <span>{{ $c2 }}</span>
                                            <span>{{ $c3 }}</span>
                                            <span>{{ $c4 }}</span>
                                        </div>
                                    </div>
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

<!-- Reset PIN Modal -->
<div class="modal fade" id="csbResetPinModal" tabindex="-1" aria-hidden="true" style="z-index: 2065;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 16px;">
            <div class="modal-header py-3 px-4" style="background: #162846; color: #fff;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-key" style="color: #c8232c;"></i>
                    <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 15px;">RESET DEBIT CARD PIN</h5>
                </div>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="csbResetPinForm" onsubmit="csbSubmitPinReset(event)">
                    <input type="hidden" id="csbResetCardId" value="">
                    <div id="csbPinNotice" class="alert alert-danger d-none py-2 small mb-3"></div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">New 4-Digit PIN</label>
                        <input type="password" id="csbNewPinInput" class="form-control form-control-lg text-center fw-bold shadow-none" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" placeholder="••••" required style="letter-spacing: 8px; font-size: 24px;" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Confirm 4-Digit PIN</label>
                        <input type="password" id="csbConfirmPinInput" class="form-control form-control-lg text-center fw-bold shadow-none" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" placeholder="••••" required style="letter-spacing: 8px; font-size: 24px;" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                    </div>

                    <div class="alert alert-light border small text-muted mb-4" style="font-size: 12px;">
                        <i class="fa-solid fa-lock text-success me-1"></i> Your new PIN takes effect immediately at ATMs and merchant terminals nationwide upon 2FA verification.
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" id="btnSubmitPinReset" class="btn fw-bold text-white py-2" style="background: #c8232c; border-radius: 8px;">
                            <i class="fa-solid fa-check me-1"></i> AUTHORIZE &amp; UPDATE PIN
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="csbBackToCards()">
                            &larr; Back to Cards
                        </button>
                    </div>
                </form>
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

window.csbToggleCardFreeze = function(btn, cardId) {
    if (!cardId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'warning', title: 'Card Notice', text: 'No card found.' });
        }
        return;
    }
    var $btn = $(btn);
    var origText = $btn.html();

    var proceed = function() {
        if (typeof window.hideLoader === 'function') window.hideLoader();
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Updating...');

        var token = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

        $.ajax({
            url: "{{ route('user.cards.toggle-status') }}",
            type: "POST",
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                _token: token,
                card_id: cardId,
                security_verified: 1
            }
        }).done(function(res) {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Card Status Updated',
                    text: res.message || 'Your card status has been updated successfully.',
                    confirmButtonColor: '#162846'
                }).then(function() {
                    window.location.reload();
                });
            } else {
                window.location.reload();
            }
        }).fail(function(xhr) {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            $btn.prop('disabled', false).html(origText);
            var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not update card status.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Card Control Error',
                    text: errorMsg,
                    confirmButtonColor: '#162846'
                }).then(function() {
                    var cardsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('manageCardsModal'));
                    if (cardsModal) cardsModal.show();
                });
            } else {
                var cardsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('manageCardsModal'));
                if (cardsModal) cardsModal.show();
            }
        });
    };

    var cardsModalEl = document.getElementById('manageCardsModal');
    var cardsModal = cardsModalEl ? bootstrap.Modal.getInstance(cardsModalEl) : null;
    if (cardsModal) cardsModal.hide();

    if (typeof SecurityGate !== 'undefined' && typeof SecurityGate.gate === 'function') {
        SecurityGate.onCancelCb = function() {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            var cm = bootstrap.Modal.getOrCreateInstance(document.getElementById('manageCardsModal'));
            if (cm) cm.show();
        };
        SecurityGate.gate(proceed);
    } else {
        proceed();
    }
};

window.csbResetPin = function(cardId) {
    if (!cardId) {
        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Card Notice', text: 'No card found.' });
        return;
    }
    document.getElementById('csbResetCardId').value = cardId;
    document.getElementById('csbNewPinInput').value = '';
    document.getElementById('csbConfirmPinInput').value = '';
    $('#csbPinNotice').addClass('d-none').text('');

    var cardsModal = bootstrap.Modal.getInstance(document.getElementById('manageCardsModal'));
    if (cardsModal) cardsModal.hide();

    setTimeout(function() {
        var pinModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('csbResetPinModal'));
        pinModal.show();
    }, 250);
};

window.csbBackToCards = function() {
    var pinModal = bootstrap.Modal.getInstance(document.getElementById('csbResetPinModal'));
    if (pinModal) pinModal.hide();
    setTimeout(function() {
        var cardsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('manageCardsModal'));
        cardsModal.show();
    }, 250);
};

window.csbSubmitPinReset = function(e) {
    e.preventDefault();
    var cardId = document.getElementById('csbResetCardId').value;
    var pin = document.getElementById('csbNewPinInput').value.trim();
    var confirmPin = document.getElementById('csbConfirmPinInput').value.trim();
    var $notice = $('#csbPinNotice');
    $notice.addClass('d-none').text('');

    if (pin.length !== 4 || !/^\d{4}$/.test(pin)) {
        $notice.removeClass('d-none').text('PIN must be exactly 4 numeric digits.');
        return;
    }
    if (pin !== confirmPin) {
        $notice.removeClass('d-none').text('PIN and confirmation PIN do not match.');
        return;
    }

    var $btn = $('#btnSubmitPinReset');
    var origText = $btn.html();

    var proceed = function() {
        if (typeof window.hideLoader === 'function') window.hideLoader();
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Authorizing...');

        var token = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

        $.ajax({
            url: "{{ route('user.cards.reset-pin') }}",
            type: "POST",
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                _token: token,
                card_id: cardId,
                new_pin: pin,
                confirm_pin: confirmPin,
                security_verified: 1
            }
        }).done(function(res) {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            $btn.prop('disabled', false).html(origText);
            var pinModal = bootstrap.Modal.getInstance(document.getElementById('csbResetPinModal'));
            if (pinModal) pinModal.hide();

            document.getElementById('csbNewPinInput').value = '';
            document.getElementById('csbConfirmPinInput').value = '';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'PIN Updated Successfully',
                    text: res.message || 'Your new 4-digit debit card PIN is now active.',
                    confirmButtonColor: '#162846'
                }).then(function() {
                    var cardsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('manageCardsModal'));
                    if (cardsModal) cardsModal.show();
                });
            }
        }).fail(function(xhr) {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            $btn.prop('disabled', false).html(origText);
            var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not reset card PIN. Please try again.';
            $notice.removeClass('d-none').text(errorMsg);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'PIN Reset Error',
                    text: errorMsg,
                    confirmButtonColor: '#162846'
                });
            }
            var pm = bootstrap.Modal.getOrCreateInstance(document.getElementById('csbResetPinModal'));
            if (pm) pm.show();
        });
    };

    var pinModalEl = document.getElementById('csbResetPinModal');
    var pinModal = pinModalEl ? bootstrap.Modal.getInstance(pinModalEl) : null;
    if (pinModal) pinModal.hide();

    if (typeof SecurityGate !== 'undefined' && typeof SecurityGate.gate === 'function') {
        SecurityGate.onCancelCb = function() {
            if (typeof window.hideLoader === 'function') window.hideLoader();
            var pm = bootstrap.Modal.getOrCreateInstance(document.getElementById('csbResetPinModal'));
            if (pm) pm.show();
        };
        SecurityGate.gate(proceed);
    } else {
        proceed();
    }
};
</script>

