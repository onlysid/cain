<?php // General Settings

// Hospital Info Settings Subset
$hospitalInfoKeys = ['hospital_name', 'office_name', 'hospital_location', 'date_format', 'visible_ct'];
$hospitalInfo = array_intersect_key($settings, array_flip($hospitalInfoKeys));

// Accepted Date Formats
$dateFormats = ["d M Y", "d F Y", "d/m/Y"];
?>

<form action="/process" method="POST">
    <?php if($form->getErrors()) : ?>
        <ul class="form-errors">
            <?php foreach($form->getErrors() as $errorKey => $error) : ?>
                <li><?= $error;?></li>
            <?php endforeach;?>
        </ul>
    <?php endif;?>
    <input type="hidden" name="action" value="general-settings">
    <input type="hidden" name="return-path" value="<?= $currentURL;?>">
    <h3 class="text-dark mt-4 w-full text-center rounded-xl px-4 py-2 bg-fuchsia-200/75 shadow-lg">Hospital Information</h3>
    <div class="form-fields">
        <div class="field">
            <label for="hospitalName">Hospital Name</label>
            <div class="input-wrapper <?= $form->getError('hospitalName') ? "error" : "";?>">
                <input required id="hospitalName" type="text" name="hospitalName" value="<?= $hospitalInfo['hospital_name'];?>">
            </div>
        </div>
        <div class="field">
            <label for="officeName">Office Name</label>
            <div class="input-wrapper <?= $form->getError('officeName') ? "error" : "";?>">
                <input required id="officeName" type="text" name="officeName" value="<?= $hospitalInfo['office_name'];?>">
            </div>
        </div>
    </div>
    <div class="form-fields">
        <div class="field">
            <label for="hospitalLocation">Hospital Location</label>
            <div class="input-wrapper <?= $form->getError('hospitalLocation') ? "error" : "";?>">
                <input required id="hospitalLocation" type="text" name="hospitalLocation" value="<?= $hospitalInfo['hospital_location'];?>">
            </div>
        </div>
    </div>
    <h3 class="text-dark mt-4 w-full text-center rounded-xl px-4 py-2 bg-fuchsia-200/75 shadow-lg">Other Settings</h3>
    <div class="form-fields">
        <div class="field">
            <label for="dateFormat">Date Format</label>
            <div class="input-wrapper <?= $form->getError('dateFormat') ? "error" : "";?> select-wrapper">
                <select required name="dateFormat" id="dateFormat">
                    <?php foreach($dateFormats as $dateFormat) : ?>
                        <option <?= ($dateFormat == $hospitalInfo['date_format']) ? 'selected' : '';?> value="<?= $dateFormat;?>"><?= date($dateFormat);?></option>
                    <?php endforeach;?>
                </select>
            </div>
        </div>
    </div>
    <div class="form-fields">
        <label for="ctVisible" class="field !flex-row toggle-field !px-6 py-2 rounded-full bg-white shadow-md">
            <div class="flex flex-col w-full">
                <div class="shrink">Show Additional Result Info</div>
                <div class="description !text-xs text-grey mr-4">Show the CT values and constituent results (where available).</div>
            </div>
            <div class="checkbox-wrapper">
                <input class="tgl" name="ctVisible" id="ctVisible" type="checkbox" <?= $hospitalInfo['visible_ct'] ? "checked" : "";?>>
                <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="ctVisible"><span></span></label>
            </div>
        </label>
    </div>
    <button class="btn smaller-btn trigger-loading" type="submit">Save Settings</button>
</form>