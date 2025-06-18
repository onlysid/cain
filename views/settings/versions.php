<?php // Versions of various apps
require_once BASE_DIR . "/utils/Software.php";

// Get LIMS version
$limsVersion = $settings['app_version'];

?>
<div class="flex flex-wrap gap-2 w-full justify-center items-stretch">
    <div class="grow p-6 basis-1/4 min-w-60 flex flex-col gap-3 rounded-xl bg-white shadow-xl shadow-dark/50">
        <h4>General DMS Information</h4>
        <ul class="ml-4">
            <li class="list-disc text-dark">PhP Version: <?= phpversion();?></li>
            <li class="list-disc text-dark">MariaDB: <?= $cainDB->conn->query("select version();")->fetchColumn();?></li>
        </ul>
    </div>
    <?php foreach($software as $softwareItem) : ?>
        <div class="grow p-6 basis-1/4 min-w-60 flex flex-col gap-3 rounded-xl bg-white shadow-xl shadow-dark/50">
            <h4><?= $softwareItem->getTitle();?></h4>
            <?php if($softwareItem->getVersion()) : ?>
                <ul class="ml-4">
                    <?php foreach($softwareItem->getVersion() as $version) : ?>
                        <li class="list-disc text-dark"><?= $version['value'];?></li>
                    <?php endforeach;?>
                </ul>
            <?php else : ?>
                <p>No item exists in database.</p>
            <?php endif;?>
            <?php if($softwareItem->changelogLink || $softwareItem->scripts) : ?>
                <div class="flex items-center justify-center flex-wrap gap-1 w-full">
                    <?php // Load button to run PhP scripts if they exist
                    if($softwareItem->scripts) : ?>
                        <?php foreach($softwareItem->scripts as $scriptTitle => $scriptAction) : ?>
                            <form action="/process" method="post" class="grow w-full">
                                <input type="hidden" name="action" value="<?= $scriptAction;?>">
                                <button type="submit" class="btn-small !py-1 !px-5 w-full"><?= $scriptTitle;?></button>
                            </form>
                        <?php endforeach; ?>
                    <?php endif;
                    if($softwareItem->changelogLink) : ?>
                        <a href="<?= $softwareItem->changelogLink;?>" class="btn-small !py-1 !px-5 grow text-center">Changelog</a>
                    <?php endif;?>
                </div>
            <?php endif;?>
        </div>
    <?php endforeach;?>
    <!-- App software version -->
    <div class="grow p-6 basis-1/4 min-w-60 flex flex-col gap-3 rounded-xl bg-white shadow-xl shadow-dark/50">
        <h4>LIMS Middleware</h4>
        <ul class="ml-4">
            <li class="list-disc text-dark"><?= $limsVersion;?></li>
        </ul>
    </div>
</div>

