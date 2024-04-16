<?php // Versions of various apps
require_once BASE_DIR . "/utils/Software.php";

?>
<div class="flex flex-wrap gap-2 w-full justify-center items-stretch">
    <?php foreach($software as $softwareItem) : ?>
        <div class="grow p-6 basis-1/4 min-w-60 flex flex-col gap-3 rounded-xl border-2 border-solid bg-white shadow-xl">
            <h2><?= $softwareItem->getTitle();?></h2>
            <?php if($softwareItem->getVersion()) : ?>
                <ul class="flex flex-col gap-1 items-center justify-center">
                    <?php foreach($softwareItem->getVersion() as $version) : ?>
                        <li class="rounded-xl px-4 py-2 w-full bg-dark text-center text-white"><?= $version['value'];?></li>
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
                            <form action="/process" method="post" class=grow w-full">
                                <input type="hidden" name="action" value="<?= $scriptAction;?>">
                                <button type="submit" class="btn-small w-full"><?= $scriptTitle;?></button>
                            </form>
                        <?php endforeach; ?>
                    <?php endif;
                    if($softwareItem->changelogLink) : ?>
                        <a href="<?= $softwareItem->changelogLink;?>" class="btn-small grow text-center">Changelog</a>
                    <?php endif;?>
                </div>
            <?php endif;?>
        </div>
    <?php endforeach;?>
</div>

