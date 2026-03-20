@php
    $riskScore = data_get($manual_field, 'risk_score', 0);
    $riskFlags = data_get($manual_field, 'risk_flags', []);
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="title mb-0">{{ __('Fund Transfer Review') }}</h3>
    @if($riskScore > 0)
        <div class="risk-badge p-2 px-3 rounded-pill d-inline-flex align-items-center {{ $riskScore >= 60 ? 'bg-danger text-white' : 'bg-warning text-dark' }}">
            <i data-lucide="shield-alert" class="me-2"></i>
            <span class="fw-bold">{{ __('Risk Score') }}: {{ $riskScore }}/100</span>
        </div>
    @endif
</div>

@if(!empty($riskFlags))
<div class="alert alert-soft-danger d-flex align-items-center mb-4 border-0" style="background: rgba(220, 53, 69, 0.1); border-radius: 12px;">
    <i data-lucide="alert-circle" class="text-danger me-3" style="width: 24px; height: 24px;"></i>
    <div>
        <div class="fw-bold text-danger">{{ __('Security Flags Detected') }}</div>
        <div class="small text-muted">{{ implode(', ', $riskFlags) }}</div>
    </div>
</div>
@endif

<div class="row g-4">
    <div class="col-xl-7">
        <div class="row g-4">
            <div class="col-12">
                <div class="site-card h-100">
                    <div class="site-card-header d-flex justify-content-between align-items-center">
                        <h4 class="title-small mb-0">{{ __('Transaction Intelligence') }}</h4>
                        <span class="badge bg-light text-dark border">{{ $transaction->tnx }}</span>
                    </div>
                    <div class="site-card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-3 px-4">{{ __('Sender') }}</td>
                                        <td class="fw-bold py-3 px-4">
                                            {{ $transaction->user->full_name }} 
                                            <span class="text-muted small">(@ {{ $transaction->user->username }})</span>
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-3 px-4">{{ __('Transfer Type') }}</td>
                                        <td class="py-3 px-4 text-primary fw-bold">
                                            {{ ucfirst(str_replace('_', ' ', $transaction->transfer_type->value)) }}
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-3 px-4">{{ __('Amount') }}</td>
                                        <td class="py-3 px-4 h5 mb-0 fw-bold">
                                            {{ setting('site_currency', 'global') }} {{ number_format($transaction->amount, 2) }}
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-3 px-4">{{ __('Fee / Charge') }}</td>
                                        <td class="py-3 px-4 text-danger fw-bold">
                                            + {{ setting('site_currency', 'global') }} {{ number_format($transaction->charge, 2) }}
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-3 px-4">{{ __('Total Deduction') }}</td>
                                        <td class="py-3 px-4 text-dark fw-bold" style="font-size: 1.1rem">
                                            {{ setting('site_currency', 'global') }} {{ number_format($transaction->final_amount, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-3 px-4">{{ __('Recipient Account') }}</td>
                                        <td class="py-3 px-4 fw-bold">
                                            {{ $transaction->beneficiary->account_number ?? data_get($manual_field, 'account_number') }}
                                            <div class="small text-muted fw-normal">
                                                {{ $transaction->beneficiary->account_name ?? data_get($manual_field, 'account_name') }}
                                                @if($transaction->bank_id != 0)
                                                    <br><span class="text-primary">{{ \App\Models\OthersBank::find($transaction->bank_id)->name ?? 'External' }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($manual_field['routing_number']) || isset($manual_field['bank_name']))
            <div class="col-12">
                <div class="site-card">
                    <div class="site-card-header">
                        <h4 class="title-small">{{ __('External Routing Details') }}</h4>
                    </div>
                    <div class="site-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small text-uppercase">{{ __('Bank Name') }}</label>
                                <div class="fw-bold">{{ data_get($manual_field, 'bank_name', 'N/A') }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small text-uppercase">{{ __('Routing Number') }}</label>
                                <div class="fw-bold">{{ data_get($manual_field, 'routing_number', 'N/A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="col-xl-5">
        <div class="site-card shadow-sm border-0 mb-4" style="background: #f8faff;">
            <div class="site-card-header bg-transparent border-0 pt-4 px-4">
                <h4 class="title-small d-flex align-items-center">
                    <i data-lucide="history" class="me-2 text-primary"></i>
                    {{ __('360° Activity View') }}
                </h4>
            </div>
            <div class="site-card-body px-4 pb-4">
                <p class="text-muted small mb-3">{{ __('Recent user transactions for context:') }}</p>
                <div class="activity-timeline">
                    @forelse($recent_transactions as $rt)
                        <div class="activity-item d-flex pb-3 @if(!$loop->last) border-left @endif" style="position: relative;">
                            <div class="dot" style="width: 10px; height: 10px; border-radius: 50%; background: #00549b; position: absolute; left: -5px; top: 5px;"></div>
                            <div class="ms-4">
                                <div class="small fw-bold">{{ $rt->description }}</div>
                                <div class="extra-small text-muted d-flex justify-content-between" style="font-size: 0.75rem">
                                    <span>{{ $rt->created_at->format('M d, H:i') }}</span>
                                    <span class="{{ $rt->status->value == 'success' ? 'text-success' : 'text-danger' }} fw-bold">
                                        {{ setting('site_currency', 'global') }}{{ number_format($rt->amount, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            {{ __('No prior transaction history found.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        @if($transaction->status == \App\Enums\TxnStatus::Pending)
        <div class="site-card border-primary">
            <div class="site-card-header bg-primary text-white">
                <h4 class="title-small mb-0">{{ __('Administrative Action') }}</h4>
            </div>
            <div class="site-card-body">
                <form action="{{ route('admin.fund.transfer.action.now') }}" method="post" id="actionForm">
                    @csrf
                    <input type="hidden" name="id" value="{{ $id }}">
                    
                    <div class="site-input-groups">
                        <label class="box-input-label">{{ __('Decision Notes / Rejection Reason') }}</label>
                        <textarea name="message" id="actionReason" class="form-textarea mb-3" placeholder="Enter decision notes..."></textarea>
                        
                        <div class="rejection-templates mb-3">
                            <label class="small text-muted mb-2 d-block">{{ __('Quick Reason Templates:') }}</label>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1" onclick="setReason('Insufficient funds in uncollected deposits.')">Funds</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1" onclick="setReason('Invalid recipient account details.')">Recipient</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1" onclick="setReason('Transaction flagged for security verification.')">Security</button>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="status" value="success" class="site-btn-sm primary-btn w-100 py-2">
                            <i data-lucide="check-circle" class="me-2"></i>
                            {{ __('Approve Transfer') }}
                        </button>
                        <button type="submit" name="status" value="failed" class="site-btn-sm red-btn w-100 py-2">
                            <i data-lucide="x-circle" class="me-2"></i>
                            {{ __('Reject Transfer') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
            <div class="alert alert-info py-3 text-center border-0" style="border-radius: 12px;">
                <i data-lucide="info" class="mb-2"></i>
                <div class="fw-bold">{{ __('Action Already Taken') }}</div>
                <div class="small">Performed on {{ $transaction->updated_at->format('M d, Y') }}</div>
                @if($transaction->action_message)
                    <div class="mt-2 p-2 bg-white rounded small text-dark border">{{ $transaction->action_message }}</div>
                @endif
            </div>
        @endif
    </div>
</div>

<style>
    .activity-timeline .activity-item {
        border-left: 2px solid #edeff2;
        margin-left: 5px;
    }
    .extra-small { font-size: 0.7rem; }
</style>

<script>
    lucide.createIcons();
    function setReason(text) {
        document.getElementById('actionReason').value = text;
    }
</script>
