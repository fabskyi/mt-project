<?php
require_once "config.php";

$data = json_decode(file_get_contents("php://input"), true);

$lokasi = $data['lokasi_id'];
$plan   = $data['plan_qty'];
$date   = $data['plan_date'];

$stmt = $conn->prepare("
    INSERT INTO production_plan (lokasi_id, plan_qty, plan_date)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE
    plan_qty = VALUES(plan_qty)
");

$stmt->bind_param("iis", $lokasi, $plan, $date);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}
