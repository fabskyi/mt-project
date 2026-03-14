<?php
require_once "config.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$scan_input = strtoupper(trim(preg_replace('/[\r\n]+/', '', $data['part'])));

$stmt = $conn->prepare("
        SELECT 
        i.id,
        i.part_name,
        i.part_number,
        i.current_stock,
        GROUP_CONCAT(m.model_name SEPARATOR ', ') as models
    FROM items i
    LEFT JOIN model_items mi ON i.id = mi.item_id
    LEFT JOIN models m ON mi.model_id = m.id
    WHERE i.part_name = ?
    OR i.part_number = ?
    GROUP BY i.id
    LIMIT 1
");
$stmt->bind_param("ss", $scan_input, $scan_input);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "error" => "Part tidak ditemukan"]);
    exit;
}

$row = $result->fetch_assoc();

echo json_encode([
    "success" => true,
    "item_id" => $row['id'],
    "part" => $row['part_name'],
    "models" => $row['models'],
    "stock" => $row['current_stock']
]);
