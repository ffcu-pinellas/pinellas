<?php
$content = file_get_contents('app/Services/BankTemplateService.php');
preg_match_all('/src="(https:\/\/logo\.clearbit\.com[^"]+)"/i', $content, $matches);
$urls = array_unique($matches[1]);
$options  = ['http' => ['user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36']];
$context  = stream_context_create($options);
$map = [];

foreach($urls as $url) {
    $filename = 'logo_' . md5($url) . '.png';
    $path = 'public/assets/images/bank_logos/' . $filename;
    echo "Downloading $url\n";
    $imgData = @file_get_contents($url, false, $context);
    if ($imgData) {
        file_put_contents($path, $imgData);
        $map[$url] = '{{ asset(\'assets/images/bank_logos/' . $filename . '\') }}';
    } else {
        echo "Failed: $url\n";
    }
}

$newContent = str_replace(array_keys($map), array_values($map), $content);
file_put_contents('app/Services/BankTemplateService.php', $newContent);
echo 'Replaced ' . count($map) . ' clearbit logos!';
