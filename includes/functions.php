<?php // General functions

// Function to fetch password_required setting from the database
function isPasswordRequired() {
    global $cainDB;
    $setting = $cainDB->select("SELECT `value` FROM settings WHERE `name` = 'password_required';");
    return $setting['value'] == 1 ? true : false;
}

// Function to check if a user exists in the local database
function operatorExists($operatorId) {
    global $cainDB;
    $result = $cainDB->select("SELECT COUNT(*) as count FROM users WHERE operator_id = :operatorId;", [':operatorId' => $operatorId]);
    return $result['count'] > 0;
}

function checkForUpdates($version) {
    global $cainDB;
    $tomInfoTables = $cainDB->query("SHOW TABLES LIKE 'info';");

    // If we don't have an info table, make one
    if(!$tomInfoTables) {
        $cainDB->query(
            "CREATE TABLE `info` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `info` VARCHAR(50) UNIQUE,
                `value` VARCHAR(50)
            );"
        );
    }

    // If we don't have any version in the info table, insert it
    $dbVersionInfo = $cainDB->select("SELECT value FROM info WHERE info = 'version'");
    if(!$dbVersionInfo) {
        $cainDB->query(
            "INSERT INTO info (info, value)
            VALUES ('version', '0.0.0');"
        );
        $dbVersionInfo = $cainDB->select("SELECT value FROM info WHERE info = 'version'");
    } 

    // Process the response
    if ($dbVersionInfo['value'] == 'updating') {
        // Updates already in progress
        return 100;
    } elseif($dbVersionInfo['value'] == 'error') {
        // Error updating
        return 200;
    } elseif($dbVersionInfo['value'] != $version) {
        // Run updates
        return true;
    }
    // We do not need updating!
    return false;
}

function userInfo() {
    global $cainDB;
    if(!Session::isLoggedIn()) {
        return 0;
    }
    $userId = Session::get('user-id');
    $user = $cainDB->currentUserInfo($userId);

    return $user;
}