<?php // QC Settings

// Hospital Info Settings Subset
$qcKeys = ['qc_policy', 'qc_positive_requirements', 'qc_negative_requirements', 'qc_enable_independence'];
$qcSettings = array_intersect_key($settings, array_flip($qcKeys));
?>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>Select default QC policy settings.</p>
</section>

<form id="qcPolicyForm" action="/process" method="POST">
    <h3 class="text-dark mt-4 w-full text-center rounded-xl px-4 py-2 bg-blue-200/75 shadow-lg">General QC Settings</h3>

    <input type="hidden" name="action" value="qc-settings">
    <input type="hidden" name="return-path" value="<?= $currentURL;?>">
    <div class="form-fields">
        <div class="field">
            <label for="qcPolicy">QC Policy</label>
            <div class="input-wrapper select-wrapper">
                <select name="qcPolicy" id="qcPolicy">
                    <option value="0" <?= $qcSettings['qc_policy'] === "0" ? "selected" : "";?>>Off</option>
                    <option value="1" <?= $qcSettings['qc_policy'] === "1" ? "selected" : "";?>>Automatic</option>
                    <option value="2" <?= $qcSettings['qc_policy'] === "2" ? "selected" : "";?>>Manual</option>
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
    <button id="qcPolicySubmitButton" class="btn smaller-btn" type="submit">Save Settings</button>

    <div class="modal-wrapper">
        <div id="qcFormWarning" class="generic-modal">
            <div class="close-modal" data-modal-close>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
                </svg>
            </div>
            <svg class="fill-red-500 h-10 w-auto mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24V296c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/>
            </svg>
            <h2 class="text-center">Are you sure?</h2>
            <p class="text-center">Switching to automatic QC policy will run a check and, where possible, pass or fail any unverified lots based on your chosen policy.</p>

            <div class="w-full flex justify-center items-center gap-3 mt-3">
                <button type="submit" class="btn smaller-btn">Save Settings</button>
                <div class="cursor-pointer btn smaller-btn close-modal no-styles" data-modal-close>Cancel</div>
            </div>
        </div>
    </div>
</form>
