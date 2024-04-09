<?php // Results Page
$hospitalInfo = systemInfo();
$hospitalInfoArray = array_column($hospitalInfo, 'value', 'name');

// Get all the data
$results = getResults($_GET);

?>

<h1>Results</h1>

<div class="w-full flex flex-col gap-2">
    <div id="tableInfoWrapper" class="w-full flex justify-between items-center">
        <p>1-10 of 3890 Results</p>
        <div id="filterSearchWrapper" class="flex items-center gap-2.5">
            <form action="/" method="GET" id="searchForm" class="flex !flex-row !gap-2">
                <div class="form-fields">
                    <div class="field">
                        <div class="input-wrapper !py-1 !pr-1">
                            <input type="text" placeholder="Search..." name="s" value="<?= $_GET['s'] ?? "";?>">
                            <button class="aspect-square rounded-full p-2 bg-dark transition-all duration-500 hover:scale-110 hover:opacity-75" type="submit">
                                <svg class="h-4 w-auto fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            <button id="filter" class="transition-all duration-500 hover:scale-110 hover:opacity-75">
                <svg class="h-7 fill-dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M0 416c0 17.7 14.3 32 32 32l54.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 448c17.7 0 32-14.3 32-32s-14.3-32-32-32l-246.7 0c-12.3-28.3-40.5-48-73.3-48s-61 19.7-73.3 48L32 384c-17.7 0-32 14.3-32 32zm128 0a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zM320 256a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm32-80c-32.8 0-61 19.7-73.3 48L32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l246.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48l54.7 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-54.7 0c-12.3-28.3-40.5-48-73.3-48zM192 128a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm73.3-64C253 35.7 224.8 16 192 16s-61 19.7-73.3 48L32 64C14.3 64 0 78.3 0 96s14.3 32 32 32l86.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 128c17.7 0 32-14.3 32-32s-14.3-32-32-32L265.3 64z"/>
                </svg>
            </button>
        </div>
    </div>
    <table>
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
            </tr>
        </thead>

        <tbody>
            <?php foreach($results as $result) : ?>
                <tr>
                    <td><?= (new DateTime($result['testcompletetimestamp']))->format($hospitalInfoArray['date_format']);?></td>
                    <td><?= $result['firstName'];?> <?= $result['lastName'];?></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>