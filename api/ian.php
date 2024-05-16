<?php // Endpoint for Ian to test his mould project...

// Firstly, if we have no data, quit.
if(!$data) {
    // Throw error and stop processing things.
    echo(json_encode(["Error" => "No data available."]));
    exit;
}

$response = "Ian, stop sending me logger stuff!";

// Provide the response
echo json_encode(["status" => $response]);

