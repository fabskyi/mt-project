<?php
require_once __DIR__ . "/config.php";
header("Content-Type: application/json");

$data = [];

try {

    $sql = "
        SELECT 
            m.model_name,
            i.part_name,
            i.current_stock,
            mi.safety_stock
        FROM model_items mi
        JOIN models m ON mi.model_id = m.id
        JOIN items i ON mi.item_id = i.id
        ORDER BY m.model_name ASC
    ";

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {

        $stock  = (int)$row['current_stock'];
        $safety = (int)$row['safety_stock'];

        if ($stock <= $safety) {
            $status = "low";
        } else {
            $status = "safe";
        }

        $row['status'] = $status;
        $data[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);
} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
