<?php
session_start();
require_once __DIR__ . "/api/config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'all') {
    die("Akses ditolak!");
}

if (isset($_POST['create'])) {

    $nik = trim($_POST['nik']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $check = $conn->prepare("SELECT id FROM users WHERE nik=?");
    $check->bind_param("s", $nik);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "NIK sudah terdaftar!";
    } else {

        $stmt = $conn->prepare("INSERT INTO users (nik,password,role) VALUES (?,?,?)");
        $stmt->bind_param("sss", $nik, $password, $role);

        if ($stmt->execute()) {
            $success = "Akun berhasil dibuat!";
        } else {
            $error = "Gagal membuat akun!";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Buat Akun</title>
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #f0f2f5, #e4e8ee);
            display: grid;
            place-items: center;
        }

        .box {
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            width: 420px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
            animation: fadeIn 0.4s ease;
        }

        h2 {
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
        }

        input,
        select {
            width: 100%;
            height: 52px;
            /* 🔥 samakan tinggi */
            padding: 0 16px;
            /* padding horizontal saja */
            margin-bottom: 18px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            font-size: 14px;
            transition: 0.2s;
            appearance: none;
            /* 🔥 hilangkan default style beda */
        }

        input:focus,
        select:focus {
            border-color: #000;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
        }

        button {
            padding: 14px;
            background: #000;
            color: white;
            border: none;
            border-radius: 10px;
            width: 100%;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        button:hover {
            background: #222;
            transform: translateY(-1px);
        }

        .msg {
            margin-bottom: 18px;
            text-align: center;
            font-size: 14px;
        }

        .error {
            color: #dc2626;
        }

        .success {
            color: #16a34a;
        }

        .toggle-pass {
            font-size: 12px;
            color: #555;
            text-align: right;
            margin-top: -12px;
            margin-bottom: 15px;
            cursor: pointer;
            user-select: none;
        }

        .toggle-pass:hover {
            color: #000;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            font-size: 13px;
            color: #555;
            transition: 0.2s;
        }

        .back-link:hover {
            color: #000;
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
    </style>
</head>

<body>

    <div class="box">
        <h2>Buat Akun Baru</h2>

        <?php if (isset($error)) echo "<div class='msg error'>$error</div>"; ?>
        <?php if (isset($success)) echo "<div class='msg success'>$success</div>"; ?>

        <form method="POST">

            <input type="text" name="nik" placeholder="NIK" required>

            <input type="password" id="password" name="password" placeholder="Password" required>
            <div class="toggle-pass" onclick="togglePassword()">
                Show / Hide Password
            </div>

            <select name="role" required>
                <option value="">-- Pilih Role --</option>
                <option value="monitor">Monitor</option>
                <option value="operator">Operator</option>
                <option value="ms1">MS1</option>
                <option value="ms2">MS2</option>
                <option value="machining">Machining</option>
                <option value="all">All Access</option>
            </select>


            <button type="submit" name="create">Buat Akun</button>

        </form>

        <br>
        <a href="home_menu.php" class="back-link">← Kembali</a>

    </div>

    <script>
        function togglePassword() {
            let pass = document.getElementById("password");
            pass.type = pass.type === "password" ? "text" : "password";
        }
    </script>

</body>

</html>