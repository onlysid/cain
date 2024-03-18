<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $route->title;?></title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="css/output.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-primary flex flex-col font-roboto items-center fixed justify-center h-screen w-full">
    <div class="flex items-stretch overflow-hidden grow">
        <?php if($route->showMenu) : ?>
            <div class="shrink-0 py-8 -my-2 md:block relative hidden -mr-4 rounded-xl">
                <div class="h-full w-full pl-6 pt-2 rounded-xl pr-12 overflow-y-scroll overflow-x-hidden">
                    <?php include 'templates/menu.php';?>
                </div>
                <div class="absolute bottom-8 w-[calc(100%_+50px)] h-2.5 bg-gradient-to-b from-transparent to-primary"></div>
                <div class="absolute top-8 w-[calc(100%_+50px)] h-2.5 bg-gradient-to-t from-transparent to-primary"></div>
            </div>

            <div class="fixed left-0 top-0 z-50 bg-blue-900/40 backdrop-blur-xl md:hidden opacity-0 pointer-events-none w-full h-full flex flex-col px-16 py-20">
                <div id="mobMenuClose" class="cursor-pointer fixed top-6 right-6 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-auto fill-white transition-all duration-500 group-hover:scale-110" viewBox="0 0 384 512">
                        <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                    </svg>
                </div>
                <?php include 'templates/menu.php';?>
            </div>
        <?php endif;?>
    
        <div class="overflow-y-scroll z-10 md:-ml-4 p-4 mx-auto container">
            <?php include $route->view; ?>
        </div>
    </div>

    <?php if($route->showMenu) {
        include 'templates/footer.php';
    }?>
</body>
</html>
