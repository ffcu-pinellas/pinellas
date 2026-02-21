<?php
/**
 * Advanced Transaction & Remote Deposit Generator
 * Timeline: Jan 2023 - March 2026
 * Features: SQL Variables, High-Value Biasing, Table Separation
 */

$totalEntries = 420; // Increased to cover longer timeline
$startDate = "2023-01-01";
$endDate = "2026-03-31";

$vendors = [
    'Amazon Purchase' => ['min' => 10, 'max' => 2500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Apple Pay Purchase' => ['min' => 5, 'max' => 1500, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'PayPal Purchase' => ['min' => 20, 'max' => 3000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Starbucks Purchase' => ['min' => 4, 'max' => 40, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Netflix Purchase' => ['min' => 15, 'max' => 20, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Spotify Purchase' => ['min' => 9, 'max' => 15, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Uber Purchase' => ['min' => 15, 'max' => 120, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Lyft Purchase' => ['min' => 10, 'max' => 80, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Shell Gas Purchase' => ['min' => 40, 'max' => 120, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Chevron Purchase' => ['min' => 35, 'max' => 110, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Exxon Purchase' => ['min' => 45, 'max' => 130, 'type' => 'subtract', 'method' => 'Debit Card'],
    'McDonald\'s Purchase' => ['min' => 8, 'max' => 45, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Domino\'s Purchase' => ['min' => 20, 'max' => 60, 'type' => 'subtract', 'method' => 'Debit Card'],
    'KFC Purchase' => ['min' => 15, 'max' => 50, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Pizza Hut Purchase' => ['min' => 25, 'max' => 70, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Burger King Purchase' => ['min' => 12, 'max' => 40, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Walmart Purchase' => ['min' => 20, 'max' => 800, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Target Purchase' => ['min' => 15, 'max' => 600, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Home Depot Purchase' => ['min' => 50, 'max' => 3000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Best Buy Purchase' => ['min' => 100, 'max' => 5000, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Nike Purchase' => ['min' => 80, 'max' => 500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Adidas Purchase' => ['min' => 70, 'max' => 400, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Delta Airlines Purchase' => ['min' => 200, 'max' => 2500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'United Airlines Purchase' => ['min' => 180, 'max' => 2200, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'American Airlines Purchase' => ['min' => 250, 'max' => 3000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Expedia Purchase' => ['min' => 100, 'max' => 4000, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Airbnb Purchase' => ['min' => 300, 'max' => 5000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Hilton Purchase' => ['min' => 200, 'max' => 3500, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'AT&T Purchase' => ['min' => 80, 'max' => 400, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Verizon Purchase' => ['min' => 90, 'max' => 450, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'T-Mobile Purchase' => ['min' => 70, 'max' => 350, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Spectrum Purchase' => ['min' => 60, 'max' => 220, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'FPL Purchase' => ['min' => 120, 'max' => 600, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Duke Energy Purchase' => ['min' => 130, 'max' => 650, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Zelle Transfer from Contact' => ['min' => 20, 'max' => 2000, 'type' => 'deposit', 'method' => 'Zelle'],
    'Venmo Purchase' => ['min' => 10, 'max' => 1000, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'CashApp Purchase' => ['min' => 5, 'max' => 500, 'type' => 'subtract', 'method' => 'Debit Card'],
];

$highValueTypes = [
    'Real Estate Investment / Wire Transfer' => ['min' => 100000, 'max' => 2000000, 'type' => 'fund_transfer', 'method' => 'Wire', 'transfer_type' => 'wire_transfer'],
    'Business Capital / Equity Distribution' => ['min' => 250000, 'max' => 1500000, 'type' => 'deposit', 'method' => 'Wire', 'transfer_type' => null],
    'Asset Management Buy-In' => ['min' => 500000, 'max' => 1800000, 'type' => 'fund_transfer', 'method' => 'Wire', 'transfer_type' => 'wire_transfer'],
    'Luxury Vehicle Acquisition' => ['min' => 85000, 'max' => 150000, 'type' => 'subtract', 'method' => 'ACH', 'transfer_type' => null],
];

$incomeTypes = [
    'Payroll Deposit' => ['min' => 3500, 'max' => 9500, 'type' => 'deposit', 'method' => 'ACH'],
    'MEMBER TRANSFER TO FAMILY' => ['min' => 100, 'max' => 5000, 'type' => 'fund_transfer', 'method' => 'Internal', 'transfer_type' => 'own_bank_transfer'],
    'EXTERNAL TRANSFER FROM OTHER BANK' => ['min' => 1000, 'max' => 25000, 'type' => 'deposit', 'method' => 'Wire', 'transfer_type' => 'other_bank_transfer'],
];

$remoteDepositVendors = [
    ['amount_min' => 500, 'amount_max' => 20000, 'account' => 'Checking', 'acc_num' => '0212797369'],
    ['amount_min' => 1000, 'amount_max' => 150000, 'account' => 'Savings', 'acc_num' => '427026051'],
];

function generateTnx() { return 'TRX' . strtoupper(substr(md5(uniqid()), 0, 10)); }
function generateRD() { return 'RD-' . strtoupper(substr(md5(uniqid()), 0, 10)); }

$transactions = [];
$remoteDeposits = [];

for ($i = 0; $i < $totalEntries; $i++) {
    $rand = rand(1, 100);
    $date = date("Y-m-d H:i:s", rand(strtotime($startDate), strtotime($endDate)));
    
    // 5-10% increase in high value (let's say 12% total chance for high value)
    if ($rand <= 12) {
        $key = array_rand($highValueTypes);
        $conf = $highValueTypes[$key];
    } elseif ($rand <= 25) {
        $key = array_rand($incomeTypes);
        $conf = $incomeTypes[$key];
    } elseif ($rand <= 35) {
        // Remote Deposit Path (Table Separation)
        $conf = $remoteDepositVendors[array_rand($remoteDepositVendors)];
        $amount = number_format(rand($conf['amount_min'] * 100, $conf['amount_max'] * 100) / 100, 2, '.', '');
        $status = (rand(1, 10) > 2) ? 'approved' : ((rand(1, 2) == 1) ? 'pending' : 'rejected');
        
        $remoteDeposits[] = [
            'amount' => $amount,
            'front_image' => 'assets/global/images/'.['dPRvsvDOYKvSpZxv5v2d.jpeg','zNjuO2j8WwsAITAFTHrV6L8tz18N8Pv2fvuwZSMT.png'][rand(0,1)],
            'back_image' => 'assets/global/images/'.['iyNrvi2xrsARYoV6tTEr.jpeg','xtUz7qGjgfPnfqBMXaIFwpMXjcXyYnovn6FiUyOf.png'][rand(0,1)],
            'status' => $status,
            'account_name' => $conf['account'],
            'account_number' => $conf['acc_num'],
            'note' => ($status == 'rejected') ? 'Rejected by system audit' : NULL,
            'created_at' => $date,
            'updated_at' => $date
        ];
        continue; // Skip standard transaction table for these
    } else {
        $key = array_rand($vendors);
        $conf = $vendors[$key];
    }

    $amount = number_format(rand($conf['min'] * 100, $conf['max'] * 100) / 100, 2, '.', '');
    $type = $conf['type'];
    $method = $conf['method'];
    $transfer_type = $conf['transfer_type'] ?? NULL;
    $walletType = (rand(1, 10) > 7) ? 'primary_savings' : 'default';
    
    $transactions[] = [
        'tnx' => generateTnx(),
        'description' => $key,
        'amount' => $amount,
        'type' => $type,
        'final_amount' => $amount,
        'method' => $method,
        'wallet_type' => $walletType,
        'status' => (rand(1, 20) > 1) ? 'success' : 'failed',
        'transfer_type' => $transfer_type,
        'created_at' => $date,
        'updated_at' => $date
    ];
}

// Sorting
usort($transactions, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });
usort($remoteDeposits, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });

// Final SQL Build
$sql = "SET @target_user_id = 2;\n\n";
$sql .= "/* CLEANUP EXISTING DATA IF NEEDED */\n";
$sql .= "-- DELETE FROM `transactions` WHERE `user_id` = @target_user_id;\n";
$sql .= "-- DELETE FROM `remote_deposits` WHERE `user_id` = @target_user_id;\n\n";

// Transactions Block
$sql .= "INSERT INTO `transactions` (`user_id`, `from_user_id`, `from_model`, `target_id`, `target_type`, `is_level`, `tnx`, `description`, `amount`, `type`, `charge`, `final_amount`, `points`, `method`, `pay_currency`, `pay_amount`, `manual_field_data`, `wallet_type`, `card_id`, `approval_cause`, `status`, `transfer_type`, `beneficiery_id`, `bank_id`, `created_at`, `updated_at`, `action_message`, `purpose`) VALUES\n";
$rows = [];
foreach ($transactions as $t) {
    $desc = str_replace("'", "''", $t['description']);
    $rows[] = "(@target_user_id, NULL, 'User', NULL, NULL, '0', '{$t['tnx']}', '{$desc}', '{$t['amount']}', '{$t['type']}', '0', '{$t['final_amount']}', '0', '{$t['method']}', NULL, NULL, '[]', '{$t['wallet_type']}', NULL, NULL, '{$t['status']}', " . ($t['transfer_type'] ? "'{$t['transfer_type']}'" : "NULL") . ", NULL, NULL, '{$t['created_at']}', '{$t['updated_at']}', NULL, NULL)";
}
$sql .= implode(",\n", $rows) . ";\n\n";

// Remote Deposits Block
$sql .= "INSERT INTO `remote_deposits` (`user_id`, `amount`, `front_image`, `back_image`, `status`, `account_name`, `account_number`, `note`, `created_at`, `updated_at`) VALUES\n";
$rdRows = [];
foreach ($remoteDeposits as $r) {
    $note = $r['note'] ? "'{$r['note']}'" : "NULL";
    $rdRows[] = "(@target_user_id, '{$r['amount']}', '{$r['front_image']}', '{$r['back_image']}', '{$r['status']}', '{$r['account_name']}', '{$r['account_number']}', $note, '{$r['created_at']}', '{$r['updated_at']}')";
}
$sql .= implode(",\n", $rdRows) . ";\n";

file_put_contents('user_history_split.sql', $sql);
echo "Finished! Generated " . count($transactions) . " standard transactions and " . count($remoteDeposits) . " remote deposits.\n";
echo "Output saved to: user_history_split.sql\n";
