<?php // General Settings

// Hospital Info Settings Subset
$hospitalInfoKeys = ['hospital_name', 'office_name', 'hospital_location', 'date_format'];
$hospitalInfo = array_intersect_key($settings, array_flip($hospitalInfoKeys));

// Accepted Date Formats
$dateFormats = ["d M Y", "d F Y", "d/m/Y"];
?>

<form action="/process" method="POST">
    <input type="hidden" name="action" value="general-settings">
    <input type="hidden" name="return-path" value="<?= $currentURL;?>">
    <h3 class="text-dark mt-4 w-full text-center rounded-xl px-4 py-2 bg-blue-200/75 shadow-lg">Hospital Information</h3>
    <div class="form-fields">
        <div class="field">
            <label for="hospitalName">Hospital Name</label>
            <div class="input-wrapper">
                <input required id="hospitalName" type="text" name="hospitalName" value="<?= $hospitalInfo['hospital_name'];?>">
            </div>
        </div>
        <div class="field">
            <label for="officeName">Office Name</label>
            <div class="input-wrapper">
                <input required id="officeName" type="text" name="officeName" value="<?= $hospitalInfo['office_name'];?>">
            </div>
        </div>
    </div>
    <div class="form-fields">
        <div class="field">
            <label for="hospitalLocation">Hospital Location</label>
            <div class="input-wrapper">
                <input required id="hospitalLocation" type="text" name="hospitalLocation" value="<?= $hospitalInfo['hospital_location'];?>">
            </div>
        </div>
    </div>
    <h3 class="text-dark mt-4 w-full text-center rounded-xl px-4 py-2 bg-blue-200/75 shadow-lg">Other Settings</h3>
    <div class="form-fields">
        <div class="field">
            <label for="dateFormat">Date Format</label>
            <div class="input-wrapper select-wrapper">
                <select required name="dateFormat" id="dateFormat">
                    <?php foreach($dateFormats as $dateFormat) : ?>
                        <option <?= ($dateFormat == $hospitalInfo['date_format']) ? 'selected' : '';?> value="<?= $dateFormat;?>"><?= date($dateFormat);?></option>
                    <?php endforeach;?>
                </select>
            </div>
        </div>
    </div>
    <button class="btn smaller-btn" type="submit">Save Settings</button>
</form>