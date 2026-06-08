<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "site_logo: " . setting('site_logo', 'global') . "\n";
echo "site_title: " . setting('site_title', 'global') . "\n";
echo "site_logo_height: " . setting('site_logo_height', 'global') . "\n";
echo "site_logo_width: " . setting('site_logo_width', 'global') . "\n";
