
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

<div class="cain-grid max-w-4xl mx-auto">
    <div class="cain-grid-item">
        <div class="grid-title">
            <h4>QC Enforcement:</h4>
            <p>Is QC enforced before an instrument is able to be used?</p>
        </div>
    </div>
    <p class="cain-grid-content"><?= ucfirst($qcSettings['qc_enforcement']);?></p>
    <div class="cain-grid-item">
        <div class="grid-title">
            <h4>Successful Positive Tests:</h4>
            <p>How many successful positive tests must be carried out before QC is passed.</p>
        </div>
    </div>
    <p class="cain-grid-content"><?= ucfirst($qcSettings['qc_positive_requirements']);?></p>
    <div class="cain-grid-item">
        <div class="grid-title">
            <h4>Successful Negative Tests:</h4>
            <p>How many successful negative tests must be carried out before QC is passed.</p>
        </div>
    </div>
    <p class="cain-grid-content"><?= ucfirst($qcSettings['qc_negative_requirements']);?></p>
    <div class="cain-grid-item">
        <div class="grid-title">
            <h4>Independence:</h4>
            <p>Allow each instrument to have its own QC policy if necessary.</p>
        </div>
    </div>
    <p class="cain-grid-content"><?= $qcSettings['qc_enable_independence'] ? "ENABLED" : "DISABLED";?></p>
</div>

<?php if($currentUser['user_type'] >= ADMINISTRATIVE_CLINICIAN) : ?>
    <div class="flex items-end max-w-4xl mx-auto">
        <a href="/settings/qc" class="btn border-btn">Adjust QC Policy</a>
    </div>
<?php endif;?>