@extends('backend.layouts.app')
@section('title')
    {{ __('Wire Transfer Settings') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-xl-12">
                        <div class="title-content">
                            <h2 class="title">{{ __('Wire Transfer Settings') }}</h2>
                            <a href="{{ route('admin.fund.transfer.wire') }}" class="title-btn"><i
                                    data-lucide="corner-down-left"></i>{{ __('All Wire Transfers') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xl-9">
                    <div class="site-card">
                        <div class="site-card-body">
                            <form action="{{ route('admin.wire.transfer.post') }}" class="row g-4" method="post">
                                @csrf

                                {{-- Global Status Switch --}}
                                <div class="col-12">
                                    <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-bold mb-1">{{ __('Global Wire Transfer System Status') }}</h6>
                                            <p class="text-muted small mb-0">{{ __('Enable or disable the wire transfer feature system-wide for all members.') }}</p>
                                        </div>
                                        <div class="switch-field" style="margin: 0;">
                                            <input type="radio" id="status_active" name="status" value="1" @checked(($wireTransfer?->status ?? 1) == 1) />
                                            <label for="status_active" style="padding: 6px 18px; font-weight: 600;">{{ __('Active') }}</label>
                                            <input type="radio" id="status_disabled" name="status" value="0" @checked(($wireTransfer?->status ?? 1) == 0) />
                                            <label for="status_disabled" style="padding: 6px 18px; font-weight: 600;">{{ __('Disabled') }}</label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Fee Configurations --}}
                                <div class="col-xl-6">
                                    <div class="site-input-groups position-relative">
                                        <label class="box-input-label" for="">{{ __('Domestic Wire Fee / Charge:') }}</label>
                                        <div class="position-relative">
                                            <input type="number" step="0.01" class="box-input"
                                                   oninput="this.value = validateDouble(this.value)" name="charge" value="{{ $wireTransfer?->charge ?? 25.00 }}"/>
                                            <div class="prcntcurr">
                                                <select name="charge_type" class="form-select">
                                                    <option value="fixed" {{ ($wireTransfer?->charge_type ?? 'fixed') == 'fixed' ? 'selected' : '' }}>{{ $currencySymbol }} (Fixed)</option>
                                                    <option value="percentage" {{ ($wireTransfer?->charge_type ?? '') == 'percentage' ? 'selected' : '' }}>{{ __('% (Percentage)') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="site-input-groups position-relative">
                                        <label class="box-input-label" for="">{{ __('International Wire Fee / Charge:') }}</label>
                                        <div class="position-relative">
                                            <input type="number" step="0.01" class="box-input"
                                                   oninput="this.value = validateDouble(this.value)" name="international_charge" value="{{ $wireTransfer?->international_charge ?? 45.00 }}"/>
                                            <div class="prcntcurr">
                                                <select name="international_charge_type" class="form-select">
                                                    <option value="fixed" {{ ($wireTransfer?->international_charge_type ?? 'fixed') == 'fixed' ? 'selected' : '' }}>{{ $currencySymbol }} (Fixed)</option>
                                                    <option value="percentage" {{ ($wireTransfer?->international_charge_type ?? '') == 'percentage' ? 'selected' : '' }}>{{ __('% (Percentage)') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Transaction Limits --}}
                                <div class="col-xl-6">
                                    <div class="site-input-groups">
                                        <label class="box-input-label" for="">{{ __('Minimum Wire Amount:') }}</label>
                                        <div class="input-group joint-input">
                                            <input type="number" step="0.01" name="minimum_transfer" class="form-control" value="{{ $wireTransfer?->minimum_transfer ?? 50.00 }}"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="site-input-groups">
                                        <label class="box-input-label" for="">{{ __('Maximum Wire Amount:') }}</label>
                                        <div class="input-group joint-input">
                                            <input type="number" step="0.01" name="maximum_transfer" class="form-control" value="{{ $wireTransfer?->maximum_transfer ?? 500000.00 }}"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="site-input-groups">
                                        <label class="box-input-label" for="">{{ __('Daily Limit Maximum Amount:') }}</label>
                                        <div class="input-group joint-input">
                                            <input type="number" step="0.01" name="daily_limit_maximum_amount" class="form-control" value="{{ $wireTransfer?->daily_limit_maximum_amount ?? 1000000.00 }}"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="site-input-groups">
                                        <label class="box-input-label" for="">{{ __('Daily Limit Maximum Count:') }}</label>
                                        <div class="input-group joint-input">
                                            <input type="number" name="daily_limit_maximum_count" class="form-control" value="{{ $wireTransfer?->daily_limit_maximum_count ?? 10 }}"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="site-input-groups">
                                        <label class="box-input-label" for="">{{ __('Monthly Limit Maximum Amount:') }}</label>
                                        <div class="input-group joint-input">
                                            <input type="number" step="0.01" name="monthly_limit_maximum_amount" class="form-control" value="{{ $wireTransfer?->monthly_limit_maximum_amount ?? 5000000.00 }}"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="site-input-groups">
                                        <label class="box-input-label" for="">{{ __('Monthly Limit Maximum Count:') }}</label>
                                        <div class="input-group joint-input">
                                            <input type="number" name="monthly_limit_maximum_count" class="form-control" value="{{ $wireTransfer?->monthly_limit_maximum_count ?? 50 }}"/>
                                        </div>
                                    </div>
                                </div>

                                {{-- Dynamic Custom Field Builder --}}
                                <div class="col-12 mt-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fw-bold mb-0 text-primary">{{ __('Custom Form Fields (Optional)') }}</h5>
                                        <a href="javascript:void(0)" id="generate" class="site-btn-xs primary-btn">
                                            <i class="fas fa-plus me-1"></i> {{ __('Add Field Option') }}
                                        </a>
                                    </div>
                                    <p class="text-muted small mb-3">{{ __('Add dynamic input fields (such as invoice upload or tax ID) that users must fill when submitting wires.') }}</p>
                                </div>

                                @php
                                    $rowId = 0;
                                    $options = is_string($wireTransfer?->field_options) ? json_decode($wireTransfer->field_options, true) : ($wireTransfer?->field_options ?? []);
                                @endphp
                                <div class="col-12 addOptions">
                                    @if(!empty($options) && is_array($options))
                                        @foreach($options as $key => $value)
                                            @php
                                                $rowId++;
                                            @endphp
                                            <div class="mb-3 p-3 border rounded-3 bg-light">
                                                <div class="option-remove-row row g-3 align-items-center">
                                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12">
                                                        <div class="site-input-groups mb-0">
                                                            <input name="field_options[{{$key}}][name]" class="box-input"
                                                                   type="text" value="{{ $value['name'] ?? '' }}" required
                                                                   placeholder="Field Name (e.g. Invoice Document)">
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12">
                                                        <div class="site-input-groups mb-0">
                                                            <select name="field_options[{{$key}}][type]" class="form-select form-select-lg">
                                                                <option value="text" @selected(($value['type'] ?? '') == 'text')>{{ __('Input Text') }}</option>
                                                                <option value="textarea" @selected(($value['type'] ?? '') == 'textarea')>{{ __('Textarea') }}</option>
                                                                <option value="file" @selected(($value['type'] ?? '') == 'file')>{{ __('File Upload (PDF / Image)') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                                                        <div class="site-input-groups mb-0">
                                                            <select name="field_options[{{ $key }}][validation]" class="form-select form-select-lg">
                                                                <option value="required" @selected(($value['validation'] ?? '') == 'required')>{{ __('Required') }}</option>
                                                                <option value="nullable" @selected(($value['validation'] ?? '') == 'nullable')>{{ __('Optional') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-1 col-lg-6 col-md-6 col-sm-6 col-12 text-center">
                                                        <button class="delete-option-row delete_desc btn btn-danger btn-sm rounded-circle" type="button">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                {{-- Instructions & Disclosures --}}
                                <div class="col-xl-12 mt-3">
                                    <div class="site-input-groups fw-normal">
                                        <label for="" class="box-input-label">{{ __('Wire Instructions & Disclosures:') }}</label>
                                        <div class="site-editor">
                                            <textarea class="summernote" name="instructions">
                                                {!! $wireTransfer?->instructions ?? '<p>Domestic wire transfers received before 3:00 PM EST are processed on the same business day. International wire transfers may take 1-3 business days depending on intermediary banks.</p>' !!}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-12 mt-4">
                                    <button type="submit" class="site-btn primary-btn w-100 py-3 fw-bold">
                                        {{ __('Save Wire Transfer Settings') }}
                                    </button>
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
        $(document).ready(function () {
            var i = Number({{ $rowId }});
            $("#generate").on('click', function () {
                ++i;
                var form = `
                <div class="mb-3 p-3 border rounded-3 bg-light">
                    <div class="option-remove-row row g-3 align-items-center">
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12">
                            <div class="site-input-groups mb-0">
                                <input name="field_options[` + i + `][name]" class="box-input" type="text" value="" required placeholder="Field Name (e.g. Reference Code)">
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12">
                            <div class="site-input-groups mb-0">
                                <select name="field_options[` + i + `][type]" class="form-select form-select-lg">
                                    <option value="text">{{ __('Input Text') }}</option>
                                    <option value="textarea">{{ __('Textarea') }}</option>
                                    <option value="file">{{ __('File Upload (PDF / Image)') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                            <div class="site-input-groups mb-0">
                                <select name="field_options[` + i + `][validation]" class="form-select form-select-lg">
                                    <option value="required">{{ __('Required') }}</option>
                                    <option value="nullable">{{ __('Optional') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xl-1 col-lg-6 col-md-6 col-sm-6 col-12 text-center">
                            <button class="delete-option-row delete_desc btn btn-danger btn-sm rounded-circle" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
                $('.addOptions').append(form);
            });

            $(document).on('click', '.delete_desc', function () {
                $(this).closest('.mb-3').remove();
            });
        });
    </script>
@endsection
