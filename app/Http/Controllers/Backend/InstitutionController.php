<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InstitutionController extends Controller
{
    public function switchScope($institutionId = 'all')
    {
        $admin = auth('admin')->user();

        if ($institutionId === 'all' || empty($institutionId)) {
            if (!$admin->isSuperAdmin() && !$admin->hasInstitutionAccess('*')) {
                notify()->error('You are restricted to your assigned institution only.');
                return back();
            }
            session()->forget('admin_institution_id');
            notify()->success('Switched to Global View (All Institutions).');
            return back();
        }

        $institution = Institution::findOrFail($institutionId);

        if (!$admin->hasInstitutionAccess($institution->id)) {
            notify()->error("You do not have authorized access to {$institution->name}.");
            return back();
        }

        session(['admin_institution_id' => $institution->id]);
        notify()->success("Active scope set to {$institution->name}.");
        return back();
    }

    public function control(Request $request, $institutionId = null)
    {
        $admin = auth('admin')->user();
        $institutions = Institution::orderBy('is_default', 'desc')->get();

        if (!$admin->isSuperAdmin()) {
            $allowed = $admin->getAllowedInstitutionsList();
            if (!in_array('*', $allowed)) {
                $institutions = $institutions->whereIn('id', $allowed);
            }
        }

        $selectedInstitution = null;
        if ($institutionId && $institutionId !== 'all') {
            $selectedInstitution = Institution::findOrFail($institutionId);
        } elseif (session()->has('admin_institution_id')) {
            $selectedInstitution = Institution::find(session('admin_institution_id'));
        }

        if (!$selectedInstitution && $institutions->count() > 0) {
            $selectedInstitution = $institutions->first();
        }

        // Metrics for selected institution
        $metrics = [];
        $members = collect();
        $allOfficers = \App\Models\Admin::where('is_admin', 0)->get();

        if ($selectedInstitution) {
            $userQuery = \App\Models\User::where('institution_id', $selectedInstitution->id);
            $metrics['total_members'] = (clone $userQuery)->count();
            $metrics['active_members'] = (clone $userQuery)->where('status', 1)->count();
            $metrics['total_balance'] = (clone $userQuery)->sum('balance');
            $metrics['pending_kyc'] = (clone $userQuery)->where('kyc', 0)->count();

            $metrics['total_transactions'] = \App\Models\Transaction::whereHas('user', function ($q) use ($selectedInstitution) {
                $q->where('institution_id', $selectedInstitution->id);
            })->count();

            $metrics['pending_deposits'] = \App\Models\Deposit::whereHas('user', function ($q) use ($selectedInstitution) {
                $q->where('institution_id', $selectedInstitution->id);
            })->where('status', 'pending')->count();

            $members = $userQuery->with('staff')->latest()->take(15)->get();
        }

        return view('backend.institution.control', compact('institutions', 'selectedInstitution', 'metrics', 'members', 'allOfficers'));
    }

    public function updateOfficerAccess(Request $request, Institution $institution)
    {
        $admin = auth('admin')->user();
        if (!$admin->isSuperAdmin()) {
            notify()->error('Only Super Admins can manage Account Officer institution access.');
            return back();
        }

        $officerAccess = $request->input('officer_access', []); // [officer_id => 1 or 0]

        $officers = \App\Models\Admin::where('is_admin', 0)->get();

        foreach ($officers as $officer) {
            $currentAllowed = $officer->getAllowedInstitutionsList();
            $hasAll = in_array('*', $currentAllowed) || in_array('all', $currentAllowed);

            $shouldHaveAccess = !empty($officerAccess[$officer->id]);

            if ($shouldHaveAccess) {
                if (!$officer->hasInstitutionAccess($institution->id)) {
                    if ($hasAll) {
                        // Already has access
                    } else {
                        $currentAllowed[] = (string)$institution->id;
                        $officer->allowed_institutions = json_encode(array_values(array_unique($currentAllowed)));
                        $officer->save();
                    }
                }
            } else {
                // Revoke access
                if ($hasAll) {
                    // Convert all to explicit list except this one
                    $allIds = Institution::where('id', '!=', $institution->id)->pluck('id')->map(fn($id) => (string)$id)->toArray();
                    $officer->allowed_institutions = json_encode($allIds);
                    $officer->save();
                } else {
                    $updated = array_values(array_diff(array_map('strval', $currentAllowed), [(string)$institution->id]));
                    $officer->allowed_institutions = json_encode($updated);
                    $officer->save();
                }
            }
        }

        notify()->success("Account Officer access permissions for {$institution->name} updated successfully.");
        return back();
    }

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

