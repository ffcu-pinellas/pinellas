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
                                                    <div class="d-flex gap-1 align-items-center">
                                                        <!-- 1. Clear Full Check -->
                                                        <form action="{{ route('admin.remote.deposit.approve', $deposit->id) }}" method="POST" onsubmit="return confirm('Release 100% of check #{{ $deposit->id }} immediately?');">
                                                            @csrf
                                                            <button type="submit" class="site-btn-sm primary-btn" title="Clear Full Check Immediately"><i data-lucide="check-check"></i></button>
                                                        </form>

                                                        <!-- 2. Approve with $225 Immediate Release (Reg CC Hold) -->
                                                        <form action="{{ route('admin.remote.deposit.approve_regcc', $deposit->id) }}" method="POST" onsubmit="return confirm('Release $225.00 now and place remainder on 5-business-day hold?');">
                                                            @csrf
                                                            <button type="submit" class="site-btn-sm text-white" style="background-color: #0284c7; border: 1px solid #0284c7;" title="Release $225 Immediately (Hold Rest 5 Days)"><i data-lucide="scale"></i></button>
                                                        </form>

                                                        <!-- 3. Place on Full 5-Day Hold -->
                                                        <form action="{{ route('admin.remote.deposit.full_hold', $deposit->id) }}" method="POST" onsubmit="return confirm('Place 100% of check amount on standard 5-business-day hold?');">
                                                            @csrf
                                                            <button type="submit" class="site-btn-sm text-white" style="background-color: #d97706; border: 1px solid #d97706;" title="Place on Full 5-Day Hold (0 Immediate)"><i data-lucide="clock"></i></button>
                                                        </form>

                                                        <!-- 4. Reject / Retake Modal -->
                                                        <button type="button" class="site-btn-sm red-btn" title="Decline / Request Photo Retake" data-bs-toggle="modal" data-bs-target="#rejectModal_{{ $deposit->id }}">
                                                            <i data-lucide="x"></i>
                                                        </button>
                                                    </div>

                                                    <!-- Reject / Retake Modal -->
                                                    <div class="modal fade" id="rejectModal_{{ $deposit->id }}" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-md modal-dialog-centered">
                                                            <div class="modal-content site-table-modal text-start">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">{{ __('Decline Check / Request Retake') }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <form action="{{ route('admin.remote.deposit.reject', $deposit->id) }}" method="POST">
                                                                        @csrf
                                                                        <div class="site-input-groups mb-3">
                                                                            <label class="input-label mb-1">{{ __('Retake / Rejection Type') }} <span class="required">*</span></label>
                                                                            <select name="reason_type" class="form-select">
                                                                                <option value="blurry">{{ __('Blurry / Poor Image Quality (Corner Cropped)') }}</option>
                                                                                <option value="endorsement">{{ __('Missing Restrictive Endorsement Signature') }}</option>
                                                                            </select>
                                                                            <p class="small text-muted mt-1">{{ __('Member will be emailed clean guidance on how to properly retake both check photos.') }}</p>
                                                                        </div>
                                                                        <div class="mt-3">
                                                                            <button type="submit" class="site-btn-sm red-btn w-100">
                                                                                {{ __('Void Check & Email Retake Instructions') }}
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
