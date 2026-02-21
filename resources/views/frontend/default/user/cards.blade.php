@extends('frontend::layouts.user')

@section('title')
    {{ __('My Cards') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-6 col-lg-8 col-md-10 col-12">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('user.dashboard') }}" class="btn btn-icon btn-light rounded-circle me-3"><i class="fas fa-arrow-left"></i></a>
            <h2 class="mb-0">Manage your cards</h2>
        </div>

        @forelse($cards as $card)
        <div class="card-container mb-5">
            <!-- Realistic Card Design with Flip -->
            <div class="credit-card-wrap mb-4" onclick="this.classList.toggle('flipped')">
                <div class="credit-card-inner">
                    <!-- Front -->
                    <div class="credit-card-front shadow-lg">
                        <div class="card-bg"></div>
                        <div class="card-content p-4 d-flex flex-column justify-content-between h-100 position-relative z-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" alt="Pinellas FCU" style="height: 30px; filter: brightness(0) invert(1);">
                                <span class="text-white opacity-75 small">{{ $card->card_type ?? 'Debit' }}</span>
                            </div>
                            <div class="d-flex align-items-center my-3">
                                <i class="fas fa-microchip fa-3x text-warning me-3" style="opacity: 0.8;"></i>
                                <i class="fas fa-wifi text-white opacity-50 fa-rotate-90"></i>
                            </div>
                            <div class="card-number-display mb-2">
                                <span class="text-white fs-4 tracking-widest card-num-masked">•••• •••• •••• {{ substr($card->card_number, -4) }}</span>
                                <span class="text-white fs-4 tracking-widest card-num-full d-none">{{ chunk_split($card->card_number, 4, ' ') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <div class="text-white opacity-75 fs-7 text-uppercase mb-0" style="font-size: 10px;">Card Holder</div>
                                    <div class="text-white text-uppercase small fw-bold">{{ $card->name_on_card ?? auth()->user()->full_name }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="text-white opacity-75 fs-7 text-uppercase mb-0" style="font-size: 10px;">Expires</div>
                                    <div class="text-white small fw-bold">{{ $card->expiry_date ?? '12/28' }}</div>
                                </div>
                                <!-- Visa/Mastercard Logo -->
                                <div class="card-brand">
                                    <i class="fab fa-cc-visa text-white fs-1 opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back -->
                    <div class="credit-card-back shadow-lg">
                        <div class="card-bg"></div>
                        <div class="black-strip"></div>
                        <div class="cvv-strip">
                            <span class="cvv-number">{{ $card->cvv ?? rand(100,999) }}</span>
                        </div>
                        <div class="card-content p-4 position-relative z-1 mt-4">
                            <p class="text-white small fw-bold">Authorized Signature - Not Valid Unless Signed</p>
                            <div class="bg-white mb-3" style="height: 30px; opacity: 0.9;"></div>
                            <div class="text-white small fw-bold">
                                For customer service, call 1-800-PINELLAS.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center text-muted small mb-4">
                <i class="fas fa-sync-alt me-1"></i> Tap card to flip
            </div>

            <!-- Controls -->
            <div class="bg-white rounded-4 shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center">
                        <div class="status-indicator {{ $card->status == 'active' ? 'bg-success' : 'bg-danger' }} rounded-circle me-2" style="width: 10px; height: 10px;"></div>
                        <span class="small" id="cardStatusText">{{ $card->status == 'active' ? 'Active' : 'Locked' }}</span>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="showDetailsToggle">
                        <label class="form-check-label small" for="showDetailsToggle">Show details</label>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <form id="toggleStatusForm_{{ $card->id }}" action="{{ route('user.cards.toggle-status') }}" method="POST" onsubmit="event.preventDefault(); SecurityGate.gate(this);">
                            @csrf
                            <input type="hidden" name="card_id" value="{{ $card->id }}">
                            <button type="submit" class="btn btn-outline-danger w-100 py-3 rounded-4 d-flex flex-column align-items-center gap-2 h-100 justify-content-center border-2 btn-action">
                                <i class="fas {{ $card->status == 'active' ? 'fa-lock' : 'fa-unlock' }} fa-lg"></i>
                                <span class="small">{{ $card->status == 'active' ? 'Lock Card' : 'Unlock Card' }}</span>
                            </button>
                        </form>
                    </div>
                    <div class="col-6">
                        <form id="reportLostForm_{{ $card->id }}" action="{{ route('user.cards.report-lost') }}" method="POST" onsubmit="event.preventDefault(); if(confirm('Are you sure you want to report this card as lost? It will be permanently locked.')) { SecurityGate.gate(this); }">
                            @csrf
                            <input type="hidden" name="card_id" value="{{ $card->id }}">
                            <button type="submit" class="btn btn-outline-secondary w-100 py-3 rounded-4 d-flex flex-column align-items-center gap-2 h-100 justify-content-center border-2 btn-action">
                                <i class="fas fa-exclamation-triangle fa-lg"></i>
                                <span class="small">Report Lost</span>
                            </button>
                        </form>
                    </div>
                    <div class="col-6">
                         <button data-bs-toggle="modal" data-bs-target="#resetPinModal" class="btn btn-outline-primary w-100 py-3 rounded-4 d-flex flex-column align-items-center gap-2 h-100 justify-content-center border-2 btn-action">
                            <i class="fas fa-key fa-lg"></i>
                            <span class="small">Reset PIN</span>
                        </button>
                    </div>
                    <div class="col-6">
                         <a href="{{ route('user.transactions') }}?search=card" class="btn btn-outline-primary w-100 py-3 rounded-4 d-flex flex-column align-items-center gap-2 h-100 justify-content-center border-2 btn-action">
                            <i class="fas fa-history fa-lg"></i>
                            <span class="small">History</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reset PIN Modal -->
        <div class="modal fade" id="resetPinModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold text-dark pt-3 px-3">Update Card PIN</h5>
                        <button type="button" class="btn-close me-2 mt-2" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="resetPinForm" action="{{ route('user.cards.reset-pin') }}" method="POST" onsubmit="event.preventDefault(); SecurityGate.gate(this);">
                        @csrf
                        <div class="modal-body p-4">
                            <p class="small text-muted mb-4">Set a new 4-digit PIN for your physical or virtual card.</p>
                            <input type="hidden" name="card_id" value="{{ $card->id }}">
                            <div class="mb-4 text-center">
                                <label class="form-label small fw-bold text-uppercase d-block mb-1">New 4-Digit PIN</label>
                                <input type="password" name="new_pin" class="form-control form-control-lg text-center fw-bold fs-2 border-2 shadow-none mx-auto" style="max-width: 150px;" maxlength="4" pattern="\d{4}" required placeholder="••••" inputmode="numeric">
                            </div>
                            <div class="mb-4 text-center">
                                <label class="form-label small fw-bold text-uppercase d-block mb-1">Confirm PIN</label>
                                <input type="password" name="confirm_pin" class="form-control form-control-lg text-center fw-bold fs-2 border-2 shadow-none mx-auto" style="max-width: 150px;" maxlength="4" pattern="\d{4}" required placeholder="••••" inputmode="numeric">
                            </div>
                            <div class="mb-1">
                                <label class="form-label small fw-bold text-uppercase">Account Password</label>
                                <input type="password" name="password" class="form-control" required placeholder="Verify password to save">
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm">Reset Card PIN</button>
                            <button type="button" class="btn btn-link w-100 text-muted text-decoration-none small mt-2" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @empty
        <div class="text-center py-5">
            <div class="mb-4">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="far fa-credit-card fa-3x text-muted"></i>
                </div>
            </div>
            <h4 class="mb-3">No cards found</h4>
            <p class="text-muted mb-4">You don't have any active cards associated with your account.</p>
            <a href="{{ route('user.dashboard') }}" class="btn btn-primary rounded-pill px-4">Return to Dashboard</a>
        </div>
        @endforelse
    </div>
</div>
@endsection

@section('style')
<style>
    .credit-card-wrap {
        perspective: 1000px;
        height: 240px;
        cursor: pointer;
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
        max-width: 400px;
        height: 240px; /* Explicit height */
        left: 0;
        right: 0;
        margin: auto;
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        border-radius: 20px;
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
        opacity: 0.3;
        mix-blend-mode: overlay;
    }
    .black-strip {
        height: 50px;
        background: #000;
        margin-top: 20px;
        position: relative;
        z-index: 2;
    }
    .cvv-strip {
        background: white;
        height: 30px;
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
    }
    .tracking-widest {
        letter-spacing: 0.15em;
    }
    .btn-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-action {
        transition: all 0.2s;
    }
    .btn-action:hover {
        transform: translateY(-2px);
    }
    .rounded-4 { border-radius: 1.25rem !important; }
</style>
@endsection

@section('script')
<script>
    // Toggle Details
    document.getElementById('showDetailsToggle')?.addEventListener('change', function() {
        const isChecked = this.checked;
        if(isChecked) {
            document.querySelectorAll('.card-num-masked').forEach(el => el.classList.add('d-none'));
            document.querySelectorAll('.card-num-full').forEach(el => el.classList.remove('d-none'));
        } else {
            document.querySelectorAll('.card-num-masked').forEach(el => el.classList.remove('d-none'));
            document.querySelectorAll('.card-num-full').forEach(el => el.classList.add('d-none'));
        }
    });

    // We no longer need the manual fetch functions as we use form submissions via SecurityGate
</script>
@endsection
