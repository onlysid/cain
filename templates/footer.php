<?php // The footer menu bar 

// Get hospital information for the footer
$hospitalInfo = systemInfo();
$hospitalName = $officeName = "";

foreach ($hospitalInfo as $setting) {
    if ($setting['name'] === 'hospital_name') {
        $hospitalName = $setting['value'];
    }

    if ($setting['name'] === 'office_name') {
        $officeName = $setting['value'];
    }
}

// var_dump($_SESSION);
?>

<div id="footerWrapper">
    <div class="flex items-center justify-between gap-4 container mx-auto">

        <div class="flex items-center overflow-hidden md:justify-normal w-full justify-between">
            <div class="flex items-center">

                <a href="/settings" id="settingsIcon" class="footer-icon hidden md:block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="!fill-white/50 rotate-90" viewBox="0 0 512 512">
                        <path d="M0 416c0 17.7 14.3 32 32 32l54.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 448c17.7 0 32-14.3 32-32s-14.3-32-32-32l-246.7 0c-12.3-28.3-40.5-48-73.3-48s-61 19.7-73.3 48L32 384c-17.7 0-32 14.3-32 32zm128 0a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zM320 256a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm32-80c-32.8 0-61 19.7-73.3 48L32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l246.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48l54.7 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-54.7 0c-12.3-28.3-40.5-48-73.3-48zM192 128a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm73.3-64C253 35.7 224.8 16 192 16s-61 19.7-73.3 48L32 64C14.3 64 0 78.3 0 96s14.3 32 32 32l86.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 128c17.7 0 32-14.3 32-32s-14.3-32-32-32L265.3 64z"/>
                    </svg>
                </a>

                <div id="menuIcon" class="footer-icon md:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="!fill-white/50" viewBox="0 0 448 512">
                        <path d="M0 96C0 78.3 14.3 64 32 64H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H416c17.7 0 32 14.3 32 32z"/>
                    </svg>
                </div>

                <div id="userIcon" class="logout-trigger footer-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path d="M304 128a80 80 0 1 0 -160 0 80 80 0 1 0 160 0zM96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM49.3 464H398.7c-8.9-63.3-63.3-112-129-112H178.3c-65.7 0-120.1 48.7-129 112zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3z"/>
                    </svg>
                </div>
            </div>

            <div id="userInfo" class="px-3 logout-trigger cursor-pointer flex flex-col md:items-start justify-center py-1 overflow-hidden truncate">
                <?php if($currentUser) : ?>
                    <p class="font-bold text-white text-right xl:text-xl text-lg truncate overflow-hidden"><?= ucfirst($currentUser['first_name'][0] ?? "") . " " . ucfirst($currentUser['last_name'] ?? "");?></p>
                <?php endif;?>
                <p class="text-white font-medium text-base xl:text-lg truncate overflow-hidden"><?= $hospitalName;?> - <?= $officeName;?></p>
            </div>
        </div>

        <div class="md:flex items-center gap-4 hidden">
            <div class="bg-primary rounded-xl w-64 h-12 flex justify-center items-center">
                <p>Cain Medical</p>
            </div>
        </div>

    </div>

    <div id="logoutModal">
        <div class="notice gap-4 inline-flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-auto fill-white shrink-0" viewBox="0 0 512 512">
                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24V264c0 13.3-10.7 24-24 24s-24-10.7-24-24V152c0-13.3 10.7-24 24-24zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"/>
            </svg>
            <h3><?= $currentUser ? "Are you sure you want to log out?" : "Please log in to use Cain.";?></h3>
        </div>
        <div class="flex flex-col w-full gap-5 justify-center items-center max-w-[15rem]">
            <button id="logoutCancel" class="w-full btn simple-border-btn">Cancel</button>
            <form action="process" method="POST" class="w-full" id="logout">
                <input type="hidden" name="action" value="logout">
                <input type="hidden" name="return-path" value="<?= $currentURL;?>">
                <?php if($currentUser) : ?>
                    <button type="submit" class="w-full btn alt-border-btn">Log out</a>
                <?php else : ?>
                    <a href="/login" class="w-full btn alt-border-btn">Log in</a>
                <?php endif;?>
            </form>
        </div>
    </div>
</div>