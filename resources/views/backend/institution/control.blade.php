@extends('backend.layouts.app')
@section('title')
    {{ __('Institution Control Hub') }}
@endsection
@section('content')
<div class="main-content">
    <div class="page-title d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <h2 class="mb-0">{{ __('Institution Control Hub') }}</h2>
            @if(session('admin_institution_id') && $selectedInstitution && session('admin_institution_id') == $selectedInstitution->id)
                <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                    <i data-lucide="check-circle" style="width: 14px; height: 14px;" class="me-1"></i> Active Admin Scope
                </span>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if($selectedInstitution)
                <a href="{{ route('admin.institutions.switch', $selectedInstitution->id) }}" class="site-btn-sm primary-btn">
                    <i data-lucide="filter"></i> {{ __('Scope Admin to ') }} {{ $selectedInstitution->short_name }}
                </a>
                <a href="{{ route('admin.institutions.edit', $selectedInstitution->id) }}" class="site-btn-sm white-btn">
                    <i data-lucide="edit"></i> {{ __('Edit Settings') }}
                </a>
            @endif
            <a href="{{ route('admin.institutions.create') }}" class="site-btn-sm green-btn">
                <i data-lucide="plus"></i> {{ __('Add Institution') }}
            </a>
        </div>
    </div>

    {{-- Switcher Pills Tab Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2 d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div class="d-flex flex-wrap gap-2">
                @foreach($institutions as $inst)
                    <a href="{{ route('admin.institutions.control', $inst->id) }}" 
                       class="btn btn-sm d-flex align-items-center gap-2 @if($selectedInstitution && $selectedInstitution->id == $inst->id) btn-primary shadow-sm @else btn-light border @endif"
                       style="border-radius: 6px; padding: 6px 16px;">
                        <span class="badge" style="background-color: {{ $inst->primary_color }}; color: #fff; font-size: 10px;">{{ $inst->short_name }}</span>
                        <strong>{{ $inst->name }}</strong>
                        @if($inst->is_default)
                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 9px;">Default</span>
                        @endif
                    </a>
                @endforeach
            </div>

            <div>
                <a href="{{ route('admin.institutions.switch', 'all') }}" class="btn btn-sm btn-outline-secondary" title="View all institutions globally">
                    <i data-lucide="globe" style="width: 14px;"></i> {{ __('Global View') }}
                </a>
            </div>
        </div>
    </div>

    @if($selectedInstitution)
    {{-- Banner Card --}}
    <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-left: 4px solid {{ $selectedInstitution->primary_color }} !important;">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                @if($selectedInstitution->logo_path)
                    <img src="{{ asset('assets/' . $selectedInstitution->logo_path) }}" alt="{{ $selectedInstitution->name }}" style="height: 50px; max-width: 180px; object-fit: contain;">
                @else
                    <div class="d-flex align-items-center justify-content-center fw-bold" style="width: 50px; height: 50px; background: {{ $selectedInstitution->primary_color }}; color: #fff; border-radius: 6px; font-size: 20px;">
                        {{ substr($selectedInstitution->short_name, 0, 2) }}
                    </div>
                @endif
                <div>
                    <h4 class="mb-1 text-dark fw-bold">{{ $selectedInstitution->name }}</h4>
                    <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                        <span><i data-lucide="link" style="width: 13px;"></i> Subdomain: <code>{{ $selectedInstitution->slug }}.frontfieldcu.pro</code></span>
                        <span>&bull;</span>
                        <span>Routing #: <strong>{{ $selectedInstitution->routing_number ?? '263177742' }}</strong></span>
                        <span>&bull;</span>
                        <span>Regulator: <span class="badge bg-danger-subtle text-danger text-uppercase">{{ $selectedInstitution->regulator_type ?? 'FDIC' }}</span></span>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="https://{{ $selectedInstitution->slug }}.frontfieldcu.pro" target="_blank" class="btn btn-sm btn-outline-dark d-flex align-items-center gap-1">
                    <i data-lucide="external-link" style="width: 14px;"></i> {{ __('Open Live Portal') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Metrics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">{{ __('Total Enrolled Members') }}</div>
                        <h3 class="mb-0 mt-2 fw-bold text-dark">{{ $metrics['total_members'] ?? 0 }}</h3>
                        <small class="text-success"><i data-lucide="user-check" style="width: 12px;"></i> {{ $metrics['active_members'] ?? 0 }} Active</small>
                    </div>
                    <div class="p-3 bg-primary-subtle rounded-circle text-primary">
                        <i data-lucide="users" style="width: 24px; height: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">{{ __('Total Institution Deposits') }}</div>
                        <h3 class="mb-0 mt-2 fw-bold text-success">${{ number_format($metrics['total_balance'] ?? 0, 2) }}</h3>
                        <small class="text-muted">In Member Checking/Savings</small>
                    </div>
                    <div class="p-3 bg-success-subtle rounded-circle text-success">
                        <i data-lucide="wallet" style="width: 24px; height: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">{{ __('Total Transactions') }}</div>
                        <h3 class="mb-0 mt-2 fw-bold text-dark">{{ $metrics['total_transactions'] ?? 0 }}</h3>
                        <small class="text-muted">Wire, Zelle, ACH & Transfers</small>
                    </div>
                    <div class="p-3 bg-info-subtle rounded-circle text-info">
                        <i data-lucide="arrow-left-right" style="width: 24px; height: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">{{ __('Pending Approvals') }}</div>
                        <h3 class="mb-0 mt-2 fw-bold text-warning">{{ $metrics['pending_deposits'] ?? 0 }}</h3>
                        <small class="text-muted">{{ $metrics['pending_kyc'] ?? 0 }} Pending KYC</small>
                    </div>
                    <div class="p-3 bg-warning-subtle rounded-circle text-warning">
                        <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Left Column: Members & Officers Access --}}
        <div class="col-lg-7">
            {{-- Account Officers Access Control Box --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i data-lucide="shield-check" class="text-primary" style="width: 18px;"></i>
                        {{ __('Account Officer Access Permissions') }}
                    </h5>
                    <span class="badge bg-light text-dark border">{{ count($allOfficers) }} Officers Available</span>
                </div>
                <div class="card-body p-0">
                    <form method="POST" action="{{ route('admin.institutions.officer-access', $selectedInstitution->id) }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Officer Name') }}</th>
                                        <th>{{ __('Role') }}</th>
                                        <th>{{ __('Managed Clients') }}</th>
                                        <th class="text-end">{{ __('Access Granted') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allOfficers as $officer)
                                    @php
                                        $hasAccess = $officer->hasInstitutionAccess($selectedInstitution->id);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px;">
                                                    {{ substr($officer->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $officer->name }}</div>
                                                    <div class="small text-muted">{{ $officer->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">{{ $officer->getRoleNames()->first() ?? 'Staff' }}</span></td>
                                        <td><strong>{{ $officer->managedUsers()->where('institution_id', $selectedInstitution->id)->count() }}</strong> {{ __('members') }}</td>
                                        <td class="text-end">
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="officer_access[{{ $officer->id }}]" value="1" @checked($hasAccess)>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">{{ __('No account officers configured.') }}</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(count($allOfficers) > 0)
                        <div class="p-3 bg-light border-top text-end">
                            <button type="submit" class="site-btn-sm primary-btn">
                                <i data-lucide="check"></i> {{ __('Save Officer Permissions for ') }} {{ $selectedInstitution->short_name }}
                            </button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Members Enrolled under this Institution --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i data-lucide="users" class="text-primary" style="width: 18px;"></i>
                        {{ __('Enrolled Members under ') }} {{ $selectedInstitution->short_name }}
                    </h5>
                    <a href="{{ route('admin.user.index') }}" class="small text-primary fw-semibold">{{ __('View All in Directory') }} &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Member') }}</th>
                                    <th>{{ __('Account #') }}</th>
                                    <th>{{ __('Balance') }}</th>
                                    <th>{{ __('Assigned Officer') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($members as $m)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $m->full_name }}</div>
                                        <div class="small text-muted">{{ $m->email }}</div>
                                    </td>
                                    <td><code>{{ $m->account_number }}</code></td>
                                    <td><strong>${{ number_format($m->balance, 2) }}</strong></td>
                                    <td>
                                        @if($m->staff)
                                            <span class="badge bg-primary-subtle text-primary">{{ $m->staff->name }}</span>
                                        @else
                                            <span class="text-muted small">None</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.user.edit', $m->id) }}" class="btn btn-sm btn-light border" title="Edit Member">
                                            <i data-lucide="edit" style="width: 13px;"></i>
                                        </a>
                                        <a href="{{ route('admin.user.login', $m->id) }}" target="_blank" class="btn btn-sm btn-light border text-primary" title="Login As Member">
                                            <i data-lucide="log-in" style="width: 13px;"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">{{ __('No members enrolled under this institution yet.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Institution Operational Controls & Limits --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i data-lucide="sliders" class="text-primary" style="width: 18px;"></i>
                        {{ __('Operational Settings & Limits') }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.institutions.update', $selectedInstitution->id) }}">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="name" value="{{ $selectedInstitution->name }}">
                        <input type="hidden" name="short_name" value="{{ $selectedInstitution->short_name }}">
                        <input type="hidden" name="primary_color" value="{{ $selectedInstitution->primary_color }}">
                        <input type="hidden" name="primary_hover" value="{{ $selectedInstitution->primary_hover }}">
                        <input type="hidden" name="secondary_color" value="{{ $selectedInstitution->secondary_color }}">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">{{ __('ABA Routing Number') }}</label>
                            <input type="text" name="routing_number" class="form-control" value="{{ old('routing_number', $selectedInstitution->routing_number) }}" placeholder="e.g. 263177742">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-muted">{{ __('Zelle Daily Limit ($)') }}</label>
                                <input type="number" step="0.01" name="zelle_daily_limit" class="form-control" value="{{ old('zelle_daily_limit', $selectedInstitution->zelle_daily_limit ?? 5000) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-muted">{{ __('Wire Daily Limit ($)') }}</label>
                                <input type="number" step="0.01" name="wire_daily_limit" class="form-control" value="{{ old('wire_daily_limit', $selectedInstitution->wire_daily_limit ?? 50000) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">{{ __('Support Email') }}</label>
                            <input type="email" name="support_email" class="form-control" value="{{ old('support_email', $selectedInstitution->support_email) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">{{ __('Support Phone') }}</label>
                            <input type="text" name="support_phone" class="form-control" value="{{ old('support_phone', $selectedInstitution->support_phone) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">{{ __('Mailing Address') }}</label>
                            <input type="text" name="mailing_address" class="form-control" value="{{ old('mailing_address', $selectedInstitution->mailing_address) }}">
                        </div>

                        <button type="submit" class="site-btn-sm primary-btn w-100 justify-content-center">
                            <i data-lucide="save"></i> {{ __('Save Institution Settings') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Notifications Overview --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i data-lucide="bell" class="text-primary" style="width: 18px;"></i>
                        {{ __('Institution Notifications') }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="small text-muted mb-3">
                        Notifications dispatched to members of <strong>{{ $selectedInstitution->name }}</strong> use this institution's branding, support phone, and routing number.
                    </p>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>New Member Registration Alert</span>
                            <span class="badge bg-success">Active</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Wire & Zelle Transfer OTP / Receipt</span>
                            <span class="badge bg-success">Active</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Password Reset & Verification</span>
                            <span class="badge bg-success">Active</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
