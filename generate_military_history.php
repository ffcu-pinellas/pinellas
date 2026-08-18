<?php
/**
 * Military Transaction & Remote Deposit History Generator Runner
 * Calls the Python generator for comprehensive 4.5 year military financial history.
 */

$cmd = "python " . escapeshellarg(__DIR__ . "/scratch/generate_military_sql.py");
passthru($cmd, $returnVar);

if ($returnVar === 0) {
    echo "\nMilitary history generation complete: database/user_military_history.sql\n";
} else {
    echo "\nFailed to generate military history. Exit code: $returnVar\n";
}
