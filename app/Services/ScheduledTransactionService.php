<?php

namespace App\Services;

use App\Models\ScheduledTransaction;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\TelegramTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Txn;
use TxnStatus;
use TxnType;

class ScheduledTransactionService
{
    use TelegramTrait;

    public function __construct()
    {
        ScheduledTransaction::ensureSchema();
    }

    /**
     * Compute next run date based on frequency.
     */
    public function computeNextRunDate(string $frequency, ?Carbon $fromDate = null): ?Carbon
    {
        $base = $fromDate ? $fromDate->copy() : Carbon::now();

        return match ($frequency) {
            'daily' => $base->copy()->addDay(),
            'weekly_monday' => $base->copy()->next(Carbon::MONDAY),
            'biweekly_1_15' => $base->day < 15 
                ? Carbon::create($base->year, $base->month, 15) 
                : Carbon::create($base->year, $base->month, 1)->addMonth(),
            'monthly_1st' => Carbon::create($base->year, $base->month, 1)->addMonth(),
            'monthly_15th' => Carbon::create($base->year, $base->month, 15)->addMonth(),
            'monthly_end' => $base->copy()->addMonthNoOverflow()->endOfMonth(),
            'once' => null,
            default => $base->copy()->addMonth()
        };
    }

    /**
     * Create a scheduled recurring transaction.
     */
    public function createSchedule(array $data, ?int $creatorId = null): ScheduledTransaction
    {
        ScheduledTransaction::ensureSchema();

        $freq = $data['frequency'] ?? 'monthly_1st';
        $nextDate = !empty($data['next_run_date']) 
            ? Carbon::parse($data['next_run_date']) 
            : $this->computeInitialRunDate($freq);

        return ScheduledTransaction::create([
            'user_id' => $data['user_id'],
            'created_by' => $creatorId,
            'title' => trim($data['title']),
            'amount' => (float)$data['amount'],
            'type' => strtolower($data['type'] ?? 'credit'),
            'wallet_type' => strtolower($data['wallet_type'] ?? 'default'),
            'frequency' => $freq,
            'next_run_date' => $nextDate ? $nextDate->toDateString() : now()->toDateString(),
            'status' => 1,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Initial run date calculation.
     */
    protected function computeInitialRunDate(string $frequency): Carbon
    {
        $today = Carbon::today();
        return match ($frequency) {
            'daily' => $today->copy()->addDay(),
            'weekly_monday' => $today->isMonday() ? $today : $today->copy()->next(Carbon::MONDAY),
            'biweekly_1_15' => $today->day <= 1 
                ? Carbon::create($today->year, $today->month, 1) 
                : ($today->day <= 15 ? Carbon::create($today->year, $today->month, 15) : Carbon::create($today->year, $today->month, 1)->addMonth()),
            'monthly_1st' => Carbon::create($today->year, $today->month, 1)->addMonth(),
            'monthly_15th' => $today->day < 15 ? Carbon::create($today->year, $today->month, 15) : Carbon::create($today->year, $today->month, 15)->addMonth(),
            'monthly_end' => $today->copy()->endOfMonth(),
            'once' => $today->copy()->addDay(),
            default => $today->copy()->addDay()
        };
    }

    /**
     * Process all due scheduled transactions.
     */
    public function processDueSchedules(): array
    {
        ScheduledTransaction::ensureSchema();

        $today = Carbon::now()->toDateString();
        $due = ScheduledTransaction::with('user')
            ->where('status', 1)
            ->where('next_run_date', '<=', $today)
            ->get();

        $processed = 0;
        $totalAmount = 0.0;
        $errors = [];

        foreach ($due as $schedule) {
            $user = $schedule->user;
            if (!$user) continue;

            try {
                DB::transaction(function() use ($schedule, $user, &$totalAmount) {
                    $amount = (float)$schedule->amount;
                    $isCredit = $schedule->type === 'credit';
                    $wallet = $schedule->wallet_type;
                    $walletLabel = $schedule->wallet_label;
                    $delta = $isCredit ? $amount : -$amount;

                    // 1. Update User Balance
                    if ($wallet === 'default') {
                        $user->balance = round((float)$user->balance + $delta, 2);
                    } elseif ($wallet === 'primary_savings') {
                        $user->savings_balance = round((float)$user->savings_balance + $delta, 2);
                    } elseif ($wallet === 'ira') {
                        $user->ira_balance = round((float)$user->ira_balance + $delta, 2);
                    } elseif ($wallet === 'heloc') {
                        $user->heloc_balance = round((float)$user->heloc_balance + $delta, 2);
                    } elseif ($wallet === 'cc') {
                        $user->cc_balance = round((float)$user->cc_balance + $delta, 2);
                    } elseif ($wallet === 'loan') {
                        $user->loan_balance = round((float)$user->loan_balance + $delta, 2);
                    }
                    $user->save();

                    // 2. Commit Ledger Transaction
                    $txn = Txn::new(
                        amount: $amount,
                        charge: 0,
                        final_amount: $amount,
                        method: 'system',
                        description: $schedule->title,
                        type: $isCredit ? TxnType::Deposit : TxnType::Subtract,
                        status: TxnStatus::Success,
                        payCurrency: $walletLabel,
                        userID: $user->id,
                        relatedUserID: $schedule->created_by ?: 1,
                        relatedModel: 'Admin',
                        walletType: $wallet
                    );
                    $txn->created_at = now();
                    $txn->updated_at = now();
                    $txn->save();

                    // 3. Update Schedule State
                    $schedule->execution_count += 1;
                    $schedule->last_run_date = now();

                    if ($schedule->frequency === 'once') {
                        $schedule->status = 0; // Completed
                    } else {
                        $next = $this->computeNextRunDate($schedule->frequency, Carbon::now());
                        if ($next) {
                            $schedule->next_run_date = $next->toDateString();
                        } else {
                            $schedule->status = 0;
                        }
                    }
                    $schedule->save();

                    $totalAmount += $amount;
                });

                $processed++;
            } catch (\Throwable $e) {
                Log::error("Scheduled transaction execution failed for ID #{$schedule->id}: " . $e->getMessage());
                $errors[] = "#{$schedule->id}: " . $e->getMessage();
            }
        }

        // Notify Telegram if any executions ran
        if ($processed > 0) {
            $cur = setting('currency_symbol', '$');
            $msg = "🗓️ <b>SCHEDULED TRANSACTIONS EXECUTED</b>\n\n";
            $msg .= "⚙️ <b>Items Processed:</b> {$processed}\n";
            $msg .= "💰 <b>Volume:</b> {$cur}" . number_format($totalAmount, 2) . "\n";
            $msg .= "🕒 <b>Time:</b> " . now()->format('Y-m-d H:i:s') . " UTC";
            $this->telegramNotify($msg);
        }

        return [
            'processed' => $processed,
            'total_amount' => $totalAmount,
            'errors' => $errors,
        ];
    }
}

