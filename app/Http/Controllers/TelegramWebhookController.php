<?php

namespace App\Http\Controllers;

use App\Enums\TxnStatus;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionSecurityCode;
use App\Models\User;
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
                $this->setUserStatus($targetUser, $newStatus, $botToken, $chatId, $messageId, $callbackId);
                return response()->json(['status' => 'status_updated'], 200);
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
                $this->editMessageText($botToken, $chatId, $messageId, $card['text'], $card['keyboard']);
                $this->answerCallbackQuery($botToken, $callbackId, "🔄 Refreshed @{$targetUser->username}.", false);
                return response()->json(['status' => 'user_refreshed'], 200);
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
                    $helpMsg .= "<b>👤 Member Security & Status:</b>\n";
                    $helpMsg .= "🔓 <code>/unlock &lt;user_id_or_username&gt;</code> — Clear PIN/MFA lockouts and activate member\n";
                    $helpMsg .= "👤 <code>/user &lt;user_id_or_username&gt;</code> — View member profile & tap status buttons\n";
                    $helpMsg .= "⚙️ <code>/setstatus &lt;user&gt; &lt;active|disabled|closed&gt;</code> — Change account status\n\n";
                    $helpMsg .= "<b>⚙️ System:</b>\n";
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

        $keyboard = [
            $statusButtons,
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
    protected function setUserStatus(User $user, int $targetStatus, string $botToken, string $chatId, ?int $messageId = null, ?string $callbackId = null): void
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

        if ($messageId) {
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
}

