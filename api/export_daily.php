<?php
require_once __DIR__ . "/config.php";

$lokasi_id = intval($_GET['lokasi_id'] ?? 0);
$date = $_GET['date'] ?? date('Y-m-d');

if ($lokasi_id <= 0) {
    die("Invalid lokasi");
}

$fileName = "Daily_Report_{$date}_Lokasi_{$lokasi_id}.xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$fileName\"");

echo "<table border='1'>";
echo "<tr style='font-weight:bold; background:#E0E0E0;'>
        <td>Model</td>
        <td>Part Number</td>
        <td>Stock</td>
        <td>Safety</td>
        <td>IN</td>
        <td>OUT</td>
        <td>Last Update</td>
      </tr>";

$stmt = $conn->prepare("
    SELECT 
        m.model_name,
        i.part_number,
        i.current_stock,
        ms.safety_stock,
        i.updated_at,
        COALESCE(agg.total_in,0) as total_in,
        COALESCE(agg.total_out,0) as total_out

    FROM items i

    /* =========================
       AGREGASI STOCK DULU
    ========================= */
    LEFT JOIN (
        SELECT 
            sm.item_id,
            SUM(CASE WHEN sm.type = 'IN' THEN sm.qty ELSE 0 END) as total_in,
            SUM(CASE WHEN sm.type = 'OUT' THEN sm.qty ELSE 0 END) as total_out
        FROM stock_logs sm
        WHERE DATE(sm.created_at) = ?
        GROUP BY sm.item_id
    ) agg ON agg.item_id = i.id

    /* =========================
       AMBIL 1 MODEL SAJA
    ========================= */
    LEFT JOIN (
        SELECT 
            mi.item_id,
            MIN(m.model_name) as model_name,
            MIN(mi.safety_stock) as safety_stock
        FROM model_items mi
        JOIN models m ON mi.model_id = m.id
        GROUP BY mi.item_id
    ) m ON m.item_id = i.id

    LEFT JOIN (
        SELECT 
            mi.item_id,
            MIN(mi.safety_stock) as safety_stock
        FROM model_items mi
        GROUP BY mi.item_id
    ) ms ON ms.item_id = i.id

    WHERE i.location_id = ?
    AND DATE(i.updated_at) = ?

    ORDER BY m.model_name ASC
");

$stmt->bind_param("sis", $date, $lokasi_id, $date);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['model_name']}</td>
            <td>{$row['part_number']}</td>
            <td>{$row['current_stock']}</td>
            <td>{$row['safety_stock']}</td>
            <td>{$row['total_in']}</td>
            <td>{$row['total_out']}</td>
            <td>{$row['updated_at']}</td>
          </tr>";
}

echo "</table>";
exit;
