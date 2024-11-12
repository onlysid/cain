<?php // Lots QC Results Page

// Do we have any filters?
$filters = $_GET ?? $session->get('result-filters');
$page = $filters['p'] ?? 1;
$itemsPerPage = $filters['ipp'] ?? 10;

// Get the QC results
$qcResultsInfo = getLotQCResults(false, $page, $itemsPerPage);
$qcCount = $qcResultsInfo['count'];
$qcResults = $qcResultsInfo['results'];

// Get the pagination information
$totalPageCount = ceil($qcCount / $itemsPerPage);
$firstItemIndex = ceil(($itemsPerPage * $page) - $itemsPerPage + 1);
$lastItemIndex = (($page * $itemsPerPage < $qcCount) ? ($firstItemIndex + ($itemsPerPage - 1)) : ($qcCount));

$resultNumberText = (($qcCount > $itemsPerPage) ? ($firstItemIndex . "-" . $lastItemIndex .  " of ") : "") . $qcCount . " Result" . ($qcCount == 1 ? "" : "s");
?>

<h1 class="mb-2">Lots QC Results</h1>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>View and verify results for QC tests. To verify any QC results, click on the result and check if the result matches the intended outcome by comparing it with the operator's references.</p>
</section>

<!-- First we show all priority QC results -->
<table id="priorityList">
    <thead>
        <th>Date/Time</th>
        <th>Lot</th>
        <th>Test Result</th>
        <th>QC Result</th>
        <th></th>
    </thead>

    <tbody>
        <?php foreach($qcResults as $result) :
            $testResult = sanitiseResult($result['result']);
            $qcResult = 'Unverified';
            if($result['qc_result'] === '1') {
                $qcResult = 'Pass';
            } elseif($result['qc_result'] === '0') {
                $qcResult = 'Fail';
            }?>
            <tr data-modal-open="<?= $result['id'];?>Modal">
                <td><?= convertTimestamp($result['timestamp'], true);?></td>
                <td><?= $result['lot_number'];?></td>
                <td class="font-black <?= $testResult['summary'] == 'Positive' ? "text-red-500" : "";?>"><?= $testResult['summary'];?></td>
                <td class="font-black <?= $qcResult == 'Fail' ? 'text-red-500' : '';?><?= $qcResult == 'Unverified' ? 'text-amber-500' : '';?><?= $qcResult == 'Pass' ? 'text-green-500' : '';?>"><?= $qcResult;?></td>
                <td class="end">
                    <div class="table-controls">
                        <?php if($qcResult == 'Unverified') : ?>
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
    <?php foreach($qcResults as $result) :
        $qcResultText = 'unverified';
        if ($result['qc_result'] === null) {
            $qcResultText = 'unverified';
        } else {
            switch ($result['qc_result']) {
                case 0:
                    $qcResultText = 'fail';
                    break;
                case 1:
                    $qcResultText = 'pass';
                    break;
            }
        }
        $resultInfo = sanitiseResult($result['result']);
        ?>

        <div id="<?= $result['id'];?>Modal" class="generic-modal <?= $qcResultText;?>">
            <div class="close-modal" data-modal-close>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
                </svg>
            </div>

            <h2>QC Result #<?= $result['id'];?></h2>
            <hr>
            <div class="flex flex-wrap items-center gap-x-5">
                <p>Date/Time: <span class="font-black"><?= convertTimestamp($result['timestamp'], true);?></span></p>
                <p>Lot: <span class="font-black"><?= $result['lot_number'];?></span></p>
                <p>Result: #<span class="font-black"><?= $result['result_id'];?></span></p>
            </div>
            <hr>
            <div class="qc-result-wrapper">
                <p>Result: <span class="qc-result"><?= strtoupper($qcResultText);?> <?= $result['qc_result'] !== null ? "(Operator: " . ($result['operator_id'] ?? "unknown") . ")" : "";?></span></p>
            </div>
            <hr>
            <form action="/process" method="POST">
                <input type="hidden" name="action" value="edit-lot-result">
                <input type="hidden" name="return-path" value="<?= $currentURL;?>">
                <input type="hidden" name="id" class="lot-result-id" value="<?= $result['id'];?>">

                <div class="p-4 w-full rounded-xl border-4 border-primary shadow-lg bg-gradient-to-r from-cyan-200/25 to-secondary/25">
                    <h4 class="mb-3">Result Information:</h4>
                    <div class="flex gap-x-2 gap-y-4 justify-center items-stretch w-full flex-wrap">
                        <div class="grow flex result-details flex-wrap gap-2 items-stretch">
                            <?php if(!$resultInfo || $resultInfo['result'] === null) : ?>
                                <div class="result-info invalid">
                                    <h4><?= $result["product"];?></h4>
                                    <p>Invalid</p>
                                </div>
                            <?php elseif(gettype($resultInfo["result"]) === "boolean") : ?>
                                <div class="result-info <?= $resultInfo["result"] ? "pos" : "";?>">
                                    <h4><?= $result["product"];?></h4>
                                    <p><?= $resultInfo["result"] ? "Positive" : "Negative";?></p>
                                </div>
                            <?php else : ?>

                                <?php foreach($resultInfo["result"] as $resultKey => $resultData) : ?>
                                    <div class="<?= $resultData ? "pos" : "";?> result-info">
                                        <h4><?= $resultKey;?></h4>
                                        <p><?= $resultData ? "Positive" : "Negative";?></p>
                                    </div>
                                <?php endforeach;?>

                            <?php endif;?>
                        </div>
                        <p class="grow shadow-xl basis-1/2 text-base bg-white rounded-lg p-2 border-secondary border h-auto"><?= $result['reserve1'] || $result['reserve2'] ? $result['reserve1'] . " " . $result['reserve2'] : "Operator left no notes.";?></p>
                    </div>
                </div>


                <div class="form-fields -mb-2 -mt-1">
                    <div class="field">
                        <label>QC Result</label>
                        <div class="input-wrapper select-wrapper">
                            <select required name="qcResult">
                                <option <?= $result['qc_result'] === null ? 'selected' : '';?> disabled value="">Please select</option>
                                <option <?= $result['qc_result'] === '0' ? 'selected' : '';?> value="0">Fail</option>
                                <option <?= $result['qc_result'] === '1' ? 'selected' : '';?> value="1">Pass</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-fields">
                    <div class="field">
                        <label for="qcNotes">Reference</label>
                        <div class="input-wrapper">
                            <textarea class="textarea" name="qcNotes" placeholder="If you would like to leave comments about this decision, fill this in." id="qcNotes"><?= $result['reference'];?></textarea>
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