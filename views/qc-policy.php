
<?php // QC Settings

$hospitalInfo = systemInfo();

// Extract 'name' as keys and 'value' as values
$settings = array_column($hospitalInfo, 'value', 'name');

// Hospital Info Settings Subset
$qcKeys = ['qc_policy', 'qc_positive_requirements', 'qc_negative_requirements', 'qc_enable_independence'];
$qcSettings = array_intersect_key($settings, array_flip($qcKeys));
?>

<h1>Quality Control Policy for Lots</h1>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>These are the current QC policy settings for Lots.</p>
</section>

<div class="cain-grid max-w-4xl mx-auto">
    <div class="cain-grid-item">
        <div class="grid-title">
            <h4>QC Policy:</h4>
            <p>Are we automatically validating QC? 0 = Off, 1 = On, 2 = Manual</p>
        </div>
    </div>
    <p class="cain-grid-content"><?= ucfirst($qcSettings['qc_policy']);?></p>
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
</div>

<?php if($currentUser['user_type'] >= ADMINISTRATIVE_CLINICIAN) : ?>
    <div class="flex items-end max-w-4xl mx-auto">
        <a href="/settings/qc" class="btn border-btn">Adjust QC Policy</a>
    </div>
<?php endif;?>