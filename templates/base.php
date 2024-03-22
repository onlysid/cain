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
    <main>
        <?php if($showMenu) : ?>
            <div id="menuContentBind" class="flex items-stretch overflow-hidden grow w-full">
                <div id="desktopMenuWrapper">
                    <div id="desktopMenu">
                        <?php include 'templates/menu.php';?>
                    </div>
    
                    <div class="menu-brush bottom-8 bg-gradient-to-b"></div>
                    <div class="menu-brush top-8 bg-gradient-to-t"></div>
                </div>
    
                <div id="mobileMenu">
                    <div id="mobMenuClose" class="cursor-pointer fixed top-5 right-5 group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-auto fill-white transition-all duration-500 group-hover:scale-110" viewBox="0 0 384 512">
                            <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                        </svg>
                    </div>
                    <?php include 'templates/menu.php';?>
                </div>
            <?php endif;?>
        
            <div id="contentOuterWrapper" class="<?= $showMenu ? 'grow ' : '';?>">
                <div id="contentWrapper">
                    <?php include $route->view; ?>
                </div>
            </div>
    
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