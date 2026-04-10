<?php
require_once __DIR__ . "/config.php";  // Pakai $conn (mysqli)
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST required']);
    exit;
}

$item_id = intval($_POST['item_id'] ?? 0);
if ($item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item_id']);
    exit;
}

// Toggle production_status (0→1 atau 1→0)
$sql = "UPDATE model_items SET production_status = NOT production_status WHERE id = ?";
$stmt = $conn->prepare($sql);  // ✅ Ganti $pdo → $conn
$stmt->bind_param("i", $item_id);

if ($stmt->execute()) {
    // Ambil status baru
    $sql = "SELECT production_status FROM model_items WHERE id = ?";
    $stmt2 = $conn->prepare($sql);
    $stmt2->bind_param("i", $item_id);
    $stmt2->execute();
    $status = $stmt2->get_result()->fetch_assoc()['production_status'] ?? 0;
    
    echo json_encode([
        'success' => true, 
        'status' => (int)$status,
        'message' => $status ? 'RUNNING' : 'STOPPED'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}

$stmt->close();
?>