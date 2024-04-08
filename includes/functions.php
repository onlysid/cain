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
    $cainVersionsTable = $cainDB->query("SHOW TABLES LIKE 'versions';");

    // If we don't have an info table, make one
    if(!$cainVersionsTable) {
        $cainDB->query(
            "CREATE TABLE `versions` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `info` VARCHAR(50) UNIQUE,
                `value` VARCHAR(50)
            );"
        );
    }

    // If we don't have any version in the info table, insert it
    $dbVersionInfo = $cainDB->select("SELECT value FROM versions WHERE info = 'web-app'");
    if(!$dbVersionInfo) {
        $cainDB->query(
            "INSERT INTO versions (info, value)
            VALUES ('web-app', '0.0.0');"
        );
        $dbVersionInfo = $cainDB->select("SELECT value FROM versions WHERE info = 'web-app'");
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

// Get current user info
function userInfo() {
    global $cainDB;
    if(!Session::isLoggedIn()) {
        return 0;
    }
    $userId = Session::get('user-id');
    $user = $cainDB->currentUserInfo($userId);

    return $user;
}

// Retrieve everything from the settings table in the db
function systemInfo() {
    global $cainDB;
    return $cainDB->selectAll("SELECT * FROM settings;");
}

function updateInstrument($instrumentData) {
    global $cainDB;
    $serialNumber = $instrumentData['serial_number'];

    if($serialNumber) {
        // Get rid of this from the instrument data array as we have it stored separately
        unset($instrumentData['serial_number']);

        // Check if the instrument already exists in the database
        $instrumentExists = $cainDB->query("SELECT id FROM instruments WHERE serial_number = '$serialNumber';");
    
        if(!$instrumentExists) {
            $query = "INSERT INTO instruments ";
            $query1 = "(serial_number, ";
            $query2 = " VALUES ('$serialNumber', ";
        } else {
            $query = "UPDATE instruments SET ";
        }

        $validDataCount = 0;
        foreach($instrumentData as $data) {
            if(isset($data)) {
                $validDataCount++;
            }
        }
    
        $i = 1;
        foreach($instrumentData as $dbCol => $data) {
            if(isset($data)) {
                if(!$instrumentExists) {
                    $query1 .= $dbCol . (($i < $validDataCount) ? ", " : ")");
                    $query2 .= "'" . $data . "'" . (($i < $validDataCount) ? ", " : ");");
                } else {
                    $query .= $dbCol . " = '" . $data . "'" . (($i < $validDataCount) ? ", " : "");
                }
                $i++;
            }
        }

        
        if($validDataCount > 0) {
            if($instrumentExists) {
                $query .= " WHERE serial_number = '$serialNumber';";
            } else {
                $query .= $query1 . $query2;
            }

            // Run the query
            $cainDB->query($query);
            return true;
        }
    }

    return false;
}

function getInstrumentSnapshot($instrumentId = null) {
    global $cainDB;

    // If the instrument ID is not passed, we get all instrument data
    if($instrumentId) {
        return $cainDB->select("SELECT * FROM instruments WHERE ?", [$instrumentId]);
    } else {
        return $cainDB->selectAll("SELECT * FROM instruments;");
    }
}