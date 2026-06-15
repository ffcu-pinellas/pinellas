<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Arr;
use DB;
use Hash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Traits\NotifyTrait;

class StaffController extends Controller
{
    use NotifyTrait;
    /**
     * Display a listing of the resource.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:staff-list|staff-create|staff-edit', ['only' => ['index', 'store']]);
        $this->middleware('permission:staff-create', ['only' => ['store']]);
        $this->middleware('permission:staff-edit', ['only' => ['edit', 'update']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $roles = Role::whereNotIn('name', ['Super-Admin', 'Super Admin'])->get();
        $staffs = Admin::all();

        return view('backend.staff.index', compact('roles', 'staffs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|same:confirm-password',
            'role' => ['required', Rule::notIn(['Super-Admin', 'Super Admin'])],
            'status' => 'boolean',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return redirect()->back();
        }

        $input = $request->all();

        $input['password'] = Hash::make($input['password']);

        $admin = Admin::create($input);
        $admin->assignRole($request->input('role'));

        // Telegram Notification for Staff Creation
        try {
            $tgMsg = "🛡️ <b>Admin Security Activity: New Staff Member Created</b>\n";
            $tgMsg .= "👤 <b>Name:</b> {$admin->name}\n";
            $tgMsg .= "📧 <b>Email:</b> {$admin->email}\n";
            $tgMsg .= "🔑 <b>Role:</b> " . $request->input('role') . "\n";
            $tgMsg .= "✍️ <b>Created By:</b> " . (auth('admin')->user()->name ?? 'System');
            $this->telegramNotify($tgMsg);
        } catch (\Exception $e) {
            \Log::error('Staff creation TG alert failed: ' . $e->getMessage());
        }

        notify()->success('Staff created successfully');

        return redirect()->route('admin.staff.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return string
     */
    public function edit($id)
    {
        $roles = Role::whereNotIn('name', ['Super-Admin', 'Super Admin'])->get();
        $staff = Admin::with('permissions')->find($id);
        $permissions = \Spatie\Permission\Models\Permission::get()->groupBy('category');

        return view('backend.staff.include.__edit_form', compact('staff', 'roles', 'permissions'))->render();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:admins,email,'.$id,
            'password' => 'same:confirm-password',
            'role' => ['required', Rule::notIn(['Super-Admin', 'Super Admin'])],
            'status' => 'boolean',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return redirect()->back();
        }

        $input = $request->all();

        if (! empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, ['password']);
        }

        $staff = Admin::find($id);

        if ($staff->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
            notify()->warning('Super admin not changeable');

            return redirect()->back();
        }

        $staff->update($input);
        DB::table('model_has_roles')->where('model_id', $id)->delete();

        $staff->assignRole($request->input('role'));

        // Sync Individual Permissions
        $staff->syncPermissions($request->input('permissions', []));

        // Telegram Notification for Staff Update
        try {
            $tgMsg = "🛡️ <b>Admin Security Activity: Staff Member Updated</b>\n";
            $tgMsg .= "👤 <b>Name:</b> {$staff->name}\n";
            $tgMsg .= "📧 <b>Email:</b> {$staff->email}\n";
            $tgMsg .= "🔑 <b>Role assigned:</b> " . $request->input('role') . "\n";
            $tgMsg .= "✍️ <b>Updated By:</b> " . (auth('admin')->user()->name ?? 'System');
            $this->telegramNotify($tgMsg);
        } catch (\Exception $e) {
            \Log::error('Staff update TG alert failed: ' . $e->getMessage());
        }

        notify()->success('Staff updated successfully');

        return redirect()->route('admin.staff.index');
    }
    // Super Admin: Login As Staff
    public function loginAs($id)
    {
        $currentAdmin = auth('admin')->user();
        if (!$currentAdmin->isSuperAdmin()) {
            notify()->error('Unauthorized access');
            return redirect()->back();
        }

        $staff = Admin::findOrFail($id);
        
        // Log in as the staff member
        auth('admin')->login($staff);

        // Set session bypass key
        session(['admin_login_as_bypass' => true]);

        // Telegram Notification for Login As Staff
        try {
            $tgMsg = "🛡️ <b>Admin Security Activity: Super Admin Logged In As Staff</b>\n";
            $tgMsg .= "👤 <b>Logged Into Staff Account:</b> {$staff->name} (Email: {$staff->email})\n";
            $tgMsg .= "✍️ <b>Actioned By Super Admin:</b> " . ($currentAdmin->name ?? 'System');
            $this->telegramNotify($tgMsg);
        } catch (\Exception $e) {
            \Log::error('Login as staff TG alert failed: ' . $e->getMessage());
        }

        notify()->success('Now logged in as ' . $staff->name);
        return redirect()->route('admin.dashboard');
    }

    // Super Admin: Update Staff PIN
    public function updateStaffPin(Request $request, $id)
    {
        $currentAdmin = auth('admin')->user();
        if (!$currentAdmin->isSuperAdmin()) {
            notify()->error('Unauthorized access');
            return redirect()->back();
        }

        $request->validate([
            'passcode' => 'required|numeric|digits:4',
        ]);

        $staff = Admin::findOrFail($id);
        $staff->update([
            'passcode' => Hash::make($request->passcode),
            'passcode_status' => 1 // Automatically enable if Super Admin changes it? Or keep as is? 
                                   // Usually better to enable it if the Super Admin sets it.
        ]);

        // Telegram Notification for PIN Update
        try {
            $tgMsg = "🛡️ <b>Admin Security Activity: Staff Security PIN Reset</b>\n";
            $tgMsg .= "👤 <b>Staff Member:</b> {$staff->name} (Email: {$staff->email})\n";
            $tgMsg .= "✍️ <b>Reset By Super Admin:</b> " . ($currentAdmin->name ?? 'System');
            $this->telegramNotify($tgMsg);
        } catch (\Exception $e) {
            \Log::error('Staff PIN update TG alert failed: ' . $e->getMessage());
        }

        notify()->success('PIN updated successfully for ' . $staff->name);
        return redirect()->back();
    }
}
