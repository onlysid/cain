<?php
include_once __DIR__ . "/../includes/config.php";
include_once BASE_DIR . "/includes/functions.php";

$totalGzSize = getExpiredLogSize();

// Output the result as JSON
header('Content-Type: application/json');
echo json_encode([
    'totalGzSize' => $totalGzSize,
    'formattedSize' => formatSize($totalGzSize)
]);

exit;
