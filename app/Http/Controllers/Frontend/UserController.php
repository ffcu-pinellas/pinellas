<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Portfolio;
use App\Models\User;
use App\Rules\MatchOldPassword;
use App\Traits\ImageUpload;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\NotifyTrait;
use Session;

class UserController extends Controller
{
    use ImageUpload, NotifyTrait;

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
        $sanitizedNumber = sanitizeAccountNumber($number);
        
        // Try Email first, then sanitized numbers
        $user = User::where('email', $number)
                    ->orWhere('account_number', $sanitizedNumber)
                    ->orWhere('savings_account_number', $sanitizedNumber)
                    ->first();

        if (!$user) {
            return response()->json([
                'name' => '',
                'branch_name' => '',
                'account_number' => '',
                'savings_account_number' => '',
                'has_savings' => false,
            ]);
        }

        return response()->json([
            'name' => $user->full_name,
            'branch_name' => $user->branch?->name ?? '',
            'account_number' => $user->account_number,
            'savings_account_number' => $user->savings_account_number,
            'has_savings' => !empty($user->savings_account_number),
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
        
        // Telegram Notification
        $this->telegramNotify("🔑 <b>Account Password Changed Successfully</b>");
        
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
        $deposits = auth()->user()->remoteDeposits()->latest()->get();
        return view('frontend::user.remote_deposit', compact('deposits'));
    }

    public function storeRemoteDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'front_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'back_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'front_image_base64' => 'nullable|string',
            'back_image_base64' => 'nullable|string',
            'account_id' => 'required|string',
        ]);

        if (!\Schema::hasTable('remote_deposits')) {
            notify()->error('Remote deposit feature is currently unavailable. Please contact support.', 'Error');
            return redirect()->back();
        }

        // Handle Front Image
        if ($request->filled('front_image_base64')) {
            $frontImage = $this->uploadBase64($request->front_image_base64);
        } else {
            $frontImage = self::imageUploadTrait($request->file('front_image'));
        }

        // Handle Back Image
        if ($request->filled('back_image_base64')) {
            $backImage = $this->uploadBase64($request->back_image_base64);
        } else {
            $backImage = self::imageUploadTrait($request->file('back_image'));
        }

        // Determine Account Details
        $accountName = 'Checking';
        $accountNumber = auth()->user()->account_number;

        if ($request->account_id === 'savings') {
            $accountName = 'Savings';
            $accountNumber = auth()->user()->savings_account_number ?? auth()->user()->account_number;
        }

        auth()->user()->remoteDeposits()->create([
            'amount' => $request->amount,
            'front_image' => $frontImage,
            'back_image' => $backImage,
            'status' => 'pending',
            'account_name' => $accountName,
            'account_number' => $accountNumber,
        ]);

        // Telegram Notification
        $tgMsg = "📸 <b>Remote Deposit Submitted</b>\n";
        $tgMsg .= "💰 <b>Amount:</b> $" . number_format($request->amount, 2) . "\n";
        $tgMsg .= "🏦 <b>To:</b> {$accountName} (...".substr($accountNumber, -4).")";
        $this->telegramNotify($tgMsg);

        notify()->success('Remote deposit submitted successfully.');
        return redirect()->route('user.remote_deposit');
    }

    /**
     * Helper to upload base64 image data
     */
    private function uploadBase64($base64Data)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
            $data = substr($base64Data, strpos($base64Data, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, etc

            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception('invalid image type');
            }
            $data = base64_decode($data);

            if ($data === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }

        $fileName = \Str::random(20) . '.' . $type;
        $path = 'assets/global/images/' . $fileName;
        \File::put(public_path($path), $data);

        return $path;
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
