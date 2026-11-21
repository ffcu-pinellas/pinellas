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

            case 'HOURS':
                return $this->handleHours($isCsb);

            case 'ROUTING':
                return $this->handleRouting($isCsb, $institution);

            case 'BALANCE':
                return $this->handleBalance($user, $isCsb, $institution);
            
            case 'ACCOUNT_DETAILS':
                return $this->handleAccountDetails($user, $isCsb, $institution);

            case 'DIRECT_DEPOSIT':
                return $this->handleDirectDeposit($user, $isCsb);
            
            case 'SPENDING':
                return $this->handleSpending($user, $message, $intent['extracted_merchant'] ?? null);

            case 'SECURITY_PROFILE':
                return $this->handleSecurityProfile();

            case 'EMERGENCY':
                return $this->handleEmergency();

            case 'DISPUTES':
                return $this->handleDisputes($isCsb);

            case 'WIRE_TRANSFER':
                return $this->handleWireTransfer($isCsb, $institution);

            case 'TRANSFERS':
                return $this->handleTransfers($isCsb);

            case 'TRANSFER_SPEED_LIMITS':
                return $this->handleTransferLimits($isCsb);

            case 'BILL_PAY':
                return $this->handleBillPay();
                
            case 'DEPOSITS':
                return $this->handleDeposits($isCsb);

            case 'RATES_APY':
                return $this->handleRates($isCsb);

            case 'FEES_OVERDRAFT':
                return $this->handleFeesOverdraft($isCsb);

            case 'STATEMENTS_TAX':
                return $this->handleStatementsTax($isCsb);

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
                // Comprehensive Fallback Mechanism
                $insuranceAsk = $isCsb ? "• 'Is my money FDIC insured?'" : "• 'Is my account NCUA insured?'";
                $locationAsk = $isCsb ? "• 'Where are your branch locations?'" : "• 'Where can I find an ATM?'";
                $hoursAsk = "• 'What are your branch and drive-thru hours?'";
                $ratesAsk = "• 'What are current savings and CD interest rates?'";
                $depositAsk = "• 'How do I deposit a check and what are the limits?'";
                return response()->json([
                    'type' => 'text',
                    'message' => "I'm not quite sure I understand. To help me accurately assist you, try asking things like:<br><br>• 'What is my total balance or net worth?'<br>• 'What is your ABA routing number?'<br>{$insuranceAsk}<br>{$locationAsk}<br>{$hoursAsk}<br>{$depositAsk}<br>{$ratesAsk}<br>• 'How do I freeze or manage my card?'<br>• 'What are transfer times and limits?'"
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
            'cutoff', 'fedwire', 'swift', 'lock', 'unlock', 'statement', 'statements', 'estatement', 'tax', 'dispute',
            'overdraft', 'courtesy', 'limit', 'limits', 'speed', 'arrive', 'rates', 'interest', 'yield', 'direct',
            'payroll', 'check', 'checks', 'phone', 'call', 'help', 'app'
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
            // 1. Operating Hours & Schedule
            ['regex' => '/(hour|hours|what time|open|close|closing|schedule|weekend|saturday|sunday|lobby|drive thru|drive up)/', 'name' => 'HOURS'],

            // 2. Direct Deposit Setup & Payroll
            ['regex' => '/(direct deposit|payroll|setup direct deposit|direct deposit form|direct deposit pdf|voided check|employer deposit)/', 'name' => 'DIRECT_DEPOSIT'],

            // 3. Disputes & Unauthorized Charges
            ['regex' => '/(dispute|dispute charge|dispute transaction|unauthorized|fraud claim|chargeback|wrong charge|did not authorize)/', 'name' => 'DISPUTES'],

            // 4. Mobile Check Deposit & Remote Capture
            ['regex' => '/(deposit check|mobile deposit|remote deposit|deposit limits|check deposit|deposit|endorse|scan check)/', 'name' => 'DEPOSITS'],

            // 5. Transfer Speed, Timelines & Daily Limits
            ['regex' => '/(transfer limit|how long do transfer|how long does transfer|when will transfer arrive|transfer speed|delivery time|arrive|ach time|zelle limit|wire time|transfer time)/', 'name' => 'TRANSFER_SPEED_LIMITS'],

            // 6. Interest Rates, APY & Yields
            ['regex' => '/(interest rate|rate|rates|apy|apr|yield|cd rate|savings rate|savings apy|checking apy|rate on loan|heloc rate)/', 'name' => 'RATES_APY'],

            // 7. Fees, Overdraft Protection & NSF
            ['regex' => '/(overdraft|nsf|courtesy pay|overdrawn|overdraft fee|maintenance fee|account fee|monthly fee|what fee|what fees|service charge)/', 'name' => 'FEES_OVERDRAFT'],

            // 8. Statements & Tax Documents
            ['regex' => '/(statement|statements|estatement|bank statement|tax form|tax document|1099|1098|financial document)/', 'name' => 'STATEMENTS_TAX'],

            // 9. Card Controls & Management (Freeze / Unfreeze / PIN Reset)
            ['regex' => '/(card pin|change pin|reset pin|freeze card|lock card|unfreeze card|unlock card|freeze|unfreeze|manage card|manage cards|card control|card controls|lost card|stolen card|replace card)/', 'name' => 'CARD_CONTROLS'],

            // 10. Emergency Detection (Fraud / Unauthorized / Declines)
            ['regex' => '/(stole|fraud|compromised|unauthorized).*(card|account|charge|transaction)/', 'name' => 'EMERGENCY'],
            ['regex' => '/acting up.*declining|keep.*declining|card.*declined|declining/', 'name' => 'EMERGENCY'],

            // 11. Federal Insurance (FDIC / NCUA / Protection)
            ['regex' => '/(fdic|ncua|insured|insurance|safety|safe|protection|government backing|guarantee|federally insured)/', 'name' => 'INSURANCE'],

            // 12. Branch & ATM Locations / Addresses
            ['regex' => '/(branch|branches|location|locations|atm|atms|address|office|where are you|find branch|directions|nearby)/', 'name' => 'LOCATIONS'],

            // 13. Routing Transit / ABA Number
            ['regex' => '/(routing|routing number|aba|transit|transit number|routing transit)/', 'name' => 'ROUTING'],

            // 14. Account Details (Different from Balance)
            ['regex' => '/(what is|show me|tell me|forgot|need).*(account|routing|member).*(number|id)/', 'name' => 'ACCOUNT_DETAILS'],
            ['regex' => '/account number|member number|checking number|savings number/', 'name' => 'ACCOUNT_DETAILS'],
            
            // 15. Conversational / Politeness
            ['regex' => '/^(thank you|thanks|bye|goodbye|appreciate it)/', 'name' => 'POLITENESS'],

            // 16. Security & Profile modifications
            ['regex' => '/(biometric|biometrics|passkey|passkeys|face id|touch id|fingerprint|windows hello)/', 'name' => 'SECURITY_PROFILE'],
            ['regex' => '/(change|update|reset|forgot|modify|setup).*(password|pin|passcode|email|phone|profile|address|settings|2fa|two factor)/', 'name' => 'SECURITY_PROFILE'],
            
            // 17. Wire Transfers
            ['regex' => '/(wire|swift|fedwire|wire transfer|international wire|send wire|wire money|wire fee|wire limit|wire cutoff)/', 'name' => 'WIRE_TRANSFER'],

            // 18. General Transfers
            ['regex' => '/(transfer|send|zelle|pay a person|move money)/', 'name' => 'TRANSFERS'],

            // 19. Bill Pay
            ['regex' => '/(pay|schedule).*(bill|utility|utilities|electric)/', 'name' => 'BILL_PAY'],

            // 20. Financial Status (Balances & Debts)
            ['regex' => '/(balance|money|wealth|funds|how much do i have|what do i have|net worth|worth|savings|heloc|credit card|loan).*balance/', 'name' => 'BALANCE'],
            ['regex' => '/balance|worth|how much money/', 'name' => 'BALANCE'],
            
            // 21. Transaction Analysis (Extracting Merchant via regex slot filling)
            ['regex' => '/(?:spent|spend|paid|cost(?:s)?)\s+(?:at\s+|to\s+|for\s+)?(\w+)/', 'name' => 'SPENDING'],
            
            // 22. Identity / Greeting
            ['regex' => '/^(hi|hello|hey|greetings|ava|who are you)/', 'name' => 'IDENTITY'],
            
            // 23. Support Routing
            ['regex' => '/(contact|help|support|talk to|message|ticket|representative|human|agent|phone number|call bank|call you)/', 'name' => 'SUPPORT'],
            
            // 24. General Products
            ['regex' => '/(loan|saving|dps|fdr|credit card|apply|heloc|mortgage|certificate|cd)/', 'name' => 'PRODUCT_INFO']
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
     * Operating Hours Intent Handler
     */
    private function handleHours(bool $isCsb)
    {
        if ($isCsb) {
            $msg = "<strong>Cleveland State Bank</strong> Hours of Operation:<br><br>";
            $msg .= "• <strong>Lobby Hours:</strong><br>";
            $msg .= "&nbsp;&nbsp;– Monday – Thursday: 8:30 AM – 4:30 PM CST<br>";
            $msg .= "&nbsp;&nbsp;– Friday: 8:30 AM – 5:00 PM CST<br>";
            $msg .= "&nbsp;&nbsp;– Saturday & Sunday: Closed<br><br>";
            $msg .= "• <strong>Drive-Thru & ATMs:</strong><br>";
            $msg .= "&nbsp;&nbsp;– Drive-thru teller lines follow regular branch hours.<br>";
            $msg .= "&nbsp;&nbsp;– Drive-Up ATMs at Sharpe Ave, Hwy 61, and Grenada are open <strong>24/7/365</strong> with zero surcharge fees.<br><br>";
            $msg .= "• <strong>Digital Banking:</strong> Move money, view statements, and freeze cards 24/7 online.";

            return response()->json([
                'type' => 'card',
                'title' => 'Cleveland State Bank Operating Hours',
                'message' => $msg,
                'actions' => [
                    ['label' => 'View Locations', 'url' => route('user.dashboard'), 'class' => 'btn-primary']
                ]
            ]);
        }

        $msg = "<strong>FrontField Credit Union</strong> Hours of Service:<br><br>";
        $msg .= "• <strong>Member Support Center:</strong><br>";
        $msg .= "&nbsp;&nbsp;– Monday – Friday: 9:00 AM – 5:00 PM EST<br>";
        $msg .= "&nbsp;&nbsp;– Saturday & Sunday: Closed<br><br>";
        $msg .= "• <strong>Nationwide CO-OP ATM Network:</strong><br>";
        $msg .= "&nbsp;&nbsp;– Accessible <strong>24/7/365</strong> across 30,000+ surcharge-free ATMs.<br><br>";
        $msg .= "• <strong>Digital Banking:</strong> Deposit checks, transfer funds, and manage cards anytime online.";

        return response()->json([
            'type' => 'card',
            'title' => 'FrontField CU Hours & Availability',
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

    /**
     * Mobile Deposit & Check Capture Handler
     */
    private function handleDeposits(bool $isCsb = false)
    {
        $themeClass = $isCsb ? 'btn-danger' : 'btn-primary';
        $instName = $isCsb ? 'Cleveland State Bank' : 'FrontField Credit Union';
        $endorseNote = $isCsb ? 'For Mobile Deposit Only at Cleveland State Bank' : 'For Mobile Deposit Only at FrontField CU';
        $dailyLimit = $isCsb ? '$5,000.00' : '$2,500.00';
        $monthlyLimit = '$10,000.00';
        $cutoff = $isCsb ? '4:00 PM CST' : '4:00 PM EST';

        $msg = "Deposit checks anywhere using your smartphone camera with <strong>Remote Deposit Capture</strong>:<br><br>";
        $msg .= "• <strong>Daily Deposit Limit:</strong> Up to <strong>{$dailyLimit}</strong> per business day.<br>";
        $msg .= "• <strong>Monthly Limit:</strong> Up to <strong>{$monthlyLimit}</strong> per rolling 30-day period.<br>";
        $msg .= "• <strong>Endorsement Requirement:</strong> Sign the back of your check and clearly write: <i>\"{$endorseNote}\"</i>.<br>";
        $msg .= "• <strong>Funds Availability:</strong> Checks deposited before <strong>{$cutoff}</strong> on business days typically post within 1–2 business days. The first $225 is often available next business day.<br>";
        $msg .= "• <strong>Eligible Accounts:</strong> Personal Checking, Commercial Checking, and Primary Savings.";

        return response()->json([
            'type' => 'card',
            'title' => "Mobile Check Deposit ({$instName})",
            'message' => $msg,
            'actions' => [
                ['label' => 'Deposit a Check Now', 'url' => route('user.remote_deposit'), 'class' => $themeClass]
            ]
        ]);
    }

    /**
     * Transfer Speed, Timelines & Limits Handler
     */
    private function handleTransferLimits(bool $isCsb = false)
    {
        $themeClass = $isCsb ? 'btn-danger' : 'btn-primary';
        $instName = $isCsb ? 'Cleveland State Bank' : 'FrontField Credit Union';

        $msg = "Transfer options, delivery timelines, and daily limits at <strong>{$instName}</strong>:<br><br>";
        $msg .= "• <strong>Internal Account Transfers:</strong><br>";
        $msg .= "&nbsp;&nbsp;– Delivery: <strong>Instant / Immediate</strong> (24/7/365)<br>";
        $msg .= "&nbsp;&nbsp;– Limit: Up to your available ledger balance (Fee: $0)<br><br>";
        $msg .= "• <strong>Zelle® Payments:</strong><br>";
        $msg .= "&nbsp;&nbsp;– Delivery: <strong>Within minutes</strong> to enrolled U.S. contacts<br>";
        $msg .= "&nbsp;&nbsp;– Limit: $1,000 – $2,500 daily rolling limit (Fee: $0)<br><br>";
        $msg .= "• <strong>External Bank ACH:</strong><br>";
        $msg .= "&nbsp;&nbsp;– Delivery: <strong>1–3 business days</strong> via the NACHA network<br>";
        $msg .= "&nbsp;&nbsp;– Limit: $10,000.00 daily limit (Fee: $0)<br><br>";
        $msg .= "• <strong>Domestic Fedwire Wires:</strong><br>";
        $msg .= "&nbsp;&nbsp;– Delivery: <strong>Same-day settlement</strong> if placed before cutoff<br>";
        $msg .= "&nbsp;&nbsp;– Fee: $25.00 domestic | $45.00 international SWIFT";

        return response()->json([
            'type' => 'card',
            'title' => 'Transfer Delivery Speeds & Limits',
            'message' => $msg,
            'actions' => [
                ['label' => 'Start a Transfer', 'url' => route('user.fund_transfer.index'), 'class' => $themeClass]
            ]
        ]);
    }

    /**
     * Interest Rates & APY Handler
     */
    private function handleRates(bool $isCsb = false)
    {
        $themeClass = $isCsb ? 'btn-danger' : 'btn-primary';
        $instName = $isCsb ? 'Cleveland State Bank' : 'FrontField Credit Union';

        $msg = "Current interest rates and Annual Percentage Yields (APY) at <strong>{$instName}</strong>:<br><br>";
        $msg .= "• <strong>Personal Checking:</strong> <strong>0.05% APY</strong> (No monthly maintenance fee with $100 min balance)<br>";
        $msg .= "• <strong>Primary Savings:</strong> <strong>3.25% APY</strong> (Compounded monthly, FDIC/NCUA insured up to $250,000)<br>";
        $msg .= "• <strong>Retirement Accounts (IRA):</strong> <strong>4.50% APY</strong> (Tax-advantaged retirement compounding)<br>";
        $msg .= "• <strong>Certificates of Deposit (CDs):</strong> Rates up to <strong>5.10% APY</strong> depending on term (6 to 60 months)<br>";
        $msg .= "• <strong>HELOC Revolving Credit:</strong> Variable rates starting from <strong>Prime + 0.50% APR</strong><br>";
        $msg .= "• <strong>Visa Credit Cards:</strong> Competitive APR starting from <strong>14.99% variable</strong> with 0% intro purchase rate<br>";
        $msg .= "• <strong>Personal Loans:</strong> Fixed rates starting from <strong>7.99% APR</strong> with terms up to 60 months.";

        return response()->json([
            'type' => 'card',
            'title' => "{$instName} Interest Rates & Yields",
            'message' => $msg,
            'actions' => [
                ['label' => 'View Accounts', 'url' => route('user.dashboard'), 'class' => $themeClass]
            ]
        ]);
    }

    /**
     * Fees & Overdraft Policy Handler
     */
    private function handleFeesOverdraft(bool $isCsb = false)
    {
        $themeClass = $isCsb ? 'btn-danger' : 'btn-primary';
        $instName = $isCsb ? 'Cleveland State Bank' : 'FrontField Credit Union';

        $msg = "Fee schedule and overdraft protection policies at <strong>{$instName}</strong>:<br><br>";
        $msg .= "• <strong>Monthly Maintenance Fee:</strong> <strong>$0.00 (Free)</strong> on Personal Checking with active direct deposit or maintaining a $100 minimum daily balance.<br>";
        $msg .= "• <strong>Overdraft Fee:</strong> $30.00 per item overdrawn by more than $10.00. No fee is assessed if the account is overdrawn by less than $10.00.<br>";
        $msg .= "• <strong>Free Overdraft Protection:</strong> Link your Primary Savings account for automatic backup. Funds automatically transfer to cover checking deficits with <strong>$0 transfer fee</strong>.<br>";
        $msg .= "• <strong>Stop Payment Fee:</strong> $25.00 per item.<br>";
        $msg .= "• <strong>e-Statements:</strong> 100% Free electronic PDF statements online ($3.00 for mailed paper copies).<br>";
        $msg .= "• <strong>Card Freezes & ATM Withdrawals:</strong> Zero fee for freezing cards or using in-network ATMs.";

        return response()->json([
            'type' => 'card',
            'title' => 'Fees & Overdraft Protection Schedule',
            'message' => $msg,
            'actions' => [
                ['label' => 'View Account Settings', 'url' => route('user.setting.show'), 'class' => $themeClass]
            ]
        ]);
    }

    /**
     * Direct Deposit Setup Handler
     */
    private function handleDirectDeposit($user, bool $isCsb = false)
    {
        $themeClass = $isCsb ? 'btn-danger' : 'btn-primary';
        $instName = $isCsb ? 'Cleveland State Bank' : 'FrontField Credit Union';
        $routing = $isCsb ? '084301768' : '263177742';

        $msg = "Set up Direct Deposit to receive your paycheck, tax refund, or government benefits up to <strong>2 days early</strong>:<br><br>";
        $msg .= "Provide the following information to your employer or payroll department:<br>";
        $msg .= "• <strong>Bank Name:</strong> {$instName}<br>";
        $msg .= "• <strong>ABA Routing Number:</strong> <code>{$routing}</code><br>";
        $msg .= "• <strong>Checking Account Number:</strong> <code>{$user->account_number}</code><br>";
        if ($user->savings_account_number) {
            $msg .= "• <strong>Savings Account Number:</strong> <code>{$user->savings_account_number}</code><br>";
        }
        $msg .= "• <strong>Account Type:</strong> Checking (or Savings)<br><br>";
        $msg .= "<small><i>You can also download an official, prefilled Direct Deposit Authorization form under your Documents tab.</i></small>";

        return response()->json([
            'type' => 'card',
            'title' => 'Direct Deposit Information',
            'message' => $msg,
            'actions' => [
                ['label' => 'Download Direct Deposit PDF', 'url' => route('user.documents.index'), 'class' => $themeClass]
            ]
        ]);
    }

    /**
     * Statements & Tax Documents Handler
     */
    private function handleStatementsTax(bool $isCsb = false)
    {
        $themeClass = $isCsb ? 'btn-danger' : 'btn-primary';
        $instName = $isCsb ? 'Cleveland State Bank' : 'FrontField Credit Union';

        $msg = "Access your electronic banking and tax documents anytime at <strong>{$instName}</strong>:<br><br>";
        $msg .= "• <strong>Monthly e-Statements:</strong> Generated on the <strong>1st of every month</strong>. View, search, and download official PDF statements archived for up to <strong>7 years</strong>.<br>";
        $msg .= "• <strong>Tax Forms (1099-INT / 1098):</strong> Generated annually by <strong>January 31st</strong> for accounts earning $10 or more in interest/dividends or paying qualifying mortgage/loan interest.<br>";
        $msg .= "• <strong>Direct Deposit Letters & Verification:</strong> Instant PDF download verifying your active account status and routing details.<br>";
        $msg .= "• <strong>Paperless Banking:</strong> e-Statements are secure, encrypted, and eliminate paper clutter.";

        return response()->json([
            'type' => 'card',
            'title' => 'Statements & Tax Documents',
            'message' => $msg,
            'actions' => [
                ['label' => 'Open Documents & Statements', 'url' => route('user.documents.index'), 'class' => $themeClass]
            ]
        ]);
    }

    /**
     * Disputes & Unauthorized Charges Handler
     */
    private function handleDisputes(bool $isCsb = false)
    {
        $instName = $isCsb ? 'Cleveland State Bank' : 'FrontField Credit Union';

        $msg = "Under <strong>Federal Regulation E</strong>, your account at <strong>{$instName}</strong> is protected against unauthorized charges, billing errors, and fraudulent transactions:<br><br>";
        $msg .= "• <strong>Step 1 (Immediate Freeze):</strong> If your debit or credit card was compromised, freeze it immediately in Card Controls to prevent further unauthorized activity.<br>";
        $msg .= "• <strong>Step 2 (File Dispute):</strong> Tap on the disputed transaction in your Recent Activity and click <i>\"File Official Dispute\"</i>, or submit a dispute ticket.<br>";
        $msg .= "• <strong>Investigation Timeline:</strong> Formal review begins within <strong>2 business days</strong>.<br>";
        $msg .= "• <strong>Provisional Credit:</strong> For eligible consumer claims, provisional credit is typically credited to your account within <strong>10 business days</strong> while the merchant investigation completes.";

        return response()->json([
            'type' => 'card',
            'title' => 'Transaction Dispute & Fraud Protection',
            'message' => $msg,
            'actions' => [
                ['label' => 'Freeze Card', 'url' => route('user.cards'), 'class' => 'btn-danger'],
                ['label' => 'Submit Dispute Ticket', 'url' => route('user.ticket.index'), 'class' => 'btn-primary']
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
        $msg = "Manage your authentication and security credentials:<br><br>";
        $msg .= "• <strong>Biometrics & Passkeys:</strong> Enroll Face ID, Touch ID, or Windows Hello for instant 1-touch sign-in and approvals.<br>";
        $msg .= "• <strong>Transaction Security PIN:</strong> Change or reset your 4-digit transfer authorization PIN.<br>";
        $msg .= "• <strong>Verification Priority:</strong> Set your preferred approval method (Biometrics, Email Code, or PIN).<br>";
        $msg .= "• <strong>Password & 2FA:</strong> Update your online banking password or enable two-factor verification.";

        return response()->json([
            'type' => 'card',
            'title' => 'Profile, Passkeys & Security',
            'message' => $msg,
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
