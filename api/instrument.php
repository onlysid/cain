<?php // Send information about an instrument (Assay Module).

/*
Get moduleSerialNumber, frontPanelId, status, progress, timeRemaining, faultCode, versionNumber
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
$instrumentData['serial_number'] = $data['moduleSerialNumber'] ?? null;

if($instrumentData['serial_number']) {
    $instrumentData['module_id'] = $data['frontPanelId'] ?? null;
    $instrumentData['status'] = $data['status'] ?? null;
    $instrumentData['progress'] = $data['progress'] ?? null;
    $instrumentData['time_remaining'] = $data['timeRemaining'] ?? null;
    $instrumentData['fault_code'] = $data['faultCode'] ?? null;
    $instrumentData['version_number'] = $data['versionNumber'] ?? null;

    $response = updateInstrument($instrumentData);
}


// Provide the response
if(isset($response)) {
    echo json_encode(["status" => 10]);
} else {
    echo json_encode(["status" => 422]);
}
