<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StatementService
{
    /**
     * Parse date range from flexible user string (e.g. '30days', '90days', '2026-10-01 2026-10-31', 'october').
     */
    public function parseDateRange(?string $rangeStr): array
    {
        $now = Carbon::now();
        $range = strtolower(trim((string)$rangeStr));

        if (empty($range) || $range === '30days' || $range === '30d' || $range === 'month') {
            return [$now->copy()->subDays(30)->startOfDay(), $now->copy()->endOfDay(), 'Last 30 Days'];
        }

        if ($range === '60days' || $range === '60d') {
            return [$now->copy()->subDays(60)->startOfDay(), $now->copy()->endOfDay(), 'Last 60 Days'];
        }

        if ($range === '90days' || $range === '90d' || $range === 'quarter') {
            return [$now->copy()->subDays(90)->startOfDay(), $now->copy()->endOfDay(), 'Last 90 Days'];
        }

        if ($range === '180days' || $range === '6m') {
            return [$now->copy()->subDays(180)->startOfDay(), $now->copy()->endOfDay(), 'Last 6 Months'];
        }

        if ($range === '1year' || $range === '1y' || $range === '365days') {
            return [$now->copy()->subYear()->startOfDay(), $now->copy()->endOfDay(), 'Last 1 Year'];
        }

        // Custom two dates: 'YYYY-MM-DD YYYY-MM-DD' or 'YYYY-MM-DD to YYYY-MM-DD'
        $cleanedRange = str_replace([' to ', ' - ', ' / '], ' ', $range);
        $parts = preg_split('/\s+/', $cleanedRange);
        if (count($parts) >= 2) {
            try {
                $from = Carbon::parse($parts[0])->startOfDay();
                $to = Carbon::parse($parts[1])->endOfDay();
                if ($from->gt($to)) {
                    $tmp = $from;
                    $from = $to;
                    $to = $tmp;
                }
                return [$from, $to, $from->format('M d, Y') . ' — ' . $to->format('M d, Y')];
            } catch (\Throwable $e) {}
        }

        // Month names like 'october', 'september', 'current_month'
        $monthNames = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
        if (in_array($range, $monthNames)) {
            $monthNum = array_search($range, $monthNames) + 1;
            $from = Carbon::create($now->year, $monthNum, 1)->startOfMonth();
            $to = $from->copy()->endOfMonth();
            return [$from, $to, ucfirst($range) . ' ' . $now->year];
        }

        // Numeric days (e.g. '14', '45')
        if (is_numeric($range)) {
            $days = (int)$range;
            return [$now->copy()->subDays($days)->startOfDay(), $now->copy()->endOfDay(), "Last {$days} Days"];
        }

        // Default fallback to 30 days
        return [$now->copy()->subDays(30)->startOfDay(), $now->copy()->endOfDay(), 'Last 30 Days'];
    }

    /**
     * Generate official eStatement PDF for a member.
     */
    public function generateStatementPdf(User $user, ?Carbon $fromDate = null, ?Carbon $toDate = null, array $selectedAccounts = ['checking', 'savings']): array
    {
        $fromDate = $fromDate ?: Carbon::now()->subDays(30)->startOfDay();
        $toDate = $toDate ?: Carbon::now()->endOfDay();

        $walletTypes = [];
        if (in_array('checking', $selectedAccounts)) {
            $walletTypes[] = 'default';
            $walletTypes[] = null;
        }
        if (in_array('savings', $selectedAccounts)) $walletTypes[] = 'primary_savings';
        if (in_array('ira', $selectedAccounts)) $walletTypes[] = 'ira';
        if (in_array('heloc', $selectedAccounts)) $walletTypes[] = 'heloc';
        if (in_array('cc', $selectedAccounts)) $walletTypes[] = 'cc';
        if (in_array('loan', $selectedAccounts)) $walletTypes[] = 'loan';

        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->when(!empty($walletTypes), function ($query) use ($walletTypes) {
                $query->whereIn('wallet_type', $walletTypes);
            })
            ->latest()
            ->get();

        $mask = function($acc) {
            if (!$acc) return '';
            return '**** ' . substr($acc, -4);
        };

        $maskedAccounts = [
            'checking' => $mask($user->account_number),
            'savings' => $mask($user->savings_account_number),
            'ira' => $mask($user->ira_account_number),
            'heloc' => $mask($user->heloc_account_number),
            'cc' => $mask($user->cc_account_number),
            'loan' => $mask($user->loan_account_number),
        ];

        // Base64 logo for PDF
        $logoBase64 = null;
        $siteLogo = setting('site_logo', 'global');
        $logoPath = $siteLogo ? public_path('assets/' . $siteLogo) : null;
        if ($logoPath && file_exists($logoPath)) {
            try {
                $logoData = file_get_contents($logoPath);
                if ($logoData) {
                    $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
                }
            } catch (\Throwable $e) {}
        }
        if (!$logoBase64) {
            try {
                $fallbackPath = public_path('assets/global/images/6RR9UFs6kLq67BrPItMv.png');
                if (file_exists($fallbackPath)) {
                    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($fallbackPath));
                }
            } catch (\Throwable $e) {}
        }

        $siteTitle = setting('site_title', 'global') ?? 'FrontField FCU';
        $homeDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'frontfieldcu.pro';
        $from_date = $fromDate;
        $to_date = $toDate;

        $pdf = Pdf::loadView('frontend::user.transaction.statement_pdf', compact(
            'transactions', 
            'user', 
            'from_date', 
            'to_date', 
            'selectedAccounts', 
            'maskedAccounts', 
            'logoBase64', 
            'siteTitle', 
            'homeDomain'
        ));

        $filename = 'eStatement_' . $user->username . '_' . $fromDate->format('Ymd') . '-' . $toDate->format('Ymd') . '.pdf';

        return [
            'pdf_binary' => $pdf->output(),
            'filename' => $filename,
            'txn_count' => $transactions->count(),
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];
    }

    /**
     * Generate statement and dispatch directly as a Telegram document.
     */
    public function sendStatementToTelegram(User $user, ?string $rangeStr, string $botToken, string $chatId): array
    {
        [$from, $to, $label] = $this->parseDateRange($rangeStr);
        $res = $this->generateStatementPdf($user, $from, $to);

        $cur = setting('currency_symbol', '$');
        $caption = "📄 <b>OFFICIAL ACCOUNT STATEMENT</b>\n\n";
        $caption .= "👤 <b>Member:</b> " . htmlspecialchars($user->full_name) . " (@{$user->username})\n";
        $caption .= "💳 <b>Account #:</b> <code>{$user->account_number}</code>\n";
        $caption .= "📅 <b>Period:</b> {$label}\n";
        $caption .= "📊 <b>Ledger Transactions:</b> {$res['txn_count']} records\n";
        $caption .= "💰 <b>Current Checking:</b> {$cur}" . number_format($user->balance, 2) . "\n";
        $caption .= "🏦 <b>Primary Savings:</b> {$cur}" . number_format($user->savings_balance, 2) . "\n";
        $caption .= "━━━━━━━━━━━━━━━━━━━━\n";
        $caption .= "🏛️ <i>FrontField Credit Union — NCUA Insured</i>";

        try {
            $apiUrl = "https://api.telegram.org/bot{$botToken}/sendDocument";
            $response = Http::timeout(25)
                ->attach('document', $res['pdf_binary'], $res['filename'])
                ->post($apiUrl, [
                    'chat_id' => $chatId,
                    'caption' => $caption,
                    'parse_mode' => 'HTML',
                ]);

            if ($response->successful() && ($response->json('ok') === true)) {
                return ['success' => true, 'message' => 'eStatement delivered to Telegram successfully!'];
            }

            Log::error('Telegram sendStatement failed: ' . $response->body());
            return ['success' => false, 'message' => $response->json('description') ?? 'Telegram delivery failed'];
        } catch (\Throwable $e) {
            Log::error('Telegram statement exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
