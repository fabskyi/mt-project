<?php
require_once __DIR__ . "/config.php";
header('Content-Type: application/json');

/* ambil lokasi dari parameter */
$lokasi_id = intval($_GET['lokasi_id'] ?? 0);

if ($lokasi_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid lokasi_id"
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        mi.id AS model_item_id,
        mi.model_id,
        m.model_name,
        i.id AS item_id,
        i.part_name,
        i.part_number,
        i.current_stock,
        mi.safety_stock,
        i.updated_at
    FROM model_items mi
    JOIN models m ON mi.model_id = m.id
    JOIN items i ON mi.item_id = i.id
    WHERE i.location_id = ?
    ORDER BY m.model_name ASC
");

$stmt->bind_param("i", $lokasi_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {

    $stock  = intval($row['current_stock']);
    $safety = intval($row['safety_stock']);

    if ($stock <= $safety) {
        $status = "low";
    } elseif ($stock <= ($safety * 1.5)) {
        $status = "warning";
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

exit;
