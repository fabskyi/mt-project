<?php
require_once "config.php";
header("Content-Type: application/json");

$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$newStock = isset($_POST['new_stock']) ? intval($_POST['new_stock']) : 0;

if ($item_id <= 0) {
    echo json_encode(["status" => "INVALID_REQUEST"]);
    exit;
}

$conn->begin_transaction();

try {

    $get = $conn->prepare("
        SELECT current_stock 
        FROM items 
        WHERE id = ?
        FOR UPDATE
    ");
    $get->bind_param("i", $item_id);
    $get->execute();
    $result = $get->get_result();

    if ($result->num_rows == 0) {
        throw new Exception("NOT_FOUND");
    }

    $row = $result->fetch_assoc();
    $oldStock = intval($row['current_stock']);

    if ($newStock < 0) {
        throw new Exception("NEGATIVE_STOCK_NOT_ALLOWED");
    }

    $type = $newStock > $oldStock ? "in" : "out";
    $qty  = abs($newStock - $oldStock);

    $update = $conn->prepare("
        UPDATE items 
        SET current_stock = ?, last_update = NOW()
        WHERE id = ?
    ");
    $update->bind_param("ii", $newStock, $item_id);
    $update->execute();

    if ($qty > 0) {
        $insert = $conn->prepare("
            INSERT INTO transactions (item_id, type, qty)
            VALUES (?, ?, ?)
        ");
        $insert->bind_param("isi", $item_id, $type, $qty);
        $insert->execute();
    }

    $conn->commit();

    echo json_encode(["status" => "OK"]);
} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => "ERROR",
        "msg" => $e->getMessage()
    ]);
}
