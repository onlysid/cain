<?php // Functions specific to API calls

// Function to delete all data related to a given process
function deleteProcessData($processId) {
    global $cainDB;
    try {
        // Begin a transaction
        $cainDB->beginTransaction();

        // Delete data associated with the process from process_data table
        $cainDB->query("DELETE FROM process_data WHERE process_id = :processId", array(':processId' => $processId));

        // Delete the process from process_queue table
        $cainDB->query("DELETE FROM process_queue WHERE id = :processId", array(':processId' => $processId));

        // Commit the transaction
        $cainDB->commit();

        // Reset auto-increment counter for process_data table
        $cainDB->query("ALTER TABLE process_data AUTO_INCREMENT = 1");

        // Reset auto-increment counter for process_queue table
        $cainDB->query("ALTER TABLE process_queue AUTO_INCREMENT = 1");

    } catch(PDOException $e) {
        // Rollback the transaction on error
        $cainDB->rollBack();
        // Throw the exception for handling at a higher level
        throw $e;
    }
}

// Function to add user to the queue (for LIMS processing)
function externalOperatorCheck($operatorId) {
    global $cainDB;

    try {
        // Start a transaction to ensure data consistency across tables
        $cainDB->beginTransaction();

        // Insert the process into the process queue
        $cainDB->query("INSERT INTO process_queue (status) VALUES (40)");

        // Get the last inserted process ID
        $processId = $cainDB->conn->lastInsertId();

        // Insert 'operatorId' into the process_data table and link it to the process in the queue
        $cainDB->query("INSERT INTO process_data (process_id, `key`, value) VALUES (?, 'operatorId', ?)", [$processId, $operatorId]);

        // Commit the transaction
        $cainDB->commit();

        // Call a separate function to handle polling and status check
        return pollProcessStatus($processId, 42);

    } catch(PDOException $e) {
        // Rollback the transaction on error
        $cainDB->rollBack();
        throw $e;
    }
}

// Function to poll process status and handle deletion
function pollProcessStatus($processId, $completionStatus) {
    global $cainDB;
    
    // Poll the database for the status change (timeout according to the LIMS Timeout settings)
    $startTime = time();
    $response = false;

    while (time() - $startTime < LIMS_TIMEOUT) {
        // Check the status of the queue entry
        $status = $cainDB->select("SELECT status FROM process_queue WHERE id = ?", [$processId]);

        // If status is 'pass' or 'fail', return the status
        if ($status && ($status['status'] === strval($completionStatus))) {
            // Check if operator ID is accepted
            $response = true;
            break;
        }

        // Sleep for 1 second before polling again
        sleep(1);
    }

    // Delete process data and process
    deleteProcessData($processId);
    return $response;
}
