<?php // Updating cover

// Checks if we are updating the database and loads a loading screen until the update completes.
include BASE_DIR . '/includes/version.php';

// Check for Cain updates
$areWeUpdating = false;
$areWeUpdating = checkForUpdates($version);

// If we are updating, load a cover screen with JS.
if($areWeUpdating === true || $areWeUpdating === 100) : ?>
    <script src="/js/updateCheck.js" />
<?php // Otherwise, we have some kind of corruption. Prompt the user to retry.
elseif($areWeUpdating === 200) :
    Session::setWarning("db-error");
endif; ?>