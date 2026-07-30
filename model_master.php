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
    <title>Model & Routing - PT. YADIN</title>

    <script src="sidebar.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:Segoe UI,sans-serif; }
        body { background:#f7f8fa; }

        /* MAIN */
        .main { flex:1; padding:24px 28px; overflow:auto; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px; }
        .topbar h2 { font-size:17px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px; }
        .top-actions { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }

        /* BUTTONS */
        .btn-nav { padding:7px 14px; background:#fff; color:#475569; border:1px solid #e2e8f0; border-radius:7px; font-size:12px; cursor:pointer; transition:.15s; display:inline-flex; align-items:center; gap:4px; }
        .btn-nav:hover { background:#f1f5f9; }
        .btn-nav:active { transform:scale(.96); }
        .btn-logout { padding:7px 14px; background:#fff; color:#dc2626; border:1px solid #fecaca; border-radius:7px; font-size:12px; cursor:pointer; transition:.15s; }
        .btn-logout:hover { background:#fef2f2; }
        .btn-logout:active { transform:scale(.96); }
        .btn-add { padding:7px 14px; background:#4f46e5; color:#fff; border:none; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer; transition:.15s; display:inline-flex; align-items:center; gap:5px; }
        .btn-add:hover { background:#4338ca; transform:translateY(-1px); box-shadow:0 4px 10px rgba(79,70,229,.25); }
        .btn-add:active { transform:translateY(0) scale(.96); box-shadow:none; }

        /* icon action buttons */
        .btn-icon { width:26px; height:26px; border-radius:7px; border:1px solid #e2e8f0; background:#fff; cursor:pointer; font-size:12px; display:inline-flex; align-items:center; justify-content:center; transition:.15s; color:#64748b; flex-shrink:0; }
        .btn-icon:hover { background:#f1f5f9; transform:translateY(-1px); }
        .btn-icon:active { transform:translateY(0) scale(.92); }
        .btn-icon.danger:hover { background:#1e1e1e; border-color:#1e1e1e; color:#fff; }

        /* vector icon system — hitam-putih saja, no fill color */
        .icon { width:16px; height:16px; flex-shrink:0; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; vertical-align:-3px; }
        .icon-sm { width:13px; height:13px; }
        .icon-lg { width:20px; height:20px; }
        h2 .icon, .section-head h3 .icon { width:18px; height:18px; color:#1e293b; }
        .btn-nav .icon, .btn-add .icon, .btn-cancel-modal .icon, .modal-close .icon { vertical-align:-2px; }
        .modal-close { display:inline-flex; }

        /* ══════════════════════════════════════════════════════
           STATS OVERVIEW
        ══════════════════════════════════════════════════════ */
        .stats-row { display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:12px; margin-bottom:20px; }
        .stat-tile { background:#fff; border:1px solid #eef0f3; border-radius:12px; padding:14px 16px; display:flex; align-items:center; gap:12px; transition:.2s; animation:cardIn .35s ease backwards; }
        .stat-tile:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(15,23,42,.06); border-color:#e0e7ff; }
        .stat-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:#f1f5f9; color:#334155; }
        .stat-icon .icon { width:19px; height:19px; }
        .stat-tile.c-amber .stat-icon { background:#1e293b; color:#fff; }
        .stat-info { display:flex; flex-direction:column; min-width:0; }
        .stat-value { font-size:19px; font-weight:800; color:#1e293b; line-height:1.2; }
        .stat-value.warn { color:#d97706; }
        .stat-label { font-size:11px; color:#94a3b8; font-weight:600; white-space:nowrap; }

        /* SECTIONS */
        .section-card { background:#fff; border-radius:12px; border:1px solid #eef0f3; padding:20px; margin-bottom:20px; }
        .section-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px; }
        .section-head h3 { font-size:14.5px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:6px; }
        .section-head .hint { font-size:12px; color:#94a3b8; font-weight:400; margin-left:2px; }
        .section-toolbar { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
        .section-toolbar input[type=text] { padding:7px 12px; border:1px solid #e2e8f0; border-radius:7px; font-size:12.5px; width:220px; background:#fafbfc; transition:.15s; }
        .section-toolbar input[type=text]:focus { outline:none; border-color:#a5b4fc; background:#fff; box-shadow:0 0 0 3px rgba(165,180,252,.2); width:260px; }

        /* TABLE (Model Engine) */
        .table-container { overflow:auto; max-height:420px; border-radius:10px; border:1px solid #f1f5f9; }
        table.data-table { width:100%; border-collapse:collapse; font-size:12.5px; }
        table.data-table thead { background:#fafbfc; position:sticky; top:0; z-index:2; }
        table.data-table th { padding:9px 12px; font-size:10.5px; font-weight:700; letter-spacing:.3px; text-transform:uppercase; color:#94a3b8; border-bottom:1px solid #eef0f3; text-align:left; white-space:nowrap; }
        table.data-table td { padding:8px 12px; border-bottom:1px solid #f5f6f8; color:#334155; vertical-align:middle; }
        table.data-table tbody tr { transition:background .12s; }
        table.data-table tbody tr:hover { background:#fafbff; }
        .col-center { text-align:center !important; }
        .empty-row td { text-align:center; padding:32px; color:#94a3b8; }
        .empty-state-icon { width:28px; height:28px; display:block; margin:0 auto 8px; opacity:.4; stroke-width:1.6; }
        .row-actions { display:flex; gap:6px; }

        .count-text { color:#64748b; font-size:11.5px; white-space:nowrap; }
        .count-text.zero { color:#c3c9d4; }

        mark.hl { background:#fef08a; color:#713f12; padding:0 2px; border-radius:3px; font-weight:700; }

        /* Skeleton loading */
        @keyframes shimmer { 0%{background-position:-300px 0} 100%{background-position:300px 0} }
        .skeleton { background:linear-gradient(90deg,#f1f5f9 25%,#e8ebf0 37%,#f1f5f9 63%); background-size:400px 100%; animation:shimmer 1.3s ease infinite; border-radius:5px; display:inline-block; }
        .skeleton-row td { padding:11px 12px; }
        .skeleton-bar { height:11px; width:70%; }
        .skeleton-card { height:120px; border-radius:10px; border:1px solid #eef0f3; }

        /* ══════════════════════════════════════════════════════
           ROUTE CARDS (Routing Part <-> Model)
        ══════════════════════════════════════════════════════ */
        .route-grid-container { max-height:640px; overflow:auto; border-radius:10px; padding:2px; }
        .route-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:12px; }
        .route-empty-global { text-align:center; padding:48px 20px; color:#94a3b8; grid-column:1/-1; }
        .route-empty-global .empty-state-icon { width:34px; height:34px; }

        .route-card { border:1px solid #eef0f3; border-radius:10px; background:#fafbfc; display:flex; flex-direction:column; overflow:hidden; transition:transform .15s ease, box-shadow .15s ease, border-color .15s ease; animation:cardIn .3s ease backwards; }
        .route-card:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(15,23,42,.07); border-color:#e0e7ff; }
        .route-card.is-empty { border-style:dashed; border-color:#fde68a; background:#fffdf5; }
        @keyframes cardIn { from{opacity:0; transform:translateY(8px)} to{opacity:1; transform:translateY(0)} }

        .route-card-header { display:flex; align-items:center; justify-content:space-between; gap:8px; padding:9px 10px 9px 12px; background:#fff; border-bottom:1px solid #eef0f3; }
        .route-card-title { display:flex; align-items:baseline; gap:8px; min-width:0; }
        .route-card-title .model-name { font-size:13px; font-weight:700; color:#1e293b; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .btn-add-mini { width:24px; height:24px; border-radius:6px; border:1px dashed #c7d2fe; background:#eef2ff; color:#4f46e5; cursor:pointer; display:flex; align-items:center; justify-content:center; line-height:1; flex-shrink:0; transition:.15s; }
        .btn-add-mini .icon { width:14px; height:14px; }
        .btn-add-mini:hover { background:#e0e7ff; border-style:solid; transform:rotate(90deg); }
        .btn-add-mini:active { transform:rotate(90deg) scale(.9); }

        .route-list { position:relative; list-style:none; margin:0; padding:8px 12px 8px 16px; margin-left:16px; max-height:220px; overflow:auto; }

        .route-item { display:flex; align-items:center; gap:6px; padding:4px 0; border-radius:6px; transition:background .12s; }
        .route-item:hover { background:#f1f5ff; }
        .route-item:hover .ri-connector path { stroke:#4f46e5; animation-play-state:paused; }
        .route-item .ri-connector { flex-shrink:0; overflow:visible; }
        .route-item .ri-connector path {
            stroke-dasharray:4 3;
            animation:flowDash .7s linear infinite;
            animation-delay:calc(var(--wi, 0) * .1s);
        }
        @keyframes flowDash { to { stroke-dashoffset:-14; } }
        .route-item .ri-part { flex:1; min-width:0; display:flex; flex-direction:column; line-height:1.3; }
        .route-item .ri-partnum { font-size:12px; font-weight:600; color:#1e293b; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .route-item .ri-partname { font-size:10.5px; color:#94a3b8; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .route-item .ri-qty { font-size:10.5px; color:#94a3b8; background:#fff; border:1px solid #eef0f3; padding:1px 6px; border-radius:5px; flex-shrink:0; }
        .route-empty { padding:14px 14px 14px 24px; color:#c3c9d4; font-size:12px; font-style:italic; }

        /* custom scrollbars */
        .table-container::-webkit-scrollbar, .route-grid-container::-webkit-scrollbar, .route-list::-webkit-scrollbar { width:8px; height:8px; }
        .table-container::-webkit-scrollbar-thumb, .route-grid-container::-webkit-scrollbar-thumb, .route-list::-webkit-scrollbar-thumb { background:#e2e8f0; border-radius:10px; }
        .table-container::-webkit-scrollbar-thumb:hover, .route-grid-container::-webkit-scrollbar-thumb:hover, .route-list::-webkit-scrollbar-thumb:hover { background:#c7d2fe; }

        /* MODAL (shared pattern) */
        .modal-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.45); display:none; justify-content:center; align-items:center; z-index:9998; animation:fadeIn .15s ease; }
        .modal-box { background:#fff; width:420px; max-width:95vw; border-radius:14px; box-shadow:0 20px 50px rgba(15,23,42,.18); overflow:hidden; animation:slideUp .18s ease; }
        .modal-header { padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; }
        .modal-header h3 { font-size:15px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:6px; }
        .modal-close { font-size:20px; cursor:pointer; color:#94a3b8; line-height:1; transition:.15s; }
        .modal-close:hover { color:#334155; transform:rotate(90deg); }
        .modal-body { padding:18px 20px; }
        .modal-footer { padding:12px 20px; border-top:1px solid #f1f5f9; display:flex; justify-content:flex-end; gap:8px; }
        .form-group { margin-bottom:14px; }
        .form-group label { font-size:11.5px; font-weight:600; color:#64748b; display:block; margin-bottom:5px; }
        .form-group input, .form-group select { width:100%; padding:8px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; color:#1e293b; transition:.15s; }
        .form-group input:focus, .form-group select:focus { border-color:#a5b4fc; outline:none; box-shadow:0 0 0 3px rgba(165,180,252,.2); }
        .form-error { font-size:12px; color:#dc2626; margin-top:6px; display:none; }
        .btn-save-modal { padding:8px 18px; background:#4f46e5; color:#fff; border:none; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer; transition:.15s; }
        .btn-save-modal:hover { background:#4338ca; transform:translateY(-1px); box-shadow:0 4px 10px rgba(79,70,229,.25); }
        .btn-save-modal:active { transform:translateY(0) scale(.96); box-shadow:none; }
        .btn-cancel-modal { padding:8px 16px; background:#f1f5f9; color:#475569; border:none; border-radius:8px; font-size:12.5px; cursor:pointer; font-weight:600; transition:.15s; }
        .btn-cancel-modal:hover { background:#e2e8f0; }
        .btn-cancel-modal:active { transform:scale(.96); }

        @keyframes fadeIn  { from{opacity:0} to{opacity:1} }
        @keyframes slideUp { from{transform:translateY(12px);opacity:0} to{transform:translateY(0);opacity:1} }

        /* TOAST CONFIRM */
        .toast-confirm { position:fixed; inset:0; background:rgba(15,23,42,0.35); display:none; justify-content:center; align-items:center; z-index:9999; }
        .toast-box { background:white; padding:20px 24px; border-radius:12px; width:340px; text-align:center; box-shadow:0 20px 50px rgba(15,23,42,.2); animation:scaleIn .15s ease; }
        .toast-box .toast-sub { font-size:12px; color:#64748b; margin-top:6px; }
        .toast-actions { margin-top:15px; display:flex; justify-content:center; gap:10px; }
        .btn-yes { background:#dc2626; color:white; border:none; padding:7px 18px; border-radius:7px; cursor:pointer; font-weight:600; font-size:12.5px; transition:.15s; }
        .btn-yes:hover { background:#b91c1c; }
        .btn-yes:active { transform:scale(.96); }
        .btn-no  { background:#f1f5f9; color:#475569; border:none; padding:7px 18px; border-radius:7px; cursor:pointer; font-size:12.5px; transition:.15s; }
        .btn-no:hover { background:#e2e8f0; }
        .btn-no:active { transform:scale(.96); }
        @keyframes scaleIn { from{transform:scale(.94);opacity:0} to{transform:scale(1);opacity:1} }

        /* SNACKBAR */
        #snackbar { visibility:hidden; min-width:250px; background:#1e293b; color:white; border-radius:8px; padding:12px 20px; position:fixed; z-index:9999; bottom:30px; left:50%; transform:translateX(-50%); font-size:13px; font-weight:600; box-shadow:0 10px 30px rgba(0,0,0,.2); display:flex; align-items:center; gap:8px; justify-content:center; }
        #snackbar .icon { color:#fff; flex-shrink:0; }
        #snackbar.show { visibility:visible; animation:fadeInUp .3s cubic-bezier(.34,1.56,.64,1), fadeOut .5s ease 2.5s forwards; }
        @keyframes fadeInUp { from{opacity:0;transform:translateX(-50%) translateY(20px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }
        @keyframes fadeOut  { to{opacity:0} }
    </style>
</head>
<body>
<svg width="0" height="0" style="position:absolute">
    <defs>
        <marker id="flowArrowhead" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
            <path d="M1,1 L9,5 L1,9 Z" fill="#a5b4fc"/>
        </marker>
    </defs>
</svg>
<div class="layout">

    <?php include 'sidebar.php'; ?>

    <main class="main">
        <header class="topbar">
            <h2><svg class="icon" viewBox="0 0 24 24"><path d="M4 7h3a1 1 0 0 0 1 -1v-1a2 2 0 0 1 4 0v1a1 1 0 0 0 1 1h3a1 1 0 0 1 1 1v3a1 1 0 0 0 1 1h1a2 2 0 0 1 0 4h-1a1 1 0 0 0 -1 1v3a1 1 0 0 1 -1 1h-3a1 1 0 0 1 -1 -1v-1a2 2 0 0 0 -4 0v1a1 1 0 0 1 -1 1h-3a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1h1a2 2 0 0 0 0 -4h-1a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1"/></svg> Model & Routing <span class="hint" style="font-weight:400;color:#94a3b8;font-size:13px;">MS<?php echo $lokasi; ?></span></h2>
            <div class="top-actions">
                <button class="btn-nav" onclick="goBack()"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M15 6l-6 6l6 6"/></svg> Back</button>
                <button class="btn-logout" onclick="logout()">Logout</button>
            </div>
        </header>

        <!-- STATS OVERVIEW -->
        <section class="stats-row" id="statsRow">
            <div class="stat-tile c-indigo" style="animation-delay:0ms">
                <div class="stat-icon"><svg class="icon" viewBox="0 0 24 24"><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/></svg></div>
                <div class="stat-info">
                    <span class="stat-value" id="statTotalModel">–</span>
                    <span class="stat-label">Total Model</span>
                </div>
            </div>
            <div class="stat-tile c-sky" style="animation-delay:40ms">
                <div class="stat-icon"><svg class="icon" viewBox="0 0 24 24"><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg></div>
                <div class="stat-info">
                    <span class="stat-value" id="statTotalPart">–</span>
                    <span class="stat-label">Part Number (MS<?php echo $lokasi; ?>)</span>
                </div>
            </div>
            <div class="stat-tile c-emerald" style="animation-delay:80ms">
                <div class="stat-icon"><svg class="icon" viewBox="0 0 24 24"><path d="M9 15l6 -6"/><path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464"/><path d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463"/></svg></div>
                <div class="stat-info">
                    <span class="stat-value" id="statTotalRouting">–</span>
                    <span class="stat-label">Total Routing</span>
                </div>
            </div>
            <div class="stat-tile c-amber" style="animation-delay:120ms">
                <div class="stat-icon"><svg class="icon" viewBox="0 0 24 24"><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"/><path d="M12 16h.01"/></svg></div>
                <div class="stat-info">
                    <span class="stat-value warn" id="statNoPart">–</span>
                    <span class="stat-label">Model Belum Ada Part</span>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════
             SECTION 1: MODEL ENGINE
        ═══════════════════════════════════ -->
        <section class="section-card">
            <div class="section-head">
                <h3><svg class="icon" viewBox="0 0 24 24"><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/></svg> Model Engine <span class="hint">nama-nama model produksi</span></h3>
                <div class="section-toolbar">
                    <input type="text" id="modelSearch" placeholder="Cari nama model..." oninput="renderModelTable()">
                    <button class="btn-add" onclick="openModelModal()"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg> Tambah Model</button>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:40px">No</th>
                            <th>Nama Model</th>
                            <th style="width:120px" class="col-center">Part Terhubung</th>
                            <th style="width:80px" class="col-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="modelTableBody">
                        <tr class="skeleton-row"><td><span class="skeleton skeleton-bar" style="width:16px"></span></td><td><span class="skeleton skeleton-bar" style="width:60%"></span></td><td class="col-center"><span class="skeleton skeleton-bar" style="width:40px"></span></td><td class="col-center"><span class="skeleton skeleton-bar" style="width:50px"></span></td></tr>
                        <tr class="skeleton-row"><td><span class="skeleton skeleton-bar" style="width:16px"></span></td><td><span class="skeleton skeleton-bar" style="width:45%"></span></td><td class="col-center"><span class="skeleton skeleton-bar" style="width:40px"></span></td><td class="col-center"><span class="skeleton skeleton-bar" style="width:50px"></span></td></tr>
                        <tr class="skeleton-row"><td><span class="skeleton skeleton-bar" style="width:16px"></span></td><td><span class="skeleton skeleton-bar" style="width:55%"></span></td><td class="col-center"><span class="skeleton skeleton-bar" style="width:40px"></span></td><td class="col-center"><span class="skeleton skeleton-bar" style="width:50px"></span></td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ═══════════════════════════════════
             SECTION 2: ROUTING PART <-> MODEL (route cards)
        ═══════════════════════════════════ -->
        <section class="section-card">
            <div class="section-head">
                <h3><svg class="icon" viewBox="0 0 24 24"><path d="M9 15l6 -6"/><path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464"/><path d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463"/></svg> Routing Model → Part <span class="hint">tiap model dipakai untuk part apa saja</span></h3>
                <div class="section-toolbar">
                    <input type="text" id="routingSearch" placeholder="Cari model / part number / nama..." oninput="renderRoutingCards()">
                    <button class="btn-add" onclick="openRoutingModal()"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg> Tambah Routing</button>
                </div>
            </div>
            <div class="route-grid-container">
                <div class="route-grid" id="routingCardsGrid">
                    <div class="skeleton skeleton-card"></div>
                    <div class="skeleton skeleton-card"></div>
                    <div class="skeleton skeleton-card"></div>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- MODEL ADD/EDIT MODAL -->
<div id="modelModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modelModalTitle">Tambah Model</h3>
            <span class="modal-close" onclick="closeModelModal()"><svg class="icon" viewBox="0 0 24 24"><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg></span>
        </div>
        <div class="modal-body">
            <input type="hidden" id="modelIdInput">
            <div class="form-group">
                <label>Nama Model</label>
                <input type="text" id="modelNameInput" placeholder="Contoh: TF65-H" maxlength="50">
                <div class="form-error" id="modelFormError"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="closeModelModal()">Batal</button>
            <button class="btn-save-modal" onclick="saveModel()">Simpan</button>
        </div>
    </div>
</div>

<!-- TAMBAH ROUTING MODAL (pilih model dulu, lalu part) -->
<div id="routingModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg> Tambah Routing</h3>
            <span class="modal-close" onclick="closeRoutingModal()"><svg class="icon" viewBox="0 0 24 24"><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg></span>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Model</label>
                <select id="routingModelSelect" onchange="onRoutingModelChange()"></select>
            </div>
            <div class="form-group">
                <label>Part Number</label>
                <select id="routingPartSelect"></select>
            </div>
            <div class="form-group">
                <label>Usage Qty (jumlah part per unit model)</label>
                <input type="number" id="routingQtyInput" value="1" min="1">
            </div>
            <div class="form-error" id="routingFormError"></div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="closeRoutingModal()">Batal</button>
            <button class="btn-save-modal" onclick="saveRouting()">Simpan</button>
        </div>
    </div>
</div>

<!-- CONFIRM TOAST -->
<div id="toastConfirm" class="toast-confirm">
    <div class="toast-box">
        <p id="toastMessage">Yakin?</p>
        <div class="toast-sub" id="toastSub"></div>
        <div class="toast-actions">
            <button id="toastYesBtn" class="btn-yes">Ya, Hapus</button>
            <button onclick="closeToast()" class="btn-no">Batal</button>
        </div>
    </div>
</div>

<div id="snackbar"></div>

<script>
    // Ikon vector hitam-putih (line icon, currentColor) dipakai di baris tabel/card yang di-render JS
    const ICON = {
        pencil: '<svg class="icon" viewBox="0 0 24 24"><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/><path d="M13.5 6.5l4 4"/></svg>',
        trash:  '<svg class="icon" viewBox="0 0 24 24"><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>',
        search: '<svg class="icon empty-state-icon" viewBox="0 0 24 24"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>',
        plus:   '<svg class="icon" viewBox="0 0 24 24"><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>',
        check:  '<svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M5 12l5 5l10 -10"/></svg>',
        alert:  '<svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"/><path d="M12 16h.01"/></svg>',
    };

    let currentLokasi = <?php echo $lokasi; ?>;
    let models  = [];
    let routing = [];
    let pendingConfirmFn = null;

    document.addEventListener("DOMContentLoaded", () => {
        loadModels(() => loadRouting());
    });

    function showSnackbar(msg, type) {
        const sb = document.getElementById("snackbar");
        const icon = type === "error" ? ICON.alert : ICON.check;
        sb.innerHTML = icon + '<span>' + escapeHtml(msg) + '</span>';
        sb.className = "show";
        setTimeout(() => sb.className = "", 3200);
    }

    function askConfirm(msg, sub, fn) {
        document.getElementById("toastMessage").textContent = msg;
        document.getElementById("toastSub").textContent = sub || "";
        pendingConfirmFn = fn;
        document.getElementById("toastConfirm").style.display = "flex";
    }
    function closeToast() {
        document.getElementById("toastConfirm").style.display = "none";
        pendingConfirmFn = null;
    }
    document.getElementById("toastYesBtn").addEventListener("click", () => {
        const fn = pendingConfirmFn;
        closeToast();
        if (fn) fn();
    });

    function goBack() { window.location.href = "index.php?lokasi=" + currentLokasi; }
    function logout() { if (confirm("Yakin ingin logout?")) window.location.href = "signout.php"; }

    // ══════════════════════════════════════════════════════════
    // STATS OVERVIEW
    // ══════════════════════════════════════════════════════════

    function animateCount(el, target) {
        const start = parseInt(el.textContent) || 0;
        if (start === target) { el.textContent = target; return; }

        // Tab yang sedang tidak aktif/hidden bisa bikin requestAnimationFrame
        // tidak pernah jalan — langsung set nilai akhir supaya angka tidak macet.
        if (document.hidden) { el.textContent = target; return; }

        const duration = 400;
        const startTime = performance.now();
        let done = false;
        function finish() { if (!done) { done = true; el.textContent = target; } }
        function step(now) {
            if (done) return;
            const p = Math.min((now - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(start + (target - start) * eased);
            if (p < 1) requestAnimationFrame(step); else finish();
        }
        requestAnimationFrame(step);
        setTimeout(finish, duration + 200); // pengaman kalau rAF tidak pernah jalan
    }

    function renderStats() {
        const totalRouting = routing.reduce((sum, it) => sum + it.models.length, 0);
        const noPart = models.filter(m => m.part_count === 0).length;

        animateCount(document.getElementById("statTotalModel"), models.length);
        animateCount(document.getElementById("statTotalPart"), routing.length);
        animateCount(document.getElementById("statTotalRouting"), totalRouting);
        animateCount(document.getElementById("statNoPart"), noPart);
    }

    // ══════════════════════════════════════════════════════════
    // MODEL ENGINE (models table)
    // ══════════════════════════════════════════════════════════

    function loadModels(afterLoad) {
        fetch("api/get_models_full.php")
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    models = res.data;
                    renderModelTable();
                    renderStats();
                    if (afterLoad) afterLoad();
                } else {
                    showSnackbar("Gagal memuat daftar model", "error");
                }
            })
            .catch(() => showSnackbar("Gagal memuat daftar model", "error"));
    }

    function renderModelTable() {
        const search = document.getElementById("modelSearch").value.toLowerCase();
        const rows = models.filter(m => !search || m.model_name.toLowerCase().includes(search));

        const tbody = document.getElementById("modelTableBody");

        if (!rows.length) {
            tbody.innerHTML = `<tr class="empty-row"><td colspan="4">${ICON.search}Tidak ada model ditemukan.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map((m, i) => `
            <tr>
                <td>${i + 1}</td>
                <td><strong>${highlightMatch(m.model_name, search)}</strong></td>
                <td class="col-center">
                    <span class="count-text ${m.part_count === 0 ? 'zero' : ''}">${m.part_count} part</span>
                </td>
                <td class="col-center">
                    <div class="row-actions" style="justify-content:center">
                        <button class="btn-icon" title="Edit" onclick="openModelModal(${m.id})">${ICON.pencil}</button>
                        <button class="btn-icon danger" title="Hapus" onclick="confirmDeleteModel(${m.id}, '${escapeHtml(m.model_name)}', ${m.part_count})">${ICON.trash}</button>
                    </div>
                </td>
            </tr>
        `).join("");
    }

    function openModelModal(id) {
        document.getElementById("modelFormError").style.display = "none";
        if (id) {
            const m = models.find(x => x.id === id);
            document.getElementById("modelModalTitle").textContent = "Edit Nama Model";
            document.getElementById("modelIdInput").value = id;
            document.getElementById("modelNameInput").value = m ? m.model_name : "";
        } else {
            document.getElementById("modelModalTitle").textContent = "Tambah Model";
            document.getElementById("modelIdInput").value = "";
            document.getElementById("modelNameInput").value = "";
        }
        document.getElementById("modelModal").style.display = "flex";
        document.getElementById("modelNameInput").focus();
    }
    function closeModelModal() { document.getElementById("modelModal").style.display = "none"; }

    function saveModel() {
        const id   = document.getElementById("modelIdInput").value;
        const name = document.getElementById("modelNameInput").value.trim();
        const errEl = document.getElementById("modelFormError");

        if (!name) {
            errEl.textContent = "Nama model wajib diisi";
            errEl.style.display = "block";
            return;
        }

        const url     = id ? "api/update_model.php" : "api/add_model.php";
        const payload = id ? { id: parseInt(id), model_name: name } : { model_name: name };

        fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    closeModelModal();
                    showSnackbar(id ? "Nama model diperbarui" : "Model baru ditambahkan");
                    loadModels(() => loadRouting());
                } else {
                    errEl.textContent = res.message || "Gagal menyimpan model";
                    errEl.style.display = "block";
                }
            })
            .catch(() => {
                errEl.textContent = "Gagal menghubungi server";
                errEl.style.display = "block";
            });
    }

    function confirmDeleteModel(id, name, partCount) {
        if (partCount > 0) {
            document.getElementById("toastYesBtn").style.display = "none";
            document.querySelector("#toastConfirm .btn-no").textContent = "Oke, Mengerti";
            askConfirm(
                `Model "${name}" masih terhubung ke ${partCount} part number.`,
                "Hapus semua routing part-nya dulu di bagian Routing di bawah sebelum menghapus model ini.",
                null
            );
            return;
        }

        document.getElementById("toastYesBtn").style.display = "inline-block";
        document.querySelector("#toastConfirm .btn-no").textContent = "Batal";

        askConfirm(`Hapus model "${name}"?`, "Tindakan ini tidak bisa dibatalkan.", () => {
            fetch("api/delete_model.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        showSnackbar("Model dihapus");
                        loadModels(() => loadRouting());
                    } else {
                        showSnackbar(res.message || "Gagal menghapus model", "error");
                    }
                })
                .catch(() => showSnackbar("Gagal menghubungi server", "error"));
        });
    }

    // ══════════════════════════════════════════════════════════
    // ROUTING MODEL -> PART (route cards, model_items table)
    // ══════════════════════════════════════════════════════════

    function loadRouting(afterLoad) {
        fetch(`api/get_routing.php?lokasi_id=${currentLokasi}`)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    routing = res.data;
                    renderRoutingCards();
                    renderStats();
                    if (afterLoad) afterLoad();
                } else {
                    showSnackbar("Gagal memuat data routing", "error");
                }
            })
            .catch(() => showSnackbar("Gagal memuat data routing", "error"));
    }

    // Data dari get_routing.php dikelompokkan per PART (item -> models[]).
    // Di-invert di sini jadi per MODEL (model -> parts[]) untuk tampilan route card.
    function groupRoutingByModel() {
        const map = {};
        models.forEach(m => {
            map[m.id] = { model_id: m.id, model_name: m.model_name, parts: [] };
        });

        routing.forEach(it => {
            it.models.forEach(m => {
                if (!map[m.model_id]) return;
                map[m.model_id].parts.push({
                    item_id: it.item_id,
                    part_number: it.part_number,
                    part_name: it.part_name,
                    usage_qty: m.usage_qty
                });
            });
        });

        return Object.values(map).sort((a, b) => a.model_name.localeCompare(b.model_name));
    }

    function renderRoutingCards() {
        const search = document.getElementById("routingSearch").value.toLowerCase();
        const grouped = groupRoutingByModel();

        const filtered = grouped.filter(g => {
            if (!search) return true;
            if (g.model_name.toLowerCase().includes(search)) return true;
            return g.parts.some(p =>
                p.part_number.toLowerCase().includes(search) ||
                p.part_name.toLowerCase().includes(search)
            );
        });

        const grid = document.getElementById("routingCardsGrid");

        if (!filtered.length) {
            grid.innerHTML = `<div class="route-empty-global">${ICON.search}Tidak ada model / part ditemukan.</div>`;
            return;
        }

        grid.innerHTML = filtered.map((g, i) => {
            const parts = g.parts.slice().sort((a, b) => a.part_number.localeCompare(b.part_number));

            const listHtml = parts.length
                ? `<ul class="route-list">` + parts.map((p, wi) => `
                    <li class="route-item">
                        <svg class="ri-connector" width="20" height="20" viewBox="0 0 24 24" style="--wi:${wi % 6}">
                            <path d="M5,3 L5,14 Q5,20 11,20 L17,20" fill="none" stroke="#a5b4fc" stroke-width="2.3" stroke-linecap="round" marker-end="url(#flowArrowhead)"/>
                        </svg>
                        <span class="ri-part">
                            <span class="ri-partnum">${highlightMatch(p.part_number, search)}</span>
                            <span class="ri-partname">${highlightMatch(p.part_name, search)}</span>
                        </span>
                        <span class="ri-qty">×${p.usage_qty}</span>
                        <button class="btn-icon danger" title="Hapus routing ini"
                                onclick="confirmRemoveRouting(${g.model_id}, ${p.item_id}, '${escapeHtml(g.model_name)}', '${escapeHtml(p.part_number)}')">${ICON.trash}</button>
                    </li>
                `).join("") + `</ul>`
                : `<div class="route-empty">Belum ada part terhubung</div>`;

            return `
                <div class="route-card ${parts.length === 0 ? 'is-empty' : ''}" style="animation-delay:${Math.min(i * 20, 380)}ms">
                    <div class="route-card-header">
                        <div class="route-card-title">
                            <span class="model-name">${highlightMatch(g.model_name, search)}</span>
                            <span class="count-text ${parts.length === 0 ? 'zero' : ''}">${parts.length} part</span>
                        </div>
                        <button class="btn-add-mini" title="Tambah part ke model ini" onclick="openRoutingModal(${g.model_id})">${ICON.plus}</button>
                    </div>
                    ${listHtml}
                </div>
            `;
        }).join("");
    }

    function openRoutingModal(preselectModelId) {
        document.getElementById("routingFormError").style.display = "none";

        const modelSel = document.getElementById("routingModelSelect");
        modelSel.innerHTML = models
            .slice()
            .sort((a, b) => a.model_name.localeCompare(b.model_name))
            .map(m => `<option value="${m.id}">${escapeHtml(m.model_name)}</option>`)
            .join("");

        if (preselectModelId) modelSel.value = preselectModelId;

        onRoutingModelChange();
        document.getElementById("routingQtyInput").value = 1;
        document.getElementById("routingModal").style.display = "flex";
    }
    function closeRoutingModal() { document.getElementById("routingModal").style.display = "none"; }

    function onRoutingModelChange() {
        const modelId = parseInt(document.getElementById("routingModelSelect").value);
        const linkedItemIds = new Set(
            routing.filter(it => it.models.some(m => m.model_id === modelId)).map(it => it.item_id)
        );

        const partSel = document.getElementById("routingPartSelect");
        const available = routing
            .filter(it => !linkedItemIds.has(it.item_id))
            .sort((a, b) => a.part_number.localeCompare(b.part_number));

        partSel.innerHTML = available.length
            ? available.map(it => `<option value="${it.item_id}">${escapeHtml(it.part_number)} — ${escapeHtml(it.part_name)}</option>`).join("")
            : `<option value="">— Semua part sudah terhubung ke model ini —</option>`;
    }

    function saveRouting() {
        const model_id  = parseInt(document.getElementById("routingModelSelect").value);
        const item_id   = parseInt(document.getElementById("routingPartSelect").value);
        const usage_qty = parseInt(document.getElementById("routingQtyInput").value) || 1;
        const errEl = document.getElementById("routingFormError");

        if (!model_id || !item_id) {
            errEl.textContent = "Pilih model dan part number terlebih dahulu";
            errEl.style.display = "block";
            return;
        }
        errEl.style.display = "none";

        fetch("api/add_routing.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ item_id, model_id, usage_qty })
        })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    closeRoutingModal();
                    showSnackbar("Routing berhasil ditambahkan");
                    loadModels(() => loadRouting());
                } else {
                    errEl.textContent = res.message || "Gagal menyimpan routing";
                    errEl.style.display = "block";
                }
            })
            .catch(() => {
                errEl.textContent = "Gagal menghubungi server";
                errEl.style.display = "block";
            });
    }

    function confirmRemoveRouting(model_id, item_id, modelName, partNumber) {
        document.getElementById("toastYesBtn").style.display = "inline-block";
        document.querySelector("#toastConfirm .btn-no").textContent = "Batal";

        askConfirm(
            `Hapus routing "${partNumber}" dari model "${modelName}"?`,
            "Data allocation & monthly plan untuk kombinasi ini juga akan terhapus.",
            () => {
                fetch("api/delete_item.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ model_id, item_id })
                })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            showSnackbar("Routing dihapus");
                            loadModels(() => loadRouting());
                        } else {
                            showSnackbar(res.message || "Gagal menghapus routing", "error");
                        }
                    })
                    .catch(() => showSnackbar("Gagal menghubungi server", "error"));
            }
        );
    }

    // ══════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════

    function escapeHtml(str) {
        return String(str ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function highlightMatch(text, search) {
        const str = String(text ?? "");
        if (!search) return escapeHtml(str);
        const idx = str.toLowerCase().indexOf(search.toLowerCase());
        if (idx === -1) return escapeHtml(str);
        return escapeHtml(str.slice(0, idx)) +
            '<mark class="hl">' + escapeHtml(str.slice(idx, idx + search.length)) + '</mark>' +
            escapeHtml(str.slice(idx + search.length));
    }
</script>
</body>
</html>
