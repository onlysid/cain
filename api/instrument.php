<?php // Send information about an instrument (Assay Module).

/*
Get tabletId, tableData => {frontPanelId, status, progress, timeRemaining, faultCode, versionNumber, moduleName} for each instrument
Codes:
    Status:
    - 0: Unknown
    - 1: Idle
    - 2: Preparing Assay
    - 3: Running
    - 4: Aborting
    - 5: Result Available
    - 6: Error
    - 7: Uninitialised
    - 8: Initialising
    - 9: Assay Complete
    - 99: Disconnected

    Fault Code:
    - TODO: TBD

    Progress:
    - % completed (0-100)

    Time Remaining:
    - Number of minutes remaining until test complete

Return status
Codes:
    - 60: Success
*/

// Firstly, if we have no data, quit.
if(!$data) {
    // Throw error and stop processing things.
    echo(json_encode(["status" => 400]));
    addLogEntry('API', "ERROR: /instrument unable to find parameters.");
    exit;
}

// We have data! Clean it and add it to the instruments db if it is good.
$response = updateInstruments($data);

// Provide the response
if(isset($response) && $response) {
    echo json_encode(["status" => 10]);
} else {
    echo json_encode(["status" => 422]);
}
