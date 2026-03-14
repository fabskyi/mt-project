<?php
session_start();
require_once "config.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$nik = trim($data['nik'] ?? '');

if ($nik == '') {
    echo json_encode(["success" => false]);
    exit;
}

/* ===============================
   AMBIL DATA USER DARI TABEL USERS
   =============================== */

$stmt = $conn->prepare("SELECT id, nik, password, role FROM users WHERE nik=? LIMIT 1");

if (!$stmt) {
    echo json_encode(["success" => false]);
    exit;
}

$stmt->bind_param("s", $nik);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    // Kalau kamu belum pakai password, langsung login
    // Kalau pakai password, nanti kita tambah verify di sini

    $_SESSION['user_id'] = $row['id'];
    $_SESSION['nik']     = $row['nik'];
    $_SESSION['role']    = $row['role'];

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}
