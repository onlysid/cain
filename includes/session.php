<?php 
class Session {
    // Set session expiry time (in seconds)
    private static $sessionExpiry = 30; // 30 minutes

    function __construct() {
        global $cainDB;
        $expirySetting = $cainDB->select("SELECT value FROM settings WHERE name = 'session_expiration';");
        if ($expirySetting) {
            self::$sessionExpiry = (int) $expirySetting['value'];
        } else {
            $sessionExpiry = false;
        }
        $this->start();
    }

    // Start session
    public static function start() {
        session_start();

        // Regenerate session ID if it's time to do so (every 30 minutes)
        if (!isset($_SESSION['last-generated']) || time() - $_SESSION['last-generated'] > 60 * self::$sessionExpiry) {
            // Regenerate session ID and destroy the old session
            session_regenerate_id(true);

            // Update the last regenerated timestamp
            $_SESSION['last-generated'] = time();
        }

        // Check session expiry
        self::checkExpiry();
    }

    // Set session variable
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    // Get session variable
    public static function get($key) {
        return $_SESSION[$key] ?? null;
    }

    // Set session notice
    public static function setNotice($message) {
        if (!self::exists('notices')) {
            self::set('notices', []);
        }
        $_SESSION['notices'][] = $message;
    }

    // Get session notices
    public static function getNotices() {
        return self::get('notices') ?? [];
    }

    // Clear session notices
    public static function clearNotices() {
        self::remove('notices');
    }

    // Set session warning
    public static function setWarning($message) {
        if (!self::exists('warnings')) {
            self::set('warnings', []);
        }
        $_SESSION['warnings'][] = $message;
    }

    // Get session warnings
    public static function getWarnings() {
        return self::get('warnings') ?? [];
    }

    // Clear session warnings
    public static function clearWarnings() {
        self::remove('warnings');
    }

    // Check if session variable exists
    public static function exists($key) {
        return isset($_SESSION[$key]);
    }

    // Remove session variable
    public static function remove($key) {
        if (self::exists($key)) {
            unset($_SESSION[$key]);
        }
    }

    // Destroy session
    public static function destroy() {
        // Destroy current session
        session_destroy();

        // Start a new session
        self::start();
    }

    // Check session expiry
    private static function checkExpiry() {
        if (isset($_SESSION['last-activity']) && self::$sessionExpiry && time() - $_SESSION['last-activity'] > self::$sessionExpiry * 60) {
            // Session expired, destroy it and go to login page
            self::destroy();

            // Set a notice to let the use know why they're at the login page
            self::setNotice('You have been timed out. Please log in to continue.');
        } else {
            // Update last activity timestamp
            $_SESSION['last-activity'] = time();
        }
    }


    // Authenticate user
    public static function authenticate($operatorId, $password) {
        global $cainDB;

        $passwordRequired = isPasswordRequired();
    
        // Check if operator is in the database
        if(operatorExists($operatorId)) {
            // Get user information password
            $operatorInfo = $cainDB->select("SELECT * FROM `users` WHERE `operator_id` = :operatorId;", [':operatorId' => $operatorId]);

            // If the operator has been deactivated, show a message to say so
            if($operatorInfo['status'] == '0') {
                self::setNotice('This operator has been deactivated.');
                return false;
            }

            // If we don't need a password generally and the operator is a clinician
            if(($passwordRequired < 2 && $operatorInfo['user_type'] == CLINICIAN) || (($passwordRequired == 0 || $passwordRequired == 2) && $operatorInfo['user_type'] == ADMINISTRATIVE_CLINICIAN)) {
                return true;
            }

            self::set('provisional-operator', $operatorId);
            self::set('password-required', true);
            self::set('provisional-operator-fname', $operatorInfo['first_name']);
            self::set('provisional-operator-lname', $operatorInfo['last_name']);

            // We need a password, but the operator doesn't have one. Set one up!
            if(!$operatorInfo['password']) {
                self::set('account-create', true);
                return false;
            }

            // We need a password! But if it's null, we likely haven't asked for it.
            if(!$password) {
                // Refresh the page, this time with a password input field and pass the operatorId
                return false;
            }

            // Otherwise, Authenticate username and password
            if(password_verify($password, $operatorInfo['password'])) {
                // Update the user's password with a new salty hash (salt auto-generated)
                $newHashedPassword = password_hash($password, PASSWORD_BCRYPT);
                // Update the user's password with the new hashed password and salt
                $cainDB->query("UPDATE `users` SET `password` = :password WHERE `operator_id` = :operatorId;", [':password' => $newHashedPassword, ':operatorId' => $operatorId]);
                return true;
            }

            // Unable to authenticate the operator
            return false;
        }

        // The operator doesn't exist locally. Check externally.
        if(limsRequest(["operatorId" => $operatorId], 40, 42)['operatorResult']) {
            // If the operator exists externally, create a clinician and log them in.
            $cainDB->query("INSERT INTO `users` (`operator_id`, `user_type`) VALUES (:operatorId, 1);", [':operatorId' => $operatorId]);

            // Let the session know that this is a new operator
            self::set('new-operator', true);

            // Recur the function with the new operatorId
            return self::authenticate($operatorId, null);
        }

        return false;
    }

    // Login user
    public static function login($operatorId) {
        global $cainDB;

        // Set authenticated user ID in session after destroying what's already there
        self::remove('password-required');
        self::remove('provisional-operator-lname');
        self::remove('provisional-operator-fname');
        self::remove('provisional-operator');

        // Store a hashed version of the operator ID and use this for session auth
        $userId = md5(uniqid(mt_rand(), true));
        $cainDB->query("UPDATE `users` SET `user_id` = :userId WHERE `operator_id` = :operatorId", [':userId' => $userId, ':operatorId' => $operatorId]);

        self::set('user-id', $userId);
        return true;
    }

    // Logout user
    public static function logout() {
        self::destroy();
    }

    // Check if user is logged in
    public static function isLoggedIn() {
        global $cainDB;

        // Whilst we're checking here, we may as well update the active timestamp
        if(self::exists('user-id')) {
            // Update last active
            try {
                $cainDB->query("UPDATE `users` SET `last_active` = :timestamp WHERE `user_id` = :userId;", [":timestamp" => time(), ":userId" => self::get('user-id')]);
            } catch(PDOException $exception) {
                self::logout();
            }
            return true;
        }
        return false;
    }
}

// Start session
$session = new Session;
