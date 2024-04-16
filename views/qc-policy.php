
<?php // QC Settings

$hospitalInfo = systemInfo();

// Extract 'name' as keys and 'value' as values
$settings = array_column($hospitalInfo, 'value', 'name');

// Hospital Info Settings Subset
$qcKeys = ['qc_enforcement', 'qc_positive_requirements', 'qc_negative_requirements', 'qc_enable_independence'];
$qcSettings = array_intersect_key($settings, array_flip($qcKeys));
?>

<h1>Quality Control Policy</h1>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>These are the current default QC policy settings.</p>
</section>

<div class="form-fields">
    <div class="field">
        <label for="qcEnforcement">QC Enforcement</label>
        <div class="input-wrapper select-wrapper !justify-start">
            <div><?= ucfirst($qcSettings['qc_enforcement']);?></div>
        </div>
    </div>
</div>
<div class="form-fields">
    <div class="field">
        <label for="posRequired">Successful Positive Tests Required</label>
        <div class="input-wrapper !justify-start">
            <div><?= $qcSettings['qc_positive_requirements'];?></div>
        </div>
    </div>
    <div class="field">
        <label for="negRequired">Successful Negative Tests Required</label>
        <div class="input-wrapper !justify-start">
            <div><?= $qcSettings['qc_negative_requirements'];?></div>
        </div>
    </div>
</div>
<div class="form-fields">
    <label for="enableIndependence" class="field !flex-row toggle-field !px-6 py-2 rounded-full bg-white shadow-md">
        <div class="flex flex-col w-full">
            <div class="shrink">Enable Independence</div>
            <div class="description !text-xs text-grey mr-4">Allow each instrument to have its own QC policy if necessary.</div>
        </div>
        <div><?= $qcSettings['qc_enable_independence'] ? "ENABLED" : "DISABLED";?></div>
    </label>
</div>

<div class="grow flex items-end">
    <a href="/settings/qc" class="btn border-btn">Adjust QC Policy</a>
</div>