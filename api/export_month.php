<?php
require_once __DIR__ . "/config.php";

$month     = $_GET['month'] ?? '';
$lokasi_id = intval($_GET['lokasi_id'] ?? 0);

if (!$month || $lokasi_id <= 0) {
    die("Parameter tidak valid");
}

$startMonth = $month . "-01";
$endMonth   = date("Y-m-d", strtotime("$startMonth +1 month"));

$fileName = "Monthly_Report_{$month}_Lokasi_{$lokasi_id}.xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$fileName\"");

/* ===========================
   1️⃣ AMBIL TANGGAL YANG ADA
=========================== */

$dateQuery = $conn->prepare("
    SELECT DISTINCT DATE(created_at) as trx_date
    FROM stock_logs
    WHERE created_at >= ?
      AND created_at < ?
      AND lokasi_id = ?
    ORDER BY trx_date ASC
");

$dateQuery->bind_param("ssi", $startMonth, $endMonth, $lokasi_id);
$dateQuery->execute();
$dateResult = $dateQuery->get_result();

$dates = [];
while ($d = $dateResult->fetch_assoc()) {
    $dates[] = $d['trx_date'];
}

if (empty($dates)) {
    echo "Tidak ada transaksi di bulan ini.";
    exit;
}

/* ===========================
   2️⃣ BUILD HEADER
=========================== */

echo "<table border='1'>";
echo "<tr style='font-weight:bold; background:#E0E0E0;'>";
echo "<td>Model</td>";
echo "<td>Part Number</td>";
echo "<td>Part Name</td>";

foreach ($dates as $date) {
    $day = date("d", strtotime($date));
    echo "<td>TGL $day IN</td>";
    echo "<td>TGL $day OUT</td>";
}

echo "<td>Total IN</td>";
echo "<td>Total OUT</td>";
echo "</tr>";

/* ===========================
   3️⃣ BUILD SELECT DINAMIS
=========================== */

$selectParts = [];

foreach ($dates as $date) {

    $aliasDate = str_replace('-', '', $date);

    $selectParts[] = "
        SUM(CASE 
            WHEN DATE(sl.created_at) = '$date' AND sl.type='in'
            THEN sl.qty ELSE 0 END) AS in_$aliasDate
    ";

    $selectParts[] = "
        SUM(CASE 
            WHEN DATE(sl.created_at) = '$date' AND sl.type='out'
            THEN sl.qty ELSE 0 END) AS out_$aliasDate
    ";
}

$selectDynamic = implode(",", $selectParts);

/* ===========================
   4️⃣ QUERY UTAMA
=========================== */

$sql = "
SELECT 
    m.model_name,
    i.part_number,
    i.part_name,
    agg.*,
    agg.total_in,
    agg.total_out

FROM items i

/* =========================
   AGREGASI STOCK DULU
========================= */
LEFT JOIN (
    SELECT 
        sl.item_id,
        $selectDynamic,
        SUM(CASE WHEN sl.type='in' THEN sl.qty ELSE 0 END) AS total_in,
        SUM(CASE WHEN sl.type='out' THEN sl.qty ELSE 0 END) AS total_out
    FROM stock_logs sl
    WHERE sl.created_at >= '$startMonth'
      AND sl.created_at < '$endMonth'
      AND sl.lokasi_id = $lokasi_id
    GROUP BY sl.item_id
) agg ON agg.item_id = i.id

/* =========================
   AMBIL SATU MODEL SAJA
========================= */
LEFT JOIN (
    SELECT 
        mi.item_id,
        MIN(m.model_name) as model_name
    FROM model_items mi
    JOIN models m ON mi.model_id = m.id
    GROUP BY mi.item_id
) m ON m.item_id = i.id

WHERE i.location_id = $lokasi_id

ORDER BY m.model_name ASC
";

/* ===========================
   5️⃣ EXECUTE QUERY (INI YANG TADI HILANG)
=========================== */

$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}

/* ===========================
   6️⃣ OUTPUT DATA
=========================== */

while ($row = $result->fetch_assoc()) {

    echo "<tr>";
    echo "<td>{$row['model_name']}</td>";
    echo "<td>{$row['part_number']}</td>";
    echo "<td>{$row['part_name']}</td>";

    foreach ($dates as $date) {
        $aliasDate = str_replace('-', '', $date);

        echo "<td>" . $row['in_' . $aliasDate] . "</td>";
        echo "<td>" . $row['out_' . $aliasDate] . "</td>";
    }

    echo "<td>{$row['total_in']}</td>";
    echo "<td>{$row['total_out']}</td>";
    echo "</tr>";
}

echo "</table>";
exit;
