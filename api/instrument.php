<?php // Send information about an instrument.

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
    - TBD

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
    echo(json_encode(["Error" => "No data available."]));
    exit;
}

$response = "API Unfinished.";

// Provide the response
echo json_encode(["status" => $response]);
