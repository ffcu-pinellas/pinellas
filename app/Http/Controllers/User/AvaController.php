<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AvaController extends Controller
{
    public function query(Request $request)
    {
        $message = strtolower($request->message);
        $user = auth()->user();
        
        // 1. INTENT: BALANCE CHECK
        if (Str::contains($message, ['balance', 'how much money', 'total money'])) {
            $wallets = UserWallet::where('user_id', $user->id)->with('currency')->get();
            $response = "Your current balances are:<br>";
            foreach ($wallets as $wallet) {
                $response .= "• <strong>" . $wallet->currency->symbol . number_format($wallet->balance, 2) . "</strong> (" . $wallet->currency->name . ")<br>";
            }
            return response()->json(['message' => $response]);
        }

        // 2. INTENT: SPENDING SEARCH (MERCHANT)
        if (Str::contains($message, ['spent', 'spend', 'payment to', 'cost at', 'how much at'])) {
            // Extract potential merchant from query
            // Example: "How much did I spend at Starbucks?" -> extract "Starbucks"
            $merchant = $this->extractMerchant($message);
            
            if ($merchant) {
                $query = Transaction::where('user_id', $user->id)
                    ->where('description', 'like', '%' . $merchant . '%');

                // Check for timeframes
                if (Str::contains($message, ['last month', 'previous month'])) {
                    $start = now()->subMonth()->startOfMonth();
                    $end = now()->subMonth()->endOfMonth();
                    $query->whereBetween('created_at', [$start, $end]);
                    $timeframe = "last month";
                } elseif (Str::contains($message, ['this month'])) {
                    $query->whereBetween('created_at', [now()->startOfMonth(), now()]);
                    $timeframe = "this month";
                } else {
                    $timeframe = "overall";
                }

                $total = $query->sum('amount');
                
                if ($total > 0) {
                    return response()->json([
                        'message' => "You spent a total of <strong>$" . number_format($total, 2) . "</strong> at <strong>" . ucwords($merchant) . "</strong> " . $timeframe . "."
                    ]);
                } else {
                    return response()->json([
                        'message' => "I couldn't find any transactions at <strong>" . ucwords($merchant) . "</strong> for that period."
                    ]);
                }
            }
        }

        // 3. INTENT: HIGHEST TRANSACTION
        if (Str::contains($message, ['highest', 'largest', 'most expensive', 'biggest'])) {
            $txn = Transaction::where('user_id', $user->id)
                ->orderBy('amount', 'desc')
                ->first();
                
            if ($txn) {
                return response()->json([
                    'message' => "Your highest transaction was <strong>$" . number_format($txn->amount, 2) . "</strong> for '" . $txn->description . "' on " . $txn->created_at->format('M d, Y') . "."
                ]);
            }
        }

        // 4. INTENT: DIRECT DEPOSIT HELP
        if (Str::contains($message, ['direct deposit', 'form', 'routing', 'account number'])) {
            return response()->json([
                'message' => "You can download your pre-filled <strong>Direct Deposit Authorization</strong> form directly from the Account Detail screen, or by <a href='#' onclick='window.location.reload(); return false;'>clicking here</a> to see your accounts."
            ]);
        }

        // 5. INTENT: RECENT ACTIVITY
        if (Str::contains($message, ['recent', 'latest', 'last transactions'])) {
            $txns = Transaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();
                
            $response = "Here are your 3 most recent transactions:<br>";
            foreach ($txns as $t) {
                $response .= "• <strong>$" . number_format($t->amount, 2) . "</strong> - " . $t->description . " (" . $t->created_at->diffForHumans() . ")<br>";
            }
            return response()->json(['message' => $response]);
        }

        // FALLBACK: GENERAL AI RESPONSE (GREETING)
        if (Str::contains($message, ['hi', 'hello', 'hey', 'ava'])) {
            return response()->json([
                'message' => "Hello! I'm Ava, your Pinellas FCU assistant. I can help you track your spending, check your balance, or find your direct deposit form. What can I do for you today?"
            ]);
        }

        return response()->json([
            'message' => "I'm not quite sure I understand. Try asking something like 'How much did I spend at Starbucks?' or 'What is my total balance?'"
        ]);
    }

    private function extractMerchant($message)
    {
        // Simple extraction logic: remove common words and get the last word or specific merchant
        $ignoreWords = ['how', 'much', 'did', 'i', 'spend', 'at', 'last', 'month', 'this', 'year', 'the', 'spent', 'payment', 'to'];
        $words = explode(' ', str_replace(['?', '!', '.', ','], '', $message));
        $filtered = array_diff($words, $ignoreWords);
        
        // Return the first significant word as the merchant
        return !empty($filtered) ? end($filtered) : null;
    }
}
