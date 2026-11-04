<div class="modal fade" id="importTransactionsModal" tabindex="-1" aria-labelledby="importTransactionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content site-table-modal shadow-lg border-0" style="border-radius: 16px;">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title d-flex align-items-center gap-2" id="importTransactionsModalLabel">
                    <i data-lucide="file-spreadsheet" class="text-primary icon-sm"></i>
                    <span>{{ __('1-Click Transaction Spreadsheet Importer') }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('admin.user.transactions.import-csv', $user->id) }}" method="POST" enctype="multipart/form-data" id="importTransactionsForm">
                @csrf
                <div class="modal-body p-4">
                    
                    <!-- Guidance Alert -->
                    <div class="alert alert-primary d-flex align-items-start gap-3 p-3 rounded-3 mb-4" style="background-color: #f0f7ff; border: 1px solid #c8e1ff;">
                        <i data-lucide="info" class="text-primary mt-1 flex-shrink-0"></i>
                        <div class="flex-grow-1" style="font-size: 13px; line-height: 1.5;">
                            <div class="fw-bold text-dark mb-1">{{ __('Batch Ledger Population for') }} {{ $user->full_name }}</div>
                            <p class="text-muted mb-2">
                                {{ __('Upload any .csv, .xlsx, or .xls file. Our parser automatically recognizes columns:') }} 
                                <code>Date</code>, <code>Description</code>, <code>Amount</code>, <code>Type</code> (or separate <code>Debit</code>/<code>Credit</code>), and <code>Account</code>.
                            </p>
                            <a href="{{ route('admin.user.transactions.import-sample') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 12px; border-radius: 6px;">
                                <i data-lucide="download" style="width: 13px; height: 13px;"></i>
                                {{ __('Download Sample CSV Template') }}
                            </a>
                        </div>
                    </div>

                    <div class="row g-3">
                        
                        <!-- File Upload Zone -->
                        <div class="col-12 text-start">
                            <label class="form-label fw-bold mb-1" for="csv_file_input">
                                {{ __('Select Spreadsheet File') }} <span class="text-danger">*</span>
                            </label>
                            <div class="p-3 border border-2 border-dashed rounded-3 text-center" style="background-color: #f8fafc; border-color: #cbd5e1 !important;" id="dropzone_container">
                                <i data-lucide="upload-cloud" class="text-muted mb-2" style="width: 36px; height: 36px;"></i>
                                <div class="text-muted small mb-2">
                                    {{ __('Drag and drop your file here, or click to browse') }}
                                </div>
                                <input type="file" name="csv_file" id="csv_file_input" class="form-control" accept=".csv, .xlsx, .xls, .tsv, .txt" required style="max-width: 400px; margin: 0 auto;">
                                <div class="text-muted mt-2" style="font-size: 11px;">
                                    {{ __('Supported formats: .CSV, .XLSX, .XLS, .TSV (Max 10MB)') }}
                                </div>
                            </div>
                        </div>

                        <!-- Target Default Wallet -->
                        <div class="col-md-6 text-start">
                            <label class="form-label fw-bold mb-1" for="import_default_wallet">
                                {{ __('Fallback Account / Wallet') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="default_wallet" id="import_default_wallet" required>
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
                                @if($user->loan_account_status)
                                    <option value="loan">
                                        {{ __('Loan Account') }} ({{ $user->loan_account_number ?? 'N/A' }})
                                    </option>
                                @endif
                                @if(!empty($userWallets))
                                    @foreach($userWallets as $uw)
                                        <option value="{{ $uw->id }}">
                                            {{ $uw->currency?->name ?? 'Multi-Currency Wallet' }} ({{ $uw->currency?->code }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="text-muted mt-1" style="font-size: 11px;">
                                {{ __('Applied when the spreadsheet does not specify an Account column.') }}
                            </div>
                        </div>

                        <!-- Import Action Mode -->
                        <div class="col-md-6 text-start">
                            <label class="form-label fw-bold mb-1">
                                {{ __('Processing Mode') }} <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex flex-column gap-2 mt-1">
                                <label class="border rounded-3 p-2 d-flex align-items-center gap-2 cursor-pointer" style="background-color: #f8fafc;">
                                    <input type="radio" name="import_action" value="preview" checked class="form-check-input mt-0">
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 12px;">{{ __('Dry-Run Preview (Recommended)') }}</div>
                                        <div class="text-muted" style="font-size: 11px;">{{ __('Inspect parsed rows, uncheck items, and confirm.') }}</div>
                                    </div>
                                </label>
                                <label class="border rounded-3 p-2 d-flex align-items-center gap-2 cursor-pointer" style="background-color: #f8fafc;">
                                    <input type="radio" name="import_action" value="commit" class="form-check-input mt-0">
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 12px;">{{ __('Commit Immediately') }}</div>
                                        <div class="text-muted" style="font-size: 11px;">{{ __('Directly post entries and recalculate balance.') }}</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-top pt-3 px-4 pb-3 justify-content-between">
                    <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" id="btnSubmitImport">
                        <i data-lucide="upload" style="width: 15px; height: 15px;"></i>
                        <span>{{ __('Process Spreadsheet') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

