<?php // Updates the database and its version if it is outdated
include_once __DIR__ . "/../includes/config.php";
include_once __DIR__ . "/../includes/db.php";
include BASE_DIR . "/includes/version.php";
include BASE_DIR . "/includes/functions.php";

function autoUpdate($version) {
    global $cainDB;
    addLogEntry('system', "Running automatic updates.");

    // Get a list of all versions tables
    $versionsTable = $cainDB->select("SHOW TABLES LIKE 'versions';");

    if(!$versionsTable) {
        addLogEntry('system', "No version control found.");

        // If the versions table does not exist, create it!
        $cainDB->query(
                "CREATE TABLE `versions` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `info` VARCHAR(50) UNIQUE,
                `value` VARCHAR(50)
            );
        ");

        // Insert a value of 0.0.0. If the table doesn't exist, we must assume the site is at version 0.
        $cainDB->query(
            "INSERT INTO versions (info, value)
            VALUES ('web-app', '0.0.0');"
        );
        addLogEntry('system', "Created version control system for SAMBA III.");

        // Reccur the function
        autoUpdate($version);
    } else {
        // We know the table exists, so get the version of the database.
        $dbVersionInfo = $cainDB->select("SELECT value FROM versions WHERE info = 'web-app'");
        addLogEntry('system', "Getting database version.");

        // Just in case, check if the dbVersionInfo exists. If not, create it.
        if(!$dbVersionInfo) {
            $cainDB->query(
                "INSERT INTO versions (info, value)
                VALUES ('web-app', '0.0.0');"
            );

            addLogEntry('system', "Created version control system for SAMBA III.");

            // Recur the function
            autoUpdate($version);
        } elseif($dbVersionInfo['value'] != $version && $dbVersionInfo['value'] !== 'updating' && $dbVersionInfo !== 'error') {
            // Set a flag to indicate updates are in progress (if we are not already in progress)
            $cainDB->query("UPDATE versions SET `value` = 'updating' WHERE info = 'web-app';");

            addLogEntry('system', "Updating DMS. Upgrading from v{$dbVersionInfo['value']} to v$version.");

            // Run updates
            runUpdates($version, $dbVersionInfo['value']);
        } else {
            // We are already updating. Initiate some timeouts.
            $timeout = 600;
            $startTime = time();

            // Incrementally check the db for if we have finished updating.
            while (time() - $startTime < $timeout) {
                // Check the status of the queue entry
                $status = $cainDB->select("SELECT value FROM versions WHERE info = 'web-app';");

                // If update is successful, we will break out of our loop.
                if ($status && ($status['value'] !== 'updating')) {
                    break;
                }

                // Sleep for 1 second before polling again
                sleep(1);
            }
        }
    }
}

// Function to compare two version strings (semantic versioning x.x.x)
function compareVersions($oldV, $newV) {
    $oldV = explode(".", $oldV);
    $newV = explode(".", $newV);
    $oldV = array_map('intval', $oldV);
    $newV = array_map('intval', $newV);

    if ($oldV[0] < $newV[0]) {
        return 1;
    } elseif ($oldV[0] > $newV[0]) {
        return 0;
    } elseif ($oldV[1] < $newV[1]) {
        return 1;
    } elseif ($oldV[1] > $newV[1]) {
        return 0;
    } elseif ($oldV[2] < $newV[2]) {
        return 1;
    } else {
        return 0;
    }
}

// Function to run all updates to the database
function runUpdates($version, $dbVersion) {
    global $cainDB, $casinoGames;

    $dbVersion = $cainDB->select("SELECT value FROM versions WHERE info = 'web-app';")['value'];

    /*
     * Here we outline the versions of the database for which we need to run updates.
     * If the database is particularly old, it will run through each of these statements,
     * executing tasks one by one until it reaches the end. Newer versions of the site will skip
     * to their current DB version and execute the tasks following.
     */
    $change = [];

    // We need to track if anything goes wrong, and where.
    $caught = [];

    // v3.0.0 - We jumped ahead a bit, believe it or not!
    if(compareVersions($dbVersion, "3.0.0")) {

        // Firstly, we must go through and delete unused files
        $filesToDelete = [
            'CainMedical.gif',
            'delete.php',
            'error-log.php',
            'error.php',
            'list.php',
            'login.php',
            'lookup.php',
            'operator.php',
            'Patientresult.php',
            'phpinfo.php',
            'process.php',
            'send.php',
            'submit.php',
            'update.php',
            'verify.php'
        ];

        // Loop through the files and attempt to delete each one
        foreach ($filesToDelete as $file) {
            $filePath = BASE_DIR . DIRECTORY_SEPARATOR . $file;

            // Check if the file exists
            if (file_exists($filePath)) {
                if(unlink($filePath)) {
                    addLogEntry('system', "$file successfully deleted.");
                } else {
                    addLogEntry('system', "ERROR: Unable to delete $file. Please check permissions.");
                }
            }
        }


        // Function to recursively delete a directory and its contents
        function deleteDirectory($dir) {
            // Check if the directory exists
            if (!is_dir($dir)) {
                addLogEntry('system', "ERROR: Unable to delete $dir. It is not a directory.");
                return;
            }

            // Get all files and directories within the directory
            $files = array_diff(scandir($dir), array('.', '..')); // Exclude '.' and '..'

            foreach ($files as $file) {
                $filePath = $dir . DIRECTORY_SEPARATOR . $file;

                // If it's a directory, recursively call deleteDirectory
                if (is_dir($filePath)) {
                    deleteDirectory($filePath); // Recursively delete subdirectory
                } else {
                    // If it's a file, delete it
                    if(unlink($filePath)) {
                        addLogEntry('system', "$filePath successfully deleted.");
                    } else {
                        addLogEntry('system', "ERROR: Unable to delete $filePath. Please check permissions.");
                    }
                }
            }

            // After deleting contents, remove the directory itself
            if(rmdir($dir)) {
                addLotEntry('system', "$dir successfully deleted.");
            } else {
                addLogEntry('system', "ERROR: Unable to delete $dir. Please check permissions.");
            }
        }

        // Recursively delete the "include" directory and its contents
        $includeDirectory = BASE_DIR . DIRECTORY_SEPARATOR . 'include';
        if (is_dir($includeDirectory)) {
            deleteDirectory($includeDirectory);
        }

        // We are going to get all the tables as we have many alterations to perform
        $result = $cainDB->conn->query("SHOW TABLES");
        foreach($result as $table) {
            // Set whole DB to InnoDB utf8mb4_unicode_ci
            $change[] = "ALTER TABLE `$table[0]` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $change[] = "ALTER TABLE `$table[0]` ENGINE=InnoDB";
        }

        $lastCommandTableExists = $cainDB->select("SHOW TABLES LIKE 'last_command';");
        if($lastCommandTableExists) {
            $change[] = "DROP TABLE last_command;";
        }

        foreach($change as $dbQuery) {
            try {
                $cainDB->query($dbQuery);
            } catch(PDOException $e) {
                $caught[] = $e;
            }
        }
        $change = [];

        // Add the Lot Management Table
        $lotTable = $cainDB->select("SHOW TABLES LIKE 'lots';");
        if(!$lotTable) {
            $change[] = "CREATE TABLE lots (
                id INT PRIMARY KEY AUTO_INCREMENT,
                `lot_number` varchar(100) UNIQUE,
                `sub_lot_number` varchar(100),
                `assay_type` varchar(256),
                `assay_sub_type` varchar(256),
                `delivery_date` timestamp,
                `expiration_date` timestamp,
                `qc_pass` tinyint DEFAULT 0,
                `last_updated` timestamp
            );";
        }

        foreach($change as $dbQuery) {
            try {
                $cainDB->query($dbQuery);
            } catch(PDOException $e) {
                $caught[] = $e;
            }
        }
        $change = [];

        $resultsTableExists = $cainDB->select("SHOW TABLES LIKE 'results';");
        if($resultsTableExists) {

            // Drop any foreign keys related to lot_number
            $foreignKey = $cainDB->select("SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = '" . DB_NAME . "'
                AND TABLE_NAME = 'results'
                AND COLUMN_NAME = 'lot_number';");

            if (!empty($foreignKey)) {
                $foreignKeyName = $foreignKey['CONSTRAINT_NAME'];
                $cainDB->query("ALTER TABLE results DROP FOREIGN KEY `$foreignKeyName`;");
            }

            // Change the collation of the table
            $cainDB->query("ALTER TABLE results CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

            $change[] = "ALTER TABLE results MODIFY flag int;";
            $change[] = "ALTER TABLE results MODIFY post_timestamp BIGINT;";

            $lotsColumnExists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'results' AND column_name = 'lot_number';");
            if (!empty($lotsColumnExists) && $lotsColumnExists['COUNT(*)'] == 0) {
                // Ensure the column has the same data type as in the lots table
                $cainDB->query("ALTER TABLE results ADD lot_number varchar(100);");
            }

            // Add the foreign key (we know by this point that it does not exist)
            $change[] = "ALTER TABLE results ADD FOREIGN KEY (lot_number) REFERENCES lots(lot_number);";

            $summaryColumnExists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'results' AND column_name = 'summary';");
            if (!empty($summaryColumnExists) && $summaryColumnExists['COUNT(*)'] == 0) {
                // Ensure the column has the same data type as in the lots table
                $change[] = "ALTER TABLE results ADD summary varchar(100);";
            }

            $patientLocationExists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'results' AND column_name = 'patientLocation';");
            if (!empty($patientLocationExists) && $patientLocationExists['COUNT(*)'] == 0) {
                // Ensure the column has the same data type as in the lots table
                $change[] = "ALTER TABLE results ADD patientLocation varchar(256) DEFAULT '';";
            }

            $sampleCollectedExists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'results' AND column_name = 'sampleCollected';");
            if (!empty($sampleCollectedExists) && $sampleCollectedExists['COUNT(*)'] == 0) {
                // Ensure the column has the same data type as in the lots table
                $change[] = "ALTER TABLE results ADD sampleCollected varchar(256) DEFAULT '';";
            }

            $sampleReceivedExists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'results' AND column_name = 'sampleReceived';");
            if (!empty($sampleReceivedExists) && $sampleReceivedExists['COUNT(*)'] == 0) {
                // Ensure the column has the same data type as in the lots table
                $change[] = "ALTER TABLE results ADD sampleReceived varchar(256) DEFAULT '';";
            }

            $reserve1Exists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'results' AND column_name = 'reserve1';");
            if (!empty($reserve1Exists) && $reserve1Exists['COUNT(*)'] == 0) {
                // Ensure the column has the same data type as in the lots table
                $change[] = "ALTER TABLE results ADD reserve1 varchar(256) DEFAULT '';";
            }

            $reserve2Exists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'results' AND column_name = 'reserve2';");
            if (!empty($reserve2Exists) && $reserve2Exists['COUNT(*)'] == 0) {
                // Ensure the column has the same data type as in the lots table
                $change[] = "ALTER TABLE results ADD reserve2 varchar(256) DEFAULT '';";
            }

            $abortErrorCodeExists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'results' AND column_name = 'abortErrorCode';");
            if (!empty($abortErrorCodeExists) && $abortErrorCodeExists['COUNT(*)'] == 0) {
                // Ensure the column has the same data type as in the lots table
                $change[] = "ALTER TABLE results ADD abortErrorCode varchar(256) DEFAULT '';";
            }
        }

        // Adjustments to the users table
        $usersTableExists = $cainDB->select("SHOW TABLES LIKE 'users';");
        if($usersTableExists) {
            // Alter collation
            $change[] = "ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";

            $emailFieldExists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'users' AND column_name = 'email';");
            if($emailFieldExists["COUNT(*)"]) {
                $change[] = "RENAME TABLE users TO old_users";
                $change[] = "CREATE TABLE users(
                    id int AUTO_INCREMENT PRIMARY KEY NOT NULL,
                    operator_id varchar(50) NOT NULL UNIQUE,
                    `password` varchar(255),
                    user_id varchar(255),
                    first_name varchar(50),
                    last_name varchar(50),
                    user_type tinyint DEFAULT 1,
                    last_active int,
                    `status` tinyint DEFAULT 1
                );";
                $change[] = "INSERT INTO users (operator_id, `password`, user_id, first_name, user_type, last_active, status)
                    SELECT email, null, userid, `name`,
                        CASE
                            WHEN userlevel = 9 THEN 3
                            WHEN userlevel = 1 THEN 1
                            ELSE 2
                        END AS user_type,
                        `timestamp` AS last_active,
                        CASE
                            WHEN `status` = 'Active' or `status` = 'active' THEN 1
                            ELSE 0
                        END AS `status`
                    FROM old_users;";

                $change[] = "DROP TABLE old_users;";
            }
        }

        // Add some settings
        $settingsTableExists = $cainDB->select("SHOW TABLES LIKE 'settings'");
        if($settingsTableExists) {
            $cainDB->query("DROP TABLE `settings`;");
        }

        foreach($change as $dbQuery) {
            try {
                $cainDB->query($dbQuery);
            } catch(PDOException $e) {
                $caught[] = $e;
            }
        }
        $change = [];

        // If no service engineer admin exists, add them.
        $adminExists = $cainDB->select("SELECT * FROM users WHERE operator_id = 'Admin';");
        if($adminExists) {
            $cainDB->query("DELETE FROM users WHERE operator_id = 'Admin';");
        }
        $hashedAdminPword = '$2y$10$QWZHRHYXXkun1pygvEOeyOJ6NeMPPEtFf8Wfq77NH/8HBpd6zCQRG';
        $cainDB->query("INSERT INTO users (`operator_id`, `password`, `first_name`, `last_name`, `user_type`) VALUES ('Admin', :pword, 'Service', 'Engineer', 3);", [":pword" => $hashedAdminPword]);

        // Add the LIMS queue tables
        $processQueueExists = $cainDB->select("SHOW TABLES LIKE 'process_queue';");
        if(!$processQueueExists) {
            $change[] = "CREATE TABLE process_queue (
                id INT PRIMARY KEY AUTO_INCREMENT,
                `status` VARCHAR(50) NOT NULL
            );";
        }

        $processDataExists = $cainDB->select("SHOW TABLES LIKE 'process_data';");
        if(!$processDataExists) {
            $change[] = "CREATE TABLE process_data (
                id INT PRIMARY KEY AUTO_INCREMENT,
                `key` VARCHAR(50) NOT NULL,
                `value` VARCHAR(255),
                process_id INT,
                FOREIGN KEY (process_id) REFERENCES process_queue(id)
            );";
        }

        foreach($change as $dbQuery) {
            try {
                $cainDB->query($dbQuery);
            } catch(PDOException $e) {
                $caught[] = $e;
            }
        }
        $change = [];

        $softwareTableExists = $cainDB->select("SHOW TABLES LIKE 'software';");
        if(!$softwareTableExists) {
            $change[] = "CREATE TABLE software (
                id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `name` VARCHAR(50)
            );";

            $change[] = "INSERT INTO software (`name`) VALUES ('Web App'), ('Hub'), ('Tablet App'), ('Instrument'), ('Scripts');";
        }

        $softwareFieldExists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'versions' AND column_name = 'software';");
        if(!$softwareFieldExists["COUNT(*)"]) {
            $change[] = "ALTER TABLE versions ADD software int UNSIGNED NOT NULL DEFAULT 1;";
            $change[] = "ALTER TABLE versions ADD FOREIGN KEY (software) REFERENCES software(id);";

            $change[] = "INSERT INTO versions (`value`, `software`) VALUES ('3.1.004', 2), ('ER - 3.1.003', 3), ('ER - 3.1.004', 3), ('SIIIAM-0003 0 3.1.007', 4), ('SIIIAM-0004 0 3.1.007', 4), ('SIIIAM-0013 0 3.1.007', 4), ('SCoV - 0.0.1', 5), ('SCoV/Flu/RSV - 0.0.2', 5);";
        }

        foreach($change as $dbQuery) {
            try {
                $cainDB->query($dbQuery);
            } catch(PDOException $e) {
                $caught[] = $e;
            }
        }
        $change = [];

        // We need to change the settings page!
        $settingsTableExists = $cainDB->select("SHOW TABLES LIKE 'settings';");
        if(!$settingsTableExists) {
            $change[] = "CREATE TABLE `settings` (
                id int AUTO_INCREMENT PRIMARY KEY NOT NULL,
                `name` varchar(255) NOT NULL UNIQUE,
                `value` varchar(255),
                `flags` int DEFAULT 0 NOT NULL
            );";

            $change[] = "INSERT INTO settings (`name`, `value`) VALUES
                ('hospital_name', 'Hospital ABC'),
                ('office_name', 'Office ABC'),
                ('hospital_location', 'Location ABC'),
                ('date_format', 'd M Y'),
                ('selected_protocol', 'HL7'),
                ('cain_server_ip', '192.168.1.237'),
                ('cain_server_port', '30000'),
                ('hl7_server_ip', '192.168.1.237'),
                ('hl7_server_port', '30000'),
                ('hl7_server_dest', 'CAIN'),
                ('comms_status', '0'),
                ('patient_id', '1'),
                ('data_expiration', '365'),
                ('password_required', '3'),
                ('session_expiration', '30'),
                ('test_mode', '0'),
                ('field_behaviour', '1466015520085'),
                ('field_visibility', '2097023'),
                ('app_mode', '1'),
                ('app_version', 'v2.0.0'),
                ('qc_policy', '0'),
                ('qc_positive_requirements', '1'),
                ('qc_negative_requirements', '1')
            ;";
        }

        foreach($change as $dbQuery) {
            try {
                $cainDB->query($dbQuery);
            } catch(PDOException $e) {
                $caught[] = $e;
            }
        }
        $change = [];

        // Add the tablets table
        $tabletsTableExists = $cainDB->select("SHOW TABLES LIKE 'tablets';");
        if(!$tabletsTableExists) {
            $change[] = "CREATE TABLE tablets (
                id INT PRIMARY KEY AUTO_INCREMENT,
                tablet_id varchar(100) UNIQUE,
                `app_version` varchar(100)
            );";
        }

        // Add the Instrument table
        $instrumentsTableExists = $cainDB->select("SHOW TABLES LIKE 'instruments';");
        if(!$instrumentsTableExists) {
            $change[] = "CREATE TABLE instruments (
                `id` INT PRIMARY KEY AUTO_INCREMENT,
                `serial_number` varchar(100) UNIQUE NOT NULL,
                `module_version` varchar(100),
                `front_panel_id` varchar(100),
                `status` tinyint NOT NULL DEFAULT 0,
                `current_assay` varchar(255),
                `assay_start_time` bigint,
                `duration` int,
                `device_error` varchar(255),
                `tablet_version` varchar(100),
                `enforcement` tinyint,
                `last_connected` bigint,
                `locked` tinyint
            );";
        }

        // Add Instrument QC Test Types
        $instrumentQCTestTypesTableExists = $cainDB->select("SHOW TABLES LIKE 'instrument_test_types';");
        if(!$instrumentQCTestTypesTableExists) {
            $change[] = "CREATE TABLE instrument_test_types (
                `id` INT PRIMARY KEY AUTO_INCREMENT,
                `name` varchar(100) UNIQUE NOT NULL,
                `time_intervals` int,
                `result_intervals` int
            );";
        }

        // Required for Foreign Key Later
        foreach($change as $dbQuery) {
            try {
                $cainDB->query($dbQuery);
            } catch(PDOException $e) {
                $caught[] = $e;
            }
        }
        $change = [];

        // TODO: Add some default instrument QC test types

        // Add Instrument QC Results
        $instrumentQCResultsTableExists = $cainDB->select("SHOW TABLES LIKE 'instrument_qc_results';");
        if (!$instrumentQCResultsTableExists) {
            $change[] = "CREATE TABLE instrument_qc_results (
                `id` INT PRIMARY KEY AUTO_INCREMENT,
                `timestamp` BIGINT NOT NULL,
                `result` TINYINT NOT NULL,
                `instrument` INT NULL,
                `user` INT NULL,
                `type` INT NULL,
                `result_counter` INT DEFAULT 0 NOT NULL,
                `notes` TEXT,
                FOREIGN KEY (`instrument`) REFERENCES instruments(id) ON DELETE SET NULL,
                FOREIGN KEY (`user`) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (`type`) REFERENCES instrument_test_types(id) ON DELETE SET NULL
            );";
        }

        // Add the QC Results table
        $qcResultsTableExists = $cainDB->select("SHOW TABLES LIKE 'lots_qc_results';");
        if(!$qcResultsTableExists) {
            $change[] = "CREATE TABLE lots_qc_results (
                id INT PRIMARY KEY AUTO_INCREMENT,
                lot INT,
                `timestamp` BIGINT,
                `operator_id` varchar(50) NULL,
                `qc_result` TINYINT,
                `reference` TEXT,
                `test_result` INT,
                FOREIGN KEY (lot) REFERENCES lots(id),
                FOREIGN KEY (`test_result`) REFERENCES results(id) ON DELETE SET NULL
            );";
        }

        // Whilst we no longer need many of the columns in the results table, we are keeping them for reasons of spec fluctuations and indecisiveness.

        // Large changes to the results table. Firstly, we no longer need "sender" if it exists
        // $senderFieldExists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'results' AND column_name = 'sender';");
        // if($senderFieldExists['COUNT(*)']) {
        //     $change[] = "ALTER TABLE results DROP COLUMN sender;";
        // }

        // No longer need sequenceNumber
        // $sequenceNumberFieldExists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'results' AND column_name = 'sequenceNumber';");
        // if($sequenceNumberFieldExists['COUNT(*)']) {
        //     $change[] = "ALTER TABLE results DROP COLUMN sequenceNumber;";
        // }

        // No longer need assayStepNumber
        // $assayStepNumberFieldExists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'results' AND column_name = 'assayStepNumber';");
        // if($assayStepNumberFieldExists['COUNT(*)']) {
        //     $change[] = "ALTER TABLE results DROP COLUMN assayStepNumber;";
        // }

        // No longer need cameraReadings
        // $cameraReadingsFieldExists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'results' AND column_name = 'cameraReadings';");
        // if($cameraReadingsFieldExists['COUNT(*)']) {
        //     $change[] = "ALTER TABLE results DROP COLUMN cameraReadings;";
        // }

        // Add assayType column
        $assayTypeColumnExists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'results' AND column_name = 'assayType';");
        if(!$assayTypeColumnExists['COUNT(*)']) {
            $change[] = "ALTER TABLE results ADD assayType VARCHAR(256) NOT NULL DEFAULT '' AFTER `version`;";
        }

        // Add assaySubType column
        $assaySubTypeColumnExists = $cainDB->select("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . DB_NAME . "' AND table_name = 'results' AND column_name = 'assaySubType';");
        if(!$assaySubTypeColumnExists['COUNT(*)']) {
            $change[] = "ALTER TABLE results ADD assaySubType VARCHAR(256) NOT NULL DEFAULT '' AFTER `assayType`;";
        }

        // A few mappings should be considered, though not necessarily implemented due to LIMS integrity

        /*
            sampleCollected = collectedTime
            sampleReceived = receivedTime
            reserve1 = comment1
            reserve2 = comment2
            timestamp = startTime
            testcompletetimestamp = endTime
            abortCodeError = deviceError
            product = assayName
            firstName = patientFirstName
            lastName = patientLastName
            dob = patientDoB
        */

        foreach($change as $dbQuery) {
            try {
                $cainDB->query($dbQuery);
            } catch(PDOException $e) {
                $caught[] = $e;
            }
        }
        $change = [];
    }

    // Test long processes (and add a few seconds for psychological validation)
    if(compareVersions($dbVersion, "100.0.0")) {
        sleep(2);
    }

    // Finally, update the DB version to match the app version
    if($caught) {
        $cainDB->query("UPDATE versions SET value = 'error' WHERE info = 'web-app';");
        echo('<br>');
        echo("Something has gone wrong. Please speak with an admin about database integrity.");
        echo("<br>");
        echo("Error: $caught[0]");

        // Log detailed information securely
        $errorDetails = [
            'error_message' => $caught[0]->getMessage(),
            'stack_trace' => $caught[0]->getTraceAsString(),
            'user_id' => $currentUser['operator_id'] ?? 'unknown',
            'context' => 'Updating DMS'
        ];
        addLogEntry('system', "ERROR: " . json_encode($errorDetails, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return;
    }

    // Give some feedback to the AJAX call if everything went well
    echo("Successfully updated.");

    // Set the flag to indicate updates are complete
    $cainDB->query("UPDATE versions SET `value` = '$version' WHERE info = 'web-app';");
    addLogEntry('system', "DMS successfully updated. Now running version $version.");
}

// Set the session to determine if any of this is necessary!
autoUpdate($version);