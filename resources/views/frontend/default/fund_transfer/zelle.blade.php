@extends('frontend::layouts.user')

@section('title')
    {{ __('Send Money with Zelle®') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-9 col-12">
        <div class="text-center mb-4">
            <div class="d-flex align-items-center justify-content-center mb-2">
                <a href="{{ route('user.fund_transfer.index') }}" class="back-nav-link m-0 me-3" style="color: #741B6B;">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <img src="{{ asset('assets/external/images/zelle.png') }}" alt="Zelle" style="height: 36px; margin-top: -5px;">
            </div>
            <p class="text-muted small">Fast, safe and easy way to send money.</p>
        </div>

        <div class="banno-card p-0 mb-4 shadow-sm" style="border-top: 4px solid #741B6B;">
            <form action="{{ route('user.fund_transfer.zelle.submit') }}" method="POST" id="zelleForm">
                @csrf
                <div class="p-4 p-md-5">
                    <div class="row g-4">
                        <!-- From Account -->
                        <div class="col-12">
                            <label class="form-label small text-uppercase fw-bold text-muted">From Account</label>
                            <select name="wallet_type" class="form-select form-select-lg border-2 shadow-none" id="walletSelect" onchange="validateBalance()">
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
                                <input type="number" step="0.01" class="form-control border-2 border-start-0 shadow-none fw-bold" id="amount" name="amount" placeholder="0.00" required oninput="validateBalance()">
                            </div>
                            <div class="small mt-2 d-flex justify-content-between">
                                <span id="balanceFeedback"></span>
                                <span class="text-muted"><i class="fas fa-info-circle"></i> Daily Limit: $2,500</span>
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
                        <button type="button" id="zelleSubmitBtn" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm w-100 fs-5 fw-bold" style="background-color: #741B6B; border-color: #741B6B;" onclick="confirmZelle()">
                            Review & Send <i class="fas fa-paper-plane ms-2"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="text-center mt-4">
            <img src="{{ asset('assets/external/images/zelle logo2025.png') }}" alt="Zelle" style="max-height: 25px; opacity: 0.8; filter: grayscale(100%);">
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
</style>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let typingTimer;
    let isZelleVerified = false;
    const contactInput = document.getElementById('zelleContact');
    const noticeBox = document.getElementById('zelleNoticeBox');
    
    contactInput.addEventListener('keyup', function () {
        clearTimeout(typingTimer);
        noticeBox.classList.add('d-none');
        document.getElementById('externalNameGroup').classList.add('d-none');
        document.getElementById('externalName').removeAttribute('required');
        isZelleVerified = false;
        
        const val = this.value.trim();
        if (val.length >= 5) {
            document.getElementById('verifySpinner').classList.remove('d-none');
            typingTimer = setTimeout(() => verifyZelleNetwork(val), 800);
        }
    });

    function verifyZelleNetwork(contact) {
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
            noticeBox.classList.remove('d-none');
            
            if (data.status === 'internal') {
                document.getElementById('noticeIcon').className = 'fas fa-shield-check';
                document.getElementById('noticeTitle').innerText = 'Enrolled with Zelle®';
                document.getElementById('noticeSub').innerHTML = `You are sending money to <strong>${data.name}</strong>`;
                document.getElementById('externalNameGroup').classList.add('d-none');
                document.getElementById('externalName').removeAttribute('required');
            } else if (data.status === 'external') {
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
            isZelleVerified = true;
        })
        .catch(err => {
            console.error(err);
            document.getElementById('verifySpinner').classList.add('d-none');
        });
    }

    function validateBalance() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const fromSelect = document.getElementById('walletSelect');
        const balance = parseFloat(fromSelect.options[fromSelect.selectedIndex].getAttribute('data-balance'));
        const feedback = document.getElementById('balanceFeedback');
        
        if (amount > 2500) {
            feedback.innerHTML = '<span class="text-danger small fw-bold">Daily limit exceeded ($2,500).</span>';
            return false;
        } else if (amount > balance) {
            feedback.innerHTML = '<span class="text-danger small fw-bold">Insufficient funds available.</span>';
            return false;
        } else if (amount > 0) {
            feedback.innerHTML = '<span class="text-success small"><i class="fas fa-check"></i> Amount Verified</span>';
            return true;
        } else {
            feedback.innerHTML = '';
            return false;
        }
    }

    function confirmZelle() {
        const contact = contactInput.value.trim();
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        
        if (!contact || contact.length < 5) {
            Swal.fire({ title: 'Recipient Required', text: 'Please enter an Email or U.S Mobile Number', icon: 'warning', confirmButtonColor: '#741B6B' });
            return;
        }
        
        if (!validateBalance() || amount <= 0) {
            Swal.fire({ title: 'Invalid Amount', text: 'Please check your balance and daily limits.', icon: 'error', confirmButtonColor: '#741B6B' });
            return;
        }
        
        if (!isZelleVerified) {
            Swal.fire({ title: 'Verifying with Zelle®', text: 'Please wait while we verify this contact\'s enrollment status.', icon: 'info', confirmButtonColor: '#741B6B' });
            return;
        }

        const extName = document.getElementById('externalName').value.trim();
        if (document.getElementById('externalName').hasAttribute('required') && !extName) {
            Swal.fire({ title: 'Name Required', text: 'Please provide the missing contact name.', icon: 'warning', confirmButtonColor: '#741B6B' });
            return;
        }

        let displayName = contact;
        if (extName) displayName = `<strong>${extName}</strong> (${contact})`;

        Swal.fire({
            title: 'Confirm Payment',
            html: `You are about to securely send <strong>$${amount.toFixed(2)}</strong> to ${displayName} using Zelle.<br><br><small class="text-danger">Payments cannot be reversed.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#741B6B',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Send Now!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('zelleSubmitBtn').disabled = true;
                document.getElementById('zelleSubmitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
                SecurityGate.gate(document.getElementById('zelleForm'));
            }
        });
    }
</script>
@endsection
