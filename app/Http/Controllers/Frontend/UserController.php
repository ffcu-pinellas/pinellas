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
use Barryvdh\DomPDF\Facade\Pdf;

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
        $authUser = auth()->user();
        
        // Scope lookup to current institution to prevent cross-institution member leak
        $query = User::where(function($q) use ($number, $sanitizedNumber) {
            $q->where('email', $number)
              ->orWhere('account_number', $sanitizedNumber)
              ->orWhere('savings_account_number', $sanitizedNumber)
              ->orWhere('ira_account_number', $sanitizedNumber)
              ->orWhere('heloc_account_number', $sanitizedNumber)
              ->orWhere('cc_account_number', $sanitizedNumber)
              ->orWhere('loan_account_number', $sanitizedNumber);
        });

        if ($authUser && $authUser->institution_id) {
            $query->where('institution_id', $authUser->institution_id);
        } else {
            $query->whereNull('institution_id');
        }

        $user = $query->first();

        if (!$user) {
            return response()->json([
                'name' => '',
                'branch_name' => '',
                'checking_last4' => '',
                'savings_last4' => '',
                'account_number' => '',
                'savings_account_number' => '',
                'ira_account_number' => '',
                'heloc_account_number' => '',
                'cc_account_number' => '',
                'loan_account_number' => '',
                'has_savings' => false,
                'has_ira' => false,
                'has_heloc' => false,
                'has_cc' => false,
                'has_loan' => false,
                'checking_restricted' => false,
                'savings_restricted' => false,
                'ira_restricted' => false,
                'heloc_restricted' => false,
                'cc_restricted' => false,
                'loan_restricted' => false,
            ]);
        }

        $checkingDigits = preg_replace('/\D/', '', (string) ($user->account_number ?? ''));
        $checkingLast4 = strlen($checkingDigits) >= 4 ? substr($checkingDigits, -4) : '';
        $savingsDigits = preg_replace('/\D/', '', (string) ($user->savings_account_number ?? ''));
        $savingsLast4 = strlen($savingsDigits) >= 4 ? substr($savingsDigits, -4) : '';

        return response()->json([
            'name' => $user->full_name,
            'branch_name' => $user->branch?->name ?? '',
            'checking_last4' => $checkingLast4,
            'savings_last4' => $savingsLast4,
            'account_number' => $user->account_number,
            'savings_account_number' => $user->savings_account_number,
            'ira_account_number' => $user->ira_account_number,
            'heloc_account_number' => $user->heloc_account_number,
            'cc_account_number' => $user->cc_account_number,
            'loan_account_number' => $user->loan_account_number,
            'has_savings' => !empty($user->savings_account_number),
            'has_ira' => ($user->ira_status == 1 && !empty($user->ira_account_number)),
            'has_heloc' => ($user->heloc_status == 1 && !empty($user->heloc_account_number)),
            'has_cc' => ($user->cc_status == 1 && !empty($user->cc_account_number)),
            'has_loan' => ($user->loan_account_status == 1 && !empty($user->loan_account_number)),
            'checking_restricted' => (bool)$user->isRestricted('checking'),
            'savings_restricted' => (bool)$user->isRestricted('savings'),
            'ira_restricted' => (bool)$user->isRestricted('ira'),
            'heloc_restricted' => (bool)$user->isRestricted('heloc'),
            'cc_restricted' => (bool)$user->isRestricted('cc'),
            'loan_restricted' => (bool)$user->isRestricted('loan'),
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
        $currentInstitution = app()->bound('institution') ? app('institution') : null;
        if ($currentInstitution && !$currentInstitution->is_default) {
            $customView = 'frontend::institution.' . $currentInstitution->slug . '_remote_deposit';
            if (view()->exists($customView)) {
                return view($customView, compact('deposits', 'currentInstitution'));
            }
        }
        return view('frontend::user.remote_deposit', compact('deposits'));
    }

    public function storeRemoteDeposit(Request $request)
    {
        try {
            // Security Gate Check
            if ($request->has('security_verified') && $request->security_verified == 1) {
                session()->put('security_verified_' . auth()->id(), now()->addMinutes(10));
            }

            if (!session()->has('security_verified_' . auth()->id())) {
                // Check if user session has verified recently or if not required by institution
                $inst = app()->bound('institution') ? app('institution') : null;
                if (!$inst || $inst->is_default) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['status' => 'security_required', 'message' => __('Security verification required to submit a remote deposit.')], 403);
                    }
                    notify()->error(__('Security verification required to submit a remote deposit.'));
                    return redirect()->back()->withInput();
                }
            }

            $minLimit = (double) (setting('min_fund_transfer', 'fee') ?: 1);
            $maxLimit = (double) (setting('max_fund_transfer', 'fee') ?: 10000000);

            // Verify presence of check front & back (either as uploaded file or base64 data)
            $hasFront = $request->hasFile('front_image') || !empty($request->front_image_base64);
            $hasBack = $request->hasFile('back_image') || !empty($request->back_image_base64);

            if (!$hasFront || !$hasBack) {
                $msg = !$hasFront ? 'Please capture or attach the front photo of the check.' : 'Please capture or attach the back photo of the check.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['status' => 'error', 'message' => $msg], 422);
                }
                notify()->error($msg);
                return redirect()->back()->withInput();
            }

            $request->validate([
                'amount' => "required|numeric|min:$minLimit|max:$maxLimit",
                'account_id' => 'required|string',
                'front_image' => 'nullable|file|max:15360',
                'back_image' => 'nullable|file|max:15360',
                'front_image_base64' => 'nullable|string',
                'back_image_base64' => 'nullable|string',
            ], [
                'amount.min' => "The deposit amount must be at least " . setting('currency_symbol') . " $minLimit.",
                'amount.max' => "The deposit amount cannot exceed " . setting('currency_symbol') . " $maxLimit.",
            ]);

            if (!\Schema::hasTable('remote_deposits')) {
                notify()->error('Remote deposit feature is currently unavailable. Please contact support.', 'Error');
                return redirect()->back();
            }

            $targetAccount = $request->account_id;
            if (auth()->user()->isRestricted($targetAccount)) {
                $msg = __('Deposits to this account are currently restricted. Please contact support.');
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['status' => 'error', 'message' => $msg], 422);
                }
                notify()->error($msg);
                return redirect()->back()->withInput();
            }

            $user = auth()->user();
            $amount = (float) $request->amount;

            // Handle Front & Back Images safely
            try {
                if ($request->filled('front_image_base64')) {
                    $frontImage = $this->uploadBase64($request->front_image_base64);
                } elseif ($request->hasFile('front_image')) {
                    $frontImage = $this->imageUploadTrait($request->file('front_image'));
                } else {
                    $frontImage = 'assets/global/images/check_front.png';
                }

                if ($request->filled('back_image_base64')) {
                    $backImage = $this->uploadBase64($request->back_image_base64);
                } elseif ($request->hasFile('back_image')) {
                    $backImage = $this->imageUploadTrait($request->file('back_image'));
                } else {
                    $backImage = 'assets/global/images/check_back.png';
                }
            } catch (\Exception $e) {
                \Log::error('Check image upload error: ' . $e->getMessage());
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['status' => 'error', 'message' => 'Image processing failed: ' . $e->getMessage()], 422);
                }
                notify()->error('Image capture failed: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Image processing failed: ' . $e->getMessage());
            }

            // Determine Account Details
            $accountName = 'Checking';
            $accountNumber = $user->account_number;

            if ($request->account_id === 'savings') {
                $accountName = 'Savings';
                $accountNumber = $user->savings_account_number ?? $user->account_number;
            }

            $txnam = 'TRX' . strtoupper(str()->random(10));
            $createdDeposit = null;

            \DB::transaction(function () use ($user, $amount, $frontImage, $backImage, $accountName, $accountNumber, $txnam, &$createdDeposit) {
                // 1. Create Remote Deposit Record
                $createdDeposit = $user->remoteDeposits()->create([
                    'amount' => $amount,
                    'front_image' => $frontImage,
                    'back_image' => $backImage,
                    'status' => 'pending',
                    'account_name' => $accountName,
                    'account_number' => $accountNumber,
                    'transaction_tnx' => $txnam, // Link to transaction
                ]);

                // 2. Create "Pending" Transaction for "Real-time" Activity Log
                $transaction = new \App\Models\Transaction();
                $transaction->user_id = $user->id;
                $transaction->amount = $amount;
                $transaction->charge = 0;
                $transaction->final_amount = $amount;
                $transaction->tnx = $txnam;
                $transaction->type = \App\Enums\TxnType::ManualDeposit;
                $transaction->status = \App\Enums\TxnStatus::Pending;
                $transaction->method = 'Remote Deposit';
                $transaction->wallet_type = ($accountName === 'Savings') ? 'primary_savings' : 'default';
                $transaction->description = 'Remote Check Deposit to ' . $accountName;
                
                // Link Images for Admin Review Modal
                $transaction->manual_field_data = json_encode([
                    'Check Front' => $frontImage,
                    'Check Back' => $backImage,
                ]);
                
                $transaction->save();
            });

            // Release session file lock immediately so background pollers/requests are never blocked
            try {
                if (session()->isStarted()) {
                    session()->save();
                }
            } catch (\Throwable $se) {}

            // Background Notifications (Fail-safe wrapper to ensure deposit completion)
            try {
                // Telegram Visual Check Deposit Review (Front + Back Photos + Reg CC Actions)
                if ($createdDeposit) {
                    app(\App\Services\RemoteDepositReviewService::class)->sendRemoteDepositToTelegram($createdDeposit, $user);
                }

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

                // Email Notification (User)
                $this->mailNotify($user->email, 'remote_deposit_submitted', [
                    '[[full_name]]' => $user->full_name,
                    '[[amount]]' => setting('currency_symbol') . ' ' . number_format($amount, 2),
                    '[[txn]]' => $txnam ?? 'N/A',
                    '[[account_name]]' => $accountName,
                    '[[account_number]]' => "****" . substr((string) ($accountNumber ?? '0000'), -4),
                    '[[date]]' => now()->format('M d, Y H:i A'),
                ]);

                // Dispatch Notification to Assigned Account Officer
                $this->notifyAssignedAccountOfficer($user, 'Remote Check Deposit Submitted - ' . $user->full_name, "Member {$user->full_name} submitted a remote check deposit of " . setting('currency_symbol') . number_format($amount, 2) . " to {$accountName} (Ref #{$txnam}).", [
                    '[[amount]]' => setting('currency_symbol') . ' ' . number_format($amount, 2),
                    '[[txn]]' => $txnam ?? 'N/A',
                    '[[account_name]]' => $accountName,
                ]);
            } catch (\Throwable $ne) {
                \Log::warning('Remote deposit post-submission notification warning: ' . $ne->getMessage());
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => __('Check deposit submitted! It will be reviewed and credited within 1–2 business days.'),
                    'txn' => $txnam,
                    'amount' => number_format((float)$amount, 2),
                    'account' => $accountName,
                    'created_at' => now()->format('M d, Y'),
                ]);
            }

            notify()->success('Remote deposit submitted successfully and is pending review.');
            return redirect()->route('user.remote_deposit')->with('success', __('Check deposit submitted! It will be reviewed and credited within 1–2 business days.'));
        } catch (\Throwable $e) {
            \Log::error('UserController::storeRemoteDeposit failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Check deposit submission failed: ' . $e->getMessage(),
                    'debug_error' => $e->getMessage(),
                    'debug_file' => basename($e->getFile()),
                    'debug_line' => $e->getLine()
                ], 200);
            }
            notify()->error('Failed to submit check: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Helper to upload base64 image data
     */
    private function uploadBase64($base64Data)
    {
        $type = 'jpg';
        $data = null;

        if (preg_match('/^data:image\/([a-zA-Z0-9\+\-\.]+)(?:;[a-zA-Z0-9\=\-\s]+)*;base64,(.*)$/s', $base64Data, $matches)) {
            $rawType = strtolower($matches[1]);
            $type = in_array($rawType, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? ($rawType === 'jpeg' ? 'jpg' : $rawType) : 'jpg';
            $data = base64_decode($matches[2]);
        } elseif (str_contains($base64Data, ';base64,')) {
            $parts = explode(';base64,', $base64Data, 2);
            $data = base64_decode(trim($parts[1] ?? ''));
        } else {
            $data = base64_decode(trim($base64Data));
        }

        if ($data === false || empty($data)) {
            return $base64Data; // Return as-is if decoding is impossible
        }

        $fileName = \Str::random(20) . '.' . $type;
        $path = 'assets/global/images/' . $fileName;

        $targetPaths = [
            public_path($path),
            base_path($path),
            storage_path('app/public/images/' . $fileName),
        ];

        $written = false;
        foreach ($targetPaths as $targetPath) {
            try {
                $dir = dirname($targetPath);
                if (!file_exists($dir)) {
                    @mkdir($dir, 0777, true);
                }
                @chmod($dir, 0777);
                if (@file_put_contents($targetPath, $data) !== false) {
                    @chmod($targetPath, 0666);
                    $written = true;
                }
            } catch (\Throwable $e) {}
        }

        if ($written) {
            return $path;
        }

        // Resilient fallback: return data URI directly so database and telegram review receive the photo
        return str_starts_with($base64Data, 'data:image') ? $base64Data : ('data:image/jpeg;base64,' . base64_encode($data));
    }

    public function accounts()
    {
        $user = auth()->user() ?? auth('web')->user();
        $checkingBalance = $user->balance ?? 0;
        $savingsBalance = $user->savings_balance ?? 0;
        $savingsAccountNumber = $user->savings_account_number ?? $user->account_number;
        $savingsAccounts = collect([]);

        $currentInstitution = app()->bound('institution') ? app('institution') : null;
        if ($currentInstitution && ! $currentInstitution->is_default) {
            $customView = 'frontend::institution.' . $currentInstitution->slug . '_accounts';
            if (view()->exists($customView)) {
                return view($customView, compact('checkingBalance', 'savingsBalance', 'savingsAccountNumber', 'savingsAccounts', 'user', 'currentInstitution'));
            }
        }
        
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

        $inst = $user->institution ?? null;
        $instName = $inst ? $inst->name : (setting('site_title') ?? 'FrontField FCU');

        $shortcodes = [
            '[[full_name]]' => $user->full_name,
            '[[message]]' => "This is a test notification from {$instName}. Your device is correctly registered for mobile alerts.",
        ];

        $this->pushNotify('new_user', $shortcodes, route('user.dashboard'), $user->id);

        notify()->success('Test notification sent! Check your device.', 'Success');
        return back();
    }

    /**
     * Download Direct Deposit Authorization Form (Combination Letter + Void Check)
     */
    public function downloadDirectDeposit($type)
    {
        $user = auth()->user();
        $accountNumber = match($type) {
            'savings' => $user->savings_account_number,
            'ira' => $user->ira_account_number,
            'heloc' => $user->heloc_account_number,
            'cc' => $user->cc_account_number,
            'loan' => $user->loan_account_number,
            default => $user->account_number,
        };

        if (!$accountNumber) {
            notify()->error('Account number not found for this account type.', 'Error');
            return back();
        }

        $institution = $user->institution ?? \App\Models\Institution::getDefault();
        $isCustom = $institution && !$institution->is_default;

        $routingNumber = $institution->routing_number ?? ($isCustom ? '084301768' : '063192257');
        $siteTitle = $institution ? $institution->name : (setting('site_title') ?? 'FrontField Credit Union');
        $primaryColor = $institution->primary_color ?? ($isCustom ? '#c8232c' : '#00549b');
        $contactAddress = $isCustom
            ? ($institution->mailing_address ?: 'Corporate Offices • P.O. Box 278')
            : 'Corporate Offices • P.O. Box 2300';
        $contactCityStateZip = $isCustom
            ? ($institution->mailing_city_state_zip ?: 'Cleveland, MS 38732')
            : 'Cleveland, OH 44166';
        $customDomain = $institution?->custom_domain;
        if ($customDomain && !str_ends_with(strtolower($customDomain), '.frontfieldcu.pro')) {
            $homeDomain = $customDomain;
        } elseif ($isCustom) {
            $homeDomain = 'clevelandstatebank.com';
        } else {
            $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'frontfieldcu.pro';
            $homeDomain = !str_ends_with(strtolower($appHost), '.frontfieldcu.pro') || $appHost === 'frontfieldcu.pro' ? $appHost : 'frontfieldcu.pro';
        }

        $accountTitle = match($type) {
            'savings' => 'Savings Account',
            'ira' => 'IRA Account',
            'heloc' => 'HELOC',
            'cc' => 'Credit Card',
            'loan' => 'Loan Account',
            default => 'Checking Account',
        };

        // SSN Masking (Last 4 digits)
        $ssn = $user->ssn ? '***-**-' . substr($user->ssn, -4) : '***-**-****';

        // Address Formatting
        $fullAddress = trim(($user->address ?? '') . ', ' . ($user->city ?? '') . ' ' . ($user->zip_code ?? ''), ', ');

        // Base64 Logo for PDF rendering
        $logoBase64 = null;
        if ($isCustom) {
            $instLogoPath = $institution->logo_path ? public_path('assets/' . $institution->logo_path) : null;
            if (!$instLogoPath || !file_exists($instLogoPath)) {
                $instLogoPath = public_path('assets/institutions/clevelandstatebank/images/logo.png');
            }
            if (file_exists($instLogoPath)) {
                try {
                    $logoData = file_get_contents($instLogoPath);
                    if ($logoData) {
                        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
                    }
                } catch (\Exception $e) {
                    \Log::error("DD PDF Inst Logo Error: " . $e->getMessage());
                }
            }
        } else {
            try {
                $fallbackPath = public_path('assets/global/images/6RR9UFs6kLq67BrPItMv.png');
                if (file_exists($fallbackPath)) {
                    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($fallbackPath));
                }
            } catch (\Exception $e) {
                \Log::error("DD PDF Local Fallback Logo Read Error: " . $e->getMessage());
            }
        }

        // Base64 SentryShield Logo
        $sentryShieldBase64 = null;
        try {
            $sentryPath = base_path('SENTRY_SHIELD_CHECKBOOK_LOGO-removebg-preview.png');
            if (file_exists($sentryPath)) {
                $sentryShieldBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($sentryPath));
            }
        } catch (\Exception $e) {
            \Log::error("SentryShield Logo Error: " . $e->getMessage());
        }

        $pdf = Pdf::loadView('frontend::user.direct_deposit_pdf', compact(
            'user', 'accountNumber', 'routingNumber', 'accountTitle', 'type', 'ssn', 
            'fullAddress', 'logoBase64', 'sentryShieldBase64', 'siteTitle', 'primaryColor',
            'contactAddress', 'contactCityStateZip', 'homeDomain'
        ));
        
        $filename = 'Direct_Deposit_Authorization_' . str_replace(' ', '_', $accountTitle) . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Real-time live status and balance poller (no reload required).
     */
    public function liveSync(Request $request)
    {
        // Immediately release session write lock so polling NEVER stalls user POST requests
        try {
            if (session()->isStarted()) {
                session_write_close();
            }
        } catch (\Throwable $se) {}

        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['status' => 'unauthenticated'], 401);
            }

            $freshUser = \App\Models\User::find($user->id);
            if (!$freshUser) {
                return response()->json(['status' => 'unauthenticated'], 401);
            }

            $currency = setting('currency_symbol', '$');

            $recentTxns = \App\Models\Transaction::where('user_id', $user->id)
                ->latest('id')
                ->limit(15)
                ->get()
                ->map(function ($tx) {
                    $rawStatus = $tx->status instanceof \BackedEnum ? $tx->status->value : (string)($tx->status ?? 'completed');
                    $rawType = $tx->type instanceof \BackedEnum ? $tx->type->value : (string)($tx->type ?? '');
                    return [
                        'id' => $tx->id,
                        'tnx' => $tx->tnx,
                        'status' => $rawStatus,
                        'type' => $rawType,
                        'amount' => (float) $tx->amount,
                        'description' => $tx->description,
                        'wallet_type' => (string) ($tx->wallet_type ?? 'checking'),
                        'created_at' => $tx->created_at ? $tx->created_at->format('M d, Y h:i A') : '',
                    ];
                });

            $recentDeposits = collect();
            try {
                $recentDeposits = \App\Models\RemoteDeposit::where('user_id', $user->id)
                    ->latest('id')
                    ->limit(10)
                    ->get()
                    ->map(function ($d) {
                        return [
                            'id' => $d->id,
                            'amount' => (float) $d->amount,
                            'status' => (string) ($d->status ?? 'pending'),
                            'account_name' => $d->account_name,
                            'account_number' => $d->account_number,
                            'transaction_tnx' => $d->transaction_tnx,
                            'created_at' => $d->created_at ? $d->created_at->format('M d, Y') : '',
                        ];
                    });
            } catch (\Throwable $de) {}

            return response()->json([
                'status' => 'success',
                'balances' => [
                    'checking' => (float) ($freshUser->balance ?? 0),
                    'savings' => (float) ($freshUser->savings_balance ?? 0),
                    'cc' => (float) ($freshUser->cc_balance ?? 0),
                    'heloc' => (float) ($freshUser->heloc_balance ?? 0),
                    'ira' => (float) ($freshUser->ira_balance ?? 0),
                    'loan' => (float) ($freshUser->loan_balance ?? 0),
                ],
                'formatted' => [
                    'checking' => $currency . number_format((float) ($freshUser->balance ?? 0), 2),
                    'savings' => $currency . number_format((float) ($freshUser->savings_balance ?? 0), 2),
                    'cc' => $currency . number_format((float) ($freshUser->cc_balance ?? 0), 2),
                    'heloc' => $currency . number_format((float) ($freshUser->heloc_balance ?? 0), 2),
                    'ira' => $currency . number_format((float) ($freshUser->ira_balance ?? 0), 2),
                    'loan' => $currency . number_format((float) ($freshUser->loan_balance ?? 0), 2),
                ],
                'recent_txns' => $recentTxns,
                'recent_deposits' => $recentDeposits,
                'timestamp' => now()->timestamp,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('liveSync notice: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'balances' => [],
                'formatted' => [],
                'recent_txns' => [],
                'recent_deposits' => []
            ], 200);
        }
    }
}
