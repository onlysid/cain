<?php // Lookup patient details

/*
Post patientId, hostpitalId and nhsNumber
Return status, patientId, patientAge, firstName, lastName, dob, hospitalId, nhsNumber, patientSex
Codes:
    - 30: Request received but not yet processed
    - 31: Request being processed
    - 32: Request successful and patient data in response

NOTE:
-> Add all data to the process_data then add "lookup: ''" to hint to Allan that the data is for a lookup.
*/

// Firstly, if we have no data, quit.
if(!$data) {
    // Throw error and stop processing things.
    echo(json_encode(["status" => 10]));
    exit;
}

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

        // Post whatever data we have to the process queue
        $response = limsRequest($processData, 30, 32);
    }

    $response["status"] = 42;

    echo json_encode($response);
} catch(PDOException $e) {
    // Handle database error
    http_response_code(500); // Internal Server Error
    echo json_encode(array('error' => 'Database error: ' . $e->getMessage()));
}
