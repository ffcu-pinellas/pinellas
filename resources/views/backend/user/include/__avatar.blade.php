@if(!empty($avatar))
    <img class="avatar avatar-round" src="{{ asset($avatar)}}" alt="" height="40" width="40">
@else
    <span class="avatar-text">{{ strtoupper(substr($first_name ?? '', 0, 1) . substr($last_name ?? '', 0, 1) ?: 'U') }}</span>
@endif
