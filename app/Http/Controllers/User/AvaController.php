<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\UserWallet;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AvaController extends Controller
{
    public function query(Request $request)
    {
        $rawMessage = $request->message;
        
        // 1. Data-Driven Preprocessing & Normalization 
        $message = strtolower(preg_replace('/[^\w\s\$\.]/', '', $rawMessage));
        
        // --- NEW: Typo-Tolerance AI Algorithm (Levenshtein Auto-Correct) ---
        $message = $this->autoCorrect($message);

        $user = auth()->user();

        // 2. Advanced Heuristic Intent Parser (Regex-Based)
        $intent = $this->parseIntent($message);

        // 3. Execution Dispatcher
        switch ($intent['name']) {
            case 'BALANCE':
                return $this->handleBalance($user);
            
            case 'ACCOUNT_DETAILS':
                return $this->handleAccountDetails($user);
            
            case 'SPENDING':
                return $this->handleSpending($user, $message, $intent['extracted_merchant'] ?? null);

            case 'SECURITY_PROFILE':
                return $this->handleSecurityProfile();

            case 'EMERGENCY':
                return $this->handleEmergency();

            case 'TRANSFERS':
                return $this->handleTransfers();

            case 'BILL_PAY':
                return $this->handleBillPay();
                
            case 'DEPOSITS':
                return $this->handleDeposits();

            case 'IDENTITY':
                return $this->handleIdentity($user);

            case 'SUPPORT':
                return $this->handleSupport();

            case 'PRODUCT_INFO':
                return $this->handleProducts($message);
                
            case 'POLITENESS':
                return response()->json([
                    'type' => 'text',
                    'message' => "You're very welcome! Let me know if you need anything else. Have a great day!"
                ]);

            default:
                // Fallback Mechanism
                return response()->json([
                    'type' => 'text',
                    'message' => "I'm not quite sure I understand. To help me accurately assist you, try asking things like:<br><br>• 'What is my total balance?'<br>• 'How do I transfer money?'<br>• 'How do I change my PIN?'<br>• 'I lost my card!'"
                ]);
        }
    }

    /**
     * AI Auto-Correction Engine using Levenshtein Distance
     * This corrects typos (e.g. "blance" -> "balance") before pattern matching.
     */
    private function autoCorrect($message)
    {
        // Core banking lexicon
        $lexicon = [
            'balance', 'money', 'wealth', 'funds', 'savings', 'checking', 'account', 'number', 'routing', 'member',
            'change', 'update', 'reset', 'forgot', 'password', 'passcode', 'email', 'profile', 'settings', 'factor',
            'lost', 'stolen', 'fraud', 'freeze', 'compromised', 'card', 'transaction', 'charge', 'declining', 'spend',
            'spent', 'paid', 'cost', 'contact', 'support', 'ticket', 'loan', 'credit', 'heloc', 'deposit',
            'transfer', 'send', 'wire', 'zelle', 'pay', 'bill', 'schedule'
        ];

        $tokens = explode(' ', $message);
        foreach ($tokens as &$token) {
            // Only correct words of length 4 or more to avoid messing up small words like "at", "to", "my"
            if (strlen($token) >= 4) {
                $shortest = -1;
                $closest = $token;
                
                foreach ($lexicon as $word) {
                    $lev = levenshtein($token, $word);
                    if ($lev == 0) {
                        $closest = $word;
                        $shortest = 0;
                        break;
                    }
                    if ($lev <= ($shortest < 0 ? 2 : $shortest) && $lev <= 2) {
                        $closest = $word;
                        $shortest = $lev;
                    }
                }
                $token = $closest;
            }
        }
        return implode(' ', $tokens);
    }

    /**
     * Advanced Regex Heuristic Parser
     */
    private function parseIntent($message)
    {
        $intents = [
            // 1. Emergency Detection (Highest Priority)
            ['regex' => '/(lost|stolen|stole|fraud|freeze|compromised|lock|unauthorized).*(card|account|charge|transaction)/', 'name' => 'EMERGENCY'],
            ['regex' => '/acting up.*declining|keep.*declining|card.*declined|declining/', 'name' => 'EMERGENCY'],
            
            // 2. Account Details (Different from Balance)
            ['regex' => '/(what is|show me|tell me|forgot|need).*(account|routing|member).*(number|id)/', 'name' => 'ACCOUNT_DETAILS'],
            ['regex' => '/account number|routing number/', 'name' => 'ACCOUNT_DETAILS'],
            
            // 3. Conversational / Politeness
            ['regex' => '/^(thank you|thanks|bye|goodbye|appreciate it)/', 'name' => 'POLITENESS'],

            // 4. Security & Profile modifications
            ['regex' => '/(change|update|reset|forgot|modify|setup).*(password|pin|passcode|email|phone|profile|address|settings|2fa|two factor)/', 'name' => 'SECURITY_PROFILE'],
            
            // 5. Transactional Intents (Transfers, Bills, Deposits)
            ['regex' => '/(transfer|send|wire|zelle|pay a person|move money)/', 'name' => 'TRANSFERS'],
            ['regex' => '/(pay|schedule).*(bill|utility|utilities|electric)/', 'name' => 'BILL_PAY'],
            ['regex' => '/(deposit|scan).*(check|mobile)/', 'name' => 'DEPOSITS'],

            // 6. Financial Status (Balances & Debts)
            ['regex' => '/(balance|money|wealth|funds|how much do i have|what do i have|net worth|worth|savings|heloc|credit card|loan).*balance/', 'name' => 'BALANCE'],
            ['regex' => '/balance|worth/', 'name' => 'BALANCE'],
            
            // 7. Transaction Analysis (Extracting Merchant via regex slot filling)
            ['regex' => '/(?:spent|spend|paid|cost(?:s)?)\s+(?:at\s+|to\s+|for\s+)?(\w+)/', 'name' => 'SPENDING'],
            
            // 8. Identity / Greeting
            ['regex' => '/^(hi|hello|hey|greetings|ava|who are you)/', 'name' => 'IDENTITY'],
            
            // 9. Support Routing
            ['regex' => '/(contact|help|support|talk to|message|ticket|representative|human|agent)/', 'name' => 'SUPPORT'],
            
            // 10. General Products
            ['regex' => '/(loan|saving|dps|fdr|rate|credit card|apply)/', 'name' => 'PRODUCT_INFO']
        ];

        foreach ($intents as $intentDesc) {
            if (preg_match($intentDesc['regex'], $message, $matches)) {
                return [
                    'name' => $intentDesc['name'],
                    'extracted_merchant' => isset($matches[1]) && $intentDesc['name'] === 'SPENDING' ? $matches[1] : null
                ];
            }
        }

        return ['name' => 'UNKNOWN'];
    }

    private function handleTransfers()
    {
        return response()->json([
            'type' => 'card',
            'title' => 'Fund Transfers',
            'message' => "I can help you move money! Choose whether you want to transfer between your own accounts or send money to another member.",
            'actions' => [
                ['label' => 'Standard Transfer', 'url' => route('user.fund_transfer.index'), 'class' => 'btn-primary'],
                ['label' => 'Member-to-Member Transfer', 'url' => route('user.fund_transfer.member'), 'class' => 'btn-outline-primary']
            ]
        ]);
    }

    private function handleBillPay()
    {
        return response()->json([
            'type' => 'card',
            'title' => 'Bill Pay functionality',
            'message' => "You can easily schedule or pay your bills (Utilities, Mortgage, etc.) through our automated Bill Pay dashboard.",
            'actions' => [
                ['label' => 'Open Bill Pay', 'url' => route('user.bill-pay.index'), 'class' => 'btn-primary']
            ]
        ]);
    }

    private function handleDeposits()
    {
        return response()->json([
            'type' => 'card',
            'title' => 'Remote Check Deposit',
            'message' => "Need to deposit a check? You can use your phone to scan and deposit checks instantly with Remote Deposit Capture.",
            'actions' => [
                ['label' => 'Deposit a Check', 'url' => route('user.remote_deposit'), 'class' => 'btn-primary']
            ]
        ]);
    }

    private function handleBalance($user)
    {
        $wallets = UserWallet::where('user_id', $user->id)->with('currency')->get();
        
        $msg = "Here is your <strong>Complete Account Overview</strong>:<br><br>";
        
        // --- ASSETS ---
        $msg .= "<div style='color:#00549b; font-weight:bold; margin-bottom:5px; border-bottom:1px solid #eee;'>ASSETS</div>";
        $msg .= "• <strong>Primary Checking:</strong> $" . number_format($user->balance, 2) . "<br>";
        
        $assetsTotal = $user->balance;

        if ($user->savings_balance > 0) {
            $msg .= "• <strong>Savings Account:</strong> $" . number_format($user->savings_balance, 2) . "<br>";
            $assetsTotal += $user->savings_balance;
        }
        
        if ($user->ira_balance > 0) {
            $msg .= "• <strong>Retirement (IRA):</strong> $" . number_format($user->ira_balance, 2) . "<br>";
            $assetsTotal += $user->ira_balance;
        }

        foreach ($wallets as $wallet) {
            $msg .= "• <strong>" . $wallet->currency->name . " Wallet:</strong> " . $wallet->currency->symbol . number_format($wallet->balance, 2) . "<br>";
            $assetsTotal += $wallet->balance;
        }

        // --- LIABILITIES ---
        $liabilitiesTotal = 0;
        $hasLiabilities = false;
        
        if ($user->loan_balance > 0 || $user->heloc_balance > 0 || $user->cc_balance > 0) {
            $hasLiabilities = true;
            $msg .= "<br><div style='color:#e53e3e; font-weight:bold; margin-bottom:5px; border-bottom:1px solid #eee;'>LIABILITIES / DEBTS</div>";
            
            if ($user->cc_balance > 0) {
                $msg .= "• <strong>Credit Card:</strong> $" . number_format($user->cc_balance, 2) . " out of $" . number_format($user->cc_credit_limit, 2) . " limit<br>";
                $liabilitiesTotal += $user->cc_balance;
            }
            if ($user->heloc_balance > 0) {
                $msg .= "• <strong>HELOC:</strong> $" . number_format($user->heloc_balance, 2) . " out of $" . number_format($user->heloc_credit_limit, 2) . " limit<br>";
                $liabilitiesTotal += $user->heloc_balance;
            }
            if ($user->loan_balance > 0) {
                $msg .= "• <strong>Personal Loan:</strong> $" . number_format($user->loan_balance, 2) . "<br>";
                $liabilitiesTotal += $user->loan_balance;
            }
        }

        // --- NET WORTH ---
        $netWorth = $assetsTotal - $liabilitiesTotal;
        $msg .= "<br>Your <strong>Total Net Worth</strong> (Assets - Liabilities) is approximately <strong>$" . number_format($netWorth, 2) . "</strong>.";
        
        return response()->json(['type' => 'text', 'message' => $msg]);
    }

    private function handleAccountDetails($user)
    {
        $msg = "For your security, here are your banking details. Do not share these with anyone:<br><br>";
        
        $msg .= "• <strong>Checking Account:</strong> " . $user->account_number . "<br>";
        
        if ($user->savings_account_number) {
            $msg .= "• <strong>Savings Account:</strong> " . $user->savings_account_number . "<br>";
        }
        
        // Use 'global' as the namespace parameter for the setting helper
        $routing = setting('routing_number', 'global') ?? '063107513';
        $msg .= "• <strong>Routing Transit #:</strong> " . $routing . "<br>";

        $msg .= "<br><small><i>You can also download a formal Direct Deposit form by clicking 'Direct Deposit PDF' under your account actions.</i></small>";

        return response()->json(['type' => 'text', 'message' => $msg]);
    }

    private function handleSecurityProfile()
    {
        return response()->json([
            'type' => 'card',
            'title' => 'Profile & Security',
            'message' => "Need to update your password, PIN, Email, or 2FA settings? You can execute these actions immediately via the secure settings portal.",
            'actions' => [
                ['label' => 'Open Security Settings', 'url' => route('user.setting.security'), 'class' => 'btn-primary'],
                ['label' => 'Edit My Profile', 'url' => route('user.setting.show'), 'class' => 'btn-outline-primary']
            ]
        ]);
    }

    private function handleEmergency()
    {
        return response()->json([
            'type' => 'card',
            'title' => 'EMERGENCY: CARD / FRAUD SUPPORT',
            'message' => "If your card is lost, declining repeatedly, or you suspect fraud, take immediate action below. We actively monitor these tickets 24/7.",
            'actions' => [
                ['label' => 'Freeze My Card', 'url' => route('user.cards'), 'class' => 'btn-danger'],
                ['label' => 'Submit Fraud Ticket', 'url' => route('user.ticket.index'), 'class' => 'btn-primary']
            ]
        ]);
    }

    private function handleSupport()
    {
        return response()->json([
            'type' => 'card',
            'title' => 'Member Support',
            'message' => "Need direct help from a representative? Start a secure ticket with our team.",
            'actions' => [
                ['label' => 'Open Support Ticket', 'url' => route('user.ticket.index'), 'class' => 'btn-primary']
            ]
        ]);
    }

    private function handleSpending($user, $message, $merchant)
    {
        $merchant = $merchant ?: $this->extractMerchantFallback($message);

        if (!$merchant || strlen($merchant) <= 2) {
            return response()->json(['type' => 'text', 'message' => "I can track your expenses! Just tell me the specific merchant, for example: 'How much did I spend at Amazon?'"]);
        }

        $query = Transaction::where('user_id', $user->id)->where('description', 'like', '%' . $merchant . '%');
        
        $timeframe = "overall";
        if (preg_match('/last month|previous month/', $message)) {
            $query->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
            $timeframe = "last month";
        } elseif (preg_match('/this month/', $message)) {
            $query->whereBetween('created_at', [now()->startOfMonth(), now()]);
            $timeframe = "this month";
        } elseif (preg_match('/this year/', $message)) {
            $query->whereBetween('created_at', [now()->startOfYear(), now()]);
            $timeframe = "this year";
        }

        $total = $query->sum('amount');
        
        if ($total > 0) {
            return response()->json(['type' => 'text', 'message' => "You've spent a total of <strong>$" . number_format($total, 2) . "</strong> at <strong>" . ucwords($merchant) . "</strong> " . $timeframe . "."]);
        }
        
        return response()->json(['type' => 'text', 'message' => "I couldn't find any recent spending at <strong>" . ucwords($merchant) . "</strong> for that period."]);
    }

    private function extractMerchantFallback($message)
    {
        $ignore = ['how', 'much', 'did', 'i', 'spend', 'spent', 'pay', 'at', 'last', 'month', 'this', 'year', 'the', 'payment', 'to', 'for', 'on'];
        $words = explode(' ', $message);
        $filtered = array_diff($words, $ignore);
        return !empty($filtered) ? end($filtered) : null;
    }

    private function handleIdentity($user)
    {
        return response()->json([
            'type' => 'text',
            'message' => "I'm Ava, your personalized Pinellas FCU assistant. I'm here to help you, " . $user->first_name . ", manage your finances faster. Ask me about your balance, spending, account numbers, or settings!"
        ]);
    }

    private function handleProducts($message)
    {
        if (preg_match('/loan|heloc/', $message)) {
            return response()->json(['type' => 'text', 'message' => "We offer competitive rates on Personal, Auto, and HELOC loans. You can view our current rates and apply on the <strong>Loans</strong> page of your dashboard."]);
        }
        if (preg_match('/saving|dps|fdr/', $message)) {
            return response()->json(['type' => 'text', 'message' => "Our high-yield savings accounts help your money grow faster. Checkout our <strong>DPS</strong> and <strong>FDR</strong> programs for maximized returns."]);
        }
        if (preg_match('/credit card/', $message)) {
            return response()->json(['type' => 'text', 'message' => "Interested in a new Credit Card? Check your <strong>Cards</strong> tab to see if you are pre-approved or to apply for a limit increase."]);
        }
        return response()->json(['type' => 'text', 'message' => "We have a wide range of products including Loans, Savings, Cards, and Investments. What are you looking for specifically?"]);
    }
}
