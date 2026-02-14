<script>
    // Collapsible Sidebar Sections (Products/CRM/Settings)
    document.querySelectorAll('[data-collapse-btn]').forEach(btn => {
        btn.addEventListener('click', () => {
            const key = btn.getAttribute('data-collapse-btn');

            // Scope: if you have multiple sidebars (desktop + drawer), toggle the nearest one.
            const root = btn.closest('nav') || document;
            const panel = root.querySelector(`[data-collapse-panel="${key}"]`);
            const chev = btn.querySelector(`[data-collapse-chevron="${key}"]`) || root.querySelector(`[data-collapse-chevron="${key}"]`);

            if (!panel) return;

            const willOpen = panel.classList.contains('hidden');
            panel.classList.toggle('hidden', !willOpen);
            chev?.classList.toggle('rotate-180', willOpen);

            // Remember state
            localStorage.setItem(`collapse:${key}`, willOpen ? 'open' : 'closed');
            btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });

    // Restore dropdown states (only if server didn't open due to active route)
    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-collapse-panel]').forEach(panel => {
            const key = panel.getAttribute('data-collapse-panel');

            // If already open via server (active route), don't override
            if (!panel.classList.contains('hidden')) return;

            const saved = localStorage.getItem(`collapse:${key}`);
            if (saved === 'open') {
                panel.classList.remove('hidden');

                // Rotate matching chevrons (desktop + mobile)
                document.querySelectorAll(`[data-collapse-chevron="${key}"]`).forEach(ch => {
                    ch.classList.add('rotate-180');
                });

                document.querySelectorAll(`[data-collapse-btn="${key}"]`).forEach(b => {
                    b.setAttribute('aria-expanded', 'true');
                });
            }
        });
    });
    //   Mobile responsive drawer (for smaller screens)

    (function() {
        const openBtn = document.getElementById('btnOpenDrawer');
        const closeBtn = document.getElementById('btnCloseDrawer');
        const overlay = document.getElementById('drawerOverlay');
        const panel = document.getElementById('drawerPanel');

        if (!openBtn || !closeBtn || !overlay || !panel) return;

        function openDrawer() {
            overlay.classList.remove('hidden');
            panel.classList.remove('-translate-x-full');
            document.body.classList.add('overflow-hidden');
        }

        function closeDrawer() {
            overlay.classList.add('hidden');
            panel.classList.add('-translate-x-full');
            document.body.classList.remove('overflow-hidden');
        }

        // Click handlers
        openBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openDrawer();
        });
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeDrawer();
        });
        overlay.addEventListener('click', closeDrawer);

        // ESC closes
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeDrawer();
        });

        // Prevent clicks inside panel from closing (safety)
        panel.addEventListener('click', (e) => e.stopPropagation());
    })();


    



</script>