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
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; flex-wrap:wrap; gap:10px; }
        .topbar h2 { font-size:17px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px; }
        .top-actions { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }

        /* BUTTONS */
        .btn-nav { padding:7px 14px; background:#fff; color:#475569; border:1px solid #e2e8f0; border-radius:7px; font-size:12px; cursor:pointer; transition:.15s; }
        .btn-nav:hover { background:#f1f5f9; }
        .btn-logout { padding:7px 14px; background:#fff; color:#dc2626; border:1px solid #fecaca; border-radius:7px; font-size:12px; cursor:pointer; transition:.15s; }
        .btn-logout:hover { background:#fef2f2; }
        .btn-add { padding:7px 14px; background:#4f46e5; color:#fff; border:none; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer; transition:.15s; }
        .btn-add:hover { background:#4338ca; }

        /* icon action buttons */
        .btn-icon { width:28px; height:28px; border-radius:7px; border:1px solid #e2e8f0; background:#fff; cursor:pointer; font-size:13px; display:inline-flex; align-items:center; justify-content:center; transition:.15s; color:#64748b; }
        .btn-icon:hover { background:#f1f5f9; }
        .btn-icon.danger:hover { background:#fef2f2; border-color:#fecaca; color:#dc2626; }
        .btn-manage { padding:5px 12px; background:#eef2ff; color:#4338ca; border:1px solid #e0e7ff; border-radius:7px; font-size:11.5px; font-weight:600; cursor:pointer; transition:.15s; white-space:nowrap; }
        .btn-manage:hover { background:#e0e7ff; }

        /* SECTIONS */
        .section-card { background:#fff; border-radius:12px; border:1px solid #eef0f3; padding:20px; margin-bottom:20px; }
        .section-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px; }
        .section-head h3 { font-size:14.5px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:7px; }
        .section-head .hint { font-size:12px; color:#94a3b8; font-weight:400; margin-left:2px; }
        .section-toolbar { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
        .section-toolbar input[type=text] { padding:7px 12px; border:1px solid #e2e8f0; border-radius:7px; font-size:12.5px; width:220px; background:#fafbfc; }
        .section-toolbar input[type=text]:focus { outline:none; border-color:#c7d2fe; background:#fff; }

        /* TABLE */
        .table-container { overflow:auto; max-height:420px; border-radius:10px; border:1px solid #f1f5f9; }
        table.data-table { width:100%; border-collapse:collapse; font-size:12.5px; }
        table.data-table thead { background:#fafbfc; position:sticky; top:0; z-index:2; }
        table.data-table th { padding:9px 12px; font-size:10.5px; font-weight:700; letter-spacing:.3px; text-transform:uppercase; color:#94a3b8; border-bottom:1px solid #eef0f3; text-align:left; white-space:nowrap; }
        table.data-table td { padding:8px 12px; border-bottom:1px solid #f5f6f8; color:#334155; vertical-align:middle; }
        table.data-table tbody tr:hover { background:#fafbff; }
        .col-center { text-align:center !important; }
        .empty-row td { text-align:center; padding:26px; color:#94a3b8; }
        .row-actions { display:flex; gap:6px; }

        .count-text { color:#64748b; font-size:12px; }
        .count-text.zero { color:#c3c9d4; }

        /* Routing summary cell */
        .routing-summary { display:flex; align-items:center; gap:10px; }
        .routing-preview { color:#64748b; font-size:12px; flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .routing-preview.empty { color:#c3c9d4; font-style:italic; }

        /* MODAL (shared pattern) */
        .modal-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.45); display:none; justify-content:center; align-items:center; z-index:9998; animation:fadeIn .15s ease; }
        .modal-box { background:#fff; width:440px; max-width:95vw; border-radius:14px; box-shadow:0 20px 50px rgba(15,23,42,.18); overflow:hidden; animation:slideUp .18s ease; }
        .modal-box.wide { width:520px; }
        .modal-header { padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; }
        .modal-header h3 { font-size:15px; font-weight:700; color:#1e293b; }
        .modal-close { font-size:20px; cursor:pointer; color:#94a3b8; line-height:1; }
        .modal-close:hover { color:#334155; }
        .modal-body { padding:18px 20px; }
        .modal-footer { padding:12px 20px; border-top:1px solid #f1f5f9; display:flex; justify-content:flex-end; gap:8px; }
        .form-group { margin-bottom:14px; }
        .form-group label { font-size:11.5px; font-weight:600; color:#64748b; display:block; margin-bottom:5px; }
        .form-group input, .form-group select { width:100%; padding:8px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; color:#1e293b; }
        .form-group input:focus, .form-group select:focus { border-color:#a5b4fc; outline:none; }
        .form-error { font-size:12px; color:#dc2626; margin-top:6px; display:none; }
        .btn-save-modal { padding:8px 18px; background:#4f46e5; color:#fff; border:none; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer; transition:.15s; }
        .btn-save-modal:hover { background:#4338ca; }
        .btn-cancel-modal { padding:8px 16px; background:#f1f5f9; color:#475569; border:none; border-radius:8px; font-size:12.5px; cursor:pointer; font-weight:600; }
        .btn-cancel-modal:hover { background:#e2e8f0; }

        /* Routing manage modal specifics */
        .routing-part-label { font-size:12px; color:#94a3b8; margin-bottom:2px; }
        .add-row { display:flex; gap:8px; align-items:flex-end; margin-bottom:16px; padding-bottom:16px; border-bottom:1px solid #f1f5f9; }
        .add-row .form-group { margin-bottom:0; flex:1; min-width:0; }
        .add-row .qty-field { width:70px; flex:none; }
        .add-row button { flex:none; height:35px; }
        .linked-list { display:flex; flex-direction:column; gap:6px; max-height:260px; overflow:auto; }
        .linked-row { display:flex; align-items:center; gap:10px; background:#fafbfc; border:1px solid #f1f5f9; border-radius:8px; padding:8px 10px; }
        .linked-row .lr-name { flex:1; font-size:12.5px; font-weight:600; color:#1e293b; }
        .linked-row .lr-qty { font-size:11.5px; color:#94a3b8; background:#fff; border:1px solid #eef0f3; padding:2px 8px; border-radius:6px; }
        .linked-empty { text-align:center; padding:20px; color:#c3c9d4; font-size:12.5px; }

        @keyframes fadeIn  { from{opacity:0} to{opacity:1} }
        @keyframes slideUp { from{transform:translateY(12px);opacity:0} to{transform:translateY(0);opacity:1} }

        /* TOAST CONFIRM */
        .toast-confirm { position:fixed; inset:0; background:rgba(15,23,42,0.35); display:none; justify-content:center; align-items:center; z-index:9999; }
        .toast-box { background:white; padding:20px 24px; border-radius:12px; width:340px; text-align:center; box-shadow:0 20px 50px rgba(15,23,42,.2); animation:scaleIn .15s ease; }
        .toast-box .toast-sub { font-size:12px; color:#64748b; margin-top:6px; }
        .toast-actions { margin-top:15px; display:flex; justify-content:center; gap:10px; }
        .btn-yes { background:#dc2626; color:white; border:none; padding:7px 18px; border-radius:7px; cursor:pointer; font-weight:600; font-size:12.5px; }
        .btn-yes:hover { background:#b91c1c; }
        .btn-no  { background:#f1f5f9; color:#475569; border:none; padding:7px 18px; border-radius:7px; cursor:pointer; font-size:12.5px; }
        .btn-no:hover { background:#e2e8f0; }
        @keyframes scaleIn { from{transform:scale(.94);opacity:0} to{transform:scale(1);opacity:1} }

        /* SNACKBAR */
        #snackbar { visibility:hidden; min-width:250px; background:#1e293b; color:white; text-align:center; border-radius:8px; padding:12px 20px; position:fixed; z-index:9999; bottom:30px; left:50%; transform:translateX(-50%); font-size:13px; font-weight:600; }
        #snackbar.show { visibility:visible; animation:fadeInUp .3s ease, fadeOut .5s ease 2.5s forwards; }
        @keyframes fadeInUp { from{opacity:0;transform:translateX(-50%) translateY(20px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }
        @keyframes fadeOut  { to{opacity:0} }
    </style>
</head>
<body>
<div class="layout">

    <?php include 'sidebar.php'; ?>

    <main class="main">
        <header class="topbar">
            <h2>🧩 Model & Routing <span class="hint" style="font-weight:400;color:#94a3b8;font-size:13px;">MS<?php echo $lokasi; ?></span></h2>
            <div class="top-actions">
                <button class="btn-nav" onclick="goBack()">← Back</button>
                <button class="btn-logout" onclick="logout()">Logout</button>
            </div>
        </header>

        <!-- ═══════════════════════════════════
             SECTION 1: MODEL ENGINE
        ═══════════════════════════════════ -->
        <section class="section-card">
            <div class="section-head">
                <h3>⚙️ Model Engine <span class="hint">nama-nama model produksi</span></h3>
                <div class="section-toolbar">
                    <input type="text" id="modelSearch" placeholder="Cari nama model..." oninput="renderModelTable()">
                    <button class="btn-add" onclick="openModelModal()">+ Tambah Model</button>
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
                        <tr class="empty-row"><td colspan="4">Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ═══════════════════════════════════
             SECTION 2: ROUTING PART <-> MODEL
        ═══════════════════════════════════ -->
        <section class="section-card">
            <div class="section-head">
                <h3>🔗 Routing Part ↔ Model <span class="hint">part number ini dipakai model apa saja</span></h3>
                <div class="section-toolbar">
                    <input type="text" id="routingSearch" placeholder="Cari part number / nama / model..." oninput="renderRoutingTable()">
                    <button class="btn-add" onclick="openRoutingModal()">+ Tambah Routing</button>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:40px">No</th>
                            <th style="width:160px">Part Number</th>
                            <th style="width:170px">Part Name</th>
                            <th>Model Terhubung</th>
                            <th style="width:90px" class="col-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="routingTableBody">
                        <tr class="empty-row"><td colspan="5">Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<!-- MODEL ADD/EDIT MODAL -->
<div id="modelModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modelModalTitle">Tambah Model</h3>
            <span class="modal-close" onclick="closeModelModal()">×</span>
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

<!-- ROUTING MANAGE MODAL (add + list + remove, per part number) -->
<div id="routingModal" class="modal-overlay">
    <div class="modal-box wide">
        <div class="modal-header">
            <h3>🔗 Kelola Routing</h3>
            <span class="modal-close" onclick="closeRoutingModal()">×</span>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Part Number</label>
                <select id="routingPartSelect" onchange="refreshRoutingModal()"></select>
            </div>

            <div class="add-row">
                <div class="form-group">
                    <label>Tambah Model</label>
                    <select id="routingAddModelSelect"></select>
                </div>
                <div class="form-group qty-field">
                    <label>Qty</label>
                    <input type="number" id="routingAddQtyInput" value="1" min="1">
                </div>
                <button class="btn-save-modal" onclick="addRoutingInModal()">+ Tambah</button>
            </div>
            <div class="form-error" id="routingFormError"></div>

            <div class="linked-list" id="routingLinkedList"></div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="closeRoutingModal()">Tutup</button>
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
    let currentLokasi = <?php echo $lokasi; ?>;
    let models  = [];
    let routing = [];
    let pendingConfirmFn = null;

    document.addEventListener("DOMContentLoaded", () => {
        loadModels();
        loadRouting();
    });

    function showSnackbar(msg) {
        const sb = document.getElementById("snackbar");
        sb.textContent = msg; sb.className = "show";
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
    // MODEL ENGINE (models table)
    // ══════════════════════════════════════════════════════════

    function loadModels() {
        fetch("api/get_models_full.php")
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    models = res.data;
                    renderModelTable();
                } else {
                    showSnackbar("❌ Gagal memuat daftar model");
                }
            })
            .catch(() => showSnackbar("❌ Gagal memuat daftar model"));
    }

    function renderModelTable() {
        const search = document.getElementById("modelSearch").value.toLowerCase();
        const rows = models.filter(m => !search || m.model_name.toLowerCase().includes(search));

        const tbody = document.getElementById("modelTableBody");

        if (!rows.length) {
            tbody.innerHTML = `<tr class="empty-row"><td colspan="4">Tidak ada model ditemukan.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map((m, i) => `
            <tr>
                <td>${i + 1}</td>
                <td><strong>${escapeHtml(m.model_name)}</strong></td>
                <td class="col-center">
                    <span class="count-text ${m.part_count === 0 ? 'zero' : ''}">${m.part_count} part</span>
                </td>
                <td class="col-center">
                    <div class="row-actions" style="justify-content:center">
                        <button class="btn-icon" title="Edit" onclick="openModelModal(${m.id})">✏️</button>
                        <button class="btn-icon danger" title="Hapus" onclick="confirmDeleteModel(${m.id}, '${escapeHtml(m.model_name)}', ${m.part_count})">🗑️</button>
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
                    showSnackbar(id ? "✔ Nama model diperbarui" : "✔ Model baru ditambahkan");
                    loadModels();
                    loadRouting();
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
                        showSnackbar("✔ Model dihapus");
                        loadModels();
                    } else {
                        showSnackbar("❌ " + (res.message || "Gagal menghapus model"));
                    }
                })
                .catch(() => showSnackbar("❌ Gagal menghubungi server"));
        });
    }

    // ══════════════════════════════════════════════════════════
    // ROUTING PART NUMBER <-> MODEL (model_items table)
    // ══════════════════════════════════════════════════════════

    function loadRouting(afterLoad) {
        fetch(`api/get_routing.php?lokasi_id=${currentLokasi}`)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    routing = res.data;
                    renderRoutingTable();
                    if (afterLoad) afterLoad();
                } else {
                    showSnackbar("❌ Gagal memuat data routing");
                }
            })
            .catch(() => showSnackbar("❌ Gagal memuat data routing"));
    }

    function renderRoutingTable() {
        const search = document.getElementById("routingSearch").value.toLowerCase();

        const rows = routing.filter(it => {
            if (!search) return true;
            const inPart = it.part_name.toLowerCase().includes(search) ||
                           it.part_number.toLowerCase().includes(search);
            const inModel = it.models.some(m => m.model_name.toLowerCase().includes(search));
            return inPart || inModel;
        });

        const tbody = document.getElementById("routingTableBody");

        if (!rows.length) {
            tbody.innerHTML = `<tr class="empty-row"><td colspan="5">Tidak ada part ditemukan.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map((it, i) => {
            const count = it.models.length;
            const preview = count
                ? it.models.slice(0, 3).map(m => escapeHtml(m.model_name)).join(", ") + (count > 3 ? ` +${count - 3} lainnya` : "")
                : "Belum ada model";

            return `
                <tr>
                    <td>${i + 1}</td>
                    <td>${escapeHtml(it.part_number)}</td>
                    <td>${escapeHtml(it.part_name)}</td>
                    <td>
                        <div class="routing-summary">
                            <span class="count-text ${count === 0 ? 'zero' : ''}">${count} model</span>
                            <span class="routing-preview ${count === 0 ? 'empty' : ''}">${preview}</span>
                        </div>
                    </td>
                    <td class="col-center">
                        <button class="btn-manage" onclick="openRoutingModal(${it.item_id})">Kelola</button>
                    </td>
                </tr>
            `;
        }).join("");
    }

    function openRoutingModal(preselectItemId) {
        document.getElementById("routingFormError").style.display = "none";

        const partSel = document.getElementById("routingPartSelect");
        partSel.innerHTML = routing
            .slice()
            .sort((a, b) => a.part_number.localeCompare(b.part_number))
            .map(it => `<option value="${it.item_id}">${escapeHtml(it.part_number)} — ${escapeHtml(it.part_name)}</option>`)
            .join("");

        if (preselectItemId) partSel.value = preselectItemId;

        refreshRoutingModal();
        document.getElementById("routingModal").style.display = "flex";
    }
    function closeRoutingModal() { document.getElementById("routingModal").style.display = "none"; }

    function refreshRoutingModal() {
        const itemId = parseInt(document.getElementById("routingPartSelect").value);
        const it = routing.find(x => x.item_id === itemId);
        if (!it) return;

        const linkedIds = new Set(it.models.map(m => m.model_id));

        const addSel = document.getElementById("routingAddModelSelect");
        const available = models.filter(m => !linkedIds.has(m.id)).sort((a, b) => a.model_name.localeCompare(b.model_name));
        addSel.innerHTML = available.length
            ? available.map(m => `<option value="${m.id}">${escapeHtml(m.model_name)}</option>`).join("")
            : `<option value="">— Semua model sudah terhubung —</option>`;
        document.getElementById("routingAddQtyInput").value = 1;

        const list = document.getElementById("routingLinkedList");
        list.innerHTML = it.models.length
            ? it.models.slice().sort((a, b) => a.model_name.localeCompare(b.model_name)).map(m => `
                <div class="linked-row">
                    <span class="lr-name">${escapeHtml(m.model_name)}</span>
                    <span class="lr-qty">×${m.usage_qty}</span>
                    <button class="btn-icon danger" title="Hapus routing" onclick="removeRoutingInModal(${m.model_id}, ${itemId}, '${escapeHtml(m.model_name)}', '${escapeHtml(it.part_number)}')">🗑️</button>
                </div>
            `).join("")
            : `<div class="linked-empty">Belum ada model terhubung ke part ini</div>`;
    }

    function addRoutingInModal() {
        const item_id   = parseInt(document.getElementById("routingPartSelect").value);
        const model_id  = parseInt(document.getElementById("routingAddModelSelect").value);
        const usage_qty = parseInt(document.getElementById("routingAddQtyInput").value) || 1;
        const errEl = document.getElementById("routingFormError");

        if (!item_id || !model_id) {
            errEl.textContent = "Pilih model terlebih dahulu";
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
                    showSnackbar("✔ Model ditambahkan ke routing");
                    loadModels();
                    loadRouting(refreshRoutingModal);
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

    function removeRoutingInModal(model_id, item_id, modelName, partNumber) {
        document.getElementById("toastYesBtn").style.display = "inline-block";
        document.querySelector("#toastConfirm .btn-no").textContent = "Batal";

        askConfirm(
            `Hapus routing "${modelName}" dari part "${partNumber}"?`,
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
                            showSnackbar("✔ Routing dihapus");
                            loadModels();
                            loadRouting(refreshRoutingModal);
                        } else {
                            showSnackbar("❌ " + (res.message || "Gagal menghapus routing"));
                        }
                    })
                    .catch(() => showSnackbar("❌ Gagal menghubungi server"));
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
</script>
</body>
</html>
