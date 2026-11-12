<?php

namespace App\Services;

use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Events\NotificationEvent;
use App\Models\Admin;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionDispute;
use App\Models\User;
use App\Support\MailAsset;
use App\Traits\NotifyTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DisputeService
{
    use NotifyTrait;

    public const DEBIT_DISPUTABLE_TYPES = [
        'fund_transfer',
        'send_money',
        'withdraw',
        'withdraw_auto',
        'pay_bill',
        'subtract',
    ];

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

    /**
     * Reg E filing window in days.
     */
    public const REG_E_WINDOW_DAYS = 90;

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
     * Determine whether a transaction is eligible for a Reg E dispute claim.
     *
     * @return array{eligible: bool, reason: ?string, max_amount: float, wallet_key: string, existing_dispute: ?TransactionDispute}
     */
    public function evaluateEligibility(Transaction $txn): array
    {
        TransactionDispute::ensureSchema();

        $existing = TransactionDispute::where('transaction_id', $txn->id)->first();
        $walletKey = $this->normalizeWalletKey($txn->wallet_type);

        if ($existing) {
            return [
                'eligible' => false,
                'reason' => "Claim #{$existing->claim_number} has already been filed for this transaction.",
                'max_amount' => 0.0,
                'wallet_key' => $walletKey,
                'existing_dispute' => $existing,
            ];
        }

        $type = $txn->type instanceof TxnType ? $txn->type->value : strtolower((string) $txn->type);
        $status = $txn->status instanceof TxnStatus ? $txn->status->value : strtolower((string) $txn->status);

        if ($status !== 'success') {
            return [
                'eligible' => false,
                'reason' => 'Only completed (settled) transactions can be disputed under Regulation E.',
                'max_amount' => 0.0,
                'wallet_key' => $walletKey,
                'existing_dispute' => null,
            ];
        }

        if (! in_array($type, self::DEBIT_DISPUTABLE_TYPES, true)) {
            return [
                'eligible' => false,
                'reason' => 'Only debit / outgoing transactions are eligible for a dispute claim.',
                'max_amount' => 0.0,
                'wallet_key' => $walletKey,
                'existing_dispute' => null,
            ];
        }

        if (! array_key_exists($walletKey, self::WALLET_COLUMNS)) {
            return [
                'eligible' => false,
                'reason' => 'Only depository accounts (Checking, Primary Savings, IRA) are supported in this Dispute Center.',
                'max_amount' => 0.0,
                'wallet_key' => $walletKey,
                'existing_dispute' => null,
            ];
        }

        $tnxUpper = strtoupper((string) $txn->tnx);
        if (str_starts_with($tnxUpper, 'RECON-')
            || $txn->target_type === TransactionDispute::class
            || stripos((string) $txn->method, 'Dispute') !== false) {
            return [
                'eligible' => false,
                'reason' => 'System adjustment and dispute settlement entries cannot be disputed.',
                'max_amount' => 0.0,
                'wallet_key' => $walletKey,
                'existing_dispute' => null,
            ];
        }

        if ($txn->created_at && $txn->created_at->lt(now()->subDays(self::REG_E_WINDOW_DAYS))) {
            return [
                'eligible' => false,
                'reason' => 'This transaction exceeds the ' . self::REG_E_WINDOW_DAYS . '-day electronic filing window. Please contact Member Services.',
                'max_amount' => 0.0,
                'wallet_key' => $walletKey,
                'existing_dispute' => null,
            ];
        }

        $debitedTotal = ((float) $txn->final_amount > 0 && $type !== 'subtract')
            ? round((float) $txn->final_amount, 2)
            : round((float) $txn->amount, 2);

        return [
            'eligible' => true,
            'reason' => null,
            'max_amount' => $debitedTotal,
            'wallet_key' => $walletKey,
            'existing_dispute' => null,
        ];
    }

    /**
     * File a new Reg E dispute claim on a member's settled debit transaction.
     */
    public function submitDispute(User $user, Transaction $txn, array $input, ?string $evidencePath = null): array
    {
        TransactionDispute::ensureSchema();

        $check = $this->evaluateEligibility($txn);
        if (! $check['eligible']) {
            return ['success' => false, 'message' => $check['reason'] ?? 'Transaction is not eligible for dispute.'];
        }

        $maxAmt = $check['max_amount'];
        $requestedAmt = isset($input['disputed_amount']) && is_numeric($input['disputed_amount'])
            ? round((float) $input['disputed_amount'], 2)
            : $maxAmt;

        if ($requestedAmt <= 0 || $requestedAmt > $maxAmt) {
            return [
                'success' => false,
                'message' => 'Disputed amount must be between $0.01 and $' . number_format($maxAmt, 2) . '.',
            ];
        }

        $category = (string) ($input['category'] ?? 'unauthorized_charge');
        if (! array_key_exists($category, TransactionDispute::CATEGORIES)) {
            $category = 'unauthorized_charge';
        }

        $dispute = DB::transaction(function () use ($user, $txn, $check, $requestedAmt, $category, $input, $evidencePath) {
            // Re-check inside transaction to avoid race-condition duplicates
            $dup = TransactionDispute::where('transaction_id', $txn->id)->lockForUpdate()->first();
            if ($dup) {
                throw new \RuntimeException("Claim #{$dup->claim_number} already exists for this transaction.");
            }

            $claimNumber = TransactionDispute::generateClaimNumber();
            $merchantName = trim((string) ($input['merchant_name'] ?? ''));
            if ($merchantName === '') {
                $merchantName = $txn->description;
            }

            $categoryLabel = $category === 'other' && ! empty($input['other_reason'])
                ? 'Other: ' . trim((string) $input['other_reason'])
                : TransactionDispute::CATEGORIES[$category];

            $timeline = [
                [
                    'event' => 'Reg E Claim Filed',
                    'actor' => $user->full_name . ' (@' . $user->username . ')',
                    'at' => now('UTC')->format('M d, Y H:i \U\T\C'),
                    'note' => $categoryLabel . ' — ' . Str::limit(trim((string) $input['description']), 160),
                ],
            ];

            return TransactionDispute::create([
                'claim_number' => $claimNumber,
                'user_id' => $user->id,
                'transaction_id' => $txn->id,
                'tnx' => $txn->tnx,
                'wallet_type' => $check['wallet_key'],
                'disputed_amount' => $requestedAmt,
                'category' => $category,
                'other_reason' => $category === 'other' ? Str::limit(trim((string) ($input['other_reason'] ?? '')), 255, '') : null,
                'description' => trim((string) $input['description']),
                'merchant_name' => Str::limit($merchantName, 200, ''),
                'evidence_path' => $evidencePath,
                'status' => 'open',
                'timeline' => $timeline,
            ]);
        });

        $this->notifyClaimFiled($dispute, $user, $txn);

        return [
            'success' => true,
            'message' => "Dispute claim #{$dispute->claim_number} submitted for Reg E investigation.",
            'dispute' => $dispute,
        ];
    }

    /**
     * Issue a Reg E Provisional Credit into the member's original wallet and record a matching Refund txn.
     */
    public function issueProvisionalCredit(int $disputeId, ?int $adminId = null, string $via = 'Telegram', ?string $note = null): array
    {
        TransactionDispute::ensureSchema();

        $result = DB::transaction(function () use ($disputeId, $adminId, $via, $note) {
            $dispute = TransactionDispute::with(['user', 'transaction'])->lockForUpdate()->find($disputeId);
            if (! $dispute) {
                return ['success' => false, 'message' => "Dispute #{$disputeId} not found."];
            }

            if ($dispute->status === 'provisional_credited') {
                return ['success' => false, 'message' => "Provisional credit was already issued for {$dispute->claim_number} ({$dispute->provisional_credit_tnx})."];
            }
            if ($dispute->status !== 'open') {
                return ['success' => false, 'message' => "Claim {$dispute->claim_number} is already finalized ({$dispute->status_label})."];
            }

            $user = User::lockForUpdate()->find($dispute->user_id);
            if (! $user) {
                return ['success' => false, 'message' => 'Member account not found.'];
            }

            $walletKey = $this->normalizeWalletKey($dispute->wallet_type);
            $column = self::WALLET_COLUMNS[$walletKey] ?? 'balance';
            $amount = round((float) $dispute->disputed_amount, 2);

            $user->increment($column, $amount);

            $creditTxn = $this->createDisputeLedgerEntry(
                $user,
                $dispute,
                TxnType::Refund,
                $amount,
                $walletKey,
                "Provisional Credit — Claim #{$dispute->claim_number} (Ref {$dispute->tnx})",
                'Reg E Provisional Credit',
                $adminId
            );

            $actorLabel = $this->resolveActorLabel($adminId, $via);
            $timeline = $dispute->timeline ?? [];
            $timeline[] = [
                'event' => 'Provisional Credit Issued',
                'actor' => $actorLabel,
                'at' => now('UTC')->format('M d, Y H:i \U\T\C'),
                'note' => 'Credited $' . number_format($amount, 2) . ' to ' . (self::WALLET_LABELS[$walletKey] ?? 'Checking') . " (Ref {$creditTxn->tnx})" . ($note ? " — {$note}" : ''),
            ];

            $dispute->update([
                'status' => 'provisional_credited',
                'provisional_credit_tnx' => $creditTxn->tnx,
                'provisional_credited_at' => now(),
                'reviewed_by' => $adminId,
                'reviewed_via' => $via,
                'resolution_notes' => $note ?: $dispute->resolution_notes,
                'timeline' => $timeline,
            ]);

            return [
                'success' => true,
                'dispute' => $dispute->fresh(['user', 'transaction']),
                'user' => $user->fresh(),
                'credit_txn' => $creditTxn,
                'message' => "Provisional credit of $" . number_format($amount, 2) . " issued for {$dispute->claim_number} ({$creditTxn->tnx}).",
            ];
        });

        if ($result['success']) {
            $this->notifyStatusUpdate($result['dispute'], $result['user'], 'provisional_credited');
        }

        return $result;
    }

    /**
     * Resolve dispute in member's favor (Fraud / Error Confirmed).
     */
    public function resolveAsFraud(int $disputeId, ?int $adminId = null, string $via = 'Telegram', ?string $note = null): array
    {
        TransactionDispute::ensureSchema();

        $result = DB::transaction(function () use ($disputeId, $adminId, $via, $note) {
            $dispute = TransactionDispute::with(['user', 'transaction'])->lockForUpdate()->find($disputeId);
            if (! $dispute) {
                return ['success' => false, 'message' => "Dispute #{$disputeId} not found."];
            }

            if (! $dispute->isActive()) {
                return ['success' => false, 'message' => "Claim {$dispute->claim_number} is already finalized ({$dispute->status_label})."];
            }

            $user = User::lockForUpdate()->find($dispute->user_id);
            if (! $user) {
                return ['success' => false, 'message' => 'Member account not found.'];
            }

            $walletKey = $this->normalizeWalletKey($dispute->wallet_type);
            $column = self::WALLET_COLUMNS[$walletKey] ?? 'balance';
            $amount = round((float) $dispute->disputed_amount, 2);
            $creditTnx = $dispute->provisional_credit_tnx;

            // Only credit if Provisional Credit was NOT already posted
            if ($dispute->status === 'open' && empty($creditTnx)) {
                $user->increment($column, $amount);
                $creditTxn = $this->createDisputeLedgerEntry(
                    $user,
                    $dispute,
                    TxnType::Refund,
                    $amount,
                    $walletKey,
                    "Dispute Credit — Claim #{$dispute->claim_number} Confirmed (Ref {$dispute->tnx})",
                    'Reg E Final Fraud Refund',
                    $adminId
                );
                $creditTnx = $creditTxn->tnx;
                $actionSummary = 'Final refund of $' . number_format($amount, 2) . ' credited (' . $creditTnx . ').';
            } else {
                $actionSummary = "Existing provisional credit ({$creditTnx}) finalized permanently.";
            }

            $actorLabel = $this->resolveActorLabel($adminId, $via);
            $timeline = $dispute->timeline ?? [];
            $timeline[] = [
                'event' => 'Resolved — Fraud / Error Confirmed',
                'actor' => $actorLabel,
                'at' => now('UTC')->format('M d, Y H:i \U\T\C'),
                'note' => $actionSummary . ($note ? " Note: {$note}" : ''),
            ];

            $dispute->update([
                'status' => 'resolved_fraud',
                'provisional_credit_tnx' => $creditTnx,
                'resolved_at' => now(),
                'reviewed_by' => $adminId ?? $dispute->reviewed_by,
                'reviewed_via' => $via,
                'resolution_notes' => $note ?: ($dispute->resolution_notes ?: 'Unauthorized transaction / error confirmed after investigation.'),
                'timeline' => $timeline,
            ]);

            return [
                'success' => true,
                'dispute' => $dispute->fresh(['user', 'transaction']),
                'user' => $user->fresh(),
                'message' => "Claim {$dispute->claim_number} resolved in member favor. {$actionSummary}",
            ];
        });

        if ($result['success']) {
            $this->notifyStatusUpdate($result['dispute'], $result['user'], 'resolved_fraud');
        }

        return $result;
    }

    /**
     * Deny dispute as valid charge; reverses provisional credit cleanly if previously issued.
     */
    public function denyDispute(int $disputeId, ?int $adminId = null, string $via = 'Telegram', ?string $reason = null): array
    {
        TransactionDispute::ensureSchema();

        $result = DB::transaction(function () use ($disputeId, $adminId, $via, $reason) {
            $dispute = TransactionDispute::with(['user', 'transaction'])->lockForUpdate()->find($disputeId);
            if (! $dispute) {
                return ['success' => false, 'message' => "Dispute #{$disputeId} not found."];
            }

            if (! $dispute->isActive()) {
                return ['success' => false, 'message' => "Claim {$dispute->claim_number} is already finalized ({$dispute->status_label})."];
            }

            $user = User::lockForUpdate()->find($dispute->user_id);
            if (! $user) {
                return ['success' => false, 'message' => 'Member account not found.'];
            }

            $walletKey = $this->normalizeWalletKey($dispute->wallet_type);
            $column = self::WALLET_COLUMNS[$walletKey] ?? 'balance';
            $amount = round((float) $dispute->disputed_amount, 2);
            $clawbackTnx = null;
            $reversalMsg = 'No provisional credit reversal required.';

            // If provisional credit was posted, debit exact amount and log Subtract entry so /reconcile stays balanced
            if ($dispute->status === 'provisional_credited' && ! empty($dispute->provisional_credit_tnx)) {
                $user->decrement($column, $amount);
                $clawbackTxn = $this->createDisputeLedgerEntry(
                    $user,
                    $dispute,
                    TxnType::Subtract,
                    $amount,
                    $walletKey,
                    "Provisional Credit Reversal — Claim #{$dispute->claim_number} Denied",
                    'Reg E Provisional Reversal',
                    $adminId
                );
                $clawbackTnx = $clawbackTxn->tnx;
                $reversalMsg = 'Reversed provisional credit of $' . number_format($amount, 2) . " ({$clawbackTnx}).";
            }

            $finalNote = trim((string) $reason) !== ''
                ? trim((string) $reason)
                : 'Merchant / transaction verification confirmed transaction was valid and authorized.';

            $actorLabel = $this->resolveActorLabel($adminId, $via);
            $timeline = $dispute->timeline ?? [];
            $timeline[] = [
                'event' => 'Claim Denied — Valid Charge',
                'actor' => $actorLabel,
                'at' => now('UTC')->format('M d, Y H:i \U\T\C'),
                'note' => "{$reversalMsg} Reason: {$finalNote}",
            ];

            $dispute->update([
                'status' => 'denied',
                'clawback_tnx' => $clawbackTnx,
                'resolved_at' => now(),
                'reviewed_by' => $adminId ?? $dispute->reviewed_by,
                'reviewed_via' => $via,
                'resolution_notes' => $finalNote,
                'timeline' => $timeline,
            ]);

            return [
                'success' => true,
                'dispute' => $dispute->fresh(['user', 'transaction']),
                'user' => $user->fresh(),
                'message' => "Claim {$dispute->claim_number} denied. {$reversalMsg}",
            ];
        });

        if ($result['success']) {
            $this->notifyStatusUpdate($result['dispute'], $result['user'], 'denied');
        }

        return $result;
    }

    /**
     * Append a staff/officer investigation note to the dispute timeline without altering funds.
     */
    public function addStaffNote(int $disputeId, Admin $admin, string $note): array
    {
        TransactionDispute::ensureSchema();

        $dispute = TransactionDispute::find($disputeId);
        if (! $dispute) {
            return ['success' => false, 'message' => 'Claim not found.'];
        }

        $timeline = $dispute->timeline ?? [];
        $timeline[] = [
            'event' => 'Investigation Note Added',
            'actor' => $admin->name . ' (Staff)',
            'at' => now('UTC')->format('M d, Y H:i \U\T\C'),
            'note' => trim($note),
        ];

        $dispute->update([
            'resolution_notes' => trim($note),
            'timeline' => $timeline,
        ]);

        return ['success' => true, 'message' => 'Case note saved to claim timeline.'];
    }

    /**
     * Build a formatted Telegram card + inline action keyboard for a dispute claim.
     *
     * @return array{text: string, keyboard: array}
     */
    public function buildTelegramClaimCard(TransactionDispute $dispute): array
    {
        $dispute->loadMissing(['user', 'transaction']);
        $user = $dispute->user;
        $cur = setting('currency_symbol', 'global') ?: '$';
        $walletLabel = self::WALLET_LABELS[$this->normalizeWalletKey($dispute->wallet_type)] ?? 'Checking';

        $statusIcon = match ($dispute->status) {
            'open' => '🟡 OPEN — INVESTIGATION REQUIRED',
            'provisional_credited' => '💳 PROVISIONAL CREDIT ISSUED',
            'resolved_fraud' => '✅ RESOLVED — FRAUD CONFIRMED',
            'denied' => '❌ DENIED — VALID CHARGE',
            default => strtoupper((string) $dispute->status),
        };

        $msg = "🛡️ <b>REG E TRANSACTION DISPUTE</b>\n\n";
        $msg .= "🔖 <b>Claim #:</b> <code>{$dispute->claim_number}</code>\n";
        $msg .= "📊 <b>Status:</b> <b>{$statusIcon}</b>\n";
        if ($user) {
            $msg .= "👤 <b>Member:</b> " . htmlspecialchars($user->full_name) . " (<code>@{$user->username}</code>)\n";
        }
        $msg .= "🏦 <b>Account:</b> {$walletLabel}\n";
        $msg .= "🧾 <b>Orig. Txn:</b> <code>{$dispute->tnx}</code>\n";
        $msg .= "💰 <b>Disputed Amount:</b> <b>{$cur}" . number_format((float) $dispute->disputed_amount, 2) . "</b>\n";
        $msg .= "⚖️ <b>Reg E Category:</b> " . htmlspecialchars($dispute->category_label) . "\n";
        if ($dispute->merchant_name) {
            $msg .= "🏪 <b>Payee / Desc:</b> " . htmlspecialchars($dispute->merchant_name) . "\n";
        }
        $msg .= "📝 <b>Member Statement:</b>\n<i>" . htmlspecialchars(Str::limit((string) $dispute->description, 260)) . "</i>\n";

        if ($dispute->provisional_credit_tnx) {
            $msg .= "\n💳 <b>Credit Ref:</b> <code>{$dispute->provisional_credit_tnx}</code>";
        }
        if ($dispute->clawback_tnx) {
            $msg .= "\n↩️ <b>Reversal Ref:</b> <code>{$dispute->clawback_tnx}</code>";
        }
        if ($dispute->resolution_notes && ! $dispute->isActive()) {
            $msg .= "\n📋 <b>Determination:</b> " . htmlspecialchars($dispute->resolution_notes);
        }

        $keyboard = [];
        if ($dispute->status === 'open') {
            $keyboard[] = [
                ['text' => '💳 Issue Provisional Credit', 'callback_data' => 'dsp_prov:' . $dispute->id],
            ];
            $keyboard[] = [
                ['text' => '✅ Fraud Confirmed', 'callback_data' => 'dsp_win:' . $dispute->id],
                ['text' => '❌ Deny Claim', 'callback_data' => 'dsp_deny:' . $dispute->id],
            ];
        } elseif ($dispute->status === 'provisional_credited') {
            $keyboard[] = [
                ['text' => '✅ Confirm Fraud (Keep Credit)', 'callback_data' => 'dsp_win:' . $dispute->id],
                ['text' => '❌ Deny (Reclaim Credit)', 'callback_data' => 'dsp_deny:' . $dispute->id],
            ];
        }

        if ($user) {
            $keyboard[] = [
                ['text' => '👤 Member Profile', 'callback_data' => 'usr_view:' . $user->id],
            ];
        }

        return ['text' => $msg, 'keyboard' => $keyboard];
    }

    /**
     * Create clean Refund / Subtract ledger rows matching ReconciliationService semantics without skewing reward points.
     */
    protected function createDisputeLedgerEntry(
        User $user,
        TransactionDispute $dispute,
        TxnType $type,
        float $amount,
        string $walletKey,
        string $description,
        string $method,
        ?int $adminId = null
    ): Transaction {
        $prefix = $type === TxnType::Refund ? 'DSPCR' : 'DSPREV';

        $txn = new Transaction();
        $txn->user_id = $user->id;
        $txn->from_user_id = $adminId ?? 1;
        $txn->from_model = 'Admin';
        $txn->tnx = $prefix . strtoupper(Str::random(7));
        $txn->description = $description;
        $txn->amount = $amount;
        $txn->charge = 0;
        $txn->final_amount = $amount;
        $txn->method = $method;
        $txn->pay_currency = setting('site_currency', 'global') ?: 'USD';
        $txn->pay_amount = $amount;
        $txn->type = $type;
        $txn->status = TxnStatus::Success;
        $txn->wallet_type = $walletKey;
        $txn->approval_cause = "Reg E Claim #{$dispute->claim_number}";
        $txn->target_id = $dispute->id;
        $txn->target_type = TransactionDispute::class;
        $txn->save();

        return $txn;
    }

    protected function resolveActorLabel(?int $adminId, string $via): string
    {
        $publicChannel = stripos($via, 'telegram') !== false
            ? 'Dispute Resolution Team'
            : 'Member Protection Desk';

        if ($adminId) {
            $admin = Admin::find($adminId);
            if ($admin) {
                return "{$admin->name} ({$publicChannel})";
            }
        }

        return 'Member Protection & Dispute Operations';
    }

    public function resolveEvidenceFilePath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        $clean = ltrim($path, '/');
        foreach ([
            base_path($clean),
            public_path($clean),
            storage_path($clean),
            storage_path('app/' . $clean),
            $clean,
        ] as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Dispatch image or PDF/document dispute attachment directly to Telegram.
     */
    protected function dispatchEvidenceToTelegram(
        TransactionDispute $dispute,
        User $user,
        ?string $relativePath,
        string $caption
    ): void {
        if (empty($relativePath)) {
            return;
        }

        $botToken = Setting::get('telegram_bot_token', 'telegram') ?: env('TELEGRAM_BOT_TOKEN');
        $chatId = Setting::get('telegram_chat_id', 'telegram') ?: env('TELEGRAM_CHAT_ID');
        if (! $botToken || ! $chatId) {
            return;
        }

        $filePath = $this->resolveEvidenceFilePath($relativePath);
        if (! $filePath || ! is_readable($filePath)) {
            Log::warning("DisputeService evidence file not found on disk: {$relativePath}");
            return;
        }

        $ext = strtolower((string) pathinfo($filePath, PATHINFO_EXTENSION));
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
        $endpoint = $isImage ? 'sendPhoto' : 'sendDocument';
        $field = $isImage ? 'photo' : 'document';

        try {
            Http::timeout(25)
                ->attach($field, file_get_contents($filePath), basename($filePath))
                ->post("https://api.telegram.org/bot{$botToken}/{$endpoint}", [
                    'chat_id' => $chatId,
                    'caption' => mb_substr($caption, 0, 1000),
                ]);
        } catch (\Throwable $e) {
            Log::warning("DisputeService {$endpoint} dispatch failed: " . $e->getMessage());
        }
    }

    public function notifyAdditionalEvidence(
        TransactionDispute $dispute,
        User $user,
        string $relativePath,
        ?string $note = null
    ): void {
        $caption = "📎 Additional Documentation — Claim #{$dispute->claim_number} (@{$user->username})";
        if ($note) {
            $caption .= "\n💬 " . $note;
        }

        $this->dispatchEvidenceToTelegram($dispute, $user, $relativePath, $caption);

        $card = $this->buildTelegramClaimCard($dispute->fresh(['user', 'transaction']));
        $headerMsg = "🆕 <b>ADDITIONAL EVIDENCE ATTACHED</b>\nClaim: <code>#{$dispute->claim_number}</code> • File: <code>" . basename($relativePath) . "</code>\n"
            . ($note ? ("💬 Note: " . htmlspecialchars($note) . "\n") : '')
            . "\n" . $card['text'];
        $this->telegramNotify($headerMsg, $card['keyboard']);

        if ($user->staff_id) {
            $officer = Admin::find($user->staff_id);
            if ($officer && $officer->email) {
                $this->sendBrandedDisputeMail(
                    $officer->email,
                    "Additional Evidence Uploaded: {$user->full_name} — Claim #{$dispute->claim_number}",
                    'New Supporting Documentation Attached',
                    "Hello {$officer->name},",
                    "Member <strong>" . e($user->full_name) . "</strong> uploaded additional supporting documentation for Claim <code>#{$dispute->claim_number}</code>.<br><br>"
                    . "<strong>File:</strong> " . e(basename($relativePath))
                    . ($note ? ("<br><strong>Member Note:</strong> " . e($note)) : ''),
                    'Inspect Claim & Attachment',
                    route('admin.disputes.show', $dispute->id)
                );
            }
        }
    }

    protected function notifyClaimFiled(TransactionDispute $dispute, User $user, Transaction $txn): void
    {
        // 1. Dispatch uploaded evidence file (photo OR PDF document) + interactive decision card to Telegram
        if ($dispute->evidence_path) {
            $this->dispatchEvidenceToTelegram(
                $dispute,
                $user,
                $dispute->evidence_path,
                "📎 Supporting Evidence — Claim #{$dispute->claim_number} (@{$user->username})"
            );
        }

        $card = $this->buildTelegramClaimCard($dispute);
        $this->telegramNotify($card['text'], $card['keyboard']);

        // 2. Member in-app bell + push notification
        $this->createMemberBellNotification(
            $user->id,
            "Dispute Claim #{$dispute->claim_number} Received",
            "We have received your Reg E dispute for {$txn->tnx} ($" . number_format((float) $dispute->disputed_amount, 2) . "). Track investigation progress anytime from Activity.",
            route('user.disputes.show', $dispute->claim_number)
        );

        // 3. Branded email confirmation to member & assigned Account Officer
        $cur = setting('currency_symbol', 'global') ?: '$';
        $this->sendBrandedDisputeMail(
            $user->email,
            "Reg E Dispute Acknowledgement — Claim #{$dispute->claim_number}",
            'Transaction Dispute Received',
            "Dear {$user->full_name},",
            "Thank you for contacting FrontField Credit Union Member Protection. Your Regulation E claim has been logged and assigned for review.<br><br>"
            . "<strong>Claim Number:</strong> <code>{$dispute->claim_number}</code><br>"
            . "<strong>Transaction Reference:</strong> <code>{$txn->tnx}</code><br>"
            . "<strong>Disputed Amount:</strong> {$cur}" . number_format((float) $dispute->disputed_amount, 2) . "<br>"
            . "<strong>Reason:</strong> " . e($dispute->category_label) . "<br><br>"
            . "Under Regulation E guidelines, if our investigation requires more than 10 business days, eligible accounts may receive a provisional credit while review continues.",
            'View Claim Status',
            route('user.disputes.show', $dispute->claim_number)
        );

        if ($user->staff_id) {
            $officer = Admin::find($user->staff_id);
            if ($officer && $officer->email) {
                $this->sendBrandedDisputeMail(
                    $officer->email,
                    "Member Dispute Alert: {$user->full_name} — Claim #{$dispute->claim_number}",
                    'Assigned Member Filed Dispute',
                    "Hello {$officer->name},",
                    "Your assigned member <strong>" . e($user->full_name) . "</strong> (<code>@{$user->username}</code>) opened a Regulation E dispute:<br><br>"
                    . "• <strong>Claim #:</strong> <code>{$dispute->claim_number}</code><br>"
                    . "• <strong>Transaction:</strong> <code>{$txn->tnx}</code> ({$cur}" . number_format((float) $dispute->disputed_amount, 2) . ")<br>"
                    . "• <strong>Category:</strong> " . e($dispute->category_label) . "<br>"
                    . "• <strong>Statement:</strong> " . e($dispute->description),
                    'Inspect Claim in Admin',
                    route('admin.disputes.show', $dispute->id)
                );
            }
        }
    }

    protected function notifyStatusUpdate(TransactionDispute $dispute, User $user, string $stage): void
    {
        $cur = setting('currency_symbol', 'global') ?: '$';
        $amt = $cur . number_format((float) $dispute->disputed_amount, 2);

        if ($stage === 'provisional_credited') {
            $title = "Provisional Credit Posted ({$amt}) — Claim #{$dispute->claim_number}";
            $body = "A temporary provisional credit of <strong>{$amt}</strong> (Ref: <code>{$dispute->provisional_credit_tnx}</code>) has been posted to your "
                . (self::WALLET_LABELS[$this->normalizeWalletKey($dispute->wallet_type)] ?? 'Checking')
                . " account while our Member Protection & Dispute Operations team completes its Regulation E investigation.<br><br>"
                . "<strong>Important Reg E Notice:</strong> Please note that if our final investigation determines the disputed transaction was authorized or valid, this provisional credit may be reversed in accordance with Regulation E.";
            $noticeTitle = "Provisional Credit Posted ({$amt})";
            $shortMsg = "A {$amt} provisional credit (Ref {$dispute->provisional_credit_tnx}) was posted for Claim #{$dispute->claim_number}.";
        } elseif ($stage === 'resolved_fraud') {
            $title = "Dispute Resolved In Your Favor — Claim #{$dispute->claim_number}";
            $body = "Our Regulation E investigation for <strong>Claim #{$dispute->claim_number}</strong> (Transaction <code>{$dispute->tnx}</code>, {$amt}) is complete and resolved in your favor.<br><br>"
                . "Your credit (Ref: <code>{$dispute->provisional_credit_tnx}</code>) is now <strong>final and permanent</strong>."
                . ($dispute->resolution_notes ? "<br><br><strong>Findings:</strong> " . e($dispute->resolution_notes) : '');
            $noticeTitle = "Dispute Upheld ({$amt}) — Claim #{$dispute->claim_number}";
            $shortMsg = "Claim #{$dispute->claim_number} ({$amt}) has been permanently resolved in your favor.";
        } else {
            $clawNote = $dispute->clawback_tnx
                ? "<br><br>In accordance with Regulation E, the previously issued provisional credit of <strong>{$amt}</strong> has been reversed from your account (Reversal Ref: <code>{$dispute->clawback_tnx}</code>)."
                : '';
            $title = "Dispute Determination Notice — Claim #{$dispute->claim_number}";
            $body = "We have completed our investigation for <strong>Claim #{$dispute->claim_number}</strong> (Transaction <code>{$dispute->tnx}</code>, {$amt}) and determined the transaction to be valid/authorized.<br><br>"
                . "<strong>Findings:</strong> " . e($dispute->resolution_notes ?? 'Verified transaction record.')
                . $clawNote
                . "<br><br>You may request copies of documentation relied upon in reaching this decision via Member Support.";
            $noticeTitle = "Dispute Claim Closed — Claim #{$dispute->claim_number}";
            $shortMsg = "Claim #{$dispute->claim_number} ({$amt}) was closed as a valid/authorized charge.";
        }

        $this->createMemberBellNotification(
            $user->id,
            $noticeTitle,
            $shortMsg,
            route('user.disputes.show', $dispute->claim_number)
        );

        $this->sendBrandedDisputeMail(
            $user->email,
            $title,
            $noticeTitle,
            "Dear {$user->full_name},",
            $body,
            'View Claim & Timeline',
            route('user.disputes.show', $dispute->claim_number)
        );
    }

    protected function createMemberBellNotification(int $userId, string $title, string $notice, string $actionUrl): void
    {
        try {
            $data = [
                'icon' => 'shield-alert',
                'user_id' => $userId,
                'for' => 'user',
                'title' => $title,
                'notice' => $notice,
                'action_url' => $actionUrl,
                'read' => 0,
            ];

            Notification::create($data);

            try {
                $pusherCredentials = config('broadcasting.connections.pusher');
                if ($pusherCredentials && ! empty($pusherCredentials['key'])) {
                    event(new NotificationEvent('User', $data, $userId));
                }
            } catch (\Throwable $e) {
                Log::warning('Dispute Pusher broadcast skip: ' . $e->getMessage());
            }

            $user = User::find($userId);
            if ($user && ! empty($user->fcm_token)) {
                $this->sendFcmPush($user->fcm_token, $title, $notice, $actionUrl);
            }
        } catch (\Throwable $e) {
            Log::warning('Dispute bell/push notification skip: ' . $e->getMessage());
        }
    }

    protected function sendBrandedDisputeMail(
        string $toEmail,
        string $subject,
        string $title,
        string $salutation,
        string $bodyHtml,
        string $buttonLabel,
        string $buttonLink
    ): void {
        try {
            $siteTitle = setting('site_title', 'global') ?: 'FrontField Credit Union';
            $logoSetting = setting('site_logo', 'global');
            $logoRel = $logoSetting
                ? (Str::startsWith($logoSetting, ['assets/', 'storage/']) ? $logoSetting : 'assets/' . ltrim($logoSetting, '/'))
                : 'assets/external/images/logo.png';
            if (Str::contains($logoRel, 'fav')) {
                $logoRel = 'assets/external/images/logo.png';
            }
            $logoPath = $this->resolveEvidenceFilePath($logoRel);

            Mail::to($toEmail)->send(new \App\Mail\MailSend([
                'from_name' => $siteTitle . ' Member Protection',
                'subject' => $subject,
                'title' => $title,
                'salutation' => $salutation,
                'message_body' => $bodyHtml,
                'button_level' => $buttonLabel,
                'button_link' => $buttonLink,
                'footer_status' => 1,
                'footer_body' => $siteTitle,
                'banner' => null,
                'bottom_status' => 0,
                'bottom_title' => '',
                'bottom_body' => '',
                'site_logo' => MailAsset::absolute($logoRel),
                'site_logo_path' => $logoPath,
                'site_title' => $siteTitle,
                'site_link' => route('home'),
            ]));
        } catch (\Throwable $e) {
            Log::error("Dispute email delivery failed for {$toEmail}: " . $e->getMessage());
        }
    }
}
