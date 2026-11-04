<div class="modal fade" id="generateTransactions" tabindex="-1" aria-labelledby="generateTransactionsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content site-table-modal shadow-lg border-0" style="border-radius: 16px;">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title d-flex align-items-center gap-2" id="generateTransactionsLabel">
                    <i data-lucide="layers" class="text-primary icon-sm"></i>
                    <span>{{ __('Transaction Management & Generation') }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                
                <!-- Nav Tabs -->
                <ul class="nav nav-pills nav-fill mb-4 p-1 rounded-3" id="generateTxnTabs" role="tablist" style="background-color: #f1f5f9; border: 1px solid #e2e8f0;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-2 px-3 d-flex align-items-center justify-content-center gap-2" id="custom-txn-tab" data-bs-toggle="pill" data-bs-target="#custom-txn-pane" type="button" role="tab" aria-controls="custom-txn-pane" aria-selected="true" style="border-radius: 8px; font-size: 13px;">
                            <i data-lucide="edit-3" class="icon-xs"></i>
                            <span>{{ __('Custom Transaction Builder') }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 px-3 d-flex align-items-center justify-content-center gap-2" id="auto-batch-tab" data-bs-toggle="pill" data-bs-target="#auto-batch-pane" type="button" role="tab" aria-controls="auto-batch-pane" aria-selected="false" style="border-radius: 8px; font-size: 13px;">
                            <i data-lucide="zap" class="icon-xs"></i>
                            <span>{{ __('Auto Batch Generator') }}</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="generateTxnTabContent">
                    
                    <!-- ========================================== -->
                    <!-- TAB 1: CUSTOM TRANSACTION BUILDER (NEW)    -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade show active" id="custom-txn-pane" role="tabpanel" aria-labelledby="custom-txn-tab">
                        <form action="{{ route('admin.user.transactions.custom-create', $user->id) }}" method="post" id="customTxnForm">
                            @csrf
                            <div class="row g-3">
                                
                                <!-- Target Wallet Selection -->
                                <div class="col-12 text-start">
                                    <div class="site-input-groups mb-0">
                                        <label for="custom_wallet" class="input-label mb-1">
                                            {{ __('Target Wallet / Account') }} <span class="required">*</span>
                                        </label>
                                        <select class="form-select" name="wallet_type" id="custom_wallet" required>
                                            <option value="default">
                                                {{ __('Checking Account') }} ({{ $user->account_number ?? 'Default' }})
                                            </option>
                                            <option value="primary_savings">
                                                {{ __('Primary Savings') }} ({{ $user->savings_account_number ?? 'N/A' }})
                                            </option>
                                            @if($user->ira_status)
                                                <option value="ira">
                                                    {{ __('IRA Account') }} ({{ $user->ira_account_number ?? 'N/A' }})
                                                </option>
                                            @endif
                                            @if($user->heloc_status)
                                                <option value="heloc">
                                                    {{ __('HELOC Account') }} ({{ $user->heloc_account_number ?? 'N/A' }})
                                                </option>
                                            @endif
                                            @if($user->cc_status)
                                                <option value="cc">
                                                    {{ __('Credit Card') }} ({{ $user->cc_account_number ?? 'N/A' }})
                                                </option>
                                            @endif
                                            @if($user->loan_status)
                                                <option value="loan">
                                                    {{ __('Loan Account') }} ({{ $user->loan_account_number ?? 'N/A' }})
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

                                <!-- Transaction Direction / Type -->
                                <div class="col-12 text-start">
                                    <label class="input-label mb-2">
                                        {{ __('Transaction Direction') }} <span class="required">*</span>
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="custom-dir-card p-3 border rounded-3 w-100 d-flex flex-column align-items-center justify-content-center text-center cursor-pointer transition-all" id="dir_income_card" style="background-color: #f0fdf4; border-color: #22c55e !important; cursor: pointer;">
                                                <input type="radio" name="direction" value="income" checked class="d-none" onchange="updateDirVisual(this)">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <i data-lucide="arrow-down-left" class="text-success" style="width: 20px; height: 20px;"></i>
                                                    <strong class="text-success">{{ __('Income / Credit (+)') }}</strong>
                                                </div>
                                                <span class="text-muted small" style="font-size: 11px;">{{ __('Adds funds to user balance') }}</span>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <label class="custom-dir-card p-3 border rounded-3 w-100 d-flex flex-column align-items-center justify-content-center text-center cursor-pointer transition-all" id="dir_outcome_card" style="background-color: #ffffff; border-color: #e2e8f0; cursor: pointer;">
                                                <input type="radio" name="direction" value="outcome" class="d-none" onchange="updateDirVisual(this)">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <i data-lucide="arrow-up-right" class="text-danger" style="width: 20px; height: 20px;"></i>
                                                    <strong class="text-danger">{{ __('Outgoing / Debit (-)') }}</strong>
                                                </div>
                                                <span class="text-muted small" style="font-size: 11px;">{{ __('Deducts funds from user balance') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Amount and Custom Date -->
                                <div class="col-md-6 text-start">
                                    <div class="site-input-groups mb-0">
                                        <label class="input-label mb-1">
                                            {{ __('Amount') }} <span class="required">*</span>
                                        </label>
                                        <div class="input-group joint-input">
                                            <span class="input-group-text">{{ setting('currency_symbol', '$') }}</span>
                                            <input type="number" name="amount" class="form-control" placeholder="0.00" step="0.01" min="0.01" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 text-start">
                                    <div class="site-input-groups mb-0">
                                        <label class="input-label mb-1">
                                            {{ __('Transaction Date & Time') }} <span class="required">*</span>
                                            <span data-bs-toggle="tooltip" title="{{ __('Backdate or set custom timestamp for the ledger record.') }}" class="ms-1 cursor-pointer">
                                                <i data-lucide="info" class="icon-xs"></i>
                                            </span>
                                        </label>
                                        <input type="datetime-local" name="custom_date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                    </div>
                                </div>

                                <!-- Custom Description / Narrative -->
                                <div class="col-12 text-start">
                                    <div class="site-input-groups mb-0">
                                        <label class="input-label mb-1">
                                            {{ __('Description / Narrative') }} <span class="required">*</span>
                                        </label>
                                        <input type="text" name="description" id="custom_txn_desc" class="form-control" placeholder="e.g. ACH DIRECT DEPOSIT - US TREASURY" required>
                                        
                                        <!-- Quick Suggestion Chips -->
                                        <div class="mt-2">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <small class="text-muted" style="font-size: 11px;">{{ __('Quick Narrative Presets (Click to fill):') }}</small>
                                                <button type="button" class="btn btn-link p-0 text-decoration-none small" style="font-size: 11px;" onclick="document.getElementById('custom_txn_desc').value = document.getElementById('custom_txn_desc').value.toUpperCase()">
                                                    {{ __('UPPERCASE') }}
                                                </button>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1">
                                                <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark" style="font-size: 11px; border-radius: 6px;" onclick="setCustomDesc('DIRECT DEP FED SALARY')">
                                                    {{ __('Fed Payroll') }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark" style="font-size: 11px; border-radius: 6px;" onclick="setCustomDesc('WIRE IN JPMORGAN CHASE NA')">
                                                    {{ __('Chase Wire In') }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark" style="font-size: 11px; border-radius: 6px;" onclick="setCustomDesc('MOBILE CHECK DEPOSIT #2941')">
                                                    {{ __('Mobile Check') }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark" style="font-size: 11px; border-radius: 6px;" onclick="setCustomDesc('ZELLE TRANSFER RECEIVED')">
                                                    {{ __('Zelle Credit') }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark" style="font-size: 11px; border-radius: 6px;" onclick="setCustomDesc('OUTBOUND WIRE TRANSFER')">
                                                    {{ __('Wire Out') }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark" style="font-size: 11px; border-radius: 6px;" onclick="setCustomDesc('POS DEBIT CARD PURCHASE')">
                                                    {{ __('Card Debit') }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark" style="font-size: 11px; border-radius: 6px;" onclick="setCustomDesc('EXTERNAL ACH TRANSFER REVERSAL')">
                                                    {{ __('ACH Reversal') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="col-12 mt-4">
                                    <div class="row g-2">
                                        <div class="col-sm-6">
                                            <button type="submit" name="action_type" value="preview" class="site-btn white-btn w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                                                <i data-lucide="eye" class="icon-xs"></i>
                                                <span>{{ __('Add to Preview List') }}</span>
                                            </button>
                                        </div>
                                        <div class="col-sm-6">
                                            <button type="submit" name="action_type" value="immediate" class="site-btn primary-btn w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                                                <i data-lucide="check-circle" class="icon-xs"></i>
                                                <span>{{ __('Post Immediately') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="small text-muted mt-2 text-center mb-0" style="font-size: 11px;">
                                        <i data-lucide="info" class="icon-xs me-1"></i>
                                        {{ __('Immediate posting commits the transaction directly to user ledger and adjusts the chosen wallet balance in real time.') }}
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAB 2: AUTO BATCH GENERATOR (PRESERVED)    -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade" id="auto-batch-pane" role="tabpanel" aria-labelledby="auto-batch-tab">
                        <form action="{{ route('admin.user.transactions.generate-preview', $user->id) }}" method="post">
                            @csrf
                            <div class="row g-3">
                                <!-- Wallet Selection -->
                                <div class="col-xl-12 text-start">
                                    <div class="site-input-groups mb-0">
                                        <label for="gen_wallet" class="input-label mb-1">
                                            {{ __('Target Wallet') }} <span class="required">*</span>
                                        </label>
                                        <select class="form-select" name="wallet_type" id="gen_wallet" required>
                                            <option value="default">
                                                {{ __('Checking Account') }} ({{ $user->account_number ?? 'Default' }})
                                            </option>
                                            <option value="primary_savings">
                                                {{ __('Primary Savings') }} ({{ $user->savings_account_number ?? 'N/A' }})
                                            </option>
                                            @if($user->ira_status)
                                                <option value="ira">
                                                    {{ __('IRA Account') }} ({{ $user->ira_account_number ?? 'N/A' }})
                                                </option>
                                            @endif
                                            @if($user->heloc_status)
                                                <option value="heloc">
                                                    {{ __('HELOC Account') }} ({{ $user->heloc_account_number ?? 'N/A' }})
                                                </option>
                                            @endif
                                            @if($user->cc_status)
                                                <option value="cc">
                                                    {{ __('Credit Card') }} ({{ $user->cc_account_number ?? 'N/A' }})
                                                </option>
                                            @endif
                                            @if($user->loan_status)
                                                <option value="loan">
                                                    {{ __('Loan Account') }} ({{ $user->loan_account_number ?? 'N/A' }})
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
                                <div class="col-xl-6 text-start">
                                    <div class="site-input-groups mb-0">
                                        <label class="input-label mb-1">
                                            {{ __('Number of Items') }}
                                            <span data-bs-toggle="tooltip" title="{{ __('Total number of transactions to generate (Max 20).') }}" class="ms-1 cursor-pointer">
                                                <i data-lucide="info" class="icon-xs"></i>
                                            </span>
                                        </label>
                                        <input type="number" name="count" class="form-control" value="5" min="1" max="20" required>
                                    </div>
                                </div>
                                <div class="col-xl-6 text-start">
                                    <div class="site-input-groups mb-0">
                                        <label class="input-label mb-1">
                                            {{ __('Direction') }}
                                            <span data-bs-toggle="tooltip" title="{{ __('Select if you want only Credits (Income), Debits (Outcome), or a mix.') }}" class="ms-1 cursor-pointer">
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
                                <div class="col-xl-6 text-start">
                                    <div class="site-input-groups mb-0">
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
                                    <div class="site-input-groups mb-0">
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
                                <div class="col-xl-12 text-start">
                                    <div class="site-input-groups mb-0">
                                        <label class="input-label mb-1">
                                            {{ __('Target Net Impact (Optional)') }}
                                            <span data-bs-toggle="tooltip" title="{{ __('If set, the system will adjust transaction amounts to hit this exact net total. Use positive values for net income, negative for net outcome.') }}" class="ms-1 cursor-pointer">
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

                                <!-- Date Range -->
                                <div class="col-xl-6 text-start">
                                    <div class="site-input-groups mb-0">
                                        <label class="input-label mb-1">
                                            {{ __('From Date') }}
                                            <span data-bs-toggle="tooltip" title="{{ __('The earliest date for generated transactions.') }}" class="ms-1 cursor-pointer">
                                                <i data-lucide="info" class="icon-xs"></i>
                                            </span>
                                        </label>
                                        <input type="date" name="start_date" class="form-control" value="{{ now()->subDays(30)->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-xl-6 text-start">
                                    <div class="site-input-groups mb-0">
                                        <label class="input-label mb-1">
                                            {{ __('To Date') }}
                                            <span data-bs-toggle="tooltip" title="{{ __('The latest date for generated transactions.') }}" class="ms-1 cursor-pointer">
                                                <i data-lucide="info" class="icon-xs"></i>
                                            </span>
                                        </label>
                                        <input type="date" name="end_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                    </div>
                                </div>

                                <!-- Themes Selection -->
                                <div class="col-xl-12 text-start">
                                    <div class="site-input-groups mb-0">
                                        <label class="input-label mb-2">
                                            {{ __('Select Themes (Mix & Match)') }}
                                            <span data-bs-toggle="tooltip" title="{{ __('Select one or more professions/styles. Records will be mixed.') }}" class="ms-1 cursor-pointer">
                                                <i data-lucide="info" class="icon-xs"></i>
                                            </span>
                                        </label>
                                        
                                        <style>
                                            .theme-grid {
                                                display: grid;
                                                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
                                                gap: 8px;
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
                                                padding: 12px 8px;
                                                background: #f8faff;
                                                border: 1px solid #eef2f7;
                                                border-radius: 10px;
                                                cursor: pointer;
                                                transition: all 0.2s ease;
                                                text-align: center;
                                                height: 100%;
                                            }
                                            .theme-card i, .theme-card svg {
                                                width: 22px;
                                                height: 22px;
                                                margin-bottom: 6px;
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
                                                background: #004d73;
                                                border-color: #004d73;
                                                box-shadow: 0 4px 12px rgba(0, 77, 115, 0.25);
                                            }
                                            .theme-item input:checked ~ .theme-card i, 
                                            .theme-item input:checked ~ .theme-card svg,
                                            .theme-item input:checked ~ .theme-card span {
                                                color: #ffffff !important;
                                            }
                                            .theme-card:hover {
                                                border-color: #004d73;
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
                                                'lifestyle' => ['label' => 'Lifestyle/Creator', 'icon' => 'camera'],
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
                                    <button type="submit" class="site-btn primary-btn w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                                        <i data-lucide="eye" class="icon-xs"></i>
                                        <span>{{ __('Preview Activity') }}</span>
                                    </button>
                                    <p class="small text-muted mt-2 text-center mb-0" style="font-size: 11px;">
                                        <i data-lucide="info" class="icon-xs me-1"></i>
                                        {{ __('You will have a chance to review the transactions before balances are affected.') }}
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@push('single-script')
<script>
    function updateDirVisual(radio) {
        const incomeCard = document.getElementById('dir_income_card');
        const outcomeCard = document.getElementById('dir_outcome_card');
        if (radio.value === 'income') {
            incomeCard.style.backgroundColor = '#f0fdf4';
            incomeCard.style.borderColor = '#22c55e';
            outcomeCard.style.backgroundColor = '#ffffff';
            outcomeCard.style.borderColor = '#e2e8f0';
        } else {
            outcomeCard.style.backgroundColor = '#fef2f2';
            outcomeCard.style.borderColor = '#ef4444';
            incomeCard.style.backgroundColor = '#ffffff';
            incomeCard.style.borderColor = '#e2e8f0';
        }
    }

    function setCustomDesc(text) {
        const input = document.getElementById('custom_txn_desc');
        input.value = text;
        input.focus();
    }

    $(document).ready(function() {
        if (window.lucide) {
            lucide.createIcons();
        }
    });
</script>
@endpush
