<?php
// Include PageRoute class
require_once 'utils/PageRoute.php';

// Define routes using PageRoute objects
$routes = [
    '/' => new PageRoute('views/dashboard.php'), // Main dashboard
    '/users' => new PageRoute('views/users.php', 'Users'), // User configurations
    '/login' => new PageRoute('views/login.php', 'Login', false), // Login page
    '/blocks' => new PageRoute('views/objects.php'), // Demo blocks
];

// Dynamically populate $apiRoutes
$apiRoutes = [];
$apiDirectory = __DIR__ . '/api';
$apiFiles = scandir($apiDirectory);
foreach ($apiFiles as $file) {
    if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $route = '/'.pathinfo($file, PATHINFO_FILENAME);
        $apiRoutes[$route] = ['view' => 'api/' . $file];
    }
}

// Function to handle routing
function route($routes, $apiRoutes, $request_uri) {
    $path = parse_url($request_uri, PHP_URL_PATH);
    
    // Check if the requested path matches any non-API route
    if (array_key_exists($path, $routes)) {
        $route = $routes[$path];
        include 'templates/base.php';
        return;
    }

    // Check if the requested path matches any API route
    if (array_key_exists($path, $apiRoutes)) {
        $route = $apiRoutes[$path];
        header('Content-Type: application/json');
        include $route['view'];
        return;
    }

    // Handle 404 error for both non-API and API routes
    http_response_code(404);
    $route = new PageRoute('views/404.php', '404: Not Found');

    include 'templates/base.php';
}
// Handle the request
route($routes, $apiRoutes, $_SERVER['REQUEST_URI']);

?>
