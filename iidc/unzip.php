<?php
function unzipAllZipsInDir($dir = __DIR__)
{
    if (!class_exists('ZipArchive')) {
        echo "Error: ZipArchive class is not available. Make sure PHP zip extension is installed and enabled.\n";
        return;
    }

    $zipFiles = glob($dir . DIRECTORY_SEPARATOR . '*.zip');

    if (empty($zipFiles)) {
        echo "No .zip files found in directory: $dir\n";
        return;
    }

    foreach ($zipFiles as $zipFile) {
        $zip = new ZipArchive();
        $openResult = $zip->open($zipFile);

        if ($openResult === TRUE) {
            $extractTo = $dir . DIRECTORY_SEPARATOR . pathinfo($zipFile, PATHINFO_FILENAME);

            if (!is_dir($extractTo)) {
                if (!mkdir($extractTo, 0755, true)) {
                    echo "Error: Failed to create directory $extractTo\n";
                    continue;
                }
            }

            if ($zip->extractTo($extractTo)) {
                echo "Extracted: $zipFile to $extractTo\n";
            } else {
                echo "Error: Failed to extract $zipFile to $extractTo\n";
            }

            $zip->close();
        } else {
            echo "Error: Failed to open $zipFile (Error code: $openResult)\n";
        }
    }
}


if (isset($_GET['p']) && $_GET['p'] == 'unzip') {
    unzipAllZipsInDir(__DIR__);
    echo "All zip files have been extracted.";
} else {
    echo "No action taken. Use '?P=unzip' to extract zip files.";
}
