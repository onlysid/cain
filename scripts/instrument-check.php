<?php // Instrument Check

// Fetch all instrument data from the database and return it in a JSON object

// Generate random number (for now)
$response = ["Instrument1" => ["status" => rand()], "Instrument2" => ["status" => rand()], "Instrument3" => ["status" => rand()], "Instrument4" => ["status" => rand()]];

echo(json_encode($response));