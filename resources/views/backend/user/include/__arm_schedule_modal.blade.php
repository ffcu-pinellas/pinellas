<!-- Modal: Arm New Scheduled Recurring Transaction -->
<div class="modal fade" id="armScheduleModal" tabindex="-1" aria-labelledby="armScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.user.scheduled.store', $user->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center" id="armScheduleModalLabel">
                        <i data-lucide="calendar-clock" class="me-2 text-primary"></i>{{ __('Arm Recurring Transaction') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-3">
                        <i data-lucide="info" class="me-2 text-info" style="min-width: 16px;"></i>
                        <span>{{ __('Automate recurring payroll direct deposits, auto-debits, or future ledger entries for') }} <strong>{{ $user->full_name }}</strong>.</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('Transaction Title / Description') }} <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. ADP Payroll Direct Deposit, Mortgage Auto-Debit" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Amount') }} ({{ $currencySymbol ?? '$' }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="3850.00" required>
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
                                <option value="default">{{ __('Checking Account') }} ({{ $currencySymbol ?? '$' }}{{ number_format($user->balance, 2) }})</option>
                                <option value="primary_savings">{{ __('Primary Savings') }} ({{ $currencySymbol ?? '$' }}{{ number_format($user->savings_balance, 2) }})</option>
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
                        <label class="form-label fw-bold">{{ __('First Execution Date') }} <span class="text-muted small fw-normal">({{ __('Optional - defaults to next recurrence') }})</span></label>
                        <input type="date" name="next_run_date" class="form-control" value="{{ now()->addDay()->format('Y-m-d') }}">
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold">{{ __('Internal Notes / Memo') }} <span class="text-muted small fw-normal">({{ __('Optional') }})</span></label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Set up per member request for employer payroll simulation">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="site-btn-sm red-btn" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="site-btn-sm primary-btn"><i data-lucide="check-circle" class="me-1"></i> {{ __('Arm Schedule') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
