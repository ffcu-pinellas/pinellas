<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InstitutionController extends Controller
{
    public function index()
    {
        $institutions = Institution::orderBy('is_default', 'desc')->orderBy('name')->get();
        return view('backend.institution.index', compact('institutions'));
    }

    public function create()
    {
        return view('backend.institution.form', ['institution' => new Institution()]);
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, $this->rules());
        $data['slug'] = Str::slug($data['name'], '');  // e.g. "Cleveland State Bank" → "clevelandstatebank"

        // Prevent reserved slugs
        $reserved = ['www', 'mail', 'smtp', 'api', 'admin', 'ftp', 'cpanel', 'webmail', 'user', 'login', 'register', 'assets'];
        if (in_array($data['slug'], $reserved)) {
            return back()->withErrors(['name' => 'This institution name generates a reserved subdomain slug. Choose a different name.'])->withInput();
        }

        // Handle logo/favicon uploads
        if ($request->hasFile('logo_file')) {
            $data['logo_path'] = $request->file('logo_file')->store('institutions', 'public');
        }
        if ($request->hasFile('favicon_file')) {
            $data['favicon_path'] = $request->file('favicon_file')->store('institutions', 'public');
        }

        Institution::create($data);
        notify()->success('Institution created successfully.');
        return redirect()->route('admin.institutions.index');
    }

    public function edit(Institution $institution)
    {
        return view('backend.institution.form', compact('institution'));
    }

    public function update(Request $request, Institution $institution)
    {
        $data = $this->validate($request, $this->rules($institution->id));

        if ($request->hasFile('logo_file')) {
            $data['logo_path'] = $request->file('logo_file')->store('institutions', 'public');
        }
        if ($request->hasFile('favicon_file')) {
            $data['favicon_path'] = $request->file('favicon_file')->store('institutions', 'public');
        }

        // Don't allow changing the default institution's slug or is_default flag
        if ($institution->is_default) {
            unset($data['slug'], $data['is_default']);
        }

        $institution->update($data);
        notify()->success('Institution updated successfully.');
        return redirect()->route('admin.institutions.index');
    }

    public function toggleActive(Institution $institution)
    {
        if ($institution->is_default) {
            notify()->error('The default institution cannot be deactivated.');
            return back();
        }
        $institution->update(['is_active' => ! $institution->is_active]);
        notify()->success('Institution status updated.');
        return back();
    }

    public function destroy(Institution $institution)
    {
        if ($institution->is_default) {
            notify()->error('The default institution cannot be deleted.');
            return back();
        }
        if ($institution->users()->count() > 0) {
            notify()->error('Cannot delete an institution that has members. Deactivate it instead.');
            return back();
        }
        $institution->delete();
        notify()->success('Institution deleted.');
        return redirect()->route('admin.institutions.index');
    }

    protected function rules($ignoreId = null): array
    {
        return [
            'name'                   => 'required|string|max:100',
            'short_name'             => 'required|string|max:20',
            'is_active'              => 'boolean',
            'primary_color'          => 'required|string|max:20',
            'primary_hover'          => 'required|string|max:20',
            'secondary_color'        => 'required|string|max:20',
            'accent_color'           => 'nullable|string|max:20',
            'custom_css'             => 'nullable|string',
            'routing_number'         => 'nullable|string|max:20',
            'account_number_prefix'  => 'nullable|string|max:20',
            'regulator_badge'        => 'nullable|string|max:100',
            'regulator_type'         => 'nullable|in:ncua,fdic',
            'support_email'          => 'nullable|email|max:100',
            'support_phone'          => 'nullable|string|max:30',
            'mailing_address'        => 'nullable|string|max:200',
            'mailing_city_state_zip' => 'nullable|string|max:100',
            'mail_from_name'         => 'nullable|string|max:100',
            'mail_from_address'      => 'nullable|email|max:100',
            'homepage_html'          => 'nullable|string',
            'zelle_daily_limit'      => 'nullable|numeric|min:0',
            'wire_daily_limit'       => 'nullable|numeric|min:0',
        ];
    }
}

