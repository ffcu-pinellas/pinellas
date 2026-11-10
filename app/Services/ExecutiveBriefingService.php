<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExecutiveBriefingService
{
    /**
     * Compile weekly executive vitals across all banking operations.
     */
    public function getBriefingMetrics(): array
    {
        $currencySymbol = setting('currency_symbol', '$');
        $sevenDaysAgo = Carbon::now()->subDays(7);

        // 1. Total Assets & Balances Under Management (AUM)
        $checkingTotal = (float) User::where('status', 1)->sum('balance');
        $savingsTotal = (float) User::where('status', 1)->sum('savings_balance');
        $iraTotal = (float) User::where('status', 1)->sum('ira_balance');
        $helocTotal = (float) User::where('status', 1)->sum('heloc_balance');
        $ccTotal = (float) User::where('status', 1)->sum('cc_balance');
        $totalAum = $checkingTotal + $savingsTotal + $iraTotal;

        // 2. Member Statistics
        $totalActiveUsers = User::where('status', 1)->count();
        $totalLockedUsers = User::where('status', 0)->count();
        $totalClosedUsers = User::where('status', 2)->count();
        $totalUsers = User::count();

        $newMembers7d = User::where('created_at', '>=', $sevenDaysAgo)->count();
        $newOfficerSignups7d = User::whereNotNull('staff_id')->where('created_at', '>=', $sevenDaysAgo)->count();
        $directSignups7d = max(0, $newMembers7d - $newOfficerSignups7d);

        // 3. 7-Day Cash Flow Velocity (Inflows vs Outflows)
        $incomeTypes = ['deposit', 'manual_deposit', 'receive_money', 'refund', 'referral', 'signup_bonus', 'portfolio_bonus', 'reward_redeem', 'dps_maturity'];
        
        $txns7d = Transaction::where('created_at', '>=', $sevenDaysAgo)->get();
        $inflow7d = 0.0;
        $outflow7d = 0.0;

        foreach ($txns7d as $txn) {
            $typeVal = is_object($txn->type) ? $txn->type->value : (string)$txn->type;
            $amt = (float)$txn->amount;
            if (in_array($typeVal, $incomeTypes)) {
                $inflow7d += $amt;
            } else {
                $outflow7d += $amt;
            }
        }
        $netCashFlow7d = $inflow7d - $outflow7d;

        // 4. Staff & Account Officer Performance
        Admin::ensureOnboardingSchema();
        $officers = Admin::where('status', 1)->get();
        $officerCount = $officers->count();
        $totalOfficerClicks = (int) $officers->sum('onboarding_views_count');
        $totalOfficerSignups = (int) $officers->sum('onboarding_signups_count');
        
        // Find top performing officer
        $topOfficer = $officers->sortByDesc('onboarding_signups_count')->first();

        // 5. Security & System Status
        $frozenTransfersCount = User::where('transfer_status', 0)->orWhere('wire_transfer_status', 0)->count();
        $unverifiedKycCount = User::where(function($q) {
            $q->whereNull('kyc')->orWhere('kyc', 0);
        })->where('status', 1)->count();

        return [
            'currency' => $currencySymbol,
            'aum' => $totalAum,
            'checking' => $checkingTotal,
            'savings' => $savingsTotal,
            'ira' => $iraTotal,
            'heloc' => $helocTotal,
            'cc' => $ccTotal,
            'total_users' => $totalUsers,
            'active_users' => $totalActiveUsers,
            'locked_users' => $totalLockedUsers,
            'closed_users' => $totalClosedUsers,
            'new_users_7d' => $newMembers7d,
            'officer_signups_7d' => $newOfficerSignups7d,
            'direct_signups_7d' => $directSignups7d,
            'inflow_7d' => $inflow7d,
            'outflow_7d' => $outflow7d,
            'net_flow_7d' => $netCashFlow7d,
            'txn_count_7d' => $txns7d->count(),
            'officer_count' => $officerCount,
            'officer_clicks' => $totalOfficerClicks,
            'officer_signups' => $totalOfficerSignups,
            'top_officer' => $topOfficer,
            'frozen_transfers' => $frozenTransfersCount,
            'unverified_kyc' => $unverifiedKycCount,
            'generated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Render the executive briefing HTML text for Telegram.
     */
    public function renderBriefingMessage(?string $note = null): array
    {
        $m = $this->getBriefingMetrics();
        $c = $m['currency'];
        $siteTitle = setting('site_title', 'global') ?: 'FrontField Credit Union';

        $netSymbol = $m['net_flow_7d'] >= 0 ? '+' : '-';
        $netAbs = abs($m['net_flow_7d']);
        $netEmoji = $m['net_flow_7d'] >= 0 ? '🟢' : '🔴';

        $text = "☀️ <b>EXECUTIVE SUNDAY BRIEFING</b>\n";
        $text .= "🏛️ <b>{$siteTitle} — Operations Digest</b>\n";
        if ($note) {
            $text .= "💬 <i>{$note}</i>\n";
        }
        $text .= "━━━━━━━━━━━━━━━━━━━━\n\n";

        // 1. Assets & Liquidity
        $text .= "💰 <b>ASSETS UNDER MANAGEMENT</b>\n";
        $text .= "• <b>Total Net AUM:</b> <code>{$c}" . number_format($m['aum'], 2) . "</code>\n";
        $text .= "• Checking Reserves: {$c}" . number_format($m['checking'], 2) . "\n";
        $text .= "• Primary Share Savings: {$c}" . number_format($m['savings'], 2) . "\n";
        if ($m['ira'] > 0) {
            $text .= "• IRA Balances: {$c}" . number_format($m['ira'], 2) . "\n";
        }
        $text .= "\n";

        // 2. 7-Day Cash Flow Velocity
        $text .= "📊 <b>7-DAY CASH FLOW VELOCITY</b>\n";
        $text .= "• Inflows (Deposits/Credits): +{$c}" . number_format($m['inflow_7d'], 2) . "\n";
        $text .= "• Outflows (Wires/Transfers): -{$c}" . number_format($m['outflow_7d'], 2) . "\n";
        $text .= "• <b>Net Weekly Change:</b> {$netEmoji} <b>{$netSymbol}{$c}" . number_format($netAbs, 2) . "</b>\n";
        $text .= "• Total Transactions Processed: <b>" . number_format($m['txn_count_7d']) . "</b>\n\n";

        // 3. 7-Day Membership Growth
        $text .= "👥 <b>MEMBERSHIP & GROWTH (7 DAYS)</b>\n";
        $text .= "• Total Registered Members: <b>{$m['total_users']}</b> (🟢 {$m['active_users']} Active)\n";
        $text .= "• New Members This Week: <b>+{$m['new_users_7d']}</b>\n";
        $text .= "   👉 Via Officer Links: <b>{$m['officer_signups_7d']}</b>\n";
        $text .= "   👉 Direct Portal Signups: <b>{$m['direct_signups_7d']}</b>\n\n";

        // 4. Officer Performance
        $text .= "👔 <b>OFFICER TEAM VITALS</b>\n";
        $text .= "• Active Personnel: <b>{$m['officer_count']} Officers</b>\n";
        $text .= "• Total Onboarding Traffic: <b>{$m['officer_signups']} signups</b> ({$m['officer_clicks']} clicks)\n";
        if ($m['top_officer']) {
            $topName = htmlspecialchars($m['top_officer']->name, ENT_QUOTES, 'UTF-8');
            $topSignups = (int)($m['top_officer']->onboarding_signups_count ?? 0);
            $topClients = $m['top_officer']->managedUsers()->count();
            $text .= "• 🏆 Top Officer: <b>{$topName}</b> ({$topSignups} signups, {$topClients} clients)\n";
        }
        $text .= "\n";

        // 5. Risk & Compliance Health
        $text .= "🛡️ <b>RISK & SECURITY HEALTH</b>\n";
        $text .= "• Account Lockouts: <b>{$m['locked_users']}</b> accounts\n";
        $text .= "• Transfer Freezes: <b>{$m['frozen_transfers']}</b> members frozen\n";
        $text .= "• KYC Pending Verification: <b>{$m['unverified_kyc']}</b> members\n";
        $text .= "• Weekly Backup: <b>Accompanied by .sql.gz archive</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "🕒 <i>Delivered: {$m['generated_at']} UTC</i>";

        $keyboard = [
            [
                ['text' => '👥 Staff Team', 'callback_data' => 'stf_list'],
                ['text' => '📋 Pending Items', 'callback_data' => 'pend_list'],
            ],
            [
                ['text' => '💾 Instant DB Backup', 'callback_data' => 'sys_backup'],
                ['text' => '🔄 Refresh Briefing', 'callback_data' => 'sys_briefing'],
            ]
        ];

        return [
            'text' => $text,
            'keyboard' => $keyboard,
        ];
    }

    /**
     * Dispatch the Executive Briefing to Telegram.
     */
    public function sendToTelegram(?string $botToken = null, ?string $chatId = null, ?string $note = null): array
    {
        $botToken = $botToken ?: (Setting::get('telegram_bot_token', 'telegram') ?: env('TELEGRAM_BOT_TOKEN'));
        $chatId = $chatId ?: (Setting::get('telegram_chat_id', 'telegram') ?: env('TELEGRAM_CHAT_ID'));

        if (!$botToken || !$chatId) {
            return ['success' => false, 'message' => 'Telegram Bot Token or Chat ID is not configured.'];
        }

        $briefing = $this->renderBriefingMessage($note);

        try {
            $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
            $response = Http::timeout(10)->post($apiUrl, [
                'chat_id' => $chatId,
                'text' => $briefing['text'],
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
                'reply_markup' => [
                    'inline_keyboard' => $briefing['keyboard'],
                ],
            ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Executive Briefing delivered successfully!'];
            }

            Log::error('Executive Briefing Telegram error: ' . $response->body());
            return ['success' => false, 'message' => 'Telegram API error: ' . $response->body()];
        } catch (\Throwable $e) {
            Log::error('Executive Briefing Telegram exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

