<?php
/**
 * Standalone Cache Fix Script - ROOT VERSION
 * Bypasses Laravel's cached views by manually deleting the compiled files.
 */

// Define paths (Root Version)
$basePath = __DIR__;
$viewPath = $basePath . '/storage/framework/views';
$cachePath = $basePath . '/storage/framework/cache/data';
$routeCache = $basePath . '/storage/framework/routes.php';
$configCache = $basePath . '/storage/framework/config.php';

function clearDirectory($path) {
    if (!is_dir($path)) {
        return;
    }
    
    $files = glob($path . '/*');
    foreach ($files as $file) {
        if (is_file($file) && !str_contains($file, '.gitignore')) {
            @unlink($file);
            echo "Deleted file: " . basename($file) . "<br>";
        } elseif (is_dir($file)) {
            clearDirectory($file);
            @rmdir($file);
            echo "Deleted dir: " . basename($file) . "<br>";
        }
    }
}

echo "<html><body style='font-family:sans-serif; padding: 20px;'>";
echo "<h1 style='color: #ff4d4d;'>Manual Cache Destroyer (Root)</h1>";

echo "<h3>1. Clearing Compiled Views...</h3>";
clearDirectory($viewPath);

echo "<h3>2. Clearing Cache Data...</h3>";
clearDirectory($cachePath);

echo "<h3>3. Clearing Route & Config Cache Files...</h3>";
if (file_exists($routeCache)) {
    @unlink($routeCache);
    echo "Deleted route cache.<br>";
}
if (file_exists($configCache)) {
    @unlink($configCache);
    echo "Deleted config cache.<br>";
}

echo "<h3>4. Attempting Laravel Internal Clear...</h3>";
try {
    if (file_exists($basePath . '/vendor/autoload.php')) {
        require $basePath . '/vendor/autoload.php';
        $app = require_once $basePath . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        
        $status = \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        echo "Artisan optimize:clear status: " . $status . "<br>";
    } else {
        echo "Vendor not found, skipped Artisan call.<br>";
    }
} catch (Exception $e) {
    echo "Artisan call failed: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2 style='color: green;'>✅ DONE! Your site should now show the new UI changes.</h2>";
echo "<p>Please return to your <a href='/admin/dashboard'>Admin Dashboard</a> and refresh.</p>";
echo "</body></html>";
