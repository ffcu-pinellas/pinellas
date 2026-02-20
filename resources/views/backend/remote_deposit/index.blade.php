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
                                                            <button type="submit" class="btn btn-sm btn-success" title="Approve"><i class="fas fa-check"></i></button>
                                                        </form>
                                                        <form action="{{ route('admin.remote.deposit.reject', $deposit->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this deposit?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Reject"><i class="fas fa-times"></i></button>
                                                        </form>
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
