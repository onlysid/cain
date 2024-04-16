<?php // General functions

// Function to fetch password_required setting from the database
function isPasswordRequired() {
    global $cainDB;
    $setting = $cainDB->select("SELECT `value` FROM settings WHERE `name` = 'password_required';");

    // 0 = Not Required, 1 = Required only for Admins, 2 = Required only for Non-Admins, 3 = Required Globally
    return intval($setting['value']);
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

// Retrieve LIMS connectivity status from db
function limsConnectivity() {
    global $cainDB;
    return $cainDB->select("SELECT value FROM settings WHERE `name` = 'comms_status';");
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

function getResults($params) {
    global $cainDB;

    // Get any query params
    $searchFilter = isset($params['s']) ? $params['s'] : null;
    $sortDirection = isset($params['sd']) && ($params['sd'] != "" || $params['sd'] == "asc") ? "ASC" : "DESC";
    $sortParam = isset($params['sp']) && $params['sp'] != "" ? $params['sp'] : "testcompletetimestamp";
    $pageNumber = isset($params['p']) ? $params['p'] : 1;
    $itemsPerPage = isset($params['ipp']) ? $params['ipp'] : 10;
    $offset = ($pageNumber - 1) * $itemsPerPage;

    // Construct the WHERE clause for searching across all columns
    $searchConditions = '';
    $queryParams = [];
    if ($searchFilter !== null) {
        $searchTerms = explode(" ", $searchFilter);
        $columns = ['firstName', 'lastName', 'hospitalId', 'nhsNumber', 'clinicId', 'operatorId', 'patientId', 'sampleId', 'trackingCode', 'patientLocation', 'result', 'product']; // Replace with your actual column names

        $i = 0;
        foreach($searchTerms as $filterString) {
            $searchConditions .= "AND (";
            $j = 0;
            foreach ($columns as $column) {
                $searchConditions .= ($j != 0 ? "OR " : "") . "$column LIKE :filterString$i ";
                $queryParams[':filterString' . $i] = str_replace(' ', '%', $filterString) . '%';
                $j++;
            }
            $searchConditions .= ") ";
            $i++;
        }
        // Remove the leading 'OR' from the first condition
        $searchConditions = ltrim($searchConditions, 'AND');
    }

    // Build the SQL query
    $query = "SELECT * FROM results ";
    $countQuery = "SELECT COUNT(*) FROM results ";
    if (!empty($searchConditions)) {
        $query .= "WHERE $searchConditions ";
        $countQuery .= "WHERE $searchConditions ";
    }
    $query .= "ORDER BY $sortParam $sortDirection ";

    // Apply pagination
    $query .= " LIMIT $itemsPerPage OFFSET $offset";

    // Get the results
    $results = $cainDB->selectAll($query, $queryParams);

    // Get the count of all results
    $count = $cainDB->select($countQuery, $queryParams);

    // Get the relevant results
    return ["results" => $results, "count" => $count['COUNT(*)']];
}

function truncate($string, $length = 100, $append = "&hellip;") {
    $string = trim($string);
  
    if(strlen($string) > $length) {
        $string = wordwrap($string, $length);
        $string = explode("\n", $string, 2);
        $string = $string[0] . $append;
    }
  
    return $string;
}

function updateQueryString($keyValues, $resetPage = false) {
    // Get the current URL
    $currentUrl = $_SERVER['REQUEST_URI'];

    // Parse the URL to separate the path and query string
    $parts = parse_url($currentUrl);

    // Parse the query string into an associative array
    $query = [];
    if (isset($parts['query'])) {
        parse_str($parts['query'], $query);
    }

    // Update or add the specified key-value pairs
    foreach ($keyValues as $key => $value) {
        $query[$key] = $value;
    }

    // Reset the page if requested
    if ($resetPage && isset($query['p'])) {
        unset($query['p']);
    }

    // Rebuild the query string
    $queryString = http_build_query($query);

    // Reassemble the URL
    $newUrl = $parts['path'];
    if (!empty($queryString)) {
        $newUrl .= '?' . $queryString;
    }

    return $newUrl;
}

function checkResultCapacity() {
    global $cainDB;

    $resultsCount = $cainDB->select("SELECT COUNT(*) FROM results;")['COUNT(*)'];

    if($resultsCount >= floor(MAX_RESULTS * 0.9)) {
        Session::setWarning("max-results-reached");
    }

    return $resultsCount;
}

// Function to get all non-service admin operators
function getOperators() {
    global $cainDB;

    return $cainDB->selectAll("SELECT * FROM users WHERE user_type < 3;");
}