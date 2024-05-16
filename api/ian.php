<?php // Endpoint for Ian to test his mould project...

// Firstly, if we have no data, quit.
if(!$data) {
    // Throw error and stop processing things.
    echo(json_encode(["Error" => "No data available."]));
    exit;
}

$response = "Unsuccessful";

$query = "INSERT INTO ian (time, temp, humidity, surf_temp, pressure, magx, magy, magz, battery) VALUES ";
$i = 0;
$params = [];
foreach($data['data'] as $key => $value) {
    if($i != 0) {
        $query .= ", ";
    }

    $query .= "(";

    // Add the values
    $params[] = $time = $value['time'] ?? null;
    $params[] = $temp = $value['air temperature'] ?? null;
    $params[] = $humidity = $value['humidity'] ?? null;
    $params[] = $surfTemp = $value['surface temperature'] ?? null;
    $params[] = $pressure = $value['air pressure'] ?? null;
    $params[] = $magX = $value['magnetometer x'] ?? null;
    $params[] = $magY = $value['magnetometer y'] ?? null;
    $params[] = $magZ = $value['magnetometer z'] ?? null;
    $params[] = $battery = $value['battery voltage'] ?? null;

    $query .= "?, ?, ?, ?, ?, ?, ?, ?, ?";

    $query .= ")";
    $i++;
}

try {
    // Begin transaction to ensure safety
    $cainDB->beginTransaction();


    // Add items to db
    $cainDB->query($query, $params);

    // Commit the transaction
    $cainDB->commit();
} catch(PDOException $e) {
    // Rollback the transaction on error
    $cainDB->rollBack();
    $errors = $e;
    echo("We had an error... send this to Sid!");
    print_r($e);
}


// Provide the response
echo json_encode(["status" => $response,]);
