<?php
$content = file_get_contents('app/Services/BankTemplateService.php');
preg_match_all('/src="(https:\/\/logo\.clearbit\.com[^"]+)"/i', $content, $matches);
$urls = array_unique($matches[1]);
$map = [];

foreach($urls as $url) {
    $filename = 'logo_' . md5($url) . '.png';
    $path = 'public/assets/images/bank_logos/' . $filename;
    echo "Downloading $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $imgData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && $imgData) {
        file_put_contents($path, $imgData);
        $map[$url] = '{{ asset(\'assets/images/bank_logos/' . $filename . '\') }}';
    } else {
        echo "Failed: $url (HTTP $httpCode)\n";
    }
}

$newContent = str_replace(array_keys($map), array_values($map), $content);
file_put_contents('app/Services/BankTemplateService.php', $newContent);
echo 'Replaced ' . count($map) . ' clearbit logos!';
