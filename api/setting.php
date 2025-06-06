<?php // Endpoint to retreive DMS setting information

/*
Get settingItem
Return setting
*/

$setting = $_GET['s'] ?? $data ?? null;

if ($setting === null) {
    echo json_encode(["Error" => "No data available."]);
    addLogEntry('API', "ERROR: /settings endpoint hit with insufficient params.");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $response = patchSetting($data);

    echo json_encode([
        "res" => $response == null ? 0 : 1,
        "message" => $response == 0 ? 'Nothing was changed' : "Successfully updated setting.",
    ]);
} else {
    $response = getSetting($setting);

    echo json_encode([
        "res" => $response ? 1 : 0,
        "setting" => $response ?? "No setting with this name.",
    ]);
}