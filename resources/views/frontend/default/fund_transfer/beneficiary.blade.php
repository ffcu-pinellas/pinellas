@extends('frontend::layouts.user')

@section('title')
    {{ __('Manage Recipients') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10 col-12">
        <div class="d-flex align-items-center justify-content-between mb-5">
            <div>
                <h1 class="h3 fw-bold mb-1">Recipients</h1>
                <p class="text-muted mb-0">Manage the people and accounts you send money to.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addBox">
                <i class="fas fa-user-plus me-2"></i> Add Recipient
            </button>
        </div>

        <div class="site-card border-0 shadow-sm overflow-hidden">
            <div class="site-card-body p-0">
                <div class="recipient-list">
                    @forelse ($beneficiary as $item)
                        <div class="recipient-item p-4 d-flex align-items-center justify-content-between border-bottom hover-bg-light transition-all">
                            <div class="d-flex align-items-center gap-4">
                                <div class="recipient-avatar bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 52px; height: 52px;">
                                    {{ substr($item->account_name, 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">{{ $item->account_name }}</h6>
                                    <div class="text-muted small fw-600">
                                        {{ $item->bank_id == null ? 'Own Bank' : $item->bank->name }} • Account ...{{ substr($item->account_number, -4) }}
                                        @if($item->nick_name) <span class="ms-1 text-primary">({{ $item->nick_name }})</span> @endif
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 editBtn" 
                                    data-id="{{ $item->id }}"
                                    data-bank-id="{{ $item->bank_id ?? '0' }}"
                                    data-number="{{ $item->account_number }}"
                                    data-account="{{ $item->account_name }}"
                                    data-branch="{{ $item->branch_name }}"
                                    data-nickname="{{ $item->nick_name }}"
                                    data-bs-toggle="modal" data-bs-target="#editBox">Edit</button>
                                <button class="btn btn-outline-danger btn-sm rounded-pill px-3 dltBtn" data-id="{{ $item->id }}" data-bs-toggle="modal" data-bs-target="#deleteBox">Delete</button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-5">
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-users text-muted fs-2"></i>
                            </div>
                            <h5 class="fw-bold">No recipients yet</h5>
                            <p class="text-muted">Start by adding someone you'd like to send money to.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@include('frontend::fund_transfer.include.__delete_beneficiary')
@include('frontend::fund_transfer.include.__edit_beneficiary')
@include('frontend::fund_transfer.include.__add_beneficiary')

@endsection

@section('script')
<script>
    $(document).ready(function() {
        "use strict";
        
        // Delete Btn Click
        $(document).on('click', '.dltBtn', function() {
            $("#dltId").val($(this).data('id'));
        });

        // Edit Btn Click
        $(document).on('click', '.editBtn', function() {
            let id = $(this).data('id');
            let bank_id = $(this).data('bank-id');
            let number = $(this).data('number');
            let account = $(this).data('account');
            let branch = $(this).data('branch');
            let nickname = $(this).data('nickname');

            $("#edit_id").val(id);
            $("#edit_bank_name").val(bank_id).change();
            $("#edit_account_number").val(number);
            $("#edit_account_name").val(account);
            $("#edit_branch_name").val(branch);
            $("#edit_nick_name").val(nickname);
        });
    });
</script>
<style>
    .hover-bg-light:hover { background-color: rgba(0,0,0,0.02); }
    .transition-all { transition: all 0.2s ease; }
</style>
@endsection


@section('script')
    <script>
        $(document).ready(function() {
            "use strict"
            var currency = @json($currency);

            var charge_type, charge;

            // nice select
            $('.add-beneficiary').niceSelect();
            $('.edit-beneficiary').niceSelect();

            // own bank select event
            var isPhysicalBank = '{{ setting('multi_branch', 'permission') }}';

            if (!$('#edit_bank_name').val() && !isPhysicalBank) {
                $('#branch_name_sec').addClass('d-none');
            } else {
                $('#branch_name_sec').removeClass('d-none');
            }

            $(document).on('change', '.bank_name', function(e) {
                if ($(this).val() == null) {
                    $('#branch_name_sec').hide();
                } else {
                    $('#branch_name_sec').show();
                }
            })

            // View Details
            $(document).on('click', '.viewBtn', function() {
                let name = $(this).data('name');
                let number = $(this).data('number');
                let account = $(this).data('account');
                let branch = $(this).data('branch');
                let nickname = $(this).data('nickname');
                let id = $(this).data('id');
                let bank_id = $(this).data('bank-id');
                let daily_limit = $(this).data('daily-limit');
                let monthly_limit = $(this).data('monthly-limit');
                let charge = $(this).data('charge');
                let charge_type = $(this).data('charge-type');

                $("#bank_name").text(name);
                $("#account_number").text(number);
                $("#account_name").text(account);
                $("#branch_name").text(branch);
                $("#nick_name").text(nickname);
                $('.daily_limit').text('Max ' + daily_limit + ' ' + currency)
                $('.monthly_limit').text('Max ' + monthly_limit + ' ' + currency)
                $("#beneficiary_id").val(id);
                $("#bank_id").val(bank_id);
                $('.charge').text(charge + ' ' + (charge_type === 'percentage' ? ' % ' : currency))

                if (bank_id == 0) {
                    $('#daily-limit').hide();
                    $('#monthly-limit').hide();
                    $('.daily_limit').text('');
                    $('.monthly_limit').text('');
                } else {
                    $('.daily_limit').text('Max ' + daily_limit + ' ' + currency);
                    $('.monthly_limit').text('Max ' + monthly_limit + ' ' + currency);
                    $('#daily-limit').show();
                    $('#monthly-limit').show();
                }
            });

            // Delete Btn Click
            $(document).on('click', '.dltBtn', function() {
                let dataId = $(this).data('id');
                $("#dltId").val(dataId);
            });

            //edit Btn
            $(document).on('click', '.editBtn', function() {
                let name = $(this).data('name');
                let number = $(this).data('number');
                let account = $(this).data('account');
                let branch = $(this).data('branch');
                let nickname = $(this).data('nickname');
                let id = $(this).data('id');
                let bank_id = $(this).data('bank-id');

                $("#edit_id").val(id);
                $("#edit_bank_name").val(bank_id ?? '0').niceSelect('update');;
                $("#edit_account_number").val(number);
                $("#edit_account_name").val(account);
                $("#edit_branch_name").val(branch);
                $("#edit_nick_name").val(nickname);
            });

            $(document).on('click', '.sendMoneyBtn', function() {
                charge_type = $(this).data('charge-type');
                charge = $(this).data('charge');
                let id = $(this).data('id');

                $.ajax({
                    type: 'GET',
                    url: '/user/fund-transfer/beneficiary/show/' + id,
                    success: function(data) {
                        $("#beneficiary_id").val(id);
                        $("#send_bank_id").val(data.beneficiary.bank_id);
                        $('.min-max').text('Minimum ' + data.bank.minimum_transfer + ' ' +
                            currency + ' and ' + 'Maximum ' + data.bank.maximum_transfer +
                            ' ' + currency)
                    },
                    error: function(error) {
                        console.error('Error fetching beneficiaries:', error);
                    }
                });
            });

            $('#send-money-amount').on('keyup', function(e) {
                var amount = $(this).val();

                $('.transfer_amount').text((Number(amount) + ' ' + currency))
                $('.currency').text(currency)
                var total_charge = charge_type === 'percentage' ? amount * charge / 100 : charge
                $('.transfer_charge').text(total_charge + ' ' + currency)
                var total = (Number(amount) + Number(total_charge));
                $('.total_transfer').text(total + ' ' + currency)
            });
        })
    </script>
@endsection
