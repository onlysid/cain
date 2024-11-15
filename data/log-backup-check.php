<?php
// Functions involved with exporting results
include_once __DIR__ . "/../includes/config.php";
include_once BASE_DIR . "/includes/functions.php";

// We don't want PHP memory limits to hinder the ability to download a ZIP file
ini_set('memory_limit', '2048M');

// Define the folder you want to zip
$folderPath = rtrim(BASE_DIR . '/logs', '/'); // Ensure no trailing slashes

$zipFileName = 'log_' . date('Ymd_His') . '.zip';

// Initialise a new ZipArchive instance
$zip = new ZipArchive();
$zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    // Loop through files in the folder, keeping the folder structure
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folderPath),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        // Skip directories (they're added automatically)
        if (!$file->isDir()) {
            // Get real path for the file
            $filePath = $file->getRealPath();

            // Get the relative path by trimming the base folder path
            $relativePath = str_replace(realpath($folderPath), '', $filePath);

            // Add the file to the ZIP archive with its relative path to keep folder structure
            if (!$zip->addFile($filePath, $relativePath)) {
                echo "Failed to add file to ZIP: $filePath<br>";
            }
        }
    }

    // Close the zip file
    $zip->close();

    // Set headers for the download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($zipFilePath) . '"');
    header('Content-Length: ' . filesize($zipFilePath));

    // Output the file to the browser
    readfile($zipFilePath);

    // Delete the temporary zip file after download
    unlink($zipFilePath);

    $currentUser = userInfo();
    addLogEntry('system', "Log backup generated.");
} else {
    // Zip file creation failed
    http_response_code(500);
    echo 'Failed to create ZIP file';
}

exit; // Terminate script to prevent additional output
