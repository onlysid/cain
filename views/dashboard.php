<?php // Results Page
$hospitalInfo = systemInfo();
$hospitalInfoArray = array_column($hospitalInfo, 'value', 'name');

// See what kind of user we are
$serviceEngineer = $currentUser['user_type'] == '3';

// Do we have any filters?
$filters = $_GET ?? $session->get('result-filters');
$page = $filters['p'] ?? 1;
$itemsPerPage = $filters['ipp'] ?? 10;

// Get all the data
$results = getResults($_GET ?? $session->get('result-filters'), $itemsPerPage, false);
$resultItems = $results["results"] ?? null;

$totalResultsCount = $results["count"] ?? 0;

// Get the pagination information
$totalPageCount = ceil($totalResultsCount / $itemsPerPage);
$firstItemIndex = ceil(($itemsPerPage * $page) - $itemsPerPage + 1);
$lastItemIndex = (($page * $itemsPerPage < $totalResultsCount) ? ($firstItemIndex + ($itemsPerPage - 1)) : ($totalResultsCount));

$resultNumberText = (($totalResultsCount > $itemsPerPage) ? ($firstItemIndex . "-" . $lastItemIndex .  " of ") : "") . $totalResultsCount . " Result" . ($totalResultsCount == 1 ? "" : "s");

// Extract 'name' as keys and 'value' as values
$settings = array_column($hospitalInfo, 'value', 'name');

// Field Items
require_once 'utils/DataField.php';

// Get the bitmaps so we can use them to display the current DB values
$visibilityFields = getSettingsBitmap(count($dataFields), 3, $fieldInfo['field_behaviour']);

// Fields we are interested in
$listableFields = getFieldVisibilitySettings($dataFields, $visibilityFields);

// What field do we use to ID patient?
$defaultIdField = $settings['default_id'];

// Column name definition
if($defaultIdField == 'patient_id') {
    $idColName = "Patient ID";
}

if($defaultIdField == 'nhsNumber') {
    $idColName = "NHS #";
}

if($defaultIdField == 'hospitalId') {
    $idColName = "Hospital ID";
}

if(!isset($idColName)) {
    $defaultIdField = 'patient_id';
    $idColName = "Patient ID";
}

unset($filters['p']);

foreach ($filters as $key => $value) {
    if ($value === "") {
        unset($filters[$key]);
    }
}

// We need to determine if the result has been sent to LIMS. If we aren't connected to LIMS we don't need to display anything (unless it is flagged as sent)
$limsConnection = $settings['app_mode'] == 1 ? true : false;

// We need to include age groups for our dropdown filter
include_once BASE_DIR . "/utils/AgeGroup.php";?>

<script src="js/chartJs/dist/chart.umd.js"></script>
<link href="/js/vanilla-calendar/build/vanilla-calendar.min.css" rel="stylesheet">
<script src="/js/vanilla-calendar/build/vanilla-calendar.min.js" defer></script>
<script src="/js/dateRangePicker.js" defer></script>

<h1 class="mb-0 md:mb-2">Results</h1>

<div id="tableInfoWrapper" class="w-full flex justify-between items-center gap-2">
    <p class="results-number hidden sm:block"><?= $resultNumberText;?></p>
    <div id="filterSearchWrapper" class="flex items-center flex-col-reverse sm:flex-row w-full sm:w-auto justify-end">
        <form id="fakeSearchForm" class="w-full sm:w-auto">
            <div class="form-fields">
                <div class="field">
                    <div class="input-wrapper !py-1 !pr-1">
                        <input id="fakeSearch" type="text" placeholder="Search..." name="s" value="<?= $filters['s'] ?? "";?>">
                        <button id="fakeSearchBtn" class="cursor-pointer aspect-square rounded-full p-2 bg-dark transition-all duration-500 hover:scale-110 hover:opacity-75">
                            <svg class="h-4 w-auto fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <div class="flex items-center w-full sm:w-auto">
            <p class="grow sm:hidden text-base md:text-lg"><?= $resultNumberText;?></p>
            <button id="filter" data-modal-open="filterModal" class="p-2 transition-all duration-500 hover:scale-110 hover:opacity-75 tooltip" title="Filters & Actions">
                <svg class="h-7 fill-dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M0 416c0 17.7 14.3 32 32 32l54.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 448c17.7 0 32-14.3 32-32s-14.3-32-32-32l-246.7 0c-12.3-28.3-40.5-48-73.3-48s-61 19.7-73.3 48L32 384c-17.7 0-32 14.3-32 32zm128 0a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zM320 256a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm32-80c-32.8 0-61 19.7-73.3 48L32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l246.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48l54.7 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-54.7 0c-12.3-28.3-40.5-48-73.3-48zM192 128a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm73.3-64C253 35.7 224.8 16 192 16s-61 19.7-73.3 48L32 64C14.3 64 0 78.3 0 96s14.3 32 32 32l86.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 128c17.7 0 32-14.3 32-32s-14.3-32-32-32L265.3 64z"/>
                </svg>
            </button>
            <button onclick="window.location.reload()" class="p-2 transition-all duration-500 hover:scale-110 hover:opacity-75 tooltip" title="Refresh">
                <svg class="h-7 fill-dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M105.1 202.6c7.7-21.8 20.2-42.3 37.8-59.8c62.5-62.5 163.8-62.5 226.3 0L386.3 160H352c-17.7 0-32 14.3-32 32s14.3 32 32 32H463.5c0 0 0 0 0 0h.4c17.7 0 32-14.3 32-32V80c0-17.7-14.3-32-32-32s-32 14.3-32 32v35.2L414.4 97.6c-87.5-87.5-229.3-87.5-316.8 0C73.2 122 55.6 150.7 44.8 181.4c-5.9 16.7 2.9 34.9 19.5 40.8s34.9-2.9 40.8-19.5zM39 289.3c-5 1.5-9.8 4.2-13.7 8.2c-4 4-6.7 8.8-8.1 14c-.3 1.2-.6 2.5-.8 3.8c-.3 1.7-.4 3.4-.4 5.1V432c0 17.7 14.3 32 32 32s32-14.3 32-32V396.9l17.6 17.5 0 0c87.5 87.4 229.3 87.4 316.7 0c24.4-24.4 42.1-53.1 52.9-83.7c5.9-16.7-2.9-34.9-19.5-40.8s-34.9 2.9-40.8 19.5c-7.7 21.8-20.2 42.3-37.8 59.8c-62.5 62.5-163.8 62.5-226.3 0l-.1-.1L125.6 352H160c17.7 0 32-14.3 32-32s-14.3-32-32-32H48.4c-1.6 0-3.2 .1-4.8 .3s-3.1 .5-4.6 1z"/>
                </svg>
            </button>
            <?php if($filters): ?>
                <a href="<?= strtok($currentURL, '?');?>" id="removeFilter" class="p-2 transition-all duration-500 hover:scale-110 hover:opacity-75 tooltip" title="Clear Filters">
                    <svg class="h-7 fill-dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/>
                    </svg>
                </a>
            <?php endif;?>
        </div>
    </div>
</div>

<?php if($totalResultsCount) : ?>
    <div class="grow w-full flex flex-col gap-2">
        <table id="resultsTable">
            <thead>
                <tr>
                    <th>
                        <a href="<?= updateQueryString(["sp" => "", "sd" => ((($filters['sd'] ?? "desc") == "desc" && ($filters['sp'] ?? null) == "") || ($filters['sd'] ?? "empty") == "" ? "asc" : "")], true);?>" class="ignore-default flex gap-1.5 items-center">
                            <span>Date</span>
                            <svg class="h-4 fill-dark shrink-0 <?= (!isset($filters['sp']) || $filters['sp'] == "") ? "" : "opacity-50 !rotate-180";?> <?= (!isset($filters['sd']) || $filters['sd'] == '') ? "rotate-180" : "" ;?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 231c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-71-71V376c0 13.3-10.7 24-24 24s-24-10.7-24-24V193.9l-71 71c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9L239 119c9.4-9.4 24.6-9.4 33.9 0L385 231z"/>
                            </svg>
                        </a>
                    </th>
                    <th>
                        <a href="<?= updateQueryString(["sp" => "first_name", "sd" => ((($filters['sd'] ?? "desc") == "desc" && ($filters['sp'] ?? null) == "first_name") || ($filters['sd'] ?? "desc") == "" ? "asc" : "")], true);?>" class="ignore-default flex gap-1.5 items-center">
                            <span>Name</span>
                            <svg class="h-4 fill-dark shrink-0 <?= (isset($filters['sp']) && $filters['sp'] == "first_name") ? "" : "opacity-50 !rotate-180";?> <?= (!isset($filters['sd']) || $filters['sd'] == '') ? "rotate-180" : "" ;?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 231c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-71-71V376c0 13.3-10.7 24-24 24s-24-10.7-24-24V193.9l-71 71c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9L239 119c9.4-9.4 24.6-9.4 33.9 0L385 231z"/>
                            </svg>
                        </a>
                    </th>
                    <th>
                        <a href="<?= updateQueryString(["sp" => $defaultIdField, "sd" => ((($filters['sd'] ?? "desc") == "desc" && ($filters['sp'] ?? null) == $defaultIdField) || ($filters['sd'] ?? "desc") == "" ? "asc" : "")], true);?>" class="ignore-default flex gap-1.5 items-center">
                            <span><?= $idColName;?></span>
                            <svg class="h-4 fill-dark shrink-0 <?= (isset($filters['sp']) && $filters['sp'] == $defaultIdField) ? "" : "opacity-50 !rotate-180";?> <?= (!isset($filters['sd']) || $filters['sd'] == '') ? "rotate-180" : "" ;?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 231c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-71-71V376c0 13.3-10.7 24-24 24s-24-10.7-24-24V193.9l-71 71c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9L239 119c9.4-9.4 24.6-9.4 33.9 0L385 231z"/>
                            </svg>
                        </a>
                    </th>
                    <th class="hidden md:table-cell">
                        <a href="<?= updateQueryString(["sp" => "assay_name", "sd" => ((($filters['sd'] ?? "desc") == "desc" && ($filters['sp'] ?? null) == "assay_name") || ($filters['sd'] ?? "desc") == "" ? "asc" : "")], true);?>" class="ignore-default flex gap-1.5 items-center">
                            <span>Test</span>
                            <svg class="h-4 fill-dark shrink-0 <?= (isset($filters['sp']) && $filters['sp'] == "assay_name") ? "" : "opacity-50 !rotate-180";?> <?= (!isset($filters['sd']) || $filters['sd'] == '') ? "rotate-180" : "" ;?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 231c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-71-71V376c0 13.3-10.7 24-24 24s-24-10.7-24-24V193.9l-71 71c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9L239 119c9.4-9.4 24.6-9.4 33.9 0L385 231z"/>
                            </svg>
                        </a>
                    </th>
                    <th class="hidden md:table-cell">
                        <a href="<?= updateQueryString(["sp" => "result", "sd" => ((($filters['sd'] ?? "desc") == "desc" && ($filters['sp'] ?? null) == "result") || ($filters['sd'] ?? "desc") == "" ? "asc" : "")], true);?>" class="ignore-default flex gap-1.5 items-center">
                            <span>Result</span>
                            <svg class="h-4 fill-dark shrink-0 <?= (isset($filters['sp']) && $filters['sp'] == "result") ? "" : "opacity-50 !rotate-180";?> <?= (!isset($filters['sd']) || $filters['sd'] == '') ? "rotate-180" : "" ;?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 231c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-71-71V376c0 13.3-10.7 24-24 24s-24-10.7-24-24V193.9l-71 71c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9L239 119c9.4-9.4 24.6-9.4 33.9 0L385 231z"/>
                            </svg>
                        </a>
                    </th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php foreach($resultItems as $result) : ?>
                    <?php // We need to parse the result sensibly
                        $resultInfo = sanitiseResult($result['result']);

                        // We should also just check the given result summary. If there is any reference to "positive" in it, we should give the summary as "positive".
                        if(strpos(strtolower($result['result_summary'] ?? ""), "invalid") !== false) {
                            $summary = "Invalid";
                            $summaryText = "Invalid";
                        } else {
                            $summary = strpos(strtolower($result['result_summary'] ?? ""), "positive") !== false ? "Positive" : "Negative";
                            $summaryText = $result['result_summary'];
                        }
                    ;?>
                    <tr id="result<?= $result['master_result_id'];?>" class="result">
                        <td><?= $result['end_time'] ? (new DateTime($result['end_time']))->format($hospitalInfoArray['date_format']) : 'Unknown';?></td>
                        <td>
                            <?php if($serviceEngineer) : ?>
                                -REDACTED-
                            <?php else : ?>
                                <?php if($result['first_name'] || $result['last_name']) : ?>
                                    <?= $result['first_name'];?> <?= $result['last_name'];?>
                                <?php else : ?>
                                    -
                                <?php endif;?>
                            <?php endif;?>
                        </td>
                        <td>
                            <?php if($serviceEngineer) : ?>
                                -REDACTED-
                            <?php else : ?>
                                <?php if($result[$defaultIdField]) : ?>
                                    <?= $result[$defaultIdField];?>
                                <?php else : ?>
                                    -
                                <?php endif;?>
                            <?php endif;?>
                        </td>
                        <td class="hidden md:table-cell"><?= $result['assay_name'];?></td>
                            <?php if($serviceEngineer) : ?>
                                <td>-REDACTED-</td>
                            <?php else : ?>
                                <td class="text-elipses hidden md:table-cell <?= $summary == 'Positive' ? "active" : (!$summary || $summary == 'Invalid' ? 'invalid' : "");?>"><?= ucfirst($summaryText) ?? "Invalid";?></td>
                            <?php endif;?>
                        <td>
                            <div class="h-full flex items-center gap-1.5 justify-end">
                                <!-- Statuses and chevron -->
                                <?php if($result && $summary == 'Positive') : ?>
                                    <button class="flex items-center tooltip" title="Positive Result">
                                        <svg class="h-5 fill-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                            <path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/>
                                        </svg>
                                    </button>
                                <?php endif;?>
                                <button id="sendResult<?= $result['master_result_id'];?>" class="flex items-center">
                                <?php
                                // Determine the symbol (if any) to display
                                $limsStatus = false;
                                $limsStatusMessage = "Not sent to LIMS";

                                if($limsConnection) {
                                    $limsStatus = 'unsent';
                                }

                                if($result['lims_status'] == 2) {
                                    $limsStatus = 'active';
                                    $limsStatusMessage = "Sent to LIMS";
                                } else if($result['lims_status'] == 1 && $limsStatus) {
                                    $limsStatus = 'pending';
                                    $limsStatusMessage = "Sending to LIMS";
                                }

                                if($limsStatus !== false) : ?>
                                    <div class="status-indicator <?= $limsStatus;?> tooltip" title="<?= $limsStatusMessage;?>"></div>
                                <?php endif;?>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
<?php // No results found
else : ?>
    <div class="grow w-full flex items-center justify-center">
        <div class="flex justify-center items-center p-8 rounded-lg bg-white max-w-3xl">
            <h2>No results found. Please refine your search.</h2>
        </div>
    </div>
<?php endif;

// Pagination
if($totalPageCount > 1) : ?>

    <div id="pagination">
        <a href="<?= $page == 1 ? "#" : updateQueryString(["p" => 1]);?>" class="<?= $page == 1 ? "disabled" : "";?> !px-2 xs:!hidden"><<</a>
        <a href="<?= $page == 1 ? "#" : updateQueryString(["p" => $page - 1]);?>" class="<?= $page == 1 ? "disabled" : "";?>"><</a>
        <?php if($page - 1 > 1) : ?>
            <a class="inner-pagination" href="<?= updateQueryString(["p" => 1]);?>">1</a>
            <svg class="fill-dark inner-pagination h-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                <path d="M8 256a56 56 0 1 1 112 0A56 56 0 1 1 8 256zm160 0a56 56 0 1 1 112 0 56 56 0 1 1 -112 0zm216-56a56 56 0 1 1 0 112 56 56 0 1 1 0-112z"/>
            </svg>
        <?php endif;
        for($i = (max($page - 1, 1)); $i <= min($totalPageCount, $page + 1); $i++) : ?>
            <a class="inner-pagination <?= $page == $i ? "active" : "";?>" href="<?= updateQueryString(["p" => $i]);?>"><?= $i;?></a>
        <?php endfor;
        if($page + 1 < $totalPageCount) : ?>
            <svg class="fill-dark inner-pagination h-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                <path d="M8 256a56 56 0 1 1 112 0A56 56 0 1 1 8 256zm160 0a56 56 0 1 1 112 0 56 56 0 1 1 -112 0zm216-56a56 56 0 1 1 0 112 56 56 0 1 1 0-112z"/>
            </svg>
            <a class="inner-pagination" href="<?= updateQueryString(["p" => $totalPageCount]);?>"><?= $totalPageCount;?></a>
        <?php endif;?>
        <a href="<?= $page == $totalPageCount ? "#" : updateQueryString(["p" => $page + 1]);?>" class="<?= $page == $totalPageCount ? "disabled" : "";?>">></a>
        <a href="<?= $page == $totalPageCount ? "#" : updateQueryString(["p" => $totalPageCount]);?>" class="<?= $page == $totalPageCount ? "disabled" : "";?> !px-2 xs:!hidden">>></a>
    </div>

<?php endif;

// Results modals
if($resultItems) {
    foreach($resultItems as $result) : ?>

        <?php
        if (!empty($result['dob'])) {
            try {
                $dob = (new DateTime($result['dob']))->format($hospitalInfoArray['date_format']);
            } catch (Exception $e) {
                $dob = "Undefined";
            }
        } else {
            $dob = "Undefined";
        }

        // Result info
        $resultInfo = sanitiseResult($result['result']);

        // Get the master result information
        $resultSummary = $result['result_summary'];
        $resultAssayName = $result['assay_name'];

        // Get the individual result information (which may be the same!)
        $individualResults = explode(';', $result['result_values'] ?? "");
        $individualAssayNames = explode(';', $result['assay_names'] ?? "");
        $individualCTValues = explode(';', $result['ct_values'] ?? "");
        $individualStatusFlags = explode(';', $result['result_flags'] ?? "");
        $individualResultsProcessed = [];
        $individualAssayNamesProcessed = [];

        // We can do some further manupulation to ensure that each result and each name actually breaks up into its product information
        foreach($individualResults as $key => $individualResult) {
            $individualResultsProcessed[$key] = explode(', ', $individualResult);
        }

        foreach($individualAssayNames as $key => $individualAssayName) {
            $individualAssayNamesProcessed[$key] = explode(', ', $individualAssayName);
        }
        ?>
        <div id="result<?= $result['master_result_id'];?>Modal" class="result-modal">
            <div class="result-modal-backdrop">
                <div class="result-details relative bg-primary shadow-xl shadow-dark flex flex-col rounded-xl max-w-[min(50rem,95vw)] max-h-[calc(min(45rem,_90vh))] h-full w-full mt-4 lg:mt-8 p-4 lg:p-8 overflow-y-scroll">
                    <button class="modal-close absolute top-2 right-2 p-2 transition-all duration-500 hover:scale-110">
                        <svg class="h-8 fill-dark pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                            <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                        </svg>
                    </button>

                    <h2 class="flex text-center items-center gap-2.5 sm:text-start mx-12 sm:mx-0 sm:mr-12 mb-2.5 sm:mb-1">
                        <?= convertTimestamp($result['end_time'] ?? $result['end_time'], true);?>: <?= $serviceEngineer ? '-REDACTED-' : $result['first_name'] ?? 'Unknown';?> <?= !$serviceEngineer ? $result['last_name'] : '';?>
                        <?php // Determine the symbol (if any) to display
                        $limsStatus = false;
                        $limsStatusMessage = "Not sent to LIMS";

                        if($limsConnection) {
                            $limsStatus = 'unsent';
                        }

                        if($result['lims_status'] == 2) {
                            $limsStatus = 'active';
                            $limsStatusMessage = "Sent to LIMS";
                        } else if($result['lims_status'] == 1 && $limsStatus) {
                            $limsStatus = 'pending';
                            $limsStatusMessage = "Sending to LIMS";
                        }

                        if($limsStatus !== false) : ?>
                            <div class="status-indicator <?= $limsStatus;?> tooltip" title="<?= $limsStatusMessage;?>"><span class="hidden"> (<?= $limsStatusMessage;?>)</span></div>
                        <?php endif;?>
                    </h2>

                    <div class="bg-gradient-to-r from-transparent via-grey/75 sm:from-grey/75 to-transparent w-full mb-3 pb-0.5 rounded-full"></div>

                    <!-- Overall result!! -->
                    <div class="grid gap-2 mb-3 grid-cols-[repeat(auto-fit,minmax(200px,1fr))]">
                        <div class="result-info <?= $resultInfo['result'] ? 'pos' : '';?>">
                            <h4 class="underline">Result Summary:</h4>
                            <p><b><?= $resultAssayName;?>:</b></p>
                            <p><?= $resultSummary;?></p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-l from-transparent via-grey/75 sm:from-grey/75 to-transparent w-full mb-3 pb-0.5 rounded-full"></div>

                    <?php // We only need to display this data if the test is complex
                    if((gettype($resultInfo["result"]) !== "boolean" && $resultInfo["result"] !== null) || count($individualResultsProcessed) > 1) : ?>
                        <div class="w-full flex items-stretch justify-center gap-3 flex-wrap">

                            <?php // Show the results
                            foreach($individualResultsProcessed as $key => $res) : ?>

                                <?php // We should loop through the individual results and display them one by one
                                $ctValues = explode(",", $individualCTValues[$key] ?? "");
                                $resultInterpretation = resultInterpretation($res);
                                $resultClass = '';
                                $resultString = "Negative";

                                switch($resultInterpretation) {
                                    case 0:
                                        $resultClass = 'invalid';
                                        $resultString = "Invalid";
                                        break;
                                    case 1:
                                        $resultClass = 'pos';
                                        $resultString = "Positive";
                                        break;
                                    default:
                                        $resultClass = 'neg';
                                }?>

                                <div id="<?= $result['master_result_id'];?>Tab<?= $key;?>Content" class="result-info max-w-[20rem] relative !p-2 <?= $resultClass;?>">

                                    <?php // Show the individual status messages
                                    $individualLimsStatus = false;
                                    $individualLimsStatusMessage = "Not sent to LIMS";

                                    if($limsConnection) {
                                        $individualLimsStatus = 'unsent';
                                    }

                                    if($individualStatusFlags[$key] == 102) {
                                        $individualLimsStatus = 'active';
                                        $individualLimsStatusMessage = "Sent to LIMS";
                                    } else if($individualStatusFlags[$key] == 101 && $individualLimsStatus) {
                                        $individualLimsStatus = 'pending';
                                        $individualLimsStatusMessage = "Sending to LIMS";
                                    }?>
                                    <h4 class="px-2.5 title border-2 border-transparent py-0.5 flex items-center gap-2 bg-white !text-dark rounded-md w-full">
                                        <?php if($individualLimsStatus) : ?>
                                            <div class="status-indicator border-2 <?= $individualLimsStatus;?> tooltip" title="<?= $individualLimsStatusMessage;?>"><span class="hidden"> (<?= $individualLimsStatusMessage;?>)</span></div>
                                        <?php endif;?>
                                        <?= $individualAssayNames[$key];?>
                                    </h4>
                                    <h4 class="text-center my-2"><?= $resultString;?></h4>
                                    <?php if($settings['visible_ct'] == "1") : ?>
                                        <div class="result-accordion border-2 border-transparent w-full transition-all duration-1000 bg-white px-1.5 py-0.5 rounded-md">
                                            <button class="w-full text-start underline result-accordion-button">More details</button>
                                            <div class="result-accordion-content">
                                                <?php foreach($res as $key2 => $individualResult) : ?>

                                                    <?php // Get the individual result information
                                                    $individualResultExplosion = explode(": ", $individualResult);

                                                    // Display the individual result
                                                    if(count($individualResultExplosion) > 1) : ?>
                                                        <p class="<?= ($resultString = substr($individualResultExplosion[1], 1)) == "Positive" ? 'font-bold text-red-500' : '';?> pb-0 !text-base !text-dark"><b><?= $individualResultExplosion[0];?></b>: <?= $resultString;?></p>
                                                    <?php else : ?>
                                                        <p class="<?= $individualResultExplosion[0] == "Positive" ? 'font-bold text-red-500' : '';?> !text-base !text-dark"><?= $individualResult;?></p>
                                                    <?php endif;

                                                    // If the CT Value is not "x", it means we received one.
                                                    if(isset($ctValues[$key2]) && $ctValues[$key2] !== null && $ctValues[$key2] !== "" && $ctValues[$key2] !== "x") : ?>
                                                        <p class="!text-sm !text-dark">ct: <?= $ctValues[$key2];?></p>
                                                    <?php endif;?>
                                                    </p>

                                                <?php endforeach;?>
                                            </div>
                                        </div>
                                    <?php endif;?>

                                </div>
                                <?php endforeach;?>
                            </div>

                            <div class="bg-gradient-to-r from-transparent via-grey/75 sm:from-grey/75 to-transparent w-full my-3 pb-0.5 rounded-full"></div>
                    <?php endif;?>

                    <table class="result-explosion">
                        <?php foreach($listableFields as $keyset => $value) :
                            $keys = explode(" ", $keyset);
                            if($result[$keys[0]]) : ?>
                                <tr>
                                    <td><?= $value;?></td>
                                    <td>
                                        <?php foreach($keys as $key) : ?>
                                            <?= $serviceEngineer ? '-REDACTED-' : $result[$key];?>
                                        <?php endforeach;?>
                                    </td>
                                </tr>
                            <?php endif;
                        endforeach;?>
                    </table>

                    <div class="w-full items-center justify-center -mt-2 mb-2">
                        <p class="text-xs w-full text-center">Result ID: <?= $result['master_result_id'];?></p>
                    </div>
                </div>
                <script>
                    // A simple printing function
                    function PrintElem(elem, title) {
                        var mywindow = window.open('', 'PRINT');

                        mywindow.document.write('<html><head><title>' + title  + '</title>');
                        mywindow.document.write('<link href="/css/output.css" rel="stylesheet">');
                        mywindow.document.write('</head><body >');
                        mywindow.document.write(document.querySelector("#result" + elem + "Modal .result-details").innerHTML);
                        mywindow.document.querySelector('.modal-close').remove();
                        mywindow.document.write('</body></html>');

                        mywindow.document.close(); // necessary for IE >= 10
                        mywindow.focus(); // necessary for IE >= 10*/

                        mywindow.print();
                        mywindow.close();

                        return true;
                    }
                </script>

                <?php if($currentUser['user_type'] >= ADMINISTRATIVE_CLINICIAN) : ?>
                    <div class="shrink-0 relative result-actions mt-1.5 bg-black gap-0.5 shadow-xl shadow-dark flex rounded-2xl max-w-[min(15rem,95vw)] overflow-hidden mb-4 lg:mb-8">
                        <button onclick="PrintElem('<?= $result['master_result_id'];?>', <?= $result['master_result_id'];?>)" class="bg-gradient-to-r from-yellow-300 to-yellow-200 grow-1 px-4 py-1.5 text-black transition-all duration-500 hover:bg-fuchsia-200 hover:saturate-50 hover:scale-105 tooltip" title="Print details">Print</button>
                        <button data-id="<?= $result['master_result_id'];?>" class="delete-result bg-gradient-to-r from-red-900 to-red-600 grow-1 px-4 py-1.5 text-white transition-all duration-500 hover:bg-fuchsia-200 hover:saturate-50 hover:scale-105 tooltip" title="Delete">Delete</button>
                    </div>
                <?php else : ?>
                    <div class="result-actions mt-1.5 shadow-xl shadow-dark flex rounded-2xl max-w-[min(15rem,95vw)] overflow-hidden mb-4 lg:mb-8">
                        <button onclick="PrintElem('<?= $result['master_result_id'];?>', <?= $result['master_result_id'];?>)" class="bg-gradient-to-r from-yellow-300 to-yellow-200 grow-1 px-4 py-1.5 text-black transition-all duration-500 hover:bg-fuchsia-200 hover:saturate-50 hover:scale-105 tooltip" title="Print details">Print</button>
                    </div>
                <?php endif;?>
            </div>
        </div>
    <?php endforeach;
}

if($currentUser['user_type'] >= ADMINISTRATIVE_CLINICIAN) : ?>
    <div id="genericModalWrapper" class="modal-wrapper">
        <div class="overlay" data-modal-close></div>

        <div id="deleteResultModal" class="generic-modal">
            <div class="close-modal">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
                </svg>
            </div>
            <svg class="fill-red-500 h-10 w-auto mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24V296c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/>
            </svg>
            <p class="text-center">Are you sure you want to delete result #<span id="resultToDelete" class="font-black text-red-500"></span>?</p>
            <form action="/process" method="POST">
                <input type="hidden" name="action" value="delete-result">
                <input type="hidden" name="return-path" value="<?= $currentURL;?>">
                <input type="hidden" name="id" class="form-result-id" value="">
                <div class="w-full flex justify-center items-center gap-3 mt-3">
                    <button type="submit" class="btn smaller-btn trigger-loading">Yes</button>
                    <div class="cursor-pointer btn smaller-btn close-modal no-styles" data-modal-close>Cancel</div>
                </div>
            </form>
        </div>
    </div>
<?php endif;?>

<div id="filterModalWrapper" class="modal-wrapper">
    <div id="filterModal" class="generic-modal">
        <div class="close-modal" data-modal-close>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
            </svg>
        </div>
        <h2 class="flex items-center">Filters
            <?php if($filters): ?>
                <a href="<?= strtok($currentURL, '?');?>" id="removeFilter" class="p-2 transition-all duration-500 hover:scale-110 hover:opacity-75 tooltip" title="Clear Filters">
                    <svg class="h-7 fill-dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/>
                    </svg>
                </a>
            <?php endif;?>
        </h2>
        <div class="bg-gradient-to-r from-transparent via-grey/75 sm:from-grey/75 to-transparent w-full mb-3 pb-0.5 rounded-full"></div>
        <form action="/process" method="POST" id="filterForm" class="max-w-sm">
            <input type="hidden" name="action" value="filter-results">
            <input type="hidden" name="return-path" value="<?= $currentURL;?>">
            <input type="hidden" name="intrinsic-redirect" value="true">
            <div class="form-fields">
                <div class="field">
                    <label for="filterSearch">Search results</label>
                    <div class="input-wrapper !py-1 !pr-1">
                        <input id="filterSearch" type="text" placeholder="Search..." name="s" value="<?= $filters['s'] ?? "";?>">
                        <div class="aspect-square rounded-full p-2 bg-dark transition-all duration-500 hover:scale-110 hover:opacity-75">
                            <svg class="h-3 w-auto fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-fields">
                <label for="resultPolarity" class="field !flex-row toggle-field !px-6 py-2 rounded-full bg-white shadow-md">
                    <div class="flex flex-col w-full">
                        <div class="shrink">Positive Results</div>
                        <div class="description !text-xs text-grey mr-4">Show only positive results?</div>
                    </div>
                    <div class="checkbox-wrapper">
                        <input <?= isset($filters['r']) && $filters['r'] == 1 ? "checked" : "";?> class="tgl" name="resultPolarity" id="resultPolarity" type="checkbox">
                        <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="resultPolarity"><span></span></label>
                    </div>
                </label>
            </div>
            <div class="form-fields">
                <div class="field">
                    <label for="sex">Sex</label>
                    <div class="input-wrapper select-wrapper">
                        <select name="sex" id="sex">
                            <option value>Please Select</option>
                            <option <?= ($filters['g'] ?? null) == "M" ? "selected" : "";?> value="M">Male</option>
                            <option <?= ($filters['g'] ?? null) == "F" ? "selected" : "";?> value="F">Female</option>
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label for="ageGroup">Age Group</label>
                    <div class="input-wrapper select-wrapper">
                        <select name="ageGroup" id="ageGroup">
                            <option value>Please Select</option>
                            <?php foreach($ageGroups as $ageGroup) : ?>
                                <option <?= ($filters['a'] ?? null) == $ageGroup ? "selected" : "";?> value="<?= $ageGroup;?>"><?= $ageGroup;?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-fields <?= $settings['app_mode'] == 1 ? "" : "!hidden";?>">
                <div class="field">
                    <label for="sentToLIMS">Sent to LIMS?</label>
                    <div class="input-wrapper select-wrapper">
                        <select name="sentToLIMS" id="sentToLIMS">
                            <option value>Please Select</option>
                            <option <?= ($filters['l'] ?? null) == "100" ? "selected" : "";?> value="100">Not Sent</option>
                            <option <?= ($filters['l'] ?? null) == "101" ? "selected" : "";?> value="101">Pending</option>
                            <option <?= ($filters['l'] ?? null) == "102" ? "selected" : "";?> value="102">Sent</option>
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label for="defaultId">Default Patient ID?</label>
                    <div class="input-wrapper select-wrapper">
                        <select name="defaultId" id="defaultId">
                            <option <?= $defaultIdField == "patient_id" ? "selected" : "";?> value="patient_id">Patient ID</option>
                            <option <?= $defaultIdField == "hospital_id" ? "selected" : "";?> value="hospital_id">Hospital ID</option>
                            <option <?= $defaultIdField == "nhs_number" ? "selected" : "";?> value="nhs_number">NHS Number</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-fields">
                <div class="field">
                    <label for="filterDates">Select a date range to view results.</label>
                    <div class="input-wrapper">
                        <svg class="cursor-pointer" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                            <path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zm64 80v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm128 0v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H208c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H336zM64 400v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H208zm112 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H336c-8.8 0-16 7.2-16 16z"/>
                        </svg>
                        <input id="filterDates" name="filterDates" type="text" class="date-range-picker" placeholder="Choose Date Range" readonly value="<?= $filters['d'] ?? "";?>"/>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button id="filterSearchBtn" type="submit" class="grow btn smaller-btn">Apply</button>
                <div class="grow btn smaller-btn cursor-pointer close-modal no-styles" data-modal-close>Cancel</div>
            </div>
        </form>
    </div>
</div>