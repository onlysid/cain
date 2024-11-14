<?php // Handle all general POST requests

class Process {
    function __construct() {
        // ! Auto-redirect override (for debugging only, to remain on process page)
        $redirectOverride = false;

        if(isset($_POST['action'])) {
            switch($_POST['action']) {
                case('reset-db-version'):
                    $this->resetDbVersion();
                    break;
                case('login'):
                    $this->login();
                    break;
                case('back-to-login'):
                case('logout'):
                    $this->logout();
                    break;
                case('create-account'):
                    $this->createAccount();
                    break;
                case('general-settings'):
                    $this->updateGeneralSettings();
                    break;
                case('field-settings'):
                    $this->updateFieldSettings();
                    break;
                case('qc-settings'):
                    $this->updateQCSettings();
                    break;
                case('user-general-settings'):
                    $this->updateGeneralUserSettings();
                    break;
                case('delete-operator'):
                    $this->deleteOperator();
                    break;
                case('filter-results'):
                    $this->filterResults();
                    break;
                case('delete-result'):
                    $this->deleteResult();
                    break;
                case('delete-results'):
                    $this->deleteResults();
                    break;
                case('edit-operator'):
                    $this->editOperator();
                    break;
                case('add-operator'):
                    $this->addOperator();
                    break;
                case('toggle-operator-status'):
                    $this->toggleOperatorStatus();
                    break;
                case('network-settings'):
                    $this->updateNetworkSettings();
                    break;
                case('clean-logs'):
                    $this->cleanLogs();
                    break;
                case('qc-types'):
                    $this->changeQCTypes();
                    break;
                case('toggle-instrument-lock'):
                    $this->toggleInstrumentLock();
                    break;
                case('new-instrument-qc'):
                    $this->newInstrumentQC();
                    break;
                case('edit-instrument-qc'):
                    $this->editInstrumentQC();
                    break;
                case('edit-lot-result'):
                    $this->editLotQC();
                    break;
                case('edit-lot'):
                    $this->editLot();
                    break;
                default:
                    // Silence. This post has not been accounted for.
                    break;
            }

            // Once we are done, we may specify a return path
            if(!$redirectOverride) {
                // There may be a redirect baked into the
                if(!isset($_POST['intrinsic-redirect'])) {
                    if (!empty($_POST['return-path'])) {
                        header("Location: " . $_POST['return-path']);
                    } else {
                        header("Location: /");
                    }
                }
            }
        } else {
            // We are here by mistake. Return to referrer and set an error.
            header("Location: /");
            // Set session error
            Session::set('error', 'Invalid request.');
        }
    }

    // Reset the database version in the event of an error
    function resetDbVersion() {
        global $cainDB;
        $cainDB->query("UPDATE versions SET `value` = 0 WHERE `info` = 'web-app';");
    }

    // Log the user in
    function login() {
        global $form;
        // Attempt to authenticate the user
        $operatorId = $_POST['operatorId'];
        $password = $_POST['password'] ?? null;

        if (Session::authenticate($operatorId, $password)) {
            // Authentication successful - log user in.
            try {
                Session::login($operatorId);
            } catch(Exception $e) {
                // Throw error
                $form->setError('general', 'Something went wrong. Please try again later.');
                Session::setNotice("Something went wrong.", 2);
            }
        } else {
            if($password) {
                $form->setError('password', 'Incorrect password');
            } else {
                // Check if there are any notices
                if(!Session::get('password-required')) {
                    $form->setError('operatorId', 'Operator ID not recognised.');
                }
            }
        }
    }

    // Log the user out
    function logout() {
        try {
            Session::logout();
        } catch(Exception $e) {
            // Throw error
            $form->setError('general', 'Something went wrong. Please try again later.');
            Session::setNotice("Something went wrong.", 2);
        }
    }

    // Create an account
    function createAccount() {
        // TODO: Make first name and last name validation logic consistent and abstract the ruleset so as to DRY up the code.
        global $form, $cainDB;

        // Get and sanitize input values
        $operatorId = htmlspecialchars($_POST['operatorId']);
        $password = $_POST['password'];
        $password2 = $_POST['password2'];
        $firstName = htmlspecialchars($_POST['firstName']);
        $lastName = htmlspecialchars($_POST['lastName']);

        // Validate input values
        $errors = array();

        // Check if operator ID is !isset
        if (!isset($operatorId)) {
            $errors['operatorId'] = 'Operator ID is required.';
        }

        // Check if password is !isset
        if (!isset($password)) {
            $errors['password'] = 'Password is required.';
        }

        // Check if password confirmation is !isset
        if (!isset($password2)) {
            $errors['password2'] = 'Password confirmation is required.';
        }

        // Check if passwords match
        if ($password !== $password2) {
            $errors['password2'] = 'Passwords do not match.';
        }

        // Check if first name is !isset
        if (!isset($firstName) || empty($firstName)) {
            $errors['firstName'] = 'First name is required.';
        } else {
            // Validate firstName
            $firstName = testInput($firstName);
            // Check if firstName contains only letters, whitespace, and apostrophes
            if (!preg_match("/^[a-zA-Z]+(?:[ '][a-zA-Z]+)*$/", $firstName)) {
                $errors['firstName'] = 'First name not valid.';
            }
        }

        // Password validation
        if (!preg_match('/^(?=.*[A-Z].*[A-Z])(?=.*[a-z].*[a-z])(?=.*\d.*\d)(?=.*[^\w\d\s]).{8,}$/', $password)) {
            $errors['password'] = 'Password must contain at least 8 characters, 2 uppercase letters, 2 lowercase letters, 2 numbers, and 2 symbols.';
        }

        // If there are errors, set them in the form object
        if (!empty($errors)) {
            foreach ($errors as $field => $error) {
                $form->setError($field, $error);
            }
            return; // Exit early if there are errors
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Proceed with inserting the data into the database
        $query = "UPDATE users SET `password` = :password, `first_name` = :firstName, `last_name` = :lastName WHERE operator_id = :operatorId;";
        $params = array(
            ':operatorId' => $operatorId,
            ':password' => $hashedPassword,
            ':firstName' => $firstName,
            ':lastName' => $lastName
        );

        // Execute the query
        try {
            $cainDB->query($query, $params);

            // Destroy previous session as we're starting fresh now.
            Session::destroy();

            // Account created successfully, redirect or set a success message.
            Session::setNotice('Account created successfully. You can now log in.');

            // Make it a little easier by pre-authenticating the userId!
            Session::authenticate($operatorId, null);

            // Redirect to the login page or wherever appropriate
            header("Location: /");
            exit;
        } catch (Exception $e) {
            // Error occurred while executing the query, handle appropriately
            $form->setError('general', 'An error occurred while creating the account. Please try again later.');
            Session::setNotice("Something went wrong.", 2);
        }
    }

    function updateGeneralSettings() {
        global $cainDB, $form, $session;

        if($session->getUserType() < ADMINISTRATIVE_CLINICIAN) {
            Session::setNotice("You do not have permission to do this.", 2);
            return;
        }

        // Retrieve form data
        $hospitalName = $_POST['hospitalName'];
        $officeName = $_POST['officeName'];
        $hospitalLocation = $_POST['hospitalLocation'];
        $dateFormat = $_POST['dateFormat'];

        if(!testInput($hospitalName)) {
            $errors['hospitalName'] = "Hospital name cannot be empty.";
        }

        if(!testInput($officeName)) {
            $errors['officeName'] = "Office name cannot be empty.";
        }

        if(!testInput($hospitalLocation)) {
            $errors['hospitalLocation'] = "Hospital Location cannot be empty.";
        }

        // If there are errors, set them in the form object
        if (!empty($errors)) {
            foreach ($errors as $field => $error) {
                $form->setError($field, $error);
            }
            return; // Exit early if there are errors
        }

        // Prepare and execute the query to update all settings in one go
        try {
            // Prepare the query
            $query = "UPDATE settings SET `value` = CASE `name`
                WHEN 'hospital_name' THEN :hospitalName
                WHEN 'office_name' THEN :officeName
                WHEN 'hospital_location' THEN :hospitalLocation
                WHEN 'date_format' THEN :dateFormat
                ELSE `value`
            END;";

            // Bind parameters
            $params = [
                ':hospitalName' => $hospitalName,
                ':officeName' => $officeName,
                ':hospitalLocation' => $hospitalLocation,
                ':dateFormat' => $dateFormat
            ];

            // Execute the query
            $rowCount = $cainDB->query($query, $params);

            // Check if the update was successful
            if ($rowCount > 0) {
                Session::setNotice("Successfully updated settings.");
                return;
            } else {
                // Failed to update settings
                Session::setNotice("No changes made.", 1);
            }
        } catch (Exception $e) {
            // Handle exceptions if any
            echo "An error occurred: " . $e->getMessage();
            Session::setNotice("Something went wrong.", 2);
        }
    }

    function updateFieldSettings() {
        global $cainDB, $session;

        if($session->getUserType() < ADMINISTRATIVE_CLINICIAN) {
            Session::setNotice("You do not have permission to do this.", 2);
            return;
        }

        $hospitalInfo = systemInfo();

        // Extract 'name' as keys and 'value' as values
        $settings = array_column($hospitalInfo, 'value', 'name');

        // Field Items
        require_once 'utils/DataField.php';

        // Here are the current DB numbers
        $behaviourFields = getSettingsBitmap(count($dataFields), 3, $fieldInfo['field_behaviour']);

        // For visibility, because of the nature of checkbox form submissions, assume all settings are 0
        $visibilityFields = array_fill(0, count($dataFields), 0);

        // Now we loop through the posted behaviour data and the posted visibility data, manipulating the fields as we go
        foreach($_POST as $postLabel => $postData) {
            if(strpos($postLabel, "fieldBehaviour") !== false) {
                $behaviourFields[str_replace("fieldBehaviour", "", $postLabel)] = $postData;
            }

            if(strpos($postLabel, "fieldVisibility") !== false) {
                $visibilityFields[str_replace("fieldVisibility", "", $postLabel)] = ($postData ? 1 : 0);
            }
        }

        // Convert the manipulated data back into the integer representation
        $updatedBehaviour = convertBitmapArrayToInt($behaviourFields, 4);
        $updatedVisibility = convertBitmapArrayToInt($visibilityFields, 2);

        // Add to the database!
        $cainDB->query("UPDATE settings SET `value` = :updatedBehaviour WHERE `name` = 'field_behaviour';", [":updatedBehaviour" => $updatedBehaviour]);
        $cainDB->query("UPDATE settings SET `value` = :updatedVisibility WHERE `name` = 'field_visibility';", [":updatedVisibility" => $updatedVisibility]);

        Session::setNotice("Successfully updated settings.");
    }

    function deleteOperator() {
        global $cainDB, $session;

        if($session->getUserType() < ADMINISTRATIVE_CLINICIAN) {
            Session::setNotice("You do not have permission to do this.", 2);
            return;
        }

        $operatorId = $_POST['id'] ?? null;

        if($operatorId) {
            $cainDB->query("DELETE FROM users WHERE id = :id", [":id" => $operatorId]);
        }

        Session::setNotice("Operator successfully deleted.");
    }

    function deleteResult() {
        global $cainDB, $session;

        if($session->getUserType() < ADMINISTRATIVE_CLINICIAN) {
            Session::setNotice("You do not have permission to do this.", 2);
            return;
        }

        $resultId = $_POST['id'] ?? null;

        if($resultId) {
            $cainDB->query("DELETE FROM results WHERE id = :id", [":id" => $resultId]);
        }

        Session::setNotice("Result successfully deleted.");
    }

    function deleteResults() {
        global $cainDB, $session;

        if($session->getUserType() < ADMINISTRATIVE_CLINICIAN) {
            Session::setNotice("You do not have permission to do this.", 2);
            return;
        }

        $dateRange = $_POST['dateRange'] ?? null;

        $queryDelete = "DELETE FROM results WHERE ";
        $queryCheck = "SELECT id FROM results WHERE ";
        $query = "";
        $params = [];

        if($dateRange) {
            // We need to parse the date range
            $dateRangeArr = explode(' - ', $dateRange);

            if($dateRangeArr[0]) {
                $query .= 'STR_TO_DATE(testcompletetimestamp, "%Y-%m-%d %H:%i") >= ?';
                $params[] = $dateRangeArr[0] . " 00:00";
            }
            if(isset($dateRangeArr[1])) {
                $query .= ' AND STR_TO_DATE(testcompletetimestamp, "%Y-%m-%d %H:%i") <= ?';
                $params[] = $dateRangeArr[1] . " 00:00";
            } else {
                $query .= ' AND STR_TO_DATE(testcompletetimestamp, "%Y-%m-%d %H:%i") <= ?';
                $params[] = $dateRangeArr[0] . " 23:59";
            }
        } else {
            $query .= "1;";
        }

        // First, we must select all the ID's of the query and
        $queryCheck .= $query;
        $queryDelete .= $query;

        $results = $cainDB->selectAll($queryCheck, $params);

        foreach($results as $result) {
            $id = $result['id'];

            // Directory for CSV curves
            $curvesDir = __DIR__ . "/../curves";

            $file = $curvesDir . "/" . $id . ".csv";

            if(file_exists($file)) {
                unlink($file);
            }
        }

        $count = $cainDB->query($queryDelete, $params);

        if($count > 0) {
            Session::setNotice($count . (($count > 1 || $count == 0) ? " results " : " result ") . "successfully deleted.");
        } else {
            Session::setNotice("There are no results to delete for the selected time frame.");
        }
    }

    function editOperator() {
        global $cainDB, $form, $session;

        $id = $_POST['id'] ?? null;
        $firstName = $_POST['firstName'] ?? null;
        $lastName = $_POST['lastName'] ?? null;
        $password = $_POST['password'] ?? null;
        $password2 = $_POST['password2'] ?? null;
        $firstName = $_POST['firstName'] ?? null;
        $userType = $_POST['userType'] ?? null;

        // Firstly, we need to check if either the user is an admin clinician OR the user is trying to edit themselves
        $currUser = $cainDB->select("SELECT user_id FROM users WHERE id = ?;", [$id])['user_id'];
        if($session->getUserType() < ADMINISTRATIVE_CLINICIAN && $session->get('user-id') != $currUser) {
            Session::setNotice("You do not have permission to do this.", 2);
            return;
        }

        // Validate input values
        $errors = array();

        if (!$id) {
            $errors['id'] = 'Operator ID is required.';
        }

        if ($password && $password !== $password2) {
            $errors['password2'] = 'Passwords do not match.';
        }

        // Password validation
        if ($password && !preg_match('/^(?=.*[A-Z].*[A-Z])(?=.*[a-z].*[a-z])(?=.*\d.*\d)(?=.*[^\w\d\s]).{8,}$/', $password)) {
            $errors['password'] = 'Password must contain at least 8 characters, 2 uppercase letters, 2 lowercase letters, 2 numbers, and 2 symbols.';
        }

        // Check if first name is !isset
        if (!isset($firstName) || empty($firstName)) {
            $errors['firstName'] = 'First name is required.';
        } else {
            // Validate firstName
            $firstName = testInput($firstName);
            // Check if firstName contains only letters, whitespace, and apostrophes
            if (!preg_match("/^[a-z]+(?:[ '][a-z]+)*$/i", $firstName)) {
                $errors['firstName'] = 'LOOK AT ME! First name not valid.';
            }
        }

        // If there are errors, set them in the form object
        if (!empty($errors)) {
            if($id) {
                $form->setValue("form", "edit");
                $form->setValue("id", $id);
            }
            foreach ($errors as $field => $error) {
                $form->setError($field, $error);
            }
            return; // Exit early if there are errors
        }

        // Hash the password if provided
        $hashedPassword = $password ? password_hash($password, PASSWORD_BCRYPT) : null;

        // Construct the query
        $query = "UPDATE users SET ";
        $updates = [];

        if($userType) {
            $updates[] = "`user_type` = :userType";
        }

        // Add password update if provided
        if ($hashedPassword !== null) {
            $updates[] = "`password` = :password";
        }

        // Add first name update if provided
        if ($firstName) {
            $updates[] = "`first_name` = :firstName";
        }

        // Add last name update if provided
        if ($lastName) {
            $updates[] = "`last_name` = :lastName";
        }

        // Construct the WHERE clause
        $query .= implode(", ", $updates);
        $query .= " WHERE id = :id;";

        // Prepare parameters for query
        $params = [
            ':id' => $id,
        ];

        if($userType) {
            $params[':userType'] = $userType;
        }

        // Add password parameter if provided
        if ($hashedPassword !== null) {
            $params[':password'] = $hashedPassword;
        }

        // Add first name parameter if provided
        if ($firstName) {
            $params[':firstName'] = $firstName;
        }

        // Add last name parameter if provided
        if ($lastName) {
            $params[':lastName'] = $lastName;
        }

        // Execute the query
        try {
            // Execute the query
            $rowCount = $cainDB->query($query, $params);

            // Check if the update was successful
            if ($rowCount > 0) {
                Session::setNotice("Successfully updated settings.");
                return;
            } else {
                // Failed to update settings
                Session::setNotice("No changes made.", 1);
            }
        } catch (Exception $e) {
            // Error occurred while executing the query, handle appropriately
            $form->setError('general', 'An error occurred while creating the account. Please try again later.');
            Session::setNotice("Something went wrong.", 2);
        }
    }

    function addOperator() {
        global $cainDB, $form, $session;

        if($session->getUserType() < ADMINISTRATIVE_CLINICIAN) {
            Session::setNotice("You do not have permission to do this.", 2);
            return;
        }

        $operatorId = $_POST['operatorId'] ?? null;
        $firstName = $_POST['firstName'] ?? null;
        $lastName = $_POST['lastName'] ?? null;
        $password = $_POST['password'] ?? null;
        $password2 = $_POST['password2'] ?? null;
        $firstName = $_POST['firstName'] ?? null;
        $userType = $_POST['userType'] ?? null;

        // Validate input values
        $errors = array();

        if (!$operatorId) {
            $errors['operatorId'] = 'Operator ID is required.';
        } else {
            $form->setValue('operatorId', $operatorId);
        }

        if ($password && $password !== $password2) {
            $errors['password2'] = 'Passwords do not match.';
        }

        // Password validation
        if ($password && !preg_match('/^(?=.*[A-Z].*[A-Z])(?=.*[a-z].*[a-z])(?=.*\d.*\d)(?=.*[^\w\d\s]).{8,}$/', $password)) {
            $errors['password'] = 'Password must contain at least 8 characters, 2 uppercase letters, 2 lowercase letters, 2 numbers, and 2 symbols.';
        }

        // Check if first name is !isset
        if (!isset($firstName) || empty($firstName)) {
            $errors['firstName'] = 'First name is required.';
        } else {
            // Validate firstName
            $firstName = testInput($firstName);
            // Check if firstName contains only letters, whitespace, and apostrophes
            if (!preg_match("/^[a-zA-Z]+(?:[ '][a-zA-Z]+)*$/",$firstName)) {
                $errors['firstName'] = 'First name not valid.';
            } else {
                $form->setValue('firstName', $firstName);
            }
        }

        if($lastName) {
            $form->setValue('lastName', $lastName);
        }

        if($userType) {
            $form->setValue('userType', $userType);
        }

        // If there are errors, set them in the form object
        if (!empty($errors)) {
            $form->setValue("form", "add");
            foreach ($errors as $field => $error) {
                $form->setError($field, $error);
            }
            return; // Exit early if there are errors
        }

        // Hash the password if provided
        $hashedPassword = $password ? password_hash($password, PASSWORD_BCRYPT) : null;

        // Construct the query
        $query = "INSERT INTO users (`operator_id`, `user_type`";
        $params = [':operatorId' => $operatorId, ':userType' => $userType];

        // Add password and its placeholder if provided
        if ($hashedPassword !== null) {
            $query .= ", `password`";
            $params[':password'] = $hashedPassword;
        }

        // Add first name and its placeholder if provided
        if ($firstName) {
            $query .= ", `first_name`";
            $params[':firstName'] = $firstName;
        }

        // Add last name and its placeholder if provided
        if ($lastName) {
            $query .= ", `last_name`";
            $params[':lastName'] = $lastName;
        }

        // Complete the query
        $query .= ") VALUES (:operatorId, :userType";

        // Add placeholders for password, first name, and last name if provided
        if ($hashedPassword !== null) {
            $query .= ", :password";
        }
        if ($firstName) {
            $query .= ", :firstName";
        }
        if ($lastName) {
            $query .= ", :lastName";
        }
        $query .= ");";

        // Execute the query
        try {
            $cainDB->query($query, $params);
        } catch (Exception $e) {
            // Error occurred while executing the query, handle appropriately
            $form->setError('general', 'An error occurred while creating the account. Please try again later.');
            Session::setNotice("Something went wrong.", 2);
        }
    }

    function toggleOperatorStatus() {
        global $cainDB, $session;

        if(intval($session->getUserType()) < ADMINISTRATIVE_CLINICIAN) {
            Session::setNotice("You do not have permission to do this.", 2);
            return;
        }

        $operatorId = $_POST['id'] ?? null;

        if($operatorId) {
            $result = $cainDB->select("SELECT status, operator_id FROM users WHERE id = :id", [":id" => $operatorId]);
            $status = $result['status'];
            $operator = $result['operator_id'];

            $toggle = 0;
            if($status == 0) {
                $toggle = 1;
            }

            $cainDB->query("UPDATE users SET status = :toggle WHERE id = :id;", [":toggle" => $toggle, ":id" => $operatorId]);
        }

        Session::setNotice("$operator succsessfully " . ($status == 0 ? "activated." : "deactivated."));
    }

    function updateQCSettings() {
        global $cainDB, $session;

        if(intval($session->getUserType()) < ADMINISTRATIVE_CLINICIAN) {
            Session::setNotice("You do not have permission to do this.", 2);
            return;
        }

        // Retrieve form data
        $qcPolicy = $_POST['qcPolicy'];
        $posRequired = $_POST['posRequired'];
        $negRequired = $_POST['negRequired'];
        $enableIndependence = $_POST['enableIndependence'] == "on" ? 1 : 0;

        // Prepare and execute the query to update all settings in one go
        try {
            // Prepare the query
            $query = "UPDATE settings SET `value` = CASE `name`
                WHEN 'qc_policy' THEN :qcPolicy
                WHEN 'qc_positive_requirements' THEN :posRequired
                WHEN 'qc_negative_requirements' THEN :negRequired
                WHEN 'qc_enable_independence' THEN :enableIndependence
                ELSE `value`
            END;";

            // Bind parameters
            $params = [
                ':qcPolicy' => $qcPolicy,
                ':posRequired' => $posRequired,
                ':negRequired' => $negRequired,
                ':enableIndependence' => $enableIndependence,
            ];

            // Execute the query
            $rowCount = $cainDB->query($query, $params);

            // Check if the update was successful
            if ($rowCount > 0) {
                Session::setNotice("Successfully updated settings.");
                return;
            } else {
                // Failed to update settings
                Session::setNotice("No changes made.", 1);
            }
        } catch (Exception $e) {
            // Handle exceptions if any
            echo "An error occurred: " . $e->getMessage();
            Session::setNotice("Something went wrong.", 2);
        }

    }

    function updateGeneralUserSettings() {
        global $cainDB, $session;

        if($session->getUserType() < ADMINISTRATIVE_CLINICIAN) {
            Session::setNotice("You do not have permission to do this.", 2);
            return;
        }

        // Retrieve form data
        $sessionTimeout = $_POST['sessionTimeout'];
        $passwordRequired = $_POST['passwordRequired'] == "on" ? 1 : 0;
        $adminPasswordRequired = $_POST['adminPasswordRequired'] == "on" ? 1 : 0;

        // Prepare and execute the query to update all settings in one go
        try {
            // Prepare the query
            $query = "UPDATE settings SET `value` = CASE `name`
                WHEN 'session_expiration' THEN :sessionTimeout
                WHEN 'password_required' THEN :passwordRequired
                ELSE `value`
            END;";

            // Bind parameters
            $params = [
                ':sessionTimeout' => $sessionTimeout,
                ':passwordRequired' => $passwordRequired * 2 + $adminPasswordRequired,
            ];

            // Execute the query
            $rowCount = $cainDB->query($query, $params);

            // Check if the update was successful
            if ($rowCount > 0) {
                Session::setNotice("Successfully updated settings.");
                return;
            } else {
                // Failed to update settings
                Session::setNotice("No changes made.", 1);
            }
        } catch (Exception $e) {
            // Handle exceptions if any
            echo "An error occurred: " . $e->getMessage();
            Session::setNotice("Something went wrong.", 2);
        }
    }

    function updateNetworkSettings() {
        global $cainDB, $session;

        if($session->getUserType() < ADMINISTRATIVE_CLINICIAN) {
            Session::setNotice("You do not have permission to do this.", 2);
            return;
        }

        // Retrieve form data
        $protocol = $_POST['protocol'] == 0 ? "Cain" : "HL7";
        $cainIP = $_POST['cainIP'];
        $cainPort = $_POST['cainPort'];
        $hl7IP = $_POST['hl7IP'];
        $hl7Port = $_POST['hl7Port'];
        $hl7ServerName = $_POST['hl7ServerName'];
        $patientId = ($_POST['patientId'] ?? null) == "on" ? 1 : 0;
        $testMode = $_POST['testMode'] == "on" ? 1 : 0;
        $appMode = $_POST['appMode'] == "on" ? 1 : 0;

        // Prepare and execute the query to update all settings in one go
        try {
            // Prepare the query
            $query = "UPDATE settings SET `value` = CASE `name`
                WHEN 'selected_protocol' THEN :protocol
                WHEN 'cain_server_ip' THEN :cainIP
                WHEN 'cain_server_port' THEN :cainPort
                WHEN 'hl7_server_ip' THEN :hl7IP
                WHEN 'hly_server_port' THEN :hl7Port
                WHEN 'hl7_server_dest' THEN :hl7ServerName
                WHEN 'patient_id' THEN :patientId
                WHEN 'test_mode' THEN :testMode
                WHEN 'app_mode' THEN :appMode
                ELSE `value`
            END;";

            // Bind parameters
            $params = [
                ':protocol' => $protocol,
                ':cainIP' => $cainIP,
                ':cainPort' => $cainPort,
                ':hl7IP' => $hl7IP,
                ':hl7Port' => $hl7Port,
                ':hl7ServerName' => $hl7ServerName,
                ':patientId' => $patientId,
                ':testMode' => $testMode,
                ':appMode' => $appMode
            ];

            // Execute the query
            $rowCount = $cainDB->query($query, $params);

            // Check if the update was successful
            if ($rowCount > 0) {
                Session::setNotice("Successfully updated settings.");
                return;
            } else {
                // Failed to update settings
                Session::setNotice("No changes made.", 1);
            }
        } catch (Exception $e) {
            // Handle exceptions if any
            echo "An error occurred: " . $e->getMessage();
            Session::setNotice("Something went wrong.", 2);
        }
    }

    function filterResults() {
        global $cainDB;

        // Retrieve filters form data
        $searchQuery = $_POST['s'] ?? null;
        $sex = $_POST['sex'] ?? null;
        $ageGroup = $_POST['ageGroup'] ?? null;
        $sentToLIMS = $_POST['sentToLIMS'] ?? null;
        $filterDates = $_POST['filterDates'] ?? null;
        $resultPolarity = ($_POST['resultPolarity'] ?? null) == "on" ? 1 : 0;

        // Get the return path and the query params that already exist
        $url = $_POST['return-path'];
        $paramArr = getParams($url);

        unset($paramArr['p']);

        // Recreate the GET request by redirecting to a location with params set by the new filters
        if($searchQuery != "") {
            $paramArr['s'] = $searchQuery;
        } else {
            unset($paramArr['s']);
        }
        if($sex != "") {
            $paramArr['g'] = $sex;
        } else {
            unset($paramArr['g']);
        }
        if($ageGroup != "") {
            $paramArr['a'] = $ageGroup;
        } else {
            unset($paramArr['a']);
        }
        if($sentToLIMS != "") {
            $paramArr['l'] = $sentToLIMS;
        } else {
            unset($paramArr['l']);
        }
        if($filterDates != "") {
            $paramArr['d'] = $filterDates;
        } else {
            unset($paramArr['d']);
        }
        if($resultPolarity != "") {
            $paramArr['r'] = $resultPolarity;
        } else {
            unset($paramArr['r']);
        }

        // Create the filter query
        $filter = "";

        if(count($paramArr) > 0) {
            $filter .= "?";
            $i = 0;
            foreach($paramArr as $param => $paramData) {
                if($i != 0) {
                    $filter .= "&";
                }
                $filter .= $param . "=" . $paramData;
                $i++;
            }
        }

        header("Location: /" . $filter);
    }

    function cleanLogs() {
        // Try deleting logs
        try {
            // Get the logs folder
            $folderPath = rtrim(BASE_DIR . '/logs', '/');

            // Counter for counting deleted files
            $deletedFilesCount = 0;
            $totalFileSize = 0;

            // Loop through files in logs dir
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($folderPath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach($files as $file) {
                // Check if it's a .gz file
                if(!$file->isDir() && $file->getExtension() === 'gz') {
                    // Add to the filesize
                    $totalFileSize += $file->getSize();

                    // Delete the file and increment counter if successful
                    if(unlink($file->getRealPath())) {
                        $deletedFilesCount++;
                    } else {
                        throw new Exception('Could not delete file.');
                    }
                }
            }

            $formattedFileSize = formatSize($totalFileSize);

            Session::setNotice("Successfully deleted old logs. $formattedFileSize of space has been cleared and $deletedFilesCount file(s) have been deleted.", 1);
        } catch (Exception $e) {
            Session::setNotice("Could not delete expired logs. Please contact a service engineer.", 2);
        }
    }

    function changeQCTypes() {
        global $cainDB;

        // Get the params
        $testTypes = json_decode($_POST['qcTestTypes']) ?? null;

        // Collect IDs from the input array for easy reference
        $ids = array_map(function($testType) { return $testType->id; }, $testTypes);

        // Prepare SQL statement with ON DUPLICATE KEY UPDATE for insert or update
        $sql = "
        INSERT INTO instrument_test_types (id, name, time_intervals, result_intervals)
        VALUES (:id, :name, :time_intervals, :result_intervals)
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            time_intervals = VALUES(time_intervals),
            result_intervals = VALUES(result_intervals)
        ";

        // Loop through each test type and execute the query
        $cainDB->beginTransaction();

        foreach ($testTypes as $testType) {
            $timeIntervals = is_numeric($testType->time_intervals) ? $testType->time_intervals : null;
            $resultIntervals = is_numeric($testType->result_intervals) ? $testType->result_intervals : null;

            $cainDB->query($sql,
            [
                ':id' => $testType->id,
                ':name' => $testType->name,
                ':time_intervals' => $timeIntervals,
                ':result_intervals' => $resultIntervals
            ]);
        }

        // Delete entries from the database that are not in the current list of IDs (if no IDs available, delete all by suggesting ALL BUT that with the ID of 0 - bit of a hack)
        $in  = $ids ? str_repeat('?,', count($ids) - 1) . '?' : 0; // Create a placeholder string
        $deleteSql = "DELETE FROM instrument_test_types WHERE id NOT IN ($in)";
        $cainDB->query($deleteSql, $ids);

        $cainDB->commit();

        Session::setNotice("Successfully updated test types. You may now use these in your instrument QC.", 1);
    }

    function toggleInstrumentLock() {
        global $cainDB;

        // Get the ID of the instrument
        $instrument = $_POST['instrument'] ?? null;

        if($instrument) {
            // Get current instrument status
            $locked = $cainDB->select("SELECT * FROM instruments WHERE id = ?;", [$instrument]);

            if($locked['locked']) {
                $cainDB->query("UPDATE instruments SET locked = 0 WHERE id = ?;", [$instrument]);
                Session::setNotice("Unlocked instrument #$instrument.");
            } else {
                $cainDB->query("UPDATE instruments SET locked = 1 WHERE id = ?;", [$instrument]);
                Session::setNotice("Locked instrument #$instrument.");
            }
        }
    }

    function newInstrumentQC() {
        global $cainDB, $form;

        // Get all the input values
        $dateTime = $_POST['datetime'];
        $instrument = $_POST['instrument'];
        $qcType = $_POST['qc-type'];
        $result = $_POST['result'];
        $notes = $_POST['notes'];

        $currentUser = userInfo();

        $errors = [];

        // Check that everything which should be set is set
        if(!$dateTime) {
            $dateTime = time();
        }

        if(!isset($instrument)) {
            $errors['instrument'] = 'An instrument must be selected';
        }

        if(!isset($qcType)) {
            $errors['qc-type'] = 'A QC type must be selected';
        }

        if(!isset($currentUser)) {
            Session::setNotice("Must be logged in to log a QC result.", 1);
            return;
        }

        // If there are errors, set them in the form object
        if (!empty($errors)) {
            foreach ($errors as $field => $error) {
                $form->setError($field, $error);
            }
            header("Location: " . $_POST['return-path-err']);
            exit(); // Exit early if there are errors
        }


        $params = [
            ':dateTime' => strtotime($dateTime),
            ':result' => $result ?? 1,
            ':instrument' => $instrument,
            ':user' => $currentUser['id'],
            ':qcType' => $qcType,
            ':notes' => $notes ?? null
        ];

        try {
            $cainDB->query("INSERT INTO instrument_qc_results (`timestamp`, result, instrument, user, `type`, `notes`) VALUES (:dateTime, :result, :instrument, :user, :qcType, :notes);", $params);
            Session::setNotice('QC Test Result successfully logged.');
        } catch (Exception $e) {
            Session::setNotice("Error: Something went wrong. Please contact an administrator.");
            header("Location: " . $_POST['return-path-err']);
            exit(); // Exit early if there are errors
        }
    }

    function editInstrumentQC() {
        global $cainDB, $form;

        // Get all the input values
        $qcID = $_POST['qc-result'];
        $dateTime = $_POST['datetime'];
        $instrument = $_POST['instrument'];
        $qcType = $_POST['qc-type'];
        $result = $_POST['result'];
        $notes = $_POST['notes'];

        $currentUser = userInfo();

        $errors = [];

        // Check that everything which should be set is set
        if(!$dateTime) {
            $dateTime = time();
        }

        if(!isset($instrument)) {
            $errors['instrument'] = 'An instrument must be selected';
        }

        if(!isset($qcType)) {
            $errors['qc-type'] = 'A QC type must be selected';
        }

        if(!isset($currentUser)) {
            Session::setNotice("Must be logged in to log a QC result.", 1);
            return;
        }

        // If there are errors, set them in the form object
        if (!empty($errors)) {
            foreach ($errors as $field => $error) {
                $form->setError($field, $error);
            }
            return; // Exit early if there are errors
        }


        $params = [
            ':dateTime' => strtotime($dateTime),
            ':result' => $result ?? 1,
            ':qcType' => $qcType,
            ':notes' => $notes ?? null,
            ':id' => $qcID
        ];

        try {
            $cainDB->query("UPDATE instrument_qc_results SET `timestamp` = :dateTime, result = :result, `type` = :qcType, `notes` = :notes WHERE id = :id;", $params);
            Session::setNotice('QC Test Result successfully updated.');
        } catch (Exception $e) {
            Session::setNotice("Error: Something went wrong. Please contact an administrator.");
        }
    }

    function editLotQC() {
        global $cainDB, $session;

        // Get all the input values
        $qcID = $_POST['id'];
        $qcResult = $_POST['qcResult'];
        $qcNotes = $_POST['qcNotes'] ?? null;
        $currentUser = userInfo()['id'];

        // Run all QC related queries
        try {
            // Update the QC Result record
            $sql = "UPDATE lots_qc_results SET `qc_result` = ?, `reference` = ?, `operator_id` = ? WHERE `id` = ?;";

            $cainDB->query($sql, [$qcResult, $qcNotes, $currentUser, $qcID]);

            // Get the lot ID
            $lot = $cainDB->select("SELECT lot FROM lots_qc_results WHERE id = ?;", [$qcID])['lot'];

            // Now run QC check
            $return = lotQCCheck($lot);

            // Set notices
            Session::setNotice("Successfully updated lot QC", 0);

            if($return) {
                Session::setNotice("Lot #$lot has now passed QC.", 0);
            }
        } catch (Exception $e) {
            Session::setNotice("Error: Something went wrong. Please contact an administrator.");
        }
    }

    function editLot() {
        global $cainDB, $session;

        // Get all the input values
        $lot = $_POST['id'];
        $qcResult = $_POST['qcResult'] ?? 0;
        $deliveryDate = $_POST['delivery'] ?? null;
        $expirationDate = $_POST['expiration'] ?? null;
        $currentUser = userInfo()['id'];

        // Update the lot
        $time = Date('Y-m-y H:i:s');

        $sql = "UPDATE lots SET delivery_date = ?, expiration_date = ?, qc_pass = ?, last_updated = ? WHERE id = ?;";

        try {
            $cainDB->query($sql, [$deliveryDate, $expirationDate, $qcResult, $time, $lot]);

            Session::setNotice("Successfully updated lot #$lot", 0);
        } catch(Exception $e) {
            Session::setNotice("Error: Something went wrong. Please contact an administrator.");
        }
    }
}

// Initialise process
$process = new Process;