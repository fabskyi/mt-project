<?php
// api/delete_model.php
// Hapus model engine — ditolak kalau masih ada routing part number yang terhubung
// (model_items punya FK ON DELETE CASCADE, jadi kita cegah manual di sini
//  supaya data allocation/monthly plan part yang masih dipakai tidak ikut hilang)
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

$data = json_decode(file_get_contents("php://input"), true);
$id   = intval($data['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "ID model tidak valid"]);
    exit;
}

$check = $conn->prepare("SELECT COUNT(*) AS c FROM model_items WHERE model_id = ?");
$check->bind_param("i", $id);
$check->execute();
$count = intval($check->get_result()->fetch_assoc()['c']);
$check->close();

if ($count > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Model masih terhubung ke $count part number. Hapus routing-nya dulu sebelum menghapus model."
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM models WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(["success" => true, "deleted" => $stmt->affected_rows]);
} catch (\mysqli_sql_exception $e) {
    echo json_encode(["success" => false, "message" => "Gagal menghapus model: " . $e->getMessage()]);
}
