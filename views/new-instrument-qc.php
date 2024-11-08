<?php // Create a new QC page
$instrument = $_GET['instrument'] ?? null;

if(!$instrument) {
    header("Location: /login");
    exit();
}

// Next, get a list of QC Test Types
$qcTestTypes = getInstrumentQCTypes();

// We need to get the information of this Assay Module
$instrument = getInstrumentSnapshot($instrument);

// We may also have a pre-filled type
$qcType = $_GET['type'] ?? null;

// Check for form errors
$hasErrors = $form->getValue("form") == "edit" && $form->getValue("id") == $operator['id'];

// Start page content ?>

<link href="/js/vanilla-calendar/build/vanilla-calendar.min.css" rel="stylesheet">
<script src="/js/vanilla-calendar/build/vanilla-calendar.min.js" defer></script>
<script src="/js/dateRangePicker.js" defer></script>

<!-- Assay Module Title Area -->
<section class="flex items-center justify-between gap-4">
    <div class="page-title-area h-full flex items-center gap-4">

        <!-- Link to return to assay modules page -->
        <a class="h-12 hover:opacity-75 hover:scale-110 transition-all back-page-btn flex items-center" href="/assay-modules/<?= $instrument['id'];?>">
            <svg class="h-3/4 w-auto fill-grey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M512 256A256 256 0 1 0 0 256a256 256 0 1 0 512 0zM116.7 244.7l112-112c4.6-4.6 11.5-5.9 17.4-3.5s9.9 8.3 9.9 14.8l0 64 96 0c17.7 0 32 14.3 32 32l0 32c0 17.7-14.3 32-32 32l-96 0 0 64c0 6.5-3.9 12.3-9.9 14.8s-12.9 1.1-17.4-3.5l-112-112c-6.2-6.2-6.2-16.4 0-22.6z"/>
            </svg>
        </a>

        <h1 class="mb-0">New Instrument QC Result</h1>
    </div>
</section>

<!-- Page description -->
<section class="notice">
    <svg viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>Log a QC test for the instrument with the following serial number: <?= $instrument['serial_number'];?>.</p>
</section>

<!-- Form for handling QC test submission -->
<form action="/process" method="POST">
    <input type="hidden" name="action" value="new-instrument-qc">
    <input type="hidden" name="instrument" value="<?= $instrument['id'];?>">
    <input type="hidden" name="return-path" value="/assay-modules/<?= $instrument['id'];?>">
    <input type="hidden" name="return-path-err" value="<?= $currentURL;?>">

    <div class="form-fields">
        <div class="field">
            <label for="dateTimePicker">Select date/time of test</label>
            <div class="input-wrapper">
                <svg class="cursor-pointer" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                    <path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zm64 80v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm128 0v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H208c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H336zM64 400v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H208zm112 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H336c-8.8 0-16 7.2-16 16z"/>
                </svg>
                <input id="dateTimePicker" name="datetime" class="date-time-picker" type="text" placeholder="<?= gmdate("Y-m-d");?>" value="<?= gmdate("Y-m-d");?>" readonly/>
            </div>
        </div>
    </div>

    <div class="form-fields">
        <div class="field">
            <label for="instrument">Instrument</label>
            <div class="input-wrapper disabled <?= ($hasErrors && $form->getError('instrument')) ? "error" : "";?>">
                <input disabled spellcheck="false" type="text" name="instrument-serial" value="<?= $instrument['serial_number'];?>" class="cursor-not-allowed">
            </div>
        </div>

        <div class="field">
            <label for="qcType">QC Type</label>
            <div class="input-wrapper select-wrapper">
                <select name="qc-type" id="qcType">
                    <?php foreach($qcTestTypes as $qcTestType) : ?>
                        <option value="<?= $qcTestType['id'];?>" <?= $qcTestType['id'] == $qcType ? "selected" : "";?>><?= $qcTestType['name'];?></option>
                    <?php endforeach;?>
                </select>
            </div>
        </div>

        <div class="field">
            <label for="result">Result</label>
            <div class="input-wrapper select-wrapper">
                <select name="result" id="result">
                    <option value="1">Pass</option>
                    <option value="0">Fail</option>
                </select>
            </div>
        </div>
    </div>
    <div class="form-fields">
        <div class="field">
            <label for="notes">Notes</label>
            <div class="input-wrapper">
                <textarea class="textarea" name="notes" id="notes"></textarea>
            </div>
        </div>
    </div>

    <button type="submit" class="btn smaller-btn trigger-loading">Log QC Test</button>

</form>