<?php
require_once "config.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$model_id = intval($data['model_id'] ?? 0);
$item_id  = intval($data['item_id'] ?? 0);

if ($model_id <= 0 || $item_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid ID"]);
    exit;
}

try {

    $stmt = $conn->prepare("
        DELETE FROM model_items 
        WHERE model_id = ? AND item_id = ?
    ");

    $stmt->bind_param("ii", $model_id, $item_id);
    $stmt->execute();

    echo json_encode(["success" => true]);
} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
