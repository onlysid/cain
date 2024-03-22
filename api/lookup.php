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
    echo(json_encode(["Error" => "No data available."]));
    exit;
}

$response = "API Unfinished.";

// Provide the response
echo json_encode(["status" => $response]);
