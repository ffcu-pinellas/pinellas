<?php
/**
 * Custom High-Net-Worth Real Estate History Generator for User 10
 * Range: 2013-01-01 to 2024-12-31
 * Target Balance: $6M - $11M (40/60 Split)
 */

$targetUserId = 10;
$totalEntries = 2500;
$startDateString = "2013-01-01";
$endDateString = "2024-12-31";

// Target Final Balance Range
$targetTotalMin = 6000000;
$targetTotalMax = 11000000;
$finalTarget = rand($targetTotalMin, $targetTotalMax);

// Ratios
$checkingRatio = 0.40;
$savingsRatio = 0.60;

$targetChecking = $finalTarget * $checkingRatio;
$targetSavings = $finalTarget * $savingsRatio;

// Massively expanded vendor list to minimize repetition
$vendors = [
    // Industry & Tech
    'Zillow Premier Agent - Region HUB' => ['min' => 500, 'max' => 5000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Redfin Partner Fee - Austin Office' => ['min' => 200, 'max' => 1500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Luxury Staging Co - Interior Design' => ['min' => 1200, 'max' => 15000, 'type' => 'subtract', 'method' => 'Wire'],
    'RE/MAX Franchise Dues - Q'.rand(1,4) => ['min' => 800, 'max' => 2500, 'type' => 'subtract', 'method' => 'ACH'],
    'MLS Access Fee - Florida Chapter' => ['min' => 150, 'max' => 450, 'type' => 'subtract', 'method' => 'ACH'],
    'Salesforce CRM - Annual Subscription' => ['min' => 2500, 'max' => 8500, 'type' => 'subtract', 'method' => 'Wire'],
    'Slack Technologies - Team Plan' => ['min' => 45, 'max' => 180, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Docusign / Annual Pro Plan' => ['min' => 250, 'max' => 480, 'type' => 'subtract', 'method' => 'Debit Card'],
    
    // Luxury Dining & Lifestyle (Multi-State)
    'Le Bernardin - NYC Dining' => ['min' => 350, 'max' => 1200, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Nobu - Malibu Waterfront' => ['min' => 250, 'max' => 850, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Franklin BBQ - Austin TX' => ['min' => 80, 'max' => 250, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Joe\'s Stone Crab - Miami Beach' => ['min' => 150, 'max' => 600, 'type' => 'subtract', 'method' => 'Debit Card'],
    'The Capital Grille - Dallas' => ['min' => 150, 'max' => 850, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Starbucks Reserve - Seattle SODO' => ['min' => 10, 'max' => 55, 'type' => 'subtract', 'method' => 'Apple Pay'],
    
    // Travel & Logistics
    'NetJets - Fractional Lease Payout' => ['min' => 15000, 'max' => 85000, 'type' => 'subtract', 'method' => 'Wire'],
    'Wheels Up - Flight Hours Deposit' => ['min' => 25000, 'max' => 65000, 'type' => 'subtract', 'method' => 'Wire'],
    'American Express Travel - Global' => ['min' => 1200, 'max' => 15000, 'type' => 'subtract', 'method' => 'Wire'],
    'Hertz Gold - LAX Terminal' => ['min' => 120, 'max' => 850, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Marriott Bonvoy - Elite Stay' => ['min' => 800, 'max' => 4500, 'type' => 'subtract', 'method' => 'ACH'],
    'Four Seasons - Beverly Hills' => ['min' => 1500, 'max' => 6500, 'type' => 'subtract', 'method' => 'Wire'],
    
    // Retail & High-End
    'Tiffany & Co - Houston Gallery' => ['min' => 1200, 'max' => 12000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Cartier - Design District Miami' => ['min' => 2500, 'max' => 35000, 'type' => 'subtract', 'method' => 'Wire'],
    'Neiman Marcus - NorthPark Dallas' => ['min' => 400, 'max' => 5500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Whole Foods Market - Austin Central' => ['min' => 120, 'max' => 850, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Tesla Supercharger - CA Network' => ['min' => 20, 'max' => 70, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Mercedes-Benz Financial Services' => ['min' => 1200, 'max' => 3200, 'type' => 'subtract', 'method' => 'ACH'],
    'Apple Store - Regent Street' => ['min' => 200, 'max' => 6000, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Amazon Luxury - Marketplace' => ['min' => 100, 'max' => 3000, 'type' => 'subtract', 'method' => 'Debit Card'],
    
    // Regional Utilities & Boards
    'Sunpass Auto-Replenish - Florida' => ['min' => 20, 'max' => 150, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Stewart Title / Austin Regional (TX)' => ['min' => 600, 'max' => 4500, 'type' => 'subtract', 'method' => 'Wire'],
    'Ebby Halliday / Dallas North (TX)' => ['min' => 400, 'max' => 1200, 'type' => 'subtract', 'method' => 'ACH'],
    'Harry Norman Realtors / Buckhead (GA)' => ['min' => 150, 'max' => 850, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'St. Petersburg Board of Realtors' => ['min' => 400, 'max' => 1200, 'type' => 'subtract', 'method' => 'ACH'],
    'FPL Electric - Regional Site' => ['min' => 150, 'max' => 550, 'type' => 'subtract', 'method' => 'ACH'],
];

$highValueTypes = [
    'Premier Sotheby\'s / 30A Beachfront Closing' => ['min' => 250000, 'max' => 1200000, 'type' => 'deposit', 'method' => 'Wire'],
    'Ansley Real Estate / ATL Intown Milestone' => ['min' => 85000, 'max' => 450000, 'type' => 'deposit', 'method' => 'Wire'],
    'Briggs Freeman / Sotheby\'s TX Payout' => ['min' => 45000, 'max' => 350000, 'type' => 'deposit', 'method' => 'Wire'],
    'Compass California / Carmel Coast Closing' => ['min' => 350000, 'max' => 2500000, 'type' => 'deposit', 'method' => 'Wire'],
    'Knight Frank / London Referral Fee - Int' => ['min' => 25000, 'max' => 120000, 'type' => 'deposit', 'method' => 'Wire'],
    'The Agency / Aspen Resort Payout' => ['min' => 120000, 'max' => 850000, 'type' => 'deposit', 'method' => 'Wire'],
    'Old Republic Title / Escrow Distribution' => ['min' => 45000, 'max' => 250000, 'type' => 'deposit', 'method' => 'Wire'],
    'Property Acquisition / 1400 Ocean Dr Deposit' => ['min' => 500000, 'max' => 3500000, 'type' => 'subtract', 'method' => 'Wire'],
    'Commercial Property Acquisition - Suite 800' => ['min' => 250000, 'max' => 1200000, 'type' => 'subtract', 'method' => 'Wire'],
    'IRS Business Quarterly Tax - Fed Pay' => ['min' => 45000, 'max' => 285000, 'type' => 'subtract', 'method' => 'Wire'],
];

$incomeTypes = [
    'Residential Commission - Estate #'.rand(100,999) => ['min' => 15000, 'max' => 85000, 'type' => 'deposit', 'method' => 'ACH'],
    'Rental Portfolio / Luxury Multi-Unit (FL)' => ['min' => 12000, 'max' => 45000, 'type' => 'deposit', 'method' => 'ACH'],
    'Commercial Management / Warehouse Payout' => ['min' => 8000, 'max' => 22000, 'type' => 'deposit', 'method' => 'ACH'],
    'Property Dividend / Q'.rand(1,4).' Distribution' => ['min' => 5000, 'max' => 18000, 'type' => 'deposit', 'method' => 'ACH'],
    'MEMBER TRANSFER TO SAVINGS' => ['min' => 10000, 'max' => 50000, 'type' => 'fund_transfer', 'method' => 'Internal', 'transfer_type' => 'own_bank_transfer'],
];

function generateTnx() { return 'TRX' . strtoupper(substr(md5(uniqid()), 0, 10)); }

$transactions = [];
$netChecking = 0;
$netSavings = 0;

// Step 1: Generate Transactions
for ($i = 0; $i < $totalEntries; $i++) {
    $rand = rand(1, 100);
    $date = date("Y-m-d H:i:s", rand(strtotime($startDateString), strtotime($endDateString)));
    
    if ($rand <= 35) { 
        $key = array_rand($highValueTypes);
        $conf = $highValueTypes[$key];
    } elseif ($rand <= 55) {
        $key = array_rand($incomeTypes);
        $conf = $incomeTypes[$key];
    } else {
        $key = array_rand($vendors);
        $conf = $vendors[$key];
    }

    $amount = rand($conf['min'] * 100, $conf['max'] * 100) / 100;
    
    // Smooth out large amounts for realistic flow
    if ($amount > 500000 && rand(1, 10) > 4) {
        $amount = $amount / 2;
    }

    $type = $conf['type'];
    $walletType = (rand(1, 10) > 7) ? 'primary_savings' : 'default';

    // Handle Transfer Logic
    if ($type === 'fund_transfer') {
        $netChecking -= $amount;
        $netSavings += $amount;
        $typeVal = 'subtract';
    } else {
        if ($walletType === 'default') {
            $netChecking += ($type === 'deposit' ? $amount : -$amount);
        } else {
            $netSavings += ($type === 'deposit' ? $amount : -$amount);
        }
        $typeVal = $type;
    }

    $transactions[] = [
        'tnx' => generateTnx(),
        'description' => $key,
        'amount' => number_format($amount, 2, '.', ''),
        'type' => $typeVal,
        'method' => $conf['method'],
        'wallet_type' => $walletType,
        'transfer_type' => ($type === 'fund_transfer' ? 'own_bank_transfer' : null),
        'created_at' => $date
    ];
}

// Step 2: Calculate precise initial balances to hit targets
// target = opening + net  => opening = target - net
$openingChecking = $targetChecking - $netChecking;
$openingSavings = $targetSavings - $netSavings;

// If opening balance is negative, we need to add a "seed" deposit at the start of 2013
if ($openingChecking < 100000) {
    $seed = abs($openingChecking) + rand(500000, 1000000);
    $openingChecking += $seed;
    $date = "2013-01-01 09:00:00";
    array_unshift($transactions, [
        'tnx' => generateTnx(),
        'description' => 'Opening Balance Carry-forward',
        'amount' => number_format($seed, 2, '.', ''),
        'type' => 'deposit',
        'method' => 'ACH',
        'wallet_type' => 'default',
        'transfer_type' => null,
        'created_at' => $date
    ]);
    $netChecking += $seed;
}

if ($openingSavings < 500000) {
    $seed = abs($openingSavings) + rand(1000000, 2000000);
    $openingSavings += $seed;
    $date = "2013-01-01 09:05:00";
    array_unshift($transactions, [
        'tnx' => generateTnx(),
        'description' => 'Initial Savings Seed',
        'amount' => number_format($seed, 2, '.', ''),
        'type' => 'deposit',
        'method' => 'ACH',
        'wallet_type' => 'primary_savings',
        'transfer_type' => null,
        'created_at' => $date
    ]);
    $netSavings += $seed;
}

// Final precision adjustment
$openingChecking = $targetChecking - $netChecking;
$openingSavings = $targetSavings - $netSavings;

$finalCheckingVal = $openingChecking + $netChecking;
$finalSavingsVal = $openingSavings + $netSavings;

usort($transactions, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });

$sql = "SET @target_user_id = {$targetUserId};\n\n";

// Update initial balance
$sql .= "UPDATE users SET balance = '{$finalCheckingVal}', savings_balance = '{$finalSavingsVal}' WHERE id = @target_user_id;\n\n";

$sql .= "INSERT INTO `transactions` (`user_id`, `from_user_id`, `from_model`, `tnx`, `description`, `amount`, `type`, `final_amount`, `method`, `wallet_type`, `status`, `transfer_type`, `created_at`, `updated_at`) VALUES\n";
$rows = [];
foreach ($transactions as $t) {
    $desc = str_replace("'", "''", $t['description']);
    $rows[] = "(@target_user_id, NULL, 'User', '{$t['tnx']}', '{$desc}', '{$t['amount']}', '{$t['type']}', '{$t['amount']}', '{$t['method']}', '{$t['wallet_type']}', 'success', " . ($t['transfer_type'] ? "'{$t['transfer_type']}'" : "NULL") . ", '{$t['created_at']}', '{$t['created_at']}')";
}
$sql .= implode(",\n", $rows) . ";\n";

file_put_contents('real_estate_user10_v2.sql', $sql);
echo "Generated User 10 Profile: " . count($transactions) . " txns.\n";
echo "Target Final: " . number_format($finalTarget, 2) . "\n";
echo "Actual Checking: " . number_format($finalCheckingVal, 2) . "\n";
echo "Actual Savings: " . number_format($finalSavingsVal, 2) . "\n";
echo "Saved to: real_estate_user10_v2.sql\n";
