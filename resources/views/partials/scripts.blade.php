<script>
    (function() {
        // -----------------------------
        // Utils
        // -----------------------------
        const qs = (sel, root = document) => root.querySelector(sel);
        const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));

        // -----------------------------
        // 1) Mobile Drawer (Hamburger)
        // -----------------------------
        const openBtn = document.getElementById('btnOpenDrawer');
        const closeBtn = document.getElementById('btnCloseDrawer');
        const overlay = document.getElementById('drawerOverlay');
        const panel = document.getElementById('drawerPanel');

        function openDrawer() {
            if (!overlay || !panel) return;
            overlay.classList.remove('hidden');
            panel.classList.remove('-translate-x-full');
            document.body.classList.add('overflow-hidden');
        }

        function closeDrawer() {
            if (!overlay || !panel) return;
            overlay.classList.add('hidden');
            panel.classList.add('-translate-x-full');
            document.body.classList.remove('overflow-hidden');
        }

        if (openBtn && closeBtn && overlay && panel) {
            openBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                openDrawer();
            });
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                closeDrawer();
            });

            overlay.addEventListener('click', (e) => {
                e.preventDefault();
                closeDrawer();
            });

            // Prevent any click inside drawer from triggering outside handlers
            panel.addEventListener('click', (e) => e.stopPropagation());
        }

        // -----------------------------
        // 2) Profile Dropdown (Topbar)
        // Markup must use:
        // [data-dropdown] [data-dropdown-btn] [data-dropdown-menu]
        // -----------------------------
        const dropdowns = qsa('[data-dropdown]');

        function closeProfileDropdowns(except = null) {
            dropdowns.forEach(dd => {
                if (except && dd === except) return;
                const menu = qs('[data-dropdown-menu]', dd);
                const btn = qs('[data-dropdown-btn]', dd);
                const chev = qs('[data-dropdown-chevron]', dd);
                menu?.classList.add('hidden');
                btn?.setAttribute('aria-expanded', 'false');
                chev?.classList.remove('rotate-180');
            });
        }

        dropdowns.forEach(dd => {
            const btn = qs('[data-dropdown-btn]', dd);
            const menu = qs('[data-dropdown-menu]', dd);
            const chev = qs('[data-dropdown-chevron]', dd);
            if (!btn || !menu) return;

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const isOpen = !menu.classList.contains('hidden');
                closeProfileDropdowns(dd);

                if (!isOpen) {
                    menu.classList.remove('hidden');
                    btn.setAttribute('aria-expanded', 'true');
                    chev?.classList.add('rotate-180');
                } else {
                    menu.classList.add('hidden');
                    btn.setAttribute('aria-expanded', 'false');
                    chev?.classList.remove('rotate-180');
                }
            });

            menu.addEventListener('click', (e) => e.stopPropagation());
        });

        // -----------------------------
        // 3) Sidebar Collapses (Smooth)
        // Works for both desktop nav + mobile drawer sidebar
        // Requires panel markup using max-height (not hidden)
        // -----------------------------
        function setPanel(panel, open) {
            const inner = qs('[data-collapse-inner]', panel) || panel.firstElementChild;
            if (!inner) return;

            // Measure height each time (handles dynamic content)
            const target = open ? inner.scrollHeight : 0;

            panel.style.maxHeight = target + 'px';
            panel.dataset.open = open ? '1' : '0';
        }

        function initSidebarCollapses(root) {
            qsa('[data-collapse-btn]', root).forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const key = btn.getAttribute('data-collapse-btn');
                    const panel = qs(`[data-collapse-panel="${key}"]`, root);
                    if (!panel) return;

                    const chev = qs(`[data-collapse-chevron="${key}"]`, btn) || qs(`[data-collapse-chevron="${key}"]`, root);
                    const isOpen = panel.dataset.open === '1';
                    const willOpen = !isOpen;

                    setPanel(panel, willOpen);
                    chev?.classList.toggle('rotate-180', willOpen);
                    btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

                    localStorage.setItem(`collapse:${key}`, willOpen ? 'open' : 'closed');
                });
            });
        }

        // -----------------------------
        // 4) Outside click closes profile dropdown
        // (and does NOT break drawer)
        // -----------------------------
        document.addEventListener('click', () => {
            closeProfileDropdowns();
        });

        // -----------------------------
        // 5) ESC closes everything
        // -----------------------------
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            closeProfileDropdowns();
            closeDrawer();
        });

        // -----------------------------
        // 6) Init on DOM ready
        // -----------------------------
        window.addEventListener('DOMContentLoaded', () => {
            // Init collapses inside desktop nav + inside drawer panel
            qsa('nav').forEach(nav => initSidebarCollapses(nav));
            if (panel) initSidebarCollapses(panel);

            // Restore collapse state for each panel (server default OR saved)
            qsa('[data-collapse-panel]').forEach(panelEl => {
                const key = panelEl.getAttribute('data-collapse-panel');

                const saved = localStorage.getItem(`collapse:${key}`);
                const serverDefault = panelEl.getAttribute('data-collapse-default'); // "open" or "closed"

                const shouldOpen = (serverDefault === 'open') || (serverDefault !== 'open' && saved === 'open');
                setPanel(panelEl, shouldOpen);

                // rotate all matching chevrons
                qsa(`[data-collapse-chevron="${key}"]`).forEach(ch => ch.classList.toggle('rotate-180', shouldOpen));
                qsa(`[data-collapse-btn="${key}"]`).forEach(b => b.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false'));
            });
        });
    })();
    //   ----------------------------- For Mobile Drawer (Hamburger) -----------------------------

    (function() {
        const btn = document.getElementById('btnProfile');
        const menu = document.getElementById('profileMenu');
        if (!btn || !menu) return;

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            menu.classList.toggle('hidden');
        });

        menu.addEventListener('click', (e) => e.stopPropagation());

        document.addEventListener('click', () => menu.classList.add('hidden'));
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') menu.classList.add('hidden');
        });
    })();
    // Dark Mode Toggle
    (function() {
        const root = document.documentElement;
        const btn = document.getElementById('btnTheme');
        const icon = document.getElementById('themeIcon');

        if (!btn || !icon) return;

        // Init theme from localStorage
        const saved = localStorage.getItem('theme');
        if (saved === 'dark') root.classList.add('dark');

        function syncIcon() {
            icon.textContent = root.classList.contains('dark') ? '☀️' : '🌙';
        }
        syncIcon();

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            root.classList.toggle('dark');
            localStorage.setItem('theme', root.classList.contains('dark') ? 'dark' : 'light');
            syncIcon();
        });
    })();
</script>