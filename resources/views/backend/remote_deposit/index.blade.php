@extends('backend.layouts.app')
@section('title')
    {{ __('Remote Deposits') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="title-content">
                            <h2 class="title">{{ __('Remote Deposits') }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="site-card">
                        <div class="site-card-body table-responsive">
                            <div class="site-datatable">
                                <table class="data-table">
                                    <thead>
                                    <tr>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Account') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Front Image') }}</th>
                                        <th>{{ __('Back Image') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($deposits as $deposit)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <img src="{{ $deposit->user->avatar_path ?? asset('backend/images/default/user.png') }}" alt="user-avatar">
                                                    </div>
                                                    <div>
                                                        <h6 class="m-0">{{ $deposit->user->full_name }}</h6>
                                                        <span class="text-muted small">{{ $deposit->user->email }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ $deposit->created_at->format('M d, Y h:i A') }}
                                            </td>
                                            <td>{{ $deposit->account_name }} <br> <span class="text-muted small">{{ $deposit->account_number }}</span></td>
                                            <td>{{ setting('currency_symbol', 'global') }}{{ number_format($deposit->amount, 2) }}</td>
                                            <td>
                                                <a href="{{ asset($deposit->front_image) }}" target="_blank">
                                                    <img src="{{ asset($deposit->front_image) }}" alt="Front" class="img-thumbnail" style="height: 50px;">
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ asset($deposit->back_image) }}" target="_blank">
                                                    <img src="{{ asset($deposit->back_image) }}" alt="Back" class="img-thumbnail" style="height: 50px;">
                                                </a>
                                            </td>
                                            <td>
                                                @if($deposit->status == 'pending')
                                                    <span class="badge bg-warning">{{ __('Pending') }}</span>
                                                @elseif($deposit->status == 'approved')
                                                    <span class="badge bg-success">{{ __('Approved') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($deposit->status == 'pending')
                                                    <div class="d-flex gap-2">
                                                        <form action="{{ route('admin.remote.deposit.approve', $deposit->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to approve this deposit?');">
                                                            @csrf
                                                            <button type="submit" class="site-btn-sm primary-btn" title="Approve"><i data-lucide="check"></i></button>
                                                        </form>
                                                        <button type="button" class="site-btn-sm red-btn" title="Reject" data-bs-toggle="modal" data-bs-target="#rejectModal_{{ $deposit->id }}">
                                                            <i data-lucide="x"></i>
                                                        </button>
                                                    </div>

                                                    <!-- Reject Modal -->
                                                    <div class="modal fade" id="rejectModal_{{ $deposit->id }}" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-md modal-dialog-centered">
                                                            <div class="modal-content site-table-modal text-start">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">{{ __('Reject Remote Deposit') }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="text-danger small mb-3"><strong>Note:</strong> A $25.00 returned check fee will be automatically deducted from the user's balance upon rejection.</p>
                                                                    <form action="{{ route('admin.remote.deposit.reject', $deposit->id) }}" method="POST">
                                                                        @csrf
                                                                        <div class="site-input-groups">
                                                                            <label class="input-label mb-1">{{ __('Rejection Reason') }} <span class="required">*</span></label>
                                                                            <textarea name="note" class="form-control" rows="3" required placeholder="e.g., Poor image quality, unreadable MICR line, or missing endorsement."></textarea>
                                                                        </div>
                                                                        <div class="mt-3">
                                                                            <button type="submit" class="site-btn-sm red-btn w-100">
                                                                                {{ __('Confirm Rejection & Apply Fee') }}
                                                                            </button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{ $deposits->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
