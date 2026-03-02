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
                    <div class="site-card overflow-hidden">
                        <div class="site-card-body message-container">
                            
                            <!-- Initial Message -->
                            <div class="message-balloon balloon-user">
                                <div class="message-text">{{ $ticket->message }}</div>
                                <div class="message-meta">
                                    {{ $ticket->created_at->format('M d, Y h:i A') }}
                                </div>
                                @if($ticket->attachments && count($ticket->attachments) > 0)
                                    @foreach ($ticket->attachments as $attachment)
                                        <a href="{{ asset($attachment) }}" target="_blank" class="attachment-card">
                                            <i class="fas fa-image me-2"></i> {{ __('View Image') }}
                                        </a>
                                    @endforeach
                                @endif
                            </div>

                            @foreach($ticket->messages as $message )
                                <div class="message-balloon @if($message->model == 'admin') balloon-admin @else balloon-user @endif">
                                    @if($message->model == 'admin')
                                        <span class="admin-label">Pinellas FCU Support</span>
                                    @endif
                                    <div class="message-text">
                                        @if($message->model == 'admin')
                                            {{ __('Hi') }} {{ $user->first_name }},<br><br>
                                        @endif
                                        {{ $message->message }}
                                    </div>
                                    <div class="message-meta">
                                        {{ $message->created_at->format('M d, Y h:i A') }}
                                    </div>
                                    @php
                                        $attachments = json_decode($message->attachments);
                                    @endphp
                                    @if(is_array($attachments) && count($attachments) > 0)
                                        @foreach ($attachments as $attachment)
                                            <a href="{{ asset($attachment) }}" target="_blank" class="attachment-card">
                                                <i class="fas fa-image me-2"></i> {{ __('View Image') }}
                                            </a>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                            
                            <div style="clear: both;"></div>
                        </div>
                    </div>

                            <!-- Modal for Close Ticket -->
                            <div class="modal fade" id="closeTicket" tabindex="-1" aria-labelledby="closeTicketModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-md modal-dialog-centered">
                                    <div class="modal-content site-table-modal">
                                        <div class="modal-body popup-body"> <button type="button" class="modal-btn-close" data-bs-dismiss="modal" aria-label="Close"> <i data-lucide="x"></i> </button>
                                            <div class="popup-body-text centered">
                                                <div class="info-icon"> <i data-lucide="alert-triangle"></i> </div>
                                                <div class="title">
                                                    <h4>{{ __('Are you sure?') }}</h4>
                                                </div>
                                                <p>{{ __('You want to Close this Ticket?') }}</p>
                                                <div class="action-btns"> <a href="{{ route('user.ticket.close.now',$ticket->uuid) }}" class="site-btn-sm primary-btn me-2"> <i data-lucide="check"></i> Confirm </a> <a href="" class="site-btn-sm red-btn" data-bs-dismiss="modal" aria-label="Close"> <i data-lucide="x"></i> Cancel </a> </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal for Close Ticket End-->

                            <!-- Modal for Reopen Ticket -->
                            <div class="modal fade" id="reopenTicket" tabindex="-1" aria-labelledby="reopenTicketModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-md modal-dialog-centered">
                                    <div class="modal-content site-table-modal">
                                        <div class="modal-body popup-body"> <button type="button" class="modal-btn-close" data-bs-dismiss="modal" aria-label="Close"> <i data-lucide="x"></i> </button>
                                            <div class="popup-body-text centered">
                                                <div class="info-icon"> <i data-lucide="alert-triangle"></i> </div>
                                                <div class="title">
                                                    <h4>{{ __('Are you sure?') }}</h4>
                                                </div>
                                                <p>{{ __('You want to reopen this Ticket?') }}</p>
                                                <div class="action-btns"> <a href="{{ route('user.ticket.show',['uuid' => $ticket->uuid,'action' => 'reopen']) }}" class="site-btn-sm primary-btn me-2"> <i data-lucide="check"></i> Confirm </a> <a href="" class="site-btn-sm red-btn" data-bs-dismiss="modal" aria-label="Close"> <i data-lucide="x"></i> Cancel </a> </div>
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
    .message-container { padding: 20px; background: #f9fbfd; }
    .message-balloon { max-width: 80%; margin-bottom: 20px; padding: 15px 20px; border-radius: 20px; position: relative; clear: both; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    
    /* User Message (Right) */
    .balloon-user { float: right; background: #00549b; color: white; border-bottom-right-radius: 4px; }
    .balloon-user .message-meta { color: rgba(255,255,255,0.8); text-align: right; }
    
    /* Admin/Bank Message (Left) */
    .balloon-admin { float: left; background: white; color: #334155; border-bottom-left-radius: 4px; border: 1px solid #e2e8f0; }
    .balloon-admin .message-meta { color: #64748b; }
    
    .message-meta { font-size: 11px; margin-top: 5px; }
    .message-text { font-size: 15px; line-height: 1.5; }
    
    .avatar-sm { width: 32px; height: 32px; border-radius: 50%; margin-bottom: 5px; }
    .admin-label { font-weight: 700; font-size: 12px; display: block; margin-bottom: 4px; color: #00549b; }
    
    .attachment-card { display: inline-flex; align-items: center; background: rgba(0,0,0,0.05); padding: 5px 12px; border-radius: 8px; margin-top: 10px; font-size: 13px; color: inherit; text-decoration: none; }
    .balloon-user .attachment-card { background: rgba(255,255,255,0.1); color: white; }
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
