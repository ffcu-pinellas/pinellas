<?php

namespace App\Http\Controllers;

use App\Enums\TxnStatus;
use App\Enums\TxnType;
use Txn;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionSecurityCode;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\TelegramApprovalService;
use App\Traits\NotifyTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    use NotifyTrait;

    protected TelegramApprovalService $approvalService;

    public function __construct(TelegramApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Get configured Telegram credentials.
     */
    protected function getCredentials(): array
    {
        $botToken = Setting::get('telegram_bot_token', 'telegram') ?: env('TELEGRAM_BOT_TOKEN');
        $chatId = Setting::get('telegram_chat_id', 'telegram') ?: env('TELEGRAM_CHAT_ID');

        return [$botToken, (string) $chatId];
    }

    /**
     * Check if sender is authorized admin or matching institution group.
     */
    protected function isAuthorized(string|int $senderId, string|int $chatId): bool
    {
        [$botToken, $configuredChatId] = $this->getCredentials();

        $senderStr = (string) $senderId;
        $chatStr = (string) $chatId;
        $configuredStr = (string) $configuredChatId;

        if (!empty($configuredStr) && ($senderStr === $configuredStr || $chatStr === $configuredStr)) {
            return true;
        }

        try {
            \App\Models\Institution::ensureSchema();
            $matchesInst = \App\Models\Institution::whereNotNull('telegram_chat_id')
                ->where(function ($q) use ($senderStr, $chatStr) {
                    $q->where('telegram_chat_id', $senderStr)
                      ->orWhere('telegram_chat_id', $chatStr);
                })->exists();

            if ($matchesInst) {
                return true;
            }
        } catch (\Throwable $e) {}

        return false;
    }

    /**
     * Resolve the active institution for this chat.
     */
    protected function resolveChatInstitution(string|int $chatId): ?\App\Models\Institution
    {
        try {
            \App\Models\Institution::ensureSchema();
            // 1. Check if chat ID matches an institution's dedicated chat ID
            $inst = \App\Models\Institution::where('telegram_chat_id', (string) $chatId)->first();
            if ($inst) {
                return $inst;
            }

            // 2. Check if a temporary scope was set via /scope or /switch in this chat
            $cachedSlug = \Illuminate\Support\Facades\Cache::get("tg_inst_scope_{$chatId}");
            if ($cachedSlug && $cachedSlug !== 'all') {
                return \App\Models\Institution::where('slug', $cachedSlug)
                    ->orWhere('short_name', $cachedSlug)
                    ->orWhere('id', is_numeric($cachedSlug) ? (int)$cachedSlug : 0)
                    ->first();
            }
        } catch (\Throwable $e) {}

        return null;
    }

    /**
     * Main Telegram Webhook Endpoint.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        [$botToken, $configuredChatId] = $this->getCredentials();

        if (!$botToken) {
            Log::warning('Telegram Webhook called but TELEGRAM_BOT_TOKEN is not configured.');
            return response()->json(['status' => 'bot_token_missing'], 200);
        }

        // Feature L: Cryptographic Webhook Secret Verification (X-Telegram-Bot-Api-Secret-Token)
        $expectedSecret = $this->getWebhookSecret(false);
        if ($expectedSecret !== '') {
            $incomingSecret = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');
            if (!hash_equals($expectedSecret, $incomingSecret)) {
                Log::warning('Telegram webhook request rejected: invalid secret token.', ['ip' => $request->ip()]);
                return response()->json(['status' => 'forbidden', 'message' => 'Invalid webhook secret token.'], 403);
            }
        }

        // =====================================================================
        // 1. HANDLE INLINE KEYBOARD CALLBACK QUERIES (BUTTON CLICKS)
        // =====================================================================
        if (isset($payload['callback_query'])) {
            $callback = $payload['callback_query'];
            $callbackId = $callback['id'] ?? null;
            $fromId = $callback['from']['id'] ?? null;
            $message = $callback['message'] ?? [];
            $chatId = $message['chat']['id'] ?? $fromId;
            $messageId = $message['message_id'] ?? null;
            $originalText = $message['text'] ?? '';
            $data = $callback['data'] ?? '';

            // Security authorization check
            if (!$this->isAuthorized($fromId, $chatId)) {
                Log::warning("Unauthorized Telegram button click from ID: {$fromId}");
                $this->answerCallbackQuery($botToken, $callbackId, '⛔ Unauthorized access denied.', true);
                return response()->json(['status' => 'unauthorized'], 200);
            }

            // =================================================================
            // Institution Scope Switch Callback: scope_set:<slug>
            // =================================================================
            if (str_starts_with($data, 'scope_set:')) {
                $targetSlug = trim(substr($data, strlen('scope_set:')));
                $this->handleSwitchScopeCommand($botToken, $chatId, $targetSlug);
                $this->answerCallbackQuery($botToken, $callbackId, 'Scope updated!', false);
                return response()->json(['status' => 'scope_updated'], 200);
            }

            // =================================================================
            // Member Management Button Callbacks
            // =================================================================
            // 1. Instant Member Unlock: usr_unlock:<user_id>
            if (str_starts_with($data, 'usr_unlock:')) {
                $userId = trim(substr($data, strlen('usr_unlock:')));
                $targetUser = User::find($userId);
                if (!$targetUser) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ User ID {$userId} not found.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $this->unlockUser($targetUser, $botToken, $chatId, $messageId, $callbackId);
                return response()->json(['status' => 'user_unlocked'], 200);
            }

            // 2. Member Status Change: usr_stat:<user_id>:<status>
            if (str_starts_with($data, 'usr_stat:')) {
                $parts = explode(':', $data);
                $userId = $parts[1] ?? null;
                $newStatus = isset($parts[2]) ? (int)$parts[2] : null;
                $targetUser = User::find($userId);
                if (!$targetUser || $newStatus === null) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ User not found or invalid status.", true);
                    return response()->json(['status' => 'invalid_data'], 200);
                }
                $this->setUserStatus($targetUser, $newStatus, $botToken, $chatId, $messageId, $callbackId, $originalText);
                return response()->json(['status' => 'status_updated'], 200);
            }

            // Member Profile View (Fresh Message, Never Overwrites Alerts): usr_view:<user_id>
            if (str_starts_with($data, 'usr_view:')) {
                $userId = trim(substr($data, strlen('usr_view:')));
                $targetUser = User::find($userId);
                if (!$targetUser) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ User ID {$userId} not found.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $card = $this->renderUserCard($targetUser);
                $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
                $this->answerCallbackQuery($botToken, $callbackId, "👤 Viewing @{$targetUser->username}.", false);
                return response()->json(['status' => 'user_viewed'], 200);
            }

            // 3. Member Profile Refresh: usr_info:<user_id>
            if (str_starts_with($data, 'usr_info:')) {
                $userId = trim(substr($data, strlen('usr_info:')));
                $targetUser = User::find($userId);
                if (!$targetUser) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ User ID {$userId} not found.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $card = $this->renderUserCard($targetUser);
                if (str_contains($originalText, 'Banking Activity Notification') || str_contains($originalText, 'New Client Registration')) {
                    $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
                } else {
                    $this->editMessageText($botToken, $chatId, $messageId, $card['text'], $card['keyboard']);
                }
                $this->answerCallbackQuery($botToken, $callbackId, "🔄 Refreshed @{$targetUser->username}.", false);
                return response()->json(['status' => 'user_refreshed'], 200);
            }

            // 4. Member Ledger Export: usr_export:<user_id>
            if (str_starts_with($data, 'usr_export:')) {
                $userId = trim(substr($data, strlen('usr_export:')));
                $targetUser = User::find($userId);
                if (!$targetUser) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ User ID {$userId} not found.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $this->answerCallbackQuery($botToken, $callbackId, "📥 Exporting ledger for @{$targetUser->username}...", false);
                $this->exportUserLedgerToTelegram($targetUser, $botToken, $chatId);
                return response()->json(['status' => 'user_exported'], 200);
            }

            // 5. Member Dividend Eligibility Toggle: usr_div_tog:<user_id>
            // =================================================================
            // Remote Check Deposit Review Callbacks (Feature A)
            // =================================================================
            if (str_starts_with($data, 'rdep_clear:')) {
                $depId = (int) trim(substr($data, strlen('rdep_clear:')));
                $res = app(\App\Services\RemoteDepositReviewService::class)->clearFullCheck($depId);
                $this->answerCallbackQuery($botToken, $callbackId, $res['success'] ? "✅ Cleared in full!" : ("⚠️ " . $res['message']), true);
                if ($res['success']) {
                    $updated = $originalText . "\n\n───────────────────\n✅ <b>CLEARED IN FULL</b>\n" . htmlspecialchars($res['message']) . "\n🕒 " . now()->format('Y-m-d H:i:s');
                    $this->editMessageText($botToken, $chatId, $messageId, $updated, []);
                }
                return response()->json(['status' => 'rdep_cleared'], 200);
            }

            if (str_starts_with($data, 'rdep_regcc:')) {
                $depId = (int) trim(substr($data, strlen('rdep_regcc:')));
                $res = app(\App\Services\RemoteDepositReviewService::class)->clearWithRegCcHold($depId, 225.00, 2);
                $this->answerCallbackQuery($botToken, $callbackId, $res['success'] ? "⏳ Reg CC Hold Applied!" : ("⚠️ " . $res['message']), true);
                if ($res['success']) {
                    $updated = $originalText . "\n\n───────────────────\n⏳ <b>CLEARED WITH REG CC HOLD</b>\n" . htmlspecialchars($res['message']) . "\n🕒 " . now()->format('Y-m-d H:i:s');
                    $this->editMessageText($botToken, $chatId, $messageId, $updated, []);
                }
                return response()->json(['status' => 'rdep_regcc'], 200);
            }

            if (str_starts_with($data, 'rdep_rej_blur:') || str_starts_with($data, 'rdep_rej_endorse:')) {
                $isEndorse = str_starts_with($data, 'rdep_rej_endorse:');
                $depId = (int) trim(substr($data, strlen($isEndorse ? 'rdep_rej_endorse:' : 'rdep_rej_blur:')));
                $res = app(\App\Services\RemoteDepositReviewService::class)->rejectForRetake($depId, $isEndorse ? 'endorsement' : 'blurry');
                $this->answerCallbackQuery($botToken, $callbackId, $res['success'] ? "❌ Check voided — retake emailed." : ("⚠️ " . $res['message']), true);
                if ($res['success']) {
                    $updated = $originalText . "\n\n───────────────────\n❌ <b>REJECTED ({$res['reason_label']})</b>\n" . htmlspecialchars($res['message']) . "\n🕒 " . now()->format('Y-m-d H:i:s');
                    $this->editMessageText($botToken, $chatId, $messageId, $updated, []);
                }
                return response()->json(['status' => 'rdep_rejected'], 200);
            }

            // =================================================================
            // Ledger Reconciliation Callbacks (Feature B)
            // =================================================================
            if (str_starts_with($data, 'rec_sync:')) {
                $parts = explode(':', $data);
                $uId = (int) ($parts[1] ?? 0);
                $walletKey = $parts[2] ?? 'default';
                $res = app(\App\Services\ReconciliationService::class)->syncWalletToLedger($uId, $walletKey);
                if ($res['success']) {
                    $cur = setting('currency_symbol', 'global') ?: '$';
                    $msg = "🔧 Synced {$res['wallet_label']}: {$cur}" . number_format($res['old_balance'], 2) . " → {$cur}" . number_format($res['new_balance'], 2);
                    $this->answerCallbackQuery($botToken, $callbackId, $msg, true);
                    $updated = $originalText . "\n\n───────────────────\n✅ <b>AUTO-HEALED (SYNCED BALANCE)</b>\n{$msg}";
                    $this->editMessageText($botToken, $chatId, $messageId, $updated, []);
                } else {
                    $this->answerCallbackQuery($botToken, $callbackId, "⚠️ " . $res['message'], true);
                }
                return response()->json(['status' => 'rec_synced'], 200);
            }

            if (str_starts_with($data, 'rec_adj:')) {
                $parts = explode(':', $data);
                $uId = (int) ($parts[1] ?? 0);
                $walletKey = $parts[2] ?? 'default';
                $res = app(\App\Services\ReconciliationService::class)->insertBalancingEntry($uId, $walletKey, 1);
                if ($res['success']) {
                    $cur = setting('currency_symbol', 'global') ?: '$';
                    $msg = "➕ Inserted {$res['entry_type']} {$cur}" . number_format($res['amount'], 2) . " ({$res['tnx']}) on {$res['wallet_label']}.";
                    $this->answerCallbackQuery($botToken, $callbackId, $msg, true);
                    $updated = $originalText . "\n\n───────────────────\n✅ <b>AUTO-HEALED (BALANCING ENTRY)</b>\n{$msg}";
                    $this->editMessageText($botToken, $chatId, $messageId, $updated, []);
                } else {
                    $this->answerCallbackQuery($botToken, $callbackId, "⚠️ " . $res['message'], true);
                }
                return response()->json(['status' => 'rec_adjusted'], 200);
            }

            // =================================================================
            // Live Session Radar & Instant Kill Callbacks (Feature C)
            // =================================================================
            if (str_starts_with($data, 'usr_kick:')) {
                $uId = (int) trim(substr($data, strlen('usr_kick:')));
                $targetUser = User::find($uId);
                if (!$targetUser) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ Member #{$uId} not found.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $res = app(\App\Services\SessionRadarService::class)->kickMember($targetUser);
                $this->answerCallbackQuery(
                    $botToken,
                    $callbackId,
                    "🚪 Force logout complete for @{$targetUser->username} ({$res['terminated_sessions']} web, {$res['revoked_tokens']} app tokens).",
                    true
                );
                $card = $this->renderUserCard($targetUser, "🚪 <b>SESSIONS TERMINATED</b>\nKilled {$res['terminated_sessions']} web session(s) & revoked {$res['revoked_tokens']} Sanctum API token(s).");
                $this->editMessageText($botToken, $chatId, $messageId, $card['text'], $card['keyboard']);
                return response()->json(['status' => 'user_kicked'], 200);
            }

            if (str_starts_with($data, 'sess_kick:')) {
                $sessId = (int) trim(substr($data, strlen('sess_kick:')));
                $kicked = app(\App\Services\SessionRadarService::class)->kickSessionById($sessId);
                if ($kicked) {
                    $this->answerCallbackQuery($botToken, $callbackId, "🚪 Terminated session for {$kicked->username}.", false);
                    $rendered = $this->buildSessionsRadarPayload();
                    $this->editMessageText($botToken, $chatId, $messageId, $rendered['text'], $rendered['keyboard']);
                } else {
                    $this->answerCallbackQuery($botToken, $callbackId, "⚠️ Session already ended or not found.", true);
                }
                return response()->json(['status' => 'session_kicked'], 200);
            }

            if ($data === 'sess_list') {
                $rendered = $this->buildSessionsRadarPayload();
                $this->editMessageText($botToken, $chatId, $messageId, $rendered['text'], $rendered['keyboard']);
                $this->answerCallbackQuery($botToken, $callbackId, "🔄 Radar refreshed.", false);
                return response()->json(['status' => 'sessions_refreshed'], 200);
            }

            // =================================================================
            // Maintenance Gate Callbacks (Feature D)
            // =================================================================
            if ($data === 'maint_on' || $data === 'maint_off' || $data === 'maint_status') {
                if ($data === 'maint_on') {
                    $res = $this->applyMaintenanceState(true, null, null);
                    $this->answerCallbackQuery($botToken, $callbackId, "🔒 Maintenance Mode ENABLED.", true);
                    $this->editMessageText($botToken, $chatId, $messageId, $res['text'], $res['keyboard']);
                } elseif ($data === 'maint_off') {
                    $res = $this->applyMaintenanceState(false, null, null);
                    $this->answerCallbackQuery($botToken, $callbackId, "🟢 Site is now LIVE.", true);
                    $this->editMessageText($botToken, $chatId, $messageId, $res['text'], $res['keyboard']);
                } else {
                    $res = $this->renderMaintenanceStatusCard();
                    $this->answerCallbackQuery($botToken, $callbackId, "🔄 Refreshed.", false);
                    $this->editMessageText($botToken, $chatId, $messageId, $res['text'], $res['keyboard']);
                }
                return response()->json(['status' => 'maintenance_toggled'], 200);
            }

            // =================================================================
            // Reg E Transaction Dispute Callbacks
            // =================================================================
            if (str_starts_with($data, 'dsp_prov:') || str_starts_with($data, 'dsp_win:') || str_starts_with($data, 'dsp_deny:')) {
                [$actionPrefix, $rawId] = explode(':', $data, 2);
                $disputeId = (int) trim($rawId);
                $disputeService = app(\App\Services\DisputeService::class);

                if ($actionPrefix === 'dsp_prov') {
                    $res = $disputeService->issueProvisionalCredit($disputeId, null, 'Telegram');
                    $toast = $res['success'] ? '💳 Provisional credit posted!' : ('⚠️ ' . ($res['message'] ?? 'Failed.'));
                } elseif ($actionPrefix === 'dsp_win') {
                    $res = $disputeService->resolveAsFraud($disputeId, null, 'Telegram');
                    $toast = $res['success'] ? '✅ Claim upheld (fraud confirmed).' : ('⚠️ ' . ($res['message'] ?? 'Failed.'));
                } else {
                    $res = $disputeService->denyDispute($disputeId, null, 'Telegram');
                    $toast = $res['success'] ? '❌ Claim denied.' : ('⚠️ ' . ($res['message'] ?? 'Failed.'));
                }

                $this->answerCallbackQuery($botToken, $callbackId, mb_substr($toast, 0, 190), true);

                if (!empty($res['dispute'])) {
                    $card = $disputeService->buildTelegramClaimCard($res['dispute']->fresh(['user', 'transaction']));
                    $this->editMessageText($botToken, $chatId, $messageId, $card['text'], $card['keyboard']);
                }
                return response()->json(['status' => 'dispute_action_processed'], 200);
            }

            if (str_starts_with($data, 'dsp_view:')) {
                $disputeId = (int) trim(substr($data, strlen('dsp_view:')));
                $dispute = \App\Models\TransactionDispute::with(['user', 'transaction'])->find($disputeId);
                if (!$dispute) {
                    $this->answerCallbackQuery($botToken, $callbackId, '❌ Dispute claim not found.', true);
                    return response()->json(['status' => 'dispute_not_found'], 200);
                }
                $this->answerCallbackQuery($botToken, $callbackId, "Opening {$dispute->claim_number}...", false);
                $card = app(\App\Services\DisputeService::class)->buildTelegramClaimCard($dispute);
                $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
                return response()->json(['status' => 'dispute_viewed'], 200);
            }

            if (str_starts_with($data, 'usr_div_tog:')) {
                $userId = trim(substr($data, strlen('usr_div_tog:')));
                $targetUser = User::find($userId);
                if (!$targetUser) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ User ID {$userId} not found.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $divService = new \App\Services\DividendService();
                $newStatus = $divService->toggleUserEligibility($targetUser);
                $statusWord = $newStatus ? 'ENABLED (ON)' : 'DISABLED (OFF)';
                $this->answerCallbackQuery($botToken, $callbackId, "Dividends for @{$targetUser->username}: {$statusWord}.", false);
                $card = $this->renderUserCard($targetUser, "⚙️ <b>DIVIDEND ACCRUAL UPDATED</b>\nStatus is now <b>{$statusWord}</b> for this member.");
                $this->editMessageText($botToken, $chatId, $messageId, $card['text'], $card['keyboard']);
                return response()->json(['status' => 'dividend_toggled'], 200);
            }

            // 6. Member Dividend Payout Immediate: usr_div_pay:<user_id>
            if (str_starts_with($data, 'usr_div_pay:')) {
                $userId = trim(substr($data, strlen('usr_div_pay:')));
                $targetUser = User::find($userId);
                if (!$targetUser) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ User ID {$userId} not found.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $divService = new \App\Services\DividendService();
                $res = $divService->processSingleUser($targetUser, 'Telegram Direct Action');
                if ($res['success']) {
                    $this->answerCallbackQuery($botToken, $callbackId, "✅ " . $res['message'], true);
                    $targetUser->refresh();
                    $card = $this->renderUserCard($targetUser, "🎉 <b>DIVIDEND CREDITED</b>\n" . $res['message']);
                    $this->editMessageText($botToken, $chatId, $messageId, $card['text'], $card['keyboard']);
                } else {
                    $this->answerCallbackQuery($botToken, $callbackId, "⚠️ " . $res['message'], true);
                }
                return response()->json(['status' => 'dividend_paid'], 200);
            }

            // 7. Batch Dividend Execution from Preview: div_exec_all
            if ($data === 'div_exec_all') {
                $divService = new \App\Services\DividendService();
                $res = $divService->processBatch(null, 'Telegram One-Click Batch');
                if ($res['success']) {
                    $this->answerCallbackQuery($botToken, $callbackId, "🎉 " . $res['message'], true);
                    $updatedText = $originalText . "\n\n───────────────────\n✅ <b>BATCH EXECUTED SUCCESSFULLY</b>\n💰 Total Paid: " . setting('currency_symbol', '$') . number_format($res['total_amount'], 2) . "\n👥 Members: {$res['processed_count']}\n🕒 " . now()->format('Y-m-d H:i:s');
                    $this->editMessageText($botToken, $chatId, $messageId, $updatedText, []);
                } else {
                    $this->answerCallbackQuery($botToken, $callbackId, "⚠️ " . $res['message'], true);
                }
                return response()->json(['status' => 'batch_dividend_executed'], 200);
            }

            // 8. Staff Status Change: stf_stat:<staff_id>:<new_status>
            if (str_starts_with($data, 'stf_stat:')) {
                $parts = explode(':', $data);
                $staffId = $parts[1] ?? null;
                $newStatus = isset($parts[2]) ? (int)$parts[2] : null;
                $staff = Admin::find($staffId);
                if (!$staff || $newStatus === null) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ Staff not found or invalid data.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $staff->status = $newStatus;
                $staff->save();
                $statusWord = $newStatus ? 'ACTIVATED (🟢 Active)' : 'DISABLED (🔴 Disabled)';
                $this->answerCallbackQuery($botToken, $callbackId, "Staff {$staff->name}: {$statusWord}.", false);
                $card = $this->renderStaffCard($staff, "⚙️ <b>STATUS UPDATED</b>\nStaff account is now <b>{$statusWord}</b>.");
                $this->editMessageText($botToken, $chatId, $messageId, $card['text'], $card['keyboard']);
                return response()->json(['status' => 'staff_status_updated'], 200);
            }

            // Staff Profile View (Fresh Message, Never Overwrites Alerts): stf_view:<staff_id>
            if (str_starts_with($data, 'stf_view:')) {
                $staffId = trim(substr($data, strlen('stf_view:')));
                $staff = Admin::find($staffId);
                if (!$staff) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ Staff ID {$staffId} not found.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $card = $this->renderStaffCard($staff);
                $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
                $this->answerCallbackQuery($botToken, $callbackId, "👔 Viewing {$staff->name}.", false);
                return response()->json(['status' => 'staff_viewed'], 200);
            }

            // 9. Staff Profile Refresh: stf_info:<staff_id>
            if (str_starts_with($data, 'stf_info:')) {
                $staffId = trim(substr($data, strlen('stf_info:')));
                $staff = Admin::find($staffId);
                if (!$staff) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ Staff ID {$staffId} not found.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $card = $this->renderStaffCard($staff);
                if (str_contains($originalText, 'Banking Activity Notification') || str_contains($originalText, 'New Client Registration')) {
                    $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
                } else {
                    $this->editMessageText($botToken, $chatId, $messageId, $card['text'], $card['keyboard']);
                }
                $this->answerCallbackQuery($botToken, $callbackId, "🔄 Refreshed {$staff->name}.", false);
                return response()->json(['status' => 'staff_refreshed'], 200);
            }

            // 10. Staff PIN Prompt: stf_pin_prompt:<staff_id>
            if (str_starts_with($data, 'stf_pin_prompt:')) {
                $staffId = trim(substr($data, strlen('stf_pin_prompt:')));
                $staff = Admin::find($staffId);
                if (!$staff) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ Staff not found.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $this->answerCallbackQuery($botToken, $callbackId, "🔑 Showing PIN reset command...", false);
                $prompt = "🔑 <b>Reset Security Passcode / PIN for {$staff->name}:</b>\n\n";
                $prompt .= "Tap and copy the command below, change the PIN if desired, and send:\n\n";
                $prompt .= "<code>/staff resetpin {$staff->id} 1234</code>";
                $this->sendMessage($botToken, $chatId, $prompt);
                return response()->json(['status' => 'pin_prompt_sent'], 200);
            }

            // 11. Staff Directory: stf_list
            if ($data === 'stf_list') {
                $this->answerCallbackQuery($botToken, $callbackId, "👥 Loading staff directory...", false);
                $this->handleStaffCommand($botToken, $chatId, ['/staff', 'list']);
                return response()->json(['status' => 'staff_list_sent'], 200);
            }

            // 13. Member Freeze / Unfreeze: usr_frz:<user_id>:<status>
            if (str_starts_with($data, 'usr_frz:')) {
                $parts = explode(':', $data);
                $userId = $parts[1] ?? null;
                $freezeAction = isset($parts[2]) ? (int)$parts[2] : 1;
                $targetUser = User::find($userId);
                if (!$targetUser) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ User not found.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $newVal = ($freezeAction === 1) ? 0 : 1;
                $targetUser->transfer_status = $newVal;
                $targetUser->wire_transfer_status = $newVal;
                $targetUser->zelle_transfer_status = $newVal;
                $targetUser->checking_restricted = ($freezeAction === 1) ? 1 : 0;
                $targetUser->save();

                $toast = ($freezeAction === 1) ? "❄️ Transfers FROZEN for @{$targetUser->username}" : "♨️ Transfers RESTORED for @{$targetUser->username}";
                $this->answerCallbackQuery($botToken, $callbackId, $toast, false);

                $notice = ($freezeAction === 1) 
                    ? "❄️ <b>EMERGENCY FREEZE ACTIVATED</b>\nAll outgoing wires, internal transfers, and Zelle transactions are <b>BLOCKED</b> for this member."
                    : "♨️ <b>TRANSFERS UNFROZEN</b>\nTransfer and wire capabilities restored.";

                $card = $this->renderUserCard($targetUser, $notice);
                if (str_contains($originalText, 'Banking Activity Notification') || str_contains($originalText, 'New Client Registration')) {
                    $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
                } else {
                    $this->editMessageText($botToken, $chatId, $messageId, $card['text'], $card['keyboard']);
                }
                return response()->json(['status' => 'freeze_updated'], 200);
            }

            // 14. Pending List shortcut: pend_list
            if ($data === 'pend_list') {
                $this->answerCallbackQuery($botToken, $callbackId, "📋 Loading pending transactions...", false);
                $this->handlePendingCommand($botToken, $chatId);
                return response()->json(['status' => 'pending_loaded'], 200);
            }

            // 16. Member Officer Reassign Prompt: usr_reassign_prompt:<user_id>
            if (str_starts_with($data, 'usr_reassign_prompt:')) {
                $userId = trim(substr($data, strlen('usr_reassign_prompt:')));
                $targetUser = User::find($userId);
                if (!$targetUser) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ Member not found.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $this->answerCallbackQuery($botToken, $callbackId, "🔄 Showing reassign commands...", false);
                $officers = Admin::where('status', 1)->whereHas('roles', fn($q) => $q->whereIn('name', ['Account Officer', 'Account-Officer']))->get();
                $prompt = "🔄 <b>Reassign Member @{$targetUser->username} (#{$targetUser->id}):</b>\n\n";
                $prompt .= "Tap any command to copy, edit, and send:\n\n";
                foreach ($officers as $o) {
                    $prompt .= "• Reassign to <b>{$o->name}</b>:\n<code>/reassign {$targetUser->id} {$o->id}</code>\n\n";
                }
                $prompt .= "• Remove Officer Assignment:\n<code>/reassign {$targetUser->id} 0</code>";

                $this->sendMessage($botToken, $chatId, $prompt);
                return response()->json(['status' => 'reassign_prompt_sent'], 200);
            }

            // 15. Instant Backup shortcut: sys_backup
            if ($data === 'sys_backup') {
                $this->answerCallbackQuery($botToken, $callbackId, "⏳ Generating database backup...", false);
                try {
                    $backupService = new \App\Services\DatabaseBackupService();
                    $result = $backupService->sendToTelegram(null, 'Backup triggered via Telegram health widget.');
                    $this->sendMessage($botToken, $chatId, "💾 " . ($result['message'] ?? 'Backup complete.'));
                } catch (\Throwable $e) {
                    $this->sendMessage($botToken, $chatId, "❌ Backup failed: " . $e->getMessage());
                }
                return response()->json(['status' => 'backup_triggered'], 200);
            }

            // 16. Executive Briefing Refresh: sys_briefing
            if ($data === 'sys_briefing') {
                $this->answerCallbackQuery($botToken, $callbackId, "🔄 Refreshing Sunday Executive Briefing...", false);
                $briefingService = new \App\Services\ExecutiveBriefingService();
                $briefing = $briefingService->renderBriefingMessage('Live Refreshed Digest');
                $this->editMessageText($botToken, $chatId, $messageId, $briefing['text'], $briefing['keyboard']);
                return response()->json(['status' => 'briefing_refreshed'], 200);
            }

            // 17. Member Statement PDF Dispatch: usr_stmt:<user_id>:<period>
            if (str_starts_with($data, 'usr_stmt:')) {
                $parts = explode(':', $data);
                $userId = $parts[1] ?? null;
                $period = $parts[2] ?? '30days';
                $targetUser = User::find($userId);
                if (!$targetUser) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ Member not found.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $this->answerCallbackQuery($botToken, $callbackId, "📄 Generating official eStatement PDF for @{$targetUser->username}...", false);
                $stmtService = new \App\Services\StatementService();
                $stmtService->sendStatementToTelegram($targetUser, $period, $botToken, $chatId);
                return response()->json(['status' => 'statement_sent'], 200);
            }

            // 12. Member Profile Edit Guide: usr_edit_guide:<user_id>
            if (str_starts_with($data, 'usr_edit_guide:')) {
                $userId = trim(substr($data, strlen('usr_edit_guide:')));
                $targetUser = User::find($userId);
                if (!$targetUser) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ Member ID {$userId} not found.", true);
                    return response()->json(['status' => 'not_found'], 200);
                }
                $this->answerCallbackQuery($botToken, $callbackId, "✏️ Showing update commands for @{$targetUser->username}...", false);
                $guide = "✏️ <b>Quick Member Profile Update for {$targetUser->full_name} (@{$targetUser->username}):</b>\n\n";
                $guide .= "Tap any command to copy, edit as needed, and send:\n\n";
                $guide .= "• <b>Name:</b>\n<code>/updateuser {$targetUser->id} name {$targetUser->full_name}</code>\n\n";
                $guide .= "• <b>PIN:</b>\n<code>/updateuser {$targetUser->id} pin 1234</code>\n\n";
                $guide .= "• <b>Password:</b>\n<code>/updateuser {$targetUser->id} password NewPassword123!</code>\n\n";
                $guide .= "• <b>Date of Birth:</b>\n<code>/updateuser {$targetUser->id} dob " . ($targetUser->date_of_birth ?: '1990-01-01') . "</code>\n\n";
                $guide .= "• <b>Address:</b>\n<code>/updateuser {$targetUser->id} address " . ($targetUser->address ?: '123 Main St') . "</code>\n\n";
                $guide .= "• <b>City:</b>\n<code>/updateuser {$targetUser->id} city " . ($targetUser->city ?: 'Tampa') . "</code>\n\n";
                $guide .= "• <b>Phone:</b>\n<code>/updateuser {$targetUser->id} phone " . ($targetUser->phone ?: '+17275550199') . "</code>\n\n";
                $guide .= "• <b>Email:</b>\n<code>/updateuser {$targetUser->id} email {$targetUser->email}</code>";
                $this->sendMessage($botToken, $chatId, $guide);
                return response()->json(['status' => 'edit_guide_sent'], 200);
            }

            // =================================================================
            // Transaction Approval / Rejection Callbacks
            // =================================================================
            // Parse callback action
            // Supported formats: txn_appr:TRX123, txn_rejc:TRX123, appr:TRX123, rejc:TRX123
            $action = null;
            $tnx = null;

            if (preg_match('/^(?:txn_)?(appr|rejc):(.+)$/', $data, $matches)) {
                $action = $matches[1];
                $tnx = trim($matches[2]);
            }

            if (!$action || !$tnx) {
                $this->answerCallbackQuery($botToken, $callbackId, '⚠️ Invalid action parameter.', false);
                return response()->json(['status' => 'invalid_data'], 200);
            }

            // Find transaction
            $txn = Transaction::with(['user', 'bank'])->where('tnx', $tnx)->first();

            if (!$txn) {
                $this->answerCallbackQuery($botToken, $callbackId, "❌ Transaction {$tnx} not found in database.", true);
                return response()->json(['status' => 'not_found'], 200);
            }

            if ($txn->status !== TxnStatus::Pending && $txn->status !== 'pending') {
                $currentStatus = is_object($txn->status) ? strtoupper($txn->status->value) : strtoupper($txn->status);
                $this->answerCallbackQuery($botToken, $callbackId, "⚠️ Transaction {$tnx} is already {$currentStatus}.", true);

                // Remove buttons from message
                $this->editMessageReplyMarkup($botToken, $chatId, $messageId, []);
                return response()->json(['status' => 'already_processed'], 200);
            }

            $timestamp = Carbon::now()->format('Y-m-d H:i:s');

            if ($action === 'appr') {
                $result = $this->approvalService->approve($txn, 'Approved via Telegram Inline Action');

                if ($result['success']) {
                    $this->answerCallbackQuery($botToken, $callbackId, "✅ Transaction {$tnx} APPROVED!", true);

                    // Update Telegram message
                    $updatedText = $originalText . "\n\n───────────────────\n✅ <b>STATUS: APPROVED via Telegram</b>\n🕒 <b>Settlement Time:</b> {$timestamp}\n👤 <b>Admin:</b> Telegram SuperAdmin";
                    $this->editMessageText($botToken, $chatId, $messageId, $updatedText, []);
                } else {
                    $this->answerCallbackQuery($botToken, $callbackId, "⚠️ " . $result['message'], true);
                }
            } elseif ($action === 'rejc') {
                $result = $this->approvalService->reject($txn, 'Rejected and refunded via Telegram Inline Action');

                if ($result['success']) {
                    $this->answerCallbackQuery($botToken, $callbackId, "❌ Transaction {$tnx} REJECTED & REFUNDED!", true);

                    // Update Telegram message
                    $updatedText = $originalText . "\n\n───────────────────\n❌ <b>STATUS: REJECTED & REFUNDED</b>\n🕒 <b>Processed Time:</b> {$timestamp}\n👤 <b>Admin:</b> Telegram SuperAdmin";
                    $this->editMessageText($botToken, $chatId, $messageId, $updatedText, []);
                } else {
                    $this->answerCallbackQuery($botToken, $callbackId, "⚠️ " . $result['message'], true);
                }
            }

            return response()->json(['status' => 'callback_processed'], 200);
        }

        // =====================================================================
        // 2. HANDLE TEXT COMMANDS (e.g. /pending, /approve, /reject, /status)
        // =====================================================================
        if (isset($payload['message'])) {
            $msg = $payload['message'];
            $fromId = $msg['from']['id'] ?? null;
            $chatId = $msg['chat']['id'] ?? $fromId;
            $text = trim($msg['text'] ?? '');

            if (!$this->isAuthorized($fromId, $chatId)) {
                Log::warning("Unauthorized Telegram chat command from ID: {$fromId}");
                return response()->json(['status' => 'unauthorized'], 200);
            }

            if (empty($text)) {
                return response()->json(['status' => 'empty_text'], 200);
            }

            $parts = explode(' ', $text);
            $command = strtolower($parts[0]);

            switch ($command) {
                case '/start':
                case '/help':
                    $chatInst = $this->resolveChatInstitution($chatId);
                    $instBadge = $chatInst ? "🏛 <b>{$chatInst->name} ({$chatInst->short_name})</b>" : "🌐 <b>All Institutions (Global)</b>";

                    $helpMsg = "🏦 <b>Executive Command Center</b>\n";
                    $helpMsg .= "📌 <b>Active Scope:</b> {$instBadge}\n\n";

                    $helpMsg .= "<b>🏛 Multi-Institution Controls:</b>\n";
                    $helpMsg .= "🏛 <code>/institutions</code> — List financial institutions, assets & stats\n";
                    $helpMsg .= "🏛 <code>/switch &lt;csb|ffcu|all&gt;</code> — Set active institution command scope\n\n";
                    
                    $helpMsg .= "<b>⚡ Instant Ledger & Balances:</b>\n";
                    $helpMsg .= "⚡ <code>/credit &lt;user&gt; &lt;amt&gt; [wallet] [desc]</code>\n";
                    $helpMsg .= "   <i>Ex:</i> <code>/credit @Kalyn_Mickle 15000 checking Wire Deposit Inflow</code>\n";
                    $helpMsg .= "💸 <code>/debit &lt;user&gt; &lt;amt&gt; [wallet] [desc]</code>\n";
                    $helpMsg .= "   <i>Ex:</i> <code>/debit @Kalyn_Mickle 250 savings Account Maintenance Fee</code>\n";
                    $helpMsg .= "💰 <code>/balance &lt;user&gt; &lt;wallet&gt; &lt;+amt|-amt&gt; [desc]</code> — Raw balance adjust\n\n";

                    $helpMsg .= "<b>📄 Statements, Ledger & APY:</b>\n";
                    $helpMsg .= "📄 <code>/statement &lt;user&gt; [period/dates]</code>\n";
                    $helpMsg .= "   <i>Ex 1:</i> <code>/statement @Kalyn_Mickle 30days</code>\n";
                    $helpMsg .= "   <i>Ex 2:</i> <code>/statement @Kalyn_Mickle 2026-10-01 2026-10-31</code>\n";
                    $helpMsg .= "📥 <code>/export &lt;user&gt; [limit]</code> — Export ledger .csv for 1-Click Import\n";
                    $helpMsg .= "📈 <code>/dividend [preview|pool|run]</code> — APY pool status & batch payout\n\n";

                    $helpMsg .= "<b>🗓️ Recurring & Scheduled Transactions:</b>\n";
                    $helpMsg .= "🗓️ <code>/schedule &lt;user&gt; &lt;amt&gt; &lt;credit|debit&gt; &lt;wallet&gt; &lt;freq&gt; &lt;desc&gt;</code>\n";
                    $helpMsg .= "   <i>Ex:</i> <code>/schedule @Kalyn_Mickle 3850 credit checking biweekly_1_15 ADP Payroll Direct Deposit</code>\n";
                    $helpMsg .= "📋 <code>/schedule list</code> — View all active recurring schedules\n";
                    $helpMsg .= "▶️ <code>/schedule run</code> — Process all due items immediately\n\n";

                    $helpMsg .= "<b>👤 Member Security & Profiles:</b>\n";
                    $helpMsg .= "👤 <code>/user &lt;user&gt;</code> — Member card with 1-click status & statement buttons\n";
                    $helpMsg .= "✏️ <code>/updateuser &lt;user&gt; &lt;field&gt; &lt;val&gt;</code> — Edit name, DOB, address, PIN, pass, email\n";
                    $helpMsg .= "🔓 <code>/unlock &lt;user&gt;</code> — Clear PIN/MFA lockout and restore active\n";
                    $helpMsg .= "❄️ <code>/freeze &lt;user&gt;</code> — Emergency freeze on wires, transfers, Zelle\n";
                    $helpMsg .= "♨️ <code>/unfreeze &lt;user&gt;</code> — Restore transfer and wire capabilities\n";
                    $helpMsg .= "🔍 <code>/search &lt;query&gt;</code> — Search members by name, acct #, phone, email\n";
                    $helpMsg .= "📋 <code>/pending</code> — Pending transfers with Approve/Reject buttons\n\n";

                    $helpMsg .= "<b>👔 Staff, Officers & Security Lockdown:</b>\n";
                    $helpMsg .= "👥 <code>/staff</code> — Staff directory with client portfolios and stats\n";
                    $helpMsg .= "🚨 <code>/staff lockdown</code> — 1-tap freeze terminating all officer web sessions\n";
                    $helpMsg .= "✅ <code>/staff unlockall</code> — Lift officer portal lockdown\n";
                    $helpMsg .= "🔄 <code>/reassign &lt;user&gt; &lt;officer_id&gt;</code> — Reassign member to officer (0 to clear)\n";
                    $helpMsg .= "🔗 <code>/staff link &lt;id&gt; &lt;code&gt;</code> — Set custom vanity link (e.g. /staff link 14 sarah)\n";
                    $helpMsg .= "🔑 <code>/staff resetpin &lt;id&gt; &lt;pin&gt;</code> — Reset staff dashboard PIN\n";
                    $helpMsg .= "🔐 <code>/staff resetpass &lt;id&gt; &lt;pass&gt;</code> — Reset staff password\n\n";

                    $helpMsg .= "<b>☀️ Operations &amp; System Health:</b>\n";
                    $helpMsg .= "⚖️ <code>/reconcile [user|all]</code> — Audit wallet balances vs settled ledger &amp; auto-heal\n";
                    $helpMsg .= "   <i>Ex:</i> <code>/reconcile @Kalyn_Mickle</code> or <code>/reconcile all</code>\n";
                    $helpMsg .= "🛰️ <code>/sessions</code> — Live radar of active online members &amp; staff\n";
                    $helpMsg .= "🚪 <code>/kick &lt;user|staff:id&gt;</code> — Instant force-logout (web sessions + mobile tokens)\n";
                    $helpMsg .= "   <i>Ex:</i> <code>/kick @Kalyn_Mickle</code>\n";
                    $helpMsg .= "🔧 <code>/maintenance &lt;on|off|status|allowip&gt; [ETA | message]</code>\n";
                    $helpMsg .= "   <i>Ex:</i> <code>/maintenance on 30 mins | Upgrading Wire Engine</code>\n";
                    $helpMsg .= "📸 <i>Check Deposits:</i> Photos &amp; Reg CC Hold (\$225 now / rest 2d) arrive with 1-tap buttons\n";
                    $helpMsg .= "🛡️ <code>/disputes [open|all]</code> — List Reg E transaction dispute claims queue\n";
                    $helpMsg .= "🔍 <code>/dispute &lt;DSP-XXXXXX&gt;</code> — Inspect claim card &amp; issue Provisional Credit / Resolve / Deny\n";
                    $helpMsg .= "   <i>Ex:</i> <code>/dispute DSP-104829</code>\n";
                    $helpMsg .= "☀️ <code>/briefing</code> — Sunday Executive Operations Digest\n";
                    $helpMsg .= "💾 <code>/backup</code> — Trigger live database backup archive (.sql.gz)\n";
                    $helpMsg .= "🖥️ <code>/health</code> — Live server vitals (RAM, Disk, DB latency, PHP, Uptime)\n";
                    $helpMsg .= "🛡️ <code>/audit [limit]</code> — Security log of recent auth events & lockouts\n";
                    $helpMsg .= "❓ <code>/help</code> — Show this manual\n";

                    $this->sendMessage($botToken, $chatId, $helpMsg);
                    break;

                case '/institutions':
                case '/institution':
                    $this->handleInstitutionsCommand($botToken, $chatId);
                    break;

                case '/switch':
                case '/scope':
                    $targetScope = strtolower($parts[1] ?? '');
                    $this->handleSwitchScopeCommand($botToken, $chatId, $targetScope);
                    break;

                case '/pending':
                    $this->handlePendingCommand($botToken, $chatId);
                    break;

                case '/approve':
                    $targetTnx = $parts[1] ?? null;
                    if (!$targetTnx) {
                        $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/approve TRX12345678</code>");
                    } else {
                        $this->handleDirectApproval($botToken, $chatId, trim($targetTnx));
                    }
                    break;

                case '/reject':
                    $targetTnx = $parts[1] ?? null;
                    if (!$targetTnx) {
                        $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/reject TRX12345678 [optional reason]</code>");
                    } else {
                        $reason = isset($parts[2]) ? implode(' ', array_slice($parts, 2)) : 'Rejected via Telegram command';
                        $this->handleDirectRejection($botToken, $chatId, trim($targetTnx), $reason);
                    }
                    break;

                case '/unlock':
                    $target = $parts[1] ?? null;
                    if (!$target) {
                        $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/unlock &lt;user_id_or_username&gt;</code>\n<i>Example:</i> <code>/unlock 25</code> or <code>/unlock @john_doe</code>");
                    } else {
                        $user = $this->findUserByIdentifier($target);
                        if (!$user) {
                            $this->sendMessage($botToken, $chatId, "❌ Member <code>{$target}</code> was not found. Please verify the ID or username.");
                        } else {
                            $this->unlockUser($user, $botToken, $chatId);
                        }
                    }
                    break;

                case '/user':
                case '/member':
                    $target = $parts[1] ?? null;
                    if (!$target) {
                        $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/user &lt;user_id_or_username&gt;</code>\n<i>Example:</i> <code>/user 25</code> or <code>/user @john_doe</code>");
                    } else {
                        $user = $this->findUserByIdentifier($target);
                        if (!$user) {
                            $this->sendMessage($botToken, $chatId, "❌ Member <code>{$target}</code> was not found. Please check ID, username, or email.");
                        } else {
                            $card = $this->renderUserCard($user);
                            $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
                        }
                    }
                    break;

                case '/dividend':
                case '/dividends':
                    $this->handleDividendCommand($botToken, $chatId, array_slice($parts, 1));
                    break;

                case '/reassign':
                case '/setofficer':
                    $this->handleReassignCommand($botToken, $chatId, $parts);
                    break;

                case '/updateuser':
                case '/edituser':
                case '/setuser':
                    $this->handleUpdateUserCommand($botToken, $chatId, $parts);
                    break;

                case '/staff':
                case '/staffs':
                case '/admin':
                case '/admins':
                    $this->handleStaffCommand($botToken, $chatId, $parts);
                    break;

                case '/export':
                case '/ledger':
                    $target = $parts[1] ?? null;
                    $limit = isset($parts[2]) && is_numeric($parts[2]) ? (int)$parts[2] : null;
                    if (!$target) {
                        $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/export &lt;user_id_or_username&gt; [limit]</code>\n<i>Example:</i> <code>/export 2</code> or <code>/export @Kalyn_Mickle009 50</code>");
                    } else {
                        $user = $this->findUserByIdentifier($target);
                        if (!$user) {
                            $this->sendMessage($botToken, $chatId, "❌ Member <code>{$target}</code> was not found.");
                        } else {
                            $this->sendMessage($botToken, $chatId, "📥 Generating ledger export for <b>{$user->full_name}</b> (@{$user->username})...");
                            $this->exportUserLedgerToTelegram($user, $botToken, $chatId, $limit);
                        }
                    }
                    break;

                case '/setstatus':
                    $target = $parts[1] ?? null;
                    $statusArg = strtolower($parts[2] ?? '');
                    if (!$target || empty($statusArg)) {
                        $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/setstatus &lt;user_id_or_username&gt; &lt;active|disabled|closed&gt;</code>\n<i>Example:</i> <code>/setstatus 25 active</code>");
                    } else {
                        $user = $this->findUserByIdentifier($target);
                        if (!$user) {
                            $this->sendMessage($botToken, $chatId, "❌ Member <code>{$target}</code> was not found.");
                        } else {
                            $statusMap = [
                                'active' => 1, '1' => 1, 'enable' => 1, 'enabled' => 1, 'unlock' => 1,
                                'disabled' => 0, '0' => 0, 'disable' => 0, 'lock' => 0, 'inactive' => 0,
                                'closed' => 2, '2' => 2, 'close' => 2
                            ];
                            if (!array_key_exists($statusArg, $statusMap)) {
                                $this->sendMessage($botToken, $chatId, "⚠️ Invalid status <code>{$statusArg}</code>. Valid options are: <code>active</code>, <code>disabled</code>, or <code>closed</code>.");
                            } else {
                                $this->setUserStatus($user, $statusMap[$statusArg], $botToken, $chatId);
                            }
                        }
                    }
                    break;

                case '/status':
                    $target = $parts[1] ?? null;
                    $extraArg = strtolower($parts[2] ?? '');
                    if (!$target) {
                        $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/status &lt;TRX_or_User&gt; [optional_new_status]</code>\n<i>Examples:</i>\n• <code>/status TRX12345678</code>\n• <code>/status @john_doe</code>\n• <code>/status 25 active</code>");
                    } elseif (!empty($extraArg)) {
                        // User passed two arguments e.g. /status 25 active -> route to setstatus
                        $user = $this->findUserByIdentifier($target);
                        if (!$user) {
                            $this->sendMessage($botToken, $chatId, "❌ Member <code>{$target}</code> was not found.");
                        } else {
                            $statusMap = [
                                'active' => 1, '1' => 1, 'enable' => 1, 'enabled' => 1, 'unlock' => 1,
                                'disabled' => 0, '0' => 0, 'disable' => 0, 'lock' => 0, 'inactive' => 0,
                                'closed' => 2, '2' => 2, 'close' => 2
                            ];
                            if (!array_key_exists($extraArg, $statusMap)) {
                                $this->sendMessage($botToken, $chatId, "⚠️ Invalid status <code>{$extraArg}</code>. Use <code>active</code>, <code>disabled</code>, or <code>closed</code>.");
                            } else {
                                $this->setUserStatus($user, $statusMap[$extraArg], $botToken, $chatId);
                            }
                        }
                    } else {
                        $this->handleStatusCommand($botToken, $chatId, trim($target));
                    }
                    break;

                case '/health':
                case '/system':
                case '/vitals':
                    $this->handleHealthCommand($botToken, $chatId);
                    break;

                case '/search':
                case '/find':
                    $this->handleSearchCommand($botToken, $chatId, $parts);
                    break;

                case '/credit':
                    $this->handleInstantCreditDebit($botToken, $chatId, $parts, true);
                    break;

                case '/debit':
                    $this->handleInstantCreditDebit($botToken, $chatId, $parts, false);
                    break;

                case '/statement':
                case '/estatement':
                    $this->handleStatementCommand($botToken, $chatId, $parts);
                    break;

                case '/schedule':
                case '/recurring':
                    $this->handleScheduleCommand($botToken, $chatId, $parts);
                    break;

                case '/balance':
                case '/adjustbalance':
                case '/setbalance':
                    $this->handleBalanceCommand($botToken, $chatId, $parts);
                    break;

                case '/freeze':
                case '/locktransfers':
                    $this->handleFreezeCommand($botToken, $chatId, $parts, true);
                    break;

                case '/unfreeze':
                case '/unlocktransfers':
                    $this->handleFreezeCommand($botToken, $chatId, $parts, false);
                    break;

                case '/audit':
                case '/security':
                case '/logs':
                    $this->handleAuditCommand($botToken, $chatId, $parts);
                    break;

                case '/backup':
                    try {
                        $backupService = new \App\Services\DatabaseBackupService();
                        $result = $backupService->sendToTelegram(null, 'Manual backup requested via Telegram /backup command.');
                        $this->sendMessage($botToken, $chatId, "💾 " . ($result['message'] ?? 'Backup operation complete.'));
                    } catch (\Throwable $e) {
                        $this->sendMessage($botToken, $chatId, "❌ Backup failed: " . $e->getMessage());
                    }
                    break;

                case '/reconcile':
                case '/auditbalance':
                    $this->handleReconcileCommand($botToken, $chatId, $parts);
                    break;

                case '/sessions':
                case '/radar':
                case '/online':
                    $this->handleSessionsCommand($botToken, $chatId);
                    break;

                case '/kick':
                case '/forcelogout':
                    $this->handleKickCommand($botToken, $chatId, $parts);
                    break;

                case '/maintenance':
                case '/maint':
                    $this->handleMaintenanceCommand($botToken, $chatId, $parts);
                    break;

                case '/briefing':
                case '/digest':
                    try {
                        $briefingService = new \App\Services\ExecutiveBriefingService();
                        $briefing = $briefingService->renderBriefingMessage('Requested on-demand via Telegram');
                        $this->sendMessage($botToken, $chatId, $briefing['text'], $briefing['keyboard']);
                    } catch (\Throwable $e) {
                        $this->sendMessage($botToken, $chatId, "❌ Failed to generate briefing: " . $e->getMessage());
                    }
                    break;

                case '/disputes':
                    $this->handleDisputesListCommand($botToken, $chatId, $parts);
                    break;

                case '/dispute':
                case '/claim':
                    $this->handleDisputeInspectCommand($botToken, $chatId, $parts);
                    break;

                default:
                    // Only reply to slash commands
                    if (str_starts_with($command, '/')) {
                        $this->sendMessage($botToken, $chatId, "❓ Unknown command: <code>{$command}</code>. Type <code>/help</code> for available options.");
                    }
                    break;
            }

            return response()->json(['status' => 'message_processed'], 200);
        }

        return response()->json(['status' => 'ignored'], 200);
    }

    /**
     * Handle /pending command.
     */
    protected function handlePendingCommand(string $botToken, string $chatId, ?string $scope = null): void
    {
        $chatInst = $this->resolveChatInstitution($chatId);
        if ($scope) {
            \App\Models\Institution::ensureSchema();
            $scopeInst = \App\Models\Institution::where('slug', strtolower($scope))->orWhere('short_name', strtoupper($scope))->first();
            if ($scopeInst) {
                $chatInst = $scopeInst;
            }
        }

        $pendingQuery = Transaction::with('user.institution')
            ->where('status', TxnStatus::Pending);

        if ($chatInst) {
            $pendingQuery->whereHas('user', fn($q) => $q->where('institution_id', $chatInst->id));
        }

        $pendingTxns = $pendingQuery->latest()
            ->take(10)
            ->get();

        $scopeLabel = $chatInst ? " for {$chatInst->name} ({$chatInst->short_name})" : "";

        if ($pendingTxns->isEmpty()) {
            $this->sendMessage($botToken, $chatId, "✨ <b>No pending transactions found{$scopeLabel}!</b> All transfers and withdrawals are up to date.");
            return;
        }

        $currencySymbol = setting('currency_symbol', '$');
        $this->sendMessage($botToken, $chatId, "📋 <b>Found {$pendingTxns->count()} Pending Transaction(s){$scopeLabel}:</b>");

        foreach ($pendingTxns as $txn) {
            $instShort = $txn->user?->institution ? $txn->user->institution->short_name : 'FFCU';
            $member = $txn->user ? "{$txn->user->full_name} (@{$txn->user->username})" : "Unknown";
            $typeStr = is_object($txn->type) ? $txn->type->value : $txn->type;
            
            $msg = "🔖 <b>Ref:</b> <code>{$txn->tnx}</code> [<b>{$instShort}</b>]\n";
            $msg .= "👤 <b>Member:</b> {$member}\n";
            $msg .= "💵 <b>Amount:</b> {$currencySymbol}" . number_format($txn->amount, 2) . "\n";
            $msg .= "📝 <b>Description:</b> " . htmlspecialchars($txn->description ?? 'N/A') . "\n";
            $msg .= "📅 <b>Date:</b> " . $txn->created_at->format('M d, Y H:i');

            $keyboard = [
                [
                    ['text' => '✅ Approve', 'callback_data' => 'txn_appr:' . $txn->tnx],
                    ['text' => '❌ Reject', 'callback_data' => 'txn_rejc:' . $txn->tnx],
                ]
            ];

            $this->sendMessage($botToken, $chatId, $msg, $keyboard);
        }
    }

    /**
     * Handle /approve <tnx>.
     */
    protected function handleDirectApproval(string $botToken, string $chatId, string $tnx): void
    {
        $txn = Transaction::with(['user', 'bank'])->where('tnx', $tnx)->first();

        if (!$txn) {
            $this->sendMessage($botToken, $chatId, "❌ Transaction <code>{$tnx}</code> was not found.");
            return;
        }

        $result = $this->approvalService->approve($txn, 'Approved via /approve command');

        if ($result['success']) {
            $this->sendMessage($botToken, $chatId, "✅ <b>SUCCESS:</b> Transaction <code>{$tnx}</code> has been APPROVED!");
        } else {
            $this->sendMessage($botToken, $chatId, "⚠️ " . $result['message']);
        }
    }

    /**
     * Handle /reject <tnx>.
     */
    protected function handleDirectRejection(string $botToken, string $chatId, string $tnx, string $reason): void
    {
        $txn = Transaction::with(['user', 'bank'])->where('tnx', $tnx)->first();

        if (!$txn) {
            $this->sendMessage($botToken, $chatId, "❌ Transaction <code>{$tnx}</code> was not found.");
            return;
        }

        $result = $this->approvalService->reject($txn, $reason);

        if ($result['success']) {
            $this->sendMessage($botToken, $chatId, "❌ <b>SUCCESS:</b> Transaction <code>{$tnx}</code> has been REJECTED and refunded.\nReason: <i>{$reason}</i>");
        } else {
            $this->sendMessage($botToken, $chatId, "⚠️ " . $result['message']);
        }
    }

    /**
     * Handle /status <target> (Target can be a transaction reference or a member).
     */
    protected function handleStatusCommand(string $botToken, string $chatId, string $target): void
    {
        // 1. Check if target is a transaction reference
        $txn = Transaction::with(['user', 'bank'])->where('tnx', $target)->first();

        if ($txn) {
            $this->sendTransactionStatusReport($botToken, $chatId, $txn);
            return;
        }

        // 2. Check if target is a member identifier (ID, @username, email, account#)
        $user = $this->findUserByIdentifier($target);
        if ($user) {
            $card = $this->renderUserCard($user);
            $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
            return;
        }

        $this->sendMessage($botToken, $chatId, "❌ No transaction or member found matching <code>{$target}</code>.\nUse <code>/status TRX12345678</code> or <code>/status &lt;user_id_or_username&gt;</code>.");
    }

    /**
     * Send Transaction Status Report.
     */
    protected function sendTransactionStatusReport(string $botToken, string $chatId, Transaction $txn): void
    {
        $currencySymbol = setting('currency_symbol', '$');
        $statusStr = is_object($txn->status) ? strtoupper($txn->status->value) : strtoupper($txn->status);
        $statusEmoji = match ($statusStr) {
            'SUCCESS' => '✅',
            'PENDING' => '⏳',
            'FAILED' => '❌',
            default => 'ℹ️'
        };

        $msg = "🔍 <b>Transaction Status Report</b>\n\n";
        $msg .= "🔖 <b>Ref:</b> <code>{$txn->tnx}</code>\n";
        $msg .= "📊 <b>Status:</b> {$statusEmoji} <b>{$statusStr}</b>\n";
        $msg .= "👤 <b>Member:</b> " . ($txn->user ? "{$txn->user->full_name} (@{$txn->user->username})" : 'N/A') . "\n";
        $msg .= "💵 <b>Amount:</b> {$currencySymbol}" . number_format($txn->amount, 2) . "\n";
        $msg .= "💳 <b>Fee:</b> {$currencySymbol}" . number_format($txn->charge, 2) . "\n";
        $msg .= "💰 <b>Final Amount:</b> {$currencySymbol}" . number_format($txn->final_amount, 2) . "\n";
        $msg .= "🏦 <b>Wallet:</b> " . ucfirst($txn->wallet_type ?? 'default') . "\n";
        $msg .= "📝 <b>Description:</b> " . htmlspecialchars($txn->description ?? 'N/A') . "\n";
        if ($txn->action_message) {
            $msg .= "💬 <b>Admin Note:</b> <i>" . htmlspecialchars($txn->action_message) . "</i>\n";
        }
        $msg .= "📅 <b>Created:</b> " . $txn->created_at->format('Y-m-d H:i:s') . "\n";

        $keyboard = [];
        if ($statusStr === 'PENDING') {
            $keyboard = [
                [
                    ['text' => '✅ Approve', 'callback_data' => 'txn_appr:' . $txn->tnx],
                    ['text' => '❌ Reject', 'callback_data' => 'txn_rejc:' . $txn->tnx],
                ]
            ];
        }

        $this->sendMessage($botToken, $chatId, $msg, $keyboard);
    }

    /**
     * Render rich Member Profile card with live status buttons.
     */
    protected function renderUserCard(User $user, ?string $extraNotice = null): array
    {
        $currencySymbol = setting('currency_symbol', '$');
        
        $statusInt = (int)$user->status;
        $statusLabel = match ($statusInt) {
            1 => '🟢 ACTIVE',
            0 => '🔴 DISABLED / LOCKED',
            2 => '⚫ CLOSED',
            default => '⚪ UNKNOWN (' . $statusInt . ')'
        };

        $kycLabel = $user->kyc ? 'Verified' : 'Unverified';

        $text = "👤 <b>Member Account Profile</b>\n\n";
        if ($extraNotice) {
            $text .= "{$extraNotice}\n\n";
        }
        $text .= "🆔 <b>User ID:</b> <code>{$user->id}</code>\n";
        $instName = $user->institution ? $user->institution->name : 'FrontField Credit Union';
        $instShort = $user->institution ? $user->institution->short_name : 'FFCU';
        $text .= "🏛️ <b>Institution:</b> {$instName} (<b>{$instShort}</b>)\n";
        $text .= "👤 <b>Name:</b> " . htmlspecialchars($user->full_name) . "\n";
        $text .= "🏷️ <b>Username:</b> @{$user->username}\n";
        $text .= "💳 <b>Account #:</b> <code>{$user->account_number}</code>\n";
        $text .= "📧 <b>Email:</b> {$user->email}\n";
        if (!empty($user->phone)) {
            $text .= "📱 <b>Phone:</b> {$user->phone}\n";
        }
        if (!empty($user->date_of_birth)) {
            $text .= "📅 <b>DOB:</b> {$user->date_of_birth}\n";
        }
        if (!empty($user->address) || !empty($user->city)) {
            $loc = trim(($user->address ?? '') . ' ' . ($user->city ?? ''));
            $text .= "📍 <b>Address:</b> " . htmlspecialchars($loc) . "\n";
        }
        $pinBadge = !empty($user->transaction_pin) ? 'Configured' : 'Not Set';
        $text .= "🔑 <b>PIN:</b> {$pinBadge}\n";
        $officerName = $user->staff ? htmlspecialchars($user->staff->name) . " (ID: #{$user->staff_id})" : 'None (Unassigned)';
        $text .= "👔 <b>Account Officer:</b> {$officerName}\n";
        $isFrozen = ($user->transfer_status == 0 && $user->wire_transfer_status == 0);
        $frzBadge = $isFrozen ? '🔴 FROZEN' : '🟢 ACTIVE';
        $text .= "🛡️ <b>Transfers/Wires:</b> <b>{$frzBadge}</b>\n";
        $text .= "📊 <b>Account Status:</b> <b>{$statusLabel}</b>\n";
        $text .= "🛡️ <b>KYC Status:</b> {$kycLabel}\n";
        $text .= "───────────────────\n";
        $text .= "💰 <b>Checking Balance:</b> {$currencySymbol}" . number_format($user->balance, 2) . "\n";
        $text .= "🏦 <b>Primary Savings:</b> {$currencySymbol}" . number_format($user->savings_balance, 2) . "\n";
        if (isset($user->ira_status) && $user->ira_status) {
            $text .= "📈 <b>IRA Balance:</b> {$currencySymbol}" . number_format($user->ira_balance ?? 0, 2) . "\n";
        }
        if (isset($user->heloc_status) && $user->heloc_status) {
            $text .= "🏡 <b>HELOC Balance:</b> {$currencySymbol}" . number_format($user->heloc_balance ?? 0, 2) . "\n";
        }
        if (isset($user->cc_status) && $user->cc_status) {
            $text .= "💳 <b>Credit Card:</b> {$currencySymbol}" . number_format($user->cc_balance ?? 0, 2) . "\n";
        }
        $isDivOn = (bool)($user->dividend_status ?? false);
        $globalApy = (float)(\App\Models\Setting::get('savings_dividend_apy', 'dividend') ?? 3.25);
        $effectiveApy = $user->dividend_apy ?: $globalApy;
        $divBadge = $isDivOn ? "🟢 <b>ON</b> (" . number_format($effectiveApy, 2) . "% APY)" : "⚪ <b>OFF (Default)</b>";
        $text .= "📈 <b>Share Dividends:</b> {$divBadge}\n";

        $text .= "🕒 <b>Updated:</b> " . Carbon::now()->format('Y-m-d H:i:s');

        // Build interactive buttons based on status
        $statusButtons = [];
        if ($statusInt === 1) {
            $statusButtons[] = ['text' => '🔴 Disable Account', 'callback_data' => 'usr_stat:' . $user->id . ':0'];
            $statusButtons[] = ['text' => '⚫ Close Account', 'callback_data' => 'usr_stat:' . $user->id . ':2'];
        } elseif ($statusInt === 0) {
            $statusButtons[] = ['text' => '🔓 Activate & Unlock', 'callback_data' => 'usr_stat:' . $user->id . ':1'];
            $statusButtons[] = ['text' => '⚫ Close Account', 'callback_data' => 'usr_stat:' . $user->id . ':2'];
        } else {
            $statusButtons[] = ['text' => '🟢 Reactivate Account', 'callback_data' => 'usr_stat:' . $user->id . ':1'];
            $statusButtons[] = ['text' => '🔴 Disable Account', 'callback_data' => 'usr_stat:' . $user->id . ':0'];
        }

        $divButtons = [];
        if ($isDivOn) {
            $divButtons[] = ['text' => '📉 Dividends: Turn OFF', 'callback_data' => 'usr_div_tog:' . $user->id];
            $divButtons[] = ['text' => '💵 Credit Dividend Now', 'callback_data' => 'usr_div_pay:' . $user->id];
        } else {
            $divButtons[] = ['text' => '📈 Dividends: Turn ON', 'callback_data' => 'usr_div_tog:' . $user->id];
        }

        $isFrozen = ($user->transfer_status == 0 && $user->wire_transfer_status == 0);
        $freezeBtn = $isFrozen
            ? ['text' => '♨️ Unfreeze Transfers', 'callback_data' => 'usr_frz:' . $user->id . ':0']
            : ['text' => '❄️ Freeze Transfers', 'callback_data' => 'usr_frz:' . $user->id . ':1'];

        $keyboard = [
            $statusButtons,
            $divButtons,
            [
                $freezeBtn,
                ['text' => '🚪 Force Logout', 'callback_data' => 'usr_kick:' . $user->id],
            ],
            [
                ['text' => '✏️ Update Profile', 'callback_data' => 'usr_edit_guide:' . $user->id],
                ['text' => '🔄 Reassign Officer', 'callback_data' => 'usr_reassign_prompt:' . $user->id],
            ],
            [
                ['text' => '📥 Export Ledger (.csv)', 'callback_data' => 'usr_export:' . $user->id],
                ['text' => '📄 Statement (30d)', 'callback_data' => 'usr_stmt:' . $user->id . ':30d'],
            ],
            [
                ['text' => '🔄 Refresh Info', 'callback_data' => 'usr_info:' . $user->id],
            ]
        ];

        return [
            'text' => $text,
            'keyboard' => $keyboard,
        ];
    }

    /**
     * Unlock member and clear all security lockout attempts.
     */
    protected function unlockUser(User $user, string $botToken, string $chatId, ?int $messageId = null, ?string $callbackId = null): void
    {
        $user->status = 1;
        $user->save();

        // Clear security codes / failed lockout counters
        TransactionSecurityCode::where('user_id', $user->id)->delete();

        $notice = "✅ <b>SECURITY LOCKOUT CLEARED & ACCOUNT RESTORED</b>\nMember is now fully <b>ACTIVE</b> and can log in and transact immediately.";
        $card = $this->renderUserCard($user, $notice);

        if ($callbackId) {
            $this->answerCallbackQuery($botToken, $callbackId, "✅ User @{$user->username} unlocked and activated!", true);
        }

        if ($messageId) {
            $this->editMessageText($botToken, $chatId, $messageId, $card['text'], $card['keyboard']);
        } else {
            $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
        }
    }

    /**
     * Update member account status (Active=1, Disabled=0, Closed=2) from Telegram.
     */
    protected function setUserStatus(User $user, int $targetStatus, string $botToken, string $chatId, ?int $messageId = null, ?string $callbackId = null, ?string $originalText = null): void
    {
        $oldStatus = (int)$user->status;
        $user->status = $targetStatus;
        $user->save();

        if ($targetStatus === 1) {
            // Reset security codes if moving to active
            TransactionSecurityCode::where('user_id', $user->id)->delete();
        }

        // Notify member if account is disabled
        if ($targetStatus === 0 && $oldStatus !== 0) {
            try {
                $shortcodes = [
                    '[[full_name]]' => $user->full_name,
                    '[[site_title]]' => setting('site_title', 'global'),
                    '[[site_url]]' => route('home'),
                ];
                $this->mailNotify($user->email, 'user_account_disabled', $shortcodes);
                $this->smsNotify('user_account_disabled', $shortcodes, $user->phone);
            } catch (\Throwable $e) {
                Log::error("Failed to notify user about disabled account: " . $e->getMessage());
            }
        }

        $statusName = match ($targetStatus) {
            1 => '🟢 ACTIVE',
            0 => '🔴 DISABLED',
            2 => '⚫ CLOSED',
            default => 'STATUS ' . $targetStatus
        };

        $notice = "⚙️ <b>ACCOUNT STATUS UPDATED</b>\nNew Status: <b>{$statusName}</b>";
        $card = $this->renderUserCard($user, $notice);

        if ($callbackId) {
            $this->answerCallbackQuery($botToken, $callbackId, "Status updated to {$statusName}", false);
        }

        if ($messageId && !str_contains((string)$originalText, 'Banking Activity Notification') && !str_contains((string)$originalText, 'New Client Registration')) {
            $this->editMessageText($botToken, $chatId, $messageId, $card['text'], $card['keyboard']);
        } else {
            $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
        }
    }

    /**
     * Find member by ID, @username, email, or account number.
     */
    protected function findUserByIdentifier(string $identifier): ?User
    {
        $identifier = trim($identifier);
        $clean = ltrim($identifier, '@');

        // 1. By Numeric ID
        if (is_numeric($clean)) {
            $user = User::find($clean);
            if ($user) return $user;
        }

        // 2. By Username
        $user = User::where('username', $clean)->first();
        if ($user) return $user;

        // 3. By Email
        $user = User::where('email', $identifier)->first();
        if ($user) return $user;

        // 4. By Account Number (raw or with prefix)
        $prefix = setting('account_number_prefix', 'global') ?? '';
        $rawAccount = !empty($prefix) ? str_replace($prefix, '', $clean) : $clean;

        return User::where('account_number', $clean)
            ->orWhere('account_number', $rawAccount)
            ->first();
    }

    /**
     * Send Telegram message helper.
     */
    protected function sendMessage(string $botToken, string $chatId, string $text, ?array $inlineKeyboard = null): bool
    {
        try {
            $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
            $payload = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            if (!empty($inlineKeyboard)) {
                $payload['reply_markup'] = [
                    'inline_keyboard' => $inlineKeyboard,
                ];
            }

            Http::timeout(5)->post($apiUrl, $payload);
            return true;
        } catch (\Throwable $e) {
            Log::error("Telegram sendMessage error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Answer callback query toast helper.
     */
    protected function answerCallbackQuery(string $botToken, ?string $callbackId, string $text, bool $showAlert = false): void
    {
        if (!$callbackId) return;

        try {
            $apiUrl = "https://api.telegram.org/bot{$botToken}/answerCallbackQuery";
            Http::timeout(5)->post($apiUrl, [
                'callback_query_id' => $callbackId,
                'text' => $text,
                'show_alert' => $showAlert,
            ]);
        } catch (\Throwable $e) {
            Log::error("Telegram answerCallbackQuery error: " . $e->getMessage());
        }
    }

    /**
     * Edit message text helper.
     */
    protected function editMessageText(string $botToken, string $chatId, ?int $messageId, string $text, ?array $inlineKeyboard = null): void
    {
        if (!$messageId) return;

        try {
            $apiUrl = "https://api.telegram.org/bot{$botToken}/editMessageText";
            $payload = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            if ($inlineKeyboard !== null) {
                $payload['reply_markup'] = [
                    'inline_keyboard' => $inlineKeyboard,
                ];
            }

            Http::timeout(5)->post($apiUrl, $payload);
        } catch (\Throwable $e) {
            Log::error("Telegram editMessageText error: " . $e->getMessage());
        }
    }

    /**
     * Edit message reply markup helper.
     */
    protected function editMessageReplyMarkup(string $botToken, string $chatId, ?int $messageId, array $inlineKeyboard = []): void
    {
        if (!$messageId) return;

        try {
            $apiUrl = "https://api.telegram.org/bot{$botToken}/editMessageReplyMarkup";
            Http::timeout(5)->post($apiUrl, [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'reply_markup' => [
                    'inline_keyboard' => $inlineKeyboard,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error("Telegram editMessageReplyMarkup error: " . $e->getMessage());
        }
    }

    /**
     * Retrieve or generate the cryptographic Telegram webhook secret token.
     */
    protected function getWebhookSecret(bool $generateIfMissing = false, bool $forceRegenerate = false): string
    {
        if (! $forceRegenerate) {
            $stored = (string) (Setting::get('telegram_webhook_secret', 'telegram') ?: env('TELEGRAM_WEBHOOK_SECRET', ''));
            if ($stored !== '') {
                return $stored;
            }
        }

        if (! $generateIfMissing && ! $forceRegenerate) {
            return '';
        }

        $secret = bin2hex(random_bytes(32));
        Setting::add('telegram_webhook_secret', $secret, 'string');
        return $secret;
    }

    /**
     * One-click helper endpoint to register webhook with Telegram API + secret token.
     */
    public function setupWebhook(Request $request): JsonResponse
    {
        [$botToken, $chatId] = $this->getCredentials();

        if (!$botToken) {
            return response()->json(['success' => false, 'message' => 'TELEGRAM_BOT_TOKEN is not configured.'], 400);
        }

        $webhookUrl = url('api/telegram/webhook');
        $secretToken = $this->getWebhookSecret(true, $request->boolean('regenerate'));

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/setWebhook", [
                'url' => $webhookUrl,
                'secret_token' => $secretToken,
                'allowed_updates' => ['message', 'callback_query'],
                'drop_pending_updates' => false,
            ]);

            return response()->json([
                'success' => $response->successful(),
                'webhook_url' => $webhookUrl,
                'secret_token_configured' => true,
                'secret_fingerprint' => substr(hash('sha256', $secretToken), 0, 12),
                'telegram_response' => $response->json(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper to inspect current Telegram webhook status.
     */
    public function webhookInfo(): JsonResponse
    {
        [$botToken, $chatId] = $this->getCredentials();

        if (!$botToken) {
            return response()->json(['success' => false, 'message' => 'TELEGRAM_BOT_TOKEN is not configured.'], 400);
        }

        $secret = $this->getWebhookSecret(false);

        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$botToken}/getWebhookInfo");

            return response()->json([
                'success' => $response->successful(),
                'secret_token_configured' => $secret !== '',
                'secret_fingerprint' => $secret !== '' ? substr(hash('sha256', $secret), 0, 12) : null,
                'telegram_info' => $response->json(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Export member ledger to Telegram as a CSV file ready for 1-Click Import.
     */
    protected function exportUserLedgerToTelegram(User $user, string $botToken, string $chatId, ?int $limit = null): void
    {
        $query = Transaction::where('user_id', $user->id)->orderBy('created_at', 'asc');
        if ($limit && $limit > 0) {
            $query->take($limit);
        }
        $transactions = $query->get();

        if ($transactions->isEmpty()) {
            $this->sendMessage($botToken, $chatId, "⚠️ Member <b>{$user->full_name}</b> (@{$user->username}) has no transaction history to export.");
            return;
        }

        $currencySymbol = setting('currency_symbol', '$');
        $incomeTypes = ['deposit', 'manual_deposit', 'receive_money', 'refund', 'referral', 'signup_bonus', 'portfolio_bonus', 'reward_redeem', 'dps_maturity'];

        // Build CSV in memory
        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, ['Date', 'Description', 'Amount', 'Type', 'Account']);

        $totalIncome = 0;
        $totalOutcome = 0;

        foreach ($transactions as $txn) {
            $typeVal = is_object($txn->type) ? $txn->type->value : (string)$txn->type;
            $direction = in_array($typeVal, $incomeTypes) ? 'income' : 'outcome';
            
            $walletType = $txn->wallet_type ?? 'default';
            $walletName = match ($walletType) {
                'default' => 'Checking',
                'primary_savings' => 'Primary Savings',
                'ira' => 'IRA',
                'heloc' => 'HELOC',
                'cc' => 'Credit Card',
                'loan' => 'Loan Account',
                default => ucfirst((string)$walletType)
            };

            $amt = (float)$txn->amount;
            if ($direction === 'income') {
                $totalIncome += $amt;
            } else {
                $totalOutcome += $amt;
            }

            fputcsv($fp, [
                $txn->created_at ? $txn->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                $txn->description ?? 'Transaction',
                number_format($amt, 2, '.', ''),
                $direction,
                $walletName
            ]);
        }

        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);

        $dateStamp = now()->format('Ymd_His');
        $filename = "ledger_{$user->username}_{$dateStamp}.csv";
        $caption = "📄 <b>Transaction Ledger Export</b>\n\n";
        $caption .= "👤 <b>Member:</b> {$user->full_name} (@{$user->username})\n";
        $caption .= "💳 <b>Account #:</b> <code>{$user->account_number}</code>\n";
        $caption .= "📊 <b>Total Rows:</b> " . $transactions->count() . "\n";
        $caption .= "💰 <b>Inflow:</b> +{$currencySymbol}" . number_format($totalIncome, 2) . "\n";
        $caption .= "💸 <b>Outflow:</b> -{$currencySymbol}" . number_format($totalOutcome, 2) . "\n\n";
        $caption .= "💡 <i>Ready for 1-Click CSV Importer in Admin Portal.</i>";

        try {
            $apiUrl = "https://api.telegram.org/bot{$botToken}/sendDocument";
            $response = Http::timeout(20)
                ->attach('document', $csvContent, $filename)
                ->post($apiUrl, [
                    'chat_id' => $chatId,
                    'caption' => $caption,
                    'parse_mode' => 'HTML',
                ]);

            if (!$response->successful()) {
                Log::error('Telegram sendDocument failed: ' . $response->body());
                $this->sendMessage($botToken, $chatId, "⚠️ Failed to send ledger file: " . ($response->json('description') ?? 'Unknown Telegram error'));
            }
        } catch (\Throwable $e) {
            Log::error('Telegram export document exception: ' . $e->getMessage());
            $this->sendMessage($botToken, $chatId, "❌ Error generating export: " . $e->getMessage());
        }
    }

    /**
     * Handle /dividend command in Telegram.
     */
    protected function handleDividendCommand(string $botToken, string $chatId, array $args): void
    {
        $divService = new \App\Services\DividendService();
        $subCommand = strtolower($args[0] ?? '');
        $currencySymbol = setting('currency_symbol', '$');
        $chatInst = $this->resolveChatInstitution($chatId);
        $instId = $chatInst ? $chatInst->id : null;

        // 1. Preview / Status across all eligible members (Default)
        if (empty($subCommand) || $subCommand === 'preview' || $subCommand === 'pool') {
            $preview = $divService->previewBatch($instId);
            $global = $preview['global_settings'];
            $instLabel = $chatInst ? " for {$chatInst->short_name}" : " (Global)";

            $msg = "📈 <b>Monthly Dividend & APY Pool Status{$instLabel}</b>\n\n";
            $msg .= "⚙️ <b>Default APY:</b> " . number_format($global['default_apy'], 2) . "%\n";
            $msg .= "💰 <b>Min Balance:</b> {$currencySymbol}" . number_format($global['min_balance'], 2) . "\n";
            $msg .= "👥 <b>Eligible Members:</b> {$preview['eligible_count']} (out of {$preview['total_users_checked']} enabled)\n";
            $msg .= "💵 <b>Next Payout Pool:</b> <b>{$currencySymbol}" . number_format($preview['total_payout_amount'], 2) . "</b>\n";
            $msg .= "───────────────────\n";
            $msg .= "💡 <i>Dividends are OFF by default. Use /dividend &lt;username&gt; or tap [ 📈 Dividends: Turn ON ] on any /user card.</i>";

            $keyboard = [];
            if ($preview['eligible_count'] > 0) {
                $keyboard = [
                    [
                        ['text' => "🚀 Execute Payout Now ({$instLabel})", 'callback_data' => 'div_exec_all']
                    ]
                ];
            }

            $this->sendMessage($botToken, $chatId, $msg, $keyboard);
            return;
        }

        // 2. Direct Batch Execution: /dividend run
        if ($subCommand === 'run' || $subCommand === 'commit') {
            $this->sendMessage($botToken, $chatId, "⏳ Processing monthly dividend distribution batch...");
            $res = $divService->processBatch(null, 'Telegram /dividend run', $instId);
            if ($res['success']) {
                $this->sendMessage($botToken, $chatId, "🎉 <b>DIVIDENDS PROCESSED!</b>\n" . $res['message']);
            } else {
                $this->sendMessage($botToken, $chatId, "⚠️ " . $res['message']);
            }
            return;
        }

        // 3. Toggle Eligibility: /dividend toggle <user>
        if ($subCommand === 'toggle') {
            $target = $args[1] ?? null;
            if (!$target) {
                $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/dividend toggle &lt;user_id_or_username&gt;</code>");
                return;
            }
            $user = $this->findUserByIdentifier($target);
            if (!$user) {
                $this->sendMessage($botToken, $chatId, "❌ Member <code>{$target}</code> was not found.");
                return;
            }
            $newStatus = $divService->toggleUserEligibility($user);
            $word = $newStatus ? 'ENABLED (ON)' : 'DISABLED (OFF)';
            $this->sendMessage($botToken, $chatId, "📈 Dividends for <b>{$user->full_name}</b> (@{$user->username}) are now <b>{$word}</b>.");
            return;
        }

        // 4. Set Custom APY: /dividend setapy <user> <rate>
        if ($subCommand === 'setapy' || $subCommand === 'apy') {
            $target = $args[1] ?? null;
            $rate = isset($args[2]) && is_numeric($args[2]) ? (float)$args[2] : null;
            if (!$target || $rate === null) {
                $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/dividend setapy &lt;user&gt; &lt;rate_percentage&gt;</code>\n<i>Example:</i> <code>/dividend setapy 2 4.25</code>");
                return;
            }
            $user = $this->findUserByIdentifier($target);
            if (!$user) {
                $this->sendMessage($botToken, $chatId, "❌ Member <code>{$target}</code> was not found.");
                return;
            }
            $divService->setUserApy($user, $rate);
            $this->sendMessage($botToken, $chatId, "✅ Custom APY for <b>{$user->full_name}</b> (@{$user->username}) updated to <b>{$rate}%</b>.");
            return;
        }

        // 5. Individual Member Dividend Lookup / Credit: /dividend <user>
        $user = $this->findUserByIdentifier($subCommand);
        if ($user) {
            $calc = $divService->calculateUserDividend($user);
            if ($calc['eligible']) {
                $msg = "📈 <b>Member Dividend Calculation</b>\n\n";
                $msg .= "👤 <b>Member:</b> {$user->full_name} (@{$user->username})\n";
                $msg .= "🏦 <b>Account:</b> {$calc['wallet_name']}\n";
                $msg .= "💰 <b>Qualifying Balance:</b> {$currencySymbol}" . number_format($calc['balance'], 2) . "\n";
                $msg .= "📊 <b>APY Rate:</b> " . number_format($calc['apy'], 2) . "%\n";
                $msg .= "💵 <b>Calculated Dividend:</b> <b>+{$currencySymbol}" . number_format($calc['amount'], 2) . "</b>\n";
                $msg .= "📝 <b>Ledger Note:</b> <i>{$calc['description']}</i>";

                $keyboard = [
                    [
                        ['text' => '💵 Credit Dividend Now', 'callback_data' => 'usr_div_pay:' . $user->id]
                    ]
                ];
                $this->sendMessage($botToken, $chatId, $msg, $keyboard);
            } else {
                $msg = "ℹ️ <b>Dividend Status for {$user->full_name}</b> (@{$user->username})\n\n";
                $msg .= "⚠️ <b>Not currently eligible:</b> {$calc['reason']}\n\n";
                $msg .= "💡 <i>Tap below to turn dividends ON for this member.</i>";
                $keyboard = [
                    [
                        ['text' => '📈 Turn Dividends ON', 'callback_data' => 'usr_div_tog:' . $user->id]
                    ]
                ];
                $this->sendMessage($botToken, $chatId, $msg, $keyboard);
            }
            return;
        }

        $this->sendMessage($botToken, $chatId, "❓ Unknown parameter: <code>{$subCommand}</code>. Use <code>/dividend</code> to view pool or <code>/dividend &lt;username&gt;</code>.");
    }

    /**
     * Handle updating member profile fields directly from Telegram.
     */
    protected function handleUpdateUserCommand(string $botToken, string $chatId, array $parts): void
    {
        $target = $parts[1] ?? null;
        $field = strtolower($parts[2] ?? '');
        $value = isset($parts[3]) ? trim(implode(' ', array_slice($parts, 3))) : '';

        if (!$target || empty($field) || $value === '') {
            $guide = "⚠️ <b>Usage:</b> <code>/updateuser &lt;id_or_username&gt; &lt;field&gt; &lt;value&gt;</code>\n\n";
            $guide .= "<b>Supported Fields & Examples:</b>\n";
            $guide .= "• <code>name</code> — <code>/updateuser 25 name John Doe</code>\n";
            $guide .= "• <code>pin</code> — <code>/updateuser 25 pin 1234</code>\n";
            $guide .= "• <code>password</code> — <code>/updateuser 25 password MySecret123!</code>\n";
            $guide .= "• <code>dob</code> — <code>/updateuser 25 dob 1992-05-20</code>\n";
            $guide .= "• <code>address</code> — <code>/updateuser 25 address 742 Evergreen Terr</code>\n";
            $guide .= "• <code>city</code> — <code>/updateuser 25 city Tampa</code>\n";
            $guide .= "• <code>zip</code> — <code>/updateuser 25 zip 33701</code>\n";
            $guide .= "• <code>country</code> — <code>/updateuser 25 country USA</code>\n";
            $guide .= "• <code>phone</code> — <code>/updateuser 25 phone +17275550199</code>\n";
            $guide .= "• <code>email</code> — <code>/updateuser 25 email member@example.com</code>\n";
            $this->sendMessage($botToken, $chatId, $guide);
            return;
        }

        $user = $this->findUserByIdentifier($target);
        if (!$user) {
            $this->sendMessage($botToken, $chatId, "❌ Member <code>{$target}</code> was not found. Please verify user ID or username.");
            return;
        }

        $label = '';
        $displayVal = '';

        switch ($field) {
            case 'name':
            case 'fullname':
                $nameParts = preg_split('/\s+/', $value);
                if (count($nameParts) === 1) {
                    $user->first_name = $nameParts[0];
                } else {
                    $user->last_name = array_pop($nameParts);
                    $user->first_name = implode(' ', $nameParts);
                }
                $user->save();
                $label = 'Full Name';
                $displayVal = htmlspecialchars($user->first_name . ' ' . $user->last_name);
                break;

            case 'firstname':
            case 'first_name':
                $user->first_name = $value;
                $user->save();
                $label = 'First Name';
                $displayVal = htmlspecialchars($user->first_name);
                break;

            case 'lastname':
            case 'last_name':
                $user->last_name = $value;
                $user->save();
                $label = 'Last Name';
                $displayVal = htmlspecialchars($user->last_name);
                break;

            case 'dob':
            case 'date_of_birth':
            case 'birthdate':
                try {
                    $parsedDate = Carbon::parse($value)->format('Y-m-d');
                    $user->date_of_birth = $parsedDate;
                    $user->save();
                    $label = 'Date of Birth';
                    $displayVal = $parsedDate;
                } catch (\Throwable $e) {
                    $this->sendMessage($botToken, $chatId, "⚠️ Invalid date format <code>{$value}</code>. Please use <code>YYYY-MM-DD</code> (e.g. <code>1990-05-15</code>).");
                    return;
                }
                break;

            case 'address':
                $user->address = $value;
                $user->save();
                $label = 'Address';
                $displayVal = htmlspecialchars($value);
                break;

            case 'city':
                $user->city = $value;
                $user->save();
                $label = 'City';
                $displayVal = htmlspecialchars($value);
                break;

            case 'country':
                $user->country = $value;
                $user->save();
                $label = 'Country';
                $displayVal = htmlspecialchars($value);
                break;

            case 'zip':
            case 'zipcode':
            case 'zip_code':
                $user->zip_code = $value;
                $user->save();
                $label = 'Zip Code';
                $displayVal = htmlspecialchars($value);
                break;

            case 'pin':
            case 'transaction_pin':
                if (!preg_match('/^[0-9]{4,8}$/', $value)) {
                    $this->sendMessage($botToken, $chatId, "⚠️ Transaction PIN must be between 4 and 8 digits.");
                    return;
                }
                $user->transaction_pin = $value;
                $user->save();
                $label = 'Transaction PIN';
                $displayVal = "<code>{$value}</code> (Active)";
                break;

            case 'password':
            case 'pass':
                if (strlen($value) < 6) {
                    $this->sendMessage($botToken, $chatId, "⚠️ Password must be at least 6 characters long.");
                    return;
                }
                $user->password = Hash::make($value);
                $user->save();
                $label = 'Password';
                $displayVal = "•••••••• (Hashed & Secured)";
                break;

            case 'phone':
                $user->phone = $value;
                $user->save();
                $label = 'Phone Number';
                $displayVal = htmlspecialchars($value);
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->sendMessage($botToken, $chatId, "⚠️ <code>{$value}</code> is not a valid email address.");
                    return;
                }
                $exists = User::where('email', $value)->where('id', '!=', $user->id)->exists();
                if ($exists) {
                    $this->sendMessage($botToken, $chatId, "⚠️ Email <code>{$value}</code> is already in use by another member.");
                    return;
                }
                $user->email = $value;
                $user->save();
                $label = 'Email Address';
                $displayVal = htmlspecialchars($value);
                break;

            default:
                $this->sendMessage($botToken, $chatId, "⚠️ Unknown field <code>{$field}</code>. Supported fields: <code>name</code>, <code>pin</code>, <code>password</code>, <code>dob</code>, <code>address</code>, <code>city</code>, <code>zip</code>, <code>country</code>, <code>phone</code>, <code>email</code>.");
                return;
        }

        Log::info("Telegram Admin updated member #{$user->id} ({$user->username}): {$field} to '{$displayVal}'");
        $card = $this->renderUserCard($user, "✏️ <b>MEMBER PROFILE UPDATED</b>\nField <b>{$label}</b> changed to <b>{$displayVal}</b>.");
        $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
    }

    /**
     * Handle Admin and Staff monitoring and manageability commands.
     */
    protected function handleStaffCommand(string $botToken, string $chatId, array $parts): void
    {
        $sub = strtolower($parts[1] ?? 'list');

        // Emergency Officer Lockdown: /staff lockdown or /staff lockall
        if ($sub === 'lockdown' || $sub === 'lockall') {
            \App\Models\Setting::put('officer_portal_lockdown', '1', 'security');
            $officerCount = Admin::where('status', 1)->where('is_admin', 0)->count();
            $msg = "🚨 <b>OFFICER PORTAL LOCKDOWN ACTIVATED</b>\n\n";
            $msg .= "• All Account Officer active web sessions are <b>TERMINATED</b>.\n";
            $msg .= "• Officer logins are now <b>BLOCKED</b>.\n";
            $msg .= "• Affected Personnel: <b>{$officerCount} Officers</b>.\n";
            $msg .= "• Super-Admin access remains <b>100% active and unimpacted</b>.\n\n";
            $msg .= "💡 To lift lockdown and restore officer access: <code>/staff unlockall</code>";
            $this->sendMessage($botToken, $chatId, $msg);
            return;
        }

        // Lift Officer Lockdown: /staff unlockall or /staff unlock
        if ($sub === 'unlockall' || $sub === 'liftlockdown' || $sub === 'unlock' || $sub === 'open') {
            \App\Models\Setting::put('officer_portal_lockdown', '0', 'security');
            $msg = "✅ <b>OFFICER PORTAL LOCKDOWN LIFTED</b>\n\n";
            $msg .= "• Account Officers can now log in and manage their portfolios normally.\n";
            $msg .= "• Officer portal access is fully restored.";
            $this->sendMessage($botToken, $chatId, $msg);
            return;
        }

        // 1. Full Staff Listing: /staff or /staff list
        if (empty($parts[1]) || $sub === 'list' || $sub === 'all') {
            $staffMembers = Admin::with('roles')->get();
            if ($staffMembers->isEmpty()) {
                $this->sendMessage($botToken, $chatId, "ℹ️ No staff accounts found in database.");
                return;
            }

            $msg = "👔 <b>FrontField Credit Union — Staff Directory</b>\n";
            $msg .= "Total Personnel: <b>" . $staffMembers->count() . "</b>\n\n";

            $buttons = [];
            foreach ($staffMembers as $idx => $stf) {
                $num = $idx + 1;
                $role = $stf->roles->pluck('name')->first() ?? ($stf->is_admin ? 'Admin' : 'Staff');
                $status = $stf->status ? '🟢 ACTIVE' : '🔴 DISABLED';
                $pinStatus = $stf->passcode ? '🔑 Configured' : '⚪ Not Set';
                $assignedCount = $stf->managedUsers()->count();

                $msg .= "<b>{$num}. " . htmlspecialchars($stf->name) . "</b> ({$role})\n";
                $msg .= "   🆔 ID: <code>{$stf->id}</code> | 📊 {$status}\n";
                $msg .= "   📧 {$stf->email}\n";
                if (!empty($stf->phone)) {
                    $msg .= "   📱 {$stf->phone}\n";
                }
                $msg .= "   👥 Managed Members: <b>{$assignedCount}</b> | {$pinStatus}\n";
                $msg .= "   👉 Manage: <code>/staff {$stf->id}</code>\n\n";

                if ($staffMembers->count() <= 6) {
                    $buttons[] = [['text' => "👔 Manage " . $stf->name, 'callback_data' => 'stf_info:' . $stf->id]];
                }
            }

            $msg .= "💡 <i>Commands:</i>\n";
            $msg .= "• <code>/staff &lt;id&gt;</code> — View card & controls\n";
            $msg .= "• <code>/staff toggle &lt;id&gt;</code> — Enable / disable account\n";
            $msg .= "• <code>/staff resetpin &lt;id&gt; &lt;4-digit-pin&gt;</code> — Reset passcode\n";
            $msg .= "• <code>/staff resetpass &lt;id&gt; &lt;new_pass&gt;</code> — Reset password\n";

            $this->sendMessage($botToken, $chatId, $msg, $buttons);
            return;
        }

        // 1.5 Subcommand: link <id> <custom_slug>
        if ($sub === 'link') {
            $target = $parts[2] ?? null;
            $customCode = $parts[3] ?? null;
            if (!$target) {
                $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/staff link &lt;id_or_email&gt; &lt;custom_slug&gt;</code>\n<i>Example:</i> <code>/staff link 14 sarah</code>");
                return;
            }
            $staff = $this->findStaffByIdentifier($target);
            if (!$staff) {
                $this->sendMessage($botToken, $chatId, "❌ Staff <code>{$target}</code> not found.");
                return;
            }
            if ($customCode === 'remove' || $customCode === 'clear' || $customCode === 'none' || $customCode === '0') {
                $staff->onboarding_code = null;
                $staff->save();
                $this->sendMessage($botToken, $chatId, "✅ Vanity link removed for {$staff->name}. Default numeric link active: <code>" . url('/' . $staff->id) . "</code>");
                return;
            }
            $slug = \Illuminate\Support\Str::slug($customCode);
            $exists = Admin::where('onboarding_code', $slug)->where('id', '!=', $staff->id)->exists();
            if ($exists) {
                $this->sendMessage($botToken, $chatId, "⚠️ Vanity slug <code>{$slug}</code> is already in use by another officer.");
                return;
            }
            $staff->onboarding_code = $slug;
            $staff->save();
            $newUrl = url('/join/' . $slug);
            $card = $this->renderStaffCard($staff, "🔗 <b>ONBOARDING LINK CONFIGURED</b>\nNew vanity link active: <code>{$newUrl}</code>");
            $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
            return;
        }

        // 2. Subcommand: toggle
        if ($sub === 'toggle') {
            $target = $parts[2] ?? null;
            if (!$target) {
                $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/staff toggle &lt;id_or_email&gt;</code>");
                return;
            }
            $staff = $this->findStaffByIdentifier($target);
            if (!$staff) {
                $this->sendMessage($botToken, $chatId, "❌ Staff <code>{$target}</code> not found.");
                return;
            }
            $newStatus = $staff->status ? 0 : 1;
            $staff->status = $newStatus;
            $staff->save();
            $statusWord = $newStatus ? 'ACTIVATED (🟢 Active)' : 'DISABLED (🔴 Disabled)';
            $card = $this->renderStaffCard($staff, "⚙️ <b>STATUS UPDATED</b>\nStaff account is now <b>{$statusWord}</b>.");
            $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
            return;
        }

        // 3. Subcommand: resetpin
        if ($sub === 'resetpin' || $sub === 'pin') {
            $target = $parts[2] ?? null;
            $pin = $parts[3] ?? null;
            if (!$target || !$pin) {
                $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/staff resetpin &lt;id_or_email&gt; &lt;4-digit-pin&gt;</code>\n<i>Example:</i> <code>/staff resetpin 2 1234</code>");
                return;
            }
            if (!preg_match('/^[0-9]{4,8}$/', $pin)) {
                $this->sendMessage($botToken, $chatId, "⚠️ Staff Passcode/PIN must be between 4 and 8 digits.");
                return;
            }
            $staff = $this->findStaffByIdentifier($target);
            if (!$staff) {
                $this->sendMessage($botToken, $chatId, "❌ Staff <code>{$target}</code> not found.");
                return;
            }
            $staff->passcode = Hash::make($pin);
            $staff->passcode_status = 1;
            $staff->save();
            $card = $this->renderStaffCard($staff, "🔑 <b>PASSCODE / PIN UPDATED</b>\nNew security passcode configured and enabled for <b>" . htmlspecialchars($staff->name) . "</b>.");
            $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
            return;
        }

        // 4. Subcommand: resetpass
        if ($sub === 'resetpass' || $sub === 'password') {
            $target = $parts[2] ?? null;
            $password = $parts[3] ?? null;
            if (!$target || !$password) {
                $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/staff resetpass &lt;id_or_email&gt; &lt;new_password&gt;</code>");
                return;
            }
            if (strlen($password) < 6) {
                $this->sendMessage($botToken, $chatId, "⚠️ Password must be at least 6 characters long.");
                return;
            }
            $staff = $this->findStaffByIdentifier($target);
            if (!$staff) {
                $this->sendMessage($botToken, $chatId, "❌ Staff <code>{$target}</code> not found.");
                return;
            }
            $staff->password = Hash::make($password);
            $staff->save();
            $card = $this->renderStaffCard($staff, "🔐 <b>STAFF PASSWORD RESET</b>\nPassword successfully updated for <b>" . htmlspecialchars($staff->name) . "</b>.");
            $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
            return;
        }

        // 5. Direct ID or Email Lookup: /staff <id_or_email>
        $staff = $this->findStaffByIdentifier($parts[1]);
        if ($staff) {
            $card = $this->renderStaffCard($staff);
            $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
            return;
        }

        $this->sendMessage($botToken, $chatId, "❌ Staff member <code>{$parts[1]}</code> was not found. Use <code>/staff</code> to view directory.");
    }

    /**
     * Render rich Staff Profile card with interactive controls.
     */
    protected function renderStaffCard(Admin $staff, ?string $extraNotice = null): array
    {
        Admin::ensureOnboardingSchema();
        $role = $staff->roles->pluck('name')->first() ?? ($staff->is_admin ? 'Super-Admin' : 'Account Officer');
        $statusInt = (int)$staff->status;
        $statusBadge = $statusInt === 1 ? '🟢 ACTIVE' : '🔴 DISABLED';
        $pinBadge = $staff->passcode ? '🔑 Configured' : '⚪ Not Set';
        $assignedCount = $staff->managedUsers()->count();

        $text = "👔 <b>Staff & Personnel Profile</b>\n\n";
        if ($extraNotice) {
            $text .= "{$extraNotice}\n\n";
        }
        $text .= "🆔 <b>Staff ID:</b> <code>{$staff->id}</code>\n";
        $text .= "👤 <b>Name:</b> " . htmlspecialchars($staff->name) . "\n";
        $text .= "🏷️ <b>Role:</b> <b>{$role}</b>\n";
        $text .= "📧 <b>Email:</b> {$staff->email}\n";
        if (!empty($staff->phone)) {
            $text .= "📱 <b>Phone:</b> {$staff->phone}\n";
        }
        $text .= "📊 <b>Account Status:</b> <b>{$statusBadge}</b>\n";
        $text .= "🔑 <b>Security PIN:</b> {$pinBadge}\n";
        $text .= "👥 <b>Assigned Members:</b> <b>{$assignedCount}</b>\n";
        $text .= "🔗 <b>Onboard Link:</b> <code>{$staff->onboarding_url}</code>\n";
        $signups = (int)($staff->onboarding_signups_count ?? 0);
        $views = (int)($staff->onboarding_views_count ?? 0);
        $convRate = $views > 0 ? round(($signups / $views) * 100, 1) : 0;
        $text .= "📊 <b>Onboarding Stats:</b> <b>{$signups} signups</b> ({$views} clicks — {$convRate}% conv)\n";
        $text .= "🕒 <b>Created:</b> " . ($staff->created_at ? $staff->created_at->format('Y-m-d') : 'N/A') . "\n";
        $text .= "───────────────────\n";
        $text .= "💡 <i>Quick Commands:</i>\n";
        $text .= "• Toggle Status: <code>/staff toggle {$staff->id}</code>\n";
        $text .= "• Reset PIN: <code>/staff resetpin {$staff->id} 1234</code>\n";
        $text .= "• Reset Pass: <code>/staff resetpass {$staff->id} SecretPass123!</code>";

        $keyboard = [];
        if ($statusInt === 1) {
            $keyboard[] = [
                ['text' => '🔴 Disable Staff', 'callback_data' => 'stf_stat:' . $staff->id . ':0'],
                ['text' => '🔑 Reset PIN', 'callback_data' => 'stf_pin_prompt:' . $staff->id],
            ];
        } else {
            $keyboard[] = [
                ['text' => '🟢 Activate Staff', 'callback_data' => 'stf_stat:' . $staff->id . ':1'],
                ['text' => '🔑 Reset PIN', 'callback_data' => 'stf_pin_prompt:' . $staff->id],
            ];
        }

        $keyboard[] = [
            ['text' => '👥 Staff Directory', 'callback_data' => 'stf_list'],
            ['text' => '🔄 Refresh', 'callback_data' => 'stf_info:' . $staff->id],
        ];

        return [
            'text' => $text,
            'keyboard' => $keyboard,
        ];
    }

    /**
     * Find admin / staff by ID, email, name, or phone.
     */
    protected function findStaffByIdentifier(string $target): ?Admin
    {
        $clean = trim($target);
        if (is_numeric($clean)) {
            return Admin::find($clean);
        }
        if (filter_var($clean, FILTER_VALIDATE_EMAIL)) {
            return Admin::where('email', $clean)->first();
        }
        return Admin::where('email', $clean)
            ->orWhere('name', 'like', "%{$clean}%")
            ->orWhere('phone', $clean)
            ->first();
    }

    /**
     * Handle /health or /system command: report live server vitals.
     */
    protected function handleHealthCommand(string $botToken, string $chatId): void
    {
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();
        $serverTime = now()->format('Y-m-d H:i:s T');
        $memUsage = round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';
        $memPeak = round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB';

        // DB Latency check
        $start = microtime(true);
        try {
            \DB::select('SELECT 1');
            $dbLatency = round((microtime(true) - $start) * 1000, 2) . ' ms';
            $dbStatus = '🟢 CONNECTED';
        } catch (\Throwable $e) {
            $dbStatus = '🔴 ERROR: ' . $e->getMessage();
            $dbLatency = 'N/A';
        }

        // Disk space
        $diskFree = @disk_free_space('.');
        $diskTotal = @disk_total_space('.');
        $diskInfo = ($diskFree && $diskTotal) 
            ? round($diskFree / (1024 * 1024 * 1024), 2) . ' GB free / ' . round($diskTotal / (1024 * 1024 * 1024), 2) . ' GB'
            : 'Available';

        // Business vitals
        $totalUsers = User::count();
        $activeUsers = User::where('status', 1)->count();
        $pendingTxns = Transaction::where('status', TxnStatus::Pending)->count();
        $dividendEngine = Setting::get('savings_dividend_apy', 'dividend') ? '🟢 ACTIVE' : '⚪ OFF';

        $msg = "🖥️ <b>FrontField Credit Union — Server & System Vitals</b>\n\n";
        $msg .= "⚙️ <b>PHP Version:</b> <code>{$phpVersion}</code>\n";
        $msg .= "🚀 <b>Laravel:</b> <code>v{$laravelVersion}</code>\n";
        $msg .= "🕒 <b>Server Time:</b> <code>{$serverTime}</code>\n";
        $msg .= "💾 <b>RAM Usage:</b> <code>{$memUsage}</code> (Peak: <code>{$memPeak}</code>)\n";
        $msg .= "💽 <b>Disk Storage:</b> <code>{$diskInfo}</code>\n";
        $msg .= "🗄️ <b>Database Status:</b> <b>{$dbStatus}</b> (Ping: <code>{$dbLatency}</code>)\n";
        $msg .= "───────────────────\n";
        $msg .= "👥 <b>Active Members:</b> <b>{$activeUsers}</b> / {$totalUsers} total\n";
        $msg .= "📋 <b>Pending Transactions:</b> <b>{$pendingTxns}</b> waiting\n";
        $msg .= "📈 <b>Monthly Dividend Engine:</b> {$dividendEngine}\n\n";
        $msg .= "🕒 <i>Live System Snapshot</i>";

        $keyboard = [
            [
                ['text' => '📋 View Pending Transactions', 'callback_data' => 'pend_list'],
                ['text' => '👥 Staff Directory', 'callback_data' => 'stf_list'],
            ],
            [
                ['text' => '💾 Trigger Instant Backup', 'callback_data' => 'sys_backup'],
            ]
        ];

        $this->sendMessage($botToken, $chatId, $msg, $keyboard);
    }

    /**
     * Handle member search across name, username, acct#, email, phone.
     */
    protected function handleSearchCommand(string $botToken, string $chatId, array $parts): void
    {
        $query = isset($parts[1]) ? trim(implode(' ', array_slice($parts, 1))) : '';
        if (empty($query)) {
            $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/search &lt;query&gt;</code>\n<i>Examples:</i>\n• <code>/search 0212797369</code>\n• <code>/search Kalyn</code>\n• <code>/search user@example.com</code>");
            return;
        }

        $clean = ltrim($query, '@');
        $results = User::where('id', $clean)
            ->orWhere('username', 'like', "%{$clean}%")
            ->orWhere('account_number', 'like', "%{$clean}%")
            ->orWhere('email', 'like', "%{$clean}%")
            ->orWhere('phone', 'like', "%{$clean}%")
            ->orWhere('first_name', 'like', "%{$clean}%")
            ->orWhere('last_name', 'like', "%{$clean}%")
            ->limit(6)
            ->get();

        if ($results->isEmpty()) {
            $this->sendMessage($botToken, $chatId, "❌ No members found matching query: <code>{$query}</code>");
            return;
        }

        if ($results->count() === 1) {
            $card = $this->renderUserCard($results->first(), "🔍 <b>Search Result for:</b> <code>{$query}</code>");
            $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
            return;
        }

        $msg = "🔍 <b>Search Results for:</b> <code>{$query}</code> (" . $results->count() . " matches)\n\n";
        $keyboard = [];
        foreach ($results as $idx => $m) {
            $num = $idx + 1;
            $statusBadge = ((int)$m->status === 1) ? '🟢' : '🔴';
            $msg .= "<b>{$num}. " . htmlspecialchars($m->full_name) . "</b> (@{$m->username})\n";
            $msg .= "   💳 Acct: <code>{$m->account_number}</code> | {$statusBadge} Status\n";
            $msg .= "   💰 Checking: " . setting('currency_symbol', '$') . number_format($m->balance, 2) . "\n";
            $msg .= "   👉 View: <code>/user {$m->id}</code>\n\n";

            $keyboard[] = [
                ['text' => "👤 View {$m->first_name} (@{$m->username})", 'callback_data' => 'usr_info:' . $m->id]
            ];
        }

        $this->sendMessage($botToken, $chatId, $msg, $keyboard);
    }

    /**
     * Handle balance adjustments with synchronized ledger records.
     */
    protected function handleBalanceCommand(string $botToken, string $chatId, array $parts): void
    {
        if (count($parts) < 4) {
            $msg = "⚠️ <b>Usage:</b> <code>/balance &lt;user&gt; &lt;wallet&gt; &lt;+amount|-amount&gt; [reason]</code>\n\n";
            $msg .= "<b>Supported Wallets:</b>\n";
            $msg .= "• <code>checking</code> (Primary Checking)\n";
            $msg .= "• <code>savings</code> (Primary Savings)\n";
            $msg .= "• <code>ira</code> (IRA Account)\n";
            $msg .= "• <code>heloc</code> (HELOC Line)\n";
            $msg .= "• <code>cc</code> (Credit Card)\n";
            $msg .= "• <code>loan</code> (Loan Account)\n\n";
            $msg .= "<b>Examples:</b>\n";
            $msg .= "• <code>/balance 25 checking +1500 Direct Payroll Deposit</code>\n";
            $msg .= "• <code>/balance @kalyn savings -50 Fee Adjustment</code>";
            $this->sendMessage($botToken, $chatId, $msg);
            return;
        }

        $target = $parts[1];
        $walletArg = strtolower($parts[2]);
        $amountArg = $parts[3];
        $reason = isset($parts[4]) ? trim(implode(' ', array_slice($parts, 4))) : 'Manual Balance Adjustment via Telegram';

        $user = $this->findUserByIdentifier($target);
        if (!$user) {
            $this->sendMessage($botToken, $chatId, "❌ Member <code>{$target}</code> was not found.");
            return;
        }

        $isAdd = str_starts_with($amountArg, '+') || (!str_starts_with($amountArg, '-') && (float)$amountArg > 0);
        $cleanedStr = ltrim($amountArg, '+');
        $amount = round(abs((float)str_replace(',', '', $cleanedStr)), 2);

        if ($amount <= 0) {
            $this->sendMessage($botToken, $chatId, "⚠️ Invalid adjustment amount: <code>{$amountArg}</code>.");
            return;
        }

        $walletMap = [
            'checking' => 'default', 'check' => 'default', 'default' => 'default',
            'savings' => 'primary_savings', 'sav' => 'primary_savings', 'primary' => 'primary_savings',
            'ira' => 'ira',
            'heloc' => 'heloc',
            'cc' => 'cc', 'credit' => 'cc', 'card' => 'cc',
            'loan' => 'loan'
        ];

        if (!array_key_exists($walletArg, $walletMap)) {
            $this->sendMessage($botToken, $chatId, "⚠️ Unknown wallet <code>{$walletArg}</code>. Use <code>checking</code>, <code>savings</code>, <code>ira</code>, <code>heloc</code>, <code>cc</code>, or <code>loan</code>.");
            return;
        }

        $walletType = $walletMap[$walletArg];
        $delta = $isAdd ? $amount : -$amount;
        $walletLabel = ucfirst(str_replace('_', ' ', $walletType));
        if ($walletType === 'default') $walletLabel = 'Checking';
        if ($walletType === 'primary_savings') $walletLabel = 'Primary Savings';

        // Atomic update in DB transaction
        \DB::transaction(function() use ($user, $walletType, $walletLabel, $amount, $delta, $isAdd, $reason) {
            if ($walletType === 'default') {
                $user->balance = round((float)$user->balance + $delta, 2);
            } elseif ($walletType === 'primary_savings') {
                $user->savings_balance = round((float)$user->savings_balance + $delta, 2);
            } elseif ($walletType === 'ira') {
                $user->ira_balance = round((float)$user->ira_balance + $delta, 2);
            } elseif ($walletType === 'heloc') {
                $user->heloc_balance = round((float)$user->heloc_balance + $delta, 2);
            } elseif ($walletType === 'cc') {
                $user->cc_balance = round((float)$user->cc_balance + $delta, 2);
            } elseif ($walletType === 'loan') {
                $user->loan_balance = round((float)$user->loan_balance + $delta, 2);
            }
            $user->save();

            $txn = Txn::new(
                amount: $amount,
                charge: 0,
                final_amount: $amount,
                method: 'system',
                description: $reason,
                type: $isAdd ? TxnType::Deposit : TxnType::Subtract,
                status: TxnStatus::Success,
                payCurrency: $walletLabel,
                userID: $user->id,
                relatedUserID: 1,
                relatedModel: 'Admin',
                walletType: $walletType
            );
            $txn->created_at = now();
            $txn->updated_at = now();
            $txn->save();
        });

        $user->refresh();
        $cur = setting('currency_symbol', '$');
        $sign = $isAdd ? '+' : '-';
        $notice = "💰 <b>BALANCE ADJUSTED & LEDGER RECORDED</b>\n";
        $notice .= "Account: <b>{$walletLabel}</b>\n";
        $notice .= "Adjustment: <b>{$sign}{$cur}" . number_format($amount, 2) . "</b>\n";
        $notice .= "Reason: <i>{$reason}</i>";

        $card = $this->renderUserCard($user, $notice);
        $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
    }

    /**
     * Handle freeze and unfreeze of member transfers.
     */
    protected function handleFreezeCommand(string $botToken, string $chatId, array $parts, bool $freeze): void
    {
        $target = $parts[1] ?? null;
        if (!$target) {
            $cmd = $freeze ? '/freeze' : '/unfreeze';
            $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>{$cmd} &lt;user_id_or_username&gt;</code>");
            return;
        }

        $user = $this->findUserByIdentifier($target);
        if (!$user) {
            $this->sendMessage($botToken, $chatId, "❌ Member <code>{$target}</code> was not found.");
            return;
        }

        $newVal = $freeze ? 0 : 1;
        $user->transfer_status = $newVal;
        $user->wire_transfer_status = $newVal;
        $user->zelle_transfer_status = $newVal;
        $user->checking_restricted = $freeze ? 1 : 0;
        $user->save();

        $notice = $freeze 
            ? "❄️ <b>EMERGENCY FREEZE ACTIVATED</b>\nAll outgoing wires, internal transfers, and Zelle transactions for @{$user->username} are now <b>BLOCKED</b>."
            : "♨️ <b>TRANSFERS UNFROZEN & RESTORED</b>\nTransfer and wire capabilities restored for @{$user->username}.";

        $card = $this->renderUserCard($user, $notice);
        $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
    }

    /**
     * Handle /audit command: show live security activity.
     */
    protected function handleAuditCommand(string $botToken, string $chatId, array $parts): void
    {
        $limit = isset($parts[1]) && is_numeric($parts[1]) ? min((int)$parts[1], 15) : 8;

        $securityCodes = TransactionSecurityCode::with('user')->latest()->limit($limit)->get();
        $recentTxns = Transaction::with('user')->latest()->limit(5)->get();

        $msg = "🛡️ <b>Live Security & Activity Audit Log</b>\n";
        $msg .= "Recent security challenges & authentication attempts:\n\n";

        if ($securityCodes->isEmpty()) {
            $msg .= "✅ <i>No recent failed security challenge attempts or lockouts recorded.</i>\n\n";
        } else {
            foreach ($securityCodes as $idx => $sc) {
                $num = $idx + 1;
                $userLabel = $sc->user ? "@{$sc->user->username}" : "User #{$sc->user_id}";
                $tries = $sc->tries ?? 1;
                $codeType = strtoupper($sc->type ?? 'PIN/OTP');
                $time = $sc->updated_at ? $sc->updated_at->diffForHumans() : 'Recently';

                $statusIcon = ($tries >= 3) ? '🔴 LOCKED' : '⚠️ Active';
                $msg .= "<b>{$num}. [{$codeType}] {$userLabel}</b>\n";
                $msg .= "   Status: {$statusIcon} | Attempts: <b>{$tries}</b> | {$time}\n\n";
            }
        }

        $msg .= "<b>💸 Latest Ledger Activity:</b>\n";
        foreach ($recentTxns as $t) {
            $uName = $t->user ? "@{$t->user->username}" : "User #{$t->user_id}";
            $cur = setting('currency_symbol', '$');
            $amt = number_format($t->amount, 2);
            $st = is_object($t->status) ? $t->status->value : $t->status;
            $sign = ($t->type === TxnType::Deposit || $t->type === 'deposit') ? '+' : '-';
            $msg .= "• <code>{$t->tnx}</code>: {$uName} | <b>{$sign}{$cur}{$amt}</b> ({$st})\n";
        }

        $this->sendMessage($botToken, $chatId, $msg);
    }


    /**
     * Handle reassigning a member to an Account Officer from Telegram.
     */
    protected function handleReassignCommand(string $botToken, string $chatId, array $parts): void
    {
        $targetUser = $parts[1] ?? null;
        $targetOfficer = $parts[2] ?? null;

        if (!$targetUser || $targetOfficer === null) {
            $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/reassign &lt;member_id_or_user&gt; &lt;officer_id_or_0&gt;</code>\n<i>Examples:</i>\n• <code>/reassign 25 14</code> (Assign to Officer #14)\n• <code>/reassign 25 0</code> (Unassign / remove officer)");
            return;
        }

        $user = $this->findUserByIdentifier($targetUser);
        if (!$user) {
            $this->sendMessage($botToken, $chatId, "❌ Member <code>{$targetUser}</code> was not found.");
            return;
        }

        if ($targetOfficer === '0' || strtolower($targetOfficer) === 'none' || strtolower($targetOfficer) === 'null') {
            $user->staff_id = null;
            $user->save();

            $notice = "🔄 <b>OFFICER ASSIGNMENT REMOVED</b>\nMember @{$user->username} is now unassigned.";
            $card = $this->renderUserCard($user, $notice);
            $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
            return;
        }

        $officer = $this->findStaffByIdentifier($targetOfficer);
        if (!$officer) {
            $this->sendMessage($botToken, $chatId, "❌ Staff / Officer <code>{$targetOfficer}</code> was not found.");
            return;
        }

        $user->staff_id = $officer->id;
        $user->save();

        $notice = "🔄 <b>MEMBER REASSIGNED</b>\nMember @{$user->username} is now assigned to Account Officer <b>" . htmlspecialchars($officer->name) . "</b> (ID: #{$officer->id}).";
        $card = $this->renderUserCard($user, $notice);
        $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
    }

    /**
     * Handle instant credit and debit directly from Telegram.
     */
    protected function handleInstantCreditDebit(string $botToken, string $chatId, array $parts, bool $isCredit): void
    {
        $cmdName = $isCredit ? 'credit' : 'debit';
        $verb = $isCredit ? 'CREDIT' : 'DEBIT';
        $emoji = $isCredit ? '⚡' : '💸';

        $target = $parts[1] ?? null;
        $rawAmount = $parts[2] ?? null;

        if (!$target || !$rawAmount || !is_numeric($rawAmount) || (float)$rawAmount <= 0) {
            $example = $isCredit 
                ? "<code>/credit @Kalyn_Mickle 15000 checking Wire Deposit Inflow</code>"
                : "<code>/debit @Kalyn_Mickle 250 savings Account Maintenance Fee</code>";
            $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/{$cmdName} &lt;user&gt; &lt;amount&gt; [wallet] [description...]</code>\n\n<i>Example:</i>\n{$example}");
            return;
        }

        $user = $this->findUserByIdentifier($target);
        if (!$user) {
            $this->sendMessage($botToken, $chatId, "❌ Member <code>{$target}</code> not found.");
            return;
        }

        $amount = round((float)$rawAmount, 2);

        // Detect if 3rd param is a known wallet keyword
        $possibleWallet = strtolower($parts[3] ?? '');
        $knownWallets = [
            'checking' => 'default',
            'checkings' => 'default',
            'default' => 'default',
            'savings' => 'primary_savings',
            'primary_savings' => 'primary_savings',
            'share_savings' => 'primary_savings',
            'ira' => 'ira',
            'heloc' => 'heloc',
            'cc' => 'cc',
            'credit_card' => 'cc',
            'loan' => 'loan',
        ];

        if (array_key_exists($possibleWallet, $knownWallets)) {
            $walletType = $knownWallets[$possibleWallet];
            $descParts = array_slice($parts, 4);
        } else {
            // Default to checking and include 3rd param in description
            $walletType = 'default';
            $descParts = array_slice($parts, 3);
        }

        $description = !empty($descParts) 
            ? implode(' ', $descParts) 
            : ($isCredit ? 'Executive Ledger Credit Inflow' : 'Executive Ledger Debit Adjustment');

        $walletLabel = match ($walletType) {
            'default' => 'Checking',
            'primary_savings' => 'Primary Savings',
            'ira' => 'IRA',
            'heloc' => 'HELOC',
            'cc' => 'Credit Card',
            'loan' => 'Loan Account',
            default => ucfirst(str_replace('_', ' ', $walletType))
        };

        $delta = $isCredit ? $amount : -$amount;
        $tnxNumber = null;

        \DB::transaction(function() use ($user, $walletType, $walletLabel, $amount, $delta, $isCredit, $description, &$tnxNumber) {
            if ($walletType === 'default') {
                $user->balance = round((float)$user->balance + $delta, 2);
            } elseif ($walletType === 'primary_savings') {
                $user->savings_balance = round((float)$user->savings_balance + $delta, 2);
            } elseif ($walletType === 'ira') {
                $user->ira_balance = round((float)$user->ira_balance + $delta, 2);
            } elseif ($walletType === 'heloc') {
                $user->heloc_balance = round((float)$user->heloc_balance + $delta, 2);
            } elseif ($walletType === 'cc') {
                $user->cc_balance = round((float)$user->cc_balance + $delta, 2);
            } elseif ($walletType === 'loan') {
                $user->loan_balance = round((float)$user->loan_balance + $delta, 2);
            }
            $user->save();

            $txn = \Txn::new(
                amount: $amount,
                charge: 0,
                final_amount: $amount,
                method: 'system',
                description: $description,
                type: $isCredit ? \TxnType::Deposit : \TxnType::Subtract,
                status: \TxnStatus::Success,
                payCurrency: $walletLabel,
                userID: $user->id,
                relatedUserID: 1,
                relatedModel: 'Admin',
                walletType: $walletType
            );
            $txn->created_at = now();
            $txn->updated_at = now();
            $txn->save();

            $tnxNumber = $txn->tnx;
        });

        $user->refresh();
        $cur = setting('currency_symbol', '$');
        $sign = $isCredit ? '+' : '-';
        $newBal = match ($walletType) {
            'default' => $user->balance,
            'primary_savings' => $user->savings_balance,
            'ira' => $user->ira_balance,
            'heloc' => $user->heloc_balance,
            'cc' => $user->cc_balance,
            'loan' => $user->loan_balance,
            default => $user->balance
        };

        $msg = "{$emoji} <b>INSTANT LEDGER {$verb} COMMITTED</b>\n\n";
        $msg .= "👤 <b>Member:</b> " . htmlspecialchars($user->full_name) . " (@{$user->username})\n";
        $msg .= "💳 <b>Account #:</b> <code>{$user->account_number}</code>\n";
        $msg .= "💼 <b>Target Wallet:</b> <b>{$walletLabel}</b>\n";
        $msg .= "💰 <b>Amount:</b> <b>{$sign}{$cur}" . number_format($amount, 2) . "</b>\n";
        $msg .= "📝 <b>Description:</b> " . htmlspecialchars($description) . "\n";
        $msg .= "🔖 <b>Transaction TNX:</b> <code>{$tnxNumber}</code>\n";
        $msg .= "📈 <b>Updated {$walletLabel} Balance:</b> <b>{$cur}" . number_format($newBal, 2) . "</b>\n";
        $msg .= "🕒 <b>Timestamp:</b> " . now()->format('Y-m-d H:i:s') . " UTC\n";

        $buttons = [
            [
                ['text' => '👤 View Member', 'callback_data' => 'usr_view:' . $user->id],
                ['text' => '📥 Export Ledger (.csv)', 'callback_data' => 'usr_export:' . $user->id],
            ],
            [
                ['text' => '📄 Statement (30d)', 'callback_data' => 'usr_stmt:' . $user->id . ':30d'],
            ]
        ];

        $this->sendMessage($botToken, $chatId, $msg, $buttons);
    }

    /**
     * Handle generating and sending official PDF eStatements from Telegram.
     */
    protected function handleStatementCommand(string $botToken, string $chatId, array $parts): void
    {
        $target = $parts[1] ?? null;
        if (!$target) {
            $msg = "📄 <b>Official PDF Bank Statement Generator</b>\n\n";
            $msg .= "<b>Usage:</b> <code>/statement &lt;user&gt; [period_or_dates]</code>\n\n";
            $msg .= "<b>Examples:</b>\n";
            $msg .= "• <code>/statement @Kalyn_Mickle 30days</code>\n";
            $msg .= "• <code>/statement @Kalyn_Mickle 90days</code>\n";
            $msg .= "• <code>/statement @Kalyn_Mickle 2026-10-01 2026-10-31</code>\n";
            $msg .= "• <code>/statement @Kalyn_Mickle october</code>";
            $this->sendMessage($botToken, $chatId, $msg);
            return;
        }

        $user = $this->findUserByIdentifier($target);
        if (!$user) {
            $this->sendMessage($botToken, $chatId, "❌ Member <code>{$target}</code> not found.");
            return;
        }

        $range = isset($parts[2]) ? implode(' ', array_slice($parts, 2)) : '30days';
        $this->sendMessage($botToken, $chatId, "⏳ Generating official eStatement PDF for @{$user->username} ({$range})...");

        $stmtService = new \App\Services\StatementService();
        $res = $stmtService->sendStatementToTelegram($user, $range, $botToken, $chatId);
        if (!$res['success']) {
            $this->sendMessage($botToken, $chatId, "❌ Statement generation failed: " . $res['message']);
        }
    }

    /**
     * Handle recurring & scheduled transactions command from Telegram.
     */
    protected function handleScheduleCommand(string $botToken, string $chatId, array $parts): void
    {
        $sub = strtolower($parts[1] ?? 'help');

        // 1. Manual Runner Trigger: /schedule run
        if ($sub === 'run') {
            $this->sendMessage($botToken, $chatId, "⏳ Processing all due scheduled transactions...");
            $service = new \App\Services\ScheduledTransactionService();
            $res = $service->processDueSchedules();
            $cur = setting('currency_symbol', '$');
            $this->sendMessage($botToken, $chatId, "✅ <b>Scheduled Runner Complete:</b>\n• Processed: <b>{$res['processed']} items</b>\n• Total Volume: <b>{$cur}" . number_format($res['total_amount'], 2) . "</b>");
            return;
        }

        // 2. Listing: /schedule list [username]
        if ($sub === 'list') {
            \App\Models\ScheduledTransaction::ensureSchema();
            $query = \App\Models\ScheduledTransaction::with('user')->where('status', 1)->latest();
            $targetUserStr = $parts[2] ?? null;
            if ($targetUserStr) {
                $user = $this->findUserByIdentifier($targetUserStr);
                if ($user) {
                    $query->where('user_id', $user->id);
                }
            }
            $items = $query->take(10)->get();
            if ($items->isEmpty()) {
                $this->sendMessage($botToken, $chatId, "ℹ️ No active scheduled transactions found.");
                return;
            }

            $cur = setting('currency_symbol', '$');
            $msg = "🗓️ <b>Active Scheduled & Recurring Transactions</b>\n\n";
            foreach ($items as $idx => $item) {
                $num = $idx + 1;
                $userName = $item->user ? $item->user->full_name . " (@" . $item->user->username . ")" : "User #{$item->user_id}";
                $sign = $item->type === 'credit' ? '+' : '-';
                $msg .= "<b>{$num}. {$item->title}</b>\n";
                $msg .= "   👤 Member: {$userName}\n";
                $msg .= "   💰 Amount: <b>{$sign}{$cur}" . number_format($item->amount, 2) . "</b> ({$item->wallet_label})\n";
                $msg .= "   🔄 Frequency: <i>{$item->frequency_label}</i>\n";
                $msg .= "   📅 Next Run: <code>" . ($item->next_run_date ? $item->next_run_date->format('Y-m-d') : 'N/A') . "</code>\n";
                $msg .= "   🆔 ID: <code>{$item->id}</code> | Executed: {$item->execution_count} times\n\n";
            }
            $msg .= "💡 <i>To pause a schedule:</i> <code>/schedule pause &lt;id&gt;</code>";
            $this->sendMessage($botToken, $chatId, $msg);
            return;
        }

        // 3. Pause: /schedule pause <id>
        if ($sub === 'pause') {
            $id = $parts[2] ?? null;
            $item = \App\Models\ScheduledTransaction::find($id);
            if (!$item) {
                $this->sendMessage($botToken, $chatId, "❌ Schedule ID #{$id} not found.");
                return;
            }
            $item->status = 0;
            $item->save();
            $this->sendMessage($botToken, $chatId, "⏸️ Scheduled transaction #{$id} ({$item->title}) has been <b>PAUSED</b>.");
            return;
        }

        // 4. Create Schedule: /schedule <user> <amount> <credit|debit> <wallet> <frequency> <description...>
        $target = $parts[1] ?? null;
        $amount = $parts[2] ?? null;
        $type = strtolower($parts[3] ?? 'credit');
        $wallet = strtolower($parts[4] ?? 'checking');
        $frequency = strtolower($parts[5] ?? 'monthly_1st');
        $descParts = array_slice($parts, 6);

        if (!$target || !$amount || !is_numeric($amount) || empty($descParts)) {
            $help = "🗓️ <b>Scheduled & Recurring Transaction Scheduler</b>\n\n";
            $help .= "<b>Syntax:</b>\n";
            $help .= "<code>/schedule &lt;user&gt; &lt;amount&gt; &lt;credit|debit&gt; &lt;wallet&gt; &lt;frequency&gt; &lt;description&gt;</code>\n\n";
            $help .= "<b>Frequencies:</b>\n";
            $help .= "• <code>biweekly_1_15</code> (1st & 15th of month)\n";
            $help .= "• <code>monthly_1st</code> (1st of every month)\n";
            $help .= "• <code>monthly_15th</code> (15th of every month)\n";
            $help .= "• <code>monthly_end</code> (End of every month)\n";
            $help .= "• <code>weekly_monday</code> (Every Monday)\n";
            $help .= "• <code>daily</code> (Every day)\n";
            $help .= "• <code>once</code> (One-time future post)\n\n";
            $help .= "<b>Real Examples:</b>\n";
            $help .= "• <code>/schedule @Kalyn_Mickle 3850 credit checking biweekly_1_15 ADP Payroll Direct Deposit</code>\n";
            $help .= "• <code>/schedule @Kalyn_Mickle 1450 debit checking monthly_end Mortgage / Auto-Loan Payment</code>\n";
            $help .= "• <code>/schedule list</code>\n";
            $help .= "• <code>/schedule run</code> (process due items right now)\n";
            $this->sendMessage($botToken, $chatId, $help);
            return;
        }

        $user = $this->findUserByIdentifier($target);
        if (!$user) {
            $this->sendMessage($botToken, $chatId, "❌ Member <code>{$target}</code> not found.");
            return;
        }

        $knownWallets = [
            'checking' => 'default',
            'savings' => 'primary_savings',
            'primary_savings' => 'primary_savings',
            'ira' => 'ira',
            'heloc' => 'heloc',
            'cc' => 'cc',
            'loan' => 'loan',
        ];
        $walletType = $knownWallets[$wallet] ?? 'default';
        $title = implode(' ', $descParts);

        $service = new \App\Services\ScheduledTransactionService();
        $schedule = $service->createSchedule([
            'user_id' => $user->id,
            'title' => $title,
            'amount' => (float)$amount,
            'type' => $type === 'debit' ? 'debit' : 'credit',
            'wallet_type' => $walletType,
            'frequency' => $frequency,
        ], 1);

        $cur = setting('currency_symbol', '$');
        $sign = $schedule->type === 'credit' ? '+' : '-';
        $msg = "🗓️ <b>SCHEDULED TRANSACTION CREATED & ARMED</b>\n\n";
        $msg .= "👤 <b>Member:</b> " . htmlspecialchars($user->full_name) . " (@{$user->username})\n";
        $msg .= "📝 <b>Title:</b> " . htmlspecialchars($schedule->title) . "\n";
        $msg .= "💰 <b>Amount:</b> <b>{$sign}{$cur}" . number_format($schedule->amount, 2) . "</b> ({$schedule->wallet_label})\n";
        $msg .= "🔄 <b>Frequency:</b> <b>{$schedule->frequency_label}</b>\n";
        $msg .= "📅 <b>First Run Date:</b> <code>" . ($schedule->next_run_date ? $schedule->next_run_date->format('Y-m-d') : 'N/A') . "</code>\n";
        $msg .= "🆔 <b>Schedule ID:</b> <code>#{$schedule->id}</code>\n\n";
        $msg .= "💡 <i>Will run automatically via scheduler or type <code>/schedule run</code> to test.</i>";

        $this->sendMessage($botToken, $chatId, $msg);
    }

    /**
     * Feature B: /reconcile [@username|all] — Audit wallet balances vs transaction ledger & auto-heal.
     */
    protected function handleReconcileCommand(string $botToken, string $chatId, array $parts): void
    {
        $target = trim((string) ($parts[1] ?? 'all'));
        $reconcileService = app(\App\Services\ReconciliationService::class);
        $cur = setting('currency_symbol', 'global') ?: '$';

        if (strtolower($target) === 'all' || $target === '') {
            $report = $reconcileService->auditAll(true);
            if ($report['discrepancy_users'] === 0) {
                $msg = "⚖️ <b>LEDGER INTEGRITY VERIFIED</b>\n\n";
                $msg .= "✅ Scanned <b>{$report['total_users_scanned']}</b> member profiles across Checking, Savings & IRA.\n";
                $msg .= "📊 <b>Discrepancies Found:</b> <code>0</code>\n";
                $msg .= "💵 <b>Total Variance:</b> <code>{$cur}0.00</code>\n\n";
                $msg .= "<i>All stored wallet balances match settled transaction sums down to the cent.</i>";
                $this->sendMessage($botToken, $chatId, $msg);
                return;
            }

            $msg = "⚠️ <b>LEDGER DISCREPANCY AUDIT REPORT</b>\n\n";
            $msg .= "👥 <b>Scanned:</b> {$report['total_users_scanned']} members\n";
            $msg .= "🚨 <b>Flagged Profiles:</b> {$report['discrepancy_users']}\n";
            $msg .= "💵 <b>Cumulative Drift:</b> {$cur}" . number_format($report['total_absolute_drift'], 2) . "\n\n";

            $keyboard = [];
            $shown = 0;
            foreach ($report['audits'] as $audit) {
                if ($shown >= 12) {
                    break;
                }
                $u = $audit['user'];
                foreach ($audit['wallets'] as $w) {
                    if ($w['is_match']) {
                        continue;
                    }
                    $sign = $w['delta'] > 0 ? '+' : '-';
                    $msg .= "👤 <b>" . htmlspecialchars($u->full_name) . "</b> (<code>@{$u->username}</code>) — {$w['label']}\n";
                    $msg .= "   Stored: <b>{$cur}" . number_format($w['stored'], 2) . "</b> | Ledger: <b>{$cur}" . number_format($w['computed'], 2) . "</b> | Drift: <code>{$sign}{$cur}" . number_format($w['abs_delta'], 2) . "</code>\n";

                    if (count($keyboard) < 6) {
                        $keyboard[] = [
                            ['text' => "🔧 Sync @{$u->username} ({$w['wallet_key']})", 'callback_data' => "rec_sync:{$u->id}:{$w['wallet_key']}"],
                            ['text' => "➕ Balancing Entry", 'callback_data' => "rec_adj:{$u->id}:{$w['wallet_key']}"],
                        ];
                    }
                    $shown++;
                }
            }

            $msg .= "\n💡 <i>Use <code>/reconcile @username</code> for dedicated wallet controls or visit Admin → Settings → Audit & Reconcile.</i>";
            $this->sendMessage($botToken, $chatId, $msg, $keyboard ?: null);
            return;
        }

        $user = $this->findUserByIdentifier($target);
        if (!$user) {
            $this->sendMessage($botToken, $chatId, "❌ Member <code>" . htmlspecialchars($target) . "</code> not found.");
            return;
        }

        $audit = $reconcileService->auditUser($user);
        $msg = "⚖️ <b>MEMBER LEDGER RECONCILIATION</b>\n\n";
        $msg .= "👤 <b>Member:</b> " . htmlspecialchars($user->full_name) . " (<code>@{$user->username}</code>)\n";
        $msg .= "💳 <b>Account #:</b> <code>{$user->account_number}</code>\n\n";

        $keyboard = [];
        foreach ($audit['wallets'] as $w) {
            $badge = $w['is_match'] ? '✅ BALANCED' : '🚨 MISMATCH';
            $sign = $w['delta'] > 0 ? '+' : '-';
            $msg .= "🏦 <b>{$w['label']}</b> [{$badge}]\n";
            $msg .= "   • Stored Wallet: <code>{$cur}" . number_format($w['stored'], 2) . "</code>\n";
            $msg .= "   • Ledger Net ({$w['txn_count']} txns): <code>{$cur}" . number_format($w['computed'], 2) . "</code>\n";
            $msg .= "   • Variance: <code>" . ($w['is_match'] ? "{$cur}0.00" : "{$sign}{$cur}" . number_format($w['abs_delta'], 2)) . "</code>\n\n";

            if (!$w['is_match']) {
                $keyboard[] = [
                    ['text' => "🔧 Sync {$w['label']}", 'callback_data' => "rec_sync:{$user->id}:{$w['wallet_key']}"],
                    ['text' => "➕ Insert Balancing Entry", 'callback_data' => "rec_adj:{$user->id}:{$w['wallet_key']}"],
                ];
            }
        }

        if (empty($keyboard)) {
            $msg .= "✅ <i>Every active wallet matches its settled transaction ledger.</i>";
        } else {
            $msg .= "⚠️ <i>Choose an auto-heal option below: <b>Sync</b> updates stored wallet to match ledger sum; <b>Balancing Entry</b> inserts an official RECON transaction.</i>";
        }

        $this->sendMessage($botToken, $chatId, $msg, $keyboard ?: null);
    }

    /**
     * Feature C: /sessions — Live Active Sessions Radar.
     */
    protected function handleSessionsCommand(string $botToken, string $chatId): void
    {
        $rendered = $this->buildSessionsRadarPayload();
        $this->sendMessage($botToken, $chatId, $rendered['text'], $rendered['keyboard']);
    }

    protected function buildSessionsRadarPayload(): array
    {
        $radar = app(\App\Services\SessionRadarService::class);
        $sessions = $radar->getActiveSessions(30);

        $msg = "🛰️ <b>LIVE ACTIVE SESSIONS RADAR (Last 30m)</b>\n\n";

        if ($sessions->isEmpty()) {
            $msg .= "No active web or mobile sessions recorded in the past 30 minutes.";
            return [
                'text' => $msg,
                'keyboard' => [[['text' => '🔄 Refresh Radar', 'callback_data' => 'sess_list']]],
            ];
        }

        $msg .= "📊 <b>Total Active Sessions:</b> " . $sessions->count() . "\n\n";

        $keyboard = [];
        foreach ($sessions->take(15) as $idx => $s) {
            $icon = $s->guard === 'admin' ? '👔' : ($s->guard === 'sanctum' ? '📱' : '👤');
            $ago = $s->last_activity_at ? $s->last_activity_at->diffForHumans(null, true) . ' ago' : 'just now';
            $city = $s->city ?: 'Unknown Location';
            $device = $s->device_summary ?: 'Unknown Device';
            $userTag = $s->guard === 'admin' ? "<code>staff:{$s->authenticatable_id}</code>" : "<code>@{$s->username}</code>";

            $msg .= ($idx + 1) . ". {$icon} <b>" . htmlspecialchars((string) $s->display_name) . "</b> ({$userTag} • {$s->role_label})\n";
            $msg .= "   🌐 <code>{$s->ip_address}</code> ({$city}) • {$device}\n";
            $msg .= "   🕒 Active: {$ago} • Path: <code>" . htmlspecialchars((string) $s->current_url) . "</code>\n\n";

            if (count($keyboard) < 6) {
                $shortName = mb_strimwidth((string) ($s->username ?: $s->display_name), 0, 18, '…');
                $keyboard[] = [
                    ['text' => "🚪 Kick {$shortName} ({$s->guard})", 'callback_data' => "sess_kick:{$s->id}"],
                ];
            }
        }

        $keyboard[] = [['text' => '🔄 Refresh Radar', 'callback_data' => 'sess_list']];
        $msg .= "💡 <i>Kill all web + app sessions immediately with <code>/kick @username</code> or tap a button below.</i>";

        return ['text' => $msg, 'keyboard' => $keyboard];
    }

    /**
     * Feature C: /kick <@username|staff:id> — Instant session & Sanctum token termination.
     */
    protected function handleKickCommand(string $botToken, string $chatId, array $parts): void
    {
        $target = trim((string) ($parts[1] ?? ''));
        if ($target === '') {
            $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b>\n<code>/kick @Kalyn_Mickle</code>\n<code>/kick staff:14</code>");
            return;
        }

        $radar = app(\App\Services\SessionRadarService::class);

        if (str_starts_with(strtolower($target), 'staff:')) {
            $adminId = (int) substr($target, 6);
            $admin = Admin::find($adminId);
            if (!$admin) {
                $this->sendMessage($botToken, $chatId, "❌ Staff ID <code>{$adminId}</code> not found.");
                return;
            }
            $res = $radar->kickAdmin($admin);
            $this->sendMessage(
                $botToken,
                $chatId,
                "🚪 <b>STAFF SESSION TERMINATED</b>\n\n👔 <b>Officer/Admin:</b> " . htmlspecialchars($admin->name) . " (ID #{$admin->id})\n🔒 <b>Killed Web Sessions:</b> {$res['terminated_sessions']}"
            );
            return;
        }

        $user = $this->findUserByIdentifier($target);
        if (!$user) {
            $this->sendMessage($botToken, $chatId, "❌ Member <code>" . htmlspecialchars($target) . "</code> not found.");
            return;
        }

        $res = $radar->kickMember($user);
        $this->sendMessage(
            $botToken,
            $chatId,
            "🚪 <b>MEMBER FORCE LOGOUT EXECUTED</b>\n\n"
            . "👤 <b>Member:</b> " . htmlspecialchars($user->full_name) . " (<code>@{$user->username}</code>)\n"
            . "🌐 <b>Terminated Web Sessions:</b> {$res['terminated_sessions']}\n"
            . "📱 <b>Revoked Mobile/Sanctum Tokens:</b> {$res['revoked_tokens']}\n\n"
            . "<i>Member must re-authenticate on their next request.</i>"
        );
    }

    /**
     * Feature D: /maintenance <on|off|status|allowip> [ETA | message] — Remote Maintenance Gate.
     */
    protected function handleMaintenanceCommand(string $botToken, string $chatId, array $parts): void
    {
        $sub = strtolower(trim((string) ($parts[1] ?? 'status')));

        if ($sub === 'allowip' || $sub === 'whitelist') {
            $ip = trim((string) ($parts[2] ?? ''));
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $this->sendMessage($botToken, $chatId, "⚠️ Provide a valid IP address: <code>/maintenance allowip 203.0.113.45</code>");
                return;
            }
            app(\App\Services\SessionRadarService::class)->rememberSuperAdminIp($ip);
            $this->sendMessage($botToken, $chatId, "✅ Whitelisted IP <code>{$ip}</code> for automatic Super-Admin maintenance bypass.");
            return;
        }

        if ($sub === 'on' || $sub === 'enable') {
            $rawNote = trim(implode(' ', array_slice($parts, 2)));
            $eta = null;
            $reason = null;

            if ($rawNote !== '') {
                if (str_contains($rawNote, '|')) {
                    [$etaPart, $reasonPart] = array_map('trim', explode('|', $rawNote, 2));
                    $eta = $etaPart ?: null;
                    $reason = $reasonPart ?: null;
                } else {
                    $eta = $rawNote;
                }
            }

            $res = $this->applyMaintenanceState(true, $eta, $reason);
            $this->sendMessage($botToken, $chatId, $res['text'], $res['keyboard']);
            return;
        }

        if ($sub === 'off' || $sub === 'disable') {
            $res = $this->applyMaintenanceState(false, null, null);
            $this->sendMessage($botToken, $chatId, $res['text'], $res['keyboard']);
            return;
        }

        $res = $this->renderMaintenanceStatusCard();
        $this->sendMessage($botToken, $chatId, $res['text'], $res['keyboard']);
    }

    protected function applyMaintenanceState(bool $enable, ?string $eta, ?string $reason): array
    {
        $secret = setting('secret_key', 'site_maintenance') ?: ('ffcu-' . substr(bin2hex(random_bytes(4)), 0, 8));
        Setting::add('secret_key', $secret, 'string');

        if ($enable) {
            Setting::add('maintenance_mode', '1', 'boolean');
            Setting::add('maintenance_title', 'Scheduled System Upgrade', 'string');
            if ($reason !== null && $reason !== '') {
                Setting::add('maintenance_text', $reason, 'string');
            } else {
                $existingText = setting('maintenance_text', 'site_maintenance');
                if (!$existingText || str_contains(strtolower($existingText), 'sorry for interrupt')) {
                    Setting::add('maintenance_text', 'FrontField Credit Union is performing a scheduled core banking upgrade. Online banking & mobile access will resume shortly.', 'string');
                }
            }
            if ($eta !== null && $eta !== '') {
                Setting::add('maintenance_eta', $eta, 'string');
            }

            try {
                \Illuminate\Support\Facades\Artisan::call('down', [
                    '--render' => 'errors.maintenance',
                    '--secret' => (string) $secret,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Artisan down notice: ' . $e->getMessage());
            }

            $bypassUrl = url('/' . ltrim((string) $secret, '/'));
            $msg = "🔒 <b>MAINTENANCE GATE: ACTIVE (PUBLIC PAUSED)</b>\n\n";
            $msg .= "🏷️ <b>Banner:</b> Scheduled System Upgrade\n";
            if ($eta) {
                $msg .= "⏱️ <b>ETA:</b> " . htmlspecialchars($eta) . "\n";
            }
            $msg .= "🛡️ <b>Super-Admin IP Bypass:</b> Active\n";
            $msg .= "🔑 <b>Instant Bypass Cookie Link:</b>\n{$bypassUrl}\n\n";
            $msg .= "<i>Telegram webhook &amp; Admin Panel remain 100% accessible.</i>";

            return [
                'text' => $msg,
                'keyboard' => [
                    [['text' => '🟢 Disable Maintenance (Go Live)', 'callback_data' => 'maint_off']],
                ],
            ];
        }

        Setting::add('maintenance_mode', '0', 'boolean');
        Setting::add('maintenance_eta', '', 'string');

        try {
            \Illuminate\Support\Facades\Artisan::call('up');
        } catch (\Throwable $e) {
            Log::warning('Artisan up notice: ' . $e->getMessage());
        }

        $downFile = storage_path('framework/down');
        if (is_file($downFile)) {
            @unlink($downFile);
        }

        $msg = "🟢 <b>MAINTENANCE GATE: DISABLED (SITE LIVE)</b>\n\n";
        $msg .= "Public member portal, mobile APIs, and transfers are now online.";

        return [
            'text' => $msg,
            'keyboard' => [
                [['text' => '🔒 Enable Maintenance Gate', 'callback_data' => 'maint_on']],
            ],
        ];
    }

    protected function renderMaintenanceStatusCard(): array
    {
        $isDown = app()->isDownForMaintenance() || (bool) setting('maintenance_mode', 'site_maintenance');
        $eta = setting('maintenance_eta', 'site_maintenance', '');
        $secret = setting('secret_key', 'site_maintenance') ?: 'secret';
        $ipsRaw = Setting::get('maintenance_superadmin_ips', null, '[]');
        $ips = is_string($ipsRaw) ? json_decode($ipsRaw, true) : $ipsRaw;
        $ipCount = is_array($ips) ? count($ips) : 0;

        $msg = "🔧 <b>REMOTE MAINTENANCE GATE STATUS</b>\n\n";
        $msg .= "📡 <b>Current State:</b> " . ($isDown ? '🔒 ENABLED (Maintenance Mode)' : '🟢 LIVE (Public Accessible)') . "\n";
        if ($eta !== '') {
            $msg .= "⏱️ <b>Configured ETA:</b> " . htmlspecialchars((string) $eta) . "\n";
        }
        $msg .= "🛡️ <b>Whitelisted Super-Admin IPs:</b> {$ipCount} recorded\n";
        $msg .= "🔑 <b>Bypass URL:</b> " . url('/' . ltrim((string) $secret, '/')) . "\n\n";
        $msg .= "<i>Ex:</i> <code>/maintenance on 30 mins | Upgrading Core Ledger</code>";

        $btn = $isDown
            ? ['text' => '🟢 Go Live (/maintenance off)', 'callback_data' => 'maint_off']
            : ['text' => '🔒 Enable Maintenance Gate', 'callback_data' => 'maint_on'];

        return [
            'text' => $msg,
            'keyboard' => [[$btn, ['text' => '🔄 Refresh', 'callback_data' => 'maint_status']]],
        ];
    }

    /**
     * Handle /disputes [open|all] command.
     */
    protected function handleDisputesListCommand(string $botToken, string $chatId, array $parts): void
    {
        try {
            \App\Models\TransactionDispute::ensureSchema();
            $mode = strtolower(trim($parts[1] ?? 'open'));
            $chatInst = $this->resolveChatInstitution($chatId);

            $query = \App\Models\TransactionDispute::with('user.institution')->latest();

            if ($chatInst) {
                $query->whereHas('user', fn($u) => $u->where('institution_id', $chatInst->id));
            }

            if ($mode !== 'all') {
                $query->whereIn('status', [
                    \App\Models\TransactionDispute::STATUS_OPEN,
                    \App\Models\TransactionDispute::STATUS_PROVISIONAL,
                ]);
            }

            $claims = $query->take(10)->get();
            $cur = setting('currency_symbol', 'global') ?: '$';

            if ($claims->isEmpty()) {
                $label = $mode === 'all' ? 'recorded' : 'active/open';
                $this->sendMessage($botToken, $chatId, "🛡️ <b>REG E DISPUTE QUEUE</b>\n\n✅ No {$label} transaction disputes right now.");
                return;
            }

            $msg = "🛡️ <b>REG E TRANSACTION DISPUTES (" . strtoupper($mode === 'all' ? 'LATEST 10' : 'ACTIVE QUEUE') . ")</b>\n\n";
            $keyboard = [];

            foreach ($claims as $claim) {
                $memberName = trim(($claim->user?->first_name ?? '') . ' ' . ($claim->user?->last_name ?? ''));
                $uname = $claim->user?->username ? '@' . $claim->user->username : ($memberName ?: 'Member');
                $amt = $cur . number_format((float) $claim->disputed_amount, 2);
                $badge = match ($claim->status) {
                    \App\Models\TransactionDispute::STATUS_OPEN => '🟡 Open',
                    \App\Models\TransactionDispute::STATUS_PROVISIONAL => '💳 Prov. Credit',
                    \App\Models\TransactionDispute::STATUS_RESOLVED_FRAUD => '✅ Upheld',
                    \App\Models\TransactionDispute::STATUS_DENIED => '❌ Denied',
                    default => $claim->status_label,
                };

                $msg .= "• <code>{$claim->claim_number}</code> — <b>{$amt}</b> ({$badge})\n";
                $msg .= "  👤 " . htmlspecialchars($uname) . " • " . htmlspecialchars($claim->category_label) . "\n";

                $keyboard[] = [[
                    'text' => "🔍 {$claim->claim_number} ({$amt})",
                    'callback_data' => 'dsp_view:' . $claim->id,
                ]];
            }

            $msg .= "\n<i>Tap any claim above or type</i> <code>/dispute DSP-XXXXXX</code>";
            $this->sendMessage($botToken, $chatId, $msg, $keyboard);
        } catch (\Throwable $e) {
            Log::error('handleDisputesListCommand failed: ' . $e->getMessage());
            $this->sendMessage($botToken, $chatId, "❌ Unable to load dispute queue: " . htmlspecialchars($e->getMessage()));
        }
    }

    /**
     * Handle /dispute <DSP-XXXXXX|id> command.
     */
    protected function handleDisputeInspectCommand(string $botToken, string $chatId, array $parts): void
    {
        $lookup = trim($parts[1] ?? '');
        if ($lookup === '') {
            $this->sendMessage($botToken, $chatId, "ℹ️ Usage: <code>/dispute DSP-XXXXXX</code> or <code>/disputes</code> to browse open claims.");
            return;
        }

        try {
            \App\Models\TransactionDispute::ensureSchema();
            $normalized = ltrim($lookup, '#');
            $dispute = \App\Models\TransactionDispute::with(['user', 'transaction'])
                ->where('claim_number', strtoupper($normalized))
                ->when(ctype_digit($normalized), fn ($q) => $q->orWhere('id', (int) $normalized))
                ->first();

            if (!$dispute) {
                $this->sendMessage($botToken, $chatId, "❌ No dispute found matching <code>" . htmlspecialchars($lookup) . "</code>.");
                return;
            }

            $card = app(\App\Services\DisputeService::class)->buildTelegramClaimCard($dispute);
            $this->sendMessage($botToken, $chatId, $card['text'], $card['keyboard']);
        } catch (\Throwable $e) {
            Log::error('handleDisputeInspectCommand failed: ' . $e->getMessage());
            $this->sendMessage($botToken, $chatId, "❌ Error inspecting dispute: " . htmlspecialchars($e->getMessage()));
        }
    }

    /**
     * Handle /institutions command: List all active financial institutions and quick scope buttons.
     */
    protected function handleInstitutionsCommand(string $botToken, string $chatId): void
    {
        try {
            \App\Models\Institution::ensureSchema();
            $institutions = \App\Models\Institution::orderBy('is_default', 'desc')->get();
            $currencySymbol = setting('currency_symbol', '$');

            $msg = "🏛 <b>Financial Institutions Directory:</b>\n\n";
            $keyboard = [];

            foreach ($institutions as $inst) {
                $memberCount = \App\Models\User::where('institution_id', $inst->id)->count();
                $totalDeposits = \App\Models\User::where('institution_id', $inst->id)->sum('balance');
                $statusIcon = $inst->is_active ? '🟢' : '🔴';
                $isDef = $inst->is_default ? ' [PRIMARY ROOT]' : '';

                $msg .= "{$statusIcon} <b>{$inst->name} ({$inst->short_name})</b>{$isDef}\n";
                $msg .= "   🔢 Routing: <code>{$inst->routing_number}</code>\n";
                $msg .= "   👥 Members: <b>{$memberCount}</b> | Assets: <b>{$currencySymbol}" . number_format($totalDeposits, 2) . "</b>\n";
                $msg .= "   🔗 Domain: <code>{$inst->slug}.frontfieldcu.pro</code>\n\n";

                $keyboard[] = [
                    ['text' => "👉 Scope Telegram to {$inst->short_name}", 'callback_data' => 'scope_set:' . $inst->slug]
                ];
            }

            $keyboard[] = [
                ['text' => '🌐 Scope to All Institutions (Global)', 'callback_data' => 'scope_set:all']
            ];

            $this->sendMessage($botToken, $chatId, $msg, $keyboard);
        } catch (\Throwable $e) {
            $this->sendMessage($botToken, $chatId, "❌ Error retrieving institutions: " . htmlspecialchars($e->getMessage()));
        }
    }

    /**
     * Handle /switch <inst|all> command: Set active command scope for this Telegram chat.
     */
    protected function handleSwitchScopeCommand(string $botToken, string $chatId, string $targetScope): void
    {
        if (empty($targetScope)) {
            $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/switch &lt;csb|ffcu|all&gt;</code>\n<i>Example:</i> <code>/switch csb</code> to manage Cleveland State Bank, or <code>/switch all</code> for global view.");
            return;
        }

        if ($targetScope === 'all' || $targetScope === 'global') {
            \Illuminate\Support\Facades\Cache::forget("tg_inst_scope_{$chatId}");
            $this->sendMessage($botToken, $chatId, "🌐 <b>Scope Switched: ALL INSTITUTIONS (GLOBAL)</b>\nTelegram commands will now query across all financial institutions.");
            return;
        }

        try {
            \App\Models\Institution::ensureSchema();
            $inst = \App\Models\Institution::where('slug', $targetScope)
                ->orWhere('short_name', strtoupper($targetScope))
                ->orWhere('aliases', 'LIKE', "%{$targetScope}%")
                ->first();

            if (!$inst) {
                $this->sendMessage($botToken, $chatId, "❌ Institution <code>{$targetScope}</code> not recognized. Use <code>/institutions</code> to view available institutions.");
                return;
            }

            \Illuminate\Support\Facades\Cache::put("tg_inst_scope_{$chatId}", $inst->slug, now()->addDays(30));
            $this->sendMessage($botToken, $chatId, "✅ <b>Scope Switched to {$inst->name} ({$inst->short_name})</b>\nAll commands (<code>/pending</code>, <code>/disputes</code>, <code>/dividend</code>, <code>/user</code>) are now scoped to <b>{$inst->name}</b>.\nTo switch back, type <code>/switch all</code>.");
        } catch (\Throwable $e) {
            $this->sendMessage($botToken, $chatId, "❌ Error switching scope: " . htmlspecialchars($e->getMessage()));
        }
    }
}
