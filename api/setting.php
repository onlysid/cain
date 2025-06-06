<?php // Endpoint to retreive DMS setting information

/*
Get settingItem
Return setting
*/

// Get moduleSerialNumber from "sn" URL parameter (if we have no data, quit)
if(!($setting = ($_GET['s'] ?? null))) {
    // Throw error and stop processing things.
    echo(json_encode(["Error" => "No data available."]));
    addLogEntry('API', "ERROR: /settings endpoint hit with insufficient params.");
    exit;
}

// Get setting information
$response = getSetting($setting);

// Provide the response
echo json_encode([
    "res" => $response ? 1 : 0,
    "setting" => $response ?? "No setting found with this name.",
]);