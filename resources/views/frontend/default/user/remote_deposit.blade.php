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
                                <input type="file" name="front_image" id="front_image_file" class="d-none" accept="image/*" capture="environment">
                                
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
                                <input type="file" name="back_image" id="back_image_file" class="d-none" accept="image/*" capture="environment">

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
            <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow">
                Submit deposit
            </button>
        </form>
    </div>

    <!-- Camera Modal -->
    <div class="modal fade" id="cameraModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content overflow-hidden" style="background: #000; border: none;">
                <div class="modal-header border-0 position-absolute top-0 start-0 w-100" style="z-index: 10;">
                    <h5 class="modal-title text-white">Capture Check</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="stopCamera()"></button>
                </div>
                <div class="modal-body p-0 position-relative d-flex align-items-center justify-content-center bg-black" style="min-height: 70vh;">
                    <div id="cameraStatus" class="position-absolute text-center text-white p-4" style="z-index: 5;">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="mb-3">Configuring camera...</p>
                        <button type="button" class="btn btn-primary rounded-pill px-4 d-none" id="retryCameraButton" onclick="startCameraStream()">
                            Grant Permission
                        </button>
                    </div>
                    <video id="videoStream" autoplay playsinline muted class="w-100 h-100 object-fit-cover d-none"></video>
                    <div class="camera-overlay position-absolute top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center pointer-events-none" id="cameraOverlay">
                         <div class="check-guide" style="width: 85%; height: 50%; border: 2px dashed rgba(255,255,255,0.6); border-radius: 15px;"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center bg-dark p-4 d-none" id="cameraActions">
                    <button type="button" class="btn btn-light rounded-circle p-3 shadow-lg" onclick="takeSnapshot()" style="width: 70px; height: 70px;">
                        <i class="fas fa-camera fa-2x"></i>
                    </button>
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
    .fw-600 { font-weight: 600; }
    .deposit-capture-box { cursor: pointer; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
    .deposit-capture-box:active { transform: scale(0.97); }
    .deposit-capture-box:hover {
        border-color: #00549b !important;
        background-color: #f0f7ff !important;
    }
    .check-guide {
        box-shadow: 0 0 0 9999px rgba(0,0,0,0.5);
    }
</style>
@endsection

@section('script')
<script>
    let currentSide = null;
    let stream = null;
    let cameraTimeout = null;
    const cameraModal = new bootstrap.Modal(document.getElementById('cameraModal'));

    async function openCamera(side) {
        currentSide = side;
        stopCamera();
        
        // Reset UI
        document.getElementById('cameraStatus').classList.remove('d-none');
        document.getElementById('retryCameraButton').classList.add('d-none');
        document.getElementById('videoStream').classList.add('d-none');
        document.getElementById('cameraOverlay').classList.add('d-none');
        document.getElementById('cameraActions').classList.add('d-none');

        cameraModal.show();
        
        // Try auto-start
        setTimeout(startCameraStream, 500);
    }

    async function startCameraStream() {
        const video = document.getElementById('videoStream');
        const status = document.getElementById('cameraStatus');
        const retryBtn = document.getElementById('retryCameraButton');
        
        const constraints = {
            video: { 
                facingMode: 'environment',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        };

        try {
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;
            
            video.onloadedmetadata = () => {
                video.play().then(() => {
                    // Success UI
                    status.classList.add('d-none');
                    video.classList.remove('d-none');
                    document.getElementById('cameraOverlay').classList.remove('d-none');
                    document.getElementById('cameraActions').classList.remove('d-none');
                }).catch(e => {
                    console.warn("Play failed:", e);
                    retryBtn.classList.remove('d-none');
                    status.querySelector('p').innerText = "Permission needed to start video.";
                    status.querySelector('.spinner-border').classList.add('d-none');
                });
            };
        } catch (err) {
            console.error("Camera error:", err);
            status.querySelector('p').innerText = "Unable to access camera.";
            status.querySelector('.spinner-border').classList.add('d-none');
            retryBtn.classList.remove('d-none');
            // If it's a hard error (not found), show fallback link
            if(err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                 triggerUploadFallback();
            }
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => {
                track.stop();
                console.log("Track stopped:", track.label);
            });
            stream = null;
        }
        const video = document.getElementById('videoStream');
        if (video) video.srcObject = null;
    }

    // Modal hide event to ensure camera stops
    document.getElementById('cameraModal').addEventListener('hidden.bs.modal', stopCamera);

    function takeSnapshot() {
        const video = document.getElementById('videoStream');
        const canvas = document.getElementById('snapshotCanvas');
        const context = canvas.getContext('2d');

        // Capture at high resolution
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Compress and Convert to Base64
        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
        document.getElementById(currentSide + '_image_base64').value = dataUrl;
        
        // Update UI Preview
        const previewImg = document.getElementById(currentSide + '_preview');
        const previewContainer = document.getElementById(currentSide + '_preview_container');
        previewImg.src = dataUrl;
        previewContainer.classList.remove('d-none');
        
        const box = previewContainer.closest('.deposit-capture-box');
        box.style.borderColor = '#28a745';
        box.style.background = '#f8fff9';
        
        const statusText = box.querySelector('.status-text');
        statusText.innerHTML = '<i class="fas fa-check-circle me-1"></i> Captured';
        statusText.classList.replace('text-muted', 'text-success');

        cameraModal.hide();
        stopCamera();
    }

    function triggerUploadFallback() {
        const side = currentSide || 'front';
        cameraModal.hide();
        stopCamera();
        document.getElementById(side + '_image_file').click();
    }

    // Manual Upload Preview Logic
    ['front', 'back'].forEach(side => {
        document.getElementById(side + '_image_file').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                // Clear any base64 if user switches to manual upload
                document.getElementById(side + '_image_base64').value = '';

                const reader = new FileReader();
                reader.onload = function(event) {
                    const previewImg = document.getElementById(side + '_preview');
                    const previewContainer = document.getElementById(side + '_preview_container');
                    previewImg.src = event.target.result;
                    previewContainer.classList.remove('d-none');
                    
                    const box = previewContainer.closest('.deposit-capture-box');
                    box.style.borderColor = '#28a745';
                    box.style.background = '#f8fff9';
                    
                    const statusText = box.querySelector('.status-text');
                    statusText.innerHTML = '<i class="fas fa-check-circle me-1"></i> Uploaded';
                    statusText.classList.replace('text-muted', 'text-success');
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>
@endsection
