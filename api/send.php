<?php // Send a test result to the DMS from the tablet (and, optionally, to LIMS, but we needn't do anything here for that)

/*
Get: {
    "site": "Test Site Alpha",
    "testStartTimestamp": "2018-09-24 09:13",
    "testCompleteTimestamp": "2018-09-24 11:09",
    "clinicId": "Clinic Beta",
    "operatorId": "Smith",
    "moduleSerialNumber": "1098",
    "patientId": "P02446782",
    "patientLocation": "Ward 10",
    "firstName": "John",
    "lastName": "Smith",
    "hospitalId": "38807462",
    "nhsNumber": "1234567890",
    "dob": "1975-07-24",
    "patientAge": "23",
    "patientSex": "M",
    "sampleId": "S8427",
    "sampleCollected": "2018-09-23 14:50",
    "sampleReceived": "2018-09-24 08:10",
    "product": "HIV-1 Blood Qual",
    "result": "TBD",
    "testPurpose": "1",
    ?"lotNumber": "",? What is this
	"sender": "",
	"version": "",
	"sequenceNumber": "",
	"trackingCode": "",
	"abortErrorCode": "",
	"assayStepNumber": "",
	"reserve1": "",
	"reserve2": "",
	"cameraReading": "",
	"flag": ""
    
	The following is not to be saved in the DB, only in a CSV!
    "curveData": {
        "SCoV": "0.123,0.1241,0.32121,0.2132141,0.213213,…",
        "FluA": "0.123,0.1241,0.32121,0.2132141,0.213213,…",
    }
}
Return status

Codes:
    - 6: Result received but not yet processed.
    - 7: Send is being processed
    - 8: Send completed successfully.

Steps:
- Collect data from the API POST
- Add data to the database (everything but the curve info), set "flag" to 100, ignore bits. Al will process this then change it to 101. (101 === SENT TO LIMS)
- Get the ID of the result just added the database
- Create a CSV with curve information in /var/www/html/curves/<id>.csv
    - This CSV will have up to 6 rows and up to 45 columns
- Return status '8' to signify that we're done

Curve data will be as follows:
- 'curve_data_1' -> up to 45 data points
- 'curve_data_2' -> up to 45 data points
- Up to 6 curves rendered per test
*/

// Firstly, if we have no data, quit.
if(!$data) {
    // Throw error and stop processing things.
    echo(json_encode(["status" => 10]));
    exit;
}

$errors = null;

// Firstly, we need to check that the result isn't already in the database

// We have data! Now we must clean it, add the result to the results table and make a CSV file. Separate what needs to go in the db and what needs to be a CSV.
$dbData = [
    "sender" => $data['sender'] ?? "",
    "version" => $data['version'] ?? "",
    "sequenceNumber" => $data['sequenceNumber'] ?? "",
    "site" => $data['site'] ?? "",
    "firstName" => $data['firstName'] ?? "",
    "lastName" => $data['lastName'] ?? "",
    "dob" => $data['dob'] ?? "",
    "hospitalId" => $data['hospitalId'] ?? "",
    "nhsNumber" => $data['nhsNumber'] ?? "",
    "timestamp" => $data['timestamp'] ?? "",
    "testCompleteTimestamp" => $data['testCompleteTimestamp'] ?? "",
    "clinicId" => $data['clinicId'] ?? "",
    "operatorId" => $data['operatorId'] ?? "",
    "moduleSerialNumber" => $data['moduleSerialNumber'] ?? "",
    "patientId" => $data['patientId'] ?? "",
    "patientAge" => $data['patientAge'] ?? "",
    "patientSex" => $data['patientSex'] ?? "",
    "sampleId" => $data['sampleId'] ?? "",
    "trackingCode" => $data['trackingCode'] ?? "",
    "product" => $data['product'] ?? "",
    "result" => $data['result'] ?? "",
    "testPurpose" => $data['testPurpose'] ?? "",
    "abortErrorCode" => $data['abortErrorCode'] ?? "",
    "assayStepNumber" => $data['assayStepNumber'] ?? "",
    "cameraReadings" => $data['cameraReading'] ?? "",
    "patientLocation" => $data['patientLocation'] ?? "",
    "reserve1" => $data['reserve1'] ?? "",
    "reserve2" => $data['reserve2'] ?? "",
    "sampleCollected" => $data['sampleCollected'] ?? "",
    "sampleReceived" => $data['sampleReceived'] ?? "",
    "flag" => $data['flag'] ?? 100,
    "post_timestamp" => time(),
];

// Everything is in the database, not much is allowed to be null and most things instead default to an empty string.
$query = "INSERT INTO results (";
$query .= implode(", ", array_keys($dbData));
$query .= ") VALUES (";
$query .= implode(", ", array_fill(0, count($dbData), "?"));
$query .= ")";

// Fill array with values
$params = array_values($dbData);

try {
    // Begin transaction to ensure safety
    $cainDB->beginTransaction();
    
    // Add items to db
    $cainDB->query($query, $params);
    
    // Get the last inserted ID
    $resultId = $cainDB->conn->lastInsertId();
    
    // Commit the transaction
    $cainDB->commit();
} catch(PDOException $e) {
    // Rollback the transaction on error
    $cainDB->rollBack();
    $errors = $e;
}

if($errors) {
    $response["status"] = $errors;
} else {
    // Add the CSV data: we may have up to 6 curves.
    $csvData = [];

    // Max data points
    $maxDataPoints = 0;

    if(isset($data["curveData"])) {
        foreach($data["curveData"] as $curveTitle => $curveData) {
            // Parse curve data into an array
            $curveValues = explode(",", $curveData);

            // Set the max data points to be the larger of itself or the length of the curve values
            $maxDataPoints = max($maxDataPoints, count($curveValues));
            
            // Add curve data to $csvData array
            $csvData[$curveTitle] = $curveValues;
        }
    }

    if($csvData) {
        // Directory to store CSV files
        $directory = BASE_DIR . '/curves';

        // Ensure the directory exists
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true); // Create directory recursively
        }

        // Define file path
        $filePath = "$directory/$resultId.csv";

        // Open file for writing
        $file = fopen($filePath, "w");

        // Write CSV header
        fputcsv($file, array_merge(["0"], range(1, $maxDataPoints))); // Adding "Curve" as the first column header

        // Write CSV rows
        foreach ($csvData as $curveTitle => $curveValues) {
            // Combine curve title with its values
            $rowData = array_merge([$curveTitle], $curveValues);
            fputcsv($file, $rowData);
        }

        // Close file
        fclose($file);
    }

    // Let the user know it's being processed
    $response["status"] = 7;

    // Give the user the unique ID of the result because why not
    $response["resultId"] = $resultId;
}

// Provide the response
echo json_encode($response);
