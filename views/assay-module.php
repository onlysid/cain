<?php // Individual Assay Module Page

// We need to get the information of this Assay Module
$instrument = getInstrumentSnapshot($id);

$result = getInstrumentQCResult($instrument['qc']['pass']);

$qcTestTypes = getInstrumentQCTypes();

// Define SVG attributes based on QC status
$qcStatus = [
    1 => ['color' => 'green', 'path' => 'M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z'],
    0 => ['color' => 'red', 'path' => 'M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z'],
    2 => ['color' => 'amber', 'path' => 'M32 0C14.3 0 0 14.3 0 32S14.3 64 32 64l0 11c0 42.4 16.9 83.1 46.9 113.1L146.7 256 78.9 323.9C48.9 353.9 32 394.6 32 437l0 11c-17.7 0-32 14.3-32 32s14.3 32 32 32l32 0 256 0 32 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l0-11c0-42.4-16.9-83.1-46.9-113.1L237.3 256l67.9-67.9c30-30 46.9-70.7 46.9-113.1l0-11c17.7 0 32-14.3 32-32s-14.3-32-32-32L320 0 64 0 32 0zM96 75l0-11 192 0 0 11c0 25.5-10.1 49.9-28.1 67.9L192 210.7l-67.9-67.9C106.1 124.9 96 100.4 96 75z'],
    3 => ['color' => 'dark', 'path' => 'M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM169.8 165.3c7.9-22.3 29.1-37.3 52.8-37.3l58.3 0c34.9 0 63.1 28.3 63.1 63.1c0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24l0-13.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1c0-8.4-6.8-15.1-15.1-15.1l-58.3 0c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z']
];

// Get QC status properties
$status = $qcStatus[$instrument['qc']['pass']] ?? ['color' => 'gray', 'path' => ''];

// Start page content ?>

<!-- Assay Module Title Area -->
<section class="flex items-center justify-between gap-4">
    <div class="page-title-area h-full flex items-center gap-4">

        <!-- Link to return to assay modules page -->
        <a class="h-12 hover:opacity-75 hover:scale-110 transition-all back-page-btn flex items-center" href="/assay-modules">
            <svg class="h-3/4 w-auto fill-grey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M512 256A256 256 0 1 0 0 256a256 256 0 1 0 512 0zM116.7 244.7l112-112c4.6-4.6 11.5-5.9 17.4-3.5s9.9 8.3 9.9 14.8l0 64 96 0c17.7 0 32 14.3 32 32l0 32c0 17.7-14.3 32-32 32l-96 0 0 64c0 6.5-3.9 12.3-9.9 14.8s-12.9 1.1-17.4-3.5l-112-112c-6.2-6.2-6.2-16.4 0-22.6z"/>
            </svg>
        </a>

        <h1 class="mb-0">Assay Module #<?= $instrument['id'];?></h1>

        <div class="instrument-title-icons">
            <div class="tooltip" title="<?= $instrument['locked'] ? 'Locked' : 'Unlocked' ?>">
                <svg class="fill-<?= $instrument['locked'] ? 'red' : 'green' ?>-500" viewBox="0 0 448 512">
                    <path d="<?= $instrument['locked']
                        ? 'M144 144l0 48 160 0 0-48c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192l0-48C80 64.5 144.5 0 224 0s144 64.5 144 144l0 48 16 0c35.3 0 64 28.7 64 64l0 192c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 256c0-35.3 28.7-64 64-64l16 0z'
                        : 'M144 144c0-44.2 35.8-80 80-80c31.9 0 59.4 18.6 72.3 45.7c7.6 16 26.7 22.8 42.6 15.2s22.8-26.7 15.2-42.6C331 33.7 281.5 0 224 0C144.5 0 80 64.5 80 144l0 48-16 0c-35.3 0-64 28.7-64 64L0 448c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-192c0-35.3-28.7-64-64-64l-240 0 0-48z' ?>">
                </svg>
            </div>

            <div class="wrapper tooltip !flex gap-1 items-center" title="QC <?= ucfirst($result); ?>">
                <svg class="fill-<?= $status['color'] ?>-500" viewBox="0 0 512 512">
                    <path d="<?= $status['path'] ?>" />
                </svg>
            </div>
        </div>

    </div>
    <button data-modal-open="toggleInstrumentStatus" class="btn smaller-btn <?= $instrument['locked'] ? 'btn-red' : 'btn-green';?>" type="submit"><?= $instrument['locked'] ? "Unlock" : "Lock";?>?</button>
</section>

<?php // If we have not seen the instrument for 2hrs, make sure to alert the user that the information may not be completely up to date
if(time() - $instrument['last_connected'] > 7200) : ?>
    <section class="notice !bg-red-300 !mb-0">
        <svg viewBox="0 0 512 512">
            <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
        </svg>
        <p>Device has not been connected to SAMBA for a while. Information might not be fully up to date. Please connect device to get latest information.</p>
    </section>
<?php endif;

// If the device has reported an error, alert the user
if($instrument['device_error']) : ?>
    <section class="notice !bg-red-300 !mb-0">
        <svg viewBox="0 0 512 512">
            <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
        </svg>
        <p><span class="font-black">Error: </span><?= $instrument['device_error'];?></p>
    </section>
<?php endif;?>

<!-- Instrument's main information -->
<section class="mb-2">
    <div class="instrument-info">

        <h4 class="text-center w-full">Status: <span class="status-<?= $instrument['status'];?>"><?= strtoupper(getInstrumentStatusText($instrument['status']));?></span></h4>
        <div class="item">
            <div class="item-title">
                <svg viewBox="0 0 512 512">
                    <path d="M48 256C48 141.1 141.1 48 256 48c63.1 0 119.6 28.1 157.8 72.5c8.6 10.1 23.8 11.2 33.8 2.6s11.2-23.8 2.6-33.8C403.3 34.6 333.7 0 256 0C114.6 0 0 114.6 0 256l0 40c0 13.3 10.7 24 24 24s24-10.7 24-24l0-40zm458.5-52.9c-2.7-13-15.5-21.3-28.4-18.5s-21.3 15.5-18.5 28.4c2.9 13.9 4.5 28.3 4.5 43.1l0 40c0 13.3 10.7 24 24 24s24-10.7 24-24l0-40c0-18.1-1.9-35.8-5.5-52.9zM256 80c-19 0-37.4 3-54.5 8.6c-15.2 5-18.7 23.7-8.3 35.9c7.1 8.3 18.8 10.8 29.4 7.9c10.6-2.9 21.8-4.4 33.4-4.4c70.7 0 128 57.3 128 128l0 24.9c0 25.2-1.5 50.3-4.4 75.3c-1.7 14.6 9.4 27.8 24.2 27.8c11.8 0 21.9-8.6 23.3-20.3c3.3-27.4 5-55 5-82.7l0-24.9c0-97.2-78.8-176-176-176zM150.7 148.7c-9.1-10.6-25.3-11.4-33.9-.4C93.7 178 80 215.4 80 256l0 24.9c0 24.2-2.6 48.4-7.8 71.9C68.8 368.4 80.1 384 96.1 384c10.5 0 19.9-7 22.2-17.3c6.4-28.1 9.7-56.8 9.7-85.8l0-24.9c0-27.2 8.5-52.4 22.9-73.1c7.2-10.4 8-24.6-.2-34.2zM256 160c-53 0-96 43-96 96l0 24.9c0 35.9-4.6 71.5-13.8 106.1c-3.8 14.3 6.7 29 21.5 29c9.5 0 17.9-6.2 20.4-15.4c10.5-39 15.9-79.2 15.9-119.7l0-24.9c0-28.7 23.3-52 52-52s52 23.3 52 52l0 24.9c0 36.3-3.5 72.4-10.4 107.9c-2.7 13.9 7.7 27.2 21.8 27.2c10.2 0 19-7 21-17c7.7-38.8 11.6-78.3 11.6-118.1l0-24.9c0-53-43-96-96-96zm24 96c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 24.9c0 59.9-11 119.3-32.5 175.2l-5.9 15.3c-4.8 12.4 1.4 26.3 13.8 31s26.3-1.4 31-13.8l5.9-15.3C267.9 411.9 280 346.7 280 280.9l0-24.9z"/>
                </svg>
                <h5>Serial Number</h5>
            </div>
            <p><?= $instrument['serial_number'] ?? "Unknown";?></p>
        </div>

        <div class="item">
            <div class="item-title">
                <svg viewBox="0 0 448 512">
                    <path d="M80 104a24 24 0 1 0 0-48 24 24 0 1 0 0 48zm80-24c0 32.8-19.7 61-48 73.3l0 38.7c0 17.7 14.3 32 32 32l160 0c17.7 0 32-14.3 32-32l0-38.7C307.7 141 288 112.8 288 80c0-44.2 35.8-80 80-80s80 35.8 80 80c0 32.8-19.7 61-48 73.3l0 38.7c0 53-43 96-96 96l-48 0 0 70.7c28.3 12.3 48 40.5 48 73.3c0 44.2-35.8 80-80 80s-80-35.8-80-80c0-32.8 19.7-61 48-73.3l0-70.7-48 0c-53 0-96-43-96-96l0-38.7C19.7 141 0 112.8 0 80C0 35.8 35.8 0 80 0s80 35.8 80 80zm208 24a24 24 0 1 0 0-48 24 24 0 1 0 0 48zM248 432a24 24 0 1 0 -48 0 24 24 0 1 0 48 0z"/>
                </svg>
                <h5>Module Version</h5>
            </div>
            <p><?= $instrument['module_version'] ?? "Unknown";?></p>
        </div>

        <div class="item">
            <div class="item-title">
                <svg viewBox="0 0 448 512">
                    <path d="M181.3 32.4c17.4 2.9 29.2 19.4 26.3 36.8L197.8 128l95.1 0 11.5-69.3c2.9-17.4 19.4-29.2 36.8-26.3s29.2 19.4 26.3 36.8L357.8 128l58.2 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-68.9 0L325.8 320l58.2 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-68.9 0-11.5 69.3c-2.9 17.4-19.4 29.2-36.8 26.3s-29.2-19.4-26.3-36.8l9.8-58.7-95.1 0-11.5 69.3c-2.9 17.4-19.4 29.2-36.8 26.3s-29.2-19.4-26.3-36.8L90.2 384 32 384c-17.7 0-32-14.3-32-32s14.3-32 32-32l68.9 0 21.3-128L64 192c-17.7 0-32-14.3-32-32s14.3-32 32-32l68.9 0 11.5-69.3c2.9-17.4 19.4-29.2 36.8-26.3zM187.1 192L165.8 320l95.1 0 21.3-128-95.1 0z"/>
                </svg>
                <h5>Front Panel ID</h5>
            </div>
            <p><?= $instrument['front_panel_id'] ?? "Unknown";?></p>
        </div>

        <div class="item">
            <div class="item-title">
                <svg viewBox="0 0 448 512">
                    <path d="M0 64C0 28.7 28.7 0 64 0L384 0c35.3 0 64 28.7 64 64l0 384c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 64zM256 448a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zM384 64L64 64l0 320 320 0 0-320z"/>
                </svg>
                <h5>Tablet Version</h5>
            </div>
            <p><?= $instrument['tablet_version'] ?? "Unknown";?></p>
        </div>

        <div class="item">
            <div class="item-title">
                <svg viewBox="0 0 512 512">
                    <path d="M75 75L41 41C25.9 25.9 0 36.6 0 57.9L0 168c0 13.3 10.7 24 24 24l110.1 0c21.4 0 32.1-25.9 17-41l-30.8-30.8C155 85.5 203 64 256 64c106 0 192 86 192 192s-86 192-192 192c-40.8 0-78.6-12.7-109.7-34.4c-14.5-10.1-34.4-6.6-44.6 7.9s-6.6 34.4 7.9 44.6C151.2 495 201.7 512 256 512c141.4 0 256-114.6 256-256S397.4 0 256 0C185.3 0 121.3 28.7 75 75zm181 53c-13.3 0-24 10.7-24 24l0 104c0 6.4 2.5 12.5 7 17l72 72c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-65-65 0-94.1c0-13.3-10.7-24-24-24z"/>
                </svg>
                <h5>Last Connected</h5>
            </div>
            <p><?= $instrument['last_connected'] ? convertTimestamp($instrument['last_connected'], true) : "Never";?></p>
        </div>

        <?php if(in_array($instrument['status'], [2, 3, 4, 5, 6, 7, 8, 9])) : ?>
            <div class="item">
                <div class="item-title">
                    <svg viewBox="0 0 512 512">
                        <path d="M0 64C0 46.3 14.3 32 32 32l56 0 48 0 56 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l0 304c0 44.2-35.8 80-80 80s-80-35.8-80-80L32 96C14.3 96 0 81.7 0 64zM136 96L88 96l0 160 48 0 0-160zM288 64c0-17.7 14.3-32 32-32l56 0 48 0 56 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l0 304c0 44.2-35.8 80-80 80s-80-35.8-80-80l0-304c-17.7 0-32-14.3-32-32zM424 96l-48 0 0 160 48 0 0-160z"/>
                    </svg>
                    <h5>Current Assay</h5>
                </div>
                <p><?= isNullOrEmptyString($instrument['current_assay']) ? "Unknown" : $instrument['current_assay'];?></p>
            </div>

            <div class="item">
                <div class="item-title">
                    <svg viewBox="0 0 512 512">
                        <path d="M464 256A208 208 0 1 1 48 256a208 208 0 1 1 416 0zM0 256a256 256 0 1 0 512 0A256 256 0 1 0 0 256zM232 120l0 136c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2 280 120c0-13.3-10.7-24-24-24s-24 10.7-24 24z"/>
                    </svg>
                    <h5>Assay Start Time</h5>
                </div>
                <p><?= $instrument['assay_start_time'] ? convertTimestamp($instrument['assay_start_time'], true) : "Unknown";?></p>
            </div>

            <?php if(in_array($instrument['status'], [2, 3])) : ?>

                <div class="item">
                    <div class="item-title">
                        <svg viewBox="0 0 384 512">
                            <path d="M32 0C14.3 0 0 14.3 0 32S14.3 64 32 64l0 11c0 42.4 16.9 83.1 46.9 113.1L146.7 256 78.9 323.9C48.9 353.9 32 394.6 32 437l0 11c-17.7 0-32 14.3-32 32s14.3 32 32 32l32 0 256 0 32 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l0-11c0-42.4-16.9-83.1-46.9-113.1L237.3 256l67.9-67.9c30-30 46.9-70.7 46.9-113.1l0-11c17.7 0 32-14.3 32-32s-14.3-32-32-32L320 0 64 0 32 0zM96 75l0-11 192 0 0 11c0 19-5.6 37.4-16 53L112 128c-10.3-15.6-16-34-16-53zm16 309c3.5-5.3 7.6-10.3 12.1-14.9L192 301.3l67.9 67.9c4.6 4.6 8.6 9.6 12.1 14.9L112 384z"/>
                        </svg>
                        <h5>Time Remaining</h5>
                    </div>
                    <p><?= $instrument['duration'];?></p>
                </div>
            <?php endif;
        endif;?>
    </div>
</section>

<!-- Modal for locking/unlocking the device -->
<div class="modal-wrapper">
    <div id="toggleInstrumentStatus" class="generic-modal">
        <div class="close-modal" data-modal-close>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
            </svg>
        </div>
        <svg class="fill-red-500 h-10 w-auto mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
            <path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24V296c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/>
        </svg>
        <p class="text-center">Are you sure you want to <span class="uppercase font-black text-lg align-middle <?= $instrument['locked'] ? "text-green-500" : "text-red-500";?>"><?= $instrument['locked'] ? "unlock" : "lock";?></span> instrument #<?= $instrument['id'];?></p>
        <form class="items-center w-max" action="/process" method="POST">
            <input type="hidden" name="action" value="toggle-instrument-lock">
            <input type="hidden" name="instrument" value="<?= $instrument['id'];?>">
            <input type="hidden" name="return-path" value="<?= $currentURL;?>">

            <div class="w-full flex justify-center items-center gap-3 mt-3">
                <button type="submit" class="btn smaller-btn trigger-loading">Yes</button>
                <div class="cursor-pointer btn smaller-btn close-modal no-styles" data-modal-close>Cancel</div>
            </div>
        </form>
    </div>
</div>

<!-- List the latest QC results -->
<section class="mt-3 mb-0">
    <h2>Latest QC Results - <span class="qc-result <?= $result;?>"><?= $result;?></span></h2>
    <p class="mb-2">Click on any of the results to view more details, or start a new QC test.</p>

    <?php if(!$qcTestTypes) : ?>
        <p id="noTestTypes" class="text-center w-full p-4 bg-white rounded-lg">You currently have no QC Test Types. Please add one in the <a href="/settings/qc-types">QC Test Type Settings</a> to continue.</p>
    <?php else : ?>
        <table id="qcTable" class="!px-0">
            <thead>
                <th>#</th>
                <th>QC Name</th>
                <th>Result</th>
                <th>Test Date</th>
                <th>Results Since Test</th>
                <th></th>
            </thead>

            <!-- List of latest QC result for each type of test -->
            <tbody id="qcTableBody">
                <?php foreach($qcTestTypes as $testType) : ?>
                    <?php // If we have the test, here it is
                    $test = $instrument['qc']['res'][$testType['id']];?>
                    <tr class="qc-table" data-modal-open="qc<?= $testType['id'];?>">
                        <td><?= $testType['id'];?></td>
                        <td><?= $testType['name'];?></td>
                        <?php // We either have information about the latest test
                        if($test) :
                            // Determine if the time has expired
                            $testTimestamp = $test['timestamp'];
                            $intervalSeconds = $testType['time_intervals'] * 86400;
                            $expiredTimestamp = $intervalSeconds ? $testTimestamp + $intervalSeconds : null;
                            $currentTimestamp = time();

                            // It has not expired
                            $expiredByTime = 0;

                            if ($expiredTimestamp) {
                                // Calculate one day after the expiration timestamp
                                $nextDayAfterExpired = strtotime(gmdate("Y-m-d", $expiredTimestamp) . ' +1 day');

                                if ($currentTimestamp >= $nextDayAfterExpired) {
                                    // It's expired (one day after the expiration date)
                                    $expiredByTime = 2;
                                } elseif ($currentTimestamp >= $expiredTimestamp && $currentTimestamp < $nextDayAfterExpired) {
                                    // It's expiring today
                                    $expiredByTime = 1;
                                }
                            }

                            // Understand if the results since last test has expired
                            $resultCount = $test['result_counter'];
                            $expiredResultCount = $testType['result_intervals'] && ($resultCount >= $testType['result_intervals']);

                            ?>
                            <td><?= $test['result'] == 1 ? "<span class='text-green-500'>PASS</span>" : "<span class='text-red-500'>FAIL</span>";?></td>
                            <td class="flex flex-col expires-<?= $expiredByTime;?>">
                                <p class="text-base"><?= convertTimestamp($testTimestamp);?></p>
                                <?php if($expiredTimestamp) : ?>
                                    <p class="text-sm"><?= $expiredByTime == 1 ? "Expires today." : ($expiredByTime == 2 ? "Expired on " . convertTimestamp($expiredTimestamp) : "Expires: " . convertTimestamp($expiredTimestamp));?></p>
                                <?php endif;?>
                            </td>
                            <td class="<?= $expiredResultCount ? 'text-red-500' : 'text-green-500';?>"><?= $resultCount;?><?= $testType['result_intervals'] ? "/" . $testType['result_intervals'] : '';?></td>
                            <td>
                                <button class="details tooltip" title="View/Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <path d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152V424c0 48.6 39.4 88 88 88H360c48.6 0 88-39.4 88-88V312c0-13.3-10.7-24-24-24s-24 10.7-24 24V424c0 22.1-17.9 40-40 40H88c-22.1 0-40-17.9-40-40V152c0-22.1 17.9-40 40-40H200c13.3 0 24-10.7 24-24s-10.7-24-24-24H88z"/>
                                    </svg>
                                </button>
                            </td>
                            <?php // Or it does not exist, and so we should prompt to handle that
                        else : ?>
                            <td colspan="4" class="!py-1">
                                <div class="w-full flex justify-center">
                                    <a href="/assay-modules/new-qc?instrument=<?= $instrument['id'];?>&type=<?= $testType['id'];?>" class="btn w-full !py-1 smaller-btn">Never tested. Record QC Test now.</a>
                                </div>
                            </td>
                        <?php endif;?>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    <?php endif;?>
</section>

<!-- QC Result Popup Modal -->
<div class="modal-wrapper">

    <?php // Get all QC tests
    foreach($qcTestTypes as $testType) :
        $qcResult = $instrument['qc']['res'][$testType['id']]; ?>
        <div id="qc<?= $testType['id'];?>" class="generic-modal">
            <div class="close-modal" data-modal-close>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
                </svg>
            </div>
            <h2 class="mb-3"><?= $testType['name'];?> Test</h2>
            <?php if($qcResult = $instrument['qc']['res'][$testType['id']]) :
                // If the test type has an expiration time, set an expiration date
                $expirationString = null;
                $timeDiffMessage = null;
                if($testType['time_intervals'] && $qcResult['timestamp']) {
                    $expirationTimestamp = $testType['time_intervals'] + $qcResult['timestamp'] + 86400;

                    // Calculate the expiration
                    $timeDiffMessage = timeDifferenceMessage($expirationTimestamp);
                    $expirationString = $expirationTimestamp . " (" . $timeDiffMessage['string'] . ")";
                }?>
                <p class="font-black">Result: <?= $qcResult['result'] ? '<span class="text-green-500">PASS</span>' : '<span class="text-red-500">FAIL</span>';?></p>
                <p><span class="font-black">Date/Time: </span><?= convertTimestamp($qcResult['timestamp'], true) ?? 'UNKNOWN';?></p>
                <p class="font-black">QC Test Expires: <span class="severity-<?= $timeDiffMessage ? $timeDiffMessage['severity'] : 0;?>"><?= $expirationString ? convertTimestamp($expirationTimestamp) : 'NEVER';?></span></p>
                <p><span class="font-black">Operator: </span><?= ($testUser = userInfo($qcResult['user'])) ? $testUser['first_name'] . " " . $testUser['last_name'] . " (" . $testUser['operator_id'] . ")" : "UNKNOWN";?></p>
                <p class="font-black">Results Since Last Test: <?= $qcResult['result_counter'];?><?= $testType['result_intervals'] ? '/' . $testType['result_intervals'] : '';?></p>
                <?php if($qcResult['notes']) : ?>
                    <div class="notes-wrapper rounded-lg shadow-md bg-primary p-3">
                        <p class="font-black">Notes:</p>
                        <p><?= $qcResult['notes'] ?? "Operator left no notes.";?></p>
                    </div>
                <?php endif;?>
            <?php else : ?>
                <p>This test has not yet been completed for this instrument.</p>
            <?php endif;?>
            <div class="divider"></div>
            <div class="flex items-center gap-2">
                <?php if($qcResult) : ?>
                    <a href="/assay-modules/edit-qc?qc=<?= $qcResult['id'];?>" class="btn smaller-btn">Edit</a>
                <?php endif;?>
                <a href="/assay-modules/new-qc?instrument=<?= $instrument['id'];?>&type=<?= $testType['id'];?>" class="btn smaller-btn">New QC Test</a>
            </div>
        </div>
    <?php endforeach;?>

</div>

<!-- QC Actions -->
<section class="flex gap-3 items-center flex-wrap mb-8">
    <a href="/assay-modules/qc/<?= $instrument['id'];?>" class="btn smaller-btn border-btn">View All Tests</a>
    <a href="/assay-modules/new-qc?instrument=<?= $instrument['id'];?>" class="btn smaller-btn border-btn">Add QC Test</a>
</section>

<!-- QC Links -->
<!-- <section class="mb-0 flex flex-col shadow-lg gap-3 justify-center flex-wrap bg-white p-4 rounded-lg">
    <h2 class="underline">Quick Links</h2>
    <div class="flex flex-col justify-center flex-wrap">
        <a href="/settings/qc">General QC Settings</a>
        <a href="/settings/qc-types">QC Type Settings</a>
    </div>
</section> -->