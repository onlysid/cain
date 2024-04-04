<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $route->title;?></title>
    <link rel="stylesheet" href="/css/styles.css">
    <link href="/css/output.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/assets/favicon.png">
</head>

<?php // Some initialisation
$showMenu = $route->showMenu;
?>

<body>
    <div id="php2js" class="hidden" data-lims-timeout="<?= LIMS_TIMEOUT;?>"></div>
    <main>
        <?php // Some debugging goodies
        // print_r($form->getErrors());
        // print_r($_SESSION);
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
                                <path d="M0 416c0 17.7 14.3 32 32 32l54.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 448c17.7 0 32-14.3 32-32s-14.3-32-32-32l-246.7 0c-12.3-28.3-40.5-48-73.3-48s-61 19.7-73.3 48L32 384c-17.7 0-32 14.3-32 32zm128 0a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zM320 256a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm32-80c-32.8 0-61 19.7-73.3 48L32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l246.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48l54.7 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-54.7 0c-12.3-28.3-40.5-48-73.3-48zM192 128a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm73.3-64C253 35.7 224.8 16 192 16s-61 19.7-73.3 48L32 64C14.3 64 0 78.3 0 96s14.3 32 32 32l86.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 128c17.7 0 32-14.3 32-32s-14.3-32-32-32L265.3 64z"/>
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
                <div id="contentOuterWrapper" class="<?= $showMenu ? 'show-menu ' : '';?>">
                    <div id="contentWrapper">
                        <?php include $route->view;?>
                    </div>
                </div>
            <?php endif;?>
    
            <?php if($showMenu) : ?>
            </div>
        <?php endif;
    
        if($showMenu) {
            include 'templates/footer.php';
        }?>
    
        <?php require_once BASE_DIR . '/admin/updating.php';?>
    </main>

</body>


<!-- Scripts etc -->
<script type="module" src="/js/app.js"></script>
</html>

<?php // Housekeeping
$form->clearErrors();
Session::clearNotices();?>
