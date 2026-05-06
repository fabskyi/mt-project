<?php
require_once __DIR__ . "/config.php";
header("Content-Type: application/json");

try {

    $sql = "
        SELECT
            mi.model_id,
            m.model_name,
            i.id            AS item_id,
            i.part_name,
            i.part_number,
            i.current_stock,
            i.location_id   AS location,
            mi.safety_stock,
            mi.usage_qty
        FROM model_items mi
        JOIN models m ON mi.model_id = m.id
        JOIN items  i ON mi.item_id  = i.id
        ORDER BY m.model_name ASC, i.location_id ASC, i.part_name ASC
    ";

    $result = $conn->query($sql);
    $rows   = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $data = [];

    foreach ($rows as $row) {
        $safety       = (int)$row['safety_stock'];
        $currentStock = (int)$row['current_stock'];

        // Skip part yang safety_stock-nya 0 untuk model ini
        if ($safety <= 0) continue;

        // Status: current_stock part vs safety_stock model ini
        if ($currentStock >= $safety) {
            $status = "safe";
        } elseif ($currentStock >= ($safety * 0.5)) {
            $status = "warning";
        } else {
            $status = "low";
        }

        $data[] = [
            'model_id'      => (int)$row['model_id'],
            'model_name'    => $row['model_name'],
            'item_id'       => (int)$row['item_id'],
            'part_name'     => $row['part_name'],
            'part_number'   => $row['part_number'],
            'location'      => (int)$row['location'],
            'safety_stock'  => $safety,        // → BAR di chart
            'current_stock' => $currentStock,  // → GARIS di chart
            'status'        => $status,
        ];
    }

    echo json_encode([
        "success" => true,
        "data"    => $data
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error"   => $e->getMessage()
    ]);
}
