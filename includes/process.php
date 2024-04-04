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
                case('update-instruments'):
                    $this->updateAllInstruments();
                    break;
                case('general-settings'):
                    $this->updateGeneralSettings();
                    break;
                case('field-settings'):
                    $this->updateFieldSettings();
                    break;
                default:
                    // Silence. This post has not been accounted for.
                    break;
            }

            // Once we are done, we may specify a return path
            if(!$redirectOverride) {
                if (!empty($_POST['return-path'])) {
                    header("Location: " . $_POST['return-path']);
                } else {
                    header("Location: /");
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
            }
        } else {
            if($password) {
                $form->setError('password', 'Incorrect password');
            } else {
                // Check if there are any notices
                if(!Session::getNotices()) {
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
        }
    }

    // Create an account
    function createAccount() {
        global $form, $cainDB;

        // Get and sanitize input values
        $operatorId = htmlspecialchars($_POST['operatorId']);
        $password = htmlspecialchars($_POST['password']);
        $password2 = htmlspecialchars($_POST['password2']);
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
        if (!isset($firstName)) {
            $errors['firstName'] = 'First name is required.';
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
        }
    }

    function updateAllInstruments() {
        Session::setNotice("Updating instruments is a feature that I will one day think of.");
    }

    function updateGeneralSettings() {
        global $cainDB;

        // Retrieve form data
        $hospitalName = $_POST['hospitalName'];
        $officeName = $_POST['officeName'];
        $hospitalLocation = $_POST['hospitalLocation'];
        $dateFormat = $_POST['dateFormat'];

        // TODO: Sanitize form data if required

        // Prepare and execute the query to update all settings in one go
        try {
            // Prepare the query
            $query = "UPDATE settings SET `value` = CASE `name`
                WHEN 'hospital_name' THEN :hospitalName
                WHEN 'office_name' THEN :officeName
                WHEN 'hospital_location' THEN :hospitalLocation
                WHEN 'date_format' THEN :dateFormat
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
                return;
            } else {
                // Failed to update settings
                echo "Failed to update settings.";
            }
        } catch (Exception $e) {
            // Handle exceptions if any
            echo "An error occurred: " . $e->getMessage();
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
        
    }
}

// Initialise process
$process = new Process;