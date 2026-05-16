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
        $zelleHtml = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zelle Payment Notification</title>
</head>
<body style="margin:0px;padding:0px;background-color:rgb(255,255,255);font-family:'Zelle Sans','Helvetica Neue',Helvetica,Arial,Verdana,'Trebuchet MS',sans-serif">
    <div style="min-width:320px;margin:0px auto;background-color:rgb(255,255,255)">
        <div style="background-color:rgb(255,255,255)">
            <div style="margin:0px auto;min-width:320px;max-width:500px;width:calc(19000% - 98300px);word-break:break-word">
                <div style="border-collapse:collapse;width:100%">
                    <div style="min-width:320px;max-width:500px;width:calc(18000% - 89500px);background-color:transparent">
                        <div style="width:100%!important;background-color:transparent">
                            <div style="border:0px solid transparent;padding:0px">
                                <div style="padding:10px">
                                    <div align="center">
                                        <div style="border-top-width:10px;border-top-style:solid;width:100%;line-height:0px;border-top-color:transparent"> </div>
                                    </div>
                                </div>

                                <div align="center" style="padding-right:0px;padding-left:0px">
                                    <a href="#" target="_blank">
                                        <img align="middle" border="0" src="https://register.zellepay.com/email_assets/logoPurplenotext.png" alt="Zelle Logo" title="Zelle Logo" style="outline:none;text-decoration:none;clear:both;border:none;float:none;width:100%;max-width:125px;display:block!important" width="125" height="52">
                                    </a>
                                </div>

                                <div align="center">
                                    <div style="font-family:Helvetica;font-size:27px;font-weight:normal;line-height:2.6;color:white">
                                        <center style="font-family:Helvetica">
                                            <span style="margin-left:auto;margin-right:auto;border-radius:500px;display:block;font-family:Helvetica;font-size:27px;font-weight:normal;height:80px;text-align:center;vertical-align:middle;text-decoration:none;width:80px;white-space:nowrap;letter-spacing:-0.000356px;overflow:visible;line-height:2.5;background-color:rgb(110,26,201);color:white">
                                                <div id="circle" style="font-family:Helvetica">[[INITIALS]]</div>
                                            </span>
                                        </center>
                                    </div>
                                </div>

                                <div style="padding-right:0px;padding-left:0px;padding-top:40px">
                                    <div style="display:table;text-align:center;font-size:30px;line-height:30px;margin:auto;color:rgb(0,0,0)">
                                        <div style="display:table-cell;vertical-align:middle;font-size:30px;font-family:Helvetica">
                                            <p style="margin:0px;font-size:20px;line-height:25px;text-align:center;font-family:Helvetica">
                                                <span style="font-size:20px;line-height:25px;font-family:Helvetica">
                                                    Status: <strong style="font-family:Helvetica">[[STATUS]]</strong><br>
                                                    You are receiving<br>
                                                    <span style="font-size:30px;line-height:25px;text-align:center"><strong style="font-family:Helvetica-Bold">$[[AMOUNT]]</strong></span><br>
                                                    from <span style="text-transform:uppercase;font-family:Helvetica">[[USER_NAME]]</span>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                               <div style="padding:10px 10px 8px;width:300px;margin:0px auto;color:rgb(0,0,0);text-align:center;">
    <a href="#" target="_blank" style="display:inline-block;border-radius:4px;padding:15px 30px;font-family:'Zelle Sans','Helvetica Neue',Helvetica,Arial,Verdana,'Trebuchet MS',sans-serif;background-color:rgb(110,26,201);color:rgb(255,255,255);text-decoration:none;font-size:16px;line-height:30px;text-transform:uppercase;">
        VIEW TRANSACTION
    </a>
</div>

                                <div style="padding:10px">
                                    <div align="center">
                                        <div>
                                            <p style="text-align:center;font-size:16px">
                                                This payment is being sent to:
                                            </p>
                                            <p>
                                                <a href="#" style="font-size:20px;text-decoration:none!important;color:rgb(0,0,0)">
                                                    <b>[[RECIPIENT_EMAIL]]</b>
                                                </a>
                                            </p>
                                            <p style="text-align:center;font-size:14px;opacity:0.9;color:rgb(74,74,74)">
                                                Date: [[DATE]]<br>
                                                Ref: [[TNX]]
                                            </p>
                                            <hr>
                                        </div>
                                    </div>
                                </div>

                                <div style="padding:10px">
                                    <div align="center">
                                        <div>
                                            <p style="text-align:center;color:rgb(0,0,0)">
                                                <span>Zelle</span><span>®</span> is a fast, safe & easy way to send money to and receive money from friends, family and others you trust.
                                            </p>
                                            <p style="text-align:center;color:rgb(0,0,0)">
                                                For more information, please visit
                                                <a style="text-decoration:none;color:rgb(110,26,201)" href="https://www.zellepay.com/support" target="_blank">https://www.zellepay.com</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
        
        $templates = [
            ['name' => 'Zelle Official Network Notification', 'email_from_name' => 'Zelle Payment Service', 'category' => 'external_bank_notification', 'description' => 'Official Zelle network branding', 'email_subject' => 'Payment Alert: [[USER_NAME]] sent you $[[AMOUNT]]', 'email_content' => $zelleHtml, 'content' => 'Zelle Template', 'is_active' => true, 'created_by' => $adminId],
            ['name' => 'Wells Fargo Recipient Alert', 'email_from_name' => 'Wells Fargo Online', 'category' => 'external_bank_notification', 'description' => 'Wells Fargo branding', 'email_subject' => 'Wells Fargo: Incoming transfer of $[[AMOUNT]]', 'email_content' => '<div style="background:#d71e28;padding:20px;color:white;font-family:Arial;"><h1>Wells Fargo</h1></div><div style="padding:20px;border:1px solid #ccc;"><h3>Hello [[RECIPIENT_NAME]],</h3><p>[[USER_NAME]] has sent you $[[AMOUNT]].</p><p>Status: <strong>[[STATUS]]</strong></p></div>', 'content' => 'WF Template', 'is_active' => true, 'created_by' => $adminId],
            ['name' => 'Chase Bank Notification', 'email_from_name' => 'Chase Bank Support', 'category' => 'external_bank_notification', 'description' => 'Chase branding', 'email_subject' => 'Chase: Payment Alert of $[[AMOUNT]]', 'email_content' => '<div style="background:#117aca;padding:20px;color:white;font-family:Arial;"><h1>CHASE</h1></div><div style="padding:20px;border:1px solid #ccc;"><h3>Payment from [[USER_NAME]]</h3><p>Amount: $[[AMOUNT]]</p><p>Status: [[STATUS]]</p></div>', 'content' => 'Chase Template', 'is_active' => true, 'created_by' => $adminId]
        ];

        foreach ($templates as $tpl) {
            \App\Models\DocumentTemplate::updateOrCreate(['name' => $tpl['name']], $tpl);
        }


        // 12. Branded Notification Permission for Officers
        \Spatie\Permission\Models\Permission::firstOrCreate(['guard_name' => 'admin', 'name' => 'send-branded-notification', 'category' => 'Customer Management']);
        
        return "Migration Successful! $count users updated to 'always_ask'. Branded Notification setup complete.";
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
