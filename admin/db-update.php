<?php
// Updates the database and its version if it is outdated
include_once __DIR__ . "/../includes/config.php";
include_once __DIR__ . "/../includes/db.php";
include BASE_DIR . "/includes/version.php";
include BASE_DIR . "/includes/functions.php";

// Increase memory limit temporarily as this can be a hefty script
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', '300');

/*
 * Executes an array of SQL queries within a transaction.
 *
 * @param object $db The database connection.
 * @param array $queries An associative array where each key is an SQL query and the value is an array of parameters.
 * @throws Exception if any query fails.
 */
function executeQueries($db, array $queries) {
    try {
        $db->beginTransaction();
        foreach ($queries as $sql => $params) {
            $params = is_array($params) ? $params : [];
            $db->query($sql, $params);
        }
        if($db->conn->inTransaction()) {
            $db->commit();
        }
    } catch (PDOException $e) {
        if ($db->conn->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

/*
 * Recursively deletes a directory and all its contents.
 *
 * @param string $dir Directory path.
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        addLogEntry('system', "ERROR: Unable to delete $dir. It is not a directory.");
        return;
    }
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $filePath = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($filePath)) {
            deleteDirectory($filePath);
        } else {
            if (unlink($filePath)) {
                addLogEntry('system', "$filePath successfully deleted.");
            } else {
                addLogEntry('system', "ERROR: Unable to delete $filePath. Please check permissions.");
            }
        }
    }
    if (rmdir($dir)) {
        addLogEntry('system', "$dir successfully deleted.");
    } else {
        addLogEntry('system', "ERROR: Unable to delete $dir. Please check permissions.");
    }
}

/*
 * Compares two version strings (semantic versioning: x.x.x).
 *
 * @param string $oldV The current version.
 * @param string $newV The target version.
 * @return bool Returns true if an update is needed (i.e. $oldV is less than $newV), false otherwise.
 */
function compareVersions($oldV, $newV) {
    $oldParts = array_map('intval', explode('.', $oldV));
    $newParts = array_map('intval', explode('.', $newV));
    if ($oldParts[0] < $newParts[0]) return true;
    if ($oldParts[0] > $newParts[0]) return false;
    if ($oldParts[1] < $newParts[1]) return true;
    if ($oldParts[1] > $newParts[1]) return false;
    return ($oldParts[2] < $newParts[2]);
}

/*
 * Automatically updates the database schema if it is outdated.
 *
 * @param string $version The target version.
 */
function autoUpdate($version) {
    global $cainDB;
    addLogEntry('system', "Running automatic updates.");

    // Check if the versions table exists.
    $versionsTable = $cainDB->select("SHOW TABLES LIKE 'versions';");
    if (!$versionsTable) {
        addLogEntry('system', "No version control found. Creating versions table.");
        $cainDB->query(
            "CREATE TABLE `versions` (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                info VARCHAR(50) UNIQUE,
                value VARCHAR(50)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );
        // Insert initial version as 0.0.0.
        $cainDB->query(
            "INSERT INTO versions (info, value) VALUES ('web-app', '0.0.0');"
        );
        addLogEntry('system', "Created version control system for SAMBA III.");
        autoUpdate($version);
        return;
    } else {
        // Get the current database version.
        $dbVersionInfo = $cainDB->select("SELECT value FROM versions WHERE info = 'web-app'");
        addLogEntry('system', "Current database version: " . ($dbVersionInfo['value'] ?? 'unknown'));

        // If the version entry does not exist, create it.
        if (!$dbVersionInfo) {
            $cainDB->query(
                "INSERT INTO versions (info, value) VALUES ('web-app', '0.0.0');"
            );
            addLogEntry('system', "Created version control entry for SAMBA III.");
            autoUpdate($version);
            return;
        } elseif ($dbVersionInfo['value'] != $version && $dbVersionInfo['value'] !== 'updating' && $dbVersionInfo['value'] !== 'error') {
            // Flag that updates are in progress.
            $cainDB->query("UPDATE versions SET value = 'updating' WHERE info = 'web-app';");
            addLogEntry('system', "Updating DMS. Upgrading from v{$dbVersionInfo['value']} to v$version.");
            runUpdates($version, $dbVersionInfo['value']);
        } else {
            // If updates are already in progress, wait until they complete.
            $timeout = 600;
            $startTime = time();
            while (time() - $startTime < $timeout) {
                $status = $cainDB->select("SELECT value FROM versions WHERE info = 'web-app';");
                if ($status && ($status['value'] !== 'updating')) {
                    break;
                }
                sleep(1);
            }
        }
    }
}

/*
 * Runs all database updates based on version differences.
 *
 * @param string $version The target version.
 * @param string $dbVersion The current database version.
 * @param bool $retry If this is the second attempt.
 */
function runUpdates($version, $dbVersion, $retry = true) {
    global $cainDB;
    try {
        // =================== Version 3.0.0 Updates =================== //
        if (compareVersions($dbVersion, "3.0.0")) {
            // Delete unused files.
            $filesToDelete = [
                'CainMedical.gif', 'delete.php', 'error-log.php', 'error.php', 'list.php', 'login.php',
                'lookup.php', 'operator.php', 'Patientresult.php', 'phpinfo.php', 'process.php',
                'send.php', 'submit.php', 'update.php', 'verify.php'
            ];
            foreach ($filesToDelete as $file) {
                $filePath = BASE_DIR . DIRECTORY_SEPARATOR . $file;
                if (file_exists($filePath)) {
                    if (unlink($filePath)) {
                        addLogEntry('system', "$file successfully deleted.");
                    } else {
                        addLogEntry('system', "ERROR: Unable to delete $file. Please check permissions.");
                    }
                }
            }

            // Recursively delete the "include" directory.
            $includeDirectory = BASE_DIR . DIRECTORY_SEPARATOR . 'include';
            if (is_dir($includeDirectory)) {
                deleteDirectory($includeDirectory);
            }

            // Alter all tables to use InnoDB and utf8mb4_unicode_ci.
            $alterQueries = [];
            $result = $cainDB->conn->query("SHOW TABLES");
            foreach ($result as $table) {
                $tableName = $table[0];
                $alterQueries["ALTER TABLE `$tableName` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"] = [];
                $alterQueries["ALTER TABLE `$tableName` ENGINE=InnoDB"] = [];
            }
            // Drop the last_command table if it exists.
            if ($cainDB->select("SHOW TABLES LIKE 'last_command';")) {
                $alterQueries["DROP TABLE last_command;"] = [];
            }
            executeQueries($cainDB, $alterQueries);

            // Add the Lot Management table if it doesn't exist.
            if (!$cainDB->select("SHOW TABLES LIKE 'lots';")) {
                executeQueries($cainDB, [
                    "CREATE TABLE lots (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        lot_number VARCHAR(100) UNIQUE,
                        sub_lot_number VARCHAR(100),
                        assay_type VARCHAR(256),
                        assay_sub_type VARCHAR(256),
                        delivery_date TIMESTAMP,
                        expiration_date TIMESTAMP,
                        qc_pass TINYINT DEFAULT 0,
                        last_updated TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;" => []
                ]);
            }

            // Update the structure of the 'results' table.
            if ($cainDB->select("SHOW TABLES LIKE 'results';")) {
                // Drop foreign key for lot_number if it exists.
                $fk = $cainDB->select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = '" . DB_NAME . "'
                    AND TABLE_NAME = 'results'
                    AND COLUMN_NAME = 'lot_number';");
                if (!empty($fk)) {
                    $fkName = $fk['CONSTRAINT_NAME'];
                    $cainDB->query("ALTER TABLE results DROP FOREIGN KEY `$fkName`;");
                }
                // Convert collation.
                $cainDB->query("ALTER TABLE results CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                // Batch alteration queries.
                $resultsAlter = [];
                $resultsAlter["ALTER TABLE results MODIFY flag INT;"] = [];
                $resultsAlter["ALTER TABLE results MODIFY post_timestamp BIGINT;"] = [];
                $columnsToCheck = [
                    'lot_number'      => "ALTER TABLE results ADD lot_number VARCHAR(100);",
                    'summary'         => "ALTER TABLE results ADD summary VARCHAR(100);",
                    'patientLocation' => "ALTER TABLE results ADD patientLocation VARCHAR(256) DEFAULT '';",
                    'sampleCollected' => "ALTER TABLE results ADD sampleCollected VARCHAR(256) DEFAULT '';",
                    'sampleReceived'  => "ALTER TABLE results ADD sampleReceived VARCHAR(256) DEFAULT '';",
                    'reserve1'        => "ALTER TABLE results ADD reserve1 VARCHAR(256) DEFAULT '';",
                    'reserve2'        => "ALTER TABLE results ADD reserve2 VARCHAR(256) DEFAULT '';",
                    'abortErrorCode'  => "ALTER TABLE results ADD abortErrorCode VARCHAR(256) DEFAULT '';"
                ];
                foreach ($columnsToCheck as $col => $sql) {
                    $exists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                        WHERE table_schema = '" . DB_NAME . "'
                        AND table_name = 'results'
                        AND column_name = '$col';");
                    if (empty($exists) || $exists['cnt'] == 0) {
                        $resultsAlter[$sql] = [];
                    }
                }
                executeQueries($cainDB, $resultsAlter);
            }

            // Adjust the 'users' table.
            if ($cainDB->select("SHOW TABLES LIKE 'users';")) {
                $usersAlter = [];
                $usersAlter["ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"] = [];
                $emailExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                    WHERE table_schema = '" . DB_NAME . "'
                    AND table_name = 'users'
                    AND column_name = 'email';");
                if ($emailExists["cnt"]) {
                    $usersAlter["RENAME TABLE users TO old_users;"] = [];
                    $usersAlter["CREATE TABLE users(
                        id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
                        operator_id VARCHAR(50) NOT NULL UNIQUE,
                        password VARCHAR(255),
                        user_id VARCHAR(255),
                        first_name VARCHAR(50),
                        last_name VARCHAR(50),
                        user_type TINYINT DEFAULT 1,
                        last_active INT,
                        status TINYINT DEFAULT 1
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"] = [];
                    $usersAlter["INSERT INTO users (operator_id, password, user_id, first_name, user_type, last_active, status)
                        SELECT email, NULL, userid, name,
                        CASE WHEN userlevel = 9 THEN 3 WHEN userlevel = 1 THEN 1 ELSE 2 END,
                        timestamp,
                        CASE WHEN status = 'Active' OR status = 'active' THEN 1 ELSE 0 END
                        FROM old_users;"] = [];
                    $usersAlter["DROP TABLE old_users;"] = [];
                }
                executeQueries($cainDB, $usersAlter);
            }

            // Ensure the Service Engineer admin exists.
            $adminExists = $cainDB->select("SELECT * FROM users WHERE operator_id = 'Admin';");
            if ($adminExists) {
                $cainDB->query("DELETE FROM users WHERE operator_id = 'Admin';");
            }
            $hashedAdminPword = '$2y$10$pBWJjc7jNtmobRv86Iidnun9DVkAjzOR2IfSippKf.Ce5qZt3VJBK';
            $cainDB->query("INSERT INTO users (operator_id, password, first_name, last_name, user_type)
                VALUES ('Admin', :pword, 'Service', 'Engineer', 3);", [":pword" => $hashedAdminPword]);

            // Create LIMS queue tables.
            $limsQueries = [];
            if (!$cainDB->select("SHOW TABLES LIKE 'process_queue';")) {
                $limsQueries["CREATE TABLE process_queue (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    status VARCHAR(50) NOT NULL
                );"] = [];
            }
            if (!$cainDB->select("SHOW TABLES LIKE 'process_data';")) {
                $limsQueries["CREATE TABLE process_data (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    `key` VARCHAR(50) NOT NULL,
                    value VARCHAR(255),
                    process_id INT,
                    FOREIGN KEY (process_id) REFERENCES process_queue(id)
                );"] = [];
            }
            executeQueries($cainDB, $limsQueries);

            // Create the software 'table' and update the versions 'table' if necessary.
            $softwareQueries = [];
            if (!$cainDB->select("SHOW TABLES LIKE 'software';")) {
                $softwareQueries["CREATE TABLE software (
                    id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(50)
                );"] = [];
                $softwareQueries["INSERT INTO software (name) VALUES ('Web App'), ('Hub'), ('Tablet App'), ('Instrument'), ('Scripts');"] = [];
            }
            $softwareFieldExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'versions'
                AND column_name = 'software';");
            if (!$softwareFieldExists["cnt"]) {
                $softwareQueries["ALTER TABLE versions ADD software INT UNSIGNED NOT NULL DEFAULT 1;"] = [];
                $softwareQueries["ALTER TABLE versions ADD FOREIGN KEY (software) REFERENCES software(id);"] = [];
                $softwareQueries["INSERT INTO versions (value, software) VALUES
                    ('3.1.0', 2), ('ER - 3.1.003', 3), ('ER - 3.1.004', 3), ('SIIIAM-0003 0 3.1.007', 4),
                    ('SIIIAM-0004 0 3.1.007', 4), ('SIIIAM-0013 0 3.1.007', 4), ('SCoV - 0.0.1', 5), ('SCoV/Flu/RSV - 0.0.2', 5);"] = [];
            }
            executeQueries($cainDB, $softwareQueries);

            // Update or create the settings table.
            $settingsQueries = [];
            if (!$cainDB->select("SHOW TABLES LIKE 'settings';")) {
                $settingsQueries["CREATE TABLE settings (
                    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
                    name VARCHAR(255) NOT NULL UNIQUE,
                    value VARCHAR(255),
                    flags INT DEFAULT 0 NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"] = [];
            } else {
                // Ensure the 'id' column is AUTO_INCREMENT and primary key.
                $idCol = $cainDB->select("SELECT COLUMN_KEY FROM information_schema.columns
                    WHERE table_schema = '" . DB_NAME . "'
                    AND table_name = 'settings'
                    AND column_name = 'id';");
                if ($idCol) {
                    $settingsQueries["ALTER TABLE settings MODIFY COLUMN id INT AUTO_INCREMENT NOT NULL;"] = [];
                    if ($idCol['COLUMN_KEY'] !== 'PRI') {
                        $settingsQueries["ALTER TABLE settings ADD PRIMARY KEY (id);"] = [];
                    }
                } else {
                    $settingsQueries["ALTER TABLE settings ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY NOT NULL;"] = [];
                }
                // Check the 'name' column.
                $nameCol = $cainDB->select("SELECT COLUMN_KEY FROM information_schema.columns
                    WHERE table_schema = '" . DB_NAME . "'
                    AND table_name = 'settings'
                    AND column_name = 'name';");
                if ($nameCol) {
                    if (!$nameCol['COLUMN_KEY']) {
                        $settingsQueries["ALTER TABLE settings MODIFY COLUMN name VARCHAR(255) NOT NULL UNIQUE;"] = [];
                    }
                } else {
                    $settingsQueries["ALTER TABLE settings ADD COLUMN name VARCHAR(255) NOT NULL UNIQUE;"] = [];
                }
                // Check the 'value' column.
                $valueCol = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                    WHERE table_schema = '" . DB_NAME . "'
                    AND table_name = 'settings'
                    AND column_name = 'value';");
                if ($valueCol['cnt']) {
                    $settingsQueries["ALTER TABLE settings MODIFY COLUMN value VARCHAR(255) NOT NULL;"] = [];
                } else {
                    $settingsQueries["ALTER TABLE settings ADD COLUMN value VARCHAR(255) NOT NULL;"] = [];
                }
                // Check the 'flags' column.
                $flagsCol = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                    WHERE table_schema = '" . DB_NAME . "'
                    AND table_name = 'settings'
                    AND column_name = 'flags';");
                if ($flagsCol['cnt']) {
                    $settingsQueries["ALTER TABLE settings MODIFY COLUMN flags INT DEFAULT 0 NOT NULL;"] = [];
                } else {
                    $settingsQueries["ALTER TABLE settings ADD COLUMN flags INT DEFAULT 0 NOT NULL;"] = [];
                }
            }
            // Ensure default settings entries exist.
            $settingsEntries = [
                ['hospital_name', 'Hospital ABC'],
                ['office_name', 'Office ABC'],
                ['hospital_location', 'Location ABC'],
                ['date_format', 'd M Y'],
                ['selected_protocol', 'HL7'],
                ['cain_server_ip', '192.168.1.237'],
                ['cain_server_port', '30000'],
                ['hl7_server_ip', '192.168.1.237'],
                ['hl7_server_port', '30000'],
                ['hl7_server_dest', 'CAIN'],
                ['comms_status', '0'],
                ['patient_id', '1'],
                ['data_expiration', '365'],
                ['password_required', '3'],
                ['session_expiration', '30'],
                ['test_mode', '0'],
                ['field_behaviour', '1466015520085'],
                ['field_visibility', '2097023'],
                ['app_mode', '1'],
                ['app_version', 'v2.0.0'],
                ['qc_policy', '0'],
                ['qc_positive_requirements', '1'],
                ['qc_negative_requirements', '1']
            ];
            foreach ($settingsEntries as $entry) {
                $name = $entry[0];
                $value = $entry[1];
                $exists = $cainDB->select("SELECT COUNT(*) AS count FROM settings WHERE name = ?;", [$name]);
                if (empty($exists) || $exists['count'] == 0) {
                    $settingsQueries["INSERT INTO settings (name, value) VALUES ('$name', '$value');"] = [];
                }
            }
            executeQueries($cainDB, $settingsQueries);

            // Create tablets, instruments, and instrument test types tables if they don't exist.
            $otherTablesQueries = [];
            if (!$cainDB->select("SHOW TABLES LIKE 'tablets';")) {
                $otherTablesQueries["CREATE TABLE tablets (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    tablet_id VARCHAR(100) UNIQUE,
                    app_version VARCHAR(100)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"] = [];
            }
            if (!$cainDB->select("SHOW TABLES LIKE 'instruments';")) {
                $otherTablesQueries["CREATE TABLE instruments (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    serial_number VARCHAR(100) UNIQUE NOT NULL,
                    module_version VARCHAR(100),
                    front_panel_id VARCHAR(100),
                    status TINYINT NOT NULL DEFAULT 0,
                    current_assay VARCHAR(255),
                    assay_start_time BIGINT,
                    duration INT,
                    device_error VARCHAR(255),
                    tablet_version VARCHAR(100),
                    enforcement TINYINT,
                    last_connected BIGINT,
                    locked TINYINT
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"] = [];
            }
            if (!$cainDB->select("SHOW TABLES LIKE 'instrument_test_types';")) {
                $otherTablesQueries["CREATE TABLE instrument_test_types (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(100) UNIQUE NOT NULL,
                    time_intervals INT,
                    result_intervals INT
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"] = [];
            }
            executeQueries($cainDB, $otherTablesQueries);

            // Create instrument QC results and lots QC results tables if they don't exist.
            $qcTablesQueries = [];
            if (!$cainDB->select("SHOW TABLES LIKE 'instrument_qc_results';")) {
                $qcTablesQueries["CREATE TABLE instrument_qc_results (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    timestamp BIGINT NOT NULL,
                    result TINYINT NOT NULL,
                    instrument INT NULL,
                    user INT NULL,
                    type INT NULL,
                    result_counter INT DEFAULT 0 NOT NULL,
                    notes TEXT,
                    FOREIGN KEY (instrument) REFERENCES instruments(id) ON DELETE SET NULL,
                    FOREIGN KEY (user) REFERENCES users(id) ON DELETE SET NULL,
                    FOREIGN KEY (type) REFERENCES instrument_test_types(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"] = [];
            }
            if (!$cainDB->select("SHOW TABLES LIKE 'lots_qc_results';")) {
                $qcTablesQueries["CREATE TABLE lots_qc_results (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    lot INT,
                    timestamp BIGINT,
                    operator_id VARCHAR(50) NULL,
                    qc_result TINYINT,
                    reference TEXT,
                    test_result INT,
                    FOREIGN KEY (lot) REFERENCES lots(id),
                    FOREIGN KEY (test_result) REFERENCES results(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"] = [];
            }
            executeQueries($cainDB, $qcTablesQueries);

            // Alter the results table to add assayType and assaySubType columns if they don't exist.
            $resultsExtraQueries = [];
            $assayTypeExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'results'
                AND column_name = 'assayType';");
            if (!$assayTypeExists['cnt']) {
                $resultsExtraQueries["ALTER TABLE results ADD assayType VARCHAR(256) NOT NULL DEFAULT '' AFTER version;"] = [];
            }
            $assaySubTypeExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'results'
                AND column_name = 'assaySubType';");
            if (!$assaySubTypeExists['cnt']) {
                $resultsExtraQueries["ALTER TABLE results ADD assaySubType VARCHAR(256) NOT NULL DEFAULT '' AFTER assayType;"] = [];
            }
            executeQueries($cainDB, $resultsExtraQueries);
        }

        // =================== Version 3.1.2 Updates =================== //
        if (compareVersions($dbVersion, "3.1.2")) {
            $updateResultsQueries = [];
            $fields = [
                'assayStepNumber' => "ALTER TABLE results %s assayStepNumber VARCHAR(255) NOT NULL DEFAULT '';",
                'cameraReadings'  => "ALTER TABLE results %s cameraReadings VARCHAR(255) NOT NULL DEFAULT '';",
                'sender'          => "ALTER TABLE results %s sender VARCHAR(255) NOT NULL DEFAULT '';",
                'sequenceNumber'  => "ALTER TABLE results %s sequenceNumber VARCHAR(255) NOT NULL DEFAULT '';"
            ];
            foreach ($fields as $field => $template) {
                $exists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                    WHERE table_schema = '" . DB_NAME . "'
                    AND table_name = 'results'
                    AND column_name = '$field';");
                if ($exists['cnt']) {
                    $updateResultsQueries[sprintf($template, "MODIFY COLUMN")] = [];
                } else {
                    $updateResultsQueries[sprintf($template, "ADD COLUMN")] = [];
                }
            }
            executeQueries($cainDB, $updateResultsQueries);
        }

        // =================== Version 3.1.3 Updates =================== //
        if (compareVersions($dbVersion, "3.1.3")) {
            $testCount = $cainDB->select("SELECT COUNT(*) AS cnt FROM instrument_test_types;")['cnt'];
            if (!$testCount) {
                executeQueries($cainDB, [
                    "INSERT INTO instrument_test_types (name, time_intervals, result_intervals) VALUES
                    ('Batch Acceptance', 90, 5000),
                    ('Engineering Visit', 180, 10000),
                    ('Environmental Test', 365, 50000),
                    ('External Quality Assurance (EQA) Scheme', 365, 50000),
                    ('Routine QC', 30, 1000);" => []
                ]);
            }
        }

        // =================== Version 3.1.6 Updates =================== //
        if (compareVersions($dbVersion, "3.1.6")) {
            $updates = [];
            $refExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'lots'
                AND column_name = 'reference';");
            if (!$refExists['cnt']) {
                $updates["ALTER TABLE lots ADD COLUMN reference TEXT;"] = [];
            }
            $defaultIdExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM settings WHERE name = 'default_id';");
            if (empty($defaultIdExists) || $defaultIdExists['cnt'] == 0) {
                $updates["INSERT INTO settings (name, value) VALUES ('default_id', 'patientId');"] = [];
            }
            executeQueries($cainDB, $updates);
        }

        // =================== Version 3.1.7 Updates ===================
        if (compareVersions($dbVersion, "3.1.7")) {
            $prodYearExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'lots'
                AND column_name = 'production_year';");
            if (!$prodYearExists['cnt']) {
                executeQueries($cainDB, [
                    "ALTER TABLE lots ADD COLUMN production_year TINYINT;" => []
                ]);
            }
        }

        // =================== Version 3.2.0 Updates =================== //
        if (compareVersions($dbVersion, "3.2.0")) {
            // Create the master_results table if it doesn't exist.
            if (!$cainDB->select("SHOW TABLES LIKE 'master_results';")) {
                executeQueries($cainDB, [
                    "CREATE TABLE master_results (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        version VARCHAR(256) DEFAULT NULL,
                        patient_id VARCHAR(256) DEFAULT NULL,
                        age VARCHAR(256) DEFAULT NULL,
                        sex VARCHAR(256) DEFAULT NULL,
                        first_name VARCHAR(256) DEFAULT NULL,
                        last_name VARCHAR(256) DEFAULT NULL,
                        dob VARCHAR(256) DEFAULT NULL,
                        nhs_number VARCHAR(256) DEFAULT NULL,
                        hospital_id VARCHAR(256) DEFAULT NULL,
                        location VARCHAR(256) DEFAULT NULL,
                        collected_time VARCHAR(256) DEFAULT NULL,
                        received_time VARCHAR(256) DEFAULT NULL,
                        start_time VARCHAR(256) DEFAULT NULL,
                        end_time VARCHAR(256) DEFAULT NULL,
                        comment_1 VARCHAR(256) DEFAULT NULL,
                        comment_2 VARCHAR(256) DEFAULT NULL,
                        operator_id VARCHAR(256) DEFAULT NULL,
                        test_purpose VARCHAR(256) DEFAULT NULL,
                        device_error VARCHAR(256) DEFAULT NULL,
                        module_serial_number VARCHAR(256) DEFAULT NULL,
                        lot_number VARCHAR(100) DEFAULT NULL,
                        assay_id VARCHAR(256) DEFAULT NULL,
                        assay_name VARCHAR(256) DEFAULT NULL,
                        assay_type VARCHAR(256) DEFAULT NULL,
                        assay_sub_type VARCHAR(256) DEFAULT NULL,
                        assay_version VARCHAR(256) DEFAULT NULL,
                        expected_result TEXT,
                        result VARCHAR(256) DEFAULT NULL,
                        FOREIGN KEY (lot_number) REFERENCES lots(lot_number) ON DELETE SET NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;" => []
                ]);
            }

            // Add a foreign key reference from the results table to the master_results table.
            try {
                $cainDB->beginTransaction();
                $resultsTableExists = $cainDB->select("SHOW TABLES LIKE 'results';");
                if (!$resultsTableExists) {
                    throw new Exception("Results table does not exist. Database may be corrupt. Please contact an admin.");
                }
                $fk = $cainDB->select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = '" . DB_NAME . "'
                    AND TABLE_NAME = 'results'
                    AND COLUMN_NAME = 'master_result';");
                if (!empty($fk)) {
                    $fkName = $fk['CONSTRAINT_NAME'];
                    $cainDB->query("ALTER TABLE results DROP FOREIGN KEY `$fkName`;");
                }
                // Check if the master_result column exists; if not, add it.
                $masterColExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'results'
                AND column_name = 'master_result';");
                if (empty($masterColExists) || $masterColExists['cnt'] == 0) {
                    $cainDB->query("ALTER TABLE results ADD master_result INT DEFAULT NULL;");
                }

                // Clean up any orphaned master_result values before adding the constraint.
                // This query sets master_result to NULL if the referenced master_results id does not exist.
                $cainDB->query("UPDATE results SET master_result = NULL
                                WHERE master_result IS NOT NULL
                                AND master_result NOT IN (SELECT id FROM master_results);");

                // Now safely add the foreign key constraint.
                $cainDB->query("ALTER TABLE results
                    ADD FOREIGN KEY (master_result)
                    REFERENCES master_results(id)
                    ON DELETE CASCADE;");
                if($cainDB->conn->inTransaction()) {
                    $cainDB->commit();
                }
            } catch (Exception $e) {
                if($cainDB->conn->inTransaction()) {
                    $cainDB->rollBack();
                }
                throw $e;
            }

            // For every existing result, create a corresponding master_results record and update the result.
            $results = $cainDB->selectAll("SELECT * FROM results ORDER BY id DESC;");
            $columnsMap = [
                'version'              => 'version',
                'patient_id'           => 'patientId',
                'age'                  => 'patientAge',
                'sex'                  => 'patientSex',
                'first_name'           => 'firstName',
                'last_name'            => 'lastName',
                'dob'                  => 'dob',
                'hospital_id'          => 'hospitalId',
                'nhs_number'           => 'nhsNumber',
                'collected_time'       => 'sampleCollected',
                'received_time'        => 'sampleReceived',
                'location'             => 'patientLocation',
                'comment_1'            => 'reserve1',
                'comment_2'            => 'reserve2',
                'result'               => 'result',
                'operator_id'          => 'operatorId',
                'test_purpose'         => 'testPurpose',
                'device_error'         => 'abortErrorCode',
                'assay_type'           => 'assayType',
                'assay_sub_type'       => 'assaySubType',
                'lot_number'           => 'lot_number',
                'module_serial_number' => 'moduleSerialNumber',
                'assay_name'           => 'product',
                'start_time'           => 'timestamp',
                'end_time'             => 'testcompletetimestamp'
            ];
            foreach ($results as $result) {
                try {
                    // Only move the result if it isn't already referencing another result
                    if($result['master_result'] == null) {
                        $columns = array_keys($columnsMap);
                        $placeholders = array_fill(0, count($columns), '?');
                        $sql = "INSERT INTO master_results (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
                        $cainDB->beginTransaction();
                        $values = [];
                        foreach ($columnsMap as $column => $key) {
                            $values[] = (isNullOrEmptyString($result[$key]) ? null : $result[$key]);
                        }
                        $cainDB->query($sql, $values);
                        $masterId = $cainDB->conn->lastInsertId();
                        $updateSql = "UPDATE results SET master_result = ? WHERE id = ?";
                        $cainDB->query($updateSql, [$masterId, $result['id']]);
                        if($cainDB->conn->inTransaction()) {
                            $cainDB->commit();
                        }
                    }
                } catch (PDOException $e) {
                    if($cainDB->conn->inTransaction()) {
                        $cainDB->rollBack();
                    }
                    throw $e;
                }
            }

            // Further results table modifications
            if ($cainDB->select("SHOW TABLES LIKE 'results';")) {
                // Remove lot_number, assay_subtype, assay_type and summary from the results table (if they exist)
                $resultsQueries = [];
                $assayTypeExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'results'
                AND column_name = 'assayType';");
                if ($assayTypeExists['cnt']) {
                    $resultsQueries["ALTER TABLE results DROP assayType;"] = [];
                }

                $lotNumberExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'results'
                AND column_name = 'lot_number';");
                if ($lotNumberExists['cnt']) {
                    $resultsQueries["ALTER TABLE results DROP lot_number;"] = [];
                }

                $assaySubTypeExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'results'
                AND column_name = 'assaySubType';");
                if ($assaySubTypeExists['cnt']) {
                    $resultsQueries["ALTER TABLE results DROP assaySubType;"] = [];
                }

                $summaryExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'results'
                AND column_name = 'summary';");
                if ($summaryExists['cnt']) {
                    $resultsQueries["ALTER TABLE results DROP summary;"] = [];
                }

                $ctExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'results'
                AND column_name = 'ct_values';");
                if (!$ctExists['cnt']) {
                    $resultsQueries["ALTER TABLE results ADD ct_values VARCHAR(256);"] = [];
                }

                executeQueries($cainDB, $resultsQueries);
            }

            // Add a ct flag to the database
            $updates = [];
            $defaultIdExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM settings WHERE name = 'visible_ct';");
            if (empty($defaultIdExists) || $defaultIdExists['cnt'] == 0) {
                $updates["INSERT INTO settings (name, value) VALUES ('visible_ct', '0');"] = [];
            }
            executeQueries($cainDB, $updates);

            // Reassign lots_qc_results information from results table to the new master_results table
            $updates = [];
            try {
                $cainDB->beginTransaction();
                // This is quite complex. If there is a foreign key to the test_result data, remove it
                $resultsTableExists = $cainDB->select("SHOW TABLES LIKE 'lots_qc_results';");
                if (!$resultsTableExists) {
                    throw new Exception("Lots QC table does not exist. Database may be corrupt. Please contact an admin.");
                }
                $fk = $cainDB->select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = '" . DB_NAME . "'
                    AND TABLE_NAME = 'lots_qc_results'
                    AND COLUMN_NAME = 'test_result';");
                if (!empty($fk)) {
                    $fkName = $fk['CONSTRAINT_NAME'];
                    $cainDB->query("ALTER TABLE lots_qc_results DROP FOREIGN KEY `$fkName`;");

                    // Now we need to loop through every result and update the test_result column to have the ID of the master_result instead of the result.
                    $testResults = $cainDB->query("UPDATE lots_qc_results q
                        LEFT JOIN results r ON q.test_result = r.id
                        SET q.test_result = r.master_result;
                    ");

                    // We do this here because if we never had a foreign key, then we never needed this logic to begin with
                }

                // Finally, we need to add a foreign key to the master results table
                $fk = $cainDB->select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = '" . DB_NAME . "'
                    AND TABLE_NAME = 'lots_qc_results'
                    AND COLUMN_NAME = 'test_result';");
                if (empty($fk)) {
                    $cainDB->query("ALTER TABLE lots_qc_results ADD FOREIGN KEY (test_result) REFERENCES master_results(id) ON DELETE CASCADE;");
                }

                if($cainDB->conn->inTransaction()) {
                    $cainDB->commit();
                }
            } catch (Exception $e) {
                if($cainDB->conn->inTransaction()) {
                    $cainDB->rollBack();
                }
                throw $e;
            }
        }

        if(compareVersions($dbVersion, "3.2.1")) {
            $updates = [];

            // Add an overall_result field to the results table
            $overallColExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
            WHERE table_schema = '" . DB_NAME . "'
            AND table_name = 'results'
            AND column_name = 'overall_result';");
            if (empty($overallColExists) || $overallColExists['cnt'] == 0) {
                $cainDB->query("ALTER TABLE results ADD overall_result varchar(256) DEFAULT NULL;");

                // Copy all results to the overall results column
                $cainDB->query("UPDATE results SET overall_result = result;");
            }

            // Add a verbose logging option to the settings table
            $verboseLogging = $cainDB->select("SELECT COUNT(*) AS cnt FROM settings WHERE name = 'verbose_logging';");
            if (empty($verboseLogging) || $verboseLogging['cnt'] == 0) {
                $updates["INSERT INTO settings (name, value) VALUES ('verbose_logging', '0');"] = [];
            }

            // Execute queries for 3.2.1
            executeQueries($cainDB, $updates);
        }

        if(compareVersions($dbVersion, "3.3.0")) {
            $updates = [];

            // Add a moduleName field to capture an instrument's friendly name (or null if it does not exist)
            $moduleNameColExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
            WHERE table_schema = '" . DB_NAME . "'
            AND table_name = 'instruments'
            AND column_name = 'module_name';");
            if(empty($moduleNameColExists) || $moduleNameColExists['cnt'] == 0) {
                $cainDB->query("ALTER TABLE instruments ADD module_name varchar(256) DEFAULT NULL;");
            }

            // Add backup settings for LIMS simulator support
            $backupSettingNames = [
                'cain_server_ip_backup',
                'cain_server_port_backup',
                'selected_protocol_backup'
            ];

            foreach ($backupSettingNames as $backupName) {
                $rowExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM settings WHERE `name` = ?;", [$backupName]);
                if (empty($rowExists) || $rowExists['cnt'] == 0) {
                    $cainDB->query("INSERT INTO settings (`name`, `value`) VALUES (?, 0);", [$backupName]);
                }
            }

            // Add simulator tables
            if (!$cainDB->select("SHOW TABLES LIKE 'simulator_operators';")) {
                $updates["CREATE TABLE simulator_operators (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    operator_id VARCHAR(50) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"] = [];
            }

            if (!$cainDB->select("SHOW TABLES LIKE 'simulator_patients';")) {
                $updates["CREATE TABLE simulator_patients (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    patientId VARCHAR(50),
                    hospitalId VARCHAR(50),
                    nhsNumber VARCHAR(50),
                    firstName VARCHAR(50),
                    lastName VARCHAR(50),
                    dob VARCHAR(50),
                    patientSex VARCHAR(5),
                    patientAge VARCHAR(5)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"] = [];
            }
            
            executeQueries($cainDB, $updates);
        }

        if (compareVersions($dbVersion, "3.5.0")) {
            $updates = [];

            // Add a result_target field (if missing)
            $resultTargetColExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'results'
                AND column_name = 'result_target';");
            if (empty($resultTargetColExists) || (int)$resultTargetColExists['cnt'] === 0) {
                $cainDB->query("ALTER TABLE results ADD result_target varchar(256) DEFAULT NULL AFTER product;");

                // Copy product -> result_target
                $cainDB->query("
                    UPDATE results r
                    SET r.result_target = r.product;
                ");

                // ---- Build eligible set: version is numeric and >= 3.0 ----
                $cainDB->query("
                    DROP TEMPORARY TABLE IF EXISTS _eligible_results;
                    CREATE TEMPORARY TABLE _eligible_results AS
                    SELECT id, master_result
                    FROM results
                    WHERE version REGEXP '^[0-9]+(\\.[0-9]+)?$'
                    AND CAST(version AS DECIMAL(10,4)) >= 3.0;
                ");

                // For multiplex only: product = assay_name (only eligible rows; skip singlets)
                $cainDB->query("
                    UPDATE results r
                    JOIN _eligible_results e ON e.id = r.id
                    JOIN master_results mr ON mr.id = r.master_result
                    JOIN (
                        SELECT master_result
                        FROM results
                        WHERE master_result IS NOT NULL
                        GROUP BY master_result
                        HAVING COUNT(*) > 1
                    ) m ON m.master_result = r.master_result
                    SET r.product = mr.assay_name
                    WHERE mr.assay_name IS NOT NULL
                    AND mr.assay_name <> '';
                ");

                // For multiplex only: format product as *product*result_target (only eligible rows)
                $cainDB->query("
                    UPDATE results r
                    JOIN _eligible_results e ON e.id = r.id
                    JOIN (
                        SELECT master_result
                        FROM results
                        WHERE master_result IS NOT NULL
                        GROUP BY master_result
                        HAVING COUNT(*) > 1
                    ) m ON m.master_result = r.master_result
                    SET r.product = CONCAT('*', TRIM(r.product), '*', TRIM(r.result_target))
                    WHERE COALESCE(r.result_target, '') <> '';
                ");

                // Clean up (temporary tables auto-drop on session end)
                $cainDB->query('DROP TEMPORARY TABLE IF EXISTS _eligible_results;');
            }

            // Update datetimes to have second-resolution where possible
            $timestampColExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'results'
                AND column_name = 'timestamp';");
            if (!empty($timestampColExists) && (int)$timestampColExists['cnt'] !== 0) {
                $cainDB->query("
                    UPDATE results
                    SET timestamp = CONCAT(timestamp, ':00')
                    WHERE timestamp REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}$';
                ");

                // Enforce different timestamps for results of the same multiplex
                $cainDB->query("
                    DROP TEMPORARY TABLE IF EXISTS tmp_ts;

                    CREATE TEMPORARY TABLE tmp_ts AS
                    SELECT
                    b.id,
                    b.master_result,
                    b.ts,
                    @off := IF(@grp = b.master_result AND @prev_ts = b.ts, @off + 1, 0) AS off,
                    @prev_ts := b.ts AS _set_prev_ts,
                    @grp := b.master_result AS _set_grp
                    FROM (
                    SELECT
                        r.id,
                        r.master_result,
                        STR_TO_DATE(r.`timestamp`, '%Y-%m-%d %H:%i:%s') AS ts
                    FROM results r
                    WHERE STR_TO_DATE(r.`timestamp`, '%Y-%m-%d %H:%i:%s') IS NOT NULL
                    ) AS b
                    JOIN (SELECT @grp := NULL, @prev_ts := NULL, @off := 0) vars
                    ORDER BY b.master_result, b.ts, b.id;

                    UPDATE results r
                    JOIN tmp_ts t ON t.id = r.id
                    SET r.`timestamp` = DATE_FORMAT(DATE_ADD(t.ts, INTERVAL t.off SECOND), '%Y-%m-%d %H:%i:%s');

                    DROP TEMPORARY TABLE tmp_ts;
                ");
            }

            $testCompleteTimestampColExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'results'
                AND column_name = 'testcompletetimestamp';");
            if (!empty($testCompleteTimestampColExists) && (int)$testCompleteTimestampColExists['cnt'] !== 0) {
                $cainDB->query("
                    UPDATE results
                    SET testcompletetimestamp = CONCAT(testcompletetimestamp, ':00')
                    WHERE testcompletetimestamp REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}$';
                ");

                // Enforce different testcompletetimestamp for multiplex
                $cainDB->query("
                    DROP TEMPORARY TABLE IF EXISTS tmp_tct;

                    CREATE TEMPORARY TABLE tmp_tct AS
                    SELECT
                    b.id,
                    b.master_result,
                    b.ts,
                    @off := IF(@grp = b.master_result AND @prev_ts = b.ts, @off + 1, 0) AS off,
                    @prev_ts := b.ts AS _set_prev_ts,
                    @grp := b.master_result AS _set_grp
                    FROM (
                    SELECT
                        r.id,
                        r.master_result,
                        STR_TO_DATE(r.`testcompletetimestamp`, '%Y-%m-%d %H:%i:%s') AS ts
                    FROM results r
                    WHERE STR_TO_DATE(r.`testcompletetimestamp`, '%Y-%m-%d %H:%i:%s') IS NOT NULL
                    ) AS b
                    JOIN (SELECT @grp := NULL, @prev_ts := NULL, @off := 0) vars
                    ORDER BY b.master_result, b.ts, b.id;

                    UPDATE results r
                    JOIN tmp_tct t ON t.id = r.id
                    SET r.`testcompletetimestamp` = DATE_FORMAT(DATE_ADD(t.ts, INTERVAL t.off SECOND), '%Y-%m-%d %H:%i:%s');

                    DROP TEMPORARY TABLE tmp_tct;
                ");
            }
        }

        if(compareVersions($dbVersion, "3.7.0")) {
            $updates = [];

            // Add setting to send invalid result to LIMS
            $sendInvalidResultsToLims = $cainDB->select("SELECT COUNT(*) AS cnt FROM settings WHERE name = 'send_invalid_results_to_lims';");
            if (empty($sendInvalidResultsToLims) || $sendInvalidResultsToLims['cnt'] == 0) {
                $updates["INSERT INTO settings (name, value) VALUES ('send_invalid_results_to_lims', '1');"] = [];
            }

            // Add setting to configure the default retry timeout (basically, the default retry count for results -- 12 ~ 60s)
            $limsRetryTimeout = $cainDB->select("SELECT COUNT(*) AS cnt FROM settings WHERE name = 'lims_retry_timeout';");
            if (empty($limsRetryTimeout) || $limsRetryTimeout['cnt'] == 0) {
                $updates["INSERT INTO settings (name, value) VALUES ('lims_retry_timeout', '12');"] = [];
            }

            // Add retry_count to the results field
            $retryCountExists = $cainDB->select("SELECT COUNT(*) AS cnt FROM information_schema.columns
                WHERE table_schema = '" . DB_NAME . "'
                AND table_name = 'results'
                AND column_name = 'retry_count';");
            if (!$retryCountExists['cnt']) {
                $updates["ALTER TABLE results ADD retry_count SMALLINT DEFAULT 12;"] = [];
            }

            // Execute queries for 3.7.0
            executeQueries($cainDB, $updates);
        }

        // =================== Version 100.0.0 Updates (Test) ===================
        if (compareVersions($dbVersion, "100.0.0")) {
            sleep(2);
        }

        // Finally, update the database version to the target version.
        $cainDB->query("UPDATE versions SET value = '$version' WHERE info = 'web-app';");
        addLogEntry('system', "DMS successfully updated. Now running version $version.");
        echo("Successfully updated.");
    } catch (Exception $e) {
        // On error, mark the version as error.
        $cainDB->query("UPDATE versions SET value = 'error' WHERE info = 'web-app';");
        // Now double-check integrity on first error.
        if ($retry) {
            $conn = $cainDB->connect();
            if (checkAndSetupRequiredTables($conn)) {
                // If tables were missing and have now been created, try updates once more.
                runUpdates($version, $dbVersion, false);
                return;
            } else {
                // If tables exist, rethrow.
                throw $e;
            }
        } else {
            throw $e;
        }
        logUpdateError("Updating DMS", $e);
        echo("Something has gone wrong. Please speak with an admin about database integrity. <br><br> Message: $e");
        return;
    }
}

// Kick off the auto-update process.
autoUpdate($version);