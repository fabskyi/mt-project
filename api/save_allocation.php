<?php
// api/save_allocation.php
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['allocations']) || !is_array($input['allocations'])) {
    echo json_encode(["success" => false, "message" => "Invalid payload"]);
    exit;
}

$allocations   = $input['allocations'];
$affectedItems = [];

$conn->begin_transaction();

try {
    $stmtUpdate = $conn->prepare("
        UPDATE model_items
        SET allocation_percentage = ?,
            monthly_plan          = ?,
            working_days_per_week = ?
        WHERE id = ?
    ");

    $stmtGetItem = $conn->prepare("SELECT item_id FROM model_items WHERE id = ?");

    foreach ($allocations as $alloc) {
        $model_item_id = intval($alloc['model_item_id']);
        $pct           = floatval($alloc['allocation_percentage'] ?? 0);
        $monthly_plan  = intval($alloc['monthly_plan'] ?? 0);
        $days_per_week = intval($alloc['working_days_per_week'] ?? 5);

        if ($pct < 0)   $pct = 0;
        if ($pct > 100) $pct = 100;
        if ($monthly_plan < 0) $monthly_plan = 0;
        if ($days_per_week < 0) $days_per_week = 0;
        if ($days_per_week > 7) $days_per_week = 7;

        $stmtGetItem->bind_param("i", $model_item_id);
        $stmtGetItem->execute();
        $resItem = $stmtGetItem->get_result()->fetch_assoc();
        if (!$resItem) continue;

        $item_id = intval($resItem['item_id']);

        $stmtUpdate->bind_param("diii", $pct, $monthly_plan, $days_per_week, $model_item_id);
        $stmtUpdate->execute();

        $affectedItems[$item_id] = true;
    }

    // Recalculate allocated_stock untuk setiap item yang terpengaruh
    foreach (array_keys($affectedItems) as $item_id) {
        $conn->query("CALL recalculate_allocated_stock($item_id)");
        while ($conn->more_results() && $conn->next_result()) {
            $extra = $conn->store_result();
            if ($extra) $extra->free();
        }
    }
    
    // Recalculate safety_stock untuk setiap item yang terpengaruh
    // (trigger akan handle ini otomatis, tapi kita call manual untuk memastikan)
    foreach (array_keys($affectedItems) as $item_id) {
        $conn->query("CALL recalculate_safety_stock($item_id)");
        while ($conn->more_results() && $conn->next_result()) {
            $extra = $conn->store_result();
            if ($extra) $extra->free();
        }
    }

    $conn->commit();
    echo json_encode(["success" => true, "recalculated" => count($affectedItems)]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}