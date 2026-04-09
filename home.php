<?php
session_start();
session_unset();
session_regenerate_id(true);
require_once __DIR__ . "/api/config.php";

if (isset($_POST['login'])) {

    $nik = $_POST['nik'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT 
            u.id,
            u.nik,
            u.password,
            u.role,
            COALESCE(k.nama, u.nik) AS nama
        FROM users u
        LEFT JOIN karyawan k ON u.nik = k.nik
        WHERE u.nik = ?
    ");

    $stmt->bind_param("s", $nik);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();


        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nik']     = $user['nik'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['nama']    = $user['nama'];

            if ($user['role'] == "monitor") {
                header("Location: monitor.php");
            } elseif ($user['role'] == "ms1") {
                header("Location: index.php?lokasi=1");
            } elseif ($user['role'] == "ms2") {
                header("Location: index.php?lokasi=2");
            } elseif ($user['role'] == "all") {
                header("Location: home_menu.php");
            } elseif ($user['role'] == "operator") {
                header("Location: transaction.php");
            } elseif ($user['role'] == "machining") {
                header("Location: transaction.php");
            }


            exit;
        } else {
            $error = "Password salah";
        }
    } else {
        $error = "NIK tidak ditemukan";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Inventory Management System</title>
    <meta name="viewport" content="width=device-    width, initial-scale=1.0">

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
            width: 400px;
            padding: 45px;
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

        /* Logo */
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h2 {
            font-weight: 600;
            letter-spacing: 1px;
            color: #111827;
        }

        .logo p {
            font-size: 13px;
            color: #6b7280;
            margin-top: 5px;
        }

        /* Input */
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            height: 52px;
            padding: 0 16px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            font-size: 14px;
            transition: 0.2s;
        }

        input:focus {
            border-color: #000;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
        }

        /* Toggle */
        .toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 12px;
            color: #6b7280;
        }

        .toggle:hover {
            color: #000;
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

        /* Error */
        .error {
            background: #fef2f2;
            color: #dc2626;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 18px;
            font-size: 14px;
        }
    </style>

    <script>
        window.onload = function() {
            if (!sessionStorage.getItem("reloaded")) {
                sessionStorage.setItem("reloaded", "true");
                location.reload();
            }
        }
    </script>

</head>

<body>

    <div class="box">

        <div class="logo">
            <h2>PT. YADIN</h2>
            <p>Supermarket Machine Shop</p>
        </div>

        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

        <form method="POST">

            <div class="input-group">
                <input type="text" name="nik" placeholder="NIK / USERNAME" required>
            </div>

            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="PASSWORD" required>
                <span class="toggle" onclick="togglePassword()">Show</span>
            </div>

            <button type="submit" name="login">LOGIN</button>

        </form>

    </div>

    <script>
        function togglePassword() {
            let pass = document.getElementById("password");
            let toggle = document.querySelector(".toggle");

            if (pass.type === "password") {
                pass.type = "text";
                toggle.innerText = "Hide";
            } else {
                pass.type = "password";
                toggle.innerText = "Show";
            }
        }
    </script>

</body>

</html>