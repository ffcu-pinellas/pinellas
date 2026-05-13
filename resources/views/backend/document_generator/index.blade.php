@extends('backend.layouts.app')
@section('title')
    {{ __('Document Generator') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="title-content">
                            <h2 class="title">{{ __('Generate Customer Document') }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="site-card">
                        <div class="site-card-body">
                            <form action="{{ route('admin.document-generator.generate') }}" method="post" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="input-box">
                                            <label for="user_id">{{ __('Select Customer (Optional)') }}</label>
                                            <select class="form-select select2" name="user_id" id="user_id">
                                                <option value="">{{ __('None (General Document)') }}</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->username }})</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">{{ __('Selecting a customer allows you to use dynamic variables like [USER_NAME].') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="input-box">
                                            <label for="title">{{ __('Document Title') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" id="title" required placeholder="e.g., Account Verification Letter">
                                        </div>
                                    </div>

                                    <div class="col-xl-12 mt-4">
                                        <div class="input-box">
                                            <label for="content">{{ __('Document Content') }} <span class="text-danger">*</span></label>
                                            <div class="alert alert-info">
                                                <strong>{{ __('Available Variables (if customer is selected):') }}</strong><br>
                                                <code>[USER_NAME]</code> : Customer's Full Name <br>
                                                <code>[USER_ADDRESS]</code> : Customer's Address <br>
                                                <code>[USER_ACCOUNT_NUMBER]</code> : Customer's Account Number <br>
                                                <code>[USER_BALANCE]</code> : Customer's Current Balance
                                            </div>
                                            <textarea class="form-control summernote" name="content" id="content" rows="15" required></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-xl-12 d-flex gap-2">
                                        <button type="submit" name="action" value="preview" class="site-btn secondary-btn" formtarget="_blank">{{ __('Preview PDF') }}</button>
                                        <button type="submit" name="action" value="download" class="site-btn primary-btn">{{ __('Download PDF') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "{{ __('Select a customer') }}",
                allowClear: true
            });
            
            $('.summernote').summernote({
                height: 400,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear', 'italic']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
@endsection
