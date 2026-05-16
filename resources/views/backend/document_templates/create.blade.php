@extends('backend.layouts.app')
@section('title')
    {{ __('Create Document Template') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="title-content">
                            <h2 class="title">{{ __('Create New Document Template') }}</h2>
                            <a href="{{ route('admin.document-template.index') }}" class="site-btn primary-btn"><i class="ant-arrow-left"></i>{{ __('Back to Templates') }}</a>
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
                            <form action="{{ route('admin.document-template.store') }}" method="post">
                                @csrf

                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="input-box">
                                            <label for="name">{{ __('Template Name') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="name" id="name" required placeholder="e.g., Standard Account Statement">
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="input-box">
                                            <label for="category">{{ __('Category') }} <span class="text-danger">*</span></label>
                                            <select class="form-select" name="category" id="category" required>
                                                <option value="">{{ __('Select Category') }}</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category }}">{{ ucwords(str_replace('_', ' ', $category)) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-xl-12 mt-3">
                                        <div class="input-box">
                                            <label for="description">{{ __('Description (Internal)') }}</label>
                                            <textarea class="form-control" name="description" id="description" rows="2" placeholder="{{ __('Optional short description of this template') }}"></textarea>
                                        </div>
                                    </div>

                                    <div class="col-xl-12 mt-4">
                                        <div class="input-box">
                                            <label for="content">{{ __('PDF Document Content') }} <span class="text-danger">*</span></label>
                                            <div class="alert alert-info">
                                                <strong>{{ __('Available Variables:') }}</strong><br>
                                                <div class="mt-2 mb-2 d-flex gap-2 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-primary insert-var" data-target=".summernote-main" data-var="[USER_NAME]">[USER_NAME]</button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary insert-var" data-target=".summernote-main" data-var="[USER_ADDRESS]">[USER_ADDRESS]</button>
                                                    <button type="button" class="btn btn-sm btn-outline-success insert-var" data-target=".summernote-main" data-var="[CHECKING_ACCOUNT]">[CHECKING_ACCOUNT]</button>
                                                    <button type="button" class="btn btn-sm btn-outline-success insert-var" data-target=".summernote-main" data-var="[CHECKING_BALANCE]">[CHECKING_BALANCE]</button>
                                                    <button type="button" class="btn btn-sm btn-outline-info insert-var" data-target=".summernote-main" data-var="[SAVINGS_ACCOUNT]">[SAVINGS_ACCOUNT]</button>
                                                    <button type="button" class="btn btn-sm btn-outline-info insert-var" data-target=".summernote-main" data-var="[SAVINGS_BALANCE]">[SAVINGS_BALANCE]</button>
                                                    <button type="button" class="btn btn-sm btn-outline-warning insert-var" data-target=".summernote-main" data-var="[IRA_ACCOUNT]">[IRA_ACCOUNT]</button>
                                                    <button type="button" class="btn btn-sm btn-outline-warning insert-var" data-target=".summernote-main" data-var="[IRA_BALANCE]">[IRA_BALANCE]</button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary insert-var" data-target=".summernote-main" data-var="[LOAN_ACCOUNT]">[LOAN_ACCOUNT]</button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary insert-var" data-target=".summernote-main" data-var="[LOAN_BALANCE]">[LOAN_BALANCE]</button>
                                                    <button type="button" class="btn btn-sm btn-outline-dark insert-var" data-target=".summernote-main" data-var="[AUTHORISED_SIGNATURE]">[AUTHORISED_SIGNATURE]</button>
                                                    <button type="button" class="btn btn-sm btn-outline-dark insert-var" data-target=".summernote-main" data-var="[USER_SIGNATURE_LINE]">[USER_SIGNATURE_LINE]</button>
                                                </div>
                                            </div>
                                            <textarea class="form-control summernote-main" name="content" id="content" required></textarea>
                                        </div>
                                    </div>

                                    <div class="col-xl-12 mt-5">
                                        <div class="email-config-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef;">
                                            <h4 class="mb-4">{{ __('Default Email Configuration') }}</h4>
                                            <div class="row">
                                                <div class="col-xl-4">
                                                    <div class="input-box">
                                                        <label for="email_from_name">{{ __('Email From Name') }}</label>
                                                        <input type="text" class="form-control" name="email_from_name" id="email_from_name" placeholder="e.g., Wells Fargo Notification">
                                                    </div>
                                                </div>
                                                <div class="col-xl-4">
                                                    <div class="input-box">
                                                        <label for="email_subject">{{ __('Email Subject') }}</label>
                                                        <input type="text" class="form-control" name="email_subject" id="email_subject" placeholder="e.g., Your requested document">
                                                    </div>
                                                </div>
                                                <div class="col-xl-4">
                                                    <div class="input-box">
                                                        <label for="email_salutation">{{ __('Email Salutation') }}</label>
                                                        <input type="text" class="form-control" name="email_salutation" id="email_salutation" placeholder="e.g., Dear [USER_NAME]">
                                                    </div>
                                                </div>
                                                <div class="col-xl-12 mt-3">
                                                    <div class="input-box">
                                                        <label for="email_content">{{ __('Email Message Content') }}</label>
                                                        <div class="mt-2 mb-2 d-flex gap-2 flex-wrap">
                                                            <button type="button" class="btn btn-sm btn-outline-primary insert-var" data-target=".summernote-email" data-var="[USER_NAME]">[USER_NAME]</button>
                                                            <button type="button" class="btn btn-sm btn-outline-info insert-var" data-target=".summernote-email" data-var="[RECIPIENT_NAME]">[RECIPIENT_NAME]</button>
                                                            <button type="button" class="btn btn-sm btn-outline-success insert-var" data-target=".summernote-email" data-var="[AMOUNT]">[AMOUNT]</button>
                                                            <button type="button" class="btn btn-sm btn-outline-warning insert-var" data-target=".summernote-email" data-var="[STATUS]">[STATUS]</button>
                                                            <button type="button" class="btn btn-sm btn-outline-dark insert-var" data-target=".summernote-email" data-var="[BANK_NAME]">[BANK_NAME]</button>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary insert-var" data-target=".summernote-email" data-var="[ACCOUNT_NUMBER]">[ACCOUNT_NUMBER]</button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger insert-var" data-target=".summernote-email" data-var="[DATE]">[DATE]</button>
                                                        </div>
                                                        <textarea class="form-control summernote-email" name="email_content" id="email_content"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-12 mt-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                                            <label class="form-check-label" for="is_active">{{ __('Active Template') }}</label>
                                        </div>
                                    </div>

                                    <div class="col-xl-12 mt-4">
                                        <button type="submit" class="site-btn primary-btn">{{ __('Create Template') }}</button>
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

            $('.summernote-main').summernote(summernoteOptions);
            
            var emailSummernoteOptions = Object.assign({}, summernoteOptions);
            emailSummernoteOptions.height = 200;
            $('.summernote-email').summernote(emailSummernoteOptions);

            $('.insert-var').on('click', function() {
                var variable = $(this).data('var');
                var target = $(this).data('target');
                $(target).summernote('editor.insertText', variable);
            });
        });
    </script>
@endsection
