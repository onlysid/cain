<?php
include_once BASE_DIR . '/includes/process.php';

// Function to handle routing
function route($routes, $apiRoutes) {
    global $cainDB, $form, $settingsRoutes;

    $currentURL = htmlspecialchars(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $currentUser = userInfo();

    $settingsPage = false;
    $loginPage = false;

    $currentPage = strtok($_SERVER["REQUEST_URI"], '?');
    $path = parse_url($currentPage, PHP_URL_PATH);

    // If not logged in, check access
    if ((!Session::isLoggedIn()) && $currentPage !== '/login') {
        if(array_key_exists($path, $routes) || preg_match('#^/assay-modules/#', $path, $matches)) {
            if($routes[$path]->accessLevel !== GUEST) {
                header("Location: /login");
                exit();
            }
        }
    }

    // Special handling for /process:
    if ($path === '/process') {
        // Instead of including process.php again, call our helper.
        runProcess();
        return;
    }

    // API routing
    if (array_key_exists($path, $apiRoutes)) {
        include BASE_DIR . '/includes/api.php';
        return;
    }

    // Dynamic Assay Module Route Handling
    if(preg_match('#^/assay-modules/(\d+)$#', $path, $matches)) {
        // Handle individual Assay Module pages
        $id = $matches[1];
        $route = new PageRoute('views/assay-module.php', 'Assay Module #' . $id);
    } elseif(preg_match('#^/assay-modules/edit-qc$#', $path) && isset($_GET['qc'])) {
        // Handle individual QC Test pages
        $qcId = $_GET['qc'];
        $route = new PageRoute('views/edit-instrument-qc.php', 'Edit QC Record #' . $qcId);
    } elseif(preg_match('#^/assay-modules/new-qc$#', $path) && isset($_GET['instrument'])) {
        // Handle creating a new QC Test
        $instrument = $_GET['instrument'];
        $type = $_GET['type'] ?? null;
        $route = new PageRoute('views/new-instrument-qc.php', 'Create New QC Record');
    } elseif(preg_match('#^/assay-modules/qc/(\w+)$#', $path, $matches)) {
        // Handle viewing a list of all QC tests for a given instrument
        $instrument = $matches[1];
        $route = new PageRoute('views/instrument-qc-list.php', 'QC Records for Instrument ' . $instrument);
    } else {
        // General Routing
        if(array_key_exists($path, $routes)) {
            if(array_key_exists($path, $settingsRoutes)) {
                $settingsPage = true;
            }

            $requestedRoute = $routes[$path];
            if($requestedRoute->accessLevel > ($currentUser['user_type'] ?? 0)) {
                $route = new PageRoute('views/403.php', '403: Forbidden', false, false, false);
            } else {
                $route = $requestedRoute;
            }
        } elseif(http_response_code() === 403) {
            $route = new PageRoute('views/403.php', '403: Forbidden', false, false, false);
        } else {
            http_response_code(400);
            $route = new PageRoute('views/404.php', '404: Not Found', false, false, false);
        }
    }


    include BASE_DIR . '/templates/base.php';
}


// Include PageRoute class
require_once 'utils/PageRoute.php';

// Define routes using PageRoute objects
$routes = [
    '/' => new PageRoute('views/dashboard.php', 'All Results'), // Main dashboard
    '/assay-modules' => new PageRoute('views/assay-modules.php', 'Assay Modules'), // Assay Modules List
    '/lots' => new PageRoute('views/lots.php', 'Lots'), // Lots Management
    '/qc-results' => new PageRoute('views/lots-qc-results.php', 'Lots QC Results'), // Lots QC results
    '/login' => new PageRoute('auth/login.php', 'Login', false, GUEST, false), // Login page
    '/blocks' => new PageRoute('views/objects.php', 'Demo', true), // Demo blocks
    '/changelog' => new PageRoute('views/changelog.php', 'Changelog', true), // Changelog for version updates
    '/backup' => new PageRoute('views/backup.php', 'Backup/Delete', true, ADMINISTRATIVE_CLINICIAN), // Backup results
    '/about' => new PageRoute('views/about.php', 'About', true), // About this website
];

// Define "settings" routes
$settingsRoutes = [
    '/settings' => new PageRoute('views/settings/account.php', 'Account Settings', false),
    '/settings/general' => new PageRoute('views/settings/general.php', 'General', false, ADMINISTRATIVE_CLINICIAN),
    '/settings/fields' => new PageRoute('views/settings/fields.php', 'Field Selection', false, ADMINISTRATIVE_CLINICIAN),
    '/settings/qc-types' => new PageRoute('views/settings/qc-types.php', 'Assay QC Types', false, ADMINISTRATIVE_CLINICIAN),
    '/settings/qc' => new PageRoute('views/settings/qc.php', 'Lot QC Policy', false, ADMINISTRATIVE_CLINICIAN),
    '/settings/users-list' => new PageRoute('views/settings/users-list.php', 'Users', false, ADMINISTRATIVE_CLINICIAN), // User config
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
    if($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        // Add the filename to the apiRoutes array.
        $route = '/' . pathinfo($file, PATHINFO_FILENAME);
        $apiRoutes[$route] = ['view' => 'api/' . $file];
        $route = '/' . ucfirst(pathinfo($file, PATHINFO_FILENAME));
        $apiRoutes[$route] = ['view' => 'api/' . $file];
    }
}

// Handle the request
try {
    route($routes, $apiRoutes);
} catch (Exception $e) {
    // Log the original error.
    $err = $e->getMessage();
    addLogEntry('system', "ERROR: $err");

    // Instantiate the DB class and connect.
    $conn = $cainDB->connect();

    // Use the helper to check for and create required tables.
    if (checkAndSetupRequiredTables($conn)) {
        // If tables were missing and have now been created, retry the operation.
        route($routes, $apiRoutes);
    } else {
        // If the tables exist, rethrow the original exception.
        throw $e;
    }
}
?>
