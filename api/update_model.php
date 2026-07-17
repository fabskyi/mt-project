<?php
// api/update_model.php
// Ganti/update nama model engine
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

$data       = json_decode(file_get_contents("php://input"), true);
$id         = intval($data['id'] ?? 0);
$model_name = trim($data['model_name'] ?? '');

if ($id <= 0 || $model_name === '') {
    echo json_encode(["success" => false, "message" => "Data tidak lengkap"]);
    exit;
}

if (mb_strlen($model_name) > 50) {
    echo json_encode(["success" => false, "message" => "Nama model maksimal 50 karakter"]);
    exit;
}

$check = $conn->prepare("SELECT id FROM models WHERE model_name = ? AND id != ?");
$check->bind_param("si", $model_name, $id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Nama model '$model_name' sudah dipakai model lain"]);
    exit;
}
$check->close();

$stmt = $conn->prepare("UPDATE models SET model_name = ? WHERE id = ?");
$stmt->bind_param("si", $model_name, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "updated" => $stmt->affected_rows]);
} else {
    echo json_encode(["success" => false, "message" => $stmt->error]);
}
