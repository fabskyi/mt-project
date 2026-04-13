<?php
session_start();
require_once __DIR__ . "/config.php";
header("Content-Type: application/json");


if (!isset($_SESSION['nik'])) {
    echo json_encode(["success" => false, "error" => "Session expired"]);
    exit;
}

$nik_karyawan = $_SESSION['nik'];

$data = json_decode(file_get_contents("php://input"), true);

$mode = strtoupper($data['mode'] ?? '');
$scan_input = trim($data['part'] ?? '');
$qty  = intval($data['qty'] ?? 0);

if ($mode == '' || $scan_input == '' || $qty <= 0) {
    echo json_encode(["success" => false, "error" => "Data tidak lengkap"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, part_name, current_stock
    FROM items
    WHERE part_number = ?
    LIMIT 1
");
$stmt->bind_param("s", $scan_input);
error_log("scan_input: " . $scan_input);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode(["success" => false, "error" => "Part tidak ditemukan"]);
    exit;
}

$item = $res->fetch_assoc();
$item_id = $item['id'];
$current_stock = intval($item['current_stock']);

if ($mode == "IN" || $mode == "RETURN") {
    $new_stock = $current_stock + $qty;
} else if ($mode == "OUT") {

    if ($current_stock < $qty) {
        echo json_encode(["success" => false, "error" => "Stock tidak cukup"]);
        exit;
    }

    $new_stock = $current_stock - $qty;
} else {
    echo json_encode(["success" => false, "error" => "Mode tidak valid"]);
    exit;
}

$update = $conn->prepare("
    UPDATE items
    SET current_stock=?
    WHERE id=?
");
$update->bind_param("ii", $new_stock, $item_id);
$update->execute();

$cekNik = $conn->prepare("SELECT nik FROM karyawan WHERE nik = ?");
$cekNik->bind_param("s", $nik_karyawan);
$cekNik->execute();
$cekRes = $cekNik->get_result();

if ($cekRes->num_rows == 0) {
    echo json_encode([
        "success" => false,
        "error" => "NIK tidak terdaftar di tabel karyawan"
    ]);
    exit;
}
$type = strtolower($mode);

$insert = $conn->prepare("
    INSERT INTO transactions (item_id, type, qty, nik, created_at)
    VALUES (?, ?, ?, ?, NOW())
");

$insert->bind_param("isis", $item_id, $type, $qty, $nik_karyawan);
$insert->execute();

echo json_encode([
    "success" => true,
    "stock_after" => $new_stock
]);
