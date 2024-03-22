<?php // Function to handle routing

function route($routes, $apiRoutes) {

    // Get the current page
    $currentPage = $_SERVER['REQUEST_URI'];
    $path = parse_url($currentPage, PHP_URL_PATH);
    
    // If we are not logged in, we need to check if we are allowed to be here
    if (!isset($_SESSION['user_id']) && $currentPage !== '/login') {
        if(array_key_exists($path, $routes)) {
            if($routes[$path]->accessLevel !== GUEST) {
                // Redirect to login page
                header("Location: /login");
                exit();
            }
        }
    }
    
    // Check if the requested path matches any API route
    if (array_key_exists($path, $apiRoutes)) {
        include BASE_DIR . '/includes/api.php';
        return;
    }
    
    // Handle general routing logic
    if (array_key_exists($path, $routes)) {
        // Check if the requested path matches any non-API route
        $route = $routes[$path];
    } elseif (http_response_code() === 403) {
        // Check if the HTTP response code is 403 (Forbidden)
        $route = new PageRoute('views/403.php', '403: Forbidden', false, false);
    } else {
        // Handle 404 error
        http_response_code(400);
        $route = new PageRoute('views/404.php', '404: Not Found', false, false);
    }

    include BASE_DIR . '/templates/base.php';
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
    '/login' => new PageRoute('auth/login.php', 'Login', false), // Login page
    '/blocks' => new PageRoute('views/objects.php', 'Demo', true, GUEST), // Demo blocks
    '/about' => new PageRoute('views/about.php', 'Demo', true, GUEST), // About this website
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
route($routes, $apiRoutes);

?>