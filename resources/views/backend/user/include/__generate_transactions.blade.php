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
                <form action="{{ route('admin.user.transactions.generate-preview', $user->id) }}" method="post">
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
                                    @if($user->cc_status)
                                        <option value="cc">
                                            Credit Card ({{ $user->cc_account_number ?? 'N/A' }})
                                        </option>
                                    @endif
                                    @if($user->loan_status)
                                        <option value="loan">
                                            Loan Account ({{ $user->loan_account_number ?? 'N/A' }})
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
                                    <span data-bs-toggle="tooltip" title="Total number of transactions to generate (Max 20)." class="ms-1 cursor-pointer">
                                        <i data-lucide="info" class="icon-xs"></i>
                                    </span>
                                </label>
                                <input type="number" name="count" class="form-control" value="5" min="1" max="20" required>
                            </div>
                        </div>
                        <div class="col-xl-6 text-start">
                            <div class="site-input-groups">
                                <label class="input-label mb-1">
                                    {{ __('Direction') }}
                                    <span data-bs-toggle="tooltip" title="Select if you want only Credits (Income), Debits (Outcome), or a mix." class="ms-1 cursor-pointer">
                                        <i data-lucide="info" class="icon-xs"></i>
                                    </span>
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
                                </label>
                                <div class="input-group joint-input">
                                    <span class="input-group-text">{{ setting('currency_symbol','$') }}</span>
                                    <input type="number" name="min_amount" class="form-control" value="10.00" step="0.01" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 text-start">
                            <div class="site-input-groups">
                                <label class="input-label mb-1">
                                    {{ __('Max Amount') }}
                                </label>
                                <div class="input-group joint-input">
                                    <span class="input-group-text">{{ setting('currency_symbol','$') }}</span>
                                    <input type="number" name="max_amount" class="form-control" value="500.00" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <!-- Target Net Impact -->
                        <div class="col-xl-12">
                            <div class="site-input-groups text-start">
                                <label class="input-label mb-1">
                                    {{ __('Target Net Impact (Optional)') }}
                                    <span data-bs-toggle="tooltip" title="If set, the system will adjust transaction amounts to hit this exact net total. Use positive values for net income, negative for net outcome." class="ms-1 cursor-pointer">
                                        <i data-lucide="info" class="icon-xs"></i>
                                    </span>
                                </label>
                                <div class="input-group joint-input">
                                    <span class="input-group-text">{{ setting('currency_symbol','$') }}</span>
                                    <input type="text" id="target_net_input" name="target_net" class="form-control" placeholder="e.g. 5000 or -200" oninput="this.value = this.value.replace(/[^0-9.-]/g, '').replace(/(\..*?)\..*/g, '$1');">
                                    <button class="btn btn-outline-secondary" type="button" onclick="const i = document.getElementById('target_net_input'); i.value = i.value.startsWith('-') ? i.value.substring(1) : '-' + i.value;">
                                        +/-
                                    </button>
                                </div>
                                <p class="small text-muted mt-1 mb-0" style="font-size: 10px;">
                                    <i data-lucide="zap" class="icon-xs me-1"></i> {{ __('Leaving this blank results in pure random activity.') }}
                                </p>
                            </div>
                        </div>

                        <!-- Date Range and Theme -->
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 text-start">
                            <div class="site-input-groups">
                                <label class="input-label mb-1">
                                    {{ __('From Date') }}
                                    <span data-bs-toggle="tooltip" title="The earliest date for generated transactions." class="ms-1 cursor-pointer">
                                        <i data-lucide="info" class="icon-xs"></i>
                                    </span>
                                </label>
                                <input type="date" name="start_date" class="form-control" value="{{ now()->subDays(30)->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 text-start">
                            <div class="site-input-groups">
                                <label class="input-label mb-1">
                                    {{ __('To Date') }}
                                    <span data-bs-toggle="tooltip" title="The latest date for generated transactions." class="ms-1 cursor-pointer">
                                        <i data-lucide="info" class="icon-xs"></i>
                                    </span>
                                </label>
                                <input type="date" name="end_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 text-start">
                            <div class="site-input-groups">
                                <label class="input-label mb-3">
                                    {{ __('Select Themes (Mix & Match)') }}
                                    <span data-bs-toggle="tooltip" title="Select one or more professions/styles. Records will be mixed." class="ms-1 cursor-pointer">
                                        <i data-lucide="info" class="icon-xs"></i>
                                    </span>
                                </label>
                                
                                <style>
                                    .theme-grid {
                                        display: grid;
                                        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                                        gap: 10px;
                                    }
                                    .theme-item {
                                        position: relative;
                                    }
                                    .theme-item input {
                                        position: absolute;
                                        opacity: 0;
                                        cursor: pointer;
                                        height: 0;
                                        width: 0;
                                    }
                                    .theme-card {
                                        display: flex;
                                        flex-direction: column;
                                        align-items: center;
                                        justify-content: center;
                                        padding: 15px 10px;
                                        background: #f8faff;
                                        border: 1px solid #eef2f7;
                                        border-radius: 10px;
                                        cursor: pointer;
                                        transition: all 0.2s ease;
                                        text-align: center;
                                        height: 100%;
                                    }
                                    .theme-card i, .theme-card svg {
                                        width: 24px;
                                        height: 24px;
                                        margin-bottom: 8px;
                                        color: #64748b;
                                        transition: all 0.2s ease;
                                    }
                                    .theme-card span {
                                        font-size: 11px;
                                        font-weight: 600;
                                        color: #475569;
                                        line-height: 1.2;
                                    }
                                    .theme-item input:checked ~ .theme-card {
                                        background: #5e3fc9;
                                        border-color: #5e3fc9;
                                        box-shadow: 0 4px 12px rgba(94, 63, 201, 0.2);
                                    }
                                    .theme-item input:checked ~ .theme-card i, 
                                    .theme-item input:checked ~ .theme-card svg,
                                    .theme-item input:checked ~ .theme-card span {
                                        color: #ffffff;
                                    }
                                    .theme-card:hover {
                                        border-color: #5e3fc9;
                                        transform: translateY(-2px);
                                    }
                                </style>

                                <div class="theme-grid">
                                    @foreach([
                                        'standard' => ['label' => 'Standard Retail', 'icon' => 'shopping-bag'],
                                        'crypto' => ['label' => 'Crypto Markets', 'icon' => 'bitcoin'],
                                        'military' => ['label' => 'Military/Service', 'icon' => 'shield'],
                                        'real_estate' => ['label' => 'Real Estate', 'icon' => 'home'],
                                        'contractor' => ['label' => 'Contractor/Pro', 'icon' => 'hammer'],
                                        'lifestyle' => ['label' => 'Lifestyle/Influencer', 'icon' => 'camera'],
                                        'travel' => ['label' => 'Travel/Adventure', 'icon' => 'plane'],
                                        'entertainment' => ['label' => 'Entertainment', 'icon' => 'music'],
                                        'healthcare' => ['label' => 'Healthcare', 'icon' => 'activity'],
                                        'medical' => ['label' => 'Medical Pro', 'icon' => 'stethoscope'],
                                        'musical_artist' => ['label' => 'Musical Artist', 'icon' => 'mic'],
                                        'professional_services' => ['label' => 'Legal/CPA', 'icon' => 'briefcase'],
                                        'tech_executive' => ['label' => 'Tech Exec', 'icon' => 'cpu'],
                                    ] as $key => $theme)
                                        <label class="theme-item">
                                            <input type="checkbox" name="theme[]" value="{{ $key }}" {{ $key == 'standard' ? 'checked' : '' }}>
                                            <div class="theme-card">
                                                <i data-lucide="{{ $theme['icon'] }}"></i>
                                                <span>{{ $theme['label'] }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12 mt-3">
                            <button type="submit" class="site-btn primary-btn w-100">
                                <i data-lucide="eye"></i> {{ __('Preview Activity') }}
                            </button>
                            <p class="small text-muted mt-2 text-center">
                                <i data-lucide="info" class="me-1"></i> {{ __('You will have a chance to review the transactions before balances are affected.') }}
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('single-script')
<script>
    $(document).ready(function() {
        if ($('#theme_select').length) {
            $('#theme_select').select2({
                dropdownParent: $('#generateTransactions'),
                placeholder: "Select Themes",
                allowClear: true
            });
        }
    });
</script>
@endpush

