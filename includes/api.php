<?php // Get the route of the path and set some headers so the webserver knows how to interpret the request.
$route = $apiRoutes[$path];

// There are a few routes in which we can GET:
$getAllowedAPIs = [
    'datafields',
    'cartridgeqccheck',
    'instrumentqccheck',
    'setting',
];

// If we aren't posting to this page, we aren't allowed to see it (except if it's listed above).
if ($_SERVER["REQUEST_METHOD"] != "POST" && !in_array(pathinfo($route['view'], PATHINFO_FILENAME), $getAllowedAPIs)) {
    // Redirect to the 403 error page
    header("HTTP/1.1 403 Forbidden");
    $route = new PageRoute('views/403.php', '403: Forbidden', false, false);
    include_once BASE_DIR . '/templates/base.php';
    exit; // Stop further execution
}

// The content is JSON
header('Content-Type: application/json; charset=UTF-8');

// Allow from all origins
header("Access-Control-Allow-Origin: *");

// Allow only POST requests
header("Access-Control-Allow-Methods: POST");

// Allow all the above headers and some more.
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get the data
$data = $_POST;
$raw = false;

// If we don't have post data, it's probably written raw...
if(!$data) {
    $raw = true;
    $data = json_decode(file_get_contents("php://input"), true);
}

// If we have verbose logging, add this
if($verboseLogging = $cainDB->select("SELECT `value` FROM settings WHERE `name` = 'verbose_logging';")['value'] == '1') {
    addLogEntry('API', "INFO: Received data at $path - " . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

// Include API-specific logic.
include $route['view'];
