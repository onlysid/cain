<?php // Send information to and from the DMS about different config settings

/*
Upon app start (or whenever appropriate, like logging in), hit this API with info about the tablet and app version.
The API will respond with any config options (including field visibility settings).

Get tabledId, appVersion

Return config
Codes:
    - 150: Success
*/

// Firstly, if we have no data, quit.
if(!$data) {
    // Throw error and stop processing things.
    echo(json_encode(["status" => 400]));
    exit;
}

// We have data! Clean it and return some information.
$tabletId = $data['tabletId'] ?? null;
$appVersion = $data['appVersion'] ?? null;

if($tabletId) {
    // Create/update the tablet information in the database
    updateTablet($tabletId, $appVersion);

    // Generate the config
    $config = getConfigForTablet();

    echo json_encode(["status" => 150, "config" => $config]);
}

// Provide the response
if(isset($response)) {
    echo json_encode(["status" => 10]);
} else {
    echo json_encode(["status" => 422]);
}
