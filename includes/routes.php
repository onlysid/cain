<?php // Define routes for CAIN and routing logic

// Function to handle routing
function route($routes, $apiRoutes, $request_uri) {
    // Get the current page
    $currentPage = $_SERVER['REQUEST_URI'];

    $path = parse_url($request_uri, PHP_URL_PATH);
    
    // Check if the requested path matches any non-API route
    if (array_key_exists($path, $routes)) {
        $route = $routes[$path];
        include 'templates/base.php';
        return;
    }

    // Check if the requested path matches any API route
    if (array_key_exists($path, $apiRoutes)) {
        include 'includes/api.php';
        return;
    }

    // Handle 404 error for both non-API and API routes
    http_response_code(404);
    $route = new PageRoute('views/404.php', '404: Not Found');

    include 'templates/base.php';
}

// Include PageRoute class
require_once 'utils/PageRoute.php';

// Define routes using PageRoute objects
$routes = [
    '/' => new PageRoute('views/dashboard.php', 'Dashboard'), // Main dashboard
    '/assay-modules' => new PageRoute('views/assay-modules.php', 'Assay Modules'), // Assay Modules List
    '/users' => new PageRoute('views/users.php', 'Users'), // User config
    '/result-config' => new PageRoute('views/result-config.php', 'Result Configuration'), // Result config
    '/qc-policy' => new PageRoute('views/qc-policy.php', 'Quality Control Policy'), // QC Policy config
    '/logs' => new PageRoute('views/logs.php', 'Logs'), // List of all logs
    '/network-settings' => new PageRoute('views/network-settings.php', 'Network Settings'), // Network Settings
    '/versions' => new PageRoute('views/versions.php', 'Versions'), // Network Settings
    '/login' => new PageRoute('views/login.php', 'Login', false), // Login page
    '/blocks' => new PageRoute('views/objects.php', 'Demo'), // Demo blocks
];

// Dynamically populate apiRoutes
$apiRoutes = [];
$apiFiles = scandir('api');
foreach ($apiFiles as $file) {
    if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        // Add the filename to tha apiRoutes array.
        $route = '/'.pathinfo($file, PATHINFO_FILENAME);
        $apiRoutes[$route] = ['view' => 'api/' . $file];
    }
}

// Handle the request
route($routes, $apiRoutes, $_SERVER['REQUEST_URI']);

?>