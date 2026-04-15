<?php
session_start();
require_once __DIR__ . "/config.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../home.php"); exit; }

$role = $_SESSION['role'];
if ($role !== 'ms2' && $role !== 'ms1' && $role !== 'machining' && $role !== 'all'){
    http_response_code(403);
    die("Akses ditolak");
}

$date = $_GET['date'] ?? date('Y-m-d');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="transaksi_' . $date . '.csv"');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM agar Excel tidak error
fputcsv($out, ['No', 'Waktu', 'NIK', 'Nama', 'Part Name', 'Part Number', 'Type', 'Qty']);

$stmt = $conn->prepare("
    SELECT 
        t.type,
        t.qty,
        t.created_at,
        t.nik,
        k.nama,
        i.part_name,
        i.part_number
    FROM transactions t
    JOIN items i ON t.item_id = i.id
    LEFT JOIN karyawan k ON t.nik = k.nik
    WHERE DATE(t.created_at) = ?
    ORDER BY t.created_at DESC
");
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$no = 1;
while ($row = $result->fetch_assoc()) {
    fputcsv($out, [
        $no++,
        $row['created_at'],
        $row['nik'],
        $row['nama'] ?? '-',
        $row['part_name'],
        $row['part_number'],
        strtoupper($row['type']),
        $row['qty']
    ]);
}

fclose($out);
exit;
