<?php
require_once __DIR__ . "/config.php";
header("Content-Type: application/json");

$part = trim($_POST['part_number'] ?? '');
$type = $_POST['type'] ?? '';
$qty  = intval($_POST['qty'] ?? 1);
$nik  = trim($_POST['nik'] ?? '');

if ($part == '' || ($type != 'in' && $type != 'out') || $qty <= 0) {
    echo json_encode(["status" => "INVALID_REQUEST"]);
    exit;
}

$conn->begin_transaction();

try {

    $stmt = $conn->prepare("
        SELECT id, current_stock 
        FROM items 
        WHERE part_number = ?
        FOR UPDATE
    ");
    $stmt->bind_param("s", $part);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        throw new Exception("NOT_FOUND");
    }

    $row = $result->fetch_assoc();
    $item_id = $row['id'];
    $currentStock = intval($row['current_stock']);

    if ($type == "in") {
        $newStock = $currentStock + $qty;
    } else {
        if ($currentStock < $qty) {
            throw new Exception("Stock tidak mencukupi");
        }
        $newStock = $currentStock - $qty;
    }

    $update = $conn->prepare("
        UPDATE items 
        SET current_stock = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $update->bind_param("ii", $newStock, $item_id);
    $update->execute();

    $trx = $conn->prepare("
        INSERT INTO transactions 
        (item_id, type, qty, nik)
        VALUES (?, ?, ?, ?)
    ");
    $trx->bind_param("isis", $item_id, $type, $qty, $nik);
    $trx->execute();

    $conn->commit();

    echo json_encode([
        "status" => "OK",
        "stock"  => $newStock
    ]);
} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => "ERROR",
        "msg" => $e->getMessage()
    ]);
}
