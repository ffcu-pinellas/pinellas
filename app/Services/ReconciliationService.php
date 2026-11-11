<?php

namespace App\Services;

use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReconciliationService
{
    public const WALLET_COLUMNS = [
        'default' => 'balance',
        'primary_savings' => 'savings_balance',
        'ira' => 'ira_balance',
    ];

    public const WALLET_LABELS = [
        'default' => 'Checking (0010)',
        'primary_savings' => 'Primary Savings (0000)',
        'ira' => 'IRA Account',
    ];

    protected const CREDIT_TYPES = [
        'deposit',
        'manual_deposit',
        'receive_money',
        'refund',
        'referral',
        'signup_bonus',
        'portfolio_bonus',
        'reward_redeem',
        'dps_maturity',
        'dps_cancelled',
        'dps_decrease',
        'fdr_cancelled',
        'fdr_decrease',
        'fdr_installment',
        'loan',
    ];

    protected const DEBIT_SETTLED_TYPES = [
        'subtract',
        'send_money',
        'exchange',
        'fund_transfer',
        'withdraw',
        'withdraw_auto',
        'pay_bill',
        'loan_installment',
        'dps_installment',
        'fdr',
        'fdr_increase',
        'dps_increase',
        'fdr_maturity_fee',
    ];

    protected const PRE_DEDUCTED_PENDING_TYPES = [
        'fund_transfer',
        'send_money',
        'withdraw',
        'withdraw_auto',
    ];

    /**
     * Normalize raw DB wallet_type column into canonical keys.
     */
    public function normalizeWalletKey(?string $raw): string
    {
        $val = strtolower(trim((string) $raw));
        if ($val === '' || $val === 'default' || $val === 'checking') {
            return 'default';
        }
        if (in_array($val, ['primary_savings', 'savings_primary', 'savings'], true)) {
            return 'primary_savings';
        }

        return $val;
    }

    /**
     * Audit every standard wallet for a single member against their ledger history.
     */
    public function auditUser(User $user): array
    {
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $ledgerTotals = [
            'default' => 0.0,
            'primary_savings' => 0.0,
            'ira' => 0.0,
        ];
        $counts = [
            'default' => 0,
            'primary_savings' => 0,
            'ira' => 0,
        ];
        $lastTxnDates = [
            'default' => null,
            'primary_savings' => null,
            'ira' => null,
        ];

        foreach ($transactions as $txn) {
            $walletKey = $this->normalizeWalletKey($txn->wallet_type);
            if (!array_key_exists($walletKey, self::WALLET_COLUMNS)) {
                continue;
            }

            $type = $txn->type instanceof TxnType ? $txn->type->value : strtolower((string) $txn->type);
            $status = $txn->status instanceof TxnStatus ? $txn->status->value : strtolower((string) $txn->status);

            $signedDelta = $this->computeSignedEffect($txn, $type, $status);
            if ($signedDelta === null) {
                continue;
            }

            $ledgerTotals[$walletKey] += $signedDelta;
            $counts[$walletKey]++;
            $lastTxnDates[$walletKey] = $txn->created_at;
        }

        $wallets = [];
        $hasDiscrepancy = false;

        foreach (self::WALLET_COLUMNS as $key => $column) {
            // Skip IRA if inactive, 0 stored, and 0 transactions
            $stored = round((float) ($user->{$column} ?? 0), 2);
            $computed = round($ledgerTotals[$key], 2);
            $delta = round($stored - $computed, 2);

            if ($key === 'ira' && (int) ($user->ira_status ?? 0) !== 1 && $stored == 0.0 && $counts[$key] === 0) {
                continue;
            }

            $isMatch = abs($delta) < 0.01;
            if (!$isMatch) {
                $hasDiscrepancy = true;
            }

            $wallets[$key] = [
                'wallet_key' => $key,
                'label' => self::WALLET_LABELS[$key],
                'column' => $column,
                'stored' => $stored,
                'computed' => $computed,
                'delta' => $delta,
                'abs_delta' => abs($delta),
                'is_match' => $isMatch,
                'txn_count' => $counts[$key],
                'last_txn_at' => $lastTxnDates[$key],
            ];
        }

        return [
            'user' => $user,
            'has_discrepancy' => $hasDiscrepancy,
            'wallets' => $wallets,
        ];
    }

    /**
     * Scan all active/existing members and return summary + discrepancies list.
     */
    public function auditAll(bool $onlyDiscrepancies = true): array
    {
        $users = User::orderBy('id')->get();
        $results = [];
        $discrepancyCount = 0;
        $totalDrift = 0.0;

        foreach ($users as $user) {
            $audit = $this->auditUser($user);
            if ($audit['has_discrepancy']) {
                $discrepancyCount++;
                foreach ($audit['wallets'] as $w) {
                    if (!$w['is_match']) {
                        $totalDrift += $w['abs_delta'];
                    }
                }
            }

            if (!$onlyDiscrepancies || $audit['has_discrepancy']) {
                $results[] = $audit;
            }
        }

        return [
            'total_users_scanned' => $users->count(),
            'discrepancy_users' => $discrepancyCount,
            'total_absolute_drift' => round($totalDrift, 2),
            'audits' => $results,
        ];
    }

    /**
     * Heal Option 1: Overwrite stored wallet balance to match mathematical ledger net.
     */
    public function syncWalletToLedger(int $userId, string $walletKey): array
    {
        if (!isset(self::WALLET_COLUMNS[$walletKey])) {
            return ['success' => false, 'message' => 'Invalid wallet specified.'];
        }

        return DB::transaction(function () use ($userId, $walletKey) {
            $user = User::lockForUpdate()->findOrFail($userId);
            $audit = $this->auditUser($user);
            $wallet = $audit['wallets'][$walletKey] ?? null;

            if (!$wallet) {
                return ['success' => false, 'message' => 'Wallet not active for member.'];
            }

            $column = self::WALLET_COLUMNS[$walletKey];
            $oldStored = $wallet['stored'];
            $newStored = $wallet['computed'];

            $user->{$column} = $newStored;
            $user->save();

            return [
                'success' => true,
                'user' => $user,
                'wallet_label' => $wallet['label'],
                'old_balance' => $oldStored,
                'new_balance' => $newStored,
                'delta' => round($newStored - $oldStored, 2),
            ];
        });
    }

    /**
     * Heal Option 2: Keep stored wallet balance untouched and insert an official balancing entry into transactions.
     */
    public function insertBalancingEntry(int $userId, string $walletKey, ?int $adminId = null): array
    {
        if (!isset(self::WALLET_COLUMNS[$walletKey])) {
            return ['success' => false, 'message' => 'Invalid wallet specified.'];
        }

        return DB::transaction(function () use ($userId, $walletKey, $adminId) {
            $user = User::lockForUpdate()->findOrFail($userId);
            $audit = $this->auditUser($user);
            $wallet = $audit['wallets'][$walletKey] ?? null;

            if (!$wallet || $wallet['is_match']) {
                return ['success' => false, 'message' => 'No balance discrepancy to adjust for this wallet.'];
            }

            // delta = stored - computed. If stored > computed (+), ledger needs a Deposit (+delta).
            // If stored < computed (-), ledger needs a Subtract (|delta|).
            $delta = $wallet['delta'];
            $isCredit = $delta > 0;
            $absDelta = round(abs($delta), 2);
            $tnxRef = 'RECON-' . strtoupper(Str::random(8));

            $txn = new Transaction();
            $txn->user_id = $user->id;
            $txn->from_user_id = $adminId ?? 1;
            $txn->from_model = 'Admin';
            $txn->tnx = $tnxRef;
            $txn->description = 'Ledger Integrity Balancing Entry (' . $wallet['label'] . ')';
            $txn->amount = $absDelta;
            $txn->charge = 0;
            $txn->final_amount = $absDelta;
            $txn->method = 'Reconciliation';
            $txn->pay_currency = 'USD';
            $txn->pay_amount = $absDelta;
            $txn->type = $isCredit ? TxnType::Deposit : TxnType::Subtract;
            $txn->status = TxnStatus::Success;
            $txn->wallet_type = $walletKey;
            $txn->approval_cause = 'Ledger Reconciliation Auto-Heal';
            $txn->save();

            return [
                'success' => true,
                'user' => $user,
                'wallet_label' => $wallet['label'],
                'tnx' => $tnxRef,
                'entry_type' => $isCredit ? 'Credit (+)' : 'Debit (-)',
                'amount' => $absDelta,
                'stored_balance' => $wallet['stored'],
            ];
        });
    }

    /**
     * Compute net signed wallet impact for a single transaction row.
     */
    protected function computeSignedEffect(Transaction $txn, string $type, string $status): ?float
    {
        if ($status === 'success') {
            if ($type === 'loan_applied') {
                return -round((float) ($txn->charge ?? 0), 2);
            }

            if (in_array($type, self::CREDIT_TYPES, true)) {
                return round((float) $txn->amount, 2);
            }

            if (in_array($type, self::DEBIT_SETTLED_TYPES, true)) {
                $debitAmt = ((float) $txn->final_amount > 0 && $type !== 'subtract')
                    ? (float) $txn->final_amount
                    : (float) $txn->amount;
                return -round($debitAmt, 2);
            }
        }

        if ($status === 'pending' && in_array($type, self::PRE_DEDUCTED_PENDING_TYPES, true)) {
            $debitAmt = (float) $txn->final_amount > 0 ? (float) $txn->final_amount : (float) $txn->amount;
            return -round($debitAmt, 2);
        }

        return null;
    }
}
