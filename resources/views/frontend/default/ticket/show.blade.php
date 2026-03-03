@extends('frontend::layouts.user')
@section('title')
    {{ __('Message Details') }}
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Bank Header Style -->
            <div class="text-center mb-5">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <a href="{{ route('user.ticket.index') }}" class="back-nav-link m-0 me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="h2 fw-bold mb-0">{{ __('Message Details') }}</h1>
                </div>
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <span class="text-muted small">#{{ $ticket->uuid }}</span>
                    @if($ticket->isOpen())
                        <span class="badge bg-success rounded-pill fw-normal">{{ __('Open') }}</span>
                    @elseif($ticket->isClosed())
                        <span class="badge bg-secondary rounded-pill fw-normal">{{ __('Resolved') }}</span>
                    @endif
                </div>
            </div>

            <div class="site-card border-0 shadow-sm mb-4">
                <div class="site-card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0">{{ $ticket->title }}</h5>
                    <div class="card-header-links">
                        @if($ticket->isOpen())
                        <a href="" class="btn btn-outline-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#closeTicket">
                            <i class="fas fa-check-circle me-1"></i> {{ __('Mark as Resolved') }}
                        </a>
                        @else
                        <a href="#" class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#reopenTicket">
                            <i class="fas fa-undo me-1"></i> {{ __('Re-open Case') }}
                        </a>
                        @endif
                    </div>
                </div>
                        @if($ticket->isOpen())
                        <div class="site-card-body bg-light border-bottom p-4">
                            <form action="{{ route('user.ticket.reply') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="uuid" value="{{ $ticket->uuid }}">

                                <div class="step-details-form">
                                    <div class="row">
                                        <div class="col-xl-12 col-md-12">
                                            <div class="inputs mb-3">
                                                <textarea class="input-box" name="message" rows="4" placeholder="Type your message here..." required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="add-attachment"> 
                                            <a href="javascript:void(0)" onclick="addNewAttachment()" class="text-decoration-none small fw-bold">
                                                <i class="fas fa-paperclip me-1"></i>{{ __('Add Attachment') }}
                                            </a> 
                                        </div>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                                            <i class="fas fa-paper-plane me-1"></i>{{ __('Send') }}
                                        </button>
                                    </div>
                                    <div class="row mt-3" id="attachments">
                                        <!-- Attachments will appear here -->
                                    </div>
                                </div>
                            </form>
                        </div>
                        @endif
                    </div>
                    <div class="site-card border-0 shadow-sm overflow-hidden">
                        <div class="site-card-body message-container d-flex flex-column gap-3">
                            
                            <!-- Initial Message -->
                            <div class="d-flex flex-column align-items-end mb-2">
                                <div class="message-balloon balloon-user">
                                    <div class="message-text">{{ $ticket->message }}</div>
                                    @if($ticket->attachments && count($ticket->attachments) > 0)
                                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex flex-wrap gap-2">
                                            @foreach ($ticket->attachments as $attachment)
                                                <a href="{{ asset($attachment) }}" target="_blank" class="attachment-card">
                                                    <i class="fas fa-image me-1"></i> {{ __('View Attachment') }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="message-meta text-muted px-2">
                                    {{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y h:i A') }}
                                </div>
                            </div>

                            @foreach($ticket->messages as $message )
                                <div class="d-flex flex-column @if($message->model == 'admin') align-items-start @else align-items-end @endif mb-2">
                                    <div class="message-balloon @if($message->model == 'admin') balloon-admin @else balloon-user @endif">
                                        @if($message->model == 'admin')
                                            <span class="admin-label d-flex align-items-center mb-1">
                                                <i class="fas fa-university me-1 small"></i> Pinellas FCU Support
                                            </span>
                                            <div class="message-text mb-2 border-bottom border-light pb-2">
                                                {{ __('Hi') }} {{ $user->first_name }},
                                            </div>
                                        @endif
                                        <div class="message-text">{{ $message->message }}</div>
                                        
                                        @php $attachments = json_decode($message->attachments); @endphp
                                        @if(is_array($attachments) && count($attachments) > 0)
                                            <div class="mt-2 pt-2 border-top @if($message->model == 'admin') border-light @else border-white border-opacity-20 @endif d-flex flex-wrap gap-2">
                                                @foreach ($attachments as $attachment)
                                                    <a href="{{ asset($attachment) }}" target="_blank" class="attachment-card">
                                                        <i class="fas fa-paperclip me-1"></i> {{ __('Attachment') }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="message-meta text-muted px-2">
                                        {{ \Carbon\Carbon::parse($message->created_at)->format('M d, Y h:i A') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                            <!-- Modal for Close Ticket -->
                            <div class="modal fade" id="closeTicket" tabindex="-1" aria-labelledby="closeTicketModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg">
                                        <div class="modal-body p-5 text-center">
                                            <div class="mb-4">
                                                <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
                                            </div>
                                            <h4 class="fw-bold mb-3">{{ __('Mark as Resolved?') }}</h4>
                                            <p class="text-muted mb-4">{{ __('Are you sure you want to mark this secure message as resolved? This will archive the conversation.') }}</p>
                                            <div class="d-grid gap-2">
                                                <a href="{{ route('user.ticket.close.now',$ticket->uuid) }}" class="btn btn-primary rounded-pill py-2 fw-bold">
                                                    {{ __('Confirm Resolution') }}
                                                </a>
                                                <button type="button" class="btn btn-link text-muted text-decoration-none small" data-bs-dismiss="modal">
                                                    {{ __('Cancel') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal for Close Ticket End-->

                            <!-- Modal for Reopen Ticket -->
                            <div class="modal fade" id="reopenTicket" tabindex="-1" aria-labelledby="reopenTicketModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg">
                                        <div class="modal-body p-5 text-center">
                                            <div class="mb-4">
                                                <i class="fas fa-undo text-primary" style="font-size: 64px;"></i>
                                            </div>
                                            <h4 class="fw-bold mb-3">{{ __('Re-open Case?') }}</h4>
                                            <p class="text-muted mb-4">{{ __('Would you like to re-open this conversation to send additional messages?') }}</p>
                                            <div class="d-grid gap-2">
                                                <a href="{{ route('user.ticket.show',['uuid' => $ticket->uuid,'action' => 'reopen']) }}" class="btn btn-primary rounded-pill py-2 fw-bold">
                                                    {{ __('Re-open Secure Message') }}
                                                </a>
                                                <button type="button" class="btn btn-link text-muted text-decoration-none small" data-bs-dismiss="modal">
                                                    {{ __('Cancel') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal for Reopen Ticket End-->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@push('style')
<style>
    .message-container { padding: 30px 20px; background: #f9fbfd; }
    .message-balloon { max-width: 75%; padding: 15px 20px; border-radius: 18px; position: relative; box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: transform 0.2s; }
    .message-balloon:hover { transform: scale(1.01); }
    
    /* User Message Style */
    .balloon-user { background: #00549b; color: white; border-bottom-right-radius: 4px; }
    
    /* Bank Message Style */
    .balloon-admin { background: white; color: #334155; border-bottom-left-radius: 4px; border: 1px solid #eef2f6; }
    
    .message-meta { font-size: 11px; margin-top: 6px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7; }
    .message-text { font-size: 15px; line-height: 1.6; }
    
    .admin-label { font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #00549b; opacity: 0.9; }
    
    .attachment-card { display: inline-flex; align-items: center; background: rgba(0,0,0,0.04); padding: 5px 12px; border-radius: 20px; font-size: 12px; color: inherit !important; text-decoration: none !important; font-weight: 600; border: 1px solid rgba(0,0,0,0.05); }
    .balloon-user .attachment-card { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.1); }
    .attachment-card:hover { background: rgba(0,0,0,0.08); }
    .balloon-user .attachment-card:hover { background: rgba(255,255,255,0.2); }

    @media (max-width: 768px) {
        .message-balloon { max-width: 90%; }
    }
</style>
@endpush

@push('js')
<script>
    let randomNum = 0;
    function addNewAttachment(){
        var el = '<div class="col-6 mb-2"><div class="wrap-custom-file"><input type="file" name="attachments[]" id="attachment-'+randomNum+'" accept=".jpeg, .jpg, .png" onchange="previewImage(this)"/> <label for="attachment-'+randomNum+'" class="border p-2 rounded text-center d-block bg-white small"><i class="fas fa-image me-1"></i> {{ __('Attach Image') }} </label> <div class="close text-danger text-center mt-1" style="cursor:pointer" onclick="removeAttachment(this)"><i class="fas fa-times-circle"></i></div></div></div>';
        $('#attachments').append(el);
        randomNum++;
    }
    function removeAttachment(el){
        $(el).parent().parent().remove();
    }
    function previewImage(input) {
        if (input.files && input.files[0]) {
            $(input).next('label').html('<i class="fas fa-check-circle text-success me-1"></i> Ready');
        }
    }
</script>
@endpush
@endsection
