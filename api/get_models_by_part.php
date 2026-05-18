<?php
// api/get_models_by_part.php
// Mengembalikan semua model yang punya allocated stock untuk 1 part number
// Dipakai di step pilih model saat mode OUT

session_start();
require_once __DIR__ . "/config.php";
header("Content-Type: application/json");

if (!isset($_SESSION['nik'])) {
    echo json_encode(["success" => false, "error" => "Session expired"]);
    exit;
}

$part_number = trim($_GET['part'] ?? '');
if (!$part_number) {
    echo json_encode(["success" => false, "error" => "Part number wajib diisi"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT
        mi.id           AS model_item_id,
        m.model_name,
        mi.allocated_stock,
        mi.safety_stock,
        mi.monthly_plan
    FROM model_items mi
    JOIN models m ON m.id  = mi.model_id
    JOIN items  i ON i.id  = mi.item_id
    WHERE i.part_number = ?
    ORDER BY mi.allocated_stock DESC, m.model_name ASC
");
$stmt->bind_param("s", $part_number);
$stmt->execute();
$result = $stmt->get_result();

$models = [];
while ($row = $result->fetch_assoc()) {
    $models[] = $row;
}
$stmt->close();

echo json_encode(["success" => true, "models" => $models]);