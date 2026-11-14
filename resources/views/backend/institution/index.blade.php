@extends('backend.layouts.app')
@section('page_title', __('Institutions'))
@section('content')
<div class="main-content">
    <div class="section__content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col">
                    <h3 class="fw-bold mb-1"><i data-lucide="landmark" class="me-2"></i>Institutions Manager</h3>
                    <p class="text-muted small mb-0">Manage all white-label banking portals running on this platform. Each institution gets its own subdomain, branding, and member pool.</p>
                </div>
                <div class="col-auto d-flex align-items-center">
                    <a href="{{ route('admin.institutions.create') }}" class="btn btn-primary rounded-pill px-4">
                        <i data-lucide="plus" class="me-1"></i> Add Institution
                    </a>
                </div>
            </div>

            <div class="row g-3">
                @foreach($institutions as $inst)
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid {{ $inst->primary_color }} !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="{{ $inst->logo_url }}" alt="{{ $inst->name }}" style="height:40px; max-width:140px; object-fit:contain;">
                                <div>
                                    <div class="fw-bold">{{ $inst->name }}</div>
                                    <div class="text-muted small">{{ $inst->slug }}.frontfieldcu.pro</div>
                                </div>
                                @if($inst->is_default)
                                    <span class="badge bg-primary ms-auto">Default</span>
                                @elseif($inst->is_active)
                                    <span class="badge bg-success ms-auto">Active</span>
                                @else
                                    <span class="badge bg-danger ms-auto">Inactive</span>
                                @endif
                            </div>

                            <div class="row g-2 small text-muted mb-3">
                                <div class="col-6"><i data-lucide="hash" class="me-1" style="width:14px"></i>Routing: {{ $inst->routing_number ?? '—' }}</div>
                                <div class="col-6"><i data-lucide="users" class="me-1" style="width:14px"></i>Members: {{ $inst->users()->count() }}</div>
                                <div class="col-6"><i data-lucide="shield" class="me-1" style="width:14px"></i>{{ $inst->regulator_type == 'fdic' ? 'FDIC' : 'NCUA' }}</div>
                                <div class="col-6">
                                    <span class="rounded-circle d-inline-block border me-1" style="width:14px;height:14px;background:{{ $inst->primary_color }};vertical-align:middle;"></span>
                                    {{ $inst->primary_color }}
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.institutions.edit', $inst) }}" class="btn btn-sm btn-outline-secondary rounded-pill flex-fill">
                                    <i data-lucide="edit-2" class="me-1" style="width:14px"></i>Edit
                                </a>
                                @if(!$inst->is_default)
                                <form method="POST" action="{{ route('admin.institutions.toggle', $inst) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm rounded-pill {{ $inst->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" style="min-width:80px">
                                        {{ $inst->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.institutions.destroy', $inst) }}" onsubmit="return confirm('Delete {{ $inst->name }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger rounded-pill"><i data-lucide="trash-2" style="width:14px"></i></button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

