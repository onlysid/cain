<?php // Live view of simulator output

$dataUpdate = isset($_GET['view']) && $_GET['view'] === 'data';

// Export the LIMS simulator data to CSV txt file JUST in case it's out of sync...
exportSimulatorDataToCSV();

if($dataUpdate) : ?>

    <?php // Get simulator dummy data 
    $simOperators = $cainDB->selectAll("SELECT * FROM simulator_operators");
    $simPatients = $cainDB->selectAll("SELECT * FROM simulator_patients");
    ?>
    <a href="/settings/lims-simulator" class="btn smaller-btn !py-1.5 w-max">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
            <path d="M459.5 440.6c9.5 7.9 22.8 9.7 34.1 4.4s18.4-16.6 18.4-29l0-320c0-12.4-7.2-23.7-18.4-29s-24.5-3.6-34.1 4.4L288 214.3l0 41.7 0 41.7L459.5 440.6zM256 352l0-96 0-128 0-32c0-12.4-7.2-23.7-18.4-29s-24.5-3.6-34.1 4.4l-192 160C4.2 237.5 0 246.5 0 256s4.2 18.5 11.5 24.6l192 160c9.5 7.9 22.8 9.7 34.1 4.4s18.4-16.6 18.4-29l0-64z"/>
        </svg>
        Go Back
    </a>

    <div class="flex flex-col mt-3">
        <h4 class="mb-0.5 ml-1.5">Operators</h4>
        <table class="no-shadow reduced-padding">
            <thead>
                <th>Operator ID</th>
                <th></th>
            </thead>
            <tbody>
                <?php foreach($simOperators as $op) : ?>
                    <tr>
                        <td><?= $op['operator_id'];?></td>
                        <td class="text-end">
                            <form method="POST" action="/process" class="items-end">
                                <input type="hidden" name="id" value="<?= $op['id']; ?>">
                                <input type="hidden" name="action" value="delete-simulator-operator">
                                <input type="hidden" name="return-path" value="<?= $currentURL;?>">
                                <button class="remove-row-btn w-4">
                                    <svg class="fill-red-500" viewBox="0 0 448 512">
                                        <path d="M135.2 17.7L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-7.2-14.3C307.4 6.8 296.3 0 284.2 0L163.8 0c-12.1 0-23.2 6.8-28.6 17.7zM416 128L32 128 53.2 467c1.6 25.3 22.6 45 47.9 45l245.8 0c25.3 0 46.3-19.7 47.9-45L416 128z"/>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach;?>
                <tr>
                    <form method="POST" action="/process">
                        <input type="hidden" name="action" value="add-simulator-operator">
                        <input type="hidden" name="return-path" value="<?= $currentURL;?>">
                        <td><input type="text" name="operator_id" placeholder="New Operator ID" class="input w-full rounded-lg p-1.5"></td>
                        <td class="text-end">
                            <button type="submit" class="w-4 align-middle tooltip" title="Add new operator">
                                <svg class="fill-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                    <path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 144L48 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l144 0 0 144c0 17.7 14.3 32 32 32s32-14.3 32-32l0-144 144 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-144 0 0-144z"/>
                                </svg>
                            </button>
                        </td>
                    </form>
                </tr>
            </tbody>
        </table>

        <h4 class="mb-0.5 mt-3 ml-1.5">Patients</h4>
        <table class="no-shadow reduced-padding">
            <thead>
                <th>Patient ID</th>
                <th>Hospital ID</th>
                <th>NHS #</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>DOB</th>
                <th>Sex</th>
                <th>Age</th>
                <th></th>
            </thead>
            <tbody>
                <?php foreach($simPatients as $patient) : ?>
                    <tr>
                        <td><?= $patient['patientId'];?></td>
                        <td><?= $patient['hospitalId'];?></td>
                        <td><?= $patient['nhsNumber'];?></td>
                        <td><?= $patient['firstName'];?></td>
                        <td><?= $patient['lastName'];?></td>
                        <td><?= $patient['dob'];?></td>
                        <td><?= $patient['patientSex'];?></td>
                        <td><?= $patient['patientAge'];?></td>
                        <td class="text-end">
                            <form method="POST" action="/process" class="items-end">
                                <input type="hidden" name="id" value="<?= $patient['id']; ?>">
                                <input type="hidden" name="action" value="delete-simulator-patient">
                                <input type="hidden" name="return-path" value="<?= $currentURL;?>">
                                <button class="remove-row-btn w-4">
                                    <svg class="fill-red-500" viewBox="0 0 448 512">
                                        <path d="M135.2 17.7L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-7.2-14.3C307.4 6.8 296.3 0 284.2 0L163.8 0c-12.1 0-23.2 6.8-28.6 17.7zM416 128L32 128 53.2 467c1.6 25.3 22.6 45 47.9 45l245.8 0c25.3 0 46.3-19.7 47.9-45L416 128z"/>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach;?>
                <tr>
                    <form method="POST" action="/process">
                        <input type="hidden" name="action" value="add-simulator-patient">
                        <input type="hidden" name="return-path" value="<?= $currentURL;?>">
                        <td><input name="patientId" placeholder="Patient ID" class="input w-full rounded-lg p-1.5" required></td>
                        <td><input name="hospitalId" placeholder="Hospital ID" class="input w-full rounded-lg p-1.5"></td>
                        <td><input name="nhsNumber" placeholder="NHS #" class="input w-full rounded-lg p-1.5"></td>
                        <td><input name="firstName" placeholder="First" class="input w-full rounded-lg p-1.5"></td>
                        <td><input name="lastName" placeholder="Last" class="input w-full rounded-lg p-1.5"></td>
                        <td><input name="dob" placeholder="DOB" class="input w-full rounded-lg p-1.5"></td>
                        <td><input name="patientSex" placeholder="Sex" class="input w-full rounded-lg p-1.5"></td>
                        <td><input name="patientAge" placeholder="Age" class="input w-full rounded-lg p-1.5"></td>
                        <td class="text-end">
                            <button type="submit" class="w-4 align-middle tooltip" title="Add new operator">
                                <svg class="fill-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                    <path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 144L48 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l144 0 0 144c0 17.7 14.3 32 32 32s32-14.3 32-32l0-144 144 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-144 0 0-144z"/>
                                </svg>
                            </button>
                        </td>
                    </form>
                </tr>
            </tbody>
        </table>
    </div>

<?php else : ?>

    <?php
    $logFile = __DIR__ . '/../../simdata/simoutput.txt';
    $logData = [];

    $numLines = 50;

    if(file_exists($logFile)) {
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lastLines = array_slice($lines, -$numLines);
        $logData = array_reverse($lastLines);
    }?>

    <section class="notice">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
            <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
        </svg>
        <p>View logs from the LIMS simulator and update LIMS data.</p>
    </section>

    <div id="logContent" class="bg-gray-100 p-4 rounded-xl shadow-md overflow-y-auto show-scrollbar space-y-2">
        <div id="logData" class="text-sm text-dark font-mono">
            <?php if (empty($logData)) : ?>
                <p class="text-center">No output available.</p>
            <?php else : ?>
                <?php foreach ($logData as $line): ?>
                    <div class="whitespace-pre-wrap break-words bg-white rounded p-2 shadow-sm"><?= htmlspecialchars($line); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex items-center justify-center gap-2 mt-2">
        <a href="/settings/lims-simulator?view=data" class="btn smaller-btn btn-green">LIMS Data</a>
        <button id="clearOutputBtn" class="btn smaller-btn btn-red">Clear Output</button>
    </div>

    <script>
        (function() {
            const logUrl = '/simdata/simoutput.txt';
            const logElem = document.getElementById('logData');

            async function refreshLog() {
                try {
                    const response = await fetch(logUrl, { cache: 'no-store' });
                    if (response.ok) {
                        const text = await response.text();
                        const lines = text.split('\n').filter(line => line.trim() !== '').slice(-<?= $numLines;?>).reverse();

                        if (lines.length === 0) {
                            logElem.innerHTML = '<p class="text-center">No output available.</p>';
                        } else {
                            logElem.innerHTML = lines.map(line => `<div class="whitespace-pre-wrap break-words rounded p-2">${line.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>`).join('');
                        }
                    }
                } catch (err) {
                    console.error('Failed to fetch log:', err);
                }
            }

            document.getElementById('clearOutputBtn').addEventListener('click', async () => {
                try {
                    const response = await fetch('/scripts/clear-lims-sim-check.php', { method: 'POST' });
                    if (response.ok) {
                        refreshLog();
                    } else {
                        console.error('Failed to clear log');
                    }
                } catch (err) {
                    console.error('Error clearing log:', err);
                }
            });

            // Initial refresh and interval
            refreshLog();
            setInterval(refreshLog, 5000);
        })();
    </script>

<?php endif;