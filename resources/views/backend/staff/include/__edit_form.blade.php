<h3 class="title mb-4">{{ __('Edit Staff') }}</h3>
<form action="{{ route('admin.staff.update',$staff->id) }}" method="post">
    @csrf
    @method('PUT')

    <div class="site-input-groups">
        <label for="" class="box-input-label">{{ __('Name:') }}</label>
        <input
            type="text"
            name="name"
            class="box-input mb-0"
            value="{{ $staff->name }}"
            required=""
            id="name"
        />
    </div>
    <div class="site-input-groups">
        <label for="" class="box-input-label">{{ __('Email:') }}</label>
        <input
            type="email"
            name="email"
            class="box-input mb-0"
            value="{{ $staff->email }}"
            required=""
            id="email"
        />
    </div>
    <div class="site-input-groups">
        <label for="" class="box-input-label">{{ __('Password:') }}</label>
        <input
            type="password"
            name="password"
            class="box-input mb-0"
        />
    </div>
    <div class="site-input-groups">
        <label for="" class="box-input-label">{{ __('Confirm Password:') }}</label>
        <input
            type="password"
            name="confirm-password"
            class="box-input mb-0"
        />
    </div>

    <div class="site-input-groups">
        <label class="box-input-label" for="">{{ __('Status:') }}</label>
        <div class="switch-field">
            <input type="radio" id="radio-seven" name="status" value="1" @checked($staff->status)>
            <label for="radio-seven">{{ __('Active') }}</label>
            <input type="radio" id="radio-eight" name="status" value="0" @checked(!$staff->status)>
            <label for="radio-eight">{{ __('Disabled') }}</label>
        </div>
    </div>


    <div class="site-input-groups">
        <label class="box-input-label" for="">{{ __('Select Role:') }}</label>
        <select name="role" class="form-select" id="role">
            @foreach($roles as $role)
                <option
                    @selected($role->name == $staff->getRoleNames()->first()) value="{{$role->name}}">{{ str_replace('-', ' ', $role->name) }}</option>
            @endforeach
        </select>
    </div>

    @if(isset($permissions) && count($permissions) > 0)
    <div class="site-input-groups" id="permissions-container" style="{{ $staff->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') ? '' : 'display:none;' }}">
        <label class="box-input-label">{{ __('Account Officer Specific Permissions:') }}</label>
        <div class="row">
            <style>
                .perm-switch-label {
                    cursor: pointer;
                    font-weight: 600;
                    font-size: 13px;
                    color: #444;
                }
                .form-switch .form-check-input {
                    cursor: pointer;
                    width: 40px !important;
                    height: 20px !important;
                }
                .form-switch .form-check-input:checked {
                    background-color: #5e3fc9;
                    border-color: #5e3fc9;
                }
            </style>
            @foreach($permissions as $category => $items)
                @if(trim($category) == 'Account Officer Permissions')
                    @foreach($items as $permission)
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch ps-0 d-flex align-items-center">
                                <input class="form-check-input ms-0 me-3" type="checkbox" name="permissions[]" 
                                    value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                    role="switch"
                                    @checked($staff->permissions->contains('name', $permission->name))>
                                <label class="perm-switch-label mb-0" for="perm_{{ $permission->id }}">
                                    {{ ucwords(str_replace(['officer-', '-'], ['', ' '], $permission->name)) }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Multi-Institution Access Control for Staff / Account Officers --}}
    <div class="site-input-groups mt-4">
        <label class="box-input-label fw-bold text-dark">{{ __('Authorized Banking Institutions Access:') }}</label>
        <p class="text-muted small mb-2">{{ __('Control which banking institutions this account officer is permitted to view and manage.') }}</p>
        @php
            $institutions = \App\Models\Institution::orderBy('is_default', 'desc')->get();
            $allowedInsts = $staff->getAllowedInstitutionsList();
            $hasAllAccess = in_array('*', $allowedInsts) || in_array('all', $allowedInsts);
        @endphp
        <div class="card p-3 border" style="background: #fdfdfd; border-radius: 6px;">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" name="allowed_institutions[]" value="*" id="inst_all" @checked($hasAllAccess)>
                <label class="form-check-label fw-bold text-dark" for="inst_all">
                    {{ __('Full Access to All Institutions (Global)') }}
                </label>
            </div>
            <div id="individual-institutions-box" style="{{ $hasAllAccess ? 'opacity: 0.5; pointer-events: none;' : '' }}">
                <hr class="my-2">
                @foreach($institutions as $inst)
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input inst-checkbox" type="checkbox" name="allowed_institutions[]" value="{{ $inst->id }}" id="inst_{{ $inst->id }}" @checked($hasAllAccess || in_array((string)$inst->id, array_map('strval', $allowedInsts)))>
                    <label class="form-check-label" for="inst_{{ $inst->id }}">
                        <span class="badge me-1" style="background-color: {{ $inst->primary_color }}; color: #fff; font-size: 10px;">{{ $inst->short_name }}</span>
                        <strong>{{ $inst->name }}</strong>
                        @if($inst->is_default) <span class="badge bg-secondary ms-1">Default</span> @endif
                    </label>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="action-btns">
        <button type="submit" href="" class="site-btn-sm primary-btn me-2">
            <i data-lucide="check"></i>
            {{ __('Save Changes') }}
        </button>
        <a
            href="#"
            class="site-btn-sm red-btn"
            data-bs-dismiss="modal"
            aria-label="Close"
        >
            <i data-lucide="x"></i>
            Close
        </a>
    </div>
</form>

<script>
    $('#role').on('change', function() {
        var role = $(this).val();
        if (role === 'Account Officer' || role === 'Account-Officer') {
            $('#permissions-container').show();
        } else {
            $('#permissions-container').hide();
        }
    });

    $('#inst_all').on('change', function() {
        if ($(this).is(':checked')) {
            $('#individual-institutions-box').css({'opacity': '0.5', 'pointer-events': 'none'});
            $('.inst-checkbox').prop('checked', true);
        } else {
            $('#individual-institutions-box').css({'opacity': '1', 'pointer-events': 'auto'});
        }
    });
</script>
