<?php // Updates the database and its version if it is outdated
include_once __DIR__ . "/../includes/config.php";
include_once __DIR__ . "/../includes/db.php";
include BASE_DIR . "/includes/version.php";

function autoUpdate($version) {
    global $cainDB;
    $infoTable = $cainDB->select("SHOW TABLES LIKE 'info';");

    if(!$infoTable) {
        $cainDB->query(
                "CREATE TABLE `info` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `info` VARCHAR(50) UNIQUE,
                `value` VARCHAR(50)
            );
        ");
        $cainDB->query(
            "INSERT INTO info (info, value)
            VALUES ('version', '0.0.0');"
        );
        autoUpdate($version);
    } else {
        $dbVersionInfo = $cainDB->select("SELECT value FROM info WHERE info = 'version'");
        if(!$dbVersionInfo) {
            $cainDB->query(
                "INSERT INTO info (info, value)
                VALUES ('version', '0.0.0');"
            );
            autoUpdate($version);
        } elseif($dbVersionInfo['value'] != $version) {
            // Run updates
            runUpdates($version, $dbVersionInfo['value']);
            
            // Set a flag to indicate updates are in progress
            $cainDB->query("UPDATE info SET value = 'updating' WHERE info = 'version';");

            // Set the flag to indicate updates are complete
            $cainDB->query("UPDATE info SET value = '$version' WHERE info = 'version';");
        }
    }
}

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

    // v3.0.0 - We jumped ahead a bit, believe it or not!
    if(compareVersions($dbVersion, "3.0.0")) {
        // TODO: Change the results flag from tinyint 4 to int 11

        // Adjustments to the users table
        $usersTableExists = $cainDB->select("SHOW TABLES LIKE 'users';");

        foreach($change as $dbQuery) {
            $cainDB->query($dbQuery);
        }
        $change = [];
    }

    // Test long processes
    if(compareVersions($dbVersion, "100.0.0")) {
        sleep(5);
    }
 
    // Finally, update the DB version to match the app version
    $updateVersionQuery = "UPDATE info SET value = '$version' WHERE info = 'version'";
    $cainDB->query($updateVersionQuery);

}

// Set the session to determine if any of this is necessary!
autoUpdate($version);

echo("Successfully updated.");