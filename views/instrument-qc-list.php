<?php // QC Tests for a given instrument
$instrument = getInstrumentSnapshot($instrument);

// Get the instrument's QC results
$qcResults = getInstrumentQCResults($instrument['id']);

// Get test types
$qcTestTypes = getInstrumentQCTypes();

// Get current time
$currentTimestamp = time();
?>

<!-- QC Results Title Area -->
<section class="page-title-area flex items-center gap-2">

    <!-- Link to return to assay modules page -->
    <a class="h-12 hover:opacity-75 hover:scale-110 transition-all back-page-btn flex items-center" href="/assay-modules/<?= $instrument['id'];?>">
        <svg class="h-3/4 w-auto fill-grey" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
            <path d="M512 256A256 256 0 1 0 0 256a256 256 0 1 0 512 0zM116.7 244.7l112-112c4.6-4.6 11.5-5.9 17.4-3.5s9.9 8.3 9.9 14.8l0 64 96 0c17.7 0 32 14.3 32 32l0 32c0 17.7-14.3 32-32 32l-96 0 0 64c0 6.5-3.9 12.3-9.9 14.8s-12.9 1.1-17.4-3.5l-112-112c-6.2-6.2-6.2-16.4 0-22.6z"/>
        </svg>
    </a>

    <h1 class="mb-0">QC Results</h1>
    <a href="/assay-modules/new-qc?instrument=<?= $instrument['id'];?>">
        <svg data-modal-open="newUserModal" class="h-10 w-auto fill-green-600 p-1 cursor-pointer scale-95 transition-all duration-500 hover:scale-105 hover:fill-fuchsia-500 new-user-button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
            <path class="pointer-events-none" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM232 344V280H168c-13.3 0-24-10.7-24-24s10.7-24 24-24h64V168c0-13.3 10.7-24 24-24s24 10.7 24 24v64h64c13.3 0 24 10.7 24 24s-10.7 24-24 24H280v64c0 13.3-10.7 24-24 24s-24-10.7-24-24z"/>
        </svg>
    </a>
</section>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>This is a full list of all QC tests for the instrument with the following serial number: <span class="font-black"><?= $instrument['serial_number'];?></span>.</p>
</section>

<?php if(count($qcResults)) : ?>
    <table id="qcTable" class="!px-0">
        <thead>
            <th>Test Date</th>
            <th>Result</th>
            <th>QC Name</th>
            <th>Results Since Test</th>
            <th></th>
        </thead>

        <tbody id="qcTableBody">
            <?php foreach($qcResults as $qcResult) :
                // Determine if the time has expired
                $testTimestamp = $qcResult['timestamp'];
                $intervalSeconds = $qcResult['type'] ? $qcTestTypes[$qcResult['type']]['time_intervals'] * 86400 : null;
                $expiredTimestamp = $intervalSeconds ? $testTimestamp + $intervalSeconds : null;

                $expiredByTime = 0;

                if ($expiredTimestamp) {
                    // Calculate one day after the expiration timestamp
                    $nextDayAfterExpired = strtotime(gmdate("Y-m-d", $expiredTimestamp) . ' +1 day');

                    if ($currentTimestamp >= $nextDayAfterExpired) {
                        // It's expired (one day after the expiration date)
                        $expiredByTime = 2;
                    } elseif ($currentTimestamp >= $nextDayAfterExpired && $currentTimestamp < $nextDayAfterExpired) {
                        // It's expiring today
                        $expiredByTime = 1;
                    }
                }

                // Understand if the results since last test has expired
                $resultCount = $qcResult['result_counter'];
                $expiredResultCount = $qcResult['type'] && $qcTestTypes[$qcResult['type']]['result_intervals'] && ($resultCount >= $qcTestTypes[$qcResult['type']]['result_intervals']);
                ?>
                <tr data-modal-open="test<?= $qcResult['id'];?>">
                    <td class="expires-<?= $expiredByTime;?>">
                        <p class="text-base"><?= convertTimestamp($testTimestamp);?></p>
                        <?php if($expiredTimestamp) : ?>
                            <p class="text-sm"><?= $expiredByTime == 1 ? "Expires today." : ($expiredByTime == 2 ? "Expired on " . convertTimestamp($expiredTimestamp) : "Expires: " . convertTimestamp($expiredTimestamp));?></p>
                        <?php endif;?>
                    </td>
                    <td><?= $qcResult['result'] == 1 ? "<span class='text-green-500'>PASS</span>" : "<span class='text-red-500'>FAIL</span>";?></td>
                    <td><?= $qcResult['type'] ? $qcTestTypes[$qcResult['type']]['name'] : "(Test type no longer exists.)";?></td>
                    <td class="<?= $expiredResultCount ? 'text-red-500' : 'text-green-500';?>"><?= $resultCount;?><?= ($qcResult['type'] && $qcTestTypes[$qcResult['type']]['result_intervals']) ? "/" . $qcTestTypes[$qcResult['type']]['result_intervals'] : '';?></td>
                    <td></td>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>


    <!-- QC Result Popup Modal -->
    <div id="qcModalWrapper" class="modal-wrapper">

        <?php // Get all QC tests
        foreach($qcResults as $qcResult) : ?>
            <div id="test<?= $qcResult['id'];?>" class="generic-modal">
                <div class="close-modal" data-modal-close>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
                    </svg>
                </div>

                <h2 class="mb-3">QC Test #<?= $qcResult['id'];?></h2>
                <?php // Determine if the time has expired
                $testTimestamp = $qcResult['timestamp'];
                $intervalSeconds = $qcResult['type'] ? $qcTestTypes[$qcResult['type']]['time_intervals'] * 86400 : null;
                $expiredTimestamp = $intervalSeconds ? $testTimestamp + $intervalSeconds : null;

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
                $resultCount = $qcResult['result_counter'];
                $expiredResultCount = $qcResult['type'] && $qcTestTypes[$qcResult['type']]['result_intervals'] && ($resultCount >= $qcTestTypes[$qcResult['type']]['result_intervals']);
                ?>
                <p class="font-black">Result: <?= $qcResult['result'] ? '<span class="text-green-500">PASS</span>' : '<span class="text-red-500">FAIL</span>';?></p>
                <p><span class="font-black">Date/Time: </span><?= convertTimestamp($qcResult['timestamp'], true) ?? 'UNKNOWN';?></p>
                <p class="font-black">QC Test Expires: <span class="severity-<?= $timeDiffMessage ? $timeDiffMessage['severity'] : 0;?>"><?= $expiredTimestamp ? convertTimestamp($expiredTimestamp) : 'NEVER';?></span></p>
                <p><span class="font-black">Operator: </span><?= ($testUser = userInfo($qcResult['user'])) ? $testUser['first_name'] . " " . $testUser['last_name'] . " (" . $testUser['operator_id'] . ")" : "UNKNOWN";?></p>
                <p class="font-black">Results Since Last Test: <?= $qcResult['result_counter'];?><?= $qcResult['type'] && $qcTestTypes[$qcResult['type']]['result_intervals'] ? '/' . $qcTestTypes[$qcResult['type']]['result_intervals'] : '';?></p>
                <?php if($qcResult['notes']) : ?>

                    <div class="notes-wrapper rounded-lg shadow-md bg-primary p-3">
                        <p class="font-black">Notes:</p>
                        <p><?= $qcResult['notes'] ?? "Operator left no notes.";?></p>
                    </div>

                <?php endif;?>
                <div class="divider"></div>
                <div class="flex items-center gap-2">
                    <a href="/assay-modules/edit-qc?qc=<?= $qcResult['id'];?>" class="btn smaller-btn">Edit</a>
                </div>
            </div>
        <?php endforeach;?>

    </div>
<?php else : ?>
    <div class="grow w-full flex items-center justify-center">
        <div class="flex justify-center items-center p-8 rounded-lg bg-white max-w-3xl">
            <h2>No QC results found.</h2>
        </div>
    </div>
<?php endif;?>