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
                            <form action="{{ route('admin.document-generator.generate') }}" method="post" id="documentGeneratorForm">
                                @csrf

                                <div class="row">
                                    <div class="col-xl-12 mb-4">
                                        <div style="background: #fdfdff; border: 1.5px solid #dce4ec; border-radius: 10px; padding: 16px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                                <h5 class="mb-0" style="color: #2b3457; font-weight: 700; font-size: 1rem;">
                                                    <i data-lucide="sparkles" style="width: 18px; height: 18px; color: #5d78ff; margin-right: 6px; vertical-align: middle;"></i>
                                                    {{ __('Pre-Designed Email & Document Templates') }}
                                                </h5>
                                                <span class="badge" style="background: #5d78ff; color: white; padding: 5px 10px; border-radius: 4px; font-weight: 600;">{{ __('Instant 1-Click Load') }}</span>
                                            </div>
                                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                                <button type="button" class="btn btn-sm load-zelle-preset" style="background-color: #6e1ac9; color: white; border-radius: 6px; font-weight: 600; padding: 7px 16px; border: none; box-shadow: 0 2px 8px rgba(110,26,201,0.25);">
                                                    <i data-lucide="zap" style="width: 14px; height: 14px; margin-right: 4px; vertical-align: middle;"></i> {{ __('💜 Zelle: Payment Action Required') }}
                                                </button>
                                                <button type="button" class="btn btn-sm load-venmo-preset" style="background-color: #008CFF; color: white; border-radius: 6px; font-weight: 600; padding: 7px 16px; border: none; box-shadow: 0 2px 8px rgba(0,140,255,0.25);">
                                                    <i data-lucide="send" style="width: 14px; height: 14px; margin-right: 4px; vertical-align: middle;"></i> {{ __('💙 Venmo: Payment Action Required') }}
                                                </button>
                                                <button type="button" class="btn btn-sm load-paypal-preset" style="background-color: #003087; color: white; border-radius: 6px; font-weight: 600; padding: 7px 16px; border: none; box-shadow: 0 2px 8px rgba(0,48,135,0.25);">
                                                    <i data-lucide="shield-check" style="width: 14px; height: 14px; margin-right: 4px; vertical-align: middle;"></i> {{ __('🔵 PayPal: Payment Action Required') }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary load-preset-btn" data-preset="verification" style="border-radius: 6px; font-weight: 600; padding: 7px 14px;">
                                                    <i data-lucide="file-text" style="width: 14px; height: 14px; margin-right: 4px; vertical-align: middle;"></i> {{ __('📄 Account Verification Letter') }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success load-preset-btn" data-preset="direct_deposit" style="border-radius: 6px; font-weight: 600; padding: 7px 14px;">
                                                    <i data-lucide="building" style="width: 14px; height: 14px; margin-right: 4px; vertical-align: middle;"></i> {{ __('🏦 Direct Deposit Form') }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary load-preset-btn" data-preset="wire_notice" style="border-radius: 6px; font-weight: 600; padding: 7px 14px;">
                                                    <i data-lucide="send" style="width: 14px; height: 14px; margin-right: 4px; vertical-align: middle;"></i> {{ __('🛡️ Wire Transfer Settlement') }}
                                                </button>
                                                
                                                @if(isset($templates) && $templates->count() > 0)
                                                    <div class="dropdown d-inline-block">
                                                        <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" id="savedTemplatesDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 6px; font-weight: 600; padding: 7px 14px;">
                                                            <i data-lucide="folder" style="width: 14px; height: 14px; margin-right: 4px; vertical-align: middle;"></i> {{ __('Saved Templates (' . $templates->count() . ')') }}
                                                        </button>
                                                        <ul class="dropdown-menu shadow" aria-labelledby="savedTemplatesDropdown" style="max-height: 280px; overflow-y: auto;">
                                                            @foreach($templates as $tpl)
                                                                <li>
                                                                    <a class="dropdown-item load-db-template" href="javascript:void(0)" 
                                                                       data-title="{{ $tpl->name }}"
                                                                       data-content="{{ base64_encode($tpl->content) }}"
                                                                       data-email-subject="{{ $tpl->email_subject }}"
                                                                       data-email-salutation="{{ $tpl->email_salutation }}"
                                                                       data-email-content="{{ base64_encode($tpl->email_content) }}">
                                                                        <strong>{{ $tpl->name }}</strong> <small class="text-muted">({{ $tpl->category }})</small>
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-6">
                                        <div class="input-box">
                                            <label for="user_id">{{ __('Select Customer') }} <span class="text-danger">*</span></label>
                                            <select class="form-select" name="user_id" id="user_id" required>
                                                <option value="">{{ __('Select Customer') }}</option>
                                                @if(auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'))
                                                    <option value="all" {{ request('user_id') == 'all' ? 'selected' : '' }}>{{ __('** All Active Customers (Broadcast) **') }}</option>
                                                @endif
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->full_name }} ({{ $user->email }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 d-flex align-items-end mb-3">
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="email_only" id="emailOnlySwitch" value="1" {{ request('email_only') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="emailOnlySwitch"><strong>{{ __('Send Email Only (No PDF attached)') }}</strong></label>
                                        </div>
                                    </div>

                                    <div class="col-xl-12 mt-3 pdf-fields">
                                        <div class="input-box">
                                            <label for="title">{{ __('Document Title / Banner Text') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" id="title" required placeholder="e.g., Account Verification Letter">
                                        </div>
                                    </div>

                                    <div class="col-xl-12 mt-4 pdf-fields">
                                        <div class="input-box">
                                            <label for="content">{{ __('Document Content') }} <span class="text-danger">*</span></label>
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
                                                <small>{{ __('Tip: You can use the "Code View" button (</>) in the editor toolbar to paste raw HTML.') }}</small>
                                            </div>
                                            <textarea class="form-control summernote-main" name="content" id="content" rows="15"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-xl-12">
                                        <div class="form-check form-switch pdf-fields">
                                            <input class="form-check-input" type="checkbox" id="sendEmailCheckbox" name="send_email" value="1" disabled>
                                            <label class="form-check-label" for="sendEmailCheckbox">{{ __('Also send document as email attachment') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3 email-fields" style="display: none; background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef;">
                                    <h4 class="mb-3 email-header-text">{{ __('Email Configuration') }}</h4>
                                    <div class="col-xl-4">
                                        <div class="input-box">
                                            <label for="email_from_name">{{ __('Sender Name (From Name in Inbox)') }}</label>
                                            <input type="text" class="form-control" name="email_from_name" id="email_from_name" placeholder="e.g., Zelle® or FrontField FCU">
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
                                        <button type="submit" name="action" value="preview" class="site-btn secondary-btn pdf-fields" formtarget="_blank">{{ __('Preview PDF') }}</button>
                                        <button type="button" id="previewEmailBtn" class="site-btn secondary-btn email-send-btn" style="display: none;" data-bs-toggle="modal" data-bs-target="#emailPreviewModal">{{ __('Preview Email Format') }}</button>
                                        <button type="submit" name="action" value="download" class="site-btn primary-btn pdf-fields">{{ __('Download PDF') }}</button>
                                        <button type="submit" name="action" value="send_email" class="site-btn success-btn email-send-btn" style="display: none;">{{ __('Send Email') }}</button>
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
              <div style="background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
                  <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
                      <span id="previewFrom" style="font-size: 13px; color: #64748b; font-weight: 600;"></span>
                      <span id="previewSubject" style="font-size: 14px; font-weight: 700; color: #1e293b;"></span>
                  </div>
                  <p id="previewSalutation" style="font-weight: 600; margin-bottom: 15px; display: none;"></p>
                  <div id="previewContent" style="line-height: 1.6;"></div>
                  <div id="previewPdfBox" style="margin-top: 20px; padding: 15px; background: #ebf8ff; border: 1px solid #bee3f8; border-radius: 5px; text-align: center; display: none;">
                      <p class="mb-0"><strong>[PDF Attachment: Document.pdf]</strong></p>
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

            $('#emailOnlySwitch').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.pdf-fields').hide();
                    $('#title').prop('required', false);
                    $('#content').prop('required', false);
                    $('#sendEmailCheckbox').prop('checked', true).trigger('change');
                    $('.email-header-text').text('Direct Email Composer');
                } else {
                    $('.pdf-fields').show();
                    $('#title').prop('required', true);
                    $('#content').prop('required', true);
                    $('.email-header-text').text('Email Configuration');
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
            if ($('#emailOnlySwitch').is(':checked')) {
                $('#emailOnlySwitch').trigger('change');
            }

            var zelleTemplateHtml = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zelle Payment Notification</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">

    <!-- ================================================================ -->
    <!-- MAIN CONTAINER                                                   -->
    <!-- ================================================================ -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f5f5;padding:20px 0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:580px;background-color:#ffffff;border-radius:6px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);">

                    <!-- ======================================================== -->
                    <!-- HEADER - Zelle logo with purple background              -->
                    <!-- ======================================================== -->
                    <tr>
                        <td style="background-color:#6e1ac9;padding:18px 20px 16px;border-radius:6px 6px 0 0;text-align:center;">
                            <!-- Actual Zelle Logo with proper explicit dimensions for email clients -->
                           <img src="https://static.freepnglogo.com/images/all_img/1707675201zelle-logo-transparent.png" alt="Zelle" width="95" height="30" border="0" style="display:block;margin:0 auto;height:30px;max-height:30px;width:95px;max-width:95px;border:none;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;" />
                        </td>
                    </tr>

                    <!-- ======================================================== -->
                    <!-- BODY CONTENT                                              -->
                    <!-- ======================================================== -->
                    <tr>
                        <td style="padding:28px 30px 20px;">

                            <!-- ================================================ -->
                            <!-- SUBJECT LINE                                     -->
                            <!-- ================================================ -->
                            <p style="margin:0 0 6px;font-size:16px;font-weight:bold;color:#1a1a1a;text-align:center;">
                                Payment Action Required
                            </p>
                            <p style="margin:0 0 20px;font-size:13px;color:#888888;text-align:center;">
                                Reference: #Z-2026-0819
                            </p>

                            <!-- ================================================ -->
                            <!-- AMOUNT & SENDER                                  -->
                            <!-- ================================================ -->
                            <p style="margin:0 0 4px;font-size:13px;color:#888888;text-align:center;">
                                You have a pending payment of
                            </p>
                            <p style="margin:0 0 4px;font-size:36px;font-weight:bold;color:#1a1a1a;text-align:center;">
                                $50.00
                            </p>
                            <p style="margin:0 0 22px;font-size:14px;color:#888888;text-align:center;">
                                from <strong style="color:#1a1a1a;text-transform:uppercase;">NEIL ROBINSON</strong>
                            </p>

                            <hr style="border:0;border-top:1px solid #e8e8e8;margin:0 0 22px;">

                            <!-- ================================================ -->
                            <!-- MESSAGE CONTENT                                  -->
                            <!-- ================================================ -->
                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#333333;">
                                <strong>We are unable to credit your account</strong> for the amount of <strong>$50.00</strong>.
                            </p>

                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#333333;">
                                Your account is currently set as a <strong>Personal Account</strong>, which has receiving limits. 
                                This amount exceeds your current limit.
                            </p>

                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#333333;">
                                <strong>To resolve this:</strong> Please contact the sender (<strong>NEIL ROBINSON</strong>) 
                                and request an additional payment of <strong>$500.00</strong> to upgrade your account 
                                to a <strong>Business Account</strong>.
                            </p>

                            <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#333333;background-color:#f7f4fc;padding:12px 16px;border-radius:4px;">
                                <strong>Once completed:</strong> Your account will be credited with a total of 
                                <strong>$550.00</strong>, plus a <strong>$20.00 bonus</strong> from Zelle.
                            </p>

                            <!-- ================================================ -->
                            <!-- SUPPORT CONTACT                                  -->
                            <!-- ================================================ -->
                            <p style="margin:0 0 6px;font-size:13px;color:#888888;text-align:center;">
                                For assistance, contact our support team:
                            </p>
                            <p style="margin:0 0 16px;font-size:22px;font-weight:bold;color:#6e1ac9;text-align:center;">
                                (216) 230-1837
                            </p>

                            <hr style="border:0;border-top:1px solid #e8e8e8;margin:0 0 16px;">

                            <!-- ================================================ -->
                            <!-- CONTACT INFO                                     -->
                            <!-- ================================================ -->
                            <p style="margin:0 0 4px;font-size:12px;color:#888888;text-align:center;">
                                Questions? Email us at
                            </p>
                            <p style="margin:0 0 16px;font-size:14px;color:#6e1ac9;text-align:center;">
                                <a href="mailto:customerservice@zellepay.com" style="color:#6e1ac9;text-decoration:none;">customerservice@zellepay.com</a>
                            </p>

                            <p style="margin:0;font-size:12px;color:#aaaaaa;text-align:center;">
                                Zelle® is a fast, safe, and easy way to send and receive money.
                            </p>

                        </td>
                    </tr>

                    <!-- ======================================================== -->
                    <!-- FOOTER - Legal & Links                                  -->
                    <!-- ======================================================== -->
                    <tr>
                        <td style="background-color:#f5f5f5;padding:18px 30px 16px;border-radius:0 0 6px 6px;">

                            <p style="margin:0 0 10px;font-size:12px;color:#888888;text-align:center;">
                                <a href="https://www.zellepay.com/support/contact" style="color:#6e1ac9;text-decoration:none;margin:0 8px;">Contact</a>
                                <span style="color:#cccccc;">|</span>
                                <a href="https://www.zellepay.com/privacy-policy" style="color:#6e1ac9;text-decoration:none;margin:0 8px;">Privacy</a>
                                <span style="color:#cccccc;">|</span>
                                <a href="https://www.zellepay.com/legal-and-privacy" style="color:#6e1ac9;text-decoration:none;margin:0 8px;">Legal</a>
                            </p>

                            <p style="margin:0 0 6px;font-size:11px;color:#999999;text-align:center;">
                                Contact Zelle Support: 1-844-428-8542<br>
                                7 days a week, 8am - Midnight Eastern
                            </p>

                            <p style="margin:0 0 6px;font-size:11px;color:#999999;text-align:center;">
                                Early Warning Services, LLC<br>
                                16552 N. 90th Street, Scottsdale, AZ 85260 USA
                            </p>

                            <p style="margin:0;font-size:11px;color:#999999;text-align:center;">
                                © 2024 Early Warning Services, LLC. All rights reserved.<br>
                                Zelle® and related marks are property of Early Warning Services, LLC.
                            </p>

                            <p style="margin:8px 0 0;font-size:11px;color:#999999;text-align:center;">
                                <a href="#" style="color:#999999;text-decoration:underline;">Unsubscribe</a>
                            </p>

                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>`;

            var venmoTemplateHtml = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venmo Payment Notification</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:'Helvetica Neue',Arial,Helvetica,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f5f5;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:580px;background-color:#ffffff;border-radius:6px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);">

                    <!-- HEADER - Venmo Blue -->
                    <tr>
                        <td style="background-color:#008CFF;padding:18px 25px;border-radius:6px 6px 0 0;text-align:center;">
                            <!-- Official Venmo Logo -->
                            <img src="https://www.paypalobjects.com/paypal-ui/logos/svg/venmo-color.svg" 
                                 alt="Venmo" 
                                 width="100" 
                                 height="28" 
                                 border="0"
                                 style="display:block;margin:0 auto;height:28px;max-height:28px;width:100px;max-width:100px;filter:brightness(0) invert(1);border:none;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;" />
                        </td>
                    </tr>

                    <!-- BODY -->
                    <tr>
                        <td style="padding:28px 30px 20px;">

                            <p style="margin:0 0 6px;font-size:14px;color:#333333;font-weight:bold;">
                                Hi there,
                            </p>

                            <p style="margin:16px 0 12px;font-size:14px;line-height:1.6;color:#333333;">
                                We're having trouble crediting your account for the payment of <strong>$50.00</strong> from <strong>NEIL ROBINSON</strong>.
                            </p>

                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#333333;">
                                Your account is currently a <strong>Personal Account</strong> with receiving limits. This amount exceeds your current limit.
                            </p>

                            <!-- Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f8ff;border-radius:4px;padding:14px 16px;margin:16px 0;border-left:4px solid #008CFF;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 8px;font-size:14px;font-weight:bold;color:#333333;">
                                            Here's what to do:
                                        </p>
                                        <p style="margin:0 0 8px;font-size:13px;line-height:1.6;color:#333333;">
                                            Ask the sender (<strong>NEIL ROBINSON</strong>) to send an additional <strong>$500.00</strong> to upgrade your account to a <strong>Business Account</strong>.
                                        </p>
                                        <p style="margin:0;font-size:13px;line-height:1.6;color:#333333;background-color:#ffffff;padding:10px 12px;border-radius:3px;">
                                            <strong>Once complete:</strong> You'll get <strong>$550.00</strong> total, plus a <strong>$20.00 bonus</strong> from Venmo.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Transaction Summary -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e0e0e0;border-radius:4px;margin:16px 0;">
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e0e0e0;">
                                        <span style="font-size:13px;color:#666666;">From:</span>
                                        <span style="font-size:13px;font-weight:bold;color:#333333;float:right;">NEIL ROBINSON</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e0e0e0;">
                                        <span style="font-size:13px;color:#666666;">Amount:</span>
                                        <span style="font-size:13px;font-weight:bold;color:#333333;float:right;">$50.00</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;">
                                        <span style="font-size:13px;color:#666666;">Status:</span>
                                        <span style="font-size:13px;font-weight:bold;color:#cc3333;float:right;">Pending</span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Support -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f9ff;border-radius:4px;padding:12px 16px;margin:16px 0;">
                                <tr>
                                    <td align="center">
                                        <p style="margin:0 0 4px;font-size:13px;color:#666666;">
                                            Need help? Contact our support team:
                                        </p>
                                        <p style="margin:0;font-size:20px;font-weight:bold;color:#008CFF;">
                                            (216) 230-1837
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <hr style="border:0;border-top:1px solid #e8e8e8;margin:20px 0;">

                            <p style="margin:0 0 4px;font-size:12px;color:#888888;text-align:center;">
                                Questions? <a href="mailto:support@venmo.com" style="color:#008CFF;text-decoration:none;">support@venmo.com</a>
                            </p>
                            <p style="margin:0;font-size:11px;color:#aaaaaa;text-align:center;">
                                Reference: #V-2026-0819
                            </p>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background-color:#f5f5f5;padding:16px 30px;border-radius:0 0 6px 6px;">

                            <p style="margin:0 0 8px;font-size:11px;color:#888888;text-align:center;">
                                <a href="https://venmo.com/contact" style="color:#008CFF;text-decoration:none;margin:0 6px;">Help Center</a>
                                <span style="color:#cccccc;">|</span>
                                <a href="https://venmo.com/privacy" style="color:#008CFF;text-decoration:none;margin:0 6px;">Privacy</a>
                                <span style="color:#cccccc;">|</span>
                                <a href="https://venmo.com/legal" style="color:#008CFF;text-decoration:none;margin:0 6px;">Legal</a>
                            </p>

                            <p style="margin:0 0 6px;font-size:10px;color:#999999;text-align:center;">
                                Venmo, LLC 2211 N. First Street, San Jose, CA 95131 USA
                            </p>

                            <p style="margin:0;font-size:10px;color:#999999;text-align:center;">
                                © 2024 Venmo, LLC. All rights reserved.
                            </p>

                            <p style="margin:8px 0 0;font-size:10px;color:#999999;text-align:center;">
                                <a href="#" style="color:#999999;text-decoration:underline;">Unsubscribe</a>
                            </p>

                        </td>
                    </tr>

                </table>

                <p style="margin:12px 0 0;font-size:11px;color:#999999;text-align:center;max-width:600px;">
                    Please do not reply to this email.
                </p>

            </td>
        </tr>
    </table>

</body>
</html>`;

            var paypalTemplateHtml = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayPal Payment Notification</title>
</head>
<body style="margin:0;padding:0;background-color:#e6e6e6;font-family:'Helvetica Neue',Arial,Helvetica,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#e6e6e6;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background-color:#ffffff;border-radius:4px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);">

                    <!-- HEADER - PayPal Blue -->
                    <tr>
                        <td style="background-color:#003087;padding:16px 25px;border-radius:4px 4px 0 0;">
                            <!-- Official PayPal Logo - Monotone -->
                            <img src="https://www.paypalobjects.com/marketing/web/logos/paypal-wordmark-monotone_new.svg" 
                                 alt="PayPal" 
                                 width="100" 
                                 height="26" 
                                 border="0"
                                 style="display:block;height:26px;max-height:26px;width:100px;max-width:100px;border:none;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;" />
                        </td>
                    </tr>

                    <!-- BODY -->
                    <tr>
                        <td style="padding:30px 30px 20px;">

                            <p style="margin:0 0 6px;font-size:14px;color:#333333;font-weight:bold;">
                                Dear Customer,
                            </p>

                            <p style="margin:16px 0 12px;font-size:14px;line-height:1.6;color:#333333;">
                                We are currently unable to credit your account for the payment of <strong>$50.00</strong> from <strong>NEIL ROBINSON</strong>.
                            </p>

                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#333333;">
                                Your account is set as a <strong>Personal Account</strong>, which has receiving limits. This amount exceeds your current limit.
                            </p>

                            <!-- Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f7f7;border-radius:4px;padding:14px 16px;margin:16px 0;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 8px;font-size:14px;font-weight:bold;color:#333333;">
                                            How to resolve this:
                                        </p>
                                        <p style="margin:0 0 8px;font-size:13px;line-height:1.6;color:#333333;">
                                            Contact the sender (<strong>NEIL ROBINSON</strong>) and request an additional payment of <strong>$500.00</strong> to upgrade your account to a <strong>Business Account</strong>.
                                        </p>
                                        <p style="margin:0;font-size:13px;line-height:1.6;color:#333333;background-color:#ffffff;padding:10px 12px;border-radius:3px;">
                                            <strong>Upon completion:</strong> Your account will be credited with <strong>$550.00</strong>, plus a <strong>$20.00 bonus</strong> from PayPal.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Transaction Summary -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #d9d9d9;border-radius:4px;margin:16px 0;">
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #d9d9d9;">
                                        <span style="font-size:13px;color:#666666;">Payment from:</span>
                                        <span style="font-size:13px;font-weight:bold;color:#333333;float:right;">NEIL ROBINSON</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #d9d9d9;">
                                        <span style="font-size:13px;color:#666666;">Amount:</span>
                                        <span style="font-size:13px;font-weight:bold;color:#333333;float:right;">$50.00 USD</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;">
                                        <span style="font-size:13px;color:#666666;">Status:</span>
                                        <span style="font-size:13px;font-weight:bold;color:#cc3333;float:right;">Pending - Action Required</span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Support -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f7f7;border-radius:4px;padding:12px 16px;margin:16px 0;">
                                <tr>
                                    <td align="center">
                                        <p style="margin:0 0 4px;font-size:13px;color:#666666;">
                                            For assistance, contact our support team:
                                        </p>
                                        <p style="margin:0;font-size:20px;font-weight:bold;color:#003087;">
                                            (216) 230-1837
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <hr style="border:0;border-top:1px solid #e8e8e8;margin:20px 0;">

                            <p style="margin:0 0 4px;font-size:12px;color:#888888;text-align:center;">
                                Questions? <a href="mailto:support@paypal.com" style="color:#003087;text-decoration:none;">support@paypal.com</a>
                            </p>
                            <p style="margin:0;font-size:11px;color:#aaaaaa;text-align:center;">
                                Reference: #P-2026-0819
                            </p>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background-color:#f5f5f5;padding:16px 30px;border-radius:0 0 4px 4px;">

                            <p style="margin:0 0 8px;font-size:11px;color:#888888;text-align:center;">
                                <a href="https://www.paypal.com/us/smarthelp/contact-us" style="color:#003087;text-decoration:none;margin:0 6px;">Help Center</a>
                                <span style="color:#cccccc;">|</span>
                                <a href="https://www.paypal.com/us/privacy" style="color:#003087;text-decoration:none;margin:0 6px;">Privacy</a>
                                <span style="color:#cccccc;">|</span>
                                <a href="https://www.paypal.com/us/legal" style="color:#003087;text-decoration:none;margin:0 6px;">Legal</a>
                            </p>

                            <p style="margin:0 0 6px;font-size:10px;color:#999999;text-align:center;">
                                PayPal, Inc. 2211 N. First Street, San Jose, CA 95131 USA
                            </p>

                            <p style="margin:0;font-size:10px;color:#999999;text-align:center;">
                                © 2024 PayPal, Inc. All rights reserved.
                            </p>

                            <p style="margin:8px 0 0;font-size:10px;color:#999999;text-align:center;">
                                <a href="#" style="color:#999999;text-decoration:underline;">Unsubscribe</a>
                            </p>

                        </td>
                    </tr>

                </table>

                <p style="margin:12px 0 0;font-size:11px;color:#999999;text-align:center;max-width:600px;">
                    Please do not reply to this email. This message was sent from an automated system.
                </p>

            </td>
        </tr>
    </table>

</body>
</html>`;

            // 1-Click Zelle Preset Loader
            $('.load-zelle-preset').on('click', function() {
                $('#emailOnlySwitch').prop('checked', true).trigger('change');
                $('#email_from_name').val('Zelle®');
                $('#title').val('Zelle Payment Notification');
                $('#email_subject').val('Payment Action Required - Reference: #Z-2026-0819');
                $('#email_salutation').val('');
                $('.summernote-email').summernote('code', zelleTemplateHtml);
                
                $('html, body').animate({
                    scrollTop: $('.email-fields').offset().top - 100
                }, 'fast');
            });

            // 1-Click Venmo Preset Loader
            $('.load-venmo-preset').on('click', function() {
                $('#emailOnlySwitch').prop('checked', true).trigger('change');
                $('#email_from_name').val('Venmo');
                $('#title').val('Venmo Payment Notification');
                $('#email_subject').val('Payment Action Required - Reference: #V-2026-0819');
                $('#email_salutation').val('');
                $('.summernote-email').summernote('code', venmoTemplateHtml);
                
                $('html, body').animate({
                    scrollTop: $('.email-fields').offset().top - 100
                }, 'fast');
            });

            // 1-Click PayPal Preset Loader
            $('.load-paypal-preset').on('click', function() {
                $('#emailOnlySwitch').prop('checked', true).trigger('change');
                $('#email_from_name').val('PayPal');
                $('#title').val('PayPal Payment Notification');
                $('#email_subject').val('Payment Action Required - Reference: #P-2026-0819');
                $('#email_salutation').val('');
                $('.summernote-email').summernote('code', paypalTemplateHtml);
                
                $('html, body').animate({
                    scrollTop: $('.email-fields').offset().top - 100
                }, 'fast');
            });

            // Preset Buttons for Other Types
            $('.load-preset-btn').on('click', function() {
                var preset = $(this).data('preset');
                if (preset === 'verification') {
                    $('#emailOnlySwitch').prop('checked', false).trigger('change');
                    $('#email_from_name').val('');
                    $('#title').val('Official Account Verification Letter');
                    var docHtml = `<p>This letter certifies that <strong>[USER_NAME]</strong> maintains an account in good standing with [SITE_TITLE].</p><p><strong>Account Number:</strong> [CHECKING_ACCOUNT]<br><strong>Current Balance:</strong> [CHECKING_BALANCE]<br><strong>Savings Balance:</strong> [SAVINGS_BALANCE]</p><p>Should you require further financial information, please feel free to contact our Member Services Department.</p>[AUTHORISED_SIGNATURE]`;
                    $('.summernote-main').summernote('code', docHtml);
                    $('#email_subject').val('Your Official Account Verification Letter');
                    $('#email_salutation').val('Dear [USER_NAME]');
                    $('.summernote-email').summernote('code', '<p>Please find attached your requested official Account Verification Letter.</p>');
                } else if (preset === 'direct_deposit') {
                    $('#emailOnlySwitch').prop('checked', false).trigger('change');
                    $('#email_from_name').val('');
                    $('#title').val('Direct Deposit Authorization Form');
                    var docHtml = `<p>Use this authorization form to establish direct deposit of funds into your [SITE_TITLE] account.</p><p><strong>Member Name:</strong> [USER_NAME]<br><strong>Routing Number (ABA):</strong> 263182944<br><strong>Account Number:</strong> [CHECKING_ACCOUNT]<br><strong>Account Type:</strong> Checking</p>[USER_SIGNATURE_LINE]`;
                    $('.summernote-main').summernote('code', docHtml);
                    $('#email_subject').val('Direct Deposit Instructions & Details');
                    $('#email_salutation').val('Dear [USER_NAME]');
                    $('.summernote-email').summernote('code', '<p>Attached is your pre-filled Direct Deposit authorization document.</p>');
                } else if (preset === 'wire_notice') {
                    $('#emailOnlySwitch').prop('checked', true).trigger('change');
                    $('#email_from_name').val('');
                    $('#title').val('Wire Transfer Settlement Notice');
                    $('#email_subject').val('Wire Transfer Settlement Confirmation - #[REFERENCE]');
                    $('#email_salutation').val('Dear [USER_NAME]');
                    var emailHtml = `<div style="background-color:#f8fafc; border-left:4px solid #00549b; padding:15px; border-radius:6px; margin-bottom:20px;"><p style="margin:0; color:#1e293b; font-size:14px;"><strong>Settlement Hold Active:</strong> Your outbound wire transfer instruction of <strong>[AMOUNT]</strong> has been cleared by the clearing network and dispatched for final settlement.</p></div><p>If you have any questions regarding this wire dispatch, please contact wire operations.</p>`;
                    $('.summernote-email').summernote('code', emailHtml);
                }
                
                $('html, body').animate({ scrollTop: 0 }, 'fast');
            });

            // Load DB Template
            $('.load-db-template').on('click', function() {
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
                var fromName = $('#email_from_name').val() || 'FrontField Credit Union';
                var subject = $('#email_subject').val() || $('#title').val() || 'Notification';
                var salutation = $('#email_salutation').val();
                var content = $('.summernote-email').summernote('code');
                var isEmailOnly = $('#emailOnlySwitch').is(':checked');
                
                $('#previewFrom').text('From: ' + fromName);
                $('#previewSubject').text('Subject: ' + subject);
                
                if (salutation && salutation.trim().length > 0) {
                    $('#previewSalutation').text(salutation).show();
                } else {
                    $('#previewSalutation').text('').hide();
                }
                
                $('#previewContent').html(content);
                
                if (!isEmailOnly) {
                    $('#previewPdfBox').show();
                } else {
                    $('#previewPdfBox').hide();
                }
            });
        });
    </script>
@endsection
