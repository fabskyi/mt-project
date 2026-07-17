<?php
// api/get_routing.php
// Daftar semua part number di 1 lokasi beserta model-model yang sudah di-routing ke part itu
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

$lokasi_id = intval($_GET['lokasi_id'] ?? 0);

if ($lokasi_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid lokasi_id"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT
        i.id   AS item_id,
        i.part_name,
        i.part_number,
        mi.id         AS model_item_id,
        mi.model_id,
        m.model_name,
        mi.usage_qty
    FROM items i
    LEFT JOIN model_items mi ON mi.item_id = i.id
    LEFT JOIN models m       ON m.id = mi.model_id
    WHERE i.location_id = ?
    ORDER BY i.part_name ASC, m.model_name ASC
");
$stmt->bind_param("i", $lokasi_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $item_id = intval($row['item_id']);

    if (!isset($items[$item_id])) {
        $items[$item_id] = [
            "item_id"     => $item_id,
            "part_name"   => $row['part_name'],
            "part_number" => $row['part_number'],
            "models"      => []
        ];
    }

    if ($row['model_item_id'] !== null) {
        $items[$item_id]["models"][] = [
            "model_item_id" => intval($row['model_item_id']),
            "model_id"      => intval($row['model_id']),
            "model_name"    => $row['model_name'],
            "usage_qty"     => intval($row['usage_qty'])
        ];
    }
}
$stmt->close();

echo json_encode(["success" => true, "data" => array_values($items)]);
