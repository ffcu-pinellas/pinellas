@extends('frontend::layouts.user')

@section('title')
    {{ __('Card Management') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12 text-center mb-5">
        <h1 class="fw-bold">Manage your cards</h1>
        <p class="text-muted">Control your debit and credit cards securely.</p>
    </div>
</div>

<div class="row g-4 justify-content-center">
    @forelse($cards as $card)
    <div class="col-md-6 col-lg-4">
        <!-- Card Viz -->
        <div class="banno-card p-0 overflow-hidden text-white position-relative shadow-lg border-0 mb-3" style="border-radius: 16px; background: linear-gradient(135deg, #00549b 0%, #003366 100%); height: 220px;">
            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                <i class="fas fa-wifi fa-2x"></i>
            </div>
            <div class="p-4 d-flex flex-column h-100 justify-content-between">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold opacity-75">{{ $card->type }}</span>
                    @if($card->is_virtual)
                        <span class="badge bg-white text-primary rounded-pill px-3">Virtual</span>
                    @endif
                </div>
                
                <div class="my-3 text-center">
                    <div class="h3 mb-0" style="letter-spacing: 4px; font-family: monospace;">
                        {{ chunk_split($card->card_number, 4, ' ') }}
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-end">
                    <div>
                        <div class="small opacity-75 text-uppercase">Card Holder</div>
                        <div class="fw-bold">{{ strtoupper($card->card_holder_name) }}</div>
                    </div>
                    <div class="text-end">
                        <div class="small opacity-75 text-uppercase">Expires</div>
                        <div class="fw-bold">{{ $card->expiry_month }}/{{ substr($card->expiry_year, -2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="bg-white p-4 rounded-3 shadow-sm text-center">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold {{ $card->status == 'active' ? 'text-success' : 'text-danger' }}">
                    <i class="fas fa-circle small me-1"></i> {{ ucfirst($card->status) }}
                </span>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="cardStatus{{ $card->id }}" {{ $card->status == 'active' ? 'checked' : '' }} disabled>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <button class="btn btn-light w-100 py-2 small fw-bold text-muted">
                        <i class="fas fa-lock me-1"></i> Lock Card
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn btn-light w-100 py-2 small fw-bold text-muted">
                        <i class="fas fa-flag me-1"></i> Report Lost
                    </button>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <img src="{{ asset('assets/global/images/no-data.png') }}" alt="No Cards" class="img-fluid mb-3" style="max-width: 150px;">
        <h5 class="text-muted">No cards found</h5>
        <p class="small text-muted">Contact support to request a card.</p>
    </div>
    @endforelse
</div>
@endsection
