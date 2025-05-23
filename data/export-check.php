<?php
// Functions involved with exporting results
include_once __DIR__ . "/../includes/config.php";
include_once BASE_DIR . "/includes/db.php";
include_once BASE_DIR . "/includes/functions.php";
$dateRange = $_POST['dateRange'];

// We don't want PhP memory limits to hinder the ability to download a CSV
ini_set('memory_limit', '2048M');

if ($dateRange) {
    $query = "SELECT
            r.*,
            GROUP_CONCAT(res.result SEPARATOR ';') AS result_values,
            GROUP_CONCAT(res.product SEPARATOR ';') AS assay_names,
            GROUP_CONCAT(res.flag SEPARATOR ';') AS result_flags,
            GROUP_CONCAT(res.ct_values SEPARATOR ';') AS ct_values
        FROM master_results r
        LEFT JOIN results res ON res.master_result = r.id
        WHERE STR_TO_DATE(end_time, '%Y-%m-%d %H:%i') BETWEEN ? AND ?
        GROUP BY r.id;
    ";

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
    $query = "SELECT
            r.*,
            GROUP_CONCAT(res.result SEPARATOR ';') AS result_values,
            GROUP_CONCAT(res.product SEPARATOR ';') AS assay_names,
            GROUP_CONCAT(res.flag SEPARATOR ';') AS result_flags,
            GROUP_CONCAT(res.ct_values SEPARATOR ';') AS ct_values
        FROM master_results r
        LEFT JOIN results res ON res.master_result = r.id
        GROUP BY r.id;
        ";
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