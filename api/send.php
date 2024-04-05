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
    "lotNumber": "",
	"sender": "",
	"version": "",
	"sequenceNumber": "",
	"trackingCode": "",
	"abortErrorCode": "",
	"assayStepNumber": "",
	"reserve1": "",
	"reserve2": "",
	"cameraReading": "",
    
	The following is not to be saved in the DB, only in a CSV!
    "curve_data_1": "0.123,0.1241,0.32121,0.2132141,0.213213,…",
	"curve_data_2": "0.123,0.1241,0.32121,0.2132141,0.213213,…",
	"curve_index": "123567", (/var/www/html/curves/123567.csv)
	"flags": ""
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

// We have data! Now we must clean it, add the result to the results table and make a CSV file.


$response = "API Unfinished.";

// Provide the response
echo json_encode(["status" => $response]);
