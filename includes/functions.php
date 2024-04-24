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
function userInfo($operatorId = null) {
    global $cainDB;
    if(!Session::isLoggedIn()) {
        return 0;
    }
    $userId = Session::get('user-id') ?? $operatorId;
    $user = $cainDB->userInfo($userId);

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

function updateInstruments($tabletData) {
    global $cainDB;
    /*
        We get a JSON object of ALL instruments for a given tablet {"tablet_id" => {"serial_number" => {Instrument Data}, {"serial_number" => {Instrument Data}}}
        Each tablet can have up to 8 instruments (not really relevant for this section but important to understand globally)
        Select all instruments in the db with the associated tablet.
        Loop through the instruments in the JSON data and update any instruments in the db with its new data.
        If an instrument in the JSON data does not have a corresponding db object, add it to the db. (may need additional serial number checks)
        If an instrument in the db does not have a corresponding object in the JSON data, set its status to 0.
    */

    $tabletSerial = $tabletData['tablet_id'];
    $tabletData = $tabletData['tablet_data'];

    if($tabletSerial) {
        // We have some data to parse! Start a transaction.
        try {
            $cainDB->beginTransaction();

            // Get the tablet ID
            $tabletId = $cainDB->select("SELECT id FROM tablets WHERE tablet_id = :tabletSerial;", [":tabletSerial" => $tabletSerial])['id'];

            if(!$tabletId) {
                // Insert the tablet into the database
                $cainDB->query("INSERT INTO tablets (`tablet_id`) VALUES ('$tabletSerial');");

                // Get the last inserted process ID
                $tabletId = $cainDB->conn->lastInsertId();
            }

            // At this stage, we definitely have an ID for the tablet. Now we need to get a list of instrument IDs who have this tablet ID.
            $oldTabletInstrumentsArray = $cainDB->selectAll("SELECT serial_number FROM instruments WHERE tablet = :tabletId;", [":tabletId" => $tabletId]);
            $oldTabletInstruments = [];

            // Make a standard array of old instrument serial numbers
            foreach($oldTabletInstrumentsArray as $oldInstrument) {
                $oldTabletInstruments[] = $oldInstrument['serial_number'];
            }

            // Similarly, get a list of all instruments
            $allDbInstrumentsArray = $cainDB->selectAll("SELECT serial_number FROM instruments;");
            $allDbInstruments = [];

            // Make a standard array of old instrument serial numbers
            foreach($allDbInstrumentsArray as $dbInstrument) {
                $allDbInstruments[] = $dbInstrument['serial_number'];
            }

            // We know all the new data for instruments connected to the tablet (tabletData). Loop through them and update the ones still connected.
            foreach($tabletData as $serialNumber => $data) {
                $instrumentData['module_id'] = $data['frontPanelId'] ?? null;
                $instrumentData['status'] = $data['status'] ?? null;
                $instrumentData['progress'] = $data['progress'] ?? null;
                $instrumentData['time_remaining'] = $data['timeRemaining'] ?? null;
                $instrumentData['last_connected'] = time();
                $instrumentData['fault_code'] = $data['faultCode'] ?? null;
                $instrumentData['version_number'] = $data['versionNumber'] ?? null;
                $instrumentData['tablet'] = $tabletId;

                $instrumentExists = in_array($serialNumber, $allDbInstruments);

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
                }

                $oldTabletInstruments = array_diff($oldTabletInstruments, [$serialNumber]);
            }

            // Loop through the remaining instruments which were once associated with the tablet and dissociate them
            foreach($oldTabletInstruments as $disconnectedInstrument) {
                $cainDB->query("UPDATE instruments SET `status` = 0, tablet = null WHERE `serial_number` = :serialNumber;", [":serialNumber" => $disconnectedInstrument]);
            }

            // Commit the transaction
            $cainDB->commit();

            return true;

        } catch(PDOException $e) {
            // Rollback the transaction on error
            $cainDB->rollBack();
            // Throw the exception for handling at a higher level
            throw $e;
        }
    }

    // Something has gone wrong!
    return false;
}

function getInstrumentSnapshot($instrumentId = null) {
    global $cainDB;

    // If the instrument ID is not passed, we get all instrument data
    if($instrumentId) {
        return $cainDB->select("SELECT * FROM instruments i INNER JOIN tablets t ON i.tablet = t.id WHERE ? ORDER BY last_connected DESC, time_remaining ASC;", [$instrumentId]);
    } else {
        return $cainDB->selectAll("SELECT * FROM instruments i INNER JOIN tablets t ON i.tablet = t.id ORDER BY last_connected DESC, time_remaining ASC;");
    }
}

function getResults($params, $itemsPerPage) {
    global $cainDB;

    // Get any query params
    $searchFilter = isset($params['s']) ? $params['s'] : null;
    $sortDirection = isset($params['sd']) && ($params['sd'] != "" || $params['sd'] == "asc") ? "ASC" : "DESC";
    $sortParam = isset($params['sp']) && $params['sp'] != "" ? $params['sp'] : "testcompletetimestamp";
    $pageNumber = isset($params['p']) ? $params['p'] : 1;
    $offset = ($pageNumber - 1) * $itemsPerPage;

    // Construct the WHERE clause for searching across all columns
    $searchConditions = '';
    $queryParams = [];
    if ($searchFilter !== null) {
        $searchTerms = explode(" ", $searchFilter);
        $columns = ['firstName', 'lastName', 'hospitalId', 'nhsNumber', 'clinicId', 'operatorId', 'patientId', 'sampleId', 'trackingCode', 'patientLocation', 'result', 'product'];

        $i = 0;
        foreach($searchTerms as $filterString) {
            $searchConditions .= "AND (";
            $j = 0;
            foreach ($columns as $column) {
                $searchConditions .= ($j != 0 ? "OR " : "") . "$column LIKE :filterString$i ";
                $queryParams[':filterString' . $i] = '%' . str_replace(' ', '%', $filterString) . '%';
                $j++;
            }
            $searchConditions .= ") ";
            $i++;
        }
        // Remove the leading 'OR' from the first condition
        $searchConditions = ltrim($searchConditions, 'AND');
    }

    // Build the SQL query
    $query = "SELECT *, r.id AS result_id FROM results r LEFT JOIN lots i ON i.lot_number = r.lot_number ";
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

function getLots($params, $itemsPerPage) {
    global $cainDB;

    // Get any query params
    $searchFilter = isset($params['s']) ? $params['s'] : null;
    $sortDirection = isset($params['sd']) && ($params['sd'] != "" || $params['sd'] == "asc") ? "ASC" : "DESC";
    $sortParam = isset($params['sp']) && $params['sp'] != "" ? $params['sp'] : "id";
    $pageNumber = isset($params['p']) ? $params['p'] : 1;
    $offset = ($pageNumber - 1) * $itemsPerPage;

    // Construct the WHERE clause for searching across all columns
    $searchConditions = '';
    $queryParams = [];
    if ($searchFilter !== null) {
        $searchTerms = explode(" ", $searchFilter);
        $columns = ['lot_number', 'production_year', 'expiration_year', 'expiration_month', 'assay_type', 'production_run', 'sub_lot', 'assay_sub_type', 'check_digit'];

        $i = 0;
        foreach($searchTerms as $filterString) {
            $searchConditions .= "AND (";
            $j = 0;
            foreach ($columns as $column) {
                $searchConditions .= ($j != 0 ? "OR " : "") . "$column LIKE :filterString$i ";
                $queryParams[':filterString' . $i] = '%' . str_replace(' ', '%', $filterString) . '%';
                $j++;
            }
            $searchConditions .= ") ";
            $i++;
        }
        // Remove the leading 'OR' from the first condition
        $searchConditions = ltrim($searchConditions, 'AND');
    }

    // Build the SQL query
    $query = "SELECT * FROM lots ";
    $countQuery = "SELECT COUNT(*) FROM lots ";
    if (!empty($searchConditions)) {
        $query .= "WHERE $searchConditions ";
        $countQuery .= "WHERE $searchConditions ";
    }
    $query .= "ORDER BY $sortParam $sortDirection ";

    // Apply pagination
    $query .= " LIMIT $itemsPerPage OFFSET $offset";

    // Get the lots
    $lots = $cainDB->selectAll($query, $queryParams);

    // Get the count of all lots
    $count = $cainDB->select($countQuery, $queryParams);

    // Get the relevant results
    return ["lots" => $lots, "count" => $count['COUNT(*)']];
}

// Function to sanitize and validate input data
function testInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
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
function getOperators($currentUserId = null) {
    global $cainDB;

    if($currentUserId) {
        return $cainDB->selectAll("SELECT * FROM users WHERE user_type < 3 AND id != :currentUser;", [":currentUser" => $currentUserId]);
    } else {
        return $cainDB->selectAll("SELECT * FROM users WHERE user_type <3;");
    }
}

// Function to get the behaviour of all fields (for the tablet)
function getFieldBehaviourSettings($dataFields, $behaviourFields) {
    $result = [];
    foreach($dataFields as $index => $dataField) {
        $behaviour = "Hidden";
        switch($behaviourFields[$index]) {
            case 1:
                $behaviour = "Visible";
                break;
            case 2:
                $behaviour = "Mandatory";
                break;
            default:
                break;

        }

        // Account for data field overrides
        if($field->behaviourLock) {
            $behaviour = "Automatic";
        }

        $result[$dataField->name] = $behaviour;
    }

    return $result;
}

function getFieldVisibilitySettings($dataFields, $visibilityFields) {
    $result = [];
    foreach($dataFields as $index => $dataField) {
        if($visibilityFields[$index] && !$dataField->visibilityLock) {
            $result[$dataField->dbName] = $dataField->name;
        }
    }

    return $result;
}

function updateTablet($tabletId, $appVersion) {
    global $cainDB;

    if($tabletId) {
        // Check if we have the tablet in the db
        $tabletExists = $cainDB->select("SELECT * FROM tablets WHERE tablet_id = ?;", [$tabletId]);

        // If it exists, update it. If not, create it.
        if($tabletExists) {
            $cainDB->query("UPDATE tablets SET app_version = ? WHERE tablet_id = ?;", [$appVersion, $tabletId]);
            return true;
        }

        $cainDB->query("INSERT INTO tablets (tablet_id, app_version) VALUES (?, ?);", [$tabletId, $appVersion]);
        return true;
    }

    // Something has gone wrong
    return false;
}

// Add a Lot to the db
function updateLot($lotNumber, $qcResult = null) {
    global $cainDB;

    if($lotNumber) {
        // Check if we have the lot number in the DB
        $lotExists = $cainDB->select("SELECT * FROM lots WHERE lot_number = ?;", [$lotNumber]);

        if($lotExists) {
            $cainDB->query("UPDATE lots SET qc_result = ? WHERE lot_number = ?;", [$qcResult, $lotNumber]);
            return true;
        }

        $cainDB->query("INSERT INTO lots (lot_number, qc_result) VALUES (?, ?);", [$lotNumber, $qcResult]);
        return true;
    }

    // Something has gone wrong
    return false;
}

// Turn a test result into an array of useful information about the test
function parseResult($result) {
    // First we must create the response object
    $parsedResult = ["posCount" => 0];

    // Now we must fill it. Begin by exploding by comma
    $resultArray = explode(", ", $result);

    // This function can handle if we are given multiplex results like SARS-CoV-2: 1Negative, RSV: 2Positive OR just simply positive and negative strings.
    if(count($resultArray) >= 2) {
        foreach($resultArray as $resultItem) {
            $resultKeyValue = explode(": ", $resultItem);
            $resultValue = stripos($resultKeyValue[1], "Positive") !== false ? true : false;
            $parsedResult["result"][$resultKeyValue[0]] = $resultValue;
            if($resultValue) {
                $parsedResult["posCount"]++;
            } 
        }
    } else {
        $parsedResult["result"] = stripos($result, "Positive") !== false ? true : false;
        if($parsedResult["result"]) {
            $parsedResult["posCount"]++;
        }
    }

    return $parsedResult;
}