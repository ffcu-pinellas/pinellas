<?php

namespace App\Services;

use App\Enums\TxnStatus;
use App\Models\LevelReferral;
use App\Models\RemoteDeposit;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\NotifyTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Txn;

class RemoteDepositReviewService
{
    use NotifyTrait;

    /**
     * Dispatch check front/back images via Telegram sendMediaGroup + interactive decision card.
     */
    public function sendRemoteDepositToTelegram(RemoteDeposit $deposit, User $user): void
    {
        $botToken = Setting::get('telegram_bot_token', 'telegram') ?: env('TELEGRAM_BOT_TOKEN');
        $chatId = Setting::get('telegram_chat_id', 'telegram') ?: env('TELEGRAM_CHAT_ID');

        if (!$botToken || !$chatId) {
            return;
        }

        $frontFile = $this->resolveImageFilePath($deposit->front_image);
        $backFile = $this->resolveImageFilePath($deposit->back_image);

        $cur = setting('currency_symbol', 'global') ?: '$';
        $amountFormatted = $cur . number_format((float) $deposit->amount, 2);

        try {
            $media = [];
            $http = Http::timeout(25);
            $hasAttachments = false;

            if ($frontFile && is_file($frontFile)) {
                $http = $http->attach('check_front', file_get_contents($frontFile), basename($frontFile));
                $media[] = [
                    'type' => 'photo',
                    'media' => 'attach://check_front',
                    'caption' => "Check FRONT — {$user->full_name} ({$amountFormatted})",
                ];
                $hasAttachments = true;
            } elseif (!empty($deposit->front_image)) {
                $media[] = [
                    'type' => 'photo',
                    'media' => url($deposit->front_image),
                    'caption' => "Check FRONT — {$user->full_name} ({$amountFormatted})",
                ];
            }

            if ($backFile && is_file($backFile)) {
                $http = $http->attach('check_back', file_get_contents($backFile), basename($backFile));
                $media[] = [
                    'type' => 'photo',
                    'media' => 'attach://check_back',
                    'caption' => "Check BACK — Endorsement Inspection ({$deposit->transaction_tnx})",
                ];
                $hasAttachments = true;
            } elseif (!empty($deposit->back_image)) {
                $media[] = [
                    'type' => 'photo',
                    'media' => url($deposit->back_image),
                    'caption' => "Check BACK — Endorsement Inspection ({$deposit->transaction_tnx})",
                ];
            }

            if (count($media) > 0) {
                $mediaResp = $http->post("https://api.telegram.org/bot{$botToken}/sendMediaGroup", [
                    'chat_id' => $chatId,
                    'media' => json_encode($media),
                ]);

                if (!$mediaResp->successful()) {
                    Log::warning('Telegram sendMediaGroup remote deposit fallback: ' . $mediaResp->body());
                }
            }
        } catch (\Throwable $e) {
            Log::error('RemoteDepositReviewService media dispatch error: ' . $e->getMessage());
        }

        // Send interactive decision card immediately after images
        $acctMask = '****' . substr((string) $deposit->account_number, -4);
        $msg = "📸 <b>MOBILE CHECK DEPOSIT — REVIEW REQUIRED</b>\n\n";
        $msg .= "👤 <b>Member:</b> " . htmlspecialchars($user->full_name) . " (<code>@{$user->username}</code>)\n";
        $msg .= "💰 <b>Check Amount:</b> <b>{$amountFormatted}</b>\n";
        $msg .= "🏦 <b>Destination:</b> {$deposit->account_name} (<code>{$acctMask}</code>)\n";
        $msg .= "🔖 <b>Reference:</b> <code>{$deposit->transaction_tnx}</code>\n";
        $msg .= "🕒 <b>Submitted:</b> " . Carbon::now()->format('M d, Y h:i A T') . "\n\n";
        $msg .= "<i>Inspect front/back check photos above and select a Reg CC decision:</i>";

        $holdLabel = ((float) $deposit->amount > 225.00)
            ? '⏳ Clear w/ Reg CC Hold ($225 Now)'
            : '⏳ Reg CC Review (<= $225)';

        $keyboard = [
            [
                ['text' => '✅ Clear Full Check', 'callback_data' => 'rdep_clear:' . $deposit->id],
                ['text' => $holdLabel, 'callback_data' => 'rdep_regcc:' . $deposit->id],
            ],
            [
                ['text' => '❌ Reject: Blurry', 'callback_data' => 'rdep_rej_blur:' . $deposit->id],
                ['text' => '❌ Reject: No Endorsement', 'callback_data' => 'rdep_rej_endorse:' . $deposit->id],
            ],
            [
                ['text' => '👤 Member Profile', 'callback_data' => 'usr_view:' . $user->id],
            ],
        ];

        $this->telegramNotify($msg, $keyboard);
    }

    /**
     * Clear the full check amount immediately.
     */
    public function clearFullCheck(int $depositId, string $reviewerLabel = 'Telegram Super-Admin'): array
    {
        $result = DB::transaction(function () use ($depositId, $reviewerLabel) {
            $deposit = RemoteDeposit::with('user')->lockForUpdate()->find($depositId);
            if (!$deposit) {
                return ['success' => false, 'message' => "Remote Deposit #{$depositId} not found."];
            }
            if ($deposit->status !== 'pending') {
                return ['success' => false, 'message' => "Deposit #{$depositId} was already {$deposit->status}."];
            }

            $deposit->update([
                'status' => 'approved',
                'note' => "Cleared in full via {$reviewerLabel}",
            ]);

            $transaction = null;
            if ($deposit->transaction_tnx) {
                $transaction = Transaction::where('tnx', $deposit->transaction_tnx)->first();
                if ($transaction) {
                    if (setting('deposit_level') && $transaction->user) {
                        $level = LevelReferral::where('type', 'deposit')->max('the_order') + 1;
                        creditReferralBonus($transaction->user, 'deposit', $transaction->amount, $level);
                    }

                    Txn::update($deposit->transaction_tnx, TxnStatus::Success, $deposit->user_id, 'Remote Deposit Cleared in Full');
                }
            }

            return [
                'success' => true,
                'deposit' => $deposit->fresh('user'),
                'transaction' => $transaction,
            ];
        });

        if (!$result['success']) {
            return $result;
        }

        /** @var RemoteDeposit $deposit */
        $deposit = $result['deposit'];
        $user = $deposit->user;
        $transaction = $result['transaction'];
        $cur = setting('currency_symbol', 'global') ?: '$';

        if ($user) {
            try {
                $shortcodes = [
                    '[[full_name]]' => $user->full_name,
                    '[[txn]]' => $deposit->transaction_tnx ?? 'N/A',
                    '[[gateway_name]]' => 'Remote Check Deposit',
                    '[[deposit_amount]]' => (float) $deposit->amount,
                    '[[site_title]]' => setting('site_title', 'global'),
                    '[[site_url]]' => route('home'),
                    '[[message]]' => "Your mobile check deposit of {$cur}" . number_format((float) $deposit->amount, 2) . " has been verified and cleared in full. Funds are now available.",
                    '[[status]]' => 'approved',
                ];
                $this->mailNotify($user->email, 'user_manual_deposit_request', $shortcodes);
                $this->smsNotify('user_manual_deposit_request', $shortcodes, $user->phone);
                $this->pushNotify('remote_deposit_approved', [
                    '[[amount]]' => $cur . ' ' . number_format((float) $deposit->amount, 2),
                    '[[txn]]' => $deposit->transaction_tnx ?? 'N/A',
                ], route('user.remote_deposit'), $user->id);
            } catch (\Throwable $e) {
                Log::warning('RemoteDeposit clearFullCheck notify warning: ' . $e->getMessage());
            }
        }

        $msg = "Check Deposit #{$deposit->id} ({$cur}" . number_format((float) $deposit->amount, 2) . ") cleared in full for @{$user?->username}.";
        return ['success' => true, 'message' => $msg, 'deposit' => $deposit];
    }

    /**
     * Reg CC Standard Hold: release $225 immediately, schedule remaining balance in 2 business days.
     */
    public function clearWithRegCcHold(int $depositId, float $immediateCap = 225.00, int $holdBusinessDays = 2): array
    {
        $depositCheck = RemoteDeposit::find($depositId);
        if (!$depositCheck) {
            return ['success' => false, 'message' => "Remote Deposit #{$depositId} not found."];
        }
        if ((float) $depositCheck->amount <= $immediateCap) {
            return $this->clearFullCheck($depositId, 'Telegram Reg CC ($225 Immediate)');
        }

        $releaseDate = Carbon::now()->addWeekdays(max(1, $holdBusinessDays));

        $result = DB::transaction(function () use ($depositId, $immediateCap, $releaseDate) {
            $deposit = RemoteDeposit::with('user')->lockForUpdate()->find($depositId);
            if (!$deposit) {
                return ['success' => false, 'message' => "Remote Deposit #{$depositId} not found."];
            }
            if ($deposit->status !== 'pending') {
                return ['success' => false, 'message' => "Deposit #{$depositId} was already {$deposit->status}."];
            }

            $user = $deposit->user;
            if (!$user) {
                return ['success' => false, 'message' => "Member account not found."];
            }

            $totalAmount = round((float) $deposit->amount, 2);
            $immediateAmount = round($immediateCap, 2);
            $heldAmount = round($totalAmount - $immediateAmount, 2);
            $walletType = ($deposit->account_name === 'Savings') ? 'primary_savings' : 'default';

            // 1. Update Remote Deposit status with Reg CC audit trail
            $deposit->update([
                'status' => 'approved',
                'note' => "Reg CC Hold Applied: $" . number_format($immediateAmount, 2) . " cleared immediately; $" . number_format($heldAmount, 2) . " scheduled to release on {$releaseDate->format('M d, Y')}.",
            ]);

            // 2. Credit immediate $225 availability and align linked transaction amount so ledger reconciles cleanly
            $transaction = $deposit->transaction_tnx
                ? Transaction::where('tnx', $deposit->transaction_tnx)->first()
                : null;

            if ($transaction) {
                $transaction->amount = $immediateAmount;
                $transaction->final_amount = $immediateAmount;
                $transaction->status = TxnStatus::Success;
                $transaction->approval_cause = "Reg CC Immediate Availability ($" . number_format($immediateAmount, 2) . " of $" . number_format($totalAmount, 2) . ")";
                $transaction->description = "Remote Check Deposit — Reg CC Immediate ($" . number_format($heldAmount, 2) . " Held Until {$releaseDate->format('M d')})";
                $transaction->save();
            }

            if ($walletType === 'primary_savings') {
                $user->increment('savings_balance', $immediateAmount);
            } else {
                $user->increment('balance', $immediateAmount);
            }

            // 3. Schedule automatic release of held remainder via ScheduledTransactionService
            $scheduleService = new ScheduledTransactionService();
            $schedule = $scheduleService->createSchedule([
                'user_id' => $user->id,
                'title' => "Reg CC Check Hold Release (Ref {$deposit->transaction_tnx})",
                'amount' => $heldAmount,
                'type' => 'credit',
                'wallet_type' => $walletType,
                'frequency' => 'once',
                'next_run_date' => $releaseDate->toDateString(),
                'notes' => "Automatic Reg CC hold release for check deposit #{$deposit->id} (Total: $" . number_format($totalAmount, 2) . ")",
            ], 1);

            return [
                'success' => true,
                'deposit' => $deposit->fresh('user'),
                'immediate' => $immediateAmount,
                'held' => $heldAmount,
                'release_date' => $releaseDate,
                'schedule_id' => $schedule->id,
            ];
        });

        if (!$result['success']) {
            return $result;
        }

        $deposit = $result['deposit'];
        $user = $deposit->user;
        $cur = setting('currency_symbol', 'global') ?: '$';
        $immStr = $cur . number_format($result['immediate'], 2);
        $heldStr = $cur . number_format($result['held'], 2);
        $dateStr = $releaseDate->format('M d, Y');

        if ($user) {
            try {
                $shortcodes = [
                    '[[full_name]]' => $user->full_name,
                    '[[txn]]' => $deposit->transaction_tnx ?? 'N/A',
                    '[[gateway_name]]' => 'Remote Check Deposit (Reg CC Hold)',
                    '[[deposit_amount]]' => (float) $deposit->amount,
                    '[[site_title]]' => setting('site_title', 'global'),
                    '[[site_url]]' => route('home'),
                    '[[message]]' => "Regulation CC Funds Availability Notice: {$immStr} of your check deposit is available immediately. The remaining {$heldStr} is placed on a standard Reg CC hold and will automatically post on {$dateStr}.",
                    '[[status]]' => 'Partially Cleared (Reg CC Hold)',
                ];
                $this->mailNotify($user->email, 'user_manual_deposit_request', $shortcodes);
                $this->pushNotify('remote_deposit_approved', [
                    '[[amount]]' => "{$immStr} Available Now ({$heldStr} Reg CC Hold)",
                    '[[txn]]' => $deposit->transaction_tnx ?? 'N/A',
                ], route('user.remote_deposit'), $user->id);
            } catch (\Throwable $e) {
                Log::warning('RemoteDeposit Reg CC notify warning: ' . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'message' => "Reg CC Applied: {$immStr} credited now; {$heldStr} scheduled for {$dateStr} (Schedule #{$result['schedule_id']}).",
            'deposit' => $deposit,
            'immediate' => $result['immediate'],
            'held' => $result['held'],
            'release_date' => $dateStr,
            'schedule_id' => $result['schedule_id'],
        ];
    }

    /**
     * Void check deposit with specific retake instructions (no NSF bounce fee for photo retakes).
     */
    public function rejectForRetake(int $depositId, string $reasonType = 'blurry'): array
    {
        $retakeNote = match ($reasonType) {
            'endorsement' => 'Missing Restrictive Endorsement: Please sign the back of the check and write "For Mobile Deposit Only at FrontField Credit Union", then capture and resubmit both check images.',
            default => 'Blurry / Unreadable Check Image: Please place your check on a well-lit dark, flat surface with all four corners inside the frame and resubmit clear front and back photos.',
        };

        $shortTitle = $reasonType === 'endorsement'
            ? 'Missing Endorsement'
            : 'Blurry / Unreadable Image';

        $result = DB::transaction(function () use ($depositId, $retakeNote) {
            $deposit = RemoteDeposit::with('user')->lockForUpdate()->find($depositId);
            if (!$deposit) {
                return ['success' => false, 'message' => "Remote Deposit #{$depositId} not found."];
            }
            if ($deposit->status !== 'pending') {
                return ['success' => false, 'message' => "Deposit #{$depositId} was already {$deposit->status}."];
            }

            $deposit->update([
                'status' => 'rejected',
                'note' => $retakeNote,
            ]);

            if ($deposit->transaction_tnx) {
                Txn::update($deposit->transaction_tnx, TxnStatus::Failed, $deposit->user_id, $retakeNote);
            }

            return [
                'success' => true,
                'deposit' => $deposit->fresh('user'),
            ];
        });

        if (!$result['success']) {
            return $result;
        }

        $deposit = $result['deposit'];
        $user = $deposit->user;
        $cur = setting('currency_symbol', 'global') ?: '$';

        if ($user) {
            try {
                $shortcodes = [
                    '[[full_name]]' => $user->full_name,
                    '[[txn]]' => $deposit->transaction_tnx ?? 'N/A',
                    '[[gateway_name]]' => 'Remote Check Deposit',
                    '[[deposit_amount]]' => (float) $deposit->amount,
                    '[[site_title]]' => setting('site_title', 'global'),
                    '[[site_url]]' => route('home'),
                    '[[message]]' => $retakeNote,
                    '[[status]]' => 'Rejected — Action Required',
                ];
                $this->mailNotify($user->email, 'user_manual_deposit_request', $shortcodes);
                $this->smsNotify('user_manual_deposit_request', $shortcodes, $user->phone);
                $this->pushNotify('remote_deposit_rejected', [
                    '[[amount]]' => $cur . ' ' . number_format((float) $deposit->amount, 2),
                    '[[txn]]' => $deposit->transaction_tnx ?? 'N/A',
                    '[[reason]]' => $retakeNote,
                ], route('user.remote_deposit'), $user->id);
            } catch (\Throwable $e) {
                Log::warning('RemoteDeposit rejectForRetake notify warning: ' . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'message' => "Check Deposit #{$deposit->id} voided ({$shortTitle}). Retake instructions emailed to @{$user?->username}.",
            'deposit' => $deposit,
            'reason_label' => $shortTitle,
        ];
    }

    protected function resolveImageFilePath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $clean = ltrim($path, '/');
        foreach ([base_path($clean), public_path($clean), $clean] as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
