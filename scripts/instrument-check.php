<?php // Instrument Check
include_once __DIR__ . "/../includes/config.php";
include_once BASE_DIR . "/includes/db.php";
include_once BASE_DIR . "/includes/functions.php";

// Fetch all instrument data from the database and return it in a JSON object
$response = getInstrumentSnapshot();

echo(json_encode($response));