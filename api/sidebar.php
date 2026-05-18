<?php

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
                <span class="nav-icon">🏭</span>
                <span class="nav-label">Admin MS1</span>
            </li>
            <li id="btnMS2" class="<?= $lokasi == 2 ? 'active' : '' ?>" onclick="setLokasi(2)">
                <span class="nav-icon">🏭</span>
                <span class="nav-label">Admin MS2</span>
            </li>
        </ul>
        <?php elseif ($role == "ms1"): ?>
        <ul class="sidebar-nav">
            <li class="active" onclick="setLokasi(1)">
                <span class="nav-icon">🏭</span>
                <span class="nav-label">Admin MS1</span>
            </li>
        </ul>
        <?php elseif ($role == "ms2"): ?>
        <ul class="sidebar-nav">
            <li class="active" onclick="setLokasi(2)">
                <span class="nav-icon">🏭</span>
                <span class="nav-label">Admin MS2</span>
            </li>
        </ul>
        <?php endif; ?>

        <!-- MENU NAVIGASI -->
        <div class="sidebar-section-label">MENU</div>
        <ul class="sidebar-nav">
            <li class="<?= $currentPage == 'index.php' ? 'active' : '' ?>"
                onclick="window.location.href='index.php?lokasi=<?= $lokasi ?>'">
                <span class="nav-icon">📦</span>
                <span class="nav-label">Supermarket</span>
            </li>
            <li class="<?= $currentPage == 'allocation_setting.php' ? 'active' : '' ?>"
                onclick="window.location.href='allocation_setting.php?lokasi=<?= $lokasi ?>'">
                <span class="nav-icon">⚙️</span>
                <span class="nav-label">Allocation Setting</span>
            </li>
            <li class="<?= $currentPage == 'history.php' ? 'active' : '' ?>"
                onclick="window.location.href='history.php?lokasi=<?= $lokasi ?>'">
                <span class="nav-icon">📋</span>
                <span class="nav-label">History</span>
            </li>
        </ul>

        <!-- SPACER + LOGOUT di bawah -->
        <div class="sidebar-bottom">
            <div class="sidebar-user">
                <span class="nav-icon">👤</span>
                <span class="nav-label"><?= htmlspecialchars($_SESSION['user_id'] ?? 'User') ?></span>
            </div>
            <button class="btn-sidebar-logout" onclick="if(confirm('Yakin ingin logout?')) window.location.href='signout.php'">
                <span class="nav-icon">🚪</span>
                <span class="nav-label">Logout</span>
            </button>
        </div>
    </div>
</aside>

<!-- Toggle button yang muncul saat sidebar hidden -->
<button class="sidebar-show-btn" id="sidebarShowBtn" onclick="toggleSidebar()" title="Show Sidebar">
    ▶
</button>