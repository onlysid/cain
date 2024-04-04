<?php // General Settings

// Field Items
require_once 'utils/DataField.php';

// Get the bitmaps so we can use them to display the current DB values
$behaviourFields = getSettingsBitmap(count($dataFields), 3, $fieldInfo['field_behaviour']);
$visibilityFields = getSettingsBitmap(count($dataFields), 2, $fieldInfo['field_visibility']);

// Page setup ?>

<form action="/process" method="POST">
    <input type="hidden" name="action" value="field-settings">
    <input type="hidden" name="return-path" value="<?= $currentURL;?>">
    <div class="flex flex-col gap-2 items-center justify-center form-fields rounded-none">
        <table class="w-full text-sm lg:text-base text-left rtl:text-right">
            <thead class="text-dark border-light-grey border bg-blue-200">
                <th class="text-start lg:px-6 sm:px-4 px-2 py-3">Field</th>
                <th class="text-start lg:px-6 sm:px-4 px-2 py-3">Behaviour</th>
                <th class="text-start lg:px-6 sm:px-4 px-2 py-3">Visibility</th>
            </thead>
            <tbody class="bg-gray-50">
                <?php foreach($dataFields as $index => $dataField) : ?>
                    <tr class="border-light-grey border">
                        <td class="lg:px-6 sm:px-4 px-2 py-2"><?= $dataField->name;?></td>
                        <td class="lg:px-6 sm:px-4 px-2 py-2">
                            <div class="input-wrapper <?= $dataField->behaviourLock ? "disabled" : "";?>">
                                <select name="fieldBehaviour<?= $index;?>" id="fieldBehaviour<?= $index;?>">
                                    <option value="0" <?= $behaviourFields[$index] == 0 ? "selected" : "";?> <?= $dataField->behaviourLock ? "disabled" : "";?>>Hidden</option>
                                    <option value="1" <?= $behaviourFields[$index] == 1 ? "selected" : "";?> <?= $dataField->behaviourLock ? "disabled" : "";?>>Visible</option>
                                    <option value="2" <?= $behaviourFields[$index] == 2 ? "selected" : "";?> <?= $dataField->behaviourLock ? "disabled" : "";?>>Mandatory</option>
                                    <option value="2" <?= $dataField->behaviourLock ? "selected" : "disabled";?>>Automatic</option>
                                </select>
                            </div>
                        </td>
                        <td class="lg:px-6 sm:px-4 px-2 py-2">
                            <div class="checkbox-wrapper">
                                <input class="tgl" name="fieldVisibility<?= $index;?>" id="fieldVisibility<?= $index;?>" type="checkbox" <?= $dataField->visibilityLock ? "disabled" : "";?> <?= $dataField->visibilityLock || $visibilityFields[$index] ? "checked" : "";?>>
                                <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="fieldVisibility<?= $index;?>"><span></span></label>
                            </div>
                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
    <button class="btn smaller-btn" type="submit">Save Settings</button>
</form>