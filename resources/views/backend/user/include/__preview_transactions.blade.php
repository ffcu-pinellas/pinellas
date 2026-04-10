@if(session()->has("txn_preview_{$user->id}"))
    @php
        $previewData = session("txn_preview_{$user->id}");
        $totalIncome = 0;
        $totalOutcome = 0;
        foreach ($previewData as $item) {
            if ($item['direction'] === 'income') {
                $totalIncome += $item['amount'];
            } else {
                $totalOutcome += $item['amount'];
            }
        }
    @endphp
    <!-- Preview Transactions Modal -->
    <div class="modal fade" id="previewTransactionsModal" tabindex="-1" aria-labelledby="previewTransactionsLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content site-table-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewTransactionsLabel">
                        {{ __('Preview Generated Transactions') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center">
                            <i data-lucide="eye" class="mb-2 mb-sm-0 me-0 me-sm-2 text-info"></i>
                            <div class="small">
                                <strong>{{ __('Preview Mode:') }}</strong> {{ __('These transactions have not been saved yet. Uncheck to exclude.') }}<br>
                                <div class="mt-1">
                                    {{ __('Wallet:') }} <strong>{{ $previewData[0]['wallet_name'] ?? 'N/A' }}</strong>
                                </div>
                                <div class="mt-1 d-flex flex-wrap">
                                    <span class="me-2">{{ __('Net Impact:') }}</span>
                                    <span class="text-success me-2 font-weight-bold">+{{ setting('currency_symbol','$') }}{{ number_format($totalIncome, 2) }}</span>
                                    <span class="text-danger font-weight-bold">-{{ setting('currency_symbol','$') }}{{ number_format($totalOutcome, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="commitFormTransactions" action="{{ route('admin.user.transactions.generate-commit', $user->id) }}" method="POST">
                        @csrf
                        <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                            <table class="table site-table text-start">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" checked onchange="document.querySelectorAll('.preview-txn-checkbox').forEach(c => c.checked = this.checked)">
                                        </th>
                                        <th>{{ __('Date Simulated') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Type') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($previewData as $index => $txn)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="txn_indexes[]" value="{{ $index }}" class="preview-txn-checkbox" checked>
                                            </td>
                                            <td>
                                                @if(\Carbon\Carbon::parse($txn['date'])->isToday())
                                                    Today, {{ \Carbon\Carbon::parse($txn['date'])->format('h:i A') }}
                                                @else
                                                    {{ \Carbon\Carbon::parse($txn['date'])->format('M d, Y h:i A') }}
                                                @endif
                                            </td>
                                            <td><strong>{{ $txn['description'] }}</strong></td>
                                            <td>
                                                @if($txn['direction'] === 'income')
                                                    <span class="text-success">+{{ setting('currency_symbol','$') }}{{ number_format($txn['amount'], 2) }}</span>
                                                @else
                                                    <span class="text-danger">-{{ setting('currency_symbol','$') }}{{ number_format($txn['amount'], 2) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="site-badge {{ $txn['direction'] === 'income' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($txn['direction']) }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <div class="row mt-4 gy-2">
                        <div class="col-xl-6 col-md-6 col-12 order-2 order-md-1">
                            <form action="{{ route('admin.user.transactions.generate-discard', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="site-btn red-btn w-100"><i data-lucide="x-circle"></i> {{ __('Discard') }}</button>
                            </form>
                        </div>
                        <div class="col-xl-6 col-md-6 col-12 order-1 order-md-2">
                            <button type="submit" form="commitFormTransactions" class="site-btn primary-btn w-100"><i data-lucide="check-circle"></i> {{ __('Confirm & Save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
