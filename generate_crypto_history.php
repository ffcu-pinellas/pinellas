<?php
/**
 * Crypto & Stock Trader History Generator
 * Timeline: Jan 2023 - March 2026
 * Profile: High Net Worth (Crypto/Stocks)
 * Range: $100k - $1.5M for large transfers
 */

$totalEntries = 650;
$startDate = date("Y-m-d", rand(strtotime("2023-01-01"), strtotime("2023-03-31")));
$endDate = date("Y-m-d");

$vendors = [
    // --- Luxury & High Value ---
    'Whole Foods Market' => ['min' => 120, 'max' => 850, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Apple Store Purchase' => ['min' => 200, 'max' => 12000, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Tesla Supercharger' => ['min' => 15, 'max' => 65, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Equinox Luxury Fitness' => ['min' => 250, 'max' => 450, 'type' => 'subtract', 'method' => 'ACH'],
    'Peloton Interactive' => ['min' => 44, 'max' => 3500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Gucci Boutique' => ['min' => 500, 'max' => 8500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Nordstrom' => ['min' => 150, 'max' => 4500, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Ritz-Carlton Stay' => ['min' => 1200, 'max' => 15000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Louis Vuitton' => ['min' => 800, 'max' => 12000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Prada Milano' => ['min' => 600, 'max' => 9500, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'NetJets Monthly' => ['min' => 15000, 'max' => 45000, 'type' => 'subtract', 'method' => 'Wire'],
    'Sotheby\'s Auction Payment' => ['min' => 25000, 'max' => 150000, 'type' => 'subtract', 'method' => 'Wire'],
    'Four Seasons Resort' => ['min' => 2500, 'max' => 8000, 'type' => 'subtract', 'method' => 'Apple Pay'],
    // --- Daily Life & Regular (Mid/Low Value) ---
    'Amazon Market' => ['min' => 10, 'max' => 5000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Uber Ride' => ['min' => 15, 'max' => 350, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Lyft Ride' => ['min' => 10, 'max' => 180, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Starbucks Coffee' => ['min' => 6, 'max' => 40, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Target Purchase' => ['min' => 15, 'max' => 600, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Walmart Supercenter' => ['min' => 20, 'max' => 800, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Publix Grocery' => ['min' => 50, 'max' => 400, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Costco Wholesale' => ['min' => 100, 'max' => 1200, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Home Depot' => ['min' => 50, 'max' => 3000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Best Buy' => ['min' => 100, 'max' => 5000, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Netflix Subscription' => ['min' => 15, 'max' => 25, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Spotify Subscription' => ['min' => 10, 'max' => 18, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Delta Airlines' => ['min' => 200, 'max' => 3500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'American Airlines' => ['min' => 250, 'max' => 4000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Shell Gas Station' => ['min' => 40, 'max' => 150, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Chevron Gas' => ['min' => 35, 'max' => 130, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'McDonald\'s' => ['min' => 8, 'max' => 45, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Chick-fil-A' => ['min' => 12, 'max' => 60, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Panera Bread' => ['min' => 15, 'max' => 65, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Verizon Wireless' => ['min' => 120, 'max' => 450, 'type' => 'subtract', 'method' => 'ACH'],
    'AT&T Mobility' => ['min' => 80, 'max' => 400, 'type' => 'subtract', 'method' => 'Debit Card'],
    // --- Incoming Trade/Refunds/Small Payouts ---
    'Apple Store / Refund' => ['min' => 200, 'max' => 3500, 'type' => 'deposit', 'method' => 'Apple Pay'],
    'Gucci / Boutique Credit' => ['min' => 500, 'max' => 2500, 'type' => 'deposit', 'method' => 'Debit Card'],
    'Louis Vuitton / Return' => ['min' => 800, 'max' => 4000, 'type' => 'deposit', 'method' => 'Debit Card'],
    'Tesla / Service Refund' => ['min' => 100, 'max' => 1500, 'type' => 'deposit', 'method' => 'Apple Pay'],
    'NetJets / Charter Refund' => ['min' => 5000, 'max' => 25000, 'type' => 'deposit', 'method' => 'Wire'],
    'Coinbase / Loyalty Payout' => ['min' => 50, 'max' => 500, 'type' => 'deposit', 'method' => 'ACH'],
    'Amazon / Refund' => ['min' => 15, 'max' => 500, 'type' => 'deposit', 'method' => 'Debit Card'],
    'Zelle Transfer from Contact' => ['min' => 20, 'max' => 2000, 'type' => 'deposit', 'method' => 'Zelle'],
    'Venmo / Payout' => ['min' => 50, 'max' => 4500, 'type' => 'deposit', 'method' => 'ACH'],
    'CashApp / Received' => ['min' => 25, 'max' => 2800, 'type' => 'deposit', 'method' => 'ACH'],
];

$highValueTypes = [
    // --- Crypto & Trading Settlements ---
    'Coinbase Pro Withdrawal' => ['min' => 150000, 'max' => 950000, 'type' => 'deposit', 'method' => 'Wire'],
    'Binance US Disbursement' => ['min' => 300000, 'max' => 1200000, 'type' => 'deposit', 'method' => 'Wire'],
    'Kraken OTC Settlement' => ['min' => 500000, 'max' => 2500000, 'type' => 'deposit', 'method' => 'Wire'],
    'Charles Schwab Brokerage Transfer' => ['min' => 200000, 'max' => 1500000, 'type' => 'deposit', 'method' => 'Wire'],
    'Robinhood Crypto Sale' => ['min' => 50000, 'max' => 300000, 'type' => 'deposit', 'method' => 'ACH'],
    'Fidelity Investments / Settlement' => ['min' => 85000, 'max' => 450000, 'type' => 'deposit', 'method' => 'Wire'],
    // --- Creator/Business Revenue ---
    'Facebook Adsense Revenue' => ['min' => 25000, 'max' => 85000, 'type' => 'deposit', 'method' => 'ACH'],
    'Instagram Influencer Payout' => ['min' => 15000, 'max' => 65000, 'type' => 'deposit', 'method' => 'ACH'],
    'TikTok Creator Revenue' => ['min' => 10000, 'max' => 55000, 'type' => 'deposit', 'method' => 'ACH'],
    'YouTube Partner Program / Adsense' => ['min' => 15000, 'max' => 85000, 'type' => 'deposit', 'method' => 'ACH'],
    'Stripe / Business Settlement' => ['min' => 20000, 'max' => 165000, 'type' => 'deposit', 'method' => 'ACH'],
    // --- Real Estate & General High Income ---
    'Real Estate Sale / Escrow Settlement' => ['min' => 150000, 'max' => 2500000, 'type' => 'deposit', 'method' => 'Wire'],
    'IRS Tax Refund' => ['min' => 8000, 'max' => 45000, 'type' => 'deposit', 'method' => 'ACH'],
    'MEMBER TRANSFER TO SAVINGS' => ['min' => 5000, 'max' => 35000, 'type' => 'fund_transfer', 'method' => 'Internal', 'transfer_type' => 'own_bank_transfer'],
];

$remoteDepositVendors = [
    ['amount_min' => 5000, 'amount_max' => 50000, 'account' => 'Checking', 'type' => 'checking'],
    ['amount_min' => 10000, 'amount_max' => 250000, 'account' => 'Savings', 'type' => 'savings'],
];

function generateTnx() { return 'TRX' . strtoupper(substr(md5(uniqid()), 0, 10)); }

$transactions = [];
$remoteDeposits = [];

for ($i = 0; $i < $totalEntries; $i++) {
    $rand = rand(1, 100);
    $date = date("Y-m-d H:i:s", rand(strtotime($startDate), strtotime($endDate)));
    
    if ($rand <= 20) { 
        $key = array_rand($highValueTypes);
        $conf = $highValueTypes[$key];
    } elseif ($rand <= 35) { // Remote deposits
        $conf = $remoteDepositVendors[array_rand($remoteDepositVendors)];
        $amount = number_format(rand($conf['amount_min'] * 100, $conf['amount_max'] * 100) / 100, 2, '.', '');
        $status = (rand(1, 10) > 1) ? 'approved' : 'pending';
        
        $remoteDeposits[] = [
            'amount' => $amount,
            'front_image' => 'assets/global/images/'.['dPRvsvDOYKvSpZxv5v2d.jpeg','zNjuO2j8WwsAITAFTHrV6L8tz18N8Pv2fvuwZSMT.png'][rand(0,1)],
            'back_image' => 'assets/global/images/'.['iyNrvi2xrsARYoV6tTEr.jpeg','xtUz7qGjgfPnfqBMXaIFwpMXjcXyYnovn6FiUyOf.png'][rand(0,1)],
            'status' => $status,
            'account_name' => $conf['account'],
            'type' => $conf['type'],
            'created_at' => $date,
            'updated_at' => $date
        ];

        // Sync with standard history
        if ($status == 'approved') {
            $transactions[] = [
                'tnx' => generateTnx(),
                'description' => "Remote Deposit - Mobile App",
                'amount' => $amount,
                'type' => 'deposit',
                'final_amount' => $amount,
                'method' => 'Mobile',
                'wallet_type' => ($conf['type'] == 'savings') ? 'primary_savings' : 'default',
                'status' => 'success',
                'transfer_type' => null,
                'created_at' => $date,
                'updated_at' => $date
            ];
        }
        continue;
    } else { 
        $key = array_rand($vendors);
        $conf = $vendors[$key];
    }

    $amount = number_format(rand($conf['min'] * 100, $conf['max'] * 100) / 100, 2, '.', '');
    $type = $conf['type'];
    $method = $conf['method'];
    $transfer_type = $conf['transfer_type'] ?? NULL;
    $walletType = (rand(1, 10) > 6) ? 'primary_savings' : 'default'; 
    
    $transactions[] = [
        'tnx' => generateTnx(),
        'description' => $key,
        'amount' => $amount,
        'type' => $type,
        'final_amount' => $amount,
        'method' => $method,
        'wallet_type' => $walletType,
        'status' => 'success',
        'transfer_type' => $transfer_type,
        'created_at' => $date,
        'updated_at' => $date
    ];
}

usort($transactions, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });
usort($remoteDeposits, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });

// Enforce no remote deposits in top 15
$changesMade = true;
while ($changesMade) {
    $changesMade = false;
    for ($i = 0; $i < 15 && $i < count($transactions); $i++) {
        if ($transactions[$i]['method'] === 'Mobile') {
            for ($j = 15; $j < count($transactions); $j++) {
                if ($transactions[$j]['method'] !== 'Mobile') {
                    $mobileDate = $transactions[$i]['created_at'];
                    $standardDate = $transactions[$j]['created_at'];
                    
                    $transactions[$i]['created_at'] = $standardDate;
                    $transactions[$i]['updated_at'] = $standardDate;
                    $transactions[$j]['created_at'] = $mobileDate;
                    $transactions[$j]['updated_at'] = $mobileDate;
                    
                    foreach ($remoteDeposits as &$rd) {
                        if ($rd['created_at'] === $mobileDate && $rd['amount'] === $transactions[$i]['amount']) {
                            $rd['created_at'] = $standardDate;
                            $rd['updated_at'] = $standardDate;
                            break;
                        }
                    }
                    unset($rd);
                    
                    usort($transactions, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });
                    usort($remoteDeposits, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });
                    
                    $changesMade = true;
                    break 2;
                }
            }
        }
    }
}

$sql = "SET @target_user_id = 2;\n\n";

// Transactions Block
$sql .= "INSERT INTO `transactions` (`user_id`, `from_user_id`, `from_model`, `target_id`, `target_type`, `is_level`, `tnx`, `description`, `amount`, `type`, `charge`, `final_amount`, `points`, `method`, `pay_currency`, `pay_amount`, `manual_field_data`, `wallet_type`, `card_id`, `approval_cause`, `status`, `transfer_type`, `beneficiery_id`, `bank_id`, `created_at`, `updated_at`, `action_message`, `purpose`) VALUES\n";
$rows = [];
foreach ($transactions as $t) {
    $desc = str_replace("'", "''", $t['description']);
    $rows[] = "(@target_user_id, NULL, 'User', NULL, NULL, '0', '{$t['tnx']}', '{$desc}', '{$t['amount']}', '{$t['type']}', '0', '{$t['final_amount']}', '0', '{$t['method']}', NULL, NULL, '[]', '{$t['wallet_type']}', NULL, NULL, '{$t['status']}', " . ($t['transfer_type'] ? "'{$t['transfer_type']}'" : "NULL") . ", NULL, NULL, '{$t['created_at']}', '{$t['updated_at']}', NULL, NULL)";
}
$sql .= implode(",\n", $rows) . ";\n\n";

// Remote Deposits Block (With subqueries for account numbers)
$sql .= "INSERT INTO `remote_deposits` (`user_id`, `amount`, `front_image`, `back_image`, `status`, `account_name`, `account_number`, `note`, `created_at`, `updated_at`) VALUES\n";
$rdRows = [];
foreach ($remoteDeposits as $r) {
    $accNumSubquery = ($r['type'] == 'savings') ? "(SELECT savings_account_number FROM users WHERE id = @target_user_id)" : "(SELECT account_number FROM users WHERE id = @target_user_id)";
    $rdRows[] = "(@target_user_id, '{$r['amount']}', '{$r['front_image']}', '{$r['back_image']}', '{$r['status']}', '{$r['account_name']}', $accNumSubquery, NULL, '{$r['created_at']}', '{$r['updated_at']}')";
}
$sql .= implode(",\n", $rdRows) . ";\n";

file_put_contents('user_crypto_history.sql', $sql);
echo "Generated Crypto Profile: " . count($transactions) . " txns, " . count($remoteDeposits) . " deposits.\n";
echo "Saved to: user_crypto_history.sql\n";
