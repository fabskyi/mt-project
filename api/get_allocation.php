<?php
// api/get_allocation.php
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

$lokasi_id = intval($_GET['lokasi_id'] ?? 1);

$sql = "
    SELECT
        mi.id                   AS model_item_id,
        mi.model_id,
        mi.item_id,
        m.model_name,
        i.part_name,
        i.part_number,
        i.current_stock,
        mi.allocation_percentage,
        mi.allocated_stock,
        mi.safety_stock,
        mi.monthly_plan,
        mi.working_days_per_week,
        ss_agg.total_safety_stock,
        mp_agg.total_monthly_plan,
        COALESCE(ls.working_days_per_month, 22) AS working_days_per_month
    FROM model_items mi
    JOIN models  m ON m.id  = mi.model_id
    JOIN items   i ON i.id  = mi.item_id
    LEFT JOIN location_settings ls ON ls.location_id = i.location_id
    JOIN (
        SELECT item_id, SUM(safety_stock) AS total_safety_stock
        FROM model_items GROUP BY item_id
    ) ss_agg ON ss_agg.item_id = mi.item_id
    JOIN (
        SELECT item_id, SUM(monthly_plan) AS total_monthly_plan
        FROM model_items GROUP BY item_id
    ) mp_agg ON mp_agg.item_id = mi.item_id
    WHERE i.location_id = ?
    ORDER BY i.part_name ASC, m.model_name ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lokasi_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(["success" => true, "data" => $data]);