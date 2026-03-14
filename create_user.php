<?php
require_once "api/config.php";

$nik = "1001";
$password = password_hash("12345", PASSWORD_DEFAULT);
$role = "all";

$stmt = $conn->prepare("INSERT INTO users (nik,password,role) VALUES (?,?,?)");
$stmt->bind_param("sss", $nik, $password, $role);
$stmt->execute();

echo "User created!";
