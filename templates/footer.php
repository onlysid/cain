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

$userTypeTitle = "Clinician";
if($currentUser['user_type'] == ADMINISTRATIVE_CLINICIAN) {
    $userTypeTitle = "Admin";
} elseif($currentUser['user_type'] == SERVICE_ENGINEER) {
    $userTypeTitle = "Service Engineer";
}
?>

<div id="footerWrapper">
    <div class="flex items-center justify-between gap-4 container mx-auto">

        <div class="flex items-center md:justify-normal w-full justify-between">
            <div class="flex items-center">

                <?php if($showMenu) : ?>
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
                <?php endif;?>

                <div id="userIcon" class="logout-trigger footer-icon tooltip tooltip-alt" title="<?= $userTypeTitle;?>">
                    <?php if($currentUser['user_type'] == CLINICIAN) : ?>
                        <svg class="tooltip" title="Service Engineer" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                            <path d="M304 128a80 80 0 1 0 -160 0 80 80 0 1 0 160 0zM96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM49.3 464H398.7c-8.9-63.3-63.3-112-129-112H178.3c-65.7 0-120.1 48.7-129 112zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3z"/>
                        </svg>
                    <?php elseif($currentUser['user_type'] == ADMINISTRATIVE_CLINICIAN) : ?>
                        <svg class="tooltip" title="Service Engineer" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                            <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c1.8 0 3.5-.2 5.3-.5c-76.3-55.1-99.8-141-103.1-200.2c-16.1-4.8-33.1-7.3-50.7-7.3H178.3zm308.8-78.3l-120 48C358 277.4 352 286.2 352 296c0 63.3 25.9 168.8 134.8 214.2c5.9 2.5 12.6 2.5 18.5 0C614.1 464.8 640 359.3 640 296c0-9.8-6-18.6-15.1-22.3l-120-48c-5.7-2.3-12.1-2.3-17.8 0zM591.4 312c-3.9 50.7-27.2 116.7-95.4 149.7V273.8L591.4 312z"/>
                        </svg>
                    <?php elseif($currentUser['user_type'] == SERVICE_ENGINEER) : ?>
                        <svg class="tooltip" title="Service Engineer" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                            <path d="M370.7 96.1C346.1 39.5 289.7 0 224 0S101.9 39.5 77.3 96.1C60.9 97.5 48 111.2 48 128v64c0 16.8 12.9 30.5 29.3 31.9C101.9 280.5 158.3 320 224 320s122.1-39.5 146.7-96.1c16.4-1.4 29.3-15.1 29.3-31.9V128c0-16.8-12.9-30.5-29.3-31.9zM336 144v16c0 53-43 96-96 96H208c-53 0-96-43-96-96V144c0-26.5 21.5-48 48-48H288c26.5 0 48 21.5 48 48zM189.3 162.7l-6-21.2c-.9-3.3-3.9-5.5-7.3-5.5s-6.4 2.2-7.3 5.5l-6 21.2-21.2 6c-3.3 .9-5.5 3.9-5.5 7.3s2.2 6.4 5.5 7.3l21.2 6 6 21.2c.9 3.3 3.9 5.5 7.3 5.5s6.4-2.2 7.3-5.5l6-21.2 21.2-6c3.3-.9 5.5-3.9 5.5-7.3s-2.2-6.4-5.5-7.3l-21.2-6zM112.7 316.5C46.7 342.6 0 407 0 482.3C0 498.7 13.3 512 29.7 512H128V448c0-17.7 14.3-32 32-32H288c17.7 0 32 14.3 32 32v64l98.3 0c16.4 0 29.7-13.3 29.7-29.7c0-75.3-46.7-139.7-112.7-165.8C303.9 338.8 265.5 352 224 352s-79.9-13.2-111.3-35.5zM176 448c-8.8 0-16 7.2-16 16v48h32V464c0-8.8-7.2-16-16-16zm96 32a16 16 0 1 0 0-32 16 16 0 1 0 0 32z"/>
                        </svg>
                    <?php endif;?>
                </div>
            </div>

            <div id="userInfo" class="logout-trigger px-3 cursor-pointer flex flex-col md:items-start justify-center py-1 overflow-hidden truncate">
                <?php if($currentUser) : ?>
                    <p class="font-bold text-white text-right xl:text-xl text-lg truncate overflow-hidden"><?= $currentUser['first_name'] ? (ucfirst($currentUser['first_name'] ?? "") . " " . ucfirst($currentUser['last_name'] ?? "")) : ucfirst($currentUser['operator_id']);?></p>
                <?php endif;?>
                <p class="text-white font-medium text-base xl:text-lg truncate overflow-hidden"><?= $hospitalName;?> - <?= $officeName;?></p>
            </div>
        </div>

        <div class="md:flex items-center gap-4 hidden">
            <div class="bg-primary rounded-xl flex justify-center items-center gap-4 py-3 pl-6 pr-8">
                <p class="text-lg">LIMS:</p>
                <div id="limsStatus">
                    <div class="icon animated-icon"></div>
                    <div class="icon tooltip tooltip-alt" title="Connected"></div>
                </div>
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
            <a href="/settings" class="w-full btn simple-border-btn">Account Settings</a>
            <form action="/process" method="POST" class="w-full" id="logout">
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

<script type="module" src="/js/limsCheck.js"></script>