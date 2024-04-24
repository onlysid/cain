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
*/

// Firstly, if we have no data, quit.
if(!$data) {
    // Throw error and stop processing things.
    echo(json_encode(["status" => 42]));
    exit;
}

// Main logic
try {
    // Get operatorId and password from POST request
    $operatorId = $data['operatorId'] ?? null;
    $password = $data['password'] ?? null;

    // If we have no operator ID, throw an error.
    if(!$operatorId) {
        echo json_encode(["status" => 42, "auth" => false, "res" => 0, "message" => "No Operator ID Provided."]);
        return;
    } else {
        // Check if the operator exists
        $operatorExists = operatorExists($operatorId);

        // If the operator does not exist, check LIMS and create the operator locally if need be
        if(!$operatorExists) {
            // If we have a successful result in the auth value, then LIMS has found the operator. Otherwise, no operator exists.
            $limsResponse = limsRequest(["operatorId" => $operatorId], 40, 42);
            if(isset($limsResponse['operatorResult']) ? $limsResponse['operatorResult'] == 'true' : false) {
                // Add the operator to the database
                $cainDB->query("INSERT INTO `users` (`operator_id`, `user_type`) VALUES (:operatorId, 1);", [':operatorId' => $operatorId]);
            } else {
                // There is no operator by that name, throw an error.
                echo json_encode(["status" => 42, "auth" => false, "res" => 1, "message" => "This operator does not exist."]);
                return;
            }
        }
        
        // At this point, we have an operator. Get it!
        $operator = $cainDB->getOperatorInfo($operatorId);

        // We don't need all that data, let's just get the ones we need.
        $fields = ["operator_id", "first_name", "last_name", "user_type", "status"];

        // Initialize an empty array to store the filtered fields
        $filteredOperator = [];

        // Loop through the desired fields and copy them to the filtered array
        foreach ($fields as $field) {
            // Convert snake_case to camelCase
            $camelCaseField = lcfirst(str_replace('_', '', ucwords($field, '_')));
            // Check if the field exists in the original operator array
            if (isset($operator[$field])) {
                // Copy the field to the filtered array with camelCase key
                $filteredOperator[$camelCaseField] = $operator[$field];
            }
        }

        // Check if password is required
        $passwordRequired = isPasswordRequired();

        // If the password is not required at all, authentication is complete.
        if(($passwordRequired < 2 && $operator['user_type'] == CLINICIAN) || (($passwordRequired == 0 || $passwordRequired == 2) && $operator['user_type'] == ADMINISTRATIVE_CLINICIAN)) {
            echo json_encode(["status" => 42, "auth" => true, "res" => 2, "operator" => $filteredOperator, "message" => "No password required. Successfully authenticated."]);
            return;
        }

        // At this point, we need a password. Start by checking that the user actually has a password.
        if(!$operator['password']) {
            echo json_encode(["status" => 42, "auth" => false, "res" => 3, "operator" => $filteredOperator, "message" => "This operator has not been set up properly. Please create an account by logging into the DMS first."]);
            return;
        }

        // Check if a password was supplied.
        if(!$password) {
            echo json_encode(["status" => 42, "auth" => false, "res" => 4, "operator" => $filteredOperator, "message" => "Password required. Please enter a password."]);
            return;
        }

        // We have a password, check authentication.
        if(Session::authenticate($operatorId, $password)) {
            // Password has been accepted
            echo json_encode(["status" => 42, "auth" => true, "res" => 5, "operator" => $filteredOperator, "message" => "Password accepted. Successfully authenticated."]);
            return;
        } else {
            // Password has been rejected
            echo json_encode(["status" => 42, "auth" => false, "res" => 6, "operator" => $filteredOperator, "message" => "Password rejected. Authentication failed."]);
            return;
        }
    }
} catch(PDOException $e) {
    // Handle database error
    http_response_code(500); // Internal Server Error
    echo json_encode(array('error' => 'Database error: ' . $e->getMessage()));
}
