<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

// Ambil role dari session - SUPPORT SEMUA VARIASI
$user_role = $_SESSION['role'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="assets/yanmar.png">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="assets/yanmar.png">
    <meta name="theme-color" content="#ffffff">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PT. Yadin Supermarket</title>
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

        /* Role Badge */
        .role-badge {
            text-align: center;
            margin-bottom: 25px;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
        }

        .role-admin {
            background: #fef3c7;
            color: #d97706;
            border: 1px solid #f59e0b;
        }

        .role-ms1 {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #3b82f6;
        }

        .role-ms2 {
            background: #f0f9ff;
            color: #0e7490;
            border: 1px solid #06b6d4;
        }

        .role-noaccess {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
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

        /* Disabled Button */
        .menu .disabled {
            background: #f9fafb !important;
            color: #9ca3af !important;
            border-color: #d1d5db !important;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .menu .disabled:hover {
            background: #f9fafb !important;
            color: #9ca3af !important;
            transform: none !important;
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

        <!-- Role Badge - FULL SUPPORT -->
        <?php 
        $display_role = '';
        $role_class = 'role-noaccess';
        
        if (in_array($user_role, ['admin', 'all'])) {
            $display_role = '🛡️ ADMIN - Full Access';
            $role_class = 'role-admin';
        } elseif (in_array($user_role, ['ms_1', 'ms1'])) {
            $display_role = '🏭 MS1 Operator';
            $role_class = 'role-ms1';
        } elseif (in_array($user_role, ['ms_2', 'ms2'])) {
            $display_role = '🏭 MS2 Operator';
            $role_class = 'role-ms2';
        } else {
            $display_role = '❌ Role: ' . htmlspecialchars($user_role) . ' - No Access';
        }
        ?>
        <div class="role-badge <?php echo $role_class; ?>">
            <?php echo $display_role; ?>
        </div>

        <div class="menu">
            <!-- 1. Buat Akun Baru - Hanya Admin -->
            <?php if (in_array($user_role, ['admin', 'all'])): ?>
                <a href="create_account.php">Buat Akun Baru</a>
            <?php else: ?>
                <a class="disabled" title="Admin only">Buat Akun Baru</a>
            <?php endif; ?>

            <!-- 2. Tambah Karyawan - Hanya Admin -->
            <?php if (in_array($user_role, ['admin', 'all'])): ?>
                <a href="add_karyawan.php">Tambah Karyawan</a>
            <?php else: ?>
                <a class="disabled" title="Admin only">Tambah Karyawan</a>
            <?php endif; ?>

            <!-- 3. Monitoring Display - SEMUA ROLE -->
            <a href="monitor.php">Monitoring Display</a>

            <!-- 4. Dashboard MS1 - Admin & ms_1/ms1 -->
            <?php if (in_array($user_role, ['admin', 'all', 'ms_1', 'ms1'])): ?>
                <a href="index.php?lokasi=1">Dashboard MS1</a>
            <?php else: ?>
                <a class="disabled" title="MS1 Operator or Admin only">Dashboard MS1</a>
            <?php endif; ?>

            <!-- 5. Dashboard MS2 - Admin & ms_2/ms2 -->
            <?php if (in_array($user_role, ['admin', 'all', 'ms_2', 'ms2'])): ?>
                <a href="index.php?lokasi=2">Dashboard MS2</a>
            <?php else: ?>
                <a class="disabled" title="MS2 Operator or Admin only">Dashboard MS2</a>
            <?php endif; ?>

            <!-- 6. Transaction System - SESUAI ROLE -->
            <?php if (in_array($user_role, ['ms_1', 'ms1'])): ?>
                <a href="transaction.php?lokasi=1">Transaction MS1</a>
            <?php elseif (in_array($user_role, ['ms_2', 'ms2'])): ?>
                <a href="transaction.php?lokasi=2">Transaction MS2</a>
            <?php elseif (in_array($user_role, ['admin', 'all'])): ?>
                <a href="transaction.php">Transaction System</a>
            <?php else: ?>
                <a class="disabled" title="Operator only">Transaction System</a>
            <?php endif; ?>

            <!-- 7. Logout - SEMUA -->
            <a href="signout.php" class="logout">Logout</a>
        </div>
    </div>
</body>
</html>
