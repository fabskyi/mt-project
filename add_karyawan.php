<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'all') {
    die("Akses ditolak!");
}

require_once __DIR__ . "/api/config.php";

if (!isset($_SESSION['nik'])) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nik  = trim($_POST['nik']);
    $nama = trim($_POST['nama']);

    if ($nik == "" || $nama == "") {
        $message = "Semua field wajib diisi";
    } else {

        // cek duplicate
        $cek = $conn->prepare("SELECT nik FROM karyawan WHERE nik=?");
        $cek->bind_param("s", $nik);
        $cek->execute();
        $res = $cek->get_result();

        if ($res->num_rows > 0) {
            $message = "NIK sudah terdaftar";
        } else {

            $insert = $conn->prepare("
                INSERT INTO karyawan (nik, nama)
                VALUES (?, ?)
            ");
            $insert->bind_param("ss", $nik, $nama);

            if ($insert->execute()) {
                $message = "Karyawan berhasil ditambahkan";
            } else {
                $message = "Gagal menambahkan karyawan";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Karyawan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        html,
        body {
            height: 100%;
        }

        body {
            background: linear-gradient(135deg, #f0f2f5, #e4e8ee);
            display: grid;
            place-items: center;
        }

        /* Card */
        .box {
            width: 420px;
            padding: 40px;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .box h3 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
        }

        /* Input */
        .input-group {
            margin-bottom: 18px;
        }

        input {
            width: 100%;
            height: 52px;
            padding: 0 16px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            font-size: 14px;
            transition: 0.2s;
        }

        input:focus {
            border-color: #000;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
        }

        /* Button */
        button {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 10px;
            background: #000;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        button:hover {
            background: #222;
            transform: translateY(-1px);
        }

        /* Message */
        .msg {
            margin-top: 18px;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-size: 14px;
        }

        .msg.success {
            background: #ecfdf5;
            color: #16a34a;
        }

        .msg.error {
            background: #fef2f2;
            color: #dc2626;
        }

        /* Back link */
        .back {
            display: block;
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            color: #555;
            text-decoration: none;
            transition: 0.2s;
        }

        .back:hover {
            color: #000;
        }
    </style>
</head>

<body>

    <div class="box">
        <h3>Tambah Karyawan</h3>

        <form method="POST">
            <div class="input-group">
                <input type="text" name="nik" placeholder="NIK" required>
            </div>

            <div class="input-group">
                <input type="text" name="nama" placeholder="Nama Karyawan" required>
            </div>

            <button type="submit">Simpan</button>
        </form>

        <?php if ($message != ""): ?>
            <div class="msg <?= (strpos($message, 'berhasil') !== false) ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <a href="home_menu.php" class="back">← Kembali ke Menu</a>
    </div>

</body>

</html>