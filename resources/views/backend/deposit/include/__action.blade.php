@can('deposit-action')
    <span type="button"
          data-id="{{$id}}"
          id="deposit-action"
    ><button class="round-icon-btn red-btn" data-bs-toggle="tooltip" title="" data-bs-original-title="Approval Process"><i
                data-lucide="eye"></i></button></span>
    <script>
        'use strict';
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
@endcan
