<script>
  // Theme (localStorage)
  const root = document.documentElement;
  const icon = document.getElementById('themeIcon');
  const saved = localStorage.getItem('theme');

  if (saved === 'dark') root.classList.add('dark');
  function syncIcon() {
    icon.textContent = root.classList.contains('dark') ? '☀️' : '🌙';
  }
  syncIcon();

  document.getElementById('btnTheme')?.addEventListener('click', () => {
    root.classList.toggle('dark');
    localStorage.setItem('theme', root.classList.contains('dark') ? 'dark' : 'light');
    syncIcon();
  });

  // Profile dropdown
  const btnProfile = document.getElementById('btnProfile');
  const profileMenu = document.getElementById('profileMenu');
  btnProfile?.addEventListener('click', (e) => {
    e.stopPropagation();
    profileMenu.classList.toggle('hidden');
  });
  document.addEventListener('click', () => profileMenu?.classList.add('hidden'));

  // Mobile Drawer
  const overlay = document.getElementById('drawerOverlay');
  const panel = document.getElementById('drawerPanel');

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

  document.getElementById('btnOpenDrawer')?.addEventListener('click', openDrawer);
  document.getElementById('btnCloseDrawer')?.addEventListener('click', closeDrawer);
  overlay?.addEventListener('click', closeDrawer);
</script>
