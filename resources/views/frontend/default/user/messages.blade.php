@extends('frontend::layouts.user')

@section('title')
{{ __('Message Center') }}
@endsection

@section('content')
<div class="row h-100">
    <!-- Message List (Left) -->
    <div class="col-lg-4 border-end bg-white p-0 d-none d-lg-block">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Messages</h5>
            <button class="btn btn-sm btn-primary rounded-circle"><i class="fas fa-plus"></i></button>
        </div>
        <div class="list-group list-group-flush">
            <!-- Active Message Item -->
            <a href="#" class="list-group-item list-group-item-action active border-0 py-3" aria-current="true">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1 text-truncate">Holiday Hours</h6>
                    <small>Feb 15</small>
                </div>
                <p class="mb-1 small text-truncate">We will be closed on Monday...</p>
                 <small class="text-white-50">Pinellas FCU</small>
            </a>
            
            <!-- Other Items -->
            <a href="#" class="list-group-item list-group-item-action border-0 py-3">
                 <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1 text-truncate">Deposit Confirmation</h6>
                    <small class="text-muted">Jan 28</small>
                </div>
                <p class="mb-1 small text-muted text-truncate">Your deposit of $500.00 has been...</p>
                 <small class="text-muted">System</small>
            </a>
        </div>
    </div>

    <!-- Message Content (Right) -->
    <div class="col-lg-8 p-0 d-flex flex-column" style="height: calc(100vh - 120px);">
        <div class="p-3 border-bottom bg-white d-flex align-items-center">
            <button class="btn btn-link text-dark d-lg-none me-2"><i class="fas fa-arrow-left"></i></button>
            <div>
                 <h6 class="mb-0 fw-bold">Holiday Hours</h6>
                 <small class="text-muted">From: Pinellas FCU | Feb 15, 2026</small>
            </div>
        </div>
        
        <div class="flex-grow-1 p-4 overflow-auto bg-light">
            <div class="d-flex mb-3">
                <div class="bg-white p-3 rounded-3 shadow-sm" style="max-width: 80%;">
                    <p class="mb-0">President's Day<br>We represent closed on February 16th. We will open for regular hours on February 17th.</p>
                </div>
            </div>
             <div class="d-flex justify-content-end mb-3">
                <div class="bg-primary text-white p-3 rounded-3 shadow-sm" style="max-width: 80%;">
                    <p class="mb-0">Thank you for the update!</p>
                </div>
            </div>
        </div>

        <div class="p-3 bg-white border-top">
             <div class="input-group">
                <input type="text" class="form-control" placeholder="Type a message..." aria-label="Type a message">
                <button class="btn btn-primary" type="button"><i class="fas fa-paper-plane"></i></button>
             </div>
        </div>
    </div>
</div>
@endsection
