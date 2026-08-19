@extends('backend.layouts.app')
@section('title')
    {{ __('Create Email Template') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-md-12">
                    <div class="site-card">
                        <div class="site-card-header">
                            <h3 class="title">{{ __('Create Email Template') }}</h3>
                            <div class="card-header-links">
                                <a href="{{ route('admin.email-template') }}" class="card-header-link">{{ __('Back') }}</a>
                            </div>
                        </div>
                        <div class="site-card-body">
                            <form action="{{ route('admin.email-template-store') }}" method="post" enctype="multipart/form-data">
                                @csrf

                                <div class="site-input-groups row">
                                    <label for="" class="col-sm-3 col-label">{{ __('Template Name') }} <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" name="name" class="box-input" value="{{ old('name') }}" placeholder="e.g., Zelle Payment Notification" required/>
                                    </div>
                                </div>

                                <div class="site-input-groups row">
                                    <label for="" class="col-sm-3 col-label">{{ __('Template Code') }} <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" name="code" class="box-input" value="{{ old('code') }}" placeholder="e.g., zelle_payment_notification" required/>
                                    </div>
                                </div>

                                <div class="site-input-groups row">
                                    <label for="" class="col-sm-3 col-label">{{ __('Email For') }}</label>
                                    <div class="col-sm-9">
                                        <select name="for" class="form-select">
                                            <option value="User" {{ old('for') == 'User' ? 'selected' : '' }}>{{ __('User') }}</option>
                                            <option value="Admin" {{ old('for') == 'Admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="site-input-groups row">
                                    <label for="" class="col-sm-3 col-label">{{ __('Email Subject') }} <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" name="subject" class="box-input" value="{{ old('subject') }}" placeholder="e.g., Payment Action Required - Reference: #[[reference]]" required/>
                                    </div>
                                </div>

                                <div class="site-input-groups row">
                                    <label for="" class="col-sm-3 col-label">{{ __('Title / Heading') }}</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="title" class="box-input" value="{{ old('title') }}" placeholder="e.g., Zelle Payment Notification"/>
                                    </div>
                                </div>

                                <div class="site-input-groups row">
                                    <label for="" class="col-sm-3 col-label">{{ __('Salutation') }}</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="salutation" class="box-input" value="{{ old('salutation', 'Dear [[full_name]]') }}"/>
                                    </div>
                                </div>

                                <div class="site-input-groups row">
                                    <label for="" class="col-sm-3 col-label">{{ __('Message Body') }} <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <textarea name="message_body" class="form-textarea" cols="30" rows="12" placeholder="{{ __('Write text or paste raw HTML email template code here...') }}" required>{{ old('message_body') }}</textarea>
                                    </div>
                                </div>

                                <div class="site-input-groups row">
                                    <label for="" class="col-sm-3 col-label">{{ __('Button (Optional)') }}</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="button_level" class="box-input" value="{{ old('button_level') }}" placeholder="{{ __('Button Label') }}"/>
                                    </div>
                                    <div class="col-sm-5">
                                        <input type="text" name="button_link" class="box-input" value="{{ old('button_link') }}" placeholder="{{ __('Button Link URL') }}"/>
                                    </div>
                                </div>

                                <div class="site-input-groups row">
                                    <label for="" class="col-sm-3 col-label">{{ __('Short Codes (Comma Separated)') }}</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="short_codes" class="box-input" value="{{ old('short_codes', '[[full_name]], [[amount]], [[sender_name]], [[reference]], [[support_phone]], [[support_email]]') }}"/>
                                    </div>
                                </div>

                                <div class="site-input-groups row">
                                    <label for="" class="col-sm-3 col-label">{{ __('Status') }}</label>
                                    <div class="col-sm-9">
                                        <div class="switch-field mb-0">
                                            <input type="radio" id="status_active" name="status" value="1" checked/>
                                            <label for="status_active">{{ __('Active') }}</label>
                                            <input type="radio" id="status_inactive" name="status" value="0"/>
                                            <label for="status_inactive">{{ __('Inactive') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-xl-12">
                                        <button type="submit" class="site-btn primary-btn w-100">{{ __('Save Email Template') }}</button>
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
