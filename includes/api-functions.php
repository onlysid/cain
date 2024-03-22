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
function externalOperatorCheck($operatorId, $password) {
    global $cainDB;

    try {
        // Start a transaction to ensure data consistency across tables
        $cainDB->beginTransaction();

        // Insert the process into the process queue
        $cainDB->query("INSERT INTO process_queue (status) VALUES (40)");

        // Get the last inserted process ID
        $processId = $cainDB->conn->lastInsertId();

        // Insert 'operatorId' and 'password' data into the process_data table and link it to the process in the queue
        $cainDB->query("INSERT INTO process_data (process_id, `key`, value) VALUES (?, 'operatorId', ?)", [$processId, $operatorId]);
        $cainDB->query("INSERT INTO process_data (process_id, `key`, value) VALUES (?, 'password', ?)", [$processId, $password]);

        // Commit the transaction
        $cainDB->commit();

        // Call a separate function to handle polling and status check
        pollProcessStatus($processId);

    } catch(PDOException $e) {
        // Rollback the transaction on error
        $cainDB->rollBack();
        throw $e; // Re-throw the exception for handling at a higher level
    }
}

// Function to poll process status and handle deletion
function pollProcessStatus($processId) {
    global $cainDB;
    // Poll the database for the status change (timeout in 20s)
    $timeout = 20;
    $startTime = time();

    while (time() - $startTime < $timeout) {
        // Check the status of the queue entry
        $status = $cainDB->select("SELECT status FROM process_queue WHERE id = ?", [$processId]);

        // If status is 'pass' or 'fail', return the status
        if ($status && ($status['status'] === 42)) {
            // Check if operator ID is accepted
            echo json_encode(array('status' => "Operator ID Accepted"));
            break;
        }

        // Sleep for 1 second before polling again
        sleep(1);
    }

    // If timeout reached without status change, return 'BUSY'
    if (time() - $startTime >= $timeout) {
        echo json_encode(array('status' => 'BUSY'));
    }

    // Delete process data and process
    deleteProcessData($processId);
}
