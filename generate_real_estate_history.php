<?php
/**
 * Real Estate Professional Transaction & Remote Deposit Generator
 * Timeline: Jan 2023 - March 2026
 * Profile: Luxury Real Estate Broker / Agent
 */

$totalEntries = 450;
$startDate = "2023-01-01";
$endDate = "2026-03-31";

$vendors = [
    // Industry Specific & Business
    'Zillow Premier Agent' => ['min' => 500, 'max' => 5000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Redfin Partner Fee' => ['min' => 200, 'max' => 1500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Luxury Staging Co' => ['min' => 1200, 'max' => 15000, 'type' => 'subtract', 'method' => 'Wire'],
    'Pro Photo / Drone' => ['min' => 350, 'max' => 2500, 'type' => 'subtract', 'method' => 'Debit Card'],
    'RE/MAX Franchise Dues' => ['min' => 800, 'max' => 2500, 'type' => 'subtract', 'method' => 'ACH'],
    'MLS Access Fee' => ['min' => 150, 'max' => 450, 'type' => 'subtract', 'method' => 'ACH'],
    'Compass Marketing Kit' => ['min' => 300, 'max' => 2000, 'type' => 'subtract', 'method' => 'Debit Card'],
    // Everyday General & Luxury Lifestyle
    'Whole Foods Market' => ['min' => 120, 'max' => 850, 'type' => 'subtract', 'method' => 'Debit Card'],
    'The Capital Grille' => ['min' => 150, 'max' => 850, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Ruth\'s Chris Steak' => ['min' => 200, 'max' => 1200, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Starbucks Reserve' => ['min' => 10, 'max' => 55, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Prada Milano Boutique' => ['min' => 800, 'max' => 5000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Gucci Store Purchase' => ['min' => 500, 'max' => 4500, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Louis Vuitton' => ['min' => 1200, 'max' => 8000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Equinox Luxury Fitness' => ['min' => 250, 'max' => 450, 'type' => 'subtract', 'method' => 'ACH'],
    'Tesla Supercharger' => ['min' => 20, 'max' => 70, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Mercedes-Benz Lease' => ['min' => 950, 'max' => 2200, 'type' => 'subtract', 'method' => 'ACH'],
    'Apple Store' => ['min' => 200, 'max' => 6000, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Amazon Luxury' => ['min' => 100, 'max' => 3000, 'type' => 'subtract', 'method' => 'Debit Card'],
    'Uber Black Client' => ['min' => 45, 'max' => 350, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Netflix 4K' => ['min' => 20, 'max' => 25, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Spotify Premium' => ['min' => 10, 'max' => 15, 'type' => 'subtract', 'method' => 'Apple Pay'],
    'Duke Energy' => ['min' => 200, 'max' => 600, 'type' => 'subtract', 'method' => 'ACH'],
    'Verizon Business' => ['min' => 150, 'max' => 500, 'type' => 'subtract', 'method' => 'ACH'],
    // Incoming Vendor Transactions (Refunds/Credits)
    'Zillow / Lead Credit Refund' => ['min' => 100, 'max' => 1500, 'type' => 'deposit', 'method' => 'Debit Card'],
    'Redfin / Referral Credit' => ['min' => 200, 'max' => 2000, 'type' => 'deposit', 'method' => 'Debit Card'],
    'MLS / Overpayment Refund' => ['min' => 50, 'max' => 300, 'type' => 'deposit', 'method' => 'Debit Card'],
    'Compass / Expense Reimbursement' => ['min' => 300, 'max' => 5000, 'type' => 'deposit', 'method' => 'ACH'],
    'Cash App / Client Deposit' => ['min' => 500, 'max' => 10000, 'type' => 'deposit', 'method' => 'ACH'],
    'Venmo / Consulting Fee' => ['min' => 250, 'max' => 2500, 'type' => 'deposit', 'method' => 'ACH'],
];

$highValueTypes = [
    'Sotheby\'s International / 123 Luxury Ln Commission' => ['min' => 125000, 'max' => 450000, 'type' => 'deposit', 'method' => 'Wire'],
    'Coldwell Banker Realty / Brokerage Bonus' => ['min' => 35000, 'max' => 95000, 'type' => 'deposit', 'method' => 'Wire'],
    'Compass Real Estate / Performance Incentive' => ['min' => 50000, 'max' => 150000, 'type' => 'deposit', 'method' => 'Wire'],
    'First American Title / Escrow Disbursement' => ['min' => 75000, 'max' => 300000, 'type' => 'deposit', 'method' => 'Wire'],
    'Fidelity National Title / Closing Funds' => ['min' => 120000, 'max' => 500000, 'type' => 'deposit', 'method' => 'Wire'],
    'Berkshire Hathaway / Quarterly Dividend' => ['min' => 25000, 'max' => 75000, 'type' => 'deposit', 'method' => 'ACH'],
    'Property Acquisition / Deposit 456 Ocean Dr' => ['min' => 100000, 'max' => 250000, 'type' => 'subtract', 'method' => 'Wire'],
    'Luxury Listing Ad Campaign - WSJ / Mansion Global' => ['min' => 15000, 'max' => 45000, 'type' => 'subtract', 'method' => 'Wire'],
    'Quarterly Tax Payout - IRS Business' => ['min' => 35000, 'max' => 110000, 'type' => 'subtract', 'method' => 'Wire'],
];

$incomeTypes = [
    'Residential Commission - 789 Park Ave' => ['min' => 15000, 'max' => 65000, 'type' => 'deposit', 'method' => 'ACH'],
    'Rental Management Payout - Luxury Condos' => ['min' => 5000, 'max' => 18000, 'type' => 'deposit', 'method' => 'ACH'],
    'Referral Fee - Keller Williams NYC' => ['min' => 3500, 'max' => 12000, 'type' => 'deposit', 'method' => 'ACH'],
    'MEMBER TRANSFER TO SAVINGS' => ['min' => 5000, 'max' => 45000, 'type' => 'fund_transfer', 'method' => 'Internal', 'transfer_type' => 'own_bank_transfer'],
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
    
    if ($rand <= 25) { 
        $key = array_rand($highValueTypes);
        $conf = $highValueTypes[$key];
    } elseif ($rand <= 45) {
        $key = array_rand($incomeTypes);
        $conf = $incomeTypes[$key];
    } elseif ($rand <= 55) {
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
                'description' => "Remote Deposit - Commission Check",
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

$sql = "SET @target_user_id = 5;\n\n";

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

file_put_contents('real_estate_user5_history.sql', $sql);
echo "Generated Real Estate Profile: " . count($transactions) . " txns, " . count($remoteDeposits) . " deposits.\n";
echo "Saved to: real_estate_user5_history.sql\n";
