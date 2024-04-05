<?php // Updating cover
include BASE_DIR . '/includes/version.php';

// Checks if we are updating the database and loads a loading screen until the update completes.

// Check for Cain updates
$areWeUpdating = false;
$areWeUpdating = checkForUpdates($version);

// If we are updating, load a cover screen with JS.
if($areWeUpdating === true || $areWeUpdating === 100) : ?>
    <script src="/js/updateCheck.js" />
<?php // Otherwise, we have some kind of corruption. Prompt the user to retry.
elseif($areWeUpdating === 200) : ?>
    <form action="process" method="POST" class="fixed bottom-0 z-50 bg-red-500 w-full flex justify-center py-5 px-4">
        <input type="hidden" name="action" value="reset-db-version">
        <input type="hidden" name="return-path" value="<?= $currentURL;?>">
        <p class="text-white text-center">Warning: Database may be corrupted. Please speak with an admin or <button type="submit" class="!text-blue-100 underline hover:!text-green-100">try safely resetting (click here).</button></p>
    </form>
<?php endif;?>