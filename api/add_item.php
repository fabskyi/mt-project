<?php
require_once "config.php";
header("Content-Type: application/json");

$conn->begin_transaction();

try {

    $data = json_decode(file_get_contents("php://input"), true);

    $part_name   = $data['part_name'] ?? '';
    $part_number = $data['part_number'] ?? '';
    $stock       = intval($data['current_stock'] ?? 0);
    $safety      = intval($data['safety_stock'] ?? 0);
    $location_id = intval($data['location_id'] ?? 0);
    $model_ids   = $data['model_ids'] ?? [];

    if ($part_name == '' || $part_number == '' || $location_id <= 0 || empty($model_ids)) {
        throw new Exception("Data tidak lengkap");
    }

    /* INSERT ITEMS (1x saja) */
    $stmt = $conn->prepare("
        INSERT INTO items 
        (part_name, part_number, current_stock, location_id, created_at, updated_at)
        VALUES (?,?,?,?,NOW(),NOW())
    ");

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param("ssii", $part_name, $part_number, $stock, $location_id);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $item_id = $conn->insert_id;

    /* INSERT MODEL_ITEMS (loop banyak model) */
    $stmt2 = $conn->prepare("
        INSERT INTO model_items 
        (model_id, item_id, usage_qty, safety_stock)
        VALUES (?,?,1,?)
    ");

    if (!$stmt2) {
        throw new Exception($conn->error);
    }

    foreach ($model_ids as $model_id) {

        $model_id = intval($model_id);

        $stmt2->bind_param("iii", $model_id, $item_id, $safety);

        if (!$stmt2->execute()) {
            throw new Exception($stmt2->error);
        }
    }

    $conn->commit();

    echo json_encode(["success" => true]);
} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "success" => false,
        "error"   => $e->getMessage()
    ]);
}
