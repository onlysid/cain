<?php // Function to handle routing

function route($routes, $apiRoutes) {
    // Set all global parameters
    global $cainDB, $form, $settingsRoutes;

    // Get the current URL
    $currentURL = htmlspecialchars(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $currentUser = userInfo();
    
    // Are we a settings page?
    $settingsPage = false;

    // Get the current page
    $currentPage = strtok($_SERVER["REQUEST_URI"], '?');
    $path = parse_url($currentPage, PHP_URL_PATH);
    
    // If we are not logged in, we need to check if we are allowed to be here
    if ((!Session::isLoggedIn()) && $currentPage !== '/login') {
        if(array_key_exists($path, $routes)) {
            if($routes[$path]->accessLevel !== GUEST) {
                // Redirect to login page
                header("Location: /login");
                exit();
            }
        }
    }

    // Check for processes
    if($path === '/process') {
        include BASE_DIR . '/includes/process.php';
        return;
    }
    
    // Check if the requested path matches any API route
    if (array_key_exists($path, $apiRoutes)) {
        include BASE_DIR . '/includes/api.php';
        return;
    }
    
    // Handle general routing logic
    if (array_key_exists($path, $routes)) {
        // If we are in a settingsRoute then we are a settingsPage
        if(array_key_exists($path, $settingsRoutes)) {
            $settingsPage = true;
        }
        
        // The path matches a valid route, check that the current user access this path
        $requestedRoute = $routes[$path];
        if($requestedRoute->accessLevel > ($currentUser['user_type'] ?? 0)) {
            // We cannot show this resource to this user
            $route = new PageRoute('views/403.php', '403: Forbidden', false, false);
        } else {
            $route = $requestedRoute;
        }
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
    '/' => new PageRoute('views/dashboard.php', 'All Results'), // Main dashboard
    '/assay-modules' => new PageRoute('views/assay-modules.php', 'Assay Modules'), // Assay Modules List
    '/users' => new PageRoute('views/users.php', 'Users', true, ADMINISTRATIVE_CLINICIAN), // User config
    '/qc-policy' => new PageRoute('views/qc-policy.php', 'Quality Control Policy'), // QC Policy config
    '/lots' => new PageRoute('views/lots.php', 'Lots'), // Versions & About
    '/login' => new PageRoute('auth/login.php', 'Login', false, GUEST), // Login page
    '/blocks' => new PageRoute('views/objects.php', 'Demo', true), // Demo blocks
    '/changelog' => new PageRoute('views/changelog.php', 'Changelog', true), // About this website
    '/about' => new PageRoute('views/about.php', 'About', true),
];

// Define "settings" routes
$settingsRoutes = [
    '/settings' => new PageRoute('views/settings/general.php', 'General', false),
    '/settings/account' => new PageRoute('views/settings/account.php', 'Account Settings', false),
    '/settings/fields' => new PageRoute('views/settings/fields.php', 'Field Selection', false, ADMINISTRATIVE_CLINICIAN),
    '/settings/qc' => new PageRoute('views/settings/qc.php', 'QC Settings', false, ADMINISTRATIVE_CLINICIAN),
    '/settings/users' => new PageRoute('views/settings/users.php', 'User Settings', false, ADMINISTRATIVE_CLINICIAN),
    '/settings/network' => new PageRoute('views/settings/network.php', 'Network Settings', false, ADMINISTRATIVE_CLINICIAN),
    '/settings/versions' => new PageRoute('views/settings/versions.php', 'Versions', false, ADMINISTRATIVE_CLINICIAN),
    '/settings/logs' => new PageRoute('views/settings/logs.php', 'Logs', false, ADMINISTRATIVE_CLINICIAN),
];

// Add settings routes to general routes to combine logic
$routes += $settingsRoutes;

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