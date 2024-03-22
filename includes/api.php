<?php // Get the route of the path and set some headers so the webserver knows how to interpret the request.
$route = $apiRoutes[$path];

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

// If we don't have post data, it's probably written raw...
if(!$data) {
    $data = json_decode(file_get_contents("php://input"), true);
}

// Include API-specific logic.
include 'api-functions.php';
include $route['view'];