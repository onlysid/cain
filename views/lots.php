<?php // Lots Page

// Do we have any filters?
$filters = $_GET;
$page = $filters['p'] ?? 1;
$priority = $filters['priority'] ?? 'off';

$itemsPerPage = $filters['ipp'] ?? 10;

// Get all the data
$lots = getLots($_GET, $itemsPerPage);

$lotItems = $lots["lots"];
$totalLotCount = $lots["count"];

// Get the pagination information
$totalPageCount = ceil($totalLotCount / $itemsPerPage);
$firstItemIndex = ceil(($itemsPerPage * $page) - $itemsPerPage + 1);
$lastItemIndex = (($page * $itemsPerPage < $totalLotCount) ? ($firstItemIndex + ($itemsPerPage - 1)) : ($totalLotCount));

$lotNumberText = (($totalLotCount > $itemsPerPage) ? ($firstItemIndex . "-" . $lastItemIndex .  " of ") : "") . $totalLotCount . " Lot" . ($totalLotCount == 1 ? "" : "s");

unset($filters['p']);

foreach ($filters as $key => $value) {
    if ($value === "") {
        unset($filters[$key]);
    }
}

// Get the QC Policy
$hospitalInfo = systemInfo();

$qcPolicy = null;
$qcPositives = null;
$qcNegatives = null;
$qcPolicyName = "Off";
$qcDesc = "No QC tests are required.";

foreach($hospitalInfo as $setting) {
    if($setting['name'] === 'qc_policy') {
        $qcPolicy = $setting['value'];
    }

    if($setting['name'] === 'qc_positive_requirements') {
        $qcPositives = $setting['value'];
    }

    if($setting['name'] === 'qc_negative_requirements') {
        $qcNegatives = $setting['value'];
    }
}

if($qcPolicy == 1) {
    $qcPolicyName = "Automatic";
    $qcDesc = "QC will automatically pass once the number of successful positive and negative tests reach those defined below.";
}

if($qcPolicy == 2) {
    $qcPolicyName = "Manual";
    $qcDesc = "QC must be set as 'passed' or 'failed' below manually.";
}

// Page setup ?>

<link href="/js/vanilla-calendar/build/vanilla-calendar.min.css" rel="stylesheet">
<script src="/js/vanilla-calendar/build/vanilla-calendar.min.js" defer></script>
<script src="/js/dateRangePicker.js" defer></script>

<h1 class="mb-2">Lots</h1>

<section class="notice my-0">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>View lots and their details and verify QC statuses.</p>
</section>

<section class="p-4 rounded-lg bg-fuchsia-200 shadow-md">
    <h4 class="underline mb-1">QC Policy Information:</h4>
    <p class="text-base"><span class="font-black">QC Policy: </span><?= $qcPolicyName;?> (<?= $qcDesc;?>)</p>
    <?php if($qcPolicy) : ?>
        <p class="text-base"><span class="font-black">Number of Successful Positive Tests Required: </span><?= $qcPositives;?></p>
        <p class="text-base"><span class="font-black">Number of Successful Negative Tests Required: </span><?= $qcNegatives;?></p>
    <?php endif;?>

    <!-- QC Links -->
     <div class="flex gap-3 mt-2 items-center">
        <?php if($currentUser['user_type'] >= ADMINISTRATIVE_CLINICIAN) : ?>
            <a href="/settings/qc" class="btn btn-small w-max border-btn hover:!text-white">Change QC Policy</a>
        <?php endif;
        if($qcPolicy) : ?>
            <a href="/qc-results" class="btn btn-small w-max border-btn hover:!text-white">View and Verify QC Results</a>
        <?php endif;?>
     </div>
</section>

<section class="my-0 w-full flex justify-between items-center">
    <p class="results-number hidden sm:block"><?= $lotNumberText;?></p>

    <form class="w-full sm:w-auto flex-row justify-between" id="lotsFilterForm">

        <div class="form-fields !gap-0">
            <div class="field">
                <div class="input-wrapper !py-1 !pr-1">
                    <input id="search" type="text" placeholder="Search..." name="s" value="<?= $filters['s'] ?? "";?>">
                    <button id="searchBtn" class="cursor-pointer aspect-square rounded-full p-2 bg-dark transition-all duration-500 hover:scale-110 hover:opacity-75">
                        <svg class="h-4 w-auto fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
                        </svg>
                    </button>
                </div>
            </div>
            <?php if($qcPolicy) : ?>
                <label for="priority" class="tooltip !flex field ml-2 gap-2 border border-light !flex-row toggle-field pr-2 pl-4 py-2 rounded-full bg-white h-full" title="View lots with unverified QC only.">
                    <div class="flex flex-col w-full">
                        <div class="shrink">Unverified</div>
                    </div>
                    <div class="checkbox-wrapper">
                        <input class="tgl" name="priority" id="priority" type="checkbox" <?= ($priority == 'on') ? "checked" : "";?>>
                        <label class="toggle" data-tg-off="DISABLED" data-tg-on="ENABLED" for="priority"><span></span></label>
                    </div>
                </label>
                <?php if($filters) : ?>
                    <a href="<?= strtok($currentURL, '?');?>" id="removeFilter" class="p-2 transition-all duration-500 hover:scale-110 hover:opacity-75 tooltip" title="Clear Filters">
                        <svg class="h-7 fill-dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/>
                        </svg>
                    </a>
                <?php endif;
            endif;?>
        </div>
    </form>
</section>

<?php if($totalLotCount) : ?>
    <table id="priorityList">
        <thead>
            <th>Lot</th>
            <th>Last Updated</th>
            <th>Expiration Date</th>
            <?php if($qcPolicy) : ?>
                <th>QC Details</th>
            <?php else : ?>
                <th>Delivery Date</th>
            <?php endif;?>
            <th></th>
        </thead>

        <tbody>
            <?php foreach($lotItems as $lot) :
                $qcResult = 'Unverified';
                if($lot['qc_pass'] == 1) {
                    $qcResult = 'Pass';
                } elseif($lot['qc_pass'] == 2) {
                    $qcResult = 'Fail';
                }

                // Check expiration date
                if($lot['expiration_date']) {
                    $expired = checkExpiration($lot['expiration_date']);
                } else {
                    $expired = false;
                }?>
                <tr class="lot <?= $qcResult;?><?= $expired ? ' expired' : '';?>" data-modal-open="<?= $lot['id'];?>Modal">
                    <td><?= $lot['lot_number'];?></td>
                    <td><?= convertTimestamp($lot['last_updated'], true);?></td>
                    <td class="font-black expiration"><?= $lot['expiration_date'] ? date('m/y', strtotime($lot['expiration_date'])) : 'Unset';?></td>
                    <?php if($qcPolicy) : ?>
                        <td class="font-black <?= $qcResult == 'Fail' ? 'text-red-500' : '';?><?= $qcResult == 'Unverified' ? 'text-amber-500' : '';?><?= $qcResult == 'Pass' ? 'text-green-500' : '';?>">
                            <?= $qcResult;?>
                            <?php if($qcPolicy != 0 && $lot['failure_count'] == 0) : ?>
                                <p class="text-xs <?= $lot['positive_count'] >= $qcPositives ? 'text-green-500' : '';?>"><?= $lot['positive_count'];?> Positive Test<?= $lot['positive_count'] > 1 || $lot['positive_count'] == 0 ? 's' : '';?></p>
                                <p class="text-xs <?= $lot['negative_count'] >= $qcNegatives ? 'text-green-500' : '';?>"><?= $lot['negative_count'];?> Negative Test<?= $lot['negative_count'] > 1 || $lot['negative_count'] == 0 ? 's' : '';?></p>
                            <?php elseif($lot['failure_count'] != 0) :?>
                                <p class="text-xs text-red-500"><?= $lot['failure_count'];?> Failed Test<?= $lot['failure_count'] > 1 || $lot['failure_count'] == 0 ? 's' : '';?>!</p>
                            <?php endif;?>
                        </td>
                    <?php else : ?>
                        <td class="font-black"><?= convertTimestamp($lot['delivery_date']);?></td>
                    <?php endif;?>
                    <td class="end">
                        <div class="table-controls">
                            <?php if($qcPolicy && $qcResult == 'Unverified') : ?>
                                <button class="flex items-center w-6 h-auto tooltip" title="Unverified">
                                    <svg class="w-full fill-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480L40 480c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24l0 112c0 13.3 10.7 24 24 24s24-10.7 24-24l0-112c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/>
                                    </svg>
                                </button>
                            <?php endif;?>
                            <button class="details tooltip" title="View/Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152V424c0 48.6 39.4 88 88 88H360c48.6 0 88-39.4 88-88V312c0-13.3-10.7-24-24-24s-24 10.7-24 24V424c0 22.1-17.9 40-40 40H88c-22.1 0-40-17.9-40-40V152c0-22.1 17.9-40 40-40H200c13.3 0 24-10.7 24-24s-10.7-24-24-24H88z"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>

    <?php // Pagination
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

    // Individual data modals ?>
    <div class="modal-wrapper">
        <?php foreach($lotItems as $lot) :
            $qcResult = 'Unverified';
            if($lot['qc_pass'] == 1) {
                $qcResult = 'Pass';
            } elseif($lot['qc_pass'] == 2) {
                $qcResult = 'Fail';
            }
            // Check expiration date
            if($lot['expiration_date']) {
                $expired = checkExpiration($lot['expiration_date']);
            } else {
                $expired = false;
            }?>
            <div id="<?= $lot['id'];?>Modal" class="generic-modal lot-modal <?= $expired ? 'expired' : '';?> <?= strtolower($qcResult);?>">
                <div class="close-modal" data-modal-close>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
                    </svg>
                </div>
                <h2>Lot <?= $lot['lot_number'];?><?= $expired ? " (Expired)" : "";?></h2>
                <hr>
                <div class="flex flex-wrap items-center gap-x-5">
                    <p>Last Updated: <span class="font-black"><?= convertTimestamp($lot['last_updated'], true);?></span></p>
                    <?php if(isset($lot['sub_lot_number'])) : ?>
                        <p>Sub Lot Number: <span class="font-black"><?= $lot['sub_lot_number'];?></span></p>
                    <?php endif;?>
                    <?php if(isset($lot['assay_type'])) : ?>
                        <p>Assay Type: <span class="font-black"><?= $lot['assay_type'];?></span></p>
                    <?php endif;?>
                    <?php if(isset($lot['assay_sub_type'])) : ?>
                        <p>Assay Sub Type: <span class="font-black"><?= $lot['assay_sub_type'];?></span></p>
                    <?php endif;?>
                    <?php if(isset($lot['production_year'])) : ?>
                        <p>Production Year: <span class="font-black">20<?= $lot['production_year'];?></span></p>
                    <?php endif;?>
                </div>
                <?php if($qcPolicy) : ?>
                    <hr>
                    <div class="qc-result-wrapper">
                        <p>Result: <span class="qc-result"><?= strtoupper($qcResult);?></span></p>
                        <?php if($qcPolicy != 0 && $lot['failure_count'] == 0) : ?>
                            <p class="text-sm <?= $lot['positive_count'] >= $qcPositives ? 'text-green-500' : '';?>"><?= $lot['positive_count'];?> Passed Positive Test<?= $lot['positive_count'] > 1 || $lot['positive_count'] == 0 ? 's' : '';?>. <?= $qcPositives;?> required.</p>
                            <p class="text-sm <?= $lot['negative_count'] >= $qcNegatives ? 'text-green-500' : '';?>"><?= $lot['negative_count'];?> Passed Negative Test<?= $lot['negative_count'] > 1 || $lot['negative_count'] == 0 ? 's' : '';?>. <?= $qcPositives;?> required.</p>
                        <?php elseif($lot['failure_count'] != 0) :?>
                            <p class="text-sm text-red-500"><?= $lot['failure_count'];?> Failed Test<?= $lot['failure_count'] > 1 || $lot['failure_count'] == 0 ? 's' : '';?>! Lot QC should be considered failed.</p>
                        <?php endif;?>
                    </div>
                <?php endif;?>
                <hr>
                <form action="/process" method="POST">
                    <input type="hidden" name="action" value="edit-lot">
                    <input type="hidden" name="return-path" value="<?= $currentURL;?>">
                    <input type="hidden" name="id" class="lot-id" value="<?= $lot['id'];?>">

                    <div class="form-fields <?= $qcPolicy == 2 ? '' : '!hidden';?>">
                        <div class="field">
                            <label for="qcResult<?= $lot['id'];?>">QC Result</label>
                            <div class="input-wrapper select-wrapper">
                                <select id="qcResult<?= $lot['id'];?>" required name="qcResult">
                                    <option <?= $lot['qc_pass'] == 0 ? 'selected' : '';?> value="0">Unverified</option>
                                    <option <?= $lot['qc_pass'] == 2 ? 'selected' : '';?> value="2">Fail</option>
                                    <option <?= $lot['qc_pass'] == 1 ? 'selected' : '';?> value="1">Pass</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-fields">
                        <div class="field">
                            <label for="deliveryDate<?= $lot['id'];?>">Delivery Date</label>
                            <div class="input-wrapper">
                                <svg class="cursor-pointer" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                    <path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zm64 80v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm128 0v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H208c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H336zM64 400v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H208zm112 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H336c-8.8 0-16 7.2-16 16z"/>
                                </svg>
                                <input id="deliveryDate<?= $lot['id'];?>" name="delivery" class="date-picker" placeholder="Date of Lot Delivery" type="text" value="<?= $lot['delivery_date'] ? Date('Y-m-d', strtotime($lot['delivery_date'])) : '';?>" readonly/>
                            </div>
                        </div>
                        <div class="field">
                            <label for="expirationDate<?= $lot['id'];?>">Expiration Date</label>
                            <div class="input-wrapper expiration">
                                <svg class="cursor-pointer" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                    <path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zm64 80v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm128 0v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H208c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H336zM64 400v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H208zm112 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H336c-8.8 0-16 7.2-16 16z"/>
                                </svg>
                                <input id="expirationDate<?= $lot['id'];?>" name="expiration" class="month-picker" placeholder="Expiration Date" type="text" value="<?= $lot['expiration_date'] ? Date('Y-m', strtotime($lot['expiration_date'])) : '';?>" readonly/>
                            </div>
                        </div>
                    </div>

                    <div class="form-fields">
                        <div class="field">
                            <label for="notes">Reference</label>
                            <div class="input-wrapper">
                                <textarea class="textarea" name="notes" placeholder="If you would like to leave comments about this lot, fill this in." id="notes"><?= $lot['reference'];?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="w-full flex justify-center items-center gap-3">
                        <button type="submit" class="btn smaller-btn">Save</button>
                        <div class="cursor-pointer btn smaller-btn close-modal no-styles" data-modal-close>Close</div>
                    </div>
                </form>
            </div>

        <?php endforeach;?>
    </div>
<?php else : ?>
    <div class="grow w-full flex items-center justify-center">
        <div class="flex justify-center items-center p-8 rounded-lg bg-white max-w-3xl">
            <h2>No lots found. Please refine your search.</h2>
        </div>
    </div>
<?php endif;?>