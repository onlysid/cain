<?php // Logs
include_once BASE_DIR . "/utils/LogTypes.php";

// Get the current log from the URL parameter, default to 'system'
$logParam = $_GET['q'] ?? 'system';
$logsPerPage = 40;

// Get the log data
$logData = readLogFile($logParam, $logsPerPage);

// Split the log data into lines
$logLines = explode("\n", $logData);

?>

<section class="notice">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
    </svg>
    <p>View all of the Samba Hub logs</p>
</section>

<script src="/js/logBackupCheck.js" defer></script>

<!-- Tab Navigation for Logs -->
<div class="flex mb-2 space-x-2 flex-wrap justify-center">
    <?php foreach ($logTypes as $logType): ?>
        <a href="?q=<?= $logType->name ?>" class="log-link <?= ($logParam === $logType->name) ? 'active' : ''; ?>">
            <?= ucfirst($logType->name) ?>
        </a>
    <?php endforeach; ?>
</div>

<input <?= empty($logData) ? 'disabled' : '';?> type="text" id="searchInput" placeholder="<?= empty($logData) ? 'Unable to search empty logs.' : 'Search logs...';?>" class="border p-2 mb-2 px-4 py-2 rounded-xl disabled:bg-white/50 disabled:cursor-not-allowed">

<!-- Log Content Display -->
<div id="logContent" class="bg-gray-100 p-4 rounded-xl shadow-md overflow-auto show-scrollbar">
    <?php if(empty($logData)) : ?>
            <p class="text-center">The log is empty.</p>
        <?php else : ?>
            <pre id="logData" class="whitespace-pre-wrap text-sm text-dark"><?= htmlspecialchars($logData);?></pre>
        <?php endif;?>

    <div class="w-full flex justify-center">
        <?php if(empty($logData)) : ?>
            <button onClick="window.location.reload();" class="mt-4 btn btn-small self-center hover:!text-secondary">Refresh</button>
        <?php else : ?>
            <?php if(count($logLines) >= $logsPerPage) : ?>
                <button id="loadMore" class="btn btn-small self-center mt-4 hover:!text-secondary">Load More</button>
            <?php endif;?>
        <?php endif;?>
    </div>
</div>

<!-- Log clearing / downloading -->
<form class="form" method="POST" action="/process">
    <input type="hidden" name="action" value="backup-logs">
    <input type="hidden" name="return-path" value="<?= $currentURL;?>">
    <div class="flex items-center gap-2 mt-2">
        <div id="backupBtn" class="btn smaller-btn btn-green cursor-pointer">Backup</div>
        <div id="genericModalBtn" class="btn smaller-btn btn-red cursor-pointer" data-modal-open="cleanModal">Clean Logs</div>
    </div>
</form>

<div class="modal-wrapper">
    <div id="cleanModal" class="generic-modal">
        <div class="close-modal" data-modal-close>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/>
            </svg>
        </div>
        <svg class="fill-red-500 h-10 w-auto mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
            <path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24V296c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/>
        </svg>
        <p class="text-center">Are you sure you want to clean your logs?</p>
        <p class="text-center">Expired log files will be deleted and <span id="clearedSize" class="font-black text-red-500"></span> will be cleared.</p>
        <form action="/process" method="POST">
            <input type="hidden" name="action" value="clean-logs">
            <input type="hidden" name="return-path" value="<?= $currentURL;?>">
            <div class="w-full flex justify-center items-center gap-3 mt-3">
                <button type="submit" class="btn smaller-btn trigger-loading">Yes</button>
                <div class="cursor-pointer btn smaller-btn close-modal no-styles" data-modal-close>Cancel</div>
            </div>
        </form>
    </div>
</div>

<script>
    const limit = <?= $logsPerPage; ?>; // Number of logs to load at once
    let offset = limit + 1; // Starting point for loading more logs

    const loadMore = document.getElementById('loadMore');
    const logContentElement = document.getElementById('logData');
    const searchInput = document.getElementById('searchInput');

    // Function to fetch and append logs
    function fetchLogs(isSearch = false) {
        const searchTerm = searchInput.value;
        const logType = "<?= $logParam ?>";

        // Reset the offset and log content if it's a new search
        if (isSearch) {
            offset = 0;
            logContentElement.innerHTML = ''; // Clear log content
        }

        fetch(`/scripts/log-check.php?q=${logType}&offset=${offset}&limit=${limit}&search=${encodeURIComponent(searchTerm)}`)
            .then(response => response.text())
            .then(data => {
                if (data.trim() === '' || data.includes('No logs found')) {
                    if(loadMore) {
                        loadMore.style.display = 'none'; // Hide "Load More" if no logs are found or search is empty
                    }
                    if (isSearch && data.includes('No logs found')) {
                        logContentElement.innerHTML = data; // Display no logs message
                    }
                } else {
                    logContentElement.innerHTML += data.replace(/\n{2,}/g, '\n'); // Append new logs AND remove double newlines
                    if(loadMore) {
                        loadMore.style.display = data.includes('<!-- no-more-logs -->') ? 'none' : 'block'; // Show or hide "Load More"
                    }
                }
                offset += limit; // Update the offset for next fetch
            });
    }

    // Event listener for "Load More" button
    if (loadMore) {
        loadMore.addEventListener('click', function () {
            fetchLogs();
        });
    }

    // Event listener for search input
    searchInput.addEventListener('input', function () {
        fetchLogs(true); // Pass true to indicate a search
    });
</script>