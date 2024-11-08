<?php // Menu items

// Include PageRoute class
require_once 'utils/MenuItem.php';

// Define all menu items
$menuItems = [
    new MenuItem($routes['/'], null, 'list-icon'),
    new MenuItem($routes['/assay-modules']),
    new MenuItem($routes['/qc-policy']),
    new MenuItem($routes['/lots']),
    new MenuItem($routes['/users'], 'User Login Control'),
    new MenuItem($routes['/backup']),
    new MenuItem($routes['/about']),
    new MenuItem($routes['/settings'], "Settings"),
    // Add more menu items above and sort as you like
];?>

<div id="menu">
    <?php foreach($menuItems as $menuItem) : ?>
        <?php $targetRoute = array_search($menuItem->pageRoute, $routes);

        // Check if the current route matches the target route or any children. First, we assume it does not.
        $linkMatch = false;

        // Now, check various scenarios
        if($targetRoute !== "/") {
            if(str_contains($currentPage, $targetRoute)) {
                $linkMatch = true;
            } else {
                $linkMatch = false;
            }
        } else {
            if($currentPage === $targetRoute && $currentPage === "/") {
                $linkMatch = true;
            }
        }

        if(($currentUser['user_type'] ?? 0) >= $menuItem->pageRoute->accessLevel) : ?>
            <a href="<?= $targetRoute;?>" class="btn w-full <?= $menuItem->icon ? "has-icon" : "";?> <?= $linkMatch ? 'active' : '';?>">
                <?php if($menuItem->icon) {
                    include('assets/' . $menuItem->icon . '.svg');
                };?>
                <?= $menuItem->title ?? $menuItem->pageRoute->title;?>
            </a>
        <?php endif;?>
    <?php endforeach;?>
    <!-- Firefox workaround for getting a bit of padding at the bottom of the menu... -->
    <a href="#" class="h-0 invisible">#</a>
</div>