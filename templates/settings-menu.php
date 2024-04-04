<?php // Menu items 

// Include PageRoute class
require_once 'utils/MenuItem.php';

// Define all menu items
?>

<div id="settingsMenu" class="w-0 h-full bg-black sm:border-r-2 border-black sm:w-64 botder-solid pt-16 sm:pt-0 gap-0.5 flex flex-col overflow-y-scroll absolute sm:relative transition-all duration-500">
    <?php foreach($settingsRoutes as $settingsPage) : 
        $targetRoute = array_search($settingsPage, $settingsRoutes);
        if(($currentUser['user_type'] ?? 0) >= $settingsPage->accessLevel) : ?>
            <a class="menu-item whitespace-nowrap <?= $targetRoute === $currentPage ? 'active' : '';?>" href="<?= $targetRoute;?>"><?= $settingsPage->title;?></a>
        <?php endif;
    endforeach;?>    
</div>