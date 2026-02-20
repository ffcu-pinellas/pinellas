@extends('frontend::layouts.user')

@section('title')
    {{ __('Transfers') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10 col-12">
        <div class="text-center mb-5">
            <h1 class="h2 fw-bold mb-3">Transfer funds</h1>
            <p class="text-muted">Move money between your accounts or to other members.</p>
        </div>

        <div class="banno-card p-0 overflow-hidden mb-4">
            <form action="{{ route('user.fund_transfer.transfer') }}" method="POST" id="transferForm">
                @csrf
                <div class="p-0">
                    <!-- Step 1: From Account -->
                    <div class="p-5 border-bottom">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">1</div>
                            <h5 class="fw-bold mb-0">From</h5>
                        </div>
                        <div class="inputs">
                            <select name="wallet_type" class="form-select form-select-lg rounded-3 fw-bold border-2" id="walletSelect" style="padding: 15px;">
                                <option value="default" data-currency="{{ setting('site_currency') }}" selected>
                                    {{ __('Checking account (...') . substr(auth()->user()->account_number, -4) . ')' }} - {{ setting('site_currency') }} {{ auth()->user()->balance }}
                                </option>
                                @if(auth()->user()->savings_balance > 0)
                                    <option value="primary_savings" data-currency="{{ setting('site_currency') }}">
                                        {{ __('Savings account (...') . substr(auth()->user()->savings_account_number ?? auth()->user()->account_number, -4) . 'S00)' }} - {{ setting('site_currency') }} {{ auth()->user()->savings_balance }}
                                    </option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <!-- Step 2: Recipient Details -->
                    <div class="p-5 border-bottom bg-light bg-opacity-10">
                        <!-- Internal / Member Transfer View -->
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">2</div>
                            <h5 class="fw-bold mb-0">To Member</h5>
                        </div>

                        <!-- Simplified Manual Entry -->
                        <div class="inputs">
                            <div class="row g-4">
                                <input type="hidden" name="bank_id" value="0" id="bankId"> <!-- Force Internal/Own Bank -->
                                <input type="hidden" name="beneficiary_id" value=""> 

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-uppercase text-muted mb-2">Recipient Info</label>
                                    <input type="text" class="form-control form-control-lg fw-bold border-2" id="account_number" name="member_identifier" placeholder="Email or Account #" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-uppercase text-muted mb-2">Member's Name</label>
                                    <div id="account_name_wrapper" class="form-control form-control-lg border-2 bg-light d-flex align-items-center" style="min-height: 50px; color: #64748b;">
                                        <span id="account_name_text">Enter info to verify...</span>
                                    </div>
                                    <input type="hidden" id="account_name" name="manual_data[account_name]">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-uppercase text-muted mb-2">Target Account</label>
                                    <select name="target_account_type" id="target_account_type" class="form-select form-select-lg fw-bold border-2">
                                        <option value="checking" selected>Checking</option>
                                        <option value="savings" id="opt-savings" hidden disabled>Savings</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> Enter the account number to verify the member's name.</div>
                        </div>
                    </div>
                    </div>

                    <!-- Step 3: Amount & Memo -->
                    <div class="p-5">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">3</div>
                            <h5 class="fw-bold mb-0">Details</h5>
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase">Amount</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white border-2 border-end-0 fw-bold" id="amountCurrencySymbol">{{ $currency }}</span>
                                    <input type="number" step="0.01" class="form-control border-2 border-start-0 fw-bold" id="amount" name="amount" placeholder="0.00" style="font-size: 1.5rem;">
                                </div>
                                <div class="small text-muted mt-2 min-max"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase">Memo (Optional)</label>
                                <textarea class="form-control border-2" rows="2" name="purpose" placeholder="What's this for?" style="height: 62px;"></textarea>
                            </div>
                        </div>

                        <!-- Review Table -->
                        <div class="mt-5 p-4 rounded-3 border bg-light bg-opacity-25 shadow-sm">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Transfer Amount</span>
                                <span class="fw-bold amount">0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Service Fee</span>
                                <span class="text-danger fw-bold charge2">0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="h6 fw-bold mb-0">Total Withdrawal</span>
                                <span class="h6 fw-bold mb-0 total">0.00</span>
                            </div>
                        </div>

                        <div class="mt-5 text-center">
                            <button @if (auth()->user()->passcode !== null && setting('fund_transfer_passcode_status')) type="button" data-bs-toggle="modal" data-bs-target="#passcode" @else type="submit" @endif class="btn btn-primary rounded-pill px-5 py-3 fw-bold fs-5 shadow-sm w-100 w-md-auto">
                                <i class="fas fa-paper-plane me-2"></i> {{ __('Send Transfer Now') }}
                            </button>
                        </div>
                    </div>
                </div>

                @if (auth()->user()->passcode !== null && setting('fund_transfer_passcode_status'))
                    <div class="modal fade" id="passcode" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                                <div class="modal-body p-5 text-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                        <i class="fas fa-lock text-primary fs-2"></i>
                                    </div>
                                    <h4 class="fw-bold mb-3">Security Passcode</h4>
                                    <p class="text-muted mb-4">Please enter your 6-digit security passcode to authorize this transfer.</p>
                                    <div class="mb-4">
                                        <input type="password" class="form-control form-control-lg text-center fw-bold border-2" name="passcode" required placeholder="••••••" style="letter-spacing: 0.5rem; font-size: 2rem;">
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary rounded-pill py-3 fw-bold">Authorize Transfer</button>
                                        <button type="button" class="btn btn-link text-muted text-decoration-none fw-bold" data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

@include('frontend::fund_transfer.include.__add_beneficiary')

@endsection

@section('script')
<script>
    $(document).ready(function() {
        "use strict";

        // Logic for own bank vs other bank branch field
        var isPhysicalBank = '{{ setting('multi_branch', 'permission') }}';
        if (!isPhysicalBank) {
            $('#bankId').on('change', function() {
                if ($(this).val() == 0) {
                    $('#branch_name_field').hide();
                } else {
                    $('#branch_name_field').show();
                }
            });
        }

        var currency = "{{ setting('site_currency') }}";
        var globalData = { charge_type: 'fixed', charge: 0, minimum_transfer: 0, maximum_transfer: 0 };

        const onWalletChange = function() {
            currency = $('#walletSelect').find(':selected').data('currency');
            $('#amountCurrencySymbol').text(currency);
        };

        $('#walletSelect').on('change', function() {
            onWalletChange();
            onAmountChange();
            onChangeBank();
        });
        onWalletChange();

        $('#account_number').on('input', function() {
            var val = $(this).val();
            var nameText = $('#account_name_text');
            var nameInput = $('#account_name');
            var optSavings = $('#opt-savings');
            var targetSelect = $('#target_account_type');

            if(val.length > 4 || (val.includes('@') && val.length > 3)) {
                $.ajax({
                    type: 'GET',
                    url: '/user/search-by-account-number/' + encodeURIComponent(val),
                    success: function(data) {
                        if (data.name) {
                            nameText.text(data.name).removeClass('text-muted text-danger').addClass('fw-bold text-dark');
                            nameInput.val(data.name);

                            if(data.has_savings) {
                                optSavings.prop('hidden', false).prop('disabled', false);
                            } else {
                                optSavings.prop('hidden', true).prop('disabled', true);
                                targetSelect.val('checking');
                            }
                            
                            // Auto-select based on identifier
                            if(val === data.savings_account_number) {
                                targetSelect.val('savings');
                            } else if(val === data.account_number) {
                                targetSelect.val('checking');
                            }
                        } else {
                            nameText.text('Member not Found!').addClass('text-danger').removeClass('text-muted fw-bold text-dark');
                            nameInput.val('');
                            optSavings.prop('hidden', true).prop('disabled', true);
                            targetSelect.val('checking');
                        }
                    }
                });
            } else {
                nameText.text('Enter info to verify...').removeClass('text-danger fw-bold text-dark').addClass('text-muted');
                nameInput.val('');
                optSavings.prop('hidden', true).prop('disabled', true);
                targetSelect.val('checking');
            }
        });

        $('#beneficiaryId').on('change', function() {
            if ($(this).val() != '') {
                $('.custom-fields').hide();
            } else {
                $('.custom-fields').show();
            }
            onAmountChange();
        });

        const onChangeBank = function() {
            var bankId = $('#bankId').val();
            if(!bankId) return;

            $.ajax({
                type: 'GET',
                url: '/user/fund-transfer/beneficiary-details/' + bankId,
                data: { currency_code: currency },
                success: function(data) {
                    $('#beneficiaryId').empty().append('<option value="" selected>--{{ __('Choose Recipient') }}--</option>');
                    globalData = data.banksData;
                    
                    $.each(data.beneficiaries, function(key, beneficiary) {
                        $('#beneficiaryId').append('<option value="' + beneficiary.id + '">' + beneficiary.account_name + ' (...' + beneficiary.account_number.slice(-4) + ')</option>');
                    });
                    
                    $('.charge2').text((globalData.charge_type === 'percentage' ? globalData.charge + '%' : globalData.charge + ' ' + currency));
                    $('.min-max').text('Min: ' + globalData.minimum_transfer + ' ' + currency + ' • Max: ' + globalData.maximum_transfer + ' ' + currency);
                    
                    onAmountChange();
                }
            });
        };

        $('#bankId').change(onChangeBank);

        const onAmountChange = function() {
            var amount = Number($('#amount').val()) || 0;
            $('.amount').text(amount.toFixed(2) + ' ' + currency);
            
            var charge = globalData.charge_type === 'percentage' ? (amount * globalData.charge / 100) : Number(globalData.charge);
            $('.charge2').text(charge.toFixed(2) + ' ' + currency);
            
            var total = amount + charge;
            $('.total').text(total.toFixed(2) + ' ' + currency);
        };

        $('#amount').on('keyup input', onAmountChange);

        // Auto-trigger if bankId is already set (e.g. Member Transfer)
        if ($('#bankId').val()) {
            onChangeBank();
        }
    });
</script>
@endsection

