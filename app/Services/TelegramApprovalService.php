<?php

namespace App\Services;

use App\Enums\TransferType;
use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Facades\Txn\Txn;
use App\Mail\FundTransferRejectedUserMail;
use App\Mail\IncomingMemberTransferMail;
use App\Mail\IncomingZelleTransferMail;
use App\Mail\ZellePaymentApproved;
use App\Mail\ZellePaymentRejected;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserWallet;
use App\Traits\NotifyTrait;
use App\Traits\RewardTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TelegramApprovalService
{
    use NotifyTrait, RewardTrait;

    /**
     * Approve a pending transaction.
     *
     * @param Transaction $transaction
     * @param string|null $adminNote
     * @return array ['success' => bool, 'message' => string]
     */
    public function approve(Transaction $transaction, ?string $adminNote = null): array
    {
        if ($transaction->status !== TxnStatus::Pending && $transaction->status !== 'pending') {
            $statusStr = is_object($transaction->status) ? $transaction->status->value : $transaction->status;
            return [
                'success' => false,
                'message' => "Transaction {$transaction->tnx} is already {$statusStr}."
            ];
        }

        return DB::transaction(function () use ($transaction, $adminNote) {
            $transaction->refresh();
            if ($transaction->status !== TxnStatus::Pending && $transaction->status !== 'pending') {
                $statusStr = is_object($transaction->status) ? $transaction->status->value : $transaction->status;
                return [
                    'success' => false,
                    'message' => "Transaction {$transaction->tnx} was already processed ({$statusStr})."
                ];
            }

            $transaction->update([
                'status' => TxnStatus::Success,
                'action_message' => $adminNote ?? 'Approved via Telegram Bot',
            ]);

            // Handle Member-to-Member / Internal Transfers credit to recipient
            if ($transaction->transfer_type == TransferType::OwnBankTransfer) {
                $manual_data_arr = json_decode($transaction->manual_field_data, true);
                $accountNumber = data_get($manual_data_arr, 'account_number');

                if ($transaction->method == 'Zelle') {
                    $receiver = User::where('email', $accountNumber)->orWhere('phone', $accountNumber)->first();
                    $sanitizedNumber = $receiver ? $receiver->account_number : '';
                } else {
                    $sanitizedNumber = sanitizeAccountNumber($accountNumber);
                    $receiver = User::where('account_number', $sanitizedNumber)
                        ->orWhere('savings_account_number', $sanitizedNumber)
                        ->first();
                }

                if ($receiver) {
                    $creditAmount = $transaction->amount;
                    $receiverWalletType = 'default';

                    if ($transaction->method != 'Zelle' && $receiver->savings_account_number == $sanitizedNumber) {
                        $receiver->increment('savings_balance', $creditAmount);
                        $receiverWalletType = 'primary_savings';
                    } else {
                        $receiver->increment('balance', $creditAmount);
                        $receiverWalletType = 'default';
                    }

                    // Create ReceiveMoney transaction record
                    $txn = new \App\Facades\Txn\Txn;
                    $txn->new(
                        $creditAmount,
                        0,
                        $creditAmount,
                        'system',
                        "Transfer received from {$transaction->user->full_name} (" . substr($transaction->user->account_number, -4) . ")",
                        TxnType::ReceiveMoney,
                        TxnStatus::Success,
                        $receiverWalletType == 'primary_savings' ? 'Primary Savings' : 'Checking',
                        $receiver->id,
                        $transaction->user_id,
                        'User',
                        $receiverWalletType
                    );

                    // Notify recipient of incoming transfer approval
                    if ($receiver->id !== $transaction->user_id) {
                        $memo = data_get($manual_data_arr, 'memo') ?? data_get($manual_data_arr, 'purpose') ?? $transaction->purpose;
                        if ($transaction->method == 'Zelle') {
                            try {
                                Mail::to($receiver->email)->send(new IncomingZelleTransferMail(
                                    $receiver,
                                    $transaction->user,
                                    $transaction,
                                    'success',
                                    $memo,
                                    $receiverWalletType,
                                    $receiver->account_number
                                ));
                            } catch (\Throwable $e) {
                                Log::error('Telegram recipient Zelle approval email error: ' . $e->getMessage());
                            }
                        } else {
                            try {
                                Mail::to($receiver->email)->send(new IncomingMemberTransferMail(
                                    $receiver,
                                    $transaction->user,
                                    $transaction,
                                    'success',
                                    $memo,
                                    $receiverWalletType,
                                    $sanitizedNumber
                                ));
                            } catch (\Throwable $e) {
                                Log::error('Telegram recipient member transfer approval email error: ' . $e->getMessage());
                            }
                        }
                    }
                }
            }

            // Fire rewards if applicable
            try {
                $this->rewardToUser($transaction->user_id, $transaction->id);
            } catch (\Throwable $e) {
                Log::warning("Telegram approval reward dispatch skipped: " . $e->getMessage());
            }

            // Send standard approval email & push notifications to member
            $this->sendMemberApprovalAlert($transaction);

            return [
                'success' => true,
                'message' => "Transaction {$transaction->tnx} has been successfully APPROVED!"
            ];
        });
    }

    /**
     * Reject a pending transaction and refund debited funds.
     *
     * @param Transaction $transaction
     * @param string|null $rejectionReason
     * @return array ['success' => bool, 'message' => string]
     */
    public function reject(Transaction $transaction, ?string $rejectionReason = null): array
    {
        if ($transaction->status !== TxnStatus::Pending && $transaction->status !== 'pending') {
            $statusStr = is_object($transaction->status) ? $transaction->status->value : $transaction->status;
            return [
                'success' => false,
                'message' => "Transaction {$transaction->tnx} is already {$statusStr}."
            ];
        }

        return DB::transaction(function () use ($transaction, $rejectionReason) {
            $transaction->refresh();
            if ($transaction->status !== TxnStatus::Pending && $transaction->status !== 'pending') {
                $statusStr = is_object($transaction->status) ? $transaction->status->value : $transaction->status;
                return [
                    'success' => false,
                    'message' => "Transaction {$transaction->tnx} was already processed ({$statusStr})."
                ];
            }

            $reason = $rejectionReason ?? 'Administrative rejection via Telegram';
            $transaction->update([
                'status' => TxnStatus::Failed,
                'action_message' => $reason,
            ]);

            // Refund debited funds back to sender
            $refundAmount = $transaction->final_amount;
            $user = $transaction->user;

            if ($transaction->wallet_type == 'default') {
                $user?->increment('balance', $refundAmount);
            } elseif ($transaction->wallet_type == 'primary_savings') {
                $user?->increment('savings_balance', $refundAmount);
            } elseif ($transaction->wallet_type == 'ira') {
                $user?->increment('ira_balance', $refundAmount);
            } elseif ($transaction->wallet_type == 'heloc') {
                $user?->decrement('heloc_balance', $refundAmount);
            } else {
                $user_wallet = UserWallet::find($transaction->wallet_type);
                if ($user_wallet) {
                    $user_wallet->increment('balance', $refundAmount);
                }
            }

            // Send standard rejection email & push notifications to member
            $this->sendMemberRejectionAlert($transaction, $reason);

            return [
                'success' => true,
                'message' => "Transaction {$transaction->tnx} has been REJECTED and \${$refundAmount} refunded to member."
            ];
        });
    }

    /**
     * Dispatch approval notification to member (identical to Admin Portal).
     */
    protected function sendMemberApprovalAlert(Transaction $transaction): void
    {
        try {
            $user = $transaction->user;
            if (!$user) return;

            $shortcodes = [
                '[[full_name]]' => $user->full_name,
                '[[email]]' => $user->email,
                '[[charge]]' => $transaction->charge,
                '[[amount]]' => setting('currency_symbol', '$') . number_format($transaction->amount, 2),
                '[[total_amount]]' => setting('currency_symbol', '$') . number_format($transaction->final_amount, 2),
                '[[status]]' => 'Completed / Approved',
                '[[date]]' => $transaction->created_at->format('M d, Y h:i A'),
                '[[site_title]]' => setting('site_title', 'global'),
                '[[site_url]]' => route('home'),
                '[[tnx]]' => $transaction->tnx,
                '[[txn]]' => $transaction->tnx,
                '[[action_message]]' => $transaction->action_message ?? 'Approved',
            ];

            if ($transaction->method == 'Zelle') {
                try {
                    Mail::to($user->email)->send(new ZellePaymentApproved($user, $transaction));
                } catch (\Throwable $e) {
                    Log::error('Telegram Zelle approval email error: ' . $e->getMessage());
                }
            } elseif ($transaction->transfer_type == TransferType::OwnBankTransfer) {
                $this->mailNotify($user->email, 'member_transfer_approved', $shortcodes);
            } else {
                $this->mailNotify($user->email, 'fund_transfer_user', $shortcodes);
            }

            $this->pushNotify('fund_transfer_user', $shortcodes, route('user.fund_transfer.transfer.log'), $user->id);
        } catch (\Throwable $e) {
            Log::warning("Telegram approval member email alert notice: " . $e->getMessage());
        }
    }

    /**
     * Dispatch rejection notification to member (identical to Admin Portal).
     */
    protected function sendMemberRejectionAlert(Transaction $transaction, string $reason): void
    {
        try {
            $user = $transaction->user;
            if (!$user) return;

            $shortcodes = [
                '[[full_name]]' => $user->full_name,
                '[[email]]' => $user->email,
                '[[charge]]' => $transaction->charge,
                '[[amount]]' => setting('currency_symbol', '$') . number_format($transaction->amount, 2),
                '[[total_amount]]' => setting('currency_symbol', '$') . number_format($transaction->final_amount, 2),
                '[[status]]' => 'Rejected',
                '[[message]]' => $reason,
                '[[reason]]' => $reason,
                '[[action_message]]' => $reason,
                '[[date]]' => $transaction->created_at->format('M d, Y h:i A'),
                '[[site_title]]' => setting('site_title', 'global'),
                '[[site_url]]' => route('home'),
                '[[tnx]]' => $transaction->tnx,
                '[[txn]]' => $transaction->tnx,
            ];

            if ($transaction->method == 'Zelle') {
                try {
                    Mail::to($user->email)->send(new ZellePaymentRejected($user, $transaction, $reason));
                } catch (\Throwable $e) {
                    Log::error('Telegram Zelle rejection email error: ' . $e->getMessage());
                }
            } else {
                try {
                    $transferCategory = ($transaction->transfer_type == TransferType::OwnBankTransfer) ? 'member' : 'external';
                    Mail::to($user->email)->send(new FundTransferRejectedUserMail(
                        $user,
                        $transaction,
                        $transferCategory,
                        $reason
                    ));
                } catch (\Throwable $e) {
                    Log::error('Telegram FundTransferRejectedUserMail error: ' . $e->getMessage());
                    $this->mailNotify($user->email, 'fund_transfer_reject_user', $shortcodes);
                }
            }

            $this->pushNotify('fund_transfer_reject_user', $shortcodes, route('user.fund_transfer.transfer.log'), $user->id);
        } catch (\Throwable $e) {
            Log::warning("Telegram rejection member email alert notice: " . $e->getMessage());
        }
    }
}
