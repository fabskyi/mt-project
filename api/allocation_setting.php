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

if ($role == "monitor") {
    header("Location: monitor.php");
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="sidebar.css">
    <link rel="icon" type="image/png" href="assets/yanmar.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Allocation Setting - PT. YADIN</title>

    <script src="sidebar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:Segoe UI,sans-serif; }
        body { background:#f4f6f9; }
        .layout { display:flex; min-height:100vh; }

        /* SIDEBAR */
        .sidebar { width:220px; background:#1f2d3d; color:white; padding:20px; flex-shrink:0; }
        .logo { display:flex; align-items:center; gap:10px; margin-bottom:35px; padding-bottom:15px; border-bottom:1px solid rgba(255,255,255,0.1); }
        .logo-img { height:30px; width:auto; }
        .logo-title { font-size:19px; font-weight:bold; letter-spacing:1px; color:white; position:relative; padding-left:5px; }
        .logo-title::before { content:""; position:absolute; left:0; top:5px; width:4px; height:18px; background:#e11d48; border-radius:2px; }
        .logo-sub { font-size:11px; color:rgba(255,255,255,0.6); margin-top:6px; }
        .sidebar ul { list-style:none; }
        .sidebar li { padding:12px; border-radius:8px; cursor:pointer; margin-bottom:10px; }
        .sidebar li.active, .sidebar li:hover { background:#3c8dbc; }

        /* MAIN */
        .main { flex:1; padding:25px; overflow:hidden; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px; }
        .top-actions { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        .top-actions input, .top-actions select { padding:6px 10px; border:1px solid #ccc; border-radius:6px; font-size:13px; }
        #searchInput { width:200px; }

        /* WORKING DAYS SETTING */
        .working-days-box { background:linear-gradient(135deg,#fff7ed,#ffedd5); border:2px solid #fb923c; border-radius:10px; padding:10px 16px; display:flex; align-items:center; gap:12px; margin-bottom:16px; box-shadow:0 4px 12px rgba(251,146,60,0.15); flex-wrap:wrap; }
        .working-days-box label { font-size:12px; font-weight:700; color:#9a3412; text-transform:uppercase; letter-spacing:0.5px; white-space:nowrap; }
        .working-days-box input { width:70px; padding:7px 10px; border:2px solid #fb923c; border-radius:8px; text-align:center; font-size:15px; font-weight:800; color:#ea580c; background:white; transition:all .2s; }
        .working-days-box input:focus { outline:none; border-color:#ea580c; box-shadow:0 0 0 3px rgba(234,88,12,0.2); }
        .working-days-box .hint { font-size:11px; color:#92400e; background:#fed7aa; padding:4px 10px; border-radius:6px; }
        .btn-save-days { padding:7px 16px; background:linear-gradient(135deg,#fb923c,#f97316); color:white; border:none; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer; transition:.2s; }
        .btn-save-days:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(249,115,22,0.4); }
        .btn-save-days:disabled { opacity:.5; cursor:not-allowed; transform:none; }
        .formula-hint { font-size:11px; color:#92400e; background:#fed7aa; border:1px solid #fb923c; border-radius:6px; padding:4px 10px; display:inline-flex; align-items:center; gap:5px; }

        /* CARDS */
        .summary { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:14px; margin-bottom:18px; }
        .card { background:white; padding:14px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08); text-align:center; }
        .card h3 { font-size:12px; color:#666; margin-bottom:8px; }
        .card p { font-size:24px; font-weight:bold; color:#1f2d3d; }

        /* TOOLBAR */
        .table-toolbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:10px; }
        .toolbar-left { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
        .toolbar-right { display:flex; gap:8px; align-items:center; }
        .bulk-box { display:flex; gap:6px; align-items:center; background:white; border:1px solid #e2e8f0; padding:6px 10px; border-radius:8px; font-size:12px; }
        .bulk-box select, .bulk-box input { padding:4px 8px; border:1px solid #cbd5e1; border-radius:6px; font-size:12px; }
        .bulk-box select { max-width:160px; }
        .bulk-box input { width:65px; }
        .page-info { font-size:13px; color:#64748b; white-space:nowrap; }

        /* TABLE */
        .table-container { background:#fff; border-radius:14px; box-shadow:0 6px 18px rgba(0,0,0,0.05); overflow:auto; max-height:calc(100vh - 470px); }
        #allocationTable { width:100%; border-collapse:collapse; font-size:14px; }
        #allocationTable thead { background:#f1f5f9; position:sticky; top:0; z-index:10; }
        #allocationTable th { padding:12px 14px; font-size:11px; font-weight:600; text-transform:uppercase; color:#475569; border-bottom:2px solid #e2e8f0; text-align:center; white-space:nowrap; }
        #allocationTable td { padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#334155; text-align:center; vertical-align:middle; }
        #allocationTable tbody tr:nth-child(even) { background:#fafbfd; }
        #allocationTable tbody tr:hover { background:#eef6ff; }
        #allocationTable th:first-child, #allocationTable td:first-child { position:sticky; left:0; background:#f8fafc; z-index:3; box-shadow:2px 0 6px rgba(0,0,0,0.04); }
        #allocationTable tbody tr:nth-child(even) td:first-child { background:#fafbfd; }

        /* pending row highlight */
        tr.pending td { background:#fffbeb !important; }
        tr.pending td:first-child { background:#fffbeb !important; }

        /* INPUT */
        .pct-input { width:76px; padding:5px 8px; border-radius:8px; border:1px solid #cbd5e1; text-align:center; font-weight:600; font-size:13px; transition:all .2s; }
        .pct-input:focus { border-color:#3c8dbc; box-shadow:0 0 0 3px rgba(60,141,188,.15); outline:none; }
        .pct-input.error { border-color:#dc2626; background:#fff1f1; }

        /* Days/Week input */
        .days-input { width:60px; padding:5px 8px; border-radius:8px; border:1px solid #fb923c; text-align:center; font-weight:600; font-size:13px; background:#fffbeb; transition:all .2s; }
        .days-input:focus { border-color:#ea580c; box-shadow:0 0 0 3px rgba(234,88,12,.15); outline:none; }

        /* BADGES */
        .alloc-badge { background:#eff6ff; color:#1d4ed8; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; display:inline-block; }
        .total-badge { padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700; display:inline-block; }
        .total-badge.ok   { background:#dcfce7; color:#16a34a; }
        .total-badge.warn { background:#fef9c3; color:#ca8a04; }
        .total-badge.over { background:#fee2e2; color:#dc2626; }

        /* Safety Stock badge */
        .ss-badge { background:#f0fdf4; color:#15803d; padding:3px 9px; border-radius:20px; font-size:12px; font-weight:600; display:inline-block; position:relative; }
        .ss-badge.auto { background:linear-gradient(135deg,#fef3c7,#fef08a); color:#854d0e; border:1px solid #fcd34d; }
        .ss-badge.auto::before { content:"⚡"; position:absolute; top:-4px; right:-4px; font-size:10px; }
        .ss-zero { background:#f1f5f9; color:#94a3b8; padding:3px 9px; border-radius:20px; font-size:11px; display:inline-block; }
        .ss-pct-badge { background:#fdf4ff; color:#7e22ce; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:600; display:inline-block; cursor:default; }
        .ss-pct-badge[title] { border-bottom:1px dashed #a855f7; }

        /* Monthly Plan input */
        .mp-input { width:76px; padding:5px 8px; border-radius:8px; border:1px solid #cbd5e1; text-align:center; font-weight:600; font-size:13px; transition:all .2s; background:#fffbeb; }
        .mp-input:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.15); outline:none; }
        .mp-pct-badge { background:#fff7ed; color:#ea580c; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:600; display:inline-block; }
        .mp-zero { background:#f1f5f9; color:#94a3b8; padding:3px 9px; border-radius:20px; font-size:11px; display:inline-block; }

        /* BAR */
        .pct-bar-wrap { width:100%; height:7px; background:#e2e8f0; border-radius:999px; overflow:hidden; min-width:70px; }
        .pct-bar-fill { height:100%; border-radius:999px; background:linear-gradient(90deg,#22c55e,#16a34a); transition:width .4s,background .3s; }
        .pct-bar-fill.warn   { background:linear-gradient(90deg,#f59e0b,#d97706); }
        .pct-bar-fill.danger { background:linear-gradient(90deg,#dc2626,#ef4444); }

        /* PAGINATION */
        .pagination { display:flex; justify-content:center; align-items:center; gap:5px; padding:14px 0 2px; flex-wrap:wrap; }
        .page-btn { padding:5px 11px; border:1px solid #e2e8f0; background:white; border-radius:6px; cursor:pointer; font-size:13px; transition:.15s; }
        .page-btn:hover:not(:disabled) { background:#f1f5f9; }
        .page-btn.active { background:#1f2d3d; color:white; border-color:#1f2d3d; font-weight:600; }
        .page-btn:disabled { opacity:.35; cursor:default; }

        /* BUTTONS */
        .btn-nav { padding:7px 14px; background:#6c757d; color:#fff; border:none; border-radius:6px; font-size:12px; cursor:pointer; transition:.2s; }
        .btn-nav:hover { background:#5a6268; }
        .btn-logout { padding:7px 14px; background:#dc3545; color:#fff; border:none; border-radius:6px; font-size:12px; cursor:pointer; }
        .btn-logout:hover { background:#b02a37; }
        .btn-save-all { padding:7px 16px; background:linear-gradient(135deg,#111,#333); color:#fff; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; transition:.2s; display:flex; align-items:center; gap:5px; }
        .btn-save-all:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(0,0,0,.2); }
        .btn-refresh { padding:7px 14px; background:#0d9488; color:#fff; border:none; border-radius:6px; font-size:12px; cursor:pointer; display:flex; align-items:center; gap:5px; }
        .btn-refresh:hover { background:#0f766e; }
        .btn-refresh .icon { display:inline-block; }
        .btn-refresh.spinning .icon { animation:spinOnce .6s ease forwards; }
        .btn-bulk { padding:5px 12px; background:#3c8dbc; color:white; border:none; border-radius:6px; font-size:12px; cursor:pointer; font-weight:600; white-space:nowrap; }
        .btn-bulk:hover { background:#2e7da8; }
        .pending-count { background:#f59e0b; color:white; font-size:11px; padding:1px 6px; border-radius:20px; display:none; }

        /* Auto from Monthly Plan button */
        .btn-auto-ss { padding:7px 14px; background:linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:5px; transition:.2s; }
        .btn-auto-ss:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(109,40,217,.35); }
        .btn-auto-ss:disabled { opacity:.5; cursor:not-allowed; transform:none; }

        /* Monthly Plan Manager button */
        .btn-mp-manager { padding:7px 14px; background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:5px; transition:.2s; }
        .btn-mp-manager:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(245,158,11,.35); }

        /* Import Excel button */
        .btn-import-excel { padding:7px 14px; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; transition:.2s; }
        .btn-import-excel:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(16,185,129,.35); }

        /* Transfer button */
        .btn-transfer { padding:7px 14px; background:linear-gradient(135deg,#ea580c,#c2410c); color:#fff; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:5px; transition:.2s; }
        .btn-transfer:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(234,88,12,.35); }

        @keyframes spinOnce { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }

        /* TOAST */
        .toast-confirm { position:fixed; inset:0; background:rgba(0,0,0,0.4); display:none; justify-content:center; align-items:center; z-index:9999; }
        .toast-box { background:white; padding:20px 25px; border-radius:12px; width:340px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,.2); animation:scaleIn .2s ease; }
        .toast-box .toast-sub { font-size:12px; color:#64748b; margin-top:6px; }
        .toast-actions { margin-top:15px; display:flex; justify-content:center; gap:10px; }
        .btn-yes { background:#16a34a; color:white; border:none; padding:6px 18px; border-radius:6px; cursor:pointer; font-weight:600; }
        .btn-yes.purple { background:#7c3aed; }
        .btn-no  { background:#e5e7eb; border:none; padding:6px 18px; border-radius:6px; cursor:pointer; }
        @keyframes scaleIn { from{transform:scale(.8);opacity:0} to{transform:scale(1);opacity:1} }

        /* SNACKBAR */
        #snackbar { visibility:hidden; min-width:250px; background:#1f2d3d; color:white; text-align:center; border-radius:8px; padding:12px 20px; position:fixed; z-index:9999; bottom:30px; left:50%; transform:translateX(-50%); font-size:13px; font-weight:600; }
        #snackbar.show { visibility:visible; animation:fadeInUp .3s ease, fadeOut .5s ease 2.5s forwards; }
        @keyframes fadeInUp { from{opacity:0;transform:translateX(-50%) translateY(20px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }
        @keyframes fadeOut  { to{opacity:0} }

        /* SKELETON */
        .skeleton-row td { background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); background-size:400% 100%; animation:shimmer 1.2s infinite; height:18px; }
        @keyframes shimmer { 0%{background-position:100% 0} 100%{background-position:-100% 0} }

        /* ══════════════════════════════════════════════════════
           MONTHLY PLAN MANAGER MODAL
        ══════════════════════════════════════════════════════ */
        .modal-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); display:none; justify-content:center; align-items:center; z-index:9998; animation:fadeIn .2s ease; }

        /* MP Modal box */
        .mp-modal-box { background:#fff; width:680px; max-width:96vw; border-radius:20px; box-shadow:0 25px 60px rgba(0,0,0,.25); overflow:hidden; animation:slideUp .25s ease; max-height:90vh; display:flex; flex-direction:column; }
        .mp-modal-header { padding:18px 24px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; background:linear-gradient(135deg,#fffbeb,#fff); flex-shrink:0; }
        .mp-modal-header h3 { font-size:17px; font-weight:700; color:#1f2d3d; display:flex; align-items:center; gap:8px; }
        .mp-modal-body { padding:20px 24px; overflow-y:auto; flex:1; }
        .mp-modal-footer { padding:14px 24px; border-top:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; background:#fafbfd; flex-shrink:0; }
        .modal-close { font-size:22px; cursor:pointer; color:#94a3b8; line-height:1; }
        .modal-close:hover { color:#334155; }

        /* Model selector */
        .mp-model-select { margin-bottom:16px; }
        .mp-model-select label { font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:6px; }
        .mp-model-select select { width:100%; padding:10px 12px; border:1px solid #e2e8f0; border-radius:10px; font-size:14px; font-weight:600; }
        .mp-model-select select:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.15); outline:none; }

        /* Summary bar */
        .mp-summary-bar { display:flex; gap:12px; margin-bottom:16px; flex-wrap:wrap; }
        .mp-stat { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:10px 16px; flex:1; min-width:120px; text-align:center; }
        .mp-stat label { font-size:11px; color:#64748b; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase; }
        .mp-stat span { font-size:20px; font-weight:800; color:#1f2d3d; }
        .mp-stat.highlight { background:linear-gradient(135deg,#fffbeb,#fef3c7); border-color:#fcd34d; }
        .mp-stat.highlight span { color:#b45309; }

        /* Part list in modal */
        .mp-part-list { display:flex; flex-direction:column; gap:8px; }
        .mp-part-row { display:flex; align-items:center; gap:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:10px 14px; transition:.2s; }
        .mp-part-row:hover { background:#eff6ff; border-color:#bfdbfe; }
        .mp-part-row.changed { background:#fffbeb; border-color:#fcd34d; }
        .mp-part-info { flex:1; min-width:0; }
        .mp-part-name { font-size:13px; font-weight:600; color:#1f2d3d; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .mp-part-num  { font-size:11px; color:#94a3b8; margin-top:2px; }
        .mp-part-ss   { font-size:11px; color:#16a34a; font-weight:600; background:#f0fdf4; padding:2px 7px; border-radius:10px; white-space:nowrap; }
        .mp-input-wrap { display:flex; align-items:center; gap:6px; flex-shrink:0; }
        .mp-input-wrap label { font-size:11px; color:#64748b; white-space:nowrap; }
        .mp-modal-input { width:90px; padding:7px 10px; border:1px solid #cbd5e1; border-radius:8px; text-align:center; font-size:14px; font-weight:700; color:#1f2d3d; transition:.2s; }
        .mp-modal-input:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.15); outline:none; background:#fffbeb; }
        .mp-modal-input.changed { border-color:#f59e0b; background:#fffbeb; }
        .mp-pct-live { font-size:12px; font-weight:700; color:#ea580c; min-width:44px; text-align:right; }

        /* Search in modal */
        .mp-search { width:100%; padding:9px 12px; border:1px solid #e2e8f0; border-radius:10px; font-size:13px; margin-bottom:14px; }
        .mp-search:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.15); outline:none; }

        /* Bulk set in modal */
        .mp-bulk-row { display:flex; gap:8px; align-items:center; margin-bottom:14px; background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:10px 14px; flex-wrap:wrap; }
        .mp-bulk-row label { font-size:12px; font-weight:600; color:#92400e; white-space:nowrap; }
        .mp-bulk-row input { width:80px; padding:6px 8px; border:1px solid #fcd34d; border-radius:7px; text-align:center; font-size:13px; font-weight:700; }
        .btn-mp-bulk { padding:6px 14px; background:#f59e0b; color:white; border:none; border-radius:7px; font-size:12px; font-weight:700; cursor:pointer; }
        .btn-mp-bulk:hover { background:#d97706; }
        .btn-mp-reset { padding:6px 12px; background:#e5e7eb; color:#374151; border:none; border-radius:7px; font-size:12px; cursor:pointer; }
        .btn-mp-reset:hover { background:#d1d5db; }

        /* Save btn */
        .btn-mp-save { padding:9px 22px; background:linear-gradient(135deg,#f59e0b,#d97706); color:white; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; transition:.2s; }
        .btn-mp-save:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(245,158,11,.35); }
        .btn-mp-save:disabled { opacity:.4; cursor:not-allowed; transform:none; }

        /* Empty state */
        .mp-empty { text-align:center; padding:40px; color:#94a3b8; font-size:14px; }

        /* Changed count badge */
        .mp-changed-count { background:#f59e0b; color:white; font-size:11px; padding:2px 7px; border-radius:20px; display:none; margin-left:4px; }

        /* Import info banner */
        .import-info { background:linear-gradient(135deg,#d1fae5,#a7f3d0); border:1px solid #10b981; border-radius:10px; padding:10px 14px; margin-bottom:14px; font-size:12px; color:#065f46; display:none; }
        .import-info.show { display:block; }
        .import-info strong { color:#047857; }

        /* ══════════════════════════════════════════════════════
           TRANSFER STOCK MODAL
        ══════════════════════════════════════════════════════ */
        .modal-box { background:#fff; width:560px; max-width:95vw; border-radius:20px; box-shadow:0 25px 60px rgba(0,0,0,.25); overflow:hidden; animation:slideUp .25s ease; }
        .modal-header { padding:18px 24px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; background:linear-gradient(135deg,#fff7ed,#fff); }
        .modal-header h3 { font-size:17px; font-weight:700; color:#1f2d3d; display:flex; align-items:center; gap:8px; }
        .modal-body { padding:20px 24px; }
        .modal-footer { padding:14px 24px; border-top:1px solid #f1f5f9; display:flex; justify-content:flex-end; gap:10px; background:#fafbfd; }

        /* Part selector */
        .part-selector { margin-bottom:18px; }
        .part-selector label { font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:6px; }
        .part-selector select { width:100%; padding:10px 12px; border:1px solid #e2e8f0; border-radius:10px; font-size:14px; transition:.2s; }
        .part-selector select:focus { border-color:#ea580c; box-shadow:0 0 0 3px rgba(234,88,12,.15); outline:none; }

        /* Transfer flow */
        .transfer-flow { display:flex; gap:12px; align-items:stretch; margin-bottom:16px; }
        .transfer-side { flex:1; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px; }
        .transfer-side.from { border-color:#fca5a5; background:#fff5f5; }
        .transfer-side.to   { border-color:#86efac; background:#f0fdf4; }
        .transfer-side label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#64748b; display:block; margin-bottom:8px; }
        .transfer-side.from label { color:#dc2626; }
        .transfer-side.to   label { color:#16a34a; }
        .transfer-side select { width:100%; padding:8px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; font-weight:600; }
        .transfer-side select:focus { outline:none; border-color:#3c8dbc; }
        .transfer-stock-info { margin-top:8px; font-size:12px; color:#64748b; display:flex; justify-content:space-between; }
        .transfer-stock-info strong { color:#1f2d3d; }
        .transfer-arrow { display:flex; align-items:center; justify-content:center; font-size:24px; color:#94a3b8; flex-shrink:0; padding-top:24px; }

        /* Qty box */
        .qty-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px; margin-bottom:16px; }
        .qty-box label { font-size:12px; font-weight:600; color:#475569; display:block; margin-bottom:8px; }
        .qty-row { display:flex; align-items:center; gap:10px; }
        .qty-stepper { display:flex; align-items:center; gap:0; border:1px solid #cbd5e1; border-radius:8px; overflow:hidden; }
        .qty-stepper button { width:36px; height:36px; border:none; background:#f1f5f9; cursor:pointer; font-size:18px; font-weight:600; color:#334155; transition:.15s; }
        .qty-stepper button:hover { background:#e2e8f0; }
        .qty-stepper input { width:70px; height:36px; border:none; border-left:1px solid #cbd5e1; border-right:1px solid #cbd5e1; text-align:center; font-size:15px; font-weight:700; color:#1f2d3d; }
        .qty-stepper input:focus { outline:none; background:#eff6ff; }
        .qty-max { font-size:12px; color:#64748b; }
        .qty-max span { font-weight:700; color:#dc2626; }

        /* Preview */
        .transfer-preview { background:linear-gradient(135deg,#eff6ff,#f0fdf4); border:1px solid #bfdbfe; border-radius:12px; padding:14px; font-size:13px; display:none; }
        .transfer-preview.show { display:block; }
        .preview-row { display:flex; justify-content:space-between; align-items:center; padding:4px 0; }
        .preview-row:not(:last-child) { border-bottom:1px dashed #e2e8f0; }
        .preview-label { color:#64748b; }
        .preview-val { font-weight:700; color:#1f2d3d; }
        .preview-val.red   { color:#dc2626; }
        .preview-val.green { color:#16a34a; }

        /* Urgent badge */
        .urgent-badge { background:#fef2f2; border:1px solid #fca5a5; color:#dc2626; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:600; display:flex; align-items:center; gap:6px; margin-bottom:14px; }

        /* Buttons in modal */
        .btn-do-transfer { padding:9px 20px; background:linear-gradient(135deg,#ea580c,#c2410c); color:white; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; transition:.2s; }
        .btn-do-transfer:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(234,88,12,.3); }
        .btn-do-transfer:disabled { opacity:.4; cursor:not-allowed; transform:none; }
        .btn-cancel-modal { padding:9px 18px; background:#e2e8f0; border:none; border-radius:10px; font-size:13px; cursor:pointer; font-weight:600; }

        @keyframes fadeIn  { from{opacity:0} to{opacity:1} }
        @keyframes slideUp { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }

        /* Legend hint */
        .ss-legend { font-size:11px; color:#7e22ce; background:#fdf4ff; border:1px solid #e9d5ff; border-radius:6px; padding:4px 10px; display:inline-flex; align-items:center; gap:5px; }
    </style>
</head>
<body>
<div class="layout">

   <?php include 'sidebar.php'; ?>

    <main class="main">
        <header class="topbar">
            <h2 id="pageTitle">ALLOCATION SETTING - MS<?php echo $lokasi; ?></h2>
            <div class="top-actions">
                <select id="filterItem" onchange="onFilterChange()">
                    <option value="">All Parts</option>
                </select>
                <select id="filterModel" onchange="onFilterChange()">
                    <option value="">All Models</option>
                </select>
                <input type="text" id="searchInput" placeholder="Search...">
                <button class="btn-auto-ss" id="btnAutoSS" onclick="confirmAutoSS()" title="Hitung otomatis allocation% dari rasio monthly plan tiap model">
                    📅 Auto dari Monthly Plan
                </button>
                <button class="btn-mp-manager" onclick="openMpModal()" title="Atur Monthly Plan per Model">
                    📋 Monthly Plan
                </button>
                <button class="btn-transfer" onclick="openTransferModal()" title="Transfer allocated stock antar model (urgent)">
                    🔀 Transfer Stock
                </button>
                <button class="btn-save-all" onclick="confirmSaveAll()">
                    💾 Save All
                    <span class="pending-count" id="pendingCount">0</span>
                </button>
                <button id="btnRefresh" class="btn-refresh" onclick="manualRefresh()">
                    <i class="icon">⟳</i> Refresh
                </button>
                <button class="btn-nav" onclick="goBack()">← Back</button>
                <button class="btn-logout" onclick="logout()">Logout</button>
            </div>
        </header>

        <!-- WORKING DAYS / MONTH SETTING BOX -->
        <div class="working-days-box">
            <label>🗓️ Working Days(Month):</label>
            <input type="number" id="workingDaysMonth" min="1" max="31" value="22" oninput="onDaysMonthChange()">
            <span class="hint" id="daysHint">Default: 22 hari</span>
            <button class="btn-save-days" id="btnSaveDays" onclick="saveWorkingDaysMonth()" disabled>
                💾 Simpan & Recalculate
            </button>
            <span class="formula-hint" title="Safety Stock = (Monthly Plan ÷ Days/Month) × Days/Week">
                Input sesuai hari kerja
            </span>
        </div>

        <section class="summary">
            <div class="card"><h3>Total Row</h3><p id="cardTotalItem">0</p></div>
            <div class="card"><h3>Total Part</h3><p id="cardTotalPart">0</p></div>
            <div class="card"><h3>Fully Allocated</h3><p id="cardFullAlloc">0</p></div>
            <div class="card"><h3>Unallocated</h3><p id="cardUnalloc">0</p></div>
            <div class="card"><h3>Over Allocated</h3><p id="cardOver">0</p></div>
            <div class="card"><h3>Pending Changes</h3><p id="cardPending">0</p></div>
        </section>

        <div class="table-toolbar">
            <div class="toolbar-left">
                <div class="bulk-box">
                    <span>Bulk:</span>
                    <select id="bulkPart"><option value="">Pilih Part</option></select>
                    <input type="number" id="bulkPct" placeholder="%" min="0" max="100" step="0.01">
                    <button class="btn-bulk" onclick="applyBulk()">Set ke Model Terfilter</button>
                </div>
                <span class="ss-legend">📅 Monthly Plan = dasar perhitungan alokasi otomatis</span>
            </div>
            <div class="toolbar-right">
                <span class="page-info" id="pageInfo">-</span>
                <select id="perPageSelect" onchange="onPerPageChange()">
                    <option value="50">50 / page</option>
                    <option value="100">100 / page</option>
                    <option value="200">200 / page</option>
                    <option value="999999">All</option>
                </select>
            </div>
        </div>

        <section class="table-container">
            <table id="allocationTable">
                <thead>
                    <tr>
                        <th style="width:46px">No</th>
                        <th style="width:130px">Model</th>
                        <th style="width:150px">Part Name</th>
                        <th style="width:155px">Part Number</th>
                        <th style="width:80px">Stock</th>
                        <th style="width:100px" title="Rencana produksi bulanan per model">Monthly Plan</th>
                        <th style="width:90px" title="Alokasi % berdasarkan rasio monthly plan">MP %</th>
                        <th style="width:75px" title="Hari kerja per minggu (untuk hitung safety stock)">Days/Week</th>
                        <th style="width:95px" title="Auto-calculated: (MP ÷ Days/Mo) × Days/Wk">Safety Stock</th>
                        <th style="width:100px">Alloc %</th>
                        <th style="width:110px">Alloc Stock</th>
                        <th style="width:130px">Total % (Part)</th>
                        <th style="width:130px">Distribution</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr class="skeleton-row"><td colspan="13">&nbsp;</td></tr>
                </tbody>
            </table>
        </section>

        <div class="pagination" id="pagination"></div>
    </main>
</div>

<!-- ═══════════════════════════════════════════════════
     SAVE ALL CONFIRM TOAST
═══════════════════════════════════════════════════ -->
<div id="toastConfirm" class="toast-confirm">
    <div class="toast-box">
        <p id="toastMessage">Simpan semua perubahan alokasi?</p>
        <div class="toast-sub" id="toastSub"></div>
        <div class="toast-actions">
            <button id="toastYesBtn" class="btn-yes">✔ Yes, Save</button>
            <button onclick="closeToast()" class="btn-no">Cancel</button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════
     MONTHLY PLAN MANAGER MODAL
═══════════════════════════════════════════════════ -->
<div id="mpModal" class="modal-overlay">
    <div class="mp-modal-box">
        <div class="mp-modal-header">
            <h3>📋 Monthly Plan Manager
                <span class="mp-changed-count" id="mpChangedCount">0</span>
            </h3>
            <div style="display:flex;gap:10px;align-items:center;">
                <input type="file" id="mpExcelFile" accept=".xlsx,.xls" style="display:none" onchange="handleExcelImport(this)">
                <button class="btn-import-excel" onclick="document.getElementById('mpExcelFile').click()" title="Import dari Excel (MODEL, Qty)">
                    📊 Import Excel
                </button>
                <span class="modal-close" onclick="closeMpModal()">×</span>
            </div>
        </div>
        <div class="mp-modal-body">

            <div class="import-info" id="importInfo">
                <strong>📊 Format Excel:</strong> Kolom 1 = MODEL, Kolom 2 = Qty
            </div>

            <div class="mp-model-select">
                <label>Filter Model (opsional)</label>
                <select id="mpModelFilter" onchange="renderMpList()">
                    <option value="">— Semua Model —</option>
                </select>
            </div>

            <div class="mp-summary-bar">
                <div class="mp-stat"><label>Total Model</label><span id="mpStatTotal">0</span></div>
                <div class="mp-stat highlight"><label>Total Monthly Plan</label><span id="mpStatSum">0</span></div>
                <div class="mp-stat"><label>Sudah Diisi</label><span id="mpStatFilled">0</span></div>
                <div class="mp-stat"><label>Belum Diisi</label><span id="mpStatEmpty">0</span></div>
            </div>

            <div class="mp-bulk-row">
                <label>⚡ Set semua model yang terlihat ke:</label>
                <input type="number" id="mpBulkVal" placeholder="qty" min="0">
                <button class="btn-mp-bulk" onclick="applyMpBulk()">Apply</button>
                <button class="btn-mp-reset" onclick="resetMpAll()">Reset ke 0</button>
            </div>

            <input type="text" class="mp-search" id="mpSearch" placeholder="🔍 Cari model atau part..." oninput="renderMpList()">

            <div class="mp-part-list" id="mpPartList">
                <div class="mp-empty">Memuat data...</div>
            </div>
        </div>
        <div class="mp-modal-footer">
            <span style="font-size:12px;color:#64748b" id="mpFooterInfo">—</span>
            <div style="display:flex;gap:10px">
                <button class="btn-cancel-modal" onclick="closeMpModal()">Batal</button>
                <button class="btn-mp-save" id="btnMpSave" onclick="saveMpChanges()" disabled>
                    💾 Simpan Monthly Plan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════
     TRANSFER STOCK MODAL
═══════════════════════════════════════════════════ -->
<div id="transferModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>🔀 Transfer Allocated Stock</h3>
            <span class="modal-close" onclick="closeTransferModal()">×</span>
        </div>
        <div class="modal-body">

            <div class="urgent-badge">
                ⚡ Gunakan fitur ini saat ada model yang dikerjakan secara urgent dan butuh tambahan stock segera.
            </div>

            <div class="part-selector">
                <label>1. Pilih Part yang ingin ditransfer</label>
                <select id="trPartSelect" onchange="onTrPartChange()">
                    <option value="">— Pilih Part —</option>
                </select>
            </div>

            <div class="transfer-flow">
                <div class="transfer-side from">
                    <label>📤 Ambil dari Model</label>
                    <select id="trFromModel" onchange="onTrModelChange()">
                        <option value="">— Pilih Model —</option>
                    </select>
                    <div class="transfer-stock-info">
                        <span>Alloc Stock:</span>
                        <strong id="trFromStock">—</strong>
                    </div>
                </div>

                <div class="transfer-arrow">➜</div>

                <div class="transfer-side to">
                    <label>📥 Berikan ke Model (Urgent)</label>
                    <select id="trToModel" onchange="onTrModelChange()">
                        <option value="">— Pilih Model —</option>
                    </select>
                    <div class="transfer-stock-info">
                        <span>Alloc Stock:</span>
                        <strong id="trToStock">—</strong>
                    </div>
                </div>
            </div>

            <div class="qty-box">
                <label>3. Jumlah Stock yang Dipindah</label>
                <div class="qty-row">
                    <div class="qty-stepper">
                        <button onclick="stepQty(-1)">−</button>
                        <input type="number" id="trQty" value="1" min="1" oninput="onTrQtyChange()">
                        <button onclick="stepQty(1)">+</button>
                    </div>
                    <div class="qty-max">Maks: <span id="trMaxQty">—</span> unit</div>
                </div>
            </div>

            <div class="transfer-preview" id="trPreview">
                <div class="preview-row">
                    <span class="preview-label">Part</span>
                    <span class="preview-val" id="pvPart">—</span>
                </div>
                <div class="preview-row">
                    <span class="preview-label">📤 <span id="pvFromName">—</span> sesudah transfer</span>
                    <span class="preview-val red" id="pvFromAfter">—</span>
                </div>
                <div class="preview-row">
                    <span class="preview-label">📥 <span id="pvToName">—</span> sesudah transfer</span>
                    <span class="preview-val green" id="pvToAfter">—</span>
                </div>
                <div class="preview-row">
                    <span class="preview-label">Total stock tetap</span>
                    <span class="preview-val" id="pvTotal">—</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="closeTransferModal()">Batal</button>
            <button class="btn-do-transfer" id="btnDoTransfer" onclick="doTransfer()" disabled>
                🔀 Lakukan Transfer
            </button>
        </div>
    </div>
</div>

<div id="snackbar"></div>

<script>
    let currentLokasi     = <?php echo $lokasi; ?>;
    let allData           = [];
    let filteredData      = [];
    let pendingChanges    = {};   // { model_item_id: { pct, mp, days } }
    let currentPage       = 1;
    let perPage           = 50;
    let filtersLoaded     = false;
    let workingDaysMonth  = 22;
    let originalDaysMonth = 22;
    let toastMode         = 'save';

    // ── MP Manager state ──
    let mpChanges = {}; // { model_item_id: new_monthly_plan }

    // ── Transfer state ──
    let trData = { partItemId: null, fromId: null, toId: null, fromStock: 0, toStock: 0 };

    document.addEventListener("DOMContentLoaded", () => {
        loadLocationSettings();
        loadData();
        document.getElementById("searchInput").addEventListener("keyup", () => {
            currentPage = 1;
            applyFilter();
        });
    });

    // ══════════════════════════════════════════════════════════════════════════
    // WORKING DAYS / MONTH
    // ══════════════════════════════════════════════════════════════════════════

    function loadLocationSettings() {
        fetch("./api/get_location_settings.php?lokasi_id=" + currentLokasi)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    workingDaysMonth  = res.working_days_per_month;
                    originalDaysMonth = workingDaysMonth;
                    document.getElementById("workingDaysMonth").value = workingDaysMonth;
                    document.getElementById("daysHint").textContent =
                        workingDaysMonth === 22 ? "Default: 22 hari" : `Tersimpan: ${workingDaysMonth} hari`;
                }
            });
    }

    function onDaysMonthChange() {
        const val = parseInt(document.getElementById("workingDaysMonth").value) || 22;
        const btn = document.getElementById("btnSaveDays");
        if (val !== originalDaysMonth) {
            btn.disabled = false;
            document.getElementById("daysHint").textContent = `Akan diubah ke ${val} hari`;
        } else {
            btn.disabled = true;
            document.getElementById("daysHint").textContent = `Tersimpan: ${val} hari`;
        }
        workingDaysMonth = val;
        renderPage(); // Live preview
    }

    function saveWorkingDaysMonth() {
        const val = parseInt(document.getElementById("workingDaysMonth").value) || 22;
        const btn = document.getElementById("btnSaveDays");
        btn.disabled = true;
        btn.textContent = "⟳ Menyimpan...";

        fetch("./api/save_location_settings.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ lokasi_id: currentLokasi, working_days_per_month: val })
        })
        .then(r => r.json())
        .then(res => {
            btn.textContent = "💾 Simpan & Recalculate";
            if (res.success) {
                showSnackbar(`✔ Working days diupdate! ${res.recalculated} part recalculated.`);
                originalDaysMonth = val;
                workingDaysMonth  = val;
                document.getElementById("daysHint").textContent = `Tersimpan: ${val} hari`;
                loadData();
            } else {
                btn.disabled = false;
                showSnackbar("❌ " + (res.message || "Gagal menyimpan"));
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = "💾 Simpan & Recalculate";
            showSnackbar("❌ Connection error");
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // LOAD DATA
    // ══════════════════════════════════════════════════════════════════════════

    function loadData() {
        fetch("./api/get_allocation.php?lokasi_id=" + currentLokasi)
            .then(r => r.json())
            .then(res => {
                if (!res.success) return;
                allData = res.data;

                // Sync workingDaysMonth dari data jika ada
                if (allData.length > 0 && allData[0].working_days_per_month) {
                    workingDaysMonth = parseInt(allData[0].working_days_per_month);
                }

                if (!filtersLoaded) {
                    populateFilters(allData);
                    filtersLoaded = true;
                }
                applyFilter();
                updateCards();
            });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // FILTERS
    // ══════════════════════════════════════════════════════════════════════════

    function populateFilters(data) {
        const partSel  = document.getElementById("filterItem");
        const modelSel = document.getElementById("filterModel");
        const bulkSel  = document.getElementById("bulkPart");

        [...new Set(data.map(d => d.part_name))].sort().forEach(p => {
            [partSel, bulkSel].forEach(sel => {
                const o = document.createElement("option");
                o.value = p; o.textContent = p; sel.appendChild(o);
            });
        });

        [...new Set(data.map(d => d.model_name))].sort().forEach(m => {
            const o = document.createElement("option");
            o.value = m; o.textContent = m; modelSel.appendChild(o);
        });
    }

    function onFilterChange() { currentPage = 1; applyFilter(); }

    function applyFilter() {
        const pf = document.getElementById("filterItem").value.toLowerCase();
        const mf = document.getElementById("filterModel").value.toLowerCase();
        const s  = document.getElementById("searchInput").value.toLowerCase();

        filteredData = allData.filter(d =>
            (!pf || d.part_name.toLowerCase()  === pf) &&
            (!mf || d.model_name.toLowerCase() === mf) &&
            (!s  || d.part_name.toLowerCase().includes(s) ||
                    d.model_name.toLowerCase().includes(s) ||
                    d.part_number.toLowerCase().includes(s))
        );

        renderPage();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SAFETY STOCK FORMULA
    // ══════════════════════════════════════════════════════════════════════════

    function calculateSafetyStock(monthlyPlan, daysPerWeek, daysPerMonth) {
        const dpm = daysPerMonth || workingDaysMonth;
        if (monthlyPlan <= 0 || daysPerWeek <= 0 || dpm <= 0) return 0;
        return Math.ceil((monthlyPlan / dpm) * daysPerWeek);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RENDER
    // ══════════════════════════════════════════════════════════════════════════

    function renderPage() {
        const total    = filteredData.length;
        const start    = (currentPage - 1) * perPage;
        const end      = Math.min(start + perPage, total);
        const pageData = filteredData.slice(start, end);

        document.getElementById("pageInfo").textContent =
            total === 0 ? "No data" : `Showing ${start+1}–${end} of ${total}`;

        renderTable(pageData, start);
        renderPagination(total);
    }

    function renderTable(data, offset) {
        const tbody = document.getElementById("tableBody");
        tbody.innerHTML = "";

        if (!data.length) {
            tbody.innerHTML = `<tr><td colspan="13" style="padding:30px;color:#94a3b8;text-align:center">No data found.</td></tr>`;
            return;
        }

        const frag = document.createDocumentFragment();

        data.forEach((row, idx) => {
            const pending  = pendingChanges[row.model_item_id] || {};
            const pct      = pending.pct  !== undefined ? pending.pct  : parseFloat(row.allocation_percentage) || 0;
            const mp       = pending.mp   !== undefined ? pending.mp   : parseInt(row.monthly_plan) || 0;
            const daysWeek = pending.days !== undefined ? pending.days : parseInt(row.working_days_per_week) || 5;
            const stock    = parseFloat(row.current_stock) || 0;
            const allocSt  = parseFloat(row.allocated_stock) || 0;
            const totalPct = calcTotalPct(row.item_id);
            const badgeCls = totalPct > 100 ? "over" : Math.abs(totalPct - 100) < 0.01 ? "ok" : "warn";
            const barCls   = totalPct > 100 ? "danger" : pct >= 80 ? "warn" : "";
            const isPend   = pendingChanges[row.model_item_id] !== undefined;

            // Auto-calculated Safety Stock
            const ss = calculateSafetyStock(mp, daysWeek);

            // Monthly Plan % badge
            const totalMP  = parseInt(row.total_monthly_plan) || 0;
            const mpPct    = totalMP > 0 ? (mp / totalMP * 100) : 0;
            const mpPctStr = totalMP > 0 ? mpPct.toFixed(1) + "%" : "—";
            const mpBadge  = totalMP > 0
                ? `<span class="mp-pct-badge" title="Monthly plan model ini: ${mp} / Total MP part: ${totalMP}">${mpPctStr}</span>`
                : `<span class="mp-zero">no plan</span>`;

            const tr = document.createElement("tr");
            if (isPend) tr.classList.add("pending");
            tr.innerHTML = `
                <td>${offset + idx + 1}</td>
                <td>${row.model_name}</td>
                <td style="text-align:left">${row.part_name}</td>
                <td style="font-size:12px;color:#64748b">${row.part_number}</td>
                <td><strong>${stock}</strong></td>
                <td>
                    <input type="number" class="mp-input"
                        min="0" step="1" value="${mp}"
                        data-id="${row.model_item_id}"
                        data-item-id="${row.item_id}"
                        oninput="onMpChange(this)"
                        placeholder="0">
                </td>
                <td id="mpbadge-${row.model_item_id}">${mpBadge}</td>
                <td>
                    <input type="number" class="days-input"
                        min="0" max="7" step="1" value="${daysWeek}"
                        data-id="${row.model_item_id}"
                        data-item-id="${row.item_id}"
                        oninput="onDaysWeekChange(this)"
                        placeholder="5">
                </td>
                <td>
                    <span class="ss-badge auto" id="ss-${row.model_item_id}"
                          title="Auto: (${mp} ÷ ${workingDaysMonth}) × ${daysWeek} = ${ss}">${ss}</span>
                </td>
                <td>
                    <input type="number" class="pct-input"
                        min="0" max="100" step="0.01" value="${pct}"
                        data-id="${row.model_item_id}"
                        data-item-id="${row.item_id}"
                        data-stock="${stock}"
                        oninput="onPctChange(this)">
                </td>
                <td><span class="alloc-badge" id="alloc-${row.model_item_id}">${allocSt}</span></td>
                <td><span class="total-badge ${badgeCls}" id="totalbadge-${row.item_id}">${totalPct.toFixed(2)}%</span></td>
                <td>
                    <div class="pct-bar-wrap">
                        <div class="pct-bar-fill ${barCls}" id="bar-${row.model_item_id}"
                             style="width:${Math.min(pct, 100)}%"></div>
                    </div>
                </td>`;
            frag.appendChild(tr);
        });

        tbody.appendChild(frag);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PAGINATION
    // ══════════════════════════════════════════════════════════════════════════

    function renderPagination(total) {
        const totalPages = Math.ceil(total / perPage);
        const pag = document.getElementById("pagination");
        pag.innerHTML = "";
        if (totalPages <= 1) return;

        const mk = (label, page, active, disabled) => {
            const b = document.createElement("button");
            b.className = "page-btn" + (active ? " active" : "");
            b.textContent = label; b.disabled = disabled;
            if (!disabled && page) b.onclick = () => { currentPage = page; renderPage(); };
            return b;
        };

        pag.appendChild(mk("«", 1, false, currentPage === 1));
        pag.appendChild(mk("‹", currentPage - 1, false, currentPage === 1));

        let s = Math.max(1, currentPage - 2), e = Math.min(totalPages, currentPage + 2);
        if (s > 1) pag.appendChild(mk("...", null, false, true));
        for (let p = s; p <= e; p++) pag.appendChild(mk(p, p, p === currentPage, false));
        if (e < totalPages) pag.appendChild(mk("...", null, false, true));

        pag.appendChild(mk("›", currentPage + 1, false, currentPage === totalPages));
        pag.appendChild(mk("»", totalPages, false, currentPage === totalPages));
    }

    function onPerPageChange() {
        perPage = parseInt(document.getElementById("perPageSelect").value);
        currentPage = 1;
        renderPage();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    function calcTotalPct(itemId) {
        let t = 0;
        allData.filter(d => d.item_id === itemId).forEach(d => {
            t += pendingChanges[d.model_item_id]?.pct !== undefined
                ? pendingChanges[d.model_item_id].pct
                : parseFloat(d.allocation_percentage) || 0;
        });
        return t;
    }

    function getExistingPending(id) {
        return pendingChanges[id] || {};
    }

    function updateMpBadgesForItem(itemId) {
        let totalMP = 0;
        allData.filter(d => d.item_id == itemId).forEach(d => {
            totalMP += pendingChanges[d.model_item_id]?.mp !== undefined
                ? pendingChanges[d.model_item_id].mp
                : parseInt(d.monthly_plan) || 0;
        });
        allData.filter(d => d.item_id == itemId).forEach(d => {
            const el = document.getElementById("mpbadge-" + d.model_item_id);
            if (!el) return;
            const thisMp = pendingChanges[d.model_item_id]?.mp !== undefined
                ? pendingChanges[d.model_item_id].mp
                : parseInt(d.monthly_plan) || 0;
            if (totalMP > 0) {
                const pctStr = (thisMp / totalMP * 100).toFixed(1) + "%";
                el.innerHTML = `<span class="mp-pct-badge" title="Monthly plan: ${thisMp} / Total: ${totalMP}">${pctStr}</span>`;
            } else {
                el.innerHTML = `<span class="mp-zero">no plan</span>`;
            }
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // INPUT HANDLERS
    // ══════════════════════════════════════════════════════════════════════════

    function onPctChange(input) {
        const id     = input.dataset.id;
        const itemId = input.dataset.itemId;
        const stock  = parseFloat(input.dataset.stock) || 0;
        let   val    = parseFloat(input.value) || 0;
        if (val < 0) val = 0;
        if (val > 100) val = 100;

        const ex = getExistingPending(id);
        pendingChanges[id] = {
            pct : val,
            mp  : ex.mp   !== undefined ? ex.mp   : parseInt(document.querySelector(`[data-id="${id}"].mp-input`)?.value) || 0,
            days: ex.days !== undefined ? ex.days : parseInt(document.querySelector(`[data-id="${id}"].days-input`)?.value) || 5
        };
        input.closest("tr").classList.add("pending");

        const ae = document.getElementById("alloc-" + id);
        if (ae) ae.textContent = Math.floor(stock * val / 100);

        const be = document.getElementById("bar-" + id);
        if (be) { be.style.width = Math.min(val, 100) + "%"; be.className = "pct-bar-fill" + (val >= 80 ? " warn" : ""); }

        const totalPct = calcTotalPct(itemId);
        document.querySelectorAll(`[id^="totalbadge-${itemId}"]`).forEach(el => {
            el.textContent = totalPct.toFixed(2) + "%";
            el.className = "total-badge " + (totalPct > 100 ? "over" : Math.abs(totalPct - 100) < 0.01 ? "ok" : "warn");
        });

        input.classList.toggle("error", totalPct > 100);
        updatePendingUI();
    }

    function onMpChange(input) {
        const id     = input.dataset.id;
        const itemId = input.dataset.itemId;
        let   val    = parseInt(input.value) || 0;
        if (val < 0) val = 0;

        const ex = getExistingPending(id);
        pendingChanges[id] = {
            pct : ex.pct  !== undefined ? ex.pct  : parseFloat(document.querySelector(`input.pct-input[data-id="${id}"]`)?.value) || 0,
            mp  : val,
            days: ex.days !== undefined ? ex.days : parseInt(document.querySelector(`[data-id="${id}"].days-input`)?.value) || 5
        };
        input.closest("tr").classList.add("pending");

        // Live update Safety Stock
        const daysWeek = pendingChanges[id].days;
        const ss = calculateSafetyStock(val, daysWeek);
        const ssEl = document.getElementById("ss-" + id);
        if (ssEl) {
            ssEl.textContent = ss;
            ssEl.title = `Auto: (${val} ÷ ${workingDaysMonth}) × ${daysWeek} = ${ss}`;
        }

        // Live update MP% badges
        updateMpBadgesForItem(itemId);
        updatePendingUI();
    }

    function onDaysWeekChange(input) {
        const id     = input.dataset.id;
        let   val    = parseInt(input.value) || 0;
        if (val < 0) val = 0;
        if (val > 7) val = 7;

        const ex = getExistingPending(id);
        pendingChanges[id] = {
            pct : ex.pct !== undefined ? ex.pct : parseFloat(document.querySelector(`input.pct-input[data-id="${id}"]`)?.value) || 0,
            mp  : ex.mp  !== undefined ? ex.mp  : parseInt(document.querySelector(`[data-id="${id}"].mp-input`)?.value) || 0,
            days: val
        };
        input.closest("tr").classList.add("pending");

        // Live update Safety Stock
        const mp = pendingChanges[id].mp;
        const ss = calculateSafetyStock(mp, val);
        const ssEl = document.getElementById("ss-" + id);
        if (ssEl) {
            ssEl.textContent = ss;
            ssEl.title = `Auto: (${mp} ÷ ${workingDaysMonth}) × ${val} = ${ss}`;
        }

        updatePendingUI();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // BULK SET (main table)
    // ══════════════════════════════════════════════════════════════════════════

    function applyBulk() {
        const part = document.getElementById("bulkPart").value;
        const pct  = parseFloat(document.getElementById("bulkPct").value);

        if (!part)                         { showSnackbar("⚠ Pilih Part terlebih dahulu"); return; }
        if (isNaN(pct) || pct < 0 || pct > 100) { showSnackbar("⚠ Masukkan % antara 0–100"); return; }

        const targets = filteredData.filter(d => d.part_name === part);
        if (!targets.length) { showSnackbar("⚠ Tidak ada data yang cocok di filter saat ini"); return; }

        targets.forEach(d => {
            const ex = getExistingPending(d.model_item_id);
            pendingChanges[d.model_item_id] = {
                pct : pct,
                mp  : ex.mp   !== undefined ? ex.mp   : parseInt(d.monthly_plan) || 0,
                days: ex.days !== undefined ? ex.days : parseInt(d.working_days_per_week) || 5
            };
        });

        renderPage();
        updateCards();
        updatePendingUI();
        showSnackbar(`✔ ${targets.length} row diset ke ${pct}%`);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AUTO ALLOCATION FROM MONTHLY PLAN
    // ══════════════════════════════════════════════════════════════════════════

    function confirmAutoSS() {
        const partsWithMP = new Set(allData.filter(d => parseInt(d.monthly_plan) > 0).map(d => d.item_id));
        const partsNoMP   = new Set(allData.filter(d => parseInt(d.total_monthly_plan) === 0).map(d => d.item_id));

        toastMode = 'autoSS';
        document.getElementById("toastMessage").textContent = "📅 Hitung otomatis alokasi dari Monthly Plan?";
        document.getElementById("toastSub").textContent =
            `${partsWithMP.size} part akan dihitung ulang (${partsNoMP.size} part tanpa Monthly Plan akan di-skip).`;
        document.getElementById("toastYesBtn").textContent = "📅 Ya, Hitung Otomatis";
        document.getElementById("toastYesBtn").className   = "btn-yes purple";
        document.getElementById("toastYesBtn").onclick     = doAutoSS;
        document.getElementById("toastConfirm").style.display = "flex";
    }

    function doAutoSS() {
        closeToast();
        const btn = document.getElementById("btnAutoSS");
        btn.disabled = true;
        btn.textContent = "⟳ Menghitung...";

        fetch("./api/calculate_from_safety.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ lokasi_id: currentLokasi })
        })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = "📅 Auto dari Monthly Plan";
            if (res.success) {
                showSnackbar(`✔ ${res.message}`);
                pendingChanges = {};
                resetFiltersAndLoad();
            } else {
                showSnackbar("❌ " + (res.message || "Gagal menghitung"));
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = "📅 Auto dari Monthly Plan";
            showSnackbar("❌ Connection error");
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SAVE ALL
    // ══════════════════════════════════════════════════════════════════════════

    function confirmSaveAll() {
        if (!Object.keys(pendingChanges).length) { showSnackbar("No changes to save."); return; }

        const overItems = new Set(allData.filter(d => calcTotalPct(d.item_id) > 100.001).map(d => d.item_id));
        if (overItems.size) { showSnackbar(`❌ ${overItems.size} part melebihi 100% — perbaiki dulu`); return; }

        toastMode = 'save';
        document.getElementById("toastMessage").textContent = "Simpan semua perubahan alokasi?";
        document.getElementById("toastSub").textContent     = `${Object.keys(pendingChanges).length} baris akan diperbarui.`;
        document.getElementById("toastYesBtn").textContent  = "✔ Yes, Save";
        document.getElementById("toastYesBtn").className    = "btn-yes";
        document.getElementById("toastYesBtn").onclick      = doSaveAll;
        document.getElementById("toastConfirm").style.display = "flex";
    }

    function doSaveAll() {
        closeToast();
        const payload = Object.entries(pendingChanges).map(([id, val]) => ({
            model_item_id        : parseInt(id),
            allocation_percentage: val.pct,
            monthly_plan         : val.mp,
            working_days_per_week: val.days
        }));

        fetch("./api/save_allocation.php", {
            method : "POST",
            headers: { "Content-Type": "application/json" },
            body   : JSON.stringify({ allocations: payload })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showSnackbar(`✔ Saved! ${res.recalculated} part recalculated.`);
                pendingChanges = {};
                resetFiltersAndLoad();
            } else {
                showSnackbar("❌ " + (res.message || "Save failed"));
            }
        })
        .catch(() => showSnackbar("❌ Connection error"));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CARDS & PENDING UI
    // ══════════════════════════════════════════════════════════════════════════

    function updateCards() {
        const pctMap = {};
        allData.forEach(d => {
            if (!pctMap[d.item_id]) pctMap[d.item_id] = 0;
            pctMap[d.item_id] += pendingChanges[d.model_item_id]?.pct !== undefined
                ? pendingChanges[d.model_item_id].pct
                : parseFloat(d.allocation_percentage) || 0;
        });

        let fully = 0, unalloc = 0, over = 0;
        Object.values(pctMap).forEach(t => {
            if (Math.abs(t - 100) < 0.01) fully++;
            else if (t === 0) unalloc++;
            else if (t > 100) over++;
        });

        document.getElementById("cardTotalItem").textContent = allData.length;
        document.getElementById("cardTotalPart").textContent = Object.keys(pctMap).length;
        document.getElementById("cardFullAlloc").textContent = fully;
        document.getElementById("cardUnalloc").textContent   = unalloc;
        document.getElementById("cardOver").textContent      = over;
    }

    function updatePendingUI() {
        const c = Object.keys(pendingChanges).length;
        document.getElementById("cardPending").textContent = c;
        const b = document.getElementById("pendingCount");
        b.textContent = c; b.style.display = c > 0 ? "inline" : "none";
    }

    // ══════════════════════════════════════════════════════════════════════════
    // UTILITIES
    // ══════════════════════════════════════════════════════════════════════════

    function resetFiltersAndLoad() {
        filtersLoaded = false;
        document.getElementById("filterItem").innerHTML  = '<option value="">All Parts</option>';
        document.getElementById("filterModel").innerHTML = '<option value="">All Models</option>';
        document.getElementById("bulkPart").innerHTML    = '<option value="">Pilih Part</option>';
        document.getElementById("searchInput").value     = "";
        currentPage = 1;
        loadData();
        loadLocationSettings();
        updatePendingUI();
    }

    function showSnackbar(msg) {
        const sb = document.getElementById("snackbar");
        sb.textContent = msg; sb.className = "show";
        setTimeout(() => sb.className = "", 3200);
    }

    function closeToast() { document.getElementById("toastConfirm").style.display = "none"; }

    function manualRefresh() {
        const btn = document.getElementById("btnRefresh");
        btn.classList.remove("spinning"); void btn.offsetWidth;
        btn.classList.add("spinning"); btn.disabled = true;
        pendingChanges = {};
        resetFiltersAndLoad();
        setTimeout(() => { btn.classList.remove("spinning"); btn.disabled = false; }, 800);
    }

    function setLokasi(id) {
        currentLokasi = id;
        document.getElementById("pageTitle").textContent = "ALLOCATION SETTING - MS" + id;
        if (document.getElementById("btnMS1")) document.getElementById("btnMS1").classList.toggle("active", id === 1);
        if (document.getElementById("btnMS2")) document.getElementById("btnMS2").classList.toggle("active", id === 2);
        pendingChanges = {}; currentPage = 1;
        resetFiltersAndLoad();
    }

    function goBack()  { window.location.href = "index.php?lokasi=" + currentLokasi; }
    function logout()  { if (confirm("Yakin ingin logout?")) window.location.href = "signout.php"; }

    // ══════════════════════════════════════════════════════════════════════════
    // EXCEL IMPORT FOR MONTHLY PLAN
    // ══════════════════════════════════════════════════════════════════════════

    function handleExcelImport(input) {
        const file = input.files[0];
        if (!file) return;

        document.getElementById("importInfo").classList.add("show");
        showSnackbar("📊 Membaca file Excel...");

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });

                // Parse data: skip header row (index 0), column 0 = MODEL, column 1 = Qty
                let imported = 0;
                let notFound = 0;
                const importedModels = [];

                for (let i = 1; i < jsonData.length; i++) {
                    const row = jsonData[i];
                    if (!row || row.length < 2) continue;

                    const modelName = String(row[0] || '').trim();
                    const qty = parseInt(row[1]) || 0;

                    if (!modelName) continue;

                    // Find matching model_item_id in allData
                    const matches = allData.filter(d => 
                        d.model_name.toLowerCase() === modelName.toLowerCase()
                    );

                    if (matches.length > 0) {
                        matches.forEach(match => {
                            const orig = parseInt(match.monthly_plan) || 0;
                            if (qty !== orig) {
                                mpChanges[match.model_item_id] = qty;
                            }
                        });
                        imported += matches.length;
                        importedModels.push(`${modelName} → ${qty}`);
                    } else {
                        notFound++;
                    }
                }

                // Update UI
                renderMpList();
                updateMpChangedBadge();

                // Show summary
                if (imported > 0) {
                    showSnackbar(`✔ ${imported} baris diimpor dari Excel (${notFound} model tidak ditemukan)`);
                    console.log("Imported:", importedModels.join(", "));
                } else {
                    showSnackbar("⚠ Tidak ada data yang cocok dari Excel");
                }

            } catch (err) {
                console.error("Excel import error:", err);
                showSnackbar("❌ Gagal membaca file Excel");
            }

            // Reset file input
            input.value = '';
        };

        reader.readAsArrayBuffer(file);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MONTHLY PLAN MANAGER MODAL
    // ══════════════════════════════════════════════════════════════════════════

    function openMpModal() {
        mpChanges = {};

        const sel = document.getElementById("mpModelFilter");
        sel.innerHTML = '<option value="">— Semua Model —</option>';
        [...new Set(allData.map(d => d.model_name))].sort().forEach(m => {
            const o = document.createElement("option");
            o.value = m; o.textContent = m;
            sel.appendChild(o);
        });

        document.getElementById("mpSearch").value = "";
        document.getElementById("importInfo").classList.remove("show");
        renderMpList();
        updateMpChangedBadge();
        document.getElementById("mpModal").style.display = "flex";
    }

    function closeMpModal() {
        document.getElementById("mpModal").style.display = "none";
    }

    function renderMpList() {
        const modelFilter = document.getElementById("mpModelFilter").value.toLowerCase();
        const search      = document.getElementById("mpSearch").value.toLowerCase();

        let rows = allData.filter(d =>
            (!modelFilter || d.model_name.toLowerCase() === modelFilter) &&
            (!search ||
                d.model_name.toLowerCase().includes(search) ||
                d.part_name.toLowerCase().includes(search) ||
                d.part_number.toLowerCase().includes(search))
        );

        const list  = document.getElementById("mpPartList");
        const total = rows.length;
        let filled = 0;

        if (!rows.length) {
            list.innerHTML = `<div class="mp-empty">Tidak ada data ditemukan.</div>`;
            updateMpStats(0, 0, 0, 0);
            return;
        }

        let totalPlan = 0;
        rows.forEach(d => {
            const val = mpChanges[d.model_item_id] !== undefined
                ? mpChanges[d.model_item_id]
                : parseInt(d.monthly_plan) || 0;
            if (val > 0) filled++;
            totalPlan += val;
        });

        list.innerHTML = "";
        const frag = document.createDocumentFragment();

        rows.forEach(d => {
            const currentVal = mpChanges[d.model_item_id] !== undefined
                ? mpChanges[d.model_item_id]
                : parseInt(d.monthly_plan) || 0;
            const isChanged  = mpChanges[d.model_item_id] !== undefined;
            const ss         = parseInt(d.safety_stock) || 0;

            const itemTotal = allData
                .filter(x => x.item_id === d.item_id)
                .reduce((sum, x) => sum + (mpChanges[x.model_item_id] !== undefined
                    ? mpChanges[x.model_item_id]
                    : parseInt(x.monthly_plan) || 0), 0);
            const pctStr = itemTotal > 0
                ? ((currentVal / itemTotal) * 100).toFixed(1) + "%"
                : "—";

            const div = document.createElement("div");
            div.className = "mp-part-row" + (isChanged ? " changed" : "");
            div.dataset.id = d.model_item_id;
            div.innerHTML = `
                <div class="mp-part-info">
                    <div class="mp-part-name">${d.model_name}</div>
                    <div class="mp-part-num">${d.part_name} · ${d.part_number}</div>
                </div>
                <span class="mp-part-ss">SS: ${ss}</span>
                <div class="mp-input-wrap">
                    <label>Plan/bln</label>
                    <input type="number" class="mp-modal-input ${isChanged ? 'changed' : ''}"
                        value="${currentVal}" min="0" step="1"
                        data-id="${d.model_item_id}"
                        data-item-id="${d.item_id}"
                        data-original="${parseInt(d.monthly_plan) || 0}"
                        oninput="onMpModalInput(this)">
                </div>
                <div class="mp-pct-live" id="mppct-${d.model_item_id}">${pctStr}</div>
            `;
            frag.appendChild(div);
        });

        list.appendChild(frag);
        updateMpStats(total, totalPlan, filled, total - filled);
        document.getElementById("mpFooterInfo").textContent =
            `${total} baris ditampilkan · ${Object.keys(mpChanges).length} perubahan pending`;
    }

    function onMpModalInput(input) {
        const id     = input.dataset.id;
        const itemId = input.dataset.itemId;
        const orig   = parseInt(input.dataset.original) || 0;
        let   val    = parseInt(input.value) || 0;
        if (val < 0) val = 0;

        if (val !== orig) {
            mpChanges[id] = val;
            input.classList.add("changed");
            input.closest(".mp-part-row").classList.add("changed");
        } else {
            delete mpChanges[id];
            input.classList.remove("changed");
            input.closest(".mp-part-row").classList.remove("changed");
        }

        // Live update % for same item_id rows
        const itemTotal = allData
            .filter(x => x.item_id == itemId)
            .reduce((sum, x) => sum + (mpChanges[x.model_item_id] !== undefined
                ? mpChanges[x.model_item_id]
                : parseInt(x.monthly_plan) || 0), 0);

        allData.filter(x => x.item_id == itemId).forEach(x => {
            const el = document.getElementById("mppct-" + x.model_item_id);
            if (!el) return;
            const xVal = mpChanges[x.model_item_id] !== undefined
                ? mpChanges[x.model_item_id]
                : parseInt(x.monthly_plan) || 0;
            el.textContent = itemTotal > 0
                ? ((xVal / itemTotal) * 100).toFixed(1) + "%"
                : "—";
        });

        updateMpChangedBadge();
        document.getElementById("mpFooterInfo").textContent =
            `${document.querySelectorAll(".mp-part-row").length} baris · ${Object.keys(mpChanges).length} perubahan pending`;
    }

    function applyMpBulk() {
        const val = parseInt(document.getElementById("mpBulkVal").value);
        if (isNaN(val) || val < 0) { showSnackbar("⚠ Masukkan angka yang valid"); return; }

        document.querySelectorAll(".mp-modal-input").forEach(input => {
            const orig = parseInt(input.dataset.original) || 0;
            input.value = val;
            if (val !== orig) {
                mpChanges[input.dataset.id] = val;
                input.classList.add("changed");
                input.closest(".mp-part-row").classList.add("changed");
            } else {
                delete mpChanges[input.dataset.id];
                input.classList.remove("changed");
                input.closest(".mp-part-row").classList.remove("changed");
            }
        });

        renderMpList();
        updateMpChangedBadge();
        showSnackbar(`✔ ${Object.keys(mpChanges).length} baris diset ke ${val}`);
    }

    function resetMpAll() {
        document.querySelectorAll(".mp-modal-input").forEach(input => {
            input.value = 0;
            const orig = parseInt(input.dataset.original) || 0;
            if (orig !== 0) {
                mpChanges[input.dataset.id] = 0;
                input.classList.add("changed");
                input.closest(".mp-part-row").classList.add("changed");
            } else {
                delete mpChanges[input.dataset.id];
                input.classList.remove("changed");
                input.closest(".mp-part-row").classList.remove("changed");
            }
        });
        renderMpList();
        updateMpChangedBadge();
    }

    function updateMpStats(total, sum, filled, empty) {
        document.getElementById("mpStatTotal").textContent  = total;
        document.getElementById("mpStatSum").textContent    = sum;
        document.getElementById("mpStatFilled").textContent = filled;
        document.getElementById("mpStatEmpty").textContent  = empty;
    }

    function updateMpChangedBadge() {
        const c = Object.keys(mpChanges).length;
        const b = document.getElementById("mpChangedCount");
        b.textContent = c;
        b.style.display = c > 0 ? "inline" : "none";
        document.getElementById("btnMpSave").disabled = c === 0;
    }

    function saveMpChanges() {
        if (!Object.keys(mpChanges).length) return;

        const btn = document.getElementById("btnMpSave");
        btn.disabled = true;
        btn.textContent = "⟳ Menyimpan...";

        const payload = Object.entries(mpChanges).map(([id, val]) => ({
            model_item_id: parseInt(id),
            monthly_plan : val
        }));

        fetch("./api/save_monthly_plan.php", {
            method : "POST",
            headers: { "Content-Type": "application/json" },
            body   : JSON.stringify({ plans: payload })
        })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            btn.textContent = "💾 Simpan Monthly Plan";
            if (res.success) {
                showSnackbar(`✔ Monthly Plan tersimpan! ${res.updated} baris diperbarui.`);
                mpChanges = {};
                closeMpModal();
                loadData();
            } else {
                showSnackbar("❌ " + (res.message || "Gagal menyimpan"));
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = "💾 Simpan Monthly Plan";
            showSnackbar("❌ Connection error");
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TRANSFER STOCK MODAL
    // ══════════════════════════════════════════════════════════════════════════

    function openTransferModal() {
        const sel = document.getElementById("trPartSelect");
        sel.innerHTML = '<option value="">— Pilih Part —</option>';

        const partMap = {};
        allData.forEach(d => {
            if (!partMap[d.item_id]) partMap[d.item_id] = { name: d.part_name, count: 0 };
            partMap[d.item_id].count++;
        });

        Object.entries(partMap)
            .filter(([, v]) => v.count >= 2)
            .sort((a, b) => a[1].name.localeCompare(b[1].name))
            .forEach(([id, v]) => {
                const o = document.createElement("option");
                o.value = id; o.textContent = v.name;
                sel.appendChild(o);
            });

        resetTransferModal();
        document.getElementById("transferModal").style.display = "flex";
    }

    function closeTransferModal() {
        document.getElementById("transferModal").style.display = "none";
    }

    function resetTransferModal() {
        trData = { partItemId: null, fromId: null, toId: null, fromStock: 0, toStock: 0 };
        document.getElementById("trFromModel").innerHTML = '<option value="">— Pilih Model —</option>';
        document.getElementById("trToModel").innerHTML   = '<option value="">— Pilih Model —</option>';
        document.getElementById("trFromStock").textContent = "—";
        document.getElementById("trToStock").textContent   = "—";
        document.getElementById("trMaxQty").textContent    = "—";
        document.getElementById("trQty").value = 1;
        document.getElementById("trPreview").classList.remove("show");
        document.getElementById("btnDoTransfer").disabled = true;
    }

    function onTrPartChange() {
        const itemId = document.getElementById("trPartSelect").value;
        if (!itemId) { resetTransferModal(); return; }

        trData.partItemId = parseInt(itemId);
        trData.fromId = null; trData.toId = null;

        const models  = allData.filter(d => d.item_id == itemId);
        const fromSel = document.getElementById("trFromModel");
        const toSel   = document.getElementById("trToModel");

        fromSel.innerHTML = '<option value="">— Pilih Model —</option>';
        toSel.innerHTML   = '<option value="">— Pilih Model —</option>';

        models.forEach(m => {
            const allocSt = parseFloat(m.allocated_stock) || 0;
            const o1 = document.createElement("option");
            o1.value = m.model_item_id;
            o1.textContent = `${m.model_name}  (Alloc: ${allocSt})`;
            o1.dataset.stock = allocSt;
            fromSel.appendChild(o1);

            const o2 = o1.cloneNode(true);
            toSel.appendChild(o2);
        });

        document.getElementById("trFromStock").textContent = "—";
        document.getElementById("trToStock").textContent   = "—";
        document.getElementById("trPreview").classList.remove("show");
        document.getElementById("btnDoTransfer").disabled = true;
    }

    function onTrModelChange() {
        const fromSel = document.getElementById("trFromModel");
        const toSel   = document.getElementById("trToModel");
        const fromVal = fromSel.value;
        const toVal   = toSel.value;

        if (fromVal) {
            const opt = fromSel.options[fromSel.selectedIndex];
            trData.fromId    = parseInt(fromVal);
            trData.fromStock = parseFloat(opt.dataset.stock) || 0;
            document.getElementById("trFromStock").textContent = trData.fromStock + " unit";
            document.getElementById("trMaxQty").textContent    = trData.fromStock;
        }

        if (toVal) {
            const opt = toSel.options[toSel.selectedIndex];
            trData.toId    = parseInt(toVal);
            trData.toStock = parseFloat(opt.dataset.stock) || 0;
            document.getElementById("trToStock").textContent = trData.toStock + " unit";
        }

        const maxQ = trData.fromStock;
        const curQ = parseInt(document.getElementById("trQty").value) || 1;
        if (curQ > maxQ) document.getElementById("trQty").value = maxQ;

        updateTransferPreview();
    }

    function stepQty(delta) {
        const input = document.getElementById("trQty");
        let val = (parseInt(input.value) || 0) + delta;
        val = Math.max(1, Math.min(val, trData.fromStock || 1));
        input.value = val;
        updateTransferPreview();
    }

    function onTrQtyChange() { updateTransferPreview(); }

    function updateTransferPreview() {
        const qty = parseInt(document.getElementById("trQty").value) || 0;

        if (!trData.fromId || !trData.toId || trData.fromId === trData.toId || qty <= 0) {
            document.getElementById("trPreview").classList.remove("show");
            document.getElementById("btnDoTransfer").disabled = true;
            return;
        }

        if (qty > trData.fromStock) {
            document.getElementById("trQty").value = trData.fromStock;
            return;
        }

        const fromRow = allData.find(d => d.model_item_id == trData.fromId);
        const toRow   = allData.find(d => d.model_item_id == trData.toId);
        const partRow = allData.find(d => d.item_id == trData.partItemId);

        const fromAfter = trData.fromStock - qty;
        const toAfter   = trData.toStock   + qty;
        const total     = partRow ? parseFloat(partRow.current_stock) : 0;

        document.getElementById("pvPart").textContent     = partRow ? partRow.part_name : "—";
        document.getElementById("pvFromName").textContent = fromRow ? fromRow.model_name : "—";
        document.getElementById("pvToName").textContent   = toRow   ? toRow.model_name   : "—";
        document.getElementById("pvFromAfter").textContent = fromAfter + " unit";
        document.getElementById("pvToAfter").textContent   = toAfter   + " unit";
        document.getElementById("pvTotal").textContent     = total + " unit (tidak berubah)";

        document.getElementById("trPreview").classList.add("show");
        document.getElementById("btnDoTransfer").disabled = false;
    }

    function doTransfer() {
        const qty = parseInt(document.getElementById("trQty").value) || 0;
        if (!trData.fromId || !trData.toId || qty <= 0) return;

        const btn = document.getElementById("btnDoTransfer");
        btn.disabled = true;
        btn.textContent = "⟳ Mentransfer...";

        fetch("./api/transfer_stock.php", {
            method : "POST",
            headers: { "Content-Type": "application/json" },
            body   : JSON.stringify({
                from_model_item_id: trData.fromId,
                to_model_item_id  : trData.toId,
                qty               : qty
            })
        })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            btn.textContent = "🔀 Lakukan Transfer";
            if (res.success) {
                showSnackbar(`✔ Transfer berhasil! ${qty} unit dipindah.`);
                closeTransferModal();
                loadData();
            } else {
                showSnackbar("❌ " + (res.message || "Transfer gagal"));
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = "🔀 Lakukan Transfer";
            showSnackbar("❌ Connection error");
        });
    }
</script>
</body>
</html>