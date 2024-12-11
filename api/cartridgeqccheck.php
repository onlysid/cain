<?php // Check if the specified product and lot number has been QC tested.

/*
Get assayType, assaySubType, lot, subLot
Return status, isValid(bool)
Codes:
    - 50: Success
*/

// Get params
if(!($lotNumber = $_GET['lot'])) {
    // Throw an error and stop processing things, as this is the bare minimum we require.
    echo(json_encode(["Error" => "No data available."]));
    addLogEntry('API', "ERROR: /cartridgeqccheck unable to find 'lot' parameter");
    exit;
}
$subLotNumber = $_GET['sublot'] ?? null;
$assayType = $_GET['assay'] ?? null;
$subAssayType = $_GET['subassay'] ?? null;

// Update the lot with information (or add to the DB)
$params = [
    "sub_lot_number" => $_GET['sublot'] ?? null,
    "assay_type" => $_GET['assay'] ?? null,
    "assay_sub_type" => $_GET['subassay'] ?? null,
];

$lot = updateLot($lotNumber, $params);

// If the lot was not updated, something went wrong.
if(!$lot) {
    echo(json_encode(["Error" => "Something went wrong."]));
    addLogEntry('API', "ERROR: /cartridgeqccheck - something went wrong.");
    exit;
}

// Otherwise, we need to check if the lot has passed QC.
$response = lotQCCheck($lot);

// Provide the response
echo json_encode(["status" => 50, "isValid" => $response ?? false]);

