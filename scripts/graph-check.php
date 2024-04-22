<?php // Graph Check
include_once __DIR__ . "/../includes/config.php";

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Fetch data from the id.csv if it exists and return it as JSON data
    // Check if CSV file exists for the given ID
    $csvFile = BASE_DIR . "/curves/$id.csv";

    if(file_exists($csvFile)) {
        // Read the CSV file into an array
        $csvData = array_map('str_getcsv', file($csvFile));
        
        // Extract X and Y data from the CSV data
        $xData = array_shift($csvData); // First row contains X values
        $yData = $csvData; // Remaining rows contain Y values
        
        // Construct data array for JSON output
        $data = array();
        foreach($yData as $index => $row) {
            $rowData = array();
            foreach($row as $key => $value) {
                $rowData[] = array("x" => $xData[$key], "y" => $value);
            }
            $data[] = $rowData;
        }
        
        // Output data in JSON format
        echo json_encode($data);
    } else {
        // File doesn't exist, return message
        echo json_encode(["error" => 1]);
    }
} else {
    echo json_encode(["error" => 0]);
}
