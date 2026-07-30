<?php
// api/get_role_hierarchy.php
// Sumber tunggal struktur role parent-child, dibaca dari tabel role_hierarchy.
// Dipakai user_setting.php supaya UI selalu sinkron dengan DB (bukan hardcode di JS).
session_start();
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'all') {
    echo json_encode(["success" => false, "message" => "Akses ditolak"]);
    exit;
}

$result = mysqli_query($conn, "SELECT role, parent_group, parent_label, child_label FROM role_hierarchy ORDER BY sort_order ASC");

$rows = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
}

echo json_encode(["success" => true, "data" => $rows]);
