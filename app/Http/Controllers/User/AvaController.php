<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\UserWallet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AvaController extends Controller
{
    public function query(Request $request)
    {
        $rawMessage = $request->message;
        $message = strtolower($rawMessage);
        $user = auth()->user();
        
        // --- 1. NORMALIZATION & PREPROCESSING ---
        $tokens = $this->tokenize($message);

        // --- 2. INTENT SCORING ENGINE ---
        $intents = $this->getIntents();
        $bestIntent = 'UNKNOWN';
        $highestScore = 0;

        foreach ($intents as $name => $intent) {
            $score = 0;
            foreach ($intent['keywords'] as $keyword => $weight) {
                if (str_contains($message, $keyword)) {
                    $score += $weight;
                }
            }
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestIntent = $name;
            }
        }

        // --- 3. EXECUTION DISPATCHER ---
        switch ($bestIntent) {
            case 'BALANCE':
                return $this->handleBalance($user);
            
            case 'SPENDING':
                return $this->handleSpending($user, $message);

            case 'EMERGENCY':
                return $this->handleEmergency();

            case 'IDENTITY':
                return $this->handleIdentity($user);

            case 'SUPPORT':
                return $this->handleSupport();

            case 'PRODUCT_INFO':
                return $this->handleProducts($message);

            default:
                return response()->json([
                    'type' => 'text',
                    'message' => "I'm not quite sure I understand. I'm still learning! You can ask me things like:<br>• 'What's my total balance?'<br>• 'How much did I spend at Amazon?'<br>• 'I lost my card!'"
                ]);
        }
    }

    private function handleBalance($user)
    {
        $wallets = UserWallet::where('user_id', $user->id)->with('currency')->get();
        $totalNet = $user->balance + $user->savings_balance + $user->ira_balance;
        
        $msg = "Here is your <strong>Complete Account Overview</strong>:<br><br>";
        $msg .= "• <strong>Primary Checking:</strong> $" . number_format($user->balance, 2) . "<br>";
        
        if ($user->savings_balance > 0) {
            $msg .= "• <strong>Savings Account:</strong> $" . number_format($user->savings_balance, 2) . "<br>";
        }
        
        if ($user->ira_balance > 0) {
            $msg .= "• <strong>Retirement (IRA):</strong> $" . number_format($user->ira_balance, 2) . "<br>";
        }

        foreach ($wallets as $wallet) {
            $msg .= "• <strong>" . $wallet->currency->name . ":</strong> " . $wallet->currency->symbol . number_format($wallet->balance, 2) . "<br>";
            $totalNet += $wallet->balance;
        }

        $msg .= "<br>Your <strong>Estimated Total Net Worth</strong> is approximately <strong>$" . number_format($totalNet, 2) . "</strong>.";
        
        return response()->json(['type' => 'text', 'message' => $msg]);
    }

    private function handleSpending($user, $message)
    {
        $merchant = $this->extractMerchant($message);
        if (!$merchant) {
            return response()->json(['type' => 'text', 'message' => "I can help with spending! Could you tell me the merchant name? (e.g., 'How much at Amazon?')"]);
        }

        $query = Transaction::where('user_id', $user->id)->where('description', 'like', '%' . $merchant . '%');
        
        $timeframe = "overall";
        if (str_contains($message, 'last month')) {
            $query->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
            $timeframe = "last month";
        } elseif (str_contains($message, 'this month')) {
            $query->whereBetween('created_at', [now()->startOfMonth(), now()]);
            $timeframe = "this month";
        }

        $total = $query->sum('amount');
        
        if ($total > 0) {
            return response()->json(['type' => 'text', 'message' => "You've spent a total of <strong>$" . number_format($total, 2) . "</strong> at <strong>" . ucwords($merchant) . "</strong> " . $timeframe . "."]);
        }
        
        return response()->json(['type' => 'text', 'message' => "I couldn't find any spending at <strong>" . ucwords($merchant) . "</strong> for that period."]);
    }

    private function handleEmergency()
    {
        return response()->json([
            'type' => 'card',
            'title' => 'EMERGENCY: Card / Fraud Support',
            'message' => "If your card is lost or you suspect fraud, please take immediate action below. We are here to protect your accounts 24/7.",
            'actions' => [
                ['label' => 'Freeze My Card', 'url' => route('user.cards'), 'class' => 'btn-danger'],
                ['label' => 'Message Support Now', 'url' => route('user.ticket.index'), 'class' => 'btn-primary'],
                ['label' => 'Email Fraud Dept', 'url' => 'mailto:fraud@pinellasfcu.com', 'class' => 'btn-outline-dark']
            ]
        ]);
    }

    private function handleIdentity($user)
    {
        return response()->json([
            'type' => 'text',
            'message' => "I'm Ava, your personalized Pinellas FCU assistant. I'm here to help you, " . $user->first_name . ", manage your finances faster. Ask me about your balance, spending, or how to contact us!"
        ]);
    }

    private function handleSupport()
    {
        return response()->json([
            'type' => 'card',
            'title' => 'Contact & Support',
            'message' => "Need direct help? You can start a secure chat with our team or email us anytime.",
            'actions' => [
                ['label' => 'Send Secure Message', 'url' => route('user.ticket.index'), 'class' => 'btn-primary'],
                ['label' => 'Support Email', 'url' => 'mailto:support@pinellasfcu.com', 'class' => 'btn-outline-primary']
            ]
        ]);
    }

    private function handleProducts($message)
    {
        if (str_contains($message, 'loan')) {
            return response()->json(['type' => 'text', 'message' => "We offer competitive rates on Personal, Auto, and HELOC loans. You can view our current rates and apply on the <strong>Loans</strong> page of your dashboard."]);
        }
        if (str_contains($message, 'saving')) {
            return response()->json(['type' => 'text', 'message' => "Our high-yield savings accounts help your money grow faster. Checkout our <strong>DPS</strong> and <strong>FDR</strong> programs for maximized returns."]);
        }
        return response()->json(['type' => 'text', 'message' => "We have a wide range of products including Loans, Savings, Rewards, and Investments. What are you looking for specifically?"]);
    }

    private function tokenize($message)
    {
        return explode(' ', preg_replace('/[^\w\s]/', '', $message));
    }

    private function getIntents()
    {
        return [
            'BALANCE' => ['keywords' => ['balance' => 10, 'money' => 8, 'wealth' => 10, 'account' => 5, 'checking' => 5, 'savings' => 5]],
            'SPENDING' => ['keywords' => ['spent' => 10, 'spend' => 10, 'pay' => 5, 'cost' => 5, 'amazon' => 10, 'starbucks' => 10, 'walmart' => 10]],
            'EMERGENCY' => ['keywords' => ['lost' => 15, 'stole' => 15, 'fraud' => 20, 'freeze' => 20, 'stolen' => 15]],
            'IDENTITY' => ['keywords' => ['who' => 5, 'name' => 5, 'ava' => 10, 'you' => 5]],
            'SUPPORT' => ['keywords' => ['contact' => 10, 'help' => 10, 'support' => 10, 'talk' => 5, 'message' => 5, 'ticket' => 5]],
            'PRODUCT_INFO' => ['keywords' => ['loan' => 10, 'saving' => 10, 'dps' => 10, 'fdr' => 10, 'rate' => 5]]
        ];
    }

    private function extractMerchant($message)
    {
        $ignore = ['how', 'much', 'did', 'i', 'spend', 'at', 'last', 'month', 'this', 'year', 'the', 'spent', 'payment', 'to', 'for'];
        $words = $this->tokenize($message);
        $filtered = array_diff($words, $ignore);
        return !empty($filtered) ? end($filtered) : null;
    }
}
