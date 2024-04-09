<?php // General Settings

// Hospital Info Settings Subset
$networkInfoKeys = ['selected_protocol', 'dms_ip', 'dms_port', 'lims_ip', 'lims_port', 'lims_server_name', 'patient_id'];
$networkInfo = array_intersect_key($settings, array_flip($networkInfoKeys));

?>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>Not too sure what this area is currently meant to do</p>
</section>

<form action="/process" method="POST">
    <input type="hidden" name="action" value="logging-settings">
    <input type="hidden" name="return-path" value="<?= $currentURL;?>">
    <h3 class="text-dark mt-4 w-full text-center rounded-xl px-4 py-2 bg-blue-200/75 shadow-lg">System Settings</h3>

    <div class="form-fields">
        <div class="field">
            <label for="protocol">Protocol</label>
            <div class="input-wrapper select-wrapper">
                <select required name="protocol" id="protocol">
                    <option <?= $networkInfo['selected_protocol'] == "Cain" ? "selected" : "";?> value="0">Cain</option>
                    <option <?= $networkInfo['selected_protocol'] == "HL7" ? "selected" : "";?> value="1">HL7</option>
                </select>
            </div>
        </div>
        <div class="field">
            <label for="dmsIP">DMS/Cain IP Address</label>
            <div class="input-wrapper">
                <input required id="dmsIP" type="text" name="dmsIP" value="<?= $networkInfo['dms_ip'];?>">
            </div>
        </div>
        <div class="field">
            <label for="dmsPort">DMS/Cain Port</label>
            <div class="input-wrapper">
                <input required id="dmsPort" type="number" name="dmsPort" value="<?= $networkInfo['dms_port'];?>">
            </div>
        </div>
    </div>
    <div id="limsOptions" class="form-fields <?= $networkInfo['selected_protocol'] == "HL7" ? "active" : "";?>">
        <h3 class="text-dark mt-4 w-full text-center rounded-xl px-4 py-2 bg-blue-200/75 shadow-lg">LIMS Settings</h3>
        <div class="form-fields">
            <div class="field">
                <label for="limsIP">LIMS IP Address</label>
                <div class="input-wrapper">
                    <input required id="limsIP" type="text" name="limsIP" value="<?= $networkInfo['lims_ip'];?>">
                </div>
            </div>
            <div class="field">
                <label for="limsPort">LIMS Port</label>
                <div class="input-wrapper">
                    <input required id="limsPort" type="number" name="limsPort" value="<?= $networkInfo['lims_port'];?>">
                </div>
            </div>
            <div class="field">
                <label for="limsServerName">LIMS Servername</label>
                <div class="input-wrapper">
                    <input required id="limsServerName" type="text" name="limsServerName" value="<?= $networkInfo['lims_server_name'];?>">
                </div>
            </div>
        </div>
        <div class="form-fields">
            <label for="patientId" class="field !flex-row toggle-field !px-6 py-2 rounded-full bg-white shadow-md">
                <div class="flex flex-col w-full">
                    <div class="shrink">Positive Patient ID</div>
                    <div class="description !text-xs text-grey mr-4">Are we able to download patient data from LIMS?</div>
                </div>
                <div class="checkbox-wrapper">
                    <input class="tgl" name="patientId" id="patientId" type="checkbox" <?= $networkInfo['patient_id'] ? "checked" : "";?>>
                    <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="patientId"><span></span></label>
                </div>
            </label>
        </div>
    </div>
    <button class="btn smaller-btn" type="submit">Save Settings</button>
</form>