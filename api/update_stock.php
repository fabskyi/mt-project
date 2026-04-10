<?php
require_once __DIR__ . "/config.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$item_id   = intval($data['item_id'] ?? 0);
$new_stock = intval($data['stock'] ?? 0);

if ($item_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid ID"]);
    exit;
}

$conn->begin_transaction();

try {

    // 1️⃣ Lock row
    $stmt = $conn->prepare("
        SELECT current_stock, location_id 
        FROM items 
        WHERE id=? 
        FOR UPDATE
    ");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        throw new Exception("Item tidak ditemukan");
    }

    $before_stock = intval($row['current_stock']);
    $lokasi_id    = intval($row['location_id']);

    if ($new_stock < 0) {
        throw new Exception("Stock tidak boleh minus");
    }

    if ($before_stock == $new_stock) {
        $conn->commit();
        echo json_encode(["success" => true]);
        exit;
    }

    // 2️⃣ Hitung selisih
    if ($new_stock > $before_stock) {
        $type = "in";
        $qty  = $new_stock - $before_stock;
    } else {
        $type = "out";
        $qty  = $before_stock - $new_stock;
    }

    $update = $conn->prepare("
        UPDATE items 
        SET current_stock=?, updated_at=NOW()
        WHERE id=?
    ");
    $update->bind_param("ii", $new_stock, $item_id);
    $update->execute();

    $note = ($lokasi_id == 1) ? "ADMIN MS 1" : "ADMIN MS 2";

    $log = $conn->prepare("
        INSERT INTO stock_logs
        (item_id, lokasi_id, type, qty, before_stock, after_stock, note)
        VALUES (?,?,?,?,?,?,?)
    ");

    $log->bind_param(
        "iisiiis",
        $item_id,
        $lokasi_id,
        $type,
        $qty,
        $before_stock,
        $new_stock,
        $note
    );
    $log->execute();

    $safetyQ = $conn->prepare("
        SELECT safety_stock 
        FROM model_items 
        WHERE item_id=? 
        LIMIT 1
    ");
    $safetyQ->bind_param("i", $item_id);
    $safetyQ->execute();
    $safetyResult = $safetyQ->get_result();
    $safetyRow = $safetyResult->fetch_assoc();
    $safety_stock = intval($safetyRow['safety_stock'] ?? 0);

    $conn->commit();

    echo json_encode([
        "success" => true,
        "safety_stock" => $safety_stock
    ]);
} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
