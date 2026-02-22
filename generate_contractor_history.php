<?php
/**
 * Contractor Transaction & Remote Deposit Generator
 * Timeline: Jan 2023 - March 2026
 * Profile: Multi-state General Contractor
 */

$totalEntries = 420;
$startDate = "2023-01-01";
$endDate = "2026-03-31";

$vendors = [
    // Industry Specific
    'Home Depot Pro' => ['min' => 100, 'max' => 5000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Lowe\'s Business' => ['min' => 50, 'max' => 3500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Sunbelt Rentals' => ['min' => 200, 'max' => 12000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Sherwin-Williams' => ['min' => 80, 'max' => 2500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Grainger Industrial' => ['min' => 45, 'max' => 1800, 'type' => 'subtract', 'method' => 'Debit Card'],
    'United Rentals' => ['min' => 500, 'max' => 15000, 'type' => 'subtract', 'method' => 'Wire'],
    'Ferguson Plumbing' => ['min' => 150, 'max' => 6000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'ABC Supply Co' => ['min' => 1200, 'max' => 18000, 'type' => 'subtract', 'method' => 'Wire'],
    // Everyday General Usage
    'Shell Gas Station' => ['min' => 40, 'max' => 150, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Chevron Daily' => ['min' => 35, 'max' => 130, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Walmart Supercenter' => ['min' => 20, 'max' => 600, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Target Purchase' => ['min' => 15, 'max' => 400, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Publix Grocery' => ['min' => 50, 'max' => 300, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Costco Wholesale' => ['min' => 100, 'max' => 1200, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Amazon Market' => ['min' => 10, 'max' => 2000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Starbucks Coffee' => ['min' => 5, 'max' => 45, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Chick-fil-A' => ['min' => 12, 'max' => 60, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'McDonald\'s' => ['min' => 8, 'max' => 40, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Panera Bread' => ['min' => 15, 'max' => 55, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Netflix' => ['min' => 15, 'max' => 20, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Spotify' => ['min' => 10, 'max' => 15, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Disney+' => ['min' => 8, 'max' => 15, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Uber Ride' => ['min' => 15, 'max' => 100, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Lyft Ride' => ['min' => 10, 'max' => 80, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Verizon Wireless' => ['min' => 120, 'max' => 400, 'type' => 'subtract', 'method' => 'ACH'],
    'FPL Electric' => ['min' => 150, 'max' => 550, 'type' => 'subtract', 'method' => 'ACH'],
    'Geico Fleet Insurance' => ['min' => 800, 'max' => 3500, 'type' => 'subtract', 'method' => 'ACH'],
    // Incoming Vendor Transactions (Refunds/Small Payments)
    'Home Depot / Refund' => ['min' => 50, 'max' => 1200, 'type' => 'deposit', 'method' => 'Debit Card'],
    'Lowe\'s / Credit Return' => ['min' => 25, 'max' => 850, 'type' => 'deposit', 'method' => 'Debit Card'],
    'Sunbelt Rentals / Deposit Refund' => ['min' => 200, 'max' => 1500, 'type' => 'deposit', 'method' => 'Debit Card'],
    'Cash App / Client Payment' => ['min' => 100, 'max' => 2500, 'type' => 'deposit', 'method' => 'ACH'],
    'Venmo / Small Job Payout' => ['min' => 75, 'max' => 1800, 'type' => 'deposit', 'method' => 'ACH'],
    'Zelle / Service Call Fee' => ['min' => 150, 'max' => 450, 'type' => 'deposit', 'method' => 'ACH'],
];

$highValueTypes = [
    'Turner Construction / Progress Payment' => ['min' => 250000, 'max' => 850000, 'type' => 'deposit', 'method' => 'Wire'],
    'Bechtel Group / Project Milestone' => ['min' => 500000, 'max' => 1500000, 'type' => 'deposit', 'method' => 'Wire'],
    'US Army Corps of Engineers / Federal Award' => ['min' => 1000000, 'max' => 3500000, 'type' => 'deposit', 'method' => 'Wire'],
    'FEMA Hurricane Relief / Contract Milestone' => ['min' => 150000, 'max' => 650000, 'type' => 'deposit', 'method' => 'Wire'],
    'AECOM Technical Services / Payout' => ['min' => 75000, 'max' => 220000, 'type' => 'deposit', 'method' => 'ACH'],
    'Kiewit Corporation / Retention Release' => ['min' => 45000, 'max' => 125000, 'type' => 'deposit', 'method' => 'ACH'],
    'Subcontractor Payout - Otis Elevator' => ['min' => 50000, 'max' => 150000, 'type' => 'subtract', 'method' => 'Wire'],
    'Cat Rental Store / Heavy Equip Purchase' => ['min' => 120000, 'max' => 250000, 'type' => 'subtract', 'method' => 'Wire'],
    'Bulk Payment - Cemex Concrete' => ['min' => 25000, 'max' => 75000, 'type' => 'subtract', 'method' => 'ACH'],
    'Payroll Funding - ADP Corporate' => ['min' => 35000, 'max' => 95000, 'type' => 'subtract', 'method' => 'ACH'],
];

$incomeTypes = [
    'Residential Remodel Install - Private Client' => ['min' => 15000, 'max' => 45000, 'type' => 'deposit', 'method' => 'ACH'],
    'Architectural Site Survey / Consultation' => ['min' => 1500, 'max' => 8500, 'type' => 'deposit', 'method' => 'ACH'],
    'Vendor Rebate - Ferguson Enterprise' => ['min' => 500, 'max' => 3500, 'type' => 'deposit', 'method' => 'ACH'],
    'Equipment Sale - Used Skid Steer' => ['min' => 8000, 'max' => 25000, 'type' => 'deposit', 'method' => 'ACH'],
    'MEMBER TRANSFER TO SAVINGS' => ['min' => 2000, 'max' => 25000, 'type' => 'fund_transfer', 'method' => 'Internal', 'transfer_type' => 'own_bank_transfer'],
];

$remoteDepositVendors = [
    ['amount_min' => 2500, 'amount_max' => 45000, 'account' => 'Checking', 'type' => 'checking'],
    ['amount_min' => 5000, 'amount_max' => 100000, 'account' => 'Savings', 'type' => 'savings'],
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
    } elseif ($rand <= 40) {
        $key = array_rand($incomeTypes);
        $conf = $incomeTypes[$key];
    } elseif ($rand <= 50) {
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

        if ($status == 'approved') {
            $transactions[] = [
                'tnx' => generateTnx(),
                'description' => "Remote Deposit - Business Check",
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

$sql = "SET @target_user_id = 4;\n\n";

$sql .= "INSERT INTO `transactions` (`user_id`, `from_user_id`, `from_model`, `target_id`, `target_type`, `is_level`, `tnx`, `description`, `amount`, `type`, `charge`, `final_amount`, `points`, `method`, `pay_currency`, `pay_amount`, `manual_field_data`, `wallet_type`, `card_id`, `approval_cause`, `status`, `transfer_type`, `beneficiery_id`, `bank_id`, `created_at`, `updated_at`, `action_message`, `purpose`) VALUES\n";
$rows = [];
foreach ($transactions as $t) {
    $desc = str_replace("'", "''", $t['description']);
    $rows[] = "(@target_user_id, NULL, 'User', NULL, NULL, '0', '{$t['tnx']}', '{$desc}', '{$t['amount']}', '{$t['type']}', '0', '{$t['final_amount']}', '0', '{$t['method']}', NULL, NULL, '[]', '{$t['wallet_type']}', NULL, NULL, '{$t['status']}', " . ($t['transfer_type'] ? "'{$t['transfer_type']}'" : "NULL") . ", NULL, NULL, '{$t['created_at']}', '{$t['updated_at']}', NULL, NULL)";
}
$sql .= implode(",\n", $rows) . ";\n\n";

$sql .= "INSERT INTO `remote_deposits` (`user_id`, `amount`, `front_image`, `back_image`, `status`, `account_name`, `account_number`, `note`, `created_at`, `updated_at`) VALUES\n";
$rdRows = [];
foreach ($remoteDeposits as $r) {
    $accNumSubquery = ($r['type'] == 'savings') ? "(SELECT savings_account_number FROM users WHERE id = @target_user_id)" : "(SELECT account_number FROM users WHERE id = @target_user_id)";
    $rdRows[] = "(@target_user_id, '{$r['amount']}', '{$r['front_image']}', '{$r['back_image']}', '{$r['status']}', '{$r['account_name']}', $accNumSubquery, NULL, '{$r['created_at']}', '{$r['updated_at']}')";
}
$sql .= implode(",\n", $rdRows) . ";\n";

file_put_contents('contractor_user4_history.sql', $sql);
echo "Generated Contractor Profile: " . count($transactions) . " txns, " . count($remoteDeposits) . " deposits.\n";
echo "Saved to: contractor_user4_history.sql\n";
