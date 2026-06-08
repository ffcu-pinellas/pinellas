<?php
// Bootstrap Laravel
$rootPath = __DIR__;
if (!file_exists($rootPath . '/vendor/autoload.php')) {
    $rootPath = dirname(__DIR__);
}

require $rootPath . '/vendor/autoload.php';
$app = require_once $rootPath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

try {
    // Force Site Logo to the existing FrontField logo asset
    Setting::set('site_logo', 'assets/global/images/6RR9UFs6kLq67BrPItMv.png');
    Setting::set('site_favicon', 'assets/global/images/6RR9UFs6kLq67BrPItMv.png');
    
    // Set Site Title
    Setting::set('site_title', 'FrontField Credit Union');
    
    // Set Site Phone
    Setting::set('site_phone', '216 230 1837');

    // Populate SEO settings
    Setting::set('meta_title', 'FrontField Credit Union - Secure Digital Banking');
    Setting::set('meta_description', 'Manage your accounts, check balances, send money instantly with Zelle®, pay bills, and access premium credit union services with FrontField Credit Union.');
    Setting::set('meta_keywords', 'FrontField, FrontField Credit Union, credit union, online banking, digital banking, secure banking, check balances, mobile banking, Zelle');
    
    // Set copyright text
    Setting::set('copyright_text', '© 2026 FrontField Credit Union. All rights reserved.');

    // Flush the settings cache
    Setting::flushCache();
    
    echo "<h1>Rebranding and Logo Force Sync Successful!</h1>";
    echo "<p>Site Logo set to: assets/global/images/6RR9UFs6kLq67BrPItMv.png</p>";
    echo "<p>Site Title, Phone, and SEO meta settings updated.</p>";
} catch (\Exception $e) {
    echo "<h1>Error updating settings:</h1><pre>" . $e->getMessage() . "</pre>";
}

// Self-delete to prevent unauthorized access
@unlink(__FILE__);
