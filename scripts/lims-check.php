<?php // Instrument Check
include_once __DIR__ . "/../includes/config.php";
include_once BASE_DIR . "/includes/db.php";
include_once BASE_DIR . "/includes/functions.php";

// Get settings info
$response = limsConnectivity();

echo(json_encode($response));