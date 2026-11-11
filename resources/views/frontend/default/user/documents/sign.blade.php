@extends('frontend::layouts.user')

@section('title')
    {{ __('E-Sign Document') }} — {{ $docTitle }}
@endsection

@section('content')
<div class="container-fluid py-3">
    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <a href="{{ route('user.documents.index') }}" class="text-decoration-none small fw-semibold">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('Back to My Documents') }}
                </a>
                <h2 class="h4 fw-bold mt-1 mb-0">{{ $docTitle }}</h2>
            </div>
            <div class="d-flex gap-2 align-items-center">
                @if($signature->status === 'signed')
                    <span class="badge bg-success px-3 py-2"><i class="fas fa-shield-alt me-1"></i> {{ __('Executed & Verified') }}</span>
                    <a href="{{ route('user.documents.download', $signature->external_signature_id) }}" class="btn btn-sm btn-primary fw-semibold">
                        <i class="fas fa-download me-1"></i> {{ __('Download Signed PDF') }}
                    </a>
                @elseif($signature->status === 'pending')
                    <span class="badge bg-warning text-dark px-3 py-2">
                        <i class="fas fa-pen-nib me-1"></i> {{ __('Awaiting Your Signature') }}
                        @if($signature->expires_at) • {{ __('Expires') }} {{ $signature->expires_at->format('M d, Y') }} @endif
                    </span>
                @elseif($signature->status === 'declined')
                    <span class="badge bg-danger px-3 py-2">{{ __('Declined') }}</span>
                @else
                    <span class="badge bg-secondary px-3 py-2">{{ __('Expired') }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Official Document Viewer -->
        <div class="col-lg-7">
            <div class="banno-card p-4 bg-white border" style="min-height: 460px;">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                    <div>
                        <strong class="text-primary">{{ setting('site_title', 'global') ?? 'FrontField Credit Union' }}</strong>
                        <div class="small text-muted">{{ __('Official Electronic Record') }}</div>
                    </div>
                    <div class="text-end small text-muted">
                        <div>{{ __('Member') }}: <strong>{{ auth()->user()->full_name }}</strong></div>
                        <div>{{ __('Account') }}: ••••{{ substr((string) auth()->user()->account_number, -4) }}</div>
                    </div>
                </div>

                <div class="document-preview-body" style="line-height: 1.65; font-size: 14px; color: #1e293b;">
                    {!! $renderedHtml !!}
                </div>

                @if($signature->status === 'signed')
                    <div class="mt-4 pt-3 border-top bg-light p-3 rounded">
                        <div class="small fw-bold text-success mb-2"><i class="fas fa-check-circle me-1"></i> {{ __('Electronic Signature Certificate') }}</div>
                        @if(!empty($signature->signature_data))
                            <div class="mb-2 bg-white border rounded p-2 d-inline-block">
                                <img src="{{ $signature->signature_data }}" alt="Executed Signature" style="max-height: 75px;">
                            </div>
                        @endif
                        <div class="small text-muted">
                            <div><strong>{{ __('Signed By') }}:</strong> {{ auth()->user()->full_name }}</div>
                            <div><strong>{{ __('Timestamp (UTC)') }}:</strong> {{ data_get($signature->metadata, 'signed_at_utc') ?? $signature->signed_at?->format('Y-m-d H:i:s \U\T\C') }}</div>
                            <div><strong>{{ __('IP Address') }}:</strong> {{ $signature->ip_address }}</div>
                            <div><strong>{{ __('SHA-256 Digest') }}:</strong> <code class="text-break">{{ data_get($signature->metadata, 'verification_hash') }}</code></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Signing Pad / Status Column -->
        <div class="col-lg-5">
            @if($signature->status === 'pending')
                <div class="banno-card p-4">
                    <h4 class="h6 fw-bold mb-2"><i class="fas fa-signature text-primary me-1"></i> {{ __('Adopt Your Electronic Signature') }}</h4>
                    <p class="small text-muted mb-3">{{ __('Draw your signature below or type your full legal name to adopt an electronic signature.') }}</p>

                    <ul class="nav nav-pills mb-3 gap-2" id="sigModeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active btn-sm" id="draw-tab" data-bs-toggle="pill" data-bs-target="#draw-pane" type="button" role="tab">
                                <i class="fas fa-pen me-1"></i> {{ __('Draw Signature') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link btn-sm" id="type-tab" data-bs-toggle="pill" data-bs-target="#type-pane" type="button" role="tab">
                                <i class="fas fa-font me-1"></i> {{ __('Type Name') }}
                            </button>
                        </li>
                    </ul>

                    <form id="esignForm" method="POST" action="{{ route('user.documents.sign.submit', $signature->external_signature_id) }}">
                        @csrf
                        <input type="hidden" name="signature_mode" id="signatureModeInput" value="draw">
                        <input type="hidden" name="signature_data" id="signatureDataInput" value="">

                        <div class="tab-content mb-3">
                            <div class="tab-pane fade show active" id="draw-pane" role="tabpanel">
                                <div class="border rounded bg-light position-relative" style="touch-action: none;">
                                    <canvas id="sigCanvas" width="420" height="170" style="width: 100%; height: 170px; display: block; cursor: crosshair;"></canvas>
                                    <div id="sigPlaceholder" class="position-absolute top-50 start-50 translate-middle text-muted small pe-none opacity-50">
                                        {{ __('Sign inside this box') }}
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-2">
                                    <button type="button" id="clearSigBtn" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eraser me-1"></i> {{ __('Clear Pad') }}
                                    </button>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="type-pane" role="tabpanel">
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">{{ __('Full Legal Name') }}</label>
                                    <input type="text" class="form-control" name="typed_name" id="typedSigInput" value="{{ auth()->user()->full_name }}" placeholder="Type full name">
                                </div>
                                <div class="border rounded p-3 bg-light text-center">
                                    <div id="typedPreview" style="font-family: 'Brush Script MT', 'Segoe Script', Georgia, cursive; font-size: 32px; color: #0f172a; min-height: 52px;">
                                        {{ auth()->user()->full_name }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3 bg-light p-3 rounded border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="consent_accepted" id="esignConsentCheck" value="1" required>
                            <label class="form-check-label small" for="esignConsentCheck">
                                {{ __('I agree to the ESIGN Act Electronic Records Disclosure and adopt this signature as my legal equivalent to a handwritten signature for this document.') }}
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mb-2">
                            <i class="fas fa-check-double me-1"></i> {{ __('Adopt & Sign Document') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('user.documents.sign.decline', $signature->external_signature_id) }}" onsubmit="return confirm('Decline signing this document? Your Account Officer will be notified.');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100 btn-sm mt-1">
                            {{ __('Decline to Sign') }}
                        </button>
                    </form>
                </div>
            @else
                <div class="banno-card p-4">
                    <h4 class="h6 fw-bold mb-2">{{ __('Document Status') }}</h4>
                    <p class="small text-muted mb-3">
                        @if($signature->status === 'signed')
                            {{ __('This agreement has been executed with a tamper-evident SHA-256 verification digest. You can download your archival copy anytime.') }}
                        @elseif($signature->status === 'declined')
                            {{ __('You declined this document request. Please contact your Account Officer if a revised agreement is needed.') }}
                        @else
                            {{ __('This signature request expired before completion.') }}
                        @endif
                    </p>
                    <a href="{{ route('user.documents.download', $signature->external_signature_id) }}" class="btn btn-primary w-100">
                        <i class="fas fa-file-pdf me-1"></i> {{ __('Download PDF Record') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
(function() {
    var canvas = document.getElementById('sigCanvas');
    if (!canvas) return;

    var ctx = canvas.getContext('2d');
    var placeholder = document.getElementById('sigPlaceholder');
    var modeInput = document.getElementById('signatureModeInput');
    var dataInput = document.getElementById('signatureDataInput');
    var typedInput = document.getElementById('typedSigInput');
    var typedPreview = document.getElementById('typedPreview');

    var drawing = false;
    var hasInk = false;

    ctx.strokeStyle = '#0f172a';
    ctx.lineWidth = 2.4;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    function getPos(e) {
        var rect = canvas.getBoundingClientRect();
        var clientX = e.touches ? e.touches[0].clientX : e.clientX;
        var clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: (clientX - rect.left) * (canvas.width / rect.width),
            y: (clientY - rect.top) * (canvas.height / rect.height)
        };
    }

    function startDraw(e) {
        e.preventDefault();
        drawing = true;
        hasInk = true;
        if (placeholder) placeholder.style.display = 'none';
        var pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function moveDraw(e) {
        if (!drawing) return;
        e.preventDefault();
        var pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }

    function stopDraw() {
        drawing = false;
    }

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', moveDraw);
    window.addEventListener('mouseup', stopDraw);

    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', moveDraw, { passive: false });
    canvas.addEventListener('touchend', stopDraw);

    document.getElementById('clearSigBtn')?.addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasInk = false;
        if (placeholder) placeholder.style.display = 'block';
    });

    document.getElementById('draw-tab')?.addEventListener('shown.bs.tab', function() {
        modeInput.value = 'draw';
    });
    document.getElementById('type-tab')?.addEventListener('shown.bs.tab', function() {
        modeInput.value = 'type';
    });

    typedInput?.addEventListener('input', function() {
        if (typedPreview) typedPreview.textContent = this.value || '';
    });

    function renderTypedSignaturePng(name) {
        var tmp = document.createElement('canvas');
        tmp.width = 460;
        tmp.height = 140;
        var tctx = tmp.getContext('2d');
        tctx.clearRect(0, 0, tmp.width, tmp.height);
        tctx.fillStyle = '#0f172a';
        tctx.font = 'italic 38px Georgia, "Times New Roman", serif';
        tctx.textAlign = 'center';
        tctx.textBaseline = 'middle';
        tctx.fillText(name, tmp.width / 2, tmp.height / 2);
        return tmp.toDataURL('image/png');
    }

    document.getElementById('esignForm')?.addEventListener('submit', function(e) {
        if (modeInput.value === 'draw') {
            if (!hasInk) {
                e.preventDefault();
                alert('Please draw your signature inside the signature box or switch to Type Name.');
                return false;
            }
            dataInput.value = canvas.toDataURL('image/png');
        } else {
            var name = (typedInput?.value || '').trim();
            if (!name) {
                e.preventDefault();
                alert('Please type your full legal name to adopt a signature.');
                return false;
            }
            dataInput.value = renderTypedSignaturePng(name);
        }
    });
})();
</script>
@endsection
