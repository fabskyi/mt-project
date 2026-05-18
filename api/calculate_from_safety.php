<?php
// api/calculate_from_safety.php
// Menghitung allocation_percentage berdasarkan rasio monthly_plan per model
// Menggunakan Largest Remainder Method agar total TEPAT 100%

header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

$input = json_decode(file_get_contents("php://input"), true);

if (!empty($input['item_ids']) && is_array($input['item_ids'])) {
    $ids          = array_map('intval', $input['item_ids']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types        = str_repeat('i', count($ids));
    $sqlFetch = "SELECT mi.id AS model_item_id, mi.item_id, mi.monthly_plan
                 FROM model_items mi WHERE mi.item_id IN ($placeholders)";
    $stmt = $conn->prepare($sqlFetch);
    $stmt->bind_param($types, ...$ids);

} elseif (isset($input['lokasi_id'])) {
    $lokasi_id = intval($input['lokasi_id']);
    $sqlFetch  = "SELECT mi.id AS model_item_id, mi.item_id, mi.monthly_plan
                  FROM model_items mi
                  JOIN items i ON i.id = mi.item_id
                  WHERE i.location_id = ?";
    $stmt = $conn->prepare($sqlFetch);
    $stmt->bind_param("i", $lokasi_id);

} else {
    echo json_encode(["success" => false, "message" => "Berikan lokasi_id atau item_ids"]);
    exit;
}

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!$rows) {
    echo json_encode(["success" => true, "updated" => 0, "message" => "Tidak ada data"]);
    exit;
}

// Kelompokkan per item_id
$byItem = [];
foreach ($rows as $r) {
    $byItem[$r['item_id']][] = $r;
}

// Hitung total monthly_plan dari SEMUA model per item
$itemIds       = array_keys($byItem);
$placeholders2 = implode(',', array_fill(0, count($itemIds), '?'));
$types2        = str_repeat('i', count($itemIds));

$stmtTotal = $conn->prepare(
    "SELECT item_id, SUM(monthly_plan) AS total_mp
     FROM model_items WHERE item_id IN ($placeholders2) GROUP BY item_id"
);
$stmtTotal->bind_param($types2, ...$itemIds);
$stmtTotal->execute();
$totalRows = $stmtTotal->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtTotal->close();

$totalMPMap = [];
foreach ($totalRows as $tr) {
    $totalMPMap[$tr['item_id']] = (float)$tr['total_mp'];
}

$conn->begin_transaction();
try {
    // Nonaktifkan trigger validasi sementara
    $conn->query("SET @disable_alloc_check = 1");

    $stmtUpdate = $conn->prepare(
        "UPDATE model_items SET allocation_percentage = ? WHERE id = ?"
    );
    if (!$stmtUpdate) throw new Exception("Prepare update failed: " . $conn->error);

    $updatedCount  = 0;
    $affectedItems = [];
    $skippedItems  = [];

    foreach ($byItem as $item_id => $modelRows) {
        $totalMP = $totalMPMap[$item_id] ?? 0;

        // Skip jika semua monthly_plan = 0
        if ($totalMP <= 0) {
            $skippedItems[] = $item_id;
            continue;
        }

        // ── Largest Remainder Method ──────────────────────────────────────────
        // Skala ke 10000 (presisi 2 desimal = 100.00%)
        $scale  = 10000;
        $target = 10000;
        $floors     = [];
        $remainders = [];

        foreach ($modelRows as $mr) {
            $exact        = ($mr['monthly_plan'] / $totalMP) * $target;
            $floors[]     = (int) floor($exact);
            $remainders[] = $exact - floor($exact);
        }

        // Bagi sisa ke model dengan remainder terbesar
        $assigned = array_sum($floors);
        $leftover = $target - $assigned;

        arsort($remainders);
        $given = 0;
        foreach ($remainders as $idx => $rem) {
            if ($given >= $leftover) break;
            $floors[$idx]++;
            $given++;
        }

        // Konversi ke persen 2 desimal & simpan ke DB
        foreach ($modelRows as $i => $mr) {
            $pct = round($floors[$i] / 100, 2);
            $mid = (int)$mr['model_item_id'];
            $stmtUpdate->bind_param("di", $pct, $mid);
            if (!$stmtUpdate->execute()) {
                throw new Exception("Update failed for id=$mid: " . $stmtUpdate->error);
            }
            $updatedCount++;
        }

        $affectedItems[] = (int)$item_id;
    }

    $stmtUpdate->close();

    // Aktifkan kembali trigger validasi
    $conn->query("SET @disable_alloc_check = 0");

    // Recalculate allocated_stock
    foreach ($affectedItems as $item_id) {
        if (!$conn->query("CALL recalculate_allocated_stock($item_id)")) {
            throw new Exception("CALL failed item_id=$item_id: " . $conn->error);
        }
        while ($conn->more_results() && $conn->next_result()) {
            $extra = $conn->store_result();
            if ($extra) $extra->free();
        }
    }

    $conn->commit();
    echo json_encode([
        "success"      => true,
        "updated"      => $updatedCount,
        "recalculated" => count($affectedItems),
        "skipped"      => count($skippedItems),
        "message"      => "Berhasil menghitung " . $updatedCount . " baris dari " . count($affectedItems) . " part" .
                          (count($skippedItems) > 0 ? " (" . count($skippedItems) . " part di-skip karena monthly plan = 0)" : "")
    ]);

} catch (Exception $e) {
    $conn->rollback();
    $conn->query("SET @disable_alloc_check = 0");
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}