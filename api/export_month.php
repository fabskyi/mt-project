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
echo "<td>Tanggal</td>";
echo "<td>Type</td>";
echo "<td>Qty</td>";
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
    DATE(sl.created_at) as trx_date,
    sl.type,
    SUM(sl.qty) as qty

FROM stock_logs sl

JOIN items i ON sl.item_id = i.id

LEFT JOIN (
    SELECT 
        mi.item_id,
        MIN(m.model_name) as model_name
    FROM model_items mi
    JOIN models m ON mi.model_id = m.id
    GROUP BY mi.item_id
) m ON m.item_id = i.id

WHERE sl.created_at >= '$startMonth'
  AND sl.created_at < '$endMonth'
  AND sl.lokasi_id = $lokasi_id

GROUP BY 
    m.model_name,
    i.part_number,
    i.part_name,
    DATE(sl.created_at),
    sl.type

ORDER BY 
    m.model_name ASC,
    i.part_name ASC,
    trx_date ASC
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

    while ($row = $result->fetch_assoc()) {

        echo "<tr>";
        echo "<td>{$row['model_name']}</td>";
        echo "<td>{$row['part_number']}</td>";
        echo "<td>{$row['part_name']}</td>";
        echo "<td>{$row['trx_date']}</td>";
        echo "<td>" . strtoupper($row['type']) . "</td>";
        echo "<td>{$row['qty']}</td>";
        echo "</tr>";
    }
}

echo "</table>";
exit;
