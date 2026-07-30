<?php
// api/update_user.php
// Update role/nama/password akun user (superadmin only)
session_start();
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'all') {
    echo json_encode(["success" => false, "message" => "Akses ditolak"]);
    exit;
}

$data     = json_decode(file_get_contents("php://input"), true);
$id       = intval($data['id'] ?? 0);
$nama     = trim($data['nama'] ?? '');
$role     = trim($data['role'] ?? '');
$password = (string)($data['password'] ?? '');

if ($id <= 0 || $nama === '') {
    echo json_encode(["success" => false, "message" => "Nama wajib diisi"]);
    exit;
}

// Validasi role terhadap role_hierarchy (bukan array hardcode, biar selalu sinkron dgn DB)
$roleCheck = $conn->prepare("SELECT role FROM role_hierarchy WHERE role = ?");
$roleCheck->bind_param("s", $role);
$roleCheck->execute();
if ($roleCheck->get_result()->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Role '$role' tidak valid"]);
    exit;
}
$roleCheck->close();

$cur = $conn->prepare("SELECT nik, role FROM users WHERE id = ?");
$cur->bind_param("i", $id);
$cur->execute();
$curRow = $cur->get_result()->fetch_assoc();
$cur->close();

if (!$curRow) {
    echo json_encode(["success" => false, "message" => "User tidak ditemukan"]);
    exit;
}

if ($curRow['role'] === 'all' && $role !== 'all') {
    $countAll = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'all'")->fetch_assoc()['c'];
    if (intval($countAll) <= 1) {
        echo json_encode(["success" => false, "message" => "Tidak bisa mengubah role akun superadmin terakhir. Buat superadmin lain dulu."]);
        exit;
    }
}

$conn->begin_transaction();
try {
    // Sinkronkan nama ke tabel karyawan juga (dipakai transaction.php)
    $kStmt = $conn->prepare("INSERT INTO karyawan (nik, nama) VALUES (?, ?) ON DUPLICATE KEY UPDATE nama = VALUES(nama)");
    $kStmt->bind_param("ss", $curRow['nik'], $nama);
    if (!$kStmt->execute()) throw new Exception($kStmt->error);

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET nama = ?, role = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nama, $role, $hash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET nama = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nama, $role, $id);
    }
    if (!$stmt->execute()) throw new Exception($stmt->error);

    $affected = $stmt->affected_rows;
    $conn->commit();
    echo json_encode(["success" => true, "updated" => $affected]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
