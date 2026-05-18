<?php
// api/get_location_settings.php
// Ambil setting hari kerja per bulan untuk lokasi tertentu
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

$lokasi_id = intval($_GET['lokasi_id'] ?? 1);

$sql = "SELECT working_days_per_month FROM location_settings WHERE location_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lokasi_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    echo json_encode([
        "success" => true,
        "working_days_per_month" => (int)$row['working_days_per_month']
    ]);
} else {
    // Default jika belum ada
    echo json_encode([
        "success" => true,
        "working_days_per_month" => 22
    ]);
}