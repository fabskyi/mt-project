<?php
// api/save_monthly_plan.php
// Menyimpan monthly_plan per model_item
// Tidak mengubah allocation_percentage — user harus klik "Auto dari Monthly Plan" secara terpisah

header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['plans']) || !is_array($input['plans'])) {
    echo json_encode(["success" => false, "message" => "Invalid payload"]);
    exit;
}

$plans = $input['plans'];
if (!count($plans)) {
    echo json_encode(["success" => true, "updated" => 0]);
    exit;
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("UPDATE model_items SET monthly_plan = ? WHERE id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

    $updated = 0;
    foreach ($plans as $p) {
        $id  = intval($p['model_item_id']);
        $val = max(0, intval($p['monthly_plan']));

        $stmt->bind_param("ii", $val, $id);
        if (!$stmt->execute()) throw new Exception("Update failed id=$id: " . $stmt->error);
        $updated++;
    }

    $stmt->close();
    $conn->commit();

    echo json_encode([
        "success" => true,
        "updated" => $updated,
        "message" => "$updated monthly plan berhasil disimpan"
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}