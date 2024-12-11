<?php // API to log in via tablet (on tablet app)

/*
Standard authentication happens elsewhere for the DMS.
This is solely for the purpose of taking data from the tablet, inserting it into the database and,
optionally, awaiting a response from LIMS.

The DMS->LIMS application (AL) polls the database looking for changes in the status field.

Returns:
"status" (< SAMBA II)
    This is for the old system. All requests are now processed within this script so 42 is always returned.
    - 40: Request received but not yet processed
    - 41: Request being processed
    - 42: Request is processed

"auth"
    - true: Login credentials are valid and authorised
    - false: Login is invalid

"res"
    - 0: No operator ID provided
    - 1: Operator does not exist
    - 2: No password required. Successfully authenticated.
    - 3: This operator has not been set up properly. Please create an account by logging into the DMS first.
    - 4: Password required. Please enter a password.
    - 5: Password accepted. Successfully authenticated.
    - 6: Password rejected. Authentication failed.

"operator"
    "operatorId"
    "firstName"
    "lastName"
    "userType"
        - 1: Clinician
        - 2: Admin clinician
        - 3: Service engineer
    "status"
        To determine if a user has been deactivated.
        - 0: Inactive user
        - 1: Active user

"message"
    - String to verify what just happened.
*/

// Main logic
try {
    // Get operatorId and password from POST request
    $operatorId = $data['operatorId'] ?? null;
    $password = $data['password'] ?? null;

    // If we have no operator ID, throw an error.
    if(!$operatorId) {
        echo json_encode(["status" => 42, "auth" => false, "res" => 0, "message" => "No Operator ID Provided."]);
        addLogEntry('API', "ERROR: /operator no operator ID provided.");
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
                addLogEntry('access', "Somebody tried logging in as $operatorId, but they do not exist.");
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
            addLogEntry('access', "{$filteredOperator['operatorId']} successfully logged into the tablet app.");
            return;
        }

        // At this point, we need a password. Start by checking that the user actually has a password.
        if(!$operator['password']) {
            echo json_encode(["status" => 42, "auth" => false, "res" => 3, "operator" => $filteredOperator, "message" => "This operator has not been set up properly. Please create an account by logging into the DMS first."]);
            addLogEntry('access', "{$filteredOperator['operatorId']} tried to log into the tablet app with an account that is not fully set up.");
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
            addLogEntry('access', "{$filteredOperator['operatorId']} successfully logged into the tablet app.");
            return;
        } else {
            // Password has been rejected
            echo json_encode(["status" => 42, "auth" => false, "res" => 6, "operator" => $filteredOperator, "message" => "Password rejected. Authentication failed."]);
            addLogEntry('access', "{$filteredOperator['operatorId']} entered an incorrect password on the tablet app.");
            return;
        }
    }
} catch(PDOException $e) {
    // Log detailed information securely
    $errorDetails = [
        'error_message' => $e->getMessage(),
        'stack_trace' => $e->getTraceAsString(),
        'user_id' => $currentUser['operator_id'] ?? 'unknown',
        'context' => 'Updating general settings'
    ];
    addLogEntry('API', "ERROR: /operator - " . json_encode($errorDetails, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}
