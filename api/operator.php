<?php // API to log in via tablet (on tablet app)

/* 
Standard authentication happens elsewhere for the DMS.
This is solely for the purpose of taking data from the tablet, inserting it into the database and,
optionally, awaiting a response from LIMS.

The DMS->LIMS application (AL) polls the database looking for changes in the status field.

Returns:
"status"
    - 40: Request received but not yet processed
    - 41: Request being processed
    - 42: Request is processed

TODO: We may need to add password generation logic here from the tablet
*/

// Firstly, if we have no data, quit.
if(!$data) {
    // Throw error and stop processing things.
    echo(json_encode(["status" => 42]));
    exit;
}

// Main logic
try {
    // Check if password is required
    $passwordRequired = isPasswordRequired();
    
    // Get operatorId and password from POST request
    $operatorId = $data['operatorId'] ?? null;

    // Check if operator exists
    $operatorExists = operatorExists($operatorId);

    // Instantiate "result"
    $result = false;

    // If the operator exists, we check if they need a password
    if($operatorId) {
        if($operatorExists) {
            $userType = $cainDB->getOperatorInfo($operatorId)['user_type'];
            if(($passwordRequired < 2 && $userType == CLINICIAN) || (($passwordRequired == 0 || $passwordRequired == 2) && $userType == ADMINISTRATIVE_CLINICIAN)) {
                $result = true;
            }
        } else {
            // TODO: See if there's something specific we're meant to get back
            // The user does not exist in the database. Request LIMS stuff!
            $response = limsRequest(["operatorId" => $operatorId], 40, 42);
            // Any repsonse will do (just that the response array is not empty)
            if($response) {
                $result = true;
            }
        }
    }
    echo json_encode(["status" => 42, "operatorId" => $operatorId, "operatorResult" => $result]);
} catch(PDOException $e) {
    // Handle database error
    http_response_code(500); // Internal Server Error
    echo json_encode(array('error' => 'Database error: ' . $e->getMessage()));
}
