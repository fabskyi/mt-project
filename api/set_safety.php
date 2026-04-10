<?php
require_once __DIR__ . "/config.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$model_id = intval($data['model_id'] ?? 0);
$safety = intval($data['safety_stock'] ?? -1);

if ($model_id <= 0 || $safety < 0) {
    echo json_encode(["status" => "INVALID"]);
    exit;
}

$stmt = $conn->prepare("
    UPDATE model_items
    SET safety_stock = ?
    WHERE model_id = ?
");

$stmt->bind_param("ii", $safety, $model_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "OK"]);
} else {
    echo json_encode([
        "status" => "ERROR",
        "msg" => $conn->error
    ]);
}
