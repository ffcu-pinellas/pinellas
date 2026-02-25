@extends('frontend::layouts.user')

@section('title')
{{ __('Remote Deposit') }}
@endsection

@section('content')
<div class="row justify-content-center mb-5">
    <div class="col-lg-7">
        <!-- Banno Header -->
        <div class="text-center mb-5">
            <h1 class="h2 fw-bold mb-3">Deposit a check</h1>
            <p class="text-muted">Quickly deposit checks from anywhere using your device's camera.</p>
            <button type="button" class="btn btn-link text-decoration-none" data-bs-toggle="modal" data-bs-target="#historyModal">
                <i class="fas fa-history me-1"></i> View History
            </button>
        </div>

        <form action="{{ route('user.remote_deposit.store') }}" method="POST" enctype="multipart/form-data" id="depositForm">
            @csrf
            
            <!-- Step 1: Info -->
            <div class="site-card mb-4 p-4">
                <div class="row g-3">
                    <div class="col-12">
                         <label class="small text-muted mb-1 fw-bold text-uppercase">Deposit to</label>
                         <select name="account_id" class="form-select border-0 border-bottom shadow-none rounded-0 px-0 fw-600" style="padding-bottom: 10px; font-size: 14px;" required>
                            <option value="checking">Personal Checking (...{{ substr(auth()->user()->account_number, -4) }}) - ${{ number_format(auth()->user()->balance, 2) }}</option>
                            <option value="savings">Primary Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}) - ${{ number_format(auth()->user()->savings_balance, 2) }}</option>
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <label class="small text-muted mb-1 fw-bold text-uppercase">Amount</label>
                        <div class="input-group border-bottom">
                            <span class="input-group-text bg-transparent border-0 ps-0 fs-4 fw-600">$</span>
                            <input type="number" name="amount" class="form-control border-0 shadow-none fs-4 fw-600 px-1" step="0.01" min="0.01" placeholder="0.00" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Photos -->
            <div class="site-card mb-4 overflow-hidden">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h6 class="fw-bold text-uppercase small text-muted mb-0">Check Photos</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6 text-center">
                            <div class="deposit-capture-box position-relative rounded-3 p-4 bg-light d-flex flex-column align-items-center justify-content-center" style="min-height: 180px; border: 2px dashed #ddd; transition: all 0.2s;" onclick="openCamera('front')">
                                <div id="front_preview_container" class="position-absolute top-0 start-0 w-100 h-100 d-none" style="z-index: 1;">
                                    <img id="front_preview" class="w-100 h-100 object-fit-cover rounded-3">
                                </div>
                                <input type="hidden" name="front_image_base64" id="front_image_base64">
                                <input type="file" name="front_image" id="front_image_file" class="native-capture-fallback" accept="image/*" capture="environment">
                                
                                <div class="capture-icon mb-2">
                                    <i class="fas fa-camera fa-2x text-primary"></i>
                                </div>
                                <div class="fw-bold mb-1">Front of check</div>
                                <div class="small text-muted status-text">Tap to capture</div>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <div class="deposit-capture-box position-relative rounded-3 p-4 bg-light d-flex flex-column align-items-center justify-content-center" style="min-height: 180px; border: 2px dashed #ddd; transition: all 0.2s;" onclick="openCamera('back')">
                                <div id="back_preview_container" class="position-absolute top-0 start-0 w-100 h-100 d-none" style="z-index: 1;">
                                    <img id="back_preview" class="w-100 h-100 object-fit-cover rounded-3">
                                </div>
                                <input type="hidden" name="back_image_base64" id="back_image_base64">
                                <input type="file" name="back_image" id="back_image_file" class="native-capture-fallback" accept="image/*" capture="environment">

                                <div class="capture-icon mb-2">
                                    <i class="fas fa-signature fa-2x text-primary"></i>
                                </div>
                                <div class="fw-bold mb-1">Back of check</div>
                                <div class="small text-muted status-text">Tap to capture</div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted">Trouble with the camera? <a href="javascript:void(0)" onclick="triggerUploadFallback()">Upload existing photo</a></small>
                    </div>
                </div>
            </div>

            <!-- Guidance -->
            <div class="alert alert-secondary border-0 small d-flex gap-3 p-4 mb-4" style="background: rgba(0,0,0,0.03); border-radius: 12px;">
                <i class="fas fa-lightbulb text-warning fa-lg"></i>
                <div>
                    <div class="fw-bold text-dark mb-1">Endorsement guidance</div>
                    <div class="text-muted">
                        Be sure to endorse the back of your check with your signature and "For Mobile Deposit Only to Pinellas FCU".
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow" onclick="event.preventDefault(); SecurityGate.gate(this.form);">
                Submit deposit
            </button>
        </form>
    </div>

    <!-- Professional Scanner Modal -->
    <div class="modal fade" id="cameraModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content overflow-hidden border-0" style="background: #000;">
                <!-- Header Overlay -->
                <div class="scanner-header position-absolute top-0 start-0 w-100 d-flex justify-content-between align-items-center p-3" style="z-index: 101;">
                    <div class="text-white">
                        <h6 class="mb-0 fw-bold" id="scanner-title">Front of Check</h6>
                        <small class="opacity-75" id="scanner-subtitle">Align within brackets</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" onclick="stopCamera()"></button>
                </div>

                <!-- Scanner Viewport -->
                <div class="modal-body p-0 position-relative d-flex align-items-center justify-content-center bg-black" style="min-height: 80vh;">
                    <video id="videoStream" autoplay playsinline muted class="w-100 h-100 object-fit-cover"></video>
                    
                    <!-- Scanner Overlay -->
                    <div class="scanner-overlay position-absolute top-0 start-0 w-100 h-100 pointer-events-none">
                        <!-- Dark Edges (Hole punched for center) -->
                        <div class="scanner-mask"></div>
                        
                        <!-- Alignment Brackets -->
                        <div class="scanner-lens">
                            <div class="bracket top-left"></div>
                            <div class="bracket top-right"></div>
                            <div class="bracket bottom-left"></div>
                            <div class="bracket bottom-right"></div>
                            
                            <!-- Scanning Line -->
                            <div class="scanner-line"></div>
                        </div>

                        <!-- Real-time Instruction -->
                        <div class="scanner-instruction position-absolute w-100 text-center" style="bottom: 20%;">
                            <div class="d-inline-block px-4 py-2 rounded-pill bg-dark bg-opacity-75 text-white shadow-lg border border-light border-opacity-10" id="scanner-feedback">
                                <i class="fas fa-arrows-alt me-2 text-info"></i> Align check and hold steady
                            </div>
                        </div>
                    </div>

                    <!-- Shutter Flash -->
                    <div id="shutter-flash" class="position-absolute top-0 start-0 w-100 h-100 bg-white" style="z-index: 110; display: none;"></div>
                </div>

                <!-- Controls -->
                <div class="modal-footer border-0 justify-content-center bg-dark p-4 position-relative" style="z-index: 105;">
                    <div class="d-flex align-items-center gap-5">
                        <button type="button" class="btn btn-link text-white text-decoration-none" onclick="triggerUploadFallback()">
                            <i class="fas fa-file-upload fa-lg"></i><br>
                            <small class="opacity-75">Upload</small>
                        </button>

                        <div class="shutter-container">
                            <button type="button" class="btn btn-light rounded-circle shutter-btn shadow-lg" id="btn-snapshot" onclick="takeSnapshot()">
                                <div class="shutter-inner"></div>
                            </button>
                            <!-- Countdown Spinner -->
                            <svg class="countdown-svg d-none" viewBox="0 0 80 80">
                                <circle cx="40" cy="40" r="36" />
                            </svg>
                        </div>

                        <button type="button" class="btn btn-link text-white text-decoration-none" onclick="toggleFlash()">
                            <i class="fas fa-bolt fa-lg" id="flash-icon"></i><br>
                            <small class="opacity-75">Flash</small>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <canvas id="snapshotCanvas" class="d-none"></canvas>

    <!-- History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered shadow-lg">
            <div class="modal-content" style="border-radius: 20px;">
                <div class="modal-header border-bottom-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">Deposit History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    @if($deposits->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <tbody>
                                @foreach($deposits as $deposit)
                                    <tr class="border-bottom">
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 text-primary" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-camera"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $deposit->created_at->format('M d, Y') }}</div>
                                                    <div class="small text-muted text-capitalize">
                                                        {{ $deposit->account_name }} 
                                                        @if($deposit->account_number)
                                                            <span class="text-xs">(...{{ substr($deposit->account_number, -4) }})</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4 py-3">
                                            <div class="fw-bold text-dark">+{{ setting('currency_symbol') }} {{ number_format($deposit->amount, 2) }}</div>
                                            @if($deposit->status == 'pending')
                                                <span class="badge bg-warning text-dark bg-opacity-25 rounded-pill px-3">Pending</span>
                                            @elseif($deposit->status == 'approved')
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Approved</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Rejected</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" alt="No Data" style="height: 60px; opacity: 0.5;" class="mb-3">
                        <p class="text-muted small mb-0">No past deposits found.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
    .scanner-mask {
        position: absolute;
        width: 100%;
        height: 100%;
        box-shadow: inset 0 0 0 2000px rgba(0,0,0,0.6);
        z-index: 100;
    }
    .scanner-lens {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 85%;
        height: 50%;
        z-index: 101;
        pointer-events: none;
    }
    .bracket {
        position: absolute;
        width: 30px;
        height: 30px;
        border: 4px solid #fff;
        border-radius: 4px;
    }
    .top-left { top: 0; left: 0; border-right: 0; border-bottom: 0; }
    .top-right { top: 0; right: 0; border-left: 0; border-bottom: 0; }
    .bottom-left { bottom: 0; left: 0; border-right: 0; border-top: 0; }
    .bottom-right { bottom: 0; right: 0; border-left: 0; border-top: 0; }

    .scanner-line {
        position: absolute;
        width: 100%;
        height: 2px;
        background: rgba(0, 174, 239, 0.5);
        box-shadow: 0 0 15px #00aeef;
        top: 0;
        animation: scan 3s linear infinite;
    }
    @keyframes scan {
        0% { top: 0; }
        50% { top: 100%; }
        100% { top: 0; }
    }

    .shutter-btn {
        width: 75px;
        height: 75px;
        padding: 0;
        border: 4px solid rgba(255,255,255,0.3);
        background: rgba(255,255,255,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .shutter-btn:active { transform: scale(0.9); }
    .shutter-inner {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 2px solid #000;
    }

    .countdown-svg {
        position: absolute;
        top: 0;
        left: 0;
        width: 75px;
        height: 75px;
        transform: rotate(-90deg);
        pointer-events: none;
    }
    .countdown-svg circle {
        fill: none;
        stroke: #00aeef;
        stroke-width: 4;
        stroke-dasharray: 226;
        stroke-dashoffset: 226;
        transition: stroke-dashoffset 3s linear;
    }
    .counting .countdown-svg circle {
        stroke-dashoffset: 0;
    }

    @keyframes flash {
        0% { opacity: 0; }
        50% { opacity: 1; }
        100% { opacity: 0; }
    }
    .flash-animate {
        display: block !important;
        animation: flash 0.2s ease-out;
    }
</style>
@endsection

@section('script')
<script>
    let currentSide = null;
    let stream = null;
    let autoSnapTimer = null;
    const cameraModal = new bootstrap.Modal(document.getElementById('cameraModal'));
    
    // Robust context detection
    const isIOS = /iPad|iPhone|iPod/.test(navigator.platform) || (navigator.userAgent.includes("Mac") && navigator.maxTouchPoints > 1);
    const isNativeApp = !!window.Capacitor;

    function openCamera(side) {
        currentSide = side;
        
        // Update labels
        document.getElementById('scanner-title').textContent = (side === 'front' ? 'Front of Check' : 'Back of Check');
        document.getElementById('scanner-subtitle').textContent = (side === 'front' ? 'Place on dark, flat surface' : 'Ensure endorsement is visible');
        document.getElementById('scanner-feedback').innerHTML = '<i class="fas fa-arrows-alt me-2 text-info"></i> Align check and hold steady';

        // Fallback for non-supported or iOS (native picker is better for iOS Safari)
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || (isIOS && !isNativeApp)) {
            document.getElementById(side + '_image_file').click();
            return;
        }

        cameraModal.show();
        startCameraStream();
    }

    let isCameraStarting = false;

    async function startCameraStream() {
        if (isCameraStarting) return;
        isCameraStarting = true;

        const video = document.getElementById('videoStream');
        
        // Ensure everything is clean
        stopCamera();

        const constraints = {
            video: { 
                facingMode: 'environment',
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            }
        };

        try {
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;
            
            video.onloadedmetadata = () => {
                // Defensive play() for autoplay restrictions
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.then(_ => {
                        // Start Auto-Snap logic after a short settlement delay
                        clearTimeout(autoSnapTimer);
                        autoSnapTimer = setTimeout(startAutoSnapCountdown, 1500);
                    }).catch(error => {
                        console.error("Autoplay blocked:", error);
                        // Show a placeholder or instructions if needed
                    });
                }
            };
        } catch (err) {
            console.error("Camera access denied:", err);
            notify('Camera access failed. Please use manual upload.', 'error');
            triggerUploadFallback();
        } finally {
            isCameraStarting = false;
        }
    }

    function startAutoSnapCountdown() {
        const feedback = document.getElementById('scanner-feedback');
        const container = document.querySelector('.shutter-container');
        
        feedback.innerHTML = '<i class="fas fa-hourglass-half me-2 text-warning border-0"></i> Capturing in 3s... Hold Steady';
        container.classList.add('counting');
        
        setTimeout(() => {
            if (cameraModal._isShown) takeSnapshot();
        }, 3000);
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => {
                track.stop();
            });
            stream = null;
        }
        clearTimeout(autoSnapTimer);
        document.querySelector('.shutter-container').classList.remove('counting');
    }

    function takeSnapshot() {
        const video = document.getElementById('videoStream');
        const canvas = document.getElementById('snapshotCanvas');
        const flash = document.getElementById('shutter-flash');
        
        // Shutter effect
        flash.classList.add('flash-animate');
        setTimeout(() => flash.classList.remove('flash-animate'), 200);

        // Haptic feedback (If native)
        if (window.Capacitor && window.Capacitor.isPluginAvailable('Haptics')) {
            window.Capacitor.Plugins.Haptics.impact({ style: 'heavy' });
        }

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
        document.getElementById(currentSide + '_image_base64').value = dataUrl;
        
        // Update UI
        const previewImg = document.getElementById(currentSide + '_preview');
        const previewContainer = document.getElementById(currentSide + '_preview_container');
        previewImg.src = dataUrl;
        previewContainer.classList.remove('d-none');
        
        const box = previewContainer.closest('.deposit-capture-box');
        box.style.borderColor = '#28a745';
        box.style.background = '#f8fff9';
        
        const statusText = box.querySelector('.status-text');
        statusText.innerHTML = '<i class="fas fa-check-circle me-1"></i> Captured <a href="javascript:void(0)" onclick="event.stopPropagation(); triggerUploadFallback()" class="ms-2 small text-decoration-underline text-success">Retake</a>';
        statusText.classList.replace('text-muted', 'text-success');

        cameraModal.hide();
        stopCamera();
    }

    async function toggleFlash() {
        const icon = document.getElementById('flash-icon');
        if (stream) {
            const track = stream.getVideoTracks()[0];
            const capabilities = track.getCapabilities();
            
            if (capabilities.torch) {
                try {
                    const isFlashOn = track.getSettings().torch || false;
                    await track.applyConstraints({
                        advanced: [{ torch: !isFlashOn }]
                    });
                    icon.className = !isFlashOn ? 'fas fa-bolt fa-lg text-warning' : 'fas fa-bolt fa-lg';
                } catch (e) {
                    console.error("Flash error:", e);
                    notify('Flash control failed', 'error');
                }
            } else {
                notify('Flash not supported on this lens', 'info');
            }
        }
    }

    function triggerUploadFallback() {
        const side = currentSide || 'front';
        cameraModal.hide();
        stopCamera();
        document.getElementById(side + '_image_file').click();
    }

    // Modal hide safety
    document.getElementById('cameraModal').addEventListener('hidden.bs.modal', stopCamera);

    // Manual Upload logic maintained
    ['front', 'back'].forEach(side => {
        document.getElementById(side + '_image_file').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById(side + '_image_base64').value = '';
                    const previewImg = document.getElementById(side + '_preview');
                    const previewContainer = document.getElementById(side + '_preview_container');
                    previewImg.src = e.target.result;
                    previewContainer.classList.remove('d-none');
                    const box = previewContainer.closest('.deposit-capture-box');
                    box.style.borderColor = '#28a745';
                    const st = box.querySelector('.status-text');
                    st.innerHTML = '<i class="fas fa-check-circle me-1"></i> Uploaded <a href="javascript:void(0)" onclick="event.stopPropagation(); triggerUploadFallback()" class="ms-2 small text-decoration-underline text-success">Retake</a>';
                    st.classList.replace('text-muted', 'text-success');
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>
@endsection
