<?php // The root of the CAIN medical app.

// For debugging (remove in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/session.php';
require_once 'includes/form.php';
require_once 'includes/functions.php';
include 'includes/api-functions.php';

// Define your routes
require_once 'includes/routes.php';