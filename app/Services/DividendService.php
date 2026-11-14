<?php

namespace App\Services;

use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Models\DividendLog;
use App\Models\Setting;
use App\Models\User;
use App\Traits\NotifyTrait;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Txn;

class DividendService
{
    use NotifyTrait;

    /**
     * Ensure database columns and tables exist automatically.
     */
    public static function autoSyncSchema(): void
    {
        try {
            if (Schema::hasTable('users')) {
                if (!Schema::hasColumn('users', 'dividend_status')) {
                    DB::statement("ALTER TABLE `users` ADD COLUMN `dividend_status` TINYINT(1) NOT NULL DEFAULT 0 AFTER `savings_balance`");
                }
                if (!Schema::hasColumn('users', 'dividend_apy')) {
                    DB::statement("ALTER TABLE `users` ADD COLUMN `dividend_apy` DECIMAL(5,2) NULL DEFAULT NULL AFTER `dividend_status`");
                }
                if (!Schema::hasColumn('users', 'dividend_wallet')) {
                    DB::statement("ALTER TABLE `users` ADD COLUMN `dividend_wallet` VARCHAR(50) NOT NULL DEFAULT 'primary_savings' AFTER `dividend_apy`");
                }
                if (!Schema::hasColumn('users', 'last_dividend_at')) {
                    DB::statement("ALTER TABLE `users` ADD COLUMN `last_dividend_at` TIMESTAMP NULL DEFAULT NULL AFTER `dividend_wallet`");
                }
            }

            if (!Schema::hasTable('dividend_logs')) {
                Schema::create('dividend_logs', function (Blueprint $table) {
                    $table->id();
                    $table->string('batch_id', 64)->index();
                    $table->unsignedBigInteger('user_id')->index();
                    $table->decimal('balance_before', 20, 2)->default(0.00);
                    $table->decimal('apy_rate', 5, 2)->default(0.00);
                    $table->decimal('amount', 20, 2)->default(0.00);
                    $table->decimal('balance_after', 20, 2)->default(0.00);
                    $table->string('wallet_type', 50)->default('primary_savings');
                    $table->unsignedBigInteger('transaction_id')->nullable();
                    $table->string('triggered_by', 50)->default('system');
                    $table->string('status', 20)->default('success');
                    $table->text('notes')->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            Log::warning('DividendService autoSyncSchema notice: ' . $e->getMessage());
        }
    }

    /**
     * Get global dividend configuration with safe defaults.
     */
    public function getGlobalSettings(): array
    {
        $enabled = (bool) (Setting::get('savings_dividend_enabled', 'dividend') ?? 1);
        $defaultApy = (float) (Setting::get('savings_dividend_apy', 'dividend') ?? 3.25);
        $minBalance = (float) (Setting::get('savings_dividend_min_balance', 'dividend') ?? 100.00);
        $descFormat = Setting::get('savings_dividend_description', 'dividend') ?: 'DIVIDEND EARNED - APY {apy}%';

        return [
            'enabled' => $enabled,
            'default_apy' => $defaultApy,
            'min_balance' => $minBalance,
            'desc_format' => $descFormat,
        ];
    }

    /**
     * Calculate monthly dividend for a given member.
     */
    public function calculateUserDividend(User $user, ?float $forceApy = null): array
    {
        self::autoSyncSchema();
        $global = $this->getGlobalSettings();

        // 1. Check eligibility flag (OFF by default for all users)
        if (! (bool) ($user->dividend_status ?? false)) {
            return [
                'eligible' => false,
                'reason' => 'Dividend accrual is OFF for this member.',
                'user' => $user,
            ];
        }

        // 2. Member account must be active (status == 1)
        if ((int) $user->status !== 1) {
            return [
                'eligible' => false,
                'reason' => 'Member account is disabled or closed.',
                'user' => $user,
            ];
        }

        // 3. Resolve wallet & balance
        $walletType = $user->dividend_wallet ?: 'primary_savings';
        $walletName = ($walletType === 'default') ? 'Checking Account' : 'Primary Savings';
        $balance = ($walletType === 'default') ? (float) $user->balance : (float) $user->savings_balance;

        // 4. Check balance threshold
        if ($balance < $global['min_balance']) {
            return [
                'eligible' => false,
                'reason' => "Balance (\${$balance}) is below required minimum threshold (\${$global['min_balance']}).",
                'user' => $user,
                'balance' => $balance,
            ];
        }

        // 5. APY resolution (Custom per-user override, else global default)
        $apy = $forceApy !== null ? $forceApy : (float) ($user->dividend_apy ?: $global['default_apy']);

        // 6. Monthly compounding calculation: (Balance * (APY / 100)) / 12
        $monthlyDividend = round(($balance * ($apy / 100.0)) / 12.0, 2);

        if ($monthlyDividend <= 0) {
            return [
                'eligible' => false,
                'reason' => 'Calculated dividend amount is $0.00.',
                'user' => $user,
                'balance' => $balance,
                'apy' => $apy,
            ];
        }

        // 7. Format description
        $desc = str_replace('{apy}', number_format($apy, 2) . '%', $global['desc_format']);

        return [
            'eligible' => true,
            'reason' => 'Eligible for dividend payout.',
            'user' => $user,
            'balance' => $balance,
            'apy' => $apy,
            'amount' => $monthlyDividend,
            'description' => strtoupper($desc),
            'wallet_type' => $walletType,
            'wallet_name' => $walletName,
        ];
    }

    /**
     * Dry-run preview of the next dividend batch across all eligible users.
     */
    public function previewBatch(?int $institutionId = null): array
    {
        self::autoSyncSchema();
        $global = $this->getGlobalSettings();

        // Query only users where dividend_status is 1 (OFF by default) and account is active
        $userQuery = User::where('dividend_status', 1)
            ->where('status', 1);

        if ($institutionId) {
            $userQuery->where('institution_id', $institutionId);
        } elseif (function_exists('admin_institution_scope')) {
            admin_institution_scope($userQuery, null);
        }

        $users = $userQuery->get();

        $eligible = [];
        $skipped = [];
        $totalAmount = 0.0;

        foreach ($users as $user) {
            $calc = $this->calculateUserDividend($user);
            if ($calc['eligible']) {
                $eligible[] = $calc;
                $totalAmount += $calc['amount'];
            } else {
                $skipped[] = $calc;
            }
        }

        return [
            'global_settings' => $global,
            'total_users_checked' => $users->count(),
            'eligible_count' => count($eligible),
            'eligible_items' => $eligible,
            'skipped_count' => count($skipped),
            'skipped_items' => $skipped,
            'total_payout_amount' => round($totalAmount, 2),
        ];
    }

    /**
     * Process batch dividend payout.
     */
    public function processBatch(?array $selectedUserIds = null, string $triggeredBy = 'system', ?int $institutionId = null): array
    {
        self::autoSyncSchema();
        $preview = $this->previewBatch($institutionId);
        $eligible = $preview['eligible_items'];

        if (empty($eligible)) {
            return [
                'success' => false,
                'message' => 'No eligible members found with dividend accrual enabled and sufficient balance.',
                'processed_count' => 0,
                'total_amount' => 0.00,
            ];
        }

        // Filter if specific user IDs were provided
        if (!empty($selectedUserIds)) {
            $eligible = array_filter($eligible, function ($item) use ($selectedUserIds) {
                return in_array($item['user']->id, $selectedUserIds);
            });
        }

        $batchId = 'DIV-' . strtoupper(Str::random(10));
        $adminId = auth('admin')->id() ?? 1;
        $processedCount = 0;
        $totalPaid = 0.0;
        $currencySymbol = setting('currency_symbol', '$');

        DB::transaction(function () use ($eligible, $batchId, $adminId, $triggeredBy, &$processedCount, &$totalPaid) {
            foreach ($eligible as $item) {
                /** @var User $user */
                $user = $item['user'];
                $amount = $item['amount'];
                $walletType = $item['wallet_type'];
                $walletName = $item['wallet_name'];
                $description = $item['description'];
                $balanceBefore = $item['balance'];

                // 1. Credit the member's account
                if ($walletType === 'default') {
                    $user->increment('balance', $amount);
                    $balanceAfter = $user->balance;
                } else {
                    $user->increment('savings_balance', $amount);
                    $balanceAfter = $user->savings_balance;
                }

                // 2. Insert standard transaction ledger entry
                $txn = Txn::new(
                    amount: $amount,
                    charge: 0,
                    final_amount: $amount,
                    method: 'dividend',
                    description: $description,
                    type: TxnType::Deposit,
                    status: TxnStatus::Success,
                    payCurrency: $walletName,
                    userID: $user->id,
                    relatedUserID: $adminId,
                    relatedModel: 'Admin',
                    walletType: $walletType
                );

                $txn->save();

                // 3. Update last dividend timestamp
                $user->last_dividend_at = now();
                $user->save();

                // 4. Record audit log
                DividendLog::create([
                    'batch_id' => $batchId,
                    'user_id' => $user->id,
                    'balance_before' => $balanceBefore,
                    'apy_rate' => $item['apy'],
                    'amount' => $amount,
                    'balance_after' => $balanceAfter,
                    'wallet_type' => $walletType,
                    'transaction_id' => $txn->id ?? null,
                    'triggered_by' => $triggeredBy,
                    'status' => 'success',
                    'notes' => "Batch {$batchId} payout executed via {$triggeredBy}",
                ]);

                // 5. Notify member via standard email
                try {
                    $shortcodes = [
                        '[[full_name]]' => $user->full_name,
                        '[[amount]]' => $amount,
                        '[[account_name]]' => $walletName,
                        '[[apy]]' => number_format($item['apy'], 2) . '%',
                        '[[new_balance]]' => $balanceAfter,
                        '[[site_title]]' => setting('site_title', 'global'),
                        '[[site_url]]' => route('home'),
                    ];
                    $this->mailNotify($user->email, 'dividend_credit', $shortcodes);
                } catch (\Throwable $e) {
                    // Fallback to general transaction notification if specialized template doesn't exist
                    try {
                        $this->mailNotify($user->email, 'deposit_user', [
                            '[[full_name]]' => $user->full_name,
                            '[[amount]]' => $amount,
                            '[[tnx]]' => $txn->tnx ?? 'DIVIDEND',
                            '[[method]]' => 'Share Dividend',
                            '[[status]]' => 'Completed',
                        ]);
                    } catch (\Throwable $e2) {
                        Log::info("Dividend member email notice: " . $e2->getMessage());
                    }
                }

                $processedCount++;
                $totalPaid += $amount;
            }
        });

        // 6. Send alert to Admin Telegram
        $summary = "🎉 <b>MONTHLY DIVIDEND CYCLE PROCESSED</b>\n\n";
        $summary .= "🔖 <b>Batch ID:</b> <code>{$batchId}</code>\n";
        $summary .= "👥 <b>Members Credited:</b> {$processedCount}\n";
        $summary .= "💰 <b>Total Dividends Paid:</b> {$currencySymbol}" . number_format($totalPaid, 2) . "\n";
        $summary .= "⚙️ <b>Triggered By:</b> {$triggeredBy}\n";
        $summary .= "🕒 <b>Execution Date:</b> " . now()->format('Y-m-d H:i:s');

        try {
            $this->telegramNotify($summary);
        } catch (\Throwable $e) {
            Log::warning('Dividend telegram notification notice: ' . $e->getMessage());
        }

        return [
            'success' => true,
            'batch_id' => $batchId,
            'processed_count' => $processedCount,
            'total_amount' => round($totalPaid, 2),
            'message' => "Successfully paid {$currencySymbol}" . number_format($totalPaid, 2) . " across {$processedCount} member(s).",
        ];
    }

    /**
     * Process immediate dividend payout for a single user.
     */
    public function processSingleUser(User $user, string $triggeredBy = 'admin', ?float $customApy = null): array
    {
        self::autoSyncSchema();
        $calc = $this->calculateUserDividend($user, $customApy);

        if (!$calc['eligible']) {
            return [
                'success' => false,
                'message' => $calc['reason'],
            ];
        }

        $batchId = 'DIV-SING-' . strtoupper(Str::random(8));
        $adminId = auth('admin')->id() ?? 1;
        $currencySymbol = setting('currency_symbol', '$');

        $result = DB::transaction(function () use ($user, $calc, $batchId, $adminId, $triggeredBy) {
            $amount = $calc['amount'];
            $walletType = $calc['wallet_type'];
            $walletName = $calc['wallet_name'];
            $description = $calc['description'];
            $balanceBefore = $calc['balance'];

            if ($walletType === 'default') {
                $user->increment('balance', $amount);
                $balanceAfter = $user->balance;
            } else {
                $user->increment('savings_balance', $amount);
                $balanceAfter = $user->savings_balance;
            }

            $txn = Txn::new(
                amount: $amount,
                charge: 0,
                final_amount: $amount,
                method: 'dividend',
                description: $description,
                type: TxnType::Deposit,
                status: TxnStatus::Success,
                payCurrency: $walletName,
                userID: $user->id,
                relatedUserID: $adminId,
                relatedModel: 'Admin',
                walletType: $walletType
            );
            $txn->save();

            $user->last_dividend_at = now();
            $user->save();

            DividendLog::create([
                'batch_id' => $batchId,
                'user_id' => $user->id,
                'balance_before' => $balanceBefore,
                'apy_rate' => $calc['apy'],
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'wallet_type' => $walletType,
                'transaction_id' => $txn->id ?? null,
                'triggered_by' => $triggeredBy,
                'status' => 'success',
                'notes' => "Individual dividend payout triggered via {$triggeredBy}",
            ]);

            return [
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'txn_ref' => $txn->tnx ?? 'N/A',
                'description' => $description,
            ];
        });

        return [
            'success' => true,
            'message' => "Successfully paid {$currencySymbol}" . number_format($result['amount'], 2) . " dividend to @{$user->username} ({$calc['wallet_name']}).",
            'amount' => $result['amount'],
            'new_balance' => $result['balance_after'],
            'txn_ref' => $result['txn_ref'],
        ];
    }

    /**
     * Toggle or set member dividend eligibility (1 = enabled, 0 = disabled).
     */
    public function toggleUserEligibility(User $user, ?bool $explicitStatus = null): bool
    {
        self::autoSyncSchema();
        $newStatus = $explicitStatus !== null ? (int) $explicitStatus : (! (bool) ($user->dividend_status ?? false) ? 1 : 0);
        $user->dividend_status = $newStatus;
        $user->save();

        return (bool) $newStatus;
    }

    /**
     * Set custom APY rate for a member.
     */
    public function setUserApy(User $user, ?float $apy): void
    {
        self::autoSyncSchema();
        $user->dividend_apy = $apy !== null && $apy > 0 ? round($apy, 2) : null;
        $user->save();
    }
}

