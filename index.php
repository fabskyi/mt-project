<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

$role = $_SESSION['role'];

if ($role == "ms1") {
    $lokasi = 1;
} elseif ($role == "ms2") {
    $lokasi = 2;
} elseif ($role == "all") {
    $lokasi = $_GET['lokasi'] ?? 1;
} else {
    header("Location: home.php");
    exit;
}

if ($role == "ms1" && $lokasi != 1) {
    header("Location: index.php?lokasi=1");
    exit;
}

if ($role == "ms2" && $lokasi != 2) {
    header("Location: index.php?lokasi=2");
    exit;
}

if ($role == "monitor") {
    header("Location: monitor.php");
    exit;
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Machine Shop</title>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Segoe UI, sans-serif;
        }

        body {
            background: #f4f6f9;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 220px;
            background: #1f2d3d;
            color: white;
            padding: 20px;
        }

        .logo {
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar li {
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .sidebar li.active,
        .sidebar li:hover {
            background: #3c8dbc;
        }

        .main {
            flex: 1;
            padding: 25px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .top-actions {
            display: flex;
            gap: 10px;
        }

        .top-actions input {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            width: 250px;
        }

        .btn-back {
            background: #2563eb;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            transition: 0.2s ease;
        }

        .btn-back:hover {
            background: #1d4ed8;
            transform: scale(1.05);
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 30px;
            font-weight: bold;
        }

        .report-card input {
            width: 100%;
            padding: 6px;
            margin-top: 8px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .report-card button {
            margin-top: 10px;
            padding: 8px;
            width: 100%;
            border: none;
            border-radius: 8px;
            background: #3c8dbc;
            color: white;
            cursor: pointer;
        }

        .table-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
            overflow: auto;
        }

        #stockTable {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 14px;
        }

        #stockTable thead {
            background: #f1f5f9;
        }

        #stockTable th {
            padding: 14px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            text-align: center;
        }

        #stockTable td {
            padding: 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            text-align: center;
            vertical-align: middle;
        }

        #stockTable tbody tr:nth-child(even) {
            background: #fafbfd;
        }

        #stockTable tbody tr:hover {
            background: #eef6ff;
        }

        .low-row {
            background: #fff1f1 !important;
        }

        #stockTable th:nth-child(1),
        #stockTable td:nth-child(1) {
            width: 60px;
        }

        #stockTable th:nth-child(2),
        #stockTable td:nth-child(2) {
            width: 120px;
        }

        #stockTable th:nth-child(3),
        #stockTable td:nth-child(3) {
            width: 150px;
        }

        #stockTable th:nth-child(4),
        #stockTable td:nth-child(4) {
            width: 180px;
        }

        #stockTable th:nth-child(5),
        #stockTable td:nth-child(5) {
            width: 160px;
        }

        #stockTable th:nth-child(6),
        #stockTable td:nth-child(6) {
            width: 100px;
        }

        #stockTable th:nth-child(7),
        #stockTable td:nth-child(7) {
            width: 90px;
        }

        #stockTable th:nth-child(8),
        #stockTable td:nth-child(8) {
            width: 110px;
        }

        #stockTable th:nth-child(9),
        #stockTable td:nth-child(9) {
            width: 110px;
        }

        #stockTable th:nth-child(10),
        #stockTable td:nth-child(10) {
            width: 110px;
        }

        .stock-input {
            width: 70px;
            padding: 6px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            text-align: center;
            font-weight: 600;
        }

        .low {
            background: #fee2e2;
            color: #dc2626;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .over {
            background: #fee2e2;
            color: #ff7b00;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .ok {
            background: #dcfce7;
            color: #16a34a;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .barcode-svg {
            width: 130px;
            height: 40px;
            display: block;
            margin: auto;
        }


        #stockTable th:first-child,
        #stockTable td:first-child {
            position: sticky;
            left: 0;
            background: #f8fafc;
            z-index: 3;
            box-shadow: 2px 0 6px rgba(0, 0, 0, 0.04);
        }

        #stockTable tbody tr:nth-child(even) td:first-child {
            background: #fafbfd;
        }

        .low-row td:first-child {
            background: #fff1f1 !important;
        }

        @keyframes stockUp {
            0% {
                background: #dcfce7;
            }

            100% {
                background: transparent;
            }
        }

        @keyframes stockDown {
            0% {
                background: #fee2e2;
            }

            100% {
                background: transparent;
            }
        }

        .stock-up {
            animation: stockUp 1s ease forwards;
        }

        .stock-down {
            animation: stockDown 1s ease forwards;
        }

        .stock-input {
            transition: all .2s ease;
        }

        #stockTable td span {
            transition: all .2s ease;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .top-actions input,
        .top-actions select {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 13px;
        }

        #searchInput {
            width: 260px;
        }

        #modelSafetySelect {
            width: 140px;
        }

        #safetyStockInput {
            width: 110px;
        }

        .btn-safety {
            padding: 7px 14px;
            background: #111;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-safety:hover {
            background: #333;
        }

        .fade-refresh {
            opacity: 1;
            transition: opacity .3s ease;
        }

        .fade-show {
            opacity: 1;
            transition: opacity .3s ease;
        }

        .btn-nav {
            padding: 7px 14px;
            background: #6c757d;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-nav:hover {
            background: #5a6268;
        }

        .btn-logout {
            padding: 7px 14px;
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-logout:hover {
            background: #b02a37;
        }

        /* ===== REFRESH BUTTON ===== */
        .btn-refresh {
            padding: 7px 14px;
            background: #0d9488;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-refresh:hover {
            background: #0f766e;
        }

        .btn-refresh .icon {
            display: inline-block;
            font-style: normal;
        }

        .btn-refresh.spinning .icon {
            animation: spinOnce 0.6s ease forwards;
        }

        @keyframes spinOnce {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
        /* ========================== */

        .logo {
            margin-bottom: 35px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-title {
            font-size: 19px;
            font-weight: bold;
            letter-spacing: 1px;
            color: white;
            position: relative;
            padding-left: 5px;
        }

        .logo-title::before {
            content: "";
            position: absolute;
            left: 0;
            top: 5px;
            width: 4px;
            height: 18px;
            background: #e11d48;
            border-radius: 2px;
        }

        .logo-sub {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 6px;
            letter-spacing: .5px;
            position: left;
        }

        /* ================= MODAL ================= */

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 999;
            animation: fadeIn .2s ease;
        }

        .modal-box {
            background: #ffffff;
            width: 480px;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .25);
            overflow: hidden;
            animation: slideUp .25s ease;
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 18px;
            font-weight: 600;
        }

        .close-btn {
            font-size: 22px;
            cursor: pointer;
            color: #64748b;
        }

        .close-btn:hover {
            color: #111;
        }

        .modal-body {
            padding: 20px 24px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            margin-bottom: 6px;
            color: #475569;
            font-weight: 600;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            font-size: 14px;
            transition: all .2s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #3c8dbc;
            box-shadow: 0 0 0 3px rgba(60, 141, 188, .15);
            outline: none;
        }

        .form-row {
            display: flex;
            gap: 14px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-cancel {
            background: #e2e8f0;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-save {
            background: linear-gradient(135deg, #111, #333);
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: .2s;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, .2);
        }

        /* Animations */

        @keyframes fadeIn {
            from {
                opacity: 0
            }

            to {
                opacity: 1
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0
            }

            to {
                transform: translateY(0);
                opacity: 1
            }
        }

        .btn-delete {
            background: #dc2626;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
            transition: .2s ease;
        }

        .btn-delete:hover {
            background: #b91c1c;
            transform: scale(1.05);
        }

        .tag-container {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            border: 1px solid #e2e8f0;
            padding: 8px;
            border-radius: 10px;
            cursor: text;
            min-height: 44px;
        }

        .tag-container input {
            border: none;
            outline: none;
            flex: 1;
            min-width: 120px;
        }

        .tag {
            background: #3c8dbc;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .tag span {
            cursor: pointer;
            font-weight: bold;
        }

        .dropdown-list {
            position: relative;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-top: 5px;
            max-height: 160px;
            overflow-y: auto;
            display: none;
            z-index: 10;
        }

        .dropdown-item {
            padding: 8px 10px;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background: #f1f5f9;
        }

        .add-stock-input {
            width: 60px;
            padding: 6px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            text-align: center;
            font-weight: 600;
        }

        .plan-card {
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 20px;
        }

        .plan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .plan-title {
            font-size: 14px;
            font-weight: 600;
            color: #303846;
            font-weight: bold;
        }

        .plan-ratio {
            font-size: 14px;
            font-weight: 700;
            color: #334155;
        }

        .plan-body {
            display: flex;
            justify-content: center;
        }

        #planInput {
            width: 120px;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            transition: .2s ease;
        }

        #planInput:focus {
            border-color: #3c8dbc;
            box-shadow: 0 0 0 3px rgba(60, 141, 188, .15);
            outline: none;
        }

        .plan-bar {
            width: 100%;
            height: 10px;
            background: linear-gradient(145deg, #e2e8f0, #cbd5e1);
            border-radius: 999px;
            overflow: hidden;
            margin-top: 6px;
            position: relative;
            box-shadow: inset 2px 2px 4px rgba(0, 0, 0, 0.05),
                inset -2px -2px 4px rgba(255, 255, 255, 0.6);
        }

        #planProgress {
            height: 100%;
            width: 0%;
            border-radius: 999px;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            transition: width .4s ease, background .3s ease;
            position: relative;
            overflow: hidden;
        }

        /* Animated Shine Effect */
        #planProgress::after {
            content: "";
            position: absolute;
            top: 0;
            left: -50%;
            width: 50%;
            height: 100%;
            background: linear-gradient(120deg,
                    rgba(255, 255, 255, 0.3),
                    rgba(255, 255, 255, 0.1),
                    transparent);
            transform: skewX(-20deg);
            animation: shine 2s infinite;
        }

        @keyframes shine {
            0% {
                left: -50%;
            }

            100% {
                left: 120%;
            }
        }

        #planInput:focus {
            border-color: #3c8dbc;
            box-shadow: 0 0 0 3px rgba(60, 141, 188, .15);
            outline: none;
        }

        #planDisplay {
            font-size: 14px;
            font-weight: 700;
            color: #334155;
        }

        /* Animasi delete smooth */
        .fade-out-row {
            opacity: 0;
            transform: translateX(-20px);
            transition: all 0.3s ease;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 35px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-img {
            height: 30px;
            width: auto;
        }

        .toast-confirm {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .toast-box {
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            width: 300px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: scaleIn 0.2s ease;
        }

        .toast-actions {
            margin-top: 15px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .btn-yes {
            background: #dc2626;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-no {
            background: #e5e7eb;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-run-stop {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-run-stop.running {
            background: #ff4444;
            color: white;
        }

        .btn-run-stop.stopped {
            background: #4CAF50;
            color: white;
        }

        .btn-run-stop:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/yanmar.png" class="logo-img">
                <div>
                    <div class="logo-title">PT. YADIN</div>
                    <div class="logo-sub">Inventory Management System</div>
                </div>
            </div>
            <ul>
                <?php if ($role == "ms1"): ?>
                    <li class="active" onclick="setLokasi(1)">Admin MS1</li>
                <?php endif; ?>

                <?php if ($role == "ms2"): ?>
                    <li class="active" onclick="setLokasi(2)">Admin MS2</li>
                <?php endif; ?>

                <?php if ($role == "all"): ?>
                    <li id="btnMS1" class="active" onclick="setLokasi(1)">Admin MS1</li>
                    <li id="btnMS2" onclick="setLokasi(2)">Admin MS2</li>
                <?php endif; ?>

            </ul>
        </aside>
        <main class="main">

            <header class="topbar">
                <h2 id="pageTitle">SUPERMARKET MACHINE SHOP - MS<?php echo $lokasi; ?></h2>
                <div class="top-actions">

                    <select id="searchPart" onchange="loadItems()">
                        <option value="">All Parts</option>
                    </select>

                    <select id="sortStockSelect">
                        <option value="">All Status</option>
                        <option value="lower">🔴 Lower</option>
                        <option value="ok">🟢 OK</option>
                        <option value="over">🟠 Over</option>
                    </select>

                    <input type="text" id="searchInput" placeholder="Search model / part / barcode...">
                    <select id="modelSafetySelect">
                        <option value="">Model</option>
                    </select>

                    <input type="number" id="safetyStockInput" placeholder="Safety Stock">
                    <button class="btn-safety" onclick="submitSafetyStock()"> Submit </button>
                    <button class="btn-nav" onclick="openAddModal()">Add Item</button>
                    <button class="btn-nav" onclick="openHistory()">History</button>

                    <!-- ===== TOMBOL REFRESH MANUAL ===== -->
                    <button id="btnRefresh" class="btn-refresh" onclick="manualRefresh()">
                        <i class="icon">⟳</i> Refresh
                    </button>
                    <!-- =================================== -->

                    <?php if ($_SESSION['role'] == 'all'): ?>
                        <button class="btn-back" onclick="goBack()">Back</button>
                    <?php endif; ?>
                    <button class="btn-logout" onclick="logout()">Logout</button>
                </div>
            </header>

            <section class="summary">

                <div class="card">
                    <h3>Total Item(Item)</h3>
                    <p id="totalItem">0</p>
                </div>

                <div class="card">
                    <h3>Total Stock(Qty)</h3>
                    <p id="totalStock">0</p>
                </div>

                <div class="card">
                    <h3>Low Stock(Item)</h3>
                    <p id="lowStock">0</p>
                </div>

                <div class="card">
                    <h3>Total Safety Stock</h3>
                    <p id="totalSafetystock">0</p>
                </div>

                <div class="card plan-card">
                    <div class="plan-header">
                        <span class="plan-title">Plan Production</span>
                        <span id="planDisplay" class="plan-ratio">0 / 0</span>
                    </div>

                    <div class="plan-body">
                        <input type="number" id="planInput" placeholder="Type & Enter">
                    </div>

                    <div class="plan-bar">
                        <div id="planProgress"></div>
                    </div>
                </div>

                <div class="card report-card">
                    <h3>Daily Report</h3>
                    <input type="date" id="dailyDate">
                    <button onclick="exportDaily()">Export</button>
                </div>

                <div class="card report-card">
                    <h3>Monthly Report</h3>
                    <input type="month" id="monthlyDate">
                    <button onclick="exportMonthly()">Export</button>
                </div>

            </section>
            <section id="lowStockSection" style="display:none; margin-bottom:20px;">
                <div style="
            background:#fff1f1;
            border-left:6px solid #dc2626;
            padding:18px;
            border-radius:12px;
            box-shadow:0 4px 12px rgba(220,38,38,0.15);
        ">
                    <h3 style="margin-bottom:15px; color:#dc2626;">
                        ⚠ LOW STOCK ALERT
                    </h3>

                    <table id="lowStockTable" style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="background:#fee2e2;">
                                <th style="padding:8px;">Model</th>
                                <th style="padding:8px;">Part</th>
                                <th style="padding:8px;">Stock</th>
                                <th style="padding:8px;">Safety</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>
            <section class="table-container">
                <table id="stockTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Model</th>
                            <th>Part</th>
                            <th>Part Number</th>
                            <th>Barcode</th>
                            <th>Stock</th>
                            <th>Add</th>
                            <th>Safety</th>
                            <th>Status</th>
                            <th>Production Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </section>

        </main>
    </div>
    <!-- ================= ADD ITEM MODAL ================= -->
    <div id="addModal" class="modal-overlay">

        <div class="modal-box">

            <div class="modal-header">
                <h3>Add New Item</h3>
                <span onclick="closeAddModal()" class="close-btn">&times;</span>
            </div>

            <div class="modal-body">

                <div class="form-group">
                    <label>Model</label>

                    <div class="tag-container" onclick="focusModelInput()">
                        <div id="selectedModels" class="tags"></div>
                        <input type="text" id="modelSearchInput" placeholder="Ketik untuk cari model...">
                    </div>

                    <div id="modelDropdown" class="dropdown-list"></div>
                </div>

                <div class="form-group">
                    <label>Part Name</label>
                    <input type="text" id="newPartName">
                </div>

                <div class="form-group">
                    <label>Part Number</label>
                    <input type="text" id="newPartNumber">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Cureent Stock</label>
                        <input type="number" id="newStock">
                    </div>

                    <div class="form-group">
                        <label>Safety Stock</label>
                        <input type="number" id="newSafety">
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button class="btn-save" onclick="submitNewItem()">Save Item</button>
            </div>

        </div>

    </div>

    <div id="toastConfirm" class="toast-confirm">
        <div class="toast-box">
            <p id="toastMessage">Yakin ingin menghapus?</p>
            <div class="toast-actions">
                <button onclick="confirmDelete()" class="btn-yes">Yes</button>
                <button onclick="closeToast()" class="btn-no">Cancel</button>
            </div>
        </div>
    </div>
    <script>

        let currentSort = "";
        let currentLokasi = <?php echo $lokasi; ?>;
        let isSearching = false;
        let firstLoad = true;
        let lastDataMap = {};
        let currentPlan = 0;
        let rowMap = {};
        let lastHash = "";

        function manualRefresh() {
            const btn = document.getElementById("btnRefresh");
            lastHash = "";

            btn.classList.remove("spinning");
            void btn.offsetWidth;
            btn.classList.add("spinning");

            btn.disabled = true;

            loadItems();
            loadPlan();

            setTimeout(() => {
                btn.classList.remove("spinning");
                btn.disabled = false;
            }, 800);
        }

        function loadItems() {
            fetch("./api/get_items.php?lokasi_id=" + currentLokasi)
                .then(res => res.json())
                .then(response => {

                    if (!response.success) return;

                    let data = response.data;
                    populatePartDropdown(data);
                    // ===== HASH CHECK TAMBAHAN =====

                    let search = document.getElementById("searchPart")?.value.toLowerCase() || "";

                    if (search !== "") {
                        data = data.filter(item =>
                            item.part_name.toLowerCase().includes(search)
                        );
                    }

                    // 1. Buat hash SEBELUM filter status, tapi SERTAKAN currentSort
                    let newHash = JSON.stringify(data) + currentSort;

                    // 2. Bandingkan dulu
                    if (newHash === lastHash) {
                        return; // data + filter sama → skip
                    }

                    // 3. Baru simpan hash baru
                    lastHash = newHash;

                    // 4. Sort & filter status
                    data.sort((a, b) => a.item_id - b.item_id);

                    if (currentSort === "lower") {
                        data = data.filter(item => parseInt(item.current_stock) < parseInt(item.safety_stock));
                    }
                    if (currentSort === "ok") {
                        data = data.filter(item => parseInt(item.current_stock) === parseInt(item.safety_stock));
                    }
                    if (currentSort === "over") {
                        data = data.filter(item => parseInt(item.current_stock) > parseInt(item.safety_stock));
                    }

                    // 5. Baru render tabel
                    let tbody = document.querySelector("#stockTable tbody");
                    tbody.innerHTML = "";


                    let totalItem = 0;
                    let totalStock = 0;
                    let totalSafetyStock = 0;

                    let countedModels = new Set();
                    let countedParts = new Set(); // TAMBAHAN

                    let lowStock = 0;
                    data.forEach((item, index) => {

                        let stock = parseInt(item.current_stock) || 0;
                        let safety = parseInt(item.safety_stock) || 0;
                        let isLow = stock <= safety;

                        totalItem++;

                        if (!countedParts.has(item.part_number)) {
                            countedParts.add(item.part_number);
                            totalStock += stock;
                        }
                        if (!countedModels.has(item.model_name)) {
                            countedModels.add(item.model_name);
                            totalSafetyStock += safety;
                        }
                        if (isLow) lowStock++;

                        let rowClass = isLow ? "low-row" : "";

                        let row = document.createElement("tr");
                        row.className = rowClass;

                        row.innerHTML = `
                                        <td>${index + 1}</td>
                                        <td>${item.model_name}</td>
                                        <td>${item.part_name}</td>
                                        <td>${item.part_number}</td>
                                        <td><svg id="barcode-${item.item_id}" class="barcode-svg"></svg></td>

                                        <td>
                                            <input type="number" 
                                                class="stock-input"
                                                value="${stock}"
                                                data-current="${stock}"
                                                onchange="updateStock(${item.item_id}, this)">
                                        </td>

                                        <td>
                                            <input type="number"
                                                class="add-stock-input"
                                                placeholder="+"
                                                onkeydown="handleAddStock(event, ${item.item_id})">
                                        </td>

                                        <td>${safety}</td>

                                        <td id="status-${item.item_id}">
                                            ${safety === stock ? '<span class="ok">OK</span>' :
                                safety > stock ? '<span class="low">LOWER</span>' :
                                    '<span class="over">OVER</span>'}
                                        </td>

                                    <td>
  <button class="btn-run-stop ${stock > safety ? 'running' : 'stopped'}"
      data-item="${item.model_item_id}"
      data-stock="${stock}"
      data-safety="${safety}"
      onclick="toggleRunStop(this)">
      ${stock > safety ? 'STOP' : 'RUN'}
  </button>
</td>
                                    `;

                        tbody.appendChild(row);

                        JsBarcode(`#barcode-${item.item_id}`, item.part_number, {
                            format: "CODE128",
                            displayValue: false,
                            height: 40
                        });

                    });

                    document.getElementById("totalItem").innerText = totalItem;
                    document.getElementById("totalStock").innerText = totalStock;
                    document.getElementById("lowStock").innerText = lowStock;
                    document.getElementById("totalSafetystock").innerText = totalSafetyStock;
                    updatePlanDisplay();

                });
        }

        // ---- Ganti seluruh fungsi toggleRunStop ----

        function toggleRunStop(button) {
            const itemId = button.dataset.item;

            fetch('./api/toggle_production.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `item_id=${itemId}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Ambil nilai stock & safety dari data-attribute baris
                        const row = button.closest('tr');
                        const stock = parseInt(button.dataset.stock) || 0;
                        const safety = parseInt(button.dataset.safety) || 0;

                        // Logika: OVER → STOP | OK / LOWER → RUN
                        const isOver = stock > safety;

                        button.textContent = isOver ? 'STOP' : 'RUN';
                        button.classList.toggle('running', isOver);
                        button.classList.toggle('stopped', !isOver);
                        row.classList.toggle('running-row', isOver);

                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error('Toggle failed:', err);
                    alert('Connection error');
                });
        }

        
        function updateStock(itemId, input) {

            let newStockInt = parseInt(input.value);
            let oldStock = parseInt(input.dataset.current);

            fetch("./api/update_stock.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    item_id: itemId,
                    stock: newStockInt
                })
            })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {

                        let safety = parseInt(res.safety_stock);
                        let statusEl = document.getElementById("status-" + itemId);
                        let row = statusEl.closest("tr");

                        if (newStockInt <= safety) {
                            statusEl.innerHTML = '<span class="low">LOW</span>';
                            row.classList.add("low-row");
                        } else {
                            statusEl.innerHTML = '<span class="ok">OK</span>';
                            row.classList.remove("low-row");
                        }

                        input.classList.remove("stock-up", "stock-down");

                        if (newStockInt > oldStock) {
                            input.classList.add("stock-up");
                        } else if (newStockInt < oldStock) {
                            input.classList.add("stock-down");
                        }

                        input.dataset.current = newStockInt;

                    } else {
                        alert("Gagal update stock");
                    }
                });
        }

        document.getElementById("sortStockSelect")
            .addEventListener("change", function () {
                currentSort = this.value;
                loadItems();
            });

        document.getElementById("searchInput")
            .addEventListener("keyup", function () {

                let filter = this.value.toLowerCase();
                let rows = document.querySelectorAll("#stockTable tbody tr");

                isSearching = filter.length > 0;

                rows.forEach(row => {
                    row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
                });

            });

        function setLokasi(id) {

            currentLokasi = id;

            document.getElementById("pageTitle").innerText =
                "SUPERMARKET MACHINE SHOP - MS" + id;

            if (document.getElementById("btnMS1"))
                document.getElementById("btnMS1").classList.toggle("active", id === 1);

            if (document.getElementById("btnMS2"))
                document.getElementById("btnMS2").classList.toggle("active", id === 2);

            loadItems();
            loadPlan();
        }

        function exportDaily() {
            let date = document.getElementById("dailyDate").value;
            window.open("./api/export_daily.php?lokasi_id=" + currentLokasi + "&date=" + date, "_blank");
        }

        function exportMonthly() {
            let month = document.getElementById("monthlyDate").value;
            window.open("./api/export_month.php?lokasi_id=" + currentLokasi + "&month=" + month, "_blank");
        }

        document.addEventListener("DOMContentLoaded", function () {
            let today = new Date().toISOString().split("T")[0];
            document.getElementById("dailyDate").value = today;
            let now = new Date();
            let month = now.getFullYear() + "-" + String(now.getMonth() + 1).padStart(2, '0');
            document.getElementById("monthlyDate").value = month;
            loadItems();
            loadPlan();
        });
        document.getElementById("dailyDate").addEventListener("change", function () {
            loadPlan();
        });

        function submitSafetyStock() {

            const model = document.getElementById("modelSafetySelect").value;
            const safety = document.getElementById("safetyStockInput").value;

            if (model === "" || safety === "") {
                alert("Pilih model dan isi safety stock");
                return;
            }

            fetch("api/set_safety.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    model_id: model,
                    safety_stock: safety
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "OK") {
                        alert("Safety stock berhasil diupdate");
                        document.getElementById("safetyStockInput").value = "";
                    } else {
                        alert("Gagal update");
                        console.log(data);
                    }
                })
                .catch(() => {
                    alert("Server error");
                });
        }

        async function loadModels() {

            const res = await fetch("api/get_models.php");
            const data = await res.json();

            if (data.success) {

                const select = document.getElementById("modelSafetySelect");

                select.innerHTML = '<option value="">Model</option>';

                data.data.forEach(model => {
                    const opt = document.createElement("option");
                    opt.value = model.id;
                    opt.textContent = model.model_name;
                    select.appendChild(opt);
                });
            }
        }

        loadModels();

        let autoRefreshInterval = 10000;
        let isEditing = false;

        document.addEventListener("focusin", function (e) {
            if (e.target.matches(".stock-input, .add-stock-input")) {
                isEditing = true;
            }
        });

        document.addEventListener("focusout", function (e) {
            if (e.target.matches(".stock-input, .add-stock-input")) {
                isEditing = false;
            }
        });

        function smoothReload() {
            if (isEditing) return;
            loadItems();
        }

        function smoothReload() {

            if (isEditing) return;

            let tableContainer = document.querySelector(".table-container");

            tableContainer.classList.add("fade-refresh");

            setTimeout(() => {
                loadItems();
                tableContainer.classList.remove("fade-refresh");
                tableContainer.classList.add("fade-show");
            }, 300);

        }

        setInterval(() => {
            if (!isEditing && !isSearching) {
                loadItems();
                loadPlan();
            }
        }, 2000);

        function goBack() {
            window.location.href = "home_menu.php";
        }

        function logout() {
            if (confirm("Yakin ingin logout?")) {
                window.location.href = "signout.php";
            }
        }

        function openAddModal() {
            document.getElementById("addModal").style.display = "flex";
            loadModelsForAdd();
        }

        function closeAddModal() {
            document.getElementById("addModal").style.display = "none";
        }

        function submitNewItem() {

            const partName = document.getElementById("newPartName").value.trim();
            const partNumber = document.getElementById("newPartNumber").value.trim();
            const stock = document.getElementById("newStock").value;
            const safety = document.getElementById("newSafety").value;

            const modelIds = selectedModelIds;

            if (modelIds.length === 0 || partName === "" || partNumber === "" || stock === "") {
                alert("Lengkapi semua data termasuk Model");
                return;
            }

            fetch("api/add_item.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    model_ids: modelIds,
                    part_name: partName,
                    part_number: partNumber,
                    current_stock: stock,
                    safety_stock: safety,
                    location_id: currentLokasi
                })
            })
                .then(res => res.json())
                .then(data => {

                    if (data.success) {

                        alert("Item berhasil ditambahkan");

                        document.getElementById("newPartName").value = "";
                        document.getElementById("newPartNumber").value = "";
                        document.getElementById("newStock").value = "";
                        document.getElementById("newSafety").value = "";

                        selectedModelIds = [];
                        document.getElementById("selectedModels").innerHTML = "";

                        closeAddModal();
                        loadItems();

                    } else {
                        alert("Gagal tambah item");
                        console.log(data);
                    }
                });
        }
        async function loadModelsForAdd() {

            const res = await fetch("api/get_models.php");
            const data = await res.json();

            if (data.success) {

                const select = document.getElementById("newModelSelect");
                select.innerHTML = '<option value="">Pilih Model</option>';

                data.data.forEach(model => {
                    const opt = document.createElement("option");
                    opt.value = model.id;
                    opt.textContent = model.model_name;
                    select.appendChild(opt);
                });
            }
        }

        let deleteData = {};

        function deleteItem(btn) {

            deleteData = {
                modelId: btn.dataset.model,
                itemId: btn.dataset.item,
                partName: btn.dataset.part,
                element: btn
            };

            document.getElementById("toastMessage").innerText =
                "Hapus part: " + deleteData.partName + " ?";

            document.getElementById("toastConfirm").style.display = "flex";
        }


        function closeToast() {
            document.getElementById("toastConfirm").style.display = "none";
        }

        function confirmDelete() {

            const {
                modelId,
                itemId,
                element
            } = deleteData;

            fetch("api/delete_item.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    model_id: modelId,
                    item_id: itemId
                })
            })
                .then(res => res.json())
                .then(data => {

                    if (data.success) {

                        const row = element.closest("tr");
                        row.classList.add("fade-out-row");

                        setTimeout(() => {
                            row.remove();
                            loadItems();
                        }, 300);

                    } else {
                        alert("Gagal menghapus");
                    }

                    closeToast();
                });
        }

        function populatePartDropdown(data) {

            let select = document.getElementById("searchPart");

            if (select.dataset.loaded) return; // supaya tidak reload terus

            let parts = new Set();

            data.forEach(item => {
                parts.add(item.part_name);
            });

            parts.forEach(part => {

                let option = document.createElement("option");
                option.value = part;
                option.textContent = part;

                select.appendChild(option);
            });

            select.dataset.loaded = true;
        }

        function goBack() {
            window.history.back();
        }

        let allModels = [];
        let selectedModelIds = [];

        async function loadModelsForAdd() {

            const res = await fetch("api/get_models.php");
            const data = await res.json();

            if (data.success) {
                allModels = data.data;
            }
        }

        function focusModelInput() {
            document.getElementById("modelSearchInput").focus();
        }

        document.getElementById("modelSearchInput")
            .addEventListener("input", function () {

                const keyword = this.value.toLowerCase();
                const dropdown = document.getElementById("modelDropdown");
                dropdown.innerHTML = "";

                if (keyword === "") {
                    dropdown.style.display = "none";
                    return;
                }

                const filtered = allModels.filter(model =>
                    model.model_name.toLowerCase().includes(keyword) &&
                    !selectedModelIds.includes(model.id.toString())
                );

                filtered.forEach(model => {

                    const div = document.createElement("div");
                    div.className = "dropdown-item";
                    div.textContent = model.model_name;

                    div.onclick = () => addModelTag(model);

                    dropdown.appendChild(div);
                });

                dropdown.style.display = filtered.length ? "block" : "none";
            });

        function addModelTag(model) {

            if (selectedModelIds.includes(model.id.toString())) {
                return;
            }

            selectedModelIds.push(model.id.toString());

            const tag = document.createElement("div");
            tag.className = "tag";
            tag.innerHTML = `
            ${model.model_name}
            <span onclick="removeModelTag('${model.id}', this)">×</span>
        `;

            document.getElementById("selectedModels").appendChild(tag);

            document.getElementById("modelSearchInput").value = "";
            document.getElementById("modelDropdown").style.display = "none";
        }

        function removeModelTag(id, el) {

            selectedModelIds = selectedModelIds.filter(mid => mid !== id);

            el.parentElement.remove();
        }

        function handleAddStock(event, itemId) {

            if (event.key !== "Enter") return;

            const input = event.target;
            const addValue = parseInt(input.value);

            if (isNaN(addValue) || addValue === 0) {
                input.value = "";
                return;
            }

            // Ambil stock sekarang dari input stock di row yang sama
            const row = input.closest("tr");
            const stockInput = row.querySelector(".stock-input");

            let currentStock = parseInt(stockInput.value) || 0;
            let newStock = currentStock + addValue;

            fetch("./api/update_stock.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    item_id: itemId,
                    stock: newStock
                })
            })
                .then(res => res.json())
                .then(res => {

                    if (res.success) {

                        // Update langsung di UI TANPA reload
                        stockInput.value = newStock;
                        stockInput.dataset.current = newStock;

                        let safety = parseInt(res.safety_stock);
                        let statusEl = document.getElementById("status-" + itemId);
                        let row = statusEl.closest("tr");

                        if (newStock <= safety) {
                            statusEl.innerHTML = '<span class="low">LOW</span>';
                            row.classList.add("low-row");
                        } else {
                            statusEl.innerHTML = '<span class="ok">OK</span>';
                            row.classList.remove("low-row");
                        }

                        // Animasi naik
                        stockInput.classList.remove("stock-up");
                        void stockInput.offsetWidth;
                        stockInput.classList.add("stock-up");

                    } else {
                        alert("Gagal tambah stock");
                    }

                    input.value = "";
                });
        }

        function updatePlanDisplay() {

            let totalSafetyStock = parseInt(
                document.getElementById("totalSafetystock").innerText
            ) || 0;

            let el = document.getElementById("planDisplay");
            let progress = document.getElementById("planProgress");

            el.innerText = totalSafetyStock + " / " + currentPlan;

            let percent = totalSafetyStock > 0 ?
                (currentPlan / totalSafetyStock) * 100 :
                0;

            percent = Math.min(percent, 100);

            progress.style.width = percent + "%";

            if (currentPlan > totalSafetyStock) {
                el.style.color = "#dc2626";
                progress.style.background = "linear-gradient(90deg,#dc2626,#ef4444)";
            } else {
                el.style.color = "#16a34a";
                progress.style.background = "linear-gradient(90deg,#16a34a,#22c55e)";
            }
        }

        function loadPlan() {

            let today = document.getElementById("dailyDate").value;

            fetch("api/get_plan.php?lokasi_id=" + currentLokasi + "&date=" + today)
                .then(res => res.json())
                .then(res => {

                    if (res.success) {
                        currentPlan = parseInt(res.plan) || 0;
                        updatePlanDisplay();
                    }

                });
        }

        planInput.addEventListener("keyup", function (e) {

            if (e.key === "Enter") {

                let value = parseInt(this.value);

                if (!isNaN(value) && value > 0) {

                    currentPlan = value;

                    fetch("api/set_plan.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            lokasi_id: currentLokasi,
                            plan_qty: currentPlan,
                            plan_date: document.getElementById("dailyDate").value
                        })
                    });

                    updatePlanDisplay();
                }

                this.value = "";
            }
        });

        function openHistory() {
            window.location.href = "history.php?lokasi=" + currentLokasi;
        }
    </script>
</body>

</html>
