<?php // Handle all general POST requests

class Process {
    function __construct() {
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
                default:
                    // Silence. This post has not been accounted for.
                    break;
            }

            // Once we are done, we may specify a return path
            if (!empty($_POST['return-path'])) {
                header("Location: " . $_POST['return-path']);
            } else {
                header("Location: /");
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
        $cainDB->query("UPDATE info SET `value` = 0 WHERE `info` = 'version';");
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
            Session::destroy();
            // Account created successfully, redirect or set a success message
            Session::setNotice('Account created successfully. You can now log in.');
            // Redirect to the login page or wherever appropriate
            header("Location: /");
            exit;
        } catch (Exception $e) {
            // Error occurred while executing the query, handle appropriately
            $form->setError('general', 'An error occurred while creating the account. Please try again later.');
        }
    }
}

// Initialise process
$process = new Process;