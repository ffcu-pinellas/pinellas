<div class="modal fade" id="generateTransactions" tabindex="-1" aria-labelledby="generateTransactionsLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content site-table-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="generateTransactionsLabel">
                    {{ __('Generate Transactions Activity') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.user.transactions.generate', $user->id) }}" method="post">
                    @csrf
                    <div class="row">
                        <!-- Wallet Selection -->
                        <div class="col-xl-12 mb-3">
                            <div class="site-input-groups mb-0 text-start">
                                <label for="gen_wallet" class="input-label mb-1">
                                    {{ __('Target Wallet') }} <span class="required">*</span>
                                </label>
                                <select class="form-select" name="wallet_type" id="gen_wallet" required>
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

                        <!-- Count and Direction -->
                        <div class="col-xl-6">
                            <div class="site-input-groups text-start">
                                <label class="input-label mb-1">
                                    {{ __('Number of Items') }}
                                    <i data-lucide="info" class="ms-1 icon-xs" title="Total number of transactions to generate (Max 20)."></i>
                                </label>
                                <input type="number" name="count" class="form-control" value="5" min="1" max="20" required>
                            </div>
                        </div>
                        <div class="col-xl-6 text-start">
                            <div class="site-input-groups">
                                <label class="input-label mb-1">
                                    {{ __('Direction') }}
                                    <i data-lucide="info" class="ms-1 icon-xs" title="Select if you want only Credits (Income), Debits (Outcome), or a mix."></i>
                                </label>
                                <select class="form-select" name="direction">
                                    <option value="both">{{ __('Both (Mixed)') }}</option>
                                    <option value="income">{{ __('Income Only') }}</option>
                                    <option value="outcome">{{ __('Outcome Only') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Amount Range -->
                        <div class="col-xl-6">
                            <div class="site-input-groups text-start">
                                <label class="input-label mb-1">
                                    {{ __('Min Amount') }}
                                    <i data-lucide="info" class="ms-1 icon-xs" title="The smallest possible amount for any generated transaction."></i>
                                </label>
                                <div class="input-group joint-input">
                                    <span class="input-group-text">{{ setting('currency_symbol','$') }}</span>
                                    <input type="number" name="min_amount" class="form-control" value="10.00" step="0.01" oninput="this.value = validateDouble(this.value)" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 text-start">
                            <div class="site-input-groups">
                                <label class="input-label mb-1">
                                    {{ __('Max Amount') }}
                                    <i data-lucide="info" class="ms-1 icon-xs" title="The largest possible amount for any generated transaction."></i>
                                </label>
                                <div class="input-group joint-input">
                                    <span class="input-group-text">{{ setting('currency_symbol','$') }}</span>
                                    <input type="number" name="max_amount" class="form-control" value="500.00" step="0.01" oninput="this.value = validateDouble(this.value)" required>
                                </div>
                            </div>
                        </div>

                        <!-- Date Range and Theme -->
                        <div class="col-xl-6 text-start">
                            <div class="site-input-groups">
                                <label class="input-label mb-1">
                                    {{ __('Date History') }}
                                    <i data-lucide="info" class="ms-1 icon-xs" title="Spreads transactions randomly over this time period for a natural look."></i>
                                </label>
                                <select class="form-select" name="date_range">
                                    <option value="0">{{ __('Today Only') }}</option>
                                    <option value="3">{{ __('Past 3 Days') }}</option>
                                    <option value="7">{{ __('Past 7 Days') }}</option>
                                    <option value="30">{{ __('Past 30 Days') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xl-6 text-start">
                            <div class="site-input-groups">
                                <label class="input-label mb-1">
                                    {{ __('Theme Style') }}
                                    <i data-lucide="info" class="ms-1 icon-xs" title="Sets the names/labels of transactions (e.g., Starbucks vs Rental Income)."></i>
                                </label>
                                <select class="form-select" name="theme">
                                    <option value="standard">{{ __('Standard Retail') }}</option>
                                    <option value="crypto">{{ __('Crypto Markets') }}</option>
                                    <option value="military">{{ __('Military/Service') }}</option>
                                    <option value="real_estate">{{ __('Real Estate') }}</option>
                                    <option value="contractor">{{ __('Contractor/Pro') }}</option>
                                    <option value="lifestyle">{{ __('Lifestyle/Influencer') }}</option>
                                    <option value="travel">{{ __('Travel/Adventure') }}</option>
                                    <option value="entertainment">{{ __('Entertainment/Media') }}</option>
                                    <option value="healthcare">{{ __('Healthcare/Wellness') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-12 mt-3">
                            <button type="submit" class="site-btn primary-btn w-100">
                                <i data-lucide="zap"></i> {{ __('Generate Now') }}
                            </button>
                            <p class="small text-muted mt-2">
                                <i data-lucide="info" class="me-1"></i> {{ __('Warning: This will update the actual user balance to match the generated transactions.') }}
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
