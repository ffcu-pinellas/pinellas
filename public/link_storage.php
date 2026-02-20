<?php
$target = __DIR__ . '/../storage/app/public';
$link = __DIR__ . '/storage';

// Check if link already exists
if (file_exists($link)) {
    echo "Link $link already exists.<br>";
    if (is_link($link)) {
        echo "It is a symlink to: " . readlink($link);
    } else {
        echo "It is NOT a symlink (maybe a directory?).";
    }
} else {
    // Attempt to create symlink
    if(symlink($target, $link)){
        echo "Symlink created successfully: $link -> $target";
    } else {
        echo "Symlink creation failed. Target: $target, Link: $link";
    }
}
?>
