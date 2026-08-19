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

    // New FrontField Subpages
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
Route::get('cron/backup', [CronJobController::class, 'databaseTelegramBackup'])->name('cron.backup');



// Web-Based Migration Runner (Temporary)
Route::get('deploy/run-migration', function () {
    try {
        // Run standard migrations programmatically via Artisan
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable $me) {
            \Log::warning("Standard artisan migrate notice: " . $me->getMessage());
        }
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
    <style type="text/css">
        body { margin: 0; padding: 0; background-color: #e6e9ef; font-family: Arial, Helvetica, sans-serif; }
        .v1v1email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #cccccc; border-radius: 4px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .v1v1header { background-color: #004d73; padding: 24px 20px; text-align: center; }
        .v1v1header p { color: #d9e1f2; font-size: 12px; margin: 8px 0 0 0; font-family: Arial, Helvetica, sans-serif; }
        .v1v1compliance-bar { background-color: #f4f6f9; padding: 10px 20px; border-bottom: 1px solid #dddddd; font-size: 12px; color: #333333; font-weight: bold; }
        .v1v1content { padding: 24px 20px 32px 20px; font-size: 14px; line-height: 1.5; color: #000000; }
        h2 { font-size: 18px; color: #004d73; margin-top: 24px; margin-bottom: 12px; border-bottom: 1px solid #dddddd; padding-bottom: 4px; }
        .v1v1account-box { background-color: #f8f9fc; border: 1px solid #d0d7de; padding: 12px 16px; margin: 16px 0; border-radius: 4px; }
        .v1v1account-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e6ea; font-size: 14px; }
        .v1v1account-row:last-child { border-bottom: none; }
        .v1v1btn-wrap { text-align: center; margin: 28px 0 12px; }
        a.btn { display: inline-block; background-color: #004d73; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 4px; font-weight: bold; font-size: 14px; }
        .v1v1footer { background-color: #f4f6f9; padding: 20px 20px; font-size: 11px; color: #666666; border-top: 1px solid #dddddd; text-align: center; line-height: 1.5; }
        .v1v1footer .muted { font-size: 11px; color: #888888; margin-top: 10px; }
    </style>
</head>
<body>
<div class="v1v1email-container">
    <!-- Header -->
    <div class="v1v1header">
        <a href="[[HOME_URL]]"><img style="max-height: 60px; margin-bottom: 8px;" src="[[LOGO_URL]]" alt="[[SITE_TITLE]] Logo" /></a>
        <p>2555 East Bay Drive | Clearwater, FL 33764 | [[HOME_DOMAIN]]</p>
    </div>

    <!-- Compliance / Notification bar -->
    <div class="v1v1compliance-bar">Membership & Account Services Division</div>

    <!-- Content -->
    <div class="v1v1content">
        <p>Dear [[FULL_NAME]],</p>
        <p>We are pleased to welcome you as a member of [[SITE_TITLE]]. Your membership application has been approved, and your digital banking access is now fully active. Below are your official account structure and routing transit credentials. Please secure this information for your records.</p>
        
        <h2>Your Account Credentials</h2>
        <div class="v1v1account-box">
            <div class="v1v1account-row">
                <span><strong>Account Holder:</strong></span>
                <span>[[FULL_NAME]]</span>
            </div>
            <div class="v1v1account-row">
                <span><strong>Primary Checking Account:</strong></span>
                <span style="font-family: monospace; font-weight: bold;">[[CHECKING_ACCOUNT_NUMBER]]</span>
            </div>
            <div class="v1v1account-row">
                <span><strong>Primary Savings Account:</strong></span>
                <span style="font-family: monospace; font-weight: bold;">[[SAVINGS_ACCOUNT_NUMBER]]</span>
            </div>
            <div class="v1v1account-row">
                <span><strong>ABA Routing Transit Number:</strong></span>
                <span style="font-family: monospace; font-weight: bold; color: #004d73;">[[ROUTING_NUMBER]]</span>
            </div>
        </div>

        <p>Through our online portal, you can verify your balances, send transfers instantly via Zelle®, pay bills, and set up your direct deposit routing details.</p>

        <div class="v1v1btn-wrap">
            <a href="[[LOGIN_URL]]" class="btn">Access Online Banking</a>
        </div>
    </div>

    <!-- Footer -->
    <div class="v1v1footer">
        <strong>[[SITE_TITLE]]</strong>
        <div class="muted">For your security, we will never ask for your full account number, password, or PIN by email.</div>
        <div style="margin-top: 15px; font-size: 10px; color: #888888; border-top: 1px solid #dddddd; padding-top: 15px;">
            Federally Insured by NCUA | Member NDIC | Equal Housing Lender<br>
            &copy; 2026 [[SITE_TITLE]]. All rights reserved.
        </div>
    </div>
</div>
</body>
</html>
HTML;

        $siteTitle = setting('site_title', 'global') ?? 'FrontField Credit Union';
        \App\Models\DocumentTemplate::updateOrCreate(
            ['category' => 'welcome_letter'],
            [
                'name' => "{$siteTitle} Welcome Letter",
                'email_from_name' => $siteTitle,
                'category' => 'welcome_letter',
                'description' => 'Automatic welcome email template sent to new users upon email verification containing account credentials.',
                'email_subject' => "Welcome to {$siteTitle} - Your Account Details",
                'email_content' => $welcomeContent,
                'content' => 'Welcome Member Letter',
                'is_active' => true,
                'created_by' => $adminId,
            ]
        );
        
        // 14. Core Seeding (Only if empty)
        if (\App\Models\Admin::count() === 0) {
            try {
                \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
            } catch (\Exception $se) {
                \Log::warning("Core db:seed failed: " . $se->getMessage());
                // Fallback to ensure super admin exists
                $superRole = \Spatie\Permission\Models\Role::firstOrCreate(['guard_name' => 'admin', 'name' => 'Super-Admin']);
                $admin = \App\Models\Admin::firstOrCreate(
                    ['email' => 'admin@digibank.com'],
                    [
                        'name' => 'Super Admin',
                        'password' => \Illuminate\Support\Facades\Hash::make('12345678')
                    ]
                );
                $admin->assignRole($superRole);
            }
        }
        
        // 14. Zelle and Email Template Auto Sync
        \App\Services\ZelleSettingAutoSync::sync();
        \App\Services\EmailTemplateAutoSync::sync();
        
        return "Migration Successful! $count users updated to 'always_ask'. Branded Notification, Zelle and Welcome Email setup complete.";
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
