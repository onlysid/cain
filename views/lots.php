<?php // Lots Page

// Do we have any filters?
$filters = $_GET;
$page = $filters['p'] ?? 1;
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
?>

<h1 class="mb-2">Lots</h1>

<?php if(false) : ?>
    <div id="tableInfoWrapper" class="w-full flex justify-between items-center gap-2">
        <p class="lots-number hidden sm:block"><?= $lotNumberText;?></p>
        <div id="filterSearchWrapper" class="flex items-center flex-col-reverse sm:flex-row w-full sm:w-auto justify-end">
            <form action="/lots" method="GET" id="searchForm" class="w-full sm:w-auto">
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
            <div class="flex items-center w-full sm:w-auto">
                <p class="grow sm:hidden text-base md:text-lg"><?= $lotNumberText;?></p>
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

    <?php if($totalLotCount) : ?>
        <div class="grow w-full flex flex-col gap-2">
            <table id="lotsTable">
                <thead>
                    <tr>
                        <th>
                            <a href="<?= updateQueryString(["sp" => "lot_number", "sd" => ((($filters['sd'] ?? "desc") == "desc" && ($filters['sp'] ?? null) == "lot_number") || ($filters['sd'] ?? "desc") == "" ? "asc" : "")], true);?>" class="ignore-default flex gap-1.5 items-center">
                                <span>#</span>
                                <svg class="h-4 fill-dark shrink-0 <?= (isset($filters['sp']) && $filters['sp'] == "lot_number") ? "" : "opacity-50 !rotate-180";?> <?= (!isset($filters['sd']) || $filters['sd'] == '') ? "rotate-180" : "" ;?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 231c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-71-71V376c0 13.3-10.7 24-24 24s-24-10.7-24-24V193.9l-71 71c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9L239 119c9.4-9.4 24.6-9.4 33.9 0L385 231z"/>
                                </svg>
                            </a>
                        </th>
                        <th>
                            <a href="<?= updateQueryString(["sp" => "production_year", "sd" => ((($filters['sd'] ?? "desc") == "desc" && ($filters['sp'] ?? null) == "production_year") || ($filters['sd'] ?? "desc") == "" ? "asc" : "")], true);?>" class="ignore-default flex gap-1.5 items-center">
                                <span>Produced</span>
                                <svg class="h-4 fill-dark shrink-0 <?= (isset($filters['sp']) && $filters['sp'] == "production_year") ? "" : "opacity-50 !rotate-180";?> <?= (!isset($filters['sd']) || $filters['sd'] == '') ? "rotate-180" : "" ;?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 231c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-71-71V376c0 13.3-10.7 24-24 24s-24-10.7-24-24V193.9l-71 71c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9L239 119c9.4-9.4 24.6-9.4 33.9 0L385 231z"/>
                                </svg>
                            </a>
                        </th>
                        <th>
                            <a href="<?= updateQueryString(["sp" => "expiration_year", "sd" => ((($filters['sd'] ?? "desc") == "desc" && ($filters['sp'] ?? null) == "expiration_year") || ($filters['sd'] ?? "desc") == "" ? "asc" : "")], true);?>" class="ignore-default flex gap-1.5 items-center">
                                <span>Expiration</span>
                                <svg class="h-4 fill-dark shrink-0 <?= (isset($filters['sp']) && $filters['sp'] == "expiration_year") ? "" : "opacity-50 !rotate-180";?> <?= (!isset($filters['sd']) || $filters['sd'] == '') ? "rotate-180" : "" ;?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 231c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-71-71V376c0 13.3-10.7 24-24 24s-24-10.7-24-24V193.9l-71 71c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9L239 119c9.4-9.4 24.6-9.4 33.9 0L385 231z"/>
                                </svg>
                            </a>
                        </th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($lotItems as $lot) : ?>
                        <tr id="lot<?= $lot['id'];?>" class="lot">
                            <td><?= $lot['lot_number'];?></td>
                            <td><?= $lot['production_year'];?></td>
                            <td><?= $lot["expiration_month"];?>/<?=$lot['expiration_year'];?></td>
                            <td>
                                <div class="h-full flex items-center gap-1.5 justify-end">
                                    <!-- Statuses and chevron -->
                                    <?php if($lot['check_digit']) : ?>
                                        <p class="tooltip" title="Not sure what this means..."><?= $lot['check_digit'];?></p>
                                    <?php endif;?>
                                    <?php if($lot['qc_result'] != null) : ?>
                                        <div class="status-indicator <?= $lot['qc_result'] == 1 ? 'active' : '';?> tooltip" title="<?= $lot['qc_result'] == 1 ? 'QC Passed' : 'QC Failed';?>"></div>
                                    <?php endif;?>
                                    <button class="details tooltip" title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
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
    <?php // No lots found
    else : ?>
        <div class="grow w-full flex items-center justify-center">
            <div class="flex justify-center items-center p-8 rounded-lg bg-white max-w-3xl">
                <h2>No lots found. Please refine your search.</h2>
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

    // Lots modals
    foreach($lotItems as $lot) : ?>
        <div id="lot<?= $lot['id'];?>Modal" class="lot-modal">
            <div class="lot-modal-backdrop">
                <div class="relative lot-details bg-primary shadow-xl shadow-dark flex flex-col rounded-xl max-w-[35rem] max-h-[calc(min(35rem,_90vh))] h-full w-full m-4 lg:m-8 p-4 lg:p-8 overflow-y-scroll">
                    <button class="modal-close absolute top-2 right-2 p-2 transition-all duration-500 hover:scale-110">
                        <svg class="h-8 fill-dark pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                            <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                        </svg>
                    </button>
                    <h2 class="text-center sm:text-start mx-12 sm:mx-0 sm:mr-12 mb-2.5 sm:mb-1">Lot Details</h2>
                    <div class="bg-gradient-to-r from-transparent via-grey/75 sm:from-grey/75 to-transparent w-full mb-3 pb-0.5 rounded-full"></div>

                    <div class="flex result-details flex-wrap gap-2 items-stretch">
                        <div class="lot-info <?= (isset($lot["qc_result"]) && $lot['qc_result'] == 1) ? "pos" : "neg";?>">
                            <h4>QC Result</h4>
                            <p><?= (isset($lot["qc_result"]) && $lot['qc_result'] == 1) ? "Pass" : "Fail";?></p>
                        </div>
                    </div>

                    <table class="lot-explosion">
                        <?php if($lot['lot_number']) : ?>
                            <tr>
                                <td>Lot Number</td>
                                <td><?= $lot['lot_number'];?></td>
                            </tr>
                        <?php endif;
                        if($lot['expiration_year']) : ?>
                            <tr>
                                <td>Expiration</td>
                                <td><?= $lot['expiration_month'] ? $lot['expiration_month'] . "/" : "";?><?= $lot['expiration_year'];?></td>
                            </tr>
                        <?php endif;
                        if($lot['assay_type']) : ?>
                            <tr>
                                <td>Assay Type</td>
                                <td><?= $lot['assay_type'];?></td>
                            </tr>
                        <?php endif;
                        if($lot['production_run']) : ?>
                            <tr>
                                <td>Production Runr</td>
                                <td><?= $lot['production_run'];?></td>
                            </tr>
                        <?php endif;
                        if($lot['sub_lot']) : ?>
                            <tr>
                                <td>Sub-Lot</td>
                                <td><?= $lot['sub_lot'];?></td>
                            </tr>
                        <?php endif;
                        if($lot['assay_sub_type']) : ?>
                            <tr>
                                <td>Assay Sub-Type</td>
                                <td><?= $lot['assay_sub_type'];?></td>
                            </tr>
                        <?php endif;
                        if($lot['check_digit']) : ?>
                            <tr>
                                <td>Check Digit</td>
                                <td><?= $lot['check_digit'];?></td>
                            </tr>
                        <?php endif;?>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach;?>
<?php else : ?>

    <p>Lots frontend incomplete</p>

<?php endif;?>