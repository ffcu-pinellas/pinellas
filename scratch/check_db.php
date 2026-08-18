<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "=== wire_transfars columns ===\n";
if (Schema::hasTable('wire_transfars')) {
    echo implode(', ', Schema::getColumnListing('wire_transfars')) . "\n";
} else {
    echo "Table wire_transfars does not exist.\n";
}

echo "\n=== users table check ===\n";
$userCols = ['wire_transfer_status', 'custom_wire_min_limit', 'custom_wire_max_limit', 'custom_wire_daily_limit', 'checking_restricted', 'savings_restricted', 'ira_restricted', 'heloc_restricted'];
foreach ($userCols as $col) {
    echo "$col: " . (Schema::hasColumn('users', $col) ? 'EXISTS' : 'MISSING') . "\n";
}

echo "\n=== wire_transfars table check ===\n";
$wireCols = ['status', 'international_charge', 'international_charge_type'];
foreach ($wireCols as $col) {
    echo "$col: " . (Schema::hasColumn('wire_transfars', $col) ? 'EXISTS' : 'MISSING') . "\n";
}
