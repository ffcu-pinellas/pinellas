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
        $message = strtolower(preg_replace('/[^\w\s\$\.]/', '', (string) $rawMessage));
        
        // --- Typo-Tolerance AI Algorithm (Levenshtein Auto-Correct) ---
        $message = $this->autoCorrect($message);

        $user = auth()->user();

        // Resolve Institution Context
        $institution = app()->bound('institution') ? app('institution') : ($user->institution ?? \App\Models\Institution::getDefault());
        $isCsb = ($institution && ($institution->slug === 'clevelandstatebank' || str_contains($institution->slug, 'cleveland') || !$institution->is_default))
            || str_contains($request->getHost(), 'clevelandstatebank');

        // 2. Advanced Heuristic Intent Parser (Regex-Based)
        $intent = $this->parseIntent($message);

        // 3. Execution Dispatcher
        switch ($intent['name']) {
            case 'CARD_CONTROLS':
                return $this->handleCardControls($isCsb);

            case 'INSURANCE':
                return $this->handleInsurance($isCsb, $institution);

            case 'LOCATIONS':
                return $this->handleLocations($isCsb, $institution);

            case 'ROUTING':
                return $this->handleRouting($isCsb, $institution);

            case 'BALANCE':
                return $this->handleBalance($user, $isCsb, $institution);
            
            case 'ACCOUNT_DETAILS':
                return $this->handleAccountDetails($user, $isCsb, $institution);
            
            case 'SPENDING':
                return $this->handleSpending($user, $message, $intent['extracted_merchant'] ?? null);

            case 'SECURITY_PROFILE':
                return $this->handleSecurityProfile();

            case 'EMERGENCY':
                return $this->handleEmergency();

            case 'WIRE_TRANSFER':
                return $this->handleWireTransfer($isCsb, $institution);

            case 'TRANSFERS':
                return $this->handleTransfers($isCsb);

            case 'BILL_PAY':
                return $this->handleBillPay();
                
            case 'DEPOSITS':
                return $this->handleDeposits();

            case 'IDENTITY':
                return $this->handleIdentity($user, $isCsb, $institution);

            case 'SUPPORT':
                return $this->handleSupport($isCsb, $institution);

            case 'PRODUCT_INFO':
                return $this->handleProducts($message, $isCsb);
                
            case 'POLITENESS':
                return response()->json([
                    'type' => 'text',
                    'message' => "You're very welcome! Let me know if you need anything else. Have a great day!"
                ]);

            default:
                // Fallback Mechanism
                $insuranceAsk = $isCsb ? "• 'Is my money FDIC insured?'" : "• 'Is my account NCUA insured?'";
                $locationAsk = $isCsb ? "• 'Where are your branch locations?'" : "• 'Where can I find an ATM?'";
                return response()->json([
                    'type' => 'text',
                    'message' => "I'm not quite sure I understand. To help me accurately assist you, try asking things like:<br><br>• 'What is my total balance?'<br>• 'What is your routing number?'<br>{$insuranceAsk}<br>{$locationAsk}<br>• 'How do I freeze or manage my card?'<br>• 'What are wire transfer cutoff times?'"
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
            'lost', 'stolen', 'fraud', 'freeze', 'compromised', 'card', 'cards', 'transaction', 'charge', 'declining', 'spend',
            'spent', 'paid', 'cost', 'contact', 'support', 'ticket', 'loan', 'credit', 'heloc', 'deposit',
            'transfer', 'send', 'wire', 'zelle', 'pay', 'bill', 'schedule', 'insurance', 'insured', 'fdic', 'ncua',
            'location', 'locations', 'branch', 'branches', 'office', 'atm', 'address', 'hours', 'transit', 'controls',
            'cutoff', 'fedwire', 'swift', 'lock', 'unlock'
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
            // 1. Card Controls & Management (Freeze / Unfreeze / PIN Reset)
            ['regex' => '/(card pin|change pin|reset pin|freeze card|lock card|unfreeze card|unlock card|freeze|unfreeze|manage cards|card controls|lost card|stolen card)/', 'name' => 'CARD_CONTROLS'],

            // 2. Emergency Detection (Fraud / Unauthorized / Declines)
            ['regex' => '/(stole|fraud|compromised|unauthorized).*(card|account|charge|transaction)/', 'name' => 'EMERGENCY'],
            ['regex' => '/acting up.*declining|keep.*declining|card.*declined|declining/', 'name' => 'EMERGENCY'],

            // 3. Federal Insurance (FDIC / NCUA / Protection)
            ['regex' => '/(fdic|ncua|insured|insurance|safety|safe|protection|government backing|guarantee|federally insured)/', 'name' => 'INSURANCE'],

            // 4. Branch & ATM Locations / Addresses / Hours
            ['regex' => '/(branch|branches|location|locations|atm|atms|address|office|hours|where are you|find branch|directions|nearby)/', 'name' => 'LOCATIONS'],

            // 5. Routing Transit / ABA Number
            ['regex' => '/(routing|routing number|aba|transit|transit number|routing transit)/', 'name' => 'ROUTING'],

            // 6. Account Details (Different from Balance)
            ['regex' => '/(what is|show me|tell me|forgot|need).*(account|routing|member).*(number|id)/', 'name' => 'ACCOUNT_DETAILS'],
            ['regex' => '/account number|member number/', 'name' => 'ACCOUNT_DETAILS'],
            
            // 7. Conversational / Politeness
            ['regex' => '/^(thank you|thanks|bye|goodbye|appreciate it)/', 'name' => 'POLITENESS'],

            // 8. Security & Profile modifications
            ['regex' => '/(change|update|reset|forgot|modify|setup).*(password|pin|passcode|email|phone|profile|address|settings|2fa|two factor)/', 'name' => 'SECURITY_PROFILE'],
            
            // 9. Transactional Intents (Wire, Transfers, Bills, Deposits)
            ['regex' => '/(wire|swift|fedwire|wire transfer|international wire|send wire|wire money|wire fee|wire limit|wire cutoff)/', 'name' => 'WIRE_TRANSFER'],
            ['regex' => '/(transfer|send|zelle|pay a person|move money)/', 'name' => 'TRANSFERS'],
            ['regex' => '/(pay|schedule).*(bill|utility|utilities|electric)/', 'name' => 'BILL_PAY'],
            ['regex' => '/(deposit|scan).*(check|mobile)/', 'name' => 'DEPOSITS'],

            // 10. Financial Status (Balances & Debts)
            ['regex' => '/(balance|money|wealth|funds|how much do i have|what do i have|net worth|worth|savings|heloc|credit card|loan).*balance/', 'name' => 'BALANCE'],
            ['regex' => '/balance|worth/', 'name' => 'BALANCE'],
            
            // 11. Transaction Analysis (Extracting Merchant via regex slot filling)
            ['regex' => '/(?:spent|spend|paid|cost(?:s)?)\s+(?:at\s+|to\s+|for\s+)?(\w+)/', 'name' => 'SPENDING'],
            
            // 12. Identity / Greeting
            ['regex' => '/^(hi|hello|hey|greetings|ava|who are you)/', 'name' => 'IDENTITY'],
            
            // 13. Support Routing
            ['regex' => '/(contact|help|support|talk to|message|ticket|representative|human|agent)/', 'name' => 'SUPPORT'],
            
            // 14. General Products
            ['regex' => '/(loan|saving|dps|fdr|rate|credit card|apply|heloc|mortgage|certificate)/', 'name' => 'PRODUCT_INFO']
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

    /**
     * Insurance (FDIC vs NCUA) Intent Handler
     */
    private function handleInsurance(bool $isCsb, $institution)
    {
        if ($isCsb) {
            $msg = "<strong>Cleveland State Bank</strong> is an official Member of the <strong>FDIC (Federal Deposit Insurance Corporation)</strong>.<br><br>";
            $msg .= "• <strong>Coverage Limit:</strong> Your deposits are federally insured up to at least <strong>$250,000</strong> per depositor, for each account ownership category.<br>";
            $msg .= "• <strong>Protected Accounts:</strong> Personal & Commercial Checking, Savings, Certificates of Deposit (CDs), and Money Market accounts.<br>";
            $msg .= "• <strong>Government Backing:</strong> Backed by the full faith and credit of the United States Government.";

            return response()->json([
                'type' => 'card',
                'title' => 'FDIC Insurance & Deposit Protection',
                'message' => $msg,
                'actions' => [
                    ['label' => 'View Accounts', 'url' => route('user.dashboard'), 'class' => 'btn-primary']
                ]
            ]);
        }

        $msg = "<strong>FrontField Credit Union</strong> is federally insured by the <strong>NCUA (National Credit Union Administration)</strong>.<br><br>";
        $msg .= "• <strong>Coverage Limit:</strong> Your member share deposits are insured up to <strong>$250,000</strong> by the National Credit Union Share Insurance Fund (NCUSIF).<br>";
        $msg .= "• <strong>Protected Accounts:</strong> Member Share Drafts (Checking), Regular Share Savings, IRA Shares, and Share Certificates.<br>";
        $msg .= "• <strong>Government Backing:</strong> Backed by the full faith and credit of the United States Government.";

        return response()->json([
            'type' => 'card',
            'title' => 'NCUA Federal Share Insurance',
            'message' => $msg,
            'actions' => [
                ['label' => 'View Accounts', 'url' => route('user.dashboard'), 'class' => 'btn-primary']
            ]
        ]);
    }

    /**
     * Branch & ATM Locations Intent Handler
     */
    private function handleLocations(bool $isCsb, $institution)
    {
        if ($isCsb) {
            $msg = "Official <strong>Cleveland State Bank</strong> Branch & ATM Locations in Mississippi:<br><br>";
            $msg .= "• <strong>Main Office:</strong> 100 N Sharpe Ave, Cleveland, MS 38732 | Phone: (662) 843-9461<br>";
            $msg .= "• <strong>Highway 61 Branch:</strong> 810 Hwy 61 N, Cleveland, MS 38732 | Full Service & 24/7 Drive-Up ATM<br>";
            $msg .= "• <strong>Grenada State Bank Branch:</strong> 1300 Sunset Dr, Grenada, MS 38901 | Phone: (662) 226-5500<br><br>";
            $msg .= "<strong>Lobby Hours:</strong> Mon–Thu 8:30 AM – 4:30 PM, Fri 8:30 AM – 5:00 PM CST.<br>";
            $msg .= "Drive-up ATMs are accessible 24/7 with zero surcharge fees for CSB cardholders.";

            return response()->json([
                'type' => 'card',
                'title' => 'Cleveland State Bank Locations & Hours',
                'message' => $msg,
                'actions' => [
                    ['label' => 'Branch Directory', 'url' => route('user.dashboard'), 'class' => 'btn-primary']
                ]
            ]);
        }

        $msg = "<strong>FrontField Credit Union</strong> Locations & Access:<br><br>";
        $msg .= "• <strong>Corporate Headquarters:</strong> P.O. Box 2300, Cleveland, OH 44166 | Phone: 216 230 1837<br>";
        $msg .= "• <strong>Nationwide CO-OP Network:</strong> Over <strong>30,000 surcharge-free ATMs</strong> and 5,000+ shared branch locations across the country.<br>";
        $msg .= "• <strong>24/7 Digital Banking:</strong> Deposit checks, transfer funds, and pay bills on your phone or web browser anytime.";

        return response()->json([
            'type' => 'card',
            'title' => 'FrontField CU Locations & Nationwide Network',
            'message' => $msg,
            'actions' => [
                ['label' => 'Account Services', 'url' => route('user.dashboard'), 'class' => 'btn-primary']
            ]
        ]);
    }

    /**
     * Routing & Transit Number Intent Handler
     */
    private function handleRouting(bool $isCsb, $institution)
    {
        if ($isCsb) {
            $routing = '084301768';
            $msg = "<strong>Cleveland State Bank</strong> Routing Transit Number:<br><br>";
            $msg .= "• <strong>Routing / ABA #:</strong> <code>{$routing}</code><br>";
            $msg .= "• <strong>Institution Name:</strong> Cleveland State Bank<br>";
            $msg .= "• <strong>Usage:</strong> Direct Deposit, Fedwire, ACH, and Electronic Payments.<br><br>";
            $msg .= "<small><i>For inbound wire transfers, provide this 9-digit ABA routing number alongside your account number.</i></small>";
        } else {
            $routing = '263177742';
            $msg = "<strong>FrontField Credit Union</strong> Routing Transit Number:<br><br>";
            $msg .= "• <strong>Routing / Transit #:</strong> <code>{$routing}</code><br>";
            $msg .= "• <strong>Institution Name:</strong> FrontField Credit Union<br>";
            $msg .= "• <strong>Usage:</strong> Direct Deposit, Domestic Wire, ACH, and Bill Pay.<br><br>";
            $msg .= "<small><i>You can also download a prefilled Direct Deposit Authorization form under your account documents.</i></small>";
        }

        return response()->json([
            'type' => 'card',
            'title' => 'Routing & Transit Number',
            'message' => $msg,
            'actions' => [
                ['label' => 'Direct Deposit & Documents', 'url' => route('user.documents.index'), 'class' => 'btn-primary']
            ]
        ]);
    }

    /**
     * Card Controls & PIN Management Intent Handler
     */
    private function handleCardControls(bool $isCsb)
    {
        $themeClass = $isCsb ? 'btn-danger' : 'btn-primary';
        $msg = "Manage your debit and credit cards instantly with 24/7 Card Controls:<br><br>";
        $msg .= "• <strong>Instant Freeze / Lock:</strong> Temporarily freeze your card to prevent unauthorized charges if misplaced.<br>";
        $msg .= "• <strong>Instant Unfreeze:</strong> Reactivate your card with one tap the moment you find it.<br>";
        $msg .= "• <strong>Reset Card PIN:</strong> Set a new 4-digit PIN instantly online without waiting for mail.<br>";
        $msg .= "• <strong>Report Lost / Stolen:</strong> Permanently deactivate compromised cards and request an expedited replacement.";

        return response()->json([
            'type' => 'card',
            'title' => 'Card Controls & Security',
            'message' => $msg,
            'actions' => [
                ['label' => 'Manage Cards & Freeze', 'url' => route('user.cards'), 'class' => $themeClass]
            ]
        ]);
    }

    private function handleWireTransfer(bool $isCsb = false, $institution = null)
    {
        $wire = \App\Models\WireTransfar::first();
        $currencySymbol = setting('currency_symbol', 'global') ?? '$';
        $domFee = $wire ? (($wire->charge_type === 'percentage') ? $wire->charge . '%' : $currencySymbol . number_format($wire->charge, 2)) : '$25.00';
        $intlFee = $wire ? (($wire->international_charge_type === 'percentage') ? ($wire->international_charge ?? 45) . '%' : $currencySymbol . number_format($wire->international_charge ?? 45.00, 2)) : '$45.00';

        if ($isCsb) {
            $msg = "You can send high-value <strong>Domestic Fedwire</strong> and <strong>International SWIFT</strong> wire transfers directly from your Personal Checking, Commercial Checking, Savings, or HELOC account.<br><br>";
            $msg .= "• <strong>Domestic Fedwire:</strong> Same-day delivery via 9-digit ABA Routing: <strong>084301768</strong> (Fee: {$domFee})<br>";
            $msg .= "• <strong>International SWIFT:</strong> Global delivery via SWIFT/BIC & IBAN (Fee: {$intlFee})<br>";
            $msg .= "• <strong>Fedwire Cutoff:</strong> Orders submitted before <strong>2:30 PM CST (3:30 PM EST)</strong> are processed on the same business day.";
        } else {
            $msg = "You can send high-value <strong>Domestic</strong> and <strong>International (SWIFT)</strong> wire transfers directly from your Checking, Savings, IRA, or HELOC account.<br><br>";
            $msg .= "• <strong>Domestic Wires:</strong> Same-day delivery via 9-digit ABA Routing: <strong>263177742</strong> (Fee: {$domFee})<br>";
            $msg .= "• <strong>International Wires:</strong> Global delivery via SWIFT/BIC & IBAN (Fee: {$intlFee})<br>";
            $msg .= "• <strong>Wire Cutoff:</strong> Orders submitted before <strong>3:00 PM EST</strong> are processed on the same business day.";
        }

        return response()->json([
            'type' => 'card',
            'title' => $isCsb ? 'Cleveland State Bank Wire Transfers' : 'Domestic & International Wire Transfers',
            'message' => $msg,
            'actions' => [
                ['label' => 'Send Wire Transfer', 'url' => route('user.fund_transfer.transfer.wire'), 'class' => 'btn-primary'],
                ['label' => 'All Transfer Options', 'url' => route('user.fund_transfer.index'), 'class' => 'btn-outline-primary']
            ]
        ]);
    }

    private function handleTransfers(bool $isCsb = false)
    {
        $destDesc = $isCsb ? "between your own CSB accounts or send money to another customer" : "between your own accounts or send money to another member";
        return response()->json([
            'type' => 'card',
            'title' => 'Fund Transfers',
            'message' => "I can help you move money! Choose whether you want to transfer {$destDesc}.",
            'actions' => [
                ['label' => 'Standard Transfer', 'url' => route('user.fund_transfer.index'), 'class' => 'btn-primary'],
                ['label' => 'Direct Transfer', 'url' => route('user.fund_transfer.member'), 'class' => 'btn-outline-primary']
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

    private function handleBalance($user, bool $isCsb = false, $institution = null)
    {
        $wallets = UserWallet::where('user_id', $user->id)->with('currency')->get();
        $brandColor = $isCsb ? ($institution->primary_color ?? '#c8232c') : '#00549b';
        
        $msg = "Here is your <strong>Complete Account Overview</strong>:<br><br>";
        
        // --- ASSETS ---
        $msg .= "<div style='color:{$brandColor}; font-weight:bold; margin-bottom:5px; border-bottom:1px solid #eee;'>ASSETS</div>";
        $checkingLabel = $isCsb ? 'Primary Checking' : 'Member Share Draft (Checking)';
        $msg .= "• <strong>{$checkingLabel}:</strong> $" . number_format($user->balance, 2) . "<br>";
        
        $assetsTotal = $user->balance;

        if ($user->savings_balance > 0) {
            $savingsLabel = $isCsb ? 'Primary Savings' : 'Member Share Savings';
            $msg .= "• <strong>{$savingsLabel}:</strong> $" . number_format($user->savings_balance, 2) . "<br>";
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

    private function handleAccountDetails($user, bool $isCsb = false, $institution = null)
    {
        $msg = "For your security, here are your banking details. Do not share these with anyone:<br><br>";
        
        $checkingLabel = $isCsb ? 'Checking Account' : 'Member Share Draft (Checking)';
        $savingsLabel = $isCsb ? 'Savings Account' : 'Member Share Savings';

        $msg .= "• <strong>{$checkingLabel}:</strong> " . $user->account_number . "<br>";
        
        if ($user->savings_account_number) {
            $msg .= "• <strong>{$savingsLabel}:</strong> " . $user->savings_account_number . "<br>";
        }
        
        $routing = $isCsb ? '084301768' : '263177742';
        $msg .= "• <strong>Routing Transit #:</strong> " . $routing . "<br>";

        $instName = $institution ? $institution->name : ($isCsb ? 'Cleveland State Bank' : 'FrontField Credit Union');
        $msg .= "• <strong>Financial Institution:</strong> {$instName}<br>";

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

    private function handleSupport(bool $isCsb = false, $institution = null)
    {
        if ($isCsb) {
            return response()->json([
                'type' => 'card',
                'title' => 'Customer Care & Support',
                'message' => "Need direct help from a representative? Start a secure ticket with our Cleveland State Bank Customer Care team or call (662) 843-9461.",
                'actions' => [
                    ['label' => 'Open Support Ticket', 'url' => route('user.ticket.index'), 'class' => 'btn-primary']
                ]
            ]);
        }

        return response()->json([
            'type' => 'card',
            'title' => 'Member Support',
            'message' => "Need direct help from a representative? Start a secure ticket with our Member Support team or call 216 230 1837.",
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

    private function handleIdentity($user, bool $isCsb = false, $institution = null)
    {
        if ($isCsb) {
            return response()->json([
                'type' => 'text',
                'message' => "I'm Ava, your virtual banking assistant for <strong>Cleveland State Bank</strong>. I'm here to help you, " . $user->first_name . ", manage your finances faster. Ask me about your balance, FDIC insurance, branch locations, routing number, wire cutoffs, or card controls!"
            ]);
        }

        return response()->json([
            'type' => 'text',
            'message' => "I'm Ava, your personalized <strong>FrontField Credit Union</strong> assistant. I'm here to help you, " . $user->first_name . ", manage your finances faster. Ask me about your member share balance, NCUA insurance, routing number, wire cutoffs, or spending!"
        ]);
    }

    private function handleProducts($message, bool $isCsb = false)
    {
        if (preg_match('/loan|heloc|mortgage/', $message)) {
            $desc = $isCsb
                ? "Cleveland State Bank offers competitive rates on Personal Loans, Commercial Lending, Mortgages, and Home Equity Lines of Credit (HELOC). You can review options directly in your dashboard."
                : "We offer competitive rates on Personal, Auto, and HELOC loans. You can view our current rates and apply on the <strong>Loans</strong> page of your dashboard.";
            return response()->json(['type' => 'text', 'message' => $desc]);
        }
        if (preg_match('/saving|dps|fdr|certificate|cd/', $message)) {
            $desc = $isCsb
                ? "Cleveland State Bank offers High-Yield Savings, Certificates of Deposit (CDs), and Treasury Management accounts. All deposits are FDIC insured up to $250,000."
                : "Our high-yield savings accounts help your money grow faster. Checkout our <strong>DPS</strong> and <strong>FDR</strong> programs for maximized returns.";
            return response()->json(['type' => 'text', 'message' => $desc]);
        }
        if (preg_match('/credit card/', $message)) {
            $desc = $isCsb
                ? "Interested in a Cleveland State Bank credit card? Visit your <strong>Cards</strong> tab to manage cards or view limit options."
                : "Interested in a new Credit Card? Check your <strong>Cards</strong> tab to see if you are pre-approved or to apply for a limit increase.";
            return response()->json(['type' => 'text', 'message' => $desc]);
        }

        $instName = $isCsb ? 'Cleveland State Bank' : 'FrontField Credit Union';
        return response()->json(['type' => 'text', 'message' => "At {$instName}, we offer a wide range of services including Checking, Savings, Loans, and Investments. What would you like to explore?"]);
    }
}
