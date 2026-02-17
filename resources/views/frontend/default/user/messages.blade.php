@extends('frontend::layouts.user')

@section('title')
{{ __('Messages') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Banno Support Header -->
        <div class="text-center mb-5">
            <h1 class="h2 fw-bold mb-3">Support</h1>
            <p class="text-muted">Need help? Start a conversation with one of our member service representatives.</p>
        </div>

        <div class="site-card overflow-hidden">
            <!-- Team Presence -->
            <div class="p-5 text-center border-bottom bg-white">
                <div class="d-flex justify-content-center mb-4">
                    <div class="avatar-group d-flex">
                        <div class="rounded-circle border border-white border-4 overflow-hidden shadow-sm" style="width: 80px; height: 80px; margin-right: -20px; z-index: 3;">
                            <img src="https://i.pravatar.cc/150?u=a" alt="Agent" class="w-100 h-100 object-fit-cover">
                        </div>
                        <div class="rounded-circle border border-white border-4 overflow-hidden shadow-sm" style="width: 80px; height: 80px; margin-right: -20px; z-index: 2;">
                            <img src="https://i.pravatar.cc/150?u=c" alt="Agent" class="w-100 h-100 object-fit-cover">
                        </div>
                        <div class="rounded-circle border border-white border-4 overflow-hidden shadow-sm" style="width: 80px; height: 80px; z-index: 1;">
                            <img src="https://i.pravatar.cc/150?u=co" alt="Agent" class="w-100 h-100 object-fit-cover">
                        </div>
                    </div>
                </div>
                <h5 class="fw-bold mb-2">Pinellas FCU Member Service</h5>
                <p class="small text-muted mb-4 px-lg-5">
                    Our team is available to assist you during regular business hours.<br>
                    Messages sent after hours will be answered the following business day.
                </p>
                <a href="{{ route('user.ticket.index') }}" class="btn btn-danger rounded-pill px-5 py-2 fw-bold shadow-sm">
                    Start a conversation
                </a>
            </div>

            <!-- Recent Conversations -->
            <div class="bg-light p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-uppercase small text-muted mb-0">Recent Conversations</h6>
                    <a href="{{ route('user.ticket.index') }}" class="small text-decoration-none fw-bold">View all</a>
                </div>

                <div class="conversation-list">
                    @forelse($tickets as $ticket)
                    <div class="site-card mb-2 border-0 shadow-sm activity-row" onclick="window.location.href='{{ route('user.ticket.show', $ticket->uuid) }}'">
                        <div class="p-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-comment-dots"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ Str::limit($ticket->title, 40) }}</div>
                                    <div class="small text-muted">{{ $ticket->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                            <div>
                                @if($ticket->status == 'open')
                                    <span class="badge bg-success rounded-pill fw-normal">Open</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill fw-normal">Closed</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 bg-white rounded-3 border">
                        <p class="text-muted small mb-0">No past conversations found.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Contact Methods -->
        <div class="row g-4 mt-4">
            <div class="col-md-6">
                <div class="site-card p-4 text-center h-100 border-0 shadow-sm">
                    <i class="fas fa-phone-alt fa-2x text-primary mb-3"></i>
                    <h6 class="fw-bold">Call Us</h6>
                    <p class="small text-muted">727.586.3322<br>Toll Free: 800.226.3322</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="site-card p-4 text-center h-100 border-0 shadow-sm">
                    <i class="fas fa-map-marker-alt fa-2x text-primary mb-3"></i>
                    <h6 class="fw-bold">Visit a Branch</h6>
                    <p class="small text-muted">Find our locations and shared branches across the country.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
    .activity-row { cursor: pointer; transition: transform 0.2s; }
    .activity-row:hover { transform: translateY(-2px); }
    .avatar-group div { background: white; }
</style>
@endsection

