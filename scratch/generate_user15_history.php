<?php
/**
 * Custom Transaction & Remote Deposit Generator for User 15
 * Timeline: Jan 2021 - June 2026 (5 Years Account Age)
 * Target: Business Person - Everyday Usage & High-Value Transactions
 */

$totalEntries = 750; // High frequency for 5 years
$startDate = "2021-01-15";
$endDate = "2026-06-08";

$targetUserId = 15;

$vendors = [
    // --- Everyday Spend ---
    'Amazon Purchase' => ['min' => 20, 'max' => 3500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Walmart Supercenter' => ['min' => 40, 'max' => 900, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Target Purchase' => ['min' => 30, 'max' => 700, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Costco Wholesale' => ['min' => 150, 'max' => 2200, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Whole Foods Market' => ['min' => 100, 'max' => 600, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Publix Grocery' => ['min' => 80, 'max' => 450, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Starbucks Purchase' => ['min' => 8, 'max' => 60, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'McDonald\'s' => ['min' => 10, 'max' => 50, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Chick-fil-A' => ['min' => 15, 'max' => 75, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Ruth\'s Chris Steak House' => ['min' => 200, 'max' => 900, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Uber Ride' => ['min' => 20, 'max' => 180, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Shell Gas Station' => ['min' => 50, 'max' => 150, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Chevron Gas' => ['min' => 45, 'max' => 140, 'type' => 'subtract', 'method' => 'Apple Pay'],
    
    // --- Utilities & Bills ---
    'Verizon Wireless' => ['min' => 150, 'max' => 650, 'type' => 'subtract', 'method' => 'ACH'],
    'T-Mobile' => ['min' => 100, 'max' => 450, 'type' => 'subtract', 'method' => 'ACH'],
    'Spectrum Cable/Internet' => ['min' => 80, 'max' => 300, 'type' => 'subtract', 'method' => 'ACH'],
    'FPL Electric' => ['min' => 200, 'max' => 850, 'type' => 'subtract', 'method' => 'ACH'],
    'Geico Auto Insurance' => ['min' => 200, 'max' => 1100, 'type' => 'subtract', 'method' => 'ACH'],

    // --- Travel & Leisure ---
    'Delta Airlines' => ['min' => 400, 'max' => 4500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'American Airlines' => ['min' => 500, 'max' => 5000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Marriott Hotels' => ['min' => 300, 'max' => 6000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Airbnb Booking' => ['min' => 500, 'max' => 8000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Apple Store Purchase' => ['min' => 500, 'max' => 6000, 'type' => 'subtract', 'method' => 'Apple Pay'],

    // --- Home Goods & Hardware ---
    'Home Depot' => ['min' => 100, 'max' => 5000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Best Buy' => ['min' => 200, 'max' => 7000, 'type' => 'subtract', 'method' => 'Apple Pay'],

    // --- Payouts / Refunds ---
    'Amazon / Refund' => ['min' => 30, 'max' => 1200, 'type' => 'deposit', 'method' => 'Debit Card'],
    'Zelle Transfer from Contact' => ['min' => 50, 'max' => 2500, 'type' => 'deposit', 'method' => 'Zelle'],
    'Venmo / Received' => ['min' => 20, 'max' => 2000, 'type' => 'deposit', 'method' => 'ACH'],
    'CashApp / Received' => ['min' => 10, 'max' => 1200, 'type' => 'deposit', 'method' => 'ACH'],
];

$highValueBusinessTypes = [
    // --- Business Deposits / Wire / Settlements ---
    'Stripe Invoice Settlement' => ['min' => 15000, 'max' => 120000, 'type' => 'deposit', 'method' => 'Wire'],
    'Square Daily Business Payout' => ['min' => 5000, 'max' => 45000, 'type' => 'deposit', 'method' => 'ACH'],
    'Consulting Client Monthly Retainer' => ['min' => 12000, 'max' => 75000, 'type' => 'deposit', 'method' => 'Wire'],
    'Business Capital / Equity Distribution' => ['min' => 100000, 'max' => 2500000, 'type' => 'deposit', 'method' => 'Wire'],
    'Real Estate Escrow Disbursement' => ['min' => 150000, 'max' => 1800000, 'type' => 'deposit', 'method' => 'Wire'],
    'Brokerage Account Withdrawal' => ['min' => 50000, 'max' => 850000, 'type' => 'deposit', 'method' => 'Wire'],

    // --- Business Outbound / High Value Spend ---
    'IRS Corporate Tax Payment' => ['min' => 20000, 'max' => 180000, 'type' => 'subtract', 'method' => 'ACH'],
    'Office Lease / Rent' => ['min' => 4500, 'max' => 18000, 'type' => 'subtract', 'method' => 'ACH'],
    'Software Stack / AWS Cloud Invoice' => ['min' => 2500, 'max' => 15000, 'type' => 'subtract', 'method' => 'ACH'],
    'Asset Management Buy-In' => ['min' => 100000, 'max' => 1500000, 'type' => 'subtract', 'method' => 'Wire'],
    'Luxury Vehicle Acquisition' => ['min' => 85000, 'max' => 220000, 'type' => 'subtract', 'method' => 'Wire'],
    'Marketing Agency Retainer' => ['min' => 8000, 'max' => 35000, 'type' => 'subtract', 'method' => 'ACH'],
];

$internalTransfers = [
    'MEMBER TRANSFER TO SAVINGS' => ['min' => 1000, 'max' => 25000, 'type' => 'fund_transfer', 'method' => 'Internal', 'transfer_type' => 'own_bank_transfer'],
    'MEMBER TRANSFER FROM SAVINGS' => ['min' => 500, 'max' => 15000, 'type' => 'fund_transfer', 'method' => 'Internal', 'transfer_type' => 'own_bank_transfer'],
];

$remoteDepositVendors = [
    ['amount_min' => 1500, 'amount_max' => 35000, 'account' => 'Checking', 'type' => 'checking'],
    ['amount_min' => 5000, 'amount_max' => 200000, 'account' => 'Savings', 'type' => 'savings'],
];

function generateTnx() { return 'TRX' . strtoupper(substr(md5(uniqid()), 0, 10)); }

$transactions = [];
$remoteDeposits = [];

for ($i = 0; $i < $totalEntries; $i++) {
    $rand = rand(1, 100);
    $date = date("Y-m-d H:i:s", rand(strtotime($startDate), strtotime($endDate)));
    
    if ($rand <= 30) { // Higher frequency of business transactions
        $key = array_rand($highValueBusinessTypes);
        $conf = $highValueBusinessTypes[$key];
    } elseif ($rand <= 40) {
        $key = array_rand($internalTransfers);
        $conf = $internalTransfers[$key];
    } elseif ($rand <= 50) {
        // Remote deposits representing client check deposits
        $conf = $remoteDepositVendors[array_rand($remoteDepositVendors)];
        $amount = number_format(rand($conf['amount_min'] * 100, $conf['amount_max'] * 100) / 100, 2, '.', '');
        $status = (rand(1, 15) > 1) ? 'approved' : 'pending';
        
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

        if ($status == 'approved') {
            $transactions[] = [
                'tnx' => generateTnx(),
                'description' => "Remote Deposit - Mobile Check Credit",
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
    
    // Distribute wallets between checking (default) and savings (primary_savings)
    $walletType = (rand(1, 10) > 7) ? 'primary_savings' : 'default';
    
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

// Sort by date (descending)
usort($transactions, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });
usort($remoteDeposits, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });

// Clean up: Ensure no remote deposits in top 15 (for aesthetic reasons)
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

// Build Output SQL
$sql = "SET @target_user_id = {$targetUserId};\n\n";
$sql .= "/* CLEANUP EXISTING DATA IF NEEDED */\n";
$sql .= "DELETE FROM `transactions` WHERE `user_id` = @target_user_id;\n";
$sql .= "DELETE FROM `remote_deposits` WHERE `user_id` = @target_user_id;\n\n";

// Write transactions
$sql .= "INSERT INTO `transactions` (`user_id`, `from_user_id`, `from_model`, `target_id`, `target_type`, `is_level`, `tnx`, `description`, `amount`, `type`, `charge`, `final_amount`, `points`, `method`, `pay_currency`, `pay_amount`, `manual_field_data`, `wallet_type`, `card_id`, `approval_cause`, `status`, `transfer_type`, `beneficiery_id`, `bank_id`, `created_at`, `updated_at`, `action_message`, `purpose`) VALUES\n";
$rows = [];
foreach ($transactions as $t) {
    $desc = str_replace("'", "''", $t['description']);
    $rows[] = "(@target_user_id, NULL, 'User', NULL, NULL, '0', '{$t['tnx']}', '{$desc}', '{$t['amount']}', '{$t['type']}', '0', '{$t['final_amount']}', '0', '{$t['method']}', NULL, NULL, '[]', '{$t['wallet_type']}', NULL, NULL, '{$t['status']}', " . ($t['transfer_type'] ? "'{$t['transfer_type']}'" : "NULL") . ", NULL, NULL, '{$t['created_at']}', '{$t['updated_at']}', NULL, NULL)";
}
$sql .= implode(",\n", $rows) . ";\n\n";

// Write remote deposits
$sql .= "INSERT INTO `remote_deposits` (`user_id`, `amount`, `front_image`, `back_image`, `status`, `account_name`, `account_number`, `note`, `created_at`, `updated_at`) VALUES\n";
$rdRows = [];
foreach ($remoteDeposits as $r) {
    $accNumSubquery = ($r['type'] == 'savings') ? "(SELECT savings_account_number FROM users WHERE id = @target_user_id)" : "(SELECT account_number FROM users WHERE id = @target_user_id)";
    $rdRows[] = "(@target_user_id, '{$r['amount']}', '{$r['front_image']}', '{$r['back_image']}', '{$r['status']}', '{$r['account_name']}', $accNumSubquery, NULL, '{$r['created_at']}', '{$r['updated_at']}')";
}
$sql .= implode(",\n", $rdRows) . ";\n";

$outputPath = dirname(__DIR__) . '/database/user15_history.sql';
file_put_contents($outputPath, $sql);
echo "Success: Generated " . count($transactions) . " transactions and " . count($remoteDeposits) . " remote deposits for User 15.\n";
echo "Output saved to: database/user15_history.sql\n";
