<?php // We will likely need these settings!
$hospitalInfo = systemInfo();

// Extract 'name' as keys and 'value' as values
$settings = array_column($hospitalInfo, 'value', 'name');

// Page Setup ?>

<div id="settings">
    <h1 class="text-white text-center md:-mt-3">Cain Settings</h1>
    <div class="bg-primary-dark rounded-2xl h-full shadow-2xl shadow-blue-200/50 flex relative justify-between overflow-hidden">
        <a id="settingsBackLink" href="/">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
            </svg>
            <span>Exit</span>
        </a>
        <div id="settingsMobileMenuIcon" class="cursor-pointer p-1 z-50 rounded-full sm:hidden absolute top-3.5 left-2 transition-all duration-500">
            <svg id="openSettingsMenu" class="h-8 px-4 fill-dark absolute scale-100 transition-all duration-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 512">
                <path d="M64 360a56 56 0 1 0 0 112 56 56 0 1 0 0-112zm0-160a56 56 0 1 0 0 112 56 56 0 1 0 0-112zM120 96A56 56 0 1 0 8 96a56 56 0 1 0 112 0z"/>
            </svg>
            <svg id="closeSettingsMenu" class="h-8 fill-white absolute scale-0 transition-all duration-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
            </svg>
        </div>
        <?php include_once "templates/settings-menu.php";?>
        <div id="settingWrapper" class="h-full grow mx-auto container py-4 sm:py-6 flex flex-col overflow-y-scroll overflow-x-hidden">
            <h1 class="text-center sm:text-start mx-12 sm:mx-0 sm:mr-12 mb-2.5 sm:mb-1"><?= $route->title;?></h1>    
            <div class="bg-gradient-to-r from-transparent via-grey/75 sm:from-grey/75 to-transparent w-full mb-3 pb-0.5 rounded-full"></div>
            <?php include_once $route->view;?>
        </div>
    </div>
</div>