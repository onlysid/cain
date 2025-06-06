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
        addLogEntry('system', "No version control found.");
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
        addLogEntry('system', "Created version control system for SAMBA III.");
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

// Function to check for and create missing required tables.
function checkAndSetupRequiredTables($conn) {
    // Define the required tables.
    $requiredTables = ['results', 'settings', 'users'];
    $missingTable = false;

    // Check for each required table.
    foreach ($requiredTables as $table) {
        $stmt = $conn->prepare("SHOW TABLES LIKE :table");
        $stmt->execute([':table' => $table]);
        if ($stmt->rowCount() === 0) {
            $missingTable = true;
            break;
        }
    }

    // If one or more tables are missing, create them.
    if ($missingTable) {
        $sql = "
        CREATE TABLE IF NOT EXISTS `results` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `sender` varchar(256) NOT NULL DEFAULT '\"\"',
            `sequenceNumber` varchar(256) NOT NULL DEFAULT '\"\"',
            `version` varchar(256) NOT NULL DEFAULT '\"\"',
            `assayType` varchar(256) NOT NULL DEFAULT '',
            `assaySubType` varchar(256) NOT NULL DEFAULT '',
            `site` varchar(256) NOT NULL DEFAULT '\"\"',
            `firstName` varchar(256) NOT NULL DEFAULT '\"\"',
            `lastName` varchar(256) NOT NULL DEFAULT '\"\"',
            `dob` varchar(256) NOT NULL DEFAULT '\"\"',
            `hospitalId` varchar(256) NOT NULL DEFAULT '\"\"',
            `nhsNumber` varchar(256) NOT NULL,
            `timestamp` varchar(256) NOT NULL DEFAULT '\"\"',
            `testcompletetimestamp` varchar(256) NOT NULL DEFAULT '\"\"',
            `clinicId` varchar(256) NOT NULL DEFAULT '\"\"',
            `operatorId` varchar(256) NOT NULL DEFAULT '\"\"',
            `moduleSerialNumber` varchar(256) NOT NULL DEFAULT '\"\"',
            `patientId` varchar(256) NOT NULL DEFAULT '\"\"',
            `patientAge` varchar(256) NOT NULL DEFAULT '\"\"',
            `patientSex` varchar(256) NOT NULL DEFAULT '\"\"',
            `sampleid` varchar(256) NOT NULL DEFAULT '\"\"',
            `trackingCode` varchar(256) NOT NULL DEFAULT '\"\"',
            `product` varchar(256) NOT NULL DEFAULT '\"\"',
            `result` varchar(256) NOT NULL DEFAULT '\"\"',
            `testPurpose` varchar(256) NOT NULL DEFAULT '\"\"',
            `abortErrorCode` varchar(255) NOT NULL DEFAULT '\"\"',
            `patientLocation` varchar(255) NOT NULL DEFAULT '\"\"',
            `reserve1` varchar(255) NOT NULL DEFAULT '\"\"',
            `reserve2` varchar(255) NOT NULL DEFAULT '\"\"',
            `sampleCollected` varchar(255) NOT NULL DEFAULT '\"\"',
            `sampleReceived` varchar(255) NOT NULL DEFAULT '\"\"',
            `flag` int(11) DEFAULT NULL,
            `post_timestamp` bigint(20) DEFAULT NULL,
            `assayStepNumber` varchar(256) NOT NULL DEFAULT '\"\"',
            `cameraReadings` varchar(256) NOT NULL DEFAULT '\"\"',
            `bits` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `value` varchar(255) DEFAULT NULL,
            `flags` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `operator_id` varchar(50) NOT NULL,
            `password` varchar(255) DEFAULT NULL,
            `user_id` varchar(255) DEFAULT NULL,
            `first_name` varchar(50) DEFAULT NULL,
            `last_name` varchar(50) DEFAULT NULL,
            `user_type` tinyint(4) DEFAULT 1,
            `last_active` int(11) DEFAULT NULL,
            `status` tinyint(4) DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `operator_id` (`operator_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            $conn->exec($sql);
        } catch (PDOException $ex) {
            addLogEntry('system', "Database setup failed: " . $ex->getMessage());
            http_response_code(500);
            include('views/generic-error.php');
            exit;
        }
        return true;
    }

    return false;
}

// Logs update error with context
function logUpdateError($context, $e) {
    $errorDetails = [
        'error_message' => $e->getMessage(),
        'stack_trace'   => $e->getTraceAsString(),
        'context'       => $context
    ];
    error_log("Update ERROR: " . json_encode($errorDetails, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    addLogEntry('system', "ERROR: " . json_encode($errorDetails, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

// Get current user info
function userInfo($operatorId = null) {
    global $cainDB, $session;

    // Ensure $session is valid before using it
    if(is_null($session)) {
        return 0;
    }

    // Check if the user is logged in
    if(!$session->isLoggedIn()) {
        return 0;
    }

    // Get the user ID from session or passed parameter
    $userId = Session::get('user-id') ?? $operatorId;

    // Fetch user info from the database
    $user = $cainDB->userInfo($userId);

    return $user;
}

// String interpretation of time differences and a sprinkle of severity information
function timeDifferenceMessage($timestamp) {
    $now = time();
    $diff = $timestamp - $now;

    if ($diff > 86400) {
        $days = floor($diff / 86400);
        return [
            'string' => "$days day" . ($days > 1 ? "s" : "") . " away",
            'severity' => 0
        ];
    } elseif ($diff > 3600) {
        $hours = floor($diff / 3600);
        return [
            'string' => "$hours hour" . ($hours > 1 ? "s" : "") . " away",
            'severity' => 0
        ];
    } elseif ($diff > 60) {
        $minutes = floor($diff / 60);
        return [
            'string' => "$minutes minute" . ($minutes > 1 ? "s" : "") . " away",
            'severity' => 1
        ];
    } elseif ($diff > 0) {
        return [
            'string' => "Less than a minute away",
            'severity' => 1
        ];
    } else {
        return [
            'string' => "Elapsed",
            'severity' => 2
        ];
    }
}

// Retrieve everything from the settings table in the db
function systemInfo() {
    global $cainDB;

    try {
        return $cainDB->selectAll("SELECT * FROM settings;");
    } catch (Exception $e) {
        // Suppress the error and return an empty array as a fallback
        error_log("Failed to retrieve settings: " . $e->getMessage());
        return [];
    }
}

// Retrieve LIMS connectivity status from db
function limsConnectivity() {
    global $cainDB;
    return $cainDB->select("SELECT `value` FROM settings WHERE `name` = 'comms_status';")['value'];
}

function updateInstruments($tabletData) {
    global $cainDB;
    /*
        NOTE: This has slightly changed. We no longer group things by tablet.

        We get a JSON object of ALL instruments for a given tablet {"tablet_id" => {"serial_number" => {Instrument Data}, {"serial_number" => {Instrument Data}}}
        Each tablet can have up to 8 instruments (not really relevant for this section but important to understand globally)
        Select all instruments in the db with the associated tablet.
        Loop through the instruments in the JSON data and update any instruments in the db with its new data.
        If an instrument in the JSON data does not have a corresponding db object, add it to the db. (may need additional serial number checks)
        If an instrument in the db does not have a corresponding object in the JSON data, set its status to 0.
    */

    if($tabletData) {
        // We have some data to parse! Start a transaction.
        try {
            $cainDB->beginTransaction();

            // At this stage, we definitely have an ID for the tablet. Now we need to get a list of instrument IDs who have this tablet ID.
            $oldTabletInstrumentsArray = $cainDB->selectAll("SELECT serial_number FROM instruments;");
            $oldTabletInstruments = [];

            $serialNumber = $tabletData['moduleSerialNumber'] ?? null;

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
            $instrumentData['module_version'] = $tabletData['moduleVersion'] ?? null;
            $instrumentData['front_panel_id'] = $tabletData['frontPanelId'] ?? null;
            $instrumentData['status'] = $tabletData['moduleStatus'] ?? null;
            $instrumentData['current_assay'] = $tabletData['runningAssay'] ?? null;
            $instrumentData['assay_start_time'] = !empty($tabletData['assayStartTime']) ? strtotime($tabletData['assayStartTime']) : null;
            $instrumentData['duration'] = !empty($tableData['assayDuration']) ? (int)$tabletData['assayDuration'] : null;
            $instrumentData['device_error'] = $tabletData['deviceError'] ?? null;
            $instrumentData['tablet_version'] = $tabletData['tabletVersion'] ?? null;
            $instrumentData['last_connected'] = time();

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

            // Commit the transaction
            $cainDB->commit();

            return true;

        } catch(PDOException $e) {
            // Rollback the transaction on error
            $cainDB->rollBack();
            // Throw the exception for handling at a higher level
            throw $e;

            // Log detailed information securely
            $errorDetails = [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'context' => 'Updating instruments'
            ];

            // Log error
            addLogEntry('system', "Error updating instrument: " . json_encode($errorDetails, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
    }

    // Something has gone wrong!
    return false;
}

function getInstrumentStatusText($code = 0) {
    switch($code) {
        case 0:
            return "Unknown";
            break;
        case 1:
            return "Idle";
            break;
        case 2:
            return "Preparing Assay";
            break;
        case 3:
            return "Running";
            break;
        case 4:
            return "Aborting";
            break;
        case 5:
            return "Result Available";
            break;
        case 6:
            return "Error";
            break;
        case 7:
            return "Uninitialised";
            break;
        case 8:
            return "Initialising";
            break;
        case 9:
            return "Assay Complete";
            break;
        default:
            return "Disconnected";
            break;
    }
}

// Add QC and number of tests to the instrument snapshot
function enrichInstrumentWithQC($instrument) {
    global $cainDB;
    // We need to know if the instrument has passed QC. Get all QC Types.
    $qcTypes = getInstrumentQCTypes();

    // Add QC Types to the result
    $instrument['qc'] = [
        // QC Pass, 0=fail, 1=pass, 2=expired, 3=untested. Priority order: 0, 3, 2, 1
        'pass' => 1,
        'res' => []
    ];

    // Add the number of tests that the instrument has performed in total.

    foreach($qcTypes as $qcType) {
        // Get the latest QC test for this QC type for this instrument
        $latestTest = $cainDB->select("SELECT * FROM instrument_qc_results WHERE instrument = ? AND `type` = ? ORDER BY `timestamp` DESC LIMIT 1;", [$instrument['id'], $qcType['id']]);

        // If this is false, no test has been carried out yet
        $instrument['qc']['res'][$qcType['id']] = $latestTest;

        if($latestTest) {
            // TODO: unused?!
            $formattedTimestamp = date("Y-m-d H:i", $latestTest['timestamp']);
        }

        // If we've already failed QC test, we need no more info
        if(in_array($instrument['qc']['pass'], [0])) {
            continue;
        }

        // If there are QC type conditions of any kind AND EITHER the latest test failed or does not exist, fail the test.
        if(($qcType['time_intervals'] || $qcType['result_intervals'])) {
            if($latestTest && !$latestTest['result']) {
                // QC has been tested and failed
                $instrument['qc']['pass'] = 0;
                continue;
            }

            if((!$latestTest)) {
                // QC has never been run, but it is required
                $instrument['qc']['pass'] = 3;
                continue;
            }
        }

        // If we've already determined something is untested, we need no more info
        if(in_array($instrument['qc']['pass'], [0, 3])) {
            continue;
        }

        // Check if the QC test requires time intervals
        if ($qcType['time_intervals']) {
            // Determine if the time has expired
            $testTimestamp = $latestTest['timestamp'];

            // The number of days before it expires converted to seconds
            $intervalSeconds = $qcType['time_intervals'] * 86400;

            // The day of expiration as a timestamp
            $expiredTimestamp = $testTimestamp + $intervalSeconds;

            // Current timestamp
            $currentTimestamp = time();

            // Calculate one day after the expiration timestamp
            $nextDayAfterExpired = strtotime(gmdate("Y-m-d", $expiredTimestamp) . ' +1 day');

            // Check if the QC test has expired only if it's the day after the expiration date
            if ($currentTimestamp >= $nextDayAfterExpired) {
                // The QC test has expired
                $instrument['qc']['pass'] = 2;
                continue;
            }
        }

        if($qcType['result_intervals']) {
            // If we are here, we require that this test is carried out every x results generated. Check this.

            // Result count comparison
            if($latestTest['result_counter'] > $qcType['result_intervals']) {
                // The QC test has expired
                $instrument['qc']['pass'] = 2;
                continue;
            }
        }

        // Otherwise, QC Test has passed!
    }

    return $instrument;
}

function getInstrumentSnapshot($instrumentId = null, $sn = null) {
    global $cainDB;

    if($instrumentId) {
        // Single instrument case
        $instrument = $cainDB->select("SELECT *, (assay_start_time + duration) as end_time FROM instruments WHERE id = ? ORDER BY last_connected DESC, end_time ASC;", [$instrumentId]);
        if($instrument) {
            $instrument = enrichInstrumentWithQC($instrument, $cainDB);
            return $instrument;
        }
    } else {
        if($sn) {
            // If we have a serial number, get the instrument from that
            $instrument = $cainDB->select("SELECT *, (assay_start_time + duration) as end_time FROM instruments WHERE serial_number = ? ORDER BY last_connected DESC, end_time ASC;", [$sn]);
            if($instrument) {
                $instrument = enrichInstrumentWithQC($instrument, $cainDB);
            }
            return $instrument;
        }

        // Multiple instruments case
        $instruments = $cainDB->selectAll("SELECT *, (assay_start_time + duration) as end_time FROM instruments ORDER BY last_connected DESC, end_time ASC;");

        foreach ($instruments as &$instrument) {
            $instrument = enrichInstrumentWithQC($instrument, $cainDB);
        }

        return $instruments;
    }
}

function getIndividualInstrumentQCResult($id) {
    global $cainDB;

    // If we have an ID, get it!
    if($id) {
        return $cainDB->select("SELECT * FROM instrument_qc_results WHERE id = ?;", [$id]);
    }

    return null;
}

// Get all instrument QC results for a given instrument
function getInstrumentQCResults($instrumentId = null, $orderByTime = false) {
    global $cainDB;

    // If there is an instrument ID, get its specific results. Otherwise, get all results.
    if($instrumentId) {
        return $cainDB->selectAll("SELECT * FROM instrument_qc_results WHERE instrument = ? ORDER BY `timestamp`;", [$instrumentId]);
    } else {
        return $cainDB->selectAll("SELECT * FROM instrument_qc_results ORDER BY `timestamp`;");
    }
}

// Get instrument QC result text
function getInstrumentQCResult($result) {
    switch($result) {
        case 0:
            return "failed";
            break;
        case 1:
            return "passed";
            break;
        case 2:
            return "expired";
            break;
        default:
            return "untested";
            break;
    }
}

function getResults($params, $itemsPerPage) {
    global $cainDB;

    // Get any query params
    $searchFilter = isset($params['s']) ? $params['s'] : null;
    $sortDirection = isset($params['sd']) && ($params['sd'] != "" || $params['sd'] == "asc") ? "ASC" : "DESC";
    $sortParam = isset($params['sp']) && $params['sp'] != "" ? $params['sp'] : "end_time";
    $pageNumber = isset($params['p']) ? $params['p'] : 1;
    $offset = $itemsPerPage ? ($pageNumber - 1) * $itemsPerPage : null;

    // These filters need a little extra cleaning
    $resultPolarity = isset($params['r']) ? $params['r'] : null;
    $sex = isset($params['g']) ? $params['g'] : null;
    $age = isset($params['a']) ? $params['a'] : null;
    $sentToLIMS = isset($params['l']) ? $params['l'] : null;
    $dateRange = isset($params['d']) ? $params['d'] : null;
    $filters = [];

    // Check for positive result
    if($resultPolarity) {
        $filters[] = "r.result LIKE '" . ($resultPolarity == 0 ? 'Negative' : 'Positive') . "'";
    }

    if($sex) {
        $filters[] = "r.sex = '$sex'";
    }

    if($age) {
        // We need to parse the age
        $ageRange = explode('-', $age);
        if(count($ageRange) > 1) {
            $minAge = $ageRange[0];
            $maxAge = $ageRange[1];
        } else {
            $minAge = explode('+', $age)[0];
            $maxAge = null;
        }

        $filters[] = "r.age >= $minAge";
        if($maxAge) {
            $filters[] = "r.age <= $maxAge";
        }
    }

    if($dateRange) {
        // We need to parse the date range
        $dateRangeArr = explode(' - ', $dateRange);

        if($dateRangeArr[0]) {
            $filters[] = 'STR_TO_DATE(r.end_time, "%Y-%m-%d %H:%i") >= "' . $dateRangeArr[0] . ' 00:00"';
        }
        if(isset($dateRangeArr[1])) {
            $filters[] = 'STR_TO_DATE(r.end_time, "%Y-%m-%d %H:%i") <= "' . $dateRangeArr[1] . ' 00:00"';
        } else {
            $filters[] = 'STR_TO_DATE(r.end_time, "%Y-%m-%d %H:%i") <= "' . $dateRangeArr[0] . ' 23:59"';
        }
    }

    // Initialize the base SQL queries
    $query = "SELECT
        r.*,
        r.id AS master_result_id,
        r.result AS result_summary,
        i.*,
        res.*,
        GROUP_CONCAT(res.overall_result SEPARATOR ';') AS result_values,
        GROUP_CONCAT(res.product SEPARATOR ';') AS assay_names,
        GROUP_CONCAT(res.flag SEPARATOR ';') AS result_flags,
        GROUP_CONCAT(res.ct_values SEPARATOR ';') AS ct_values,
        CASE
            WHEN SUM(res.flag = 101) > 0 THEN 1
            WHEN SUM(res.flag = 102) = COUNT(res.flag) THEN 2
            ELSE 0
        END AS lims_status
        FROM master_results r
        LEFT JOIN lots i ON i.lot_number = r.lot_number
        LEFT JOIN results res ON res.master_result = r.id ";

    $countQuery = "SELECT COUNT(*) as cnt FROM (
        SELECT r.id,
            CASE
                WHEN SUM(res.flag = 101) > 0 THEN 1
                WHEN SUM(res.flag = 102) = COUNT(res.flag) THEN 2
                ELSE 0
            END AS lims_status
        FROM master_results r
        LEFT JOIN lots i ON i.lot_number = r.lot_number
        LEFT JOIN results res ON res.master_result = r.id ";

    $searchConditions = '';
    $queryParams = [];

    if ($searchFilter !== null) {
        $searchTerms = explode(" ", $searchFilter);
        $columns = ['first_name', 'last_name', 'hospital_id', 'nhs_number', 'operator_id', 'patient_id', 'location', 'result', 'assay_name'];

        $termConditions = []; // Array to store each search term's condition group

        foreach ($searchTerms as $index => $filterString) {
            $columnConditions = []; // Array to store conditions for each column for this term
            foreach ($columns as $column) {
                $columnConditions[] = "r.$column LIKE :filterString{$index}_$column";
                $queryParams[":filterString{$index}_$column"] = '%' . $filterString . '%';
            }
            // Wrap each term's column conditions in parentheses and join them with OR
            $termConditions[] = '(' . implode(' OR ', $columnConditions) . ')';
        }

        // Combine all term conditions with AND
        $searchConditions .= implode(' AND ', $termConditions);
    }

    // Add any additional filter conditions from $filters
    if (count($filters) > 0) {
        foreach ($filters as $filter) {
            if (!empty($searchConditions)) {
                $searchConditions .= " AND ";
            }
            $searchConditions .= $filter;
        }
    }

    // Append the WHERE clause to the queries if conditions exist
    if (!empty($searchConditions)) {
        $query .= "WHERE $searchConditions ";
        $countQuery .= "WHERE $searchConditions ";
    }

    $query .= "GROUP BY r.id ";
    $countQuery .= "GROUP BY r.id ";

    // If $sentToLIMS is provided, map it to the computed lims_status and add a HAVING clause
    if ($sentToLIMS) {
        if ($sentToLIMS == 101) {
            $limsFilter = 1;
        } else if ($sentToLIMS == 102) {
            $limsFilter = 2;
        } else if ($sentToLIMS == 100) {
            $limsFilter = 0;
        }
        $query .= "HAVING lims_status = $limsFilter ";
        $countQuery .= "HAVING lims_status = $limsFilter ";
    }

    // Add the ORDER BY clause
    $query .= "ORDER BY r.$sortParam $sortDirection ";
    $countQuery .= ") as subquery";

    // Apply pagination
    if ($itemsPerPage) {
        $query .= " LIMIT $itemsPerPage OFFSET $offset ";
    }

    // Execute the queries
    try {
        $results = $cainDB->selectAll($query, $queryParams);
        $count = $cainDB->select($countQuery, $queryParams);
    } catch(Exception $e) {
        $err = $e->getMessage();
        addLogEntry('system', "Error in getting results. $err");
        return null;
    }

    // Return the results and count
    return ["results" => $results, "count" => $count['cnt']];
}

function getLots($params, $itemsPerPage) {
    global $cainDB;

    // Get any query params
    $searchFilter = isset($params['s']) ? $params['s'] : null;
    $pageNumber = isset($params['p']) ? $params['p'] : 1;
    $offset = ($pageNumber - 1) * $itemsPerPage;

    // Also get the priority toggle
    $priority = isset($params['priority']) && $params['priority'] === 'on';

    // Construct the WHERE clause for searching across all columns
    $searchConditions = '';
    $queryParams = [];
    if ($searchFilter !== null) {
        $searchTerms = explode(" ", $searchFilter);
        $columns = ['lot_number'];

        $i = 0;
        foreach ($searchTerms as $filterString) {
            $searchConditions .= "AND (";
            $j = 0;
            foreach ($columns as $column) {
                $searchConditions .= ($j != 0 ? "OR " : "") . "lots.$column LIKE :filterString$i ";
                $queryParams[':filterString' . $i] = '%' . str_replace(' ', '%', $filterString) . '%';
                $j++;
            }
            $searchConditions .= ") ";
            $i++;
        }
        $searchConditions = ltrim($searchConditions, 'AND');
    }

    // Add priority condition if necessary
    if ($priority) {
        $searchConditions .= ($searchConditions ? " AND " : "") . "lots.qc_pass = 0 ";
    }

    // Build the SQL query
    $query = "
        SELECT lots.*,
               GROUP_CONCAT(CASE WHEN lots_qc_results.qc_result = 1 THEN master_results.result END SEPARATOR '|||') AS positive_results,
               GROUP_CONCAT(CASE WHEN lots_qc_results.qc_result = 0 THEN master_results.result END SEPARATOR '|||') AS failure_results
        FROM lots
        LEFT JOIN lots_qc_results ON lots.id = lots_qc_results.lot
        LEFT JOIN master_results ON lots_qc_results.test_result = master_results.id
    ";
    $countQuery = "SELECT COUNT(*) FROM lots ";

    if (!empty($searchConditions)) {
        $query .= "WHERE $searchConditions ";
        $countQuery .= "WHERE $searchConditions ";
    }

    $query .= "GROUP BY lots.id ";
    $query .= "ORDER BY lots.last_updated DESC ";
    $query .= "LIMIT $itemsPerPage OFFSET $offset";

    // Fetch lots with results
    $lots = $cainDB->selectAll($query, $queryParams);

    // Apply parseResult function and count positives and negatives
    foreach ($lots as &$lot) {
        $positiveCount = 0;
        $negativeCount = 0;
        $failureCount = 0;

        // Parse positive results
        $positiveResults = isset($lot['positive_results']) ? explode('|||', $lot['positive_results']) : [];
        foreach ($positiveResults as $result) {
            $parsedResult = parseResult($result);
            if ($parsedResult['posCount'] > 0) {
                $positiveCount++;
            } elseif ($parsedResult['posCount'] == 0) {
                $negativeCount++;
            }
        }

        // Parse failure results
        $failureResults = isset($lot['failure_results']) ? explode('|||', $lot['failure_results']) : [];
        foreach ($failureResults as $result) {
            $failureCount++;
        }

        // Add counts to each lot
        $lot['positive_count'] = $positiveCount;
        $lot['negative_count'] = $negativeCount;
        $lot['failure_count'] = $failureCount;
    }

    // Get the count of all lots
    $count = $cainDB->select($countQuery, $queryParams);

    return ["lots" => $lots, "count" => $count['COUNT(*)']];
}

// Function to sanitize and validate input data
function testInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}

function getParams($url) {
    // Parse the URL to get the query string
    $queryString = parse_url($url, PHP_URL_QUERY);

    // Initialize an empty array to store parameters
    $parameters = array();

    // If there are query parameters
    if ($queryString) {
        // Parse the query string into an associative array
        parse_str($queryString, $parameters);
    }

    return $parameters;
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

// Check if string is null or empty
function isNullOrEmptyString($str) {
    return $str === null || trim($str) === '';
}

// Check if we have data within an API request. If we don't, check something else!
function getValueOrFallback($primary, $fallback) {
    return (isset($primary) && $primary !== '') ? $primary : $fallback;
}

// If a value is an empty string, just make it null (for use with API)
function cleanValue($value) {
    return ($value === '') ? null : $value;
}

// Function to get all non-service admin operators
function getOperators($currentUserId = null) {
    global $cainDB;

    if($currentUserId) {
        return $cainDB->selectAll("SELECT * FROM users WHERE user_type < 3 AND id != :currentUser;", [":currentUser" => $currentUserId]);
    } else {
        return $cainDB->selectAll("SELECT * FROM users WHERE user_type < 3;");
    }
}

// Get QC Types
function getInstrumentQCTypes($arr = false) {
    global $cainDB;

    $instrumentQCTypes = $cainDB->selectAll("SELECT * FROM instrument_test_types;");
    if($arr) {
        return $instrumentQCTypes;
    }

    $ret = [];

    foreach($instrumentQCTypes as $qcType) {
        $ret[$qcType['id']] = $qcType;
    }

    return $ret;
}

/*
    Get instrument QC individual test result outcome
    returns 0=fail, 1=pass, 2=expired, 3=untested
*/
function getInstrumentQCOutcome($qcTest) {
    // Get the possible QC types
    $qcTypes = getInstrumentQCTypes();

    // If the test is false, it is untested
    if($qcTest == false) {
        return 3;
    }

    // If the test failed, return failure
    if($qcTest['result'] == 0) {
        return 0;
    }

    // If the timestamp plus the test type's interval period exceeds the current timestamp, it has expired.
    $testTimestamp = $qcTest['timestamp'];
    $intervalSeconds = $qcTypes[$qcTest['type']]['time_intervals'] * 3600;
    $expiredTimestamp = $intervalSeconds ? $testTimestamp + $intervalSeconds : null;
    $currentTimestamp = time();

    if($expiredTimestamp && $currentTimestamp >= $expiredTimestamp) {
        // It's expired
        return 2;
    }

    // If the number of tests allowed is less than the result counter, it has expired
    if($qcTypes[$qcTest['type']]['result_intervals'] && $qcTest['result_counter'] >= $qcTypes[$qcTest['type']]['result_intervals'] ) {
        return 2;
    }

    return 1;

}

// Function to get the behaviour of all fields (for the tablet)
function getFieldBehaviourSettings($dataFields, $behaviourFields, $tablet = false) {
    $result = [];
    foreach($dataFields as $index => $dataField) {
        $behaviour = "OFF";
        switch($behaviourFields[$index]) {
            case 1:
                $behaviour = "ON";
                break;
            case 2:
                $behaviour = "MANDATORY";
                break;
            default:
                break;

        }

        // Account for data field overrides
        if($dataField->behaviourLock) {
            $behaviour = "AUTOMATIC";
        }

        if($tablet) {
            $result[$dataField->tabletName] = $behaviour;
        } else {
            $result[$dataField->dbName] = $behaviour;
        }
    }

    return $result;
}

function getFieldVisibilitySettings($dataFields, $visibilityFields) {
    $result = [];
    foreach($dataFields as $index => $dataField) {
        if($visibilityFields[$index] && !$dataField->behaviourLock) {
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
function updateLot($lotNumber, $params = null) {
    global $cainDB;

    // We need to add this to the query.
    $timestamp = Date("Y:m:d H:i:s");

    if($lotNumber) {
        // Check if we have the lot number in the DB
        $lotExists = $cainDB->select("SELECT * FROM lots WHERE lot_number = ?;", [$lotNumber]);

        if($lotExists) {
            // Update the lot with the new params
            $sql = "UPDATE lots SET last_updated = ?";
            $queryParams = [$timestamp];

            // Loop through params and
            $i = 1;
            if($params) {
                foreach($params as $dbCol => $param) {
                    $sql .= ",  " . $dbCol . " = ?";
                    $queryParams[] = $param;
                    $i++;
                }
            }

            // Complete the query
            $queryParams[] = $lotNumber;
            $sql .= " WHERE lot_number = ?;";

            // Execute the query
            $cainDB->query($sql, $queryParams);

            // Return ID of lot
            return $lotExists['id'];
        }

        // The lot does not exist. Insert it!
        $sql = "INSERT INTO lots (lot_number, last_updated" . ($params ? ", " : "") . implode(", ", array_keys($params ?? [])) . ") VALUES (?, ?" . ($params ? ", " : "") . implode(", ", array_fill(0, count((array) $params), "?")) . ");";
        $cainDB->beginTransaction();
        $cainDB->query($sql, array_merge([$lotNumber, $timestamp], array_values((array) $params)));
        $lotID = $cainDB->conn->lastInsertId();
        $cainDB->commit();

        // Return ID of inserted lot
        return $lotID;
    }

    // Something has gone wrong
    return false;
}

// Check a lot's QC and update it if necessary
function lotQCCheck($lot) {
    global $cainDB;

    // Check QC policy
    $qcPolicy = $cainDB->select("SELECT `value` FROM settings WHERE `name` = 'qc_policy'")['value'];

    // If the QC Policy is 0 (off), we can say QC is passed.
    if($qcPolicy == 0) {
        return true;
    }

    // If the QC Policy is 1 (on), we must run an automatic QC check
    if($qcPolicy == 1) {
        if(lotAutoQCCheck($lot)) {
            return true;
        }
    }

    // Check the lot for its QC pass flag
    if($qcPolicy == 2 && ($qcPass = $cainDB->select("SELECT qc_pass FROM lots WHERE id = ?;", [$lot])['qc_pass'] == 1)) {
        return true;
    }

    return false;
}

// Run an automatic QC check
function lotAutoQCCheck($lot) {
    global $cainDB;

    // Firstly, check if we have QC set to automatic, otherwise we don't need this at all.
    if($cainDB->select("SELECT value FROM settings WHERE `name` = 'qc_policy'")['value'] != 1) {
        return false;
    }

    // Firstly, if any QC test has failed, then the QC has failed
    $failures = $cainDB->select("SELECT COUNT(*) as count FROM lots_qc_results WHERE qc_result = 0;")['count'];

    if($failures) {
        // QC has failed. Fail the lot and move on.
        $cainDB->query("UPDATE lots SET qc_pass = 2 WHERE id = ?;", [$lot]);
        return false;
    }

    // Now check if lot has expired
    if($expirationDate = $cainDB->select("SELECT * FROM lots WHERE id = ?;", [$lot])['expiration_date']) {
        if(checkExpiration($expirationDate)) {
            $cainDB->query("UPDATE lots SET qc_pass = 2 WHERE id = ?;", [$lot]);
            return false;
        }
    }

    // Now, check how many successful positives and negatives we need
    $positiveTestsRequired = $cainDB->select("SELECT `value` FROM settings WHERE `name` = 'qc_positive_requirements';")['value'];
    $negativeTestsRequired = $cainDB->select("SELECT `value` FROM settings WHERE `name` = 'qc_negative_requirements';")['value'];

    // Now, check the count of successful positive QC tests
    $positiveTests = $cainDB->select("SELECT COUNT(*) as count FROM lots_qc_results as qc JOIN master_results ON qc.test_result = master_results.id WHERE qc.lot = ? AND qc.qc_result = 1 AND master_results.result LIKE '%positive%' AND master_results.result NOT LIKE 'invalid';", [$lot])['count'];

    // Now, check the count of successful positive QC tests
    $negativeTests = $cainDB->select("SELECT COUNT(*) as count FROM lots_qc_results as qc JOIN master_results ON qc.test_result = master_results.id WHERE qc.lot = ? AND qc.qc_result = 1 AND master_results.result NOT LIKE '%positive%' AND master_results.result NOT LIKE 'invalid';", [$lot])['count'];

    // If we have the right counts, we have passed QC!
    if($positiveTests >= $positiveTestsRequired && $negativeTests >= $negativeTestsRequired) {
        // We should update the value of qc_pass to 1 and return that QC was passed
        $cainDB->query("UPDATE lots SET qc_pass = 1 WHERE id = ?;", [$lot]);
        return true;
    }

    // Otherwise, we have not passed QC.
    $cainDB->query("UPDATE lots SET qc_pass = 0 WHERE id = ?;", [$lot]);
    return false;
}

/*
 * Add QC Result to DMS
 * qcParams contains 'lot' and 'result' where 'result' is the actual result that's been committed.
 */
function newLotQC($lotID, $resultID, $timestamp) {
    global $cainDB;

    if(!$timestamp || $timestamp == "") {
        // Set time to now
        $timestamp = time();
    }

    // If we don't have a resultID, we cannot add the QC result
    if(!$resultID) {
        return false;
    }

    // Convert timestamp strictly to int
    $timestamp = strtotime($timestamp);

    // Add the result QC
    if($cainDB->query("INSERT INTO lots_qc_results (`lot`, `test_result`, `timestamp`) VALUES (?, ?, ?);", [$lotID, $resultID, $timestamp])) {
        return true;
    }

    // Something went wrong
    return false;
}

// Get lot QC results
function getLotQCResults($priority = false, $page = 1, $itemsPerPage = 10) {
    global $cainDB;

    // Calculate the offset for pagination
    $offset = ($page - 1) * $itemsPerPage;

    // Initialize the SQL query base
    $sql = "SELECT
        qc_results.*,
        master_results.*,
        lots.*,
        users.*,
        qc_results.id AS id,
        master_results.id AS result_id,
        lots.id AS lot_id,
        users.id AS user_id,
        qc_results.reference AS reference
    FROM
        lots_qc_results AS qc_results
    JOIN
        master_results ON qc_results.test_result = master_results.id
    LEFT JOIN
        users ON qc_results.operator_id = users.id
    JOIN
        lots ON qc_results.lot = lots.id";

    // Add condition to ensure priority rows come first based on the value of qc_result
    $sql .= " ORDER BY qc_results.qc_result IS NULL DESC, qc_results.timestamp DESC";

    // Add LIMIT and OFFSET with string concatenation to ensure they are properly handled
    $sql .= " LIMIT " . (int)$itemsPerPage . " OFFSET " . (int)$offset . ";";

    // Execute the query and fetch the results
    $results = $cainDB->selectAll($sql);

    // Get the total count of all results for pagination purposes
    $count = $cainDB->select("SELECT COUNT(*) as count FROM lots_qc_results WHERE test_result IS NOT null")['count'];

    return [
        'results' => $results,
        'count' => $count
    ];
}

// Turn a test result into an array of useful information about the test
function parseResult($result) {
    // First we must create the response object
    $parsedResult = ["posCount" => 0];

    // Now we must fill it. Begin by exploding by comma
    $resultArray = explode(", ", $result);


    // Handle multiplex results like "SARS-CoV-2: 1Negative, RSV: 2Positive" or simple "Positive" and "Negative" strings
    if (count($resultArray) >= 2) {
        foreach ($resultArray as $resultItem) {
            $resultKeyValue = explode(": ", $resultItem);

            // If any part of the result is "Invalid", set the parsed result to invalid and return immediately
            if (stripos($resultKeyValue[1], "Invalid") !== false) {
                $parsedResult['posCount'] = null;
                $parsedResult['result'] = null;
                return $parsedResult;
            }

            $resultValue = stripos($resultKeyValue[1], "Positive") !== false ? true : false;
            $parsedResult["result"][$resultKeyValue[0]] = $resultValue;
            if ($resultValue) {
                $parsedResult["posCount"]++;
            }
        }
    } else {
        // Handle simple result strings
        if (stripos($result, "Invalid") !== false) {
            $parsedResult['posCount'] = null;
            $parsedResult['result'] = null;
            return $parsedResult;
        }

        $parsedResult["result"] = stripos($result, "Positive") !== false ? true : false;
        if ($parsedResult["result"]) {
            $parsedResult["posCount"]++;
        }
    }

    return $parsedResult;
}

// Turn a SAMBA III test result into a SAMBA II test result and add a summary
function sanitiseResult($result) {
    // Define the return array
    $ret = [
        'result' => null,
        'summary' => null,
        'ct' => null,
    ];

    // If the result is null, just return
    if($result === null) {
        return $ret;
    }

    // Check if the result is in JSON format
    if ($decodedResult = json_decode($result) && !is_string($result)) {
        $ret = processJSONResults($decodedResult);
    } else {
        // Handle non-JSON format by parsing the result
        $parsedResult = parseResult($result) ?? null;

        // If any part of the parsed result is invalid, set the summary to "Invalid" and return immediately
        if ($parsedResult['result'] === null) {
            $ret['summary'] = 'Invalid';
            return $ret;
        }

        // Otherwise, assign the parsed result and determine the summary
        $ret['result'] = $parsedResult['result'];

        // Set summary based on positive count
        if ($parsedResult['posCount'] > 0) {
            $ret['summary'] = 'Positive';
        } else {
            $ret['summary'] = 'Negative';
        }
    }

    return $ret;
}

function processJSONResults($results) {
    $formattedResults = [];
    $summary = null;
    $hasPositiveControl = true;
    $hasInvalidControlOrTarget = false;
    $ctValues = [];

    // Process each result entry
    foreach ($results as $result) {
        $controlResult = $result->control->result;

        // Check control results to determine if any are "Invalid" or "Negative"
        if ($controlResult === 'Negative' || $controlResult === 'Invalid') {
            // Flag invalid control result
            $hasInvalidControlOrTarget = true;

            // Set summary to Invalid if any control is Negative or Invalid
            $summary = 'Invalid';
        }

        if ($controlResult !== 'Positive') {
            $hasPositiveControl = false;
        }

        // Process target results
        foreach ($result->targetResults as $target) {
            // Normalize target name
            $targetName = str_replace(['-', ' '], '', ucwords(strtolower($target->name)));

            // Add the CT Value
            $ctValues[] = $target->ct;

            if($target->result === 'Invalid') {
                $targetIsPositive = null;
            } else {
                $targetIsPositive = $target->result === 'Positive';
            }

            // Ensure a target is recorded as true if it was positive in any instance
            if (!isset($formattedResults[$targetName]) || !$formattedResults[$targetName]) {
                $formattedResults[$targetName] = $targetIsPositive;
            }

            // Set summary to Invalid if any target result is Invalid and break out of the loop early
            if ($target->result === 'Invalid') {
                $hasInvalidControlOrTarget = true;
                $summary = 'Invalid';
            } elseif ($target->result === 'Positive' && $summary !== 'Invalid') {
                $summary = 'Positive';
            }
        }
    }

    // Finalize summary based on control results
    if ($hasPositiveControl && !$hasInvalidControlOrTarget) {
        if ($summary === 'Negative') {
            $summary = 'Negative';
        } elseif ($summary === 'Positive') {
            $summary = 'Positive';
        }
    } else {
        // If any controls or targets were "Invalid," summary is set to "Invalid"
        $summary = 'Invalid';
    }

    return [
        'result' => $summary == 'Invalid' ? 'Invalid' : $formattedResults,
        'summary' => $summary,
        'ct' => empty($ctValues) ? null : implode(',', $ctValues),
    ];
}

function resultStringify($result) {
    $stringifiedResult = '';
    $index = 1;

    if($result !== null && $result !== []) {
        // Multiplex
        if(is_array($result)) {
            foreach ($result as $target => $isPositive) {
                $targetName = str_replace(['-', ' '], '', ucwords(strtolower($target))); // Normalize the target name
                if($isPositive === null) {
                    $status = 'Invalid';
                } else {
                    $status = $isPositive ? 'Positive' : 'Negative';
                }

                // Append the formatted target and result status to the string
                $stringifiedResult .= "{$targetName}: {$index}{$status}, ";
                $index++;
            }
        } else {
            // Simple SAMBA II result OR Invalid
            if($result === 'Invalid') {
                $stringifiedResult = $result;
            } else {
                $stringifiedResult = $result ? 'Positive' : 'Negative';
            }
        }
    } else {
        $stringifiedResult = "Invalid";
    }

    // Remove trailing comma and space
    $stringifiedResult = rtrim($stringifiedResult, ', ');

    return $stringifiedResult;
}

/* Functions for logging */

// Adding a log entry and handling log rotation and compression
function addLogEntry($logType, $message) {
    // Get the primary log directory
    $logDir = __DIR__ . '/../logs/' . $logType;
    $folderName = basename($logDir);

    // Fallback directory if primary isn't writable
    $fallbackDir = sys_get_temp_dir() . '/app_logs/' . $logType;

    // Determine which directory to use
    $activeLogDir = is_writable($logDir) || @mkdir($logDir, 0777, true) ? $logDir : $fallbackDir;

    // Create fallback directory if necessary
    if ($activeLogDir === $fallbackDir && !is_dir($fallbackDir)) {
        mkdir($fallbackDir, 0777, true);
    }

    // Get the current log file
    $currentLogFile = $activeLogDir . '/' . $folderName . '.log';

    // Get all log files (including those which are zipped)
    $allFiles = glob($activeLogDir . '/' . $folderName . '-*.log*');

    // Natural sort to get the correct order
    usort($allFiles, function ($a, $b) {
        return filemtime($a) <=> filemtime($b);
    });

    // Use the most recent files (or create a new one if none exist)
    $latestFile = end($allFiles);

    // Initialize a log number
    $logNumber = 1;

    // Get the latest log number
    if (preg_match('/-(\d+)\.log(?:\.gz)?$/', $latestFile, $matches)) {
        $logNumber = (int)$matches[1] + 1;

        // Reset log number if it reaches 999
        $logNumber = ($logNumber === 999) ? 1 : $logNumber;
    }

    // Define a maximum file size for log rotation (2MB in this example)
    $maxFileSize = 1024 * 1024 * 2; // 2MB

    // Check if the latest file exists and is too large
    if (file_exists($currentLogFile) && filesize($currentLogFile) >= $maxFileSize) {
        compressLogFile($currentLogFile, $logNumber, $logType);
    }

    // If no latest file exists or the file was rotated, create a new one
    if (!$latestFile) {
        $latestFile = $activeLogDir . '/' . $folderName . '-' . str_pad($logNumber, 3, '0', STR_PAD_LEFT) . '.log';
    }

    // Append the new log entry
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = $timestamp . " - " . $message . PHP_EOL;

    file_put_contents($currentLogFile, $logEntry, FILE_APPEND);
}

// Function to compress log files when they hit the size limit
function compressLogFile($logFilePath, $logNumber, $logType) {
    $logDir = dirname($logFilePath);
    $gzFilePath = $logDir . '/' . $logType . '-' . str_pad($logNumber, 3, '0', STR_PAD_LEFT) . '.log.gz';

    // Open the original log file and the new compressed file
    $logFile = fopen($logFilePath, 'rb');
    $gzFile = gzopen($gzFilePath, 'wb9'); // Max compression level 9

    while (!feof($logFile)) {
        gzwrite($gzFile, fread($logFile, 1024 * 512));
    }

    fclose($logFile);
    gzclose($gzFile);

    // Delete the original log file after compression
    unlink($logFilePath);
}

// Reading log data (including gzipped files) and loading in reverse order
function readLogFile($logType, $limit = 1000, $offset = 0) {
    $logDir = __DIR__ . '/../logs/' . $logType;
    $fallbackDir = sys_get_temp_dir() . '/app_logs/' . $logType;

    // Determine which directory to use
    $activeLogDir = is_dir($logDir) ? $logDir : $fallbackDir;

    // Find all log files including gzipped ones and sort them naturally
    $logFiles = array_merge(
        glob($activeLogDir . '/*.log'),
        glob($activeLogDir . '/*.log.gz')
    );
    natsort($logFiles);

    $lines = [];

    // Read from the most recent log files (reverse order)
    foreach (array_reverse($logFiles) as $logfile) {
        $fileLines = [];

        // Handle gzipped files
        if (substr($logfile, -3) === '.gz') {
            $gz = gzopen($logfile, 'rb');
            if ($gz) {
                while (!gzeof($gz)) {
                    $fileLines[] = gzgets($gz);
                }
                gzclose($gz);
            }
        } else {
            // Handle regular log files
            $file = fopen($logfile, 'r');
            if ($file) {
                while (($line = fgets($file)) !== false) {
                    $fileLines[] = $line;
                }
                fclose($file);
            }
        }

        // Reverse the lines so the most recent ones come first
        $fileLines = array_reverse($fileLines);

        // Merge the lines from the current file to the overall list
        $lines = array_merge($lines, $fileLines);

        // Stop when we've loaded enough lines
        if (count($lines) >= $offset + $limit) {
            break;
        }
    }

    // Slice the lines according to the offset and limit, then return them
    return implode("", array_slice($lines, $offset, $limit));
}

// Convert the total size to a human-readable format
function formatSize($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $unitIndex = 0;

    while ($size >= 1024 && $unitIndex < count($units) - 1) {
        $size /= 1024;
        $unitIndex++;
    }

    return round($size, 2) . ' ' . $units[$unitIndex];
}

// Convert timestamps to their desired format
function convertTimestamp($timestamp, $time = false) {
    // Check if the timestamp is null or empty, and return a safe default value
    if (empty($timestamp)) {
        return "Unknown";
    }

    // Ensure that the timestamp is a valid string or UNIX timestamp
    if (is_string($timestamp)) {
        // Attempt to convert the timestamp using strtotime, handle invalid input gracefully
        $timestamp = strtotime($timestamp);
    }

    // Check for invalid timestamps (false or 0)
    if (!$timestamp) {
        return "Unknown";
    }

    // Return the formatted date/time based on whether the time is needed
    if ($time) {
        return date("d/m/Y H:i", $timestamp);
    } else {
        return date("d/m/Y", $timestamp);
    }
}

// Get the total size of the expired log files
function getExpiredLogSize() {
    // Define the path to the primary and fallback logs directory
    $primaryPath = BASE_DIR . '/logs'; // Primary log directory
    $fallbackPath = sys_get_temp_dir() . '/app_logs'; // Fallback log directory

    // Determine which directory to use
    $activePath = is_dir($primaryPath) ? $primaryPath : $fallbackPath;

    // Variable to hold the total size of `.gz` files
    $totalGzSize = 0;

    // Ensure the active path exists
    if (is_dir($activePath)) {
        // Loop through files in the logs directory
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($activePath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // Check if the file is not a directory and is a `.gz` file
            if (!$file->isDir()) {
                // Add the file size to the total
                $totalGzSize += $file->getSize();
            }
        }
    }

    return $totalGzSize;
}

// Function to check expiration of cartridge
function checkExpiration($timestamp) {
    // Extract the year and month from the given timestamp
    $expirationYearMonth = date('Y-m', strtotime($timestamp));

    // Get the current year and month
    $currentYearMonth = date('Y-m');

    // Compare year and month
    if ($currentYearMonth > $expirationYearMonth) {
        return true; // Expired
    } else {
        return false; // Not expired
    }
}

// Check if an API Version is valid
function isValidAPIVersion($version) {
    // Ensure that the version is a string.
    if (!is_string($version)) {
        return false;
    }
    // Use a regular expression to check if it follows the pattern "a.b"
    return preg_match('/^\d+\.\d+$/', $version) === 1;
}

// Compare two API versions. Returns 1 if x is newer than y, -1 if not and 0 if equal.
function compareAPIVersions($x, $y) {
    $validX = isValidAPIVersion($x);
    $validY = isValidAPIVersion($y);

    // Both versions are invalid.
    if (!$validX && !$validY) {
        return 0;
    }
    // Only y is invalid: assume y is older.
    if ($validX && !$validY) {
        return 1;
    }
    // Only x is invalid: assume x is older.
    if (!$validX && $validY) {
        return -1;
    }

    // Both versions are valid; split into major and minor components.
    list($majorX, $minorX) = explode('.', $x);
    list($majorY, $minorY) = explode('.', $y);

    // Convert to integers for numerical comparison.
    $majorX = (int)$majorX;
    $minorX = (int)$minorX;
    $majorY = (int)$majorY;
    $minorY = (int)$minorY;

    // Compare major versions first.
    if ($majorX > $majorY) {
        return 1;
    } elseif ($majorX < $majorY) {
        return -1;
    } else {
        // Compare minor versions if majors are equal.
        if ($minorX > $minorY) {
            return 1;
        } elseif ($minorX < $minorY) {
            return -1;
        } else {
            return 0;
        }
    }
}

// Check, given an array of results, if we are invalid (0), positive (1) or negative (2)
function resultInterpretation($result) {
    $invalidCount = 0;
    $positiveCount = 0;

    foreach($result as $part) {
        if(strpos(strtolower($part), 'invalid') !== false) {
            $invalidCount++;
        }

        if(strpos(strtolower($part), 'positive') !== false) {
            $positiveCount++;
        }
    }

    if($invalidCount > 0) {
        return 0;
    }

    if($positiveCount > 0) {
        return 1;
    }

    return 2;
}

// Get a particular setting from the db
function getSetting($setting) {
    global $cainDB;
    $settingValue = $cainDB->select("SELECT `value` FROM settings WHERE `name` = ?;", [$setting]);

    return $settingValue['value'] ?? null;
}

// Put a particular setting into the db
function patchSetting($settingArr) {
    global $cainDB;
    $settingField = $settingArr['settingField'] ?? null;
    $settingValue = $settingArr['settingValue'] ?? null;
    $updateSetting = null;

    if($settingField !== null && $settingValue !== null) {
        $updateSetting = $cainDB->query("UPDATE settings SET `value` = ? WHERE `name` = ?;", [$settingValue, $settingField]);
    }

    return $updateSetting;
}