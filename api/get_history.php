<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['nik'])) {
    echo "Session expired";
    exit;
}

/*
Menampilkan 20 transaksi terakhir
Sekarang tampilkan:
- Type
- Part
- Qty
- NIK
- Nama Karyawan
*/

$query = "
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
ORDER BY t.created_at DESC
LIMIT 20
";

$result = $conn->query($query);

if ($result->num_rows == 0) {
    echo "<div class='history-item'>Belum ada transaksi</div>";
    exit;
}

while ($row = $result->fetch_assoc()) {

    $type = strtolower($row['type'] ?? '');

    $typeColor = "black";
    $typeLabel = "-";

    switch ($type) {
        case "in":
            $typeColor = "green";
            $typeLabel = "IN";
            break;
        case "out":
            $typeColor = "red";
            $typeLabel = "OUT";
            break;
        case "return":
            $typeColor = "orange";
            $typeLabel = "RETURN";
            break;
    }

    echo "
    <div class='history-item'>
        <strong style='color:$typeColor'>$typeLabel</strong>
        - {$row['part_name']} ({$row['part_number']})
        - Qty: {$row['qty']}
        <br>
        <small>
            {$row['nama']} (NIK: {$row['nik']}) | {$row['created_at']}
        </small>
    </div>
    ";
}
