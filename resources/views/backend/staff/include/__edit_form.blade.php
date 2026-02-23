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
        <style>
            .permission-switch-group {
                display: flex;
                align-items: center;
                margin-bottom: 12px;
                background: #f8f9fa;
                padding: 10px 15px;
                border-radius: 8px;
                border: 1px solid #e9ecef;
            }
            .os-switch {
                position: relative;
                display: inline-block;
                width: 44px;
                height: 22px;
                margin-right: 12px;
            }
            .os-switch input { opacity: 0; width: 0; height: 0; }
            .os-slider {
                position: absolute;
                cursor: pointer;
                top: 0; left: 0; right: 0; bottom: 0;
                background-color: #dee2e6;
                transition: .3s;
                border-radius: 22px;
            }
            .os-slider:before {
                position: absolute;
                content: "";
                height: 16px; width: 16px;
                left: 3px; bottom: 3px;
                background-color: white;
                transition: .3s;
                border-radius: 50%;
            }
            input:checked + .os-slider { background-color: #5e3fc9; }
            input:checked + .os-slider:before { transform: translateX(22px); }
            .perm-text {
                font-size: 13px;
                font-weight: 600;
                color: #2b3457;
                cursor: pointer;
            }
        </style>
        <div class="row">
            @foreach($permissions as $category => $items)
                @if(trim($category) == 'Account Officer Permissions')
                    @foreach($items as $permission)
                        <div class="col-md-6">
                            <div class="permission-switch-group">
                                <label class="os-switch mb-0">
                                    <input type="checkbox" name="permissions[]" 
                                        value="{{ $permission->name }}"
                                        @checked($staff->hasPermissionTo($permission->name, 'admin'))>
                                    <span class="os-slider"></span>
                                </label>
                                <span class="perm-text" onclick="$(this).prev().find('input').click();">
                                    {{ ucwords(str_replace(['officer-', '-'], ['', ' '], $permission->name)) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>
    </div>
    @endif

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
</script>
