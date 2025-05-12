<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $route->title;?></title>
    <link href="/css/output.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/assets/favicon.png">
</head>

<?php // Some initialisation
$showMenu = $route->showMenu;
$showFooter = $route->showFooter;

// Check to see if we are using too many results
try {
    $resultsNum = checkResultCapacity();
} catch (Exception $e) {
    addLogEntry('system', "Could not check results capacity.");
}
?>

<body class="<?= $showMenu ? "show-menu" : "";?>">
    <div id="php2js" class="hidden" data-lims-timeout="<?= LIMS_TIMEOUT;?>"></div>
    <main>
        <?php // Some debugging goodies
        if($showMenu) : ?>
            <div id="menuContentBind" class="flex items-stretch overflow-hidden grow w-full">
                <div id="desktopMenuWrapper">
                    <div id="desktopMenu">
                        <?php include 'templates/menu.php';?>
                    </div>

                    <div class="menu-brush bottom-8 bg-gradient-to-b"></div>
                    <div class="menu-brush top-8 bg-gradient-to-t"></div>
                </div>

                <div id="mobileMenu">
                    <div class="fixed top-4 w-full flex justify-between mx-auto container">
                        <a href="/settings" id="mobileSettingsIcon" class="cursor-pointer group flex justify-center items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-auto fill-white transition-all duration-500 group-hover:scale-110" viewBox="0 0 512 512">
                                <path d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z"/>
                            </svg>
                        </a>

                        <div id="mobMenuClose" class="cursor-pointer group flex justify-center items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-auto fill-white transition-all duration-500 group-hover:scale-110" viewBox="0 0 384 512">
                                <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                            </svg>
                        </div>
                    </div>
                    <?php include 'templates/menu.php';?>
                </div>
            <?php endif;?>

            <?php // Don't bother with wrappers if we are on a settings page. Also load a new "template"
            if($settingsPage) : ?>
                <?php include "templates/settings.php";?>
            <?php else : ?>
                <div id="contentOuterWrapper" class="<?= $showMenu ? 'show-menu ' : '';?><?= isset($settings) && $settings ? " settings-wrapper" : "";?>">
                    <div id="contentWrapper" class="<?= isset($settings) && $settings ? "settings-wrapper" : "";?>">
                        <?php include $route->view;?>
                    </div>
                </div>
            <?php endif;?>

            <?php if($showMenu) : ?>
            </div>
        <?php endif;

        // Check for updates
        require_once BASE_DIR . '/admin/updating.php';

        // Show warnings
        $warnings = Session::getWarnings();

        if(Session::getWarnings()) : ?>
            <div id="messageBoard" class="bg-red-500 w-full <?= Session::isLoggedIn() ? '' : 'fixed-bottom';?>">
                <div class="py-2 mx-auto contatiner px-8 text-center text-white flex justify-center items-center">
                    <?php if(in_array('db-error', $warnings)) : ?>
                        <form action="/process" method="POST">
                            <input type="hidden" name="action" value="reset-db-version">
                            <input type="hidden" name="return-path" value="<?= $currentURL;?>">
                            <p class="text-white text-center font-bold">Warning: Database may be corrupted. Please speak with an admin or <button type="submit" class="!text-fuchsia-100 underline hover:!text-green-100">try safely resetting (click here).</button></p>
                        </form>
                    <?php elseif(in_array('max-results-reached', $warnings)) : ?>
                        <p class="text-white text-center font-bold">Warning: The system has saved <?= $resultsNum;?>/<?= MAX_RESULTS;?> results. Please backup and clear results to ensure continued safe operation of the database.</p>
                    <?php endif;?>
                </div>
            </div>
        <?php endif;

        // Show notices ?>
        <div id="notices">
            <?php foreach(Session::getNotices() as $notice) :
                $severity = "";
                switch($notice[1]) {
                    case 1:
                        $severity = "warn";
                        break;
                    case 2:
                        $severity = "alert";
                        break;
                    default:
                        break;
                }?>
                <div class="notice <?= $severity;?>">
                    <div class="notice-content-wrapper">
                        <div class="notice-content">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                                <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                            </svg>
                        </div>
                    </div>
                    <p><?= $notice[0];?></p>
                </div>
            <?php endforeach;?>
        </div>

        <?php if($showFooter) {
            include 'templates/footer.php';
        }?>

    </main>

</body>


<!-- Scripts etc -->
<script type="module" src="/js/app.js"></script>
<script type="module" src="/js/modals.js"></script>
</html>

<?php // Housekeeping
$form->clearErrors();
$form->clearValues();
Session::clearNotices();
Session::clearWarnings();?>
