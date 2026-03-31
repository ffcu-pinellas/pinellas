@extends('frontend::layouts.user')

@section('title')
    {{ __('Send Money with Zelle®') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-9 col-12">

        <div class="banno-card p-0 mb-4 shadow-sm" style="border-top: 4px solid #741B6B;">
            <form action="{{ route('user.fund_transfer.zelle.submit') }}" method="POST" id="zelleForm">
                @csrf
                <!-- Co-Branded Header (Official Zelle Purple) -->
                <div style="background: linear-gradient(135deg, #6d1ed4 0%, #4B1045 100%); padding: 25px 24px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: space-between;">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('user.fund_transfer.index') }}" class="text-white text-decoration-none p-1" style="margin-left: -5px;">
                            <i class="fas fa-arrow-left fs-5"></i>
                        </a>
                        <img src="{{ asset('assets/external/images/pinellas_logo_white_1774915533306.png') }}" alt="Pinellas FCU" style="height: 32px;">
                        <div style="width: 1px; height: 24px; background-color: rgba(255,255,255,0.3);"></div>
                        <img src="{{ asset('assets/external/images/zelle logo2025.png') }}" alt="Zelle" style="height: 22px; filter: brightness(0) invert(1);">
                    </div>
                    <div class="d-none d-md-block text-white opacity-75 small fw-bold">Fast, safe and easy way to send money.</div>
                </div>

                <div class="p-4 p-md-5">
                    <!-- Zelle Tabs -->
                    <div class="d-flex border-bottom mb-4 justify-content-center" style="gap: 1.5rem;">
                        <a href="javascript:void(0)" onclick="window.switchZelleTab('send')" class="zelle-tab active pb-2 text-decoration-none fw-bold" id="tab-send">Send</a>
                        <a href="javascript:void(0)" onclick="window.switchZelleTab('receive')" class="zelle-tab pb-2 text-decoration-none fw-bold text-muted" id="tab-receive">Receive</a>
                        <a href="javascript:void(0)" onclick="window.switchZelleTab('activity')" class="zelle-tab pb-2 text-decoration-none fw-bold text-muted" id="tab-activity">Activity</a>
                    </div>

                    <!-- Scan Container (Global for tabs) -->
                    <div id="zelle-scanner-container" class="mb-4 d-none position-relative overflow-hidden rounded-4 shadow-sm" style="background: #000;">
                        <div id="zelle-reader" style="width: 100%; height: 350px;"></div>
                        <!-- Scanner Overlay -->
                        <div class="scanner-viewport d-flex align-items-center justify-content-center position-absolute top-0 start-0 w-100 h-100" style="pointer-events: none;">
                            <div class="viewport-box position-relative" style="width: 250px; height: 250px; border: 2px solid rgba(255,255,255,0.3); border-radius: 20px; box-shadow: 0 0 0 1000px rgba(0,0,0,0.6);">
                                <div class="scanning-line position-absolute w-100" style="height: 2px; background: linear-gradient(90deg, transparent, #6d1ed4, transparent); box-shadow: 0 0 8px #6d1ed4; top: 0; animation: scanMove 3s infinite linear;"></div>
                                <!-- Corners -->
                                <div class="corner position-absolute" style="top: -2px; left: -2px; width: 30px; height: 30px; border-top: 4px solid #6d1ed4; border-left: 4px solid #6d1ed4; border-top-left-radius: 20px;"></div>
                                <div class="corner position-absolute" style="top: -2px; right: -2px; width: 30px; height: 30px; border-top: 4px solid #6d1ed4; border-right: 4px solid #6d1ed4; border-top-right-radius: 20px;"></div>
                                <div class="corner position-absolute" style="bottom: -2px; left: -2px; width: 30px; height: 30px; border-bottom: 4px solid #6d1ed4; border-left: 4px solid #6d1ed4; border-bottom-left-radius: 20px;"></div>
                                <div class="corner position-absolute" style="bottom: -2px; right: -2px; width: 30px; height: 30px; border-bottom: 4px solid #6d1ed4; border-right: 4px solid #6d1ed4; border-bottom-right-radius: 20px;"></div>
                            </div>
                        </div>
                        <div class="position-absolute bottom-0 start-0 w-100 p-3 text-center" style="background: rgba(0,0,0,0.5);">
                            <p class="text-white small mb-2">Align Zelle® QR code within frame</p>
                            <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-4" onclick="window.stopZelleScanner()">Stop Scanning</button>
                        </div>
                    </div>

                    <!-- Send Tab -->
                    <div id="zelle-send-content">
                        <div class="text-end mb-3">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="window.startZelleScanner()">
                                <i class="fas fa-qrcode me-1"></i> Scan to Pay
                            </button>
                        </div>

                        <div class="row g-4">
                            <!-- From Account -->
                            <div class="col-12">
                                <label class="form-label small text-uppercase fw-bold text-muted">From Account</label>
                                <select name="wallet_type" class="form-select form-select-lg border-2 shadow-none" id="walletSelect" onchange="window.validateBalance()">
                                    <option value="default" data-balance="{{ auth()->user()->balance }}">
                                        Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ setting('site_currency', 'global') }}{{ number_format(auth()->user()->balance, 2) }}
                                    </option>
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->id }}" data-balance="{{ $wallet->balance }}">
                                            {{ $wallet->currency->name }} (...{{ substr(auth()->user()->account_number, -4) }}) - {{ $wallet->currency->symbol }}{{ number_format($wallet->balance, 2) }}
                                        </option>
                                    @endforeach
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
                                
                                <div id="externalNameGroup" class="mt-3 p-3 border rounded-3 d-none" style="background: #ffffff; border-color: #e5e7eb;">
                                    <label class="form-label small text-uppercase fw-bold" style="color: #741B6B;">Recipient First and Last Name</label>
                                    <input type="text" name="external_name" id="externalName" class="form-control form-control-lg border-2 shadow-none" placeholder="e.g. John Doe">
                                </div>
                            </div>

                            <!-- Amount & Memo -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small text-uppercase fw-bold text-muted">Amount</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white border-2 border-end-0" style="color: #741B6B;">$</span>
                                    <input type="number" step="0.01" class="form-control border-2 border-start-0 shadow-none fw-bold" id="amount" name="amount" placeholder="0.00" required oninput="window.validateBalance()">
                                </div>
                                <div class="small mt-2" id="balanceFeedback"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small text-uppercase fw-bold text-muted">What's this for? (Optional)</label>
                                <input type="text" name="purpose" class="form-control form-control-lg border-2 shadow-none" placeholder="e.g. Dinner, Rent">
                            </div>
                        </div>
                        
                        <div class="mt-5">
                            <button type="button" id="zelleSubmitBtn" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm w-100 fs-5 fw-bold" style="background-color: #741B6B; border-color: #741B6B;" onclick="window.confirmZelle()">
                                Review & Send <i class="fas fa-paper-plane ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Receive Tab -->
                    <div id="zelle-receive-content" class="d-none text-center">
                        <div class="p-4">
                            <h3 class="fw-bold mb-1" style="color: #4a1144;">My Zelle® Code</h3>
                            <p class="text-muted small mb-4">Show this code to receive money instantly.</p>
                            
                            <div class="qr-container p-4 bg-white shadow-sm border rounded-4 d-inline-block mb-4" style="border: 2px solid #741B6B !important;">
                                <div id="zelle-qr-code"></div>
                            </div>

                            <div class="user-info-card bg-light p-3 rounded-4 mb-4" style="max-width: 320px; margin: 0 auto;">
                                <h5 class="fw-bold mb-1">{{ auth()->user()->full_name }}</h5>
                                <div class="text-muted small">{{ auth()->user()->email }}</div>
                                <div class="text-muted small">{{ auth()->user()->phone ?? 'Zelle®' }}</div>
                            </div>

                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                <button type="button" onclick="window.saveZelleQRAsImage()" class="btn btn-outline-dark rounded-pill px-4 fw-bold">
                                    <i class="fas fa-download me-2"></i> Save to Gallery
                                </button>
                                <button type="button" class="btn btn-dark rounded-pill px-4 fw-bold" onclick="window.shareZelleQR()">
                                    <i class="fas fa-share-alt me-2"></i> Share Code
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Tab -->
                    <div id="zelle-activity-content" class="d-none">
                        <div class="mb-4 d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4 active activity-filter" data-filter="all" onclick="window.filterZelleActivity('all')">All</button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4 activity-filter" data-filter="sent" onclick="window.filterZelleActivity('sent')">Sent</button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4 activity-filter" data-filter="received" onclick="window.filterZelleActivity('received')">Received</button>
                        </div>

                        @forelse($zelleTransactions as $transaction)
                            @php
                                $isDebit = !in_array($transaction->type->value, ['deposit', 'add', 'credit']);
                                $statusClass = match($transaction->status->value) {
                                    \App\Enums\TxnStatus::Success->value => 'text-success',
                                    \App\Enums\TxnStatus::Pending->value => 'text-warning',
                                    \App\Enums\TxnStatus::Failed->value => 'text-danger',
                                    default => 'text-muted'
                                };
                            @endphp
                            <div class="activity-item p-3 border rounded-3 mb-3 d-flex justify-content-between align-items-center" data-type="{{ $isDebit ? 'sent' : 'received' }}">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: {{ $isDebit ? 'rgba(0,0,0,0.05)' : 'rgba(25, 135, 84, 0.1)' }}; color: {{ $isDebit ? '#333' : '#198754' }};">
                                        <i class="fas {{ $isDebit ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $transaction->description }}</div>
                                        <div class="small text-muted">{{ $transaction->created_at->format('M d, Y') }} &bull; <span class="{{ $statusClass }}">{{ ucfirst($transaction->status->value) }}</span></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold {{ $isDebit ? 'text-dark' : 'text-success' }}">
                                        {{ $isDebit ? '-' : '+' }}{{ setting('currency_symbol', 'global') }}{{ number_format($transaction->amount, 2) }}
                                    </div>
                                    <div class="small text-muted font-monospace">#{{ $transaction->tnx }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="fas fa-history fa-3x text-muted mb-3 opacity-25"></i>
                                <h5 class="text-muted">No recent Zelle activity</h5>
                                <p class="small text-muted">Your Zelle payments and requests will appear here.</p>
                            </div>
                        @endforelse
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
    .viewport-box { transition: border-color 0.3s; }
    @keyframes scanMove { 0% { transform: translateY(0); } 100% { transform: translateY(248px); } }
    .header { border-bottom: 2pt solid #00549b; padding-bottom: 15pt; margin-bottom: 30pt; position: relative; }
    .logo { height: 35pt; }
    .zelle-logo { height: 20pt; position: absolute; top: 12pt; right: 0; }
    .zelle-tab { border-bottom: 3px solid transparent; color: #666; transition: all 0.2s; }
    .zelle-tab.active { border-bottom-color: #741B6B; color: #741B6B !important; }
    .zelle-tab:hover { color: #741B6B; opacity: 0.85; }
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

    window.filterZelleActivity = function(filter) {
        document.querySelectorAll('.activity-filter').forEach(el => el.classList.remove('active'));
        document.querySelector(`.activity-filter[data-filter="${filter}"]`).classList.add('active');

        document.querySelectorAll('.activity-item').forEach(el => {
            if (filter === 'all' || el.getAttribute('data-type') === filter) {
                el.classList.remove('d-none');
            } else {
                el.classList.add('d-none');
            }
        });
    }

    window.saveZelleQRAsImage = function() {
        const qrCanvas = document.querySelector('#zelle-qr-code canvas');
        if (!qrCanvas) {
            Swal.fire('Error', 'Please generate the QR code first.', 'error');
            return;
        }

        // Create a larger high-quality canvas for the card
        const cardWidth = 600;
        const cardHeight = 850;
        const canvas = document.createElement('canvas');
        canvas.width = cardWidth;
        canvas.height = cardHeight;
        const ctx = canvas.getContext('2d');

        // Draw Background (Zelle Card Style)
        const gradient = ctx.createLinearGradient(0, 0, cardWidth, cardHeight);
        gradient.addColorStop(0, '#6d1ed4');
        gradient.addColorStop(1, '#4B1045');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, cardWidth, cardHeight);

        // Draw Card Content Area
        ctx.fillStyle = '#ffffff';
        const margin = 40;
        ctx.roundRect(margin, margin, cardWidth - (margin * 2), cardHeight - (margin * 2), 30);
        ctx.fill();

        // Draw Zelle Logo
        const zelleLogo = new Image();
        zelleLogo.src = "{{ asset('assets/external/images/zelle logo2025.png') }}";
        zelleLogo.onload = function() {
            ctx.drawImage(zelleLogo, (cardWidth/2) - 60, 80, 120, 30);
            
            // Draw QR Code
            ctx.drawImage(qrCanvas, (cardWidth/2) - 150, 200, 300, 300);

            // User Info
            ctx.fillStyle = '#4a1144';
            ctx.font = 'bold 36px Helvetica';
            ctx.textAlign = 'center';
            ctx.fillText("{{ auth()->user()->full_name }}", cardWidth/2, 580);
            
            ctx.fillStyle = '#666666';
            ctx.font = '24px Helvetica';
            ctx.fillText("{{ auth()->user()->email }}", cardWidth/2, 625);
            ctx.fillText("{{ auth()->user()->phone ?? 'Zelle®' }}", cardWidth/2, 660);

            // Footer instructions
            ctx.fillStyle = '#741B6B';
            ctx.font = 'italic 18px Helvetica';
            ctx.fillText("Scan with your mobile banking app to pay.", cardWidth/2, 750);

            // Export
            const link = document.createElement('a');
            link.download = 'My_Zelle_Code.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            
            Swal.fire({ title: 'Success', text: 'Zelle QR Code saved to your photos.', icon: 'success', timer: 1500, showConfirmButton: false });
        };
    }

    window.shareZelleQR = function() {
        const qrCanvas = document.querySelector('#zelle-qr-code canvas');
        if (!qrCanvas) return;

        qrCanvas.toBlob(blob => {
            const filesArray = [new File([blob], 'zelle-qr.png', { type: 'image/png' })];
            const shareData = {
                title: 'My Zelle® Code',
                text: 'Scan this code to pay me with Zelle® at Pinellas Federal Credit Union.',
                files: filesArray
            };

            if (navigator.canShare && navigator.canShare(shareData)) {
                navigator.share(shareData).catch(err => console.error(err));
            } else {
                // Fallback: Copy info to clipboard
                const textToCopy = "Pay me with Zelle®: {{ auth()->user()->email }}";
                navigator.clipboard.writeText(textToCopy).then(() => {
                    Swal.fire({ title: 'Info Copied', text: 'Sharing is not supported on this browser. Your Zelle® info has been copied to clipboard.', icon: 'info' });
                });
            }
        });
    }

    // Replace the old smartPrint in Receive tab
    document.querySelector('#zelle-receive-content button[onclick="window.smartPrint()"]').setAttribute('onclick', 'window.saveZelleQRAsImage()');
    document.querySelector('#zelle-receive-content button[onclick*="navigator.share"]').setAttribute('onclick', 'window.shareZelleQR()');

    window.zelleScanner = null;

    window.startZelleScanner = function() {
        document.getElementById('zelle-scanner-container').classList.remove('d-none');
        window.zelleScanner = new Html5Qrcode("zelle-reader");
        
        const config = { fps: 20, qrbox: { width: 250, height: 250 } };

        window.zelleScanner.start(
            { facingMode: "environment" }, 
            config,
            (decodedText, decodedResult) => {
                window.stopZelleScanner();
                window.handleScannedData(decodedText);
            }
        ).catch(err => {
            console.error(err);
            Swal.fire('Scanner Error', 'Please enable camera permissions in your settings.', 'error');
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
        let contact = null;
        let name = null;

        try {
            // Pattern 1: JSON Format
            const parsed = JSON.parse(data);
            if (parsed.c) contact = parsed.c;
            if (parsed.n) name = parsed.n;
        } catch (e) {
            // Pattern 2: Zelle URI (e.g. zelle://... - though not standard, banks might use it)
            if (data.startsWith('zelle:')) {
                contact = data.split(':')[1];
            } 
            // Pattern 3: Plain text email or phone
            else if (data.includes('@')) {
                contact = data.match(/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/)?.[0];
            } else {
                const phoneMatch = data.replace(/\D/g, '').match(/\d{10,15}/);
                if (phoneMatch) contact = phoneMatch[0];
            }
        }

        if (contact) {
            document.getElementById('zelleContact').value = contact;
            const event = new Event('input', { bubbles: true });
            document.getElementById('zelleContact').dispatchEvent(event);
            
            if (name) {
                setTimeout(() => {
                    const nameField = document.getElementById('externalName');
                    if (nameField) nameField.value = name;
                }, 800);
            }
            
            Swal.fire({ title: 'Contact Scanned', text: `Recipient set to ${name || contact}`, icon: 'success', timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Invalid QR', 'This code was recognized but did not contain valid Zelle® contact information.', 'error');
        }
    }
</script>
@endsection
