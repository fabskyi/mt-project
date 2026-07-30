<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- ══════════════════════════════════════════════
     SIDEBAR COMPONENT
     Pastikan CSS sidebar sudah diload (dari sidebar.css atau inline)
══════════════════════════════════════════════ -->
<aside class="sidebar" id="mainSidebar">

    <!-- Toggle button (muncul di dalam sidebar) -->
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()" title="Hide/Show Sidebar">
        <span id="toggleIcon">◀</span>
    </button>

    <div class="sidebar-content">
        <div class="logo">
            <img src="assets/yanmar.png" class="logo-img">
            <div class="logo-text">
                <div class="logo-title">PT. YADIN</div>
            </div>
        </div>

        <!-- LOKASI SWITCHER (hanya untuk role 'all') -->
        <?php if ($role == "all"): ?>
        <div class="sidebar-section-label">LOKASI</div>
        <ul class="sidebar-nav">
            <li id="btnMS1" class="<?= $lokasi == 1 ? 'active' : '' ?>" onclick="setLokasi(1)">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M4 21v-13l5 4v-4l5 4v-4l5 4v9a1 1 0 0 1 -1 1h-14a1 1 0 0 1 -1 -1z"/><path d="M9 21v-4a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4"/></svg></span>
                <span class="nav-label">Admin MS1</span>
            </li>
            <li id="btnMS2" class="<?= $lokasi == 2 ? 'active' : '' ?>" onclick="setLokasi(2)">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M4 21v-13l5 4v-4l5 4v-4l5 4v9a1 1 0 0 1 -1 1h-14a1 1 0 0 1 -1 -1z"/><path d="M9 21v-4a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4"/></svg></span>
                <span class="nav-label">Admin MS2</span>
            </li>
        </ul>
        <?php elseif ($role == "ms1"): ?>
        <ul class="sidebar-nav">
            <li class="active" onclick="setLokasi(1)">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M4 21v-13l5 4v-4l5 4v-4l5 4v9a1 1 0 0 1 -1 1h-14a1 1 0 0 1 -1 -1z"/><path d="M9 21v-4a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4"/></svg></span>
                <span class="nav-label">Admin MS1</span>
            </li>
        </ul>
        <?php elseif ($role == "ms2"): ?>
        <ul class="sidebar-nav">
            <li class="active" onclick="setLokasi(2)">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M4 21v-13l5 4v-4l5 4v-4l5 4v9a1 1 0 0 1 -1 1h-14a1 1 0 0 1 -1 -1z"/><path d="M9 21v-4a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4"/></svg></span>
                <span class="nav-label">Admin MS2</span>
            </li>
        </ul>
        <?php endif; ?>

        <!-- MENU NAVIGASI -->
        <div class="sidebar-section-label">MENU</div>
        <ul class="sidebar-nav">
            <li class="<?= $currentPage == 'index.php' ? 'active' : '' ?>"
                onclick="window.location.href='index.php?lokasi=<?= $lokasi ?>'">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg></span>
                <span class="nav-label">Supermarket</span>
            </li>
            <li class="<?= $currentPage == 'allocation_setting.php' ? 'active' : '' ?>"
                onclick="window.location.href='allocation_setting.php?lokasi=<?= $lokasi ?>'">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/></svg></span>
                <span class="nav-label">Allocation Setting</span>
            </li>
            <li class="<?= $currentPage == 'history.php' ? 'active' : '' ?>"
                onclick="window.location.href='history.php?lokasi=<?= $lokasi ?>'">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z"/><path d="M9 12l.01 0"/><path d="M13 12l2 0"/><path d="M9 16l.01 0"/><path d="M13 16l2 0"/></svg></span>
                <span class="nav-label">History</span>
            </li>
            <li class="<?= $currentPage == 'model_master.php' ? 'active' : '' ?>"
                onclick="window.location.href='model_master.php?lokasi=<?= $lokasi ?>'">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M4 7h3a1 1 0 0 0 1 -1v-1a2 2 0 0 1 4 0v1a1 1 0 0 0 1 1h3a1 1 0 0 1 1 1v3a1 1 0 0 0 1 1h1a2 2 0 0 1 0 4h-1a1 1 0 0 0 -1 1v3a1 1 0 0 1 -1 1h-3a1 1 0 0 1 -1 -1v-1a2 2 0 0 0 -4 0v1a1 1 0 0 1 -1 1h-3a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1h1a2 2 0 0 0 0 -4h-1a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1"/></svg></span>
                <span class="nav-label">Model &amp; Routing</span>
            </li>
            <?php if ($role == "all"): ?>
            <li class="<?= $currentPage == 'user_setting.php' ? 'active' : '' ?>"
                onclick="window.location.href='user_setting.php'">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg></span>
                <span class="nav-label">User Setting</span>
            </li>
            <?php endif; ?>
        </ul>

        <!-- SPACER + LOGOUT di bawah -->
        <div class="sidebar-bottom">
            <div class="sidebar-user">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg></span>
                <span class="nav-label"><?= htmlspecialchars($_SESSION['user_id'] ?? 'User') ?></span>
            </div>

           <button class="btn-sidebar-logout"
                onclick="if(confirm('Home Page?')) window.location.href='home_menu.php'">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg></span>
                <span class="nav-label">Home</span>
            </button>
             
            <button class="btn-sidebar-logout" onclick="if(confirm('logout?')) window.location.href='signout.php'">
                <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/><path d="M20 12l-9 0"/><path d="M17 15l3 -3l-3 -3"/></svg></span>
                <span class="nav-label">Logout</span>
            </button>
        </div>
    </div>
</aside>

<!-- Toggle button yang muncul saat sidebar hidden -->
<button class="sidebar-show-btn" id="sidebarShowBtn" onclick="toggleSidebar()" title="Show Sidebar">
    ▶
</button>
