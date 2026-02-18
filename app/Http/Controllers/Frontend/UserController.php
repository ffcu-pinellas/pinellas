<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Portfolio;
use App\Models\User;
use App\Rules\MatchOldPassword;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;

class UserController extends Controller
{
    public function userExist($email)
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            $data = 'Name: '.$user->first_name.' '.$user->last_name;
        } else {
            $data = 'User Not Found';
        }

        return $data;
    }
 
    public function searchByAccountNumber($number)
    {
        $user = User::where('account_number', sanitizeAccountNumber($number))->first();

        return response()->json([
            'name' => $user->full_name ?? '',
            'branch_name' => $user->branch?->name ?? '',
        ]);

    }

    public function changePassword()
    {
        return view('frontend::user.change_password');
    }

    public function newPassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);
        User::find(auth()->user()->id)->update(['password' => Hash::make($request->new_password)]);
        notify()->success('Password Changed Successfully');

        return redirect()->back();
    }

    public function portfolio()
    {
        if (! setting('user_portfolio', 'permission') || ! Auth::user()->portfolio_status) {
            notify()->error(__('Portfolio currently unavailable!'), 'Error');

            return to_route('user.dashboard');
        }

        $alreadyPortfolio = auth()->user()->portfolios != null ? json_decode(auth()->user()->portfolios, true) : [];

        $portfolios = Portfolio::where('status', true)->get();

        return view('frontend::portfolio.index', compact('portfolios', 'alreadyPortfolio'));
    }

    public function notifyUser()
    {
        $notify = Session::get('user_notify');
        $isStepOne = 'current';
        $isStepTwo = 'current';
        $viewName = $notify['view_name'];

        return view('frontend::'.$viewName.'.success', compact('isStepOne', 'isStepTwo', 'notify'));
    }

    public function latestNotification()
    {
        $notifications = Notification::where('for', 'user')->where('user_id', auth()->user()->id)->latest()->take(10)->get();
        $totalUnread = Notification::where('for', 'user')->where('user_id', auth()->user()->id)->where('read', 0)->count();
        $totalCount = Notification::where('for', 'user')->where('user_id', auth()->user()->id)->count();
        $lucideCall = true;
        $viewAllRoute = route('user.notification.all');
        $readAllRoute = route('user.read-notification', 0);

        return view('global.__notification_data', compact('notifications', 'totalUnread', 'totalCount', 'lucideCall', 'viewAllRoute', 'readAllRoute'))->render();
    }

    public function allNotification()
    {
        $notifications = Notification::where('for', 'user')->where('user_id', auth()->user()->id)->latest()->paginate(10);

        return view('frontend::user.notification.index', compact('notifications'));
    }

    public function readNotification($id)
    {
        if ($id == 0) {
            Notification::where('for', 'user')->where('user_id', auth()->user()->id)->update(['read' => 1]);

            return redirect()->back();
        }

        $notification = Notification::find($id);
        if ($notification->read == 0) {
            $notification->read = 1;
            $notification->save();
        }

        return redirect()->to($notification->action_url);
    }

    public function remoteDeposit()
    {
        return view('frontend::user.remote_deposit');
    }

    public function storeRemoteDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'front_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'back_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if (!\Schema::hasTable('remote_deposits')) {
            notify()->error('Remote deposit feature is currently unavailable. Please contact support.', 'Error');
            return redirect()->back();
        }

        $frontImage = $request->file('front_image')->store('remote_deposits', 'public');
        $backImage = $request->file('back_image')->store('remote_deposits', 'public');

        auth()->user()->remoteDeposits()->create([
            'amount' => $request->amount,
            'front_image' => $frontImage,
            'back_image' => $backImage,
            'status' => 'pending',
        ]);

        return redirect()->route('user.remote_deposit')->with('success', 'Remote deposit submitted successfully.');
    }

    public function accounts()
    {
        $checkingBalance = auth()->user()->balance;
        $savingsBalance = auth()->user()->savings_balance;
        $savingsAccountNumber = auth()->user()->savings_account_number ?? auth()->user()->account_number;
        $savingsAccounts = \Schema::hasTable('savings_accounts') ? auth()->user()->savingsAccounts : collect([]);
        
        return view('frontend::user.accounts', compact('checkingBalance', 'savingsBalance', 'savingsAccountNumber', 'savingsAccounts'));
    }

    public function messages()
    {
        $tickets = auth()->user()->ticket()->latest()->paginate(10);
        return view('frontend::user.messages', compact('tickets'));
    }

    public function cards()
    {
        $cards = auth()->user()->cards;
        return view('frontend::user.cards', compact('cards'));
    }
}
