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
                    @php $netImpact = $totalIncome - $totalOutcome; @endphp
                    <div class="alert alert-info mb-3 p-3 rounded-3" style="background-color: #f0f7ff; border: 1px solid #c8e1ff;">
                        <div class="d-flex align-items-start gap-3">
                            <i data-lucide="calculator" class="text-primary mt-1 flex-shrink-0" style="width: 24px; height: 24px;"></i>
                            <div class="flex-grow-1" style="font-size: 13px;">
                                <div class="fw-bold text-dark mb-1">{{ __('Transaction Batch Calculation Breakdown') }}</div>
                                <div class="d-flex flex-wrap gap-3 my-1">
                                    <span>{{ __('Total Inflows:') }} <strong class="text-success">+{{ setting('currency_symbol','$') }}{{ number_format($totalIncome, 2) }}</strong></span>
                                    <span>{{ __('Total Outflows:') }} <strong class="text-danger">-{{ setting('currency_symbol','$') }}{{ number_format($totalOutcome, 2) }}</strong></span>
                                    <span>{{ __('Net Balance Impact:') }} 
                                        @if($netImpact >= 0)
                                            <strong class="text-success fw-bold">+{{ setting('currency_symbol','$') }}{{ number_format($netImpact, 2) }}</strong>
                                        @else
                                            <strong class="text-danger fw-bold">-{{ setting('currency_symbol','$') }}{{ number_format(abs($netImpact), 2) }}</strong>
                                        @endif
                                    </span>
                                </div>
                                <div class="text-muted mt-1" style="font-size: 11px;">
                                    {{ __('Uncheck any rows you wish to exclude. Target Account: ') }}<strong>{{ $previewData[0]['wallet_name'] ?? 'Primary Account' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="commitFormTransactions" action="{{ route('admin.user.transactions.generate-commit', $user->id) }}" method="POST">
                        @csrf
                        <style>
                            @media (max-width: 576px) {
                                .preview-txn-table th:nth-child(5), 
                                .preview-txn-table td:nth-child(5) {
                                    display: none;
                                }
                                .preview-txn-table td {
                                    font-size: 11px;
                                    padding: 8px 4px !important;
                                }
                                .date-compact {
                                    font-size: 10px;
                                    line-height: 1.1;
                                    display: block;
                                }
                            }
                        </style>
                        <div class="table-responsive">
                            <table class="table site-table text-start preview-txn-table">
                                <thead>
                                    <tr>
                                        <th style="width: 30px;">
                                            <input type="checkbox" checked onchange="document.querySelectorAll('.preview-txn-checkbox').forEach(c => c.checked = this.checked)">
                                        </th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Account') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th class="d-none d-sm-table-cell">{{ __('Type') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($previewData as $index => $txn)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="txn_indexes[]" value="{{ $index }}" class="preview-txn-checkbox" checked>
                                            </td>
                                            <td class="white-space-nowrap">
                                                <span class="date-compact">
                                                    @if(\Carbon\Carbon::parse($txn['date'])->isToday())
                                                        Today, {{ \Carbon\Carbon::parse($txn['date'])->format('h:i A') }}
                                                    @else
                                                        {{ \Carbon\Carbon::parse($txn['date'])->format('M d, Y') }}<br>
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($txn['date'])->format('h:i A') }}</small>
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="white-space-nowrap">
                                                <span class="badge bg-light text-dark border" style="font-size: 11px;">
                                                    {{ $txn['wallet_name'] ?? 'Checking' }}
                                                </span>
                                            </td>
                                            <td style="min-width: 120px;"><strong>{{ $txn['description'] }}</strong></td>
                                            <td class="white-space-nowrap">
                                                @if($txn['direction'] === 'income')
                                                    <span class="text-success fw-bold">+{{ setting('currency_symbol','$') }}{{ number_format($txn['amount'], 2) }}</span>
                                                @else
                                                    <span class="text-danger fw-bold">-{{ setting('currency_symbol','$') }}{{ number_format($txn['amount'], 2) }}</span>
                                                @endif
                                            </td>
                                            <td class="d-none d-sm-table-cell">
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
