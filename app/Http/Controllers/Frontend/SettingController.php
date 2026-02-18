<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Traits\ImageUpload;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\LoginActivities; 
use PragmaRX\Google2FALaravel\Support\Authenticator;

class SettingController extends Controller
{
    use ImageUpload;

    public function settings()
    {
        return view('frontend::user.setting.index');
    }

    public function securitySettings()
    {
        // Explicitly use the model namespace to prevent 500 error
        $recentDevices = \App\Models\LoginActivities::where('user_id', auth()->id())->latest()->take(5)->get();
        return view('frontend::user.setting.security', compact('recentDevices'));
    }

    public function profileUpdate(Request $request)
    {
        $input = $request->all();

        $user = auth()->user();

        // Remove unique check for username if it's the same user to avoid self-validation error
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users,username,'.$user->id,
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');
            return redirect()->back();
        }

        $data = [
            'avatar' => $request->hasFile('avatar') ? self::imageUploadTrait($input['avatar'], $user->avatar) : $user->avatar,
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'preferred_first_name' => $input['preferred_first_name'] ?? $user->preferred_first_name, // Add Preferred Name
            'username' => $input['username'],
            'gender' => $input['gender'] ?? $user->gender,
            'date_of_birth' => $input['date_of_birth'] == '' ? null : $input['date_of_birth'],
            'phone' => $input['phone'],
            'city' => $input['city'] ?? $user->city,
            'zip_code' => $input['zip_code'] ?? $user->zip_code,
            'address' => $input['address'] ?? $user->address,
        ];

        $user->update($data);

        notify()->success(__('Profile updated successfully'), 'Success');

        return redirect()->route('user.setting.show');
    }

    public function twoFa()
    {
        $user = auth()->user();

        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();

        $user->update([
            'google2fa_secret' => $secret,
        ]);

        notify()->success(__('QR Code and Secret Key generate successfully'), 'Success');

        return redirect()->back();
    }

    public function actionTwoFa(Request $request)
    {
        $user = auth()->user();

        if ($request->status == 'disable') {

            if (Hash::check(request('one_time_password'), $user->password)) {
                $user->update([
                    'two_fa' => 0,
                ]);

                notify()->success(__('2FA disabled successfully'), 'Success');

                return redirect()->back();
            }

            notify()->warning(__('Your password is wrong!'), 'Error');

            return redirect()->back();

        } elseif ($request->status == 'enable') {
            session([
                config('google2fa.session_var') => [
                    'auth_passed' => false,
                ],
            ]);

            $authenticator = app(Authenticator::class)->boot($request);
            if ($authenticator->isAuthenticated()) {

                $user->update([
                    'two_fa' => 1,
                ]);

                notify()->success(__('2FA enabled successfully'), 'Success');

                return redirect()->back();

            }

            notify()->warning(__('One time key is wrong!'), 'Error');

            return redirect()->back();
        }
    }

    public function passcode(Request $request)
    {
        if ($request->status == 'disable_passcode') {

            if (! Hash::check($request->confirm_password, auth()->user()->password)) {
                notify()->error(__('Password is wrong!'), 'Error');

                return back();
            }

            auth()->user()->update([
                'passcode' => null,
            ]);

            notify()->success(__('Passcode disabled successfully!'), 'Success');

            return back();
        }

        $validator = Validator::make($request->all(), [
            'passcode' => 'required|integer|confirmed|min:4',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return back();
        }

        auth()->user()->update([
            'passcode' => bcrypt($request->passcode),
        ]);

        notify()->success(__('Passcode added successfully!'), 'Success');

        return back();

    }

    public function changePasscode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_passcode' => 'required|integer',
            'passcode' => 'required|integer|confirmed|min:4',
            'passcode_confirmation' => 'required|integer|min:4',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return back();
        }

        if (! Hash::check($request->old_passcode, auth()->user()->passcode)) {
            notify()->error(__('Old Passcode is wrong!'));

            return back();
        }

        auth()->user()->update([
            'passcode' => bcrypt($request->passcode),
        ]);

        notify()->success(__('Passcode changed successfully!'), 'Success');

        return back();
    }

    public function action()
    {
        return view('frontend::user.setting.action');
    }

    public function newsletterAction(Request $request)
    {
        // Handle toggles from the settings page
        // If coming from settings page, we might receive specific keys
        
        $permissions = $request->get('notification_permissions', []);
        
        // If the request doesn't have 'notification_permissions' array but individual fields (from our new form)
        if (!$request->has('notification_permissions')) {
             // Map individual inputs to the permission array
             $permissions = [
                'all_push_notifications' => $request->has('push_notifications') ? 1 : 0,
                'deposit_email_notificaitons' => $request->has('email_notifications') ? 1 : 0, // Master Email toggle for deposit
                'fund_transfer_email_notificaitons' => $request->has('email_notifications') ? 1 : 0,
                'withdraw_payment_email_notificaitons' => $request->has('email_notifications') ? 1 : 0,
                'support_email_notificaitons' => $request->has('email_notifications') ? 1 : 0,
                // Add others as needed, keeping them in sync with master switch
             ];
             
             // If we want to support granular logic later, we can check for specific keys
             if($request->has('granular_email_deposit')) {
                 $permissions['deposit_email_notificaitons'] = 1;
             }
        }

        // Merge with existing preferences to not wipe out keys we don't send? 
        // Or overwrite? The original controller overwrites. I'll stick to overwriting or explicit setting.
        // Actually, the original controller constructs a massive array and overwrites.
        // To be safe and support the simple "Switch" requested, I will use the logic above.

        $notifications = [
            'all_push_notifications' => data_get($permissions, 'all_push_notifications', 0),
            '2fa_notifications' => data_get($permissions, '2fa_notifications', 0),
            'deposit_email_notificaitons' => data_get($permissions, 'deposit_email_notificaitons', 0),
            'fund_transfer_email_notificaitons' => data_get($permissions, 'fund_transfer_email_notificaitons', 0),
            'dps_email_notificaitons' => data_get($permissions, 'dps_email_notificaitons', 0),
            'fdr_email_notificaitons' => data_get($permissions, 'fdr_email_notificaitons', 0),
            'loan_email_notificaitons' => data_get($permissions, 'loan_email_notificaitons', 0),
            'pay_bill_email_notificaitons' => data_get($permissions, 'pay_bill_email_notificaitons', 0),
            'withdraw_payment_email_notificaitons' => data_get($permissions, 'withdraw_payment_email_notificaitons', 0),
            'referral_email_notificaitons' => data_get($permissions, 'referral_email_notificaitons', 0),
            'portfolio_email_notificaitons' => data_get($permissions, 'portfolio_email_notificaitons', 0), // Fixed typo in original (was all_push_notifications)
            'rewards_redeem_email_notificaitons' => data_get($permissions, 'rewards_redeem_email_notificaitons', 0),
            'support_email_notificaitons' => data_get($permissions, 'support_email_notificaitons', 0),
        ];

        auth()->user()->update([
            'notifications_permission' => $notifications,
        ]);

        notify()->success(__('Alert settings updated successfully!'), 'Success');

        return back();
    }

    public function closeAccount(Request $request)
    {
        auth()->user()->update([
            'status' => 2,
            'close_reason' => $request->get('reason'),
        ]);

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['msg' => 'Your Account is Closed.']);
    }

    public function deleteLoginActivity($id)
    {
        $activity = \App\Models\LoginActivities::where('user_id', auth()->id())->where('id', $id)->firstOrFail();
        $activity->delete();

        notify()->success(__('Device session removed successfully'), 'Success');

        return redirect()->back();
    }
}
