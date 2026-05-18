<?php
session_start();
require_once __DIR__ . "/config.php";

header("Content-Type: application/json");

if (!isset($_SESSION['nik'])) {
    echo json_encode(["success" => false, "error" => "Session expired"]);
    exit;
}

$nik_karyawan = $_SESSION['nik'];
$data         = json_decode(file_get_contents("php://input"), true);

$mode           = strtoupper($data['mode']           ?? '');
$scan_input     = trim($data['part']                 ?? '');
$qty            = intval($data['qty']                ?? 0);
$model_item_id  = intval($data['model_item_id']      ?? 0); // hanya untuk OUT

if ($mode === '' || $scan_input === '' || $qty <= 0) {
    echo json_encode(["success" => false, "error" => "Data tidak lengkap"]);
    exit;
}

// OUT wajib ada model_item_id
if ($mode === 'OUT' && $model_item_id <= 0) {
    echo json_encode(["success" => false, "error" => "Pilih model terlebih dahulu"]);
    exit;
}

// ── 1. Ambil data item ────────────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT id, part_name, current_stock, location_id
    FROM items
    WHERE part_number = ?
    LIMIT 1
");
$stmt->bind_param("s", $scan_input);
$stmt->execute();
$stmt->bind_result($item_id, $part_name, $current_stock, $location_id);
$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    echo json_encode(["success" => false, "error" => "Part tidak ditemukan"]);
    exit;
}

$current_stock = intval($current_stock ?? 0);
$location_id   = intval($location_id   ?? 1);
$lokasi        = ($location_id == 2) ? "MS2" : "MS1";

// ── 2. Validasi model_item_id milik item ini (khusus OUT) ─────────────────
if ($mode === 'OUT') {
    $stmtChk = $conn->prepare("
        SELECT mi.id, mi.allocated_stock, m.model_name
        FROM model_items mi
        JOIN models m ON m.id = mi.model_id
        WHERE mi.id = ? AND mi.item_id = ?
        LIMIT 1
    ");
    $stmtChk->bind_param("ii", $model_item_id, $item_id);
    $stmtChk->execute();
    $stmtChk->bind_result($mi_id, $allocated_stock, $model_name);
    $miFound = $stmtChk->fetch();
    $stmtChk->close();

    if (!$miFound) {
        echo json_encode(["success" => false, "error" => "Model tidak valid untuk part ini"]);
        exit;
    }

    $allocated_stock = intval($allocated_stock ?? 0);

    // Cek allocated stock cukup
    if ($allocated_stock < $qty) {
        echo json_encode([
            "success" => false,
            "error"   => "Allocated stock model $model_name tidak cukup (tersedia: $allocated_stock)"
        ]);
        exit;
    }
}

// ── 3. Hitung stock baru ──────────────────────────────────────────────────
if ($mode === "IN" || $mode === "RETURN") {
    $new_stock = $current_stock + $qty;
} elseif ($mode === "OUT") {
    if ($current_stock < $qty) {
        echo json_encode(["success" => false, "error" => "Stock tidak cukup (total stock: $current_stock)"]);
        exit;
    }
    $new_stock = $current_stock - $qty;
} else {
    echo json_encode(["success" => false, "error" => "Mode tidak valid"]);
    exit;
}

// ── 4. Cek NIK ────────────────────────────────────────────────────────────
$cekNik = $conn->prepare("SELECT nik FROM karyawan WHERE nik = ?");
$cekNik->bind_param("s", $nik_karyawan);
$cekNik->execute();
$cekNik->bind_result($dummy);
$nik_exists = $cekNik->fetch();
$cekNik->close();

if (!$nik_exists) {
    echo json_encode(["success" => false, "error" => "NIK tidak terdaftar"]);
    exit;
}

// ── 5. Transaksi DB ───────────────────────────────────────────────────────
$conn->begin_transaction();

try {
    // 5a. Update current_stock di items
    $upItem = $conn->prepare("UPDATE items SET current_stock = ? WHERE id = ?");
    $upItem->bind_param("ii", $new_stock, $item_id);
    $upItem->execute();
    $upItem->close();

    // 5b. Jika OUT → kurangi allocated_stock model yang dipilih
    if ($mode === 'OUT') {
        $upAlloc = $conn->prepare("
            UPDATE model_items
            SET allocated_stock = allocated_stock - ?
            WHERE id = ?
        ");
        $upAlloc->bind_param("ii", $qty, $model_item_id);
        $upAlloc->execute();
        $upAlloc->close();
    }

    // 5c. Insert transaksi (tambah kolom model_item_id jika ada)
    $type = strtolower($mode);

    // Cek apakah kolom model_item_id ada di tabel transactions
    // Jika belum ada, insert tanpa kolom itu
    $miIdForInsert = ($mode === 'OUT') ? $model_item_id : null;

    if ($miIdForInsert !== null) {
        $ins = $conn->prepare("
            INSERT INTO transactions (item_id, type, qty, nik, lokasi, model_item_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $ins->bind_param("isissi", $item_id, $type, $qty, $nik_karyawan, $lokasi, $miIdForInsert);
    } else {
        $ins = $conn->prepare("
            INSERT INTO transactions (item_id, type, qty, nik, lokasi, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $ins->bind_param("isiss", $item_id, $type, $qty, $nik_karyawan, $lokasi);
    }

    $ins->execute();
    $ins->close();

    $conn->commit();

    echo json_encode([
        "success"         => true,
        "stock_after"     => $new_stock,
        "allocated_after" => ($mode === 'OUT') ? ($allocated_stock - $qty) : null
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "error" => "DB error: " . $e->getMessage()]);
}