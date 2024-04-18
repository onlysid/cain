<?php // Send information about an instrument (Assay Module).

/*
Get tabletId, instrumentDump => {moduleSerialNumber, frontPanelId, status, progress, timeRemaining, faultCode, versionNumber} for each instrument
Codes:
    Status:
    - 0: UNKNOWN
    - 1: IDLE
    - 2: RUNNING TEST
    - 3: TEST ABORTED
    - 4: RESULT AVAILABLE
    - 5: FAULT CONDITION

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
    exit;
}

// We have data! Clean it and add it to the instruments db if it is good.
$instrumentData['tablet_id'] = $data['tabletId'] ?? null;

if($instrumentData['tablet_id']) {
    $instrumentData['tablet_data'] = $data['tabletData'] ?? null;
    
    $response = updateInstruments($instrumentData);
}


// Provide the response
if(isset($response) && $response) {
    echo json_encode(["status" => 10]);
} else {
    echo json_encode(["status" => 422]);
}
