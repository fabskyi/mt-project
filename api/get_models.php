<?php
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

$response = [
    "success" => false,
    "data" => []
];

$query = "
    SELECT id, model_name 
    FROM models
    ORDER BY model_name ASC
";

$result = mysqli_query($conn, $query);

if ($result) {

    $models = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $models[] = $row;
    }

    $response["success"] = true;
    $response["data"] = $models;
}

echo json_encode($response);
