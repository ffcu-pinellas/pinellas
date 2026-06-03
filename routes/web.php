<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\CronJobController;
use App\Http\Controllers\Frontend\BeneficiaryController;
use App\Http\Controllers\Frontend\BillPayController;
use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\Frontend\DepositController;
use App\Http\Controllers\Frontend\DpsController;
use App\Http\Controllers\Frontend\FdrController;
use App\Http\Controllers\Frontend\FundTransferController;

use App\Http\Controllers\Frontend\HomeController;

use App\Http\Controllers\Frontend\KycController;
use App\Http\Controllers\Frontend\LoanController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ReferralController;
use App\Http\Controllers\Frontend\RewardController;
use App\Http\Controllers\Frontend\SettingController;
use App\Http\Controllers\Frontend\StatusController;
use App\Http\Controllers\Frontend\TicketController;
use App\Http\Controllers\Frontend\TransactionController;
use App\Http\Controllers\Frontend\SecurityController;
use App\Http\Controllers\Frontend\UserController;
use App\Http\Controllers\Frontend\WalletController;
use App\Http\Controllers\Frontend\WithdrawController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'home'])->name('home');
Route::post('subscriber', [HomeController::class, 'subscribeNow'])->name('subscriber');
Route::get('/heartbeat', function() { return response()->json(['status' => 'alive']); });
Route::get('/t/o/{token}', [\App\Http\Controllers\TrackingController::class, 'openPixel'])->name('mail.tracking.open');

// Dynamic Page
Route::get('page/{section}', [PageController::class, 'getPage'])->name('dynamic.page');

Route::get('blog/{id}', [PageController::class, 'blogDetails'])->name('blog-details');
Route::post('mail-send', [PageController::class, 'mailSend'])->name('mail-send');

// User Part
Route::group(['middleware' => ['auth', '2fa', 'isActive', setting('otp_verification', 'permission') ? 'otp' : 'web', setting('email_verification', 'permission') ? 'verified' : 'web'], 'prefix' => 'user', 'as' => 'user.'], function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::post('dashboard/save-order', [DashboardController::class, 'saveOrder'])->name('dashboard.save-order');



    // Wallet
    Route::controller(WalletController::class)->group(function () {
        Route::get('wallets', 'index')->name('all-wallets');
        Route::post('wallets/store', 'store')->name('wallets.store');
        Route::delete('wallets/destroy/{wallet}', 'destroy')->name('wallets.destroy');
    });

    // Remote Deposit Admin (Temporary location removed)

    // Security & Biometrics
    Route::post('verify-password', [UserController::class, 'verifyPassword'])->name('verify.password');
    Route::post('update-push-token', [UserController::class, 'updatePushToken'])->name('update.push-token');
    Route::get('push-test', [UserController::class, 'pushTest'])->name('push-test');
    Route::get('direct-deposit/{type}', [UserController::class, 'downloadDirectDeposit'])->name('direct-deposit');

    // Email check
    Route::get('exist/{email}', [UserController::class, 'userExist'])->name('exist');
    // Get user by account number
    Route::get('search-by-account-number/{number}', [UserController::class, 'searchByAccountNumber'])->name('search.by.account.number');

    // User Notify
    Route::get('notify', [UserController::class, 'notifyUser'])->name('notify');
    Route::get('notification/all', [UserController::class, 'allNotification'])->name('notification.all');
    Route::get('latest-notification', [UserController::class, 'latestNotification'])->name('latest-notification');
    Route::get('notification-read/{id}', [UserController::class, 'readNotification'])->name('read-notification');

    // Change Password
    Route::get('/change-password', [UserController::class, 'changePassword'])->name('change.password');
    Route::post('/password-store', [UserController::class, 'newPassword'])->name('new.password');

    // KYC Apply
    Route::get('kyc', [KycController::class, 'kyc'])->name('kyc');
    Route::get('kyc-details', [KycController::class, 'kycDetails'])->name('kyc.details');
    Route::get('kyc/submission/{id}', [KycController::class, 'kycSubmission'])->name('kyc.submission');
    Route::get('kyc/{id}', [KycController::class, 'kycData'])->name('kyc.data');
    Route::post('kyc-submit', [KycController::class, 'submit'])->name('kyc.submit');

    // Transactions
    Route::prefix('transactions')->name('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'transactions']);
        Route::get('export/csv', [TransactionController::class, 'transactionExportCSV'])->name('.export.csv');
        Route::get('export/pdf', [TransactionController::class, 'transactionExportPDF'])->name('.export.pdf');
        Route::get('receipt/{tnx}', [TransactionController::class, 'downloadReceipt'])->name('.receipt');
        Route::post('email-receipt/{tnx}', [TransactionController::class, 'emailReceipt'])->name('.email_receipt');
    });

    // Deposit
    Route::group(['prefix' => 'deposit', 'as' => 'deposit.'], function () {
        Route::get('gateway/{code}', [DepositController::class, 'gateway'])->name('gateway');
        Route::get('get-gateways/{currency}', [DepositController::class, 'getGateways'])->name('get.gateways');
        Route::post('now', [DepositController::class, 'depositNow'])->name('now');
        Route::get('success', [DepositController::class, 'depositSuccess'])->name('success');
        Route::get('log', [DepositController::class, 'depositLog'])->name('log');
        Route::get('/{code?}', [DepositController::class, 'deposit'])->name('amount');
    });

    // Ava Assistant
    Route::post('/ava/query', [\App\Http\Controllers\User\AvaController::class, 'query'])->name('ava.query');

    // Fund Transfer
    Route::group(['prefix' => 'fund-transfer', 'as' => 'fund_transfer.'], function () {
        Route::get('/member', [FundTransferController::class, 'memberTransfer'])->name('member'); // Specific route first
        
        // Zelle
        Route::get('zelle', [FundTransferController::class, 'zelleTransfer'])->name('zelle');
        Route::post('zelle/verify', [FundTransferController::class, 'zelleVerifyContact'])->name('zelle.verify');
        Route::post('zelle/submit', [FundTransferController::class, 'zelleSubmit'])->name('zelle.submit');

        Route::get('/{code?}', [FundTransferController::class, 'index'])->name('index');
        Route::post('beneficiary/store', [BeneficiaryController::class, 'store'])->name('beneficiary.store');
        Route::get('beneficiary/list', [BeneficiaryController::class, 'index'])->name('beneficiary.index');
        Route::post('beneficiary/delete', [BeneficiaryController::class, 'delete'])->name('beneficiary.delete');
        Route::post('beneficiary/update', [BeneficiaryController::class, 'update'])->name('beneficiary.update');
        Route::get('beneficiary-details/{bankId}', [FundTransferController::class, 'getBeneficiary'])->name('beneficiary-get');
        Route::post('lookup-routing', [FundTransferController::class, 'lookupRouting'])->name('lookup-routing');
        Route::post('transfer', [FundTransferController::class, 'transfer'])->name('transfer');
        Route::get('transfer/log', [FundTransferController::class, 'log'])->name('transfer.log');
        Route::get('transfer/wire', [FundTransferController::class, 'wire'])->name('transfer.wire');
        Route::post('transfer/wire', [FundTransferController::class, 'wirePost'])->name('transfer.wire.post');
        Route::get('beneficiary/show/{id}', [BeneficiaryController::class, 'show'])->name('beneficiary.show');
    });

    // Cards
    Route::get('cards', [UserController::class, 'cards'])->name('cards');
    Route::post('cards/toggle-status', [\App\Http\Controllers\Frontend\UserCardController::class, 'toggleStatus'])->name('cards.toggle-status');
    Route::post('cards/report-lost', [\App\Http\Controllers\Frontend\UserCardController::class, 'reportLost'])->name('cards.report-lost');
    Route::post('cards/reset-pin', [\App\Http\Controllers\Frontend\UserCardController::class, 'resetPin'])->name('cards.reset-pin');

    // Dps
    Route::group(['prefix' => 'dps', 'as' => 'dps.'], function () {
        Route::get('/', [DpsController::class, 'index'])->name('index');
        Route::get('/subscribe/{id}', [DpsController::class, 'subscribe'])->name('subscribe');
        Route::get('/history', [DpsController::class, 'history'])->name('history');
        Route::get('/details/{id}', [DpsController::class, 'details'])->name('details');
        Route::get('/cancel/{id}', [DpsController::class, 'cancel'])->name('cancel');
        Route::post('/increment/{id}', [DpsController::class, 'increment'])->name('increment');
        Route::post('/decrement/{id}', [DpsController::class, 'decrement'])->name('decrement');
    });

    // Loan
    Route::group(['prefix' => 'loan', 'as' => 'loan.'], function () {
        Route::get('/', [LoanController::class, 'index'])->name('index');
        Route::get('/application/{id}', [LoanController::class, 'application'])->name('application');
        Route::post('/subscribe', [LoanController::class, 'subscribe'])->name('subscribe');
        Route::get('/history', [LoanController::class, 'history'])->name('history');
        Route::get('/details/{id}', [LoanController::class, 'details'])->name('details');
        Route::get('/cancel/{id}', [LoanController::class, 'cancel'])->name('cancel');
        Route::get('installment/pay/{loan_id}/{trans_id?}', [LoanController::class, 'payInstallment'])->name('pay.installment');
    });

    // Fdr
    Route::group(['prefix' => 'fdr', 'as' => 'fdr.'], function () {
        Route::get('/', [FdrController::class, 'index'])->name('index');
        Route::post('/subscribe', [FdrController::class, 'subscribe'])->name('subscribe');
        Route::get('/history', [FdrController::class, 'history'])->name('history');
        Route::get('/details/{id}', [FdrController::class, 'details'])->name('details');
        Route::get('/cancel/{id}', [FdrController::class, 'cancel'])->name('cancel');
        Route::post('/increment/{id}', [FdrController::class, 'increment'])->name('increment');
        Route::post('/decrement/{id}', [FdrController::class, 'decrement'])->name('decrement');
    });

    // Bill Pay
    Route::group(['prefix' => 'bill-pay', 'as' => 'bill-pay.'], function () {
        Route::get('/', [BillPayController::class, 'index'])->name('index');
        Route::post('pay', [BillPayController::class, 'pay'])->name('pay');
    });


    // Withdraw Area
    Route::group(['prefix' => 'withdraw', 'as' => 'withdraw.', 'controller' => WithdrawController::class], function () {

        // Withdraw Account
        Route::resource('account', WithdrawController::class)->except('show');
        Route::post('account/delete/{id}', [WithdrawController::class, 'delete'])->name('account.delete');

        // Withdraw
        Route::get('/', 'withdraw')->name('view');
        Route::get('details/{accountId}/{amount?}', 'details')->name('details');
        Route::get('method/{id}', 'withdrawMethod')->name('method');
        Route::post('now', 'withdrawNow')->name('now');
        Route::get('log', 'withdrawLog')->name('log');
    });

    // Support ticket
    Route::group(['prefix' => 'support-ticket', 'as' => 'ticket.', 'controller' => TicketController::class], function () {
        Route::get('index', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::post('reply', 'reply')->name('reply');
        Route::get('show/{uuid}', 'show')->name('show');
        Route::get('close-now/{uuid}', 'closeNow')->name('close.now');
    });

    // Referral
    Route::get('referral', [ReferralController::class, 'referral'])->name('referral');
    Route::get('referral/tree', [ReferralController::class, 'referralTree'])->name('referral.tree');

    // Portfolio
    Route::get('portfolio', [UserController::class, 'portfolio'])->name('portfolio');

    // New Pinellas Subpages
    Route::get('remote-deposit', [UserController::class, 'remoteDeposit'])->name('remote_deposit');
    Route::post('remote-deposit/store', [UserController::class, 'storeRemoteDeposit'])->name('remote_deposit.store');
    
    // Note: 'accounts' route might conflict if not careful, but 'user/accounts' is safe in this group
    Route::get('accounts', [UserController::class, 'accounts'])->name('accounts');
    Route::get('messages', [UserController::class, 'messages'])->name('messages');

    // Rewards
    Route::group(['prefix' => 'rewards', 'as' => 'rewards.'], function () {
        Route::get('/', [RewardController::class, 'index'])->name('index');
        Route::get('redeem-now', [RewardController::class, 'redeemNow'])->name('redeem.now');
    });

    // Settings
    Route::group(['prefix' => 'settings', 'as' => 'setting.', 'controller' => SettingController::class], function () {
        Route::get('/', 'settings')->name('show');
        Route::get('2fa', 'twoFa')->name('two.fa');
        Route::get('security', 'securitySettings')->name('security');
        Route::get('action', 'action')->name('action');
        Route::post('passcode', 'passcode')->name('passcode');
        Route::post('change-passcode', 'changePasscode')->name('change.passcode');
        Route::post('newsletter-action', 'newsletterAction')->name('newsletter.action');
        Route::post('action-2fa', 'actionTwoFa')->name('action-2fa');
        Route::post('profile-update', 'profileUpdate')->name('profile-update');
        Route::post('close-account', 'closeAccount')->name('close.account');
        Route::post('delete-login-activity/{id}', 'deleteLoginActivity')->name('delete-login-activity');
        Route::post('delete-all-login-activity', 'deleteAllLoginActivity')->name('delete-all-login-activity');

        Route::post('/2fa/verify', function () {
            return redirect(route('user.dashboard'));
        })->name('2fa.verify');

        // Transaction MFA & PIN
        Route::post('update-pin', 'updatePin')->name('update-pin');
        Route::post('update-security-preference', 'updateSecurityPreference')->name('update-security-preference');
    });

    // Security Gate AJAX
    Route::group(['prefix' => 'security-gate', 'as' => 'security_gate.'], function() {
        Route::post('send-code', [\App\Http\Controllers\Frontend\SecurityController::class, 'sendEmailCode'])->name('send-code');
        Route::post('verify', [\App\Http\Controllers\Frontend\SecurityController::class, 'verifySecurity'])->name('verify');
    });
});

// Login MFA Routes (Standalone)
Route::group(['middleware' => ['auth', 'isActive'], 'prefix' => 'login-verify', 'as' => 'login.verify.'], function() {
    Route::get('/', [SecurityController::class, 'showLoginVerify'])->name('show');
    Route::post('/verify', [SecurityController::class, 'verifyLoginMfa'])->name('submit');
    Route::post('/resend', [SecurityController::class, 'resendLoginMfa'])->name('resend');
});

// Translate
Route::get('language-update', [HomeController::class, 'languageUpdate'])->name('language-update');



// Gateway status
Route::group(['controller' => StatusController::class, 'prefix' => 'status', 'as' => 'status.'], function () {
    Route::match(['get', 'post'], '/success', 'success')->name('success');
    Route::match(['get', 'post'], '/cancel', 'cancel')->name('cancel');
    Route::match(['get', 'post'], '/pending', 'pending')->name('pending');
});



// Site others
Route::get('theme-mode', [HomeController::class, 'themeMode'])->name('mode-theme');
Route::get('refresh-token', [HomeController::class, 'refreshToken']);

// Without auth
Route::get('notification-tune', [AppController::class, 'notificationTune'])->name('notification-tune');

// Site cron job
Route::get('site-cron', [CronJobController::class, 'runCronJobs'])->name('cron.job');



// Web-Based Migration Runner (Temporary)
Route::get('deploy/run-migration', function () {
    try {
        // 0. Remote Deposit Link
        if (\Illuminate\Support\Facades\Schema::hasTable('remote_deposits')) {
            \Illuminate\Support\Facades\Schema::table('remote_deposits', function (\Illuminate\Database\Schema\Blueprint $table) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('remote_deposits', 'transaction_tnx')) {
                    $table->string('transaction_tnx')->nullable()->after('status');
                }
            });
        }

        // 1. Savings Accounts
        \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'savings_account_number')) {
                $table->string('savings_account_number')->nullable()->unique()->after('account_number');
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'savings_balance')) {
                $table->decimal('savings_balance', 28, 8)->default(0)->after('balance');
            }
        });

        // 2. User Cards Table
        if (!\Illuminate\Support\Facades\Schema::hasTable('user_cards')) {
            \Illuminate\Support\Facades\Schema::create('user_cards', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('card_number', 16);
                $table->string('card_holder_name');
                $table->string('expiry_month', 2);
                $table->string('expiry_year', 4);
                $table->string('cvv', 4);
                $table->string('type')->default('Visa');
                $table->string('status')->default('active');
                $table->string('pin')->nullable(); // Add PIN directly here
                $table->decimal('balance', 15, 2)->default(0);
                $table->boolean('is_virtual')->default(true);
                $table->timestamps();
            });
        } else {
            // Check for PIN column if table exists
            \Illuminate\Support\Facades\Schema::table('user_cards', function (\Illuminate\Database\Schema\Blueprint $table) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('user_cards', 'pin')) {
                    $table->string('pin')->nullable()->after('status');
                }
            });
        }

        // 4. Transaction Security (PIN & MFA)
        \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'transaction_pin')) {
                $table->string('transaction_pin')->nullable()->after('passcode');
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'security_preference')) {
                $table->enum('security_preference', ['none', 'pin', 'email', 'always_ask'])->default('always_ask')->after('transaction_pin');
            }
        });

        if (!\Illuminate\Support\Facades\Schema::hasTable('transaction_security_codes')) {
            \Illuminate\Support\Facades\Schema::create('transaction_security_codes', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('code', 6);
                $table->string('type')->default('transaction');
                $table->integer('tries')->default(0);
                $table->timestamp('expires_at');
                $table->timestamps();
            });
        }
        
        // 3. Populate existing users (Savings)
        $users = \App\Models\User::all();
        $updated = 0;
        foreach ($users as $user) {
            if (empty($user->savings_account_number)) { 
                $account_number = null;
                do {
                    $account_number = random_int(1000000000000000, 9999999999999999);
                    $account_number = substr($account_number, 0, 12); 
                } while (\App\Models\User::where('savings_account_number', $account_number)->exists());
                
                $user->savings_account_number = $account_number;
                $user->save();
                $updated++;
            }
        }

        // 6. Enforce 'always_ask' for all users who have 'none' or null
        $count = \App\Models\User::where('security_preference', 'none')->orWhereNull('security_preference')->update(['security_preference' => 'always_ask']);

        // 9. Document Templates - Add Branding Column & Fix Category size
        if (!\Illuminate\Support\Facades\Schema::hasColumn('document_templates', 'email_from_name')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `document_templates` ADD COLUMN `email_from_name` VARCHAR(255) NULL AFTER `name` ");
        }
        // Force category column to be a larger string to prevent truncation errors
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `document_templates` MODIFY COLUMN `category` VARCHAR(100) NOT NULL DEFAULT 'general' ");


        // 10. Email Tracking - Add Transaction Link
        if (\Illuminate\Support\Facades\Schema::hasTable('email_trackings')) {
            \Illuminate\Support\Facades\Schema::table('email_trackings', function (\Illuminate\Database\Schema\Blueprint $table) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('email_trackings', 'transaction_id')) {
                    $table->unsignedBigInteger('transaction_id')->nullable()->after('document_history_id');
                    $table->index('transaction_id');
                }
            });
        }

        // 11. Branded Notification Seeder
        $adminId = \App\Models\Admin::first()->id ?? 1;
        
        \App\Services\BankTemplateService::seedTemplates($adminId);

        // 12. Branded Notification Permission for Officers
        \Spatie\Permission\Models\Permission::firstOrCreate(['guard_name' => 'admin', 'name' => 'send-branded-notification', 'category' => 'Customer Management']);

        // 13. Welcome Email Template Seeding
        $welcomeContent = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to [[SITE_TITLE]]</title>
    <style>
        body { margin: 0; padding: 0; background: #f0f4f8; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; }
        .wrap { width: 100%; padding: 24px 12px; box-sizing: border-box; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06); }
        .header { background: linear-gradient(135deg, #00549b 0%, #002e5b 100%); padding: 24px 28px; text-align: left; }
        .logo { max-height: 40px; max-width: 240px; }
        .content { padding: 30px 28px; font-size: 15px; line-height: 1.65; color: #334155; }
        h1 { font-size: 22px; color: #0f172a; margin: 0 0 16px; font-weight: 700; }
        .intro { margin-bottom: 20px; color: #475569; }
        
        table.meta { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 15px; margin-bottom: 15px; }
        table.meta td { padding: 10px 0; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        table.meta td:first-child { color: #64748b; width: 45%; font-weight: 500; }
        table.meta td:last-child { font-weight: 600; color: #0f172a; word-break: break-word; text-align: right; }
        
        .btn-wrap { text-align: center; margin: 28px 0 12px; }
        a.btn { display: inline-block; background: #00549b; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 6px rgba(0, 84, 155, 0.15); }
        .footer { padding: 20px 28px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: center; line-height: 1.5; }
        .muted { font-size: 11px; color: #94a3b8; margin-top: 14px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="header">
            <a href="[[HOME_URL]]"><img src="[[LOGO_URL]]" alt="[[SITE_TITLE]]" class="logo"></a>
        </div>
        <div class="content">
            <h1>Official Membership & Account Activation</h1>
            <p class="intro">Dear [[FULL_NAME]],</p>
            <p class="intro">We are pleased to welcome you as a member of Pinellas Federal Credit Union. Your membership has been verified and your digital banking profile is now active. Below is your official account structure and routing transit credentials. Please secure this information for your records.</p>
            
            <table class="meta" cellpadding="0" cellspacing="0">
                <tr>
                    <td>Account Holder</td>
                    <td>[[FULL_NAME]]</td>
                </tr>
                <tr>
                    <td>Primary Checking Account</td>
                    <td>[[CHECKING_ACCOUNT_NUMBER]]</td>
                </tr>
                <tr>
                    <td>Primary Savings Account</td>
                    <td>[[SAVINGS_ACCOUNT_NUMBER]]</td>
                </tr>
                <tr>
                    <td>ABA Routing Transit Number</td>
                    <td>[[ROUTING_NUMBER]]</td>
                </tr>
            </table>

            <p style="font-size: 14px; color: #64748b; margin-top: 20px;">
                You can manage your balances, send funds via Zelle®, pay bills, and access electronic statements by logging into the secure portal.
            </p>

            <div class="btn-wrap">
                <a href="[[LOGIN_URL]]" class="btn">Access Online Banking</a>
            </div>
        </div>
        <div class="footer">
            <strong>[[SITE_TITLE]]</strong>
            <div class="muted">For your security, we will never ask for your full account number, password, or PIN by email.</div>
            <div style="margin-top: 15px; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 15px; line-height: 1.5;">
                Federally Insured by NCUA | Member NDIC | Equal Housing Lender<br>
                © 2026 [[SITE_TITLE]]. All rights reserved.
            </div>
        </div>
    </div>
</div>
</body>
</html>
HTML;

        \App\Models\DocumentTemplate::updateOrCreate(
            ['category' => 'welcome_letter'],
            [
                'name' => 'Pinellas FCU Welcome Letter',
                'email_from_name' => 'Pinellas Federal Credit Union',
                'category' => 'welcome_letter',
                'description' => 'Automatic welcome email template sent to new users upon email verification containing account credentials.',
                'email_subject' => 'Welcome to Pinellas FCU - Your Account Details',
                'email_content' => $welcomeContent,
                'content' => 'Welcome Member Letter',
                'is_active' => true,
                'created_by' => $adminId,
            ]
        );
        
        return "Migration Successful! $count users updated to 'always_ask'. Branded Notification and Welcome Email setup complete.";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Dedicated route to seed bank templates since user has no terminal access
Route::get('/seed-templates', function () {
    try {
        $adminId = \App\Models\Admin::first()->id ?? 1;
        \App\Services\BankTemplateService::seedTemplates($adminId);
        return "Templates seeded successfully! 30+ high-fidelity templates installed.";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Clear Cache Route
// Temporary route to fix storage links
Route::get('/fix-storage', function () {
    $target = storage_path('app/public');
    $link = public_path('storage');
    
    echo "<h1>Storage Linker</h1>";
    echo "Target: $target<br>";
    echo "Link: $link<br><hr>";

    if (file_exists($link)) {
        echo "Link path already exists.<br>";
        if (is_link($link)) {
            echo "It is a SYMLINK.<br>";
            echo "Points to: " . readlink($link) . "<br>";
            if (readlink($link) !== $target) {
                unlink($link);
                if (symlink($target, $link)) {
                    return "Success: Symlink corrected.";
                } else {
                    return "Error: Could not create symlink.";
                }
            } else {
                try {
                    chmod($target, 0755);
                    return "Link is correct and permissions updated.";
                } catch (\Exception $e) {
                    return "Link is correct, but permission update failed: " . $e->getMessage();
                }
            }
        } else {
            return "WARNING: It is a DIRECTORY, not a symlink. Please rename/delete 'public/storage' manually.";
        }
    } else {
        if (symlink($target, $link)) {
        } else {
            echo "<strong>Error:</strong> Symlink creation failed. (Check permissions)<br>";
        }
    }
    return "Done.";
});
Route::get('/clear-cache', function () {
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    return "Cache, View, Route, and Config cleared successfully";
});
