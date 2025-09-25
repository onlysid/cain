<?php
/**
 * Generates 100,000 randomized results and POSTs them to /send.
 * To use, simply run php send-100000.php
 */

ini_set('memory_limit', '2048M');
set_time_limit(0);

$ENDPOINT = 'localhost/send';
$TARGET = 100000;
$okTotal = 0;
$clientSeq = 1;
$CONCURRENCY = 10;

// ----- Random helpers -----
function randMac(): string {
    $octets = [];
    for ($i = 0; $i < 6; $i++) {
        $octets[] = strtoupper(str_pad(dechex(random_int(0, 255)), 2, '0', STR_PAD_LEFT));
    }
    return implode(':', $octets);
}

function randFrom(array $arr) {
    return $arr[array_rand($arr)];
}

function maybeCt(): ?string {
    // Return a CT value like "32.81" occasionally (when positive)
    $val = random_int(2200, 3600) / 100; // 22.00 - 36.00
    return number_format($val, 2, '.', '');
}

function randFirstName(): string {
    static $first = ['Olivia','Amelia','Isla','Ava','Mia','Sophia','Grace','Emily','Lily',
        'Freya','Jack','Oliver','Noah','George','Leo','Arthur','Harry','Oscar','Charlie','Theo'];
    return randFrom($first);
}

function randLastName(): string {
    static $last = ['Smith','Jones','Taylor','Brown','Williams','Wilson','Johnson','Davies','Patel',
        'Wright','Walker','Thompson','White','Edwards','Hughes','Green','Hall','Lewis','Clark','Young'];
    return randFrom($last);
}

function randDoB(int $minAge = 0, int $maxAge = 100): string {
    $age = random_int($minAge, $maxAge);
    // Birthdate between Jan 1 and Dec 31 of (today - age)
    $year = (int)date('Y') - $age;
    $start = strtotime("$year-01-01 00:00:00");
    $end   = strtotime("$year-12-31 23:59:59");
    $t = random_int($start, $end);
    return date('Y-m-d', $t);
}

function randHospitalId(): string {
    // Example format: HXX-##### (e.g., HQA-49320)
    $letters = chr(random_int(65,90)) . chr(random_int(65,90));
    $digits  = str_pad((string)random_int(0, 99999), 5, '0', STR_PAD_LEFT);
    return "H{$letters}-{$digits}";
}

function randNhsNumber(): string {
    // Generate valid 10-digit NHS number with Mod 11 check
    // weights 10..2 for first 9 digits; check = 11 - (sum % 11); 11->0, 10 invalid
    while (true) {
        $digits = [];
        for ($i=0; $i<9; $i++) $digits[] = random_int(0,9);
        $sum = 0;
        for ($i=0; $i<9; $i++) $sum += $digits[$i] * (10 - $i);
        $rem = $sum % 11;
        $check = 11 - $rem;
        if ($check === 11) $check = 0;
        if ($check === 10) continue; // invalid, try again
        $digits[] = $check;
        return implode('', $digits);
    }
}

function randPatientLocation(): string {
    $units = ['ED','ICU','AMU','CCU','OPD','Theatre','Paediatrics','Maternity','Oncology','Ward'];
    $u = randFrom($units);
    if ($u === 'Ward') {
        $wardNum = random_int(1, 20);
        $suffix  = randFrom(['A','B','C']);
        $bed     = random_int(1, 30);
        return "Ward {$wardNum}{$suffix} / Bed {$bed}";
    }
    return $u;
}

function randDateTime(string $start = '2018-01-01 00:00:00', string $end = '2030-01-01 00:00:00', bool $minutePrecision = true): string {
    $tsStart = strtotime($start);
    $tsEnd   = strtotime($end);
    $t = random_int($tsStart, $tsEnd);
    if ($minutePrecision) $t -= ($t % 60);
    return date('Y-m-d H:i', $t);
}


function buildPayload(int $i): array {
    // Choose start/end first
    $start = randDateTime('2025-08-01 00:00:00', '2025-09-24 23:59:59');
    $endTs = strtotime($start) + random_int(10, 90) * 60; // +10–90 minutes
    $end   = date('Y-m-d H:i', $endTs);

    // collected/received before start
    $collectedTs = strtotime($start) - random_int(0, 48*60) * 60; // up to 48h before start
    $collected   = date('Y-m-d H:i', $collectedTs);
    $receivedTs  = random_int($collectedTs, strtotime($start));   // between collected and start
    $received    = date('Y-m-d H:i', $receivedTs);

    // Random positivity
    $isFluAPos = (random_int(1, 100) <= 12);
    $overallEnum   = $isFluAPos ? 'SOME_POSITIVE' : 'NEGATIVE';
    $overallString = $isFluAPos ? 'Positive' : 'Negative';
    $fluAct        = $isFluAPos ? number_format(random_int(2200, 3600)/100, 2, '.', '') : null;

    $sampleId  = str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    $patientId = (string)random_int(1, 999999);

    $assayName = 'SARS-CoV-2, FluA, FluB, RSV';

    return [
        "clientSeq" => $i,
        "version" => "3.9999999",
        "sampleId" => $sampleId,
        "patientId" => $patientId,
        "patientAge" => (string)random_int(0, 99),
        "patientSex" => randFrom(['M','F','']), // sometimes blank
        "patientFirstName" => randFirstName(),
        "patientLastName"  => randLastName(),
        "patientDoB" => randDoB(0, 99),         // YYYY-MM-DD
        "hospitalId" => randHospitalId(),
        "nhsNumber"  => randNhsNumber(),
        "collectedTime" => $collected,          // YYYY-MM-DD HH:MM
        "receivedTime"  => $received,           // YYYY-MM-DD HH:MM
        "patientLocation" => randPatientLocation(),
        "comment1" => "",
        "comment2" => "",
        "operatorId" => randFrom(["service", "user", "tech", "nurse"]),
        "testPurpose" => 1,
        "expectedResult" => "",
        "assayType" => "90",
        "assaySubType" => "01",
        "lotNumber" => str_pad((string)random_int(1, 999), 3, '0', STR_PAD_LEFT),
        "subLotNumber" => str_pad((string)random_int(1, 99), 2, '0', STR_PAD_LEFT),
        "productionYear" => str_pad((string)random_int(24, 26), 2, '0', STR_PAD_LEFT),
        "expiryYear" => (string)random_int(2025, 2027),
        "expiryMonth" => str_pad((string)random_int(1, 12), 2, '0', STR_PAD_LEFT),
        "assayId" => random_int(1, 200),
        "moduleSerialNumber" => randMac(),
        "assayName" => $assayName,
        "assayVersion" => "0." . random_int(1, 9) . "." . random_int(0, 9),
        "startTime" => $start,
        "endTime"   => $end,
        "deviceError" => (random_int(1, 100) <= 3) ? "E" . random_int(100, 199) : "",
        "result" => [
            "signature" => [
                "signature" => base64_encode(random_bytes(48)),
                "instrumentId" => "SIII-" . str_replace(':', '', substr(randMac(), 0, 12)),
            ],
            "safetyCriticalCrc32" => random_int(1, PHP_INT_MAX),
            "results" => [
                [
                    "combinedResult" => [
                        "overallResult" => [
                            "resultValueEnum" => "NEGATIVE",
                            "resultValueString" => "Negative",
                            "target" => ["assayTargetName" => "SARS-CoV-2"]
                        ],
                        "constituentResults" => [
                            [
                                "chamber" => "VIRTUAL_CHAMBER_A",
                                "channel" => 4,
                                "result" => [
                                    "resultValueEnum" => "NEGATIVE",
                                    "resultValueString" => "Negative",
                                    "target" => ["assayTargetName" => "SARS-CoV-2-ORF1AB"]
                                ]
                            ],
                            [
                                "chamber" => "VIRTUAL_CHAMBER_A",
                                "channel" => 5,
                                "result" => [
                                    "resultValueEnum" => "NEGATIVE",
                                    "resultValueString" => "Negative",
                                    "target" => ["assayTargetName" => "SARS-CoV-2-ORF8"]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    "singleResult" => [
                        "chamber" => "VIRTUAL_CHAMBER_A",
                        "channel" => 1,
                        "result" => [
                            "resultValueEnum" => "NEGATIVE",
                            "resultValueString" => "Negative",
                            "target" => ["assayTargetName" => "RSV"]
                        ]
                    ]
                ],
                [
                    "singleResult" => [
                        "chamber" => "VIRTUAL_CHAMBER_A",
                        "channel" => 3,
                        "result" => array_filter([
                            "resultValueEnum" => $isFluAPos ? "POSITIVE" : "NEGATIVE",
                            "resultValueString" => $isFluAPos ? "Positive" : "Negative",
                            "target" => ["assayTargetName" => "Flu A"],
                            "ct" => $isFluAPos ? $fluAct : null
                        ], fn($v) => $v !== null)
                    ]
                ],
                [
                    "singleResult" => [
                        "chamber" => "VIRTUAL_CHAMBER_A",
                        "channel" => 2,
                        "result" => [
                            "resultValueEnum" => "NEGATIVE",
                            "resultValueString" => "Negative",
                            "target" => ["assayTargetName" => "Flu B"]
                        ]
                    ]
                ]
            ],
            "overallResultValueEnum" => $overallEnum,
            "overallResultValueString" => $overallString
        ]
    ];
}


// ----- cURL Multi POST pump -----
function postJsonMultiEnsureAck(string $endpoint, array $payloads, int $concurrency = 10,): array {
    $mh = curl_multi_init();
    $headers = ['Content-Type: application/json'];

    $total = count($payloads);
    $nextIndex = 0;
    $inFlight = 0;
    $handles = []; // chId => ['idx'=>int,'tries'=>int]
    $ok = 0;
    $failedPayloads = [];
    $consecFail = 0;

    $enqueue = function(int $i, int $tries = 1) use ($endpoint, $payloads, $headers, $mh, &$handles) {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payloads[$i], JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 30,
        ]);
        curl_multi_add_handle($mh, $ch);
        $handles[(int)$ch] = ['idx'=>$i, 'tries'=>$tries];
    };

    while ($inFlight < $concurrency && $nextIndex < $total) { $enqueue($nextIndex++); $inFlight++; }

    do {
        curl_multi_exec($mh, $running);

        while ($info = curl_multi_info_read($mh)) {
            $ch   = $info['handle'];
            $meta = $handles[(int)$ch]; unset($handles[(int)$ch]);
            $idx  = $meta['idx']; $tries = $meta['tries'];
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $body = curl_multi_getcontent($ch);
            $err  = curl_error($ch);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
            $inFlight--;

            $ack = false;
            if ($http >= 200 && $http < 300 && $err === '') {
                $json = json_decode($body, true);
                $ack = is_array($json) && (
                    (!empty($json['status']) && (int)$json['status'] === 8)
                );
            }

            if ($ack) {
                $ok++;
                $consecFail = 0;
            } else {
                $consecFail++;
                if ($tries < 4) {
                    // retry with exponential backoff
                    usleep((int)(pow(2, $tries-1) * 250000)); // 0.25s, 0.5s, 1.0s
                    $enqueue($idx, $tries+1);
                    $inFlight++;
                } else {
                    $failedPayloads[] = $payloads[$idx];
                }
            }

            // keep pipeline full
            if ($nextIndex < $total && $inFlight < $concurrency) { $enqueue($nextIndex++); $inFlight++; }
        }

        if ($running) curl_multi_select($mh, 0.2);

        // Adaptive backoff if we see a failure streak (likely throttling)
        if ($consecFail >= 20) {
            usleep(1000000); // 1s pause
            $consecFail = 0;
        }

    } while ($running || $inFlight > 0);

    curl_multi_close($mh);
    return ['ok'=>$ok, 'failedPayloads'=>$failedPayloads];
}


// ----- Driver: generate and stream-post in chunks to keep memory reasonable -----
$BATCH = 2000; // number of payloads to keep in memory at once
$sent = 0;
$failedQueue = []; 

while ($okTotal < $TARGET) {
    $toSend = [];

    // first, fill from failedQueue if any
    while (!empty($failedQueue) && count($toSend) < $BATCH) {
        $toSend[] = array_pop($failedQueue);
    }

    // then, top up with fresh payloads
    $needFresh = max(0, min($BATCH, $TARGET - $okTotal) - count($toSend));
    for ($i = 0; $i < $needFresh; $i++) {
        $p = buildPayload($clientSeq);
        $p['clientSeq'] = $clientSeq; // ensure uniqueness client-side
        $clientSeq++;
        $toSend[] = $p;
    }

    // nothing to do (shouldn't happen, but safety)
    if (empty($toSend)) break;

    $res = postJsonMultiEnsureAck($ENDPOINT, $toSend, $CONCURRENCY);
    $okTotal += $res['ok'];

    // requeue failures
    foreach ($res['failedPayloads'] as $fp) {
        $failedQueue[] = $fp;
    }

    echo "Progress: okTotal={$okTotal}, inRetryQueue=" . count($failedQueue) . PHP_EOL;

    // if retry queue gets large (throttle detected), back off a bit
    if (count($failedQueue) > 200) {
        usleep(1500000); // 1.5s
    }
}

echo "DONE: confirmed inserts = {$okTotal}" . PHP_EOL;