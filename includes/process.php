<?php // Handle all general POST requests

class Process {
    function __construct() {
        // ! Auto-redirect override (for debugging only, to remain on process page)
        $redirectOverride = true;

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
                case('update-instruments'):
                    $this->updateAllInstruments();
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
                default:
                    // Silence. This post has not been accounted for.
                    break;
            }

            // Once we are done, we may specify a return path
            if(!$redirectOverride) {
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
            if (!preg_match("/^[a-zA-Z]+(?:[ '][a-zA-Z]+)*$/",$firstName)) {
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

    function updateAllInstruments() {
        Session::setNotice("Updating instruments is a feature that I will one day think of.");
    }

    function updateGeneralSettings() {
        global $cainDB, $form;

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
        global $cainDB;

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
        global $cainDB;

        $operatorId = $_POST['id'] ?? null;

        if($operatorId) {
            $cainDB->query("DELETE FROM users WHERE id = :id", [":id" => $operatorId]);
        }

        Session::setNotice("Operator successfully deleted.");
    }

    function deleteResult() {
        global $cainDB;

        $resultId = $_POST['id'] ?? null;

        if($resultId) {
            $cainDB->query("DELETE FROM results WHERE id = :id", [":id" => $resultId]);
        }

        Session::setNotice("Result successfully deleted.");
    }

    function editOperator() {
        global $cainDB, $form;

        $id = $_POST['id'] ?? null;
        $firstName = $_POST['firstName'] ?? null;
        $lastName = $_POST['lastName'] ?? null;
        $password = $_POST['password'] ?? null;
        $password2 = $_POST['password2'] ?? null;
        $firstName = $_POST['firstName'] ?? null;
        $userType = $_POST['userType'] ?? null;

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
            if (!preg_match("/^[a-zA-Z]+(?:[ '][a-zA-Z]+)*$/",$firstName)) {
                $errors['firstName'] = 'First name not valid.';
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
        global $cainDB, $form;

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
        global $cainDB;

        $operatorId = $_POST['id'] ?? null;

        if($operatorId) {
            $status = $cainDB->select("SELECT status FROM users WHERE id = :id", [":id" => $operatorId])['status'];

            $toggle = 0;
            if($status == 0) {
                $toggle = 1;
            }

            $cainDB->query("UPDATE users SET status = :toggle WHERE id = :id;", [":toggle" => $toggle, ":id" => $operatorId]);
        }
    }

    function updateQCSettings() {
        global $cainDB;

        // Retrieve form data
        $qcEnforcement = $_POST['qcEnforcement'];
        $posRequired = $_POST['posRequired'];
        $negRequired = $_POST['negRequired'];
        $enableIndependence = $_POST['enableIndependence'] == "on" ? 1 : 0;

        // Prepare and execute the query to update all settings in one go
        try {
            // Prepare the query
            $query = "UPDATE settings SET `value` = CASE `name`
                WHEN 'qc_enforcement' THEN :qcEnforcement
                WHEN 'qc_positive_requirements' THEN :posRequired
                WHEN 'qc_negative_requirements' THEN :negRequired
                WHEN 'qc_enable_independence' THEN :enableIndependence
                ELSE `value`
            END;";
            
            // Bind parameters
            $params = [
                ':qcEnforcement' => $qcEnforcement,
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
        global $cainDB;

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
        global $cainDB;

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
        header("Location: /");
        Session::setNotice("Oh yeah, filtering doesn't completely work yet...", 2);
    }
}

// Initialise process
$process = new Process;