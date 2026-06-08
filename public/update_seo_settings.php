<?php
// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

try {
    // Populate SEO settings
    Setting::set('meta_title', 'FrontField Credit Union - Secure Digital Banking');
    Setting::set('meta_description', 'Manage your accounts, check balances, send money instantly with Zelle®, pay bills, and access premium credit union services with FrontField Credit Union.');
    Setting::set('meta_keywords', 'FrontField, FrontField Credit Union, credit union, online banking, digital banking, secure banking, check balances, mobile banking, Zelle');
    
    // Set copyright text
    Setting::set('copyright_text', '© 2026 FrontField Credit Union. All rights reserved.');

    // Flush the settings cache
    Setting::flushCache();
    
    echo "SEO settings updated successfully! meta_title, meta_description, meta_keywords, and copyright_text updated.";
} catch (\Exception $e) {
    echo "Error updating SEO settings: " . $e->getMessage();
}

// Self-delete to prevent unauthorized access
@unlink(__FILE__);
