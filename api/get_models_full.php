<?php
// api/get_models_full.php
// Daftar semua model beserta jumlah part (routing) yang terhubung
header("Content-Type: application/json");
require_once __DIR__ . "/config.php";

$query = "
    SELECT
        m.id,
        m.model_name,
        COUNT(mi.id) AS part_count
    FROM models m
    LEFT JOIN model_items mi ON mi.model_id = m.id
    GROUP BY m.id, m.model_name
    ORDER BY m.model_name ASC
";

$result = mysqli_query($conn, $query);

$models = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['id']         = intval($row['id']);
        $row['part_count'] = intval($row['part_count']);
        $models[] = $row;
    }
}

echo json_encode(["success" => true, "data" => $models]);
