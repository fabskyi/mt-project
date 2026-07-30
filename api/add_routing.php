<?php
// api/add_routing.php
// Hubungkan (routing) 1 part number ke 1 model engine
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

$data      = json_decode(file_get_contents("php://input"), true);
$item_id   = intval($data['item_id'] ?? 0);
$model_id  = intval($data['model_id'] ?? 0);
$usage_qty = intval($data['usage_qty'] ?? 1);

if ($item_id <= 0 || $model_id <= 0) {
    echo json_encode(["success" => false, "message" => "Pilih part number dan model terlebih dahulu"]);
    exit;
}
if ($usage_qty <= 0) $usage_qty = 1;

$check = $conn->prepare("SELECT id FROM model_items WHERE model_id = ? AND item_id = ?");
$check->bind_param("ii", $model_id, $item_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Part number ini sudah terhubung ke model tersebut"]);
    exit;
}
$check->close();

try {
    $stmt = $conn->prepare("
        INSERT INTO model_items (model_id, item_id, usage_qty)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iii", $model_id, $item_id, $usage_qty);
    $stmt->execute();
    echo json_encode(["success" => true, "model_item_id" => $conn->insert_id]);
} catch (\mysqli_sql_exception $e) {
    $msg = str_contains($e->getMessage(), 'foreign key constraint')
        ? "Model atau part ini sudah tidak ada (mungkin baru saja dihapus). Muat ulang halaman."
        : "Gagal menyimpan routing: " . $e->getMessage();
    echo json_encode(["success" => false, "message" => $msg]);
}
