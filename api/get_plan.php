<?php
require_once "config.php";

$date = $_GET['date'];

$stmt = $conn->prepare("
    SELECT plan_qty 
    FROM production_plan
    WHERE plan_date = ?
");

$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "success" => true,
        "plan" => $row['plan_qty']
    ]);
} else {
    echo json_encode([
        "success" => true,
        "plan" => 0
    ]);
}
