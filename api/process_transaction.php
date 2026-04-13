<?php
session_start();
require_once __DIR__ . "/config.php";

header("Content-Type: application/json");

// Aktifkan error reporting sementara (hapus setelah debug selesai)
ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['nik'])) {
    echo json_encode(["success" => false, "error" => "Session expired"]);
    exit;
}

$nik_karyawan = $_SESSION['nik'];

$data = json_decode(file_get_contents("php://input"), true);

$mode       = strtoupper($data['mode'] ?? '');
$scan_input = trim($data['part'] ?? '');
$qty        = intval($data['qty'] ?? 0);

if ($mode === '' || $scan_input === '' || $qty <= 0) {
    echo json_encode(["success" => false, "error" => "Data tidak lengkap"]);
    exit;
}

// ====================== 1. Ambil data item ======================
$stmt = $conn->prepare("
    SELECT id, part_name, current_stock 
    FROM items 
    WHERE part_number = ? 
    LIMIT 1
");
$stmt->bind_param("s", $scan_input);
$stmt->execute();
$stmt->bind_result($item_id, $part_name, $current_stock);
$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    echo json_encode(["success" => false, "error" => "Part tidak ditemukan"]);
    exit;
}

$current_stock = intval($current_stock);

// ====================== 2. Hitung stock baru ======================
if ($mode === "IN" || $mode === "RETURN") {
    $new_stock = $current_stock + $qty;
} elseif ($mode === "OUT") {
    if ($current_stock < $qty) {
        echo json_encode(["success" => false, "error" => "Stock tidak cukup"]);
        exit;
    }
    $new_stock = $current_stock - $qty;
} else {
    echo json_encode(["success" => false, "error" => "Mode tidak valid"]);
    exit;
}

// ====================== 3. Update stock ======================
$update = $conn->prepare("UPDATE items SET current_stock = ? WHERE id = ?");
$update->bind_param("ii", $new_stock, $item_id);
$update->execute();
$update->close();

// ====================== 4. Cek NIK karyawan ======================
$cekNik = $conn->prepare("SELECT nik FROM karyawan WHERE nik = ?");
$cekNik->bind_param("s", $nik_karyawan);
$cekNik->execute();
$cekNik->bind_result($dummy_nik);
$nik_exists = $cekNik->fetch();
$cekNik->close();

if (!$nik_exists) {
    echo json_encode([
        "success" => false,
        "error" => "NIK tidak terdaftar di tabel karyawan"
    ]);
    exit;
}

// ====================== 5. Insert transaksi ======================
$type = strtolower($mode);

$insert = $conn->prepare("
    INSERT INTO transactions (item_id, type, qty, nik, created_at)
    VALUES (?, ?, ?, ?, NOW())
");
$insert->bind_param("isis", $item_id, $type, $qty, $nik_karyawan);
$insert->execute();
$insert->close();

// ====================== Sukses ======================
echo json_encode([
    "success" => true,
    "stock_after" => $new_stock,
    "message" => "Transaksi berhasil"
]);
