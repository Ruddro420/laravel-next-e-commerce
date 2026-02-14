@php
  $item = function(string $route, string $title, string $desc, string $iconBg) {
    $isActive = request()->routeIs($route);

    // ✅ Active item classes (prevents white-on-click + keeps highlight)
    $base = 'w-full flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition';
    $active = 'bg-slate-900 text-white dark:bg-white dark:text-slate-900';
    $idle = 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800';

    return [
      'href' => route($route),
      'cls' => $base.' '.($isActive ? $active : $idle),
      'isActive' => $isActive,
      'title' => $title,
      'desc' => $desc,
      'iconBg' => $iconBg,
    ];
  };

  $menu = [
    $item('dashboard', 'Overview',  'KPIs & trends',   'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300'),
    $item('orders',    'Orders',    'Manage sales',   'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'),
    $item('products',  'Products',  'Catalog',        'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'),
    $item('customers', 'Customers', 'CRM & segments', 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300'),
    $item('analytics', 'Analytics', 'Reports',        'bg-violet-50 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300'),
    $item('settings',  'Settings',  'Store & user',   'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200'),
  ];
@endphp

<div class="space-y-1">
  @foreach($menu as $m)
    <a href="{{ $m['href'] }}" class="{{ $m['cls'] }}">
      <span class="grid h-9 w-9 place-items-center rounded-2xl {{ $m['iconBg'] }}">
        {{-- simple icon dot --}}
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 3v18M3 12h18"/>
        </svg>
      </span>
      <div class="text-left">
        <div>{{ $m['title'] }}</div>
        <div class="text-xs opacity-70">{{ $m['desc'] }}</div>
      </div>
    </a>
  @endforeach
</div>
