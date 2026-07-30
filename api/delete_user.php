<?php
// api/delete_user.php
// Hapus akun user (superadmin only)
session_start();
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'all') {
    echo json_encode(["success" => false, "message" => "Akses ditolak"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id   = intval($data['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "ID user tidak valid"]);
    exit;
}

if ($id === intval($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Tidak bisa menghapus akun yang sedang login"]);
    exit;
}

$cur = $conn->prepare("SELECT role FROM users WHERE id = ?");
$cur->bind_param("i", $id);
$cur->execute();
$curRow = $cur->get_result()->fetch_assoc();
$cur->close();

if (!$curRow) {
    echo json_encode(["success" => false, "message" => "User tidak ditemukan"]);
    exit;
}

if ($curRow['role'] === 'all') {
    $countAll = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'all'")->fetch_assoc()['c'];
    if (intval($countAll) <= 1) {
        echo json_encode(["success" => false, "message" => "Tidak bisa menghapus akun superadmin terakhir"]);
        exit;
    }
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "deleted" => $stmt->affected_rows]);
} else {
    echo json_encode(["success" => false, "message" => $stmt->error]);
}
