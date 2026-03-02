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
                    <form id="filter-form">
                        <div class="table-filter">
                            <div class="filter">
                                <div class="single-f-box">
                                    <label for="">{{ __('Subject') }}</label>
                                    <input class="search" type="text" name="subject" value="{{ request('subject') }}" autocomplete="off"/>
                                </div>
                                <div class="single-f-box">
                                    <label for="">{{ __('Date') }}</label>
                                    <input type="text" name="daterange" value="{{ request('daterange') }}" autocomplete="off" />
                                </div>
                                <button class="apply-btn me-2" name="filter">
                                    <i data-lucide="filter"></i>{{ __('Filter') }}
                                </button>
                                @if(request()->has('filter'))
                                <button type="button" class="apply-btn bg-danger reset-filter">
                                    <i data-lucide="x"></i>{{ __('Reset Filter') }}
                                </button>
                                @endif
                            </div>
                            <div class="filter">
                                <div class="single-f-box w-auto ms-4 me-0">
                                    <label for="">{{ __('Entries') }}</label>
                                    <select name="limit" class="nice-select page-count" onchange="$('#filter-form').submit()">
                                        <option value="15" @selected(request('limit',15) == '15')>15</option>
                                        <option value="30" @selected(request('limit') == '30')>30</option>
                                        <option value="50" @selected(request('limit') == '50')>50</option>
                                        <option value="100" @selected(request('limit') == '100')>100</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
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
                            <div class="site-table-list">
                                <div class="site-table-col">
                                    <div class="description">
                                        <div class="event-icon">
                                            <i data-lucide="message-circle"></i> 
                                        </div>
                                        <div class="content">
                                            <div class="title fw-bold">
                                                <a href="{{ route('user.ticket.show',$ticket->uuid) }}" class="text-dark text-decoration-none">
                                                    {{ $ticket->title }} <span class="text-muted small ms-1">#{{ $ticket->uuid }}</span>
                                                </a>
                                            </div>
                                            <div class="date">{{ $ticket->created_at }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="site-table-col">
                                    @if($ticket->priority == 'low')
                                    <div class="type site-badge badge-pending">{{ $ticket->priority }}</div>
                                    @elseif($ticket->priority == 'high')
                                    <div class="type site-badge badge-primary">{{ $ticket->priority }}</div>
                                    @else
                                    <div class="type site-badge badge-success">{{ $ticket->priority }}</div>
                                    @endif
                                </div>
                                <div class="site-table-col">
                                    <div class="trx fw-bold">{{ $ticket->messages->last()?->created_at->diffForHumans() ?? '--' }}</div>
                                </div>
                                <div class="site-table-col">
                                    @if($ticket->isOpen())
                                        <span class="ms-2 status site-badge badge-primary">{{ __('Opened') }}</span>
                                    @elseif($ticket->isClosed())
                                        <span class="ms-2 status site-badge badge-failed">{{ __('Closed') }}</span>
                                    @endif
                                </div>
                                <div class="site-table-col">
                                    <div class="action">
                                        <a href="{{ route('user.ticket.show',$ticket->uuid) }}" class="icon-btn"><i data-lucide="eye"></i>{{ __('View') }}</a>
                                    </div>
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
                        <div class="modal-dialog modal-md modal-dialog-centered">
                            <div class="modal-content site-table-modal">
                                <div class="modal-body popup-body"> <button type="button" class="modal-btn-close" data-bs-dismiss="modal" aria-label="Close"> <i data-lucide="x"></i> </button>
                                    <div class="popup-body-text">
                                        <div class="title fw-bold text-primary mb-3"><i class="fas fa-edit me-2"></i>{{ __('New Support Request') }}</div>

                                        <form action="{{ route('user.ticket.store') }}" method="post" enctype="multipart/form-data">
                                            @csrf
                                            <div class="step-details-form">
                                                <div class="row">
                                                    <div class="col-xl-12 col-lg-12 col-md-12">
                                                        <div class="inputs mb-3">
                                                            <label for="" class="input-label fw-bold">{{ __('Subject') }}<span class="text-danger">*</span></label>
                                                            <input type="text" class="input-box" name="title" placeholder="Brief description of your request" required />
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12 col-md-12">
                                                        <div class="inputs mb-3">
                                                            <label for="" class="input-label fw-bold">{{ __('Inquiry Category') }}<span class="text-danger">*</span></label>
                                                            <select class="input-box" name="priority" required>
                                                                <option selected disabled value="">{{ __('Select Category') }}</option>
                                                                <option value="low">{{ __('General Inquiry') }}</option>
                                                                <option value="medium">{{ __('Account Support') }}</option>
                                                                <option value="high">{{ __('Urgent / Fraud Report') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12 col-md-12">
                                                        <div class="inputs mb-3">
                                                            <label for="" class="input-label fw-bold">{{ __('How can we help you?') }}<span class="text-danger">*</span></label>
                                                            <textarea class="input-box" name="message" rows="5" placeholder="Please provide details about your request..." required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">

                                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                                            <div class="add-attachment"> <a href="javascript:void(0)" onclick="addNewAttachment()">
                                                                <i data-lucide="plus-circle"></i>{{ __('Add') }}</a>
                                                            </div>
                                                        </div>

                                                        <div id="attachments">
                                                            <div class="wrap-custom-file">
                                                                <input type="file" name="attachments[]" id="attach" accept=".jpeg, .jpg, .png" />
                                                                <label for="attach">
                                                                    <img class="upload-icon" src="{{ asset('front/images/icons/upload.svg') }}" alt="" />
                                                                    <span>{{ __('Attach Image') }}</span>
                                                                </label>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="action-btns mt-4 d-flex gap-2">
                                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                                    <i class="fas fa-paper-plane me-1"></i> {{ __('Send Message') }}
                                                </button>

                                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-toggle="modal" aria-label="Close">
                                                    {{ __('Cancel') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
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
