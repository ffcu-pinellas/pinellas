<?php

namespace App\Services;

use App\Enums\TransferType;
use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Facades\Txn\Txn;
use App\Models\Beneficiary;
use App\Models\Currency;
use App\Models\OthersBank;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\NotifyTrait;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class TransferService
{
    use NotifyTrait;

    public function validate(User $user, array $input, $walletType = 'default')
    {
        if (! setting('transfer_status', 'permission') || ! $user->transfer_status) {
            \Log::error("TransferService::validate - Transfer Disabled for User ID: {$user->id}");
            throw ValidationException::withMessages(['error' => __('Fund transfer currently unavailable!')]);
        }

        if (! setting('kyc_fund_transfer') && ! $user->kyc) {
            throw ValidationException::withMessages(['error' => __('Please verify your KYC.')]);
        }

        $amount = $input['amount'];
        $bankId = $input['bank_id'];
        
        \Log::info("TransferService::validate - Amount: $amount, BankID: $bankId, Wallet: $walletType");

        $bankInfo = OthersBank::find($bankId);
        $currencyCode = ($walletType == 'default' || $walletType == 'primary_savings') ? setting('site_currency', 'global') : $walletType;

        if ($bankId != 0) {
            $query = Transaction::where('user_id', $user->id)
                ->where('bank_id', $bankInfo->id)
                ->where('type', TxnType::FundTransfer)
                ->whereIn('transfer_type', [TransferType::OtherBankTransfer, TransferType::OwnBankTransfer]);

            $todayAmount = CurrencyService::convert((clone $query)->whereDate('created_at', Carbon::today())->sum('amount'), setting('site_currency', 'global'), $currencyCode);
            $todayCount = (clone $query)->whereDate('created_at', Carbon::today())->count();
            $monthAmount = CurrencyService::convert((clone $query)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount'), setting('site_currency', 'global'), $currencyCode);
            $monthCount = (clone $query)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

            if ($todayCount >= $bankInfo->daily_limit_maximum_count) {
                throw ValidationException::withMessages(['error' => __('Daily transaction count limit exceeded.')]);
            }
            if ($todayAmount >= CurrencyService::convert($bankInfo->daily_limit_maximum_amount, setting('site_currency', 'global'), $currencyCode)) {
                throw ValidationException::withMessages(['error' => __('Daily transaction amount limit exceeded.')]);
            }
            if ($monthAmount >= CurrencyService::convert($bankInfo->monthly_limit_maximum_amount, setting('site_currency', 'global'), $currencyCode)) {
                throw ValidationException::withMessages(['error' => __('Monthly transaction amount limit exceeded.')]);
            }
            if ($monthCount >= $bankInfo->monthly_limit_maximum_count) {
                throw ValidationException::withMessages(['error' => __('Monthly transaction count limit exceeded.')]);
            }

            // Check limits
            $min = CurrencyService::convert($bankInfo->minimum_transfer, setting('site_currency', 'global'), $currencyCode);
            $max = CurrencyService::convert($bankInfo->maximum_transfer, setting('site_currency', 'global'), $currencyCode);
            if ($amount < $min || $amount > $max) {
                throw ValidationException::withMessages([
                    'error' => __('Please Transfer the Amount within the range :min to :max', [
                        'min' => $min . " " . $currencyCode,
                        'max' => $max . " " . $currencyCode,
                    ]),
                ]);
            }
        } else {
            $min = CurrencyService::convert(setting('min_fund_transfer', 'fee'), setting('site_currency', 'global'), $currencyCode);
            $max = CurrencyService::convert(setting('max_fund_transfer', 'fee'), setting('site_currency', 'global'), $currencyCode);
            if ($amount < $min || $amount > $max) {
                throw ValidationException::withMessages(['error' => __('Transfer amount must be between :min and :max', ['min' => $min . " " . $currencyCode, 'max' => $max . " " . $currencyCode])]);
            }
        }

        if ($bankId == 0) {
            $accountNumber = $input['manual_data']['account_number'] ?? null;
            $beneficiaryId = $input['beneficiary_id'] ?? null;
            if ($beneficiaryId) {
                $beneficiary = Beneficiary::find($beneficiaryId);
                $accountNumber = $beneficiary->account_number ?? $accountNumber;
            }
            
            $sanitizedNumber = sanitizeAccountNumber($accountNumber);
            $receiver = User::where('account_number', $sanitizedNumber)->first();
            
            if (!$receiver) {
                $receiver = User::where('savings_account_number', $sanitizedNumber)->first();
            }

            if (! $receiver) {
                throw ValidationException::withMessages(['error' => __('Receiver Account not found!')]);
            }
        }
    }

    public function process(User $user, array $input, $walletType = 'default')
    {
        $amount = $input['amount'];
        $bankId = $input['bank_id'];
        $bankInfo = OthersBank::find($bankId);
        $currency = setting('site_currency', 'global');
        $currencyCode = ($walletType == 'default' || $walletType == 'primary_savings') ? $currency : $walletType;

        $manualData = $input['manual_data'] ?? [];
        $beneficiary = Beneficiary::find($input['beneficiary_id'] ?? null);
        $accountNumber = $beneficiary?->account_number ?? $manualData['account_number'];
        $sanitizedNumber = sanitizeAccountNumber($accountNumber);
        $receiver = User::where('account_number', $sanitizedNumber)
                        ->orWhere('savings_account_number', $sanitizedNumber)
                        ->first();

        $charge = $this->calculateTransferCharge($bankInfo, $amount, $currencyCode);

        $finalAmount = $amount + $charge;
        $walletType = $walletType;

        if ($walletType == 'primary_savings') {
             $wallet = null;
             $balance = $user->savings_balance;
        } elseif ($walletType !== 'default') {
            $wallet = $user->wallets()->whereRelation('currency', 'code', $walletType)->first();
            $walletType = $wallet?->id;
            $wallet = $wallet;
            $balance = $wallet?->balance;
        } else {
            $wallet = null;
            $balance = $user->balance;
        }

        if ($balance < $finalAmount) {
            throw ValidationException::withMessages(['error' => __('Insufficient balance.')]);
        }

        $txnType = TxnType::FundTransfer;
        $transferType = $bankId == 0 ? TransferType::OwnBankTransfer : TransferType::OtherBankTransfer;
        
        // Determine if it's a "Self" (Intra-Account) transfer or a "Member" transfer
        $isSelfTransfer = ($bankId == 0 && $receiver && $receiver->id === $user->id);
        
        // "Self" transfers are processed instantly. "Member" and "External" require admin approval.
        $initialStatus = $isSelfTransfer ? TxnStatus::Success : TxnStatus::Pending;
        $descriptionSuffix = $isSelfTransfer ? 'INTRA-ACCOUNT TRANSFER' : ($bankId == 0 ? 'MEMBER TRANSFER' : 'EXTERNAL TRANSFER');
        $description = $descriptionSuffix . ' TO ' . $accountNumber;

        $txnWalletType = ($walletType == 'primary_savings') ? 'primary_savings' : ($wallet->id ?? 'default');

        $txnInfo = Txn::transfer(
            $amount, 
            $charge, 
            $finalAmount, 
            $description, 
            $txnType, 
            $initialStatus, 
            $currency, 
            $finalAmount, 
            $user->id, 
            null, 
            'User', 
            $beneficiary?->id, 
            $bankId, 
            $input['purpose'] ?? 'Transfer', 
            $transferType, 
            $manualData, 
            $txnWalletType
        );

        if ($bankId == 0 && $receiver) {
            $transaction = Transaction::tnx($txnInfo['tnx']);
            
            // If it's a self-transfer, the primary transaction is already Success.
            // If it's a member transfer, we wait for admin approval (unless business logic says otherwise, but user requested approval).
            if ($isSelfTransfer) {
                $transaction->update(['status' => TxnStatus::Success]);

                $sanitizedNumber = sanitizeAccountNumber($accountNumber);
                $receiverWalletType = 'default';
                
                // Credit the secondary account instantly
                if ($receiver->savings_account_number == $sanitizedNumber) {
                    $receiver->increment('savings_balance', $amount);
                    $receiverWalletType = 'primary_savings';
                } else {
                    $receiver->increment('balance', $amount);
                    $receiverWalletType = 'default';
                }

                (new Txn)->new(
                    $amount, 
                    0, 
                    $amount, 
                    'System', 
                    'INTRA-ACCOUNT TRANSFER FROM ' . ($walletType == 'primary_savings' ? 'SAVINGS' : 'CHECKING'), 
                    TxnType::ReceiveMoney, 
                    TxnStatus::Success, 
                    $currency, 
                    $amount, 
                    $receiver->id, 
                    null, 
                    'User', 
                    [], 
                    $receiverWalletType, 
                    approvalCause: $input['purpose'] ?? 'Fund Transfer'
                );
            }
            // Member transfers (receiver != user) remain Pending on line 141, and no receipt is recorded until approval in Controller.
        }

        if ($walletType == 'primary_savings') {
            $user->decrement('savings_balance', $finalAmount);
        } else {
            $wallet ? $wallet->decrement('balance', $finalAmount) : $user->decrement('balance', $finalAmount);
        }

        $this->sendNotification($user, $txnInfo, $accountNumber, $manualData);

        return [
            'amount' => $amount,
            'currency' => $currencyCode,
            'account' => $accountNumber,
            'tnx' => $txnInfo['tnx'],
        ];
    }

    public function sendNotification($user, $txnInfo, $account_number, $manual_data)
    {
        $shortcodes = [
            '[[full_name]]' => $user->full_name,
            '[[email]]' => $user->email,
            '[[charge]]' => $txnInfo->charge,
            '[[amount]]' => $txnInfo->amount,
            '[[total_amount]]' => $txnInfo->final_amount,
            '[[account_number]]' => $account_number,
            '[[account_name]]' => data_get($manual_data, 'account_name'),
            '[[branch_name]]' => data_get($manual_data, 'branch_name'),
            '[[site_title]]' => setting('site_title', 'global'),
            '[[site_url]]' => route('home'),
        ];

        $this->pushNotify('fund_transfer_request', $shortcodes, route('admin.fund.transfer.pending'), auth()->id(), 'Admin');
        $this->mailNotify($txnInfo->user->email, 'fund_transfer_request', $shortcodes);
        $this->smsNotify('fund_transfer_request', $shortcodes, $txnInfo->user->phone);
    }

    protected function calculateTransferCharge($bankInfo, $amount, $currencyCode)
    {

        if ($bankInfo) {
            $chargeType = $bankInfo->charge_type;
            $baseCharge = CurrencyService::convert(
                $bankInfo->charge,
                setting('site_currency', 'global'),
                $currencyCode
            );
        } else {
            $chargeType = setting('fund_transfer_charge_type', 'fee');
            $baseCharge = setting('fund_transfer_charge', 'fee');

            if ($currencyCode !== setting('site_currency', 'global') && $chargeType === 'fixed') {
                $baseCharge = CurrencyService::convert(
                    $baseCharge,
                    setting('site_currency', 'global'),
                    $currencyCode
                );
            }
        }
        return ($chargeType === 'percentage')
            ? ($baseCharge / 100) * $amount
            : $baseCharge;
    }
}
