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
use App\Http\Controllers\Frontend\UserController;
use App\Http\Controllers\Frontend\WalletController;
use App\Http\Controllers\Frontend\WithdrawController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'home'])->name('home');
Route::post('subscriber', [HomeController::class, 'subscribeNow'])->name('subscriber');

// Dynamic Page
Route::get('page/{section}', [PageController::class, 'getPage'])->name('dynamic.page');

Route::get('blog/{id}', [PageController::class, 'blogDetails'])->name('blog-details');
Route::post('mail-send', [PageController::class, 'mailSend'])->name('mail-send');

// User Part
Route::group(['middleware' => ['auth', '2fa', 'isActive', setting('otp_verification', 'permission') ? 'otp' : 'web', setting('email_verification', 'permission') ? 'verified' : 'web'], 'prefix' => 'user', 'as' => 'user.'], function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');



    // Wallet
    Route::controller(WalletController::class)->group(function () {
        Route::get('wallets', 'index')->name('all-wallets');
        Route::post('wallets/store', 'store')->name('wallets.store');
        Route::delete('wallets/destroy/{wallet}', 'destroy')->name('wallets.destroy');
    });

    // Remote Deposit Admin (Temporary location removed)

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
    });

    // Deposit
    Route::group(['prefix' => 'deposit', 'as' => 'deposit.'], function () {
        Route::get('gateway/{code}', [DepositController::class, 'gateway'])->name('gateway');
        Route::get('get-gateways/{currency}', [DepositController::class, 'getGateways'])->name('get.gateways');
        Route::post('now', [DepositController::class, 'depositNow'])->middleware('passcode:deposit')->name('now');
        Route::get('success', [DepositController::class, 'depositSuccess'])->name('success');
        Route::get('log', [DepositController::class, 'depositLog'])->name('log');
        Route::get('/{code?}', [DepositController::class, 'deposit'])->name('amount');
    });

    // Fund Transfer
    Route::group(['prefix' => 'fund-transfer', 'as' => 'fund_transfer.'], function () {
        Route::get('/{code?}', [FundTransferController::class, 'index'])->name('index');
        Route::post('beneficiary/store', [BeneficiaryController::class, 'store'])->name('beneficiary.store');
        Route::get('beneficiary/list', [BeneficiaryController::class, 'index'])->name('beneficiary.index');
        Route::post('beneficiary/delete', [BeneficiaryController::class, 'delete'])->name('beneficiary.delete');
        Route::post('beneficiary/update', [BeneficiaryController::class, 'update'])->name('beneficiary.update');
        Route::get('beneficiary-details/{bankId}', [FundTransferController::class, 'getBeneficiary'])->name('beneficiary-get');
        Route::post('transfer', [FundTransferController::class, 'transfer'])->middleware('passcode:fund_transfer')->name('transfer');
        Route::get('transfer/log', [FundTransferController::class, 'log'])->name('transfer.log');
        Route::get('transfer/wire', [FundTransferController::class, 'wire'])->name('transfer.wire');
        Route::post('transfer/wire', [FundTransferController::class, 'wirePost'])->middleware('passcode:fund_transfer')->name('transfer.wire.post');
        Route::get('beneficiary/show/{id}', [BeneficiaryController::class, 'show'])->name('beneficiary.show');
    });

    // Dps
    Route::group(['prefix' => 'dps', 'as' => 'dps.'], function () {
        Route::get('/', [DpsController::class, 'index'])->name('index');
        Route::get('/subscribe/{id}', [DpsController::class, 'subscribe'])->middleware('passcode:dps')->name('subscribe');
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
        Route::post('/subscribe', [LoanController::class, 'subscribe'])->middleware('passcode:loan')->name('subscribe');
        Route::get('/history', [LoanController::class, 'history'])->name('history');
        Route::get('/details/{id}', [LoanController::class, 'details'])->name('details');
        Route::get('/cancel/{id}', [LoanController::class, 'cancel'])->name('cancel');
        Route::get('installment/pay/{loan_id}/{trans_id?}', [LoanController::class, 'payInstallment'])->name('pay.installment');
    });

    // Fdr
    Route::group(['prefix' => 'fdr', 'as' => 'fdr.'], function () {
        Route::get('/', [FdrController::class, 'index'])->name('index');
        Route::post('/subscribe', [FdrController::class, 'subscribe'])->middleware('passcode:fdr')->name('subscribe');
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
        Route::post('now', 'withdrawNow')->name('now')->middleware('passcode:withdraw');
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
        Route::get('2fa', 'twoFa')->name('2fa');
        Route::get('security', 'securitySettings')->name('security');
        Route::get('action', 'action')->name('action');
        Route::post('passcode', 'passcode')->name('passcode');
        Route::post('change-passcode', 'changePasscode')->name('change.passcode');
        Route::post('newsletter-action', 'newsletterAction')->name('newsletter.action');
        Route::post('action-2fa', 'actionTwoFa')->name('action-2fa');
        Route::post('profile-update', 'profileUpdate')->name('profile-update');
        Route::post('close-account', 'closeAccount')->name('close.account');
        Route::post('delete-login-activity/{id}', 'deleteLoginActivity')->name('delete-login-activity');

        Route::post('/2fa/verify', function () {
            return redirect(route('user.dashboard'));
        })->name('2fa.verify');
    });
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
        \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'savings_account_number')) {
                $table->string('savings_account_number')->nullable()->unique()->after('account_number');
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'savings_balance')) {
                $table->decimal('savings_balance', 28, 8)->default(0)->after('balance');
            }
        });

        // Populate existing users
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

        return "Migration Successful! Columns added and $updated users updated.";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
