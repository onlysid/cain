<?php // QC Settings

?>

<form action="/process" method="POST">
    <input type="hidden" name="action" value="general-settings">
    <input type="hidden" name="return-path" value="<?= $currentURL;?>">
    <h3 class="text-dark mt-4 w-full text-center rounded-xl px-4 py-2 bg-blue-200/75 shadow-lg">QC Information</h3>
    <div class="form-fields">
        <div class="field">
            <label for="hospitalName">QC Setting 1</label>
            <div class="input-wrapper">
                <input required id="hospitalName" type="text" name="hospitalName" value="">
            </div>
        </div>
        <div class="field">
            <label for="officeName">QC Setting 2</label>
            <div class="input-wrapper">
                <input required id="officeName" type="text" name="officeName" value="">
            </div>
        </div>
    </div>
    <button class="btn smaller-btn" type="submit">Save Settings</button>
</form>