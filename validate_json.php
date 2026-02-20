<?php
$file = 'C:\\Users\\USER\\Downloads\\frontfield-remodel pinelas fcu\\database\\deploy_banks.sql';
$content = file_get_contents($file);

preg_match_all("/\((NULL, '.*?', '.*?', '.*?', '.*?', '.*?', '.*?', .*?, .*?, .*?, .*?, .*?, .*?, '(.*?)', '.*?', .*?, .*?, .*?)\),/", $content, $matches);

foreach ($matches[2] as $index => $json) {
    $decoded = json_decode(stripslashes($json));
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Line " . ($index + 1) . " has invalid JSON: " . json_last_error_msg() . "\n";
        echo "JSON: " . $json . "\n\n";
    }
}
echo "Validation complete.\n";
