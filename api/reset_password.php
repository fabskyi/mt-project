<?php
require_once "api/config.php";

$nik = "admin";
$newPassword = password_hash("12345", PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password=? WHERE nik=?");
$stmt->bind_param("ss", $newPassword, $nik);
$stmt->execute();

echo "Password berhasil direset!";
