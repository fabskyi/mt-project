// sidebar.js — Shared sidebar logic untuk semua page PT. YADIN
// Include di setiap page: <script src="sidebar.js"></script>

(function () {
    const STORAGE_KEY = 'pt_yadin_sidebar_collapsed';

    function getSidebar()    { return document.getElementById('mainSidebar'); }
    function getShowBtn()    { return document.getElementById('sidebarShowBtn'); }
    function getToggleIcon() { return document.getElementById('toggleIcon'); }

    // ── Toggle sidebar ───────────────────────────────────────────────────────
    function toggleSidebar() {
        const sidebar = getSidebar();
        if (!sidebar) return;

        const isCollapsed = sidebar.classList.toggle('collapsed');

        // Simpan state ke localStorage
        localStorage.setItem(STORAGE_KEY, isCollapsed ? '1' : '0');

        updateToggleIcon(isCollapsed);
        updateShowBtn(isCollapsed);
    }

    function updateToggleIcon(isCollapsed) {
        const icon = getToggleIcon();
        if (icon) icon.textContent = isCollapsed ? '▶' : '◀';
    }

    function updateShowBtn(isCollapsed) {
        const btn = getShowBtn();
        if (!btn) return;
        if (isCollapsed) {
            btn.classList.add('visible');
            btn.style.display = 'flex';
        } else {
            btn.classList.remove('visible');
            btn.style.display = 'none';
        }
    }

    // ── Restore state on load ────────────────────────────────────────────────
    function restoreState() {
        const sidebar    = getSidebar();
        if (!sidebar) return;

        const isCollapsed = localStorage.getItem(STORAGE_KEY) === '1';

        if (isCollapsed) {
            sidebar.classList.add('collapsed');
        } else {
            sidebar.classList.remove('collapsed');
        }

        updateToggleIcon(isCollapsed);
        updateShowBtn(isCollapsed);
    }

    // ── Keyboard shortcut: [ to toggle ──────────────────────────────────────
    document.addEventListener('keydown', function (e) {
        // Ctrl+B or [ to toggle sidebar (like VS Code)
        if ((e.ctrlKey && e.key === 'b') || e.key === '[') {
            const active = document.activeElement;
            // Jangan trigger kalau user sedang mengetik di input
            if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA')) return;
            e.preventDefault();
            toggleSidebar();
        }
    });

    // ── Expose globally ──────────────────────────────────────────────────────
    window.toggleSidebar = toggleSidebar;

    // ── Run on DOM ready ─────────────────────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', restoreState);
    } else {
        restoreState();
    }
})();