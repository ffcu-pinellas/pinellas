@extends('frontend::layouts.user')
@section('title')
    {{ __('Secure Messages') }}
@endsection
@push('style')
<link rel="stylesheet" href="{{ asset('front/css/daterangepicker.css') }}">
@endpush
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Bank Header Style -->
            <div class="text-center mb-5">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <a href="{{ route('user.dashboard') }}" class="back-nav-link m-0 me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="h2 fw-bold mb-0">{{ __('Secure Message Center') }}</h1>
                </div>
                <p class="text-muted">{{ __('Communicate securely with Pinellas FCU member services.') }}</p>
            </div>

            <div class="site-card border-0 shadow-sm">
                <div class="site-card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-inbox me-2 text-primary"></i>{{ __('Inbox') }}</h5>
                    <div class="card-header-links">
                        <a href="javascript:void(0)"
                            class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#openTicket"
                            >
                            <i class="fas fa-edit me-1"></i> {{ __('Compose') }}
                        </a>
                    </div>
                </div>
                <div class="site-card-body p-0 overflow-x-auto">
                    <div class="px-4 py-3 bg-light border-bottom">
                        <form id="filter-form" class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" name="subject" class="form-control border-start-0" placeholder="Search by subject..." value="{{ request('subject') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                                    <input type="text" name="daterange" class="form-control border-start-0" placeholder="Filter by date..." value="{{ request('daterange') }}">
                                </div>
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 flex-grow-1 fw-bold">{{ __('Filter') }}</button>
                                @if(request()->has('subject') || request()->has('daterange'))
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill reset-filter"><i class="fas fa-undo"></i></button>
                                @endif
                            </div>
                        </form>
                    </div>
                    <div class="site-custom-table">
                        <div class="contents">
                            <div class="site-table-list site-table-head bg-light">
                                <div class="site-table-col fw-bold">{{ __('Subject / Message ID') }}</div>
                                <div class="site-table-col fw-bold text-center">{{ __('Priority') }}</div>
                                <div class="site-table-col fw-bold text-center">{{ __('Last Activity') }}</div>
                                <div class="site-table-col fw-bold text-center">{{ __('Status') }}</div>
                                <div class="site-table-col fw-bold text-end">{{ __('Action') }}</div>
                            </div>
                            @foreach ($tickets as $ticket)
                            <div class="site-table-list py-3 border-bottom hover-bg-light transition-all" style="cursor: pointer;" onclick="window.location.href='{{ route('user.ticket.show',$ticket->uuid) }}'">
                                <div class="site-table-col">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                            <i class="fas fa-comment-alt"></i>
                                        </div>
                                        <div class="content">
                                            <div class="title fw-bold text-dark mb-0">
                                                {{ $ticket->title }}
                                            </div>
                                            <div class="small text-muted">ID: #{{ $ticket->uuid }} • {{ $ticket->created_at->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="site-table-col text-center">
                                    @if($ticket->priority == 'low')
                                        <span class="badge bg-info bg-opacity-10 text-info fw-normal rounded-pill px-3">{{ __('General') }}</span>
                                    @elseif($ticket->priority == 'high')
                                        <span class="badge bg-danger bg-opacity-10 text-danger fw-normal rounded-pill px-3">{{ __('Urgent') }}</span>
                                    @else
                                        <span class="badge bg-primary bg-opacity-10 text-primary fw-normal rounded-pill px-3">{{ __('Support') }}</span>
                                    @endif
                                </div>
                                <div class="site-table-col text-center">
                                    <div class="small fw-bold text-dark">{{ $ticket->messages->last()?->created_at->diffForHumans() ?? '--' }}</div>
                                </div>
                                <div class="site-table-col text-center">
                                    @if($ticket->isOpen())
                                        <span class="badge bg-success rounded-pill fw-normal px-3">{{ __('Open') }}</span>
                                    @elseif($ticket->isClosed())
                                        <span class="badge bg-secondary rounded-pill fw-normal px-3">{{ __('Resolved') }}</span>
                                    @endif
                                </div>
                                <div class="site-table-col text-end">
                                    <a href="{{ route('user.ticket.show',$ticket->uuid) }}" class="btn btn-light btn-sm rounded-circle"><i class="fas fa-chevron-right text-muted"></i></a>
                                </div>
                            </div>
                            @endforeach
                            {{ $tickets->links() }}
                        </div>

                        @if(count($tickets) == 0)
                        <div class="no-data-found">{{ __('No Data Found') }}</div>
                        @endif
                    </div>

                    <!-- Modal for open Ticket-->
                    <div class="modal fade" id="openTicket" tabindex="-1" aria-labelledby="openTicketModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header bg-primary text-white border-0 py-3">
                                    <h5 class="modal-title fw-bold" id="openTicketModalLabel">
                                        <i class="fas fa-edit me-2"></i>{{ __('New Support Request') }}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <form action="{{ route('user.ticket.store') }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small text-muted text-uppercase">{{ __('Subject') }}<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" placeholder="Brief description of your request" required />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small text-muted text-uppercase">{{ __('Inquiry Category') }}<span class="text-danger">*</span></label>
                                            <select class="form-select" name="priority" required>
                                                <option selected disabled value="">{{ __('Select Category') }}</option>
                                                <option value="low">{{ __('General Inquiry') }}</option>
                                                <option value="medium">{{ __('Account Support') }}</option>
                                                <option value="high">{{ __('Urgent / Fraud Report') }}</option>
                                            </select>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label fw-bold small text-muted text-uppercase">{{ __('How can we help you?') }}<span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="message" rows="5" placeholder="Please provide details about your request..." required></textarea>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label fw-bold small text-muted text-uppercase mb-2">{{ __('Attachments') }} <span class="fw-normal text-muted">(Optional)</span></label>
                                            <div id="attachments" class="row g-2">
                                                <div class="col-6 mb-2">
                                                    <div class="wrap-custom-file">
                                                        <input type="file" name="attachments[]" id="attach-0" accept=".jpeg, .jpg, .png" onchange="previewImage(this)"/>
                                                        <label for="attach-0" class="border p-2 rounded text-center d-block bg-white small mb-0" style="cursor:pointer">
                                                            <i class="fas fa-image me-1 text-primary"></i> <span>{{ __('Attach Image') }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="javascript:void(0)" onclick="addNewAttachment()" class="text-decoration-none small fw-bold">
                                                <i class="fas fa-plus-circle me-1"></i>{{ __('Add another image') }}
                                            </a>
                                        </div>

                                        <div class="pt-2">
                                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm mb-2">
                                                <i class="fas fa-paper-plane me-1"></i> {{ __('Send Message') }}
                                            </button>
                                            <button type="button" class="btn btn-link w-100 text-muted text-decoration-none small" data-bs-dismiss="modal">
                                                {{ __('Discard and Close') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Modal for open Ticket end-->
                </div>
            </div>
        </div>
    </div>
    @push('js')
    <script src="{{ asset('front/js/moment.min.js') }}"></script>
    <script src="{{ asset('front/js/daterangepicker.min.js') }}"></script>
    <script>


        // Initialize datepicker
        $('input[name="daterange"]').daterangepicker({
            opens: 'left'
        });

        @if(request('daterange') == null)
        // Set default is empty for date range
        $('input[name=daterange]').val('');
        @endif

        // Reset filter
        $('.reset-filter').on('click',function(){
            window.location.href = "{{ route('user.ticket.index') }}";
        });

        let randomNum = 0;

        function addNewAttachment(){

            var el = '<div class="wrap-custom-file"><input type="file" name="attachments[]" id="attachment-'+randomNum+'" accept=".jpeg, .jpg, .png" /> <label for="attachment-'+randomNum+'"><img class="upload-icon" src="{{ asset('front/images/icons/upload.svg') }}" alt="" /> <span>{{ __('Attach Image') }}</span> </label> <div class="close" onclick="removeAttachment(this)"><i data-lucide="x"></i></div></div>';

            $('#attachments').append(el);

            randomNum++;

            lucide.createIcons();

            runImagePreviewer();
        }

        function removeAttachment(el){
            $(el).parent().remove();
        }

    </script>
    @endpush
@endsection
