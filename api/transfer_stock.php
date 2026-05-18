<?php
// api/transfer_stock.php
// Memindahkan allocated_stock dari 1 model ke model lain untuk part yang sama
// Tidak mengubah total stock, hanya redistribusi allocated_stock

header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

$input = json_decode(file_get_contents("php://input"), true);

$from_id = intval($input['from_model_item_id'] ?? 0);
$to_id   = intval($input['to_model_item_id']   ?? 0);
$qty     = intval($input['qty']                ?? 0);

// Validasi input dasar
if (!$from_id || !$to_id || $qty <= 0) {
    echo json_encode(["success" => false, "message" => "Data tidak lengkap"]);
    exit;
}

if ($from_id === $to_id) {
    echo json_encode(["success" => false, "message" => "Model asal dan tujuan tidak boleh sama"]);
    exit;
}

$conn->begin_transaction();

try {
    // 1. Ambil data dari model
    $stmtFrom = $conn->prepare("SELECT id, item_id, allocated_stock FROM model_items WHERE id = ? FOR UPDATE");
    $stmtFrom->bind_param("i", $from_id);
    $stmtFrom->execute();
    $fromRow = $stmtFrom->get_result()->fetch_assoc();
    $stmtFrom->close();

    if (!$fromRow) throw new Exception("Model asal tidak ditemukan");

    // 2. Ambil data ke model
    $stmtTo = $conn->prepare("SELECT id, item_id, allocated_stock FROM model_items WHERE id = ? FOR UPDATE");
    $stmtTo->bind_param("i", $to_id);
    $stmtTo->execute();
    $toRow = $stmtTo->get_result()->fetch_assoc();
    $stmtTo->close();

    if (!$toRow) throw new Exception("Model tujuan tidak ditemukan");

    // 3. Pastikan keduanya untuk part yang sama
    if ($fromRow['item_id'] !== $toRow['item_id']) {
        throw new Exception("Model asal dan tujuan harus untuk part yang sama");
    }

    // 4. Validasi stock cukup
    $fromStock = (int) $fromRow['allocated_stock'];
    if ($qty > $fromStock) {
        throw new Exception("Stock tidak cukup. Tersedia: {$fromStock}, diminta: {$qty}");
    }

    // 5. Kurangi dari model asal
    $stmtDecr = $conn->prepare("UPDATE model_items SET allocated_stock = allocated_stock - ? WHERE id = ?");
    $stmtDecr->bind_param("ii", $qty, $from_id);
    if (!$stmtDecr->execute()) throw new Exception("Gagal mengurangi stock: " . $stmtDecr->error);
    $stmtDecr->close();

    // 6. Tambah ke model tujuan
    $stmtIncr = $conn->prepare("UPDATE model_items SET allocated_stock = allocated_stock + ? WHERE id = ?");
    $stmtIncr->bind_param("ii", $qty, $to_id);
    if (!$stmtIncr->execute()) throw new Exception("Gagal menambah stock: " . $stmtIncr->error);
    $stmtIncr->close();

    $conn->commit();

    // Ambil nilai terbaru untuk response
    $stmtCheck = $conn->prepare("
        SELECT mi.id, m.model_name, mi.allocated_stock
        FROM model_items mi
        JOIN models m ON m.id = mi.model_id
        WHERE mi.id IN (?, ?)
    ");
    $stmtCheck->bind_param  ("ii", $from_id, $to_id);
    $stmtCheck->execute();
    $afterRows = $stmtCheck->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtCheck->close();

    echo json_encode([
        "success"  => true,
        "qty"      => $qty,
        "after"    => $afterRows,
        "message"  => "Transfer berhasil: {$qty} unit dipindahkan"
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}