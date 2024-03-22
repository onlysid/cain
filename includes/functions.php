<?php // General functions

// Function to fetch password_required setting from the database
function isPasswordRequired() {
    global $cainDB;
    $setting = $cainDB->select("SELECT value FROM settings WHERE `name` = 'password_required';");
    return $setting['password_required'] ?? 0;
}

// Function to check if a user exists in the local database
function operatorExists($operatorId) {
    global $cainDB;
    $result = $cainDB->select("SELECT COUNT(*) as count FROM users WHERE email = :operatorId;", [':operatorId' => $operatorId]);
    return $result['count'] > 0;
}

function checkForUpdates($version) {
    global $cainDB;
    $tomInfoTables = $cainDB->query("SHOW TABLES LIKE 'info';");

    if(!$tomInfoTables) {
        $cainDB->query(
            "CREATE TABLE `info` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `info` VARCHAR(50) UNIQUE,
                `value` VARCHAR(50)
            );"
        );
        $cainDB->query(
            "INSERT INTO info (info, value)
            VALUES ('version', '0.0.0');"
        );
        autoUpdateTomDB($version);
    } else {
        $dbVersionInfo = $cainDB->select("SELECT value FROM info WHERE info = 'version'");
        if(!$dbVersionInfo) {
            $cainDB->query(
                "INSERT INTO info (info, value)
                VALUES ('version', '0.0.0');"
            );
            autoUpdateTomDB($version);
        } elseif ($dbVersionInfo['value'] == 'updating') {
            // Code to mean that updates are already in process
            return 100;
        } 
        elseif($dbVersionInfo['value'] != $version) {
            // Run updates
            return true;
        }
    }
    return false;
}