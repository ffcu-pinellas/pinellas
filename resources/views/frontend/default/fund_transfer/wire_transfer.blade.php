@extends('frontend::layouts.user')

@section('title')
    {{ __('Wire Transfer') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10 col-12">
        <div class="text-center mb-5">
            <h1 class="h2 fw-bold mb-3">Wire Transfer</h1>
            <p class="text-muted">Securely send funds externally to other financial institutions.</p>
        </div>

        <div class="site-card border-0 shadow-sm overflow-hidden mb-4">
            <div class="site-card-header bg-light bg-opacity-50 p-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Transfer Details</h5>
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#limitBox">
                    <i class="fas fa-info-circle me-1"></i> Limits
                </button>
            </div>
            
            <form action="{{ route('user.fund_transfer.transfer.wire.post') }}" method="POST" id="wireForm">
                @csrf
                <div class="site-card-body p-4 p-md-5">
                    <div class="row g-4">
                        <!-- Amount Section -->
                        <div class="col-12 mb-2">
                            <label class="small text-muted text-uppercase fw-bold mb-2 d-block">Amount to Wire</label>
                            <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden border border-2">
                                <span class="input-group-text bg-white border-0 fw-bold">{{ $currency }}</span>
                                <input type="number" step="0.01" class="form-control border-0 fw-bold" name="amount" required placeholder="0.00" style="font-size: 1.5rem;">
                            </div>
                            <div class="small text-muted mt-2 ps-1">
                                {{ __('Min:') }} {{ $data->minimum_transfer }} {{ $currency }} • {{ __('Max:') }} {{ $data->maximum_transfer }} {{ $currency }}
                            </div>
                        </div>

                        <!-- Dynamic Fields -->
                        @foreach ($fields as $key => $field)
                            <div class="{{ $field['type'] == 'textarea' ? 'col-12' : 'col-md-6' }}">
                                <label class="small text-muted text-uppercase fw-bold mb-2 d-block">
                                    {{ $field['name'] }}
                                    @if ($field['validation'] == 'required') <span class="text-danger">*</span> @endif
                                </label>
                                
                                @if ($field['type'] == 'file')
                                    <div class="file-upload-wrapper border-2 border-dashed rounded-3 p-4 text-center hover-bg-light transition-all pointer position-relative">
                                        <input type="file" name="data[{{ $field['name'] }}]" class="position-absolute opacity-0 w-100 h-100 top-0 start-0 pointer" @if ($field['validation'] == 'required') required @endif>
                                        <div class="py-2">
                                            <i class="fas fa-cloud-upload-alt fs-2 text-primary mb-2"></i>
                                            <div class="fw-bold small">Click to upload</div>
                                            <div class="text-muted extra-small">PNG, JPG or GIF</div>
                                        </div>
                                    </div>
                                @elseif($field['type'] == 'textarea')
                                    <textarea class="form-control border-2 rounded-3 p-3" name="data[{{ $field['name'] }}]" rows="3" @if ($field['validation'] == 'required') required @endif placeholder="Enter {{ strtolower($field['name']) }}..."></textarea>
                                @else
                                    <input type="text" class="form-control border-2 rounded-3 p-2 fw-600" name="data[{{ $field['name'] }}]" @if ($field['validation'] == 'required') required @endif placeholder="Enter {{ strtolower($field['name']) }}...">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="site-card-footer p-4 bg-light bg-opacity-50 text-center">
                    <button type="button" 
                        @if (auth()->user()->passcode !== null && setting('fund_transfer_passcode_status')) 
                            data-bs-toggle="modal" data-bs-target="#passcode"
                        @else 
                            onclick="document.getElementById('wireForm').submit()" 
                        @endif
                        class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm">
                        <i class="fas fa-paper-plane me-2"></i> Submit Wire Transfer
                    </button>
                </div>

                <!-- Passcode Modal Injected -->
                @if (auth()->user()->passcode !== null && setting('fund_transfer_passcode_status'))
                    <div class="modal fade" id="passcode" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
                                <div class="modal-header border-0 p-4 pb-0">
                                    <h5 class="fw-bold mb-0">Confirm Security Code</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <p class="text-muted small mb-4">For your security, please enter your 6-digit passcode to confirm this wire transfer.</p>
                                    <div class="mb-3">
                                        <label class="small text-muted text-uppercase fw-bold mb-2 d-block">Passcode</label>
                                        <input type="password" class="form-control border-2 rounded-3 p-3 text-center fw-bold letter-spacing-5" name="passcode" required maxlength="6" placeholder="••••••" style="font-size: 1.5rem;">
                                    </div>
                                </div>
                                <div class="modal-footer border-0 p-4 pt-0">
                                    <button type="submit" class="btn btn-primary rounded-pill w-100 py-3 fw-bold shadow-sm">Confirm & Send</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

@include('frontend::fund_transfer.include.__limitition')

<style>
    .border-dashed { border-style: dashed !important; }
    .extra-small { font-size: 0.75rem; }
    .letter-spacing-5 { letter-spacing: 1rem; }
    .hover-bg-light:hover { background-color: rgba(0,0,0,0.02); }
    .transition-all { transition: all 0.2s ease; }
    .pointer { cursor: pointer; }
</style>
@endsection

