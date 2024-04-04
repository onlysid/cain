<?php // Changelog
require_once BASE_DIR . "/includes/parsedown.php";?>

<div id="changelog" class="container-fluid">
    <?php $contents = file_get_contents(BASE_DIR . "/CHANGELOG.md");
    $parsedown = new Parsedown();
    echo $parsedown->text($contents);?>
</div>