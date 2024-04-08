<?php // Check if the specified product and lot number has been QC tested.

/*
Get productCode, lot, moduleSerialNumber
Return status, isValid(bool)
Codes:
    - 50: Success
*/

// Firstly, if we have no data, quit.
if(!$data) {
    // Throw error and stop processing things.
    echo(json_encode(["Error" => "No data available."]));
    exit;
}

$response = "API Unfinished. More information required.";

// Provide the response
echo json_encode(["status" => $response]);

