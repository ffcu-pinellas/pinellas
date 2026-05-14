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
                                                </div>
                                                <small>{{ __('Tip: You can use the "Code View" button (</>) in the editor toolbar to paste raw HTML.') }}</small>
                                            </div>
                                            <textarea class="form-control summernote-main" name="content" id="content" rows="15" required></textarea>
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
                                    <div class="col-xl-6 mt-3">
                                        <div class="input-box">
                                            <label for="email_cc">{{ __('CC (Optional, comma separated)') }}</label>
                                            <input type="text" class="form-control" name="email_cc" id="email_cc" placeholder="e.g., manager@example.com">
                                        </div>
                                    </div>
                                    <div class="col-xl-6 mt-3">
                                        <div class="input-box">
                                            <label for="email_bcc">{{ __('BCC (Optional, comma separated)') }}</label>
                                            <input type="text" class="form-control" name="email_bcc" id="email_bcc" placeholder="e.g., archive@example.com">
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mt-3">
                                        <div class="input-box">
                                            <label for="email_content">{{ __('Email Message Content') }}</label>
                                            <div class="mt-2 mb-2 d-flex gap-2 flex-wrap">
                                                <button type="button" class="btn btn-sm btn-outline-primary insert-var" data-target=".summernote-email" data-var="[USER_NAME]">[USER_NAME]</button>
                                                <button type="button" class="btn btn-sm btn-outline-success insert-var" data-target=".summernote-email" data-var="[CHECKING_BALANCE]">[CHECKING_BALANCE]</button>
                                                <button type="button" class="btn btn-sm btn-outline-info insert-var" data-target=".summernote-email" data-var="[SAVINGS_BALANCE]">[SAVINGS_BALANCE]</button>
                                                <button type="button" class="btn btn-sm btn-outline-warning insert-var" data-target=".summernote-email" data-var="[IRA_BALANCE]">[IRA_BALANCE]</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary insert-var" data-target=".summernote-email" data-var="[LOAN_BALANCE]">[LOAN_BALANCE]</button>
                                            </div>
                                            <textarea class="form-control summernote-email" name="email_content" id="email_content" rows="5"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-xl-12 d-flex gap-2 flex-wrap">
                                        <button type="submit" name="action" value="preview" class="site-btn secondary-btn" formtarget="_blank">{{ __('Preview PDF') }}</button>
                                        <button type="button" id="previewEmailBtn" class="site-btn secondary-btn email-send-btn" style="display: none;" data-bs-toggle="modal" data-bs-target="#emailPreviewModal">{{ __('Preview Email Format') }}</button>
                                        <button type="submit" name="action" value="download" class="site-btn primary-btn">{{ __('Download PDF') }}</button>
                                        <button type="submit" name="action" value="send_email" class="site-btn success-btn email-send-btn" style="display: none;">{{ __('Send via Email') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- History Section -->
                    <div class="site-card mt-4">
                        <div class="site-card-header">
                            <h3 class="title">{{ __('Document & Email History (Templates)') }}</h3>
                        </div>
                        <div class="site-card-body table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Title</th>
                                        <th>Sent To</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($histories as $history)
                                        <tr>
                                            <td>{{ $history->created_at->format('M d, Y h:i A') }}</td>
                                            <td>{{ $history->title }}</td>
                                            <td>{{ $history->user ? $history->user->full_name : 'N/A' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary load-template-btn" 
                                                    data-title="{{ $history->title }}" 
                                                    data-content="{{ base64_encode($history->content) }}"
                                                    data-email-subject="{{ $history->email_subject }}"
                                                    data-email-salutation="{{ $history->email_salutation }}"
                                                    data-email-content="{{ base64_encode($history->email_content) }}">
                                                    Load Template
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-3">
                                {{ $histories->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Preview Modal -->
    <div class="modal fade" id="emailPreviewModal" tabindex="-1" aria-labelledby="emailPreviewModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="emailPreviewModalLabel">Email Preview (Approximate)</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" style="background: #f7fafc; padding: 20px;">
              <div style="background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e2e8f0;">
                  <h4 id="previewSubject" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px;"></h4>
                  <p id="previewSalutation" style="font-weight: bold;"></p>
                  <div id="previewContent" style="margin-bottom: 20px; line-height: 1.6;"></div>
                  <div style="padding: 15px; background: #ebf8ff; border: 1px solid #bee3f8; border-radius: 5px; text-align: center;">
                      <p><strong>[PDF Attachment: Document.pdf]</strong></p>
                      <button class="btn btn-primary btn-sm" disabled>Go to Dashboard</button>
                  </div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

            $('.summernote-main').summernote(summernoteOptions);
            
            var emailSummernoteOptions = Object.assign({}, summernoteOptions);
            emailSummernoteOptions.height = 200;
            $('.summernote-email').summernote(emailSummernoteOptions);

            $('.insert-var').on('click', function() {
                var variable = $(this).data('var');
                var target = $(this).data('target');
                $(target).summernote('editor.insertText', variable);
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

            $('.load-template-btn').on('click', function() {
                var title = $(this).data('title');
                var content = atob($(this).data('content'));
                var emailSubject = $(this).data('email-subject');
                var emailSalutation = $(this).data('email-salutation');
                var emailContent = atob($(this).data('email-content'));

                $('#title').val(title);
                $('.summernote-main').summernote('code', content);
                
                if (emailSubject || emailSalutation || emailContent) {
                    $('#sendEmailCheckbox').prop('checked', true).trigger('change');
                    $('#email_subject').val(emailSubject);
                    $('#email_salutation').val(emailSalutation);
                    $('.summernote-email').summernote('code', emailContent);
                }
                
                $('html, body').animate({ scrollTop: 0 }, 'fast');
            });

            $('#previewEmailBtn').on('click', function() {
                var subject = $('#email_subject').val() || $('#title').val();
                var salutation = $('#email_salutation').val() || 'Dear [USER_NAME]';
                var content = $('.summernote-email').summernote('code');
                
                $('#previewSubject').text('Subject: ' + subject);
                $('#previewSalutation').text(salutation);
                $('#previewContent').html(content);
            });
        });
    </script>
@endsection
