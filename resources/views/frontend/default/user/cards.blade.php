@extends('frontend::layouts.user')

@section('title')
    {{ __('My Cards') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-6 col-lg-8 col-md-10 col-12">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('user.dashboard') }}" class="btn btn-icon btn-light rounded-circle me-3"><i class="fas fa-arrow-left"></i></a>
            <h2 class="fw-bold mb-0">Manage your cards</h2>
        </div>

        @forelse($cards as $card)
        <div class="card-container mb-5">
            <!-- Realistic Card Design -->
            <div class="credit-card-wrap mb-4">
                <div class="credit-card-front shadow-lg">
                    <div class="card-bg"></div>
                    <div class="card-content p-4 d-flex flex-column justify-content-between h-100 position-relative z-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" alt="Pinellas FCU" style="height: 30px; filter: brightness(0) invert(1);">
                            <span class="text-white opacity-75 small fw-bold">{{ $card->card_type ?? 'Debit' }}</span>
                        </div>
                        <div class="d-flex align-items-center my-3">
                            <img src="{{ asset('assets/global/images/chip.png') }}" alt="Chip" style="height: 35px; margin-right: 15px;">
                            <i class="fas fa-wifi text-white opacity-50 fa-rotate-90"></i>
                        </div>
                        <div class="card-number-display mb-2">
                            <span class="text-white fs-4 fw-bolder tracking-widest card-num-masked">•••• •••• •••• {{ substr($card->card_number, -4) }}</span>
                            <span class="text-white fs-4 fw-bolder tracking-widest card-num-full d-none">{{ chunk_split($card->card_number, 4, ' ') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <div class="text-white opacity-75 fs-7 text-uppercase mb-0" style="font-size: 10px;">Card Holder</div>
                                <div class="text-white fw-bold text-uppercase">{{ $card->name_on_card }}</div>
                            </div>
                            <div class="text-end">
                                <div class="text-white opacity-75 fs-7 text-uppercase mb-0" style="font-size: 10px;">Expires</div>
                                <div class="text-white fw-bold">{{ $card->expiry_date }}</div>
                            </div>
                            <!-- Visa/Mastercard Logo -->
                            <div class="card-brand">
                                <i class="fab fa-cc-visa text-white fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="bg-white rounded-4 shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center">
                        <div class="status-indicator {{ $card->status == 1 ? 'bg-success' : 'bg-danger' }} rounded-circle me-2" style="width: 10px; height: 10px;"></div>
                        <span class="fw-bold">{{ $card->status == 1 ? 'Active' : 'Locked' }}</span>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="showDetailsToggle">
                        <label class="form-check-label small fw-bold" for="showDetailsToggle">Show details</label>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <button class="btn btn-outline-danger w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-2 h-100 justify-content-center border-2">
                            <i class="fas fa-lock fa-lg"></i>
                            <span class="fw-bold small">{{ $card->status == 1 ? 'Lock Card' : 'Unlock Card' }}</span>
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-2 h-100 justify-content-center border-2">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                            <span class="fw-bold small">Report Lost</span>
                        </button>
                    </div>
                    <div class="col-6">
                         <button class="btn btn-outline-primary w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-2 h-100 justify-content-center border-2">
                            <i class="fas fa-key fa-lg"></i>
                            <span class="fw-bold small">Reset PIN</span>
                        </button>
                    </div>
                    <div class="col-6">
                         <button class="btn btn-outline-primary w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-2 h-100 justify-content-center border-2">
                            <i class="fas fa-history fa-lg"></i>
                            <span class="fw-bold small">History</span>
                        </button>
                    </div>
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
            <h4 class="fw-bold">No cards found</h4>
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
    }
    .credit-card-front {
        width: 100%;
        max-width: 400px;
        height: 240px;
        margin: 0 auto;
        border-radius: 20px;
        background: linear-gradient(135deg, #00549b 0%, #00305b 100%);
        position: relative;
        overflow: hidden;
        color: white;
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
</style>
@endsection

@section('script')
<script>
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
</script>
@endsection
