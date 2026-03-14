<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control</title>

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

        /* Card Container */
        .container {
            width: 700px;
            padding: 50px;
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

        .container h2 {
            text-align: center;
            margin-bottom: 35px;
            font-weight: 600;
        }

        /* Menu Grid */
        .menu {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        /* Button Style */
        .menu a {
            text-decoration: none;
            padding: 18px;
            border-radius: 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            color: #111827;
            font-weight: 500;
            text-align: center;
            transition: 0.2s ease;
        }

        .menu a:hover {
            background: #000;
            color: #fff;
            transform: translateY(-2px);
        }

        /* Logout Special */
        .logout {
            grid-column: span 3;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .logout:hover {
            background: #dc2626;
            color: #fff;
        }

        /* Responsive */
        @media(max-width:768px) {

            .container {
                width: 95%;
                padding: 30px;
            }

            .menu {
                grid-template-columns: 1fr;
            }

            .logout {
                grid-column: span 1;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Admin Control Panel</h2>

        <div class="menu">
            <a href="create_account.php">Buat Akun Baru</a>
            <a href="add_karyawan.php">Tambah Karyawan</a>
            <a href="monitor.php">Monitoring Display</a>
            <a href="index.php?lokasi=1">Dashboard MS1</a>
            <a href="index.php?lokasi=2">Dashboard MS2</a>
            <a href="transaction.php">Transaction System</a>
            <a href="signout.php" class="logout">Logout</a>
        </div>
    </div>

</body>

</html>