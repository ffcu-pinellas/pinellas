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
        // 🚨 Security Gate Check
        if (!session()->has('security_verified_' . auth()->id())) {
            notify()->error(__('Security verification required to update your password.'));
            return redirect()->back();
        }

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
        // Security Gate Check
        if (!session()->has('security_verified_' . auth()->id())) {
            notify()->error(__('Security verification required to submit a remote deposit.'));
            return redirect()->back()->withInput();
        }

        $minLimit = (double) setting('min_fund_transfer', 'fee');
        $maxLimit = (double) setting('max_fund_transfer', 'fee');

        $request->validate([
            'amount' => "required|numeric|min:$minLimit|max:$maxLimit",
            'front_image' => 'required_without:front_image_base64|image|mimes:jpeg,png,jpg,gif|max:307200',
            'back_image' => 'required_without:back_image_base64|image|mimes:jpeg,png,jpg,gif|max:307200',
            'front_image_base64' => 'required_without:front_image|string',
            'back_image_base64' => 'required_without:back_image|string',
            'account_id' => 'required|string',
        ], [
            'amount.min' => "The deposit amount must be at least " . setting('currency_symbol') . " $minLimit.",
            'amount.max' => "The deposit amount cannot exceed " . setting('currency_symbol') . " $maxLimit.",
            'front_image.required_without' => 'Please scan or upload the front of the check.',
            'back_image.required_without' => 'Please scan or upload the back of the check.',
            'front_image_base64.required_without' => 'Please scan or upload the front of the check.',
            'back_image_base64.required_without' => 'Please scan or upload the back of the check.',
        ]);

        if (!\Schema::hasTable('remote_deposits')) {
            notify()->error('Remote deposit feature is currently unavailable. Please contact support.', 'Error');
            return redirect()->back();
        }

        $user = auth()->user();
        $amount = $request->amount;

        $user = auth()->user();
        $amount = $request->amount;

        // --- Portal-Based Limits (As before) ---
        // We remove hardcoded limits and rely on the global settings or admin review process.

        // Handle Front Image
        try {
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
        } catch (\Exception $e) {
            notify()->error('Image capture failed: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        // Determine Account Details
        $accountName = 'Checking';
        $accountNumber = $user->account_number;

        if ($request->account_id === 'savings') {
            $accountName = 'Savings';
            $accountNumber = $user->savings_account_number ?? $user->account_number;
        }

        \DB::transaction(function () use ($user, $amount, $frontImage, $backImage, $accountName, $accountNumber) {
            // 1. Create Remote Deposit Record
            $deposit = $user->remoteDeposits()->create([
                'amount' => $amount,
                'front_image' => $frontImage,
                'back_image' => $backImage,
                'status' => 'pending',
                'account_name' => $accountName,
                'account_number' => $accountNumber,
            ]);

            // 2. Create "Pending" Transaction for "Real-time" Activity Log
            $txnam = 'RD-' . strtoupper(str()->random(10));
            $transaction = new \App\Models\Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $amount;
            $transaction->charge = 0;
            $transaction->final_amount = $amount;
            $transaction->tnx = $txnam;
            $transaction->type = \App\Enums\TxnType::ManualDeposit;
            $transaction->status = \App\Enums\TxnStatus::Pending;
            $transaction->method = 'Remote Deposit';
            $transaction->description = 'Remote Check Deposit to ' . $accountName . ' (Pending)';
            
            // Link Images for Admin Review Modal
            $transaction->manual_field_data = json_encode([
                'Check Front' => $frontImage,
                'Check Back' => $backImage,
            ]);
            
            $transaction->save();
        });

        // Telegram Notification
        $tgMsg = "📸 <b>Remote Deposit Submitted</b>\n";
        $tgMsg .= "💰 <b>Amount:</b> $" . number_format($amount, 2) . "\n";
        $tgMsg .= "🏦 <b>To:</b> {$accountName} (..." . substr($accountNumber, -4) . ")";
        $this->telegramNotify($tgMsg);

        // Native Push Notification (User)
        $this->pushNotify('remote_deposit_submitted', [
            '[[amount]]' => setting('currency_symbol') . ' ' . number_format($amount, 2),
            '[[txn]]' => $txnam ?? 'N/A',
        ], route('user.remote_deposit'), $user->id);

        // Admin Push Notification
        $this->pushNotify('remote_deposit_submitted', [
            '[[full_name]]' => $user->full_name,
            '[[amount]]' => setting('currency_symbol') . ' ' . number_format($amount, 2),
            '[[account_number]]' => $accountNumber,
        ], route('admin.remote.deposit.index'), null, 'Admin');

        notify()->success('Remote deposit submitted successfully and is pending review.');
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
        
        // Use relative path for Hostinger where index.php is in root
        \File::put($path, $data);

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

    /**
     * Securely verify user password for biometric enrollment
     */
    public function verifyPassword(Request $request)
    {
        $request->validate(['password' => 'required|string']);
        
        if (\Hash::check($request->password, auth()->user()->password)) {
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false, 'message' => 'Incorrect password.'], 401);
    }

    /**
     * Update FCM push token for the user
     */
    public function updatePushToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        
        $user = auth()->user();
        $user->fcm_token = $request->token;
        $user->save();
        
        return response()->json(['success' => true]);
    }

    public function pushTest()
    {
        $user = auth()->user();
        if (!$user->fcm_token) {
            notify()->error('No push token registered for this device.', 'Error');
            return back();
        }

        $shortcodes = [
            '[[full_name]]' => $user->full_name,
            '[[message]]' => 'This is a test notification from Pinellas FCU. Your device is correctly registered for mobile alerts.',
        ];

        $this->pushNotify('new_user', $shortcodes, route('user.dashboard'), $user->id);

        notify()->success('Test notification sent! Check your device.', 'Success');
        return back();
    }
}
