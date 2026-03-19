<div class="modal fade" id="bulkDeleteTransactions" tabindex="-1" aria-labelledby="bulkDeleteTransactionsLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content site-table-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkDeleteTransactionsLabel">
                    {{ __('Bulk Delete Transactions') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3">
                    <div class="d-flex align-items-center">
                        <i data-lucide="alert-triangle" class="me-2 text-danger"></i>
                        <div>
                            <strong>{{ __('Warning:') }}</strong> {{ __('Deleting transactions will permanently remove the records and ') }} <strong>{{ __('REVERSE') }}</strong> {{ __(' their impact on the user\'s balance to maintain ledgers.') }}
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.user.transactions.bulk-delete-preview', $user->id) }}" method="post">
                    @csrf
                    <div class="row">
                        <!-- Wallet Selection -->
                        <div class="col-xl-12 mb-3">
                            <div class="site-input-groups mb-0 text-start">
                                <label for="del_wallet" class="input-label mb-1">
                                    {{ __('Target Wallet') }} <span class="required">*</span>
                                </label>
                                <select class="form-select" name="wallet_type" id="del_wallet" required>
                                    <option value="all">{{ __('All Wallets') }}</option>
                                    <option value="default">
                                        {{ __('Checking Account') }} ({{ $user->account_number ?? 'Default' }})
                                    </option>
                                    <option value="primary_savings">
                                        Primary Savings ({{ $user->savings_account_number ?? 'N/A' }})
                                    </option>
                                    @if($user->ira_status)
                                        <option value="ira">
                                            IRA Account ({{ $user->ira_account_number ?? 'N/A' }})
                                        </option>
                                    @endif
                                    @if($user->heloc_status)
                                        <option value="heloc">
                                            HELOC Account ({{ $user->heloc_account_number ?? 'N/A' }})
                                        </option>
                                    @endif
                                    @foreach ($user_wallets as $wallet)
                                        <option value="{{ $wallet['id'] }}">
                                            {{ $wallet['name'] }} ({{ $wallet['code'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Date Range and Direction -->
                        <div class="col-xl-6 text-start">
                            <div class="site-input-groups">
                                <label class="input-label mb-1">
                                    {{ __('Date Range') }}
                                </label>
                                <select class="form-select" name="date_range">
                                    <option value="0">{{ __('Today Only') }}</option>
                                    <option value="3">{{ __('Past 3 Days') }}</option>
                                    <option value="7">{{ __('Past 7 Days') }}</option>
                                    <option value="30">{{ __('Past 30 Days') }}</option>
                                    <option value="all">{{ __('All Time') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xl-6 text-start">
                            <div class="site-input-groups">
                                <label class="input-label mb-1">
                                    {{ __('Direction') }}
                                </label>
                                <select class="form-select" name="direction">
                                    <option value="both">{{ __('Both (Mixed)') }}</option>
                                    <option value="income">{{ __('Income Only (Withdraws funds)') }}</option>
                                    <option value="outcome">{{ __('Outcome Only (Deposits funds)') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Status / Type filter -->
                        <div class="col-xl-12 text-start">
                            <div class="site-input-groups box-checkbox">
                                <div class="form-check pt-2">
                                    <input class="form-check-input" type="checkbox" name="system_only" id="systemOnlyCheck" value="1" checked>
                                    <label class="form-check-label" for="systemOnlyCheck">
                                        {{ __('Only delete "System Generated" transactions') }}
                                    </label>
                                    <p class="small text-muted mt-1">{{ __('Uncheck to allow deletion of actual user-initiated transactions.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12 mt-3">
                            <button type="submit" class="site-btn red-btn w-100">
                                <i data-lucide="filter"></i> {{ __('Preview Deletion') }}
                            </button>
                            <p class="small text-muted mt-2 text-center">
                                <i data-lucide="info" class="me-1"></i> {{ __('You will review and manually select transactions before they are deleted.') }}
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
