@extends('frontend::layouts.user')

@section('title')
    {{ __('Transfers') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <!-- Centered Card Layout -->
    <div class="col-lg-6 col-md-8 col-12">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Header -->
            <div class="card-header bg-white border-0 pt-4 pb-2 text-center position-relative">
                <a href="{{ route('user.dashboard') }}" class="position-absolute start-0 top-50 translate-middle-y ms-4 text-muted">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <h4 class="fw-bold mb-0">Transfer</h4>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('user.fund_transfer.transfer') }}" method="POST" id="transferForm">
                    @csrf
                    
                    <!-- Hidden inputs for type logic -->
                    <input type="hidden" name="transfer_type" id="transferType" value="self">
                    <input type="hidden" name="bank_id" id="bankId" value="0">
                    <input type="hidden" name="beneficiary_id" id="beneficiaryId" value="">

                    <!-- From Section -->
                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">From</label>
                        <div class="list-group list-group-flush border rounded-3">
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3 border-0 bg-light" id="fromAccountBtn" data-bs-toggle="dropdown">
                                <span class="fw-bold text-dark" id="fromAccountLabel">
                                    {{ substr(auth()->user()->account_number, -4) }} CHECKING
                                </span>
                                <div class="text-end">
                                    <span class="d-block small text-muted">Available</span>
                                    <span class="fw-bold text-dark">{{ setting('site_currency') }} {{ number_format(auth()->user()->balance, 2) }}</span>
                                </div>
                                <i class="fas fa-chevron-right ms-2 text-muted small"></i>
                            </button>
                            <!-- Dropdown for From selection -->
                            <ul class="dropdown-menu w-100 shadow-sm border-0 rounded-3 mt-1" aria-labelledby="fromAccountBtn">
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center py-3" href="#" onclick="selectFromWallet('default', '{{ auth()->user()->account_number }}', '{{ auth()->user()->balance }}', 'CHECKING')">
                                        <span>{{ substr(auth()->user()->account_number, -4) }} CHECKING</span>
                                        <span class="fw-bold">{{ setting('site_currency') }} {{ number_format(auth()->user()->balance, 2) }}</span>
                                    </a>
                                </li>
                                @if(auth()->user()->savings_balance > 0 || \Schema::hasColumn('users', 'savings_account_number'))
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center py-3" href="#" onclick="selectFromWallet('primary_savings', '{{ auth()->user()->savings_account_number ?? auth()->user()->account_number }}', '{{ auth()->user()->savings_balance }}', 'SAVINGS')">
                                        <span>{{ substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) }} SAVINGS</span>
                                        <span class="fw-bold">{{ setting('site_currency') }} {{ number_format(auth()->user()->savings_balance, 2) }}</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                            <input type="hidden" name="wallet_type" id="walletSelect" value="default">
                        </div>
                    </div>

                    <!-- Swap Icon -->
                    <div class="text-center my-2 position-relative" style="height: 20px;">
                         <div class="position-absolute start-50 top-50 translate-middle bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px; z-index: 5; border: 1px solid #dee2e6; cursor: pointer;" onclick="swapAccounts()">
                             <i class="fas fa-exchange-alt fa-rotate-90"></i>
                         </div>
                         <hr class="border-light m-0 position-absolute w-100 top-50">
                    </div>

                    <!-- To Section -->
                    <div class="mb-4">
                        <label class="small fw-bold text-muted mb-1">To</label>
                        
                        <!-- Internal/Self Target Display -->
                        <div class="list-group list-group-flush border rounded-3" id="toSelfContainer">
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3 border-0 bg-light" data-bs-toggle="modal" data-bs-target="#recipientModal">
                                <span class="fw-bold text-dark" id="toAccountLabel">Select Recipient</span>
                                <i class="fas fa-chevron-right ms-2 text-muted small"></i>
                            </button>
                        </div>
                        
                        <!-- External/Member Manual Inputs (Hidden by default) -->
                        <div id="manualInputContainer" class="mt-2" style="display:none;">
                             <div class="p-3 border rounded-3 bg-light">
                                 <div class="d-flex justify-content-between align-items-center mb-2">
                                     <span class="badge bg-primary" id="manualTypeBadge">External</span>
                                     <a href="#" class="small text-decoration-none" onclick="resetToRecipientSelect()">Change</a>
                                 </div>
                                 <div id="authMemberFields" style="display:none;">
                                      <input type="text" class="form-control mb-2" name="manual_data[account_number]" id="member_account_number" placeholder="Account Number">
                                      <input type="text" class="form-control bg-white" name="manual_data[account_name]" id="member_account_name" placeholder="Name" readonly>
                                 </div>
                                 <div id="otherBankFields" style="display:none;">
                                      <select class="form-select mb-2" id="otherBankSelect">
                                          <option value="" disabled selected>Select Bank</option>
                                          @foreach($banks as $bank)
                                          <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                          @endforeach
                                      </select>
                                      <input type="text" class="form-control mb-2" name="manual_data[routing_number]" placeholder="Routing Number (9 digits)" maxlength="9">
                                      <input type="text" class="form-control mb-2" name="manual_data[account_number]" placeholder="Account Number">
                                      <input type="text" class="form-control mb-2" name="manual_data[account_name]" placeholder="Recipient Name">
                                 </div>
                             </div>
                        </div>
                    </div>

                    <!-- Amount -->
                    <div class="mb-4">
                        <label class="small fw-bold text-muted mb-1">Amount</label>
                        <div class="input-group input-group-lg bg-light rounded-3 border overflow-hidden">
                            <span class="input-group-text border-0 bg-transparent ps-3 text-muted fw-bold">{{ setting('site_currency') }}</span>
                            <input type="number" step="0.01" class="form-control border-0 bg-transparent fw-bold fs-4 text-end pe-3" id="amount" name="amount" placeholder="0.00" required>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                             <small class="text-muted min-max-limit"></small>
                             <small class="text-danger fw-bold charge-info"></small>
                        </div>
                    </div>

                    <!-- Frequency -->
                    <div class="mb-3 d-flex justify-content-between align-items-center py-2 border-bottom">
                         <label class="fw-bold mb-0">Frequency</label>
                         <select class="form-select border-0 w-auto text-end fw-bold text-primary pe-4" name="frequency" style="background-position: right center;">
                             <option value="once">Once</option>
                             <option value="daily">Daily</option>
                             <option value="weekly">Weekly</option>
                             <option value="monthly">Monthly</option>
                         </select>
                    </div>

                    <!-- Date -->
                    <div class="mb-3 d-flex justify-content-between align-items-center py-2 border-bottom">
                         <label class="fw-bold mb-0">Date</label>
                         <div class="d-flex align-items-center">
                             <input type="date" class="form-control border-0 text-end fw-bold text-primary p-0" name="scheduled_at" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" style="width: 130px;">
                             <i class="fas fa-chevron-right ms-2 text-muted small"></i>
                         </div>
                    </div>

                    <!-- Memo -->
                    <div class="mb-4">
                        <div class="form-floating">
                            <input type="text" class="form-control bg-light border-0 rounded-3" id="memo" name="purpose" placeholder="Memo">
                            <label for="memo" class="text-muted">Memo</label>
                        </div>
                        <div class="text-end mt-1">
                             <small class="text-muted">For immediate, internal transfers only</small>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-grid mt-5">
                         <button @if (auth()->user()->passcode !== null && setting('fund_transfer_passcode_status')) type="button" data-bs-toggle="modal" data-bs-target="#passcode" @else type="submit" @endif class="btn btn-danger btn-lg rounded-pill fw-bold shadow-sm" style="background-color: #ff8a80; border-color: #ff8a80; color: white;">
                            Submit
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<!-- Recipient Selection Modal -->
<div class="modal fade" id="recipientModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0">
             <div class="modal-header border-0 pb-0">
                 <h5 class="modal-title fw-bold">Select Recipient</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
             </div>
             <div class="modal-body p-4">
                 <div class="d-grid gap-3">
                     <!-- My Accounts -->
                     <button class="btn btn-outline-light text-dark text-start p-3 border shadow-sm rounded-3 choice-btn" onclick="selectRecipientType('self')">
                         <div class="d-flex align-items-center">
                             <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3"><i class="fas fa-wallet text-primary"></i></div>
                             <div>
                                 <h6 class="mb-0 fw-bold">My Accounts</h6>
                                 <small class="text-muted">Transfer between your connected accounts</small>
                             </div>
                         </div>
                     </button>
                     <!-- Another Member -->
                     <button class="btn btn-outline-light text-dark text-start p-3 border shadow-sm rounded-3 choice-btn" onclick="selectRecipientType('member')">
                         <div class="d-flex align-items-center">
                             <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3"><i class="fas fa-users text-success"></i></div>
                             <div>
                                 <h6 class="mb-0 fw-bold">Another Member</h6>
                                 <small class="text-muted">Send to another member instantly</small>
                             </div>
                         </div>
                     </button>
                     <!-- External Bank -->
                     <button class="btn btn-outline-light text-dark text-start p-3 border shadow-sm rounded-3 choice-btn" onclick="selectRecipientType('other')">
                         <div class="d-flex align-items-center">
                             <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3"><i class="fas fa-university text-warning"></i></div>
                             <div>
                                 <h6 class="mb-0 fw-bold">External Bank</h6>
                                 <small class="text-muted">Wire or ACH transfer to other banks</small>
                             </div>
                         </div>
                     </button>
                 </div>
             </div>
        </div>
    </div>
</div>

<!-- Passcode Modal (Same as before) -->
@if (auth()->user()->passcode !== null && setting('fund_transfer_passcode_status'))
    <div class="modal fade" id="passcode" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-body p-5 text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                        <i class="fas fa-lock text-primary fs-2"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Security Passcode</h4>
                    <p class="text-muted mb-4">Please enter your 6-digit security passcode.</p>
                    <div class="mb-4">
                        <input type="password" class="form-control form-control-lg text-center fw-bold border-2" name="passcode" required placeholder="••••••" style="letter-spacing: 0.5rem; font-size: 2rem;">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill py-3 fw-bold" form="transferForm">Authorize Transfer</button>
                        <button type="button" class="btn btn-link text-muted text-decoration-none fw-bold" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@section('script')
<script>
    // State Variables
    let currentWallet = 'default'; // default = checking
    let targetType = 'self';
    
    // User Info
    const userAccount = '{{ auth()->user()->account_number }}';
    const savingsAccount = '{{ auth()->user()->savings_account_number ?? auth()->user()->account_number . "S00" }}';
    const hasSavings = {{ (auth()->user()->savings_balance > 0 || \Schema::hasColumn('users', 'savings_account_number')) ? 'true' : 'false' }};

    function selectFromWallet(wallet, accountNum, balance, label) {
        $('#walletSelect').val(wallet);
        $('#fromAccountLabel').html(accountNum.slice(-4) + ' ' + label);
        // If Self Transfer, auto-update To
        if(targetType === 'self') {
            updateSelfTarget();
        }
        currentWallet = wallet;
    }

    function selectRecipientType(type) {
        targetType = type;
        $('#transferType').val(type);
        $('#recipientModal').modal('hide');
        
        $('#toSelfContainer').hide();
        $('#manualInputContainer').show();
        $('#authMemberFields').hide();
        $('#otherBankFields').hide();
        
        if (type === 'self') {
            $('#manualInputContainer').hide();
            $('#toSelfContainer').show();
            updateSelfTarget();
        } else if (type === 'member') {
            $('#authMemberFields').show();
            $('#manualTypeBadge').text('Member').removeClass('bg-warning').addClass('bg-success');
            $('#bankId').val(0);
        } else if (type === 'other') {
            $('#otherBankFields').show();
            $('#manualTypeBadge').text('External').removeClass('bg-success').addClass('bg-warning');
        }
    }
    
    function resetToRecipientSelect() {
        $('#recipientModal').modal('show');
    }

    function updateSelfTarget() {
        // Logic: If From Checking -> To Savings. If From Savings -> To Checking.
        const from = $('#walletSelect').val();
        let toLabel = '';
        let toSub = '';
        
        if (from === 'default') {
             // From Checking
             toLabel = savingsAccount.slice(-4) + ' SAVINGS';
             toSub = '{{ setting("site_currency") }} ' + '{{ number_format(auth()->user()->savings_balance, 2) }}';
             // Internally treated as Member Transfer to own savings
             $('#member_account_number').val(savingsAccount);
        } else {
             // From Savings
             toLabel = userAccount.slice(-4) + ' CHECKING';
             toSub = '{{ setting("site_currency") }} ' + '{{ number_format(auth()->user()->balance, 2) }}';
             $('#member_account_number').val(userAccount);
        }
        
        // Update Label
        $('#toAccountLabel').html(`
            <div class="d-flex justify-content-between w-100">
                <span>${toLabel}</span>
                <span class="fw-bold">${toSub}</span>
            </div>
        `);
        
        // Ensure hidden fields are correct for self transfer
        $('#transferType').val('self'); 
        $('#bankId').val(0);
    }
    
    function swapAccounts() {
        // Only works for Self transfer really well
        if(targetType !== 'self') return;
        
        const current = $('#walletSelect').val();
        if(current === 'default' && hasSavings) {
             selectFromWallet('primary_savings', savingsAccount, '{{ auth()->user()->savings_balance }}', 'SAVINGS');
        } else {
             selectFromWallet('default', userAccount, '{{ auth()->user()->balance }}', 'CHECKING');
        }
    }

    $(document).ready(function() {
        // Initialize
        updateSelfTarget();
        
        // Member Search
        $('#member_account_number').on('input', function() {
            if($(this).val().length > 4 && targetType === 'member') {
                 $.ajax({
                    url: '/user/search-by-account-number/' + $(this).val(),
                    success: function(data) {
                        $('#member_account_name').val(data.name);
                    }
                });
            }
        });
        
        // Bank Selection
        $('#otherBankSelect').on('change', function() {
            $('#bankId').val($(this).val());
            // Fetch limits/charges
             $.ajax({
                url: '/user/fund-transfer/beneficiary-details/' + $(this).val(),
                success: function(data) {
                    // Update limits UI
                    $('.charge-info').text('Fee: ' + data.banksData.charge + (data.banksData.charge_type === 'percentage' ? '%' : ' {{ setting("site_currency") }}'));
                }
             });
        });
    });
</script>
@endsection
