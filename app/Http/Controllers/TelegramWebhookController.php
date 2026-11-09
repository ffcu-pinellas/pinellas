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
     * Check if sender is authorized admin.
     */
    protected function isAuthorized(string|int $senderId, string|int $chatId): bool
    {
        [$botToken, $configuredChatId] = $this->getCredentials();

        if (empty($configuredChatId)) {
            return false;
        }

        $senderStr = (string) $senderId;
        $chatStr = (string) $chatId;
        $configuredStr = (string) $configuredChatId;

        return ($senderStr === $configuredStr || $chatStr === $configuredStr);
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
                    $helpMsg = "🏦 <b>FrontField Credit Union — Admin Bot</b>\n\n";
                    $helpMsg .= "<b>💸 Transaction Management:</b>\n";
                    $helpMsg .= "📋 <code>/pending</code> — List all pending transactions with instant Approve/Reject buttons\n";
                    $helpMsg .= "✅ <code>/approve &lt;TNX&gt;</code> — Approve a specific transaction\n";
                    $helpMsg .= "❌ <code>/reject &lt;TNX&gt; [reason]</code> — Reject & refund a specific transaction\n";
                    $helpMsg .= "🔍 <code>/status &lt;TNX_or_User&gt;</code> — Check live details of any transaction or member\n\n";
                    $helpMsg .= "<b>👤 Member Security & Profiles:</b>\n";
                    $helpMsg .= "🔓 <code>/unlock &lt;user&gt;</code> — Clear PIN/MFA lockouts and activate member\n";
                    $helpMsg .= "👤 <code>/user &lt;user&gt;</code> — View member profile & live status buttons\n";
                    $helpMsg .= "✏️ <code>/updateuser &lt;user&gt; &lt;field&gt; &lt;val&gt;</code> — Update name, DOB, address, PIN, pass, email\n";
                    $helpMsg .= "📥 <code>/export &lt;user&gt; [limit]</code> — Export ledger (.csv) ready for 1-Click Import\n";
                    $helpMsg .= "📈 <code>/dividend [user_or_all]</code> — Manage monthly dividends & APY payouts\n";
                    $helpMsg .= "⚙️ <code>/setstatus &lt;user&gt; &lt;active|disabled|closed&gt;</code> — Change account status\n\n";
                    $helpMsg .= "<b>👔 Admin & Staff Personnel:</b>\n";
                    $helpMsg .= "👥 <code>/staff</code> — View all admins and staff directory\n";
                    $helpMsg .= "🔗 <code>/staff link &lt;id&gt; &lt;code&gt;</code> — Set custom vanity link (e.g. /staff link 14 sarah)\n";
                    $helpMsg .= "🔄 <code>/reassign &lt;user&gt; &lt;officer_id&gt;</code> — Reassign member to officer (or 0 to unassign)\n";
                    $helpMsg .= "👔 <code>/staff &lt;id&gt;</code> — View staff card with toggle and PIN buttons\n";
                    $helpMsg .= "⚙️ <code>/staff toggle &lt;id&gt;</code> — Enable or disable a staff account\n";
                    $helpMsg .= "🔑 <code>/staff resetpin &lt;id&gt; &lt;pin&gt;</code> — Reset staff passcode / PIN\n";
                    $helpMsg .= "🔐 <code>/staff resetpass &lt;id&gt; &lt;pass&gt;</code> — Reset staff dashboard password\n\n";
                    $helpMsg .= "<b>⚙️ System & Server Diagnostics:</b>\n";
                    $helpMsg .= "🖥️ <code>/health</code> — Live server vitals (RAM, Disk, DB latency, PHP, Uptime)\n";
                    $helpMsg .= "🔍 <code>/search &lt;query&gt;</code> — Search members by acct #, name, phone, email\n";
                    $helpMsg .= "💰 <code>/balance &lt;user&gt; &lt;wallet&gt; &lt;+amt|-amt&gt; [reason]</code> — Balance adjust & ledger record\n";
                    $helpMsg .= "❄️ <code>/freeze &lt;user&gt;</code> — Emergency freeze on wires, transfers, Zelle\n";
                    $helpMsg .= "♨️ <code>/unfreeze &lt;user&gt;</code> — Restore transfer and wire capabilities\n";
                    $helpMsg .= "🛡️ <code>/audit [limit]</code> — Live security feed of recent auth attempts & lockouts\n";
                    $helpMsg .= "💾 <code>/backup</code> — Trigger live database backup to this chat\n";
                    $helpMsg .= "❓ <code>/help</code> — Show this manual\n";

                    $this->sendMessage($botToken, $chatId, $helpMsg);
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
    protected function handlePendingCommand(string $botToken, string $chatId): void
    {
        $pendingTxns = Transaction::with('user')
            ->where('status', TxnStatus::Pending)
            ->latest()
            ->take(10)
            ->get();

        if ($pendingTxns->isEmpty()) {
            $this->sendMessage($botToken, $chatId, "✨ <b>No pending transactions found!</b> All transfers and withdrawals are up to date.");
            return;
        }

        $currencySymbol = setting('currency_symbol', '$');
        $this->sendMessage($botToken, $chatId, "📋 <b>Found {$pendingTxns->count()} Pending Transaction(s):</b>");

        foreach ($pendingTxns as $txn) {
            $member = $txn->user ? "{$txn->user->full_name} (@{$txn->user->username})" : "Unknown";
            $typeStr = is_object($txn->type) ? $txn->type->value : $txn->type;
            
            $msg = "🔖 <b>Ref:</b> <code>{$txn->tnx}</code>\n";
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
                ['text' => '✏️ Update Profile', 'callback_data' => 'usr_edit_guide:' . $user->id],
            ],
            [
                ['text' => '🔄 Reassign Officer', 'callback_data' => 'usr_reassign_prompt:' . $user->id],
                ['text' => '📥 Export Ledger (.csv)', 'callback_data' => 'usr_export:' . $user->id],
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
     * One-click helper endpoint to register webhook with Telegram API.
     */
    public function setupWebhook(): JsonResponse
    {
        [$botToken, $chatId] = $this->getCredentials();

        if (!$botToken) {
            return response()->json(['success' => false, 'message' => 'TELEGRAM_BOT_TOKEN is not configured.'], 400);
        }

        $webhookUrl = url('api/telegram/webhook');

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/setWebhook", [
                'url' => $webhookUrl,
                'allowed_updates' => ['message', 'callback_query'],
                'drop_pending_updates' => false,
            ]);

            return response()->json([
                'success' => $response->successful(),
                'webhook_url' => $webhookUrl,
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

        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$botToken}/getWebhookInfo");

            return response()->json([
                'success' => $response->successful(),
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

        // 1. Preview / Status across all eligible members (Default)
        if (empty($subCommand) || $subCommand === 'preview' || $subCommand === 'pool') {
            $preview = $divService->previewBatch();
            $global = $preview['global_settings'];

            $msg = "📈 <b>Monthly Dividend & APY Pool Status</b>\n\n";
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
                        ['text' => '🚀 Execute Monthly Payout Now', 'callback_data' => 'div_exec_all']
                    ]
                ];
            }

            $this->sendMessage($botToken, $chatId, $msg, $keyboard);
            return;
        }

        // 2. Direct Batch Execution: /dividend run
        if ($subCommand === 'run' || $subCommand === 'commit') {
            $this->sendMessage($botToken, $chatId, "⏳ Processing monthly dividend distribution batch...");
            $res = $divService->processBatch(null, 'Telegram /dividend run');
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
}
