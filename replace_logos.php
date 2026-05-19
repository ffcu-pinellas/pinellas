<?php
$content = file_get_contents('app/Services/BankTemplateService.php');
preg_match_all('/src="(https:\/\/logo\.clearbit\.com[^"]+)"/i', $content, $matches);
$urls = array_unique($matches[1]);
$map = [];

foreach($urls as $url) {
    $filename = 'logo_' . md5($url) . '.png';
    $map[$url] = '{{ asset(\'assets/images/bank_logos/' . $filename . '\') }}';
}

$newContent = str_replace(array_keys($map), array_values($map), $content);
file_put_contents('app/Services/BankTemplateService.php', $newContent);
echo 'Replaced ' . count($map) . ' clearbit logos!';
