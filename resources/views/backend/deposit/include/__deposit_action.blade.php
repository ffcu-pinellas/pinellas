<h3 class="title mb-4">
    {{ __('Deposit Approval Action') }}
</h3>

<ul class="list-group mb-4">
    <li class="list-group-item">
        {{ __('Deposit Wallet') }}:
        <strong>
            @if ($data->wallet_type == 'default')
                {{ __('Default Wallet') }}
            @else
                {{ $data?->userWallet?->currency?->name }} {{ __('Wallet') }}
            @endif
        </strong> <br>
        {{ __('Total amount') }}: <strong>{{ $data->final_amount. ' '.$currency }}</strong>
    </li>
    @if($data->pay_currency != $currency)
        <li class="list-group-item">
            {{ __('Conversion amount') }}: <strong>{{ $data->pay_amount. ' '.$data->pay_currency }}</strong>
        </li>
    @endif

</ul>

@php
    $manualData = json_decode($data->manual_field_data, true);
@endphp

@if(is_array($manualData))
    <ul class="list-group mb-4">
        @foreach($manualData as $key => $value)
        <li class="list-group-item d-flex flex-column align-items-start">
            <span class="mb-2 text-muted fw-bold">{{ str_replace('_', ' ', $key) }}:</span>

            @if($value != new stdClass())
                @php
                    $isImage = false;
                    $cleanPath = ltrim($value, '/');
                    // Check multiple possible paths for robustness
                    if (file_exists(public_path($cleanPath))) {
                        $isImage = true;
                    } elseif (file_exists(public_path('assets/' . $cleanPath))) {
                        $value = 'assets/' . $cleanPath;
                        $isImage = true;
                    }
                @endphp

                @if($isImage)
                    <div class="check-image-container border rounded overflow-hidden">
                        <img src="{{ asset($value) }}" alt="{{ $key }}" class="img-fluid" style="max-height: 250px; width: 100%; object-fit: contain;"/>
                    </div>
                @else
                    <strong class="text-dark">{{ $value }}</strong>
                @endif
            @else
                <i class="text-muted small">N/A</i>
            @endif
        </li>
    @endforeach
</ul>
<style>
    .check-image-container { background: #f8f9fa; display: flex; align-items: center; justify-content: center; width: 100%; }
</style>
@endif

<form action="{{ route('admin.deposit.action.now') }}" method="post">
    @csrf
    <input type="hidden" name="id" value="{{ $id }}">

    <div class="site-input-groups">
        <label for="" class="box-input-label">{{ __('Details Message(Optional)') }}</label>
        <textarea name="message" class="form-textarea mb-0" placeholder="Details Message"></textarea>
    </div>

    <div class="action-btns">
        <button type="submit" name="approve" value="yes" class="site-btn-sm primary-btn me-2">
            <i data-lucide="check"></i>
            {{ __('Approve') }}
        </button>
        <button type="submit" name="reject" value="yes" class="site-btn-sm red-btn">
            <i data-lucide="x"></i>
            {{ __('Reject') }}
        </button>
    </div>

</form>
<script>
    'use strict';
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>



