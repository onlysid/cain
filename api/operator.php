<?php // API to log in via tablet (on tablet app)

/* 
Standard authentication happens elsewhere for the DMS.
This is solely for the purpose of taking data from the tablet, inserting it into the database and,
optionally, awaiting a response from LIMS.

The DMS->LIMS application (AL) polls the database looking for changes in the status field.
*/

// Firstly, if we have no data, quit.
if(!$data) {
    // Throw error and stop processing things.
    echo(json_encode(["Error" => "No data available."]));
    exit;
}

// Main logic
try {
    // Check if password is required
    $passwordRequired = isPasswordRequired();
    
    // Get operatorId and password from POST request
    $operatorId = $data['operatorId'] ?? null;

    if(!$operatorId) {
        echo json_encode(['status' => 'ERROR']);
        return;
    }

    $password = $data['password'] ?? null;

    // Check if operator exists
    $operatorExists = operatorExists($operatorId);

    if ($passwordRequired == 0) {
        // Password not required
        if ($operatorExists) {
            // The user exists in the database and they don't require a password.
            echo json_encode(array('status' => 'PASS'));
        } else {
            // The user does not exist in the database but we don't need a password so add null to password column.
            externalOperatorCheck($operatorId, null);
        }
    } else {
        // Password required
        if ($operatorExists) {
            // Check password
            // TODO: Implement password checking logic here

            // For simplicity, let's assume password matches
            echo json_encode(array('status' => 'PASS'));
        } else {
            // The user does not exist in the database. Check if we have a password, if not, we will need one.
            if($password) {
                externalOperatorCheck($operatorId, $password);
                echo json_encode(array('status' => 'AUTH'));
            } else {
                echo json_encode(array('status' => 'AUTH'));
            }
        }
    }
} catch(PDOException $e) {
    // Handle database error
    http_response_code(500); // Internal Server Error
    echo json_encode(array('error' => 'Database error: ' . $e->getMessage()));
}
