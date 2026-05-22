<?php
$content = file_get_contents('app/Services/BankTemplateService.php');
$content = preg_replace('/\{\{\s*asset\(\'(assets\/images\/bank_logos\/[a-zA-Z0-9_\.]+\.png)\'\)\s*\}\}/', 'https://pinellascu.com/$1', $content);
file_put_contents('app/Services/BankTemplateService.php', $content);
echo 'Fixed Blade asset tags in BankTemplateService.php!';
