<?php // Config

/*
This is the first thing that is included in the app. Basic configuration settings are defined here.
*/

// Cookie Constants - these are the parameters to the setcookie function call
define("COOKIE_EXPIRE", 60 * 60 * 24 * 100);  // 100 days by default
define("COOKIE_PATH", "/");  // Avaible in whole domain
define("BASE_DIR", __DIR__ . "/..");

if(!file_exists(BASE_DIR . '/includes/db-config.php')) {
    @fopen(BASE_DIR . "/includes/db-config.php", "w");
    @copy(BASE_DIR . '/includes/db-config-sample.php', BASE_DIR . '/includes/db-config.php');
}

if(file_exists(BASE_DIR . '/includes/db-config.php')) {
    include_once BASE_DIR . '/includes/db-config.php';
} else {
    include_once BASE_DIR . '/includes/db-config-sample.php';
}

/*
 User Permission Levels - numbers associated with different levels of permission for
 different user types. If more levels are needed, be sure to run a relveant script update too. 
 */

define("SERVICE_ENGINEER", 3);
define("ADMINISTRATIVE_CLINICIAN", 2);
define("CLINICIAN", 1);
define("GUEST", 0);

// LIMS Timeout (how long until LIMS times out) (s)
define("LIMS_TIMEOUT", 30);