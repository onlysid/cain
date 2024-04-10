<?php // Results Page
$hospitalInfo = systemInfo();
$hospitalInfoArray = array_column($hospitalInfo, 'value', 'name');

// Do we have any filters?
$filters = $_GET;
unset($filters['p']);

// Get all the data
$results = getResults($_GET);
$resultItems = $results["results"];
$totalResultsCount = $results["count"];
?>

    <h1 class="mb-2">Results</h1>
    
    <div id="tableInfoWrapper" class="w-full flex justify-between items-center">
        <p class="text-lg">1-10 of <?= $totalResultsCount;?> Results</p>
        <div id="filterSearchWrapper" class="flex items-center gap-2.5">
            <form action="/" method="GET" id="searchForm">
                <div class="form-fields">
                    <div class="field">
                        <div class="input-wrapper !py-1 !pr-1">
                            <input required type="text" placeholder="Search..." name="s" value="<?= $_GET['s'] ?? "";?>">
                            <button class="aspect-square rounded-full p-2 bg-dark transition-all duration-500 hover:scale-110 hover:opacity-75" type="submit">
                                <svg class="h-4 w-auto fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            <button id="filter" class="transition-all duration-500 hover:scale-110 hover:opacity-75 tooltip" title="Coming Soon...">
                <svg class="h-7 fill-dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M0 416c0 17.7 14.3 32 32 32l54.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 448c17.7 0 32-14.3 32-32s-14.3-32-32-32l-246.7 0c-12.3-28.3-40.5-48-73.3-48s-61 19.7-73.3 48L32 384c-17.7 0-32 14.3-32 32zm128 0a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zM320 256a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm32-80c-32.8 0-61 19.7-73.3 48L32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l246.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48l54.7 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-54.7 0c-12.3-28.3-40.5-48-73.3-48zM192 128a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm73.3-64C253 35.7 224.8 16 192 16s-61 19.7-73.3 48L32 64C14.3 64 0 78.3 0 96s14.3 32 32 32l86.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 128c17.7 0 32-14.3 32-32s-14.3-32-32-32L265.3 64z"/>
                </svg>
            </button>
            <?php if($filters): ?>
                <a href="<?= strtok($currentURL, '?');?>" id="removeFilter" class="transition-all duration-500 hover:scale-110 hover:opacity-75 tooltip" title="Clear Filters">
                    <svg class="h-7 fill-dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/>
                    </svg>
                </a>
            <?php endif;?>
        </div>
    </div>

    <div class="grow w-full flex flex-col gap-2">
        <table id="resultsTable">
            <thead>
                <tr>
                    <th>
                        <button class="flex gap-1.5 items-center">
                            <span>Date</span>
                            <svg class="h-4 fill-dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 231c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-71-71V376c0 13.3-10.7 24-24 24s-24-10.7-24-24V193.9l-71 71c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9L239 119c9.4-9.4 24.6-9.4 33.9 0L385 231z"/>
                            </svg>
                        </button>
                    </th>
                    <th>
                        <button class="flex gap-1.5 items-center">
                            <span>Name</span>
                        </button>
                    </th>
                    <th>Assay</th>
                    <th>Result</th>
                    <th></th>
                </tr>
            </thead>
    
            <tbody>
                <?php foreach($resultItems as $result) : ?>
                    <?php // We need to parse the result sensibly
                        // TODO: The way we present this is to be determined. For now, we just truncate.
                        $positive = (strpos(strtolower($result['result']), "positive"));
                    ;?>
                    <tr id="result<?= $result['id'];?>" class="result">
                        <td><?= (new DateTime($result['testcompletetimestamp']))->format($hospitalInfoArray['date_format']);?></td>
                        <td><?= $result['firstName'];?> <?= $result['lastName'];?></td>
                        <td><?= $result['product'];?></td>
                        <td class="text-elipses"><?= truncate($result['result'], 30);?></td>
                        <td>
                            <div class="h-full flex items-center gap-1.5 justify-end">
                                <!-- Statuses and chevron -->
                                <?php if($positive) : ?>
                                <button class="flex items-center tooltip" title="Positive Result">
                                    <svg class="h-5 fill-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                        <path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/>
                                    </svg>
                                </button>
                                <?php endif;?>
                                <button id="sendResult<?= $result['id'];?>" class="flex items-center">
                                <?php 
                                // Set to green (sent) 102
                                // Set to amber (sending) 101
                                // Set to red (not sent) 100
                                ;?>
                                    <div class="status-indicator active tooltip" title="Sent to LIMS"></div>
                                </button>
                                <button class="relative h-5 w-auto transition-all duration-500 hover:scale-110 tooltip" title="View Details">
                                    <svg class="h-5 fill-dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                        <path d="M288 80c-65.2 0-118.8 29.6-159.9 67.7C89.6 183.5 63 226 49.4 256c13.6 30 40.2 72.5 78.6 108.3C169.2 402.4 222.8 432 288 432s118.8-29.6 159.9-67.7C486.4 328.5 513 286 526.6 256c-13.6-30-40.2-72.5-78.6-108.3C406.8 109.6 353.2 80 288 80zM95.4 112.6C142.5 68.8 207.2 32 288 32s145.5 36.8 192.6 80.6c46.8 43.5 78.1 95.4 93 131.1c3.3 7.9 3.3 16.7 0 24.6c-14.9 35.7-46.2 87.7-93 131.1C433.5 443.2 368.8 480 288 480s-145.5-36.8-192.6-80.6C48.6 356 17.3 304 2.5 268.3c-3.3-7.9-3.3-16.7 0-24.6C17.3 208 48.6 156 95.4 112.6zM288 336c44.2 0 80-35.8 80-80s-35.8-80-80-80c-.7 0-1.3 0-2 0c1.3 5.1 2 10.5 2 16c0 35.3-28.7 64-64 64c-5.5 0-10.9-.7-16-2c0 .7 0 1.3 0 2c0 44.2 35.8 80 80 80zm0-208a128 128 0 1 1 0 256 128 128 0 1 1 0-256z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
    <div class="w-full rounded-xl bg-tirtiary/50 p-4 flex items-center justify-center">
        <h1 class="mb-0">THERE WILL BE PAGINATION</h1>
    </div>

    <?php foreach($resultItems as $result) : ?>
        <?php 
        try {
            $dob = (new DateTime($result['dob']))->format($hospitalInfoArray['date_format']);
            // Further processing with $datetime
        } catch (Exception $e) {
            $dob = "Undefined";
        }    
        ;?>
        <div id="result<?= $result['id'];?>Modal" class="result-modal">
            <div class="result-modal-backdrop">
                <div class="relative result-details bg-primary shadow-xl shadow-dark flex flex-col rounded-xl max-w-[40rem] max-h-[calc(min(40rem,_90vh))] h-full w-full m-8 p-8 overflow-y-scroll">
                    <button class="modal-close absolute top-0 right-0 p-4 transition-all duration-500 hover:scale-110">
                        <svg class="h-8 fill-dark pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                            <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                        </svg>
                    </button>
                    <h2 class="mb-2"><?= (new DateTime($result['testcompletetimestamp']))->format($hospitalInfoArray['date_format']);?> - <?= $result['firstName'];?> <?= $result['lastName'];?></h2>
                    <table class="result-explosion">
                        <tr>
                            <td>Patient ID</td>
                            <td><?= $result['patientId'];?></td>
                        </tr>
                        <tr>
                            <td>Name</td>
                            <td><?= $result['firstName'];?> <?= $result['lastName'];?></td>
                        </tr>
                        <tr>
                            <td>Results</td>
                            <td><?= $result['result'];?></td>
                        </tr>
                        <tr>
                            <td>Date of Birth</td>
                            <td><?= $dob;?></td>
                        </tr>
                        <tr>
                            <td>Gender</td>
                            <td><?= $result['patientSex'];?></td>
                        </tr>
                        <tr>
                            <td>Sample Date</td>
                            <td><?= $result['patientId'];?></td>
                        </tr>
                        <tr>
                            <td>Operator</td>
                            <td><?= $result['patientId'];?></td>
                        </tr>
                        <tr>
                            <td>Test Started</td>
                            <td><?= $result['patientId'];?></td>
                        </tr>
                        <tr>
                            <td>Test Finished</td>
                            <td><?= $result['patientId'];?></td>
                        </tr>
                    </table>

                    <!-- Graph! -->
                    <div class="w-full h-16 bg-tirtiary/50 p-4 rounded-xl flex items-center justify-center">
                        <h3 class="mb-0 text-dark">THERE WILL BE A GRAPH</h3>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach;?>