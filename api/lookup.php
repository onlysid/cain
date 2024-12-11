<?php // Lookup patient details

/*
Post patientId, hostpitalId and nhsNumber
Return status, patientId, patientAge, firstName, lastName, dob, hospitalId, nhsNumber, patientSex
Codes: (For SAMBA II)
    - 30: Request received but not yet processed
    - 31: Request being processed
    - 32: Request successful and patient data in response

NOTE:
-> Add all data to the process_data then add "lookup: ''" to hint to Allan that the data is for a lookup.

Returns:
"status" (< SAMBA II)
    This is for the old system. All requests are now processed within this script so 42 is always returned.
    - 30: Request received but not yet processed
    - 31: Request being processed
    - 32: Request is processed

"patientId"
"patientAge"
"firstName"
"lastName"
"dob"
"hospitalId"
"nhsNumber"
"patientSet"
*/

// Main logic
try {
    // Get posted items
    $patientId = $data['patientId'] ?? null;
    $hospitalId = $data['hospitalId'] ?? null;
    $nhsNumber = $data['nhsNumber'] ?? null;

    // Initialise response
    $response = [];

    // We need to send all this to the process queue and wait for the response.
    if($patientId || $hospitalId || $nhsNumber) {
        // Build the data object
        $processData = [];
        if($patientId) {
            $processData["patientId"] = $patientId;
        }
        if($hospitalId) {
            $processData["hospitalId"] = $hospitalId;
        }
        if($nhsNumber) {
            $processData["nhsNumber"] = $nhsNumber;
        }
        $processData["lookup"] = "";

        // Post whatever data we have to the process queue
        $response = limsRequest($processData, 30, 32);

        // If there is nothing in the response, return that nothing was added
        if(!$response) {
            $response = [];
            $response["status"] = 0;
            $response["message"] = "No patient information found.";
        } else {
            // Generic status
            $response["status"] = 32;
        }

    } else {
        // We need at least one of these parameters to proceed with the lookup. Throw an error.
        $response["status"] = 0;
        $response["message"] = "Please provide either a patientId, hospitalId or nhsNumber";
        addLogEntry('API', "ERROR: /lookup unable to find parameters.");
    }


    echo json_encode($response);
} catch(PDOException $e) {
    // Log detailed information securely
    $errorDetails = [
        'error_message' => $e->getMessage(),
        'stack_trace' => $e->getTraceAsString(),
        'user_id' => $currentUser['operator_id'] ?? 'unknown',
        'context' => 'Updating general settings'
    ];
    addLogEntry('API', "ERROR: /lookup - " . json_encode($errorDetails, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}
