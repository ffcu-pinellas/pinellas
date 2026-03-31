@extends('frontend::layouts.user')

@section('title')
    {{ __('Send Money with Zelle®') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-9 col-12">
        <div class="text-center mb-4 p-4 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #741B6B 0%, #4a1144 100%); color: white;">
            <div class="d-flex align-items-center justify-content-center mb-2">
                <a href="{{ route('user.fund_transfer.index') }}" class="back-nav-link m-0 me-3" style="color: rgba(255,255,255,0.9);">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <img src="{{ asset('assets/external/images/zelle logo2025.png') }}" alt="Zelle" style="height: 38px; margin-top: -5px;">
            </div>
            <p class="small mb-0" style="color: rgba(255,255,255,0.8);">Fast, safe and easy way to send money.</p>
        </div>

        <div class="banno-card p-0 mb-4 shadow-sm" style="border-top: 4px solid #741B6B;">
            <form action="{{ route('user.fund_transfer.zelle.submit') }}" method="POST" id="zelleForm">
                @csrf
            <div class="p-4 p-md-5">
                <!-- Zelle Tabs -->
                <div class="d-flex border-bottom mb-4 justify-content-center" style="gap: 1.5rem;">
                    <a href="javascript:void(0)" onclick="window.switchZelleTab('send')" class="zelle-tab active pb-2 text-decoration-none fw-bold" id="tab-send">Send</a>
                    <a href="javascript:void(0)" onclick="window.switchZelleTab('receive')" class="zelle-tab pb-2 text-decoration-none fw-bold text-muted" id="tab-receive">Receive</a>
                    <a href="javascript:void(0)" onclick="window.switchZelleTab('activity')" class="zelle-tab pb-2 text-decoration-none fw-bold text-muted" id="tab-activity">Activity</a>
                </div>

                <!-- Send Tab -->
                <div id="zelle-send-content">
                    <div class="text-end mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="window.startZelleScanner()">
                            <i class="fas fa-qrcode me-1"></i> Scan to Pay
                        </button>
                    </div>
                    
                    <div id="zelle-scanner-container" class="mb-4 d-none">
                        <div id="zelle-reader" style="width: 100%; border-radius: 12px; overflow: hidden;"></div>
                        <div class="text-center mt-2">
                            <button type="button" class="btn btn-link text-danger text-decoration-none small" onclick="window.stopZelleScanner()">Cancel Scan</button>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- From Account -->
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">From Account</label>
                            <select name="wallet_type" class="form-select form-select-lg border-2 shadow-none" id="walletSelect" onchange="window.validateBalance()">
                                @if($wallets->isEmpty())
                                    <option value="default" data-balance="{{ auth()->user()->balance }}">
                                        Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ setting('site_currency', 'global') }}{{ number_format(auth()->user()->balance, 2) }}
                                    </option>
                                @else
                                    <option value="default" data-balance="{{ auth()->user()->balance }}">
                                        Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ setting('site_currency', 'global') }}{{ number_format(auth()->user()->balance, 2) }}
                                    </option>
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->id }}" data-balance="{{ $wallet->balance }}">
                                            {{ $wallet->currency->name }} (...{{ substr(auth()->user()->account_number, -4) }}) - {{ $wallet->currency->symbol }}{{ number_format($wallet->balance, 2) }}
                                        </option>
                                    @endforeach
                                @endif
                                <option value="savings" data-balance="{{ auth()->user()->savings_balance }}">
                                    Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S) - {{ setting('site_currency', 'global') }}{{ number_format(auth()->user()->savings_balance, 2) }}
                                </option>
                            </select>
                        </div>

                        <!-- Recipient Verification Box -->
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">Send To (Email or U.S. Mobile Number)</label>
                            <div class="position-relative">
                                <input type="text" name="contact" id="zelleContact" class="form-control form-control-lg border-2 shadow-none" placeholder="e.g. johndoe@email.com or 555-123-4567" required autocomplete="off">
                                <div id="verifySpinner" class="spinner-border spinner-border-sm text-primary position-absolute d-none" style="right: 15px; top: 15px;" role="status"></div>
                            </div>
                            
                            <!-- Notice UI -->
                            <div id="zelleNoticeBox" class="mt-3 p-3 rounded-3 d-none" style="transition: all 0.3s; border-left: 4px solid #741B6B; background: #fdfafc;">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="icon-circle shadow-xs" style="width: 38px; height: 38px; background: #741B6B; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                        <i class="fas fa-check" id="noticeIcon"></i>
                                    </div>
                                    <div class="min-w-0" style="padding-top: 2px;">
                                        <div class="fw-bold" style="color: #4a1144;" id="noticeTitle">Enrolled with Zelle&reg;</div>
                                        <div class="small mt-1" style="color: #666;" id="noticeSub"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- External Contact Name Entry (Dynamic) -->
                            <div id="externalNameGroup" class="mt-3 p-3 border rounded-3 d-none" style="background: #ffffff; border-color: #e5e7eb;">
                                <label class="form-label small text-uppercase fw-bold" style="color: #741B6B;">Recipient First and Last Name</label>
                                <p class="small text-muted mb-2">For your security, we require the legal name of the recipient before enrolling them as a trusted Zelle® contact.</p>
                                <input type="text" name="external_name" id="externalName" class="form-control form-control-lg border-2 shadow-none" placeholder="e.g. John Doe">
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">Amount</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-2 border-end-0" style="color: #741B6B;">$</span>
                                <input type="number" step="0.01" class="form-control border-2 border-start-0 shadow-none fw-bold" id="amount" name="amount" placeholder="0.00" required oninput="window.validateBalance()">
                            </div>
                            <div class="small mt-2 d-flex justify-content-between">
                                <span id="balanceFeedback"></span>
                                <span class="text-muted"><i class="fas fa-info-circle"></i> Remaining Limit: ${{ number_format($zelleDailyLimit, 2) }}</span>
                            </div>
                        </div>

                        <!-- Memo -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-uppercase fw-bold text-muted">What's this for? (Optional)</label>
                            <input type="text" name="purpose" class="form-control form-control-lg border-2 shadow-none" placeholder="e.g. Dinner, Rent">
                        </div>
                    </div>
                    
                    <div class="mt-3 p-3 rounded bg-light border">
                        <p class="small text-muted mb-0"><strong>Important:</strong> Zelle® should only be used to send money to friends, family or others you trust. We do not provide purchase protection for Zelle® payments. Transfers are typically available within minutes and cannot be canceled.</p>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="button" id="zelleSubmitBtn" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm w-100 fs-5 fw-bold" style="background-color: #741B6B; border-color: #741B6B;" onclick="window.confirmZelle()">
                            Review & Send <i class="fas fa-paper-plane ms-2"></i>
                        </button>
                        </div>
                    </div>
                </div>

                <!-- Receive Tab -->
                <div id="zelle-receive-content" class="d-none text-center">
                    <div class="p-4">
                        <div class="mb-4 d-inline-block p-4 rounded-circle" style="background: rgba(116, 27, 107, 0.05);">
                            <img src="{{ asset('assets/external/images/zelle small logo.png') }}" style="height: 54px;">
                        </div>
                        <h3 class="fw-bold mb-1" style="color: #741B6B;">My Zelle® Code</h3>
                        <p class="text-muted small mb-4">Show this code to a friend to receive money instantly.</p>
                        
                        <div class="qr-container p-4 bg-white shadow-sm border rounded-4 d-inline-block mb-4" style="border: 2px solid #741B6B !important;">
                            <div id="zelle-qr-code"></div>
                        </div>

                        <div class="user-info-card bg-light p-3 rounded-4 mb-4" style="max-width: 320px; margin: 0 auto;">
                            <h5 class="fw-bold mb-1">{{ auth()->user()->full_name }}</h5>
                            <div class="text-muted small">{{ safe(auth()->user()->email) }}</div>
                            <div class="text-muted small">{{ auth()->user()->phone ?? 'No phone linked' }}</div>
                        </div>

                        <div class="d-flex justify-content-center gap-3">
                            <button type="button" onclick="window.smartPrint()" class="btn btn-outline-dark rounded-pill px-4 fw-bold">
                                <i class="fas fa-print me-2"></i> Print or Save
                            </button>
                            <button type="button" class="btn btn-dark rounded-pill px-4 fw-bold" onclick="navigator.share({ title: 'My Zelle Code', text: 'Scan to pay me with Zelle', url: window.location.href })">
                                <i class="fas fa-share-alt me-2"></i> Share
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Activity Tab -->
                <div id="zelle-activity-content" class="d-none">
                    <div class="text-center py-5">
                        <i class="fas fa-history fa-3x text-muted mb-3 opacity-25"></i>
                        <h5 class="text-muted">No recent Zelle activity</h5>
                        <p class="small text-muted">Your Zelle payments and requests will appear here.</p>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="text-center mt-4">
            <img src="{{ asset('assets/external/images/zelle logo2025.png') }}" alt="Zelle" style="max-height: 32px; opacity: 0.8; filter: grayscale(100%);">
            <p class="small text-muted mt-2">Zelle and the Zelle related marks are wholly owned by<br>Early Warning Services, LLC and are used herein under license.</p>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
    .form-control:focus, .form-select:focus {
        border-color: #741B6B !important;
        box-shadow: 0 0 0 0.25rem rgba(116, 27, 107, 0.1) !important;
    }
    .zelle-tab { border-bottom: 3px solid transparent; color: #666; transition: all 0.2s; }
    .zelle-tab.active { border-bottom-color: #741B6B; color: #741B6B !important; }
    .zelle-tab:hover { color: #741B6B; }
    .qr-container canvas { max-width: 100% !important; height: auto !important; }
</style>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    window.typingTimer = null;
    window.isZelleVerified = false;
    window.verifiedName = null;
    window.remainingDailyLimit = {{ $zelleDailyLimit }};
    
    document.addEventListener("DOMContentLoaded", function () {
        const contactInput = document.getElementById('zelleContact');
        if(contactInput) {
            contactInput.addEventListener('input', function (e) {
                let val = this.value;
                const digits = val.replace(/\D/g, '');
                
                // Smart Formatting: trigger on 4th consecutive digit to avoid email collision
                if (/^\d{4,}/.test(digits) || (digits.length >= 4 && !val.includes('@'))) {
                    if (digits.length > 0) {
                        if (digits.length <= 3) val = digits;
                        else if (digits.length <= 6) val = `(${digits.slice(0, 3)}) ${digits.slice(3)}`;
                        else val = `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 10)}`;
                        this.value = val;
                    }
                }

                clearTimeout(window.typingTimer);
                document.getElementById('zelleNoticeBox').classList.add('d-none');
                document.getElementById('externalNameGroup').classList.add('d-none');
                document.getElementById('externalName').removeAttribute('required');
                window.isZelleVerified = false;
                
                const searchVal = this.value.trim();
                if (searchVal.length >= 5) {
                    document.getElementById('verifySpinner').classList.remove('d-none');
                    window.typingTimer = setTimeout(() => window.verifyZelleNetwork(searchVal), 500);
                } else {
                    document.getElementById('verifySpinner').classList.add('d-none');
                }
            });
        }
    });

    window.verifyZelleNetwork = function(contact) {
        fetch('{{ route("user.fund_transfer.zelle.verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({contact: contact})
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('verifySpinner').classList.add('d-none');
            let noticeBox = document.getElementById('zelleNoticeBox');
            noticeBox.classList.remove('d-none');
            
            if (data.status === 'internal') {
                window.verifiedName = data.name;
                document.getElementById('noticeIcon').className = 'fas fa-shield-check';
                document.getElementById('noticeTitle').innerText = 'Enrolled with Zelle®';
                document.getElementById('noticeSub').innerHTML = `You are sending money to <strong>${data.name}</strong>`;
                document.getElementById('externalNameGroup').classList.add('d-none');
                document.getElementById('externalName').removeAttribute('required');
            } else if (data.status === 'external') {
                window.verifiedName = null;
                document.getElementById('noticeIcon').className = 'fas fa-user-plus';
                document.getElementById('noticeTitle').innerText = 'New Contact';
                document.getElementById('noticeSub').innerHTML = "This recipient isn't in your contacts. Please provide their name below to continue.";
                document.getElementById('externalNameGroup').classList.remove('d-none');
                document.getElementById('externalName').setAttribute('required', 'required');
            } else {
                noticeBox.classList.add('d-none');
                document.getElementById('externalNameGroup').classList.add('d-none');
                document.getElementById('externalName').removeAttribute('required');
            }
            window.isZelleVerified = true;
        })
        .catch(err => {
            console.error(err);
            document.getElementById('verifySpinner').classList.add('d-none');
        });
    }

    window.validateBalance = function() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const fromSelect = document.getElementById('walletSelect');
        const balance = parseFloat(fromSelect.options[fromSelect.selectedIndex].getAttribute('data-balance'));
        const feedback = document.getElementById('balanceFeedback');
        
        if (amount > window.remainingDailyLimit) {
            feedback.innerHTML = `<span class="text-danger small fw-bold">Daily limit exceeded ($${window.remainingDailyLimit.toLocaleString('en-US', {minimumFractionDigits:2})} remaining).</span>`;
            return false;
        } else if (amount > balance) {
            feedback.innerHTML = '<span class="text-danger small fw-bold">Insufficient funds available.</span>';
            return false;
        } else if (amount > 0) {
            feedback.innerHTML = '<span class="text-success small"><i class="fas fa-check"></i> Limit Verified</span>';
            return true;
        } else {
            feedback.innerHTML = '';
            return false;
        }
    }

    window.confirmZelle = function() {
        const contactInput = document.getElementById('zelleContact');
        const contact = contactInput.value.trim();
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        
        if (!contact || contact.length < 5) {
            Swal.fire({ title: 'Recipient Required', text: 'Please enter an Email or U.S Mobile Number', icon: 'warning', confirmButtonColor: '#741B6B' });
            return;
        }
        
        if (!window.validateBalance() || amount <= 0) {
            Swal.fire({ title: 'Invalid Amount', text: 'Please check your balance and daily limits.', icon: 'error', confirmButtonColor: '#741B6B' });
            return;
        }
        
        if (!window.isZelleVerified) {
            // Force immediate verify block rather than trapping them
            document.getElementById('zelleSubmitBtn').disabled = true;
            document.getElementById('zelleSubmitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verifying Zelle®...';
            
            fetch('{{ route("user.fund_transfer.zelle.verify") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({contact: contact})
            }).then(res => res.json()).then(data => {
                window.isZelleVerified = true;
                document.getElementById('zelleSubmitBtn').disabled = false;
                document.getElementById('zelleSubmitBtn').innerHTML = 'Review & Send <i class="fas fa-paper-plane ms-2"></i>';
                window.verifyZelleNetwork(contact); // Updates UI dynamically
                setTimeout(window.confirmZelle, 500); // Re-trigger modal
            }).catch(err => {
                document.getElementById('zelleSubmitBtn').disabled = false;
                document.getElementById('zelleSubmitBtn').innerHTML = 'Review & Send <i class="fas fa-paper-plane ms-2"></i>';
                Swal.fire('Error', 'Unable to reach the Zelle Network. Please try again.', 'error');
            });
            return;
        }

        const extName = document.getElementById('externalName').value.trim();
        const displayRecipientName = window.verifiedName || extName || 'Recipient';
        
        if (document.getElementById('externalName').hasAttribute('required') && !extName) {
            Swal.fire({ title: 'Name Required', text: 'Please provide the missing contact name.', icon: 'warning', confirmButtonColor: '#741B6B' });
            return;
        }

        Swal.fire({
            title: '<div class="pt-3" style="color: #4a1144; font-size: 1.5rem;">Review Payment</div>',
            html: `
                <div class="text-center px-2">
                    <div class="mb-4 d-inline-block p-3 rounded-circle" style="background: rgba(116, 27, 107, 0.1);">
                        <img src="{{ asset('assets/external/images/zelle small logo.png') }}" style="height: 48px;">
                    </div>
                    <div class="display-6 fw-bold mb-1" style="color: #741B6B;">$${amount.toFixed(2)}</div>
                    <div class="text-muted small text-uppercase fw-bold mb-4">Total Amount</div>
                    
                    <div class="text-start bg-light p-3 rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Recipient Name:</span>
                            <span class="fw-bold">${displayRecipientName}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted small">Recipient Contact:</span>
                            <span class="fw-bold">${contact}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">From Account:</span>
                            <span class="fw-bold small">${document.getElementById('walletSelect').options[document.getElementById('walletSelect').selectedIndex].text.split('-')[0]}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Network Fee:</span>
                            <span class="text-success fw-bold">FREE</span>
                        </div>
                    </div>
                    <div class="alert alert-warning border-0 small text-start py-2">
                        <i class="fas fa-exclamation-triangle me-1"></i> Zelle® payments are instant and cannot be reversed.
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#741B6B',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Send Money Now',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'rounded-pill px-4',
                cancelButton: 'rounded-pill px-4'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('zelleSubmitBtn').disabled = true;
                document.getElementById('zelleSubmitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
                SecurityGate.gate(document.getElementById('zelleForm'));
            }
        });
    }

    window.switchZelleTab = function(tab) {
        document.querySelectorAll('.zelle-tab').forEach(el => {
            el.classList.add('text-muted');
            el.classList.remove('active');
        });
        document.getElementById('tab-' + tab).classList.remove('text-muted');
        document.getElementById('tab-' + tab).classList.add('active');

        document.getElementById('zelle-send-content').classList.add('d-none');
        document.getElementById('zelle-receive-content').classList.add('d-none');
        document.getElementById('zelle-activity-content').classList.add('d-none');
        
        document.getElementById('zelle-' + tab + '-content').classList.remove('d-none');

        if(tab === 'receive') {
            window.generateZelleQR();
        }
    }

    window.generateZelleQR = function() {
        const qrContainer = document.getElementById('zelle-qr-code');
        if(qrContainer.innerHTML !== '') return; // Already generated

        const userData = {
            service: 'zelle',
            n: "{{ auth()->user()->full_name }}",
            c: "{{ auth()->user()->email }}",
            p: "{{ auth()->user()->phone ?? '' }}"
        };

        new QRCode(qrContainer, {
            text: JSON.stringify(userData),
            width: 200,
            height: 200,
            colorDark : "#741B6B",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    }

    window.smartPrint = function() {
        if (typeof window.print === 'function' && !/Mobi|Android|iPhone/i.test(navigator.userAgent)) {
            window.print();
        } else {
            window.print();
        }
    }

    window.zelleScanner = null;

    window.startZelleScanner = function() {
        document.getElementById('zelle-scanner-container').classList.remove('d-none');
        window.zelleScanner = new Html5Qrcode("zelle-reader");
        
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        window.zelleScanner.start(
            { facingMode: "environment" }, 
            config,
            (decodedText, decodedResult) => {
                window.stopZelleScanner();
                window.handleScannedData(decodedText);
            }
        ).catch(err => {
            console.error(err);
            Swal.fire('Scanner Error', 'Could not access camera. Please check permissions.', 'error');
            window.stopZelleScanner();
        });
    }

    window.stopZelleScanner = function() {
        if (window.zelleScanner) {
            window.zelleScanner.stop().then(() => {
                document.getElementById('zelle-scanner-container').classList.add('d-none');
            }).catch(err => console.error(err));
        }
    }

    window.handleScannedData = function(data) {
        try {
            const parsed = JSON.parse(data);
            if (parsed.service === 'zelle') {
                document.getElementById('zelleContact').value = parsed.c;
                if (parsed.n) {
                    // Populate name if it's an external contact check
                    // We trigger the input event to start the verification logic
                    const event = new Event('input', { bubbles: true });
                    document.getElementById('zelleContact').dispatchEvent(event);
                    
                    // Wait a bit for verification to start, then force name if needed
                    setTimeout(() => {
                        const nameField = document.getElementById('externalName');
                        if (nameField) nameField.value = parsed.n;
                    }, 800);
                }
                Swal.fire({ title: 'Contact Scanned', text: `Recipient set to ${parsed.n || parsed.c}`, icon: 'success', timer: 2000, showConfirmButton: false });
            } else {
                Swal.fire('Invalid QR', 'This does not appear to be a Zelle® QR code.', 'error');
            }
        } catch (e) {
            // Check if it's a plain email or phone
            if (data.includes('@') || /^\d{10,15}$/.test(data.replace(/\D/g, ''))) {
                 document.getElementById('zelleContact').value = data;
                 const event = new Event('input', { bubbles: true });
                 document.getElementById('zelleContact').dispatchEvent(event);
                 Swal.fire({ title: 'Contact Scanned', text: `Recipient set to ${data}`, icon: 'success', timer: 2000, showConfirmButton: false });
            } else {
                Swal.fire('Invalid QR', 'Could not recognize the scanned data.', 'error');
            }
        }
    }
</script>
@endsection
