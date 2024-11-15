<?php // Check if the specified product and lot number has been QC tested.

/*
Get productCode, lot, moduleSerialNumber
Return status, isValid(bool)
Codes:
    - 50: Success
*/

// Get moduleSerialNumber from "sn" URL parameter (if we have no data, quit)
if(!($sn = ($_GET['sn'] ?? null))) {
    // Throw error and stop processing things.
    echo(json_encode(["Error" => "No data available."]));
    addLogEntry('API', "ERROR: /instrument unable to find parameters.");
    exit;
}

// Look up the module in the DB and get its ID
$instrument = getInstrumentSnapshot(null, $sn);

// If we, for some reason don't have information about this instrument, add the instrument!
if(!$instrument) {
    updateInstruments(["moduleSerialNumber" => $sn]);
}

// Now we should have the instrument
$instrument = getInstrumentSnapshot(null, $sn);

// Get all possible test types
$testTypes = getInstrumentQCTypes();

// Now return the instrument data to the tablet
if($instrument) {
    $response = [
        "locked" => ($instrument['locked'] && $instrument['locked']),
        "tests" => [],
    ];

    // Add the test results sensibly to the response
    foreach($instrument['qc']['res'] as $testResult) {
        if($testResult && $testTypes[$testResult['type']]) {
            $response['tests'][$testTypes[$testResult['type']]['name']] = getInstrumentQCOutcome($testResult);
        }
    }
} else {
    $response = [
        'error' => 'Something went wrong adding the instrument to the database.',
    ];
    addLogEntry('API', "ERROR: /instrument - instrument not found in the DMS and could not be added for some reason.");
}

// Provide the response
echo json_encode(["status" => $response]);

