<div class="header">
    @php
        $height = setting('site_logo_height','global') == 'auto' ? 'auto' : setting('site_logo_height','global').'px';
        $width = setting('site_logo_width','global') == 'auto' ? 'auto' : setting('site_logo_width','global').'px';
    @endphp

    <div class="logo">
        <a href="{{ route('admin.dashboard') }}">
            <img
                class="logo-unfold"
                style="height:{{ $height }};width:{{ $width }}"
                src="{{asset(setting('site_logo','global'))}}"
                alt="{{ setting('site_title','global') }}"
            />
            <img
                class="logo-fold"
                src="{{asset(setting('site_favicon','global'))}}"
                alt="{{ setting('site_title','global') }}"
            />
        </a>
    </div>
    <div class="nav-wrap">
        <div class="nav-left d-flex align-items-center gap-3">
            <button class="sidebar-toggle"><i data-lucide="align-justify"></i></button>

            @php
                $adminUser = auth('admin')->user();
                $currentInstId = session('admin_institution_id');
                $allInstitutions = \App\Models\Institution::orderBy('is_default', 'desc')->get();
                if ($adminUser && ! $adminUser->isSuperAdmin()) {
                    $allowed = $adminUser->getAllowedInstitutionsList();
                    if (!in_array('*', $allowed)) {
                        $allInstitutions = $allInstitutions->whereIn('id', $allowed);
                    }
                }
                $currentInst = $currentInstId ? \App\Models\Institution::find($currentInstId) : null;
            @endphp
            <div class="dropdown institution-scope-switcher">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 12.5px; padding: 5px 12px; border-radius: 4px; background: #fff;">
                    <i data-lucide="landmark" style="width: 14px; height: 14px;"></i>
                    <span class="fw-semibold">
                        {{ $currentInst ? $currentInst->name : __('All Institutions (Global)') }}
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-start shadow-sm" style="min-width: 250px; font-size: 13px;">
                    <li class="dropdown-header text-uppercase text-muted" style="font-size: 10.5px; letter-spacing: 0.5px;">{{ __('Switch Institution View') }}</li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between @if(!$currentInstId) active fw-bold @endif" href="{{ route('admin.institutions.switch', 'all') }}">
                            <span><i data-lucide="globe" style="width: 14px; height: 14px;" class="me-1 text-muted"></i> {{ __('All Institutions (Global)') }}</span>
                            @if(!$currentInstId)<i data-lucide="check" style="width: 14px; height: 14px;"></i>@endif
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    @foreach($allInstitutions as $inst)
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between @if($currentInstId == $inst->id) active fw-bold @endif" href="{{ route('admin.institutions.switch', $inst->id) }}">
                            <span>
                                <span class="badge me-1" style="background-color: {{ $inst->primary_color }}; color: #fff; font-size: 10px;">{{ $inst->short_name }}</span>
                                {{ $inst->name }}
                            </span>
                            @if($currentInstId == $inst->id)<i data-lucide="check" style="width: 14px; height: 14px;"></i>@endif
                        </a>
                    </li>
                    @endforeach
                    @if($adminUser && $adminUser->isSuperAdmin())
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item text-primary d-flex align-items-center gap-1" href="{{ route('admin.institutions.control') }}">
                            <i data-lucide="sliders" style="width: 14px; height: 14px;"></i> {{ __('Dedicated Control Hub') }}
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="nav-right">
            @if(setting('language_switcher'))
            <div class="single-nav-right admin-language-switch">
                <select name="language" class="form-select"
                        onchange="window.location.href=this.options[this.selectedIndex].value;">
                    @foreach(\App\Models\Language::where('status',true)->get() as $lang)
                        <option
                            value="{{ route('language-update',['name'=> $lang->locale]) }}" @selected( app()->getLocale() == $lang->locale )>{{$lang->name}}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="single-nav-right admin-notifications">
                @php
                    $notifications = App\Models\Notification::with('user')->where('for','admin')
                        ->when(auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                            $query->whereHas('user', function ($q) {
                                $q->where('staff_id', auth('admin')->id());
                            });
                        })
                        ->latest()->take(10)->get();
                    $totalUnread = App\Models\Notification::with('user')->where('for','admin')
                        ->when(auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                            $query->whereHas('user', function ($q) {
                                $q->where('staff_id', auth('admin')->id());
                            });
                        })
                        ->where('read', 0)->count();
                    $totalCount = App\Models\Notification::with('user')->where('for','admin')
                        ->when(auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                            $query->whereHas('user', function ($q) {
                                $q->where('staff_id', auth('admin')->id());
                            });
                        })
                        ->get()->count();
                @endphp
                @include('global.__notification_data',['notifications'=>$notifications,'totalUnread'=>$totalUnread,'totalCount'=>$totalCount])
            </div>


            <div class="single-nav-right">
                <a href="{{ route('home') }}" target="_blank" class="item" data-bs-toggle="tooltip" title=""
                   data-bs-placement="left" data-bs-original-title="Visit Landing Page">
                    <i data-lucide="globe"></i>
                </a>
            </div>
            <div class="single-nav-right">
                <button type="button" class="item" data-bs-toggle="dropdown" aria-expanded="false">
                    <i data-lucide="user"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a href="{{ route('admin.profile') }}" class="dropdown-item"><i
                                data-lucide="user"></i>{{ __('Profile') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('admin.password-change') }}" class="dropdown-item">
                            <i data-lucide="lock"></i>{{ __('Change Password') }}
                        </a>
                    </li>
                    <li class="logout">

                        <a href="{{ url('admin/logout') }}" class="dropdown-item" type="button"
                           onclick="event.preventDefault(); localStorage.clear();  $('#logout-form').submit();">
                            <i data-lucide="log-out"></i> {{ __('Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
