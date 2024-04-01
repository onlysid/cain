<?php // Updates the database and its version if it is outdated
include_once __DIR__ . "/../includes/config.php";
include_once __DIR__ . "/../includes/db.php";
include BASE_DIR . "/includes/version.php";

function autoUpdate($version) {
    global $cainDB;

    // Get a list of all info tables
    $infoTable = $cainDB->select("SHOW TABLES LIKE 'info';");

    if(!$infoTable) {
        // If the info table does not exist, create it!
        $cainDB->query(
                "CREATE TABLE `info` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `info` VARCHAR(50) UNIQUE,
                `value` VARCHAR(50)
            );
        ");

        // Insert a value of 0.0.0. If the table doesn't exist, we must assume the site is at version 0.
        $cainDB->query(
            "INSERT INTO info (info, value)
            VALUES ('version', '0.0.0');"
        );

        // Reccur the function
        autoUpdate($version);
    } else {
        // We know the table exists, so get the version of the database.
        $dbVersionInfo = $cainDB->select("SELECT value FROM info WHERE info = 'version'");
        
        // Just in case, check if the dbVersionInfo exists. If not, create it.
        if(!$dbVersionInfo) {
            $cainDB->query(
                "INSERT INTO info (info, value)
                VALUES ('version', '0.0.0');"
            );

            // Recur the function
            autoUpdate($version);
        } elseif($dbVersionInfo['value'] != $version && $dbVersionInfo['value'] !== 'updating' && $dbVersionInfo !== 'error') {
            // Set a flag to indicate updates are in progress (if we are not already in progress)
            $cainDB->query("UPDATE info SET `value` = 'updating' WHERE info = 'version';");
            
            // Run updates
            runUpdates($version, $dbVersionInfo['value']);
        } else {
            // We are already updating. Initiate some timeouts.
            $timeout = 600;
            $startTime = time();

            // Incrementally check the db for if we have finished updating.
            while (time() - $startTime < $timeout) {
                // Check the status of the queue entry
                $status = $cainDB->select("SELECT value FROM info WHERE info = 'version';");

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
    
    $dbVersion = $cainDB->select("SELECT value FROM info WHERE info = 'version';")['value'];

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
        // We are going to get all the tables as we have many alterations to perform
        $result = $cainDB->conn->query("SHOW TABLES");
        foreach($result as $table) {
            // Set whole DB to InnoDB utf8mb4_general_ci
            $change[] = "ALTER TABLE `$table[0]` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
            $change[] = "ALTER TABLE `$table[0]` ENGINE=InnoDB";
        }

        $lastCommandTableExists = $cainDB->select("SHOW TABLES LIKE 'last_command';");
        if($lastCommandTableExists) {
            $change[] = "DROP TABLE last_command;";
        }

        foreach($change as $dbQuery) {
            try {
                $cainDB->query($dbQuery);
            } catch(PDOException $exception) {
                $caught[] = $exception;
            }
        }
        $change = [];

        $resultsTableExists = $cainDB->select("SHOW TABLES LIKE 'results';");
        if($resultsTableExists) {
            $change[] = "ALTER TABLE results MODIFY flag int;";
        }

        // Adjustments to the users table
        $usersTableExists = $cainDB->select("SHOW TABLES LIKE 'users';");
        if($usersTableExists) {
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
            $passwordRequiredField = $cainDB->select("SELECT * FROM settings WHERE name = 'password_required'");
            if(!$passwordRequiredField) {
                $change[] = "ALTER TABLE settings MODIFY `flags` int;";
                $change[] = "INSERT INTO settings (`name`, `value`) VALUES ('password_required', '1');";
            }
        }

        foreach($change as $dbQuery) {
            try {
                $cainDB->query($dbQuery);
            } catch(PDOException $e) {
                echo($e);
                $caught[] = $e;
            }    
        }
        $change = [];

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
                echo($e);
                $caught[] = $e;
            }    
        }
        $change = [];
    }

    // Test long processes (and add a few seconds for psychological validation)
    if(compareVersions($dbVersion, "100.0.0")) {
        sleep(3);
    }
 
    // Finally, update the DB version to match the app version
    if($caught) {
        $cainDB->query("UPDATE info SET value = 'error' WHERE info = 'version';");
        echo("Something has gone wrong. Please speak with an admin about database integrity.");
        echo("<br>");
        echo("Error: $caught[0]");
        return;
    }

    // Give some feedback to the AJAX call if everything went well
    echo("Successfully updated.");

    // Set the flag to indicate updates are complete
    $cainDB->query("UPDATE info SET `value` = '$version' WHERE info = 'version';");
}

// Set the session to determine if any of this is necessary!
autoUpdate($version);