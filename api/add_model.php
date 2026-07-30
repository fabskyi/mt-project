<?php
// api/add_model.php
// Tambah model engine baru
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

$data       = json_decode(file_get_contents("php://input"), true);
$model_name = trim($data['model_name'] ?? '');

if ($model_name === '') {
    echo json_encode(["success" => false, "message" => "Nama model wajib diisi"]);
    exit;
}

if (mb_strlen($model_name) > 50) {
    echo json_encode(["success" => false, "message" => "Nama model maksimal 50 karakter"]);
    exit;
}

$check = $conn->prepare("SELECT id FROM models WHERE model_name = ?");
$check->bind_param("s", $model_name);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Nama model '$model_name' sudah ada"]);
    exit;
}
$check->close();

try {
    $stmt = $conn->prepare("INSERT INTO models (model_name) VALUES (?)");
    $stmt->bind_param("s", $model_name);
    $stmt->execute();
    echo json_encode(["success" => true, "id" => $conn->insert_id, "model_name" => $model_name]);
} catch (\mysqli_sql_exception $e) {
    echo json_encode(["success" => false, "message" => "Gagal menyimpan model: " . $e->getMessage()]);
}
