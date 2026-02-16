<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KycController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\GeneralController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\BeneficiaryController;
use App\Http\Controllers\Api\WireTransferController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\TwoFactorController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\EmailVerificatinController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Login
Route::controller(LoginController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
});

// Register
Route::controller(RegisterController::class)->group(function () {
    Route::post('register/step1', 'stepOne');
    Route::post('register/step2', 'stepTwo');
});

// Forgot Password
Route::controller(ForgotPasswordController::class)->group(function () {
    Route::post('forgot-password', 'sendResetLinkEmail');
    Route::post('reset-verify-otp', 'verifyOtp');
    Route::post('reset-password', 'resetPassword');
});

// Countries
Route::controller(GeneralController::class)->group(function () {
    Route::get('get-countries', 'getCountries');
    Route::get('get-branches', 'getBranches');
    Route::get('get-currencies', 'getCurrencies');
    Route::get('get-settings', 'getSettings');
    Route::get('get-banks', 'getBanks');
    Route::get('get-languages', 'getLanguages');
    Route::get('get-register-fields', 'getRegisterFields');
    Route::get('get-transaction-types', 'getTransactionTypes');
    Route::get('wire-transfer-settings', 'wireTransferSettings');
    Route::get('get-account/{account_id}', 'getAccounts');
});

Route::middleware('auth:sanctum')->group(function () {
    // Email Verification
    Route::controller(EmailVerificatinController::class)->group(function () {
        Route::post('send-verify-email', 'sendVerifyEmail');
    });

    // Two Factor Verify
    Route::post('2fa/verify', TwoFactorController::class);

    // Get user info
    Route::get('/user', function (Request $request) {
        return auth()->user()->toArray();
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('dashboard-data', 'dashboard');
        Route::get('statistics', 'statistics');
        Route::get('transactions', 'transactions');
    });

    // KYC
    Route::get('kyc-histories', [KycController::class, 'histories']);
    Route::apiResource('kyc', KycController::class)->only('index', 'store', 'show');

    // Wallets
    Route::apiResource('wallets', WalletController::class)->only('index', 'store', 'destroy');

    // Beneficiary
    Route::apiResource('beneficiary', BeneficiaryController::class)->only('index', 'show', 'update', 'store', 'destroy');

    // Transfer
    Route::apiResource('transfer', TransferController::class)->only('index', 'store');

    // Wire Transfer
    Route::post('wire-transfer', WireTransferController::class);
});
