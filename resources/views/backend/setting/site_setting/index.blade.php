@extends('backend.setting.index')
@section('setting-title')
    {{ __('Site Settings') }}
@endsection
@section('title')
    {{ __('Site Settings') }}
@endsection
@section('setting-content')
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $scopedInst = session('admin_institution_id') ? \App\Models\Institution::find(session('admin_institution_id')) : null;
    @endphp
    @if($scopedInst && !$scopedInst->is_default)
        <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between p-3 mb-4 shadow-sm" style="border-left: 4px solid {{ $scopedInst->primary_color }}; background: #fff;">
            <div class="d-flex align-items-center gap-3">
                <span class="badge" style="background-color: {{ $scopedInst->primary_color }}; color: #fff; font-size: 11px;">{{ $scopedInst->short_name }}</span>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">{{ $scopedInst->name }} Active Scope</h6>
                    <small class="text-muted">Global Site Settings apply to the primary system. To manage <strong>{{ $scopedInst->name }}</strong> specific limits, routing number, address, and dedicated Telegram credentials, use the Dedicated Control Hub.</small>
                </div>
            </div>
            <a href="{{ route('admin.institutions.control', $scopedInst->id) }}" class="btn btn-sm btn-primary text-nowrap mt-2 mt-md-0">
                <i data-lucide="sliders" style="width: 14px; height: 14px;"></i> {{ __('Open ') }}{{ $scopedInst->short_name }}{{ __(' Control Hub') }}
            </a>
        </div>
    @endif

    @foreach(config('setting') as $section => $fields)

        @includeIf('backend.setting.site_setting.include.__'. $section)

    @endforeach
@endsection
@push('single-script')
    <script>
    (function($) {
        'use strict';

        var timezoneData = JSON.parse(@json(getJsonData('timeZone')));
        const convertedData = timezoneData.map(item => ({
            id: item.name,
            text: `${item.description} (${item.name})`
        }));

        $('.site-timezone').select2({
            data: convertedData
        });

        // Account Deactivation Functionality
        function toggleElementsVisibility() {
            var inactiveAccountDisabledValue = $('input[name="inactive_account_disabled"]:checked').val();

            // Check the value and show/hide elements accordingly
            if (inactiveAccountDisabledValue == 1) {
                $('#inactive_days_sec').show();
                $('#inactive_account_fees_sec').show();
                toggleFeesAmountVisibility();
            } else {
                $('#inactive_days_sec').hide();
                $('#inactive_account_fees_sec').hide();
                $("#fees_amount_sec").hide();
            }
        }

        function toggleFeesAmountVisibility()
        {
            var inactive_account_fees = $('input[name="inactive_account_fees"]:checked').val();

            if (inactive_account_fees == 1) {
                $('#fees_amount_sec').show();
            } else {
                $('#fees_amount_sec').hide();
            }
        }

        // Initial toggle on page load
        toggleElementsVisibility();
        toggleFeesAmountVisibility();

        $('input[name="inactive_account_disabled"]').on('change', function () {
            toggleElementsVisibility();
        });

        $('input[name="inactive_account_fees"]').on('change', function () {
            toggleFeesAmountVisibility();
        });
    })(jQuery);
    </script>
@endpush
