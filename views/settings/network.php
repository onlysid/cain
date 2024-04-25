<?php // General Settings

// Hospital Info Settings Subset
$networkInfoKeys = ['selected_protocol', 'cain_server_ip', 'cain_server_port', 'hl7_server_ip', 'hl7_server_port', 'hl7_server_dest', 'patient_id', 'test_mode', 'app_mode'];
$networkInfo = array_intersect_key($settings, array_flip($networkInfoKeys));
$macAddress = exec('ifconfig | grep -o -E \'([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}\'');
?>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>Set whether or not the system is working with HL7 or Cain protocols and view/change relevant IP addresses.</p>
</section>

<form action="/process" method="POST">
    <input type="hidden" name="action" value="network-settings">
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
            <label for="dmsMac">Hub Mac Address</label>
            <div class="input-wrapper disabled">
                <input disabled id="dmsMac" name="dmsMac" class="cursor-not-allowed" value="<?= $macAddress;?>">
            </div>
        </div>
        <div class="form-fields">
            <div class="field">
                <label for="dmsIp">DMS IP Address/Hostname</label>
                <div class="input-wrapper disabled">
                    <input disabled required class="cursor-not-allowed" id="dmsIp" type="text" name="dmsIp" value="<?= gethostbyname($_SERVER['SERVER_NAME']);?>">
                </div>
            </div>
            <div class="field">
                <label for="dmsPort">DMS Port</label>
                <div class="input-wrapper disabled">
                    <input disabled required class="cursor-not-allowed" id="dmsPort" type="number" name="dmsPort" value="<?= $_SERVER['SERVER_PORT'];?>">
                </div>
            </div>
        </div>
    </div>
    <div class="form-fields">
        <label for="testMode" class="field !flex-row toggle-field !px-6 py-2 rounded-full bg-white shadow-md">
            <div class="flex flex-col w-full">
                <div class="shrink">Test Mode</div>
                <div class="description !text-xs text-grey mr-4">Dummy data and processes for testing.</div>
            </div>
            <div class="checkbox-wrapper">
                <input class="tgl" name="testMode" id="testMode" type="checkbox" <?= $networkInfo['test_mode'] ? "checked" : "";?>>
                <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="testMode"><span></span></label>
            </div>
        </label>
        <label for="appMode" class="field !flex-row toggle-field !px-6 py-2 rounded-full bg-white shadow-md">
            <div class="flex flex-col w-full">
                <div class="shrink">Enable LIMS</div>
                <div class="description !text-xs text-grey mr-4">Turn LIMS internal application on/off.</div>
            </div>
            <div class="checkbox-wrapper">
                <input class="tgl" name="appMode" id="appMode" type="checkbox" <?= $networkInfo['app_mode'] ? "checked" : "";?>>
                <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="appMode"><span></span></label>
            </div>
        </label>
    </div>
    <h3 class="text-dark mt-4 w-full text-center rounded-xl px-4 py-2 bg-blue-200/75 shadow-lg">LIMS Settings</h3>
    <div id="hl7Options" class="form-fields <?= $networkInfo['selected_protocol'] == "HL7" ? "active" : "";?>">
        <div class="form-fields">
            <div class="field">
                <label for="hl7IP">HL7 IP Address</label>
                <div class="input-wrapper">
                    <input required id="hl7IP" type="text" name="hl7IP" value="<?= $networkInfo['hl7_server_ip'];?>">
                </div>
            </div>
            <div class="field">
                <label for="hl7Port">HL7 Port</label>
                <div class="input-wrapper">
                    <input required id="hl7Port" type="number" name="hl7Port" value="<?= $networkInfo['hl7_server_port'];?>">
                </div>
            </div>
            <div class="field">
                <label for="hl7ServerName">HL7 Servername</label>
                <div class="input-wrapper">
                    <input required id="hl7ServerName" type="text" name="hl7ServerName" value="<?= $networkInfo['hl7_server_dest'];?>">
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
    <div id="cainOptions" class="form-fields <?= $networkInfo['selected_protocol'] == "Cain" ? "active" : "";?>">
        <div class="form-fields">
            <div class="field">
                <label for="cainIP">Cain IP Address</label>
                <div class="input-wrapper">
                    <input required id="cainIP" type="text" name="cainIP" value="<?= $networkInfo['cain_server_ip'];?>">
                </div>
            </div>
            <div class="field">
                <label for="cainPort">Cain Port</label>
                <div class="input-wrapper">
                    <input required id="cainPort" type="number" name="cainPort" value="<?= $networkInfo['cain_server_port'];?>">
                </div>
            </div>
        </div>
    </div>
    <button class="btn smaller-btn trigger-loading" type="submit">Save Settings</button>
</form>