@extends('frontend::layouts.user')

@section('title')
    {{ __('Card Management') }}
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('user.dashboard') }}" class="btn btn-link text-decoration-none text-muted ps-0">
            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="row justify-content-center">
    @forelse($cards as $card)
    <div class="col-md-6 col-lg-5 col-xl-4 mb-4">
        <!-- Realistic Card UI -->
        <div class="credit-card-container mb-4">
            <div class="credit-card {{ $card->status != 'active' ? 'locked' : '' }}">
                <div class="card-front">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <img src="https://www.pinellasfcu.org/templates/pinellas/images/logo.png" alt="Logo" style="height: 30px; filter: brightness(0) invert(1);">
                        <i class="fas fa-wifi fa-lg"></i>
                    </div>
                    
                    <div class="chip mb-3">
                        <div class="chip-line"></div>
                        <div class="chip-line"></div>
                        <div class="chip-line"></div>
                        <div class="chip-line"></div>
                    </div>

                    <div class="card-number-display mb-4" id="card-num-{{ $card->id }}">
                        <span class="masked-num">
                            <span class="dots">••••</span> <span class="dots">••••</span> <span class="dots">••••</span> {{ substr($card->card_number, -4) }}
                        </span>
                        <span class="full-num d-none" style="letter-spacing: 2px;">
                            {{ chunk_split($card->card_number, 4, ' ') }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="card-label">Card Holder</div>
                            <div class="card-value">{{ strtoupper($card->card_holder_name) }}</div>
                        </div>
                        <div class="text-end">
                            <div class="card-label">Expires</div>
                            <div class="card-value">{{ $card->expiry_month }}/{{ substr($card->expiry_year, -2) }}</div>
                        </div>
                    </div>
                    
                    <div class="card-brand position-absolute bottom-0 end-0 p-3">
                         <i class="fab fa-cc-visa fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Controls -->
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4">
                
                <!-- Show/Hide Switch -->
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-light text-primary me-3">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Card Details</h6>
                            <small class="text-muted">Show full card number</small>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input toggle-details" type="checkbox" data-target="#card-num-{{ $card->id }}" style="cursor: pointer;">
                    </div>
                </div>

                <!-- Lock Card -->
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-light {{ $card->status == 'active' ? 'text-success' : 'text-danger' }} me-3">
                            <i class="fas {{ $card->status == 'active' ? 'fa-unlock' : 'fa-lock' }}"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $card->status == 'active' ? 'Card Unlocked' : 'Card Locked' }}</h6>
                            <small class="text-muted">{{ $card->status == 'active' ? 'Your card is active' : 'Transactions disabled' }}</small>
                        </div>
                    </div>
                    <form action="{{ route('user.cards.status.toggle') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $card->id }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" onchange="this.form.submit()" {{ $card->status == 'active' ? 'checked' : '' }} style="cursor: pointer;">
                        </div>
                    </form>
                </div>

                <!-- Report Lost -->
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-light text-danger me-3">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Report Lost</h6>
                            <small class="text-muted">Permanently block card</small>
                        </div>
                    </div>
                    <form action="{{ route('user.cards.report.lost') }}" method="POST" onsubmit="return confirm('Are you sure you want to report this card as lost? This action cannot be undone.');">
                        @csrf
                        <input type="hidden" name="id" value="{{ $card->id }}">
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                            Report
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <img src="{{ asset('assets/global/images/no-data.png') }}" alt="No Cards" class="img-fluid mb-3" style="max-width: 150px;">
        <h5 class="text-muted">No cards found</h5>
        <div class="mt-3">
            <a href="{{ route('user.dashboard') }}" class="btn btn-primary">Return to Dashboard</a>
        </div>
    </div>
    @endforelse
</div>
@endsection

@section('style')
<style>
    .credit-card {
        background: linear-gradient(135deg, #0d47a1 0%, #002171 100%);
        border-radius: 16px;
        color: white;
        padding: 24px;
        position: relative;
        height: 220px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    .credit-card.locked {
        filter: grayscale(100%);
        opacity: 0.8;
    }
    .chip {
        width: 45px;
        height: 35px;
        background: linear-gradient(135deg, #e0e0e0 0%, #bdbdbd 100%);
        border-radius: 6px;
        position: relative;
        overflow: hidden;
    }
    .chip-line {
        position: absolute;
        background: rgba(0,0,0,0.1);
        border: 1px solid rgba(0,0,0,0.2);
    }
    /* Simple Chip Lines Styling */
    .chip-line:nth-child(1) { top: 10px; left: 0; width: 100%; height: 1px; }
    .chip-line:nth-child(2) { top: 24px; left: 0; width: 100%; height: 1px; }
    .chip-line:nth-child(3) { top: 0; left: 15px; width: 1px; height: 100%; }
    .chip-line:nth-child(4) { top: 0; left: 30px; width: 1px; height: 100%; }

    .card-number-display {
        font-family: 'Courier New', Courier, monospace;
        font-size: 1.4rem;
        letter-spacing: 2px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }
    .card-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        opacity: 0.8;
        letter-spacing: 1px;
    }
    .card-value {
        font-weight: 600;
        font-size: 0.95rem;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Toggle Switch Custom Color */
    .form-check-input:checked {
        background-color: #0d47a1;
        border-color: #0d47a1;
    }
</style>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('.toggle-details');
        
        toggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const targetId = this.getAttribute('data-target');
                const container = document.querySelector(targetId);
                const masked = container.querySelector('.masked-num');
                const full = container.querySelector('.full-num');
                
                if(this.checked) {
                    masked.classList.add('d-none');
                    full.classList.remove('d-none');
                } else {
                    masked.classList.remove('d-none');
                    full.classList.add('d-none');
                }
            });
        });
    });
</script>
@endsection
