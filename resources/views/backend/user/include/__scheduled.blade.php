@if(request('tab') == 'scheduled')
<div @class(['tab-pane fade', 'show active' => request('tab') == 'scheduled'])
    id="pills-scheduled"
    role="tabpanel"
    aria-labelledby="pills-scheduled-tab"
>
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
            <div class="site-card">
                <div class="site-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="title"><i data-lucide="calendar-clock" class="me-2 text-primary"></i>{{ __('Scheduled & Recurring Transactions') }}</h4>
                        <p class="text-muted small mb-0">{{ __('Automate ongoing payroll direct deposits, recurring loan auto-debits, or future-dated ledger entries.') }}</p>
                    </div>
                    <button type="button" class="site-btn-sm primary-btn" data-bs-toggle="modal" data-bs-target="#armScheduleModal">
                        <i data-lucide="plus-circle"></i> {{ __('Schedule New Transaction') }}
                    </button>
                </div>
                <div class="site-card-body">
                    @if($scheduledTransactions->isEmpty())
                        <div class="text-center py-5">
                            <i data-lucide="calendar-x" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                            <h5 class="text-muted">{{ __('No recurring transactions scheduled for this member yet.') }}</h5>
                            <p class="text-muted small">{{ __('Set up recurring direct deposits or auto-debits to keep the account actively banked over time.') }}</p>
                            <button type="button" class="site-btn-sm primary-btn mt-2" data-bs-toggle="modal" data-bs-target="#armScheduleModal">
                                <i data-lucide="plus-circle"></i> {{ __('Arm First Schedule') }}
                            </button>
                        </div>
                    @else
                        <div class="site-table table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Title / Description') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Target Wallet') }}</th>
                                        <th>{{ __('Frequency') }}</th>
                                        <th>{{ __('Next Run Date') }}</th>
                                        <th>{{ __('Executions') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($scheduledTransactions as $schedule)
                                        <tr>
                                            <td>
                                                <strong class="text-dark">{{ $schedule->title }}</strong>
                                                @if($schedule->notes)
                                                    <div class="text-muted small"><i data-lucide="info" style="width: 12px; height: 12px;"></i> {{ $schedule->notes }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($schedule->type === 'credit')
                                                    <span class="site-badge primary">{{ __('Credit (Inflow)') }}</span>
                                                @else
                                                    <span class="site-badge warning">{{ __('Debit (Outflow)') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong class="{{ $schedule->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                                    {{ $schedule->type === 'credit' ? '+' : '-' }}{{ $currencySymbol }}{{ number_format($schedule->amount, 2) }}
                                                </strong>
                                            </td>
                                            <td><span class="badge bg-light text-dark border">{{ $schedule->wallet_label }}</span></td>
                                            <td><span class="text-muted">{{ $schedule->frequency_label }}</span></td>
                                            <td>
                                                <code>{{ $schedule->next_run_date ? $schedule->next_run_date->format('M d, Y') : 'N/A' }}</code>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-white">{{ $schedule->execution_count }} {{ __('times') }}</span>
                                                @if($schedule->last_run_date)
                                                    <div class="text-muted small" style="font-size: 10px;">{{ __('Last:') }} {{ $schedule->last_run_date->format('Y-m-d') }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($schedule->status === 1)
                                                    <span class="site-badge success">{{ __('Active') }}</span>
                                                @else
                                                    <span class="site-badge pending">{{ __('Paused') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <form method="POST" action="{{ route('admin.user.scheduled.toggle', $schedule->id) }}">
                                                        @csrf
                                                        <button type="submit" class="site-btn-xs {{ $schedule->status ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $schedule->status ? 'Pause' : 'Resume' }}">
                                                            <i data-lucide="{{ $schedule->status ? 'pause' : 'play' }}" style="width: 14px; height: 14px;"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.user.scheduled.delete', $schedule->id) }}" onsubmit="return confirm('Remove this scheduled recurring transaction?');">
                                                        @csrf
                                                        <button type="submit" class="site-btn-xs btn-outline-danger" title="{{ __('Delete') }}">
                                                            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Arm New Scheduled Recurring Transaction -->
<div class="modal fade" id="armScheduleModal" tabindex="-1" aria-labelledby="armScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.user.scheduled.store', $user->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="armScheduleModalLabel"><i data-lucide="calendar-plus" class="me-2 text-primary"></i>{{ __('Arm Recurring Transaction') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('Transaction Title / Description') }} <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. ADP Payroll Direct Deposit, Mortgage Auto-Debit" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Amount') }} ({{ $currencySymbol }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="3850.00" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Transaction Type') }} <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="credit">{{ __('Credit (Inflow / Deposit)') }}</option>
                                <option value="debit">{{ __('Debit (Outflow / Deduction)') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Target Wallet') }} <span class="text-danger">*</span></label>
                            <select name="wallet_type" class="form-select" required>
                                <option value="default">{{ __('Checking Account') }} ({{ $currencySymbol }}{{ number_format($user->balance, 2) }})</option>
                                <option value="primary_savings">{{ __('Primary Savings') }} ({{ $currencySymbol }}{{ number_format($user->savings_balance, 2) }})</option>
                                <option value="ira">{{ __('IRA Account') }}</option>
                                <option value="heloc">{{ __('HELOC Account') }}</option>
                                <option value="cc">{{ __('Credit Card Account') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Recurrence Frequency') }} <span class="text-danger">*</span></label>
                            <select name="frequency" class="form-select" required>
                                <option value="biweekly_1_15">{{ __('Bi-Weekly (1st & 15th)') }}</option>
                                <option value="monthly_1st">{{ __('Monthly (1st of Month)') }}</option>
                                <option value="monthly_15th">{{ __('Monthly (15th of Month)') }}</option>
                                <option value="monthly_end">{{ __('Monthly (End of Month)') }}</option>
                                <option value="weekly_monday">{{ __('Weekly (Every Monday)') }}</option>
                                <option value="daily">{{ __('Daily') }}</option>
                                <option value="once">{{ __('One-Time (Future Date)') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('First Execution Date') }} ({{ __('Optional - defaults to next recurrence') }})</label>
                        <input type="date" name="next_run_date" class="form-control" value="{{ now()->addDay()->format('Y-m-d') }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">{{ __('Internal Notes / Memo') }}</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Set up per member request for employer payroll simulation">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="site-btn-sm red-btn" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="site-btn-sm primary-btn"><i data-lucide="check-circle"></i> {{ __('Arm Schedule') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
