<?php
class Session {
    // Set session expiry time (in seconds)
    private static $session_expiry = 1800; // 30 minutes

    // Start session
    public static function start() {
        session_start();

        // Regenerate session ID if it's time to do so
        if (!isset($_SESSION['last_regenerated']) || time() - $_SESSION['last_regenerated'] > 60 * 30) { // Regenerate every 30 minutes
            // Regenerate session ID and destroy the old session
            session_regenerate_id(true);

            // Update the last regenerated timestamp
            $_SESSION['last_regenerated'] = time();
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
        session_destroy();
    }

    // Check session expiry
    private static function checkExpiry() {
        if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > self::$session_expiry) {
            // Session expired, destroy it
            self::destroy();
        } else {
            // Update last activity timestamp
            $_SESSION['last_activity'] = time();
        }
    }

    // Store session data securely
    public static function secureSet($key, $value) {
        // Encrypt session data before storing
        $encrypted_value = encrypt($value); // Implement your encryption function here
        self::set($key, $encrypted_value);
    }

    // Retrieve session data securely
    public static function secureGet($key) {
        // Decrypt session data after retrieval
        $encrypted_value = self::get($key);
        if ($encrypted_value !== null) {
            return decrypt($encrypted_value); // Implement your decryption function here
        }
        return null;
    }

    // Encrypt data using OpenSSL AES-256-CBC algorithm
    private static function encrypt($data) {
        $iv_length = openssl_cipher_iv_length('AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($iv_length);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', self::$encryption_key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    // Decrypt data using OpenSSL AES-256-CBC algorithm
    private static function decrypt($data) {
        $data = base64_decode($data);
        $iv_length = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($data, 0, $iv_length);
        $encrypted_data = substr($data, $iv_length);
        return openssl_decrypt($encrypted_data, 'AES-256-CBC', self::$encryption_key, 0, $iv);
    }
}

// Start session
Session::start();
?>
