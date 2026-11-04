@extends('backend.layouts.app')
@section('title')
    {{ __('Share Dividend & APY Manager') }}
@endsection

@section('content')
<div class="main-content">
    <div class="page-title">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <div class="title-content">
                        <h2 class="title">{{ __('Share Dividend & APY Engine') }}</h2>
                        <a href="{{ route('admin.dashboard') }}" class="title-btn"><i data-lucide="corner-down-left"></i>{{ __('Dashboard') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- 4 Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card border-0 shadow-sm rounded-3 p-3" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase small fw-bold" style="color: #94a3b8; letter-spacing: 0.5px;">{{ __('Default Savings APY') }}</span>
                            <h2 class="fw-bold mt-1 mb-0 text-white">{{ number_format($globalSettings['default_apy'], 2) }}%</h2>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: rgba(255,255,255,0.1);">
                            <i data-lucide="percent" style="width: 24px; height: 24px; color: #38bdf8;"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small" style="color: #cbd5e1 !important; font-size: 11px;">
                        {{ __('Status:') }} 
                        <span class="badge {{ $globalSettings['enabled'] ? 'bg-success' : 'bg-danger' }}">
                            {{ $globalSettings['enabled'] ? __('ACTIVE') : __('PAUSED') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase small fw-bold text-muted" style="letter-spacing: 0.5px;">{{ __('Eligible Members') }}</span>
                            <h2 class="fw-bold mt-1 mb-0 text-dark">{{ $preview['eligible_count'] }}</h2>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: #f0fdf4;">
                            <i data-lucide="users" style="width: 24px; height: 24px; color: #16a34a;"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small" style="font-size: 11px;">
                        {{ __('Dividend accrual enabled & active') }}
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase small fw-bold text-muted" style="letter-spacing: 0.5px;">{{ __('Next Payout Pool') }}</span>
                            <h2 class="fw-bold mt-1 mb-0 text-primary">{{ setting('currency_symbol', '$') }}{{ number_format($preview['total_payout_amount'], 2) }}</h2>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: #eff6ff;">
                            <i data-lucide="coins" style="width: 24px; height: 24px; color: #2563eb;"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small" style="font-size: 11px;">
                        {{ __('Calculated monthly dividend total') }}
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase small fw-bold text-muted" style="letter-spacing: 0.5px;">{{ __('Historical Paid') }}</span>
                            <h2 class="fw-bold mt-1 mb-0 text-success">{{ setting('currency_symbol', '$') }}{{ number_format($totalHistoricalPaid, 2) }}</h2>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: #fefce8;">
                            <i data-lucide="award" style="width: 24px; height: 24px; color: #ca8a04;"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small" style="font-size: 11px;">
                        {{ __('Across') }} {{ $totalBatches }} {{ __('recorded batches') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Eligible Members & Dry-run Action -->
            <div class="col-xl-8 col-lg-12">
                <div class="site-card">
                    <div class="site-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h4 class="title mb-0">{{ __('Eligible Members Payout Queue') }}</h4>
                            <div class="text-muted small mt-1">{{ __('Members with Dividend Accrual ON and qualifying balances.') }}</div>
                        </div>
                        <div class="d-flex gap-2">
                            @if($preview['eligible_count'] > 0)
                                <button type="button" class="site-btn-sm primary-btn d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#confirmBatchModal">
                                    <i data-lucide="play" style="width: 14px; height: 14px;"></i>
                                    <span>{{ __('Run Dividend Cycle Now') }}</span>
                                </button>
                            @else
                                <button type="button" class="site-btn-sm primary-btn disabled" disabled title="No members currently have dividend accrual enabled.">
                                    <i data-lucide="play" style="width: 14px; height: 14px;"></i>
                                    <span>{{ __('No Eligible Members') }}</span>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="site-card-body p-0">
                        <div class="table-responsive">
                            <table class="table site-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('Member') }}</th>
                                        <th>{{ __('Account') }}</th>
                                        <th>{{ __('Savings Balance') }}</th>
                                        <th>{{ __('APY Rate') }}</th>
                                        <th>{{ __('Monthly Dividend') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($preview['eligible_items'] as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div>
                                                        <a href="{{ route('admin.user.edit', $item['user']->id) }}" class="fw-bold text-dark">
                                                            {{ $item['user']->full_name }}
                                                        </a>
                                                        <div class="text-muted small">@{{ $item['user']->username }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ $item['wallet_name'] }}</span>
                                            </td>
                                            <td class="fw-bold">
                                                {{ setting('currency_symbol', '$') }}{{ number_format($item['balance'], 2) }}
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ number_format($item['apy'], 2) }}%</span>
                                                @if($item['user']->dividend_apy)
                                                    <small class="text-muted d-block" style="font-size: 10px;">{{ __('Custom') }}</small>
                                                @endif
                                            </td>
                                            <td class="text-success fw-bold">
                                                +{{ setting('currency_symbol', '$') }}{{ number_format($item['amount'], 2) }}
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.dividends.single-pay', $item['user']->id) }}" method="POST" onsubmit="return confirm('Credit ${{ number_format($item['amount'], 2) }} dividend to {{ $item['user']->full_name }} now?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2" style="font-size: 11px;">
                                                        {{ __('Credit Now') }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i data-lucide="info" class="mb-2 text-muted" style="width: 28px; height: 28px;"></i>
                                                <p class="mb-1">{{ __('No members are currently earning dividends.') }}</p>
                                                <small>{{ __('Dividends are OFF by default. To enable a member, visit Customer Details -> Account Information -> Share Dividend -> Enabled.') }}</small>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Audit Logs -->
                <div class="site-card mt-4">
                    <div class="site-card-header">
                        <h4 class="title mb-0">{{ __('Recent Dividend Payout Ledger') }}</h4>
                    </div>
                    <div class="site-card-body p-0">
                        <div class="table-responsive">
                            <table class="table site-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Batch ID') }}</th>
                                        <th>{{ __('Member') }}</th>
                                        <th>{{ __('APY') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Triggered By') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentLogs as $log)
                                        <tr>
                                            <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
                                            <td><code>{{ $log->batch_id }}</code></td>
                                            <td>
                                                @if($log->user)
                                                    <a href="{{ route('admin.user.edit', $log->user_id) }}">{{ $log->user->full_name }}</a>
                                                @else
                                                    User #{{ $log->user_id }}
                                                @endif
                                            </td>
                                            <td>{{ number_format($log->apy_rate, 2) }}%</td>
                                            <td class="text-success fw-bold">+{{ setting('currency_symbol', '$') }}{{ number_format($log->amount, 2) }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $log->triggered_by }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-3 text-muted">
                                                {{ __('No historical dividend payouts recorded yet.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Global Settings & Scheduler Guide -->
            <div class="col-xl-4 col-lg-12">
                <div class="site-card">
                    <div class="site-card-header">
                        <h4 class="title mb-0">{{ __('Global APY Settings') }}</h4>
                    </div>
                    <div class="site-card-body">
                        <form action="{{ route('admin.dividends.settings') }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small">{{ __('Automated Engine') }}</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="savings_dividend_enabled" id="savings_dividend_enabled" value="1" {{ $globalSettings['enabled'] ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="savings_dividend_enabled">{{ __('Enable Monthly Dividend Processing') }}</label>
                                </div>
                            </div>

                            <div class="mb-3 text-start">
                                <label class="form-label fw-bold small" for="savings_dividend_apy">{{ __('Default Savings APY Rate (%)') }}</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="99.99" class="form-control" name="savings_dividend_apy" id="savings_dividend_apy" value="{{ $globalSettings['default_apy'] }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="text-muted mt-1" style="font-size: 11px;">
                                    {{ __('Applied to all eligible members without a custom APY.') }}
                                </div>
                            </div>

                            <div class="mb-3 text-start">
                                <label class="form-label fw-bold small" for="savings_dividend_min_balance">{{ __('Minimum Balance Requirement') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ setting('currency_symbol', '$') }}</span>
                                    <input type="number" step="0.01" min="0" class="form-control" name="savings_dividend_min_balance" id="savings_dividend_min_balance" value="{{ $globalSettings['min_balance'] }}" required>
                                </div>
                                <div class="text-muted mt-1" style="font-size: 11px;">
                                    {{ __('Accounts below this balance earn $0.00 dividend.') }}
                                </div>
                            </div>

                            <div class="mb-3 text-start">
                                <label class="form-label fw-bold small" for="savings_dividend_description">{{ __('Ledger Description Format') }}</label>
                                <input type="text" class="form-control" name="savings_dividend_description" id="savings_dividend_description" value="{{ $globalSettings['desc_format'] }}" required>
                                <div class="text-muted mt-1" style="font-size: 11px;">
                                    {{ __('Use {apy} to auto-insert the calculated rate.') }}
                                </div>
                            </div>

                            <button type="submit" class="site-btn-sm primary-btn w-100 centered">
                                {{ __('Save APY Settings') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Hostinger Scheduled Cron Webhook Card -->
                <div class="site-card mt-4">
                    <div class="site-card-header">
                        <h4 class="title mb-0">{{ __('Cron Webhook Automation') }}</h4>
                    </div>
                    <div class="site-card-body" style="font-size: 12px; line-height: 1.6;">
                        <p class="text-muted mb-2">
                            {{ __('Dividends run automatically on the 1st of every month. You can hook this endpoint into your Hostinger Cron Job scheduler:') }}
                        </p>
                        <div class="p-2 rounded bg-light border text-break font-monospace small mb-3">
                            <code>{{ url('cron/dividends') }}</code>
                        </div>
                        <div class="alert alert-info py-2 px-3 small mb-0">
                            <strong>{{ __('Telegram Bot Integration:') }}</strong><br>
                            {{ __('Type') }} <code>/dividend</code> {{ __('to your Telegram bot anytime to view the live pool or run payouts without a laptop.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Confirm Batch Payout -->
@if($preview['eligible_count'] > 0)
<div class="modal fade" id="confirmBatchModal" tabindex="-1" aria-labelledby="confirmBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content site-table-modal shadow-lg border-0" style="border-radius: 16px;">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title d-flex align-items-center gap-2" id="confirmBatchModalLabel">
                    <i data-lucide="shield-check" class="text-success icon-sm"></i>
                    <span>{{ __('Confirm Monthly Dividend Distribution') }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.dividends.process') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i data-lucide="alert-triangle" class="text-warning"></i>
                            <div>
                                <strong>{{ __('Ready to process batch payout:') }}</strong>
                                {{ __('This will credit') }} <strong>{{ setting('currency_symbol', '$') }}{{ number_format($preview['total_payout_amount'], 2) }}</strong> 
                                {{ __('across') }} <strong>{{ $preview['eligible_count'] }}</strong> {{ __('members.') }}
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table site-table text-start">
                            <thead>
                                <tr>
                                    <th>{{ __('Member') }}</th>
                                    <th>{{ __('Balance') }}</th>
                                    <th>{{ __('APY') }}</th>
                                    <th>{{ __('Credit Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preview['eligible_items'] as $item)
                                    <tr>
                                        <td><strong>{{ $item['user']->full_name }}</strong></td>
                                        <td>{{ setting('currency_symbol', '$') }}{{ number_format($item['balance'], 2) }}</td>
                                        <td>{{ number_format($item['apy'], 2) }}%</td>
                                        <td class="text-success fw-bold">+{{ setting('currency_symbol', '$') }}{{ number_format($item['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top justify-content-between p-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
                        <i data-lucide="check-circle" style="width: 15px; height: 15px;"></i>
                        <span>{{ __('Commit & Credit All Dividends') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
