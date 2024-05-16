<?php // Get the route of the path and set some headers so the webserver knows how to interpret the request.
$route = $apiRoutes[$path];

// If we aren't posting to this page, we aren't allowed to see it.
if ($_SERVER["REQUEST_METHOD"] != "POST") {
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

var_dump($data);

// If we don't have post data, it's probably written raw...
if(!$data) {
    $raw = true;
    $data = json_decode(file_get_contents("php://input"), true);
}

// Include API-specific logic.
include $route['view'];
