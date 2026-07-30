<?php
// api/add_user.php
// Tambah akun user baru (superadmin only)
session_start();
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'all') {
    echo json_encode(["success" => false, "message" => "Akses ditolak"]);
    exit;
}

$data     = json_decode(file_get_contents("php://input"), true);
$nik      = trim($data['nik'] ?? '');
$nama     = trim($data['nama'] ?? '');
$password = (string)($data['password'] ?? '');
$role     = trim($data['role'] ?? '');

if ($nik === '' || $nama === '' || $password === '') {
    echo json_encode(["success" => false, "message" => "NIK, nama, dan password wajib diisi"]);
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

$check = $conn->prepare("SELECT id FROM users WHERE nik = ?");
$check->bind_param("s", $nik);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "NIK '$nik' sudah terdaftar"]);
    exit;
}
$check->close();

$hash = password_hash($password, PASSWORD_DEFAULT);

$conn->begin_transaction();
try {
    // Upsert data karyawan (dipakai transaction.php untuk lookup nama dari nik)
    $kStmt = $conn->prepare("INSERT INTO karyawan (nik, nama) VALUES (?, ?) ON DUPLICATE KEY UPDATE nama = VALUES(nama)");
    $kStmt->bind_param("ss", $nik, $nama);
    if (!$kStmt->execute()) throw new Exception($kStmt->error);

    $stmt = $conn->prepare("INSERT INTO users (nik, password, role, nama) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nik, $hash, $role, $nama);
    if (!$stmt->execute()) throw new Exception($stmt->error);

    $newId = $conn->insert_id;
    $conn->commit();
    echo json_encode(["success" => true, "id" => $newId]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
