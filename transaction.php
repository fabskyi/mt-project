<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

if ($_SESSION['role'] != 'operator' && $_SESSION['role'] != 'all' && $_SESSION['role'] != 'machining' && $_SESSION['role'] != 'ms1' && $_SESSION['role'] != 'ms2') {
    die("Akses ditolak");
}

$isAdmin = ($_SESSION['role'] === 'all');
$machiningRoles = ['machining', 'ms2', 'ms1'];
$isMachining = in_array($_SESSION['role'], $machiningRoles);
$isOperator = ($_SESSION['role'] === 'operator');

if ($isAdmin) {
    $isMachining = true;
    $isOperator = true;
}

require "api/config.php";
$nik = $_SESSION['nik'];

$stmt = $conn->prepare("SELECT nama FROM karyawan WHERE nik = ?");
$stmt->bind_param("s", $nik);
$stmt->execute();
$result = $stmt->get_result();
$dataUser = $result->fetch_assoc();
$nama = $dataUser['nama'] ?? 'Unknown';
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="icon" type="image/png" href="assets/yanmar.png">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="assets/yanmar.png">
    <meta name="theme-color" content="#ffffff">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PT. Yadin Supermarket</title>
    <script src="js/html5-qrcode.min.js"></script>

    <style>
        /* ════════════════════════════════════
           RESET & BASE
        ════════════════════════════════════ */
        :root {
            --bg: #f0f2f5;
            --card: #ffffff;
            --border: #e5e7eb;
            --text: #111827;
            --muted: #6b7280;
            --radius: 14px;
            --radius-sm: 8px;
            --pop-overlay: rgba(10, 10, 20, 0.65);
            --pop-shadow: 0 32px 80px rgba(0, 0, 0, 0.22), 0 0 0 1px rgba(0, 0, 0, 0.06);
            --pop-radius: 22px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ════ HEADER ════ */
        .header {
            padding: 16px 24px;
            background: #fff;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 8px rgba(0, 0, 0, 0.06);
        }

        .user-info strong {
            font-size: 15px;
            font-weight: 700;
        }

        .user-info span {
            font-size: 12px;
            color: var(--muted);
        }

        .btn-header {
            padding: 9px 18px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-back {
            background: #2563eb;
            color: #fff;
        }

        .btn-back:hover {
            background: #1d4ed8;
        }

        .btn-logout {
            background: #111827;
            color: #fff;
        }

        .btn-logout:hover {
            background: #374151;
        }

        /* ════ CONTAINER ════ */
        .container {
            padding: 24px 18px;
            max-width: 640px;
            margin: auto;
        }

        .card {
            background: var(--card);
            padding: 20px;
            border-radius: var(--radius);
            margin-bottom: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .card h3 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        /* ════ MODE ════ */
        .mode-container {
            display: flex;
            gap: 10px;
        }

        .mode-btn {
            flex: 1;
            padding: 14px 8px;
            font-weight: 700;
            border-radius: var(--radius-sm);
            border: 2px solid #d1d5db;
            background: white;
            cursor: pointer;
            font-size: 14px;
            letter-spacing: .5px;
            transition: .18s;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .mode-btn:hover {
            border-color: #9ca3af;
            background: #f9fafb;
        }

        .mode-btn.active {
            background: #111827;
            color: white;
            border-color: #111827;
        }

        /* ════ INPUTS ════ */
        .input-field {
            width: 100%;
            padding: 14px 16px;
            font-size: max(16px, 1em);
            font-family: inherit;
            border-radius: var(--radius-sm);
            border: 2px solid #d1d5db;
            margin-bottom: 12px;
            outline: none;
            background: #fff;
            color: var(--text);
            transition: border-color .18s, background .18s;
        }

        .input-field:focus {
            border-color: #111827;
            background: #111827;
            color: white;
        }

        .input-field::placeholder {
            color: #9ca3af;
        }

        /* ════ BUTTONS ════ */
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: #111827;
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
            transition: .18s;
        }

        .btn-primary:hover {
            background: #1f2937;
        }

        .btn-primary:active {
            background: #374151;
        }

        .btn-primary.green {
            background: #059669;
        }

        .btn-primary.green:hover {
            background: #047857;
        }

        /* ════ QTY PRESETS ════ */
        .qty-presets {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .qty-preset-btn {
            flex: 1;
            padding: 11px 6px;
            font-weight: 700;
            border-radius: var(--radius-sm);
            border: 2px solid #d1d5db;
            background: white;
            cursor: pointer;
            font-size: 14px;
            min-width: 44px;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
            transition: .18s;
        }

        .qty-preset-btn:hover {
            background: #111827;
            color: white;
            border-color: #111827;
        }

        .qty-preset-btn:active {
            background: #374151;
            color: white;
        }

        /* ════ PART INFO ════ */
        .part-info-card {
            display: none;
            background: #fff;
            border-radius: var(--radius);
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .part-info-card.show {
            display: block;
            animation: slideDown .22s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pi-header {
            background: #111827;
            color: white;
            padding: 10px 18px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pi-body {
            padding: 16px 18px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .pi-item {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .pi-item.full {
            grid-column: 1 / -1;
        }

        .pi-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #9ca3af;
        }

        .pi-val {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            word-break: break-word;
        }

        .pi-val.lg {
            font-size: 22px;
        }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 8px;
        }

        .stock-badge.ok {
            background: #d1fae5;
            color: #065f46;
        }

        .stock-badge.low {
            background: #fef3c7;
            color: #92400e;
        }

        .stock-badge.out {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ════ HISTORY / DOWNLOAD ════ */
        .history-box {
            max-height: 220px;
            overflow-y: auto;
            font-size: 13px;
        }

        .history-item {
            padding: 9px 0;
            border-bottom: 1px solid var(--border);
            color: var(--muted);
        }

        .dl-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .dl-box {
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .dl-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ══════════════════════════════════════
           ████  UNIFIED POPUP SYSTEM  ████
           Semua popup pakai class yang sama
        ══════════════════════════════════════ */

        /* Overlay */
        .popup-overlay {
            position: fixed;
            inset: 0;
            background: var(--pop-overlay);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: flex;
            justify-content: center;
            align-items: flex-end;
            /* default: slide-up sheet */
            z-index: 9000;
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s ease;
        }

        .popup-overlay.is-center {
            align-items: center;
        }

        /* center modal */
        .popup-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        /* Box */
        .popup-box {
            background: #fff;
            width: 100%;
            max-width: 500px;
            box-shadow: var(--pop-shadow);
            display: flex;
            flex-direction: column;
            /* Sheet: slide from bottom */
            border-radius: var(--pop-radius) var(--pop-radius) 0 0;
            transform: translateY(48px);
            transition: transform .3s cubic-bezier(0.34, 1.2, 0.64, 1);
            max-height: 90vh;
            overflow: hidden;
        }

        /* Center modal overrides */
        .popup-overlay.is-center .popup-box {
            border-radius: var(--pop-radius);
            max-width: 400px;
            transform: scale(0.92) translateY(12px);
            transition: transform .3s cubic-bezier(0.34, 1.2, 0.64, 1);
        }

        .popup-overlay.active .popup-box {
            transform: translateY(0);
        }

        .popup-overlay.is-center.active .popup-box {
            transform: scale(1) translateY(0);
        }

        /* ── Drag handle (sheet only) ── */
        .popup-handle {
            width: 40px;
            height: 4px;
            border-radius: 2px;
            background: #d1d5db;
            margin: 12px auto 0;
            flex-shrink: 0;
        }

        /* ── Popup Header ── */
        .popup-hdr {
            padding: 18px 20px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f1f3f5;
            flex-shrink: 0;
        }

        .popup-hdr-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .popup-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .popup-icon.black {
            background: #111827;
        }

        .popup-icon.amber {
            background: #fef3c7;
        }

        .popup-icon.green {
            background: #d1fae5;
        }

        .popup-title {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
        }

        .popup-subtitle {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        .popup-close-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: #f3f4f6;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            color: #6b7280;
            flex-shrink: 0;
            touch-action: manipulation;
            transition: .15s;
        }

        .popup-close-btn:hover {
            background: #e5e7eb;
            color: #111827;
        }

        /* ── Popup Body ── */
        .popup-body {
            padding: 18px 20px;
            overflow-y: auto;
            flex: 1;
        }

        /* ── Popup Footer ── */
        .popup-ftr {
            padding: 12px 20px 20px;
            display: flex;
            gap: 10px;
            flex-shrink: 0;
            border-top: 1px solid #f1f3f5;
        }

        .pop-btn {
            flex: 1;
            padding: 14px 10px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: .18s;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .pop-btn.ghost {
            background: #f3f4f6;
            color: #374151;
            border: 1.5px solid #e5e7eb;
        }

        .pop-btn.ghost:hover {
            background: #e5e7eb;
        }

        .pop-btn.black {
            background: #111827;
            color: white;
        }

        .pop-btn.black:hover {
            background: #1f2937;
        }

        .pop-btn.black:active {
            background: #374151;
        }

        /* ══════════════════════════════════════
           CAMERA POPUP SPECIFICS
        ══════════════════════════════════════ */
        .cam-live-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .cam-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #00d97e;
            box-shadow: 0 0 0 3px rgba(0, 217, 126, .2);
            animation: dotPulse 1.6s ease-in-out infinite;
        }

        @keyframes dotPulse {

            0%,
            100% {
                box-shadow: 0 0 0 3px rgba(0, 217, 126, .2);
            }

            50% {
                box-shadow: 0 0 0 6px rgba(0, 217, 126, .05);
            }
        }

        .cam-live-txt {
            font-size: 11px;
            font-weight: 700;
            color: #00d97e;
            letter-spacing: 1px;
        }

        /* Video container */
        .cam-viewport {
            position: relative;
            background: #000;
            overflow: hidden;
            min-height: 300px;
        }

        /* Camera modal override - bigger size for better scanning */
        #cameraModal.popup-overlay.is-center .popup-box {
            max-width: 600px;
        }

        /* Hide handle for center camera modal */
        #cameraModal .popup-handle {
            display: none;
        }

        #reader {
            width: 100% !important;
            border: none !important;
            background: #000 !important;
            padding: 0 !important;
        }

        #reader video {
            width: 100% !important;
            max-height: 400px !important;
            object-fit: cover !important;
            display: block !important;
        }

        #reader img,
        #reader__dashboard,
        #reader__dashboard_section_swaplink,
        #reader__camera_permission_button {
            display: none !important;
        }

        #reader__scan_region {
            border: none !important;
            background: transparent !important;
        }

        #reader__scan_region img {
            display: none !important;
        }

        /* Scan frame */
        .scan-frame {
            position: absolute;
            inset: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .scan-target {
            width: 85%;
            max-width: 320px;
            height: 100px;
            position: relative;
        }

        .scan-target::before,
        .scan-target::after,
        .scan-target span::before,
        .scan-target span::after {
            content: '';
            position: absolute;
            width: 28px;
            height: 28px;
            border-color: #00d97e;
            border-style: solid;
        }

        .scan-target::before {
            top: 0;
            left: 0;
            border-width: 4px 0 0 4px;
            border-radius: 6px 0 0 0;
        }

        .scan-target::after {
            top: 0;
            right: 0;
            border-width: 4px 4px 0 0;
            border-radius: 0 6px 0 0;
        }

        .scan-target span::before {
            bottom: 0;
            left: 0;
            border-width: 0 0 4px 4px;
            border-radius: 0 0 0 6px;
        }

        .scan-target span::after {
            bottom: 0;
            right: 0;
            border-width: 0 4px 4px 0;
            border-radius: 0 0 6px 0;
        }

        .scan-line {
            position: absolute;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(0, 217, 126, .3) 15%, #00d97e 50%, rgba(0, 217, 126, .3) 85%, transparent);
            animation: scanAnim 2s ease-in-out infinite;
        }

        /* Garis tengah statis sebagai guide */
        .scan-guide {
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 3px;
            background: #00d97e;
            transform: translateY(-50%);
            box-shadow: 0 0 8px rgba(0, 217, 126, 0.6),
                0 0 16px rgba(0, 217, 126, 0.3);
            z-index: 1;
        }

        /* Optional: Tambahkan efek glow pulse pada guide */
        .scan-guide::before {
            content: '';
            position: absolute;
            inset: -2px;
            background: rgba(0, 217, 126, 0.3);
            filter: blur(4px);
            animation: guidePulse 2s ease-in-out infinite;
        }

        @keyframes guidePulse {

            0%,
            100% {
                opacity: 0.5;
            }

            50% {
                opacity: 1;
            }
        }

        @keyframes scanAnim {
            0% {
                top: 4px;
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                top: calc(100% - 4px);
                opacity: 0;
            }
        }

        /* Status bar inside camera */
        .cam-statusbar {
            padding: 11px 20px;
            background: #0a0a0a;
            border-top: 1px solid #1c1c1c;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555;
            letter-spacing: .3px;
            transition: color .25s;
            flex-shrink: 0;
        }

        .cam-statusbar.ok {
            color: #00d97e;
        }

        .cam-statusbar.error {
            color: #f87171;
        }

        .cam-statusbar.warning {
            color: #fbbf24;
        }

        /* Camera footer override — dark bg */
        #cameraModal .popup-ftr {
            background: #111;
            border-top: 1px solid #1c1c1c;
            padding: 14px 20px 20px;
        }

        #cameraModal .pop-btn.ghost {
            background: #1c1c1c;
            color: #888;
            border-color: #2a2a2a;
        }

        #cameraModal .pop-btn.ghost:hover {
            background: #2a2a2a;
            color: #ccc;
        }

        /* ══════════════════════════════════════
           CONFIRM POPUP SPECIFICS
        ══════════════════════════════════════ */
        .confirm-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }

        .confirm-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 11px 16px;
            font-size: 13px;
        }

        .confirm-row:not(:last-child) {
            border-bottom: 1px solid #f1f3f5;
        }

        .confirm-lbl {
            color: #6b7280;
            font-weight: 500;
        }

        .confirm-val {
            font-weight: 700;
            color: #111827;
            text-align: right;
        }

        .confirm-val.big {
            font-size: 15px;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .chip.in {
            background: #d1fae5;
            color: #065f46;
        }

        .chip.out {
            background: #fee2e2;
            color: #991b1b;
        }

        .chip.return {
            background: #ede9fe;
            color: #5b21b6;
        }
    </style>
</head>

<body>

    <!-- ════ HEADER ════ -->
    <div class="header">
        <div class="user-info">
            <strong><?= strtoupper(htmlspecialchars($nama)) ?></strong><br>
            <span>NIK: <?= htmlspecialchars($_SESSION['nik']) ?> &nbsp;·&nbsp;
                <?= strtoupper($_SESSION['role']) ?></span>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <?php if ($isAdmin): ?>
                <a href="home_menu.php" class="btn-header btn-back">← Back</a>
            <?php endif; ?>
            <a href="signout.php" class="btn-header btn-logout">Logout</a>
        </div>
    </div>

    <!-- ════ CONTENT ════ -->
    <div class="container">

        <!-- Mode -->
        <div class="card">
            <div class="mode-container">
                <?php if ($isMachining): ?>
                    <button class="mode-btn" onclick="setMode('in', this)">IN</button>
                <?php endif; ?>
                <?php if ($isOperator): ?>
                    <button class="mode-btn" onclick="setMode('out', this)">OUT</button>
                    <button class="mode-btn" onclick="setMode('return', this)">RETURN</button>
                <?php endif; ?>
            </div>
        </div>

        <button class="btn-primary" onclick="openCamera()">📷 &nbsp;Scan Barcode</button>

        <input class="input-field" type="text" id="scanInput" placeholder="Scan / Ketik Manual" autocomplete="off"
            autocorrect="off" autocapitalize="off">

        <!-- Part Info -->
        <div class="part-info-card" id="partInfoCard">
            <div class="pi-header">📦 &nbsp;Informasi Part</div>
            <div class="pi-body">
                <div class="pi-item full">
                    <span class="pi-label">Part Name</span>
                    <span class="pi-val" id="infoPartName">—</span>
                </div>
                <div class="pi-item">
                    <span class="pi-label">Part Number</span>
                    <span class="pi-val" id="infoPartNumber">—</span>
                </div>
                <div class="pi-item">
                    <span class="pi-label">Model</span>
                    <span class="pi-val" id="infoModel">—</span>
                </div>
                <div class="pi-item full">
                    <span class="pi-label">Stock Saat Ini</span>
                    <span class="pi-val lg" id="infoStock">—</span>
                </div>
            </div>
        </div>

        <input class="input-field" type="number" id="qtyInput" placeholder="Input Qty" inputmode="numeric"
            pattern="[0-9]*" style="display:none;">

        <div class="qty-presets" id="qtyPresets" style="display:none;">
            <button class="qty-preset-btn" onclick="setQty(1)">1</button>
            <button class="qty-preset-btn" onclick="setQty(5)">5</button>
            <button class="qty-preset-btn" onclick="setQty(10)">10</button>
            <button class="qty-preset-btn" onclick="setQty(15)">15</button>
            <button class="qty-preset-btn" onclick="setQty(20)">20</button>
            <button class="qty-preset-btn" onclick="setQty(25)">25</button>
        </div>

        <button class="btn-primary green" id="submitTxBtn" style="display:none;" onclick="submitTransaction()">
            ✅ &nbsp;Submit Transaksi
        </button>

        <!-- History -->
        <div class="card">
            <h3>📋 Last Transactions</h3>
            <div id="historyBox" class="history-box"></div>
        </div>

        <!-- Download -->
        <?php if ($isMachining || $isAdmin): ?>
            <div class="card">
                <h3 style="margin-bottom:16px;">📥 Download History</h3>
                <div class="dl-grid">
                    <div class="dl-box">
                        <div class="dl-label">Harian</div>
                        <input class="input-field" type="date" id="dlDate" style="margin-bottom:0; padding:11px;">
                        <button class="btn-primary" style="margin-bottom:0;" onclick="downloadHistory()">📥 CSV</button>
                    </div>
                    <div class="dl-box">
                        <div class="dl-label">Bulanan</div>
                        <input class="input-field" type="month" id="dlMonth" style="margin-bottom:0; padding:11px;">
                        <button class="btn-primary" style="margin-bottom:0;" onclick="downloadHistoryMonthly()">📥
                            CSV</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- ══════════════════════════════════════════
     ████  CAMERA POPUP  ████
     Slide-up sheet dari bawah
══════════════════════════════════════════ -->
    <div id="cameraModal" class="popup-overlay is-center" onclick="overlayClick(event,'cameraModal')">
        <div class="popup-box">

            <div class="popup-handle"></div>

            <!-- Header -->
            <div class="popup-hdr">
                <div class="popup-hdr-left">
                    <div class="popup-icon black">📷</div>
                    <div>
                        <div class="popup-title">Barcode Scanner</div>
                        <div class="popup-subtitle">Arahkan kamera ke barcode</div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:14px;">
                    <div class="cam-live-row">
                        <div class="cam-dot"></div>
                        <span class="cam-live-txt">LIVE</span>
                    </div>
                    <button class="popup-close-btn" onclick="closeCamera()">✕</button>
                </div>
            </div>

            <!-- Video -->
            <div class="cam-viewport">
                <div id="reader"></div>
                <div class="scan-frame">
                    <div class="scan-target">
                        <span></span>
                        <div class="scan-line"></div>
                        <div class="scan-guide"></div> <!-- ← TAMBAHKAN INI -->
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="cam-statusbar" id="camStatus">Menginisialisasi kamera...</div>

            <!-- Footer -->
            <div class="popup-ftr">
                <button class="pop-btn ghost" onclick="closeCamera()">✕ &nbsp;Tutup Kamera</button>
            </div>

        </div>
    </div>

    <!-- ══════════════════════════════════════════
     ████  CONFIRM POPUP  ████
     Center dialog
══════════════════════════════════════════ -->
    <div id="confirmModal" class="popup-overlay is-center" onclick="overlayClick(event,'confirmModal')">
        <div class="popup-box">

            <!-- Header -->
            <div class="popup-hdr">
                <div class="popup-hdr-left">
                    <div class="popup-icon amber">⚠️</div>
                    <div>
                        <div class="popup-title">Konfirmasi Transaksi</div>
                        <div class="popup-subtitle">Pastikan data sudah benar</div>
                    </div>
                </div>
                <button class="popup-close-btn" onclick="closeConfirm()">✕</button>
            </div>

            <!-- Detail -->
            <div class="popup-body">
                <div class="confirm-card" id="confirmDetail"></div>
            </div>

            <!-- Actions -->
            <div class="popup-ftr">
                <button class="pop-btn ghost" onclick="closeConfirm()">Batal</button>
                <button class="pop-btn black" onclick="doTransaction()">✓ &nbsp;Ya, Lanjutkan</button>
            </div>

        </div>
    </div>

    <script>
        /* ── STATE ── */
        let mode = "";
        let selectedItem = null;
        let pendingQty = 0;
        let html5QrCode = null;
        let scannerReady = false;
        let scanLocked = false;
        let isTransitioning = false; // ← TAMBAH INI
        const SCAN_COOL = 1500;

        /* ── AUDIO / VIBRATE ── */
        let audioCtx = null;
        function beep(freq = 1800, dur = 80, vol = 0.3) {
            try {
                if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator(), gain = audioCtx.createGain();
                osc.connect(gain); gain.connect(audioCtx.destination);
                osc.frequency.value = freq; osc.type = "square";
                gain.gain.setValueAtTime(vol, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + dur / 1000);
                osc.start(); osc.stop(audioCtx.currentTime + dur / 1000);
            } catch (e) { }
        }
        function vibrate(p = 60) { try { if (navigator.vibrate) navigator.vibrate(p); } catch (e) { } }

        /* ── DOM REFS ── */
        const scanInput = document.getElementById("scanInput");
        const qtyInput = document.getElementById("qtyInput");
        const submitBtn = document.getElementById("submitTxBtn");
        const partInfoCard = document.getElementById("partInfoCard");

        /* ── POPUP SYSTEM ── */
        function openPopup(id) { document.getElementById(id).classList.add("active"); }
        function closePopup(id) { document.getElementById(id).classList.remove("active"); }
        function overlayClick(e, id) {
            if (e.target.classList.contains("popup-overlay"))
                id === "cameraModal" ? closeCamera() : closePopup(id);
        }

        /* ── CAM STATUS ── */
        const camStatusEl = document.getElementById("camStatus");
        function setCamStatus(type, msg) {
            camStatusEl.className = "cam-statusbar " + type;
            camStatusEl.textContent = msg;
        }

        /* ── INIT ── */
        window.addEventListener("DOMContentLoaded", () => {
            html5QrCode = new Html5Qrcode("reader");
            const today = new Date().toISOString().split("T")[0];
            if (document.getElementById("dlDate")) document.getElementById("dlDate").value = today;
            const now = new Date();
            if (document.getElementById("dlMonth"))
                document.getElementById("dlMonth").value =
                    now.getFullYear() + "-" + String(now.getMonth() + 1).padStart(2, "0");
            loadHistory();
        });

        /* ── PART INFO ── */
        function showPartInfo(res) {
            document.getElementById("infoPartName").textContent = res.nama ?? res.name ?? '—';
            document.getElementById("infoPartNumber").textContent = res.part ?? '—';
            document.getElementById("infoModel").textContent = res.models ?? '—';
            const stock = parseInt(res.stock) || 0;
            const el = document.getElementById("infoStock");
            let badge = stock <= 0
                ? '<span class="stock-badge out">HABIS</span>'
                : stock < 10
                    ? '<span class="stock-badge low">RENDAH</span>'
                    : '<span class="stock-badge ok">TERSEDIA</span>';
            el.innerHTML = stock + badge;
            partInfoCard.classList.add("show");
        }
        function hidePartInfo() { partInfoCard.classList.remove("show"); }

        /* ── MODE ── */
        function setMode(m, btn) {
            mode = m;
            document.querySelectorAll(".mode-btn").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
        }

        /* ── CAMERA ── */
        function openCamera() {
            if (!mode) { alert("Pilih mode dulu (IN / OUT / RETURN)"); return; }

            // Cegah multiple calls
            if (scannerReady || isTransitioning) {
                console.log("Camera already active or transitioning");
                return;
            }

            isTransitioning = true; // ← LOCK
            openPopup("cameraModal");
            setCamStatus("", "Memulai kamera...");
            scanLocked = false;

            const config = { fps: 25, qrbox: { width: 320, height: 100 } };

            startCam({ facingMode: "environment" }, config)
                .catch(() => {
                    setCamStatus("warning", "⚠ Mencoba kamera lain...");
                    return new Promise(resolve => setTimeout(resolve, 300)) // ← DELAY
                        .then(() => startCam(true, config));
                })
                .catch(() => {
                    return new Promise(resolve => setTimeout(resolve, 300)) // ← DELAY
                        .then(() => startCam(undefined, config));
                })
                .catch(err => {
                    setCamStatus("error", "❌ " + (err.message ?? err));
                    isTransitioning = false; // ← UNLOCK on error
                    setTimeout(() => closePopup("cameraModal"), 2000);
                });
        }

        function startCam(cam, config) {
            return html5QrCode.start(cam ?? { facingMode: "environment" }, config, onScanSuccess)
                .then(() => {
                    scannerReady = true;
                    isTransitioning = false; // ← UNLOCK setelah berhasil
                    setCamStatus("", "📷 Arahkan ke barcode...");
                })
                .catch(err => {
                    isTransitioning = false; // ← UNLOCK on error
                    throw err; // Re-throw untuk catch chain
                });
        }

            
        function onScanSuccess(txt) {
            if (scanLocked || isTransitioning) return; // ← Tambah check isTransitioning

            scanLocked = true;
            beep(1800, 80); vibrate(60);
            setCamStatus("ok", "✅ " + txt);
            scanInput.value = txt;

            // Tambah delay sebelum stop untuk memastikan scan selesai
            setTimeout(() => {
                stopCamera(() => processBarcode(txt));
            }, 100);

            setTimeout(() => { scanLocked = false; }, SCAN_COOL);
        }


        function stopCamera(cb) {
            if (!scannerReady && !isTransitioning) {
                if (cb) cb();
                return;
            }

            isTransitioning = true; // ← LOCK

            if (!scannerReady) {
                // Jika scanner belum ready tapi transitioning, tunggu sebentar
                setTimeout(() => {
                    isTransitioning = false;
                    closePopup("cameraModal");
                    if (cb) cb();
                }, 500);
                return;
            }

            html5QrCode.stop()
                .then(() => {
                    scannerReady = false;
                    html5QrCode.clear();
                    isTransitioning = false; // ← UNLOCK
                    closePopup("cameraModal");
                    if (cb) cb();
                })
                .catch(() => {
                    scannerReady = false;
                    isTransitioning = false; // ← UNLOCK
                    closePopup("cameraModal");
                    if (cb) cb();
                });
        }

        function closeCamera() { stopCamera(); }

        /* ── PROCESS BARCODE ── */
        function processBarcode(val) {
            if (!val?.trim()) return;
            hidePartInfo();
            fetch("api/get_item_info.php", {
                method: "POST", headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ part: val.trim() })
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) { alert("❌ " + res.error); return; }
                    selectedItem = res; showPartInfo(res);
                    qtyInput.style.display = "block";
                    submitBtn.style.display = "block";
                    document.getElementById("qtyPresets").style.display = "flex";
                    qtyInput.value = "";
                    setTimeout(() => qtyInput.focus(), 300);
                })
                .catch(() => alert("❌ Koneksi gagal, coba lagi."));
        }

        scanInput.addEventListener("keydown", function (e) {
            if (e.key !== "Enter") return;
            const val = this.value.trim();
            if (!val) return;
            if (!mode) { alert("Pilih mode dulu"); return; }
            beep(900, 60); processBarcode(val);
        });

        /* ── QTY ── */
        function setQty(val) { qtyInput.value = val; qtyInput.focus(); }
        qtyInput.addEventListener("keydown", e => { if (e.key === "Enter") submitTransaction(); });

        /* ── SUBMIT ── */
        function submitTransaction() {
            const qty = parseInt(qtyInput.value);
            if (!selectedItem) { alert("Scan part dulu"); return; }
            if (!qty || qty < 1) { alert("Qty tidak valid"); return; }
            pendingQty = qty;

            const labels = { in: "IN", out: "OUT", return: "RETURN" };
            const partName = selectedItem.nama ?? selectedItem.name ?? '—';

            document.getElementById("confirmDetail").innerHTML = `
        <div class="confirm-row">
            <span class="confirm-lbl">Part</span>
            <span class="confirm-val big">${partName}</span>
        </div>
        <div class="confirm-row">
            <span class="confirm-lbl">Nomor Part</span>
            <span class="confirm-val">${selectedItem.part}</span>
        </div>
        <div class="confirm-row">
            <span class="confirm-lbl">Model Engine</span>
            <span class="confirm-val">${selectedItem.models ?? '—'}</span>
        </div>
        <div class="confirm-row">
            <span class="confirm-lbl">Mode</span>
            <span class="confirm-val"><span class="chip ${mode}">${labels[mode] ?? mode}</span></span>
        </div>
        <div class="confirm-row">
            <span class="confirm-lbl">Qty</span>
            <span class="confirm-val big">${qty} pcs</span>
        </div>
        <div class="confirm-row">
            <span class="confirm-lbl">Stock Saat Ini</span>
            <span class="confirm-val">${selectedItem.stock}</span>
        </div>
    `;
            openPopup("confirmModal");
        }

        /* ── TRANSAKSI ── */
        function doTransaction() {
            closePopup("confirmModal");
            fetch("api/process_transaction.php", {
                method: "POST", headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ mode, part: selectedItem.part, qty: pendingQty })
            })
                .then(r => r.text())
                .then(text => {
                    try {
                        const res = JSON.parse(text);
                        if (res.success) {
                            beep(1200, 120); vibrate([60, 40, 60]);
                            alert("✅ Transaction Success");
                            resetForm(); loadHistory();
                        } else { beep(400, 200, 0.4); alert("❌ " + res.error); }
                    } catch (e) { alert("Server Error."); console.error(text); }
                });
        }
        function closeConfirm() { closePopup("confirmModal"); }

        function resetForm() {
            qtyInput.value = ""; qtyInput.style.display = "none";
            submitBtn.style.display = "none";
            document.getElementById("qtyPresets").style.display = "none";
            selectedItem = null; pendingQty = 0;
            scanInput.value = ""; hidePartInfo();
            setTimeout(() => scanInput.focus(), 200);
        }

        /* ── HISTORY ── */
        function loadHistory() {
            fetch("api/get_history.php").then(r => r.text())
                .then(html => { document.getElementById("historyBox").innerHTML = html; });
        }
        setInterval(loadHistory, 3000);

        /* ── DOWNLOAD ── */
        function downloadHistory() {
            const d = document.getElementById("dlDate").value;
            if (!d) { alert("Pilih tanggal"); return; }
            window.open("api/export_transaction_daily.php?date=" + d, "_blank");
        }
        function downloadHistoryMonthly() {
            const m = document.getElementById("dlMonth").value;
            if (!m) { alert("Pilih bulan"); return; }
            window.open("api/export_transaction_monthly.php?month=" + m, "_blank");
        }
    </script>
</body>

</html>
