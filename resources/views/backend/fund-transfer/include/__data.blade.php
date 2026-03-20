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

<!-- 360-View: Recent Activity -->
<div class="site-card mt-4">
    <div class="site-card-header">
        <h4 class="title-small">{{ __('Account 360-View: Recent Transactions') }}</h4>
    </div>
    <div class="site-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                <thead class="bg-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('TRX') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $rtxn)
                        <tr>
                            <td>{{ $rtxn->created_at->format('M d, Y') }}</td>
                            <td><small class="text-muted">{{ $rtxn->tnx }}</small></td>
                            <td>{{ $rtxn->description }}</td>
                            <td class="fw-bold {{ $rtxn->type->value == 'deposit' || $rtxn->type->value == 'receive_money' ? 'text-success' : 'text-danger' }}">
                                {{ ($rtxn->type->value == 'deposit' || $rtxn->type->value == 'receive_money' ? '+' : '-') . $rtxn->amount . ' ' . $rtxn->pay_currency }}
                            </td>
                            <td>
                                <span class="badge {{ $rtxn->status->value == 'success' ? 'bg-success' : ($rtxn->status->value == 'pending' ? 'bg-warning' : 'bg-danger') }}" style="font-size: 0.7rem;">
                                    {{ ucfirst($rtxn->status->value) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">{{ __('No recent transactions found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
        <div class="mb-2">
            <span class="small text-muted me-2">{{ __('Quick Templates:') }}</span>
            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 small" onclick="document.querySelector('textarea[name=message]').value = 'Incorrect recipient details provided. Please verify and try again.'">Info Mismatch</button>
            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 small" onclick="document.querySelector('textarea[name=message]').value = 'Insufficient funds for this transfer and associated fees.'">Low Balance</button>
            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 small" onclick="document.querySelector('textarea[name=message]').value = 'Verification required. Please contact support to authorize this transfer.'">Needs Verification</button>
        </div>
        <textarea name="message" class="form-textarea mb-0" placeholder="Details Message"></textarea>
    </div>

    <div class="action-btns mt-3">
        <button type="submit" name="status" value="success" class="site-btn-sm primary-btn me-2">
            <i data-lucide="check"></i>
            {{ __('Approve Transfer') }}
        </button>
        @if($transaction->status !== \App\Enums\TxnStatus::Failed)
        <button type="submit" name="status" value="failed" class="site-btn-sm red-btn">
            <i data-lucide="x"></i>
            {{ __('Reject Transfer') }}
        </button>
        @endif
    </div>
</form>

@endif
