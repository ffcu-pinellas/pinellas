<?php

namespace App\Http\Controllers;

use App\Enums\TxnStatus;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\TelegramApprovalService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
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
                    $helpMsg .= "<b>Available Commands:</b>\n";
                    $helpMsg .= "📋 <code>/pending</code> — List all pending transactions with instant Approve/Reject buttons\n";
                    $helpMsg .= "✅ <code>/approve &lt;TNX&gt;</code> — Approve a specific transaction\n";
                    $helpMsg .= "❌ <code>/reject &lt;TNX&gt;</code> — Reject & refund a specific transaction\n";
                    $helpMsg .= "🔍 <code>/status &lt;TNX&gt;</code> — Check live details of any transaction\n";
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

                case '/status':
                    $targetTnx = $parts[1] ?? null;
                    if (!$targetTnx) {
                        $this->sendMessage($botToken, $chatId, "⚠️ <b>Usage:</b> <code>/status TRX12345678</code>");
                    } else {
                        $this->handleStatusCommand($botToken, $chatId, trim($targetTnx));
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
     * Handle /status <tnx>.
     */
    protected function handleStatusCommand(string $botToken, string $chatId, string $tnx): void
    {
        $txn = Transaction::with(['user', 'bank'])->where('tnx', $tnx)->first();

        if (!$txn) {
            $this->sendMessage($botToken, $chatId, "❌ Transaction <code>{$tnx}</code> was not found.");
            return;
        }

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

