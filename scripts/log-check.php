<?php
include '../includes/functions.php';

$logParam = $_GET['q'] ?? 'system';
$searchTerm = $_GET['search'] ?? '';
$offset = (int)$_GET['offset'] ?? 0;
$limit = (int)$_GET['limit'] ?? 10;

// Load the entire log (not just up to offset + limit initially)
$logData = readLogFile($logParam);

// Break log data into lines
$logLines = explode("\n", $logData);

// Apply search filtering if a search term exists
if ($searchTerm) {
    $logLines = array_filter($logLines, function($line) use ($searchTerm) {
        return stripos($line, $searchTerm) !== false; // Case-insensitive search
    });
}

// Reindex array after filtering
$logLines = array_values($logLines);

// Paginate the logs after filtering (apply offset and limit)
$paginatedLogs = array_slice($logLines, $offset, $limit);

// Check if we have more logs to load (compare count of lines with total log count)
$hasMoreLogs = count($logLines) > $offset + $limit;

// Return the paginated logs and signal if more logs are available
if(empty($paginatedLogs) || empty($logLines)) {
    echo '<p class="text-center">No logs found for your search.</p>'; // Show a message if no logs match
} else {
    echo implode("\n", $paginatedLogs) . "\n"; // Ensure logs are followed by a new line
    if (!$hasMoreLogs) {
        echo "<!-- no-more-logs -->"; // Special signal to indicate no more logs
    }
}
