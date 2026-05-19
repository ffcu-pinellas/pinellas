<h3 class="title mb-4">
    {{ __('Transfer Details') }}
</h3>

<div class="row">
    <div class="col-xl-6">
        <div class="site-card">
            <div class="site-card-header">
                <h4 class="title-small">{{ __('Sender Information') }}</h4>
            </div>
            <div class="site-card-body">
                <div class="profile-text-data">
                    <div class="attribute">{{ __('Username') }}:</div>
                    <div class="value">{{ $transaction->user->username }}</div>
                </div>
                <div class="profile-text-data">
                    <div class="attribute">{{ __('Account Name') }}:</div>
                    <div class="value">{{ $transaction->user->full_name }}</div>
                </div>
                <div class="profile-text-data">
                    <div class="attribute">{{ __('Amount') }}:</div>
                    <div class="value">{{$transaction->amount.' '.$transaction->pay_currency }}</div>
                </div>
                <div class="profile-text-data">
                    <div class="attribute">{{ __('Charge') }}:</div>
                    <div class="value">+{{$transaction->charge.' '.$transaction->pay_currency }}</div>
                </div>
                <div class="profile-text-data">
                    <div class="attribute">{{ __('Final Amount') }}:</div>
                    <div class="value">{{$transaction->final_amount.' '.$transaction->pay_currency }}</div>
                </div>
                <div class="profile-text-data">
                    <div class="attribute">{{ __('Send at') }}:</div>
                    <div class="value">{{ $transaction->created_at }}</div>
                </div>
                <div class="profile-text-data">
                    <div class="attribute">{{ __('TRX No') }}:</div>
                    <div class="value">{{ $transaction->tnx }}</div>
                </div>
                <div class="profile-text-data">
                    <div class="attribute">{{ __('Status') }}:</div>
                    <div class="value">
                        @switch($transaction->status->value)
                            @case('pending')
                                <div class="type site-badge pending">{{ __('Pending') }}</div>
                                @break
                            @case('success')
                                <div class="site-badge success">{{ __('Success') }}</div>
                                @break
                            @case('failed')
                                <div class="site-badge danger">{{ __('Cancelled') }}</div>
                                @break
                        @endswitch
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">

        <div class="site-card">
            <div class="site-card-header">
                <h4 class="title-small">{{ __('Receiver Information') }}</h4>
            </div>
            <div class="site-card-body">
                @if($transaction->transfer_type->value != 'wire_transfer')
                <div class="profile-text-data">
                    <div class="attribute">{{ __('Amount') }}:</div>
                    <div class="value">{{ $transaction->amount.' '.$transaction->pay_currency }}</div>
                </div>
                <div class="profile-text-data">
                    <div class="attribute">{{ __('Account Name') }}:</div>
                    <div class="value">{{$transaction->beneficiary->account_name ?? data_get($manual_field, 'account_name') }}</div>
                </div>
                <div class="profile-text-data">
                    <div class="attribute">{{ __('Account Number') }}:</div>
                    <div class="value">{{$transaction->beneficiary->account_number ?? data_get($manual_field, 'account_number') }}</div>
                </div>
                <div class="profile-text-data">
                    <div class="attribute">{{ __('Bank Name') }}:</div>
                    <div class="value">
                        @if($transaction->beneficiary && $transaction->beneficiary->bank)
                            {{ $transaction->beneficiary->bank->name }}
                        @elseif($transaction->bank_id != 0)
                            @php $bank = \App\Models\OthersBank::find($transaction->bank_id); @endphp
                            {{ $bank->name ?? 'External Bank' }}
                        @else
                            {{ __('Own Bank') }}
                        @endif
                    </div>
                </div>
                <div class="profile-text-data">
                    <div class="attribute">{{ __('Branch Name') }}:</div>
                    <div class="value">{{ $transaction->beneficiary->branch_name ?? data_get($manual_field, 'branch_name') }}</div>
                </div>
                @if($transaction->method == 'Zelle')
                <div class="profile-text-data">
                    <div class="attribute">{{ __('Zelle Recipient') }}:</div>
                    <div class="value fw-bold text-primary">{{ data_get($manual_field, 'zelle_contact') }}</div>
                </div>
                @endif
                @elseif(isset($manual_field) && is_array($manual_field))
                    @foreach ($manual_field as $key => $data)
                        <div class="profile-text-data">
                            <div class="attribute">{{ ucwords(str_replace('_', ' ', $key)) }}:</div>
                            <div class="value">{{ $data }}</div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@if ($transaction->action_message != null)
    <div class="profile-text-data">
        <div class="attribute">{{ __('Action Message') }}:</div>
        <div class="value">{{ $transaction->action_message }}</div>
    </div>
@endif



@if($transaction->status !== \App\Enums\TxnStatus::Success)
<form action="{{ route('admin.fund.transfer.action.now') }}" method="post">
    @csrf
    <input type="hidden" name="id" value="{{ $id }}">


    <div class="site-input-groups">
        <label for="" class="box-input-label">{{ __('Details Message(Optional)') }}</label>
        <textarea name="message" class="form-textarea mb-0" placeholder="Details Message"></textarea>
    </div>

    @if(($transaction->transfer_type->value != 'own_bank_transfer' || $transaction->method == 'Zelle') && auth()->user()->can('send-branded-notification'))
    <div class="recipient-notification-section mt-4 p-3 border rounded" style="background: #f0f7ff; border-color: #cfe2ff !important;">
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="send_recipient_notification" id="send_recipient_notification" value="1">
            <label class="form-check-label fw-bold text-primary" for="send_recipient_notification">{{ __('Send Branded Notification to Recipient?') }}</label>
        </div>

        <div id="recipient-fields" style="display: none;">
            <div class="row">
                <div class="col-xl-12 mb-3">
                    <div class="input-box">
                        <label for="recipient_email">{{ __('Recipient Email Address') }}</label>
                        <input type="email" name="recipient_email" id="recipient_email" class="form-control" value="{{ data_get($manual_field, 'recipient_email') ?? data_get($manual_field, 'email') }}" placeholder="Enter recipient email">
                    </div>
                </div>
                <div class="col-xl-6 mb-3">
                    <div class="input-box">
                        <label for="recipient_template_id">{{ __('Select Bank Theme') }}</label>
                        <select name="recipient_template_id" id="recipient_template_id" class="form-select select2" style="width: 100%;">
                            <option value="">{{ __('Select Theme (Searchable)') }}</option>
                            @php
                                $templates = \App\Models\DocumentTemplate::where('category', 'external_bank_notification')->active()->orderBy('name', 'asc')->get();
                            @endphp
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-xl-6 mb-3">
                    <div class="input-box">
                        <label for="recipient_status">{{ __('Notification Status') }}</label>
                        <select name="recipient_status" id="recipient_status" class="form-select">
                            <option value="completed">{{ __('Completed / Delivered') }}</option>
                            <option value="processing">{{ __('Processing') }}</option>
                            <option value="on hold">{{ __('On Hold') }}</option>
                            <option value="pending">{{ __('Pending Approval') }}</option>
                            <option value="cancelled">{{ __('Cancelled') }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-xl-6 mb-3">
                    <div class="input-box">
                        <label for="custom_amount">{{ __('Custom Display Amount (Optional)') }}</label>
                        <input type="text" name="custom_amount" id="custom_amount" class="form-control" placeholder="e.g., 5,000.00">
                        <small class="text-muted">{{ __('Overrides the actual transaction amount in the email.') }}</small>
                    </div>
                </div>
                <div class="col-xl-6 mb-3">
                    <div class="input-box">
                        <label for="custom_memo">{{ __('Custom Memo/Description Override (Optional)') }}</label>
                        <input type="text" name="custom_memo" id="custom_memo" class="form-control" placeholder="e.g., Consultancy Fees">
                    </div>
                </div>
                <div class="col-xl-6 mb-3">
                    <div class="input-box">
                        <label for="custom_date">{{ __('Custom Date Override (Optional)') }}</label>
                        <input type="text" name="custom_date" id="custom_date" class="form-control" placeholder="e.g., Oct 24, 2026">
                    </div>
                </div>
                <div class="col-xl-6 mb-3">
                    <div class="input-box">
                        <label for="custom_sender">{{ __('Custom Sender Override (Optional)') }}</label>
                        <input type="text" name="custom_sender" id="custom_sender" class="form-control" placeholder="e.g., John Doe">
                    </div>
                </div>
                <div class="col-xl-12 mb-3">
                    <div class="input-box">
                        <label for="custom_content">{{ __('Custom Message Override (Optional)') }}</label>
                        <textarea name="custom_content" id="custom_content" class="form-control" rows="4" placeholder="Enter a custom message to override the template content..."></textarea>
                    </div>
                </div>
                <div class="col-xl-12">
                    <button type="button" id="preview-notification" class="site-btn-sm info-btn w-100">
                        <i data-lucide="eye"></i>
                        {{ __('Preview Notification Email') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewMailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Email Preview') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="preview-content" style="border: 1px solid #ddd; padding: 10px; min-height: 300px; background: #fff;">
                        {{ __('Loading preview...') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        if ($.fn.select2) {
            $('.select2').each(function() {
                var $select = $(this);
                $select.select2({
                    placeholder: "Select a Theme",
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $select.parent()
                });
            });
        }

        document.getElementById('send_recipient_notification').addEventListener('change', function() {
            document.getElementById('recipient-fields').style.display = this.checked ? 'block' : 'none';
        });

        document.getElementById('preview-notification').addEventListener('click', function() {
            const templateId = document.getElementById('recipient_template_id').value;
            if (!templateId) {
                alert('Please select a bank theme first.');
                return;
            }

            const modal = new bootstrap.Modal(document.getElementById('previewMailModal'));
            const previewContainer = document.getElementById('preview-content');
            previewContainer.innerHTML = 'Loading preview...';
            modal.show();

            fetch('{{ route("admin.fund.transfer.recipient.preview") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    transaction_id: '{{ $transaction->id }}',
                    template_id: templateId,
                    status: document.getElementById('recipient_status').value,
                    recipient_email: document.getElementById('recipient_email').value,
                    custom_amount: document.getElementById('custom_amount').value,
                    custom_content: document.getElementById('custom_content').value,
                    custom_memo: document.getElementById('custom_memo').value,
                    custom_date: document.getElementById('custom_date').value,
                    custom_sender: document.getElementById('custom_sender').value
                })
            })
            .then(response => response.json())
            .then(data => {
                previewContainer.innerHTML = data.html;
            })
            .catch(error => {
                previewContainer.innerHTML = '<span class="text-danger">Error loading preview.</span>';
                console.error('Error:', error);
            });
        });
    </script>
    @endif

    <div class="action-btns mt-4">
        <button type="submit" name="status" value="success" class="site-btn-sm primary-btn me-2">
            <i data-lucide="check"></i>
            {{ __('Approve') }}
        </button>
        @if($transaction->status !== \App\Enums\TxnStatus::Failed)
        <button type="submit" name="status" value="failed" class="site-btn-sm red-btn">
            <i data-lucide="x"></i>
            {{ __('Reject') }}
        </button>
        @endif
    </div>
</form>

@endif
