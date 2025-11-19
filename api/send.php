<?php // Send a test result to the DMS from the tablet (and, optionally, to LIMS, but we needn't do anything here for that)

/*
  Use this code in the tablet app to run a test example.
  244400101012025129
/*

/*
Get: {
    "version": "A", // SAMBA 3.X otherwise it's SAMBA II (2.0)

    "assayType": "89",
    "assaySubType": "01",
    "assayName": "Covid + HIV-1 Blood Qual",
    "lotNumber": "1234",
    "subLotNumber": "12",
    "expirationDate": "2024/09", ** CHANGED TO expiryYear & expiryMonth **

    ADDITIONS:
    "productionYear": "24",
    "assayId": 572,
    "x": "Combined Targets Test",
    "assayVersion": "0.1",

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

    "clinicId": "Clinic Beta", ** REMOVED? **
    "site": "Test Site Alpha", ** REMOVED? **
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

    assayTargetName is always going to be FluA etc
    resultValueString is always the result. Ignore the enum
    Ignore chamber and channel

    !! ** RESULT HAS CHANGED - JAN 2025 ** !!
    "result": {
    "signature": {
      "signature": "MEUCIQDcC71qL2wFV…",
      "instrumentId": "SIII-FC0FE7-E6E509"
    },
    "safetyCriticalCrc32": 4122234951,
    "results": [
      {
        "singleResult": {
          "chamber": "VIRTUAL_CHAMBER_A",
          "channel": 1,
          "result": {
            "resultValueEnum": "POSITIVE",
            "resultValueString": "Positive",
            "target": {
              "assayTargetName": "Positive"
            },
            "ct": -5.283018589019775
          }
        }
      },
      {
        "singleResult": {
          "chamber": "VIRTUAL_CHAMBER_A",
          "channel": 5,
          "result": {
            "resultValueEnum": "NEGATIVE",
            "resultValueString": "Negative",
            "target": {
              "assayTargetName": "Negative"
            }
          }
        }
      },
      {
        "combinedResult": {
          "overallResult": {
            "resultValueEnum": "POSITIVE",
            "resultValueString": "Positive",
            "target": {
              "assayTargetName": "Combined Positive"
            }
          },
          "constituentResults": [
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 1,
              "result": {
                "resultValueEnum": "POSITIVE",
                "resultValueString": "Positive",
                "target": {
                  "assayTargetName": "Positive"
                },
                "ct": -5.283018589019775
              }
            },
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 2,
              "result": {
                "resultValueEnum": "POSITIVE",
                "resultValueString": "Positive",
                "target": {
                  "assayTargetName": "Positive 2"
                },
                "ct": -4.369657516479492
              }
            },
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 3,
              "result": {
                "resultValueEnum": "POSITIVE",
                "resultValueString": "Positive",
                "target": {
                  "assayTargetName": "Positive 3"
                },
                "ct": 3.5911951065063477
              }
            },
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 4,
              "result": {
                "resultValueEnum": "POSITIVE",
                "resultValueString": "Positive",
                "target": {
                  "assayTargetName": "Positive 4"
                },
                "ct": 2.396226644515991
              }
            },
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 5,
              "result": {
                "resultValueEnum": "NEGATIVE",
                "resultValueString": "Negative",
                "target": {
                  "assayTargetName": "Negative"
                }
              }
            }
          ]
        }
      },
      {
        "combinedResult": {
          "overallResult": {
            "resultValueEnum": "NEGATIVE",
            "resultValueString": "Negative",
            "target": {
              "assayTargetName": "Combined Negative"
            }
          },
          "constituentResults": [
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 1,
              "result": {
                "resultValueEnum": "POSITIVE",
                "resultValueString": "Positive",
                "target": {
                  "assayTargetName": "Positive"
                },
                "ct": -5.283018589019775
              }
            },
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 2,
              "result": {
                "resultValueEnum": "POSITIVE",
                "resultValueString": "Positive",
                "target": {
                  "assayTargetName": "Positive 2"
                },
                "ct": -4.369657516479492
              }
            },
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 3,
              "result": {
                "resultValueEnum": "POSITIVE",
                "resultValueString": "Positive",
                "target": {
                  "assayTargetName": "Positive 3"
                },
                "ct": 3.5911951065063477
              }
            },
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 4,
              "result": {
                "resultValueEnum": "POSITIVE",
                "resultValueString": "Positive",
                "target": {
                  "assayTargetName": "Positive 4"
                },
                "ct": 2.396226644515991
              }
            },
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 5,
              "result": {
                "resultValueEnum": "NEGATIVE",
                "resultValueString": "Negative",
                "target": {
                  "assayTargetName": "Negative"
                }
              }
            }
          ]
        }
      },
      {
        "combinedResult": {
          "overallResult": {
            "resultValueEnum": "POSITIVE",
            "resultValueString": "Positive",
            "target": {
              "assayTargetName": "Single Positive"
            }
          },
          "constituentResults": [
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 1,
              "result": {
                "resultValueEnum": "POSITIVE",
                "resultValueString": "Positive",
                "target": {
                  "assayTargetName": "Positive"
                },
                "ct": -5.283018589019775
              }
            }
          ]
        }
      },
      {
        "combinedResult": {
          "overallResult": {
            "resultValueEnum": "NEGATIVE",
            "resultValueString": "Negative",
            "target": {
              "assayTargetName": "Single Negative"
            }
          },
          "constituentResults": [
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 5,
              "result": {
                "resultValueEnum": "NEGATIVE",
                "resultValueString": "Negative",
                "target": {
                  "assayTargetName": "Negative"
                }
              }
            }
          ]
        }
      },
      {
        "combinedResult": {
          "overallResult": {
            "resultValueEnum": "POSITIVE",
            "resultValueString": "Positive",
            "target": {
              "assayTargetName": "Multiple Positive"
            }
          },
          "constituentResults": [
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 1,
              "result": {
                "resultValueEnum": "POSITIVE",
                "resultValueString": "Positive",
                "target": {
                  "assayTargetName": "Positive"
                },
                "ct": -5.283018589019775
              }
            },
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 5,
              "result": {
                "resultValueEnum": "NEGATIVE",
                "resultValueString": "Negative",
                "target": {
                  "assayTargetName": "Negative"
                }
              }
            }
          ]
        }
      },
      {
        "combinedResult": {
          "overallResult": {
            "resultValueEnum": "NEGATIVE",
            "resultValueString": "Negative",
            "target": {
              "assayTargetName": "Multiple Negative"
            }
          },
          "constituentResults": [
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 1,
              "result": {
                "resultValueEnum": "POSITIVE",
                "resultValueString": "Positive",
                "target": {
                  "assayTargetName": "Positive"
                },
                "ct": -5.283018589019775
              }
            },
            {
              "chamber": "VIRTUAL_CHAMBER_A",
              "channel": 5,
              "result": {
                "resultValueEnum": "NEGATIVE",
                "resultValueString": "Negative",
                "target": {
                  "assayTargetName": "Negative"
                }
              }
            }
          ]
        }
      }
    ],
    "overallResultValueEnum": "SOME_POSITIVE",
    "overallResultValueString": "Some Positive" can be some positive, all negative, invalid or error
},

** INVALID RESULT EXAMPLE **
"result": {
    "signature": {
      "signature": "MEUCICX8cILPc2q9IU3…",
      "instrumentId": "SIII-FC0FE7-E6E509"
    },
    "safetyCriticalCrc32": 1248536912,
    "overallResultValueEnum": "INVALID",
    "overallResultValueString": "Invalid"
  }


	The following is not to be saved in the DB, only in a CSV!
    "curveData": {
        "SCoV": "0.123,0.1241,0.32121,0.2132141,0.213213,…",
        "FluA": "0.123,0.1241,0.32121,0.2132141,0.213213,…",
    }
}

! Combined results DB structure and LIMS compatibility:

LIMS watches the results table, which needs to remain in tact to ensure backwards compatibility and to prevent specification changes to the LIMS middleware.
This means, unfortunately, duplicate data is potentially necessary.
Each SENT result now potentially shows, for one patient and one test, MANY constituent results.
For LIMS, this must be broken down into individual results and combined.

We do this by having a `master_results` table which contains all the shared information.
Information is first passed to the master_results table. This is the source of truth for the DMS.
We then have the `results` table as specified by the LIMS middleware. This table will, unfortunately, need some repeated information which ONLY LIMS will access.
The `results` table has a field for the `master_results` foreign key reference. This is to form a many-to-one relationship with the masters.
The DMS then displays results from the `master_results` table.

Codes:
    For SAMBA II only.
    - 6: Result received but not yet processed.
    - 7: Send is being processed
    - 8: Send completed successfully.

	These codes are important as any result sent via SAMBA II may need to be sent again. If it's sent again via this protocol, we should check the database. If there's
	a match based on the fields, we should ignore and return 6 if the flag is 100. If the flag is 101, we should actually delete this result from the database and return "8".
	FOR NOW, this API endpoint will just ALWAYS return 8. The DMS IS LIMS as far as tablets are concerned. We separate these concerns with SAMBA III.

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

The following is still relevant for results with versions below 3.X
- If ANY control result is "Negative" or "Invalid", then the whole result is "Invalid". Results should still be stored but the summary should show it as "Invalid".
- If ALL controls are "Positive":
    - If ANY target is "Invalid", the result summary is "Invalid"
    - If ANY target is "Positive" (and none are "Invalid"), the result summary is "Positive"
    - If ALL targets are "Negative", the result summary is "Negative"

Now, we have a new field in the results table called "summary", this marks the source of truth and prevents the need for parsing when displaying on the DB. We must manipulate the
data to make it fit in the previous format that LIMS is familiar with.

! This may change in the future.
!!! It did: (v3.2.0)
*/

// Firstly, if we have no data, quit.
if(!$data) {
    // Throw error and stop processing things.
    echo(json_encode(["status" => 10]));
    addLogEntry('API', "ERROR: /send unable to find parameters.");
    exit;
}

// We have data! Now we must clean it, add the result to the results table and make a CSV file. Separate what needs to go in the db and what needs to be a CSV.
$errors = null;

// Check that the lot number exists in the DB (as it relies on a foreign key)
$lotNumber = null;
if(isset($data['lotNumber'])) {
    // Create the expiration date
    $expirationDate = null;
    $deliveryDate = null;

    if((isset($data['expiryYear']) && $data['expiryYear'] !== "")) {
        $expiryYear = intval($data['expiryYear']);
        $expiryMonth = (isset($data['expiryMonth']) && $data['expiryMonth'] !== "") ? intval($data['expiryMonth']) : 1;
        $expirationDate = sprintf("%04d-%02d-01 00:00:00", $expiryYear, $expiryMonth);
    }

    // Update the lot number in our DB
    $lotID = updateLot($data['lotNumber'], [
        'assay_type' => $data['assayType'] ?? null,
        'assay_sub_type' => $data['assaySubType'] ?? null,
        'sub_lot_number' => $data['subLotNumber'] ?? null,
        'expiration_date' => $expirationDate,
        'production_year' => isset($data['productionYear']) && $data['productionYear'] !== "" ? intval($data['productionYear']) : null,
    ]);
    
    $lotNumber = $data['lotNumber'];
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

// This file has undergone many changes. We track which path the data should take from here on via its version number.
$version = $data['version'] ?? null;

// No matter the API version, we will always have a result of some sort.
$result = $data['result'] ?? null;

// We have some sort of result! We must first try to interpret it. We may have multiple results so create a results array.
$results = [];

// We will also need some kind of summary
$resultSummary = null;

// Check the version
if(!isValidAPIVersion($version)) {
    /*
        This means we have a legacy (pre-3.0) API version.
        This will only ever need to be broken into ONE master_result and ONE result.
        The result may either be simply Positive or Negative, or it may be some combination for multiplex tests.
        Sanitise the result and create something we can put into the database.
    */

    /*
        Some keywords have been changed between versions so these need to be kept track of and accounted for.
        Some additions have also been made so we should not necessarily treat an empty value as invalid.
    */

    /*
        SEND 2 LEGACY PARAM NAME TRANSFORMS:
        testStartTimestamp    => startTime
        testCompleteTimestamp => endTime
        firstName             => patientFirstName
        lastName              => patientLastName
        dob                   => patientDoB
        sampleCollected       => collectedTime
        sampleReceived        => receivedTime
        product               => assayName
        abortErrorCode        => deviceError
        reserve1              => comment1
        reserve2              => comment2
    */

    // Transform the variable names
    $data['startTime'] = getValueOrFallback($data['startTime'] ?? null, $data['testStartTimestamp'] ?? null);
    $data['endTime'] = getValueOrFallback($data['endTime'] ?? null, $data['testCompleteTimestamp'] ?? null);
    $data['patientFirstName'] = getValueOrFallback($data['patientFirstName'] ?? null, $data['firstName'] ?? null);
    $data['patientLastName'] = getValueOrFallback($data['patientLastName'] ?? null, $data['lastName'] ?? null);
    $data['patientDoB'] = getValueOrFallback($data['patientDoB'] ?? null, $data['dob'] ?? null);
    $data['collectedTime'] = getValueOrFallback($data['collectedTime'] ?? null, $data['sampleCollected'] ?? null);
    $data['receivedTime'] = getValueOrFallback($data['receivedTime'] ?? null, $data['sampleReceived'] ?? null);
    $data['assayName'] = getValueOrFallback($data['assayName'] ?? null, $data['product'] ?? null);
    $data['deviceError'] = getValueOrFallback($data['deviceError'] ?? null, $data['abortErrorCode'] ?? null);
    $data['comment1'] = getValueOrFallback($data['comment1'] ?? null, $data['reserve1'] ?? null);
    $data['comment2'] = getValueOrFallback($data['comment2'] ?? null, $data['reserve2'] ?? null);

    // If the data is not a string, json_encode it!
    if(!is_string($data['result'])) {
        $sanitisedResult = sanitiseResult(json_encode($data['result']));
    } else {
        // This will return a result and a summary
        $sanitisedResult = sanitiseResult($data['result']);
    }

    // Get the summary
    $resultSummary = $sanitisedResult['summary'];

    // Add this to the results
    $results = [
        $data['assayName'] => [
            'result'   => resultStringify($sanitisedResult['result']),
            'ct_values' => $sanitisedResult['ct'],
        ]
    ];
} else {
    // This means we have a post-3.0 API version.

    // We are given a summary
    $resultSummary = $data['result']['overallResultValueString'] ?? "Invalid";

    // Get the result array
    $resultArray = $data['result']['results'] ?? null;

    // We are also given many results. Parse them all
    if(is_array($resultArray)) {

        foreach($resultArray as $individualResultArr) {
            // We know that the first array key tells us what the result type is
            $resultType = array_key_first($individualResultArr);

            // Assign the actual result to a variable
            $individualResult = $individualResultArr[$resultType];

            // If we have a single result, proceed as normal
            if($resultType == 'singleResult') {
                // This will return a result and a summary
                $sanitisedResult = sanitiseResult($individualResult['result']['resultValueString']);

                // Add this to the results
                $results += [
                    $individualResult['result']['target']['assayTargetName'] => [
                        'result' => resultStringify($sanitisedResult['result']),
                        'overall_result' => resultStringify($sanitisedResult['result']),
                        'ct_values' => $individualResult['result']['ct'] ?? 'x',
                    ]
                ];
            } elseif($resultType == 'combinedResult') {
                // If we have multiple results, loop through them and add a multiplex-style result
                $combinedResultJSON = $individualResultArr[$resultType];

                // Overall result for the combined result
                $overallResult = $combinedResultJSON['overallResult']['resultValueString'] ?? 'Invalid';

                // Set up a counter to keep track of the result we have for LIMS
                $i = 0;

                // Set up a result string
                $resultString = "";

                // Set up a ct string
                $ctString = "";

                // Get the constituent results
                foreach($combinedResultJSON['constituentResults'] as $constituentResult) {
                    // We want results in the form: SARS-CoV-2: 1Negative, RSV: 2Positive
                    if($i !== 0) {
                        // Add the comma separation logic if we aren't on the first result
                        $resultString .= ", ";
                        $ctString .= ",";
                    }

                    // Add to the result string
                    $resultString .= $constituentResult['result']['target']['assayTargetName'] . ": " . ($i + 1) . $constituentResult['result']['resultValueString'];

                    // Add to the CT String
                    $ctString .= $constituentResult['result']['ct'] ?? "x";

                    // Increment counter
                    $i++;
                }

                // Confusingly, the result field needs to provide the unparsed overall result, and the overall_result field needs to show the parsed result for the DMS
                $results += [
                    $combinedResultJSON['overallResult']['target']['assayTargetName'] => [
                        'overall_result' => $resultString,
                        'ct_values' => cleanValue($ctString),
                        'result' => $overallResult,
                    ]
                ];
            }

        }
    } else {
        // This means we may have an invalid result
        $results += [
            ($data['assayName'] ?? "Unknown") => [
                'result' => "Invalid",
                'ct_values' => null,
                'overall_result' => "Invalid",
            ]
        ];
    }
}

// Again, if our results are empty at this point, throw an error:
if(empty($results)) {
    $errors = "Unable to parse result. Please contact an administrator.";
} else {
    // Parse the data for the master_results table
    $masterData = [
        "version"              => $version,
        "patient_id"           => cleanValue($data['patientId'] ?? null),
        "age"                  => cleanValue($data['patientAge'] ?? null),
        "sex"                  => cleanValue($data['patientSex'] ?? null),
        "first_name"           => cleanValue($data['patientFirstName'] ?? null),
        "last_name"            => cleanValue($data['patientLastName'] ?? null),
        "dob"                  => cleanValue($data['patientDoB'] ?? null),
        "nhs_number"           => cleanValue($data['nhsNumber'] ?? null),
        "hospital_id"          => cleanValue($data['hospitalId'] ?? null),
        "location"             => cleanValue($data['patientLocation'] ?? null),
        "collected_time"       => cleanValue($data['collectedTime'] ?? null),
        "received_time"        => cleanValue($data['receivedTime'] ?? null),
        "start_time"           => cleanValue($data['startTime'] ?? null),
        "end_time"             => cleanValue($data['endTime'] ?? null),
        "comment_1"            => cleanValue($data['comment1'] ?? null),
        "comment_2"            => cleanValue($data['comment2'] ?? null),
        "operator_id"          => cleanValue($data['operatorId'] ?? null),
        "test_purpose"         => cleanValue($data['testPurpose'] ?? null),
        "device_error"         => cleanValue($data['deviceError'] ?? null),
        "module_serial_number" => cleanValue($data['moduleSerialNumber'] ?? null),
        "lot_number"           => cleanValue($lotNumber) ?? null,
        "assay_name"           => cleanValue($data['assayName'] ?? null),
        "assay_id"             => cleanValue($data['assayId'] ?? null),
        "assay_type"           => cleanValue($data['assayType'] ?? null),
        "expected_result"      => cleanValue($data['expectedResult'] ?? null),
        "result"               => $resultSummary
    ];

    // Add the data to an array which can be iterated over to add each field to the db. THIS IS ESSENTIALLY WHAT IS SENT TO LIMS
    $resultData = [
        "version"               => $data['version'] ?? "",
        "sampleid"              => $data['patientId'] ?? "",
        "patientId"             => $data['patientId'] ?? "",
        "sample_tube_id"        => $data['sampleId'] ?? "",
        "firstName"             => $data['patientFirstName'] ?? "",
        "lastName"              => $data['patientLastName'] ?? "",
        "patientAge"            => $data['patientAge'] ?? "",
        "dob"                   => $data['patientDoB'] ?? "",
        "patientSex"            => $data['patientSex'] ?? "",
        "hospitalId"            => $data['hospitalId'] ?? "",
        "nhsNumber"             => $data['nhsNumber'] ?? "",
        "patientLocation"       => $data['patientLocation'] ?? "",
        "sampleCollected"       => $data['collectedTime'] ?? "",
        "sampleReceived"        => $data['receivedTime'] ?? "",
        "reserve1"              => $data['comment1'] ?? "",
        "reserve2"              => $data['comment2'] ?? "",
        "flag"                  => $data['flag'] ?? 100,
        "timestamp"             => enforceSecondResolution($data['startTime'] ?? ""),
        "testcompletetimestamp" => enforceSecondResolution($data['endTime'] ?? ""),
        "testPurpose"           => $purpose,
        "abortErrorCode"        => $data['deviceError'] ?? "",
        "clinicId"              => $data['clinicId'] ?? "",
        "site"                  => $data['site'] ?? "",
        "operatorId"            => $data['operatorId'] ?? "",
        "moduleSerialNumber"    => $data['moduleSerialNumber'] ?? "",
        "post_timestamp"        => time(),

        // Linking with the parent result (filled dynamically)
        "master_result"         => null,

        // This parameter is filled later as there may be multiple results
        "result"                => null,

        // CT Value is passed per result too
        "ct_values"             => null,

        // Also dynamically added
        "product"               => null,
        "result_target"         => null,

        // Also dynamically added
        "overall_result"        => null,

        // Legacy: we still may need a tracking code
        "trackingCode"          => $data['trackingCode'] ?? "",
    ];

    // We need a query for adding things to the master result table
    $masterQuery = "INSERT INTO master_results (";
    $masterQuery .= implode(", ", array_keys($masterData));
    $masterQuery .= ") VALUES (";
    $masterQuery .= implode(", ", array_fill(0, count($masterData), "?"));
    $masterQuery .= ")";

    // Get the master results into an array
    $masterParams = array_values($masterData);

    $masterID = null;
    $resultCount = 0;

    $resultQuery = "INSERT INTO results (";
    $resultQuery .= implode(", ", array_keys($resultData));
    $resultQuery .= ") VALUES (";
    $resultQuery .= implode(", ", array_fill(0, count($resultData), "?"));
    $resultQuery .= ")";

    try {
        // Begin transaction to ensure safety
        $cainDB->beginTransaction();

        // Add master result
        $cainDB->query($masterQuery, $masterParams);

        // Get the master ID
        $masterID = $cainDB->conn->lastInsertId();

        // Add the master ID to the result data
        $resultData['master_result'] = $masterID;

        $masterId = $masterData['id'] ?? 'batch';

        // Empty array of timestamps to understand how to increment
        $usedTs  = [];
        $usedTct = [];

        // Add individual result for each result we are sent
        foreach($results as $assayTargetName => $resultInfo) {
            // Add the individual result to the result data
            $resultData['result'] = $resultInfo['result'];

            // Set the result_target name (as for combined results, this may vary)
            $resultData['result_target'] = $assayTargetName;

            // Set the product name (again, may vary)
            if(count($results) > 1) {
              $resultData['product'] = "*" . $masterData['assay_name'] . "*" . $assayTargetName;
            } else {
              $resultData['product'] = $assayTargetName;
            }

            // Set the CT value
            $resultData['ct_values'] = $resultInfo['ct_values'];

            // Set the overall result
            $resultData['overall_result'] = $resultInfo['overall_result'] ?? null;

            // If the result is not positive/negative, set the flag to 104 if we cannot send the data to LIMS
            if(((!in_array(strtolower((string)$resultData['overall_result']), ['positive', 'negative'], true) && isValidAPIVersion($version)) ||
            str_contains(strtolower((string)$resultData['result']), "invalid")) && !sendInvalidResultsToLIMS()) {
              $resultData['flag'] = 104;
            }

            // Uniquify both timestamps for this master_result
            $resultData['timestamp'] = uniquifyTimestamp($resultData['timestamp'], $usedTs);
            $resultData['testcompletetimestamp'] = uniquifyTimestamp($resultData['testcompletetimestamp'], $usedTct);

            // Get the results into an array
            $resultParams = array_values($resultData);

            $cainDB->query($resultQuery, $resultParams);
            $resultCount++;
        }

        // Commit the transaction
        $cainDB->commit();
    } catch(PDOException $e) {
        // Rollback the transaction on error
        $cainDB->rollBack();

        // Reset the master ID variable
        $masterID = null;

        // Log detailed information securely
        $errorDetails = [
            'error_message' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString(),
            'user_id' => $currentUser['operator_id'] ?? 'unknown',
            'context' => 'Updating general settings'
        ];
        addLogEntry('API', "ERROR: /send - " . json_encode($errorDetails, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    // If the test purpose was QC, we now need to add QC information to the DMS
    if($purpose == 2 && isset($lotID)) {
        // Add to QC
        $success = newLotQC($lotID, $masterID, $resultData['timestamp'] !== "" ? $resultData['timestamp'] : $resultData['post_timestamp']);

        if(!$success) {
            $errors = "Something went wrong putting the QC test in the DMS. Please contact an admin.";
            addLogEntry('API', "ERROR: /send unable to store QC test in the DMS for result $resultID.");
        }
    }
}


// Has the API done its job?
if($errors) {
    // If not, why not?
    $response["status"] = $errors;
} else {
    // We may not have been given any curve data, in which case, this ends here.
    if(isset($data["curveData"])) {
        // We may have some CSV data (up to 6 curves)
        $csvData = [];

        // Max data points
        $maxDataPoints = 0;
        foreach($data["curveData"] as $curveTitle => $curveData) {
            // Parse curve data into an array
            $curveValues = explode(",", $curveData);

            // Set the max data points to be the larger of itself or the length of the curve values
            $maxDataPoints = max($maxDataPoints, count($curveValues));

            // Add curve data to $csvData array
            $csvData[$curveTitle] = $curveValues;
        }

        if($csvData) {
            // Directory to store CSV files
            $directory = BASE_DIR . '/curves';

            // Ensure the directory exists
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true); // Create directory recursively
            }

            // Define file path
            $filePath = "$directory/$masterID.csv";

            // Open file for writing
            $file = fopen($filePath, "w");

            // Write CSV header
            fputcsv($file, array_merge(["0"], range(1, $maxDataPoints)));

            // Write CSV rows
            foreach ($csvData as $curveTitle => $curveValues) {
                // Combine curve title with its values
                $rowData = array_merge([$curveTitle], $curveValues);
                fputcsv($file, $rowData);
            }

            // Close file
            fclose($file);
        }
    }

    // Let the user know it's complete (we are no longer processing things so status 8 is now skipped)
    // $response["status"] = 7;
    $response["status"] = 8;

    // Give the user the unique ID of the result because why not
    $response["resultID"] = $masterID;

    // Additionally, inform of the number of results that were added
    $response['numResults'] = $resultCount;
}

// Provide the response
echo json_encode($response);
