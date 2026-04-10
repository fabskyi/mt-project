<?php

require_once "config.php";

$lokasi = $_GET['lokasi'] ?? "";
$time   = $_GET['time'] ?? "today";
$type   = $_GET['type'] ?? "";

$where = [];

/* FILTER TIME */

if ($time == "today") {
    $where[] = "DATE(l.created_at) = CURDATE()";
} elseif ($time == "week") {
    $where[] = "l.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($time == "month") {
    $where[] = "l.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

/* FILTER LOKASI */

if ($lokasi != "") {
    $where[] = "l.lokasi_id = '$lokasi'";
}

/* FILTER TYPE */

if ($type != "") {
    $where[] = "l.type = '$type'";
}

/* BUILD WHERE */

$whereSQL = "";

if (count($where) > 0) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

/* QUERY */

$query = $conn->query("

SELECT 
l.id,
l.type,
l.qty,
l.before_stock,
l.after_stock,
l.note,
l.created_at,
l.lokasi_id,
i.part_name,
i.part_number

FROM stock_logs l

JOIN items i 
ON l.item_id = i.id

$whereSQL

ORDER BY l.created_at DESC

");

/* RESULT */

$data = [];

while ($row = $query->fetch_assoc()) {

    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);
