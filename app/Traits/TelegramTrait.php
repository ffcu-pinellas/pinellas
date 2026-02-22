<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait TelegramTrait
{
    /**
     * Send a notification to Telegram.
     *
     * @param string $message
     * @return bool
     */
    protected function telegramNotify(string $message)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

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
            
            Http::timeout(5)->post($apiUrl, [
                'chat_id' => $chatId,
                'text' => $formattedMessage,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Telegram Notification Error: " . $e->getMessage());
            return false;
        }
    }
}
