<?php
/**
 * Crypto & Stock Trader History Generator
 * Timeline: Jan 2023 - March 2026
 * Profile: High Net Worth (Crypto/Stocks)
 * Range: $100k - $1.5M for large transfers
 */

$totalEntries = 450;
$startDate = "2023-01-01";
$endDate = "2026-03-31";

$vendors = [
    'Whole Foods Market' => ['min' => 120, 'max' => 850, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Apple Store Purchase' => ['min' => 200, 'max' => 12000, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Tesla Supercharger' => ['min' => 15, 'max' => 65, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Equinox Monthly Membership' => ['min' => 250, 'max' => 400, 'type' => 'subtract', 'method' => 'ACH'],
    'Peloton Interactive' => ['min' => 44, 'max' => 3500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Gucci Boutique' => ['min' => 500, 'max' => 8500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Nordstrom' => ['min' => 150, 'max' => 4500, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Ritz-Carlton Stay' => ['min' => 1200, 'max' => 15000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Starbucks Coffee' => ['min' => 6, 'max' => 40, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Amazon Prime' => ['min' => 15, 'max' => 1200, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Uber Black' => ['min' => 45, 'max' => 350, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'NetJets Deposit' => ['min' => 25000, 'max' => 100000, 'type' => 'subtract', 'method' => 'Wire'],
];

$highValueTypes = [
    'Coinbase Withdrawal' => ['min' => 100000, 'max' => 850000, 'type' => 'deposit', 'method' => 'ACH', 'transfer_type' => null],
    'Binance.US Disbursement' => ['min' => 250000, 'max' => 1500000, 'type' => 'deposit', 'method' => 'Wire', 'transfer_type' => 'other_bank_transfer'],
    'Charles Schwab Brokerage Transfer' => ['min' => 150000, 'max' => 1200000, 'type' => 'deposit', 'method' => 'Wire', 'transfer_type' => 'other_bank_transfer'],
    'Robinhood Settlement' => ['min' => 50000, 'max' => 300000, 'type' => 'deposit', 'method' => 'ACH', 'transfer_type' => null],
    'Fidelity Dividend Payment' => ['min' => 1000, 'max' => 15000, 'type' => 'deposit', 'method' => 'ACH', 'transfer_type' => null],
];

$remoteDepositVendors = [
    ['amount_min' => 5000, 'amount_max' => 50000, 'account' => 'Checking', 'acc_num' => '0212797369'],
    ['amount_min' => 10000, 'amount_max' => 250000, 'account' => 'Savings', 'acc_num' => '427026051'],
];

function generateTnx() { return 'TRX' . strtoupper(substr(md5(uniqid()), 0, 10)); }

$transactions = [];
$remoteDeposits = [];

for ($i = 0; $i < $totalEntries; $i++) {
    $rand = rand(1, 100);
    $date = date("Y-m-d H:i:s", rand(strtotime($startDate), strtotime($endDate)));
    
    if ($rand <= 15) { // High Net Worth Activity (Increased frequency for large transfers)
        $key = array_rand($highValueTypes);
        $conf = $highValueTypes[$key];
    } elseif ($rand <= 25) { // Remote deposits
        $conf = $remoteDepositVendors[array_rand($remoteDepositVendors)];
        $amount = number_format(rand($conf['amount_min'] * 100, $conf['amount_max'] * 100) / 100, 2, '.', '');
        $status = (rand(1, 10) > 1) ? 'approved' : 'pending';
        
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
    } else { // High-end lifestyle spending
        $key = array_rand($vendors);
        $conf = $vendors[$key];
    }

    $amount = number_format(rand($conf['min'] * 100, $conf['max'] * 100) / 100, 2, '.', '');
    $type = $conf['type'];
    $method = $conf['method'];
    $transfer_type = $conf['transfer_type'] ?? NULL;
    $walletType = (rand(1, 10) > 6) ? 'primary_savings' : 'default'; // 40% chance savings for high net worth
    
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
$sql .= "/* CRYPTO/STOCK HIGH NET WORTH PROFILE DATA */\n\n";

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
    $rdRows[] = "(@target_user_id, '{$r['amount']}', '{$r['front_image']}', '{$r['back_image']}', '{$r['status']}', '{$r['account_name']}', '{$r['account_number']}', NULL, '{$r['created_at']}', '{$r['updated_at']}')";
}
$sql .= implode(",\n", $rdRows) . ";\n";

file_put_contents('user_crypto_history.sql', $sql);
echo "Generated Crypto/Stock Profile: " . count($transactions) . " txns, " . count($remoteDeposits) . " deposits.\n";
echo "Saved to: user_crypto_history.sql\n";
