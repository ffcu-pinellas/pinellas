@extends('frontend::layouts.user')

@section('title')
{{ __('Message Center') }}
@endsection

@section('content')
                <div class="site-card-body p-0">
                    <div class="row g-0">
                        <!-- Message List (Left) -->
                        <div class="col-md-4 border-end">
                            <div class="p-3 border-bottom bg-light">
                                <div class="position-relative">
                                    <input type="text" class="form-control rounded-pill ps-5" placeholder="Search messages">
                                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                                </div>
                            </div>
                            <div class="message-list" style="height: 500px; overflow-y: auto;">
                                @forelse($tickets as $ticket)
                                    <a href="{{ route('user.ticket.show', $ticket->uuid) }}" class="d-block text-decoration-none p-3 border-bottom hover-bg-light">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-bold text-dark">{{ Str::limit($ticket->title, 20) }}</span>
                                            <span class="small text-muted">{{ $ticket->created_at->format('M d') }}</span>
                                        </div>
                                        <div class="small text-muted text-truncate">{{ $ticket->message }}</div>
                                        <div>
                                            @if($ticket->status == 'open')
                                                <span class="badge bg-success small mt-1">Open</span>
                                            @else
                                                <span class="badge bg-secondary small mt-1">Closed</span>
                                            @endif
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center p-4 text-muted">
                                        <p>No messages yet.</p>
                                    </div>
                                @endforelse
                            </div>
                            <div class="p-3 border-top text-center">
                                <a href="{{ route('user.ticket.index') }}" class="btn btn-primary w-100 rounded-pill">View All Tickets</a>
                            </div>
                        </div>

                        <!-- Message Content (Right - Placeholder for selection) -->
                        <div class="col-md-8 d-none d-md-flex flex-column align-items-center justify-content-center bg-white" style="height: 600px;">
                            <img src="{{ asset('frontend/theme_base/money_transfer/message_placeholder.png') }}" class="mb-3" style="max-width: 150px; opacity: 0.5;" alt="">
                            <h5 class="text-muted">Select a conversation to start messaging</h5>
                            <a href="{{ route('user.ticket.index') }}" class="btn btn-outline-primary mt-3 rounded-pill">
                                <i class="fas fa-plus me-2"></i> New Support Ticket
                            </a>
                        </div>
                    </div>
                </div>
@endsection
