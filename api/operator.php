<?php // API to log in via tablet (on tablet app)

/* 
Standard authentication happens elsewhere for the DMS.
This is solely for the purpose of taking data from the tablet, inserting it into the database and,
optionally, awaiting a response from LIMS.

The DMS->LIMS application (AL) polls the database looking for changes in the status field. When it gets a response, we update the 
*/

// Firstly, if we have no data, quit.
if(!$data) {
    // Throw error and stop processing things.
    echo(json_encode(["Error" => "No data available."]));
    exit;
}

// Function to fetch password_required setting from the database
function getPasswordRequiredSetting(PDO $pdo) {
    $stmt = $pdo->query("SELECT value FROM settings WHERE `name` = 'password_required'");
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);
    return $setting['password_required'] ?? 0;
}

// Function to check if a user exists in the local database
function userExists(PDO $pdo, $username) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = :username");
    $stmt->execute([':username' => $username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] > 0;
}

// Function to add user to the queue (for LIMS processing)
function addUserToQueue(PDO $pdo, $username, $password) {
    $stmt = $pdo->prepare("INSERT INTO queue (email, password, status) VALUES (:username, :password, 'pending')");
    $stmt->execute([':username' => $username, ':password' => $password]);

    // Poll the database for the status change
    $timeout = 300; // Timeout in seconds (5 minutes)
    $start_time = time();

    while (time() - $start_time < $timeout) {
        // Sleep for 1 second before polling again
        sleep(1);

        // Check the status of the queue entry
        $stmt = $pdo->prepare("SELECT status FROM queue WHERE email = :username");
        $stmt->execute([':username' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $status = $result['status'] ?? '';

        // If status is 'pass' or 'fail', return the status
        if ($status === 'pass' || $status === 'fail') {
            echo json_encode(array('status' => $status));
            break;
        }
    }

    // If timeout reached without status change, return 'BUSY'
    if (time() - $start_time >= $timeout) {
        echo json_encode(array('status' => 'BUSY'));
    }

    // Now delete from the queue
    $stmt = $pdo->prepare("DELETE FROM queue WHERE email = :username");
    $stmt->execute([':username' => $username]);
}

// Main logic
try {
    // Connect to database
    $pdo = new PDO('mysql:host=localhost;dbname=patientresult_db', 'tom', 'S/@W4^p}Z$4/tV-{c]<[');
    
    // Check if password is required
    $passwordRequired = getPasswordRequiredSetting($pdo);
    
    // Get username and password from POST request
    $username = $data['username'] ?? null;

    if(!$username) {
        echo json_encode(['status' => 'ERROR']);
        return;
    }

    $password = $data['password'] ?? null;

    // Check if user exists
    $userExists = userExists($pdo, $username);

    if ($passwordRequired == 0) {
        // Password not required
        if ($userExists) {
            // The user exists in the database and they don't require a password.
            echo json_encode(array('status' => 'PASS'));
        } else {
            // The user does not exist in the database but we don't need a password so add null to password column.
            addUserToQueue($pdo, $username, null);
        }
    } else {
        // Password required
        if ($userExists) {
            // Check password
            // TODO: Implement your password checking logic here

            // For simplicity, let's assume password matches
            echo json_encode(array('status' => 'PASS'));
        } else {
            // The user does not exist in the database. Check if we have a password, if not, we will need one.
            if($password) {
                addUserToQueue($pdo, $username, $password);
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
