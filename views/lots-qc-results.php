<?php // Lots Page

// Do we have any filters?
$filters = $_GET ?? $session->get('result-filters');

// Get the QC results
$priorityQCResults = getLotQCResults(true);
$qcResults = getLotQCResults();

var_dump($priorityQCResults);
var_dump($qcResults);

// Make a list of the two lists with
$totalQCResults = array_merge($priorityQCResults, $qcResults);
?>

<h1 class="mb-2">Lots QC Results</h1>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>View and verify results for QC tests.</p>
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
        <?php foreach($totalQCResults as $result) : ?>
            <tr data-modal-open="<?= $result['id'];?>Modal">
                <td><?= convertTimestamp($result['timestamp'], true);?></td>
                <td><?= $result['lot_number'];?></td>
                <td><?= $result['result'];?></td>
                <td><?= $result['qc_result'];?></td>
                <td class="end">
                    <div class="table-controls">
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