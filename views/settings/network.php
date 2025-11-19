<?php // General Settings

// Hospital Info Settings Subset
$networkInfoKeys = ['selected_protocol', 'cain_server_ip', 'cain_server_port', 'hl7_server_ip', 'hl7_server_port', 'hl7_server_dest', 'patient_id', 'test_mode', 'app_mode', 'lims_retry_timeout', 'send_invalid_results_to_lims'];
$networkInfo = array_intersect_key($settings, array_flip($networkInfoKeys));

$macAddress = exec("ifconfig enp1s0 2>/dev/null | awk '/ether/{print $2}'");

if (empty($macAddress)) {
    $macAddress = exec("ifconfig | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}' | head -n 1");
}


// Get IP address of eth0
$eth0IP = getPrimaryIPv4();

// Check if current user is service engineer or higher
$serviceEngineer = (int)$currentUser['user_type'] >= SERVICE_ENGINEER;

// Determine if LIMS simulator is on
$limsOn = $serviceEngineer ? isLimsSimulatorOn() : false;
?>

<script>
const limsSimulatorSettings = {
    ip: <?= json_encode(LIMS_SIMULATOR_IP); ?>,
    port: <?= json_encode(LIMS_SIMULATOR_PORT); ?>,
    protocol: 0
};

const backupSettings = {
    ip: <?= !$limsOn ? json_encode($settings['cain_server_ip']) : json_encode($settings['cain_server_ip_backup'] ?? ''); ?>,
    port: <?= !$limsOn ? json_encode($settings['cain_server_port']) : json_encode($settings['cain_server_port_backup'] ?? ''); ?>,
    protocol: <?= json_encode($settings['selected_protocol_backup'] == 'Cain' ? '1' : '0'); ?>
};
</script>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>Set whether or not the system is working with HL7 or proprietary protocols and view/change relevant IP addresses.</p>
</section>

<form action="/process" method="POST">
    <input type="hidden" name="action" value="network-settings">
    <input type="hidden" name="return-path" value="<?= $currentURL;?>">
    <h3 class="text-dark mt-4 w-full text-center rounded-xl px-4 py-2 bg-fuchsia-200/75 shadow-lg">System Settings</h3>

    <div class="form-fields">
        <div class="field">
            <label for="protocol">Protocol</label>
            <div class="input-wrapper select-wrapper">
                <select required name="protocol" id="protocol">
                    <option <?= $networkInfo['selected_protocol'] == "Cain" ? "selected" : "";?> value="0">Proprietary</option>
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
                <label for="dmsIp">DMS IP/Hostname</label>
                <div class="input-wrapper disabled">
                    <input disabled required class="cursor-not-allowed" id="dmsIp" type="text" name="dmsIp" value="<?= gethostbyname($_SERVER['SERVER_NAME']);?>">
                </div>
            </div>
            <div class="field">
                <label for="dmsEthIp">DMS Ethernet IP</label>
                <div class="input-wrapper disabled">
                    <input disabled required class="cursor-not-allowed" id="dmsEthIp" type="text" name="dmsEthIp" value="<?= $eth0IP ?? "Not found.";?>">
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
        <label for="appMode" class="field !flex-row toggle-field !shrink !px-6 py-2 rounded-full bg-white shadow-md">
            <div class="flex flex-col w-full">
                <div class="shrink">Enable LIMS</div>
                <div class="description !text-xs text-grey mr-4">Turn LIMS internal application on/off.</div>
            </div>
            <div class="checkbox-wrapper">
                <input class="tgl" name="appMode" id="appMode" type="checkbox" <?= $networkInfo['app_mode'] ? "checked" : "";?>>
                <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="appMode"><span></span></label>
            </div>
        </label>
        <?php if($serviceEngineer) : ?>
            <label for="limsSim" class="field !flex-row toggle-field !shrink !px-6 py-2 rounded-full bg-white shadow-md">
                <div class="flex flex-col w-full">
                    <div class="shrink">LIMS Simulator</div>
                    <div class="description !text-xs text-grey mr-4">Turn on LIMS simulator.</div>
                </div>
                <div class="checkbox-wrapper">
                    <input class="tgl" name="limsSim" id="limsSim" type="checkbox" <?= $limsOn ? "checked" : "";?>>
                    <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="limsSim"><span></span></label>
                </div>
            </label>
        <?php endif;?>
    </div>
    <h3 class="text-dark mt-4 w-full text-center rounded-xl px-4 py-2 bg-fuchsia-200/75 shadow-lg">LIMS Settings</h3>
    <div id="hl7Options" class="form-fields -mt-4 <?= $networkInfo['selected_protocol'] == "HL7" ? "active" : "";?>">
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
    </div>
    <div id="cainOptions" class="form-fields mb-4 -mt-4 <?= $networkInfo['selected_protocol'] == "Cain" ? "active" : "";?>">
        <div class="form-fields">
            <div class="field">
                <label for="cainIP">IP Address</label>
                <div class="input-wrapper">
                    <input required id="cainIP" type="text" name="cainIP" value="<?= $networkInfo['cain_server_ip'];?>">
                </div>
            </div>
            <div class="field">
                <label for="cainPort">Port</label>
                <div class="input-wrapper">
                    <input required id="cainPort" type="number" name="cainPort" value="<?= $networkInfo['cain_server_port'];?>">
                </div>
            </div>
            <div class="field">
                <label for="retryTimeout">Timeout (s)</label>
                <div class="input-wrapper">
                    <input required id="retryTimeout" type="number" name="retryTimeout" value="<?= (int)$networkInfo['lims_retry_timeout'] * 5;?>">
                </div>
            </div>
        </div>
    </div>
    <div class="form-fields -mt-4">
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
        <label for="sendInvalid" class="field !flex-row toggle-field !px-6 py-2 rounded-full bg-white shadow-md">
            <div class="flex flex-col w-full">
                <div class="shrink">Send Invalid Results to LIMS</div>
                <div class="description !text-xs text-grey mr-4">Does your LIMS allow invalid results?</div>
            </div>
            <div class="checkbox-wrapper">
                <input class="tgl" name="sendInvalid" id="sendInvalid" type="checkbox" <?= $networkInfo['send_invalid_results_to_lims'] ? "checked" : "";?>>
                <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="sendInvalid"><span></span></label>
            </div>
        </label>
        <label for="testMode" class="field !flex-row toggle-field !px-6 py-2 rounded-full bg-white shadow-md">
            <div class="flex flex-col w-full">
                <div class="shrink">LIMS Test Mode</div>
                <div class="description !text-xs text-grey mr-4">Should LIMS middleware respond positively regardless of input?</div>
            </div>
            <div class="checkbox-wrapper">
                <input class="tgl" name="testMode" id="testMode" type="checkbox" <?= $networkInfo['test_mode'] ? "checked" : "";?>>
                <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="testMode"><span></span></label>
            </div>
        </label>
    </div>
    <button class="btn smaller-btn trigger-loading" type="submit">Save Settings</button>
</form>