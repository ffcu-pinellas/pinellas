@extends('frontend::layouts.user')

@php
    $fullName = auth()->user()->full_name;
    $nameParts = explode(' ', $fullName);
    $initials = strtoupper(substr($nameParts[0], 0, 1) . (count($nameParts) > 1 ? substr($nameParts[count($nameParts) - 1], 0, 1) : ''));
@endphp

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
                <div style="background: linear-gradient(135deg, #6d1ed4 0%, #4B1045 100%); padding: 30px 24px; border-bottom: 5px solid #741B6B;">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <a href="{{ route('user.fund_transfer.index') }}" class="text-white text-decoration-none p-1" style="margin-left: -5px;">
                            <i class="fas fa-arrow-left fs-5"></i>
                        </a>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ asset('assets/external/images/frontfield_logo_white_1774915533306.png') }}" alt="{{ setting('site_title', 'global') ?? 'FrontField Credit Union' }}" style="height: 30px;">
                            <div style="width: 1px; height: 22px; background-color: rgba(255,255,255,0.4);"></div>
                            <img src="{{ asset('assets/external/images/zelle logo2025.png') }}" alt="Zelle" style="height: 20px; filter: brightness(0) invert(1);">
                        </div>
                        <div style="width: 24px;"></div> <!-- Spacer to balance back button -->
                    </div>
                    
                    <!-- Prominent Header Divider -->
                    <div style="width: 50%; height: 1px; background: rgba(255,255,255,0.2); margin: 0 auto 15px auto;"></div>
                    
                    <div class="text-center text-white opacity-90 fw-bold" style="letter-spacing: 0.5px; font-size: 0.85rem;">
                        Fast, safe and easy way to send money.
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <!-- Zelle Tabs -->
                    <div class="d-flex border-bottom mb-4 justify-content-center" style="gap: 1.5rem;">
                        <a href="javascript:void(0)" onclick="window.switchZelleTab('send')" class="zelle-tab active pb-2 text-decoration-none fw-bold text-dark" id="tab-send">Send</a>
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
                    <div id="section-send" class="zelle-section">
                        <div class="text-end mb-3">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="window.startZelleScanner()">
                                <i class="fas fa-qrcode me-1"></i> Scan to Pay
                            </button>
                        </div>

                        <div class="row g-4">
                            <!-- From Account -->
                            <div class="col-12">
                                <label class="form-label small text-uppercase fw-bold text-muted">From Account</label>
                                <select name="wallet_type" class="form-select form-select-lg border-2 shadow-none" id="walletSelect" onchange="window.checkRestriction(); window.validateBalance()">
                                    <option value="default" data-balance="{{ auth()->user()->balance }}" @disabled(auth()->user()->isRestricted('checking'))>
                                        Checking (...{{ substr(auth()->user()->account_number, -4) }}) - {{ setting('site_currency', 'global') }}{{ number_format(auth()->user()->balance, 2) }}
                                        @if(auth()->user()->isRestricted('checking')) (Restricted) @endif
                                    </option>
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->id }}" data-balance="{{ $wallet->balance }}" @disabled(auth()->user()->isRestricted('checking'))>
                                            {{ $wallet->currency->name }} (...{{ substr(auth()->user()->account_number, -4) }}) - {{ $wallet->currency->symbol }}{{ number_format($wallet->balance, 2) }}
                                            @if(auth()->user()->isRestricted('checking')) (Restricted) @endif
                                        </option>
                                    @endforeach
                                    <option value="savings" data-balance="{{ auth()->user()->savings_balance }}" @disabled(auth()->user()->isRestricted('savings'))>
                                        Savings (...{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }}S) - {{ setting('site_currency', 'global') }}{{ number_format(auth()->user()->savings_balance, 2) }}
                                        @if(auth()->user()->isRestricted('savings')) (Restricted) @endif
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
                                    <span class="input-group-text bg-white border-2 border-end-0" style="color: #741B6B;">{{ setting('currency_symbol','$') }}</span>
                                    <input type="number" step="0.01" class="form-control border-2 border-start-0 shadow-none fw-bold" id="amount" name="amount" placeholder="0.00" required oninput="window.validateBalance()">
                                </div>
                                <div class="d-flex justify-content-between mt-2 small">
                                    <span class="text-muted">Daily Limit: $2,500.00</span>
                                    <span id="balanceFeedback"></span>
                                    <span class="text-muted" id="limitLabel">Remaining: ${{ number_format($zelleDailyLimit, 2) }}</span>
                                </div>
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
                    <div id="section-receive" class="zelle-section d-none">
                        <div class="d-flex flex-column align-items-center py-5">
                            <div class="qr-card shadow-lg p-0 text-center position-relative bg-white mb-4" style="width: 100%; max-width: 320px; border: 2px solid #333; border-radius: 24px; overflow: visible;">
                                <!-- Initials Circle -->
                                <div class="initials-avatar position-absolute top-0 start-50 translate-middle shadow-sm" style="width: 80px; height: 80px; background: #717171; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; border: 4px solid #fff; font-weight: 500;">
                                    {{ $initials }}
                                </div>
                                
                                <div class="px-4 pb-4 pt-5 mt-2">
                                    <h3 class="fw-bold mb-1 text-dark" style="letter-spacing: 0.5px; font-family: sans-serif;">{{ strtoupper($fullName) }}</h3>
                                    <p class="text-secondary small mb-4" style="font-size: 0.9rem;">{{ auth()->user()->email }}</p>
                                    
                                    <div id="zelle-qr-code" class="d-flex justify-content-center mb-4 p-3 bg-white rounded-4 shadow-sm mx-3" style="border: 1px solid #f0f0f0;">
                                        <!-- QR Code will be injected here -->
                                    </div>
                                    
                                    <div class="mb-5">
                                        <img src="{{ asset('assets/external/images/zelle.png') }}" alt="Zelle" style="height: 38px;">
                                    </div>
                                    
                                    <div class="d-flex justify-content-center gap-5 pt-3 border-top mx-4">
                                        <a href="javascript:void(0)" onclick="window.saveZelleQRAsImage()" class="text-dark opacity-75 d-flex flex-column align-items-center text-decoration-none" title="Save to Gallery">
                                            <i class="fas fa-download fs-2 mb-1"></i>
                                        </a>
                                        <a href="javascript:void(0)" onclick="window.shareZelleQR()" class="text-dark opacity-75 d-flex flex-column align-items-center text-decoration-none" title="Share Code">
                                            <i class="fas fa-share-alt fs-2 mb-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info border-0 rounded-4 p-3 small mx-3" style="max-width: 400px; background: #eef2ff; color: #4338ca;">
                                <i class="fas fa-info-circle me-2"></i> Others can scan this code to send you money quickly and securely with Zelle®.
                            </div>
                        </div>
                    </div>

                    <!-- Activity Tab -->
                    <div id="section-activity" class="zelle-section d-none">
                        <div class="d-flex gap-2 mb-4 overflow-auto pb-2 scrollbar-hide">
                            <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 filter-btn active activity-filter" data-filter="all" onclick="window.filterZelleActivity('all')">All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 filter-btn activity-filter" data-filter="sent" onclick="window.filterZelleActivity('sent')">Sent</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 filter-btn activity-filter" data-filter="received" onclick="window.filterZelleActivity('received')">Received</button>
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
                            <div class="activity-item p-3 border rounded-3 mb-3 d-flex justify-content-between align-items-center shadow-sm" data-type="{{ $isDebit ? 'sent' : 'received' }}">
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
                                    <a href="{{ route('user.transactions.receipt', $transaction->tnx) }}" class="btn btn-link btn-sm p-0 text-muted d-block" style="font-size: 0.75rem;">Receipt</a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                    <i class="fas fa-history text-muted fs-2"></i>
                                </div>
                                <h5>No Activity Yet</h5>
                                <p class="text-muted">Once you start sending or receiving money with Zelle®, your transactions will appear here.</p>
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

@section('style')
<style>
    .form-control:focus, .form-select:focus {
        border-color: #741B6B !important;
        box-shadow: 0 0 0 0.25rem rgba(116, 27, 107, 0.1) !important;
    }
    .viewport-box { transition: border-color 0.3s; }
    @keyframes scanMove { 0% { transform: translateY(0); } 100% { transform: translateY(248px); } }
    .zelle-tab { border-bottom: 3px solid transparent; color: #666; transition: all 0.2s; cursor: pointer; }
    .zelle-tab.active { border-bottom-color: #741B6B; color: #741B6B !important; }
    .zelle-tab:hover { color: #741B6B; opacity: 0.85; }
    .qr-container canvas { max-width: 100% !important; height: auto !important; margin: 0 auto; }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .zelle-section:not(.d-none) {
        animation: fadeInUp 0.4s ease-out forwards;
    }
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
    
    window.userRestrictions = {
        'default': {{ auth()->user()->isRestricted('checking') ? 'true' : 'false' }},
        'savings': {{ auth()->user()->isRestricted('savings') ? 'true' : 'false' }},
        'ira': {{ auth()->user()->isRestricted('ira') ? 'true' : 'false' }},
        'heloc': {{ auth()->user()->isRestricted('heloc') ? 'true' : 'false' }},
        'cc': {{ auth()->user()->isRestricted('cc') ? 'true' : 'false' }},
        'loan': {{ auth()->user()->isRestricted('loan') ? 'true' : 'false' }}
    };

    window.checkRestriction = function() {
        const fromSelect = document.getElementById('walletSelect');
        const selectedType = fromSelect.value;
        
        if (window.userRestrictions[selectedType]) {
            Swal.fire({
                title: 'Account Restricted',
                text: 'This account is currently frozen and cannot be used for payments. Please contact support for assistance.',
                icon: 'error',
                confirmButtonColor: '#741B6B'
            });
            // Reset to first non-disabled option
            for (let i = 0; i < fromSelect.options.length; i++) {
                if (!fromSelect.options[i].disabled) {
                    fromSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }
    
    document.addEventListener("DOMContentLoaded", function () {
        // Initialize QR for Receive tab
        const qrContent = JSON.stringify({
            c: "{{ auth()->user()->email }}",
            n: "{{ auth()->user()->full_name }}"
        });
        
        new QRCode(document.getElementById("zelle-qr-code"), {
            text: qrContent,
            width: 200,
            height: 200,
            colorDark: "#4B1045",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        // Search recipient input logic
        const contactInput = document.getElementById('zelleContact');
        if(contactInput) {
            contactInput.addEventListener('input', function (e) {
                clearTimeout(window.typingTimer);
                document.getElementById('zelleNoticeBox').classList.add('d-none');
                document.getElementById('externalNameGroup').classList.add('d-none');
                document.getElementById('externalName').removeAttribute('required');
                window.isZelleVerified = false;
                
                const searchVal = this.value.trim();
                if (searchVal.length >= 5) {
                    document.getElementById('verifySpinner').classList.remove('d-none');
                    window.typingTimer = setTimeout(() => window.verifyZelleNetwork(searchVal), 600);
                } else {
                    document.getElementById('verifySpinner').classList.add('d-none');
                }
            });
        }
    });

    window.switchZelleTab = function(tab) {
        const sections = document.querySelectorAll('.zelle-section');
        const tabs = document.querySelectorAll('.zelle-tab');
        
        // Hide all with delay for clean reset
        sections.forEach(s => {
            s.classList.add('d-none');
            s.style.animation = 'none';
            s.offsetHeight; // Trigger reflow
            s.style.animation = '';
        });
        
        tabs.forEach(t => {
            t.classList.remove('active', 'text-dark');
            t.classList.add('text-muted');
        });
        
        const section = document.getElementById('section-' + tab);
        if (section) section.classList.remove('d-none');
        
        const activeTab = document.getElementById('tab-' + tab);
        if (activeTab) {
            activeTab.classList.add('active', 'text-dark');
            activeTab.classList.remove('text-muted');
        }
    }

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
            if (!noticeBox) return;

            noticeBox.classList.remove('d-none');
            
            if (data.status === 'internal' && data.is_restricted) {
                window.verifiedName = null;
                window.isZelleVerified = false;
                document.getElementById('noticeIcon').className = 'fas fa-exclamation-triangle text-danger';
                document.getElementById('noticeTitle').innerText = 'Recipient Restricted';
                document.getElementById('noticeSub').innerHTML = `<span class="text-danger fw-bold">${data.name}'s account is currently restricted and cannot receive Zelle payments at this time.</span>`;
                document.getElementById('externalNameGroup').classList.add('d-none');
                return;
            }

            if (data.status === 'success' || data.status === 'internal') {
                window.verifiedName = data.name;
                document.getElementById('noticeIcon').className = 'fas fa-shield-check';
                document.getElementById('noticeTitle').innerText = 'Enrolled with Zelle®';
                document.getElementById('noticeSub').innerHTML = `You are sending money to <strong>${data.name}</strong>`;
                document.getElementById('externalNameGroup').classList.add('d-none');
                document.getElementById('externalName').removeAttribute('required');
            } else {
                window.verifiedName = null;
                document.getElementById('noticeIcon').className = 'fas fa-user-plus';
                document.getElementById('noticeTitle').innerText = 'New Contact';
                document.getElementById('noticeSub').innerHTML = "This recipient isn't in your contacts. Please provide their name below to continue.";
                document.getElementById('externalNameGroup').classList.remove('d-none');
                document.getElementById('externalName').setAttribute('required', 'required');
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
            feedback.innerHTML = '<span class="text-danger small fw-bold">Insufficient funds.</span>';
            return false;
        } else if (amount > 0) {
            feedback.innerHTML = '<span class="text-success small"><i class="fas fa-check"></i> Verified</span>';
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
        const memo = document.querySelector('input[name="purpose"]').value.trim() || '—';
        const fromSelect = document.getElementById('walletSelect');
        const fromText = fromSelect.options[fromSelect.selectedIndex].text.split(' - ')[0];
        
        if (!contact || contact.length < 5) {
            Swal.fire({ title: 'Recipient Required', text: 'Please enter an Email or U.S Mobile Number', icon: 'warning' });
            return;
        }
        
        if (!window.validateBalance() || amount <= 0) {
            Swal.fire({ title: 'Invalid Amount', text: 'Please check your balance and daily limits.', icon: 'error' });
            return;
        }
        
        if (!window.isZelleVerified) {
            // Re-trigger verification if they click too fast
            window.verifyZelleNetwork(contact);
            setTimeout(window.confirmZelle, 800);
            return;
        }

        const extName = document.getElementById('externalName').value.trim();
        const displayRecipientName = window.verifiedName || extName || 'Recipient';
        
        if (document.getElementById('externalName').hasAttribute('required') && !extName) {
            Swal.fire({ title: 'Name Required', text: 'Please provide the missing contact name.', icon: 'warning' });
            return;
        }

        Swal.fire({
            title: '<div class="pt-3" style="color: #4a1144; font-size: 1.5rem;">Review Payment</div>',
            html: `
                <div class="px-2">
                    <div class="text-center mb-4">
                        <div class="display-5 fw-bold" style="color: #741B6B;">$${amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                        <div class="text-muted small text-uppercase fw-bold">Amount to Send</div>
                    </div>

                    <div class="card border-0 bg-light rounded-4 mb-3 overflow-hidden text-start shadow-sm" style="border: 1px solid #eee!important;">
                        <div class="card-body p-0">
                            <div class="d-flex flex-column border-bottom p-3">
                                <span class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">From Account</span>
                                <span class="fw-bold text-dark small">${fromText}</span>
                            </div>
                            <div class="d-flex flex-column border-bottom p-3">
                                <span class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">To Recipient</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-dark small">${displayRecipientName}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">(${contact})</span>
                                </div>
                            </div>
                            <div class="d-flex flex-column border-bottom p-3">
                                <span class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Delivery Method</span>
                                <div class="d-flex align-items-center">
                                    <span class="fw-bold text-success small"><i class="fas fa-bolt me-1"></i> Typically in minutes</span>
                                </div>
                            </div>
                            <div class="d-flex flex-column p-3">
                                <span class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Memo</span>
                                <span class="text-dark small">${memo}</span>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-muted mb-0 text-center" style="font-size: 0.75rem; line-height: 1.4; max-width: 90%; margin: 0 auto;">
                        Money is usually sent in minutes. Once you send it, you usually <strong>cannot cancel it</strong>. Only send to people you trust.
                    </p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#741B6B',
            confirmButtonText: 'Send Money Now',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'rounded-pill px-5 py-2', cancelButton: 'rounded-pill px-5 py-2' }
        }).then((result) => {
            if (result.isConfirmed) {
                // Trigger Security Gate (Follows user's actual security settings)
                SecurityGate.gate(document.getElementById('zelleForm'));
            }
        });
    }

    window.filterZelleActivity = function(filter) {
        document.querySelectorAll('.activity-filter').forEach(el => {
            el.classList.remove('btn-dark', 'active');
            el.classList.add('btn-outline-secondary');
        });
        
        const activeBtn = document.querySelector(`.activity-filter[data-filter="${filter}"]`);
        if (activeBtn) {
            activeBtn.classList.add('btn-dark', 'active');
            activeBtn.classList.remove('btn-outline-secondary');
        }

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
            Swal.fire('Error', 'QR code not ready.', 'error');
            return;
        }

        // Create high-quality card canvas
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = 600;
        canvas.height = 850;

        // Background
        ctx.fillStyle = '#ffffff';
        ctx.roundRect(0, 0, canvas.width, canvas.height, 40);
        ctx.fill();
        ctx.strokeStyle = '#333333';
        ctx.lineWidth = 4;
        ctx.stroke();

        // Initials Circle
        ctx.beginPath();
        ctx.arc(300, 0, 80, 0, Math.PI * 2);
        ctx.fillStyle = '#717171';
        ctx.fill();
        ctx.font = 'bold 60px sans-serif';
        ctx.fillStyle = '#ffffff';
        ctx.textAlign = 'center';
        ctx.fillText("{{ $initials }}", 300, 45);

        // Name
        ctx.fillStyle = '#000000';
        ctx.font = 'bold 42px sans-serif';
        ctx.fillText("{{ strtoupper($fullName) }}", 300, 160);

        // Contact
        ctx.fillStyle = '#666666';
        ctx.font = '28px sans-serif';
        ctx.fillText("{{ auth()->user()->email }}", 300, 210);

        // QR Code (scaled up)
        ctx.drawImage(qrCanvas, 100, 280, 400, 400);

        // Zelle Logo
        const zelleImg = new Image();
        zelleImg.src = "{{ asset('assets/external/images/zelle.png') }}";
        zelleImg.onload = function() {
            const logoWidth = 150;
            const logoHeight = (zelleImg.height / zelleImg.width) * logoWidth;
            ctx.drawImage(zelleImg, (canvas.width - logoWidth) / 2, 730, logoWidth, logoHeight);

            // Trigger Download
            const link = document.createElement('a');
            link.download = 'Pay_Me_With_Zelle.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            Swal.fire({ title: 'Saved!', text: 'Your Zelle card has been saved to gallery.', icon: 'success', timer: 1500, showConfirmButton: false });
        };
    }

    window.shareZelleQR = function() {
        const shareText = "Pay me with Zelle®: {{ $fullName }} ({{ auth()->user()->email }})";
        if (navigator.share) {
            navigator.share({
                title: 'My Zelle® Code',
                text: shareText,
                url: window.location.href
            }).catch(console.error);
        } else {
            navigator.clipboard.writeText(shareText).then(() => {
                Swal.fire({ title: 'Info Copied', text: 'Payment info copied to clipboard.', icon: 'success', timer: 1500, showConfirmButton: false });
            });
        }
    }

    window.zelleScanner = null;

    window.startZelleScanner = function() {
        document.getElementById('zelle-scanner-container').classList.remove('d-none');
        window.zelleScanner = new Html5Qrcode("zelle-reader");
        window.zelleScanner.start(
            { facingMode: "environment" }, 
            { fps: 20, qrbox: { width: 250, height: 250 } },
            (decodedText) => {
                window.stopZelleScanner();
                window.handleScannedData(decodedText);
            }
        ).catch(err => {
            console.error(err);
            Swal.fire('Scanner Error', 'Camera permission required.', 'error');
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
        let contact = data;
        try {
            const parsed = JSON.parse(data);
            if (parsed.c) contact = parsed.c;
        } catch (e) {}

        if (contact) {
            document.getElementById('zelleContact').value = contact;
            window.verifyZelleNetwork(contact);
        }
    }
</script>
@endsection
