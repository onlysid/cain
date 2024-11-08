<?php
// Functions involved with exporting results
include_once __DIR__ . "/../includes/config.php";
include_once BASE_DIR . "/includes/db.php";
include_once BASE_DIR . "/includes/functions.php";
$dateRange = $_POST['dateRange'];

// We don't want PhP memory limits to hinder the ability to download a CSV
ini_set('memory_limit', '2048M');

if ($dateRange) {
    $query = "SELECT *
              FROM results
              WHERE STR_TO_DATE(endTime, '%Y-%m-%d %H:%i') BETWEEN ? AND ?";

    // We need to parse the date range
    $dateRangeArr = explode(' - ', $dateRange);

    if ($dateRangeArr[0]) {
        $minDate = $dateRangeArr[0] . ' 00:00:00';
    }
    if (isset($dateRangeArr[1])) {
        $maxDate = $dateRangeArr[1] . ' 23:59:59';
    } else {
        $maxDate = $dateRangeArr[0] . ' 23:59:59';
    }

    $results = $cainDB->selectAll($query, [$minDate, $maxDate]);
} else {
    $query = "SELECT * FROM results;";
    $results = $cainDB->selectAll($query);
}

// Check if there are any results
if (!empty($results)) {
    // Output CSV headers with the specified filename
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment;");

    // Create CSV file pointer
    $output = fopen('php://output', 'w');

    // Output CSV column headers (table keys)
    fputcsv($output, array_keys($results[0]));

    // Output each row of the result
    foreach ($results as $row) {
        fputcsv($output, $row);
    }

    // Close CSV file pointer
    fclose($output);
} else {
    // No results found
    echo json_encode(["status" => 0, "message" => "No Results Found"]);
}