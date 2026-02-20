<?php
$target = __DIR__ . '/../storage/app/public';
$link = __DIR__ . '/storage';

echo "<h1>Storage Linker</h1>";
echo "Target: $target<br>";
echo "Link: $link<br><hr>";

if (file_exists($link)) {
    echo "Link path already exists.<br>";
    if (is_link($link)) {
        echo "It is a SYMLINK.<br>";
        echo "Points to: " . readlink($link) . "<br>";
        if(readlink($link) !== $target) {
            echo "<strong>MISMATCH!</strong> Deleting and re-linking...<br>";
            unlink($link);
            if(symlink($target, $link)){
                echo "Success: Symlink corrected.<br>";
            } else {
                echo "Error: Could not create symlink.<br>";
            }
        } else {
            echo "Link is correct.<br>";
        }
    } else {
        echo "<strong>WARNING:</strong> It is a DIRECTORY, not a symlink. Please rename/delete 'public/storage' manually via FTP/File Manager.<br>";
    }
} else {
    echo "Link does not exist. Creating...<br>";
    if(symlink($target, $link)){
        echo "<strong>Success:</strong> Symlink created.<br>";
    } else {
        echo "<strong>Error:</strong> Symlink creation failed. (Check permissions)<br>";
    }
}
?>
