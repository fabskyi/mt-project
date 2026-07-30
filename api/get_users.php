<?php
// api/get_users.php
// Daftar semua akun user (superadmin only)
session_start();
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'all') {
    echo json_encode(["success" => false, "message" => "Akses ditolak"]);
    exit;
}

$query = "
    SELECT
        u.id, u.nik, COALESCE(NULLIF(u.nama,''), k.nama) AS nama, u.role,
        rh.parent_group, rh.parent_label, rh.child_label
    FROM users u
    LEFT JOIN karyawan k ON k.nik = u.nik
    LEFT JOIN role_hierarchy rh ON rh.role = u.role
    ORDER BY rh.sort_order ASC, u.nik ASC
";
$result = mysqli_query($conn, $query);

$users = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['id'] = intval($row['id']);
        $users[] = $row;
    }
}

echo json_encode(["success" => true, "data" => $users]);
