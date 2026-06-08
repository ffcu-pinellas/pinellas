<?php
// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

try {
    // Set Site Logo to the existing FrontField logo asset
    Setting::set('site_logo', 'global/images/6RR9UFs6kLq67BrPItMv.png');
    
    // Set Site Title
    Setting::set('site_title', 'FrontField Credit Union');
    
    // Set Site Phone
    Setting::set('site_phone', '216 230 1837');
    
    // Flush the settings cache
    Setting::flushCache();
    
    echo "Settings updated successfully! Logo path, site title, and phone number updated.";
} catch (\Exception $e) {
    echo "Error updating settings: " . $e->getMessage();
}

// Self-delete to prevent unauthorized access
@unlink(__FILE__);
