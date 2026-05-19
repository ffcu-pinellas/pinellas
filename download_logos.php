<?php
$content = file_get_contents('app/Services/BankTemplateService.php');

// Regex to capture the URLs (allowing anything until the closing quote)
preg_match_all('/src="(http[^"]+)"/i', $content, $matches);
$urls = array_unique($matches[1]);

if(!is_dir('public/assets/images/bank_logos')) {
    mkdir('public/assets/images/bank_logos', 0777, true);
}

$map = [];
foreach($urls as $url) {
    // Basic validation to make sure it's an image
    if (!preg_match('/\.(png|jpg|jpeg|gif)$/i', parse_url($url, PHP_URL_PATH)) && strpos($url, 'logo.clearbit.com') === false && strpos($url, 'email_assets') === false) {
        continue;
    }

    $ext = 'png';
    if(preg_match('/\.([a-z0-9]+)$/i', parse_url($url, PHP_URL_PATH), $m)) {
        $ext = $m[1];
    }
    
    $filename = 'logo_' . md5($url) . '.' . $ext;
    $path = 'public/assets/images/bank_logos/' . $filename;
    
    if(!file_exists($path)) {
        echo 'Downloading ' . $url . PHP_EOL;
        $imgData = @file_get_contents($url);
        if ($imgData) {
            file_put_contents($path, $imgData);
        } else {
            echo "Failed to download $url\n";
        }
    }
    
    $map[$url] = '{{ asset(\'assets/images/bank_logos/' . $filename . '\') }}';
}

$newContent = str_replace(array_keys($map), array_values($map), $content);
file_put_contents('app/Services/BankTemplateService.php', $newContent);

echo 'Replaced ' . count($map) . ' logos!';
