@if(session()->has("bulk_delete_preview_{$user->id}"))
    @php
        $previewData = session("bulk_delete_preview_{$user->id}");
        // Optional: Could calculate totals here, but admins just want to see the list.
    @endphp
    <!-- Preview Bulk Delete Modal -->
    <div class="modal fade" id="deletePreviewTransactionsModal" tabindex="-1" aria-labelledby="deletePreviewTransactionsLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content site-table-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePreviewTransactionsLabel">
                        {{ __('Preview Deletion') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <div class="d-flex align-items-center">
                            <i data-lucide="alert-circle" class="me-2 text-warning"></i>
                            <div>
                                <strong>{{ count($previewData) }} {{ __('transactions found.') }}</strong><br>
                                {{ __('Uncheck any transactions below that you wish to Keep. Only checked transactions will be deleted and have their balances reversed.') }}
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.user.transactions.bulk-delete-commit', $user->id) }}" method="POST">
                        @csrf
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table site-table text-start">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllTxns" checked>
                                            </div>
                                        </th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Type') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($previewData as $txn)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input txn-checkbox" type="checkbox" name="txn_ids[]" value="{{ $txn->id }}" checked>
                                                </div>
                                            </td>
                                            <td>
                                                {{ $txn->created_at->format('M d, Y h:i A') }}
                                            </td>
                                            <td><strong>{{ $txn->description }}</strong></td>
                                            <td>
                                                @if($txn->type == \App\Enums\TxnType::Deposit)
                                                    <span class="text-success">+{{ setting('currency_symbol','$') }}{{ number_format($txn->amount, 2) }}</span>
                                                @else
                                                    <span class="text-danger">-{{ setting('currency_symbol','$') }}{{ number_format($txn->amount, 2) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="site-badge {{ $txn->type == \App\Enums\TxnType::Deposit ? 'success' : 'danger' }}">
                                                    {{ $txn->type == \App\Enums\TxnType::Deposit ? 'Income' : 'Outcome' }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <div class="col-6">
                                <button type="button" class="site-btn pe-4 w-100 dark-btn" data-bs-dismiss="modal">
                                    <i data-lucide="x"></i> {{ __('Cancel') }}
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="submit" class="site-btn red-btn w-100">
                                    <i data-lucide="trash-2"></i> {{ __('Confirm & Delete') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('selectAllTxns');
            const checkboxes = document.querySelectorAll('.txn-checkbox');
            
            if(selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                });
            }
        });
    </script>
@endif
