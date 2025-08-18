<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

$base_path = "/home/www/web316.s146.goserver.host/";

function zipFolder($source, $destination, $exclude = []) {
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    $zip = new ZipArchive();
    if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return false;
    }

    $source = realpath($source);
    $exclude = array_map(function($path) {
        return realpath($path) ?: $path; // Convert to absolute paths, fallback to original if not found
    }, $exclude);

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($source) + 1);

        // Check if the file is in an excluded path
        $excludeFile = false;
        foreach ($exclude as $excludedPath) {
            if (strpos($filePath, $excludedPath) === 0) {
                $excludeFile = true;
                break;
            }
        }

        if (!$excludeFile) {
            $zip->addFile($filePath, $relativePath);
        }
    }

    return $zip->close();
}

// Example usage:
$sourceFolder = $base_path;
$destinationZip = $base_path . 'archive020325.zip'; // Specify the full path to the zip file
$excludeFolders = [
    $base_path . 'files/clients',
	$base_path . 'screencasts',
    $base_path . 'temp',
    $base_path . 'vendor1',
];

if (zipFolder($sourceFolder, $destinationZip, $excludeFolders)) {
    echo "Zip file created successfully.";
} else {
    echo "Failed to create zip file.";
}
?>