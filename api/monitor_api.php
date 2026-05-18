<?php
require_once __DIR__ . "/config.php";
header("Content-Type: application/json");

try {

    $sql = "
        SELECT
            mi.model_id,
            m.model_name,
            i.id               AS item_id,
            i.part_name,
            i.part_number,
            i.location_id      AS location,
            mi.safety_stock,
            mi.allocated_stock,
            mi.allocation_percentage,
            mi.usage_qty
        FROM model_items mi
        JOIN models m ON mi.model_id = m.id
        JOIN items  i ON mi.item_id  = i.id
        WHERE mi.safety_stock > 0
        ORDER BY m.model_name ASC, i.location_id ASC, i.part_name ASC
    ";

    $result = $conn->query($sql);
    $rows   = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $data = [];

    foreach ($rows as $row) {
        $safety         = (int)$row['safety_stock'];
        $allocatedStock = (float)$row['allocated_stock'];

        // Status: allocated_stock vs safety_stock model ini
        if ($allocatedStock >= $safety) {
            $status = "safe";
        } elseif ($allocatedStock >= ($safety * 0.5)) {
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
            'safety_stock'  => $safety,
            'current_stock' => $allocatedStock, // key tetap current_stock agar frontend tidak perlu diubah
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