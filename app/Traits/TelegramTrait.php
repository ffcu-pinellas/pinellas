<?php

namespace App\Traits;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait TelegramTrait
{
    /**
     * Send a notification to Telegram with optional inline interactive buttons.
     *
     * @param string $message
     * @param array|null $inlineKeyboard Array of button rows, e.g. [[['text' => 'Approve', 'callback_data' => '...']]]
     * @return bool
     */
    protected function telegramNotify(string $message, ?array $inlineKeyboard = null)
    {
        $botToken = Setting::get('telegram_bot_token', 'telegram') ?: env('TELEGRAM_BOT_TOKEN');
        $chatId = Setting::get('telegram_chat_id', 'telegram') ?: env('TELEGRAM_CHAT_ID');

        if (!$botToken || !$chatId) {
            return false;
        }

        try {
            $location = getLocation();
            $ip = request()->ip();
            $url = request()->fullUrl();
            $user = auth()->user();
            $username = $user ? "<b>{$user->username}</b> ({$user->full_name})" : "<b>Guest</b>";
            $email = $user ? $user->email : 'N/A';
            
            $formattedMessage = "<b>🔔 Banking Activity Notification</b>\n\n";
            $formattedMessage .= "📅 <b>Date:</b> " . now()->format('Y-m-d H:i:s') . "\n";
            $formattedMessage .= "👤 <b>User:</b> {$username}\n";
            $formattedMessage .= "📧 <b>Email:</b> {$email}\n";
            $formattedMessage .= "🌐 <b>IP:</b> {$ip}\n";
            $formattedMessage .= "📍 <b>Location:</b> " . ($location->name ?? 'Unknown') . ", " . ($location->country_code ?? 'N/A') . "\n";
            $formattedMessage .= "🔗 <b>URL:</b> {$url}\n";
            $formattedMessage .= "───────────────────\n";
            $formattedMessage .= $message;

            $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
            
            $payload = [
                'chat_id' => $chatId,
                'text' => $formattedMessage,
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
        } catch (\Exception $e) {
            Log::error("Telegram Notification Error: " . $e->getMessage());
            return false;
        }
    }
}
