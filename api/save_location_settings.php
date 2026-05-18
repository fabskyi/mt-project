<?php
// api/save_location_settings.php
// Simpan setting hari kerja per bulan dan recalculate semua safety stock
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['lokasi_id']) || !isset($input['working_days_per_month'])) {
    echo json_encode(["success" => false, "message" => "Invalid payload"]);
    exit;
}

$lokasi_id = intval($input['lokasi_id']);
$days_per_month = intval($input['working_days_per_month']);

if ($days_per_month < 1 || $days_per_month > 31) {
    echo json_encode(["success" => false, "message" => "Working days harus antara 1-31"]);
    exit;
}

$conn->begin_transaction();

try {
    // Update atau insert location_settings
    $sql = "INSERT INTO location_settings (location_id, working_days_per_month)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE working_days_per_month = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $lokasi_id, $days_per_month, $days_per_month);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan setting: " . $stmt->error);
    }
    
    // Recalculate semua safety stock untuk lokasi ini
    // Ambil semua item_id untuk lokasi ini
    $sqlItems = "SELECT DISTINCT id FROM items WHERE location_id = ?";
    $stmtItems = $conn->prepare($sqlItems);
    $stmtItems->bind_param("i", $lokasi_id);
    $stmtItems->execute();
    $itemsResult = $stmtItems->get_result();
    
    $recalculated = 0;
    while ($item = $itemsResult->fetch_assoc()) {
        $item_id = $item['id'];
        
        // Call stored procedure
        if (!$conn->query("CALL recalculate_safety_stock($item_id)")) {
            throw new Exception("CALL failed for item_id=$item_id: " . $conn->error);
        }
        
        // Clear extra result sets
        while ($conn->more_results() && $conn->next_result()) {
            $extra = $conn->store_result();
            if ($extra) $extra->free();
        }
        
        $recalculated++;
    }
    
    $conn->commit();
    
    echo json_encode([
        "success" => true,
        "message" => "Setting tersimpan dan safety stock diperbarui",
        "recalculated" => $recalculated
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}