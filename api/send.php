<?php // Send a test result to the DMS from the tablet (and, optionally, to LIMS, but we needn't do anything here for that)

/*
Get: {
    "version": "A", // SAMBA 3.X otherwise it's SAMBA II (2.0)

    "assayType": "89",
    "assaySubType": "01",
    "assayName": "Covid + HIV-1 Blood Qual"
    "lotNumber": "1234",
    "subLotNumber": "12",
    "expirationDate": "2024/09",

    "sampleId": "S8427",
    "patientId": "P02446782",
    "patientFirstName": "John",
    "patientLastName": "Smith",
    "patientAge": "23",
    "patientDoB": "1975-07-24",
    "patientSex": "M",
    "hospitalId": "38807462",
    "nhsNumber": "1234567890",
    "patientLocation": "Ward 10",
    "collectedTime": "2018-09-23 14:50",
    "receivedTime": "2018-09-24 08:10",
    "comment1": "Blah blah blah",
    "comment2": "",

    "startTime": "2018-09-24 09:13",
    "endTime": "2018-09-24 11:09",
    "testPurpose": "1",
    "deviceError": "",
    "expectedResult": "All Positive (free text field)",

    "clinicId": "Clinic Beta",
    "site": "Test Site Alpha",
    "operatorId": "Smith",
    "moduleSerialNumber": "89674523",

    "result": [
        {
            "control": {
                "result": "Positive",
                "ct": 24
            }
            "targetResults": [
                {
                    "name": "FluA",
                    "result": "Positive",
                    "ct": "25",
                },
                {
                    "name": "FluB",
                    "result": "Negative",
                    "ct": "0.23",
                },
                    "name": "RSV",
                    "result": "Negative",
                    "ct": "0.47",
            ]
        },
        {
            "control": {
                "result": "Positive",
                "ct": "22"
            },
            "targetResults": [
                {
                    "name": "HIV-1",
                    "result": "Negative",
                    "ct": "0.85"
                }
            ]
        }
    ],

	The following is not to be saved in the DB, only in a CSV!
    "curveData": {
        "SCoV": "0.123,0.1241,0.32121,0.2132141,0.213213,…",
        "FluA": "0.123,0.1241,0.32121,0.2132141,0.213213,…",
    }
}
Return status

Codes:
    For SAMBA II only.
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

// // Log data to a text file
// $logFile = __DIR__ . '/../logs/send-log.txt'; // Specify the path to your log file
// $logData = print_r($data, true); // Format the data as a string

// // Append data to the log file
// file_put_contents($logFile, $logData . "\n\n", FILE_APPEND);

// We have data! Now we must clean it, add the result to the results table and make a CSV file. Separate what needs to go in the db and what needs to be a CSV.
$errors = null;

// Check that the lot number exists in the DB (as it relies on a foreign key)
$lotNumber = null;
if($data['lotNumber']) {
    // Update the lot number in our DB
    $lotID = updateLot($data['lotNumber']);
}

// TODO: Do the same thing for Instrument eventually

// We need to verify the test purpose
$purpose = 0;
if($data['testPurpose']) {
    /*
        Purpose:
        0 - Unknown
        1 - Patient Result
        2 - QC Test
        3 - Training
    */

    $purpose = $data['testPurpose'];

    // If the purpose is anything other than those listed above, change it to 0.
    if(!in_array($purpose, [0, 1, 2, 3])) {
        $purpose = 0;
    }
}

// Add the data to an array which can be iterated over to add each field to the db.
$dbData = [
    "version" => $data['version'] ?? "",

    "assayType" => $data['assayType'] ?? "",
    "assaySubType" => $data['assaySubType'] ?? "",
    // This has been changed to assayName on the tablet
    "product" => $data['assayName'] ?? "",
    "lot_number" => $lotNumber ?? null,

    "sampleId" => $data['sampleId'] ?? "",
    "patientId" => $data['patientId'] ?? "",
    // Names have been adjusted on tablet
    "firstName" => $data['patientFirstName'] ?? "",
    "lastName" => $data['patientLastName'] ?? "",
    "patientAge" => $data['patientAge'] ?? "",
    // This has been changed to patientDoB on the tablet
    "dob" => $data['patientDoB'] ?? "",
    "patientSex" => $data['patientSex'] ?? "",
    "hospitalId" => $data['hospitalId'] ?? "",
    "nhsNumber" => $data['nhsNumber'] ?? "",
    "patientLocation" => $data['patientLocation'] ?? "",
    // This has been changed to collectedTime on the tablet
    "sampleCollected" => $data['collectedTime'] ?? "",
    // This has been changed to receivedTime on the tablet
    "sampleReceived" => $data['receivedTime'] ?? "",
    // These have also been changed to comment1 and comment2 on the tablet
    "reserve1" => $data['comment1'] ?? "",
    "reserve2" => $data['comment2'] ?? "",

    "flag" => $data['flag'] ?? 100,

    // This has been changed to startTime on the tablet
    "timestamp" => $data['startTime'] ?? "",
    // This has been changed to endTime on the tablet
    "testcompletetimestamp" => $data['endTime'] ?? "",
    "testPurpose" => $purpose,
    // This has been changed to deviceError on the tablet
    "abortErrorCode" => $data['deviceError'] ?? "",

    "clinicId" => $data['clinicId'] ?? "",
    "site" => $data['site'] ?? "",
    "operatorId" => $data['operatorId'] ?? "",
    "moduleSerialNumber" => $data['moduleSerialNumber'] ?? "",

    "post_timestamp" => time(),

    // We are expecting the result in the format described above
    "result" => json_encode($data['result']) ?? "",
];

/*
Since DMS Version 3.1.0, Results look different. They used to take the following form:
    "SARS-CoV-2: 1Negative, RSV: 2Negative, FluB: 3Positive, FluA: 4Negative"

Results now take on a marge larger form and are sent in a JSON blob which looks like this:
    [
        {
            "control": {
                "result": "Positive",
            }
            "targetResults": [
                {
                    "name": "FluA",
                    "result": "Positive",
                },
                {
                    "name": "FluB",
                    "result": "Negative",
                },
                    "name": "RSV",
                    "result": "Negative",
            ]
        },
        {
            "control": {
                "result": "Positive",
            },
            "targetResults": [
                {
                    "name": "HIV-1",
                    "result": "Negative",
                }
            ]
        }
    ],

- If ANY control result is "Negative" or "Invalid", then the whole result is "Invalid". Results should still be stored but the summary should show it as "Invalid".
- If ALL controls are "Positive":
    - If ANY target is "Invalid", the result summary is "Invalid"
    - If ANY target is "Positive" (and none are "Invalid"), the result summary is "Positive"
    - If ALL targets are "Negative", the result summary is "Negative"

Now, we have a new field in the results table called "summary", this marks the source of truth and prevents the need for parsing when displaying on the DB. We must manipulate the
data to make it fit in the previous format that LIMS is familiar with.

! This may change in the future.
*/

// That being said, sanitise the results.
$sanitisedResult = sanitiseResult($dbData['result']);

// Now replace that result with the sanitised version and add a summary.
$dbData['result'] = resultStringify($sanitisedResult['result']);
$dbData['summary'] = $sanitisedResult['summary'];

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
    $resultID = $cainDB->conn->lastInsertId();

    // Commit the transaction
    $cainDB->commit();
} catch(PDOException $e) {
    // Rollback the transaction on error
    $cainDB->rollBack();
    $errors = $e;
}

// If the test purpose was QC, we now need to add QC information to the DMS
if($purpose == 2) {
    // Add to QC
    $success = newLotQC($lotID, $resultID, $dbData['timestamp'] !== "" ? $dbData['timestamp'] : $dbData['post_timestamp']);

    if(!$success) {
        $errors = "Something went wrong putting the QC test in the DMS. Please contact an admin.";
    }
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
        $filePath = "$directory/$resultID.csv";

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
    $response["resultID"] = $resultID;
}

// Provide the response
echo json_encode($response);
