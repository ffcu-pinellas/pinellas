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
                                                <div class="mt-2 mb-2 d-flex gap-2 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-primary insert-var" data-var="[USER_NAME]">[USER_NAME]</button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary insert-var" data-var="[USER_ADDRESS]">[USER_ADDRESS]</button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary insert-var" data-var="[USER_ACCOUNT_NUMBER]">[USER_ACCOUNT_NUMBER]</button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary insert-var" data-var="[USER_BALANCE]">[USER_BALANCE]</button>
                                                </div>
                                                <small>{{ __('Tip: You can use the "Code View" button (</>) in the editor toolbar to paste raw HTML.') }}</small>
                                            </div>
                                            <textarea class="form-control summernote" name="content" id="content" rows="15" required></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-xl-12">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="sendEmailCheckbox" name="send_email" value="1">
                                            <label class="form-check-label" for="sendEmailCheckbox">{{ __('Send Document via Email to Customer') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row email-fields" style="display: none;">
                                    <div class="col-xl-6">
                                        <div class="input-box">
                                            <label for="email_subject">{{ __('Email Subject') }}</label>
                                            <input type="text" class="form-control" name="email_subject" id="email_subject" placeholder="e.g., Your requested document">
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="input-box">
                                            <label for="email_salutation">{{ __('Email Salutation') }}</label>
                                            <input type="text" class="form-control" name="email_salutation" id="email_salutation" placeholder="e.g., Dear [USER_NAME]">
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mt-3">
                                        <div class="input-box">
                                            <label for="email_content">{{ __('Email Message Content') }}</label>
                                            <textarea class="form-control summernote-email" name="email_content" id="email_content" rows="5"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-xl-12 d-flex gap-2 flex-wrap">
                                        <button type="submit" name="action" value="preview" class="site-btn secondary-btn" formtarget="_blank">{{ __('Preview PDF') }}</button>
                                        <button type="submit" name="action" value="download" class="site-btn primary-btn">{{ __('Download PDF') }}</button>
                                        <button type="submit" name="action" value="send_email" class="site-btn success-btn email-send-btn" style="display: none;">{{ __('Send via Email') }}</button>
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
            
            var summernoteOptions = {
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
            };

            $('.summernote').summernote(summernoteOptions);
            
            var emailSummernoteOptions = Object.assign({}, summernoteOptions);
            emailSummernoteOptions.height = 200;
            $('.summernote-email').summernote(emailSummernoteOptions);

            $('.insert-var').on('click', function() {
                var variable = $(this).data('var');
                $('.summernote').summernote('editor.insertText', variable);
            });

            $('#sendEmailCheckbox').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.email-fields').slideDown();
                    $('.email-send-btn').show();
                } else {
                    $('.email-fields').slideUp();
                    $('.email-send-btn').hide();
                }
            });

            $('#user_id').on('change', function() {
                if ($(this).val() == '') {
                    $('#sendEmailCheckbox').prop('checked', false).trigger('change');
                    $('#sendEmailCheckbox').prop('disabled', true);
                } else {
                    $('#sendEmailCheckbox').prop('disabled', false);
                }
            });
            // trigger on load
            $('#user_id').trigger('change');
        });
    </script>
@endsection
