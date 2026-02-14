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

@php
  $isProductsOpen = request()->routeIs('products.*');
  $activeLink = function(string $routeName) {
    return request()->routeIs($routeName)
      ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900'
      : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800';
  };
@endphp

<!-- PRODUCTS DROPDOWN -->
<div class="space-y-1">
  <!-- Parent button -->
  <button
    type="button"
    class="w-full flex items-center justify-between rounded-2xl px-3 py-2.5 text-sm font-semibold transition
           {{ $isProductsOpen ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800' }}"
    data-collapse-btn="products"
    aria-expanded="{{ $isProductsOpen ? 'true' : 'false' }}"
  >
    <span class="flex items-center gap-3">
      <span class="grid h-9 w-9 place-items-center rounded-2xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="m7.5 4.27 9 5.15v10.16l-9-5.15V4.27Z"/>
          <path d="m16.5 9.42 3-1.72v10.16l-3 1.72V9.42Z"/>
          <path d="M7.5 4.27 4.5 6v10.16l3-1.73V4.27Z"/>
        </svg>
      </span>

      <span class="text-left">
        <span class="block">Products</span>
        <span class="block text-xs opacity-70">Catalog</span>
      </span>
    </span>

    <!-- Chevron -->
    <svg class="h-4 w-4 opacity-80 transition-transform duration-200 {{ $isProductsOpen ? 'rotate-180' : '' }}"
         data-collapse-chevron="products"
         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="m6 9 6 6 6-6"/>
    </svg>
  </button>

  <!-- Children -->
  <div
    class="pl-3 pr-1 space-y-1 {{ $isProductsOpen ? '' : 'hidden' }}"
    data-collapse-panel="products"
  >
    <a href="{{ route('products.index') }}"
       class="block rounded-2xl px-3 py-2 text-sm font-semibold transition {{ $activeLink('products.index') }}">
      All Products
    </a>

    <a href="{{ route('products.create') }}"
       class="block rounded-2xl px-3 py-2 text-sm font-semibold transition {{ $activeLink('products.create') }}">
      Add Product
    </a>

    <a href="{{ route('products.categories') }}"
       class="block rounded-2xl px-3 py-2 text-sm font-semibold transition {{ $activeLink('products.categories') }}">
      Categories
    </a>

    <a href="{{ route('products.brands') }}"
       class="block rounded-2xl px-3 py-2 text-sm font-semibold transition {{ $activeLink('products.brands') }}">
      Brands
    </a>

    <a href="{{ route('products.tags') }}"
       class="block rounded-2xl px-3 py-2 text-sm font-semibold transition {{ $activeLink('products.tags') }}">
      Tags
    </a>

    <a href="{{ route('products.attributes') }}"
       class="block rounded-2xl px-3 py-2 text-sm font-semibold transition {{ $activeLink('products.attributes') }}">
      Attributes
    </a>

    <a href="{{ route('products.reviews') }}"
       class="block rounded-2xl px-3 py-2 text-sm font-semibold transition {{ $activeLink('products.reviews') }}">
      Reviews
    </a>
  </div>
</div>

