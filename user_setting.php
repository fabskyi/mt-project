<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

if ($_SESSION['role'] !== 'all') {
    header("Location: home_menu.php");
    exit;
}

$role   = $_SESSION['role'];
$lokasi = 1; // dipakai sidebar.php untuk switch lokasi (superadmin bisa lihat keduanya)
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="sidebar.css">
    <link rel="icon" type="image/png" href="assets/yanmar.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Setting - PT. YADIN</title>

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
        .btn-nav { padding:7px 14px; background:#fff; color:#475569; border:1px solid #e2e8f0; border-radius:7px; font-size:12px; cursor:pointer; transition:.15s; display:inline-flex; align-items:center; gap:4px; }
        .btn-nav:hover { background:#f1f5f9; }
        .btn-logout { padding:7px 14px; background:#fff; color:#dc2626; border:1px solid #fecaca; border-radius:7px; font-size:12px; cursor:pointer; transition:.15s; }
        .btn-logout:hover { background:#fef2f2; }
        .btn-add { padding:7px 14px; background:#4f46e5; color:#fff; border:none; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer; transition:.15s; display:inline-flex; align-items:center; gap:5px; }
        .btn-add:hover { background:#4338ca; }

        .btn-icon { width:28px; height:28px; border-radius:7px; border:1px solid #e2e8f0; background:#fff; cursor:pointer; font-size:13px; display:inline-flex; align-items:center; justify-content:center; transition:.15s; color:#64748b; }
        .btn-icon:hover { background:#f1f5f9; }
        .btn-icon.danger:hover { background:#1e1e1e; border-color:#1e1e1e; color:#fff; }

        /* vector icon system — hitam-putih saja, no fill color */
        .icon { width:16px; height:16px; flex-shrink:0; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; vertical-align:-3px; }
        .icon-sm { width:13px; height:13px; }
        h2 .icon, .section-head h3 .icon { width:18px; height:18px; color:#1e293b; }
        .modal-close { display:inline-flex; }

        /* SECTIONS */
        .section-card { background:#fff; border-radius:12px; border:1px solid #eef0f3; padding:20px; margin-bottom:20px; }
        .section-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px; }
        .section-head h3 { font-size:14.5px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:7px; }
        .section-head .hint { font-size:12px; color:#94a3b8; font-weight:400; margin-left:2px; }
        .section-toolbar { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
        .section-toolbar input[type=text], .section-toolbar select { padding:7px 12px; border:1px solid #e2e8f0; border-radius:7px; font-size:12.5px; background:#fafbfc; }
        .section-toolbar input[type=text] { width:220px; }
        .section-toolbar input:focus, .section-toolbar select:focus { outline:none; border-color:#c7d2fe; background:#fff; }

        /* TABLE */
        .table-container { overflow:auto; max-height:520px; border-radius:10px; border:1px solid #f1f5f9; }
        table.data-table { width:100%; border-collapse:collapse; font-size:12.5px; }
        table.data-table thead { background:#fafbfc; position:sticky; top:0; z-index:2; }
        table.data-table th { padding:9px 12px; font-size:10.5px; font-weight:700; letter-spacing:.3px; text-transform:uppercase; color:#94a3b8; border-bottom:1px solid #eef0f3; text-align:left; white-space:nowrap; }
        table.data-table td { padding:8px 12px; border-bottom:1px solid #f5f6f8; color:#334155; vertical-align:middle; }
        table.data-table tbody tr:hover { background:#fafbff; }
        .col-center { text-align:center !important; }
        .empty-row td { text-align:center; padding:26px; color:#94a3b8; }
        .row-actions { display:flex; gap:6px; justify-content:center; }
        .muted { color:#c3c9d4; font-style:italic; }

        /* Role badges */
        .role-badge { display:inline-flex; align-items:center; gap:6px; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:700; }
        .role-badge.superadmin { background:#fef3c7; color:#92400e; }
        .role-badge.admin      { background:#e0e7ff; color:#4338ca; }
        .role-badge.user       { background:#dcfce7; color:#166534; }
        .role-badge.monitor    { background:#f1f5f9; color:#64748b; }
        .role-child { font-size:11px; color:#94a3b8; margin-left:2px; }

        /* MODAL */
        .modal-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.45); display:none; justify-content:center; align-items:center; z-index:9998; animation:fadeIn .15s ease; }
        .modal-box { background:#fff; width:420px; max-width:95vw; border-radius:14px; box-shadow:0 20px 50px rgba(15,23,42,.18); overflow:hidden; animation:slideUp .18s ease; }
        .modal-header { padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; }
        .modal-header h3 { font-size:15px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:6px; }
        .modal-close { font-size:20px; cursor:pointer; color:#94a3b8; line-height:1; }
        .modal-close:hover { color:#334155; }
        .modal-body { padding:18px 20px; }
        .modal-footer { padding:12px 20px; border-top:1px solid #f1f5f9; display:flex; justify-content:flex-end; gap:8px; }
        .form-group { margin-bottom:14px; }
        .form-group label { font-size:11.5px; font-weight:600; color:#64748b; display:block; margin-bottom:5px; }
        .form-group input, .form-group select { width:100%; padding:8px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; color:#1e293b; }
        .form-group input:disabled { background:#f8fafc; color:#94a3b8; }
        .form-group input:focus, .form-group select:focus { border-color:#a5b4fc; outline:none; }
        .form-hint { font-size:11px; color:#94a3b8; margin-top:4px; }
        .form-error { font-size:12px; color:#dc2626; margin-top:6px; display:none; }
        .btn-save-modal { padding:8px 18px; background:#4f46e5; color:#fff; border:none; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer; transition:.15s; }
        .btn-save-modal:hover { background:#4338ca; }
        .btn-cancel-modal { padding:8px 16px; background:#f1f5f9; color:#475569; border:none; border-radius:8px; font-size:12.5px; cursor:pointer; font-weight:600; }
        .btn-cancel-modal:hover { background:#e2e8f0; }

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
        #snackbar { visibility:hidden; min-width:250px; background:#1e293b; color:white; border-radius:8px; padding:12px 20px; position:fixed; z-index:9999; bottom:30px; left:50%; transform:translateX(-50%); font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px; justify-content:center; }
        #snackbar .icon { color:#fff; flex-shrink:0; }
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
            <h2><svg class="icon" viewBox="0 0 24 24"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg> User Setting</h2>
            <div class="top-actions">
                <button class="btn-nav" onclick="goBack()"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M15 6l-6 6l6 6"/></svg> Back</button>
                <button class="btn-logout" onclick="logout()">Logout</button>
            </div>
        </header>

        <section class="section-card">
            <div class="section-head">
                <h3><svg class="icon" viewBox="0 0 24 24"><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/></svg> Daftar User <span class="hint">1 form untuk data karyawan + akun login sekaligus</span></h3>
                <div class="section-toolbar">
                    <select id="roleFilter" onchange="renderUserTable()">
                        <option value="">Semua Role</option>
                        <option value="superadmin">Superadmin</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                    <input type="text" id="userSearch" placeholder="Cari NIK / nama..." autocomplete="off" oninput="renderUserTable()">
                    <button class="btn-add" onclick="openUserModal()"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg> Tambah User</button>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:40px">No</th>
                            <th style="width:120px">NIK</th>
                            <th>Nama</th>
                            <th style="width:170px">Role</th>
                            <th style="width:90px" class="col-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <tr class="empty-row"><td colspan="5">Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<!-- USER ADD/EDIT MODAL -->
<div id="userModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="userModalTitle">Tambah User</h3>
            <span class="modal-close" onclick="closeUserModal()"><svg class="icon" viewBox="0 0 24 24"><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg></span>
        </div>
        <div class="modal-body">
            <input type="hidden" id="userIdInput">

            <div class="form-group">
                <label>NIK</label>
                <input type="text" id="userNikInput" placeholder="Contoh: 2014005" maxlength="20" autocomplete="off">
            </div>

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" id="userNamaInput" placeholder="Nama lengkap karyawan" maxlength="100" autocomplete="off">
                <div class="form-hint">Otomatis tersimpan sebagai data karyawan juga (dipakai di halaman Transaction).</div>
            </div>

            <div class="form-group">
                <label id="userPasswordLabel">Password</label>
                <input type="password" id="userPasswordInput" placeholder="Password akun" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label>Role Group</label>
                <select id="userParentSelect" onchange="onParentChange()">
                    <option value="superadmin">Superadmin (akses penuh)</option>
                    <option value="admin">Admin (per lokasi)</option>
                    <option value="user">User (transaksi saja)</option>
                </select>
            </div>

            <div class="form-group" id="userChildGroup" style="display:none">
                <label id="userChildLabel">Lokasi</label>
                <select id="userChildSelect"></select>
            </div>

            <div class="form-error" id="userFormError"></div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="closeUserModal()">Batal</button>
            <button class="btn-save-modal" onclick="saveUser()">Simpan</button>
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
    // Ikon vector hitam-putih (line icon, currentColor) dipakai di baris tabel yang di-render JS
    const ICON = {
        pencil: '<svg class="icon" viewBox="0 0 24 24"><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/><path d="M13.5 6.5l4 4"/></svg>',
        trash:  '<svg class="icon" viewBox="0 0 24 24"><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>',
        check:  '<svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M5 12l5 5l10 -10"/></svg>',
        alert:  '<svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"/><path d="M12 16h.01"/></svg>',
    };

    let currentLokasi = <?php echo $lokasi; ?>;
    let users = [];
    let pendingConfirmFn = null;

    // Role standardization: DB masih pakai kolom flat `role`,
    // tapi di UI kita kelompokkan jadi parent -> child.
    // Sumbernya diambil dari tabel `role_hierarchy` (api/get_role_hierarchy.php),
    // BUKAN hardcode di sini, supaya kalau labelnya diubah di DB otomatis sinkron ke UI.
    let ROLE_MAP     = {};
    let CHILD_OPTIONS = {};

    document.addEventListener("DOMContentLoaded", () => {
        loadRoleHierarchy().then(() => {
            onParentChange();
            loadUsers();
        });
    });

    function loadRoleHierarchy() {
        return fetch("api/get_role_hierarchy.php")
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    showSnackbar("Gagal memuat struktur role", "error");
                    return;
                }
                ROLE_MAP = {};
                CHILD_OPTIONS = {};
                res.data.forEach(row => {
                    ROLE_MAP[row.role] = {
                        parent: row.parent_group,
                        child: row.child_label ? row.role : null,
                        parentLabel: row.parent_label,
                        childLabel: row.child_label,
                    };
                    if (row.child_label) {
                        if (!CHILD_OPTIONS[row.parent_group]) CHILD_OPTIONS[row.parent_group] = [];
                        CHILD_OPTIONS[row.parent_group].push({ value: row.role, label: row.child_label });
                    }
                });
            })
            .catch(() => showSnackbar("Gagal memuat struktur role", "error"));
    }

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

    function goBack() { window.location.href = "home_menu.php"; }
    function logout() { if (confirm("Yakin ingin logout?")) window.location.href = "signout.php"; }

    // ══════════════════════════════════════════════════════════
    // USER LIST
    // ══════════════════════════════════════════════════════════

    function loadUsers() {
        fetch("api/get_users.php")
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    users = res.data;
                    renderUserTable();
                } else {
                    showSnackbar("Gagal memuat daftar user", "error");
                }
            })
            .catch(() => showSnackbar("Gagal memuat daftar user", "error"));
    }

    function renderUserTable() {
        const search = document.getElementById("userSearch").value.toLowerCase();
        const roleFilter = document.getElementById("roleFilter").value;

        const rows = users.filter(u => {
            if (roleFilter && u.parent_group !== roleFilter) return false;
            if (!search) return true;
            return u.nik.toLowerCase().includes(search) ||
                   (u.nama || "").toLowerCase().includes(search);
        });

        const tbody = document.getElementById("userTableBody");
        if (!rows.length) {
            tbody.innerHTML = `<tr class="empty-row"><td colspan="5">Tidak ada user ditemukan.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map((u, i) => {
            const nama = u.nama ? escapeHtml(u.nama) : '<span class="muted">— belum diisi —</span>';
            return `
                <tr>
                    <td>${i + 1}</td>
                    <td><strong>${escapeHtml(u.nik)}</strong></td>
                    <td>${nama}</td>
                    <td>
                        <span class="role-badge ${u.parent_group || 'user'}">${escapeHtml(u.parent_label || u.role)}</span>
                        ${u.child_label ? `<span class="role-child">${escapeHtml(u.child_label)}</span>` : ''}
                    </td>
                    <td class="col-center">
                        <div class="row-actions">
                            <button class="btn-icon" title="Edit" onclick="openUserModal(${u.id})">${ICON.pencil}</button>
                            <button class="btn-icon danger" title="Hapus" onclick="confirmDeleteUser(${u.id}, '${escapeHtml(u.nik)}')">${ICON.trash}</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join("");
    }

    // ══════════════════════════════════════════════════════════
    // ADD / EDIT MODAL
    // ══════════════════════════════════════════════════════════

    function onParentChange() {
        const parent = document.getElementById("userParentSelect").value;
        const childGroup = document.getElementById("userChildGroup");
        const childSelect = document.getElementById("userChildSelect");
        const childLabel = document.getElementById("userChildLabel");

        if (CHILD_OPTIONS[parent]) {
            childGroup.style.display = "block";
            childLabel.textContent = parent === 'admin' ? 'Lokasi' : 'Tipe';
            childSelect.innerHTML = CHILD_OPTIONS[parent].map(c => `<option value="${c.value}">${c.label}</option>`).join("");
        } else {
            childGroup.style.display = "none";
            childSelect.innerHTML = "";
        }
    }

    function openUserModal(id) {
        document.getElementById("userFormError").style.display = "none";
        const nikInput = document.getElementById("userNikInput");
        const pwLabel  = document.getElementById("userPasswordLabel");
        const pwInput  = document.getElementById("userPasswordInput");

        if (id) {
            const u = users.find(x => x.id === id);
            const info = ROLE_MAP[u.role] || { parent: 'user', child: null };

            document.getElementById("userModalTitle").textContent = "Edit User";
            document.getElementById("userIdInput").value = id;
            nikInput.value = u.nik;
            nikInput.disabled = true;
            document.getElementById("userNamaInput").value = u.nama || "";
            pwLabel.textContent = "Password (kosongkan jika tidak diubah)";
            pwInput.value = "";
            pwInput.placeholder = "•••••• (biarkan kosong = tetap)";

            document.getElementById("userParentSelect").value = info.parent;
            onParentChange();
            if (info.child) document.getElementById("userChildSelect").value = info.child;
        } else {
            document.getElementById("userModalTitle").textContent = "Tambah User";
            document.getElementById("userIdInput").value = "";
            nikInput.value = "";
            nikInput.disabled = false;
            document.getElementById("userNamaInput").value = "";
            pwLabel.textContent = "Password";
            pwInput.value = "";
            pwInput.placeholder = "Password akun";

            document.getElementById("userParentSelect").value = "superadmin";
            onParentChange();
        }

        document.getElementById("userModal").style.display = "flex";
    }
    function closeUserModal() { document.getElementById("userModal").style.display = "none"; }

    function computeRole() {
        const parent = document.getElementById("userParentSelect").value;
        if (parent === "superadmin") return "all";
        if (parent === "monitor") return "monitor";
        return document.getElementById("userChildSelect").value;
    }

    function saveUser() {
        const id       = document.getElementById("userIdInput").value;
        const nik      = document.getElementById("userNikInput").value.trim();
        const nama     = document.getElementById("userNamaInput").value.trim();
        const password = document.getElementById("userPasswordInput").value;
        const role     = computeRole();
        const errEl    = document.getElementById("userFormError");

        if (!id && !nik) {
            errEl.textContent = "NIK wajib diisi";
            errEl.style.display = "block";
            return;
        }
        if (!nama) {
            errEl.textContent = "Nama wajib diisi";
            errEl.style.display = "block";
            return;
        }
        if (!id && !password) {
            errEl.textContent = "Password wajib diisi untuk user baru";
            errEl.style.display = "block";
            return;
        }

        const url = id ? "api/update_user.php" : "api/add_user.php";
        const payload = id
            ? { id: parseInt(id), nama, role, password }
            : { nik, nama, role, password };

        fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    closeUserModal();
                    showSnackbar(id ? "User diperbarui" : "User baru ditambahkan");
                    loadUsers();
                } else {
                    errEl.textContent = res.message || "Gagal menyimpan user";
                    errEl.style.display = "block";
                }
            })
            .catch(() => {
                errEl.textContent = "Gagal menghubungi server";
                errEl.style.display = "block";
            });
    }

    function confirmDeleteUser(id, nik) {
        askConfirm(`Hapus user "${nik}"?`, "Tindakan ini tidak bisa dibatalkan.", () => {
            fetch("api/delete_user.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        showSnackbar("User dihapus");
                        loadUsers();
                    } else {
                        showSnackbar(res.message || "Gagal menghapus user", "error");
                    }
                })
                .catch(() => showSnackbar("Gagal menghubungi server", "error"));
        });
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
