<?php // QC Settings
// Hospital Info Settings Subset
$qcKeys = ['qc_enforcement', 'qc_positive_requirements', 'qc_negative_requirements', 'qc_enable_independence'];
$qcSettings = array_intersect_key($settings, array_flip($qcKeys));
?>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>Select default QC policy settings.</p>
</section>

<form action="/process" method="POST">
    <input type="hidden" name="action" value="qc-settings">
    <input type="hidden" name="return-path" value="<?= $currentURL;?>">
    <div class="form-fields">
        <div class="field">
            <label for="qcEnforcement">QC Enforcement</label>
            <div class="input-wrapper select-wrapper">
                <select name="qcEnforcement" id="qcEnforcement">
                    <option value="off" <?= $qcSettings['qc_enforcement'] === "off" ? "selected" : "";?>>Off</option>
                    <option value="lockout" <?= $qcSettings['qc_enforcement'] === "lockout" ? "selected" : "";?>>Lockout</option>
                    <option value="warn" <?= $qcSettings['qc_enforcement'] === "warn" ? "selected" : "";?>>Warn</option>
                </select>
            </div>
        </div>
    </div>
    <div class="form-fields">
        <div class="field">
            <label for="posRequired">Successful Positive Tests Required</label>
            <div class="input-wrapper">
                <input type="number" name="posRequired" id="posRequired" value="<?= $qcSettings['qc_positive_requirements'];?>" min="0">
            </div>
        </div>
        <div class="field">
            <label for="negRequired">Successful Negative Tests Required</label>
            <div class="input-wrapper">
                <input type="number" name="negRequired" id="negRequired" value="<?= $qcSettings['qc_negative_requirements'];?>" min="0">
            </div>
        </div>
    </div>
    <div class="form-fields">
        <label for="enableIndependence" class="field !flex-row toggle-field !px-6 py-2 rounded-full bg-white shadow-md">
            <div class="flex flex-col w-full">
                <div class="shrink">Enable Independence</div>
                <div class="description !text-xs text-grey mr-4">Allow each instrument to have its own QC policy if necessary.</div>
            </div>
            <div class="checkbox-wrapper">
                <input class="tgl" name="enableIndependence" id="enableIndependence" type="checkbox" <?= $qcSettings['qc_enable_independence'] ? "checked" : "";?>>
                <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="enableIndependence"><span></span></label>
            </div>
        </label>
    </div>
    <button class="btn smaller-btn" type="submit">Save Settings</button>
</form>