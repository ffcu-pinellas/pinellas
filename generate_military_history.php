<?php
/**
 * Military Transaction & Remote Deposit Generator
 * Timeline: Jan 2023 - March 2026
 * Profile: Military Citizen (E-6 to O-3 Rank)
 */

$totalEntries = 380;
$startDate = "2023-01-01";
$endDate = "2026-03-31";

$vendors = [
    'DECA COMMISSARY Purchase' => ['min' => 45, 'max' => 350, 'type' => 'subtract', 'method' => 'Debit Card'],
    'AAFES PX/BX Purchase' => ['min' => 10, 'max' => 500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'AAFES GAS STATION' => ['min' => 35, 'max' => 110, 'type' => 'subtract', 'method' => 'Debit Card'],
    'NAVY FEDERAL ATM WD' => ['min' => 20, 'max' => 200, 'type' => 'subtract', 'method' => 'Debit Card'],
    'USAA INSURANCE PREMIUM' => ['min' => 120, 'max' => 280, 'type' => 'subtract', 'method' => 'ACH'],
    'PATRIOT EXPRESS TRAVEL' => ['min' => 50, 'max' => 1200, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Starbucks Purchase' => ['min' => 5, 'max' => 25, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Amazon Purchase' => ['min' => 10, 'max' => 400, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Netflix Purchase' => ['min' => 15, 'max' => 20, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Disney+ Purchase' => ['min' => 8, 'max' => 15, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Spotify Purchase' => ['min' => 10, 'max' => 15, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'T-Mobile Purchase' => ['min' => 80, 'max' => 220, 'type' => 'subtract', 'method' => 'ACH'],
    'Base Housing Utility' => ['min' => 50, 'max' => 150, 'type' => 'subtract', 'method' => 'ACH'],
    'MCCS MARINE MART' => ['min' => 5, 'max' => 60, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Dunkin Donuts Purchase' => ['min' => 4, 'max' => 30, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Home Depot Purchase' => ['min' => 20, 'max' => 450, 'type' => 'subtract', 'method' => 'Debit Card'],
];

$highValueTypes = [
    'DFAS RE-ENLISTMENT BONUS' => ['min' => 10000, 'max' => 40000, 'type' => 'deposit', 'method' => 'ACH', 'transfer_type' => null],
    'VA DISABILITY BACKPAY' => ['min' => 5000, 'max' => 15000, 'type' => 'deposit', 'method' => 'ACH', 'transfer_type' => null],
    'TSP DISTRIBUTION WITHDRAWAL' => ['min' => 20000, 'max' => 60000, 'type' => 'deposit', 'method' => 'Wire', 'transfer_type' => 'other_bank_transfer'],
    'PCS MOVING REIMBURSEMENT' => ['min' => 2000, 'max' => 8000, 'type' => 'deposit', 'method' => 'ACH', 'transfer_type' => null],
];

$incomeTypes = [
    'DFAS-IN FED SALARY' => ['min' => 2800, 'max' => 4200, 'type' => 'deposit', 'method' => 'ACH'],
    'DFAS-MIL PAY' => ['min' => 2500, 'max' => 3800, 'type' => 'deposit', 'method' => 'ACH'],
    'BAH HOUSING ALLOWANCE' => ['min' => 1500, 'max' => 3200, 'type' => 'deposit', 'method' => 'ACH'],
];

$remoteDepositVendors = [
    ['amount_min' => 100, 'amount_max' => 1500, 'account' => 'Checking', 'acc_num' => '0212797369'],
    ['amount_min' => 500, 'amount_max' => 5000, 'account' => 'Savings', 'acc_num' => '427026051'],
];

function generateTnx() { return 'TRX' . strtoupper(substr(md5(uniqid()), 0, 10)); }

$transactions = [];
$remoteDeposits = [];

for ($i = 0; $i < $totalEntries; $i++) {
    $rand = rand(1, 100);
    $date = date("Y-m-d H:i:s", rand(strtotime($startDate), strtotime($endDate)));
    
    if ($rand <= 5) { // Rare high value
        $key = array_rand($highValueTypes);
        $conf = $highValueTypes[$key];
    } elseif ($rand <= 25) { // Regular income
        $key = array_rand($incomeTypes);
        $conf = $incomeTypes[$key];
    } elseif ($rand <= 30) { // Remote deposits
        $conf = $remoteDepositVendors[array_rand($remoteDepositVendors)];
        $amount = number_format(rand($conf['amount_min'] * 100, $conf['amount_max'] * 100) / 100, 2, '.', '');
        $status = (rand(1, 10) > 2) ? 'approved' : 'pending';
        
        $remoteDeposits[] = [
            'amount' => $amount,
            'front_image' => 'assets/global/images/'.['dPRvsvDOYKvSpZxv5v2d.jpeg','zNjuO2j8WwsAITAFTHrV6L8tz18N8Pv2fvuwZSMT.png'][rand(0,1)],
            'back_image' => 'assets/global/images/'.['iyNrvi2xrsARYoV6tTEr.jpeg','xtUz7qGjgfPnfqBMXaIFwpMXjcXyYnovn6FiUyOf.png'][rand(0,1)],
            'status' => $status,
            'account_name' => $conf['account'],
            'account_number' => $conf['acc_num'],
            'note' => NULL,
            'created_at' => $date,
            'updated_at' => $date
        ];
        continue;
    } else { // Standard daily spending
        $key = array_rand($vendors);
        $conf = $vendors[$key];
    }

    $amount = number_format(rand($conf['min'] * 100, $conf['max'] * 100) / 100, 2, '.', '');
    $type = $conf['type'];
    $method = $conf['method'];
    $transfer_type = $conf['transfer_type'] ?? NULL;
    $walletType = (rand(1, 10) > 8) ? 'primary_savings' : 'default';
    
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

$sql = "SET @target_user_id = 2;\n\n";
$sql .= "/* MILITARY PROFILE DATA */\n\n";

// Transactions
$sql .= "INSERT INTO `transactions` (`user_id`, `from_user_id`, `from_model`, `target_id`, `target_type`, `is_level`, `tnx`, `description`, `amount`, `type`, `charge`, `final_amount`, `points`, `method`, `pay_currency`, `pay_amount`, `manual_field_data`, `wallet_type`, `card_id`, `approval_cause`, `status`, `transfer_type`, `beneficiery_id`, `bank_id`, `created_at`, `updated_at`, `action_message`, `purpose`) VALUES\n";
$rows = [];
foreach ($transactions as $t) {
    $desc = str_replace("'", "''", $t['description']);
    $rows[] = "(@target_user_id, NULL, 'User', NULL, NULL, '0', '{$t['tnx']}', '{$desc}', '{$t['amount']}', '{$t['type']}', '0', '{$t['final_amount']}', '0', '{$t['method']}', NULL, NULL, '[]', '{$t['wallet_type']}', NULL, NULL, '{$t['status']}', " . ($t['transfer_type'] ? "'{$t['transfer_type']}'" : "NULL") . ", NULL, NULL, '{$t['created_at']}', '{$t['updated_at']}', NULL, NULL)";
}
$sql .= implode(",\n", $rows) . ";\n\n";

// Remote Deposits
$sql .= "INSERT INTO `remote_deposits` (`user_id`, `amount`, `front_image`, `back_image`, `status`, `account_name`, `account_number`, `note`, `created_at`, `updated_at`) VALUES\n";
$rdRows = [];
foreach ($remoteDeposits as $r) {
    $rdRows[] = "(@target_user_id, '{$r['amount']}', '{$r['front_image']}', '{$r['back_image']}', '{$r['status']}', '{$r['account_name']}', '{$r['account_number']}', NULL, '{$r['created_at']}', '{$r['updated_at']}')";
}
$sql .= implode(",\n", $rdRows) . ";\n";

file_put_contents('user_military_history.sql', $sql);
echo "Generated Military Profile: " . count($transactions) . " txns, " . count($remoteDeposits) . " deposits.\n";
echo "Saved to: user_military_history.sql\n";
