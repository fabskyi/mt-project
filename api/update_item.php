<?php
require_once "config.php";
header("Content-Type: application/json");

$id        = isset($_POST['id']) ? intval($_POST['id']) : 0;
$newStock  = isset($_POST['current_stock']) ? intval($_POST['current_stock']) : 0;
$lokasi_id = isset($_POST['lokasi_id']) ? intval($_POST['lokasi_id']) : 0;

if ($id <= 0 || $lokasi_id <= 0) {
    echo json_encode(["status" => "INVALID_REQUEST"]);
    exit;
}

/* Ambil stock lama */
$get = $conn->prepare("
    SELECT model, part_number, current_stock 
    FROM items 
    WHERE id = ? AND lokasi_id = ?
");
$get->bind_param("ii", $id, $lokasi_id);
$get->execute();
$result = $get->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["status" => "NOT_FOUND"]);
    exit;
}

$row = $result->fetch_assoc();
$oldStock = intval($row['current_stock']);

$type = $newStock > $oldStock ? "in" : "out";
$qty  = abs($newStock - $oldStock);

/* Update stock */
$update = $conn->prepare("
    UPDATE items 
    SET current_stock = ?, last_update = NOW()
    WHERE id = ? AND lokasi_id = ?
");
$update->bind_param("iii", $newStock, $id, $lokasi_id);
$update->execute();

/* Insert transaksi kalau ada perubahan */
if ($qty > 0) {
    $insert = $conn->prepare("
        INSERT INTO transactions 
        (model, part_number, type, qty, lokasi_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    $insert->bind_param(
        "sssii",
        $row['model'],
        $row['part_number'],
        $type,
        $qty,
        $lokasi_id
    );
    $insert->execute();
}

echo json_encode(["status" => "OK"]);
