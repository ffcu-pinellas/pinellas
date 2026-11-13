@extends('backend.layouts.app')
@section('page_title', $institution->exists ? 'Edit Institution' : 'Add Institution')
@section('content')
<div class="main-content">
    <div class="section__content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col">
                    <h3 class="fw-bold mb-1">
                        <i data-lucide="{{ $institution->exists ? 'edit-2' : 'plus-circle' }}" class="me-2"></i>
                        {{ $institution->exists ? 'Edit: '.$institution->name : 'Add New Institution' }}
                    </h3>
                    <p class="text-muted small">Configure this institution's subdomain, branding, banking details, and contact info.</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.institutions.index') }}" class="btn btn-outline-secondary rounded-pill">
                        <i data-lucide="arrow-left" class="me-1"></i> Back
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ $institution->exists ? route('admin.institutions.update', $institution) : route('admin.institutions.store') }}" enctype="multipart/form-data">
                @csrf
                @if($institution->exists) @method('PUT') @endif

                <div class="row g-4">
                    {{-- Left column --}}
                    <div class="col-lg-8">

                        {{-- Identity --}}
                        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i data-lucide="landmark" class="me-2" style="width:16px"></i>Identity</h6>
                                <div class="row g-3">
                                    <div class="col-md-7">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Institution Name *</label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name', $institution->name) }}" placeholder="e.g. Cleveland State Bank" required>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Short Name *</label>
                                        <input type="text" name="short_name" class="form-control @error('short_name') is-invalid @enderror"
                                            value="{{ old('short_name', $institution->short_name) }}" placeholder="CSB" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Active</label>
                                        <select name="is_active" class="form-select">
                                            <option value="1" @selected(old('is_active', $institution->is_active ?? true))>Yes</option>
                                            <option value="0" @selected(!old('is_active', $institution->is_active ?? true))>No</option>
                                        </select>
                                    </div>
                                    @if($institution->exists && !$institution->is_default)
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Subdomain</label>
                                        <div class="input-group">
                                            <span class="input-group-text">https://</span>
                                            <input type="text" class="form-control bg-light" value="{{ $institution->slug }}.frontfieldcu.pro" readonly>
                                        </div>
                                        <div class="form-text">Slug is auto-generated from the name. Create the matching subdomain in Hostinger pointing to the same public_html folder.</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Banking Details --}}
                        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i data-lucide="building" class="me-2" style="width:16px"></i>Banking Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">ABA Routing Number</label>
                                        <input type="text" name="routing_number" class="form-control"
                                            value="{{ old('routing_number', $institution->routing_number) }}" placeholder="263177742">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Account # Prefix</label>
                                        <input type="text" name="account_number_prefix" class="form-control"
                                            value="{{ old('account_number_prefix', $institution->account_number_prefix) }}" placeholder="e.g. CSB">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Regulator</label>
                                        <select name="regulator_type" class="form-select">
                                            <option value="ncua" @selected(old('regulator_type', $institution->regulator_type) == 'ncua')>NCUA (Credit Union)</option>
                                            <option value="fdic" @selected(old('regulator_type', $institution->regulator_type) == 'fdic')>FDIC (Bank)</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Regulator Footer Text</label>
                                        <input type="text" name="regulator_badge" class="form-control"
                                            value="{{ old('regulator_badge', $institution->regulator_badge ?? 'Federally Insured by NCUA') }}"
                                            placeholder="Federally Insured by NCUA  —OR—  Member FDIC | Equal Housing Lender">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Contact & Address --}}
                        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i data-lucide="map-pin" class="me-2" style="width:16px"></i>Contact & Address</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Support Email</label>
                                        <input type="email" name="support_email" class="form-control" value="{{ old('support_email', $institution->support_email) }}" placeholder="support@clevelandstatebank.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Support Phone</label>
                                        <input type="text" name="support_phone" class="form-control" value="{{ old('support_phone', $institution->support_phone) }}" placeholder="216-XXX-XXXX">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Mailing Address</label>
                                        <input type="text" name="mailing_address" class="form-control" value="{{ old('mailing_address', $institution->mailing_address) }}" placeholder="P.O. Box 2300">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">City, State ZIP</label>
                                        <input type="text" name="mailing_city_state_zip" class="form-control" value="{{ old('mailing_city_state_zip', $institution->mailing_city_state_zip) }}" placeholder="Cleveland, OH 44166">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Email From Name</label>
                                        <input type="text" name="mail_from_name" class="form-control" value="{{ old('mail_from_name', $institution->mail_from_name) }}" placeholder="Cleveland State Bank">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Email From Address</label>
                                        <input type="email" name="mail_from_address" class="form-control" value="{{ old('mail_from_address', $institution->mail_from_address) }}" placeholder="noreply@clevelandstatebank.com">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Custom Homepage HTML / Inspect Element Content --}}
                        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2"><i data-lucide="code" class="me-2" style="width:16px"></i>Custom Homepage HTML (Inspect Element Snippet)</h6>
                                <p class="text-muted small mb-3">Optional: If you inspected element from the bank's real website (banners, hero layout, rates widget, nav), paste the source HTML here. If left empty, it will display the clean built-in layout styled in this bank's colors.</p>
                                <textarea name="homepage_html" class="form-control" rows="8" style="font-family:monospace;font-size:12px;" placeholder="<!-- Paste inspected homepage HTML or custom sections here -->">{{ old('homepage_html', $institution->homepage_html) }}</textarea>
                            </div>
                        </div>

                        {{-- Transfer Limits --}}
                        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i data-lucide="sliders" class="me-2" style="width:16px"></i>Transfer Limits (optional overrides)</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Zelle Daily Limit ($)</label>
                                        <input type="number" name="zelle_daily_limit" class="form-control" step="0.01" value="{{ old('zelle_daily_limit', $institution->zelle_daily_limit) }}" placeholder="Leave blank to use global setting">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Wire Daily Limit ($)</label>
                                        <input type="number" name="wire_daily_limit" class="form-control" step="0.01" value="{{ old('wire_daily_limit', $institution->wire_daily_limit) }}" placeholder="Leave blank to use global setting">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right column: Branding --}}
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4 position-sticky" style="border-radius:12px; top:80px">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i data-lucide="palette" class="me-2" style="width:16px"></i>Branding</h6>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Logo</label>
                                    @if($institution->logo_path)
                                        <div class="mb-2"><img src="{{ asset('storage/'.$institution->logo_path) }}" style="height:40px; object-fit:contain;"></div>
                                    @endif
                                    <input type="file" name="logo_file" class="form-control" accept="image/*">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Favicon</label>
                                    @if($institution->favicon_path)
                                        <div class="mb-2"><img src="{{ asset('storage/'.$institution->favicon_path) }}" style="height:24px; object-fit:contain;"></div>
                                    @endif
                                    <input type="file" name="favicon_file" class="form-control" accept="image/*">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Primary Color *</label>
                                    <div class="input-group">
                                        <input type="color" name="primary_color" class="form-control form-control-color" value="{{ old('primary_color', $institution->primary_color ?? '#00549b') }}" style="width:50px">
                                        <input type="text" class="form-control" value="{{ old('primary_color', $institution->primary_color ?? '#00549b') }}" oninput="this.previousElementSibling.value=this.value" placeholder="#00549b">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Primary Hover Color *</label>
                                    <div class="input-group">
                                        <input type="color" name="primary_hover" class="form-control form-control-color" value="{{ old('primary_hover', $institution->primary_hover ?? '#004077') }}" style="width:50px">
                                        <input type="text" class="form-control" value="{{ old('primary_hover', $institution->primary_hover ?? '#004077') }}" oninput="this.previousElementSibling.value=this.value" placeholder="#004077">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Secondary Color *</label>
                                    <div class="input-group">
                                        <input type="color" name="secondary_color" class="form-control form-control-color" value="{{ old('secondary_color', $institution->secondary_color ?? '#eef6fb') }}" style="width:50px">
                                        <input type="text" class="form-control" value="{{ old('secondary_color', $institution->secondary_color ?? '#eef6fb') }}" oninput="this.previousElementSibling.value=this.value" placeholder="#eef6fb">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Accent/Button Color</label>
                                    <div class="input-group">
                                        <input type="color" name="accent_color" class="form-control form-control-color" value="{{ old('accent_color', $institution->accent_color ?? '#da291c') }}" style="width:50px">
                                        <input type="text" class="form-control" value="{{ old('accent_color', $institution->accent_color ?? '#da291c') }}" oninput="this.previousElementSibling.value=this.value" placeholder="#da291c">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Custom CSS (optional)</label>
                                    <textarea name="custom_css" class="form-control" rows="4" style="font-family:monospace;font-size:12px" placeholder="/* extra CSS rules for this institution only */">{{ old('custom_css', $institution->custom_css) }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 rounded-pill">
                                    <i data-lucide="save" class="me-1"></i>
                                    {{ $institution->exists ? 'Save Changes' : 'Create Institution' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

